<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Booking;
use App\Services\MidtransService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class MidtransWebhookController extends Controller
{
    public function __construct(private MidtransService $midtrans) {}

    public function handle(Request $request)
    {
        $key = 'webhook|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 100)) {
            return response()->json(['error' => 'Too many requests'], 429);
        }
        RateLimiter::hit($key, 60);

        $payload = $request->all();
        $orderId     = $payload['order_id']     ?? '';
        $statusCode  = $payload['status_code']  ?? '';
        $grossAmount = $payload['gross_amount']  ?? '';
        $signatureKey = $payload['signature_key'] ?? '';

        // Verify signature (read config at request time, not at inject time)
        $serverKey = config('services.midtrans.server_key');
        if ($serverKey) {
            $computedHash = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
            if (!hash_equals($computedHash, $signatureKey)) {
                Log::warning('Midtrans webhook: invalid signature for order ' . $orderId);
                return response()->json(['error' => 'Invalid signature'], 403);
            }
        }

        try {
            DB::transaction(function () use ($payload, $orderId, $grossAmount) {
                $payment = Payment::withoutGlobalScope('not_deleted')
                    ->where('payment_code', $orderId)
                    ->lockForUpdate()
                    ->first();

                if (!$payment) {
                    Log::warning("Midtrans webhook: payment not found for order {$orderId}");
                    return;
                }

                // Idempotency: skip if already succeeded
                if ($payment->status === 'succeeded') {
                    return;
                }

                $transactionStatus = $payload['transaction_status'] ?? '';
                $fraudStatus       = $payload['fraud_status'] ?? '';

                // Map status
                $newStatus = match ($transactionStatus) {
                    'settlement', 'capture' => ($fraudStatus === 'challenge') ? 'pending' : 'succeeded',
                    'pending'   => 'pending',
                    'expire'    => 'expired',
                    'cancel', 'deny' => 'failed',
                    'refund'    => 'refunded',
                    default     => 'pending',
                };

                $payment->status = $newStatus;

                if ($newStatus === 'succeeded') {
                    // Validate amount
                    $paidGross = (float) $grossAmount;
                    if (abs($paidGross - $payment->requested_amount) > 1) {
                        Log::error("Midtrans webhook: amount mismatch for {$orderId}. Expected {$payment->requested_amount}, got {$paidGross}");
                        return;
                    }

                    $payment->paid_amount          = $payment->requested_amount;
                    $payment->paid_at              = now();
                    $payment->external_payment_id  = $payload['transaction_id'] ?? null;
                    $payment->external_reference_id = $payload['transaction_id'] ?? null;
                    $payment->save();

                    // Update booking
                    $this->updateBookingAfterPayment($payment);
                } else {
                    $payment->save();

                    // Mark booking expired if payment expired
                    if ($newStatus === 'expired' || $newStatus === 'failed') {
                        $booking = Booking::withoutGlobalScope('not_deleted')
                            ->where('id', $payment->booking_id)
                            ->lockForUpdate()
                            ->first();

                        if ($booking && $booking->booking_status === 'pending_payment' && $payment->payment_type === 'initial') {
                            $booking->booking_status = $newStatus === 'expired' ? 'expired' : 'canceled';
                            $booking->payment_status = 'unpaid';
                            $booking->save();
                        }
                    }
                }
            });
        } catch (\Throwable $e) {
            Log::error('Midtrans webhook error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'Internal server error'], 500);
        }

        return response()->json(['status' => 'ok']);
    }

    private function updateBookingAfterPayment(Payment $payment): void
    {
        $booking = Booking::withoutGlobalScope('not_deleted')
            ->where('id', $payment->booking_id)
            ->lockForUpdate()
            ->first();

        if (!$booking) {
            return;
        }

        if ($payment->payment_type === 'initial') {
            $booking->total_paid     = $payment->paid_amount;
            $booking->booking_status = 'delivered';

            if ($booking->payment_option === 'full') {
                $booking->remaining_amount = 0;
                $booking->payment_status   = 'paid';
            } else {
                // DP
                $booking->remaining_amount = $booking->total_amount - $payment->paid_amount;
                $booking->payment_status   = 'partial';
            }
        } elseif ($payment->payment_type === 'remaining') {
            // Guard: jika booking sudah lunas (misal cash dicatat lebih dulu),
            // jangan tambahkan pembayaran lagi untuk cegah double credit.
            if ($booking->payment_status === 'paid') {
                Log::warning("Webhook remaining skipped: booking {$booking->booking_code} already paid.");
                return;
            }

            $booking->total_paid       = $booking->total_paid + $payment->paid_amount;
            $booking->remaining_amount = 0;
            $booking->payment_status   = 'paid';
        }

        $booking->save();
    }
}

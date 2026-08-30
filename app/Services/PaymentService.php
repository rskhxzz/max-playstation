<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(private MidtransService $midtrans) {}

    /**
     * Create initial payment record and Midtrans Snap transaction.
     */
    public function createInitialPayment(Booking $booking): Payment
    {
        $paymentCode = 'PAY-' . strtoupper(Str::random(10)) . '-' . time();

        $customerDetails = [
            'first_name' => $booking->customer->full_name,
            'phone'      => $booking->customer->phone_number,
        ];

        $snapResult = [];

        // Only call Midtrans if server key is configured
        if (config('services.midtrans.server_key')) {
            try {
                $snapResult = $this->midtrans->createSnapTransaction(
                    $paymentCode,
                    $booking->initial_payment_amount,
                    $customerDetails
                );
            } catch (\Throwable $e) {
                Log::error('Midtrans createSnapTransaction error: ' . $e->getMessage());
                throw new \RuntimeException('Gagal membuat transaksi pembayaran: ' . $e->getMessage());
            }
        }

        $expiryMinutes = config('services.midtrans.expiry_minutes', 60);
        $setting = \App\Models\BusinessSetting::active();
        if ($setting && $setting->payment_expiry_minutes) {
            $expiryMinutes = $setting->payment_expiry_minutes;
        }

        $payment = Payment::create([
            'booking_id'           => $booking->id,
            'payment_code'         => $paymentCode,
            'payment_type'         => 'initial',
            'payment_method'       => 'qris',
            'provider'             => 'midtrans',
            'requested_amount'     => $booking->initial_payment_amount,
            'paid_amount'          => 0,
            'status'               => 'pending',
            'qr_string'            => $snapResult['snap_token'] ?? null,
            'qr_url'               => $snapResult['redirect_url'] ?? null,
            'expires_at'           => now()->addMinutes($expiryMinutes),
        ]);

        return $payment;
    }

    /**
     * Create remaining (pelunasan) payment.
     */
    public function createRemainingPayment(Booking $booking, string $userId): Payment
    {
        // Ensure no active remaining payment exists
        $existing = Payment::where('booking_id', $booking->id)
            ->where('payment_type', 'remaining')
            ->whereIn('status', ['pending', 'succeeded'])
            ->first();

        if ($existing) {
            if ($existing->status === 'succeeded') {
                throw new \RuntimeException('Pelunasan sudah berhasil dibayar.');
            }
            // Return existing pending payment
            return $existing;
        }

        $paymentCode = 'REM-' . strtoupper(Str::random(10)) . '-' . time();

        $customerDetails = [
            'first_name' => $booking->customer->full_name,
            'phone'      => $booking->customer->phone_number,
        ];

        $snapResult = [];

        if (config('services.midtrans.server_key')) {
            try {
                $snapResult = $this->midtrans->createSnapTransaction(
                    $paymentCode,
                    $booking->remaining_amount,
                    $customerDetails
                );
            } catch (\Throwable $e) {
                Log::error('Midtrans createRemainingPayment error: ' . $e->getMessage());
                throw new \RuntimeException('Gagal membuat QRIS pelunasan: ' . $e->getMessage());
            }
        }

        $payment = Payment::create([
            'booking_id'       => $booking->id,
            'payment_code'     => $paymentCode,
            'payment_type'     => 'remaining',
            'payment_method'   => 'qris',
            'provider'         => 'midtrans',
            'requested_amount' => $booking->remaining_amount,
            'paid_amount'      => 0,
            'status'           => 'pending',
            'qr_string'        => $snapResult['snap_token'] ?? null,
            'qr_url'           => $snapResult['redirect_url'] ?? null,
            'expires_at'       => now()->addMinutes(60),
            'created_by'       => $userId,
        ]);

        return $payment;
    }
}

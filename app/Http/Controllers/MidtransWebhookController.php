<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class MidtransWebhookController extends Controller
{
    public function __construct(
        private MidtransService $midtrans
    ) {}

    public function handle(
        Request $request
    ): JsonResponse {
        $rateLimitKey =
            'midtrans-webhook|'
            . $request->ip();

        if (
            RateLimiter::tooManyAttempts(
                $rateLimitKey,
                100
            )
        ) {
            return response()->json(
                [
                    'error' =>
                    'Too many requests',
                ],
                429
            );
        }

        RateLimiter::hit(
            $rateLimitKey,
            60
        );

        if (!$this->midtrans->isConfigured()) {
            Log::critical(
                'Midtrans webhook rejected: server key is not configured.'
            );

            return response()->json(
                [
                    'error' =>
                    'Midtrans is not configured',
                ],
                500
            );
        }

        $orderId = trim(
            (string) $request->input(
                'order_id',
                ''
            )
        );

        $statusCode = trim(
            (string) $request->input(
                'status_code',
                ''
            )
        );

        $grossAmount = trim(
            (string) $request->input(
                'gross_amount',
                ''
            )
        );

        $signatureKey = trim(
            (string) $request->input(
                'signature_key',
                ''
            )
        );

        $transactionStatus = strtolower(
            trim(
                (string) $request->input(
                    'transaction_status',
                    ''
                )
            )
        );

        $fraudStatus = strtolower(
            trim(
                (string) $request->input(
                    'fraud_status',
                    ''
                )
            )
        );

        if (
            $orderId === ''
            || $statusCode === ''
            || $grossAmount === ''
            || $signatureKey === ''
            || !is_numeric($grossAmount)
        ) {
            Log::warning(
                'Midtrans webhook rejected: invalid payload.',
                [
                    'order_id' => $orderId,
                ]
            );

            return response()->json(
                [
                    'error' =>
                    'Invalid payload',
                ],
                422
            );
        }

        if (
            !$this->midtrans->verifySignature(
                $orderId,
                $statusCode,
                $grossAmount,
                $signatureKey
            )
        ) {
            Log::warning(
                'Midtrans webhook rejected: invalid signature.',
                [
                    'order_id' => $orderId,
                ]
            );

            return response()->json(
                [
                    'error' =>
                    'Invalid signature',
                ],
                403
            );
        }

        $newStatus = $this->resolveStatus(
            $transactionStatus,
            $statusCode,
            $fraudStatus
        );

        if ($newStatus === null) {
            Log::info(
                'Midtrans webhook ignored: unsupported transaction status.',
                [
                    'order_id' => $orderId,
                    'transaction_status' =>
                    $transactionStatus,
                    'fraud_status' =>
                    $fraudStatus,
                    'status_code' =>
                    $statusCode,
                ]
            );

            return response()->json([
                'status' => 'ignored',
            ]);
        }

        try {
            DB::transaction(
                function () use (
                    $request,
                    $orderId,
                    $grossAmount,
                    $newStatus
                ): void {
                    $payment =
                        Payment::withoutGlobalScope(
                            'not_deleted'
                        )
                        ->where(
                            'payment_code',
                            $orderId
                        )
                        ->lockForUpdate()
                        ->first();

                    if (!$payment) {
                        Log::warning(
                            'Midtrans webhook: payment not found.',
                            [
                                'order_id' =>
                                $orderId,
                            ]
                        );

                        throw new \RuntimeException(
                            'Payment not found.'
                        );
                    }

                    if (
                        $this->shouldIgnoreStatus(
                            $payment->status,
                            $newStatus
                        )
                    ) {
                        Log::info(
                            'Midtrans webhook ignored: stale or duplicate status.',
                            [
                                'order_id' =>
                                $orderId,
                                'current_status' =>
                                $payment->status,
                                'incoming_status' =>
                                $newStatus,
                            ]
                        );

                        return;
                    }

                    if (
                        $newStatus
                        === 'succeeded'
                    ) {
                        $this->validateAmount(
                            $payment,
                            $grossAmount
                        );
                    }

                    $payment->status =
                        $newStatus;

                    if (
                        $newStatus
                        === 'succeeded'
                    ) {
                        $payment->paid_amount =
                            $payment->requested_amount;

                        $payment->paid_at =
                            $payment->paid_at
                            ?? now();

                        $transactionId =
                            $request->input(
                                'transaction_id'
                            );

                        if (
                            $transactionId
                        ) {
                            $payment->external_payment_id =
                                $transactionId;

                            $payment->external_reference_id =
                                $transactionId;
                        }
                    }

                    if (
                        $newStatus
                        === 'refunded'
                    ) {
                        $payment->paid_at =
                            $payment->paid_at
                            ?? now();
                    }

                    $payment->save();

                    if (
                        $newStatus
                        === 'succeeded'
                    ) {
                        $this->updateBookingAfterPayment(
                            $payment
                        );

                        return;
                    }

                    if (
                        in_array(
                            $newStatus,
                            [
                                'expired',
                                'failed',
                            ],
                            true
                        )
                    ) {
                        $this->updateBookingAfterPaymentFailure(
                            $payment,
                            $newStatus
                        );
                    }
                }
            );
        } catch (
            \RuntimeException $e
        ) {
            Log::error(
                'Midtrans webhook rejected.',
                [
                    'order_id' =>
                    $orderId,
                    'message' =>
                    $e->getMessage(),
                ]
            );

            return response()->json(
                [
                    'error' =>
                    $e->getMessage(),
                ],
                $e->getMessage()
                    === 'Payment not found.'
                    ? 404
                    : 422
            );
        } catch (
            \Throwable $e
        ) {
            Log::error(
                'Midtrans webhook error.',
                [
                    'order_id' =>
                    $orderId,
                    'message' =>
                    $e->getMessage(),
                ]
            );

            return response()->json(
                [
                    'error' =>
                    'Internal server error',
                ],
                500
            );
        }

        return response()->json([
            'status' => 'ok',
        ]);
    }

    private function resolveStatus(
        string $transactionStatus,
        string $statusCode,
        string $fraudStatus
    ): ?string {
        if (
            in_array(
                $transactionStatus,
                [
                    'settlement',
                    'capture',
                ],
                true
            )
        ) {
            if (
                $statusCode !== '200'
            ) {
                return null;
            }

            if (
                $fraudStatus !== ''
                && !in_array(
                    $fraudStatus,
                    [
                        'accept',
                        'accepted',
                    ],
                    true
                )
            ) {
                return 'pending';
            }

            return 'succeeded';
        }

        return match ($transactionStatus) {
            'pending' => 'pending',
            'expire' => 'expired',
            'cancel',
            'deny',
            'failure' => 'failed',
            'refund' => 'refunded',
            default => null,
        };
    }

    private function shouldIgnoreStatus(
        ?string $currentStatus,
        string $incomingStatus
    ): bool {
        if (!$currentStatus) {
            return false;
        }

        if (
            $currentStatus === $incomingStatus
        ) {
            return true;
        }

        if (
            $currentStatus === 'refunded'
        ) {
            return true;
        }

        if (
            $currentStatus === 'succeeded'
            && $incomingStatus !== 'refunded'
        ) {
            return true;
        }

        return $this->getStatusPriority(
            $incomingStatus
        ) < $this->getStatusPriority(
            $currentStatus
        );
    }

    private function getStatusPriority(
        string $status
    ): int {
        return match ($status) {
            'pending' => 10,
            'failed',
            'expired' => 20,
            'succeeded' => 30,
            'refunded' => 40,
            default => 0,
        };
    }

    private function validateAmount(
        Payment $payment,
        string $grossAmount
    ): void {
        $expected =
            round(
                (float) $payment->requested_amount,
                2
            );

        $received =
            round(
                (float) $grossAmount,
                2
            );

        if (
            abs(
                $received
                    - $expected
            ) > 0.01
        ) {
            Log::error(
                'Midtrans webhook rejected: amount mismatch.',
                [
                    'order_id' =>
                    $payment->payment_code,
                    'expected' =>
                    $expected,
                    'received' =>
                    $received,
                ]
            );

            throw new \RuntimeException(
                'Nominal pembayaran Midtrans tidak sesuai.'
            );
        }
    }

    private function updateBookingAfterPayment(
        Payment $payment
    ): void {
        $booking =
            Booking::withoutGlobalScope(
                'not_deleted'
            )
            ->where(
                'id',
                $payment->booking_id
            )
            ->lockForUpdate()
            ->first();

        if (!$booking) {
            Log::warning(
                'Midtrans webhook: booking not found.',
                [
                    'payment_id' =>
                    $payment->id,
                    'booking_id' =>
                    $payment->booking_id,
                ]
            );

            throw new \RuntimeException(
                'Booking not found.'
            );
        }

        if (
            $payment->payment_type
            === 'initial'
        ) {
            $booking->total_paid =
                $payment->paid_amount;

            $booking->booking_status =
                'delivered';

            if (
                $booking->payment_option
                === 'full'
            ) {
                $booking->remaining_amount =
                    0;

                $booking->payment_status =
                    'paid';
            } else {
                $booking->remaining_amount =
                    max(
                        0,
                        $booking->total_amount
                            - $payment->paid_amount
                    );

                $booking->payment_status =
                    'partial';
            }
        }

        if (
            $payment->payment_type
            === 'remaining'
        ) {
            if (
                $booking->payment_status
                === 'paid'
            ) {
                Log::warning(
                    'Midtrans webhook remaining skipped: booking already paid.',
                    [
                        'booking_code' =>
                        $booking->booking_code,
                    ]
                );

                return;
            }

            $booking->total_paid +=
                $payment->paid_amount;

            $booking->remaining_amount =
                0;

            $booking->payment_status =
                'paid';
        }

        $booking->save();
    }

    private function updateBookingAfterPaymentFailure(
        Payment $payment,
        string $status
    ): void {
        if (
            $payment->payment_type
            !== 'initial'
        ) {
            return;
        }

        $booking =
            Booking::withoutGlobalScope(
                'not_deleted'
            )
            ->where(
                'id',
                $payment->booking_id
            )
            ->lockForUpdate()
            ->first();

        if (!$booking) {
            return;
        }

        if (
            $booking->booking_status
            !== 'pending_payment'
        ) {
            return;
        }

        $booking->booking_status =
            $status === 'expired'
            ? 'expired'
            : 'canceled';

        $booking->payment_status =
            'unpaid';

        $booking->save();
    }
}

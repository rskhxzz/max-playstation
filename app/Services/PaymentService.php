<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BusinessSetting;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private MidtransService $midtrans
    ) {}

    public function createInitialPayment(
        Booking $booking,
        ?string $paymentCode = null
    ): Payment {
        $paymentCode ??= 'PAY-'
            . strtoupper(Str::random(10))
            . '-'
            . time();

        $customerDetails = [
            'first_name' => $booking->customer->full_name,
            'phone' => $booking->customer->phone_number,
        ];

        try {
            $snapResult = $this->midtrans->createSnapTransaction(
                $paymentCode,
                $booking->initial_payment_amount,
                $customerDetails
            );
        } catch (\Throwable $e) {
            Log::error('Midtrans createInitialPayment error', [
                'booking_id' => $booking->id,
                'payment_code' => $paymentCode,
                'message' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'Gagal membuat transaksi pembayaran.'
            );
        }

        return Payment::create([
            'booking_id' => $booking->id,
            'payment_code' => $paymentCode,
            'payment_type' => 'initial',
            'payment_method' => 'qris',
            'provider' => 'midtrans',
            'requested_amount' => $booking->initial_payment_amount,
            'paid_amount' => 0,
            'status' => 'pending',
            'qr_string' => $snapResult['snap_token'],
            'qr_url' => $snapResult['redirect_url'] ?: null,
            'expires_at' => now()->addMinutes(
                $this->getPaymentExpiryMinutes()
            ),
        ]);
    }

    public function settleRemainingByCash(
        Booking $booking,
        string $userId
    ): Payment {
        return DB::transaction(
            function () use (
                $booking,
                $userId
            ) {
                $booking =
                    Booking::withoutGlobalScope(
                        'not_deleted'
                    )
                    ->where(
                        'id',
                        $booking->id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $booking->booking_status
                    !== 'on_delivery'
                ) {
                    throw new \RuntimeException(
                        'Booking tidak sedang dalam proses pengantaran.'
                    );
                }

                if (
                    $booking->payment_status
                    !== 'partial'
                ) {
                    throw new \RuntimeException(
                        'Tidak ada sisa tagihan yang perlu dilunasi.'
                    );
                }

                $alreadyPaid =
                    Payment::where(
                        'booking_id',
                        $booking->id
                    )
                    ->where(
                        'payment_type',
                        'remaining'
                    )
                    ->where(
                        'status',
                        'succeeded'
                    )
                    ->exists();

                if ($alreadyPaid) {
                    throw new \RuntimeException(
                        'Pelunasan sudah berhasil diproses sebelumnya.'
                    );
                }

                Payment::where(
                    'booking_id',
                    $booking->id
                )
                    ->where(
                        'payment_type',
                        'remaining'
                    )
                    ->where(
                        'status',
                        'pending'
                    )
                    ->update([
                        'status' =>
                        'failed',
                        'updated_by' =>
                        $userId,
                    ]);

                $remainingAmount =
                    (float)
                    $booking->remaining_amount;

                if ($remainingAmount <= 0) {
                    throw new \RuntimeException(
                        'Sisa tagihan tidak valid.'
                    );
                }

                $paymentCode =
                    'CSH-'
                    . strtoupper(
                        Str::random(10)
                    )
                    . '-'
                    . time();

                $payment =
                    Payment::create([
                        'booking_id' =>
                        $booking->id,
                        'payment_code' =>
                        $paymentCode,
                        'payment_type' =>
                        'remaining',
                        'payment_method' =>
                        'cash',
                        'provider' =>
                        'manual',
                        'requested_amount' =>
                        $remainingAmount,
                        'paid_amount' =>
                        $remainingAmount,
                        'status' =>
                        'succeeded',
                        'paid_at' =>
                        now(),
                        'expires_at' =>
                        now(),
                        'created_by' =>
                        $userId,
                        'updated_by' =>
                        $userId,
                    ]);

                $booking->total_paid =
                    (float) $booking->total_paid
                    + $remainingAmount;

                $booking->remaining_amount =
                    0;

                $booking->payment_status =
                    'paid';

                $booking->updated_by =
                    $userId;

                $booking->save();

                Log::info(
                    'Cash pelunasan recorded',
                    [
                        'driver_id' =>
                        $userId,
                        'booking_code' =>
                        $booking->booking_code,
                        'amount' =>
                        $remainingAmount,
                    ]
                );

                return $payment;
            }
        );
    }

    public function createRemainingPayment(
        Booking $booking,
        string $userId
    ): Payment {
        return DB::transaction(
            function () use (
                $booking,
                $userId
            ) {
                $booking =
                    Booking::withoutGlobalScope(
                        'not_deleted'
                    )
                    ->where(
                        'id',
                        $booking->id
                    )
                    ->where(
                        'driver_id',
                        $userId
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $booking->booking_status
                    !== 'on_delivery'
                ) {
                    throw new \RuntimeException(
                        'Booking tidak sedang dalam proses pengantaran.'
                    );
                }

                if (
                    $booking->payment_status
                    !== 'partial'
                ) {
                    throw new \RuntimeException(
                        'Tidak ada sisa tagihan yang perlu dilunasi.'
                    );
                }

                $remainingAmount =
                    (float)
                    $booking->remaining_amount;

                if ($remainingAmount <= 0) {
                    throw new \RuntimeException(
                        'Sisa tagihan tidak valid.'
                    );
                }

                $existing =
                    Payment::where(
                        'booking_id',
                        $booking->id
                    )
                    ->where(
                        'payment_type',
                        'remaining'
                    )
                    ->whereIn(
                        'status',
                        [
                            'pending',
                            'succeeded',
                        ]
                    )
                    ->latest('id')
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    if (
                        $existing->status
                        === 'succeeded'
                    ) {
                        throw new \RuntimeException(
                            'Pelunasan sudah berhasil dibayar.'
                        );
                    }

                    if (
                        $existing->expires_at
                        && $existing->expires_at
                        <= now()
                    ) {
                        $existing->status =
                            'expired';

                        $existing->save();
                    } else {
                        return $existing;
                    }
                }

                $paymentCode =
                    'REM-'
                    . strtoupper(
                        Str::random(10)
                    )
                    . '-'
                    . time();

                $customerDetails = [
                    'first_name' =>
                    $booking
                        ->customer
                        ->full_name,
                    'phone' =>
                    $booking
                        ->customer
                        ->phone_number,
                ];

                try {
                    $snapResult =
                        $this->midtrans
                        ->createSnapTransaction(
                            $paymentCode,
                            $remainingAmount,
                            $customerDetails
                        );
                } catch (
                    \Throwable $e
                ) {
                    Log::error(
                        'Midtrans createRemainingPayment error',
                        [
                            'booking_id' =>
                            $booking->id,
                            'message' =>
                            $e->getMessage(),
                        ]
                    );

                    throw new \RuntimeException(
                        'Gagal membuat QRIS pelunasan.'
                    );
                }

                return Payment::create([
                    'booking_id' =>
                    $booking->id,
                    'payment_code' =>
                    $paymentCode,
                    'payment_type' =>
                    'remaining',
                    'payment_method' =>
                    'qris',
                    'provider' =>
                    'midtrans',
                    'requested_amount' =>
                    $remainingAmount,
                    'paid_amount' =>
                    0,
                    'status' =>
                    'pending',
                    'qr_string' =>
                    $snapResult['snap_token'],
                    'qr_url' =>
                    $snapResult['redirect_url']
                        ?: null,
                    'expires_at' =>
                    now()->addMinutes(
                        $this->getPaymentExpiryMinutes()
                    ),
                    'created_by' =>
                    $userId,
                ]);
            }
        );
    }

    private function getPaymentExpiryMinutes(): int
    {
        $expiryMinutes =
            max(
                1,
                (int) config(
                    'services.midtrans.expiry_minutes',
                    60
                )
            );

        $setting =
            BusinessSetting::active();

        if (
            $setting
            && $setting->payment_expiry_minutes
        ) {
            $expiryMinutes =
                max(
                    1,
                    (int)
                    $setting->payment_expiry_minutes
                );
        }

        return $expiryMinutes;
    }
}

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
     * Catat pelunasan tunai (cash) oleh Driver.
     * Langsung set status succeeded tanpa melalui Midtrans.
     * Jika ada transaksi QRIS pelunasan yang masih pending, batalkan dulu.
     *
     * @throws \RuntimeException
     */
    public function settleRemainingByCash(Booking $booking, string $userId): Payment
    {
        return DB::transaction(function () use ($booking, $userId) {
            // Lock booking untuk cegah race condition
            $booking = Booking::withoutGlobalScope('not_deleted')
                ->where('id', $booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Hanya boleh jika status booking delivered dan payment partial
            if ($booking->booking_status !== 'delivered') {
                throw new \RuntimeException('Booking tidak dalam status siap diantar.');
            }
            if ($booking->payment_status !== 'partial') {
                throw new \RuntimeException('Tidak ada sisa tagihan yang perlu dilunasi.');
            }

            // Cegah double payment: cek apakah sudah ada pelunasan succeeded
            $alreadyPaid = Payment::where('booking_id', $booking->id)
                ->where('payment_type', 'remaining')
                ->where('status', 'succeeded')
                ->exists();

            if ($alreadyPaid) {
                throw new \RuntimeException('Pelunasan sudah berhasil diproses sebelumnya.');
            }

            // Batalkan QRIS pelunasan yang masih pending agar tidak double payment
            // ketika webhook Midtrans datang setelah cash dicatat
            Payment::where('booking_id', $booking->id)
                ->where('payment_type', 'remaining')
                ->where('status', 'pending')
                ->update(['status' => 'failed', 'updated_by' => $userId]);

            $remainingAmount = $booking->remaining_amount;
            $paymentCode     = 'CSH-' . strtoupper(Str::random(10)) . '-' . time();

            // Buat record pembayaran cash dengan status langsung succeeded
            $payment = Payment::create([
                'booking_id'       => $booking->id,
                'payment_code'     => $paymentCode,
                'payment_type'     => 'remaining',
                'payment_method'   => 'cash',
                'provider'         => 'manual',
                'requested_amount' => $remainingAmount,
                'paid_amount'      => $remainingAmount,
                'status'           => 'succeeded',
                'paid_at'          => now(),
                'expires_at'       => now(),
                'created_by'       => $userId,
                'updated_by'       => $userId,
            ]);

            // Perbarui booking: lunas
            $booking->total_paid       = $booking->total_paid + $remainingAmount;
            $booking->remaining_amount = 0;
            $booking->payment_status   = 'paid';
            $booking->updated_by       = $userId;
            $booking->save();

            Log::info("Cash pelunasan recorded by driver {$userId} for booking {$booking->booking_code}, amount {$remainingAmount}");

            return $payment;
        });
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

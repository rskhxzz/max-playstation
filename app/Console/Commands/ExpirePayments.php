<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpirePayments extends Command
{
    protected $signature   = 'payments:expire';
    protected $description = 'Tandai pembayaran yang sudah melewati batas waktu sebagai expired';

    public function handle(): int
    {
        $expiredPayments = Payment::withoutGlobalScope('not_deleted')
            ->where('is_deleted', false)
            ->where('status', 'pending')
            ->where('payment_type', 'initial')
            ->where('expires_at', '<=', now())
            ->get();

        $count = 0;

        foreach ($expiredPayments as $payment) {
            DB::transaction(function () use ($payment, &$count) {
                // Lock for update to prevent race condition with webhook
                $locked = Payment::withoutGlobalScope('not_deleted')
                    ->where('id', $payment->id)
                    ->lockForUpdate()
                    ->first();

                if (!$locked || $locked->status !== 'pending') {
                    return; // Already processed
                }

                $locked->status = 'expired';
                $locked->save();

                $booking = Booking::withoutGlobalScope('not_deleted')
                    ->where('id', $locked->booking_id)
                    ->lockForUpdate()
                    ->first();

                if ($booking && $booking->booking_status === 'pending_payment') {
                    $booking->booking_status = 'expired';
                    $booking->payment_status = 'unpaid';
                    $booking->save();
                    $count++;
                }
            });
        }

        if ($count > 0) {
            Log::info("ExpirePayments: {$count} booking(s) expired.");
            $this->info("{$count} pembayaran telah di-expire.");
        } else {
            $this->info('Tidak ada pembayaran yang perlu di-expire.');
        }

        return self::SUCCESS;
    }
}

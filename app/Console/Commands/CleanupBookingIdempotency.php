<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupBookingIdempotency extends Command
{
    protected $signature = 'booking:cleanup-idempotency';

    protected $description = 'Membersihkan data booking idempotency yang sudah tidak diperlukan';

    public function handle(): int
    {
        $failedDeleted = DB::table('t_booking_idempotency')
            ->whereNull('booking_id')
            ->where(
                'created_at',
                '<',
                now()->subDays(7)
            )
            ->delete();

        $completedDeleted = DB::table('t_booking_idempotency')
            ->whereNotNull('booking_id')
            ->where(
                'updated_at',
                '<',
                now()->subDays(90)
            )
            ->delete();

        $this->info(
            "Deleted failed/unfinished tokens: {$failedDeleted}"
        );

        $this->info(
            "Deleted completed tokens: {$completedDeleted}"
        );

        return self::SUCCESS;
    }
}

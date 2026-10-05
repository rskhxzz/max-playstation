<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Expire pending payments every minute
Schedule::command('payments:expire')->everyMinute();

// Cleanup booking idempotency records daily at 03:00
Schedule::command('booking:cleanup-idempotency')->dailyAt('03:00');

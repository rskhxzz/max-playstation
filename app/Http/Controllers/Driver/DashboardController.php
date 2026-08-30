<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today(config('app.timezone'));

        $readyToDeliver  = Booking::where('booking_status', 'delivered')->count();
        $arrivedToday    = Booking::where('booking_status', 'arrived')
            ->whereDate('arrived_at', $today)
            ->count();

        $deliveries = Booking::where('booking_status', 'delivered')
            ->with(['customer', 'rentalPackage'])
            ->latest()
            ->get();

        $history = Booking::where('booking_status', 'arrived')
            ->with(['customer', 'rentalPackage'])
            ->orderByDesc('arrived_at')
            ->limit(20)
            ->get();

        return view('driver.dashboard', compact('readyToDeliver', 'arrivedToday', 'deliveries', 'history'));
    }
}

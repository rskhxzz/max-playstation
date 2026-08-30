<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\PlaystationUnit;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today(config('app.timezone'));

        $totalOrdersToday   = Booking::whereDate('created_at', $today)->count();
        $pendingPayment     = Booking::where('booking_status', 'pending_payment')->count();
        $readyToDeliver     = Booking::where('booking_status', 'delivered')->count();
        $arrived            = Booking::where('booking_status', 'arrived')->count();

        $incomeToday = Payment::where('status', 'succeeded')
            ->whereDate('paid_at', $today)
            ->sum('paid_amount');

        $incomeMonth = Payment::where('status', 'succeeded')
            ->whereYear('paid_at', $today->year)
            ->whereMonth('paid_at', $today->month)
            ->sum('paid_amount');

        $activeUnits      = PlaystationUnit::where('is_active', true)->where('status', 'available')->count();
        $maintenanceUnits = PlaystationUnit::where('status', 'maintenance')->count();

        $recentBookings = Booking::with(['customer', 'rentalPackage'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalOrdersToday', 'pendingPayment', 'readyToDeliver', 'arrived',
            'incomeToday', 'incomeMonth', 'activeUnits', 'maintenanceUnits', 'recentBookings'
        ));
    }
}

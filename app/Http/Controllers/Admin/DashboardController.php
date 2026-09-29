<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\PlaystationUnit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $timezone = config(
            'app.timezone',
            'Asia/Jakarta'
        );

        $today = Carbon::today($timezone);

        $section = $request->query(
            'section',
            'overview'
        );

        $totalOrdersToday = Booking::whereDate(
            'created_at',
            $today
        )->count();

        $pendingPayment = Booking::where(
            'booking_status',
            'pending_payment'
        )->count();

        $readyToDeliver = Booking::where(
            'booking_status',
            'delivered'
        )
            ->whereNull('driver_id')
            ->count();

        $arrived = Booking::where(
            'booking_status',
            'arrived'
        )->count();

        $incomeToday = Payment::where(
            'status',
            'succeeded'
        )
            ->whereDate(
                'paid_at',
                $today
            )
            ->sum('paid_amount');

        $incomeMonth = Payment::where(
            'status',
            'succeeded'
        )
            ->whereYear(
                'paid_at',
                $today->year
            )
            ->whereMonth(
                'paid_at',
                $today->month
            )
            ->sum('paid_amount');

        $activeUnits = PlaystationUnit::where(
            'is_active',
            true
        )
            ->where(
                'status',
                'available'
            )
            ->count();

        $maintenanceUnits = PlaystationUnit::where(
            'status',
            'maintenance'
        )->count();

        $recentBookings = Booking::with([
            'customer',
            'rentalPackage',
            'driver',
        ])
            ->latest()
            ->limit(10)
            ->get();

        $driverIncome = collect();

        $driverIncomeTotals = [
            'gross' => 0,
            'fuel' => 0,
            'net' => 0,
            'deliveries' => 0,
        ];

        $startDate = $request->query(
            'start_date',
            $today
                ->copy()
                ->startOfMonth()
                ->toDateString()
        );

        $endDate = $request->query(
            'end_date',
            $today->toDateString()
        );

        if (
            $section === 'driver-income'
        ) {
            $validated = $request->validate([
                'start_date' => [
                    'nullable',
                    'date',
                ],
                'end_date' => [
                    'nullable',
                    'date',
                ],
            ]);

            $startDate =
                $validated['start_date']
                ?? $startDate;

            $endDate =
                $validated['end_date']
                ?? $endDate;

            $start = Carbon::parse(
                $startDate,
                $timezone
            )->startOfDay();

            $end = Carbon::parse(
                $endDate,
                $timezone
            )->endOfDay();

            if ($start->gt($end)) {
                return back()
                    ->withErrors([
                        'error' =>
                        'Start date tidak boleh lebih besar dari end date.',
                    ])
                    ->withInput();
            }

            $incomeBookings = Booking::query()
                ->whereNotNull('driver_id')
                ->whereNotNull('delivered_at')
                ->whereNotNull(
                    'delivery_photo_path'
                )
                ->whereIn(
                    'booking_status',
                    [
                        'arrived',
                        'completed',
                    ]
                )
                ->whereBetween(
                    'delivered_at',
                    [
                        $start,
                        $end,
                    ]
                )
                ->with('driver')
                ->get();

            $driverIncome = $incomeBookings
                ->groupBy('driver_id')
                ->map(
                    function ($bookings) {
                        $first =
                            $bookings->first();

                        $driver =
                            $first->driver;

                        return [
                            'driver_id' =>
                            $first->driver_id,

                            'driver_name' =>
                            $driver?->name
                                ?? 'Driver Tidak Diketahui',

                            'gross' =>
                            (float) $bookings
                                ->sum(
                                    'driver_fee'
                                ),

                            'fuel' =>
                            (float) $bookings
                                ->sum(
                                    'fuel_deduction'
                                ),

                            'net' =>
                            (float) $bookings
                                ->sum(
                                    'driver_income'
                                ),

                            'deliveries' =>
                            $bookings->count(),
                        ];
                    }
                )
                ->sortByDesc(
                    'net'
                )
                ->values();

            $driverIncomeTotals = [
                'gross' =>
                (float) $driverIncome
                    ->sum('gross'),

                'fuel' =>
                (float) $driverIncome
                    ->sum('fuel'),

                'net' =>
                (float) $driverIncome
                    ->sum('net'),

                'deliveries' =>
                (int) $driverIncome
                    ->sum('deliveries'),
            ];
        }

        return view(
            'admin.dashboard',
            compact(
                'section',
                'totalOrdersToday',
                'pendingPayment',
                'readyToDeliver',
                'arrived',
                'incomeToday',
                'incomeMonth',
                'activeUnits',
                'maintenanceUnits',
                'recentBookings',
                'driverIncome',
                'driverIncomeTotals',
                'startDate',
                'endDate'
            )
        );
    }
}

<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Booking;
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

        $startDate = $request->input(
            'start_date',
            $today->copy()->startOfMonth()->toDateString()
        );

        $endDate = $request->input(
            'end_date',
            $today->toDateString()
        );

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
                    'Tanggal mulai tidak boleh setelah tanggal akhir.',
                ])
                ->withInput();
        }

        $driverId =
            (string) auth('driver')->id();

        $availableBookings = Booking::query()
            ->where(
                'booking_status',
                'delivered'
            )
            ->whereNull(
                'driver_id'
            )
            ->with([
                'customer',
                'rentalPackage',
                'playstationUnit',
            ])
            ->orderBy(
                'rental_start_at'
            )
            ->get();

        $myDeliveries = Booking::query()
            ->where(
                'driver_id',
                $driverId
            )
            ->whereIn(
                'booking_status',
                [
                    'on_delivery',
                    'arrived',
                ]
            )
            ->with([
                'customer',
                'rentalPackage',
                'playstationUnit',
            ])
            ->orderByRaw(
                "CASE
                    WHEN booking_status = 'on_delivery' THEN 1
                    WHEN booking_status = 'arrived' THEN 2
                    ELSE 3
                END"
            )
            ->orderBy(
                'rental_end_at'
            )
            ->get();

        $pickupReady = Booking::query()
            ->where(
                'driver_id',
                $driverId
            )
            ->where(
                'booking_status',
                'arrived'
            )
            ->whereNotNull(
                'rental_end_at'
            )
            ->where(
                'rental_end_at',
                '<=',
                Carbon::now($timezone)
            )
            ->with([
                'customer',
                'rentalPackage',
                'playstationUnit',
            ])
            ->orderBy(
                'rental_end_at'
            )
            ->get();

        $incomeQuery = Booking::query()
            ->where(
                'driver_id',
                $driverId
            )
            ->where(
                'booking_status',
                'arrived'
            )
            ->whereNotNull(
                'delivered_at'
            )
            ->whereNotNull(
                'delivery_photo_path'
            )
            ->whereBetween(
                'delivered_at',
                [
                    $start,
                    $end,
                ]
            );

        $totalIncome = (float) (
            clone $incomeQuery
        )->sum(
            'driver_income'
        );

        $totalGross = (float) (
            clone $incomeQuery
        )->sum(
            'driver_fee'
        );

        $totalFuelDeduction = (float) (
            clone $incomeQuery
        )->sum(
            'fuel_deduction'
        );

        $totalDeliveries = (int) (
            clone $incomeQuery
        )->count();

        $incomeRows = (clone $incomeQuery)
            ->with([
                'customer',
                'rentalPackage',
            ])
            ->orderByDesc(
                'delivered_at'
            )
            ->limit(10)
            ->get();

        $history = Booking::query()
            ->where(
                'driver_id',
                $driverId
            )
            ->where(
                'booking_status',
                'completed'
            )
            ->with([
                'customer',
                'rentalPackage',
                'playstationUnit',
            ])
            ->orderByDesc(
                'picked_up_at'
            )
            ->limit(20)
            ->get();

        return view(
            'driver.dashboard',
            compact(
                'availableBookings',
                'myDeliveries',
                'pickupReady',
                'totalIncome',
                'totalGross',
                'totalFuelDeduction',
                'totalDeliveries',
                'incomeRows',
                'history',
                'startDate',
                'endDate'
            )
        );
    }
}

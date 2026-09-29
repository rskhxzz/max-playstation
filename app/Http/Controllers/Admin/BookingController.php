<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthUser;
use App\Models\Booking;
use App\Models\DeliveryRate;
use App\Models\RentalPackage;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BookingController extends Controller
{
    private function buildQuery(Request $request)
    {
        $query = Booking::withoutGlobalScope('not_deleted')
            ->with([
                'customer',
                'rentalPackage',
                'playstationUnit',
                'driver',
            ])
            ->where('is_deleted', false);

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where(
                    'booking_code',
                    'ilike',
                    "%{$search}%"
                )->orWhereHas(
                    'customer',
                    function ($customerQuery) use ($search) {
                        $customerQuery
                            ->where(
                                'full_name',
                                'ilike',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'phone_number',
                                'ilike',
                                "%{$search}%"
                            );
                    }
                );
            });
        }

        if ($request->filled('booking_status')) {
            $query->where(
                'booking_status',
                $request->booking_status
            );
        }

        if ($request->filled('payment_status')) {
            $query->where(
                'payment_status',
                $request->payment_status
            );
        }

        if ($request->filled('package_id')) {
            $query->where(
                'rental_package_id',
                $request->package_id
            );
        }

        if ($request->filled('driver_id')) {
            $query->where(
                'driver_id',
                $request->driver_id
            );
        }

        if ($request->filled('date_from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->date_from
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->date_to
            );
        }

        return $query;
    }

    private function getDrivers()
    {
        return AuthUser::query()
            ->with('role')
            ->whereHas(
                'role',
                function ($query) {
                    $query->whereRaw(
                        'LOWER(name) LIKE ?',
                        ['%driver%']
                    );
                }
            )
            ->orderBy('name')
            ->get();
    }

    public function index(Request $request)
    {
        $bookings = $this->buildQuery($request)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $packages = RentalPackage::where(
            'is_active',
            true
        )
            ->orderBy('name')
            ->get();

        $drivers = $this->getDrivers();

        return view(
            'admin.bookings.index',
            compact(
                'bookings',
                'packages',
                'drivers'
            )
        );
    }

    public function export(
        Request $request,
        ExportService $exportService
    ) {
        $bookings = $this->buildQuery($request)
            ->latest()
            ->get();

        $filters = $request->only([
            'search',
            'booking_status',
            'payment_status',
            'package_id',
            'driver_id',
            'date_from',
            'date_to',
        ]);

        if (!empty($filters['package_id'])) {
            $package = RentalPackage::find(
                $filters['package_id']
            );

            $filters['package_id'] =
                $package?->name
                ?? $filters['package_id'];
        }

        if (!empty($filters['driver_id'])) {
            $driver = AuthUser::find(
                $filters['driver_id']
            );

            $filters['driver_id'] =
                $driver?->name
                ?? $filters['driver_id'];
        }

        return $exportService->exportBookings(
            $bookings,
            $filters
        );
    }

    public function show(string $id)
    {
        $booking = Booking::withoutGlobalScope(
            'not_deleted'
        )
            ->with([
                'customer',
                'rentalPackage',
                'playstationUnit',
                'payments',
                'termsCondition',
                'driver.role',
            ])
            ->where('is_deleted', false)
            ->findOrFail($id);

        $drivers = $this->getDrivers();

        return view(
            'admin.bookings.show',
            compact(
                'booking',
                'drivers'
            )
        );
    }

    public function assignDriver(
        Request $request,
        string $id
    ) {
        $data = $request->validate([
            'driver_id' => [
                'required',
                'string',
                'exists:auth_user,id',
            ],
            'vehicle_type' => [
                'required',
                'in:personal,company',
            ],
        ]);

        try {
            DB::transaction(
                function () use (
                    $data,
                    $id
                ) {
                    $booking =
                        Booking::withoutGlobalScope(
                            'not_deleted'
                        )
                        ->where(
                            'id',
                            $id
                        )
                        ->where(
                            'is_deleted',
                            false
                        )
                        ->where(
                            'booking_status',
                            'delivered'
                        )
                        ->lockForUpdate()
                        ->first();

                    if (!$booking) {
                        throw new \RuntimeException(
                            'Driver hanya dapat ditugaskan atau diganti pada pesanan yang siap diantar.'
                        );
                    }

                    $driver = AuthUser::query()
                        ->where(
                            'id',
                            $data['driver_id']
                        )
                        ->whereHas(
                            'role',
                            function ($query) {
                                $query->whereRaw(
                                    'LOWER(name) LIKE ?',
                                    ['%driver%']
                                );
                            }
                        )
                        ->first();

                    if (!$driver) {
                        throw new \RuntimeException(
                            'Akun yang dipilih bukan driver aktif yang valid.'
                        );
                    }

                    $driverFee =
                        (float) $booking->driver_fee;

                    $companyFuelDeduction =
                        (float) $booking->fuel_deduction;

                    if ($driverFee <= 0) {
                        $rate =
                            $this->findRateByDistance(
                                (float) $booking->distance_km
                            );

                        if (!$rate) {
                            throw new \RuntimeException(
                                'Tarif driver untuk jarak pesanan tidak ditemukan.'
                            );
                        }

                        $driverFee =
                            (float) $rate->driver_fee;

                        $companyFuelDeduction =
                            (float) $rate->company_fuel_deduction;
                    }

                    $fuelDeduction =
                        $data['vehicle_type'] === 'company'
                        ? $companyFuelDeduction
                        : 0.0;

                    $booking->driver_id =
                        $driver->id;

                    $booking->vehicle_type =
                        $data['vehicle_type'];

                    $booking->driver_fee =
                        $driverFee;

                    $booking->fuel_deduction =
                        $fuelDeduction;

                    $booking->driver_income =
                        max(
                            0,
                            $driverFee - $fuelDeduction
                        );

                    $booking->driver_assigned_at =
                        now();

                    $booking->delivery_started_at =
                        $booking->delivery_started_at
                        ?? now();

                    $booking->updated_by =
                        auth('admin')->id();

                    $booking->save();
                }
            );

            return redirect()
                ->route(
                    'admin.bookings.show',
                    $id
                )
                ->with(
                    'success',
                    'Driver berhasil ditugaskan atau diperbarui.'
                );
        } catch (
            \RuntimeException $e
        ) {
            return back()
                ->withErrors([
                    'error' => $e->getMessage(),
                ])
                ->withInput();
        } catch (
            \Throwable $e
        ) {
            Log::error(
                'Assign driver error',
                [
                    'booking_id' =>
                    $id,

                    'message' =>
                    $e->getMessage(),
                ]
            );

            return back()
                ->withErrors([
                    'error' =>
                    'Gagal memperbarui driver pesanan.',
                ])
                ->withInput();
        }
    }

    public function photo(string $id)
    {
        $booking =
            Booking::withoutGlobalScope(
                'not_deleted'
            )
            ->where(
                'id',
                $id
            )
            ->where(
                'is_deleted',
                false
            )
            ->firstOrFail();

        abort_unless(
            $booking->delivery_photo_path,
            404
        );

        $disk =
            Storage::disk('local');

        abort_unless(
            $disk->exists(
                $booking->delivery_photo_path
            ),
            404
        );

        return response()->file(
            $disk->path(
                $booking->delivery_photo_path
            ),
            [
                'Cache-Control' =>
                'private, max-age=3600',
            ]
        );
    }

    public function cancel(
        Request $request,
        string $id
    ) {
        $booking =
            Booking::findOrFail($id);

        if (
            !in_array(
                $booking->booking_status,
                [
                    'pending_payment',
                    'delivered',
                ],
                true
            )
        ) {
            return back()
                ->withErrors([
                    'error' =>
                    'Booking tidak dapat dibatalkan pada status ini.',
                ]);
        }

        $booking->booking_status =
            'canceled';

        $booking->cancellation_reason =
            $request->reason;

        $booking->updated_by =
            auth('admin')->id();

        $booking->save();

        return redirect()
            ->route(
                'admin.bookings.index'
            )
            ->with(
                'success',
                'Booking berhasil dibatalkan.'
            );
    }

    private function findRateByDistance(
        float $distanceKm
    ): ?DeliveryRate {
        $rates =
            DeliveryRate::query()
            ->where(
                'is_active',
                true
            )
            ->orderBy(
                'minimum_distance_km'
            )
            ->orderBy(
                'maximum_distance_km'
            )
            ->get();

        foreach ($rates as $rate) {
            $minimum =
                (float) $rate->minimum_distance_km;

            $maximum =
                (float) $rate->maximum_distance_km;

            if (
                (
                    $minimum == 0
                    && $distanceKm <= $maximum
                )
                || (
                    $distanceKm > $minimum
                    && $distanceKm <= $maximum
                )
            ) {
                return $rate;
            }
        }

        return null;
    }
}

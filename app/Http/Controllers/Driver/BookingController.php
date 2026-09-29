<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\GoogleMapsService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BookingController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private GoogleMapsService $mapsService
    ) {}

    public function show(string $id)
    {
        $driverId =
            (string) auth('driver')->id();

        $booking = Booking::with([
            'customer',
            'rentalPackage',
            'playstationUnit',
            'driver',
            'payments',
        ])
            ->where(function ($query) use (
                $driverId
            ) {
                $query
                    ->where(function ($query) {
                        $query
                            ->where(
                                'booking_status',
                                'delivered'
                            )
                            ->whereNull(
                                'driver_id'
                            );
                    })
                    ->orWhere(function (
                        $query
                    ) use ($driverId) {
                        $query
                            ->where(
                                'driver_id',
                                $driverId
                            )
                            ->whereIn(
                                'booking_status',
                                [
                                    'delivered',
                                    'on_delivery',
                                    'arrived',
                                    'completed',
                                ]
                            );
                    });
            })
            ->findOrFail($id);

        $snapToken = null;

        if (
            $booking->payment_status ===
            'partial'
            && $booking->booking_status ===
            'delivered'
            && $booking->driver_id ===
            $driverId
        ) {
            $pending =
                $booking->payments()
                ->where(
                    'payment_type',
                    'remaining'
                )
                ->where(
                    'status',
                    'pending'
                )
                ->first();

            $snapToken =
                $pending?->qr_string;
        }

        $clientKey = config(
            'services.midtrans.client_key'
        );

        return view(
            'driver.bookings.show',
            compact(
                'booking',
                'snapToken',
                'clientKey'
            )
        );
    }

    public function accept(
        Request $request,
        string $id
    ) {
        $data = $request->validate([
            'vehicle_type' => [
                'required',
                'in:personal,company',
            ],
        ]);

        $driverId =
            (string) auth('driver')->id();

        try {
            DB::transaction(
                function () use (
                    $id,
                    $driverId,
                    $data
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
                            'Pesanan sudah tidak tersedia atau statusnya sudah berubah.'
                        );
                    }

                    if (
                        $booking->driver_id !==
                        null
                    ) {
                        if (
                            (string)
                            $booking->driver_id ===
                            $driverId
                        ) {
                            throw new \RuntimeException(
                                'Pesanan sudah ditugaskan kepada Anda.'
                            );
                        }

                        throw new \RuntimeException(
                            'Pesanan sudah diambil oleh driver lain.'
                        );
                    }

                    $driverFee =
                        (float)
                        $booking->driver_fee;

                    $companyFuelDeduction =
                        (float)
                        $booking->fuel_deduction;

                    if (
                        $driverFee <= 0
                    ) {
                        $rate =
                            $this->mapsService
                            ->getDeliveryRate(
                                (float)
                                $booking->distance_km
                            );

                        if (!$rate) {
                            throw new \RuntimeException(
                                'Tarif driver untuk jarak pesanan tidak ditemukan.'
                            );
                        }

                        $driverFee =
                            (float)
                            $rate->driver_fee;

                        $companyFuelDeduction =
                            (float)
                            $rate->company_fuel_deduction;
                    }

                    $fuelDeduction =
                        $data['vehicle_type'] ===
                        'company'
                        ? $companyFuelDeduction
                        : 0.0;

                    $driverIncome =
                        max(
                            0,
                            $driverFee
                                - $fuelDeduction
                        );

                    $booking->driver_id =
                        $driverId;

                    $booking->vehicle_type =
                        $data['vehicle_type'];

                    $booking->driver_fee =
                        $driverFee;

                    $booking->fuel_deduction =
                        $fuelDeduction;

                    $booking->driver_income =
                        $driverIncome;

                    $booking->driver_assigned_at =
                        now();

                    $booking->delivery_started_at =
                        now();

                    $booking->booking_status =
                        'on_delivery';

                    $booking->updated_by =
                        $driverId;

                    $booking->save();
                }
            );

            return redirect()
                ->route(
                    'driver.bookings.show',
                    $id
                )
                ->with(
                    'success',
                    'Pesanan berhasil diterima. Anda bertanggung jawab untuk pengantaran dan pengambilan unit PlayStation.'
                );
        } catch (
            \RuntimeException $e
        ) {
            return back()->withErrors([
                'error' =>
                $e->getMessage(),
            ]);
        } catch (
            \Throwable $e
        ) {
            Log::error(
                'Driver accept booking error',
                [
                    'booking_id' =>
                    $id,

                    'driver_id' =>
                    $driverId,

                    'message' =>
                    $e->getMessage(),
                ]
            );

            return back()->withErrors([
                'error' =>
                'Terjadi kesalahan sistem saat menerima pesanan.',
            ]);
        }
    }

    public function createRemainingPayment(
        string $id
    ) {
        $booking =
            Booking::where(
                'driver_id',
                auth('driver')->id()
            )
            ->where(
                'booking_status',
                'delivered'
            )
            ->where(
                'payment_status',
                'partial'
            )
            ->findOrFail($id);

        try {
            $payment =
                $this->paymentService
                ->createRemainingPayment(
                    $booking,
                    auth('driver')->id()
                );

            return response()->json([
                'success' => true,
                'snap_token' =>
                $payment->qr_string,
                'message' =>
                'QRIS berhasil dibuat.',
            ]);
        } catch (
            \Throwable $e
        ) {
            Log::error(
                'Create remaining payment error',
                [
                    'booking_id' =>
                    $id,

                    'message' =>
                    $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' =>
                $e->getMessage(),
            ], 422);
        }
    }

    public function settleRemainingCash(
        string $id
    ) {
        $booking =
            Booking::where(
                'driver_id',
                auth('driver')->id()
            )
            ->where(
                'booking_status',
                'delivered'
            )
            ->where(
                'payment_status',
                'partial'
            )
            ->findOrFail($id);

        try {
            $this->paymentService
                ->settleRemainingByCash(
                    $booking,
                    auth('driver')->id()
                );

            return response()->json([
                'success' => true,
                'message' =>
                'Pelunasan tunai berhasil dicatat.',
            ]);
        } catch (
            \RuntimeException $e
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                $e->getMessage(),
            ], 422);
        } catch (
            \Throwable $e
        ) {
            Log::error(
                'Settle remaining cash error',
                [
                    'booking_id' =>
                    $id,

                    'message' =>
                    $e->getMessage(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' =>
                'Terjadi kesalahan sistem.',
            ], 500);
        }
    }

    public function completeDelivery(
        Request $request,
        string $id
    ) {
        $request->validate([
            'delivery_photo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ], [
            'delivery_photo.required' =>
            'Foto customer dan unit PlayStation wajib diunggah.',

            'delivery_photo.image' =>
            'File dokumentasi harus berupa gambar.',

            'delivery_photo.mimes' =>
            'Format foto yang diperbolehkan: JPG, JPEG, PNG, atau WEBP.',

            'delivery_photo.max' =>
            'Ukuran foto maksimal 5 MB.',
        ]);

        $driverId =
            (string) auth('driver')->id();

        $file =
            $request->file(
                'delivery_photo'
            );

        $storedPath = null;
        $existingPath = null;

        try {
            $booking =
                Booking::withoutGlobalScope(
                    'not_deleted'
                )
                ->where(
                    'id',
                    $id
                )
                ->where(
                    'driver_id',
                    $driverId
                )
                ->where(
                    'is_deleted',
                    false
                )
                ->where(
                    'booking_status',
                    'on_delivery'
                )
                ->firstOrFail();

            if (
                $booking->payment_status !==
                'paid'
            ) {
                return back()
                    ->withErrors([
                        'error' =>
                        'Pelunasan customer belum selesai.',
                    ])
                    ->withInput();
            }

            DB::transaction(
                function () use (
                    $booking,
                    $file,
                    $driverId,
                    &$storedPath,
                    &$existingPath
                ) {
                    $lockedBooking =
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
                        (string)
                        $lockedBooking->driver_id !==
                        $driverId
                        || $lockedBooking->booking_status !==
                        'on_delivery'
                        || $lockedBooking->is_deleted
                    ) {
                        throw new \RuntimeException(
                            'Pesanan tidak dapat diselesaikan pada kondisi saat ini.'
                        );
                    }

                    if (
                        $lockedBooking->payment_status !==
                        'paid'
                    ) {
                        throw new \RuntimeException(
                            'Pelunasan customer belum selesai.'
                        );
                    }

                    $existingPath =
                        $lockedBooking
                        ->delivery_photo_path;

                    $storedPath =
                        $file->store(
                            'delivery-proofs/'
                                . now()->format('Y/m'),
                            'local'
                        );

                    $lockedBooking
                        ->delivery_photo_path =
                        $storedPath;

                    $lockedBooking
                        ->delivery_photo_taken_at =
                        now();

                    $lockedBooking->arrived_at =
                        $lockedBooking->arrived_at
                        ?? now();

                    $lockedBooking->delivered_at =
                        now();

                    $lockedBooking->booking_status =
                        'arrived';

                    $lockedBooking->updated_by =
                        $driverId;

                    $lockedBooking->save();
                }
            );

            if (
                $existingPath
                && Storage::disk('local')
                ->exists($existingPath)
            ) {
                try {
                    Storage::disk('local')
                        ->delete($existingPath);
                } catch (
                    \Throwable $e
                ) {
                    Log::warning(
                        'Unable to delete previous delivery photo',
                        [
                            'booking_id' =>
                            $id,

                            'path' =>
                            $existingPath,

                            'message' =>
                            $e->getMessage(),
                        ]
                    );
                }
            }

            return redirect()
                ->route(
                    'driver.dashboard'
                )
                ->with(
                    'success',
                    'Pengantaran berhasil diselesaikan dan dokumentasi tersimpan.'
                );
        } catch (
            \Throwable $e
        ) {
            if (
                $storedPath
                && Storage::disk('local')
                ->exists($storedPath)
            ) {
                Storage::disk('local')
                    ->delete($storedPath);
            }

            if (
                $e instanceof
                \RuntimeException
            ) {
                return back()
                    ->withErrors([
                        'error' =>
                        $e->getMessage(),
                    ])
                    ->withInput();
            }

            Log::error(
                'Complete delivery error',
                [
                    'booking_id' =>
                    $id,

                    'driver_id' =>
                    $driverId,

                    'message' =>
                    $e->getMessage(),
                ]
            );

            return back()
                ->withErrors([
                    'error' =>
                    'Gagal menyelesaikan pengantaran.',
                ])
                ->withInput();
        }
    }

    public function markArrived(
        Request $request,
        string $id
    ) {
        $driverId =
            (string) auth('driver')->id();

        $booking =
            Booking::where(
                'driver_id',
                $driverId
            )
            ->where(
                'booking_status',
                'delivered'
            )
            ->findOrFail($id);

        if (
            $booking->payment_status !==
            'paid'
        ) {
            return back()->withErrors([
                'error' =>
                'Pelunasan belum selesai. Selesaikan pembayaran terlebih dahulu.',
            ]);
        }

        if (
            !$booking->delivery_photo_path
        ) {
            return back()->withErrors([
                'error' =>
                'Foto customer dan unit PlayStation wajib diunggah sebelum menyelesaikan pengantaran.',
            ]);
        }

        $booking->booking_status =
            'arrived';

        $booking->arrived_at =
            $booking->arrived_at
            ?? now();

        $booking->delivered_at =
            $booking->delivered_at
            ?? now();

        $booking->updated_by =
            $driverId;

        $booking->save();

        return redirect()
            ->route(
                'driver.dashboard'
            )
            ->with(
                'success',
                'Pesanan ditandai sudah sampai.'
            );
    }

    public function completePickup(
        string $id
    ) {
        $driverId =
            (string) auth('driver')->id();

        try {
            DB::transaction(
                function () use (
                    $id,
                    $driverId
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
                            'driver_id',
                            $driverId
                        )
                        ->where(
                            'is_deleted',
                            false
                        )
                        ->where(
                            'booking_status',
                            'arrived'
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                    if (
                        !$booking->delivery_photo_path
                    ) {
                        throw new \RuntimeException(
                            'Dokumentasi pengantaran belum tersedia.'
                        );
                    }

                    if (
                        !$booking->rental_end_at
                        || now()->lt(
                            $booking->rental_end_at
                        )
                    ) {
                        throw new \RuntimeException(
                            'Waktu sewa belum selesai. Pengambilan unit belum dapat dikonfirmasi.'
                        );
                    }

                    $booking->pickup_started_at =
                        $booking->pickup_started_at
                        ?? now();

                    $booking->picked_up_at =
                        now();

                    $booking->booking_status =
                        'completed';

                    $booking->updated_by =
                        $driverId;

                    $booking->save();
                }
            );

            return redirect()
                ->route(
                    'driver.dashboard'
                )
                ->with(
                    'success',
                    'Pengambilan unit berhasil dikonfirmasi. Pesanan selesai.'
                );
        } catch (
            \RuntimeException $e
        ) {
            return back()->withErrors([
                'error' =>
                $e->getMessage(),
            ]);
        } catch (
            \Throwable $e
        ) {
            Log::error(
                'Complete pickup error',
                [
                    'booking_id' =>
                    $id,

                    'driver_id' =>
                    $driverId,

                    'message' =>
                    $e->getMessage(),
                ]
            );

            return back()->withErrors([
                'error' =>
                'Gagal menyelesaikan pengambilan unit.',
            ]);
        }
    }

    public function photo(string $id)
    {
        $booking =
            Booking::where(
                'driver_id',
                auth('driver')->id()
            )
            ->findOrFail($id);

        if (
            !$booking->delivery_photo_path
        ) {
            abort(404);
        }

        $disk =
            Storage::disk('local');

        if (
            !$disk->exists(
                $booking->delivery_photo_path
            )
        ) {
            abort(404);
        }

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
}

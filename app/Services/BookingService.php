<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\PlaystationUnit;
use App\Models\RentalPackage;
use App\Models\TermsCondition;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    public function __construct(
        private GoogleMapsService $mapsService,
        private PaymentService $paymentService
    ) {}

    public function createBooking(
        array $data
    ): array {
        $token = $data['booking_token'];

        $paymentCode = 'PAY-'
            . strtoupper(Str::random(10))
            . '-'
            . time();

        DB::table('t_booking_idempotency')->insertOrIgnore([
            'token' => $token,
            'payment_code' => $paymentCode,
            'booking_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::transaction(
            function () use ($data, $token) {
                $request = DB::table(
                    't_booking_idempotency'
                )
                    ->where(
                        'token',
                        $token
                    )
                    ->lockForUpdate()
                    ->first();

                if (!$request) {
                    throw new \RuntimeException(
                        'Gagal memproses permintaan pemesanan.'
                    );
                }

                if ($request->booking_id) {
                    $booking = Booking::withoutGlobalScope(
                        'not_deleted'
                    )
                        ->where(
                            'id',
                            $request->booking_id
                        )
                        ->firstOrFail();

                    $payment = $booking->payments()
                        ->where(
                            'payment_type',
                            'initial'
                        )
                        ->latest()
                        ->first();

                    if (!$payment) {
                        throw new \RuntimeException(
                            'Data pembayaran awal untuk pesanan tidak ditemukan.'
                        );
                    }

                    return [
                        'booking' => $booking,
                        'payment' => $payment,
                        'customer' => $booking->customer,
                    ];
                }

                $result = $this->createBookingInternal(
                    $data
                );

                DB::table('t_booking_idempotency')
                    ->where(
                        'token',
                        $token
                    )
                    ->update([
                        'booking_id' =>
                        $result['booking']->id,
                        'updated_at' => now(),
                    ]);

                return $result;
            }
        );
    }

    private function createBookingInternal(
        array $data
    ): array {
        $phone = $this->normalizePhone(
            $data['phone_number']
        );

        $customer = Customer::withoutGlobalScope(
            'not_deleted'
        )
            ->where(
                'phone_number',
                $phone
            )
            ->where(
                'is_deleted',
                false
            )
            ->first();

        if (!$customer) {
            $customer = Customer::create([
                'full_name' =>
                trim($data['full_name']),

                'phone_number' =>
                $phone,
            ]);
        } else {
            $customer->full_name =
                trim($data['full_name']);

            $customer->save();
        }

        $package = RentalPackage::where(
            'id',
            $data['rental_package_id']
        )
            ->where(
                'is_active',
                true
            )
            ->firstOrFail();

        $startAt = Carbon::parse(
            $data['rental_date']
                . ' '
                . $data['rental_time']
        )->timezone(
            config(
                'app.timezone',
                'Asia/Jakarta'
            )
        );

        if ($startAt->isPast()) {
            throw new \RuntimeException(
                'Waktu penyewaan tidak boleh di masa lampau.'
            );
        }

        $startTime = $startAt->format(
            'H:i'
        );

        if (
            $package->isTimeBlocked(
                $startTime
            )
        ) {
            $blockedStart = substr(
                (string) $package->blocked_start_time,
                0,
                5
            );

            $blockedEnd =
                $package->blocked_end_time === '00:00:00'
                ? '24:00'
                : substr(
                    (string) $package->blocked_end_time,
                    0,
                    5
                );

            throw new \RuntimeException(
                "Paket {$package->name} tidak dapat dimulai antara pukul {$blockedStart} – {$blockedEnd}."
            );
        }

        $endAt = $startAt->copy()
            ->addHours(
                (int) $package->duration_hours
            );

        $deliveryResult =
            $this->mapsService->calculateRoute(
                (float) $data['latitude'],
                (float) $data['longitude']
            );

        if (
            !$deliveryResult['success']
            || !$deliveryResult['is_serviceable']
        ) {
            throw new \RuntimeException(
                $deliveryResult['message']
            );
        }

        $distanceKm =
            (float) $deliveryResult['distance_km'];

        $deliveryFee =
            (float) $deliveryResult['delivery_fee'];

        $driverFee =
            (float) $deliveryResult['driver_fee'];

        $companyFuelDeduction =
            (float) $deliveryResult['company_fuel_deduction'];

        $unit = $this->findAvailableUnit(
            $startAt,
            $endAt
        );

        if (!$unit) {
            throw new \RuntimeException(
                'Maaf, seluruh unit PlayStation sudah dipesan pada jadwal tersebut. Silakan pilih waktu lain.'
            );
        }

        $terms = TermsCondition::where(
            'is_active',
            true
        )
            ->orderByDesc('version')
            ->first();

        $setting =
            BusinessSetting::active();

        $dpAmount = (float) (
            $setting?->down_payment_amount
            ?? 50000
        );

        $packagePrice =
            (float) $package->price;

        $discountAmount = 0.0;

        $totalAmount =
            $packagePrice
            + $deliveryFee
            - $discountAmount;

        $paymentOption =
            $data['payment_option']
            ?? 'full';

        if (
            $paymentOption === 'deposit'
        ) {
            if (
                $dpAmount >=
                $totalAmount
            ) {
                throw new \RuntimeException(
                    'Total tagihan terlalu kecil untuk opsi DP.'
                );
            }

            $initialPayment =
                $dpAmount;

            $remainingAmount =
                $totalAmount
                - $dpAmount;
        } else {
            $initialPayment =
                $totalAmount;

            $remainingAmount = 0.0;
        }

        $booking = Booking::create([
            'booking_code' =>
            $this->generateBookingCode(),

            'customer_id' =>
            $customer->id,

            'rental_package_id' =>
            $package->id,

            'playstation_unit_id' =>
            $unit->id,

            'terms_condition_id' =>
            $terms?->id,

            'rental_start_at' =>
            $startAt,

            'rental_end_at' =>
            $endAt,

            'delivery_address' =>
            $data['delivery_address'],

            'google_place_id' =>
            $data['google_place_id']
                ?? null,

            'latitude' =>
            $data['latitude'],

            'longitude' =>
            $data['longitude'],

            'distance_km' =>
            $distanceKm,

            'package_price' =>
            $packagePrice,

            'delivery_fee' =>
            $deliveryFee,

            'driver_fee' =>
            $driverFee,

            'fuel_deduction' =>
            $companyFuelDeduction,

            'driver_income' =>
            0,

            'discount_amount' =>
            $discountAmount,

            'total_amount' =>
            $totalAmount,

            'payment_option' =>
            $paymentOption,

            'initial_payment_amount' =>
            $initialPayment,

            'total_paid' =>
            0,

            'remaining_amount' =>
            $remainingAmount,

            'payment_status' =>
            'unpaid',

            'booking_status' =>
            'pending_payment',

            'terms_accepted' =>
            true,

            'terms_accepted_at' =>
            now(),

            'customer_notes' =>
            $data['customer_notes']
                ?? null,
        ]);

        $payment =
            $this->paymentService
            ->createInitialPayment(
                $booking
            );

        return [
            'booking' => $booking,
            'payment' => $payment,
            'customer' => $customer,
        ];
    }

    public function findAvailableUnit(
        Carbon $startAt,
        Carbon $endAt
    ): ?PlaystationUnit {
        $units = PlaystationUnit::where(
            'is_active',
            true
        )
            ->whereNotIn(
                'status',
                [
                    'maintenance',
                    'inactive',
                ]
            )
            ->lockForUpdate()
            ->get();

        $ignoredStatuses = [
            'canceled',
            'expired',
            'delivery_failed',
        ];

        foreach ($units as $unit) {
            $conflict = Booking::where(
                'playstation_unit_id',
                $unit->id
            )
                ->whereNotIn(
                    'booking_status',
                    $ignoredStatuses
                )
                ->where(
                    'rental_start_at',
                    '<',
                    $endAt
                )
                ->where(
                    'rental_end_at',
                    '>',
                    $startAt
                )
                ->exists();

            if (!$conflict) {
                return $unit;
            }
        }

        return null;
    }

    public function normalizePhone(
        string $phone
    ): string {
        $phone = preg_replace(
            '/\D/',
            '',
            $phone
        ) ?? '';

        if (
            str_starts_with(
                $phone,
                '0'
            )
        ) {
            return '62'
                . substr(
                    $phone,
                    1
                );
        }

        if (
            str_starts_with(
                $phone,
                '62'
            )
        ) {
            return $phone;
        }

        return '62' . $phone;
    }

    private function generateBookingCode(): string
    {
        $date = now()->format(
            'Ymd'
        );

        do {
            $code =
                'MXB-'
                . $date
                . '-'
                . strtoupper(
                    Str::random(4)
                );
        } while (
            Booking::withoutGlobalScope(
                'not_deleted'
            )
            ->where(
                'booking_code',
                $code
            )
            ->exists()
        );

        return $code;
    }
}

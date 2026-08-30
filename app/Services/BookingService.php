<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\RentalPackage;
use App\Models\PlaystationUnit;
use App\Models\TermsCondition;
use App\Models\BusinessSetting;
use App\Models\Payment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BookingService
{
    public function __construct(
        private GoogleMapsService $mapsService,
        private PaymentService $paymentService
    ) {}

    /**
     * Create a new booking with payment.
     *
     * @throws \RuntimeException
     */
    public function createBooking(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // 1. Find or create customer
            $phone    = $this->normalizePhone($data['phone_number']);
            $customer = Customer::withoutGlobalScope('not_deleted')
                ->where('phone_number', $phone)
                ->where('is_deleted', false)
                ->first();

            if (!$customer) {
                $customer = Customer::create([
                    'full_name'    => trim($data['full_name']),
                    'phone_number' => $phone,
                ]);
            } else {
                $customer->full_name = trim($data['full_name']);
                $customer->save();
            }

            // 2. Load package
            $package = RentalPackage::where('id', $data['rental_package_id'])
                ->where('is_active', true)
                ->firstOrFail();

            // 3. Parse rental start
            $startAt = Carbon::parse($data['rental_date'] . ' ' . $data['rental_time'])
                ->timezone(config('app.timezone', 'Asia/Jakarta'));

            if ($startAt->isPast()) {
                throw new \RuntimeException('Waktu penyewaan tidak boleh di masa lampau.');
            }

            // 4. Validate package time restriction
            $startTimeStr = $startAt->format('H:i');
            if ($package->isTimeBlocked($startTimeStr)) {
                $blockedStart = substr($package->blocked_start_time, 0, 5);
                $blockedEnd   = $package->blocked_end_time === '00:00:00'
                    ? '24:00'
                    : substr($package->blocked_end_time, 0, 5);
                throw new \RuntimeException(
                    "Paket {$package->name} tidak dapat dimulai antara pukul {$blockedStart} – {$blockedEnd}."
                );
            }

            // 5. Calculate end time
            $endAt = (clone $startAt)->addHours($package->duration_hours);

            // 6. Recalculate delivery from backend
            $deliveryResult = $this->mapsService->calculateRoute(
                (float) $data['latitude'],
                (float) $data['longitude']
            );

            if (!$deliveryResult['is_serviceable']) {
                throw new \RuntimeException($deliveryResult['message']);
            }

            $deliveryFee = $deliveryResult['delivery_fee'];
            $distanceKm  = $deliveryResult['distance_km'];

            // 7. Check unit availability (with lock)
            $unit = $this->findAvailableUnit($startAt, $endAt);
            if (!$unit) {
                throw new \RuntimeException(
                    'Maaf, seluruh unit PlayStation sudah dipesan pada jadwal tersebut. Silakan pilih waktu lain.'
                );
            }

            // 8. Get active terms
            $terms = TermsCondition::where('is_active', true)
                ->orderByDesc('version')
                ->first();

            // 9. Calculate amounts
            $setting = BusinessSetting::active();
            $dpAmount = $setting?->down_payment_amount ?? 50000;

            $packagePrice    = (float) $package->price;
            $discountAmount  = 0;
            $totalAmount     = $packagePrice + $deliveryFee - $discountAmount;
            $paymentOption   = $data['payment_option'] ?? 'full';

            if ($paymentOption === 'deposit') {
                if ($dpAmount >= $totalAmount) {
                    throw new \RuntimeException('Total tagihan terlalu kecil untuk opsi DP.');
                }
                $initialPayment = $dpAmount;
                $remainingAmount = $totalAmount - $dpAmount;
            } else {
                $initialPayment  = $totalAmount;
                $remainingAmount = 0;
            }

            // 10. Generate booking code
            $bookingCode = $this->generateBookingCode();

            // 11. Create booking
            $booking = Booking::create([
                'booking_code'          => $bookingCode,
                'customer_id'           => $customer->id,
                'rental_package_id'     => $package->id,
                'playstation_unit_id'   => $unit->id,
                'terms_condition_id'    => $terms?->id,
                'rental_start_at'       => $startAt,
                'rental_end_at'         => $endAt,
                'delivery_address'      => $data['delivery_address'],
                'google_place_id'       => $data['google_place_id'] ?? null,
                'latitude'              => $data['latitude'],
                'longitude'             => $data['longitude'],
                'distance_km'           => $distanceKm,
                'package_price'         => $packagePrice,
                'delivery_fee'          => $deliveryFee,
                'discount_amount'       => $discountAmount,
                'total_amount'          => $totalAmount,
                'payment_option'        => $paymentOption,
                'initial_payment_amount' => $initialPayment,
                'total_paid'            => 0,
                'remaining_amount'      => $remainingAmount,
                'payment_status'        => 'unpaid',
                'booking_status'        => 'pending_payment',
                'terms_accepted'        => true,
                'terms_accepted_at'     => now(),
                'customer_notes'        => $data['customer_notes'] ?? null,
            ]);

            // 12. Create payment
            $payment = $this->paymentService->createInitialPayment($booking);

            return compact('booking', 'payment', 'customer');
        });
    }

    /**
     * Find available PlayStation unit for the given time range.
     */
    public function findAvailableUnit(Carbon $startAt, Carbon $endAt): ?PlaystationUnit
    {
        $units = PlaystationUnit::where('is_active', true)
            ->whereNotIn('status', ['maintenance', 'inactive'])
            ->lockForUpdate()
            ->get();

        $ignoredStatuses = ['canceled', 'expired', 'delivery_failed'];

        foreach ($units as $unit) {
            $conflict = Booking::where('playstation_unit_id', $unit->id)
                ->whereNotIn('booking_status', $ignoredStatuses)
                ->where('rental_start_at', '<', $endAt)
                ->where('rental_end_at', '>', $startAt)
                ->exists();

            if (!$conflict) {
                return $unit;
            }
        }

        return null;
    }

    /**
     * Normalize phone number to 62xxxxxxxxxx format.
     */
    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (str_starts_with($phone, '+62')) {
            $phone = '62' . substr($phone, 3);
        } elseif (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    private function generateBookingCode(): string
    {
        $date = now()->format('Ymd');
        do {
            $code = 'MXB-' . $date . '-' . strtoupper(Str::random(4));
        } while (Booking::withoutGlobalScope('not_deleted')->where('booking_code', $code)->exists());

        return $code;
    }
}

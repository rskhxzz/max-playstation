<?php

namespace App\Services;

use App\Models\BusinessSetting;
use App\Models\DeliveryRate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleMapsService
{
    private string $serverKey;

    public function __construct()
    {
        $this->serverKey = config(
            'services.google_maps.server_key',
            ''
        );
    }

    public function calculateRoute(
        float $customerLat,
        float $customerLng
    ): array {
        $setting = BusinessSetting::active();

        if (!$setting) {
            return [
                'success' => false,
                'message' =>
                'Pengaturan lokasi usaha belum dikonfigurasi.',
                'distance_meters' => 0,
                'distance_km' => 0,
                'duration_seconds' => 0,
                'delivery_fee' => 0,
                'driver_fee' => 0,
                'company_fuel_deduction' => 0,
                'is_serviceable' => false,
            ];
        }

        $originLat = (float) $setting->latitude;
        $originLng = (float) $setting->longitude;
        $maxKm = (float) (
            $setting->maximum_delivery_km ?? 999
        );

        if ($this->serverKey === '') {
            $distanceMeters = $this->haversineMeters(
                $originLat,
                $originLng,
                $customerLat,
                $customerLng
            );

            $distanceKm = round(
                $distanceMeters / 1000,
                2
            );

            return $this->buildResult(
                $distanceMeters,
                $distanceKm,
                0,
                $maxKm
            );
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Goog-Api-Key' => $this->serverKey,
                'X-Goog-FieldMask' =>
                'routes.duration,routes.distanceMeters',
            ])
                ->timeout(10)
                ->post(
                    'https://routes.googleapis.com/directions/v2:computeRoutes',
                    [
                        'origin' => [
                            'location' => [
                                'latLng' => [
                                    'latitude' => $originLat,
                                    'longitude' => $originLng,
                                ],
                            ],
                        ],
                        'destination' => [
                            'location' => [
                                'latLng' => [
                                    'latitude' => $customerLat,
                                    'longitude' => $customerLng,
                                ],
                            ],
                        ],
                        'travelMode' => 'DRIVE',
                    ]
                );

            if (
                $response->failed()
                || empty($response->json('routes'))
            ) {
                $distanceMeters = $this->haversineMeters(
                    $originLat,
                    $originLng,
                    $customerLat,
                    $customerLng
                );

                $distanceKm = round(
                    $distanceMeters / 1000,
                    2
                );

                return $this->buildResult(
                    $distanceMeters,
                    $distanceKm,
                    0,
                    $maxKm
                );
            }

            $route = $response->json('routes.0');

            $distanceMeters = (int) (
                $route['distanceMeters'] ?? 0
            );

            $durationSeconds = isset($route['duration'])
                ? (int) rtrim(
                    (string) $route['duration'],
                    's'
                )
                : 0;

            $distanceKm = round(
                $distanceMeters / 1000,
                2
            );

            return $this->buildResult(
                $distanceMeters,
                $distanceKm,
                $durationSeconds,
                $maxKm
            );
        } catch (\Throwable $e) {
            Log::error(
                'GoogleMapsService error',
                [
                    'message' => $e->getMessage(),
                    'latitude' => $customerLat,
                    'longitude' => $customerLng,
                ]
            );

            $distanceMeters = $this->haversineMeters(
                $originLat,
                $originLng,
                $customerLat,
                $customerLng
            );

            $distanceKm = round(
                $distanceMeters / 1000,
                2
            );

            return $this->buildResult(
                $distanceMeters,
                $distanceKm,
                0,
                $maxKm
            );
        }
    }

    public function getDeliveryRate(
        float $distanceKm
    ): ?DeliveryRate {
        $rates = DeliveryRate::query()
            ->where('is_active', true)
            ->orderBy('minimum_distance_km')
            ->orderBy('maximum_distance_km')
            ->get();

        foreach ($rates as $rate) {
            $minimum = (float) $rate->minimum_distance_km;
            $maximum = (float) $rate->maximum_distance_km;

            if (
                $minimum == 0
                && $distanceKm <= $maximum
            ) {
                return $rate;
            }

            if (
                $distanceKm > $minimum
                && $distanceKm <= $maximum
            ) {
                return $rate;
            }
        }

        return null;
    }

    public function calculateDeliveryFee(
        float $distanceKm
    ): float {
        $rate = $this->getDeliveryRate(
            $distanceKm
        );

        return $rate
            ? (float) $rate->delivery_fee
            : 0.0;
    }

    private function buildResult(
        float $distanceMeters,
        float $distanceKm,
        int $durationSeconds,
        float $maxKm
    ): array {
        if ($distanceKm > $maxKm) {
            return [
                'success' => true,
                'distance_meters' => (int) round(
                    $distanceMeters
                ),
                'distance_km' => $distanceKm,
                'duration_seconds' => $durationSeconds,
                'delivery_fee' => 0,
                'driver_fee' => 0,
                'company_fuel_deduction' => 0,
                'is_serviceable' => false,
                'message' =>
                'Maaf, lokasi Anda berada di luar jangkauan pengiriman Maxibox Playstation.',
            ];
        }

        $rate = $this->getDeliveryRate(
            $distanceKm
        );

        if (!$rate) {
            return [
                'success' => true,
                'distance_meters' => (int) round(
                    $distanceMeters
                ),
                'distance_km' => $distanceKm,
                'duration_seconds' => $durationSeconds,
                'delivery_fee' => 0,
                'driver_fee' => 0,
                'company_fuel_deduction' => 0,
                'is_serviceable' => false,
                'message' =>
                'Jarak tersebut belum memiliki tarif pengiriman yang tersedia.',
            ];
        }

        return [
            'success' => true,
            'distance_meters' => (int) round(
                $distanceMeters
            ),
            'distance_km' => $distanceKm,
            'duration_seconds' => $durationSeconds,
            'delivery_fee' => (float) $rate->delivery_fee,
            'driver_fee' => (float) $rate->driver_fee,
            'company_fuel_deduction' =>
            (float) $rate->company_fuel_deduction,
            'is_serviceable' => true,
            'message' => 'Lokasi dapat dijangkau.',
        ];
    }

    private function haversineMeters(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $earthRadius = 6_371_000;

        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);

        $deltaPhi = deg2rad($lat2 - $lat1);
        $deltaLambda = deg2rad($lng2 - $lng1);

        $a =
            sin($deltaPhi / 2) ** 2
            + cos($phi1)
            * cos($phi2)
            * sin($deltaLambda / 2) ** 2;

        return $earthRadius
            * 2
            * atan2(
                sqrt($a),
                sqrt(1 - $a)
            );
    }
}

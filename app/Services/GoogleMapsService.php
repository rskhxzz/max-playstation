<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\BusinessSetting;
use App\Models\DeliveryRate;

class GoogleMapsService
{
    private string $serverKey;

    public function __construct()
    {
        $this->serverKey = config('services.google_maps.server_key', '');
    }

    /**
     * Calculate driving distance from business to customer using Google Routes API.
     */
    public function calculateRoute(float $customerLat, float $customerLng): array
    {
        $setting = BusinessSetting::active();

        if (!$setting) {
            return [
                'success'          => false,
                'message'          => 'Pengaturan lokasi usaha belum dikonfigurasi.',
                'distance_meters'  => 0,
                'distance_km'      => 0,
                'duration_seconds' => 0,
                'delivery_fee'     => 0,
                'is_serviceable'   => false,
            ];
        }

        $originLat = $setting->latitude;
        $originLng = $setting->longitude;
        $maxKm     = $setting->maximum_delivery_km ?? 999;

        // Fallback: jika server key tidak diisi, hitung jarak garis lurus
        if (empty($this->serverKey)) {
            $distanceM  = $this->haversineMeters($originLat, $originLng, $customerLat, $customerLng);
            $distanceKm = round($distanceM / 1000, 2);
            return $this->buildResult($distanceM, $distanceKm, 0, $maxKm);
        }

        try {
            $response = Http::withHeaders([
                'Content-Type'     => 'application/json',
                'X-Goog-Api-Key'   => $this->serverKey,
                'X-Goog-FieldMask' => 'routes.duration,routes.distanceMeters',
            ])->timeout(10)->post('https://routes.googleapis.com/directions/v2:computeRoutes', [
                'origin' => [
                    'location' => ['latLng' => ['latitude' => $originLat, 'longitude' => $originLng]],
                ],
                'destination' => [
                    'location' => ['latLng' => ['latitude' => $customerLat, 'longitude' => $customerLng]],
                ],
                'travelMode' => 'DRIVE',
            ]);

            if ($response->failed() || empty($response->json('routes'))) {
                // Fallback ke haversine
                $distanceM  = $this->haversineMeters($originLat, $originLng, $customerLat, $customerLng);
                $distanceKm = round($distanceM / 1000, 2);
                return $this->buildResult($distanceM, $distanceKm, 0, $maxKm);
            }

            $route        = $response->json('routes.0');
            $distanceM    = $route['distanceMeters'] ?? 0;
            $durationSecs = isset($route['duration'])
                ? (int) rtrim($route['duration'], 's')
                : 0;
            $distanceKm   = round($distanceM / 1000, 2);

            return $this->buildResult($distanceM, $distanceKm, $durationSecs, $maxKm);
        } catch (\Throwable $e) {
            \Log::error('GoogleMapsService error: ' . $e->getMessage());
            $distanceM  = $this->haversineMeters($originLat, $originLng, $customerLat, $customerLng);
            $distanceKm = round($distanceM / 1000, 2);
            return $this->buildResult($distanceM, $distanceKm, 0, $maxKm);
        }
    }

    private function buildResult(float $distanceM, float $distanceKm, int $durationSecs, float $maxKm): array
    {
        if ($distanceKm > $maxKm) {
            return [
                'success'          => true,
                'distance_meters'  => (int) $distanceM,
                'distance_km'      => $distanceKm,
                'duration_seconds' => $durationSecs,
                'delivery_fee'     => 0,
                'is_serviceable'   => false,
                'message'          => 'Maaf, lokasi Anda berada di luar jangkauan pengiriman Maxibox Playstation.',
            ];
        }

        $deliveryFee = $this->calculateDeliveryFee($distanceKm);

        return [
            'success'          => true,
            'distance_meters'  => (int) $distanceM,
            'distance_km'      => $distanceKm,
            'duration_seconds' => $durationSecs,
            'delivery_fee'     => $deliveryFee,
            'is_serviceable'   => true,
            'message'          => 'Lokasi dapat dijangkau.',
        ];
    }

    /**
     * Get delivery fee based on distance zones.
     */
    public function calculateDeliveryFee(float $distanceKm): float
    {
        $rates = DeliveryRate::where('is_active', true)->get();

        foreach ($rates as $rate) {
            $min = (float) $rate->minimum_distance_km;
            $max = (float) $rate->maximum_distance_km;

            // Zona awal: min=0, max>0 → 0 <= jarak <= max
            if ($min == 0 && $distanceKm <= $max) {
                return (float) $rate->delivery_fee;
            }

            // Zona lain: min < jarak <= max
            if ($distanceKm > $min && $distanceKm <= $max) {
                return (float) $rate->delivery_fee;
            }
        }

        return 0;
    }

    private function haversineMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R = 6371000;
        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);
        $dphi = deg2rad($lat2 - $lat1);
        $dlam = deg2rad($lng2 - $lng1);

        $a = sin($dphi / 2) ** 2 + cos($phi1) * cos($phi2) * sin($dlam / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}

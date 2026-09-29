<?php

namespace App\Http\Controllers;

use App\Services\GoogleMapsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class DeliveryController extends Controller
{
    public function __construct(
        private GoogleMapsService $mapsService
    ) {}

    public function calculate(
        Request $request
    ) {
        $key =
            'delivery|'
            . $request->ip();

        if (
            RateLimiter::tooManyAttempts(
                $key,
                30
            )
        ) {
            return response()->json([
                'error' =>
                'Terlalu banyak permintaan.',
            ], 429);
        }

        RateLimiter::hit(
            $key,
            60
        );

        $data = $request->validate([
            'latitude' => [
                'required',
                'numeric',
                'between:-90,90',
            ],
            'longitude' => [
                'required',
                'numeric',
                'between:-180,180',
            ],
        ]);

        $result =
            $this->mapsService->calculateRoute(
                (float) $data['latitude'],
                (float) $data['longitude']
            );

        return response()->json([
            'success' =>
            $result['success'],

            'message' =>
            $result['message'],

            'distance_meters' =>
            $result['distance_meters'],

            'distance_km' =>
            $result['distance_km'],

            'duration_seconds' =>
            $result['duration_seconds'],

            'delivery_fee' =>
            $result['delivery_fee'],

            'is_serviceable' =>
            $result['is_serviceable'],
        ]);
    }
}

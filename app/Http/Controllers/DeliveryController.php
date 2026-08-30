<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleMapsService;
use Illuminate\Support\Facades\RateLimiter;

class DeliveryController extends Controller
{
    public function __construct(private GoogleMapsService $mapsService) {}

    /**
     * POST /api/calculate-delivery
     */
    public function calculate(Request $request)
    {
        $key = 'delivery|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 30)) {
            return response()->json(['error' => 'Terlalu banyak permintaan.'], 429);
        }
        RateLimiter::hit($key, 60);

        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $result = $this->mapsService->calculateRoute(
            (float) $request->latitude,
            (float) $request->longitude
        );

        return response()->json($result);
    }
}

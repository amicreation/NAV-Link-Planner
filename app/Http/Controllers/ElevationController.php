<?php

namespace App\Http\Controllers;

use App\Services\ElevationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ElevationController extends Controller
{
    public function __construct(
        protected ElevationService $elevationService
    ) {}

    /**
     * Look up exact ground elevation AMSL for given latitude and longitude.
     */
    public function lookup(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $lat = (float) $request->input('latitude');
        $lon = (float) $request->input('longitude');

        $elevation = $this->elevationService->getSinglePointElevation($lat, $lon);

        return response()->json([
            'status' => 'success',
            'latitude' => $lat,
            'longitude' => $lon,
            'ground_elevation' => round($elevation, 3),
        ]);
    }
}

<?php

namespace App\Services;

class GeodesicService
{
    // WGS84 Constants
    public const WGS84_A = 6378137.0; // Equatorial radius in meters
    public const WGS84_F = 1.0 / 298.257223563; // Flattening
    public const WGS84_B = 6356752.314245; // Polar radius in meters
    public const EARTH_K_FACTOR = 4.0 / 3.0; // Standard atmospheric refraction k-factor
    public const EFFECTIVE_EARTH_RADIUS = 6371000.0 * (4.0 / 3.0); // ~8494666.67 meters

    /**
     * Calculates Geodesic Distance between two WGS84 points in kilometers.
     * Uses Haversine / Great-Circle with double precision.
     */
    public function calculateDistanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $rLat1 = deg2rad($lat1);
        $rLat2 = deg2rad($lat2);

        $a = sin($dLat / 2.0) ** 2 + cos($rLat1) * cos($rLat2) * (sin($dLon / 2.0) ** 2);
        $c = 2.0 * atan2(sqrt($a), sqrt(1.0 - $a));

        $distanceMeters = self::WGS84_A * $c;
        return $distanceMeters / 1000.0;
    }

    /**
     * Calculates Initial Azimuth (Bearing) from Point 1 to Point 2 in degrees (0..360).
     */
    public function calculateAzimuth(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $rLat1 = deg2rad($lat1);
        $rLat2 = deg2rad($lat2);
        $dLon = deg2rad($lon2 - $lon1);

        $y = sin($dLon) * cos($rLat2);
        $x = cos($rLat1) * sin($rLat2) - sin($rLat1) * cos($rLat2) * cos($dLon);

        $bearing = rad2deg(atan2($y, $x));
        return fmod(($bearing + 360.0), 360.0);
    }

    /**
     * Calculates Mechanical Tilt (Elevation Angle) in degrees from Site A to Site B.
     * Takes antenna heights, ground elevation, and Earth Curvature into account.
     */
    public function calculateMechanicalTilt(
        float $heightA_m,
        float $elevA_m,
        float $heightB_m,
        float $elevB_m,
        float $distanceKm
    ): float {
        if ($distanceKm <= 0.0) {
            return 0.0;
        }

        $distanceM = $distanceKm * 1000.0;
        $totalH_A = $elevA_m + $heightA_m;
        $totalH_B = $elevB_m + $heightB_m;

        // Earth Curvature drop at distance D: h_drop = D^2 / (2 * k * R)
        $earthDrop_m = ($distanceM ** 2) / (2.0 * self::EFFECTIVE_EARTH_RADIUS);
        $deltaH = ($totalH_B - $earthDrop_m) - $totalH_A;

        // Tilt angle in degrees
        $angleRad = atan2($deltaH, $distanceM);
        return rad2deg($angleRad);
    }

    /**
     * Calculates Earth Curvature drop in meters at distance x (meters) along a path of total length D (meters).
     */
    public function calculateEarthCurvatureDrop(float $xMeters, float $totalDistanceMeters): float
    {
        if ($totalDistanceMeters <= 0.0) {
            return 0.0;
        }
        return ($xMeters * ($totalDistanceMeters - $xMeters)) / (2.0 * self::EFFECTIVE_EARTH_RADIUS);
    }
}

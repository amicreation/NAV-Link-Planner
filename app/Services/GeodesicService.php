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

    /**
     * Converts Decimal Degrees to DMS formatted string (e.g. 12° 58' 17.76" N).
     */
    public static function toDms(float $decimal, bool $isLongitude = false): string
    {
        $split = self::splitDms($decimal, $isLongitude);
        return $split['formatted'];
    }

    /**
     * Alias for toDms().
     */
    public static function decimalToDms(float $decimal, bool $isLongitude = false): string
    {
        return self::toDms($decimal, $isLongitude);
    }

    /**
     * Splits Decimal Degrees into structured DMS components.
     *
     * @return array{degrees: int, minutes: int, seconds: float, direction: string, formatted: string}
     */
    public static function splitDms(float $decimal, bool $isLongitude = false): array
    {
        $direction = $isLongitude ? ($decimal >= 0 ? 'E' : 'W') : ($decimal >= 0 ? 'N' : 'S');
        $abs = abs($decimal);
        $deg = (int) floor($abs);
        $minFloat = ($abs - $deg) * 60.0;
        $min = (int) floor($minFloat);
        $sec = round(($minFloat - $min) * 60.0, 2);

        if ($sec >= 60.0) {
            $sec = 0.0;
            $min++;
            if ($min >= 60) {
                $min = 0;
                $deg++;
            }
        }

        $formatted = sprintf('%d° %02d\' %05.2f" %s', $deg, $min, $sec, $direction);

        return [
            'degrees' => $deg,
            'minutes' => $min,
            'seconds' => $sec,
            'direction' => $direction,
            'formatted' => $formatted,
        ];
    }

    /**
     * Converts a DMS string, array, or numeric value to Decimal Degrees.
     */
    public static function dmsToDecimal(mixed $input, bool $isLongitude = false): ?float
    {
        if ($input === null || $input === '') {
            return null;
        }

        if (is_numeric($input)) {
            $num = (float) $input;
            $isOutOfRange = $isLongitude ? (abs($num) > 180.0) : (abs($num) > 90.0);
            if ($isOutOfRange) {
                $absStr = (string) abs($num);
                $numParts = explode('.', $absStr);
                $intPart = $numParts[0];
                $decPart = $numParts[1] ?? '';
                $len = strlen($intPart);
                if ($len >= 5 && $len <= 7) {
                    $degLen = $len - 4;
                    $deg = (float) substr($intPart, 0, $degLen);
                    $min = (float) substr($intPart, $degLen, 2);
                    $sec = (float) (substr($intPart, $degLen + 2) . ($decPart !== '' ? '.' . $decPart : ''));
                    if ($min < 60 && $sec < 60 && (($isLongitude && $deg <= 180) || (!$isLongitude && $deg <= 90))) {
                        $val = $deg + ($min / 60.0) + ($sec / 3600.0);
                        return $num < 0 ? -$val : $val;
                    }
                }
            }
            return $num;
        }

        if (is_array($input)) {
            $deg = (float) ($input['degrees'] ?? 0);
            $min = (float) ($input['minutes'] ?? 0);
            $sec = (float) ($input['seconds'] ?? 0);
            $dir = strtoupper(trim((string) ($input['direction'] ?? '')));

            $val = abs($deg) + ($min / 60.0) + ($sec / 3600.0);
            if ($dir === 'S' || $dir === 'W' || $deg < 0) {
                $val = -$val;
            }
            return $val;
        }

        $str = trim((string) $input);
        if (is_numeric($str)) {
            return (float) $str;
        }

        // Check for negative indicator or South / West
        $isNegative = false;
        if (preg_match('/[SWsw]/', $str) || str_starts_with($str, '-')) {
            $isNegative = true;
        }

        // Normalize delimiters (°, ', ", d, m, s, :, commas, etc.) to spaces
        $normalized = preg_replace('/[°\'"”’dmsDMSNSEWnsew:\,\-]+/', ' ', $str);
        $parts = preg_split('/\s+/', trim($normalized));

        if (count($parts) >= 1 && is_numeric($parts[0])) {
            $deg = (float) $parts[0];
            $min = isset($parts[1]) && is_numeric($parts[1]) ? (float) $parts[1] : 0.0;
            $sec = isset($parts[2]) && is_numeric($parts[2]) ? (float) $parts[2] : 0.0;

            $val = abs($deg) + ($min / 60.0) + ($sec / 3600.0);
            return $isNegative ? -$val : $val;
        }

        return null;
    }
}

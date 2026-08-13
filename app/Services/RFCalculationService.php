<?php

namespace App\Services;

class RFCalculationService
{
    /**
     * Calculates 1st Fresnel Zone radius at point x along path D (both in km).
     * Returns radius in meters.
     */
    public function calculateFresnelRadiusMeters(float $xKm, float $totalDistanceKm, float $frequencyGHz): float
    {
        if ($totalDistanceKm <= 0.0 || $frequencyGHz <= 0.0) {
            return 0.0;
        }

        $d1 = max(0.001, $xKm);
        $d2 = max(0.001, $totalDistanceKm - $xKm);

        return 17.32 * sqrt(($d1 * $d2) / ($frequencyGHz * $totalDistanceKm));
    }

    /**
     * Calculates Max 1st Fresnel Zone Radius (at midpoint) in meters.
     */
    public function calculateMaxFresnelRadius(float $totalDistanceKm, float $frequencyGHz): float
    {
        return $this->calculateFresnelRadiusMeters($totalDistanceKm / 2.0, $totalDistanceKm, $frequencyGHz);
    }

    /**
     * Determines recommended RF Channel Width based on required throughput and frequency.
     */
    public function recommendChannelWidth(float $frequencyGHz, float $requiredThroughputMbps): int
    {
        // Estimated spectral efficiency ~ 6 to 8 bps/Hz for modern PtP radios (256QAM / 1024QAM)
        if ($requiredThroughputMbps <= 100) {
            return 20; // 20 MHz
        } elseif ($requiredThroughputMbps <= 250) {
            return 40; // 40 MHz
        } elseif ($requiredThroughputMbps <= 600) {
            return 80; // 80 MHz
        } else {
            return 160; // 160 MHz or dual 80MHz channel
        }
    }

    /**
     * Evaluates RF Link Engineering Feasibility.
     */
    public function evaluateLink(
        float $distanceKm,
        float $frequencyGHz,
        float $minClearanceM,
        float $maxFresnelM,
        bool $losPass,
        bool $terrainPass
    ): array {
        $requiredClearance = 0.6 * $maxFresnelM;
        $isClearanceOk = ($minClearanceM >= $requiredClearance) && $losPass && $terrainPass;

        // Practical RF Distance limits per frequency band
        $maxRecommendedDistKm = match (true) {
            $frequencyGHz < 3.0 => 50.0,
            $frequencyGHz <= 6.0 => 35.0,
            $frequencyGHz <= 11.0 => 20.0,
            $frequencyGHz <= 24.0 => 10.0,
            $frequencyGHz <= 60.0 => 3.0,
            default => 10.0,
        };

        if ($isClearanceOk && $distanceKm <= $maxRecommendedDistKm) {
            $status = 'Suitable';
            $summary = "Link path has clear 60% Fresnel clearance and optimal distance for {$frequencyGHz} GHz.";
        } elseif ($isClearanceOk || ($distanceKm <= $maxRecommendedDistKm * 1.25 && $minClearanceM >= 0.4 * $maxFresnelM)) {
            $status = 'Marginal';
            $summary = "Link is technically feasible, but Fresnel clearance or distance ({$distanceKm} km) is near operational limits.";
        } else {
            $status = 'Not Recommended';
            $summary = "Obstructions or excessive distance ({$distanceKm} km) for {$frequencyGHz} GHz band. Tower height increase required.";
        }

        return [
            'status' => $status,
            'summary' => $summary,
            'max_practical_distance_km' => $maxRecommendedDistKm,
            'required_60_percent_fresnel_m' => $requiredClearance,
        ];
    }
}

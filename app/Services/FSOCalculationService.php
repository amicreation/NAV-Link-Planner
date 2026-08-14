<?php

namespace App\Services;

class FSOCalculationService
{
    /**
     * Standard Indian Cities Visibility Reference Data (in km)
     */
    public const CITY_VISIBILITY = [
        'Agartala, Tripura' => ['q1' => 1.2, 'q2' => 3.0, 'q3' => 4.0, 'q4' => 1.8, 'worst' => 1.2],
        'Ahmedabad, Gujarat' => ['q1' => 1.2, 'q2' => 4.0, 'q3' => 5.0, 'q4' => 2.5, 'worst' => 1.2],
        'Aizawl, Mizoram' => ['q1' => 2.0, 'q2' => 3.0, 'q3' => 4.0, 'q4' => 2.0, 'worst' => 2.0],
        'Amritsar, Punjab' => ['q1' => 0.3, 'q2' => 3.0, 'q3' => 5.0, 'q4' => 1.5, 'worst' => 0.3],
        'Bengaluru, Karnataka' => ['q1' => 2.0, 'q2' => 4.0, 'q3' => 4.0, 'q4' => 2.5, 'worst' => 2.0],
        'Bhopal, Madhya Pradesh' => ['q1' => 1.5, 'q2' => 4.0, 'q3' => 5.0, 'q4' => 2.5, 'worst' => 1.5],
        'Bhubaneswar, Odisha' => ['q1' => 2.0, 'q2' => 4.0, 'q3' => 4.0, 'q4' => 2.5, 'worst' => 2.0],
        'Chennai, Tamil Nadu' => ['q1' => 3.0, 'q2' => 4.0, 'q3' => 4.0, 'q4' => 2.5, 'worst' => 2.5],
        'Dehradun, Uttarakhand' => ['q1' => 1.0, 'q2' => 4.0, 'q3' => 5.0, 'q4' => 2.0, 'worst' => 1.0],
        'Gangtok, Sikkim' => ['q1' => 2.0, 'q2' => 3.0, 'q3' => 3.0, 'q4' => 2.0, 'worst' => 2.0],
        'Gurugram, Haryana' => ['q1' => 0.3, 'q2' => 3.0, 'q3' => 5.0, 'q4' => 1.5, 'worst' => 0.3],
        'Guwahati, Assam' => ['q1' => 1.0, 'q2' => 3.0, 'q3' => 4.0, 'q4' => 1.5, 'worst' => 1.0],
        'Hyderabad, Telangana' => ['q1' => 2.5, 'q2' => 4.0, 'q3' => 4.0, 'q4' => 2.5, 'worst' => 2.5],
        'Imphal, Manipur' => ['q1' => 1.2, 'q2' => 3.0, 'q3' => 4.0, 'q4' => 1.8, 'worst' => 1.2],
        'Itanagar, Arunachal Pradesh' => ['q1' => 1.5, 'q2' => 3.0, 'q3' => 4.0, 'q4' => 2.0, 'worst' => 1.5],
        'Jaipur, Rajasthan' => ['q1' => 0.8, 'q2' => 4.0, 'q3' => 6.0, 'q4' => 2.0, 'worst' => 0.8],
        'Jammu, Jammu & Kashmir' => ['q1' => 0.8, 'q2' => 4.0, 'q3' => 6.0, 'q4' => 2.0, 'worst' => 0.8],
        'Kochi, Kerala' => ['q1' => 3.0, 'q2' => 4.0, 'q3' => 4.0, 'q4' => 3.0, 'worst' => 3.0],
        'Kohima, Nagaland' => ['q1' => 1.8, 'q2' => 3.0, 'q3' => 4.0, 'q4' => 2.0, 'worst' => 1.8],
        'Kolkata, West Bengal' => ['q1' => 1.0, 'q2' => 3.0, 'q3' => 5.0, 'q4' => 1.5, 'worst' => 1.0],
        'Lucknow, Uttar Pradesh' => ['q1' => 0.4, 'q2' => 3.0, 'q3' => 5.0, 'q4' => 1.5, 'worst' => 0.4],
        'Nagpur, Maharashtra' => ['q1' => 1.8, 'q2' => 4.0, 'q3' => 5.0, 'q4' => 2.5, 'worst' => 1.8],
        'New Delhi, Delhi' => ['q1' => 0.2, 'q2' => 3.0, 'q3' => 5.0, 'q4' => 1.2, 'worst' => 0.2],
        'Panaji, Goa' => ['q1' => 3.0, 'q2' => 5.0, 'q3' => 4.0, 'q4' => 4.0, 'worst' => 3.0],
        'Patna, Bihar' => ['q1' => 0.5, 'q2' => 3.0, 'q3' => 5.0, 'q4' => 1.5, 'worst' => 0.5],
        'Raipur, Chhattisgarh' => ['q1' => 2.0, 'q2' => 4.0, 'q3' => 5.0, 'q4' => 2.5, 'worst' => 2.0],
        'Ranchi, Jharkhand' => ['q1' => 1.5, 'q2' => 4.0, 'q3' => 5.0, 'q4' => 2.0, 'worst' => 1.5],
        'Shillong, Meghalaya' => ['q1' => 1.5, 'q2' => 3.0, 'q3' => 3.0, 'q4' => 2.0, 'worst' => 1.5],
        'Shimla, Himachal Pradesh' => ['q1' => 2.0, 'q2' => 4.0, 'q3' => 4.0, 'q4' => 3.0, 'worst' => 2.0],
        'Visakhapatnam, Andhra Pradesh' => ['q1' => 3.0, 'q2' => 4.0, 'q3' => 4.0, 'q4' => 3.0, 'worst' => 3.0],
    ];

    /**
     * Calculates FSO Beam Diameter at Receiver (Site B) in meters.
     */
    public function calculateBeamDiameterMeters(float $distanceKm, float $divergenceMrad = 0.15, float $txApertureMeters = 0.05): float
    {
        $distanceMeters = $distanceKm * 1000.0;
        $divergenceRad = $divergenceMrad / 1000.0;
        return $txApertureMeters + ($distanceMeters * tan($divergenceRad));
    }

    /**
     * Calculates Geometric Attenuation (dB)
     */
    public function calculateGeometricLossDb(float $beamDiameterMeters, float $rxApertureMeters): float
    {
        if ($rxApertureMeters <= 0 || $beamDiameterMeters <= $rxApertureMeters) {
            return 0.0;
        }

        $areaRatio = ($beamDiameterMeters / $rxApertureMeters) ** 2;
        return 10.0 * log10($areaRatio);
    }

    /**
     * Kruse model atmospheric attenuation coefficient in dB/km
     */
    public function calculateAtmosphericLossDbPerKm(float $visibilityKm, int $wavelengthNm = 1550): float
    {
        if ($visibilityKm <= 0) {
            $visibilityKm = 0.5;
        }

        if ($visibilityKm > 50.0) {
            $q = 1.6;
        } elseif ($visibilityKm > 6.0) {
            $q = 1.3;
        } elseif ($visibilityKm > 0.5) {
            $q = 0.585 * ($visibilityKm ** (1.0 / 3.0));
        } else {
            $q = 0.0;
        }

        $gamma = (3.91 / $visibilityKm) * (($wavelengthNm / 550.0) ** -$q);
        return max(0.01, $gamma);
    }

    /**
     * Complete FSO Optical Link Evaluation supporting Full Technical and LOS Survey modes.
     */
    public function evaluateLink(
        float $distanceKm,
        string $wavelength,
        float $requiredThroughputMbps,
        bool $losPass,
        bool $terrainPass,
        array $fsoInputs = []
    ): array {
        $fsoCalcMode = $fsoInputs['fso_calc_mode'] ?? 'full_tech';
        $userCustomRemarks = $fsoInputs['user_custom_remarks'] ?? '';

        // Mode 2: LOS Survey Verification Only (Without technical parameters)
        if ($fsoCalcMode === 'los_only') {
            $status = ($losPass && $terrainPass) ? 'Suitable' : 'Not Recommended';
            
            if ($losPass && $terrainPass) {
                $losRecommendation = "Line-of-Sight direct optical ray clearance verified between Site A and Site B over {$distanceKm} km span.";
                $engineeringSummary = "LOS Field Survey Passed: Unobstructed direct optical ray path between Site A and Site B. Antenna tower heights are adequate for direct line of sight.";
            } else {
                $losRecommendation = "Line-of-Sight obstructed by intermediate terrain or elevation obstacles. Recommend increasing antenna tower heights at Site A or Site B.";
                $engineeringSummary = "LOS Survey Warning: Direct optical ray path experiences terrain obstruction. Tower height adjustment required.";
            }

            return [
                'fso_calc_mode' => 'los_only',
                'status' => $status,
                'user_custom_remarks' => $userCustomRemarks,
                'atmospheric_recommendation' => $losRecommendation,
                'engineering_summary' => $engineeringSummary,
            ];
        }

        // Mode 1: Full Technical Link Budget Analysis
        $txPowerDbm = (float) ($fsoInputs['tx_power_dbm'] ?? 24.0);
        $divergenceMrad = (float) ($fsoInputs['beam_divergence_mrad'] ?? 0.15);
        $rxApertureM = (float) ($fsoInputs['rx_aperture_m'] ?? 0.9);
        $systemLossDb = (float) ($fsoInputs['system_loss_db'] ?? 3.0);
        $rxSensitivityDbm = (float) ($fsoInputs['rx_sensitivity_dbm'] ?? -18.0);
        $refFadeMarginDb = (float) ($fsoInputs['ref_fade_margin_db'] ?? 22.0);
        $selectedCity = $fsoInputs['selected_city'] ?? 'Bengaluru, Karnataka';
        $visibilityKm = (float) ($fsoInputs['visibility_km'] ?? 2.0);

        $wavelengthNm = match ($wavelength) {
            '850 nm' => 850,
            '1310 nm' => 1310,
            '1550 nm' => 1550,
            default => 1550,
        };

        // 1. Beam Diameter & Geometric Loss
        $beamDiameterM = $this->calculateBeamDiameterMeters($distanceKm, $divergenceMrad);
        $geometricLossDb = $this->calculateGeometricLossDb($beamDiameterM, $rxApertureM);

        // 2. Atmospheric Attenuation via Kruse Model
        $atmosLossDbPerKm = $this->calculateAtmosphericLossDbPerKm($visibilityKm, $wavelengthNm);
        $totalAtmosLossDb = $atmosLossDbPerKm * $distanceKm;

        // 3. Link Budget & Received Optical Power
        $totalPathLossDb = $systemLossDb + $geometricLossDb + $totalAtmosLossDb;
        $rxPowerDbm = $txPowerDbm - $totalPathLossDb;

        // 4. Fade Margin (dB)
        $calculatedFadeMarginDb = $rxPowerDbm - $rxSensitivityDbm;

        // Clean formatted numbers for text summaries
        $formattedRxPower = number_format($rxPowerDbm, 2);
        $formattedFadeMargin = number_format($calculatedFadeMarginDb, 2);

        // Verdict determination
        if ($losPass && $terrainPass && $calculatedFadeMarginDb >= 10.0) {
            $status = 'Suitable';
            $atmosphericRecommendation = "Optimal optical link budget. Calculated fade margin ({$formattedFadeMargin} dB) exceeds operational threshold for {$selectedCity} (Visibility: {$visibilityKm} km).";
            $engineeringSummary = "Line-of-Sight is clear and received optical power ({$formattedRxPower} dBm) provides robust link availability.";
        } elseif ($losPass && $terrainPass && $calculatedFadeMarginDb >= 0.0) {
            $status = 'Marginal';
            $atmosphericRecommendation = "Calculated fade margin ({$formattedFadeMargin} dB) is positive. Increased fog risk during low visibility periods in {$selectedCity}.";
            $engineeringSummary = "Distance ({$distanceKm} km) operates with reduced optical power margin. Consider increasing Tx power or narrowing beam divergence.";
        } else {
            $status = 'Not Recommended';
            $atmosphericRecommendation = "Insufficient optical power budget (Fade margin: {$formattedFadeMargin} dB). High optical attenuation over {$distanceKm} km span.";
            $engineeringSummary = "FSO link will fail receiver sensitivity threshold ({$rxSensitivityDbm} dBm). Link distance exceeds optical envelope for visibility {$visibilityKm} km.";
        }

        return [
            'fso_calc_mode' => 'full_tech',
            'status' => $status,
            'user_custom_remarks' => $userCustomRemarks,
            'tx_power_dbm' => $txPowerDbm,
            'beam_divergence_mrad' => $divergenceMrad,
            'rx_aperture_m' => $rxApertureM,
            'system_loss_db' => $systemLossDb,
            'rx_sensitivity_dbm' => $rxSensitivityDbm,
            'ref_fade_margin_db' => $refFadeMarginDb,
            'selected_city' => $selectedCity,
            'visibility_km' => $visibilityKm,
            'laser_wavelength' => $wavelength,
            'beam_diameter_at_receiver_m' => round($beamDiameterM, 3),
            'geometric_loss_db' => round($geometricLossDb, 3),
            'atmospheric_loss_db_per_km' => round($atmosLossDbPerKm, 3),
            'total_atmospheric_loss_db' => round($totalAtmosLossDb, 3),
            'total_path_loss_db' => round($totalPathLossDb, 3),
            'rx_power_dbm' => round($rxPowerDbm, 3),
            'calculated_fade_margin_db' => round($calculatedFadeMarginDb, 3),
            'max_practical_distance_km' => round($distanceKm, 3),
            'atmospheric_recommendation' => $atmosphericRecommendation,
            'engineering_summary' => $engineeringSummary,
        ];
    }
}

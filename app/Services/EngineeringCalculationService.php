<?php

namespace App\Services;

class EngineeringCalculationService
{
    public function __construct(
        protected GeodesicService $geodesicService,
        protected RFCalculationService $rfCalculationService,
        protected FSOCalculationService $fsoCalculationService,
        protected ElevationService $elevationService,
        protected SvgGeneratorService $svgGeneratorService
    ) {}

    /**
     * Executes complete RF or FSO link engineering calculation.
     */
    public function runCalculation(array $inputs): array
    {
        $tech = strtolower($inputs['technology'] ?? 'rf');

        // Site A
        $siteA = $inputs['site_a'];
        $latA = (float) $siteA['latitude'];
        $lonA = (float) $siteA['longitude'];
        $towerA = (float) $siteA['tower_height'];
        $elevA = (float) ($siteA['ground_elevation'] ?? 0.0);

        // Site B
        $siteB = $inputs['site_b'];
        $latB = (float) $siteB['latitude'];
        $lonB = (float) $siteB['longitude'];
        $towerB = (float) $siteB['tower_height'];
        $elevB = (float) ($siteB['ground_elevation'] ?? 0.0);

        // 1. Common Geodesic Calculations
        $distanceKm = $this->geodesicService->calculateDistanceKm($latA, $lonA, $latB, $lonB);
        $azimuthAtoB = $this->geodesicService->calculateAzimuth($latA, $lonA, $latB, $lonB);
        $azimuthBtoA = $this->geodesicService->calculateAzimuth($latB, $lonB, $latA, $lonA);
        $tiltA = $this->geodesicService->calculateMechanicalTilt($towerA, $elevA, $towerB, $elevB, $distanceKm);
        $tiltB = $this->geodesicService->calculateMechanicalTilt($towerB, $elevB, $towerA, $elevA, $distanceKm);

        // 2. Frequency / Wavelength specific values
        $frequencyGHz = 5.8;
        if ($tech === 'rf') {
            $frequencyGHz = (float) ($inputs['rf_frequency_ghz'] ?? 5.8);
        } else {
            $wavelength = $inputs['fso_wavelength'] ?? '1550 nm';
            $frequencyGHz = match ($wavelength) {
                '850 nm' => 352700.0,
                '1310 nm' => 228850.0,
                '1550 nm' => 193400.0,
                default => 193400.0,
            };
        }

        // 3. Terrain & Elevation Profile Evaluation
        $profileResult = $this->elevationService->generateProfile(
            $latA, $lonA, $elevA, $towerA,
            $latB, $lonB, $elevB, $towerB,
            $distanceKm,
            $tech === 'rf' ? $frequencyGHz : 10.0
        );

        // 4. Generate PNG Base64 Map and Elevation Chart graphics (PNG avoids DomPDF SVG flip bug)
        $mapPngImage = $this->svgGeneratorService->generateMapPng(
            $siteA['name'] ?? 'Site A', $latA, $lonA,
            $siteB['name'] ?? 'Site B', $latB, $lonB,
            $distanceKm
        );

        $chartPngImage = $this->svgGeneratorService->generateElevationChartPng(
            $profileResult['profile_points'] ?? [],
            $distanceKm
        );

        // 5. Technology Specific Evaluation
        if ($tech === 'rf') {
            $reqThroughput = (float) ($inputs['rf_required_throughput_mbps'] ?? 100.0);
            $channelWidth = $this->rfCalculationService->recommendChannelWidth($frequencyGHz, $reqThroughput);
            $techEvaluation = $this->rfCalculationService->evaluateLink(
                $distanceKm,
                $frequencyGHz,
                $profileResult['min_clearance_m'],
                $profileResult['max_fresnel_radius_m'],
                $profileResult['los_pass'] === 'PASS',
                $profileResult['terrain_pass'] === 'PASS'
            );

            $payload = [
                'technology' => 'rf',
                'frequency_ghz' => $frequencyGHz,
                'required_throughput_mbps' => $reqThroughput,
                'recommended_channel_width_mhz' => $channelWidth,
                'fresnel_radius_max_m' => $profileResult['max_fresnel_radius_m'],
                'min_clearance_m' => $profileResult['min_clearance_m'],
                'engineering_recommendation' => $techEvaluation['status'],
                'engineering_summary' => $techEvaluation['summary'],
                'max_practical_distance_km' => $techEvaluation['max_practical_distance_km'],
            ];
        } else {
            $wavelength = $inputs['fso_wavelength'] ?? '1550 nm';
            $reqThroughput = (float) ($inputs['fso_required_throughput_mbps'] ?? 1000.0);
            
            $fsoInputs = [
                'tx_power_dbm' => $inputs['fso_tx_power_dbm'] ?? 24,
                'beam_divergence_mrad' => $inputs['fso_beam_divergence_mrad'] ?? 0.15,
                'rx_aperture_m' => $inputs['fso_rx_aperture_m'] ?? 0.9,
                'system_loss_db' => $inputs['fso_system_loss_db'] ?? 3,
                'rx_sensitivity_dbm' => $inputs['fso_rx_sensitivity_dbm'] ?? -18,
                'ref_fade_margin_db' => $inputs['fso_ref_fade_margin_db'] ?? 22,
                'selected_city' => $inputs['fso_selected_city'] ?? 'Bengaluru, Karnataka',
                'visibility_km' => $inputs['fso_visibility_km'] ?? 2.0,
            ];

            $techEvaluation = $this->fsoCalculationService->evaluateLink(
                $distanceKm,
                $wavelength,
                $reqThroughput,
                $profileResult['los_pass'] === 'PASS',
                $profileResult['terrain_pass'] === 'PASS',
                $fsoInputs
            );

            $payload = array_merge([
                'technology' => 'fso',
                'required_throughput_mbps' => $reqThroughput,
                'engineering_recommendation' => $techEvaluation['status'],
            ], $techEvaluation);
        }

        return [
            'distance_km' => round($distanceKm, 3),
            'azimuth_a_to_b' => round($azimuthAtoB, 3),
            'azimuth_b_to_a' => round($azimuthBtoA, 3),
            'elevation_angle_a' => round($tiltA, 3),
            'elevation_angle_b' => round($tiltB, 3),
            'tilt_a' => round($tiltA, 3),
            'tilt_b' => round($tiltB, 3),
            'earth_curvature_max_m' => round($profileResult['max_earth_curvature_m'], 3),
            'fresnel_radius_max_m' => round($profileResult['max_fresnel_radius_m'], 3),
            'min_clearance_m' => round($profileResult['min_clearance_m'], 3),
            'los_status' => $profileResult['los_pass'],
            'terrain_status' => $profileResult['terrain_pass'],
            'recommendation_status' => $techEvaluation['status'],
            'recommended_tower_height_a' => round($profileResult['recommended_tower_height_a'], 3),
            'recommended_tower_height_b' => round($profileResult['recommended_tower_height_b'], 3),
            'effective_elev_a' => $profileResult['effective_elev_a'],
            'effective_elev_b' => $profileResult['effective_elev_b'],
            'map_image' => $mapPngImage,
            'chart_image' => $chartPngImage,
            'tech_payload' => $payload,
            'profile_points' => $profileResult['profile_points'],
        ];
    }
}

<?php

namespace Tests\Unit;

use App\Services\ElevationService;
use App\Services\FSOCalculationService;
use App\Services\GeodesicService;
use App\Services\RFCalculationService;
use App\Services\SvgGeneratorService;
use App\Services\EngineeringCalculationService;
use Tests\TestCase;

class EngineeringCalculationTest extends TestCase
{
    protected GeodesicService $geodesicService;
    protected RFCalculationService $rfService;
    protected FSOCalculationService $fsoService;
    protected ElevationService $elevationService;
    protected SvgGeneratorService $svgGenerator;
    protected EngineeringCalculationService $calculationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->geodesicService = new GeodesicService();
        $this->rfService = new RFCalculationService();
        $this->fsoService = new FSOCalculationService();
        $this->elevationService = new ElevationService($this->geodesicService);
        $this->svgGenerator = new SvgGeneratorService();
        $this->calculationService = new EngineeringCalculationService(
            $this->geodesicService,
            $this->rfService,
            $this->fsoService,
            $this->elevationService,
            $this->svgGenerator
        );
    }

    public function test_geodesic_distance_precision(): void
    {
        $latA = 12.97159870;
        $lonA = 77.59456270;
        $latB = 13.03582450;
        $lonB = 77.62514820;

        $distKm = $this->geodesicService->calculateDistanceKm($latA, $lonA, $latB, $lonB);
        
        $this->assertGreaterThan(7.5, $distKm);
        $this->assertLessThan(8.2, $distKm);
        $this->assertEquals('7.882', number_format($distKm, 3));
    }

    public function test_forward_and_reverse_azimuth(): void
    {
        $latA = 12.97159870;
        $lonA = 77.59456270;
        $latB = 13.03582450;
        $lonB = 77.62514820;

        $azAtoB = $this->geodesicService->calculateAzimuth($latA, $lonA, $latB, $lonB);
        $azBtoA = $this->geodesicService->calculateAzimuth($latB, $lonB, $latA, $lonA);

        $diff = abs(($azAtoB + 180.0) - $azBtoA);
        $this->assertLessThan(1.0, fmod($diff, 360.0));
    }

    public function test_rf_fresnel_radius(): void
    {
        $distKm = 10.0;
        $freqGHz = 5.8;

        $fresnelM = $this->rfService->calculateMaxFresnelRadius($distKm, $freqGHz);
        
        $this->assertGreaterThan(10.0, $fresnelM);
        $this->assertLessThan(13.0, $fresnelM);
    }

    public function test_fso_beam_diameter(): void
    {
        $distKm = 2.0;
        $beamDiamM = $this->fsoService->calculateBeamDiameterMeters($distKm, 0.15);

        $this->assertGreaterThan(0.3, $beamDiamM);
        $this->assertLessThan(0.4, $beamDiamM);
    }

    public function test_elevation_lookup_api(): void
    {
        $response = $this->getJson('/api/elevation/lookup?latitude=12.9715987&longitude=77.5945627');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'latitude' => 12.9715987,
                'longitude' => 77.5945627,
            ]);

        $this->assertGreaterThan(800, $response->json('ground_elevation'));
    }

    public function test_full_calculation_service_fso(): void
    {
        $inputs = [
            'technology' => 'fso',
            'site_a' => [
                'name' => 'Site A',
                'latitude' => 12.97159870,
                'longitude' => 77.59456270,
                'tower_height' => 30.0,
                'ground_elevation' => 920.0,
            ],
            'site_b' => [
                'name' => 'Site B',
                'latitude' => 13.03582450,
                'longitude' => 77.62514820,
                'tower_height' => 30.0,
                'ground_elevation' => 915.0,
            ],
            'fso_wavelength' => '1550 nm',
            'fso_required_throughput_mbps' => 1000,
            'fso_tx_power_dbm' => 24,
            'fso_beam_divergence_mrad' => 0.15,
            'fso_rx_aperture_m' => 0.9,
            'fso_system_loss_db' => 3,
            'fso_rx_sensitivity_dbm' => -18,
            'fso_ref_fade_margin_db' => 22,
            'fso_selected_city' => 'Bengaluru, Karnataka',
            'fso_visibility_km' => 2.0
        ];

        $res = $this->calculationService->runCalculation($inputs);

        $this->assertEquals('PASS', $res['los_status']);
        $this->assertArrayHasKey('map_image', $res);
        $this->assertArrayHasKey('chart_image', $res);
        $this->assertStringStartsWith('data:image/png;base64,', $res['map_image']);
        $this->assertStringStartsWith('data:image/png;base64,', $res['chart_image']);
    }
}

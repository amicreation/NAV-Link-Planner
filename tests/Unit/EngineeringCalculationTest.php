<?php

namespace Tests\Unit;

use App\Services\ElevationService;
use App\Services\FSOCalculationService;
use App\Services\GeodesicService;
use App\Services\RFCalculationService;
use App\Services\SvgGeneratorService;
use App\Services\EngineeringCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngineeringCalculationTest extends TestCase
{
    use RefreshDatabase;
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

    public function test_decimal_to_dms_conversion(): void
    {
        $dmsLat = GeodesicService::toDms(12.97159870, false);
        $this->assertEquals('12° 58\' 17.76" N', $dmsLat);

        $dmsLon = GeodesicService::toDms(77.59456270, true);
        $this->assertEquals('77° 35\' 40.43" E', $dmsLon);

        $dmsSouth = GeodesicService::toDms(-33.8688, false);
        $this->assertEquals('33° 52\' 07.68" S', $dmsSouth);

        $dmsWest = GeodesicService::toDms(-118.2437, true);
        $this->assertEquals('118° 14\' 37.32" W', $dmsWest);
    }

    public function test_dms_to_decimal_parsing(): void
    {
        $lat = GeodesicService::dmsToDecimal('12° 58\' 17.76" N', false);
        $this->assertEqualsWithDelta(12.9716, $lat, 0.0001);

        $lon = GeodesicService::dmsToDecimal('77° 35\' 40.43" E', true);
        $this->assertEqualsWithDelta(77.59456, $lon, 0.0001);

        $latSpace = GeodesicService::dmsToDecimal('12 58 17.76 N', false);
        $this->assertEqualsWithDelta(12.9716, $latSpace, 0.0001);

        $latColon = GeodesicService::dmsToDecimal('12:58:17.76 N', false);
        $this->assertEqualsWithDelta(12.9716, $latColon, 0.0001);

        $latSouth = GeodesicService::dmsToDecimal('33° 52\' 07.68" S', false);
        $this->assertEqualsWithDelta(-33.8688, $latSouth, 0.0001);

        $numericVal = GeodesicService::dmsToDecimal(12.9715987, false);
        $this->assertEquals(12.9715987, $numericVal);

        $latUser = GeodesicService::dmsToDecimal('19°12\'48.20"N', false);
        $this->assertEqualsWithDelta(19.21338889, $latUser, 0.0001);

        $latCompact = GeodesicService::dmsToDecimal('191248.20', false);
        $this->assertEqualsWithDelta(19.21338889, $latCompact, 0.0001);
    }

    public function test_calculation_api_accepts_dms_input(): void
    {
        $payload = [
            'technology' => 'rf',
            'site_a' => [
                'name' => 'Site A',
                'latitude' => '12° 58\' 17.76" N',
                'longitude' => '77° 35\' 40.43" E',
                'tower_height' => 30.0,
                'ground_elevation' => 920.0,
            ],
            'site_b' => [
                'name' => 'Site B',
                'latitude' => '13° 02\' 08.97" N',
                'longitude' => '77° 37\' 30.53" E',
                'tower_height' => 30.0,
                'ground_elevation' => 915.0,
            ],
            'rf_frequency_ghz' => 5.8,
            'rf_required_throughput_mbps' => 100,
        ];

        $response = $this->postJson('/api/calculate', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
        
        $this->assertNotNull($response->json('data.distance_km'));
        $this->assertEquals('PASS', $response->json('data.los_status'));
    }

    public function test_pdf_report_includes_custom_remarks(): void
    {
        $project = \App\Models\Project::first();
        if (!$project) {
            $project = \App\Models\Project::create([
                'name' => 'Test Link',
                'customer_name' => 'Test Corp',
                'engineer_name' => 'Eng. Test',
                'project_date' => now()->toDateString(),
                'technology' => 'fso',
            ]);
            \App\Models\Site::create([
                'project_id' => $project->id,
                'site_label' => 'A',
                'name' => 'Site A',
                'latitude' => 12.97159870,
                'longitude' => 77.59456270,
                'tower_height' => 30.0,
                'ground_elevation' => 920.0,
            ]);
            \App\Models\Site::create([
                'project_id' => $project->id,
                'site_label' => 'B',
                'name' => 'Site B',
                'latitude' => 13.03582450,
                'longitude' => 77.62514820,
                'tower_height' => 30.0,
                'ground_elevation' => 915.0,
            ]);
        }

        $customNote = "As this location falls in deep sea, considering the visibility for Mumbai which is 3.5km average, the link seems feasible.";

        $response = $this->post("/api/projects/{$project->id}/pdf", [
            'user_custom_remarks' => $customNote,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));

        // Also verify the rendered view directly contains the exact custom note
        $project->load(['sites', 'calculation']);
        $calcData = $project->calculation?->results_payload ?? [
            'tech_payload' => ['user_custom_remarks' => $customNote],
        ];
        $calcData['user_custom_remarks'] = $customNote;
        $calcData['tech_payload']['user_custom_remarks'] = $customNote;

        $viewHtml = view('pdf.report', [
            'project' => $project,
            'siteA' => $project->siteA,
            'siteB' => $project->siteB,
            'calc' => $calcData,
        ])->render();

        $this->assertStringContainsString('Engineer Custom Remarks & Field Notes:', $viewHtml);
        $this->assertStringContainsString($customNote, $viewHtml);
    }
}

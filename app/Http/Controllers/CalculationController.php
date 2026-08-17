<?php

namespace App\Http\Controllers;

use App\Http\Requests\PerformCalculationRequest;
use App\Models\Calculation;
use App\Models\Project;
use App\Services\EngineeringCalculationService;
use Illuminate\Http\JsonResponse;

class CalculationController extends Controller
{
    public function __construct(
        protected EngineeringCalculationService $calculationService
    ) {}

    public function calculate(PerformCalculationRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Enforce system role permission: Engineer role is restricted to FSO LOS Survey mode
        $user = auth()->user();
        if ($user && $user->role === 'engineer' && ($validated['technology'] ?? '') === 'fso') {
            $validated['fso_calc_mode'] = 'los_only';
        }

        $result = $this->calculationService->runCalculation($validated);

        // Store calculation record if linked to a project
        if (!empty($validated['project_id'])) {
            $project = Project::find($validated['project_id']);
            if ($project) {
                // Update site tower heights & ground elevations if modified/auto-calculated
                $siteA = $project->siteA;
                if ($siteA) {
                    $siteA->update([
                        'name' => $validated['site_a']['name'],
                        'latitude' => $validated['site_a']['latitude'],
                        'longitude' => $validated['site_a']['longitude'],
                        'tower_height' => $validated['site_a']['tower_height'],
                        'ground_elevation' => ($validated['site_a']['ground_elevation'] ?? 0) > 0 
                            ? $validated['site_a']['ground_elevation'] 
                            : ($result['effective_elev_a'] ?? 920.0),
                    ]);
                }

                $siteB = $project->siteB;
                if ($siteB) {
                    $siteB->update([
                        'name' => $validated['site_b']['name'],
                        'latitude' => $validated['site_b']['latitude'],
                        'longitude' => $validated['site_b']['longitude'],
                        'tower_height' => $validated['site_b']['tower_height'],
                        'ground_elevation' => ($validated['site_b']['ground_elevation'] ?? 0) > 0 
                            ? $validated['site_b']['ground_elevation'] 
                            : ($result['effective_elev_b'] ?? 915.0),
                    ]);
                }

                Calculation::create([
                    'project_id' => $project->id,
                    'distance_km' => $result['distance_km'],
                    'azimuth_a_to_b' => $result['azimuth_a_to_b'],
                    'azimuth_b_to_a' => $result['azimuth_b_to_a'],
                    'elevation_angle_a' => $result['elevation_angle_a'],
                    'elevation_angle_b' => $result['elevation_angle_b'],
                    'tilt_a' => $result['tilt_a'],
                    'tilt_b' => $result['tilt_b'],
                    'earth_curvature_max_m' => $result['earth_curvature_max_m'],
                    'fresnel_radius_max_m' => $result['fresnel_radius_max_m'],
                    'min_clearance_m' => $result['min_clearance_m'],
                    'los_status' => $result['los_status'],
                    'terrain_status' => $result['terrain_status'],
                    'recommendation_status' => $result['recommendation_status'],
                    'recommended_tower_height_a' => $result['recommended_tower_height_a'],
                    'recommended_tower_height_b' => $result['recommended_tower_height_b'],
                    'input_params' => $validated,
                    'results_payload' => $result,
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Models\Project;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index(): JsonResponse
    {
        $projects = Project::with(['sites', 'calculation'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $projects,
        ]);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $project = DB::transaction(function () use ($validated) {
            $project = Project::create([
                'name' => $validated['name'],
                'customer_name' => $validated['customer_name'],
                'engineer_name' => $validated['engineer_name'],
                'project_date' => $validated['project_date'],
                'technology' => $validated['technology'],
            ]);

            Site::create([
                'project_id' => $project->id,
                'site_label' => 'A',
                'name' => $validated['site_a']['name'],
                'latitude' => $validated['site_a']['latitude'],
                'longitude' => $validated['site_a']['longitude'],
                'tower_height' => $validated['site_a']['tower_height'],
                'ground_elevation' => $validated['site_a']['ground_elevation'] ?? 0.0,
            ]);

            Site::create([
                'project_id' => $project->id,
                'site_label' => 'B',
                'name' => $validated['site_b']['name'],
                'latitude' => $validated['site_b']['latitude'],
                'longitude' => $validated['site_b']['longitude'],
                'tower_height' => $validated['site_b']['tower_height'],
                'ground_elevation' => $validated['site_b']['ground_elevation'] ?? 0.0,
            ]);

            return $project->load(['sites', 'calculation']);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Project created successfully.',
            'data' => $project,
        ], 201);
    }

    public function show(Project $project): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $project->load(['sites', 'calculation']),
        ]);
    }

    public function destroy(Project $project): JsonResponse
    {
        $project->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Project deleted successfully.',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Report;
use App\Services\EngineeringCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected EngineeringCalculationService $calculationService
    ) {}

    public function generatePdf(Request $request, Project $project)
    {
        $project->load(['sites', 'calculation']);
        $siteA = $project->siteA;
        $siteB = $project->siteB;

        // Retrieve stored calculation or run fresh
        $calc = $project->calculation;
        if ($calc && isset($calc->results_payload)) {
            $calculationData = $calc->results_payload;
        } else {
            $inputs = [
                'project_id' => $project->id,
                'technology' => $project->technology,
                'site_a' => [
                    'name' => $siteA?->name ?? 'Site A',
                    'latitude' => $siteA?->latitude ?? 0.0,
                    'longitude' => $siteA?->longitude ?? 0.0,
                    'tower_height' => $siteA?->tower_height ?? 30.0,
                    'ground_elevation' => $siteA?->ground_elevation ?? 0.0,
                ],
                'site_b' => [
                    'name' => $siteB?->name ?? 'Site B',
                    'latitude' => $siteB?->latitude ?? 0.0,
                    'longitude' => $siteB?->longitude ?? 0.0,
                    'tower_height' => $siteB?->tower_height ?? 30.0,
                    'ground_elevation' => $siteB?->ground_elevation ?? 0.0,
                ],
                'rf_frequency_ghz' => 5.8,
                'rf_required_throughput_mbps' => 100,
                'fso_wavelength' => '1550 nm',
                'fso_required_throughput_mbps' => 1000,
            ];
            $calculationData = $this->calculationService->runCalculation($inputs);
        }

        // Merge request-provided images (map_image, chart_image, additional_images)
        if ($request->filled('map_image')) {
            $calculationData['map_image'] = $request->input('map_image');
        }
        if ($request->filled('chart_image')) {
            $calculationData['chart_image'] = $request->input('chart_image');
        }
        if ($request->has('additional_images')) {
            $calculationData['additional_images'] = $request->input('additional_images');
        }

        // Merge request-provided custom engineer survey remarks
        if ($request->has('user_custom_remarks')) {
            $userRemarks = (string) $request->input('user_custom_remarks');
            $calculationData['user_custom_remarks'] = $userRemarks;
            if (!isset($calculationData['tech_payload'])) {
                $calculationData['tech_payload'] = [];
            }
            $calculationData['tech_payload']['user_custom_remarks'] = $userRemarks;

            if ($calc && isset($calc->results_payload)) {
                $storedPayload = $calc->results_payload;
                $storedPayload['user_custom_remarks'] = $userRemarks;
                if (!isset($storedPayload['tech_payload'])) {
                    $storedPayload['tech_payload'] = [];
                }
                $storedPayload['tech_payload']['user_custom_remarks'] = $userRemarks;
                $calc->results_payload = $storedPayload;
                $calc->save();
            }
        }

        $pdf = Pdf::loadView('pdf.report', [
            'project' => $project,
            'siteA' => $siteA,
            'siteB' => $siteB,
            'calc' => $calculationData,
        ]);

        $pdf->setPaper('a4', 'portrait');

        // Log report generation
        Report::create([
            'project_id' => $project->id,
            'file_path' => 'generated_pdf_' . $project->id . '.pdf',
            'generated_at' => now(),
        ]);

        $filename = 'NAV_Link_Planner_' . str_replace(' ', '_', $project->name) . '.pdf';
        
        return $pdf->download($filename);
    }
}

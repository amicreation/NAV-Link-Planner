<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PerformCalculationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'nullable|exists:projects,id',
            'technology' => 'required|in:rf,fso',
            'site_a.name' => 'required|string|max:255',
            'site_a.latitude' => 'required|numeric|between:-90,90',
            'site_a.longitude' => 'required|numeric|between:-180,180',
            'site_a.tower_height' => 'required|numeric|min:0',
            'site_a.ground_elevation' => 'nullable|numeric',
            'site_b.name' => 'required|string|max:255',
            'site_b.latitude' => 'required|numeric|between:-90,90',
            'site_b.longitude' => 'required|numeric|between:-180,180',
            'site_b.tower_height' => 'required|numeric|min:0',
            'site_b.ground_elevation' => 'nullable|numeric',
            // RF specific
            'rf_frequency_ghz' => 'required_if:technology,rf|nullable|numeric|min:0.1',
            'rf_required_throughput_mbps' => 'required_if:technology,rf|nullable|numeric|min:1',
            // FSO specific
            'fso_wavelength' => 'required_if:technology,fso|nullable|string|in:850 nm,1310 nm,1550 nm',
            'fso_required_throughput_mbps' => 'required_if:technology,fso|nullable|numeric|min:1',
            'fso_tx_power_dbm' => 'nullable|numeric',
            'fso_beam_divergence_mrad' => 'nullable|numeric',
            'fso_rx_aperture_m' => 'nullable|numeric',
            'fso_system_loss_db' => 'nullable|numeric',
            'fso_rx_sensitivity_dbm' => 'nullable|numeric',
            'fso_ref_fade_margin_db' => 'nullable|numeric',
            'fso_selected_city' => 'nullable|string',
            'fso_visibility_km' => 'nullable|numeric',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'engineer_name' => 'required|string|max:255',
            'project_date' => 'required|date',
            'technology' => 'required|in:rf,fso',
            'site_a.name' => 'required|string|max:255',
            'site_a.latitude' => 'required|numeric|between:-90,90',
            'site_a.longitude' => 'required|numeric|between:-180,180',
            'site_a.tower_height' => 'required|numeric|min:0',
            'site_b.name' => 'required|string|max:255',
            'site_b.latitude' => 'required|numeric|between:-90,90',
            'site_b.longitude' => 'required|numeric|between:-180,180',
            'site_b.tower_height' => 'required|numeric|min:0',
        ];
    }
}

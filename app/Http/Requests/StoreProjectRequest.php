<?php

namespace App\Http\Requests;

use App\Services\GeodesicService;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $siteA = $this->input('site_a');
        $siteB = $this->input('site_b');

        if (is_array($siteA)) {
            if (isset($siteA['latitude'])) {
                $siteA['latitude'] = GeodesicService::dmsToDecimal($siteA['latitude'], false);
            }
            if (isset($siteA['longitude'])) {
                $siteA['longitude'] = GeodesicService::dmsToDecimal($siteA['longitude'], true);
            }
            $this->merge(['site_a' => $siteA]);
        }

        if (is_array($siteB)) {
            if (isset($siteB['latitude'])) {
                $siteB['latitude'] = GeodesicService::dmsToDecimal($siteB['latitude'], false);
            }
            if (isset($siteB['longitude'])) {
                $siteB['longitude'] = GeodesicService::dmsToDecimal($siteB['longitude'], true);
            }
            $this->merge(['site_b' => $siteB]);
        }
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

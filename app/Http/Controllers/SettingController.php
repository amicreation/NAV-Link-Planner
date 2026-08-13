<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = Setting::all()->pluck('value', 'key');
        return response()->json([
            'status' => 'success',
            'data' => $settings,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'company_logo' => 'nullable|string',
            'default_k_factor' => 'nullable|numeric',
        ]);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => (string) $value]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Settings updated successfully.',
        ]);
    }
}

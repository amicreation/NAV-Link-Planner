<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Calculation extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'distance_km',
        'azimuth_a_to_b',
        'azimuth_b_to_a',
        'elevation_angle_a',
        'elevation_angle_b',
        'tilt_a',
        'tilt_b',
        'earth_curvature_max_m',
        'fresnel_radius_max_m',
        'min_clearance_m',
        'los_status',
        'terrain_status',
        'recommendation_status',
        'recommended_tower_height_a',
        'recommended_tower_height_b',
        'input_params',
        'results_payload',
    ];

    protected $casts = [
        'distance_km' => 'float',
        'azimuth_a_to_b' => 'float',
        'azimuth_b_to_a' => 'float',
        'elevation_angle_a' => 'float',
        'elevation_angle_b' => 'float',
        'tilt_a' => 'float',
        'tilt_b' => 'float',
        'earth_curvature_max_m' => 'float',
        'fresnel_radius_max_m' => 'float',
        'min_clearance_m' => 'float',
        'recommended_tower_height_a' => 'float',
        'recommended_tower_height_b' => 'float',
        'input_params' => 'array',
        'results_payload' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->double('distance_km');
            $table->double('azimuth_a_to_b');
            $table->double('azimuth_b_to_a');
            $table->double('elevation_angle_a');
            $table->double('elevation_angle_b');
            $table->double('tilt_a');
            $table->double('tilt_b');
            $table->double('earth_curvature_max_m');
            $table->double('fresnel_radius_max_m')->nullable();
            $table->double('min_clearance_m')->nullable();
            $table->string('los_status'); // PASS / FAIL
            $table->string('terrain_status'); // PASS / FAIL
            $table->string('recommendation_status'); // Suitable / Marginal / Not Recommended
            $table->double('recommended_tower_height_a')->nullable();
            $table->double('recommended_tower_height_b')->nullable();
            $table->json('input_params');
            $table->json('results_payload');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculations');
    }
};

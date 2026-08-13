<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->enum('site_label', ['A', 'B']);
            $table->string('name');
            $table->decimal('latitude', 11, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('tower_height', 8, 3);
            $table->decimal('ground_elevation', 8, 3)->default(0.000);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};

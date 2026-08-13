<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'site_label',
        'name',
        'latitude',
        'longitude',
        'tower_height',
        'ground_elevation',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'tower_height' => 'float',
        'ground_elevation' => 'float',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

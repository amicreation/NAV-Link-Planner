<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'customer_name',
        'engineer_name',
        'project_date',
        'technology',
    ];

    protected $casts = [
        'project_date' => 'date:Y-m-d',
    ];

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function siteA(): HasOne
    {
        return $this->hasOne(Site::class)->where('site_label', 'A');
    }

    public function siteB(): HasOne
    {
        return $this->hasOne(Site::class)->where('site_label', 'B');
    }

    public function calculation(): HasOne
    {
        return $this->hasOne(Calculation::class)->latestOfMany();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeasurementDetail extends Model
{
    protected $fillable = [
        'plantation_id',
        'total_area_sq_m',
        'soil_type',
        'water_source_available',
        'slope_type',
        'accessibility_notes',
        'feasibility_score'
    ];

    protected $casts = [
        'water_source_available' => 'boolean'
    ];

    public function plantation()
    {
        return $this->belongsTo(Plantation::class);
    }
}

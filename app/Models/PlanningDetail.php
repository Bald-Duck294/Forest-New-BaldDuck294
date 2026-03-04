<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanningDetail extends Model
{
    protected $fillable = [
        'plantation_id',
        'plant_species',
        'plant_count',
        'spacing_pattern',
        'estimated_budget',
        'approved_budget',
        'timeline_start',
        'timeline_end',
        'approved_by'
    ];

    protected $casts = [
        'timeline_start' => 'date',
        'timeline_end' => 'date'
    ];

    public function plantation()
    {
        return $this->belongsTo(Plantation::class);
    }
}

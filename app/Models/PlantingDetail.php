<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlantingDetail extends Model
{
    protected $fillable = [
        'plantation_id',
        'planting_date',
        'actual_plant_count',
        'team_size',
        'attendance_verified',
        'before_photos',
        'after_photos'
    ];

    protected $casts = [
        'planting_date' => 'date',
        'attendance_verified' => 'boolean',
        'before_photos' => 'array',
        'after_photos' => 'array'
    ];

    public function plantation()
    {
        return $this->belongsTo(Plantation::class);
    }
}

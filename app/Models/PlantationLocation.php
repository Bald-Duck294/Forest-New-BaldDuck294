<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlantationLocation extends Model
{
    protected $fillable = [
        'plantation_id',
        'geo_polygon',
        'center_lat',
        'center_lng',
        'area_sq_m',
        'verified_at',
        'created_by'
    ];

    protected $casts = [
        'geo_polygon' => 'array',
        'verified_at' => 'datetime'
    ];

    public function plantation()
    {
        return $this->belongsTo(Plantation::class);
    }
}

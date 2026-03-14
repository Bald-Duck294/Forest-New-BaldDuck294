<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plantation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'coordinates',
        'geo_polygon',
        'area',
        'soil_type',
        'plant_species',
        'plant_count',
        'plantation_start_date',
        'plantation_end_date',
        'is_fenced',
        'photos',
        'current_phase',
        'status',
        'site_id',
        'user_id',
        'is_approved',
    ];

    protected $casts = [
        'coordinates' => 'array',
        'geo_polygon' => 'array',
        'photos' => 'array',
        'is_fenced' => 'boolean',
        'is_approved' => 'boolean',
        'plantation_start_date' => 'date',
        'plantation_end_date' => 'date',
    ];

    public function site()
    {
        return $this->belongsTo(SiteDetail::class , 'site_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class , 'user_id');
    }

    public function observations()
    {
        return $this->hasMany(ObservationRecord::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeatFeature extends Model
{
    use HasFactory;

    protected $table = 'beat_features';
    public $timestamps = false; // Using custom created_at

    protected $fillable = [
        'layer_type',
        'name',
        'geometry_type',
        'coordinates',
        'attributes',
        'company_id',
        'site_id',
        'geofence_id',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'coordinates' => 'array',
        'attributes' => 'array',
    ];
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FencingDetail extends Model
{
    protected $fillable = [
        'plantation_id',
        'fence_type',
        'material_used',
        'boundary_length_m',
        'installation_cost',
        'verification_photos'
    ];

    protected $casts = [
        'verification_photos' => 'array'
    ];

    public function plantation()
    {
        return $this->belongsTo(Plantation::class);
    }
}

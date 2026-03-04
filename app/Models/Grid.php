<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grid extends Model
{
    protected $fillable = [
        'grid_code',
        'geo_polygon',
        'is_active'
    ];

    protected $casts = [
        'geo_polygon' => 'array',
        'is_active' => 'boolean'
    ];

    public function plantations()
    {
        return $this->hasMany(Plantation::class);
    }
}

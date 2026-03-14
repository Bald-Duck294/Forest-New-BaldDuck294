<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdentificationDetail extends Model
{
    protected $fillable = [
        'plantation_id',
        'land_owner_name',
        'land_type',
        'ownership_document',
        'site_photos',
        'is_verified'
    ];

    protected $casts = [
        'site_photos' => 'array',
        'is_verified' => 'boolean'
    ];

    public function plantation()
    {
        return $this->belongsTo(Plantation::class);
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'category',
        'condition',
        'year',
        'description',
        'photo',
        'photos',
        'location'
    ];

    protected $casts = [
        'photos' => 'array',
        'location' => 'array'
    ];
}

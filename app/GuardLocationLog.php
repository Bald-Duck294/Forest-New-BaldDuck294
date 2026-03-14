<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GuardLocationLog extends Model
{
    
    protected $table = "guard_location_log";
    protected $fillable = [
        'location','distance_from_center','diff_radius_location','radius'
    ];

    public $timestamps = false;
}
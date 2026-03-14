<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Geofences extends Model
{
    
    protected $table = "geofences";
    protected $fillable = [
        'geo_name','geo_center','geo_radius','emp_name','shift_name','shift_timing','company_d','emp_id','role_id'
    ];

    public $timestamps = false;
}
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GuardLiveLocationData extends Model
{
    
    protected $table = "guard_live_location_data";
    protected $fillable = [
        'name','location','speed','provider','date','time'
    ];

    public $timestamps = false;
}
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupervisorLiveLocationData extends Model
{
    
    protected $table = "supervisor_live_location_data";
    protected $fillable = [
        'name','location','speed','provider','date','time','date_time'
    ];

    public $timestamps = false;
}
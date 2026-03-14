<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupervisorLocationHistory extends Model
{
    
    protected $table = "supervisor_location_history";
    protected $fillable = [
        'location','provider','speed','date','time','mobile_time'
    ];

    public $timestamps = false;
}
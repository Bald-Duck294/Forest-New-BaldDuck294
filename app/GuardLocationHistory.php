<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GuardLocationHistory extends Model
{
    
    protected $table = "guard_location_history";
    protected $fillable = [
        'location','speed','provider','date','time'
    ];

    public $timestamps = false;
}
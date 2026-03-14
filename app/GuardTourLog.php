<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GuardTourLog extends Model
{
    
    protected $table = "guard_tour_log";
    protected $fillable = [
        'tourName','guardName','date','startTime'
    ];

    public $timestamps = false;
}
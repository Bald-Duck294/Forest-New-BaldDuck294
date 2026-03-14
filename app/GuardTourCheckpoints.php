<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GuardTourCheckpoints extends Model
{
    
    protected $table = "guard_tour_checkpoints";
    protected $fillable = [
        'pointName','sequence','qrData','location'
    ];

    public $timestamps = false;
}
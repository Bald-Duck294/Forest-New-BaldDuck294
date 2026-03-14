<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GuardTourAssign extends Model
{
    
    protected $table = "guard_tour_assign";
    protected $fillable = [
        'guardName','tour_name'
    ];

    public $timestamps = false;
}
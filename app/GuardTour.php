<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GuardTour extends Model
{
    
    protected $table = "guard_tour";
    protected $fillable = [
        'tour_name','geo_name'
    ];

    public $timestamps = false;
}
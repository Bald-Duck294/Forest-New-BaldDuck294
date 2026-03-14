<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TourCheckPointStatus extends Model
{
    
    protected $table = "tour_checkpoint_status";
    protected $fillable = [
        'checkpointName','sequenceNo','date','time' 
    ];

    public $timestamps = false;
}
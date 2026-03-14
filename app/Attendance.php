<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    
    protected $table = "attendance";
    protected $fillable = [
        'name','geo_name','entry_date_time','entry_time','exit_date_time','exit_time','time_difference','time_calculation','duration_for_calc','date'
    ];

    public $timestamps = false;
    
}
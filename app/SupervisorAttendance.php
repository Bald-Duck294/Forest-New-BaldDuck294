<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupervisorAttendance extends Model
{
    
    protected $table = "supervisor_attendance";
    protected $fillable = [
        'name','geo_name','entry_date_time','entry_time','exit_date_time'
    ];

    public $timestamps = false;
}
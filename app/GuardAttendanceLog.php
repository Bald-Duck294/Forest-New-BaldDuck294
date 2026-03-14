<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GuardAttendanceLog extends Model
{
    
    protected $table = "guard_attendance_log";
    protected $fillable = [
        'name','geo_name','entry_date_time','entry_time','exit_date_time','exit_time','time_difference','time_calculation','supervisor_name'
    ];

    public $timestamps = false;
}
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GuardAttendance extends Model
{
    
    protected $table = "guard_attendance";
    protected $fillable = [
        'name','geo_name','entry_date_time','entry_time','exit_date_time','exit_time'
    ];

    public $timestamps = false;
}
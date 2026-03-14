<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveHoliday extends Model
{
    use SoftDeletes;
    protected $table = "leave_holiday";
    protected $fillable = [
        
        'id','name','date' ,'recurring' , 'length','company_id'
    ];

    // public $timestamps = false;
}
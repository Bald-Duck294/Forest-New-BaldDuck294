<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeavePeriodHistory extends Model
{
    use SoftDeletes;
    protected $table = "leave_period_history";
    protected $fillable = [
        'id','leave_period_start_month','leave_period_start_day','created_at','company_id'
    ];

    // public $timestamps = false;
}
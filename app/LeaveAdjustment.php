<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveAdjustment extends Model
{
    use SoftDeletes;
    protected $table = "leave_adjustment";
    protected $fillable = [
        'id','user_idd','no_of_days','leave_type_id' ,'from_date', 'to_date' ,'credited_date','note','adjustment_type',
         'deleted' , 'created_by_id' , 'created_by_name','company_id'
    ];

    // public $timestamps = false;
}
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRequest extends Model
{
    use SoftDeletes;
    protected $table = "leave_request";
    protected $fillable = [
        'id','leave_type_id ','date_applied','emp_number','company_id'
    ];

    // public $timestamps = false;
}
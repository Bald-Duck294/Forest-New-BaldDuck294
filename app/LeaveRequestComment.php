<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRequestComment extends Model
{
    use SoftDeletes;
    protected $table = "leave_request_comment";
    protected $fillable = [
        'id','leave_request_id ','created','created_by_id' ,'created_by_emp_number',
        'comments', 'company_id'
    ];

    // public $timestamps = false;
}
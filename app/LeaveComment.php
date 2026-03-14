<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeaveComment extends Model
{
    
    protected $table = "leave_comment";
    protected $fillable = [
        'id','leave_id ','created' , 'created_by_id' , 'created_by_emp_number' , 'comments','company_id'
    ];

    public $timestamps = false;
}
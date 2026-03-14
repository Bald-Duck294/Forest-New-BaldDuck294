<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveStatus extends Model
{
    //use SoftDeletes;
    protected $table = "leave_status";
    protected $fillable = [
        
        'id','status','name'

    ];

    public $timestamps = false;
}
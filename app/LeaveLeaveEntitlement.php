<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveLeaveEntitlement extends Model
{
    use SoftDeletes;
    protected $table = "leave_leave_entitlement";
    protected $fillable = [
        
        'id','leave_id','entitlement_id' ,'length_days','company_id'
    ];

    // public $timestamps = false;
}
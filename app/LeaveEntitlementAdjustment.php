<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveEntitlementAdjustment extends Model
{
    use SoftDeletes;
    protected $table = "leave_entitlement_adjustment";
    protected $fillable = [
        
        'id','adjustment_id','entitlement_id' ,'length_days','company_id'
    ];

    // public $timestamps = false;
}
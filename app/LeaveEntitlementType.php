<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveEntitlementType extends Model
{
    use SoftDeletes;
    protected $table = "leave_entitlement_type";
    protected $fillable = [
         
        'id','name','is_editable','company_id'
    ];

    //public $timestamps = false;
}
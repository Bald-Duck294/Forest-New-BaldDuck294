<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveEntitlement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id', 'user_id', 'no_of_days', 'days_used', 'leave_type_id', 'from_date', 'to_date', 'credited_date', 'note', 'entitlement_type',
        'deleted', 'created_by_id','company_id'
    ];

    // public $timestamps = false;

    public function leaveType()
    {
        return $this->belongsTo('App\LeaveType', 'leave_type_id')->withTrashed();
    }
}

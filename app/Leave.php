<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    
    protected $table = "leaves";
    protected $fillable = [
        'user_id','user_name','company_id','site_id','role_id','leave_type_id', 'leave_type_name','duration','fromDate','toDate','requested_on','reason','status','action_on','actionById','actionByName', 'actionRemark'
    ];

    public $timestamps = false;

    public function leaveType()
    {
        return $this->belongsTo('App\LeaveType', 'leave_type_id')->withTrashed();
    }
}
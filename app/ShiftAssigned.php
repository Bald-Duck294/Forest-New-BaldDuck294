<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShiftAssigned extends Model
{
    
    protected $table = "shift_assigned";
    protected $fillable = [
        'site_id','shift_id','supervisor_id','company_id','site_name','shift_name','shift_time'
    ];

    public $timestamps = false;
}
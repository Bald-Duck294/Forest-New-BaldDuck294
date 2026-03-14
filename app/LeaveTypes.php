<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeaveTypes extends Model
{
    protected $table = "leavetypes";
    protected $fillable = [
        
        'id','name','description','accural_array', 'reset_array'
    ];

    public $timestamps = false;
}
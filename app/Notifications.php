<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    
    protected $table = "notifications";
    protected $fillable = [
        'notification','type','date','time','company_id','action'
    ];

    public $timestamps = false;

    
}
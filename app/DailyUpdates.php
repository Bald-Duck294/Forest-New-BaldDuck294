<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DailyUpdates extends Model
{
    
    protected $table = "daily_updates";
    protected $fillable = [
        'user_id','user_name','description','photos','company_id','date','site_id'
    ];

    public $timestamps = false;
}
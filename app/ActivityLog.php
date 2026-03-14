<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class ActivityLog extends Model
{
    protected $table = "activity_log";
    protected $fillable = ['user_id', 'company_id', 'user_name', 'message', 'type', 'log_id', 'log_type', 'date_time'];
    protected static $logAttributes = ['user_id', 'company_id', 'user_name', 'message', 'type', 'log_id', 'log_type', 'date_time'];
}

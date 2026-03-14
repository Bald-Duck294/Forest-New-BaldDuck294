<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SiteAssign extends Model
{
    use SoftDeletes;
    protected $table = "site_assign";
    // protected $fillable = [
    //     'user_name', 'geo_name', 'date_range'
    // ];

    protected $fillable = ['user_id', 'user_name', 'site_id', 'site_name', 'client_id', 'client_name', 'company_id', 'date_range', 'shift_id', 'shift_time', 'shift_name', 'week_off', 'role_id'];

    public function clientDetails()
    {
        return $this->hasOne('App\ClientDetails', 'id');
    }

    // public $timestamps = false;
}

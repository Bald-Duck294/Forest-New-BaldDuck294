<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientVisit extends Model
{

    // protected $table = "leaves";
    protected $fillable = [
        'user_id', 'user_name', 'client_name', 'address', 'location', 'date', 'datetime', 'photos', 'remark', 'company_id', 'site_id'
    ];

    public $timestamps = false;

    public function site()
    {
        return $this->belongsTo('App\SiteDetails', 'site_id');
    }
}

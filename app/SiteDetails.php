<?php

namespace App;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class SiteDetails extends Model
{

    protected $table = "site_details";
    protected $fillable = [
        'name',
        'state',
        'email',
        'city',
        'address',
        'pincode',
        'contactPerson',
        'mobile',
        'lateTime',
        'siteType'
    ];

    public $timestamps = false;


    public function client()
    {
        return $this->belongsTo(ClientDetails::class);
    }
}
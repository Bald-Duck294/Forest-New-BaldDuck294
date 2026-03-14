<?php

namespace App;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ClientDetails extends Model
{
    
    protected $table = "client_details";
    protected $fillable = [
        'name','contact','email','spokesperson','address'
    ];

    public function siteAssign(){
        return $this->hasOne('App\SiteAssign', 'client_id');
    }

    public $timestamps = false;


     
}
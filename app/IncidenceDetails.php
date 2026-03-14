<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IncidenceDetails extends Model
{
    
    protected $table = "incidence_details";
    protected $fillable = [
        'type','remark','guard_name','geo_name','location'
    ];

    public function siteAssign(){
        return $this->hasOne('App\SiteAssign', 'id');
    }


    public $timestamps = false;
}
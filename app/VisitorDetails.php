<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VisitorDetails extends Model
{
    
    protected $table = "visitor_details";
    protected $fillable = [
        'name','email','mobile','purpose','geo_name','date'
    ];

    public $timestamps = false;
}
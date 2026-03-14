<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IncidenceComment extends Model
{
    
    protected $table = "incidence_comment";
    protected $fillable = [
        'incidence_id','user_id','client_id','site_id','company_id','comment','date_time'
    ];

    public $timestamps = false;
}
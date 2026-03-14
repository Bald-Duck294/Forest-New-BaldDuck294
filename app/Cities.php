<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cities extends Model
{
    
    protected $table = "cities";
    protected $fillable = [
        'id','name','state_code'
    ];

    public $timestamps = false;
}
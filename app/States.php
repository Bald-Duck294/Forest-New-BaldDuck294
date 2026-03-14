<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class States extends Model
{
    
    protected $table = "states";
    protected $fillable = [
        'id','name','code'
    ];

    public $timestamps = false;
}
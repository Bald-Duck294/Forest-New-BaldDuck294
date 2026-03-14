<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FcmMessage extends Model
{
    
    protected $table = "fcm_message";
    protected $fillable = [
        'message','status'
    ];

    public $timestamps = false;
}
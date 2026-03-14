<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FcmInfo extends Model
{
    
    protected $table = "fcm_info";
    protected $fillable = [
        'otp','isVerified'
    ];

    public $timestamps = false;
}
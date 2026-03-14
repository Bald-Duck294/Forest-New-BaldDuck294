<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegistrationData extends Model
{
    
    protected $table = "registrationData";
    protected $fillable = [
        'firstName','lastName','department','designation','company_name'
    ];

    public $timestamps = false;
}
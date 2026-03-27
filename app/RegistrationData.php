<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegistrationData extends Model
{
    protected $table = "registrationData";

    // Added the missing fields that your controller is trying to save
    protected $fillable = [
        'firstName',
        'lastName',
        'department',
        'designation',
        'company_name',
        'company_id',
        'mobile',
        'email',
        'role_id',
        'registrationFlag'
    ];

    public $timestamps = false;
}

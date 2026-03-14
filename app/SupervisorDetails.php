<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupervisorDetails extends Model
{
    
    protected $table = "supervisor_details";
    protected $fillable = [
        'name','contact','email','password','gender','dob','company_name'
    ];

    public $timestamps = false;
}
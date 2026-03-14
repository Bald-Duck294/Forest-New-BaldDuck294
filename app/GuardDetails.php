<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GuardDetails extends Model
{
    
    protected $table = "guard_details";
    protected $fillable = [
        'name','contact','email','password','gender','dob','company_name','shift_start','shift_end','address'
    ];

    public $timestamps = false;
}
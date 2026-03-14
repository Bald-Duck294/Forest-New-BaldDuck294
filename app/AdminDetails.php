<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminDetails extends Model
{
    
    protected $table = "admin_details";
    protected $fillable = [
        'name', 'contact', 'email','password','gender','dob','company_name','shift_start','shift_end','address','profile_pic','profile_file_name','role_id'
    ];

    public $timestamps = false;
}
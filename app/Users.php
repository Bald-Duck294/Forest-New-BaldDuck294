<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    
    protected $table = "users";
    protected $fillable = [
        'name','contact','email','password','dob','gender','role_id'
    ];

    public function attendances(){
        return $this->hasMany('App\Attendance', 'user_id');
    }

    public function overtimes(){
        return $this->hasMany('App\Models\Overtime', 'user_id');
    }

    public function cashAdvances(){
        return $this->hasMany('App\Models\CashAdvance', 'user_id');
    }

    public function leaves(){
        return $this->hasMany('App\Leave', 'user_id');
    }

    public function siteAssign(){
        return $this->hasOne('App\SiteAssign', 'user_id');
    }

    
    public $timestamps = false;
}
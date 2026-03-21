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

    public function isGlobalAdmin()
    {
        return $this->role_id === 8;
    }

    /**
     * Get the company_id, potentially returning a simulated one for Global Admins.
     */
    public function getCompanyIdAttribute($value)
    {
        // If the user is a Global Admin and we have a simulated company ID in session,
        // and we are NOT on a global route, return the simulated ID.
        if ($this->role_id === 8 && session()->has('simulated_company_id')) {
            $route = request()->route();
            if ($route && !str_starts_with($route->getName(), 'global.')) {
                return session('simulated_company_id');
            }
        }
        return $value;
    }

    public $timestamps = false;
}
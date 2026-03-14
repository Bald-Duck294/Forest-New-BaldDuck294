<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyDetails extends Model
{
    
    protected $table = "company_details";
    protected $fillable = [
        'name','contact','email','contact_person','contact_person_contact','address'
    ];

    public $timestamps = false;
}
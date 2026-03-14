<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IncidenceChecklist extends Model
{
    
    protected $table = "incidence_checklist";
    protected $fillable = [
        'type','name','type_id','company_id' ];

    public $timestamps = false;
}
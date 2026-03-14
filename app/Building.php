<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Building extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'description', 'site_id', 'client_id', 'company_id'];
}

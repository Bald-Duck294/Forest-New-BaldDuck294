<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Floor extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'description', 'building_id', 'site_id', 'client_id', 'company_id'];
}

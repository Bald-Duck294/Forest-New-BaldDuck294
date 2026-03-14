<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Block extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'description', 'floor_id', 'building_id', 'site_id', 'client_id', 'company_id'];
}

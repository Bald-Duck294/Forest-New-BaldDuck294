<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoleIdList extends Model
{
    
    protected $table = "role_id_list";
    protected $fillable = [
        'role_name'
    ];

    public $timestamps = false;
}
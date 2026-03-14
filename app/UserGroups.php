<?php

namespace App;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;


class UserGroups extends Model
{
    protected $table = "user_groups";
    protected $fillable = ['user_id', 'group_id', 'user_name', 'create_date', 'is_active'];
    public $timestamps = false;
}





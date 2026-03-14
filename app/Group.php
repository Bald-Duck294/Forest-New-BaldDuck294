<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;


class Group extends Model
{
    protected $table = "groups";
    protected $fillable = ['id', 'name', 'socket_id',  'create_date', 'is_active'];
    public $timestamps = false;
}

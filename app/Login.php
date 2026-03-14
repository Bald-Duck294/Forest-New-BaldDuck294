<?php

namespace App;
use DB;

use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    protected $table = 'users';
    // protected $fillable = ['id','contact', 'password'];
    public $timestamp = false;
}









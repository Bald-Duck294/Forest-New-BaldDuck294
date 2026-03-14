<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TestTable extends Model
{
    
    protected $table = "test_table";
    protected $fillable = [
        'test_data'
    ];

    public $timestamps = false;
}
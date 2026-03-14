<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldMaster extends Model
{
    protected $table = "field_masters";

    protected $fillable = [
        'field_key',
        'default_label'
    ];
}
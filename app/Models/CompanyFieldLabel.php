<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyFieldLabel extends Model
{
    protected $table = "company_field_labels";

    protected $fillable = [
        'company_id',
        'field_key',
        'custom_label'
    ];
}
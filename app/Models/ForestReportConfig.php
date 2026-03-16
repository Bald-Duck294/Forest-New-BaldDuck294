<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForestReportConfig extends Model
{
    protected $table = 'forest_report_configs';

    protected $fillable = [
        'category',
        'report_type',
        'fields',
        'is_active',
        'company_id'
    ];

    protected $casts = [
        'fields' => 'array',
        'is_active' => 'boolean'
    ];
}

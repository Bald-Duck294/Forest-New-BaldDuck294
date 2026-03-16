<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForestReport extends Model
{
    protected $table = 'forest_reports';

    protected $fillable = [
        'report_id',
        'patrol_id',
        'user_id',
        'company_id',
        'supervisor_id',
        'category',
        'report_type',
        'date_time',
        'date',
        'time',
        'latitude',
        'longitude',
        'site_id',
        'client_id',
        'beat',
        'round',
        'range',
        'report_data',
        'status',
        'final_remarks',
        'photo'
    ];

    protected $casts = [
        'report_data' => 'array',
        'photo' => 'array'
    ];
}

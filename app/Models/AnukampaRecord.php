<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnukampaRecord extends Model
{
    use HasFactory;

    protected $table = 'anukampa_records';

    protected $fillable = [
        'victim_name',
        'contact_number',
        'range',
        'village_name',
        'latitude',
        'longitude',
        'incident_type',
        'animal_responsible',
        'incident_date',
        'estimated_loss',
        'status',
        'remarks',
        'documents',
        'specific_details'
    ];

    protected $casts = [
        'incident_date' => 'date',
        'documents' => 'array',
        'specific_details' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
    ];
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObservationRecord extends Model
{
    protected $fillable = [
        'plantation_id',
        'observation_date',
        'survival_count',
        'survival_percentage',
        'avg_height_cm',
        'health_status',
        'inspector_id',
        'photos',
        'remarks'
    ];

    protected $casts = [
        'observation_date' => 'date',
        'photos' => 'array'
    ];

    public function plantation()
    {
        return $this->belongsTo(Plantation::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelocationRecord extends Model
{
    protected $fillable = [
        'plantation_id',
        'previous_location_id',
        'new_location_id',
        'reason',
        'relocated_by',
        'relocation_date'
    ];

    protected $casts = [
        'relocation_date' => 'date'
    ];

    public function plantation()
    {
        return $this->belongsTo(Plantation::class);
    }

    public function previousLocation()
    {
        return $this->belongsTo(PlantationLocation::class, 'previous_location_id');
    }

    public function newLocation()
    {
        return $this->belongsTo(PlantationLocation::class, 'new_location_id');
    }
}

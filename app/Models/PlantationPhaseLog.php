<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlantationPhaseLog extends Model
{
    protected $fillable = [
        'plantation_id',
        'previous_phase',
        'new_phase',
        'changed_by',
        'remarks',
        'changed_at'
    ];

    protected $casts = [
        'changed_at' => 'datetime'
    ];

    public function plantation()
    {
        return $this->belongsTo(Plantation::class);
    }
}

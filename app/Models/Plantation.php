<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plantation extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'current_phase',
        'status',
        'grid_id',
        'created_by',
        'approved_by',
        'started_at',
        'completed_at'
    ];

    protected $dates = [
        'started_at',
        'completed_at'
    ];

    public function grid()
    {
        return $this->belongsTo(Grid::class);
    }

    public function locations()
    {
        return $this->hasMany(PlantationLocation::class);
    }

    public function phaseLogs()
    {
        return $this->hasMany(PlantationPhaseLog::class);
    }

    public function identification()
    {
        return $this->hasOne(IdentificationDetail::class);
    }

    public function measurement()
    {
        return $this->hasOne(MeasurementDetail::class);
    }

    public function planning()
    {
        return $this->hasOne(PlanningDetail::class);
    }

    public function planting()
    {
        return $this->hasOne(PlantingDetail::class);
    }

    public function fencing()
    {
        return $this->hasOne(FencingDetail::class);
    }

    public function observations()
    {
        return $this->hasMany(ObservationRecord::class);
    }

    public function relocations()
    {
        return $this->hasMany(RelocationRecord::class);
    }
}

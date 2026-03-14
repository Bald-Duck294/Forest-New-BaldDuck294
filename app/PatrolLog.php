<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatrolLog extends Model
{
    // use HasFactory;

    protected $fillable = [
        'patrol_session_id',
        'type',
        'payload',
        'lat',
        'lng',
        'notes',
        'company_id'
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(CompanyDetails::class);
    }

    public function patrolSession()
    {
        return $this->belongsTo(PatrolSession::class, 'patrol_session_id');
    }

    public function session()
    {
        return $this->belongsTo(PatrolSession::class, 'patrol_session_id');
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'attachable');
    }
}

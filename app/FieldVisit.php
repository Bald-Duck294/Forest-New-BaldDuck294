<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FieldVisit extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'from',
        'to',
        'purpose',
        'remark',
        'photos',
        'location'
    ];

    protected $casts = [
        'photos' => 'array',
        'location' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'attachable');
    }
}

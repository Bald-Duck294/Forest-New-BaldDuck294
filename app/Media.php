<?php

namespace App;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    // use HasFactory;

    protected $fillable = [
        'attachable_id',
        'attachable_type',
        'disk',
        'path',
        'mime',
        'size',
        'company_id'
    ];

    public function company()
    {
        return $this->belongsTo(CompanyDetails::class);
    }

    public function attachable()
    {
        return $this->morphTo(__FUNCTION__, 'attachable_type', 'attachable_id');
    }
}

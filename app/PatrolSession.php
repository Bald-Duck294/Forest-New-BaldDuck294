<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\CompanyDetails;
use App\SiteDetails;
use App\SiteAssign;

class PatrolSession extends Model
{
    protected $fillable = [
        'user_id',
        'site_id',
        'started_at',
        'ended_at',
        'start_lat',
        'start_lng',
        'end_lat',
        'end_lng',
        'path_geojson',
        'company_id',
        'type',
        'method',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'ended_at'     => 'datetime',
        'path_geojson' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(CompanyDetails::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function site()
    {
        return $this->belongsTo(SiteDetails::class, 'site_id');
    }

    public function logs()
    {
        return $this->hasMany(PatrolLog::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'attachable');
    }

    // App\PatrolSession.php

    public function getDisplaySiteAttribute()
    {
        if ($this->site) {
            return $this->site->name;
        }

        if ($this->user && in_array($this->user->role_id, [2, 7])) {
            $assigned = SiteAssign::where('user_id', $this->user_id)->first();

            if ($assigned) {
                // Decode JSON site_id field
                $siteIds = json_decode($assigned->site_id, true); // {"1":674,"2":731}

                if (is_array($siteIds)) {
                    $ids = array_values($siteIds); // [674, 731]

                    $siteNames = SiteDetails::whereIn('id', $ids)->pluck('name')->toArray();

                    return !empty($siteNames) ? implode(', ', $siteNames) : 'N/A';
                }
            }
        }

        return 'N/A';
    }



    // Each patrol belongs to a beat
    // public function beat()
    // {
    //     return $this->belongsTo(Beat::class, 'beat_id');
    // }
}

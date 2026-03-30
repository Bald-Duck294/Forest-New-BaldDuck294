<?php

namespace App;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class SiteGeofences extends Model
{
    use SoftDeletes;
    protected $table = "site_geofences";
    protected $fillable = [
        'name', 'center', 'radius', 'type', 'poly_coords', 'poly_lat_lng', 'site_id', 'client_id', 'company_id'
    ];

    protected $spatialFields = [
        'poly_coords',
    ];

    public $timestamps = false;




}
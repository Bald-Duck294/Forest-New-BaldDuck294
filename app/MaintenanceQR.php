<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceQR extends Model
{
    use SoftDeletes;

    protected $table = 'maintenance_qr';

    protected $fillable = ['name', 'description', 'qr_data', 'block_id', 'floor_id', 'building_id', 'site_id', 'client_id', 'company_id'];
}

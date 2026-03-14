<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRequest extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'requestor_id',
        // 'equipment_name',
        // 'model_number',
        // 'maintenance_location',
        'date_of_request',
        'date_issue_noticed',
        'issue_description',
        // 'error_codes',
        'priority_level',
        // 'previous_issues',
        // 'previous_maintenance',
        'attachments',
        'additional_notes',
        'preferred_date',
        'preferred_time',
        'type_of_maintenance',
        'supervisor_approval',
        'supervisor_id',
        'supervisor_name',
        'approval_date',
        'submission_date',
        'maintenance_qr_id',
        'block_id',
        'floor_id',
        'building_id',
        'site_id',
        'client_id',
        'company_id',
        'is_completed',
        'completed_by',
        'completion_remark'
    ];


    public function client()
    {
        return $this->belongsTo('App\ClientDetails', 'client_id');
    }

    public function site()
    {
        return $this->belongsTo('App\SiteDetails', 'site_id');
    }

    public function building()
    {
        return $this->belongsTo('App\Building', 'building_id');
    }

    public function floor()
    {
        return $this->belongsTo('App\Floor', 'floor_id');
    }

    public function block()
    {
        return $this->belongsTo('App\Block', 'block_id');
    }

    public function qr()
    {
        return $this->belongsTo('App\MaintenanceQR', 'maintenance_qr_id');
    }

    public function requested_by()
    {
        return $this->belongsTo('App\Users', 'requestor_id');
    }
}

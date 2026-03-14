<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveType extends Model
{
    use SoftDeletes;
    protected $table = "leave_types";
    protected $fillable = [

        'id', 'name', 'type', 'description', 'accural', 'accural_type', 'accural_date', 'accural_month', 'accural_days', 'reset', 'reset_type',
        'reset_date', 'reset_month', 'carry_forward', 'carry_frw_num', 'carry_frw_unit', 'carry_frw_limit', 'encashment', 'encash_num', 'encash_unit',
        'encash_limit', 'exclude_in_reports_if_no_entitlement', 'company_id', 'leave_per_month',

    ];

    // public  static function boot()
    // {
    //     parent::boot();
    //     // this event do not working, when delete a parent(country)
    //     static::deleting(function ($model) {
    //         dd('delete');
    //         $model->related_model()->get()->delete();
    //     });
    // }
}

    // public $timestamps = false;
// }

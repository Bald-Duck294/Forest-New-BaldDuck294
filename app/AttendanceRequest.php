<?php

namespace App;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    
    protected $table = "attendance_requests";
    protected $fillable = [
        'applicant_name','geo_name','attendance_type','status','date','time','timestamp'
    ];

    public $timestamps = false;

    public static function updateData($id,$data){
        DB::table('attendance_requests')->where('id', $id)->update($data);
     }

     
}
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DateTime;
use App\SiteAssign;
use App\Attendance;
use App\Users;
use App\SiteDetails;
use App\CompanyDetails;
use Log;
use App\Exports\LateShowExport;
use Maatwebsite\Excel\Excel;

class LateShowController extends Controller
{

    private $excel;
    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
    }
    //late show list of guard
    public function index()
    {

        $user = session('user');
        // dd($user);
        Log::info($user->name . ' view late show list, User_id: ' . $user->id);
        $cur_date = new DateTime();
        $date = $cur_date->format("Y-m-d");
        $absent = [];
        $sites = SiteAssign::where('user_id', $user->id)->first();

        $attendance = DB::table('attendance')->where('company_id', $user->company_id)->where('dateFormat', $date)->where('role_id', 3)->pluck('user_id')->toArray();
        $guards = DB::table('users')->where('company_id', $user->company_id)->where('role_id', 3)->whereNotIn('id', $attendance)->get();

        if ($user->role_id == '1') {
            $lateAttendance = Attendance::where('company_id', $user->company_id)->where('role_id', 3)->whereNotNull('lateTime')
                ->where('dateFormat', $date)->pluck('user_id')->toArray();
            $late = Users::rightjoin('attendance', function ($join) {
                $join->on('attendance.user_id', '=', 'users.id')->on('attendance.id', '=', DB::raw("(SELECT max(id) from attendance WHERE attendance.user_id = users.id)"));
            })->where('users.company_id', $user->company_id)
                ->where('users.role_id', 3)->whereIn('users.id', $lateAttendance)
                // ->selectRaw('users.*, users.id as id, site_assign.site_name')
                ->selectRaw('users.*, users.id as id, attendance.site_name, attendance.entry_time, attendance.entry_date_time, attendance.exit_date_time, attendance.exit_time, attendance.lateTime, attendance.time_difference as duration, attendance.photo as profile_pic')
                ->orderBy('name', 'ASC')->get();

            //  dd($late);
        } else if ($user->role_id == "2") {
            if ($sites) {
                $siteArray = json_decode($sites['site_id'], true);
                $lateAttendance = Attendance::whereIn('site_id', $siteArray)->whereNotNull('lateTime')->where('dateFormat', $date)->pluck('user_id')->toArray();

                $late = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                    ->leftJoin('attendance', 'users.id', '=', 'attendance.user_id')
                    ->whereNotNull('attendance.lateTime')
                    ->where('attendance.dateFormat', $date)
                    ->whereIn('users.id', $lateAttendance)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name,attendance.lateTime,site_assign.shift_time,attendance.entry_time')
                    ->orderBy('users.name', 'ASC')->groupBy('users.name')->get();
            }
        } else if ($user->role_id == "4") {
            $site = SiteDetails::where('client_id', $user->client_id)->pluck('id')->toArray();
            $lateAttendance = Attendance::whereIn('site_id', $site)->whereNotNull('lateTime')->where('dateFormat', $date)->pluck('user_id')->toArray();

            $late = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                ->leftJoin('attendance', 'users.id', '=', 'attendance.user_id')
                ->whereNotNull('attendance.lateTime')
                ->where('attendance.dateFormat', $date)
                ->whereIn('users.id', $lateAttendance)
                ->selectRaw('users.*, users.id as id, site_assign.site_name,attendance.lateTime,site_assign.shift_time,attendance.entry_time')
                ->orderBy('users.name', 'ASC')->groupBy('users.name')->get();
        } else if ($user->role_id == "7") {
            if ($sites) {
                // dd($sites);
                $site= json_decode($sites['site_id'], true);
                $siteArray = SiteDetails::whereIn('client_id', $site)->pluck('id')->toArray();
                $userArray = SiteAssign::whereIn('site_id', $siteArray)->distinct('user_id')->pluck('user_id')->toArray();
                $lateAttendance = Attendance::whereIn('user_id', $userArray)->where('company_id', $user->company_id)->where('role_id', 3)->whereNotNull('lateTime')
                    ->where('dateFormat', $date)->pluck('user_id')->toArray();
              
                
                $late = Users::rightjoin('attendance', function ($join) {
                    $join->on('attendance.user_id', '=', 'users.id')->on('attendance.id', '=', DB::raw("(SELECT max(id) from attendance WHERE attendance.user_id = users.id)"));
                })->where('users.company_id', $user->company_id)
                    ->where('users.role_id', 3)->whereIn('users.id', $lateAttendance)
                    // ->selectRaw('users.*, users.id as id, site_assign.site_name')
                    ->selectRaw('users.*, users.id as id, attendance.site_name, attendance.entry_time, attendance.entry_date_time, attendance.exit_date_time, attendance.exit_time, attendance.lateTime, attendance.time_difference as duration, attendance.photo as profile_pic')
                    ->orderBy('name', 'ASC')->get();
            }
        }
        return view('lateshowlist')->with('late', $late);
    }

    public function export()
    {
        $user = session('user');

        //dd($flag);
        //$clients = ClientDetails::where('company_id', $user->company_id)->get();
        $cur_date = new DateTime();
        $date = $cur_date->format("Y-m-d");
        $attendance = [];
        $present = [];

        $absent = [];
        $sites = SiteAssign::where('user_id', $user->id)->first();

        $attendance = DB::table('attendance')->where('company_id', $user->company_id)->where('dateFormat', $date)->where('role_id', 3)->pluck('user_id')->toArray();
        $guards = DB::table('users')->where('company_id', $user->company_id)->where('role_id', 3)->whereNotIn('id', $attendance)->get();

        if ($user->role_id == '1') {
            $lateAttendance = Attendance::where('company_id', $user->company_id)->where('role_id', 3)->whereNotNull('lateTime')
                ->where('dateFormat', $date)->pluck('user_id')->toArray();
            $late = Users::rightjoin('attendance', function ($join) {
                $join->on('attendance.user_id', '=', 'users.id')->on('attendance.id', '=', DB::raw("(SELECT max(id) from attendance WHERE attendance.user_id = users.id)"));
            })->where('users.company_id', $user->company_id)
                ->where('users.role_id', 3)->whereIn('users.id', $lateAttendance)
                // ->selectRaw('users.*, users.id as id, site_assign.site_name')
                ->selectRaw('users.*, users.id as id, attendance.site_name, attendance.entry_time, attendance.entry_date_time, attendance.exit_date_time, attendance.exit_time, attendance.lateTime, attendance.time_difference as duration, attendance.photo as profile_pic')
                ->orderBy('name', 'ASC')->get();
            // $lateAttendance = Attendance::where('company_id', $user->company_id)
            //     ->where('role_id', 3)->whereNotNull('lateTime')
            //     ->where('dateFormat', $date)->pluck('user_id')->toArray();
            // // print_r($lateAttendance);exit;
            // $late = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
            //     // ->leftJoin('attendance', 'users.id', '=', 'attendance.user_id')
            //     ->leftJoin('attendance', function($query){
            //         $query->on('users.id', '=', 'attendance.user_id')  
            //         ->where('attendance.user_id','=',DB::raw("(select max(user_id) from attendance where attendance.user_id = users.id )"));
            //     })
            //     ->whereNotNull('attendance.lateTime')
            //     ->where('attendance.dateFormat', $date)
            //     ->where('users.company_id', $user->company_id)
            //     ->where('users.role_id', 3)->whereIn('users.id', $lateAttendance)
            //     ->selectRaw('users.*, users.id as id, site_assign.site_name,attendance.lateTime,site_assign.shift_time,attendance.entry_time')
            //     ->orderBy('name', 'ASC')->latest('attendance.id')->groupBy('name')->get();
            //  dd($late);
        } else if ($user->role_id == "2") {
            if ($sites) {
                $siteArray = json_decode($sites['site_id'], true);
                $lateAttendance = Attendance::whereIn('site_id', $siteArray)->whereNotNull('lateTime')->where('dateFormat', $date)->pluck('user_id')->toArray();

                $late = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                    ->leftJoin('attendance', 'users.id', '=', 'attendance.user_id')
                    ->whereNotNull('attendance.lateTime')
                    ->where('attendance.dateFormat', $date)
                    ->whereIn('users.id', $lateAttendance)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name,attendance.lateTime,site_assign.shift_time,attendance.entry_time')
                    ->orderBy('users.name', 'ASC')->groupBy('users.name')->get();
            }
        } else if ($user->role_id == "4") {
            $site = SiteDetails::where('client_id', $user->client_id)->pluck('id')->toArray();
            $lateAttendance = Attendance::whereIn('site_id', $site)->whereNotNull('lateTime')->where('dateFormat', $date)->pluck('user_id')->toArray();

            $late = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                ->leftJoin('attendance', 'users.id', '=', 'attendance.user_id')
                ->whereNotNull('attendance.lateTime')
                ->where('attendance.dateFormat', $date)
                ->whereIn('users.id', $lateAttendance)
                ->selectRaw('users.*, users.id as id, site_assign.site_name,attendance.lateTime,site_assign.shift_time,attendance.entry_time')
                ->orderBy('users.name', 'ASC')->groupBy('users.name')->get();
        }
        else if($user->role_id== "7"){
            if($sites) {
                $siteArray = json_decode($sites['site_id'], true);
                $lateAttendance = Attendance::whereIn('site_id', $siteArray)->whereNotNull('lateTime')->where('dateFormat', $date)->pluck('user_id')->toArray();

                $late = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                    ->leftJoin('attendance', 'users.id', '=', 'attendance.user_id')
                    ->whereNotNull('attendance.lateTime')
                    ->where('attendance.dateFormat', $date)
                    ->whereIn('users.id', $lateAttendance)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name,attendance.lateTime,site_assign.shift_time,attendance.entry_time')
                    ->orderBy('users.name', 'ASC')->groupBy('users.name')->get();
            }
        }
        $companyName = CompanyDetails::where('id', $user->company_id)->pluck('name');
        $companyName = $companyName['0'];
        // dd($late[0]['shift_time']);
        return $this->excel->download(new LateShowExport($late, $companyName), 'late.xlsx');
    }
}

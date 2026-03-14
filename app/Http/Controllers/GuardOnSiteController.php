<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Attendance;
use App\Users;
use App\SiteAssign;
use App\SiteGeofences;
use App\GuardLiveLocationData;
use App\CompanyDetails;
use App\SiteDetails;
use DateTime;
use Log;
use App\ActivityLog;
use App\Exports\CheckInExport;
use Maatwebsite\Excel\Excel;


class GuardOnSiteController extends Controller
{

    private $excel;
    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
    }



    // present guard list
    public function index($flag)
    {

        //dd($flag);
        $user = session('user');
        Log::info($user->name . ' view checkIn List, User_id: ' . $user->id);

        $cur_date = new DateTime();
        $date = $cur_date->format("Y-m-d");
        $attendance = [];
        $present = [];
        $sites = SiteAssign::where('user_id', $user->id)->first();
        if ($flag == 'checkIn') {
            if ($user->role_id == "2") {
                if ($sites) {
                    $siteArray = json_decode($sites['site_id'], true);

                    $attendance = Attendance::whereIn('site_id', $siteArray)->where('dateFormat', $date)->pluck('user_id')->toArray();
                    $present = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                        ->leftJoin('attendance', 'users.id', '=', 'attendance.user_id')
                        ->where('attendance.dateFormat', $date)
                        ->whereIn('users.id', $attendance)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name,attendance.geo_name,attendance.entry_time,attendance.dateFormat,attendance.exit_time, attendance.location')
                        ->distinct('attendance.user_id')
                        ->orderBy('attendance.id', 'desc')
                        ->get();

                    return view('guardonsitelist')->with('present', $present)->with('flag', $flag);
                }
            } elseif ($user->role_id == 7) {
                if ($sites) {
                    // $siteArray = json_decode($sites['site_id'], true);
                    // $clientArray = json_decode($sites['site_id'], true);

                    // $userArray = SiteAssign::whereIn('client_id', $clientArray)->pluck('user_id')->toArray();
                    $siteAssigned = SiteAssign::where('user_id', $user->id)->first();
                    if ($siteAssigned) {
                        $clientIds = json_decode($siteAssigned->site_id, true);
                        $siteArray = SiteDetails::whereIn('client_id', $clientIds)->pluck('id')->toArray();
                        $siteUsers = SiteAssign::where('role_id', 2)->where(function ($query) use ($siteArray) {
                            foreach ($siteArray as $siteId) {
                                $query->orWhereRaw('JSON_CONTAINS(site_id, ?)', [json_encode($siteId)]);
                            }
                        })->pluck('user_id')->toArray();

                        $siteUsers2 = SiteAssign::where('role_id', 3)->whereIn('client_id', $clientIds)->where('company_id', $user->company_id)->pluck('user_id')->toArray();
                        $userArray = array_merge($siteUsers, $siteUsers2);
                    } else {
                        $userArray = [];
                    }

                    $attendance = Attendance::whereIn('user_id', $userArray)->where('dateFormat', $date)->pluck('user_id')->toArray();
                    $present = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                        ->leftJoin('attendance', 'users.id', '=', 'attendance.user_id')
                        ->where('attendance.dateFormat', $date)
                        ->whereIn('users.id', $attendance)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name,attendance.geo_name,attendance.entry_time,attendance.dateFormat,attendance.exit_time, attendance.location')
                        ->distinct('attendance.user_id')
                        ->orderBy('attendance.id', 'desc')
                        ->get();

                    return view('guardonsitelist')->with('present', $present)->with('flag', $flag);
                }
            } else {
                $attendance = Attendance::where('company_id', $user->company_id)
                    //->where('role_id', 3)
                    ->where('dateFormat', $date)
                    ->pluck('user_id')
                    ->toArray();

                //DB::enableQueryLog();
                $present = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                    ->leftJoin('attendance', 'users.id', '=', 'attendance.user_id')
                    ->where('attendance.dateFormat', $date)
                    ->where('users.company_id', $user->company_id)
                    //->where('users.role_id', 3)
                    ->whereIn('users.id', $attendance)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name,attendance.geo_name,attendance.entry_time,attendance.dateFormat,attendance.exit_time, attendance.location')
                    ->distinct('attendance.user_id')
                    ->orderBy('attendance.id', 'desc')
                    ->get();

                return view('guardonsitelist')->with('present', $present)->with('flag', $flag);
            }
        } else {

            if ($user->role_id == "2") {
                if ($sites) {

                    $siteArray = json_decode($sites['site_id'], true);
                    $attendance = Attendance::whereIn('site_id', $siteArray)->where('dateFormat', $date)->pluck('user_id')->toArray();
                    $present = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                        ->leftJoin('attendance', 'users.id', '=', 'attendance.user_id')
                        ->where('attendance.dateFormat', $date)
                        ->whereIn('users.id', $attendance)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name,attendance.geo_name,attendance.entry_time,attendance.dateFormat,attendance.exit_time, attendance.location')
                        ->distinct('attendance.user_id')
                        ->orderBy('attendance.id', 'desc')
                        ->get();

                    return view('guardonsitelist')->with('present', $present)->with('flag', $flag);
                }
            } else if ($user->role_id == "7") {
                if ($sites) {
                    // dd($sites);
                    $site = json_decode($sites['site_id'], true);
                    $siteArray = SiteDetails::whereIn('client_id', $site)->pluck('id')->toArray();
                    $userArray = SiteAssign::whereIn('site_id', $siteArray)->distinct('user_id')->pluck('user_id')->toArray();
                    //dd($userArray);
                    //$attendance = Attendance::whereIn('site_id', $siteArray)->where('dateFormat', $date)->where('role_id', 3)->pluck('user_id')->toArray();
                    $attendance = Attendance::where('company_id', $user->company_id)
                        ->where('role_id', 3)
                        ->where('dateFormat', $date)
                        ->whereIn('user_id', $userArray)
                        ->pluck('user_id')
                        ->toArray();
                    // dd($attendance);

                    // $present = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                    //     ->leftJoin('attendance', 'users.id', '=', 'attendance.user_id')
                    //     ->where('users.company_id', $user->company_id)
                    //     //->whereNotIN('users.id', $lateAttendance)
                    //     //->where('users.role_id', 3)
                    //     ->whereIn('users.id', $attendance)
                    //     ->selectRaw('users.*, users.id as id, site_assign.site_name')
                    //     // ->orderBy('attendance.id', 'desc')
                    //     ->get();


                    $present = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                        ->leftJoin('attendance', 'users.id', '=', 'attendance.user_id')
                        ->where('attendance.dateFormat', $date)
                        ->whereIn('users.id', $attendance)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name,attendance.geo_name,attendance.entry_time,attendance.dateFormat,attendance.exit_time, attendance.location')
                        ->distinct('attendance.user_id')
                        ->orderBy('attendance.id', 'desc')
                        ->get();

                    // dd($present);

                    return view('guardonsitelist')->with('present', $present)->with('flag', $flag);
                }
            } else {
                // $attendance = Attendance::where('company_id', $user->company_id)
                //     ->where('role_id', 3)
                //     ->where('dateFormat', $date)
                //     ->pluck('user_id')
                //     ->toArray();

                // // DB::enableQueryLog();
                // $present = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                //     ->leftJoin('attendance', 'users.id', '=', 'attendance.user_id')
                //     ->where('attendance.dateFormat', $date)
                //     ->where('users.company_id', $user->company_id)
                //     ->where('users.role_id', 3)->whereIn('users.id', $attendance)
                //     ->selectRaw('users.*, users.id as id, site_assign.site_name,attendance.geo_name,attendance.entry_time,attendance.dateFormat,attendance.exit_time')
                //     ->distinct('attendance.user_id')
                //     ->orderBy('attendance.id', 'desc')
                //     ->get();

                $attendance = Attendance::where('company_id', $user->company_id)
                    ->where('role_id', 3)
                    ->where('dateFormat', $date)
                    ->whereNull('lateTime')
                    //->whereNull('exit_time')
                    ->distinct('user_id')
                    ->pluck('user_id')
                    ->toArray();

                //DB::enableQueryLog();
                $present = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                    ->leftJoin('attendance', 'users.id', '=', 'attendance.user_id')
                    ->where('attendance.dateFormat', $date)
                    ->where('users.company_id', $user->company_id)
                    ->where('users.role_id', 3)->whereIn('users.id', $attendance)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name,attendance.geo_name,attendance.entry_time,attendance.dateFormat,attendance.exit_time , attendance.location')
                    ->distinct('attendance.user_id')
                    ->orderBy('attendance.id', 'desc')
                    ->get();

                return view('guardonsitelist')->with('present', $present)->with('flag', $flag);
            }
        }
    }

    // export function for client 
    public function export($flag)
    {
        $user = session('user');

        //dd($flag);
        //$clients = ClientDetails::where('company_id', $user->company_id)->get();
        $cur_date = new DateTime();
        $date = $cur_date->format("Y-m-d");
        $attendance = [];
        $present = [];
        $sites = SiteAssign::where('user_id', $user->id)->first();
        if ($flag == 'checkIn') {
            if ($user->role_id == "2") {
                if ($sites) {
                    $siteArray = json_decode($sites['site_id'], true);
                    $attendance = Attendance::whereIn('site_id', $siteArray)->where('dateFormat', $date)->pluck('user_id')->toArray();
                    $present = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                        ->leftJoin('attendance', 'users.id', '=', 'attendance.user_id')
                        ->where('attendance.dateFormat', $date)
                        ->whereIn('users.id', $attendance)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name,attendance.geo_name,attendance.entry_time,attendance.dateFormat,attendance.exit_time,attendance.location')
                        ->distinct('attendance.user_id')
                        ->orderBy('attendance.id', 'desc')
                        ->get();
                }
            } else {
                $attendance = Attendance::where('company_id', $user->company_id)
                    //->where('role_id', 3)
                    ->where('dateFormat', $date)
                    ->pluck('user_id')
                    ->toArray();

                //DB::enableQueryLog();
                $present = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                    ->leftJoin('attendance', 'users.id', '=', 'attendance.user_id')
                    ->where('attendance.dateFormat', $date)
                    ->where('users.company_id', $user->company_id)
                    //->where('users.role_id', 3)
                    ->whereIn('users.id', $attendance)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name,attendance.geo_name,attendance.entry_time,attendance.dateFormat,attendance.exit_time,attendance.location')
                    ->distinct('attendance.user_id')
                    ->orderBy('attendance.id', 'desc')
                    ->get();
            }

            //return view('guardonsitelist')->with('present', $present);
        } else {

            if ($user->role_id == "2") {
                if ($sites) {

                    $siteArray = json_decode($sites['site_id'], true);
                    $attendance = Attendance::whereIn('site_id', $siteArray)->where('dateFormat', $date)->pluck('user_id')->toArray();
                    $present = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                        ->leftJoin('attendance', 'users.id', '=', 'attendance.user_id')
                        ->where('attendance.dateFormat', $date)
                        ->whereIn('users.id', $attendance)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name,attendance.geo_name,attendance.entry_time,attendance.dateFormat,attendance.exit_time, attendance.location')
                        ->distinct('attendance.user_id')
                        ->orderBy('attendance.id', 'desc')
                        ->get();
                }
            } else {

                // $attendance = Attendance::where('company_id', $user->company_id)
                //     ->where('role_id', 3)
                //     ->where('dateFormat', $date)
                //     ->pluck('user_id')
                //     ->toArray();
                // // DB::enableQueryLog();
                // $present = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                //     ->leftJoin('attendance', 'users.id', '=', 'attendance.user_id')
                //     ->where('attendance.dateFormat', $date)
                //     ->where('users.company_id', $user->company_id)
                //     ->where('users.role_id', 3)->whereIn('users.id', $attendance)
                //     ->selectRaw('users.*, users.id as id, site_assign.site_name,attendance.geo_name,attendance.entry_time,attendance.dateFormat,attendance.exit_time')
                //     ->distinct('attendance.user_id')
                //     ->orderBy('attendance.id', 'desc')
                //     ->get();

                $attendance = Attendance::where('company_id', $user->company_id)
                    ->where('role_id', 3)
                    ->where('dateFormat', $date)
                    ->whereNull('lateTime')
                    //->whereNull('exit_time')
                    ->distinct('user_id')
                    ->pluck('user_id')
                    ->toArray();

                //DB::enableQueryLog();

                $present = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                    ->leftJoin('attendance', 'users.id', '=', 'attendance.user_id')
                    ->where('attendance.dateFormat', $date)
                    ->where('users.company_id', $user->company_id)
                    ->where('users.role_id', 3)->whereIn('users.id', $attendance)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name,attendance.geo_name,attendance.entry_time,attendance.dateFormat,attendance.exit_time , attendance.location')
                    ->distinct('attendance.user_id')
                    ->orderBy('attendance.id', 'desc')
                    ->get();
            }
        }
        $companyName = CompanyDetails::where('id', $user->company_id)->pluck('name');
        $companyName = $companyName['0'];
        //dd($companyName);
        return $this->excel->download(new CheckInExport($present, $companyName, $flag), 'checkIn.xlsx');
    }
}

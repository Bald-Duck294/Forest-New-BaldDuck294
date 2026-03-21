<?php

namespace App\Http\Controllers;

use \Illuminate\Foundation\Auth\AuthenticatesUsers;

use Illuminate\Support\Facades\Session;
use Auth;
use Redirect;
use App\AdminDetails;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\AdminLoginModel;
use App\Login;
use App\Users;
use App\AttendanceRequest;
use App\Notifications;
use App\ClientDetails;
use App\CompanyDetails;
use App\SiteAssign;
use App\Attendance;
use App\SiteDetails;
use App\SiteGeofences;
use Log;
use App\GuardTourLog;
use App\ActivityLog;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Hash;
use Alert;
use carbon\carbon;


class LoginController extends Controller
{

    //login
    public function index(Request $request)
    {
        $password = $request->password;
        $user = Users::where('contact', $request->contact)->first();

        if ($user) {

            if (Hash::check($password, $user->password)) {

                if ($user->role_id == 8) {

                    $request->session()->put('user', $user);

                    Log::info($user->name . ' logged in as Global Superadmin');

                    return redirect()->route('global.dashboard');
                }


                $company = CompanyDetails::where('id', $user->company_id)->first();
                $features = [];
                $incCheck = json_decode($company->features, true);
                if ($incCheck) {
                    foreach ($incCheck as $checked) {
                        if ($checked['checked']) {
                            $features[] = $checked['value'];
                        }
                    }
                }
                // dump("features" , $features );
                session()->put('features', $features);
                session()->put('company', $company);
                $dashboardChecklist = [];
                $incChecklist = json_decode($company->dashboardChecklist, true);
                if ($incChecklist) {
                    foreach ($incChecklist as $checked) {
                        if ($checked['checked']) {
                            $dashboardChecklist[] = $checked['value'];
                        }
                    }
                }
                session()->put('dashboardChecklist', $dashboardChecklist);
                $checkusers = DB::table('users')->select('id')->get();
                $cur_date = new DateTime();
                $date = $cur_date->format("Y-m-d");
                $attendance = [];
                $present = [];
                $absent = [];
                $late = [];
                $sites = SiteAssign::where('user_id', $user->id)->first();
                $attendance = DB::table('attendance')->where('company_id', $user->company_id)->where('dateFormat', $date)->where('role_id', 3)->pluck('user_id')->toArray();
                $request->session()->put('user', $user);
                $users = "";
                foreach ($checkusers as $users) {
                    $users = $users->id;
                }
                Log::info($user->name . ' enter in welcome page, User_id: ' . $user->id);
                if ($company->is_forest) {
                    return redirect()->route('forest.dashboard');
                }
                if ($user->role_id == '1') {
                    $userData = Users::where('id', '=', $user->id)->first();
                    $sites = DB::table('site_details')
                        ->where('company_id', $user->company_id)
                        ->count();

                    $CheckInCount = Attendance::where('company_id', $user->company_id)
                        ->where('dateFormat', $date)
                        ->pluck('user_id')
                        ->toArray();
                    $guardonsite = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                        ->where('users.company_id', $user->company_id)
                        ->whereIn('users.id', $CheckInCount)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name')
                        ->select('count(*) as allcount')
                        ->count();
                    $incidence = DB::table('incidence_details')
                        ->where('company_id', $user->company_id)
                        ->whereNotIn('statusFlag', [1, 2])
                        ->select('count(*) as allcount')
                        ->count();
                    $notifications = Notifications::where('company_id', $user->company_id)
                        ->where('action', '=', '0')
                        ->select('count(*) as allcount')
                        ->count();
                    $clients = ClientDetails::where('company_id', $user->company_id)
                        ->select('count(*) as allcount')
                        ->count();
                    $geofences = SiteGeofences::where('company_id', $user->company_id)
                        ->select('count(*) as allcount')
                        ->count();
                    $supervisor = DB::table('users')
                        ->where('company_id', $user->company_id)
                        ->where('role_id', '=', '2')
                        ->select('count(*) as allcount')
                        ->count();
                    $attendance = Attendance::where('company_id', $user->company_id)
                        ->where('dateFormat', $date)
                        ->distinct('user_id')
                        ->pluck('user_id')
                        ->toArray();
                    $lateAttendance = Attendance::where('company_id', $user->company_id)
                        ->where('role_id', 3)
                        ->whereNotNull('lateTime')
                        ->distinct('user_id')
                        ->where('dateFormat', $date)
                        ->pluck('user_id')->toArray();
                    $present = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                        ->where('users.company_id', $user->company_id)
                        //->whereNotIN('users.id', $lateAttendance)
                        //->where('users.role_id', 3)
                        ->whereIn('users.id', $attendance)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name')
                        ->select('count(*) as allcount')
                        ->count();
                    $Tpresent = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                        ->where('users.company_id', $user->company_id)
                        ->whereNotIN('users.id', $lateAttendance)
                        ->whereIn('users.id', $attendance)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name')
                        ->select('count(*) as allcount')
                        ->count();
                    $absent = Users::leftjoin('site_assign', 'users.id', '=', 'user_id')
                        ->where('users.company_id', $user->company_id)
                        ->where('users.role_id', 3)->whereNotIn('users.id', $CheckInCount)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name')
                        ->select('count(*) as allcount')
                        ->count();
                    $late = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                        ->where('users.company_id', $user->company_id)
                        ->where('users.role_id', 3)->whereIn('users.id', $lateAttendance)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name')
                        ->select('count(*) as allcount')
                        ->count();
                    $emergencyAttendance = AttendanceRequest::where([['company_id', $user->company_id], ['dateFormat', $date]])->select('count(*) as allcount')->count();
                    $totalGuards = $Tpresent + $late + $absent;
                    $datetime2 = new DateTime($date);
                    $date1 = date('Y-m-d', strtotime('-7 day', strtotime($date)));
                    $datetime1 = new DateTime($date1, new DateTimeZone('Asia/Kolkata'));
                    $format = 'd M';
                    $interval = $datetime1->diff($datetime2);
                    $daysCount = (int) $interval->format('%a');
                    $daysCount = $daysCount + 1;
                    $m = date("m");
                    $de = date("d");
                    $y = date("Y");
                    $dateArray = array();
                    $d = [];
                    $datas = [];
                    $pendingWithSupervisor = [];
                    $resolve = [];
                    $ignore = [];
                    $attendances = [];
                    $weeklyAbsent = [];
                    $weeklyPresent = [];
                    $weeklyLate = [];
                    $siteDetails = SiteDetails::where('company_id', $user->company_id)->get();
                    $groupedData = GuardTourLog::select('site_id', 'date', DB::raw('count(*) as record_count'))->where('company_id', $user->company_id)->whereBetween('date', [$date1, $date])->groupBy('site_id', 'date')->get();          // Formatting the data
                    $formattedData = [];
                    foreach ($groupedData as $data) {
                        $siteId = $data->site_id;
                        $dateFormat = $data->date;
                        $recordCount = $data->record_count;

                        if (!isset($formattedData[$siteId])) {
                            $formattedData[$siteId] = [];
                        }
                        $formattedData[$siteId][$dateFormat] = $recordCount;
                    }
                    for ($i = 0; $i <= $daysCount - 1; $i++) {
                        $dateArray[] = '' . date($format, mktime(0, 0, 0, $m, ($de - $i), $y)) . '';
                        $d[] = '' . date('Y-m-d', mktime(0, 0, 0, $m, ($de - $i), $y)) . '';
                        $datas[] = DB::table('incidence_details')
                            ->where('company_id', $user->company_id)
                            ->where('dateFormat', $d[$i])
                            ->count();
                        $pendingWithSupervisor[] = DB::table('incidence_details')
                            ->where('company_id', $user->company_id)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 0)
                            ->count();
                        $resolve[] = DB::table('incidence_details')
                            ->where('company_id', $user->company_id)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 1)
                            ->count();
                        $ignore[] = DB::table('incidence_details')
                            ->where('company_id', $user->company_id)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 2)
                            ->count();
                        $escalateToAdmin[] = DB::table('incidence_details')
                            ->where('company_id', $user->company_id)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 3)
                            ->count();
                        $pendingAdmin[] = DB::table('incidence_details')
                            ->where('company_id', $user->company_id)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 4)
                            ->count();
                        $escalateToClient[] = DB::table('incidence_details')
                            ->where('company_id', $user->company_id)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 5)
                            ->count();

                        $attendances = Attendance::where('company_id', $user->company_id)
                            ->where('role_id', 3)
                            ->where('dateFormat', $d[$i])
                            ->pluck('user_id')
                            ->toArray();
                        $weeklyAbsent[] = Users::where('company_id', $user->company_id)
                            ->where('role_id', 3)
                            ->whereNotIn('id', $attendances)
                            ->count();
                        $weeklyPresent[] = Attendance::where('company_id', $user->company_id)
                            ->where('role_id', 3)
                            ->where('dateFormat', $d[$i])
                            ->where('lateTime', NULL)
                            ->distinct('user_id')
                            ->count();
                        $weeklyLate[] = Attendance::where('company_id', $user->company_id)
                            ->where('role_id', 3)
                            ->where('dateFormat', $d[$i])
                            ->where('lateTime', '!=', NULL)
                            ->distinct('user_id')
                            ->count();
                    }
                    $data = $dateArray;
                    $todayPending = DB::table('incidence_details')
                        ->where('company_id', $user->company_id)->where('dateFormat', $date)
                        ->where('statusFlag', 0)->count();
                    $todayResolved = DB::table('incidence_details')
                        ->where('company_id', $user->company_id)->where('dateFormat', $date)
                        ->where('statusFlag', 1)->count();
                    $todayIgnored = DB::table('incidence_details')
                        ->where('company_id', $user->company_id)->where('dateFormat', $date)
                        ->where('statusFlag', 2)->count();

                    if ($company->end_date == null) {
                        $sessionExpire['is_expired'] = 1;
                    } else if ($date > $company->end_date) {
                        $sessionExpire['is_expired'] = true;
                    } else {
                        $todaysDate_ts = strtotime($date);
                        $end_dates = strtotime($company->end_date);
                        $diff = $end_dates - $todaysDate_ts;
                        $daysLeftToExpire = $diff / 86400;
                        if ($daysLeftToExpire < 15) {
                            $sessionExpire['is_expired'] = false;
                            $sessionExpire['expire_date'] = $company->end_date;
                            $sessionExpire['is_expiring'] = $daysLeftToExpire;
                        } else {
                            $sessionExpire['is_expired'] = true;
                            $sessionExpire['expire_date'] = $company->end_date;
                        }
                    }
                    session()->put('sessionExpire', $sessionExpire);

                    $guardLog = ActivityLog::where('company_id', $user->company_id)->orderBy('id', 'desc')
                        ->take(5)->get();
                    $viewAllLog = ActivityLog::where('company_id', $user->company_id)->orderBy('id', 'desc')
                        ->get();

                    return view('welcome')->with('viewAllLog', $viewAllLog)
                        ->with('geofences', $geofences)
                        ->with('guardLog', $guardLog)
                        ->with('formattedData', $formattedData)
                        ->with('siteDetails', $siteDetails)
                        ->with('sites', $sites)
                        ->with('guards', $totalGuards)
                        ->with('guardonsite', $guardonsite)
                        ->with('incidence', $incidence)
                        ->with('noshow', $absent)
                        ->with('notifications', $notifications)
                        ->with('clients', $clients)
                        ->with('users', $user)
                        ->with('supervisor', $supervisor)
                        ->with('userData', $userData)
                        ->with('late', $late)
                        ->with('emergencyAttendance', $emergencyAttendance)->with('data', $data)->with('u', $d)->with('pendingWithSupervisor', $pendingWithSupervisor)->with('resolve', $resolve)->with('ignore', $ignore)->with('escalateToAdmin', $escalateToAdmin)->with('pendingAdmin', $pendingAdmin)->with('escalateToClient', $escalateToClient)->with('user', $user)->with('todayPending', $todayPending)->with('todayResolved', $todayResolved)->with('todayIgnored', $todayIgnored)->with('date', $date)->with('todayPresent', $present)->with('todayLate', $late)->with('todayAbsent', $absent)->with('weeklyPresent', $weeklyPresent)->with('weeklyAbsent', $weeklyAbsent)->with('weeklyLate', $weeklyLate);
                } else if ($user->role_id == '2') {

                    $site = SiteAssign::where('user_id', $user->id)->first();
                    $siteArray = json_decode($site['site_id'], true);
                    $siteUserIds = SiteAssign::where('company_id', $user->company_id)
                        ->whereIn('site_id', $siteArray)
                        ->where('role_id', 3)
                        ->pluck('user_id')
                        ->toArray();
                    $sites = SiteAssign::where('user_id', $user->id)->first();
                    if ($sites)
                        $siteArray = json_decode($sites['site_id'], true);
                    else
                        $siteArray = [];
                    $sitess = DB::table('site_details')->whereIn('id', $siteArray)->select('count(*) as allcount')->count();
                    $userArray = SiteAssign::whereIn('site_id', $siteArray)->where('role_id', 3)->pluck('user_id')->toArray();
                    $incidence = DB::table('incidence_details')
                        ->whereIn('site_id', $siteArray)
                        ->whereNotIn("statusFlag", [1, 2])
                        ->select('count(*) as allcount')
                        ->count();
                    $datetime2 = new DateTime($date);
                    $date1 = date('Y-m-d', strtotime('-7 day', strtotime($date)));
                    $datetime1 = new DateTime($date1, new DateTimeZone('Asia/Kolkata'));
                    $format = 'd M';
                    $interval = $datetime1->diff($datetime2);
                    $daysCount = (int) $interval->format('%a');
                    $daysCount = $daysCount + 1;
                    $m = date("m");
                    $de = date("d");
                    $y = date("Y");
                    $dateArray = array();
                    $d = [];
                    $pendingWithSupervisor = [];
                    $resolve = [];
                    $ignore = [];
                    $attendances = [];
                    $weeklyAbsent = [];
                    $weeklyPresent = [];
                    $weeklyLate = [];
                    $siteDetails = SiteDetails::where('company_id', $user->company_id)->get();
                    $groupedData = GuardTourLog::select('site_id', 'date', DB::raw('count(*) as record_count'))
                        ->where('company_id', $user->company_id)->whereBetween('date', [$date1, $date])
                        ->groupBy('site_id', 'date')->get();          // Formatting the data

                    $formattedData = [];
                    foreach ($groupedData as $data) {
                        $siteId = $data->site_id;
                        $dateFormat = $data->date;
                        $recordCount = $data->record_count;
                        if (!isset($formattedData[$siteId])) {
                            $formattedData[$siteId] = [];
                        }
                        $formattedData[$siteId][$dateFormat] = $recordCount;
                    }

                    for ($i = 0; $i <= $daysCount - 1; $i++) {
                        $dateArray[] = '' . date($format, mktime(0, 0, 0, $m, ($de - $i), $y)) . '';
                        $d[] = '' . date('Y-m-d', mktime(0, 0, 0, $m, ($de - $i), $y)) . '';

                        $pendingWithSupervisor[] = DB::table('incidence_details')
                            ->whereIn('site_id', $siteArray)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 0)
                            ->count();
                        $resolve[] = DB::table('incidence_details')
                            ->whereIn('site_id', $siteArray)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 1)
                            ->count();
                        $ignore[] = DB::table('incidence_details')
                            ->whereIn('site_id', $siteArray)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 2)
                            ->count();
                        $escalateToAdmin[] = DB::table('incidence_details')
                            ->whereIn('site_id', $siteArray)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 3)
                            ->count();
                        $pendingAdmin[] = DB::table('incidence_details')
                            ->whereIn('site_id', $siteArray)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 4)
                            ->count();
                        $escalateToClient[] = DB::table('incidence_details')
                            ->whereIn('site_id', $siteArray)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 5)
                            ->count();
                        $attendances = Attendance::whereIn('user_id', $siteUserIds)
                            ->where('role_id', 3)
                            ->where('dateFormat', $d[$i])
                            ->pluck('user_id')
                            ->toArray();
                        $weeklyAbsent[] = SiteAssign::whereIn('site_id', $siteArray)
                            ->whereNotIn('user_id', $attendances)
                            ->where('role_id', 3)
                            ->pluck('user_id')
                            ->count();
                        $weeklyPresent[] = Attendance::whereIn('site_id', $siteArray)
                            ->where('role_id', 3)
                            ->where('dateFormat', $d[$i])
                            ->distinct('user_id')
                            ->count();
                        $weeklyLate[] = Attendance::whereIn('site_id', $siteArray)
                            ->where('role_id', 3)
                            ->where('dateFormat', $d[$i])
                            ->where('lateTime', '!=', NULL)
                            ->distinct('user_id')
                            ->count();
                    }
                    $data = $dateArray;

                    $todayPending = DB::table('incidence_details')
                        ->whereIn('site_id', $siteArray)->where('dateFormat', $date)
                        ->where('statusFlag', 0)->count();
                    $todayResolved = DB::table('incidence_details')
                        ->whereIn('site_id', $siteArray)->where('dateFormat', $date)
                        ->where('statusFlag', 1)->count();
                    $todayIgnored = DB::table('incidence_details')
                        ->whereIn('site_id', $siteArray)->where('dateFormat', $date)
                        ->where('statusFlag', 2)->count();
                    $attendance = Attendance::whereIn('site_id', $siteArray)
                        ->where('dateFormat', $date)
                        ->pluck('user_id')
                        ->toArray();
                    $lateAttendance = Attendance::whereIn('site_id', $siteArray)
                        ->whereNotNull('lateTime')
                        ->where('dateFormat', $date)
                        ->pluck('user_id')
                        ->toArray();
                    $present = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                        ->whereIn('users.id', $attendance)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name')
                        ->select('count(*) as allcount')
                        ->count();
                    $Tpresent = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                        ->whereIn('users.id', $attendance)
                        ->whereNotIn('users.id', $lateAttendance)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name')
                        ->select('count(*) as allcount')
                        ->count();
                    $absentArray = SiteAssign::whereIn('site_id', $siteArray)
                        ->whereNotIn('user_id', $attendance)
                        ->where('role_id', 3)
                        ->pluck('user_id')
                        ->toArray();
                    $absent = DB::table('site_assign')
                        ->leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                        ->whereIn('users.id', $absentArray)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name')
                        ->select('count(*) as allcount')
                        ->count();
                    $late = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                        ->whereIn('users.id', $lateAttendance)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name')
                        ->select('count(*) as allcount')
                        ->count();
                    $emergencyAttendance = AttendanceRequest::whereIn('site_id', $siteArray)->where([['dateFormat', $date]])->select('count(*) as allcount')->count();
                    $userData = Users::where('id', '=', $user->id)->first();
                    $clients = ClientDetails::where('company_id', $user->company_id)
                        ->select('count(*) as allcount')
                        ->count();
                    $totalGuards = $Tpresent + $late + $absent;
                    $guardLog = ActivityLog::where('company_id', $user->company_id)->whereIn('user_id', $siteArray)
                        ->orderBy('id', 'desc')->take(5)->get();
                    $viewAllLog = ActivityLog::where('company_id', $user->company_id)->orderBy('id', 'desc')
                        ->get();
                    return view('welcome')->with('viewAllLog', $viewAllLog)->with('guardLog', $guardLog)
                        ->with('formattedData', $formattedData)->with('siteDetails', $siteDetails)
                        ->with('sites', $sitess)->with('guards', $totalGuards)->with('guardonsite', $present)
                        ->with('incidence', $incidence)->with('noshow', $absent)->with('clients', $clients)
                        ->with('users', $user)->with('userData', $userData)->with('late', $late)
                        ->with('emergencyAttendance', $emergencyAttendance)->with('user', $user)
                        ->with('u', $d)->with('pendingWithSupervisor', $pendingWithSupervisor)
                        ->with('resolve', $resolve)->with('ignore', $ignore)->with('escalateToAdmin', $escalateToAdmin)
                        ->with('pendingAdmin', $pendingAdmin)->with('escalateToClient', $escalateToClient)
                        ->with('todayPending', $todayPending)->with('todayResolved', $todayResolved)
                        ->with('todayIgnored', $todayIgnored)->with('date', $date)->with('todayPresent', $present)
                        ->with('todayLate', $late)->with('todayAbsent', $absent)->with('weeklyPresent', $weeklyPresent)
                        ->with('weeklyAbsent', $weeklyAbsent)->with('weeklyLate', $weeklyLate)
                        ->with('data', $data);
                } else if ($user->role_id == '4') {
                    $sites = SiteDetails::where('client_id', $user->client_id)->select('count(*) as allcount')->count();
                    $site = SiteDetails::where('client_id', $user->client_id)->pluck('id')->toArray();
                    $incidence = DB::table('incidence_details')
                        ->whereIn('site_id', $site)
                        ->whereNotIn("statusFlag", [1, 2])
                        ->select('count(*) as allcount')
                        ->count();
                    $datetime2 = new DateTime($date);
                    $date1 = date('Y-m-d', strtotime('-7 day', strtotime($date)));
                    $datetime1 = new DateTime($date1, new DateTimeZone('Asia/Kolkata'));
                    $format = 'd M';
                    $interval = $datetime1->diff($datetime2);
                    $daysCount = (int) $interval->format('%a');
                    $daysCount = $daysCount + 1;
                    $m = date("m");
                    $de = date("d");
                    $y = date("Y");
                    $dateArray = array();
                    $d = [];
                    $pendingWithSupervisor = [];
                    $resolve = [];
                    $ignore = [];
                    $attendances = [];
                    $weeklyAbsent = [];
                    $weeklyPresent = [];
                    $weeklyLate = [];
                    for ($i = 0; $i <= $daysCount - 1; $i++) {
                        $dateArray[] = '' . date($format, mktime(0, 0, 0, $m, ($de - $i), $y)) . '';
                        $d[] = '' . date('Y-m-d', mktime(0, 0, 0, $m, ($de - $i), $y)) . '';

                        $pendingWithSupervisor[] = DB::table('incidence_details')
                            ->whereIn('site_id', $site)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 0)
                            ->count();
                        $resolve[] = DB::table('incidence_details')
                            ->whereIn('site_id', $site)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 1)
                            ->count();
                        $ignore[] = DB::table('incidence_details')
                            ->whereIn('site_id', $site)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 2)
                            ->count();
                        $escalateToAdmin[] = DB::table('incidence_details')
                            ->whereIn('site_id', $site)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 3)
                            ->count();
                        $pendingAdmin[] = DB::table('incidence_details')
                            ->whereIn('site_id', $site)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 4)
                            ->count();
                        $escalateToClient[] = DB::table('incidence_details')
                            ->whereIn('site_id', $site)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 5)
                            ->count();

                        $attendances = Attendance::whereIn('site_id', $site)
                            ->where('role_id', 3)
                            ->where('dateFormat', $d[$i])
                            ->pluck('user_id')
                            ->toArray();
                        $weeklyAbsent[] = SiteAssign::whereIn('site_id', $site)
                            ->whereNotIn('user_id', $attendances)
                            ->where('role_id', 3)
                            ->pluck('user_id')
                            ->count();
                        $weeklyPresent[] = Attendance::whereIn('site_id', $site)
                            ->where('role_id', 3)
                            ->where('dateFormat', $d[$i])
                            ->count();
                        $weeklyLate[] = Attendance::whereIn('site_id', $site)
                            ->where('role_id', 3)
                            ->where('dateFormat', $d[$i])
                            ->where('lateTime', '!=', NULL)
                            ->groupBy('user_id')
                            ->count();
                    }
                    $data = $dateArray;
                    $todayPending = DB::table('incidence_details')
                        ->whereIn('site_id', $site)->where('dateFormat', $date)
                        ->where('statusFlag', 0)->count();
                    $todayResolved = DB::table('incidence_details')
                        ->whereIn('site_id', $site)->where('dateFormat', $date)
                        ->where('statusFlag', 1)->count();
                    $todayIgnored = DB::table('incidence_details')
                        ->whereIn('site_id', $site)->where('dateFormat', $date)
                        ->where('statusFlag', 2)->count();
                    $lateAttendance = Attendance::whereIn('site_id', $site)
                        ->whereNotNull('lateTime')
                        ->where('dateFormat', $date)
                        ->pluck('user_id')
                        ->toArray();
                    $present = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                        ->whereIn('users.id', $attendance)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name')
                        ->select('count(*) as allcount')
                        ->count();
                    $Tpresent = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                        ->whereIn('users.id', $attendance)
                        ->whereNotIn('users.id', $lateAttendance)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name')
                        ->select('count(*) as allcount')
                        ->count();
                    $absentArray = SiteAssign::whereIn('site_id', $site)
                        ->whereNotIn('user_id', $attendance)
                        ->where('role_id', 3)
                        ->pluck('user_id')
                        ->toArray();
                    $absent = DB::table('site_assign')
                        ->leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                        ->whereIn('users.id', $absentArray)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name')
                        ->select('count(*) as allcount')
                        ->count();
                    $late = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                        ->whereIn('users.id', $lateAttendance)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name')
                        ->select('count(*) as allcount')
                        ->count();
                    return view('welcome')->with('sites', $sites)->with('incidence', $incidence)->with('user', $user)->with('todayPending', $todayPending)->with('todayResolved', $todayResolved)->with('todayIgnored', $todayIgnored)->with('pendingWithSupervisor', $pendingWithSupervisor)->with('resolve', $resolve)->with('ignore', $ignore)->with('escalateToAdmin', $escalateToAdmin)->with('pendingAdmin', $pendingAdmin)->with('escalateToClient', $escalateToClient)->with('date', $date)->with('todayPresent', $present)->with('todayLate', $late)->with('todayAbsent', $absent)->with('weeklyPresent', $weeklyPresent)->with('weeklyAbsent', $weeklyAbsent)->with('weeklyLate', $weeklyLate)->with('data', $data)->with('guardonsite', $present)->with('noshow', $absent)->with('late', $late)->with('u', $d);
                }
                if ($user->role_id == '7') {
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
                        $clientIds = [];
                        $siteArray = [];
                        $userArray = [];
                    }
                    $sites = DB::table('site_details')
                        ->where('company_id', $user->company_id)
                        ->whereIn('id', $siteArray)
                        ->count();
                    $CheckInCount = Attendance::where('company_id', $user->company_id)
                        ->whereIn('user_id', $userArray)
                        ->where('dateFormat', $date)
                        ->distinct('user_id')
                        ->pluck('user_id')
                        ->toArray();
                    $guardonsite = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                        ->where('users.company_id', $user->company_id)
                        ->whereIn('users.id', $CheckInCount)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name')
                        ->select('count(*) as allcount')
                        ->count();
                    $incidence = DB::table('incidence_details')
                        ->where('company_id', $user->company_id)
                        ->whereIn('site_id', $siteArray)
                        ->whereNotIn('statusFlag', [1, 2])
                        ->select('count(*) as allcount')
                        ->count();
                    $notifications = Notifications::where('company_id', $user->company_id)
                        ->where('action', '=', '0')
                        ->select('count(*) as allcount')
                        ->count();
                    $clients = ClientDetails::where('company_id', $user->company_id)
                        ->whereIn('id', $clientIds)
                        ->select('count(*) as allcount')
                        ->count();
                    $geofences = SiteGeofences::where('company_id', $user->company_id)
                        ->whereIn('client_id', $clientIds)
                        ->select('count(*) as allcount')
                        ->count();
                    $supervisor = DB::table('users')
                        ->whereIn('id', $userArray)
                        ->where('company_id', $user->company_id)
                        ->where('role_id', '=', '2')
                        ->select('count(*) as allcount')
                        ->count();
                    $attendance = Attendance::where('company_id', $user->company_id)
                        ->where('role_id', 3)
                        ->where('dateFormat', $date)
                        ->whereIn('user_id', $userArray)
                        ->distinct('user_id')
                        ->pluck('user_id')
                        ->toArray();
                    $lateAttendance = Attendance::where('company_id', $user->company_id)
                        ->where('role_id', 3)
                        ->whereIn('user_id', $userArray)
                        ->whereNotNull('lateTime')
                        ->distinct('user_id')
                        ->where('dateFormat', $date)
                        ->pluck('user_id')->toArray();
                    $present = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                        ->where('users.company_id', $user->company_id)
                        ->whereIn('users.id', $attendance)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name')
                        ->select('count(*) as allcount')
                        ->count();
                    $Tpresent = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                        ->where('users.company_id', $user->company_id)
                        ->whereNotIN('users.id', $lateAttendance)
                        ->whereIn('users.id', $attendance)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name')
                        ->select('count(*) as allcount')
                        ->count();
                    $absent = Users::leftjoin('site_assign', 'users.id', '=', 'user_id')
                        ->where('users.company_id', $user->company_id)
                        ->where('users.role_id', 3)
                        ->whereIn('users.id', $userArray)
                        ->whereNotIn('users.id', $CheckInCount)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name')
                        ->select('count(*) as allcount')
                        ->count();
                    $late = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                        ->where('users.company_id', $user->company_id)
                        ->where('users.role_id', 3)->whereIn('users.id', $lateAttendance)
                        ->selectRaw('users.*, users.id as id, site_assign.site_name')
                        ->select('count(*) as allcount')
                        ->count();
                    $emergencyAttendance = AttendanceRequest::where([['company_id', $user->company_id], ['dateFormat', $date]])->whereIn('guard_id', $userArray)->select('count(*) as allcount')->count();
                    $totalGuards = $Tpresent + $late + $absent;
                    $datetime2 = new DateTime($date);
                    $date1 = date('Y-m-d', strtotime('-7 day', strtotime($date)));
                    $datetime1 = new DateTime($date1, new DateTimeZone('Asia/Kolkata'));
                    $format = 'd M';
                    $interval = $datetime1->diff($datetime2);
                    $daysCount = (int) $interval->format('%a');
                    $daysCount = $daysCount + 1;
                    $m = date("m");
                    $de = date("d");
                    $y = date("Y");
                    $dateArray = array();
                    $d = [];
                    $datas = [];
                    $pendingWithSupervisor = [];
                    $resolve = [];
                    $ignore = [];
                    $attendances = [];
                    $weeklyAbsent = [];
                    $weeklyPresent = [];
                    $weeklyLate = [];

                    $siteDetails = SiteDetails::where('company_id', $user->company_id)->whereIn('client_id', $clientIds)->get();
                    $groupedData = GuardTourLog::select('site_id', 'date', DB::raw('count(*) as record_count'))->whereIn('site_id', $siteArray)->where('company_id', $user->company_id)->whereBetween('date', [$date1, $date])->groupBy('site_id', 'date')->get();          // Formatting the data

                    $formattedData = [];
                    foreach ($groupedData as $data) {
                        $siteId = $data->site_id;
                        $dateFormat = $data->date;
                        $recordCount = $data->record_count;

                        if (!isset($formattedData[$siteId])) {
                            $formattedData[$siteId] = [];
                        }

                        $formattedData[$siteId][$dateFormat] = $recordCount;
                    }

                    for ($i = 0; $i <= $daysCount - 1; $i++) {
                        $dateArray[] = '' . date($format, mktime(0, 0, 0, $m, ($de - $i), $y)) . '';
                        $d[] = '' . date('Y-m-d', mktime(0, 0, 0, $m, ($de - $i), $y)) . '';
                        $datas[] = DB::table('incidence_details')
                            ->where('company_id', $user->company_id)
                            ->whereIn('site_id', $siteArray)
                            ->where('dateFormat', $d[$i])
                            ->count();
                        $pendingWithSupervisor[] = DB::table('incidence_details')
                            ->where('company_id', $user->company_id)
                            ->whereIn('site_id', $siteArray)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 0)
                            ->count();
                        $resolve[] = DB::table('incidence_details')
                            ->where('company_id', $user->company_id)
                            ->whereIn('site_id', $siteArray)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 1)
                            ->count();
                        $ignore[] = DB::table('incidence_details')
                            ->where('company_id', $user->company_id)
                            ->whereIn('site_id', $siteArray)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 2)
                            ->count();
                        $escalateToAdmin[] = DB::table('incidence_details')
                            ->where('company_id', $user->company_id)
                            ->whereIn('site_id', $siteArray)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 3)
                            ->count();
                        $pendingAdmin[] = DB::table('incidence_details')
                            ->where('company_id', $user->company_id)
                            ->whereIn('site_id', $siteArray)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 4)
                            ->count();
                        $escalateToClient[] = DB::table('incidence_details')
                            ->where('company_id', $user->company_id)
                            ->whereIn('site_id', $siteArray)
                            ->where('dateFormat', $d[$i])
                            ->where('statusFlag', 5)
                            ->count();
                        $attendances = Attendance::where('company_id', $user->company_id)
                            ->whereIn('user_id', $userArray)
                            ->where('dateFormat', $d[$i])
                            ->pluck('user_id')
                            ->toArray();
                        $weeklyAbsent[] = Users::where('company_id', $user->company_id)
                            // ->where('role_id', 3)
                            ->whereIn('id', $userArray)
                            ->whereNotIn('id', $attendances)
                            ->count();
                        $weeklyPresent[] = Attendance::where('company_id', $user->company_id)
                            ->whereIn('user_id', $attendances)
                            ->where('dateFormat', $d[$i])
                            ->distinct('user_id')
                            ->count();
                        $weeklyLate[] = Attendance::where('company_id', $user->company_id)
                            ->whereIn('user_id', $userArray)
                            ->where('dateFormat', $d[$i])
                            ->where('lateTime', '!=', NULL)
                            ->distinct('user_id')
                            ->count();
                    }
                    $data = $dateArray;

                    $todayPending = DB::table('incidence_details')
                        ->where('company_id', $user->company_id)->whereIn('site_id', $siteArray)->where('dateFormat', $date)
                        ->where('statusFlag', 0)->count();
                    $todayResolved = DB::table('incidence_details')
                        ->where('company_id', $user->company_id)->whereIn('site_id', $siteArray)->where('dateFormat', $date)
                        ->where('statusFlag', 1)->count();
                    $todayIgnored = DB::table('incidence_details')
                        ->where('company_id', $user->company_id)->whereIn('site_id', $siteArray)->where('dateFormat', $date)
                        ->where('statusFlag', 2)->count();
                    if ($company->end_date == null) {
                        $sessionExpire['is_expired'] = 1;
                    } else if ($date > $company->end_date) {
                        $sessionExpire['is_expired'] = true;
                    } else {
                        $todaysDate_ts = strtotime($date);
                        $end_dates = strtotime($company->end_date);
                        $diff = $end_dates - $todaysDate_ts;
                        $daysLeftToExpire = $diff / 86400;
                        if ($daysLeftToExpire < 15) {
                            $sessionExpire['is_expired'] = false;
                            $sessionExpire['expire_date'] = $company->end_date;
                            $sessionExpire['is_expiring'] = $daysLeftToExpire;
                        } else {
                            $sessionExpire['is_expired'] = true;
                            $sessionExpire['expire_date'] = $company->end_date;
                        }
                    }
                    session()->put('sessionExpire', $sessionExpire);
                    $guardLog = ActivityLog::where('company_id', $user->company_id)->whereIn('user_id', $userArray)->orderBy('id', 'desc')
                        ->take(5)->get();
                    $viewAllLog = ActivityLog::where('company_id', $user->company_id)->whereIn('user_id', $userArray)->orderBy('id', 'desc')
                        ->get();

                    return view('welcome')->with('viewAllLog', $viewAllLog)->with('geofences', $geofences)->with('guardLog', $guardLog)->with('formattedData', $formattedData)->with('siteDetails', $siteDetails)->with('sites', $sites)->with('guards', $totalGuards)->with('guardonsite', $guardonsite)->with('incidence', $incidence)->with('noshow', $absent)->with('notifications', $notifications)->with('clients', $clients)->with('users', $user)->with('supervisor', $supervisor)->with('userData', $user)->with('late', $late)->with('emergencyAttendance', $emergencyAttendance)->with('data', $data)->with('u', $d)->with('pendingWithSupervisor', $pendingWithSupervisor)->with('resolve', $resolve)->with('ignore', $ignore)->with('escalateToAdmin', $escalateToAdmin)->with('pendingAdmin', $pendingAdmin)->with('escalateToClient', $escalateToClient)->with('user', $user)->with('todayPending', $todayPending)->with('todayResolved', $todayResolved)->with('todayIgnored', $todayIgnored)->with('date', $date)->with('todayPresent', $present)->with('todayLate', $late)->with('todayAbsent', $absent)->with('weeklyPresent', $weeklyPresent)->with('weeklyAbsent', $weeklyAbsent)->with('weeklyLate', $weeklyLate);
                } else {

                    return redirect()->back()->with('error', 'Invalid Credentials');
                }
            } else {
                return redirect()->back()->with('error', 'Invalid Password');
            }
        } else {
            return redirect()->back()->with('error', 'Invalid Credentials');
        }
    }

    // dashboard
    public function viewAllLog()
    {
        $user = session('user');
        $cur_date = new DateTime();
        $date = $cur_date->format('Y-m-d');
        $viewAllLog = ActivityLog::where('company_id', $user->company_id)->orderBy('id', 'desc')->whereDate('created_at', Carbon::today())
            ->get();
        $modaldata = view('viewLog')->with('viewAllLog', $viewAllLog)->render();
        echo $modaldata;
    }

    public function logDate(Request $request)
    {

        $user = session('user');
        //$cur_date = new DateTime();
        //$date = $cur_date->format('Y-m-d');
        if ($user->role_id == '1') {
            $viewAllLog = ActivityLog::where('company_id', $user->company_id)->orderBy('id', 'desc')->whereDate('created_at', $request->date)
                ->get();
        } else if ($user->role_id == '2') {
            $sites = SiteAssign::where('user_id', $user->id)->first();
            if ($sites)
                $siteArray = json_decode($sites['site_id'], true);
            else
                $siteArray = [];

            $viewAllLog = ActivityLog::where('company_id', $user->company_id)->where('user_id', $sites->user_id)->orderBy('id', 'desc')->whereDate('created_at', $request->date)
                ->get();
        } else if ($user->role_id == '7') {
            $clients = SiteAssign::where('user_id', $user->id)->first();

            $siteArray = SiteDetails::whereIn('client_id', json_decode($clients->site_id, true))->pluck('id')->toArray();
            $sites = SiteAssign::where('site_id', $siteArray)->first();
            $viewAllLog = ActivityLog::where('company_id', $user->company_id)->where('user_id', $sites->user_id)->orderBy('id', 'desc')->whereDate('created_at', $request->date)
                ->get();
        }

        $modaldata = view('viewLog')->with('viewAllLog', $viewAllLog)->render();
        echo $modaldata;
    }

    public function dashboard(Request $request)
    {

        $user = session('user');
        //dd($user);
        if ($user) {
            Log::info($user->name . ' welcome page, User_id: ' . $user->id);
            $notification = Notifications::select('count(*) as allcount')->where('company_id', $request->company_id)->count();;
            $cur_date = new DateTime();
            $date = $cur_date->format("Y-m-d");
            $attendance = [];
            $present = [];
            $absent = [];
            $late = [];
            $sites = SiteAssign::where('user_id', $user->id)->first();

            //$attendance = DB::table('attendance')->where('company_id', $user->company_id)->where('dateFormat',  "'" . $date . "'")->where('role_id', 3)->pluck('user_id')->toArray();

            // $viewAllLog = ActivityLog::where('company_id', $user->company_id)->orderBy('id', 'desc')
            //         ->get();

            //$guards = DB::table('users')->where('company_id', $user->company_id)->where('role_id', 3)->whereNotIn('id', $attendance)->get();
            if ($user->role_id == '1') {
                $userData = Users::where('id', '=', $user->id)->first();

                $sites = DB::table('site_details')
                    ->where('company_id', $user->company_id)
                    //->select('count(*) as allcount')
                    ->count();
                //dd($sites);

                $guardLog = ActivityLog::where('company_id', $user->company_id)->orderBy('id', 'desc')
                    ->take(5)->get();

                $viewAllLog = ActivityLog::where('company_id', $user->company_id)->orderBy('id', 'desc')
                    ->get();

                $guards = DB::table('users')
                    ->where('company_id', $user->company_id)
                    ->where('role_id', '=', '3')
                    ->select('count(*) as allcount')
                    ->count();


                $attendance = Attendance::where('company_id', $user->company_id)
                    ->where('role_id', 3)
                    ->where('dateFormat', $date)
                    ->whereNull('lateTime')
                    //->whereNull('exit_time')
                    ->distinct('user_id')
                    ->pluck('user_id')
                    ->toArray();
                $checkIN = Attendance::where('company_id', $user->company_id)
                    //->where('role_id', 3)
                    ->where('dateFormat', $date)
                    ->distinct('user_id')
                    ->pluck('user_id')
                    ->toArray();
                $guardonsite = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                    ->where('users.company_id', $user->company_id)
                    //->where('users.role_id', 3)
                    ->whereIn('users.id', $checkIN)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name')
                    ->select('count(*) as allcount')
                    ->count();

                $incidence = DB::table('incidence_details')
                    ->where('company_id', $user->company_id)
                    ->whereNotIn("statusFlag", [1, 2])
                    ->select('count(*) as allcount')
                    ->count();
                // dd($incidence);

                $noshow = DB::table('users')
                    ->where('company_id', $user->company_id)
                    ->where('role_id', 3)
                    ->whereNotIn('id', $attendance)
                    ->select('count(*) as allcount')
                    ->count();

                $clients = ClientDetails::where('company_id', $user->company_id)
                    ->select('count(*) as allcount')
                    ->count();

                $geofences = SiteGeofences::where('company_id', $user->company_id)
                    ->select('count(*) as allcount')
                    ->count();

                $supervisor = DB::table('users')
                    ->where('company_id', $user->company_id)
                    ->where('role_id', '=', '2')
                    ->select('count(*) as allcount')
                    ->count();

                $notifications = Notifications::where('company_id', $user->company_id)
                    ->select('count(*) as allcount')
                    ->count();

                $lateAttendance = Attendance::where('company_id', $user->company_id)
                    ->where('role_id', 3)
                    ->whereNotNull('lateTime')
                    ->where('dateFormat', $date)
                    ->pluck('user_id')->toArray();

                $present = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                    ->where('users.company_id', $user->company_id)
                    //->whereNotIN('users.id', $lateAttendance)
                    ->where('users.role_id', 3)->whereIn('users.id', $attendance)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name')
                    ->select('count(*) as allcount')
                    ->count();
                $absentCount = Attendance::where('company_id', $user->company_id)
                    ->where('role_id', 3)
                    ->where('dateFormat', $date)
                    ->pluck('user_id')
                    ->toArray();
                $absent = Users::leftjoin('site_assign', 'users.id', '=', 'user_id')
                    ->where('users.company_id', $user->company_id)
                    ->where('users.role_id', 3)->whereNotIn('users.id', $absentCount)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name')
                    ->select('count(*) as allcount')
                    ->count();

                $late = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                    ->where('users.company_id', $user->company_id)
                    ->where('users.role_id', 3)->whereIn('users.id', $lateAttendance)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name')
                    ->select('count(*) as allcount')
                    ->count();

                $emergencyAttendance = AttendanceRequest::where([['company_id', $user->company_id], ['dateFormat', $date]])->select('count(*) as allcount')->count();

                $totalGuards = $present + $late + $absent;
                $datetime2 = new DateTime($date);
                $date1 = date('Y-m-d', strtotime('-7 day', strtotime($date)));
                $datetime1 = new DateTime($date1, new DateTimeZone('Asia/Kolkata'));
                $format = 'd M';
                $interval = $datetime1->diff($datetime2);
                $daysCount = (int) $interval->format('%a');
                $daysCount = $daysCount + 1;
                $m = date("m");
                $de = date("d");
                $y = date("Y");
                $dateArray = array();
                $d = [];
                $datas = [];
                $pendingWithSupervisor = [];
                $resolve = [];
                $ignore = [];
                $weeklyAbsent = [];
                $weeklyPresent = [];
                $weeklyLate = [];
                // $escalateToAdmin = [];
                // $pendingAdmin = [];
                // $escalateToClient = [];
                $siteDetails = SiteDetails::where('company_id', $user->company_id)->get();
                $groupedData = GuardTourLog::select('site_id', 'date', DB::raw('count(*) as record_count'))->where('company_id', $user->company_id)->whereBetween('date', [$date1, $date])->groupBy('site_id', 'date')->get();          // Formatting the data


                $formattedData = [];
                foreach ($groupedData as $data) {
                    $siteId = $data->site_id;
                    $dateFormat = $data->date;
                    $recordCount = $data->record_count;

                    if (!isset($formattedData[$siteId])) {
                        $formattedData[$siteId] = [];
                    }

                    $formattedData[$siteId][$dateFormat] = $recordCount;
                }

                for ($i = 0; $i <= $daysCount - 1; $i++) {
                    $dateArray[] = '' . date($format, mktime(0, 0, 0, $m, ($de - $i), $y)) . '';
                    $d[] = '' . date('Y-m-d', mktime(0, 0, 0, $m, ($de - $i), $y)) . '';
                    $datas[] = DB::table('incidence_details')
                        ->where('company_id', $user->company_id)
                        ->where('dateFormat', $d[$i])
                        ->count();
                    $pendingWithSupervisor[] = DB::table('incidence_details')
                        ->where('company_id', $user->company_id)
                        ->where('dateFormat', $d[$i])
                        ->where('statusFlag', 0)
                        ->count();

                    $resolve[] = DB::table('incidence_details')
                        ->where('company_id', $user->company_id)
                        ->where('dateFormat', $d[$i])
                        ->where('statusFlag', 1)
                        ->count();
                    $ignore[] = DB::table('incidence_details')
                        ->where('company_id', $user->company_id)
                        ->where('dateFormat', $d[$i])
                        ->where('statusFlag', 2)
                        ->count();
                    $escalateToAdmin[] = DB::table('incidence_details')
                        ->where('company_id', $user->company_id)
                        ->where('dateFormat', $d[$i])
                        ->where('statusFlag', 3)
                        ->count();
                    $pendingAdmin[] = DB::table('incidence_details')
                        ->where('company_id', $user->company_id)
                        ->where('dateFormat', $d[$i])
                        ->where('statusFlag', 4)
                        ->count();
                    $escalateToClient[] = DB::table('incidence_details')
                        ->where('company_id', $user->company_id)
                        ->where('dateFormat', $d[$i])
                        ->where('statusFlag', 5)
                        ->count();
                    $attendances = Attendance::where('company_id', $user->company_id)
                        ->where('role_id', 3)
                        ->where('dateFormat', $d[$i])
                        ->pluck('user_id')
                        ->toArray();
                    $weeklyAbsent[] = Users::where('company_id', $user->company_id)
                        ->where('role_id', 3)
                        ->whereNotIn('id', $attendances)
                        ->count();
                    // DB::enableQueryLog();

                    $weeklyPresent[] = Attendance::where('company_id', $user->company_id)
                        ->where('role_id', 3)
                        ->where('dateFormat', $d[$i])
                        ->whereNull('lateTime')
                        ->distinct('user_id')
                        ->count();
                    // dd(DB::getQueryLog());
                    $weeklyLate[] = Attendance::where('company_id', $user->company_id)
                        ->where('role_id', 3)
                        ->where('dateFormat', $d[$i])
                        ->where('lateTime', '!=', NULL)
                        ->distinct('user_id')
                        ->count();
                }
                $data = $dateArray;
                $todayPending = DB::table('incidence_details')
                    ->where('company_id', $user->company_id)->where('dateFormat', $date)
                    ->where('statusFlag', 0)->count();
                $todayResolved = DB::table('incidence_details')
                    ->where('company_id', $user->company_id)->where('dateFormat', $date)
                    ->where('statusFlag', 1)->count();
                $todayIgnored = DB::table('incidence_details')
                    ->where('company_id', $user->company_id)->where('dateFormat', $date)
                    ->where('statusFlag', 2)->count();

                return view('welcome')->with('viewAllLog', $viewAllLog)->with('geofences', $geofences)
                    ->with('guardLog', $guardLog)->with('formattedData', $formattedData)
                    ->with('siteDetails', $siteDetails)->with('sites', $sites)->with('guards', $totalGuards)
                    ->with('guardonsite', $guardonsite)->with('incidence', $incidence)->with('noshow', $absent)
                    ->with('notifications', $notifications)->with('clients', $clients)->with('users', $user)->with('supervisor', $supervisor)
                    ->with('userData', $userData)->with('late', $late)->with('emergencyAttendance', $emergencyAttendance)->with('data', $data)
                    ->with('u', $d)->with('pendingWithSupervisor', $pendingWithSupervisor)->with('resolve', $resolve)->with('ignore', $ignore)
                    ->with('escalateToAdmin', $escalateToAdmin)->with('pendingAdmin', $pendingAdmin)->with('escalateToClient', $escalateToClient)
                    ->with('user', $user)->with('todayPending', $todayPending)->with('todayResolved', $todayResolved)
                    ->with('todayIgnored', $todayIgnored)->with('date', $date)->with('todayPresent', $present)->with('todayLate', $late)
                    ->with('todayAbsent', $absent)->with('weeklyPresent', $weeklyPresent)->with('weeklyAbsent', $weeklyAbsent)
                    ->with('weeklyLate', $weeklyLate)->with('user', $user);
            } else if ($user->role_id == '2') {

                $sites = SiteAssign::where('user_id', $user->id)->first();
                $siteArray = json_decode($sites['site_id'], true);
                $datetime2 = new DateTime($date);
                $date1 = date('Y-m-d', strtotime('-7 day', strtotime($date)));
                $datetime1 = new DateTime($date1, new DateTimeZone('Asia/Kolkata'));
                $format = 'd M';
                $interval = $datetime1->diff($datetime2);
                $daysCount = (int) $interval->format('%a');
                $daysCount = $daysCount + 1;
                $m = date("m");
                $de = date("d");
                $y = date("Y");
                $dateArray = array();
                $d = [];
                $pendingWithSupervisor = [];
                $resolve = [];
                $ignore = [];
                $attendances = [];
                $weeklyAbsent = [];
                $weeklyPresent = [];
                $weeklyLate = [];

                $siteDetails = SiteDetails::where('company_id', $user->company_id)->get();
                $groupedData = GuardTourLog::select('site_id', 'date', DB::raw('count(*) as record_count'))->where('company_id', $user->company_id)->whereBetween('date', [$date1, $date])->groupBy('site_id', 'date')->get();          // Formatting the data


                $formattedData = [];
                foreach ($groupedData as $data) {
                    $siteId = $data->site_id;
                    $dateFormat = $data->date;
                    $recordCount = $data->record_count;

                    if (!isset($formattedData[$siteId])) {
                        $formattedData[$siteId] = [];
                    }

                    $formattedData[$siteId][$dateFormat] = $recordCount;
                }
                for ($i = 0; $i <= $daysCount - 1; $i++) {
                    $dateArray[] = '' . date($format, mktime(0, 0, 0, $m, ($de - $i), $y)) . '';
                    $d[] = '' . date('Y-m-d', mktime(0, 0, 0, $m, ($de - $i), $y)) . '';

                    $pendingWithSupervisor[] = DB::table('incidence_details')
                        ->whereIn('site_id', $siteArray)
                        ->where('dateFormat', $d[$i])
                        ->where('statusFlag', 0)
                        ->count();
                    $resolve[] = DB::table('incidence_details')
                        ->whereIn('site_id', $siteArray)
                        ->where('dateFormat', $d[$i])
                        ->where('statusFlag', 1)
                        ->count();
                    $ignore[] = DB::table('incidence_details')
                        ->whereIn('site_id', $siteArray)
                        ->where('dateFormat', $d[$i])
                        ->where('statusFlag', 2)
                        ->count();
                    $escalateToAdmin[] = DB::table('incidence_details')
                        ->whereIn('site_id', $siteArray)
                        ->where('dateFormat', $d[$i])
                        ->where('statusFlag', 3)
                        ->count();
                    $pendingAdmin[] = DB::table('incidence_details')
                        ->whereIn('site_id', $siteArray)
                        ->where('dateFormat', $d[$i])
                        ->where('statusFlag', 4)
                        ->count();
                    $escalateToClient[] = DB::table('incidence_details')
                        ->whereIn('site_id', $siteArray)
                        ->where('dateFormat', $d[$i])
                        ->where('statusFlag', 5)
                        ->count();

                    $attendances = Attendance::whereIn('site_id', $siteArray)
                        ->where('role_id', 3)
                        ->where('dateFormat', $d[$i])
                        ->pluck('user_id')
                        ->toArray();
                    $weeklyAbsent[] = SiteAssign::whereIn('site_id', $siteArray)
                        ->whereNotIn('user_id', $attendances)
                        ->where('role_id', 3)
                        ->pluck('user_id')
                        ->count();

                    // $weeklyAbsent[] = Users::where('id', $absentArray)

                    //         ->count();
                    // DB::enableQueryLog();

                    $weeklyPresent[] = Attendance::whereIn('site_id', $siteArray)
                        ->where('role_id', 3)
                        ->where('dateFormat', $d[$i])
                        ->count();
                    // dd(DB::getQueryLog());
                    $weeklyLate[] = Attendance::whereIn('site_id', $siteArray)
                        ->where('role_id', 3)
                        ->where('dateFormat', $d[$i])
                        ->where('lateTime', '!=', NULL)
                        ->count();
                }

                // dd($weeklyAbsent);
                $data = $dateArray;

                $todayPending = DB::table('incidence_details')
                    ->whereIn('site_id', $siteArray)->where('dateFormat', $date)
                    ->where('statusFlag', 0)->count();
                $todayResolved = DB::table('incidence_details')
                    ->whereIn('site_id', $siteArray)->where('dateFormat', $date)
                    ->where('statusFlag', 1)->count();
                $todayIgnored = DB::table('incidence_details')
                    ->whereIn('site_id', $siteArray)->where('dateFormat', $date)
                    ->where('statusFlag', 2)->count();
                $attendance = Attendance::whereIn('site_id', $siteArray)
                    ->where('dateFormat', $date)
                    ->pluck('user_id')
                    ->toArray();

                $lateAttendance = Attendance::whereIn('site_id', $siteArray)
                    ->whereNotNull('lateTime')
                    ->where('dateFormat', $date)
                    ->pluck('user_id')
                    ->toArray();

                $present = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                    ->whereIn('users.id', $attendance)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name')
                    ->select('count(*) as allcount')
                    ->count();

                $Tpresent = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                    ->whereIn('users.id', $attendance)
                    ->whereNotIn('users.id', $lateAttendance)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name')
                    ->select('count(*) as allcount')
                    ->count();

                $absentArray = SiteAssign::whereIn('site_id', $siteArray)
                    ->whereNotIn('user_id', $attendance)
                    ->where('role_id', 3)
                    ->pluck('user_id')
                    ->toArray();

                $absent = DB::table('site_assign')
                    ->leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                    ->whereIn('users.id', $absentArray)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name')
                    ->select('count(*) as allcount')
                    ->count();



                $late = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                    ->whereIn('users.id', $lateAttendance)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name')
                    ->select('count(*) as allcount')
                    ->count();

                $emergencyAttendance = AttendanceRequest::whereIn('site_id', $siteArray)->where([['dateFormat', $date]])->select('count(*) as allcount')->count();

                $userData = Users::where('id', '=', $user->id)->first();


                $sitess = DB::table('site_details')->whereIn('id', $siteArray)->select('count(*) as allcount')->count();
                //print_r(\DB::getQueryLog());exit;
                $guards = DB::table('users')
                    ->where([
                        ['supervisor_id', '=', $user->supervisor_id],
                        ['role_id', '=', 2]
                    ])
                    ->select('count(*) as allcount')
                    ->count();

                $incidence = DB::table('incidence_details')
                    ->whereIn('site_id', $siteArray)
                    ->whereNotIn("statusFlag", [1, 2])
                    ->select('count(*) as allcount')
                    ->count();

                $clients = ClientDetails::where('company_id', $user->company_id)
                    ->select('count(*) as allcount')
                    ->count();

                $notifications = Notifications::where('supervisor_id', $user->supervisor_id)
                    ->select('count(*) as allcount')
                    ->count();

                $totalGuards = $Tpresent + $absent + $late;

                $sitesLog = SiteAssign::where('site_id', $siteArray)->first();
                //dd($sites);
                $guardLog = ActivityLog::where('company_id', $user->company_id)->where('user_id', $sitesLog->user_id)->orderBy('id', 'desc')
                    ->take(5)->get();


                $viewAllLog = ActivityLog::where('company_id', $user->company_id)->where('user_id', $sitesLog->user_id)->orderBy('id', 'desc')
                    ->get();

                // dd($guardLog,$viewAllLog);

                return view('welcome')->with('viewAllLog', $viewAllLog)
                    ->with('guardLog', $guardLog)->with('formattedData', $formattedData)->with('siteDetails', $siteDetails)
                    ->with('sites', $sitess)->with('guards', $totalGuards)->with('guardonsite', $present)
                    ->with('incidence', $incidence)->with('noshow', $absent)->with('notifications', $notifications)
                    ->with('clients', $clients)->with('late', $late)->with('emergencyAttendance', $emergencyAttendance)
                    ->with('userData', $userData)->with('late', $late)->with('emergencyAttendance', $emergencyAttendance)
                    ->with('data', $data)->with('u', $d)->with('pendingWithSupervisor', $pendingWithSupervisor)
                    ->with('resolve', $resolve)->with('ignore', $ignore)->with('escalateToAdmin', $escalateToAdmin)
                    ->with('pendingAdmin', $pendingAdmin)->with('escalateToClient', $escalateToClient)->with('user', $user)
                    ->with('todayPending', $todayPending)->with('todayResolved', $todayResolved)
                    ->with('todayIgnored', $todayIgnored)->with('date', $date)->with('todayPresent', $present)
                    ->with('todayLate', $late)->with('todayAbsent', $absent)->with('weeklyPresent', $weeklyPresent)
                    ->with('weeklyAbsent', $weeklyAbsent)->with('weeklyLate', $weeklyLate)->with('user', $user);
            } else if ($user->role_id == '4') {
                $sites = SiteDetails::where('client_id', $user->client_id)->select('count(*) as allcount')->count();
                $site = SiteDetails::where('client_id', $user->client_id)->pluck('id')->toArray();
                $incidence = DB::table('incidence_details')
                    ->whereIn('site_id', $site)
                    ->whereNotIn("statusFlag", [1, 2])
                    ->select('count(*) as allcount')
                    ->count();
                $datetime2 = new DateTime($date);
                $date1 = date('Y-m-d', strtotime('-7 day', strtotime($date)));
                $datetime1 = new DateTime($date1, new DateTimeZone('Asia/Kolkata'));
                $format = 'd M';
                $interval = $datetime1->diff($datetime2);
                $daysCount = (int) $interval->format('%a');
                $daysCount = $daysCount + 1;
                $m = date("m");
                $de = date("d");
                $y = date("Y");
                $dateArray = array();
                $d = [];
                $pendingWithSupervisor = [];
                $resolve = [];
                $ignore = [];
                $attendances = [];
                $weeklyAbsent = [];
                $weeklyPresent = [];
                $weeklyLate = [];


                for ($i = 0; $i <= $daysCount - 1; $i++) {
                    $dateArray[] = '' . date($format, mktime(0, 0, 0, $m, ($de - $i), $y)) . '';
                    $d[] = '' . date('Y-m-d', mktime(0, 0, 0, $m, ($de - $i), $y)) . '';

                    $pendingWithSupervisor[] = DB::table('incidence_details')
                        ->whereIn('site_id', $site)
                        ->where('dateFormat', $d[$i])
                        ->where('statusFlag', 0)
                        ->count();
                    $resolve[] = DB::table('incidence_details')
                        ->whereIn('site_id', $site)
                        ->where('dateFormat', $d[$i])
                        ->where('statusFlag', 1)
                        ->count();
                    $ignore[] = DB::table('incidence_details')
                        ->whereIn('site_id', $site)
                        ->where('dateFormat', $d[$i])
                        ->where('statusFlag', 2)
                        ->count();
                    $escalateToAdmin[] = DB::table('incidence_details')
                        ->whereIn('site_id', $site)
                        ->where('dateFormat', $d[$i])
                        ->where('statusFlag', 3)
                        ->count();
                    $pendingAdmin[] = DB::table('incidence_details')
                        ->whereIn('site_id', $site)
                        ->where('dateFormat', $d[$i])
                        ->where('statusFlag', 4)
                        ->count();
                    $escalateToClient[] = DB::table('incidence_details')
                        ->whereIn('site_id', $site)
                        ->where('dateFormat', $d[$i])
                        ->where('statusFlag', 5)
                        ->count();

                    $attendances = Attendance::whereIn('site_id', $site)
                        ->where('role_id', 3)
                        ->where('dateFormat', $d[$i])
                        ->pluck('user_id')
                        ->toArray();

                    // $weeklyAbsent[] = SiteAssign::whereIn('site_id', $site)
                    //         ->whereNotIn('user_id', $attendances)
                    //         ->where('role_id', 3)
                    //         ->pluck('user_id')
                    //         ->count();

                    $weeklyAbsent[] = SiteAssign::whereIn('site_id', $site)
                        ->whereNotIn('user_id', $attendances)
                        ->where('role_id', 3)
                        ->pluck('user_id')
                        ->count();
                    // DB::enableQueryLog();

                    $weeklyPresent[] = Attendance::whereIn('site_id', $site)
                        ->where('role_id', 3)
                        ->where('dateFormat', $d[$i])
                        ->count();
                    // dd(DB::getQueryLog());
                    $weeklyLate[] = Attendance::whereIn('site_id', $site)
                        ->where('role_id', 3)
                        ->where('dateFormat', $d[$i])
                        ->where('lateTime', '!=', NULL)
                        ->count();
                }

                // dd($weeklyAbsent);
                $data = $dateArray;

                $todayPending = DB::table('incidence_details')
                    ->whereIn('site_id', $site)->where('dateFormat', $date)
                    ->where('statusFlag', 0)->count();
                $todayResolved = DB::table('incidence_details')
                    ->whereIn('site_id', $site)->where('dateFormat', $date)
                    ->where('statusFlag', 1)->count();
                $todayIgnored = DB::table('incidence_details')
                    ->whereIn('site_id', $site)->where('dateFormat', $date)
                    ->where('statusFlag', 2)->count();
                $lateAttendance = Attendance::whereIn('site_id', $site)
                    ->whereNotNull('lateTime')
                    ->where('dateFormat', $date)
                    ->pluck('user_id')
                    ->toArray();

                $present = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                    ->whereIn('users.id', $attendance)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name')
                    ->select('count(*) as allcount')
                    ->count();

                $Tpresent = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                    ->whereIn('users.id', $attendance)
                    ->whereNotIn('users.id', $lateAttendance)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name')
                    ->select('count(*) as allcount')
                    ->count();

                $absentArray = SiteAssign::whereIn('site_id', $site)
                    ->whereNotIn('user_id', $attendance)
                    ->where('role_id', 3)
                    ->pluck('user_id')
                    ->toArray();

                $absent = DB::table('site_assign')
                    ->leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                    ->whereIn('users.id', $absentArray)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name')
                    ->select('count(*) as allcount')
                    ->count();




                $late = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                    ->whereIn('users.id', $lateAttendance)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name')
                    ->select('count(*) as allcount')
                    ->count();

                return view('welcome')->with('sites', $sites)->with('incidence', $incidence)->with('user', $user)
                    ->with('todayPending', $todayPending)->with('todayResolved', $todayResolved)->with('todayIgnored', $todayIgnored)
                    ->with('pendingWithSupervisor', $pendingWithSupervisor)->with('resolve', $resolve)->with('ignore', $ignore)
                    ->with('escalateToAdmin', $escalateToAdmin)->with('pendingAdmin', $pendingAdmin)->with('escalateToClient', $escalateToClient)
                    ->with('date', $date)->with('todayPresent', $present)->with('todayLate', $late)->with('todayAbsent', $absent)
                    ->with('weeklyPresent', $weeklyPresent)->with('weeklyAbsent', $weeklyAbsent)->with('weeklyLate', $weeklyLate)
                    ->with('data', $data)->with('guardonsite', $present)->with('noshow', $absent)->with('late', $late)->with('u', $d);
            } else if ($user->role_id == '7') {

                $userData = Users::where('id', '=', $user->id)->first();
                // $clients = SiteAssign::where('user_id', $user->id)->first();
                // // dd($clients);
                // // $clientArray = ClientDetails::whereIn('id', $clients->client_id)->get();
                // // $siteArray = json_decode($clients['site_id'], true);
                // $siteArray = SiteDetails::whereIn('client_id', json_decode($clients->site_id))->pluck('id')->toArray();

                // $siteIds = SiteAssign::whereIn('site_id', $siteArray)->pluck('user_id')->toArray();
                // //dd($siteIds);

                // $userArray = SiteAssign::whereIn('site_id', $siteArray)->distinct('user_id')->pluck('user_id')->toArray();

                // dd($userData,$clients,$siteArray,$userArray,$clientArray,$siteIds);

                // $clients = SiteAssign::where('user_id', $user->id)->first();
                // $siteArray = json_decode($clients['site_id'], true);

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
                    $clientIds = [];
                    $siteArray = [];
                    $userArray = [];
                }

                $sites = DB::table('site_details')
                    ->where('company_id', $user->company_id)
                    ->whereIn('client_id', $clientIds)
                    //->select('count(*) as allcount')
                    ->count();


                //dd($CheckInCount);
                $guards = DB::table('users')
                    ->where('company_id', $user->company_id)
                    ->whereIn('id', $userArray)
                    // ->where('role_id', '=', '3')
                    ->select('count(*) as allcount')
                    ->count();


                $attendance = Attendance::where('company_id', $user->company_id)
                    // ->where('role_id', 3)
                    ->where('dateFormat', $date)
                    ->whereIn('user_id', $userArray)
                    //->whereNull('exit_time')
                    ->distinct('user_id')
                    ->pluck('user_id')
                    ->toArray();

                // $attendance = Attendance::where('company_id', $user->company_id)
                //         ->where('role_id', 3)
                //         ->where('dateFormat', $date)
                //         ->whereIn('user_id', $userArray)
                //         ->distinct('user_id')
                //         ->pluck('user_id')
                //         ->toArray();

                $checkIN = Attendance::where('company_id', $user->company_id)
                    // ->where('role_id', 3)
                    ->where('dateFormat', $date)
                    ->whereIn('user_id', $userArray)
                    ->distinct('user_id')
                    ->pluck('user_id')
                    ->toArray();

                //dd($checkIN);

                $guardonsite = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                    ->where('users.company_id', $user->company_id)
                    // ->where('users.role_id', 3)
                    ->whereIn('users.id', $checkIN)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name')
                    ->select('count(*) as allcount')
                    ->count();


                // $site_detail = SiteDetails::whereIn('client_id', $siteArray)->get();
                // dd($site_detail);

                // dd($incidence);

                //dd($geofences);
                $incidence = DB::table('incidence_details')
                    ->where('company_id', $user->company_id)
                    ->where('site_id', $siteArray)
                    ->whereNotIn("statusFlag", [1, 2])
                    ->select('count(*) as allcount')
                    ->count();
                // dd($incidence);

                $noshow = DB::table('users')
                    ->where('company_id', $user->company_id)
                    ->where('role_id', 3)
                    ->whereNotIn('id', $attendance)
                    ->select('count(*) as allcount')
                    ->count();

                //dd($siteArray);
                $clients = ClientDetails::where('company_id', $user->company_id)
                    ->whereIn('id', $clientIds)
                    ->select('count(*) as allcount')
                    ->count();

                $geofences = SiteGeofences::where('company_id', $user->company_id)
                    ->whereIn('client_id', $clientIds)
                    ->select('count(*) as allcount')
                    ->count();
                //dd($geofences);

                $supervisor = DB::table('users')
                    ->where('company_id', $user->company_id)
                    ->whereIn('id', $userArray)
                    ->where('role_id', '=', '2')
                    ->select('count(*) as allcount')
                    ->count();

                $notifications = Notifications::where('company_id', $user->company_id)
                    ->select('count(*) as allcount')
                    ->count();

                $lateAttendance = Attendance::where('company_id', $user->company_id)
                    // ->where('role_id', 3)
                    ->whereIn('user_id', $userArray)
                    ->whereNotNull('lateTime')
                    ->distinct('user_id')
                    ->where('dateFormat', $date)
                    ->pluck('user_id')->toArray();

                $present = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                    ->where('users.company_id', $user->company_id)
                    ->whereIn('users.id', $attendance)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name')
                    ->select('count(*) as allcount')
                    ->count();

                // dd($present);

                //         $present = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                //         ->where('users.company_id', $user->company_id)
                //         ->whereIn('users.id', $attendance)
                //         ->selectRaw('users.*, users.id as id, site_assign.site_name')
                //         ->select('count(*) as allcount')
                //         ->count();

                // $Tpresent =  Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                //         ->where('users.company_id', $user->company_id)
                //         ->whereNotIN('users.id', $lateAttendance)
                //         ->where('users.role_id', 3)
                //         ->whereIn('users.id', $attendance)
                //         ->selectRaw('users.*, users.id as id, site_assign.site_name')
                //         ->select('count(*) as allcount')
                //         ->count();

                //$absentCount = Attendance::where('company_id', $user->company_id)
                //         ->where('role_id', 3)
                //         ->where('dateFormat', $date)
                //         ->pluck('user_id')
                //         ->toArray();

                //$absent = Users::leftjoin('site_assign', 'users.id', '=', 'user_id')
                //         ->where('users.company_id', $user->company_id)
                //         ->whereIn('users.id', $userArray)
                //         ->where('users.role_id', 3)->whereNotIn('users.id', $absentCount)
                //         ->selectRaw('users.*, users.id as id, site_assign.site_name')
                //         ->select('count(*) as allcount')
                //         ->count();

                $absent = Users::leftjoin('site_assign', 'users.id', '=', 'user_id')
                    ->where('users.company_id', $user->company_id)
                    // ->where('users.role_id', 3)
                    ->whereIn('users.id', $userArray)
                    ->whereNotIn('users.id', $checkIN)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name')
                    ->select('count(*) as allcount')
                    ->count();

                $late = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                    ->where('users.company_id', $user->company_id)
                    // ->where('users.role_id', 3)
                    ->whereIn('users.id', $lateAttendance)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name')
                    ->select('count(*) as allcount')
                    ->count();

                $emergencyAttendance = AttendanceRequest::where([['company_id', $user->company_id], ['dateFormat', $date]])->whereIn('guard_id', $userArray)->select('count(*) as allcount')->count();

                $totalGuards = $present + $late + $absent;
                $datetime2 = new DateTime($date);
                $date1 = date('Y-m-d', strtotime('-7 day', strtotime($date)));
                $datetime1 = new DateTime($date1, new DateTimeZone('Asia/Kolkata'));
                $format = 'd M';
                $interval = $datetime1->diff($datetime2);
                $daysCount = (int) $interval->format('%a');
                $daysCount = $daysCount + 1;
                $m = date("m");
                $de = date("d");
                $y = date("Y");
                $dateArray = array();
                $d = [];
                $datas = [];
                $pendingWithSupervisor = [];
                $resolve = [];
                $ignore = [];
                $weeklyAbsent = [];
                $weeklyPresent = [];
                $weeklyLate = [];
                // $escalateToAdmin = [];
                // $pendingAdmin = [];
                // $escalateToClient = [];



                $siteDetails = SiteDetails::where('company_id', $user->company_id)->whereIn('client_id', $clientIds)->get();
                $groupedData = GuardTourLog::select('site_id', 'date', DB::raw('count(*) as record_count'))->whereIn('site_id', $siteArray)->where('company_id', $user->company_id)->whereBetween('date', [$date1, $date])->groupBy('site_id', 'date')->get();          // Formatting the data


                $formattedData = [];
                foreach ($groupedData as $data) {
                    $siteId = $data->site_id;
                    $dateFormat = $data->date;
                    $recordCount = $data->record_count;

                    if (!isset($formattedData[$siteId])) {
                        $formattedData[$siteId] = [];
                    }

                    $formattedData[$siteId][$dateFormat] = $recordCount;
                }

                for ($i = 0; $i <= $daysCount - 1; $i++) {
                    $dateArray[] = '' . date($format, mktime(0, 0, 0, $m, ($de - $i), $y)) . '';
                    $d[] = '' . date('Y-m-d', mktime(0, 0, 0, $m, ($de - $i), $y)) . '';
                    $datas[] = DB::table('incidence_details')
                        ->where('company_id', $user->company_id)
                        ->whereIn('site_id', $siteArray)
                        ->where('dateFormat', $d[$i])
                        ->count();
                    $pendingWithSupervisor[] = DB::table('incidence_details')
                        ->where('company_id', $user->company_id)
                        ->whereIn('site_id', $siteArray)
                        ->where('dateFormat', $d[$i])
                        ->where('statusFlag', 0)
                        ->count();

                    $resolve[] = DB::table('incidence_details')
                        ->where('company_id', $user->company_id)
                        ->where('dateFormat', $d[$i])->whereIn('site_id', $siteArray)
                        ->where('statusFlag', 1)
                        ->count();

                    $ignore[] = DB::table('incidence_details')
                        ->where('company_id', $user->company_id)
                        ->where('dateFormat', $d[$i])->whereIn('site_id', $siteArray)
                        ->where('statusFlag', 2)
                        ->count();

                    $escalateToAdmin[] = DB::table('incidence_details')
                        ->where('company_id', $user->company_id)
                        ->where('dateFormat', $d[$i])->whereIn('site_id', $siteArray)
                        ->where('statusFlag', 3)
                        ->count();

                    $pendingAdmin[] = DB::table('incidence_details')
                        ->where('company_id', $user->company_id)
                        ->where('dateFormat', $d[$i])->whereIn('site_id', $siteArray)
                        ->where('statusFlag', 4)
                        ->count();
                    $escalateToClient[] = DB::table('incidence_details')
                        ->where('company_id', $user->company_id)
                        ->where('dateFormat', $d[$i])->whereIn('site_id', $siteArray)
                        ->where('statusFlag', 5)
                        ->count();
                    $attendances = Attendance::where('company_id', $user->company_id)
                        // ->where('role_id', 3)
                        ->whereIn('user_id', $userArray)
                        ->where('dateFormat', $d[$i])
                        ->pluck('user_id')
                        ->toArray();
                    $weeklyAbsent[] = Users::where('company_id', $user->company_id)
                        // ->where('role_id', 3)
                        ->whereIn('id', $userArray)
                        ->whereNotIn('id', $attendances)
                        ->count();
                    // DB::enableQueryLog();
                    //         $attendances = Attendance::where('company_id', $user->company_id)
                    //         ->where('role_id', 3)
                    //         ->whereIn('user_id', $userArray)
                    //         ->where('dateFormat', $d[$i])
                    //         ->pluck('user_id')
                    //         ->toArray();

                    // $weeklyAbsent[] = Users::where('company_id', $user->company_id)
                    //         ->where('role_id', 3)
                    //         ->whereIn('id', $userArray)
                    //         ->whereNotIn('id', $attendances)
                    //         ->count();

                    $weeklyPresent[] = Attendance::where('company_id', $user->company_id)
                        // ->where('role_id', 3)
                        ->whereIn('user_id', $attendances)
                        ->where('dateFormat', $d[$i])
                        // ->whereNull('lateTime')
                        ->distinct('user_id')
                        ->count();

                    // $weeklyPresent[] = Attendance::where('company_id', $user->company_id)
                    // ->where('role_id', 3)
                    // ->whereIn('user_id', $attendances)
                    // ->where('dateFormat', $d[$i])
                    // //->where('lateTime', NULL)
                    // ->distinct('user_id')
                    // ->count();
                    // dd(DB::getQueryLog());
                    $weeklyLate[] = Attendance::where('company_id', $user->company_id)
                        // ->where('role_id', 3)
                        ->whereIn('user_id', $userArray)
                        ->where('dateFormat', $d[$i])
                        ->where('lateTime', '!=', NULL)
                        ->distinct('user_id')
                        ->count();
                }
                $data = $dateArray;
                $todayPending = DB::table('incidence_details')
                    ->where('company_id', $user->company_id)->whereIn('site_id', $siteArray)->where('dateFormat', $date)
                    ->where('statusFlag', 0)->count();
                $todayResolved = DB::table('incidence_details')
                    ->where('company_id', $user->company_id)->whereIn('site_id', $siteArray)->where('dateFormat', $date)
                    ->where('statusFlag', 1)->count();
                $todayIgnored = DB::table('incidence_details')
                    ->where('company_id', $user->company_id)->whereIn('site_id', $siteArray)->where('dateFormat', $date)
                    ->where('statusFlag', 2)->count();

                $sitesLog = SiteAssign::where('site_id', $siteArray)->first();
                //dd($sites);
                $guardLog = ActivityLog::where('company_id', $user->company_id)->where('user_id', $sitesLog->user_id)->orderBy('id', 'desc')
                    ->take(5)->get();

                $viewAllLog = ActivityLog::where('company_id', $user->company_id)->where('user_id', $sitesLog->user_id)->orderBy('id', 'desc')
                    ->get();

                return view('welcome')->with('viewAllLog', $viewAllLog)->with('geofences', $geofences)
                    ->with('guardLog', $guardLog)->with('formattedData', $formattedData)->with('siteDetails', $siteDetails)
                    ->with('sites', $sites)->with('guards', $totalGuards)->with('guardonsite', $guardonsite)->with('incidence', $incidence)
                    ->with('noshow', $absent)->with('notifications', $notifications)->with('clients', $clients)->with('users', $user)
                    ->with('supervisor', $supervisor)->with('userData', $userData)->with('late', $late)
                    ->with('emergencyAttendance', $emergencyAttendance)->with('data', $data)->with('u', $d)
                    ->with('pendingWithSupervisor', $pendingWithSupervisor)->with('resolve', $resolve)
                    ->with('ignore', $ignore)->with('escalateToAdmin', $escalateToAdmin)->with('pendingAdmin', $pendingAdmin)
                    ->with('escalateToClient', $escalateToClient)->with('user', $user)->with('todayPending', $todayPending)
                    ->with('todayResolved', $todayResolved)->with('todayIgnored', $todayIgnored)->with('date', $date)
                    ->with('todayPresent', $present)->with('todayLate', $late)->with('todayAbsent', $absent)->with('weeklyPresent', $weeklyPresent)
                    ->with('weeklyAbsent', $weeklyAbsent)->with('weeklyLate', $weeklyLate)->with('user', $user);
            } else {
                $userData = Users::where('id', '=', $user->id)->first();
                $sites = DB::table('site_details')->where('role_id', '=', $user->role_id)->select('count(*) as allcount')->count();

                $guards = DB::table('users')->where('role_id', '=', 3)->select('count(*) as allcount')->count();

                $guardonsite = DB::table('attendance')->where([['role_id', '=', $user->role_id], ['dateFormat', '=', $date]])->select('count(*) as allcount')->count();

                $incidence = DB::table('incidence_details')->where([['company_id', '=', $user->company_id], ['supervisor_id', '=', $user->supervisor_id]])->select('count(*) as allcount')->count();

                $noshow = DB::table('users')->where('company_id', $user->company_id)->where('role_id', 3)->where('supervisor_id', '=', $user->supervisor_id)->whereNotIn('id', $attendance)->select('count(*) as allcount')->count();
                $notifications = Notifications::where([['company_id', $user->company_id], ['supervisor_id', '=', $user->supervisor_id]])->select('count(*) as allcount')->count();
                return view('welcome')->with('sites', $sites)->with('guards', $guards)->with('guardonsite', $guardonsite)
                    ->with('incidence', $incidence)->with('noshow', $noshow)->with('notifications', $notifications)->with('userData', $userData);
            }
        }
    }

    // notifications
    public function notifications(Request $request)
    {
        $user = session('user');
        Log::info($user->name . ' view notification list, User_id: ' . $user->id);
        $start = $request->get("start");
        $records = Notifications::where('action', '=', '0')->orderBy('id', 'DESC')->take(5)->get();
        $data_arr = array();
        foreach ($records as $record) {
            $id = $record->id;
            $notification_id = $record->notification_id;
            $notification = $record->notification;
            $type = $record->type;
            $user_id = $record->user_id;



            $data_arr[] = array(
                "notification" => $notification,
                "notification_id" => $notification_id,
                "id" => $id,
                "type" => $type,
                "user_id" => $user_id,

            );
            //print_r($data_arr);exit;
        }
        $response = array(
            "aaData" => $data_arr
        );
        echo json_encode($response);
        exit;
    }

    // // logout
    // public function logout(Request $request)
    // {
    //         session()->forget('features');
    //         Auth::logout();
    //         $request->session()->invalidate();
    //         $request->session()->regenerateToken();
    //         return redirect('/');
    // }


    // logout
    public function logout(Request $request)
    {
        // ✅ Get user BEFORE logout
        // $user = Auth::user();
        $user = session()->get('user');
        $sessionId_whatsapp = session('sessionId_whatsapp');
        // $sessionId_whatsapps = session()->get('sessionId_whatsapp');


        // dd($sessionId_whatsapp, $sessionId_whatsapps);
        // dump($user . "user from logout");
        // Clear SSO sessions for global logout
        if ($user && $sessionId_whatsapp) {
            $query = DB::table('sso_sessions')
                ->where('id', $sessionId_whatsapp)
                ->delete();

            // dd($query);
        }

        // Original logout code
        session()->forget('features');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    // save token for notification
    public function saveToken(Request $request)
    {
        // dd($request->token);
        $user = session('user');
        // dd($user);
        $response = Users::where('id', $user->id)
            ->update([
                'web_token' => $request->token
            ]);
        //dd($response);
    }
}

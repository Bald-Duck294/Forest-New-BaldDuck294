<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use App\Attendance;
use App\Users;
use App\SiteAssign;
use App\ClientDetails;
use App\SiteDetails;
use App\ShiftAssigned;
use App\CompanyDetails;
use DateTime;
use Log;
use App\ActivityLog;
use App\Exports\GuardExport;


class GuardsController extends Controller
{
    private $excel;
    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
    }

    //guard list with present, late, absent
    // public function index()
    // {
    //     $user = session('user');
    //     Log::info($user->name . 'view guard list, User_id: ' . $user->id);
    //     $cur_date = new DateTime();
    //     $date = $cur_date->format("Y-m-d");
    //     $attendance = [];
    //     $present = [];
    //     $absent = [];
    //     $late = [];
    //     $sites = SiteAssign::where('user_id', $user->id)->first();
    //     if ($user->role_id == "2") {
    //         if ($sites) {
    //             $siteArray = json_decode($sites['site_id'], true);

    //             $attendance = Attendance::whereIn('site_id', $siteArray)->where('dateFormat', $date)->pluck('user_id')->toArray();

    //             $lateAttendance = Attendance::whereIn('site_id', $siteArray)->whereNotNull('lateTime')->where('dateFormat', $date)->pluck('user_id')->toArray();

    //             $present = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
    //                 ->whereIn('users.id', $attendance)
    //                 ->whereNotIn('users.id', $lateAttendance)
    //                 ->selectRaw('users.*, users.id as id, site_assign.site_name,site_assign.date_range,site_assign.shift_name')
    //                 ->orderBy('users.name', 'ASC')->get();

    //             $absentArray = SiteAssign::whereIn('site_id', $siteArray)->whereNotIn('user_id', $attendance)->where('role_id', 3)->pluck('user_id')->toArray();

    //             $absent = DB::table('site_assign')
    //                 //->whereIn('id', $absentArray)
    //                 ->leftjoin('users', 'site_assign.user_id', '=', 'users.id')
    //                 ->whereIn('users.id', $absentArray)
    //                 ->selectRaw('users.*, users.id as id, site_assign.site_name,site_assign.date_range,site_assign.shift_name')
    //                 ->orderBy('users.name', 'ASC')->get();

    //             $late = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
    //                 ->whereIn('users.id', $lateAttendance)
    //                 ->selectRaw('users.*, users.id as id, site_assign.site_name,site_assign.date_range,site_assign.shift_name')
    //                 ->orderBy('users.name', 'ASC')->get();

    //             return view('guardslist')->with('present', $present)->with('absent', $absent)->with('late', $late);
    //         }
    //     } else if ($user->role_id == '0') {
    //         $guards = Users::where('role_id', 3)->where('showUser', 1)->get();
    //         return view('companies/guardlist')->with('guards', $guards);
    //     } else if ($user->role_id == '7') {

    //         if ($sites) {
    //             $clientArray = json_decode($sites['site_id'], true);

    //             $userArray = SiteAssign::whereIn('client_id', $clientArray)->pluck('user_id')->toArray();

    //             $attendance = Attendance::whereIn('user_id', $userArray)->where('dateFormat', $date)->where('role_id', 3)->pluck('user_id')->toArray();

    //             $lateAttendance = Attendance::whereIn('user_id', $userArray)->whereNotNull('lateTime')->where('dateFormat', $date)->where('role_id', 3)->pluck('user_id')->toArray();

    //             $present = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
    //                 ->whereIn('users.id', $attendance)
    //                 ->whereNotIn('users.id', $lateAttendance)
    //                 ->where('users.role_id', 3)
    //                 ->selectRaw('users.*, users.id as id, site_assign.site_name,site_assign.date_range,site_assign.shift_name')
    //                 ->orderBy('users.name', 'ASC')->get();

    //             $absentArray = SiteAssign::whereIn('client_id', $clientArray)->whereNotIn('user_id', $attendance)->where('role_id', 3)->pluck('user_id')->toArray();
    //             // dd($absentArray);
    //             $absent = DB::table('site_assign')
    //                 //->whereIn('id', $absentArray)
    //                 ->leftjoin('users', 'site_assign.user_id', '=', 'users.id')
    //                 ->where('users.role_id', 3)
    //                 ->whereIn('users.id', $absentArray)
    //                 ->selectRaw('users.*, users.id as id, site_assign.site_name,site_assign.date_range,site_assign.shift_name')
    //                 ->orderBy('users.name', 'ASC')->get();

    //             $late = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
    //                 ->whereIn('users.id', $lateAttendance)
    //                 ->where('users.role_id', 3)
    //                 ->selectRaw('users.*, users.id as id, site_assign.site_name,site_assign.date_range,site_assign.shift_name')
    //                 ->orderBy('users.name', 'ASC')->get();

    //             return view('guardslist')->with('present', $present)->with('absent', $absent)->with('late', $late);
    //         }
    //     } else {
    //         $attendance = Attendance::where('company_id', $user->company_id)
    //             ->where('role_id', 3)
    //             ->where('dateFormat', $date)
    //             ->pluck('user_id')
    //             ->toArray();

    //         $lateAttendance = Attendance::where('company_id', $user->company_id)
    //             ->where('role_id', 3)->whereNotNull('lateTime')
    //             ->where('dateFormat', $date)->pluck('user_id')->toArray();

    //         $present = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
    //             ->where('users.company_id', $user->company_id)
    //             ->where('users.showUser', 1)
    //             ->whereNotIn('users.id', $lateAttendance)
    //             ->where('users.role_id', 3)->whereIn('users.id', $attendance)
    //             ->selectRaw('users.*, users.id as id, site_assign.site_name,site_assign.date_range,site_assign.shift_name')
    //             ->orderBy('name', 'ASC')
    //             ->get();

    //         $absent = Users::leftjoin('site_assign', 'users.id', '=', 'user_id')
    //             ->where('users.company_id', $user->company_id)
    //             ->where('users.showUser', 1)
    //             ->where('users.role_id', 3)->whereNotIn('users.id', $attendance)
    //             ->selectRaw('users.*, users.id as id, site_assign.site_name,site_assign.date_range,site_assign.shift_name')
    //             ->orderBy('name', 'ASC')->get();

    //         $late = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
    //             ->where('users.company_id', $user->company_id)
    //             ->where('users.showUser', 1)
    //             ->where('users.role_id', 3)->whereIn('users.id', $lateAttendance)
    //             ->selectRaw('users.*, users.id as id, site_assign.site_name,site_assign.date_range,site_assign.shift_name')
    //             ->orderBy('name', 'ASC')->get();

    //         return view('guardslist')->with('present', $present)->with('absent', $absent)->with('late', $late);
    //     }
    // }


    // guard list with present, late, absent, unassigned and KPIs
    public function index()
    {
        $user = session('user');
        Log::info($user->name . 'view guard list, User_id: ' . $user->id);
        $cur_date = new DateTime();
        $date = $cur_date->format("Y-m-d");

        $attendance = [];
        $present = collect();
        $absent = collect();
        $late = collect();

        // --- Calculate KPIs ---
        $adminsCount = Users::where('company_id', $user->company_id)->where('role_id', 2)->where('showUser', 1)->count();
        $supervisorsCount = Users::where('company_id', $user->company_id)->where('role_id', 3)->where('showUser', 1)->count();
        $totalUsersCount = Users::where('company_id', $user->company_id)->where('showUser', 1)->count();

        // --- Get Unassigned Guards ---
        $assignedUserIds = SiteAssign::where('company_id', $user->company_id)->pluck('user_id')->toArray();
        $unassigned = Users::whereNotIn('id', $assignedUserIds)
            ->where('company_id', $user->company_id)
            ->whereIn('role_id', [3])
            ->where('showUser', 1)
            ->get();

        $sites = SiteAssign::where('user_id', $user->id)->first();

        if ($user->role_id == "2") {
            if ($sites) {
                $siteArray = json_decode($sites['site_id'], true);
                $attendance = Attendance::whereIn('site_id', $siteArray)->where('dateFormat', $date)->pluck('user_id')->toArray();
                $lateAttendance = Attendance::whereIn('site_id', $siteArray)->whereNotNull('lateTime')->where('dateFormat', $date)->pluck('user_id')->toArray();

                $present = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                    ->whereIn('users.id', $attendance)
                    ->whereNotIn('users.id', $lateAttendance)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name, site_assign.date_range, site_assign.shift_name')
                    ->orderBy('users.name', 'ASC')->get();

                $absentArray = SiteAssign::whereIn('site_id', $siteArray)->whereNotIn('user_id', $attendance)->where('role_id', 3)->pluck('user_id')->toArray();
                $absent = DB::table('site_assign')
                    ->leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                    ->whereIn('users.id', $absentArray)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name, site_assign.date_range, site_assign.shift_name')
                    ->orderBy('users.name', 'ASC')->get();

                $late = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                    ->whereIn('users.id', $lateAttendance)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name, site_assign.date_range, site_assign.shift_name')
                    ->orderBy('users.name', 'ASC')->get();
            }
        }
        else if ($user->role_id == '0') {
            $guards = Users::where('role_id', 3)->where('showUser', 1)->get();
            return view('companies/guardlist')->with('guards', $guards);
        }
        else if ($user->role_id == '7') {
            if ($sites) {
                $clientArray = json_decode($sites['site_id'], true);
                $userArray = SiteAssign::whereIn('client_id', $clientArray)->pluck('user_id')->toArray();

                $attendance = Attendance::whereIn('user_id', $userArray)->where('dateFormat', $date)->where('role_id', 3)->pluck('user_id')->toArray();
                $lateAttendance = Attendance::whereIn('user_id', $userArray)->whereNotNull('lateTime')->where('dateFormat', $date)->where('role_id', 3)->pluck('user_id')->toArray();

                $present = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                    ->whereIn('users.id', $attendance)
                    ->whereNotIn('users.id', $lateAttendance)
                    ->where('users.role_id', 3)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name, site_assign.date_range, site_assign.shift_name')
                    ->orderBy('users.name', 'ASC')->get();

                $absentArray = SiteAssign::whereIn('client_id', $clientArray)->whereNotIn('user_id', $attendance)->where('role_id', 3)->pluck('user_id')->toArray();
                $absent = DB::table('site_assign')
                    ->leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                    ->where('users.role_id', 3)
                    ->whereIn('users.id', $absentArray)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name, site_assign.date_range, site_assign.shift_name')
                    ->orderBy('users.name', 'ASC')->get();

                $late = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                    ->whereIn('users.id', $lateAttendance)
                    ->where('users.role_id', 3)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name, site_assign.date_range, site_assign.shift_name')
                    ->orderBy('users.name', 'ASC')->get();
            }
        }
        else {
            $attendance = Attendance::where('company_id', $user->company_id)
                ->where('role_id', 3)
                ->where('dateFormat', $date)
                ->pluck('user_id')->toArray();

            $lateAttendance = Attendance::where('company_id', $user->company_id)
                ->where('role_id', 3)->whereNotNull('lateTime')
                ->where('dateFormat', $date)->pluck('user_id')->toArray();

            $present = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                ->where('users.company_id', $user->company_id)
                ->where('users.showUser', 1)
                ->whereNotIn('users.id', $lateAttendance)
                ->where('users.role_id', 3)->whereIn('users.id', $attendance)
                ->selectRaw('users.*, users.id as id, site_assign.site_name, site_assign.date_range, site_assign.shift_name')
                ->orderBy('name', 'ASC')->get();

            $absent = Users::leftjoin('site_assign', 'users.id', '=', 'user_id')
                ->where('users.company_id', $user->company_id)
                ->where('users.showUser', 1)
                ->where('users.role_id', 3)->whereNotIn('users.id', $attendance)
                ->selectRaw('users.*, users.id as id, site_assign.site_name, site_assign.date_range, site_assign.shift_name')
                ->orderBy('name', 'ASC')->get();

            $late = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                ->where('users.company_id', $user->company_id)
                ->where('users.showUser', 1)
                ->where('users.role_id', 3)->whereIn('users.id', $lateAttendance)
                ->selectRaw('users.*, users.id as id, site_assign.site_name, site_assign.date_range, site_assign.shift_name')
                ->orderBy('name', 'ASC')->get();
        }

        // Return everything to the single view
        return view('guardslist', compact('present', 'absent', 'late', 'unassigned', 'adminsCount', 'supervisorsCount', 'totalUsersCount'));
    }
    // guard edit - site
    public function guardEdit($client_id, $site_id)
    {
        // dd($site_id);
        $user = session('user');
        $admin = Users::where('id', $client_id)->first();

        Log::info($user->name . ' view guard edit form, User_id: ' . $user->id);

        $assignsite = SiteAssign::where('id', $site_id)->first();
        // dd($assignsite);
        $clients = ClientDetails::where('company_id', $user->company_id)->get();
        $sites = SiteDetails::where('company_id', $user->company_id)->get();
        if ($assignsite->shift_id != null) {
            $shifts = ShiftAssigned::where('id', $assignsite->shift_id)->get();
        }
        else {
            $shifts = [];
        }

        // dd($shifts);

        return view('updateassignsitetoguard')->with('admin', $admin)->with('assignsite', $assignsite)->with('sites', $sites)->with('shifts', $shifts)->with('clients', $clients)->with('id', $site_id);
    }

    // guard edit action - site
    public function editAction($id)
    {
        $user = session('user');

        $client_id = 0;
        $site_id = 0;
        $site = SiteDetails::where('id', $_POST['site'])->first();
        $shift = ShiftAssigned::where('id', $_POST['shift'])->first();
        $client = ClientDetails::where('id', $_POST['client'])->first();
        $weekoff = isset($_POST['weekoff']) ? $_POST['weekoff'] : null;
        $date = array(
            "from" => $_POST["startdate"],
            "to" => $_POST["enddate"]
        );
        $dateArray = json_encode($date, true);
        // print_r($site);exit;
        $siteassign = SiteAssign::find($id);
        $users = Users::where('id', $siteassign->user_id)->first();

        $siteassign->user_id = $users->id;
        $siteassign->client_id = $client->id;
        $siteassign->client_name = $client->name;
        $siteassign->user_name = $users->name;
        $siteassign->site_id = $site->id;
        $siteassign->site_name = $site->name;
        $siteassign->date_range = $dateArray;
        $siteassign->shift_id = $shift->id;
        $siteassign->shift_time = $shift->shift_time;
        $siteassign->shift_name = $shift->shift_name;
        $siteassign->role_id = $users->role_id;
        $siteassign->weekoff = $weekoff;
        // $siteassign->timestamp = date('Y-m-d H:i:s');

        $siteassign->save();
        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Assign User",
            'message' => $client->name . " assigned to '" . $site->name . "' by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->route('clients.clientguard_read', [$client_id, $site_id, $siteassign->user_id]);
    }

    // get sites under clients
    public function getSites($id)
    {
        return json_encode(DB::table('site_details')->where('client_id', $id)->orderBy('name', 'Asc')->get());
    }

    // get shifts under sites
    public function getShifts($id)
    {
        return json_encode(ShiftAssigned::where('site_id', $id)->orderBy('shift_name', 'Asc')->get());
    }

    //unassigned guard list
    public function unAssignGuards()
    {
        $user = session('user');
        // dd('unassigned user');
        if ($user) {
            Log::info($user->name . ' view unassign employee list, User_id: ' . $user->id);

            $guards = SiteAssign::where('company_id', $user->company_id)
                ->pluck('user_id')
                ->toArray();

            // dd($guards);
            //DB::enableQueryLog();
            $unassignedGuards = Users::whereNotIn('id', $guards)
                ->where('company_id', $user->company_id)
                ->whereIn('role_id', [3, 6])
                ->where('showUser', 1)
                ->get();
            //dd(DB::getQueryLog());

            if (!empty($unassignedGuards)) {
                // dd($unassignedGuards , "un guards");
                return view('unassignguardlist')->with('unassignedGuards', $unassignedGuards);
            }
            else {
                // dd('here');
                return view('unassignguardlist', ['jsonMessage' => 'no data found']);
            }
        }
    }

    // clients under company
    public function fetchClients()
    {
        $user = session('user');
        if ($user->role_id == '1') {
            $clients = ClientDetails::where('company_id', $user->company_id)
                ->select('count(*) as allcount')
                ->count();
        }
        else if ($user->role_id == '2') {
            $clients = ClientDetails::where('company_id', $user->company_id)
                ->select('count(*) as allcount')
                ->count();
        }
        else if ($user->role_id == "7") {
            $clients = ClientDetails::where('company_id', $user->company_id)
                ->select('count(*) as allcount')
                ->count();
        }
        return $clients;
    }

    // fetch sites using ajax
    public function fetchSites()
    {
        $user = session('user');
        if ($user->role_id == '1') {
            $sitess = DB::table('site_details')
                ->where('company_id', $user->company_id)
                ->select('count(*) as allcount')
                ->count();
        }
        else if ($user->role_id == '2') {
            $sites = SiteAssign::where('user_id', $user->id)->first();
            $siteArray = json_decode($sites['site_id'], true);
            $sitess = DB::table('site_details')->whereIn('id', $siteArray)->select('count(*) as allcount')->count();
        }
        else if ($user->role_id == "7") {
            $clients = SiteAssign::where('user_id', $user->id)->pluck('site_id')->toArray();
            // $siteArray = SiteDetails::whereIn('client_id', json_decode($clients[0]))->pluck('id')->toArray();
            $sitess = DB::table('site_details')->whereIn('client_id', json_decode($clients[0], true))->select('count(*) as allcount')->count();
        }
        return $sitess;
    }

    // fetch present using ajax
    public function fetchCheckIn()
    {
        // dd('checkIn');
        $user = session('user');
        $cur_date = new DateTime();
        $date = $cur_date->format("Y-m-d");
        if ($user->role_id == '1') {
            $attendance = Attendance::where('company_id', $user->company_id)
                ->where('role_id', 3)
                ->where('dateFormat', $date)
                ->pluck('user_id')
                ->toArray();
            $present = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                ->where('users.company_id', $user->company_id)
                ->where('users.role_id', 3)->whereIn('users.id', $attendance)
                ->selectRaw('users.*, users.id as id, site_assign.site_name')
                ->select('count(*) as allcount')
                ->count();
        }
        else if ($user->role_id == '2') {
            $sites = SiteAssign::where('user_id', $user->id)->first();
            $siteArray = json_decode($sites['site_id'], true);
            $attendance = Attendance::whereIn('site_id', $siteArray)
                ->where('dateFormat', $date)
                ->pluck('user_id')
                ->toArray();
            $present = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                ->whereIn('users.id', $attendance)
                ->selectRaw('users.*, users.id as id, site_assign.site_name')
                ->select('count(*) as allcount')
                ->count();
        }
        else if ($user->role_id == '7') {
            $clients = SiteAssign::where('user_id', $user->id)->first();
            $siteArray = SiteDetails::whereIn('client_id', json_decode($clients->site_id, true))->pluck('id')->toArray();
            $userArray = SiteAssign::whereIn('site_id', $siteArray)->distinct('user_id')->pluck('user_id')->toArray();
            $attendance = Attendance::whereIn('user_id', $userArray)
                ->where('dateFormat', $date)
                ->pluck('user_id')
                ->toArray();
            $present = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                ->whereIn('users.id', $attendance)
                ->selectRaw('users.*, users.id as id, site_assign.site_name')
                ->select('count(*) as allcount')
                ->count();
        }

        return $present;
    }

    // fetch late guard using ajax
    public function fetchLateShow()
    {
        $user = session('user');
        $cur_date = new DateTime();
        $date = $cur_date->format("Y-m-d");
        if ($user->role_id == '1') {
            $lateAttendance = Attendance::where('company_id', $user->company_id)
                ->where('role_id', 3)
                ->whereNotNull('lateTime')
                ->where('dateFormat', $date)
                ->pluck('user_id')->toArray();
            $late = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
                ->where('users.company_id', $user->company_id)
                ->where('users.role_id', 3)->whereIn('users.id', $lateAttendance)
                ->selectRaw('users.*, users.id as id, site_assign.site_name')
                ->select('count(*) as allcount')
                ->count();
        }
        else if ($user->role_id == '2') {
            $sites = SiteAssign::where('user_id', $user->id)->first();
            $siteArray = json_decode($sites['site_id'], true);
            $lateAttendance = Attendance::whereIn('site_id', $siteArray)
                ->whereNotNull('lateTime')
                ->where('dateFormat', $date)
                ->pluck('user_id')
                ->toArray();
            $late = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                ->whereIn('users.id', $lateAttendance)
                ->selectRaw('users.*, users.id as id, site_assign.site_name')
                ->select('count(*) as allcount')
                ->count();
        }
        else if ($user->role_id == '7') {
            $clients = SiteAssign::where('user_id', $user->id)->pluck('site_id')->toArray();

            $siteArray = SiteDetails::whereIn('client_id', json_decode($clients[0], true))->pluck('id')->toArray();
            //dd($siteArray);
            //$siteIds = SiteAssign::whereIn('site_id', $siteArray)->get();
            //dd($siteIds);
            $userArray = SiteAssign::whereIn('site_id', $siteArray)->distinct('user_id')->pluck('user_id')->toArray();
            $lateAttendance = Attendance::whereIn('user_id', $userArray)
                ->whereNotNull('lateTime')
                ->where('dateFormat', $date)
                ->pluck('user_id')
                ->toArray();
            // dd($lateAttendance);
            $late = SiteAssign::leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                ->whereIn('users.id', $lateAttendance)
                ->selectRaw('users.*, users.id as id, site_assign.site_name')
                ->select('count(*) as allcount')
                ->count();
        //dd($late);
        }

        return $late;
    }

    //fetch activity Log
    public function fetchLog()
    {

        $user = session('user');
        $guardLog = ActivityLog::where('company_id', $user->company_id)->orderBy('id', 'desc')
            ->take(5)->get();
        return $guardLog;
    }


    //fetch absent using ajax
    public function fetchNoShow()
    {

        $user = session('user');
        $cur_date = new DateTime();
        $date = $cur_date->format("Y-m-d");
        if ($user->role_id == '1') {
            $attendance = Attendance::where('company_id', $user->company_id)
                ->where('role_id', 3)
                ->where('dateFormat', $date)
                ->pluck('user_id')
                ->toArray();
            $absent = Users::leftjoin('site_assign', 'users.id', '=', 'user_id')
                ->where('users.company_id', $user->company_id)
                ->where('users.role_id', 3)->whereNotIn('users.id', $attendance)
                ->selectRaw('users.*, users.id as id, site_assign.site_name')
                ->select('count(*) as allcount')
                ->count();
        }
        else if ($user->role_id == '2') {
            $sites = SiteAssign::where('user_id', $user->id)->first();
            $siteArray = json_decode($sites['site_id'], true);

            $attendance = Attendance::whereIn('site_id', $siteArray)
                ->where('dateFormat', $date)
                ->pluck('user_id')
                ->toArray();
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
        }
        else if ($user->role_id == '7') {
            $clients = SiteAssign::where('user_id', $user->id)->pluck('site_id')->toArray();

            $siteArray = SiteDetails::whereIn('client_id', json_decode($clients[0], true))->pluck('id')->toArray();

            $attendance = Attendance::whereIn('site_id', $siteArray)
                ->where('dateFormat', $date)
                ->pluck('user_id')
                ->toArray();
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
        //dd($absent);
        }
        return $absent;
    }

    //export of guard
    public function unassigned_export()
    {
        // dd('in un assigned export');
        $user = session('user');
        $absent = '';
        $late = '';
        $type = "unassign";

        $guard = SiteAssign::where('company_id', $user->company_id)
            ->where('role_id', 3)
            ->pluck('user_id')
            ->toArray();

        $guards = Users::whereNotIn('id', $guard)
            ->where('company_id', $user->company_id)
            ->where('role_id', 3)
            ->where('showUser', 1)
            ->get();

        $companyName = CompanyDetails::where('id', $user->company_id)
            ->pluck('name');

        $companyName = $companyName['0'];
        return $this->excel->download(new GuardExport($guards, $absent, $late, $companyName, $type), 'unassigned guards.xlsx');
    }



    // export of guard asigned to site
    public function assignedExport()
    {
        $cur_date = new DateTime();
        $type = "assign";
        $date = $cur_date->format("Y-m-d");
        $user = session('user');

        $companyId = $user->company_id;
        $roleId = $user->role_id;

        $attendanceQuery = Attendance::where('company_id', $companyId)
            ->where('role_id', 3)
            ->where('dateFormat', $date);

        $lateAttendanceQuery = Attendance::where('company_id', $companyId)
            ->where('role_id', 3)
            ->whereNotNull('lateTime')
            ->where('dateFormat', $date);

        if ($roleId === 1) {
            // Admin: All guards
            $userIds = Users::where('company_id', $companyId)->where('role_id', 3)->pluck('id')->toArray();
        }
        elseif ($roleId === 2) {
            // Supervisor: Only guards assigned to their sites
            $siteIds = SiteAssign::where('user_id', $user->id)
                ->pluck('site_id')
                ->map(fn($item) => is_string($item) ? json_decode($item, true) : $item)
                ->flatten()
                ->unique()
                ->toArray();

            $userIds = SiteAssign::whereIn('site_id', $siteIds)->pluck('user_id')->toArray();
        }
        elseif ($roleId === 7) {
            $siteAssigned = SiteAssign::where('user_id', $user->id)->first();
            $clientSiteIds = json_decode(optional($siteAssigned)->site_id ?? '[]', true);

            $userIds = SiteAssign::whereIn('client_id', $clientSiteIds)->pluck('user_id')->toArray();

        // dd($siteAssigned , $clientSiteIds , $userIds , "ids");
        }
        else {
            // Others: no access
            $userIds = [];
        }

        // Filter attendance by accessible user IDs
        $attendance = (clone $attendanceQuery)->whereIn('user_id', $userIds)->pluck('user_id')->toArray();
        $lateAttendance = (clone $lateAttendanceQuery)->whereIn('user_id', $userIds)->pluck('user_id')->toArray();

        // Present: Attended but not late
        $guards = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
            ->where('users.company_id', $companyId)
            ->where('users.role_id', 3)
            ->whereIn('users.id', $attendance)
            ->whereNotIn('users.id', $lateAttendance)
            ->selectRaw('users.*, users.id as id, site_assign.site_name, site_assign.date_range, site_assign.shift_name')
            ->orderBy('name', 'ASC')
            ->get();

        // Absent: Not in attendance
        $absent = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
            ->where('users.company_id', $companyId)
            ->where('users.role_id', 3)
            ->whereIn('users.id', $userIds)
            ->whereNotIn('users.id', $attendance)
            ->selectRaw('users.*, users.id as id, site_assign.site_name, site_assign.date_range, site_assign.shift_name')
            ->orderBy('name', 'ASC')
            ->get();

        // Late
        $late = Users::leftJoin('site_assign', 'users.id', '=', 'user_id')
            ->where('users.company_id', $companyId)
            ->where('users.role_id', 3)
            ->whereIn('users.id', $lateAttendance)
            ->selectRaw('users.*, users.id as id, site_assign.site_name, site_assign.date_range, site_assign.shift_name')
            ->orderBy('name', 'ASC')
            ->get();

        $companyName = CompanyDetails::where('id', $companyId)->value('name');

        return $this->excel->download(
            new GuardExport(
            $guards,
            $absent,
            $late,
            $companyName,
            $type
            ),
            'assigned guards.xlsx'
        );
    }
}

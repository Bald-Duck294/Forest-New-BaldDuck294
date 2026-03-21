<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Attendance;
use App\attendanceloggs;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportExport;
use Illuminate\Support\Facades\DB;
use DateTime;
use DateTimeZone;
use App\CompanyDetails;
use App\SupervisorDetails;
use App\User;
use App\SiteAssign;
use App\SiteDetails;
use Log;
use App\Users;
use App\ClientDetails;



class GuardReportController extends Controller
{

    // guard report view
    public function reportview()
    {

        $user = session('user');
        $sites = SiteAssign::where('user_id', $user->id)->first();
        $admin = '';

        Log::info($user->name . ' view report page, User_id: ' . $user->id);
        if ($user->role_id == '1') {
            // dump('hi ter');
            $supervisor = User::where('company_id', $user->company_id)->where('role_id', '=', 2)->orderBy('name')->get();

            $guards = DB::table('users')->where('company_id', $user->company_id)->where('role_id', '=', 3)->orderBy('name')->get();
            // DB::enableQueryLog();

            $sites = SiteDetails::where('company_id', $user->company_id)->orderBy('name', 'asc')->get()->groupBy('name');

            $admin = User::where('company_id', $user->company_id)->where('role_id', 7)->orderBy('name', 'asc')->get();

        // dd(DB::getQueryLog());
        // dd($sites);
        }
        else if ($user->role_id == '2') {
            $site = SiteAssign::where('user_id', $user->id)->where('role_id', 2)->first();
            $siteArray = json_decode($site['site_id'], true);
            //  dd($siteArray);
            $sites = DB::table('site_details')->whereIn('id', $siteArray)->get();
            // dd($sites);
            $supervisor = DB::table('users')->where('role_id', '=', 2)->where('company_id', $user->company_id)->orderBy('name')->get();
            $guards = DB::table('users')->where('company_id', $user->company_id)->where('role_id', '=', 3)->orderBy('name')->get();
        // dd($supervisor , $guards , "data");
        }
        else if ($user->role_id == '4') {
            $siteId = SiteDetails::where('client_id', $user->client_id)->pluck('id')->toArray();
            $sites = DB::table('site_details')->whereIn('id', $siteId)->get();
            $supervisor = DB::table('users')->where('role_id', '=', 2)->where('client_id', $user->client_id)->orderBy('name')->get();
            $guards = DB::table('users')->where('client_id', $user->client_id)->where('role_id', '=', 3)->orderBy('name')->get();
        }
        else if ($user->role_id == '7') {
            $siteAssigned = SiteAssign::where('user_id', $user->id)->first();

            $client_ids = json_decode($siteAssigned['site_id'], true);
            // dd($siteAssigned ,  $client_ids,"site Assigned");

            $siteArray = SiteDetails::whereIn('client_id', $client_ids)->pluck('id')->toArray();
            // dd($siteArray , "array");

            $supervisor = Users::where([['users.company_id', '=', $user->company_id], ['users.role_id', '=', 2]])
                ->leftJoin('site_assign as site', 'users.id', '=', 'site.user_id')
                ->where(function ($query) use ($siteArray) {
                foreach ($siteArray as $siteId) {
                    $query->orWhereRaw('JSON_CONTAINS(site.site_id, ?)', [json_encode([$siteId])]);
                }
            })->get();

            // $supervisor = User::where('company_id', $user->company_id)->where()->where('role_id', '=', 2)->orderBy('name')->get();
            $guards = DB::table('users')->where('company_id', $user->company_id)->where('role_id', '=', 3)->orderBy('name')->get();
            $sites = SiteDetails::where('company_id', $user->company_id)->orderBy('name', 'asc')->get()->groupBy('name');
            $siteArray = SiteAssign::where('company_id', $user->company_id)->where('user_id', $user->id)->pluck('site_id');

            //  dd(json_decode($siteArray[0] ), "site Array");
            // dump($supervisor->pluck('name')->toArray(), "supervisor");
            // dump($supervisor, "super v");
            $clients = ClientDetails::where('company_id', $user->company_id)
                ->whereIn('id', json_decode($siteArray[0]))
                ->get();

        // dd($clients , "cli");

        }

        if ($user->role_id != '7') {
            $clients = ClientDetails::where('company_id', $user->company_id)
                ->orderBy('name')
                ->get();
        }

        // dd($user);
        return view('guardsreports')
            ->with('user', $user)
            ->with('admins', $admin)
            ->with('supervisors', $supervisor)
            ->with('sites', $sites)
            ->with('guards', $guards)
            ->with('clients', $clients);
    }

    public function supervisorAttendance(Request $request)
    {
        //   dd($request);
        $user = session('user');

        return json_encode(Users::where('company_id', $user->company_id)->where('role_id', 2)->where('show_user', 1)->get());
    }

    // get supervisors
    public function getSupervisorGuard($id, Request $request)
    {
        $user = session('user');
        Log::info($user->name . ' supervisor under guards, User_id: ' . $user->id);
        return json_encode(SiteAssign::where('company_id', $user->company_id)->where('site_id', $request->id)->selectRaw('*,user_name  as name, user_id as id')->orderBy('user_name')->get());
    }

    // get supervisors
    public function getSupervisor($site_id, Request $request)
    {
        $user = session('user');
        Log::info($user->name . ' supervisor under guards, User_id: ' . $user->id);
        $assignedSupervisorsFromSiteAssigned = SiteAssign::where('site_id', 'like', '%' . $site_id . '%')->where('role_id', 2)->get();

        $assignedSupervisorsArray = [];
        foreach ($assignedSupervisorsFromSiteAssigned as $item) {
            $geoArray = json_decode($item['site_id'], true);
            foreach ($geoArray as $geo) {
                if ($site_id == $geo) {
                    $assignedSupervisorsArray[] = $item['user_id'];
                }
            }
        }
        return json_encode(Users::whereIn('id', $assignedSupervisorsArray)->get());
    }


    // public function getSupervisor($site_id, Request $request)
    // {
    //     dump('Here');
    //     $user = session('user');
    //     Log::info($user->name . ' supervisor under guards, User_id: ' . $user->id);

    //     // Get supervisors assigned to the site who belong to the same company
    //     $assignedSupervisorsFromSiteAssigned = SiteAssign::where('site_id', 'like', '%' . $site_id . '%')
    //         ->where('role_id', 2)
    //         ->whereHas('user', function($query) use ($user) {
    //             $query->where('company_id', $user->company_id);
    //         })
    //         ->get();

    //     $assignedSupervisorsArray = [];
    //     foreach ($assignedSupervisorsFromSiteAssigned as $item) {
    //         $geoArray = json_decode($item['site_id'], true);
    //         foreach ($geoArray as $geo) {
    //             if ($site_id == $geo) {
    //                 $assignedSupervisorsArray[] = $item['user_id'];
    //             }
    //         }
    //     }

    //     // Get only users who belong to the same company and are in the assigned supervisors array
    //     dd($assignedSupervisorsArray,"ids");
    //     return json_encode(
    //         Users::whereIn('id', $assignedSupervisorsArray)
    //             ->where('company_id', $user->company_id)
    //             ->get()
    //     );
    // }

    // sites under clients
    public function getClientSite($id, Request $request)
    {
        $user = session('user');
        return json_encode(SiteDetails::where('client_id', $id)->get());
    }
}

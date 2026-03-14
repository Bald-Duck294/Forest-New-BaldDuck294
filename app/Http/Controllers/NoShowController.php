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
use App\Exports\NoshowExport;
use Maatwebsite\Excel\Excel;

class NoShowController extends Controller
{
    private $excel;
    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
    }

    //absent guard list
    public function index()
    {
        $user = session('user');
        Log::info($user->name . ' view no show list, User_id: ' . $user->id);
        $cur_date = new DateTime();
        $date = $cur_date->format("Y-m-d");
        $absent = [];
        $sites = SiteAssign::where('user_id', $user->id)->first();

        $attendance = DB::table('attendance')->where('company_id', $user->company_id)->where('dateFormat', $date)->where('role_id', 3)->pluck('user_id')->toArray();
        $guards = DB::table('users')->where('company_id', $user->company_id)->where('role_id', 3)->whereNotIn('id', $attendance)->get();

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
                ->orderBy('name', 'ASC')->get();
        } else if ($user->role_id == "2") {
            if ($sites) {
                $siteArray = json_decode($sites['site_id'], true);
                $attendance = Attendance::whereIn('site_id', $siteArray)->where('dateFormat', $date)->pluck('user_id')->toArray();
                $absentArray = SiteAssign::whereIn('site_id', $siteArray)->whereNotIn('user_id', $attendance)->where('role_id', 3)->pluck('user_id')->toArray();

                $absent = DB::table('site_assign')
                    //->whereIn('id', $absentArray)
                    ->leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                    ->whereIn('users.id', $absentArray)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name')
                    ->orderBy('users.name', 'ASC')->get();
            }
        } else if ($user->role_id == "4") {
            $site = SiteDetails::where('client_id', $user->client_id)->pluck('id')->toArray();
            $attendance = Attendance::whereIn('site_id', $site)->where('dateFormat', $date)->pluck('user_id')->toArray();
            $absentArray = SiteAssign::whereIn('site_id', $site)->whereNotIn('user_id', $attendance)->where('role_id', 3)->pluck('user_id')->toArray();

            $absent = DB::table('site_assign')
                // ->whereIn('id', $absentArray)
                ->leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                ->whereIn('users.id', $absentArray)
                ->selectRaw('users.*, users.id as id, site_assign.site_name')
                ->orderBy('users.name', 'ASC')->get();
        } else if ($user->role_id == "7") {
            if ($sites) {
                $site = json_decode($sites['site_id'], true);
                $siteArray = SiteDetails::whereIn('client_id', $site)->pluck('id')->toArray();
                $userArray = SiteAssign::whereIn('site_id', $siteArray)->distinct('user_id')->pluck('user_id')->toArray();

                $attendance = Attendance::whereIn('user_id', $userArray)->where('dateFormat', $date)->pluck('user_id')->toArray();
                $absentArray = SiteAssign::whereIn('site_id', $siteArray)->whereNotIn('user_id', $attendance)->where('role_id', 3)->pluck('user_id')->toArray();

                $absent = DB::table('site_assign')
                    //->whereIn('id', $absentArray)
                    ->leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                    ->whereIn('users.id', $absentArray)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name')
                    ->orderBy('users.name', 'ASC')->get();
                // dd($absent);

            }
        }
        return view('noshowlist')->with('absent', $absent);
    }


    public function export()
    {

        $user = session('user');
        Log::info($user->name . ' view no show list, User_id: ' . $user->id);
        $cur_date = new DateTime();
        $date = $cur_date->format("Y-m-d");
        $absent = [];
        $sites = SiteAssign::where('user_id', $user->id)->first();

        $attendance = DB::table('attendance')->where('company_id', $user->company_id)->where('dateFormat', $date)->where('role_id', 3)->pluck('user_id')->toArray();
        $guards = DB::table('users')->where('company_id', $user->company_id)->where('role_id', 3)->whereNotIn('id', $attendance)->get();

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
                ->orderBy('name', 'ASC')->get();
        } else if ($user->role_id == "2") {
            if ($sites) {
                $siteArray = json_decode($sites['site_id'], true);
                $attendance = Attendance::whereIn('site_id', $siteArray)->where('dateFormat', $date)->pluck('user_id')->toArray();
                $absentArray = SiteAssign::whereIn('site_id', $siteArray)->whereNotIn('user_id', $attendance)->where('role_id', 3)->pluck('user_id')->toArray();

                $absent = DB::table('site_assign')
                    //->whereIn('id', $absentArray)
                    ->leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                    ->whereIn('users.id', $absentArray)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name')
                    ->orderBy('users.name', 'ASC')->get();
            }
        } else if ($user->role_id == "4") {
            $site = SiteDetails::where('client_id', $user->client_id)->pluck('id')->toArray();
            $attendance = Attendance::whereIn('site_id', $site)->where('dateFormat', $date)->pluck('user_id')->toArray();
            $absentArray = SiteAssign::whereIn('site_id', $site)->whereNotIn('user_id', $attendance)->where('role_id', 3)->pluck('user_id')->toArray();

            $absent = DB::table('site_assign')
                //->whereIn('id', $absentArray)
                ->leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                ->whereIn('users.id', $absentArray)
                ->selectRaw('users.*, users.id as id, site_assign.site_name')
                ->orderBy('users.name', 'ASC')->get();
        } else if ($user->role_id == "7") {
            if ($sites) {
                $siteArray = json_decode($sites['site_id'], true);
                $attendance = Attendance::whereIn('site_id', $siteArray)->where('dateFormat', $date)->pluck('user_id')->toArray();
                $absentArray = SiteAssign::whereIn('site_id', $siteArray)->whereNotIn('user_id', $attendance)->where('role_id', 3)->pluck('user_id')->toArray();

                $absent = DB::table('site_assign')
                    //->whereIn('id', $absentArray)
                    ->leftjoin('users', 'site_assign.user_id', '=', 'users.id')
                    ->whereIn('users.id', $absentArray)
                    ->selectRaw('users.*, users.id as id, site_assign.site_name')
                    ->orderBy('users.name', 'ASC')->get();
            }
        }

        $companyName = CompanyDetails::where('id', $user->company_id)->pluck('name');
        $companyName = $companyName['0'];
        // dd($companyName,$absent);
        return $this->excel->download(new NoshowExport($absent, $companyName), 'absent.xlsx');
    }
}

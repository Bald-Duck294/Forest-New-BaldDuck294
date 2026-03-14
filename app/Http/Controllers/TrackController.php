<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\GuardLiveLocationData;
use App\SiteAssign;
use App\SiteDetails;
use App\SiteGeofences;
use App\ClientDetails;
use App\User;
use App\ActivityLog;
use Log;
use App\Users;


class TrackController extends Controller
{
    //show guard list, site list
    public function index($track)
    {

        $user = session('user');
        if ($track == 'track-guard') {
            Log::info($user->name . ' view track guard by guard list, User_id: '. $user->id);
            if ($user->role_id == "1" ) {
                $guards = User::where('role_id', 3)->where('company_id', $user->company_id)->get();
            } 
            else if($user->role_id == "7") {
             $site_id = SiteAssign::where('user_id', $user->id)->first();
                $siteArray = json_decode($site_id['site_id'], true);
                // dd($siteArray);
                $sites = SiteAssign::whereIn('client_id', $siteArray)->where('role_id', 3)->pluck('user_id')->toArray();
                // dd($sites);
                $guards = User::whereIn('id', $sites)->where('company_id', $user->company_id)->get();
                // $guards = User::where('role_id',3)->where('company_id', $user->company_id)->get();
            }else if ($user->role_id == '2') {
                $site_id = SiteAssign::where('user_id', $user->id)->first();
                $siteArray = json_decode($site_id['site_id'], true);
                $sites = SiteAssign::whereIn('site_id', $siteArray)->where('role_id', 3)->pluck('user_id')->toArray();
                $guards = User::whereIn('id', $sites)->get();
            } else if ($user->role_id == '4') {
                $siteArray = SiteDetails::where('client_id', $user->client_id)->pluck('id')->toArray();
                $sites = SiteAssign::whereIn('site_id', $siteArray)->where('role_id', 3)->pluck('user_id')->toArray();
                $guards = User::whereIn('id', $sites)->get();
            }
            return view('trackguardlist')->with('guards', $guards);
        } else if ($track == 'track-guard-by-site') {
            Log::info($user->name . ' track guard by site list, User_id: '. $user->id);
            if ($user->role_id == "1") {
                $sites = SiteDetails::where('company_id', $user->company_id)->get();
            }
            else if ($user->role_id == '2') {
                $site_id = SiteAssign::where('user_id', $user->id)->first();
                $siteArray = json_decode($site_id['site_id'], true);
                $sites = SiteDetails::whereIn('id', $siteArray)->get();
            } else if ($user->role_id == '4') {
                $siteArray = SiteDetails::where('client_id', $user->client_id)->pluck('id')->toArray();
                $sites = SiteDetails::whereIn('id', $siteArray)->get();
                // $guards = User::whereIn('id', $sites)->get();
            }
            else if($user->role_id == 7){
                $siteAssigned = SiteAssign::where('user_id', $user->id)->first();
                if ($siteAssigned) {
                    $clientIds = json_decode($siteAssigned->site_id, true);
                    $sites = SiteDetails::whereIn('client_id', $clientIds)->get();
                }
                else
                    $sites = [];
            }
            return view('tracksiteassignlist')->with('sites', $sites);
        } else if ($track == 'all-guard') {
            Log::info($user->name . ' track all guard, User_id: '. $user->id);
            if ($user->role_id == "1") {
                $guards = User::where('role_id', 3)->where('company_id', $user->company_id)->pluck('id')->toArray();
                $sites = SiteAssign::whereIn('user_id', $guards)->get();
                $geofences = SiteGeofences::whereIn('site_id', $sites)->get();
                $geofences->each(function ($geofence) {
                    $geofence->makeHidden('poly_coords');
                });
                // dd($geofences);
                $locations = GuardLiveLocationData::whereIn('user_id', $guards)->get();
                return view('track')->with('data', $locations)->with('option', $track)->with('geofences', $geofences);
            } else if ($user->role_id == '2') {
                $user_id = SiteAssign::where('role_id', 3)->pluck('user_id')->toArray();
                $sites = SiteAssign::where('user_id', $user_id)->pluck('user_id')->toArray();
                $geofences = SiteGeofences::where('site_id', $sites)->get();
                // dd($geofences);
                $geofences->each(function ($geofence) {
                    $geofence->makeHidden('poly_coords');
                });
                $locations = GuardLiveLocationData::whereIn('user_id', $user_id)->get();
                //print_r($locations);exit;
                return view('track')->with('data', $locations)->with('option', $track)->with('geofences', $geofences);
            } elseif ($user->role_id == '4') {
                $guards = User::where('role_id', 3)->where('id', $user->id)->pluck('id')->toArray();
                $geofences = SiteGeofences::where('user_id', $user->id)->first();
                // dd($geofences);
                $geofences->each(function ($geofence) {
                    $geofence->makeHidden('poly_coords');
                });
                $locations = GuardLiveLocationData::whereIn('user_id', $guards)->get();
                return view('track')->with('data', $locations)->with('option', $track)->with('geofences', $geofences);
            }
        }
    }

    // track guard
    public function trackByGuard($guard_id, $option)
    {
        $user = session('user');
        Log::info($user->name . ' view track by guard, User_id: '. $user->id);
        $sites = SiteAssign::where('user_id', $guard_id)->first();
        $GuardName = Users::where('id', $guard_id)->first();
        $siteArray = json_decode($sites['site_id'], true);
        $geofences = SiteGeofences::where('site_id', $siteArray)->get();
        
        $geofences->each(function ($geofence) {
            $geofence->makeHidden('poly_coords');
        });
        // dd($geofences);
        $locations = GuardLiveLocationData::where('user_id', $guard_id)->get();
        // dd($sites);
        if (count($locations) > 0) {
            return view('track')->with('data', $locations)->with('GuardName', $GuardName)->with('option', $option)->with('geofences', $geofences)->with('site_id', null);
        } else {
            return redirect()->back()->with('dataNotFound', 'Records not found');
        }
    }

    //track guard by using sites
    public function trackbysite($site_id, $option)
    {
        $user = session('user');
        Log::info($user->name . ' view track guard by site, User_id: '. $user->id);
        $assign_site = SiteAssign::where('site_id', $site_id)->pluck('user_id')->toArray();
        $siteName = SiteAssign::where('site_id', $site_id)->first();

        $geofences = SiteGeofences::where('site_id', $site_id)->get();
        
        $geofences->each(function ($geofence) {

        $geofence->makeHidden('poly_coords');

        });

        $locations = GuardLiveLocationData::whereIn('user_id', $assign_site)->get();

        //dd($site_id);
        if (count($locations) > 0) {

            return view('track')->with('data', $locations)->with('siteName', $siteName)->with('option', $option)->with('geofences', $geofences)->with('site_id', $site_id);
        
        } else {

            return redirect()->back()->with('dataNotFound', 'Records not found');
        }
    }

    // track supervisor
    public function trackbysupervisor($option)
    {
        // dd($option);
        $user =  session('user');
        //dd($user);
        Log::info($user->name . ' view supervisor list, User_id: '. $user->id);
        if ($user->role_id == '1') {
            $records = DB::table('users')
                ->where('users.company_id', $user->company_id)
                ->leftJoin('site_assign', 'users.id', '=', 'site_assign.user_id')
                ->where('users.role_id', 2)
                ->select('users.id as userId', 'users.name', 'site_assign.shift_name', 'site_assign.shift_time')
                ->get();
        } 
        else if($user->role_id == '7')
        {

            $clients = SiteAssign::where('user_id', $user->id)->pluck('site_id')->toArray();
            $siteArray = SiteDetails::whereIn('client_id', json_decode($clients[0], true))->pluck('id')->toArray();

            $records = DB::table('users')
            ->where('users.company_id', $user->company_id)
            ->where('users.role_id',2)
            ->leftjoin('site_assign as site', 'users.id', '=', 'site.user_id')
                    ->where(function ($query) use ($siteArray) {
                        foreach ($siteArray as $siteId) {
                            // $query->orWhereRaw('FIND_IN_SET(?, site.site_id)', [$siteId]);
                            $query->orWhereRaw('JSON_CONTAINS(site_id, ?)', [json_encode($siteId)]);
                        }
                    })
            ->select('users.id as userId', 'users.name', 'site.shift_name', 'site.shift_time')
            ->get();
        }
        else if($user->role_id == '2') {
            $records = DB::table('users')
                ->where('users.company_id', $user->company_id)
                ->leftJoin('site_assign', 'users.id', '=', 'site_assign.user_id')
                ->where('users.role_id', 2)
                ->select('users.id as userId', 'users.name', 'site_assign.shift_name', 'site_assign.shift_time')
                ->get();
        }
        return view('supervisorlist')->with('option', $option)->with('supervisors', $records);
    }

    // updation of track data
    public function updateTrackData(Request $request, $option)
    {
        $user = session('user');
        Log::info($user->name . ' update track data, User_id: '. $user->id);
        if ($option == 'trackbyguard') {
            $locations = GuardLiveLocationData::where('user_id', $request->user_id)->get();
            return $locations;
        } else if ($option == 'trackbysite') {
            $assign_site = SiteAssign::where('site_id', $request->site_id)->pluck('user_id')->toArray();
            $locations = GuardLiveLocationData::whereIn('user_id', $assign_site)->get();
            return $locations;
        } else {

            $locations = GuardLiveLocationData::where('company_id', $user->company_id)->get();
            return $locations;
        }
    }
}

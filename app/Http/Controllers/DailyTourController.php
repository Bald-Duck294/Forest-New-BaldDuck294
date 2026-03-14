<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\GuardTourLog;
use App\SiteAssign;
use App\TourCheckPointStatus;
use App\User;
use App\GuardTour;
use App\SiteDetails;
use App\DailyUpdates;
use Log;


class DailyTourController extends Controller
{

    // daily tour - site list
    public function index()
    {
        $user = session('user');
        Log::info($user->name . ' view daily tour site list, User_id: '. $user->id);
        // $todayDate = date("Y-m-d");
        if ($user->role_id == "1") {
            $tours = SiteDetails::where([['company_id', $user->company_id]])->orderBy('name', 'asc')->get();
            //  dd($tours);exit;
            return view('dailytoursitelist')->with('tours', $tours);
        } else if ($user->role_id == '2') {
            $site_id = SiteAssign::where('user_id', $user->id)->first();
            $siteArray = json_decode($site_id['site_id'], true);
            $tours = SiteDetails::whereIn('id', $siteArray)->orderBy('name', 'asc')->get();
            return view('dailytoursitelist')->with('tours', $tours);
        } else if ($user->role_id == '4') {
            $tours = SiteDetails::where('client_id', $user->client_id)->orderBy('name', 'asc')->get();
            return view('dailytoursitelist')->with('tours', $tours);
        }
    }

    // daily tour list
    public function getTours($site_id)
    {
        $user = session('user');
        Log::info($user->name . ' view daily tour list, User_id: '. $user->id);
        $todayDate = date("Y-m-d");
        $tours = GuardTourLog::where([['site_id', $site_id], ['date', $todayDate]])->get();
        return view('dailytourlist')->with('tours', $tours)->with('site_id', $site_id);
    }

    // tour details with checkpoint
    public function getToursDetails($tourLogId, $guardId)
    {
        $user = session('user');
        Log::info($user->name . ' view tour details, User_id: '. $user->id);
        $todayDate = date("Y-m-d");
        $checkpoints = TourCheckPointStatus::where([['tourLogId', '=', $tourLogId], ['date', $todayDate], ['guardId', $guardId]])->get();
        $guardDetails = GuardTourLog::where([['id', '=', $tourLogId], ['guardId', $guardId], ['date', $todayDate]])->first();
        return view('tourdetails')->with('checkpoints', $checkpoints)->with('guardDetails', $guardDetails);
    }

    // Daily update
    public function DailyUpdate($siteId)
    {
        $user = session('user');
        Log::info($user->name . ' view daily update page of Daily update, User_id: '. $user->id);
        $dailyupdate = DailyUpdates::where('site_id', $siteId)->orderBy('user_name', 'asc')->get();
        $siteName = SiteDetails::where('id', $siteId)->first();
        return view('dailyupdate')->with('dailyupdate', $dailyupdate)->with('siteName', $siteName->name);
    }

    // daily upload images
    public function uploadImages($rowId)
    {
        $user = session('user');
        Log::info($user->name . ' view images, User_id: '. $user->id);
        $photos = DailyUpdates::where('id', $rowId)->first();
        return $photos->photos;
    }
}

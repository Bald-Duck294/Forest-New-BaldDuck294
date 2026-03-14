<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Geofences;
use App\Users;
use App\SiteAssign;
use App\ClientDetails;
use App\Attendance;
use Log;
use App\ActivityLog;
use App\SiteGeofences;


class GeofencesController extends Controller
{
    // public function index()
    // {
    //     return view('clientsitelist');
    // }

    // public function allGeofences()
    // {
    //     $user = session('user');
    //     if ($user->role_id == 7) {
    //         $sites = SiteAssign::where('company_id', $user->company_id)->where('user_id', $user->id)->first();
    //         // dd($sites);
    //         $sitesIds = json_decode($sites->site_id, true);
    //         // dd($sitesIds);
    //         if (isset($sitesIds)) {

    //             $geofences = SiteGeofences::where('company_id', $user->company_id)->whereIn('client_id', $sitesIds)->get();
    //             // $geofences = SiteGeofences::where('company_id', $user->company_id)->get();
    //             //dd($records);
    //         } else {
    //         }
    //     } else {
    //         $geofences = SiteGeofences::where('company_id', $user->company_id)->get();
    //     }

    //     return view('allgeofencelist')->with('geofences', $geofences);
    // }

    public function allGeofences()
    {
        $user = session('user');

        if ($user->role_id == 7) {
            $sites = SiteAssign::where('company_id', $user->company_id)
                ->where('user_id', $user->id)
                ->first();
            $sitesIds = json_decode($sites->site_id);

            if (isset($sitesIds)) {
                $geofences = SiteGeofences::where('site_geofences.company_id', $user->company_id)
                    ->whereIn('site_geofences.client_id', $sitesIds)
                    ->leftJoin('client_details', 'site_geofences.client_id', '=', 'client_details.id')
                    ->leftJoin('site_details', 'site_geofences.site_id', '=', 'site_details.id')
                    ->select(
                        'site_geofences.*',
                        'client_details.name as client_name',
                        'site_details.name as site_name'
                    )
                    ->get();
            } else {
                $geofences = collect();
            }
        } else {
            $geofences = SiteGeofences::where('site_geofences.company_id', $user->company_id)
                ->leftJoin('client_details', 'site_geofences.client_id', '=', 'client_details.id')
                ->leftJoin('site_details', 'site_geofences.site_id', '=', 'site_details.id')
                ->select(
                    'site_geofences.*',
                    'client_details.name as client_name',
                    'site_details.name as site_name'
                )
                ->get();
        }

        // dump('testing');
        return view('allgeofencelist')->with('geofences', $geofences);
    }

    public function getUser(Request $request)
    {
        $user = session('user');
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value
        // Total records

        //DB::enableQueryLog();
        if ($user->role_id == '1') {
            $totalRecords = DB::table('site_details')->where('company_id', $user->company_id)->select('count(*) as allcount')->count();

            $totalRecordswithFilter = DB::table('site_details')->where('company_id', $user->company_id)->select('count(*) as allcount')->where('name', 'like', '%' . $searchValue . '%')->count();

            $records = DB::table('site_details')->orderBy($columnName, $columnSortOrder)
                ->where('name', 'like', '%' . $searchValue . '%')
                ->where('company_id', $user->company_id)
                ->select('*')
                ->skip($start)
                ->take($rowperpage)
                ->get();
        } else if ($user->role_id == '2') {
            $totalRecords = DB::table('site_details')->select('count(*) as allcount')->count();
            $totalRecordswithFilter = DB::table('site_details')->select('count(*) as allcount')->where('name', 'like', '%' . $searchValue . '%')->count();
            $records = DB::table('geofences')->orderBy($columnName, $columnSortOrder)
                ->where('name', 'like', '%' . $searchValue . '%')
                ->select('*')
                ->skip($start)
                ->take($rowperpage)
                ->get();
        } else {
            $totalRecords = DB::table('geofences')->where('role_id', '=', $user->role_id)->whereNull('emp_id')->select('count(*) as allcount')->count();
            $totalRecordswithFilter = DB::table('geofences')->select('count(*) as allcount')->where('name', 'like', '%' . $searchValue . '%')->count();
            $records = DB::table('geofences')->orderBy($columnName, $columnSortOrder)
                ->where('name', 'like', '%' . $searchValue . '%')
                ->select('*')
                ->skip($start)
                ->take($rowperpage)
                ->get();
        }


        $data_arr = array();
        $sno = $start + 1;
        foreach ($records as $record) {
            // $profile_pic = $record->profile_pic;

            $name = $record->name;


            $id = $record->id;

            $data_arr[] = array(
                // "profile_pic" => $profile_pic,

                "name" => $name,

                "id" => $id,
            );
        }

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr
        );

        echo json_encode($response);
        exit;
    }

    // view geofence
    public function viewGeofence($geoId)
    {
        $locations = SiteGeofences::where('id', $geoId)->first();
        //dd($locations);
        $locations->makeHidden('poly_coords');
        return view('gmaps')->with('data', $locations);
    }

    // guard details list
    public function getGuards($id)
    {
        ///DB::enableQueryLog();
        $assignSites = SiteAssign::select('users.id as userID', 'users.name as guardName')->join('users', 'users.id', 'site_assign.user_id')->where('site_assign.geo_id', $id)->get();
        // print_r(\DB::getQueryLog());exit;
        return view('guarddetailslist')->with('assignSites', $assignSites);
    }

    //guard details
    public function getGuardDetails($id)
    {
        $date = date('d-m-Y');
        //DB::enableQueryLog();
        $userDetails = Users::select(
            'users.name as guardName',
            'users.contact as guardContact',
            'users.address as guardAddress',
            'attendance.exit_date_time as guardExit',
            'attendance.entry_date_time as guardEntry',
            'attendance.duration_for_calc as noOfHours',
            'supervisor_details.name as supervisor_name'
        )
            ->leftjoin('attendance', 'users.id', 'attendance.user_id')
            ->leftjoin('incidence_details', 'attendance.geo_id', 'incidence_details.geo_id')
            ->leftjoin('supervisor_details', 'incidence_details.supervisor_id', 'supervisor_details.id')
            ->where('users.id', $id)
            ->first();
        //print_r(\DB::getQueryLog());exit;
        $incidentDate = Attendance::select('incidence_details.date as   incident_date')->leftjoin('incidence_details', 'attendance.geo_id', 'incidence_details.geo_id')->where('incidence_details.date', $date)->where('attendance.user_id', $id)->first();
        // print_r($incidentDate->incident_date);exit;
        return view('guarddetails')->with('guardDetails', $userDetails)->with('incidentDate', $incidentDate);
    }

    public function geofence_copy($client_id, $site_id, $id)
    {

        //dd($client_id,$site_id,$id);
        $user = session('user');
        $company = session('company');
        // Log::channel('create')->info($company->name . ' view copy geofence form');
        $geofence = SiteGeofences::find($id);
        $geofence->makeHidden('poly_coords');
        $clients = ClientDetails::where('company_id', $user->company_id)->get();

        return view('copygeofence')->with('geofence', $geofence)->with('clients', $clients)->with('id', $id)->with('site_id', $site_id)->with('client_id', $client_id);
    }
    public function geofenceCopyAction(Request $request)
    {
        $company = session('company');

        //dd($request->all());

        // if ($_GET['type'] == 'Polygon') {
        //     $pointsArray = json_decode($_GET['poly_coords']); // Expecting an array of [lat, lng] pairs

        //     $wktPolygon = $this->convertToWktPolygon($pointsArray);

        //     $poly = DB::raw("ST_GeomFromText('$wktPolygon')");
        //     $poly_lat_lng = $_GET['poly_coords'];
        // } else {
        //     $poly = NULL;
        //     $poly_lat_lng = NULL;
        // }

        $location = SiteGeofences::find($request->geo_id);

        // $loc = json_decode($_GET['center']);
        // $location->radius = $_GET['radius'];
        // $location->type = $_GET['type'];
        // $location->poly_coords = $poly;
        // $location->poly_lat_lng = $poly_lat_lng;
        // $location->name = $_GET['name'];
        // $location->center = $_GET['center'];

        // if ($_GET['type'] == 'Circle') {
        //     $location->lat = $loc->lat;
        //     $location->lng = $loc->lng;
        // }

        // $location->site_id = $site_id;
        // $location->client_id = $client_id;
        // $location->company_id = $company->id;
        // $result = $location->save();

        $result = $location->replicate();
        $result->name = $request->name;
        $result->site_id = $request->site;
        $result->client_id = $request->client;
        $result->save();
        // if ($result) {
        //     return "Geofence Copied";
        // } else {
        //     return "Something went wrong!";
        // }
        return redirect()->route('clients.getclientgeofences', [$request->client, $request->site]);
    }


    public function geofence_bulk_copy()
    {
        $user = session('user');
        $clients = ClientDetails::where('company_id', $user->company_id)->get();

        return view('bulkcopygeofence')->with('clients', $clients);
    }

    public function geofenceBulkCopyAction(Request $request)
    {
        $geofenceIds = json_decode($request->geofence_ids);
        $clientId = $request->client;
        $siteId = $request->site;

        $copiedCount = 0;

        foreach ($geofenceIds as $geoId) {
            $location = SiteGeofences::find($geoId);

            if ($location) {
                $result = $location->replicate();
                $result->client_id = $clientId;
                $result->site_id = $siteId;
                $result->save();
                $copiedCount++;
            }
        }

        return redirect()->route('clients.getclientgeofences', [$clientId, $siteId])
            ->with('success', "{$copiedCount} geofence(s) copied successfully");
    }

    // Bulk delete
    public function geofenceBulkDelete(Request $request)
    {
        $geofenceIds = $request->geofence_ids;

        SiteGeofences::whereIn('id', $geofenceIds)->delete();

        return response()->json(['success' => true, 'message' => 'Geofences deleted successfully']);
    }
}

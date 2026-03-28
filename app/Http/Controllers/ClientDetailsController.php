<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use App\States;
use App\Exports\TourDetailsExport;
use App\Cities;
use App\Geofences;
use App\ClientDetails;
use App\SiteDetails;
use App\ShiftAssigned;
use App\SiteAssign;
use App\GuardTour;
use App\Users;
use App\Shifts;
use App\GuardTourCheckpoints;
use App\GuardTourLog;
use App\SiteGeofences;
use App\Attendance;
use App\ActivityLog;
use App\RegistrationData;
use Log;
use App\CompanyDetails;
use Session;
use App\ClientComplaints;
use App\Notifications;
use App\FCMNotify;
use App\Exports\ClientExport;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Grimzy\LaravelMysqlSpatial\Types\Polygon;
use Grimzy\LaravelMysqlSpatial\Types\LineString;

class ClientDetailsController extends Controller
{
    private $excel;
    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
    }

    //client list page
    // public function index()
    // {
    //     $user = session('user');
    //     // dd($user);
    //     if ($user->role_id == 7) {

    //         $sites = SiteAssign::where('company_id', $user->company_id)->where('user_id', $user->id)->first();
    //         //dd($sites);
    //         $sitesIds = json_decode($sites->site_id, true);
    //         if (isset($sitesIds)) {

    //             $records = ClientDetails::where('company_id', $user->company_id)->whereIn('id', $sitesIds)->get();
    //             //dd($records);
    //         } else {

    //             $records = ClientDetails::where('company_id', $user->company_id)->get();
    //         }
    //     } else {
    //         $records = ClientDetails::where('company_id', $user->company_id)->get();
    //     }

    //     return view('clientdetails')->with('clients', $records);
    // }

    public function index(Request $request)
    {
        $user = session('user');
        $search = $request->input('search');
        $sort = $request->input('sort', 'id');
        $dir = $request->input('dir', 'desc');

        $query = ClientDetails::where('company_id', $user->company_id);

        if ($user->role_id == 7) {
            $sites = SiteAssign::where('company_id', $user->company_id)->where('user_id', $user->id)->first();
            $sitesIds = json_decode($sites->site_id ?? '[]', true);

            if (!empty($sitesIds)) {
                $query->whereIn('id', $sitesIds);
            }
        }

        // Search logic
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('city', 'LIKE', "%{$search}%")
                    ->orWhere('state', 'LIKE', "%{$search}%");
            });
        }

        // Sorting logic
        $allowedSorts = ['name', 'state', 'city', 'isActive', 'id', 'status'];
        if (in_array($sort, $allowedSorts)) {
            $sortColumn = ($sort == 'status') ? 'isActive' : $sort;
            $query->orderBy($sortColumn, $dir == 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('id', 'desc');
        }

        $records = $query->paginate(10);

        if ($request->ajax()) {
            return view('partials.clients_table', ['clients' => $records])->render();
        }

        return view('clientdetails')->with('clients', $records);
    }
    //client list page
    public function getClients()
    {
        $user = session('user');
        $records = ClientDetails::where('company_id', $user->company_id)->paginate(10);
        return view('clientdetails')->with('clients', $records);
    }

    /* ... Keep your existing create(), getCity(), create_action(), editClient(), editaction(), deleteClient() methods exactly as they were ... */

    // status change to inactive
    public function statusInactive($clientId)
    {
        $client = ClientDetails::find($clientId);
        if ($client) {
            $client->isActive = "0";
            $client->save();
        }
        return back()->with('success', 'Status changed to Inactive successfully!');
    }

    // status change to active
    public function statusActive($clientId)
    {
        $client = ClientDetails::find($clientId);
        if ($client) {
            $client->isActive = "1";
            $client->save();
        }
        return back()->with('success', 'Status changed to Active successfully!');
    }

    //create client form
    public function create()
    {
        $user = session('user');
        if ($user) {
            Log::channel('create')->info($user->name . ' view client create form');

            $states = DB::table('states')->orderBy('name', 'Asc')->get();
            $cities = [];

            // Fetch cities if page reloads due to validation failure
            if (old('state')) {
                $currentState = old('state');
                // Find the state by its name to get the code
                $s = DB::table('states')->where('name', $currentState)->orWhere('code', $currentState)->first();
                if ($s) {
                    $cities = DB::table('cities')->where('state_code', $s->code)->orderBy('name', 'Asc')->get();
                }
            }

            return view('createclient')->with('states', $states)->with('cities', $cities);
        }
    }
    //get city using ajax
    public function getCity($id)
    {
        $user = session('user');

        // 1. Check if the frontend sent a numeric ID (like 21) instead of a code (like 'MH')
        if (is_numeric($id)) {
            // Find the state by its ID to get its 2-letter code
            $state = DB::table('states')->where('id', $id)->first();

            // If the state is found, overwrite the $id variable with the actual code (e.g., 'MH')
            if ($state) {
                $id = $state->code;
            }
        }

        // 2. Now query the cities table using the correct string code
        $cities = DB::table('cities')
            ->where('state_code', $id)
            ->orderBy('name', 'Asc')
            ->get();

        // 3. Return as a proper JSON response
        return response()->json($cities);
    }

    // save client data in database
    // save client data in database
    // save client data in database
    public function create_action(Request $request)
    {
        $user = session('user');
        if ($user) {

            $messages = [
                'name.required' => '*Required',
                'address.required' => '*Required',
                'state.required' => '*Required',
                'city.required' => '*Required',
                'pincode.required' => '*Required',
                'contactperson.required' => '*Required',
                'contactnumber.required' => '*Required',
                'email.required' => '*Required',
                'relationshipmanager.required' => '*Required',
                'relationshipmanagercontact.required' => '*Required'
            ];

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'address' => 'required|max:100',
                'state' => 'required',
                'city' => 'required',
                'pincode' => 'required|numeric|min:6',
                'contactperson' => 'required',
                'contactnumber' => 'required|min:10',
                'email' => 'required|email',
                'relationshipmanager' => 'required',
                'relationshipmanagercontact' => 'required'
            ], $messages);

            if ($validator->fails()) {
                return redirect('clients/create')
                    ->withErrors($validator)
                    ->withInput();
            } else {

                $number = $request->contactnumber;

                // Explicitly send a red error message to the contact number field if it exists
                if (ClientDetails::where('contact', $number)->exists()) {
                    return redirect('clients/create')
                        ->withErrors(['contactnumber' => 'This Contact Number is already registered to another client.'])
                        ->withInput();
                } else {

                    $new_client = new ClientDetails();

                    $new_client->name = $_POST['name'];
                    $new_client->address = $_POST['address'];
                    $new_client->state = $_POST['state'];
                    $new_client->city = $_POST['city'];
                    $new_client->pincode = $_POST['pincode'];
                    $new_client->spokesperson = $_POST['contactperson'];
                    $new_client->contact = $_POST['contactnumber'];
                    $new_client->email = $_POST['email'];
                    $new_client->relationManager = $_POST['relationshipmanager'];
                    $new_client->relationManagerContact = $_POST['relationshipmanagercontact'];
                    $new_client->company_id = $user->company_id;
                    $new_client->save();

                    $name = explode(" ", $_POST['name']);
                    $company = CompanyDetails::where('id', $user->company_id)->first();

                    $new_user = new RegistrationData();
                    $new_user->firstName = $name[0];
                    if (isset($name[1])) {
                        $new_user->lastName = $name[1];
                    }
                    $new_user->mobile = $_POST['contactnumber'];

                    // FIXED NULL ERROR: Use $user->company_id directly, and add a fallback for the name
                    $new_user->company_id = $user->company_id;
                    $new_user->company_name = $company ? $company->name : 'Unknown Company';

                    $new_user->email = $_POST['email'];
                    $new_user->role_id = "4";
                    $new_user->save();

                    ActivityLog::create([
                        'company_id' => $user->company_id,
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'type' => "Create Client",
                        'message' => $_POST['name'] . " client created by " . $user->name,
                        'date_time' => date('Y-m-d H:i:s'),
                    ]);

                    return \Redirect::route('clients')->with('success', 'Client created successfully.');
                }
            }
        }
    }

    //client edit form
    //client edit form
    public function editClient($id)
    {
        $user = session('user');
        if ($user) {
            Log::channel('create')->info($user->name . ' view client edit form');

            $states = DB::table('states')->orderBy('name', 'Asc')->get();
            $clients = ClientDetails::find($id);

            $cities = [];
            $currentState = old('state', $clients->state);

            // Fetch cities for the saved state or old input
            if ($currentState) {
                $s = DB::table('states')->where('name', $currentState)->orWhere('code', $currentState)->first();
                if ($s) {
                    $cities = DB::table('cities')->where('state_code', $s->code)->orderBy('name', 'Asc')->get();
                }
            }

            return view('updateclient')
                ->with('clients', $clients)
                ->with('id', $id)
                ->with('states', $states)
                ->with('cities', $cities);
        }
    }

    //save updated data in database
    //save updated data in database
    public function editaction($id, Request $request)
    {
        $user = session('user');
        if ($user) {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'address' => 'required|max:100',
                'state' => 'required',
                'city' => 'required',
                'pincode' => 'required|numeric|min:6',
                'contactperson' => 'required',
                'contactnumber' => 'required|min:10',
                'relationshipmanager' => 'required',
                'relationshipmanagercontact' => 'required'
            ]);

            if ($validator->fails()) {
                return redirect()->route('clients.editClient', $id)
                    ->withErrors($validator)
                    ->withInput();
            } else {

                $client = ClientDetails::find($id);
                $new_user = RegistrationData::where('mobile', $client->contact)->first();

                // Check for duplicate contact number excluding the current client's contact
                if (ClientDetails::where('contact', $request->contactnumber)->where('id', '!=', $id)->exists()) {
                    return redirect()->route('clients.editClient', $id)
                        ->withErrors(['contactnumber' => 'This Contact Number is already registered to another client.'])
                        ->withInput();
                } else {
                    $name = explode(" ", $_POST['name']);
                    $mobile = $_POST['contactnumber'];
                    $company = CompanyDetails::where('id', $user->company_id)->first();

                    if ($new_user) {
                        $new_user->firstName = $name[0];
                        if (isset($name[1])) {
                            $new_user->lastName = $name[1];
                        }
                        $new_user->mobile = $_POST['contactnumber'];

                        // FIXED NULL ERROR: Use $user->company_id directly, and add a fallback for the name
                        $new_user->company_id = $user->company_id;
                        $new_user->company_name = $company ? $company->name : 'Unknown Company';

                        $new_user->email = $_POST['email'];
                        $new_user->role_id = "4";
                        $new_user->save();
                    }

                    $client->name = $_POST['name'];
                    $client->address = $_POST['address'];
                    $client->state = $_POST['state'];
                    $client->city = $_POST['city'];
                    $client->pincode = $_POST['pincode'];
                    $client->spokesperson = $_POST['contactperson'];
                    $client->contact = $_POST['contactnumber'];
                    $client->email = $_POST['email'];
                    $client->relationManager = $_POST['relationshipmanager'];
                    $client->relationManagerContact = $_POST['relationshipmanagercontact'];
                    $client->company_id = $user->company_id;
                    $client->save();

                    ActivityLog::create([
                        'company_id' => $user->company_id,
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'type' => "Update Client",
                        'message' => $client->name . " client updated by " . $user->name,
                        'date_time' => date('Y-m-d H:i:s'),
                    ]);

                    return \Redirect::route('clients')->with('success', 'Client updated successfully.');
                }
            }
        }
    }

    // delete client
    public function deleteClient($client_id)
    {
        $user = session('user');
        if ($user) {

            if ($client_id) {
                $client = ClientDetails::where('id', $client_id)->first();

                ActivityLog::create([
                    'company_id' => $user->company_id,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'type' => "Delete Client",
                    'message' => $client->name . " client deleted by " . $user->name,
                    'date_time' => date('Y-m-d H:i:s'),
                ]);
                $client->delete();
                SiteDetails::where('client_id', $client_id)->delete();

                ShiftAssigned::where('client_id', $client_id)->delete();
                SiteAssign::where('client_id', $client_id)->delete();
                return redirect()->route('clients')->with('error', 'Client deleted successfully !');
            }
        }
    }


    // shift list
    public function getShifts($client_id, $site_id)
    {
        $user = session('user');
        if ($user) {
            Log::channel('create')->info($user->name . ' view shift list');
            $siteName = SiteDetails::where('id', $site_id)->first();
            $shifts = ShiftAssigned::where('site_id', $site_id)->where('company_id', $user->company_id)->get();

            return view('shiftlist')->with('shifts', $shifts)->with('site_id', $site_id)->with('client_id', $client_id)->with('siteName', $siteName);
        }
    }

    // shift create form
    public function getShiftsCreate($client_id, $site_id)
    {
        $user = session('user');
        if ($user) {
            Log::channel('create')->info($user->name . ' view create shift form');
            return view('createshift')->with("client_id", $client_id)->with("site_id", $site_id);
        }
    }

    //shift saved in database
    public function shift_createaction(Request $request, $client_id, $site_id)
    {
        // dd($client_id,$site_id,$request);
        $user = session('user');
        if ($user) {
            $messages = [
                'name.required' => '*Required',
                'start.required' => '*Required',
                'end.required' => '*Required',

            ];
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'start' => 'required',
                'end' => 'required',
            ], $messages);

            if ($validator->fails()) {
                return redirect()->route('clients.getshiftscreate', [$client_id, $site_id])
                    ->withErrors($validator)
                    ->withInput();
            } else {

                $time = array(
                    "start" => date('h:i a', strtotime($_POST["start"])),
                    "end" => date('h:i a', strtotime($_POST["end"]))
                );
                $timeArray = json_encode($time);
                //dd($timeArray);
                // $admin = Users::where('id', $client_id)->first();

                // if ($admin->role_id == 7) {
                // $site_name = ClientDetails::where('id', $site_id)->first();
                // } else {
                $site_name = SiteDetails::where('id', $site_id)->first();
                // }


                ActivityLog::create([
                    'company_id' => $user->company_id,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'type' => "Create Shift",
                    'message' => " shift created in site " . $site_name->name . " by " . $user->name,
                    'date_time' => date('Y-m-d H:i:s'),
                ]);
                $new_shift = new ShiftAssigned();
                $new_shift->shift_name = $_POST['name'];
                $new_shift->shift_time = $timeArray;
                $new_shift->site_id = $site_id;
                $new_shift->site_name = $site_name->name;
                $new_shift->company_id = $user->company_id;
                $new_shift->client_id = $site_name->client_id;
                $new_shift->save();
                return redirect()->route('clients.getshifts', [$client_id, $site_id])->with('success', 'Shift created successfully !');
            }
        }
    }

    // shift edit form
    public function ShiftsEdit($id, $client_id, $site_id)
    {
        $user = session('user');
        if ($user) {
            Log::channel('create')->info($user->name . ' view edit shift form');
            $shifts = ShiftAssigned::find($id);
            return view('updateshift')->with('shifts', $shifts)->with('id', $id)->with('site_id', $site_id)->with('client_id', $client_id);
        }
    }

    // shift updated data saved in database
    public function shift_updateaction(Request $request, $id, $client_id, $site_id)
    {
        $user = session('user');
        if ($user) {
            $messages = [
                'name.required' => '*Required',
                'start.required' => '*Required',
                'end.required' => '*Required',

            ];
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'start' => 'required',
                'end' => 'required',
            ], $messages);

            if ($validator->fails()) {
                return redirect()->route('clients.shift_edit', [$id, $client_id, $site_id])
                    ->withErrors($validator)
                    ->withInput();
            } else {

                $time = array(
                    "start" => date('h:i a', strtotime($_POST["start"])),
                    "end" => date('h:i a', strtotime($_POST["end"]))
                );
                $timeArray = json_encode($time);

                // $admin = Users::where('id', $client_id)->first();

                // if ($admin->role_id == 7) {
                //     $site_name = ClientDetails::where('id', $site_id)->first();
                // } else {
                $site_name = SiteDetails::where('id', $site_id)->first();
                // }
                //$site_name = SiteDetails::where('id', $site_id)->first();

                ActivityLog::create([
                    'company_id' => $user->company_id,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'type' => "Update Shift",
                    'message' => "shift updated in site " . $site_name->name . " by " . $user->name,
                    'date_time' => date('Y-m-d H:i:s'),
                ]);

                SiteAssign::where('shift_id', $id)->update(array('shift_time' => $timeArray, 'shift_name' => $_POST['name']));

                $shiftAssigned = ShiftAssigned::find($id);
                $shiftAssigned->shift_name = $_POST['name'];
                $shiftAssigned->shift_time = $timeArray;
                // $shiftAssigned->timestamp = date('Y-m-d H:i:s');
                $shiftAssigned->save();

                return redirect()->route('clients.getshifts', [$client_id, $site_id])->with('success', 'Shift updated successfully');
            }
        }
    }

    // Delete shift
    public function ShiftsDelete($id, $client_id, $site_id)
    {
        $user = session('user');
        // $admin = Users::where('id', $user->id)->first();

        // if ($admin->role_id == 7) {
        //     $site_name = ClientDetails::where('id', $site_id)->first();
        // } else {
        $site_name = SiteDetails::where('id', $site_id)->first();
        // }
        // $site_name = SiteDetails::where('id', $site_id)->first();
        if ($user) {
            ActivityLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Delete Shift",
                'message' => "shift deleted in site " . $site_name->name . " by " . $user->name,
                'date_time' => date('Y-m-d H:i:s'),
            ]);
            ShiftAssigned::where('id', $id)->delete();
            return redirect()->route('clients.getshifts', [$client_id, $site_id])->with('error', 'Shift deleted successfully !');
        }
    }

    // geofence list
    public function getClientGeofence($client_id, $site_id)
    {
        //dd($client_id,$site_id);
        $user = session('user');
        if ($user) {

            Log::channel('create')->info($user->name . ' view geofences list');
            // $admin = Users::where('id', $client_id)->first();

            // if (isset($user->role_id) == 7) {

            //     $siteName = ClientDetails::where('id', $site_id)->first();
            // } else {

            $siteName = SiteDetails::where('id', $site_id)->first();
            // }

            //$siteName = SiteDetails::where('id', $site_id)->first();
            $geofences = SiteGeofences::where('site_id', $site_id)->get();
            //Hide the 'geom' property on each model instance
            $geofences->each(function ($geofence) {
                $geofence->makeHidden('poly_coords');
            });
            return view('clientgeofencelist')->with('geofences', $geofences)->with("site_id", $site_id)->with("client_id", $client_id)->with('siteName', $siteName);
        }
    }

    // geofence create form
    public function getGeofenceCreate($client_id, $site_id)
    {
        $user = session('user');
        if ($user) {
            Log::channel('create')->info($user->name . ' view create geofence form');
            return view('clientgeofencecreate')->with("client_id", $client_id)->with("site_id", $site_id);
        }
    }

    // saved geofence in database
    public function Geofencestore($client_id, $site_id)
    {
        // dd($client_id, $site_id);
        // dd($_GET['type'], $_GET['poly_coords']);
        $user = session('user');
        $site_name = SiteDetails::where('id', $site_id)->first();
        if ($user) {

            if ($_GET['type'] == 'Polygon') {

                $pointsArray = json_decode($_GET['poly_coords']); //Expecting an array of [lat, lng] pairs
                $wktPolygon = $this->convertToWktPolygon($pointsArray);
                $poly = DB::raw("ST_GeomFromText('$wktPolygon')");
                $poly_lat_lng = $_GET['poly_coords'];
            } else {

                $poly = NULL;
                $poly_lat_lng = NULL;
            }

            //$points = array_map(function ($coord) {
            //     return new Point($coord->lat, $coord->lng); // lat, lng order for Point
            // }, $pointsArray);

            // Ensure the polygon is closed
            // if ($points[0] != end($points)) {
            //     $points[] = $points[0];
            // }

            // $points = [];
            // foreach ($pointsArray as $point) {
            // $points[] = new Point($point->lat, $point->lng);
            // }
            // $points[] = new Point($pointsArray[0]->lat, $pointsArray[0]->lng);
            // dd($pointsArray);
            // $lineString = new LineString($points);
            // $polygon = new Polygon([$lineString]);

            // dd($polygon);

            $loc = json_decode($_GET['center']);

            // $admin = Users::where('id', $client_id)->first();
            // if (isset($admin)) {
            //     if ($admin->role_id == 7) {
            //         $siteName = ClientDetails::where('id', $client_id)->first();
            //     } else {
            //         $siteName = SiteDetails::where('id', $site_id)->first();
            //     }
            // } else {
            $siteName = SiteDetails::where('id', $site_id)->first();
            // }

            //$siteName = SiteDetails::where('id', $site_id)->first();
            $location = new SiteGeofences();
            $location->name = $_GET['name'];
            $location->center = $_GET['center'];
            if ($_GET['type'] == 'Circle') {
                $location->lat = $loc->lat;
                $location->lng = $loc->lng;
            }
            $location->radius = $_GET['radius'];
            $location->type = $_GET['type'];
            $location->poly_coords = $poly;
            $location->poly_lat_lng = $poly_lat_lng;
            $location->site_id = $site_id;
            $location->site_name = $siteName->name;
            $location->client_id = $siteName->client_id;
            $location->company_id = $user->company_id;
            $result = $location->save();
            ActivityLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Create Geofence",
                'message' => $location->name . " geofence created in site " . $site_name->name . " by " . $user->name,
                'date_time' => date('Y-m-d H:i:s'),
            ]);
            if ($result) {
                return "Geofence Created";
            } else {
                return "Something went wrong!";
            }
            return redirect()->route('clients.getshifts', [$client_id, $site_id]);
        }
    }

    // geofence edit form
    public function geofence_edit($client_id, $site_id, $id)
    {
        $user = session('user');
        if ($user) {
            Log::channel('create')->info($user->name . ' view edit geofence form');
            $geofence = SiteGeofences::find($id);
            $geofence->makeHidden('poly_coords');
            $lat = $geofence->center;
            $latlng = json_decode($lat);
            return view('updategeofence')->with('geofence', $geofence)->with('lat', $latlng->lat)->with('lng', $latlng->lng)->with('id', $id)->with('site_id', $site_id)->with('client_id', $client_id);
        }
    }

    // geofence updated data saved in database
    public function geofenceEditAction($client_id, $site_id, $id)
    {
        $user = session('user');
        if ($user) {

            // dd($client_id, $site_id, $id);
            if ($_GET['type'] == 'Polygon') {
                $pointsArray = json_decode($_GET['poly_coords']); // Expecting an array of [lat, lng] pairs

                $wktPolygon = $this->convertToWktPolygon($pointsArray);

                $poly = DB::raw("ST_GeomFromText('$wktPolygon')");
                $poly_lat_lng = $_GET['poly_coords'];
            } else {
                $poly = NULL;
                $poly_lat_lng = NULL;
            }

            $location = SiteGeofences::find($id);
            //dd($_GET['name'],$location);
            $loc = json_decode($_GET['center']);
            $location->radius = $_GET['radius'];
            $location->type = $_GET['type'];
            $location->poly_coords = $poly;
            $location->poly_lat_lng = $poly_lat_lng;
            $location->name = $_GET['name'];
            $location->center = $_GET['center'];
            if ($_GET['type'] == 'Circle') {
                $location->lat = $loc->lat;
                $location->lng = $loc->lng;
            }

            $location->site_id = $site_id;
            $location->client_id = $client_id;
            $location->company_id = $user->company_id;
            $result = $location->save();

            ActivityLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Update Geofence",
                'message' => $location->name . " geofence updated by " . $user->name,
                'date_time' => date('Y-m-d H:i:s'),
            ]);
            if ($result) {
                return "Geofence Created";
            } else {
                return "Something went wrong!";
            }
            return redirect()->route('clients.getclientgeofences', [$client_id, $site_id]);
        }
    }

    // delete geofence
    public function GeofenceDelete($client_id, $site_id, $id)
    {
        //dd($client_id, $site_id, $id);
        $user = session('user');
        if ($user) {

            $geofence = SiteGeofences::where('id', $id)->first();
            //dd($geofence);
            $geofence->delete();
            //dd($geofence);
            if ($geofence != null) {
                ActivityLog::create([
                    'company_id' => $user->company_id,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'type' => "Delete Geofence",
                    'message' => $geofence->name . " geofence deleted by " . $user->name,
                    'date_time' => date('Y-m-d H:i:s'),
                ]);
            }


            return redirect()->route('clients.getclientgeofences', [$client_id, $site_id])->with('error', "Geofence Deleted Successfully.");
        }
    }

    // total guard list
    public function getClientGuards($client_id, $site_id)
    {
        //dd($client_id, $site_id);

        $user = session('user');
        if ($user) {
            Log::channel('create')->info($user->name . ' view client employee list');
            // $admin = Users::where('id', $client_id)->first();
            // if(isset($admin)) {
            //     if ($admin->role_id == 7) {
            //         $siteName = ClientDetails::where('id', $site_id)->first();
            //     }
            // }else {
            $siteName = SiteDetails::where('id', $site_id)->first();
            // }
            $clientName = ClientDetails::where('id', $client_id)->first();
            $guards = SiteAssign::where('role_id', 3)->where('site_id', $site_id)->get();
            //dd($guards);exit;
            $shift = SiteAssign::leftJoin('shifts', 'shifts.id', 'site_assign.shift_id');
            // dd($guards);
            return view('clientguardslist')->with('guards', $guards)->with('site_id', $site_id)->with('client_id', $client_id)->with('clientName', $clientName)->with('siteName', $siteName);
        }
    }

    //assign guard form
    public function getClientGuardsCreate($client_id, $site_id)
    {
        $user = session('user');
        if ($user) {
            Log::channel('create')->info($user->name . ' view assign employee to site create form');
            $guards = Users::where('role_id', 3)->where('company_id', $user->company_id)->get();
            $clients = ClientDetails::where('company_id', $user->company_id)->orderBy('name', 'asc')->get();
            $shifts = ShiftAssigned::where('site_id', $site_id)->get();
            // dd($shifts);
            return view('createclientguard')->with("site_id", $site_id)->with('guards', $guards)->with('shifts', $shifts)->with("client_id", $client_id)->with('clients', $clients);
        }
    }

    // assign guard to the site
    public function guard_createaction(Request $request, $client_id, $site_id)
    {
        // dd($request, $client_id, $site_id);
        $user = session('user');
        if ($user) {
            if ($site_id == 0) {
                $validator = Validator::make($request->all(), [
                    'client' => 'required',
                    'site' => 'required',
                    'shift' => 'required',
                    'guard' => 'required|min:1',
                    'startdate' => 'required',
                    'enddate' => 'required',
                ]);
            } else {
                $validator = Validator::make($request->all(), [
                    'shift' => 'required',
                    'guard' => 'required',
                    'startdate' => 'required',
                    'enddate' => 'required',
                ]);
            }

            if ($validator->fails()) {
                return redirect()->route('clients.clientguard_create', [$client_id, $site_id])
                    ->withErrors($validator)
                    ->withInput();
            } else {
                $shifts = $_POST['shift'];
                if (isset($_POST['weekoff'])) {
                    $weekoff = json_encode($_POST['weekoff']);
                } else {
                    $weekoff = [];
                }
                //DB::enableQueryLog();
                $shiftDetails = ShiftAssigned::where("id", $_POST['shift'])->first();
                // $admin = Users::where('id', $client_id)->first();
                // dd($admin);
                if ($site_id == 0) {
                    $sites = SiteDetails::where('id', $request->site)->first();
                    $client = ClientDetails::where("id", $request->client)->first();
                    //dd($sites, $client);
                }
                //  else if (isset($admin)) {
                //     // if ($admin->role_id == 7) {
                //     //     $sites = ClientDetails::where('id', $site_id)->first();
                //     //     $client =  Users::where('id', $client_id)->first();
                //     // } else {

                //     $sites = SiteDetails::where('id', $site_id)->first();
                //     $client = ClientDetails::where("id", $sites->client_id)->first();
                //     // }
                // }
                else {

                    $sites = SiteDetails::where('id', $site_id)->first();
                    $client = ClientDetails::where("id", $sites->client_id)->first();
                }


                ActivityLog::create([

                    'company_id' => $user->company_id,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'type' => "Assign Employee To" . $sites->name,
                    'message' => $client->name . " assigned  to " . $sites->name . " by " . $user->name,
                    'date_time' => date('Y-m-d H:i:s'),

                ]);

                $date = array(
                    "from" => date("Y-m-d", strtotime($_POST["startdate"])),
                    "to" => date("Y-m-d", strtotime($_POST["enddate"]))
                );
                $dateArray = json_encode($date);

                for ($i = 0; $i < count($_POST['guard']); $i++) {
                    $guard_id = $_POST['guard'][$i];
                    $users = Users::where('id', $guard_id)->first();

                    $alreadyAssignGuardtoSite = SiteAssign::where('user_id', $guard_id)->first();
                    if (isset($alreadyAssignGuardtoSite)) {
                        $siteassign = SiteAssign::find($alreadyAssignGuardtoSite->id);

                        $siteassign->user_id = $users->id;
                        $siteassign->user_name = $users->name;
                        $siteassign->site_id = $sites->id;
                        $siteassign->client_id = $client->id;
                        $siteassign->client_name = $client->name;
                        $siteassign->site_name = $sites->name;
                        $siteassign->company_id = $user->company_id;
                        $siteassign->date_range = $dateArray;
                        $siteassign->shift_id = $shifts;
                        $siteassign->shift_name = $shiftDetails->shift_name;
                        $siteassign->shift_time = $shiftDetails->shift_time;
                        $siteassign->role_id = $users->role_id;
                        $siteassign->weekoff = $weekoff;
                        // $siteassign->timestamp = date('Y-m-d H:i:s');
                        $siteassign->save();
                    } else {
                        $date = array(
                            "from" => $_POST["startdate"],
                            "to" => $_POST["enddate"]
                        );
                        $dateArray = json_encode($date);
                        $site = SiteDetails::where('id', $site_id)->first();
                        $users = Users::where('id', $guard_id)->first();
                        $user = session('user');
                        $new_siteassign = new SiteAssign();

                        $new_siteassign->user_id = $users->id;
                        $new_siteassign->user_name = $users->name;
                        $new_siteassign->site_id = $sites->id;
                        $new_siteassign->client_id = $client->id;
                        $new_siteassign->client_name = $client->name;
                        $new_siteassign->site_name = $sites->name;
                        $new_siteassign->company_id = $user->company_id;
                        $new_siteassign->date_range = $dateArray;
                        $new_siteassign->shift_id = $shifts;
                        $new_siteassign->role_id = $users->role_id;
                        $new_siteassign->shift_name = $shiftDetails->shift_name;
                        $new_siteassign->shift_time = $shiftDetails->shift_time;
                        $new_siteassign->weekoff = $weekoff;
                        //$new_siteassign->timestamp = date('Y-m-d H:i:s');
                        $new_siteassign->save();
                    }
                    $a = json_decode($shiftDetails->shift_time);
                    $adminFcm = Users::where('company_id', $user->company_id)->where('role_id', 3)->where('id', $guard_id)->where('fcm_token', '!=', null)->pluck('fcm_token')->toArray();
                    $title = "New site assigned";
                    $message = "You have been assigned to site " . $sites->name . ". Your shift timing will be from " . $a->start . " to " . $a->end;
                    if (count($adminFcm) > 0) {
                        $fcm = new FCMNotify;
                        // $fcm->sendNotification($title, $message, $adminFcm);
                    }
                }

                if ($site_id == 0) {
                    return redirect()->route('clients.clientguard_read', [$client->id, $site_id, $request->userId]);
                } else {
                    return redirect()->route('clients.getclientguards', [$client_id, $site_id]);
                }
            }
        }
    }

    // unassign guard
    public function getNotAssignGuard($shift_id)
    {
        $user = session('user');
        if ($user) {
            $assign_shift = SiteAssign::where('shift_id', $shift_id)->pluck('user_id')->toArray();
            $employees = Users::whereNotIn('id', $assign_shift)
                ->where('company_id', $user->company_id)
                ->where('role_id', 3)
                ->get(['id', 'name']);
            return response()->json($employees);
        }
        return response()->json([]);
    }

    //update guard assign to site form
    public function clientguard_edit($client_id, $site_id, $id)
    {
        $user = session('user');
        if ($user) {
            Log::channel('create')->info($user->name . ' view assign employee to site edit form');
            $guards = Users::where('role_id', 3)->where('company_id', $user->company_id)->get();
            $shifts = ShiftAssigned::where('site_id', $site_id)->get();
            $siteassign = SiteAssign::where('id', $id)->first();
            return view('updateguard')->with('siteassign', $siteassign)->with('id', $id)->with('site_id', $site_id)->with('client_id', $client_id)->with('guards', $guards)->with('shifts', $shifts);
        }
    }

    // update guard assign to site data save in database
    public function clientguard_editaction(Request $req, $client_id, $site_id, $id)
    {
        $user = session('user');
        if ($user) {
            ActivityLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Update Assign Employee",
                'message' => "Update assign employee " . " by " . $user->name,
                'date_time' => date('Y-m-d H:i:s'),
            ]);

            if (isset($_POST['weekoff'])) {
                $weekoff = json_encode($_POST['weekoff']);
            } else {
                $weekoff = [];
            }
            $shifts = $_POST['shift'];
            //dd($shifts);
            $sites = SiteDetails::where('id', $site_id)->first();
            $client = ClientDetails::where("id", $sites->client_id)->first();


            $shiftDetails = ShiftAssigned::where("id", $_POST['shift'])->first();
            for ($i = 0; $i < count($_POST['guard']); $i++) {
                $guard_id = $_POST['guard'][$i];
                $date = array(
                    "from" => $_POST["startdate"],
                    "to" => $_POST["enddate"]
                );
                $dateArray = json_encode($date);
                $site = SiteDetails::where('id', $site_id)->first();
                $users = Users::where('id', $guard_id)->first();
                $alreadyAssignGuardtoSite = SiteAssign::where('user_id', $guard_id)->first();
                if (isset($alreadyAssignGuardtoSite)) {
                    $siteassign = SiteAssign::find($alreadyAssignGuardtoSite->id);
                    $site = SiteDetails::where('id', $site_id)->first();
                    $siteassign->user_id = $users->id;
                    $siteassign->user_name = $users->name;
                    $siteassign->site_id = $site_id;
                    $siteassign->client_id = $client->id;
                    $siteassign->client_name = $client->name;
                    $siteassign->site_name = $site->name;
                    $siteassign->company_id = $user->company_id;
                    $siteassign->date_range = $dateArray;
                    $siteassign->shift_id = $shifts;
                    $siteassign->shift_name = $shiftDetails->shift_name;
                    $siteassign->shift_time = $shiftDetails->shift_time;
                    $siteassign->role_id = $users->role_id;
                    $siteassign->weekoff = $weekoff;
                    // $siteassign->timestamp = date('Y-m-d H:i:s');
                    $siteassign->save();
                } else {
                    // dd("else");
                    $siteassign = SiteAssign::find($id);
                    // print_r($siteassign);exit;
                    $siteassign->user_id = $users->id;
                    $siteassign->user_name = $users->name;
                    $siteassign->site_id = $site_id;
                    $siteassign->client_id = $client->id;
                    $siteassign->client_name = $client->name;
                    $siteassign->site_name = $site->name;
                    $siteassign->company_id = $user->company_id;
                    $siteassign->date_range = $dateArray;
                    $siteassign->shift_id = $shifts;
                    $siteassign->shift_id = $shiftDetails->id;
                    $siteassign->shift_name = $shiftDetails->shift_name;
                    $siteassign->shift_time = $shiftDetails->shift_time;
                    $siteassign->role_id = $users->role_id;
                    $siteassign->weekoff = $weekoff;
                    // $siteassign->timestamp = date('Y-m-d H:i:s');

                    $siteassign->save();
                }
                $a = json_decode($shiftDetails->shift_time);
                $adminFcm = Users::where('company_id', $user->company_id)->where('role_id', 3)->where('id', $guard_id)->where('fcm_token', '!=', null)->pluck('fcm_token')->toArray();
                $title = "New site assigned";
                $message = "You have been assigned to site " . $sites->name . ". Your shift timing will be from " . $a->start . " to " . $a->end;
                if (count($adminFcm) > 0) {
                    $fcm = new FCMNotify;
                    // $fcm->sendNotification($title, $message, $adminFcm);
                }
            }
            return redirect()->route('clients.getclientguards', [$client_id, $site_id]);
        }
    }

    // view guard details page
    public function getGuardRead($client_id, $site_id, $id)
    {
        $user = session('user');
        if ($user) {
            Log::channel('create')->info($user->name . ' view guard details');
            $date = date('Y-m-d');
            if ($client_id != 0) {
                $site_assign = SiteAssign::where('user_id', $id)->first();
                $users = Users::where('id', $id)->first();
                $moduleList = json_decode($users->module);
                // dd('if', $moduleList);
                $attendance = Attendance::where('user_id', $id)->where('dateFormat', $date)->first();
                $guardTourLog = GuardTourLog::where('guardId', $id)->where('date', $date)->get();
                // dd($users, "if block");

                return view('clientguardread')->with("user", $users)->with('site_assign', $site_assign)->with('guardTourLog', $guardTourLog)->with('attendance', $attendance)->with('client_id', $client_id)->with('site_id', $site_id)->with('id', $id)->with('moduleList', $moduleList);
            } else {
                $site_assign = SiteAssign::where('user_id', $id)->first();
                $users = Users::where('id', $id)->first();
                if ($users != null) {

                    $moduleList = json_decode($users->module);
                } else {
                    $moduleList = DB::table('checkList')->where('type', 'modulePermission')->first();

                    $moduleList = json_decode($moduleList->checkList);
                }

                // dd()
                //$moduleList = json_decode($users->module);
                // dd($users, "else block");
                $attendance = Attendance::where('user_id', $id)->where('dateFormat', $date)->first();
                $guardTourLog = GuardTourLog::where('guardId', $id)->where('date', $date)->get();
                return view('clientguardread')->with("user", $users)->with('site_assign', $site_assign)->with('guardTourLog', $guardTourLog)->with('attendance', $attendance)->with('client_id', $client_id)->with('site_id', $site_id)->with('id', $id)->with('moduleList', $moduleList);
            }
        }
    }

    // delete guard from site
    public function guardDelete($client_id, $site_id, $id)
    {
        $user = session('user');
        if ($user) {

            $gaurd = SiteAssign::find($id);
            $adminFcm = Users::where('company_id', $user->company_id)->where('role_id', 3)->where('id', $gaurd->user_id)->where('fcm_token', '!=', null)->pluck('fcm_token')->toArray();
            $title = "Remove from site";
            $message = "You have been unassigned from " . $gaurd->site_name . ". Please wait to get reassign or contact supervisor ";
            if (count($adminFcm) > 0) {
                $fcm = new FCMNotify;
                // $fcm->sendNotification($title, $message, $adminFcm);
            }
            SiteAssign::where('id', $id)->delete();
            ActivityLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Delete Assign Employee",
                'message' => "Delete assign employee by " . $user->name,
                'date_time' => date('Y-m-d H:i:s'),
            ]);
            return redirect()->route('clients.getclientguards', [$client_id, $site_id]);
        }
    }

    // tours list assign to sites
    public function getClientTour($id)
    {
        $user = session('user');
        if ($user) {
            Log::channel('create')->info($user->name . ' view tour list');
            $tours = GuardTour::where('site_id', $id)->get();
            //dd($tours);
            return view('clienttourlist')->with('tours', $tours)->with("id", $id);
        }
    }

    // tour checkpoint list
    public function getGuardTourCheckpoint($tour_id)
    {
        $user = session('user');
        if ($user) {
            Log::channel('create')->info($user->name . ' view tour checkpoints');
            $checkpoints = GuardTourCheckpoints::where('tourId', $tour_id)->get();
            $guardDetails = GuardTour::where([['id', $tour_id]])->first();
            if (count($checkpoints) == 0) {
                $MasterCreated = '';
            } else {
                $MasterCreated = GuardTour::where([['id', $checkpoints[0]->tourId]])->first();
            }

            return view('guardtourcheckpoint')->with('checkpoints', $checkpoints)->with('guardDetails', $guardDetails)->with('MasterCreated', $MasterCreated)->with('tour_id', $tour_id);
        }
    }

    //details of client
    public function clientView($clientId)
    {
        $clients = ClientDetails::where('id', $clientId)->first();
        return view('viewclient')->with('clients', $clients);
    }

    // complaint list
    public function complaints()
    {
        $user = session('user');
        if ($user) {
            Log::channel('create')->info($user->name . ' view complaint list');
            if ($user->role_id == '1') {
                $complaints = ClientComplaints::where('status', 'Awaiting')->where('company_id', $user->company_id)->get();
            } else {
                $complaints = ClientComplaints::where('status', 'Awaiting')->where('client_id', $user->client_id)->get();
            }
            return view('complaints')->with('complaints', $complaints);
        }
    }

    // complaint form
    public function raiseComplaint()
    {
        $user = session('user');
        if ($user) {
            Log::channel('create')->info($user->name . ' view complaint form');
            return view('complaintform');
        }
    }

    //  complaint saved in database
    public function complaintAction(Request $request)
    {
        $user = session('user');
        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Complaint Raised",
            'message' => "complaint raised by" . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);

        $newComplaint = new ClientComplaints();
        $newComplaint->client_id = $user->client_id;
        $newComplaint->client_name = $user->name;
        $newComplaint->company_id = $user->company_id;
        $newComplaint->priority = $request->priority;
        $newComplaint->remark = $request->complaint;
        $newComplaint->dateTime = date("Y-m-d h:i:s");
        $newComplaint->save();


        $newNotification = new Notifications();
        $newNotification->notification = $user->name . ' raise complaint at ' . date("Y-m-d h:i:s");
        $newNotification->type = "Complaint";
        $newNotification->notification_id = $newComplaint->id;
        $newNotification->user_id = $user->id;
        $newNotification->company_id = $user->company_id;
        $newNotification->client_id = $user->client_id;
        $newNotification->date = date('Y-m-d');
        $newNotification->dateFormat = date('Y-m-d');
        $newNotification->dateTime = date('Y-m-d h:i:sa');
        $newNotification->time = date('h:ia');
        $newNotification->save();
        $adminFcm = Users::where('company_id', $user->company_id)->where('role_id', 1)->where('fcm_token', '!=', null)->pluck('fcm_token')->toArray();
        $webToken = Users::where('company_id', $user->company_id)->where('role_id', 1)->where('web_token', '!=', null)->pluck('web_token')->toArray();

        $title = "Complaint Alert";
        $message = "Complaint raised by " . $user->name;
        $SERVER_API_KEY = 'AAAA3AljzgA:APA91bGZyRSxcanH54Aj26-047n5gnmYe-7EB9RdnT1xMsv_S9gIWEx4XlEcgmP8X8NpIMM58QK6FyTf6OyjUkIqR2wrhAiFHuVCC-lPqsuKDmGYVPdW7KuuJpLM2wdJhJe3ZMmInVak';
        $webData = [
            "registration_ids" => $webToken,
            "notification" => [
                "title" => $title,
                "body" => $message,
            ]
        ];
        $dataString = json_encode($webData);
        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);
        if (count($adminFcm) > 0) {
            $fcm = new FCMNotify;
            // $fcm->sendNotification($title, $message, $adminFcm);
        }
        return redirect()->route('complaints')->with('success', 'Complaint raised successfully');;
    }

    // complaint resolved
    public function complaintResolved(Request $request, $notificationId)
    {
        $user = session('user');
        ActivityLog::create([

            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Complaint Resolved",
            'message' => "Complaint resolved by" . $user->name,
            'date_time' => date('Y-m-d H:i:s'),

        ]);
        ClientComplaints::where('id', $notificationId)
            ->update([
                'status' => "Resolved",
                'actionDateTime' => date('Y-m-d h:i:s'),
                'actionRemark' => $request->remark,
                'actionById' => $user->id,
                'actionByName' => $user->name,
            ]);
        Notifications::where('notification_id', $notificationId)
            ->update(['readStatusForAdmin' => 1]);
        //dd($user);

        $adminFcm = Users::where('client_id', $user->client_id)->where('role_id', 4)->where('fcm_token', '!=', null)->pluck('fcm_token')->toArray();
        $webToken = Users::where('client_id', $user->client_id)->where('role_id', 4)->where('web_token', '!=', null)->pluck('web_token')->toArray();
        $title = "Complaint Alert";
        $message = "Complaint resolved by " . $user->name;
        $SERVER_API_KEY = 'AAAA3AljzgA:APA91bGZyRSxcanH54Aj26-047n5gnmYe-7EB9RdnT1xMsv_S9gIWEx4XlEcgmP8X8NpIMM58QK6FyTf6OyjUkIqR2wrhAiFHuVCC-lPqsuKDmGYVPdW7KuuJpLM2wdJhJe3ZMmInVak';
        $webData = [
            "registration_ids" => $webToken,
            "notification" => [
                "title" => $title,
                "body" => $message,
            ]
        ];
        $dataString = json_encode($webData);
        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);
        // dd($response);
        if (count($adminFcm) > 0) {
            $fcm = new FCMNotify;
            // $fcm->sendNotification($title, $message, $adminFcm);
        }
        return "success";
    }

    public function export()
    {
        $user = session('user');
        $clients = ClientDetails::where('company_id', $user->company_id)->get();
        $company = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $company ? $company->name : 'Unknown Company';
        return $this->excel->download(new ClientExport($clients, $companyName), 'clients.xlsx');
    }

    public function tourDetailsExport(Request $request)
    {

        $user = session('user');
        if ($user) {
            Log::channel('create')->info($user->name . ' export tour details with checkpoint');
            $checkpoints = GuardTourCheckpoints::where('tourId', $request->tour_id)->orderBy('sequence', 'asc')->get();
            $guardDetails = GuardTour::where([['id', $request->tour_id]])->first();

            return $this->excel->download(new TourDetailsExport($checkpoints, $guardDetails), 'Tour Details.xlsx');
        }
    }


    public function clientModule($id, Request $request)
    {
        // dd($request);
        $user = Users::find($id);
        //dd($request,$user->module);
        if ($user) {
            $user['module'] = json_encode($request['module']);
            $user->update();
        }
        //$moduleList = json_decode($user->module);

        return "success";
    }

    private function convertToWktPolygon($polygonCoordinates)
    {

        // dd($polygonCoordinates);
        $wkt = 'POLYGON((';
        foreach ($polygonCoordinates as $point) {
            $wkt .= "{$point->lng} {$point->lat},";
        }

        // Ensure the polygon is closed by appending the first point at the end if not already closed
        if ($polygonCoordinates[0]->lat !== end($polygonCoordinates)->lat || $polygonCoordinates[0]->lng !== end($polygonCoordinates)->lng) {
            $wkt .= "{$polygonCoordinates[0]->lng} {$polygonCoordinates[0]->lat},";
        }
        // Remove the trailing comma and close the polygon
        $wkt = rtrim($wkt, ',') . '))';
        return $wkt;
    }
}

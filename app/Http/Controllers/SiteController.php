<?php

namespace App\Http\Controllers;

use Validator;
use App\Exports\SiteExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use App\States;
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
use App\ActivityLog;
use Log;

use App\CompanyDetails;


class SiteController extends Controller
{
    private $excel;
    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
    }
    public function index($client_id)
    {
        $user = session('user');
        $supervisor_id = session('supervisor_id');
        if ($supervisor_id) {
            Log::info($user->name . 'get supervisor under sites, User_id: ' . $user->id);
            $site_id = SiteAssign::where('user_id', $supervisor_id)->where('role_id', 2)->first();
            $siteArray = json_decode($site_id['site_id'], true);
            $Sites = SiteDetails::whereIn('id', $siteArray)->get();
            return view('sitelist')->with('Sites', $Sites)->with("client_id", $supervisor_id)->with('supervisor_id', $supervisor_id);
        } else {
            Log::info($user->name . ' view site list, User_id: ' . $user->id);
            if ($user->role_id == 1 || $user->role_id == 8) {
                if ($client_id != 0) {
                    $clientName = ClientDetails::where('id', $client_id)->first();
                    $Sites = SiteDetails::where('company_id', $user->company_id)->where('client_id', $client_id)->orderBy('name', 'asc')->get();
                    return view('sitelist')->with('Sites', $Sites)->with("client_id", $client_id)->with('clientName', $clientName);
                } else {

                    $Sites = SiteDetails::where('company_id', $user->company_id)->orderBy('name', 'asc')->get();
                    return view('sitelist')->with('Sites', $Sites)->with("client_id", $client_id);
                }
            } else if ($user->role_id == 2) {

                $user_id = SiteAssign::where('user_id', $user->id)->where('role_id', 2)->first();
                //print_r($user_id);exit;

                if ($client_id != 0) {
                    $clientName = ClientDetails::where('id', $client_id)->first();
                    $siteArray = json_decode($user_id['site_id'], true);
                    $Sites = SiteDetails::whereIn('id', $siteArray)->get();
                    return view('sitelist')->with('Sites', $Sites)->with("client_id", $client_id)->with('clientName', $clientName);
                } else {

                    if (!empty($user_id)) {
                        $siteArray = json_decode($user_id['site_id'], true);

                        $Sites = DB::table('site_details')->whereIn('id', $siteArray)->get();
                        return view('sitelist')->with('Sites', $Sites)->with("client_id", $client_id);
                    } else {
                        $implodeID = '0';
                        $Sites = DB::table('site_details')->Where('id', '=', $implodeID)->get();
                        return view('sitelist')->with('Sites', $Sites)->with("client_id", $client_id);
                    }
                }
            } else {
                return redirect()->back()->with('error', 'No data available');
            }
        }
    }
    public function getSites(Request $request, $client_id)
    {
        // dd($request);
        $user = session('user');
        $request->session()->forget('supervisor_id');
        Log::info($user->name . ' view site list, User_id: ' . $user->id);
        $admin = Users::where('id', $client_id)->first();
        if ($user->role_id == 1 || $user->role_id == 8) {
            if ($client_id != 0) {
                $clientName = ClientDetails::where('id', $client_id)->first();
                $Sites = SiteDetails::where('company_id', $user->company_id)->where('client_id', $client_id)->orderBy('name', 'asc')->get();
                return view('sitelist')->with('admin', $admin)->with('Sites', $Sites)->with("client_id", $client_id)->with('clientName', $clientName);
            } else {

                $Sites = SiteDetails::where('company_id', $user->company_id)->orderBy('name', 'asc')->get();
                return view('sitelist')->with('admin', $admin)->with('Sites', $Sites)->with("client_id", $client_id);
            }
        } else if ($user->role_id == 2) {

            $user_id = SiteAssign::where('user_id', $user->id)->where('role_id', 2)->first();
            if ($client_id != 0) {
                $clientName = ClientDetails::where('id', $client_id)->first();
                $siteArray = json_decode($user_id['site_id'], true);
                $Sites = SiteDetails::whereIn('id', $siteArray)->get();
                return view('sitelist')->with('admin', $admin)->with('Sites', $Sites)->with("client_id", $client_id)->with('clientName', $clientName);
            } else {

                if (!empty($user_id)) {
                    $siteArray = json_decode($user_id['site_id'], true);

                    $Sites = DB::table('site_details')->whereIn('id', $siteArray)->get();
                    return view('sitelist')->with('admin', $admin)->with('Sites', $Sites)->with("client_id", $client_id);
                } else {
                    $implodeID = '0';
                    $Sites = DB::table('site_details')->Where('id', '=', $implodeID)->get();
                    return view('sitelist')->with('admin', $admin)->with('Sites', $Sites)->with("client_id", $client_id);
                }
            }
        } else if ($user->role_id == 4) {
            $Sites = DB::table('site_details')->where('client_id', $user->client_id)->get();
            return view('sitelist')->with('admin', $admin)->with('Sites', $Sites)->with("client_id", $client_id);
        } else if ($user->role_id == 7) {
            $siteAssign = SiteAssign::where('user_id', $user->id)->first();
            if ($client_id != 0 && gettype($client_id) != "string") {
                // dd($client_id);
                $clientName = ClientDetails::where('id', $client_id)->first();
                $Sites = SiteDetails::where('company_id', $user->company_id)->whereIn('client_id', $client_id)
                    ->orderBy('name', 'asc')->get();
                // dd(count($Sites));
                return view('sitelist')->with('admin', $admin)->with('Sites', $Sites)->with("client_id", $client_id)->with('clientName', $clientName);
            } else {
                // dump(2);
                // dd($client_id);
                $clientArray = json_decode($siteAssign['site_id'], true);
                $Sites = SiteDetails::where('company_id', $user->company_id)->whereIn('client_id', $clientArray)
                    ->orderBy('name', 'asc')->get();
                return view('sitelist')->with('admin', $admin)->with('Sites', $Sites)->with("client_id", $client_id);
            }
        }
    }


    public function siteCreate($client_id)
    {
        $user = session('user');
        Log::info($user->name . ' view site form, User_id: ' . $user->id);
        if ($client_id == 0) {
            $states = DB::table('states')->get();
            $clients = ClientDetails::where('company_id', $user->company_id)->get();
            return view('createsite')->with('states', $states)->with('id', $client_id)->with('clients', $clients);
        } else {
            $states = DB::table('states')->get();
            return view('createsite')->with('states', $states)->with('id', $client_id);
        }
    }

    public function site_createaction($client_id, Request $request)
    {
        $state = DB::table('states')->where('code', $request->state)->first();
        $user = session('user');


        ActivityLog::create([
            'date_time' => date('Y-m-d H:i:s'),
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Create Site",
            'message' => "New site '" . $_POST['name'] . "' created by " . $user->name,
        ]);
        $new_site = new SiteDetails();
        if ($client_id == 0) {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'address' => 'required|max:100',
                'state' => 'required',
                'city' => 'required',
                'pincode' => 'required|numeric|min:6',
                'contactperson' => 'required',
                'contactnumber' => 'required|min:10',
                'email' => 'required|email',
                'latetime' => 'required',
                'site_type' => 'required',
                'client' => 'required',
                'sos' => 'required',
                'earlytime' => 'required',

            ]);

            if ($validator->fails()) {
                return redirect()->route('sites.site_create', $client_id)
                    ->withErrors($validator)
                    ->withInput();
            } else {
                $clientName = ClientDetails::where('id', $request->client)->first();
                // dd($clientName);
                $new_site->name = $_POST['name'];
                $new_site->address = $_POST['address'];
                $new_site->state = $state->name;
                $new_site->city = $_POST['city'];
                $new_site->pincode = $_POST['pincode'];
                $new_site->contactPerson = $_POST['contactperson'];
                $new_site->mobile = $_POST['contactnumber'];
                $new_site->email = $_POST['email'];
                $new_site->lateTime = $_POST['latetime'];
                $new_site->siteType = $_POST['site_type'];
                $new_site->client_id = $_POST['client'];
                $new_site->client_name = $clientName->name;
                $new_site->company_id = $user->company_id;
                $new_site->earlyTime = $request->earlytime;
                $new_site->sosContact = $request->sos;
                // $new_site->timestamp = date('Y-m-d H:i:s');
                $new_site->save();
            }
        } else {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'address' => 'required|max:100',
                'state' => 'required',
                'city' => 'required',
                'pincode' => 'required|numeric|min:6',
                'contactperson' => 'required',
                'contactnumber' => 'required|min:10',
                'email' => 'required|email',
                'latetime' => 'required',
                'site_type' => 'required',
                'sos' => 'required',
                'earlytime' => 'required',
            ]);

            if ($validator->fails()) {
                return redirect()->route('sites.site_create', $client_id)
                    ->withErrors($validator)
                    ->withInput();
            } else {
                $clientName = ClientDetails::where('id', $client_id)->first();

                $new_site->name = $_POST['name'];
                $new_site->address = $_POST['address'];
                $new_site->state = $state->name;
                $new_site->city = $_POST['city'];
                $new_site->pincode = $_POST['pincode'];
                $new_site->contactPerson = $_POST['contactperson'];
                $new_site->mobile = $_POST['contactnumber'];
                $new_site->email = $_POST['email'];
                $new_site->lateTime = $_POST['latetime'];
                $new_site->siteType = $_POST['site_type'];
                $new_site->client_id = $client_id;
                $new_site->client_name = $clientName->name;
                $new_site->company_id = $user->company_id;
                $new_site->earlyTime = $request->earlytime;
                $new_site->sosContact = $request->sos;
                // $new_site->timestamp = date('Y-m-d H:i:s');
                $new_site->save();
            }
        }

        return redirect()->route('sites.getsites', [$client_id])->with('success', 'site created successfully.');
    }

    public function site_edit($client_id, $id)
    {
        // dd($client_id, $id);

        $user = session('user');
        Log::info($user->name . ' view site update form, User_id: ' . $user->id);

        $states = DB::table('states')->get();
        // $admin = Users::where('id', $client_id)->first();
        // dd($admin);
        // if (isset($admin)) {
        // if ($admin->role_id == 7) {

        $sites = SiteDetails::find($id);
        $clients = ClientDetails::where('company_id', $user->company_id)->where('id', $sites->client_id)->get();
        $client_id = $sites->client_id;
        // }
        // } else {
        //     $clients = ClientDetails::where('company_id', $user->company_id)->where('id', $client_id)->first();
        //     $sites = SiteDetails::find($id);
        // }

        // dd($clients,$sites);
        //$admin = Users::where('id', $client_id)->first();
        //dd($admin,$clients);
        //if ($user->role_id == 7) {
        //$sites = ClientDetails::find($id);
        //} else {

        //}

        return view('updatesite')->with('sites', $sites)->with('clients', $clients)->with('id', $id)->with('client_id', $client_id)->with('states', $states);
    }

    public function site_editaction(Request $request, $client_id, $id)
    {
        $user = session('user');
        if ($client_id == 0) {
            $client = $_POST['client'];
        } else {
            $client = $client_id;
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'address' => 'required|max:100',
            'state' => 'required',
            'city' => 'required',
            // 'pincode' => 'required|numeric|min:6',
            // 'contactperson' => 'required',
            // 'contactnumber' => 'required|min:10',
            // 'email' => 'required|email',
            'latetime' => 'required',
            'site_type' => 'required',
            // 'client' => 'required',
            'sos' => 'required',
            'earlytime' => 'required',
        ]);
        // dd($client);

        if ($validator->fails()) {
            // dd(1);
            return redirect()->route('sites.site_edit', [$client_id, $id])
                ->withErrors($validator)
                ->withInput();
        } else {
            ActivityLog::create([
                'date_time' => date('Y-m-d H:i:s'),
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Update Site",
                'message' => "Site '" . $_POST['name'] . "' updated by " . $user->name,
            ]);
            SiteAssign::where('site_id', '=', $id)->update(['site_name' => $_POST['name']]);
            $state = DB::table('states')->where('code', $_POST['state'])->first();
            // $admin = Users::where('id', $client_id)->first();
            // if ($user->role_id == 7) {
            //     $site = ClientDetails::find($id);
            // } else {
            $site = SiteDetails::find($id);
            // }

            $site->name = $_POST['name'];
            $site->address = $_POST['address'];
            $site->state = $state->name;
            $site->city = $_POST['city'];
            $site->pincode = $_POST['pincode'];
            $site->contactPerson = $_POST['contactperson'];
            $site->mobile = $_POST['contactnumber'];
            $site->email = $_POST['email'];
            $site->lateTime = $_POST['latetime'];
            $site->siteType = $_POST['site_type'];
            $site->client_id = $client;
            //$site->timestamp = date('Y-m-d H:i:s');
            $site->save();
        }

        return redirect()->route('sites.getsites', [$client_id])->with('success', 'site updated successfully.');
    }

    public function site_delete($client_id, $id)
    {
        $user = session('user');
        // Geofences::where('id',$id)->delete();
        ShiftAssigned::where('site_id', $id)->delete();
        SiteAssign::where('site_id', $id)->delete();
        $site = SiteDetails::where('id', $id)->first();
        ActivityLog::create([
            'date_time' => date('Y-m-d H:i:s'),
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Delete Site",
            'message' => "Site '" . $site->name . "' deleted by " . $user->name,
        ]);
        $site->delete();
        return redirect()->route('sites.getsites', [$client_id])->with('success', 'site deleted successfully.');
    }

    public function getSupervisorSites(Request $request, $supervisor_id)
    {
        // dd($supervisor_id);
        $user = session('user');
        $request->session()->put('supervisor_id', $supervisor_id);
        Log::info($user->name . 'get supervisor under sites, User_id: ' . $user->id);
        $admin = Users::where('id', $supervisor_id)->first();
        //dd($admin->role_id);

        if ($admin->role_id == 7) {
            //dd($admin->role_id);
            $site_id = SiteAssign::where('user_id', $supervisor_id)->where('role_id', 7)->first();
            // dd($site_id);
        } else {
            $site_id = SiteAssign::where('user_id', $supervisor_id)->where('role_id', 2)->first();
        }

        $Sites = '';
        if ($admin->role_id == 7) {
            if ($site_id) {
                $siteArray = json_decode($site_id['site_id'], true);
                $Sites = ClientDetails::whereIn('id', $siteArray)->get();
            }
        } else {
            if ($site_id) {
                $siteArray = json_decode($site_id['site_id'], true);
                $Sites = SiteDetails::whereIn('id', $siteArray)->get();
            }
        }

        // dd($Sites);

        return view('sitelist')->with('admin', $admin)->with('Sites', $Sites)->with("client_id", $supervisor_id)->with('supervisor_id', $supervisor_id);
    }

    public function site_view($client_id, $id)
    {

        // dd($client_id,$id);
        $user = session('user');
        // $admin = Users::where('id', $client_id)->first();
        // dd($admin);
        Log::info($user->name . 'view site detail, User_id: ' . $user->id);
        if (isset($user)) {
            //dd('hii');
            if ($user->role_id == 7) {
                $sites = SiteDetails::find($id);
            } else {
                $sites = SiteDetails::find($id);
            }
        } else {
            // dd('else');
            $sites = SiteDetails::find($id);
        }
        //  dd($sites);
        return view('viewsite')->with('sites', $sites)->with('id', $id)->with('client_id', $client_id);
    }
    public function getClientSites($client_id)
    {
        return json_encode(SiteDetails::where('client_id', $client_id)->get());
    }

    public function playBackOfGuards($siteId)
    {
        $user = session('user');
        if ($user) {

            Log::info($user->name . ' view client guard list, User_id: ' . $user->id);
            $client_id = 'playBackSites';
            $siteName = SiteDetails::where('id', $siteId)->first();
            $guards = SiteAssign::where('role_id', 3)->where('site_id', $siteId)->get();
            $shift = SiteAssign::leftJoin('shifts', 'shifts.id', '=', 'site_assign.shift_id');
            //dd($guards);

            return view('playbackGuardslist')->with('guards', $guards)->with('site_id', $siteId)->with('client_id', $client_id)->with('siteName', $siteName);
        }
    }

    public function playBack($userId)
    {
        $toDate = date('d-m-Y');
        $guardName = Users::where('id', $userId)->where('role_id', '3')->first();
        $location = DB::table('guard_location_history')->where('date', $toDate)->where('user_id', $userId)->get();
        return view('playback')->with('userId', $userId)->with('data', $location)->with('guardName', $guardName);
    }


    public function updateGuardLocation(Request $request)
    {
        $fromDate = date('d-m-Y', strtotime($request->userfromDate));
        $location = DB::table('guard_location_history')->where('date', $fromDate)->where('user_id', $request->userId)->limit(100)->get();
        return $location;
    }

    public function export($clientID)
    {
        $user = session('user');
        $company = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $company ? $company->name : 'Unknown Company';
        
        $clientName = '';
        if ($clientID != 0) {
            $client = ClientDetails::where('id', $clientID)->first();
            $clientName = $client ? $client->name : '';
            
            if ($user->role_id == 1) {
                $sites = SiteDetails::where('company_id', $user->company_id)->where('client_id', $clientID)->orderBy('name', 'asc')->get();
            } else if ($user->role_id == 2) {
                $user_assign = SiteAssign::where('user_id', $user->id)->where('role_id', 2)->first();
                $siteArray = $user_assign ? json_decode($user_assign->site_id, true) : [];
                $sites = SiteDetails::whereIn('id', $siteArray)->get();
            }
        } else {
            if ($user->role_id == 1) {
                $sites = SiteDetails::where('company_id', $user->company_id)->orderBy('name', 'asc')->get();
            } else if ($user->role_id == 2) {
                $user_assign = SiteAssign::where('user_id', $user->id)->where('role_id', 2)->first();
                $siteArray = $user_assign ? json_decode($user_assign->site_id, true) : [];
                $sites = SiteDetails::whereIn('id', $siteArray)->get();
            }
        }

        return $this->excel->download(new SiteExport($sites, $companyName, $clientName), 'sites.xlsx');
    }

    public function success()
    {
        return redirect()->back()->with('success', 'Site deleted successfully.');
    }
}

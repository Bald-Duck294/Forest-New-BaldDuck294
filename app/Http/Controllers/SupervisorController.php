<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use DateTime;
use App\ClientDetails;
use App\SiteDetails;
use App\Users;
use App\SiteAssign;
use App\Shifts;
use App\CompanyDetails;
use App\ActivityLog;
use Log;
use Validator;
use App\Exports\SupervisorExport;



class SupervisorController extends Controller
{
    private $excel;
    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
    }

    //supervisor list
    public function index()
    {
        $user = session('user');
        Log::info($user->name . ' view supervisor list, User_id: ' . $user->id);
        $option = "";

        if ($user->role_id == '1') {

            $records = DB::table('users')
                ->where('users.company_id', $user->company_id)
                ->leftJoin('site_assign', 'users.id', '=', 'site_assign.user_id')
                ->where('users.role_id', 2)
                ->where('users.showUser', 1)
                ->select('users.id as userId', 'users.name', 'site_assign.shift_name', 'site_assign.shift_time')
                ->get();
        } else if ($user->role_id == '2') {

            $records = DB::table('users')
                ->where('users.company_id', $user->company_id)
                ->leftJoin('site_assign', 'users.id', '=', 'site_assign.user_id')
                ->where('users.role_id', 2)
                ->where('users.showUser', 1)
                ->select('users.id as userId', 'users.name', 'site_assign.shift_name', 'site_assign.shift_time')
                ->get();
        } else if ($user->role_id == '7') {
            //dd($user);

            // $siteAssign = SiteAssign::where('user_id',$user->id)->first();
            // // dd($siteAssign);
            // $sitesIds = json_decode($siteAssign->site_id);
            // if($sitesIds != []){
            //     $clientIds = SiteDetails::whereIn('client_id',$sitesIds)->get();

            //     dd($clientIds);
            // }

            $clients = SiteAssign::where('user_id', $user->id)->pluck('site_id')->toArray();
            $siteArray = SiteDetails::whereIn('client_id', json_decode($clients[0], true))->pluck('id')->toArray();
            // $siteAssign = SiteAssign::()
            // dd($siteArray);
            if ($siteArray) {
                $records = Users::where([['users.company_id', '=', $user->company_id], ['users.role_id', '=', 2]])
                    ->leftJoin('site_assign as site', 'users.id', '=', 'site.user_id')
                    ->where(function ($query) use ($siteArray) {
                        foreach ($siteArray as $siteId) {
                            $query->orWhereRaw('JSON_CONTAINS(site.site_id, ?)', [json_encode([$siteId])]);
                        }
                    })
                    ->where('users.showUser', 1)
                    ->select('users.id as userId', 'users.name', 'site.shift_name', 'site.shift_time')
                    ->orderBy('users.name', 'ASC')
                    ->get();
                // $records = Users::where([['users.company_id', '=', $user->company_id], ['users.role_id', '=', 2]])
                //     ->leftjoin('site_assign as site', 'users.id', '=', 'site.user_id')
                //     ->where(function ($query) use ($siteArray) {
                //         foreach ($siteArray as $siteId) {
                //             $query->orWhereRaw('FIND_IN_SET(?, site.site_id)', [$siteId]);
                //         }
                //     })
                //     ->select('users.id as userId', 'users.name', 'site.shift_name', 'site.shift_time')
                //     // ->selectRaw('users.*, site.shift_name as shift_name, site.shift_time as shift_time')
                //     ->orderBy('users.name', 'ASC')
                //     ->get();
            } else {
                $records = [];
            }

            // dd($records);



            // $records = DB::table('users')
            //     ->where('users.company_id', $user->company_id)
            //     ->leftJoin('site_assign', 'users.id', '=', 'site_assign.user_id')
            //     ->where('users.role_id', 2)
            //     ->select('users.id as userId', 'users.name', 'site_assign.shift_name', 'site_assign.shift_time')
            //     ->get();

            // dd($records);

        }
        return view('supervisorlist')->with('option', $option)->with('supervisors', $records);
    }


    public function getSupervisor(Request $request)
    {
        $user = session('user');
        Log::info($user->name . ' view supervisor list, User_id: ' . $user->id);
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
        $cur_date = new DateTime();
        $date = $cur_date->format("Y-m-d");


        if ($user->role_id == '1' || $user->role_id == '7') {
            $totalRecords = DB::table('users')
                ->where('users.company_id', $user->company_id)
                ->where('users.role_id', 2)
                ->where('users.showUser', 1)
                ->select('count(*) as allcount')
                ->count();
            $totalRecordswithFilter = DB::table('users')
                ->where('users.company_id', $user->company_id)
                ->where('users.role_id', 2)
                ->where('users.showUser', 1)
                ->select('count(*) as allcount')
                ->where('users.name', 'like', '%' . $searchValue . '%')
                ->count();
            // Fetch records
            $records = DB::table('users')
                ->where('users.company_id', $user->company_id)
                ->leftJoin('site_assign', 'users.id', '=', 'site_assign.user_id')
                ->where('users.role_id', 2)
                ->where('users.showUser', 1)
                ->where('users.name', 'like', '%' . $searchValue . '%')
                ->select('users.id as userId', 'users.name', 'site_assign.shift_name', 'site_assign.shift_time')
                ->skip($start)
                ->take($rowperpage)
                ->get();
        } else if ($user->role_id == '2') {
            $totalRecords = DB::table('users')
                ->where('users.company_id', $user->company_id)
                ->where('users.role_id', 2)
                ->where('users.showUser', 1)
                ->select('count(*) as allcount')
                ->count();
            $totalRecordswithFilter = DB::table('users')
                ->where('users.company_id', $user->company_id)
                ->where('users.role_id', 2)
                ->where('users.showUser', 1)
                ->select('count(*) as allcount')
                ->where('users.name', 'like', '%' . $searchValue . '%')
                ->count();
            // Fetch records
            $records = DB::table('users')
                ->where('users.company_id', $user->company_id)
                ->leftJoin('site_assign', 'users.id', '=', 'site_assign.user_id')
                ->where('users.role_id', 2)
                ->where('users.showUser', 1)
                ->where('users.name', 'like', '%' . $searchValue . '%')
                ->select('users.id as userId', 'users.name', 'site_assign.shift_name', 'site_assign.shift_time')
                ->skip($start)
                ->take($rowperpage)
                ->get();
        }

        $data_arr = array();
        $sno = $start + 1;
        foreach ($records as $record) {
            $name = $record->name;
            $userId = $record->userId;
            $shift_name = "Not Assigned";
            if ($record->shift_name != null) {
                $shift_name = $record->shift_name;
            }
            $shift_time = json_decode($record->shift_time);
            if ($shift_time != null) {
                $shift = $shift_time->start . " - " . $shift_time->end;
            } else {
                $shift = "NA";
            }

            $data_arr[] = array(
                //"profile_pic" => $profile_pic,
                "name" => $name,
                "userId" => $userId,
                'shift_name' => $shift_name,
                'shift' => $shift,
            );
            //print_r($data_arr);exit;
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
    public function getAssignSite_create()
    {
        $user = session('user');
        Log::info($user->name . ' view assign site to supervisor form, User_id: ' . $user->id);
        $clients = ClientDetails::where('company_id', $user->company_id)
            ->orderBy('name')
            ->get();

        return view('createsupervisorsite')
            ->with('clients', $clients);
    }

    public function getSite($client_id)
    {
        $user = session('user');
        Log::info($user->name . ' get dependent sites, User_id: ' . $user->id);
        return json_encode(DB::table('site_details')->where('client_id', $client_id)->orderBy('name', 'Asc')->get());
    }

    public function getshift($site_id)
    {
        $user = session('user');
        Log::info($user->name . ' get dependent shifts, User_id: ' . $user->id);
        return json_encode(DB::table('shifts')->where('company_id', $user->company_id)->orderBy('shift_name', 'Asc')->get());
    }

    public function getSiteSupervisor($site_id)
    {
        $user = session('user');
        Log::info($user->name . ' get supervisor under sites, User_id: ' . $user->id);
        if ($user) {
            $assignedSupervisorsFromSiteAssigned = SiteAssign::where('site_id', 'like', '%' . $site_id . '%')->where('role_id', 2)->get();
            $assignedSupervisorsArray = [];
            foreach ($assignedSupervisorsFromSiteAssigned as $item) {
                //print_r($item['site_id']);exit;
                $geoArray = json_decode($item['site_id'], true);
                //print_r($geoArray);exit;
                foreach ($geoArray as $geo) {
                    //print_r($geo);exit;
                    if ($site_id == $geo) {
                        $assignedSupervisorsArray[] = $item['user_id'];
                    }
                }
                // dd($assignedSupervisorsArray);   
            }


            $assignedSupervisors = Users::whereIn('id', $assignedSupervisorsArray)->get();
            //print_r($assignedSupervisors);exit;
            $unAssignedSupervisors = Users::where([['company_id', $user->company_id], ['role_id', 2]])->whereNotIn('id', $assignedSupervisorsArray)->get();

            return response()->json([
                'message' => 'Data Fetched',
                'assigned' => $assignedSupervisors,
                'unassigned' => $unAssignedSupervisors,
                'status' => 'SUCCESS',
                'code' => 200,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Unauthorized access',
                'status' => 'FAILURE',
                'code' => 200,
            ], 200);
        }
    }


    public function deleteAssignSupervisor($supervisor_id, $site_id)
    {
        $user = session('user');
        Log::info($user->name . ' delete assign supervisor to site, User_id: ' . $user->id);

        $siteAssign = SiteAssign::where('user_id', $supervisor_id)->first();
        ActivityLog::create([
            'date_time' => date('Y-m-d H:i:s'),
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Release supervisor assigned to site",
            'message' => "Supervisor assigned to site '" . $siteAssign->site_name . "' released by " . $user->name,
        ]);
        //print_r($siteAssign);exit;
        $siteArray = [];
        $siteArray = json_decode($siteAssign->site_id, true);
        //print_r($site_id);exit;
        $index = array_search($site_id, $siteArray);

        unset($siteArray[$index]);


        $siteAssign->site_id = json_encode($siteArray, true);
        $siteAssign->save();
        return response()->json([
            'status' => 'Success',
        ]);

        // alert()->success('Sweet Alert with success.','Site Removed');
        // return redirect()->route( 'supervisor.getAssignSite_create');
        // } else {
        //     return response()->json([
        //         'message' => 'Unauthorized Access',
        //         'status' => 'FAILURE',
        //         'code' => 200,
        //     ], 200);
        // }

    }
    public function getAssignSite_createaction(Request $request)
    {
        $users = session('user');
        ActivityLog::create([
            'date_time' => date('Y-m-d H:i:s'),
            'company_id' => $users->company_id,
            'user_id' => $users->id,
            'user_name' => $users->name,
            'type' => "Assign site to supervisor",
            'message' => "Site assign to supervisor by " . $users->name,
        ]);

        $messages = [
            'client.required' => '*Required',
            'site.required' => '*Required',
            'supervisor.required' => '*Required',
            'shift.required' => '*Required',
            'startdate.required' => '*Required',
            'enddate.required' => '*Required',
        ];

        $validator = Validator::make($request->all(), [
            'client' => 'required',
            'site' => 'required|max:100',
            'shift' => 'required',
            'startdate' => 'required',
            'enddate' => 'required',
        ], $messages);

        if ($validator->fails()) {
            dd(1);
            return redirect('/supervisor/getAssignSite_create')
                ->withErrors($validator)
                ->withInput();
        } else {
            // dd(2);
            $shift = Shifts::where('id', $_POST['shift'])->first();
            // dd($shift);
            $user = Users::where('id', $_POST['supervisor'])->first();
            $date = array(
                "from" => date('Y-m-d', strtotime($_POST["startdate"])),
                "to" => date('Y-m-d', strtotime($_POST["enddate"]))
            );
            // dd($shiftDetails);
            $dateArray = json_encode($date, true);


            $user = Users::where('id', $user->id)->first();

            if ($user) {
                // dd(4);
                $site_ids = $_POST['site'];
                // foreach ($supervisorData as $supervisor) {
                $assignCheck = SiteAssign::where('user_id', $_POST['supervisor'])->first();

                if ($assignCheck) {
                    $sites = json_decode($assignCheck->site_id, true);
                    $siteArray = [];
                    $siteArray = $sites;

                    // dd($assignCheck->site_id);
                    $siteCount = count($siteArray);
                    if ($siteCount > 0) {
                        // dd(6);
                        foreach ($site_ids as $id) {
                            foreach ($siteArray as $index => $val) {
                                if ($id == $val) {
                                    // dd('Site already assigned to supervisor');
                                    break;
                                } else {
                                    // dd('Site assigned to supervisor successfully');
                                    // if ($index == $siteCount - 1) {
                                    $sites[] = (int) $id;
                                    // }
                                }
                            }
                        }
                    } else {
                        foreach ($site_ids as $ids) {
                            $sites[] = (int) $ids;
                        }
                    }
                    // dd($sites);
                    $assignCheck->site_id = json_encode($sites, true);
                    $assignCheck->shift_id = $shift->id;
                    $assignCheck->shift_name = $shift->shift_name;
                    $assignCheck->shift_time = $shift->shift_time;
                    $assignCheck->date_range = $dateArray;
                    // dd($assignCheck);
                    $assignCheck->save();
                    return redirect()->route('supervisor');
                } else {
                    // dd(5);
                    $site_id = [];
                    foreach ($site_ids as $ids) {
                        $site_id[] = (int) $ids;
                    }
                    $assign = new SiteAssign();

                    $assign->user_id = $_POST['supervisor'];
                    $assign->user_name = $user->name;
                    $assign->site_id = json_encode($site_id, true);
                    $assign->company_id = $user->company_id;
                    $assign->shift_id = $_POST['shift'];
                    $assign->shift_name = $shift->shift_name;
                    $assign->shift_time = $shift->shift_time;
                    $assign->date_range = $dateArray;
                    $assign->role_id = 2;

                    $assign->save();
                    return redirect()->route('supervisor');
                }
            } else {
                // dd(3);
            }
        }
        // $site = $_POST['site'];
        // $siteArray=  json_encode($site);   
        // //print_r($siteArray);exit;
        // $user = session('user');
        // $new_site = new SiteAssign();
        // $username = Users::where('company_id','=',$user->company_id)->where('id','=',$_POST['supervisor'])->first();

        // $new_site->user_id = $username->id;
        // $new_site->site_id =  $siteArray;
        // $new_site->client_id = $_POST['client'];
        // $new_site->supervisor_id = $_POST['supervisor'];
        // $new_site->user_name = $username->name;
        // $new_site->company_id	 = $username->company_id;
        // $new_site->shift_id = $_POST['shift'];
        // $new_site->role_id = '2';
        // $new_site->save();
        // return redirect()->route('supervisor');
        // dd($request);


    }
    public function supervisorDetails($supervisor_id)
    {

        $user = session('user');

        Log::info($user->name . ' view supervisor details, User_id: ' . $user->id);
        $supervisorDetails = Users::where('id', $supervisor_id)->first();
        $siteAssign = SiteAssign::where('user_id', $supervisor_id)->first();
        // dd($supervisorDetails, $supervisor_id,$siteAssign);
        return view('supervisordetails')->with('supervisorDetails', $supervisorDetails)->with('siteAssign', $siteAssign);
    }

    public function siteRelease($supervisorId, $siteId)
    {
        if ($supervisorId) {
            $sites = SiteAssign::where('user_id', $supervisorId)->first();
            if ($siteId) {
                $site_ids = json_decode($sites->site_id, true);
                $index = array_search($siteId, $site_ids);
                unset($site_ids[$index]);

                $sites->site_id = json_encode($site_ids, true);
                $sites->save();
                return redirect()->route('sites.getSupervisorSites', $supervisorId);
            }
        }
    }

    public function export($flag)
    {
        // dd($flag);
        $user = session('user');
        if ($user->role_id == 1) {
            $supervisors = DB::table('users')
                ->where('users.company_id', $user->company_id)
                ->leftJoin('site_assign', 'users.id', '=', 'site_assign.user_id')
                ->where('users.role_id', 2)
                ->select('users.id as userId', 'users.name', 'site_assign.shift_name', 'site_assign.shift_time')
                ->get();
        } else if ($user->role_id == 7) {

            $clients = SiteAssign::where('user_id', $user->id)->pluck('site_id')->toArray();
            $siteArray = SiteDetails::whereIn('client_id', json_decode($clients[0], true))->pluck('id')->toArray();

            // $supervisors = DB::table('users')
            //     ->where('users.company_id', $user->company_id)
            //     ->where('users.role_id', 2)
            //     ->leftjoin('site_assign as site', 'users.id', '=', 'site.user_id')
            //     ->where(function ($query) use ($siteArray) {
            //         foreach ($siteArray as $siteId) {
            //             $query->orWhereRaw('FIND_IN_SET(?, site.site_id)', [$siteId]);
            //         }
            //     })
            //     ->select('users.id as userId', 'users.name', 'site.shift_name', 'site.shift_time')
            //     ->get();

            $supervisors = Users::where([['users.company_id', '=', $user->company_id], ['users.role_id', '=', 2]])
                ->leftJoin('site_assign as site', 'users.id', '=', 'site.user_id')
                ->where(function ($query) use ($siteArray) {
                    foreach ($siteArray as $siteId) {
                        $query->orWhereRaw('JSON_CONTAINS(site.site_id, ?)', [json_encode([$siteId])]);
                    }
                })
                ->select('users.id as userId', 'users.name', 'site.shift_name', 'site.shift_time')
                ->orderBy('users.name', 'ASC')
                ->get();
        }
        $companyName = CompanyDetails::where('id', $user->company_id)->pluck('name');
        $companyName = $companyName['0'];
        return $this->excel->download(new SupervisorExport($supervisors, $companyName, $flag), 'supervisors.xlsx');
    }
}

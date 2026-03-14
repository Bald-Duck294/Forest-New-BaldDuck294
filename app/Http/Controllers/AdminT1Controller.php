<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use App\CompanyDetails;
use App\Users;
use App\SiteDetails;
use App\SiteAssign;
use App\ClientDetails;
use Log;
use Validator;
use App\ActivityLog;
use DateTime;
use App\Shifts;

use App\Exports\AdminT1Export;



class AdminT1Controller extends Controller
{

    private $excel;
    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
    }
    //fetch admin list
    public function getAdmin()
    {
        $user = session('user');
        $option = "";
        if ($user->role_id == "0") {
            $admin = Users::where('role_id', 1)->get();
            return view('companies/guardlist')->with('option', $option)->with('admin', $admin);
        }
    }



    //fetch admin end

    //fetch supervisor start
    public function getSupervisor()
    {
        $user = session('user');
        if ($user->role_id == "0") {
            $supervisors = Users::where('role_id', 2)->get();
            return view('companies/supervisorlist')->with('supervisors', $supervisors);
        }
    }


    public function getAssignSite_create()
    {
        $user = session('user');
        Log::info($user->name . ' view assign site to supervisor form, User_id: ' . $user->id);

        $clients = ClientDetails::where('company_id', $user->company_id)
            ->orderBy('name')->get();

        $admins = Users::where('company_id', $user->company_id)->where('role_id', 7)->orderBy('name')->get();
        //dd($admin); 

        $shifts = Shifts::where('company_id', $user->company_id)->get();
        // dd($shifts);

        return view('createAdminT1Site')->with('clients', $clients)->with('admins', $admins)->with('shifts', $shifts);
    }

    public function getAssignSite_createaction(Request $request)
    {
        // dd($request);
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
            'supervisor.required' => '*Required',
            'shift.required' => '*Required',
            'startdate.required' => '*Required',
            'enddate.required' => '*Required',
        ];

        $validator = Validator::make($request->all(), [
            'client' => 'required',
            'shift' => 'required',
            'startdate' => 'required',
            'enddate' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return redirect('/adminT1/getAssignSite_create')
                ->withErrors($validator)
                ->withInput();
        } else {
            $shift = Shifts::where('id', $_POST['shift'])->first();
            //dd($shift);
            $date = array(
                "from" => date('Y-m-d', strtotime($_POST["startdate"])),
                "to" => date('Y-m-d', strtotime($_POST["enddate"]))
            );
            //dd($shift);
            $dateArray = json_encode($date, true);

            //$users = Users::where('id', $user['id'])->first();
            // if ($user) {
            $client_ids = $_POST['client'];
            $client_ids = array_map('intval', $client_ids);
            //dd( $_POST['client']);
            //$client = json_decode($_POST['client'], true);
            $admins = $_POST['admin'];
            //$admins = json_decode($_POST['admin'], true);
            // dd($request->all(), $admins);
            foreach ($admins as $admin) {
                //dd($admin);
                $user = Users::where('id', $admin)->first();
                $assignCheck = SiteAssign::where('user_id', $admin)->first();

                if ($assignCheck) {
                    $sites = json_decode($assignCheck->site_id, true);
                    $siteArray = [];
                    $siteArray = $sites;
                    // dd($assignCheck->site_id);
                    // dd($client_ids);
                    $siteCount = count($siteArray);
                    // dd($siteArray);
                    // if ($siteCount > 0) {
                    // $clientId = json_decode($client_ids, true);
                    //   dd($clientId);
                    $array = array_unique(array_merge($siteArray, $client_ids));
                    // dd($array);
                    // $array = Arr::collapse($siteArray, $client_ids);

                    // dd(json_encode($array, true));
                    $arr = json_encode($array, true);

                    $assignCheck->site_id = $arr;
                    $assignCheck->shift_id = $shift->id;
                    $assignCheck->shift_name = $shift->shift_name;
                    $assignCheck->shift_time = $shift->shift_time;
                    $assignCheck->date_range = $dateArray;
                    // dd($assignCheck);
                    $assignCheck->save();

                    // return redirect()->route('admin_t1');
                    // }
                } else if ($user) {

                    // dd($dateArray);

                    $site_id = [];

                    foreach ($client_ids as $ids) {
                        $site_id[] = (int) $ids;
                    }

                    $assign = new SiteAssign();
                    $assign->user_id = $user->id;
                    $assign->user_name = $user->name;
                    $assign->site_id = json_encode($site_id, true);
                    $assign->company_id = $user->company_id;
                    $assign->shift_id = $_POST['shift'];
                    $assign->shift_name = $shift->shift_name;
                    $assign->shift_time = $shift->shift_time;
                    $assign->date_range = $dateArray;
                    $assign->role_id = 7;
                    // dd($_POST['admin']);
                    $assign->save();
                    // return redirect()->route('admin_t1');
                }
                // else {
                //     return redirect()->route('admin_t1');
                // }
            }

            return redirect()->route('admin_t1');

            // }
        }
    }



    public function adminT1()
    {

        //dd('getAdminT1');
        $user = session('user');
        Log::info($user->name . ' view admin list, User_id: ' . $user->id);
        $option = "";
        $records = DB::table('users')
            ->where('users.company_id', $user->company_id)
            ->leftJoin('site_assign', 'users.id', '=', 'site_assign.user_id')
            ->where('users.role_id', 7)->where('users.showUser', 1)
            ->select('users.id as userId', 'users.name', 'site_assign.shift_name', 'site_assign.shift_time')
            ->get();

        //dd($records);

        return view('adminT1')->with('option', $option)->with('supervisors', $records);
    }


    public function getAdminT1(Request $request)
    {
        //dd($request);

        $user = session('user');

        Log::info($user->name . ' view admin list, User_id: ' . $user->id);
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; //Column index
        $columnName = $columnName_arr[$columnIndex]['data']; //Column name
        $columnSortOrder = $order_arr[0]['dir']; //asc or desc
        $searchValue = $search_arr['value']; //Search value
        $cur_date = new DateTime();
        $date = $cur_date->format("Y-m-d");

        if ($user->role_id == '1') {
            $totalRecords = DB::table('users')
                ->where('users.company_id', $user->company_id)
                ->where('users.role_id', 7)
                // ->where('users.showUser',1)
                ->select('count(*) as allcount')
                ->count();
            $totalRecordswithFilter = DB::table('users')
                ->where('users.company_id', $user->company_id)
                ->where('users.role_id', 7)
                // ->where('users.showUser',1)
                ->select('count(*) as allcount')
                ->where('users.name', 'like', '%' . $searchValue . '%')
                ->count();
            // Fetch records
            $records = DB::table('users')
                ->where('users.company_id', $user->company_id)
                ->leftJoin('site_assign', 'users.id', '=', 'site_assign.user_id')
                ->where('users.role_id', 7)
                // ->where('users.showUser',1)
                ->where('users.name', 'like', '%' . $searchValue . '%')
                ->select('users.id as userId', 'users.name', 'site_assign.shift_name', 'site_assign.shift_time')
                ->skip($start)
                ->take($rowperpage)
                ->get();
        } else if ($user->role_id == '2') {
            $totalRecords = DB::table('users')
                ->where('users.company_id', $user->company_id)
                ->where('users.role_id', 2)

                ->select('count(*) as allcount')
                ->count();
            $totalRecordswithFilter = DB::table('users')
                ->where('users.company_id', $user->company_id)
                ->where('users.role_id', 2)
                ->select('count(*) as allcount')
                ->where('users.name', 'like', '%' . $searchValue . '%')
                ->count();
            // Fetch records
            $records = DB::table('users')
                ->where('users.company_id', $user->company_id)
                ->leftJoin('site_assign', 'users.id', '=', 'site_assign.user_id')
                ->where('users.role_id', 2)->where('users.showUser', 1)
                ->where('users.name', 'like', '%' . $searchValue . '%')
                ->select('users.id as userId', 'users.name', 'site_assign.shift_name', 'site_assign.shift_time')
                ->skip($start)
                ->take($rowperpage)
                ->get();
        } else if ($user->role_id == "7") {
            $totalRecords = DB::table('users')
                ->where('users.company_id', $user->company_id)
                ->where('users.role_id', 7)

                ->select('count(*) as allcount')
                ->count();
            $totalRecordswithFilter = DB::table('users')
                ->where('users.company_id', $user->company_id)
                ->where('users.role_id', 7)
                ->select('count(*) as allcount')
                ->where('users.name', 'like', '%' . $searchValue . '%')
                ->count();
            // Fetch records
            $records = DB::table('users')
                ->where('users.company_id', $user->company_id)
                ->leftJoin('site_assign', 'users.id', '=', 'site_assign.user_id')
                ->where('users.role_id', 7)->where('users.showUser', 1)
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
            // dd($shift_time);
            if ($shift_time != null) {
                $shift = $shift_time->start . " - " . $shift_time->end;
            } else {
                $shift = "NA";
            }



            $data_arr[] = array(
                // "profile_pic" => $profile_pic,
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


    public function deleteAssignAdminT1($supervisor_id, $site_id)
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
    }


    public function export()
    {
        $user = session('user');
        if ($user->role_id == 1) {
            $adminT1 = DB::table('users')
                ->where('users.company_id', $user->company_id)
                ->leftJoin('site_assign', 'users.id', '=', 'site_assign.user_id')
                ->where('users.role_id', 7)->where('users.showUser', 1)
                ->select('users.id as userId', 'users.name', 'site_assign.shift_name', 'site_assign.shift_time')
                ->get();
        }
        $companyName = CompanyDetails::where('id', $user->company_id)->pluck('name');
        $companyName = $companyName['0'];
        return $this->excel->download(new AdminT1Export($adminT1, $companyName), 'adminT1.xlsx');
    }

    //fetch supervisor end


    //fetch sites end



    public function adminDetails($admin_id)
    {

        $user = session('user');

        Log::info($user->name . ' view supervisor details, User_id: ' . $user->id);
        $admin = Users::where('id', $admin_id)->first();
        $siteAssign = SiteAssign::where('user_id', $admin_id)->first();
        // dd($admin, $siteAssign);
        return view('adminT1Details')->with('admin', $admin)->with('siteAssign', $siteAssign);
    }
}

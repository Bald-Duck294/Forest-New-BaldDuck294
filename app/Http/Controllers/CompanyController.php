<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\CompanyDetails;
use App\Users;
use App\SiteDetails;
use App\ClientDetails;
use Log;
use DateTime;


class CompanyController extends Controller
{
    // fetch company list
    public function index()
    {
        $user = session('user');
        if ($user->role_id == "0") {
            $companies = CompanyDetails::get();
            return view('companies/companylist')->with('companies', $companies);
        }
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
            ->orderBy('name')
            ->get();

        return view('createAdminT1Site')
            ->with('clients', $clients);
    }



    public function adminT1()
    {
        
        //dd('getAdminT1');
        $user =  session('user');
        Log::info($user->name . ' view admin list, User_id: ' . $user->id);
        $option = "";
        $records = DB::table('users')
            ->where('users.company_id', $user->company_id)
            ->leftJoin('site_assign', 'users.id', '=', 'site_assign.user_id')
            ->where('users.role_id', 7)
            ->select('users.id as userId', 'users.name', 'site_assign.shift_name', 'site_assign.shift_time')
            ->get();

            //dd($records);

        return view('adminT1')->with('option', $option)->with('supervisors', $records);
    }


    public function getAdminT1(Request $request)
    {
        $user = session('user');
        Log::info($user->name . ' view admin list, User_id: ' . $user->id);
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


        if ($user->role_id == '1') {
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
                ->where('users.role_id', 2)
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
                ->where('users.role_id', 2)
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

    //fetch supervisor end

    //fetch sites start
    public function getSite()
    {
        $user = session('user');
        if ($user->role_id == "0") {
            $sites = SiteDetails::get();
            return view('companies/sitelist')->with('sites', $sites);
        }
    }
    //fetch sites end

    //company dashboard
    public function dashboard()
    {
        $user = session('user');
        if ($user->role_id == "0") {
            $company = CompanyDetails::select('count(*) as allcount')->count();
            $guards = Users::where('role_id', 3)->select('count(*) as allcount')->count();
            $clients = ClientDetails::select('count(*) as allcount')->count();
            $admin = Users::where('role_id', 1)->select('count(*) as allcount')->count();
            $supervisors = Users::where('role_id', 2)->select('count(*) as allcount')->count();
            $site = SiteDetails::select('count(*) as allcount')->count();

            return view('welcomenew')->with('company', $company)->with('guards', $guards)->with('clients', $clients)->with('admin', $admin)->with('supervisors', $supervisors)->with('site', $site);
        }
    }

    //create company
    public function createCompany()
    {
        $features =  DB::table('checkList')->where('type', '=', 'feature')->first();
        $featureList = json_decode($features->checkList);
        return view('companies/createcompany')->with('featureList', $featureList);
    }

    //company create action
    public function createActionCompany(Request $request)
    {

        $features = json_encode($request->features);
        // dd($features);
        $new_company = new CompanyDetails();
        $new_company->name  = $request->name;
        $new_company->contact  = $request->contact;
        $new_company->email     = $request->email;
        $new_company->contact_person   = $request->contactperson;
        $new_company->contact_person_contact  = $request->contactnumber;
        $new_company->contact_person_designation      = $request->contactpersondesignation;
        $new_company->address      = $request->address;
        $new_company->empLimit      = $request->employeelimit;
        $new_company->personGroupId      = $request->persongroupid;
        $new_company->azureGroupId      = $request->azuregroupid;
        $new_company->azureGroupType      = $request->azuregrouptype;
        // $new_company->timestamp = date('Y-m-d h:i:s');

        $new_company->save();
        return redirect()->route('companies');
    }

    //update company
    public function updateCompany($company_id)
    {
        $company = CompanyDetails::find($company_id);
        return view('companies/updatecompany')->with('company', $company)->with('id', $company_id);
    }

    //update company action
    public function updateActionCompany(Request $request, $company_id)
    {
        $company = CompanyDetails::find($company_id);
        $company->name = $request->name;
        $company->contact  = $request->contact;
        $company->email = $request->email;
        $company->contact_person = $request->contactperson;
        $company->contact_person_contact = $request->contactnumber;
        $company->contact_person_designation = $request->contactpersondesignation;
        $company->address = $request->address;
        $company->empLimit = $request->employeelimit;
        $company->personGroupId = $request->persongroupid;
        $company->azureGroupId     = $request->azuregroupid;
        $company->azureGroupType = $request->azuregrouptype;
        // $company->timestamp = date('Y-m-d H:i:s');;
        $company->save();

        return redirect()->route('companies');
    }

    //delete company
    public function deleteCompany($company_id)
    {
        CompanyDetails::where('id', $company_id)->delete();
        return redirect()->route('companies');
    }

    //view company
    public function viewCompany($company_id)
    {
        // $session_id = session()->getId();
        // dd($session_id);
    }
}

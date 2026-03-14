<?php

namespace App\Http\Controllers;

use App\LeavePeriodHistory;
use App\LeaveStatus;
use App\Leaves;
use App\LeaveHoliday;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use DateTime;
use DateInterval;
use Log;
use App\SiteAssign;
use App\LeaveType;
use App\LeaveRequest;
use App\Leave;
use App\LeaveEntitlement;
use App\Users;
use App\States;
use App\SiteDetails;
use App\Cities;
use App\ClientDetails;
use App\LeaveAdjustment;
use App\LeaveEntitlementAdjustment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\ActivityLog;
use App\LeaveRequestComment;

class LeaveController extends Controller
{
    public function index()
    {
        return view('leaves.leaves');
    }

    public function advanceLeaveCreate()
    {
        return view('leaves.create');
    }

    public function applyLeave()
    {
        //$leave = LeaveType::all();
        $user = session('user');
        // dd($user , "user");
        $user = session('user');
        $leaves = LeaveEntitlement::where('user_id', $user->id)->where('company_id', $user->company_id)->get();

        // dd($leaves , "leaves");
        // dump($leaves , "leaves");   
        return view('leaves.applyLeave', compact('leaves'));
    }

    public function leaveList()
    {
        $user = session('user');
        $query = 'leaves.*, (select SUM(no_of_days) from leave_entitlements le where le.user_id = leaves.user_id and le.leave_type_id = leaves.leave_type_id) as no_of_days 
        , (select SUM(days_used) from leave_entitlements le where le.user_id = leaves.user_id and le.leave_type_id = leaves.leave_type_id) as days_used';
        $emp = Leave::where('company_id', $user->company_id)
            ->selectRaw($query)
            ->get();

        $leave = LeaveType::where('company_id', $user->company_id)->get();
        $leaveStatus = LeaveStatus::all();

        // dd($leave , $emp , $leaveStatus , "status");
        return view('leaves.leaveList')->with('leaveStatus', $leaveStatus)->with('leave', $leave)->with('emp', $emp);
    }

    public function leaveListAction(Request $req)
    {
        //dd($req);
        $type = $req->type;
        //$employee = Users::where('name',$req->employee)->first();
        return $type;
    }

    public function assignLeave()
    {
        //$leave = LeaveType::all();
        $user = session('user');
        // dd($user , "user");
        // $userArray = Users::pluck('id');
        // $site = SiteAssign::whereNotIn('user_id',$userArray)->pluck('id');
        // dd($site);
        //$leave = LeaveEntitlement::get();
        //dd($leave);
        return view('leaves.assignLeave');
    }


    public function createLeave(Request $req)
    {

        // dd($req->all() , "requesst all");
        // dd($req);
        $user = session('user');
        // dd($user);
        // dd($siteArray);
        $cur_date = new DateTime();
        $currDateTime = $cur_date->format('Y-m-d H:i:m');
        //dd($currDateTime);
        // $leave = Leave::where('user_id',$user->id)->whereBetween('todate',[$req->fromDate,$req->toDate])->get();

        $currDate = $cur_date->format('Y-m-d');
        $leaveEntitlement = LeaveEntitlement::find($req->type);
        $leaveType = LeaveType::where('id', $leaveEntitlement->leave_type_id)->where('company_id', $user->company_id)->first();
        //dd($leaveType);
        $startDate = date('Y-m-d', strtotime($req->fromDate));
        $endDate = date('Y-m-d', strtotime($req->toDate));
        //dd($startDate , $endDate);
        $datetime1 = new DateTime($startDate);
        $datetime2 = new DateTime($endDate);
        $interval = $datetime1->diff($datetime2);
        //dd($interval);


        if ($req->fromDate == null) {
            $firstDate = date('Y-m-d', strtotime($req->toDate));
        } else {
            $firstDate = date('Y-m-d', strtotime($req->fromDate));
        }
        $lastDate = date('Y-m-d', strtotime($req->toDate));

        $check = Leave::where(function ($query) use ($firstDate, $lastDate) {
            $query->whereBetween('fromDate', [$firstDate, $lastDate]);
            $query->orWhereBetween('toDate', [$firstDate, $lastDate]);
            $query->orWhere(function ($query1) use ($firstDate, $lastDate) {
                $query1->where('fromDate', '<=', $firstDate);
                $query1->where('toDate', '>=', $lastDate);
            });
        })->where('user_id', $user->id)->count();

        // dd($check, $firstDate, $lastDate);
        if ($check > 0) {
            return redirect()->route('applyLeave')->with('warning', 'Leaves are overlapping with other leaves');
        } else {
            // dd($req);
            if ($req->fromDate != '') {
                //dd($req);
                $daysCount = (int) $interval->format('%a');
                $daysCount = $daysCount + 1;
                $day = $leaveEntitlement->no_of_days - $daysCount;

                // if ($daysCount > $leaveEntitlement->no_of_days) {
                //     //dd('if');
                //     $day_used = $leaveEntitlement->no_of_days - 0;
                //     //dd($day_used);
                // } else {
                //     //dd('else');
                //     $day_used = $leaveEntitlement->no_of_days - $daysCount;
                // }

                //dd($daysCount);

                if ($req->flag == 'confirm') {
                    $data = $req->all();

                    unset($data['type']);
                    $data['user_id'] = $user->id;
                    $data['user_name'] = $user->name;
                    $data['company_id'] = $user->company_id;
                    $data['reason'] = $req->reason;
                    $data['role_id'] = $user->role_id;
                    $data['leave_type_id'] = $leaveType->id;
                    $data['leave_type_name'] = $leaveType->name;
                    $data['duration'] = $req->day;
                    $data['is_paid'] = 1;
                    $data['requested_on'] = $currDate;

                    $data['status'] = 'Awaiting';
                    $data['action_on'] = '';
                    $data['actionById'] = '';
                    $data['actionByName'] = '';
                    $data['actionRemark'] = '';

                    $diff = $daysCount - $day - 1;
                    //dd($daysCount, $day, $diff);
                    $lastDate = $datetime1->add(new DateInterval('P' . $day - 1 . 'D'));
                    $first = $datetime2->sub(new DateInterval('P' . $diff . 'D'));

                    $data['toDate'] = $lastDate->format('Y-m-d');
                    $data['fromDate'] = $startDate;
                    // Leave::create($data);
                    $data['is_paid'] = 0;
                    $data['fromDate'] = $first->format('Y-m-d');
                    $data['toDate'] = $endDate;
                    //dd($data);
                    Leave::create($data);
                } else {

                    $leave = new Leave();
                    $leave->user_id =  $user->id;
                    $leave->user_name =  $user->name;
                    $leave->company_id =  $user->company_id;
                    $leave->role_id = $user->role_id;
                    $leave->leave_type_id =  $leaveType->id;
                    $leave->leave_type_name =  $leaveType->name;
                    $leave->duration = $req->day;
                    $leave->fromDate = $startDate;
                    $leave->toDate =  $endDate;
                    $leave->requested_on = $currDate;
                    $leave->reason = $req->reason;

                    $leave->status = 'Awaiting';
                    $leave->action_on = '';
                    $leave->actionById = '';
                    $leave->actionByName = '';
                    $leave->actionRemark = '';

                    $leave->save();
                }
            } else {

                // $day_used = $leaveEntitlement->no_of_days - 1;

                $leave = new Leave();
                $leave->user_id =  $user->id;
                $leave->user_name =  $user->name;
                $leave->company_id =  $user->company_id;
                $leave->role_id = $user->role_id;
                $leave->leave_type_id =  $leaveType->id;
                $leave->leave_type_name =  $leaveType->name;

                if ($req->day == '0.5') {
                    $leave->duration = 'Half Day';
                    $daysCount = 0.5;
                } else {
                    $leave->duration = 'Full Day';
                    $daysCount = 1;
                }
                $leave->fromDate = $endDate;
                $leave->toDate =  $endDate;
                $leave->requested_on = $currDate;
                $leave->reason = $req->reason;

                $leave->status = 'Awaiting';
                $leave->action_on = '';
                $leave->actionById = '';
                $leave->actionByName = '';
                $leave->actionRemark = '';
                $leave->save();
            }
            // dd($day_used);
            $leaveEntitlement->days_used =  $leaveEntitlement->days_used + $daysCount;

            //  dd($leaveEntitlement , $leaveEntitlement->days_used , $daysCount ,"leave entitlment");
            //if($leaveEntitlement->no_of_days > $day_used) {
            //     //dd($leaveEntitlement->no_of_days,$leaveEntitlement->days_used);
            //     $leaveEntitlement->days_used =  $leaveEntitlement->days_used + $day_used;
            // }else {
            //     //dd($leaveEntitlement->days_used);
            //     $leaveEntitlement->days_used = $day_used;
            // }
            $leaveEntitlement->save();

            // dd($leaveEntitlement , "leave entitlement");

            ActivityLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Apply Leave",
                'message' => "Leave applied by " . $user->name,
                'date_time' => date('Y-m-d H:i:s'),
            ]);

            return redirect()->route('applyLeave')->with('success', '"Leave successfully applied."');
        }
    }



public function assignAction(Request $req)
{
    $user = session('user');
    $site = SiteAssign::where('user_id', $req->user_id)->first();
    $emp = Users::where('id', $req->user_id)->first();
    $currDate = now()->format('Y-m-d');
    $startDate = $req->fromDate ? date('Y-m-d', strtotime($req->fromDate)) : date('Y-m-d', strtotime($req->toDate));
    $endDate = date('Y-m-d', strtotime($req->toDate));

    // Check for overlapping leaves only if they are Awaiting or Approved
    $overlappingLeaves = Leave::where(function ($query) use ($startDate, $endDate) {
        $query->whereBetween('fromDate', [$startDate, $endDate])
              ->orWhereBetween('toDate', [$startDate, $endDate])
              ->orWhere(function ($query1) use ($startDate, $endDate) {
                  $query1->where('fromDate', '<=', $startDate)
                         ->where('toDate', '>=', $endDate);
              });
    })
    ->where('user_id', $req->user_id)
    ->whereIn('status', ['Awaiting', 'Approved'])
    ->count();

    if ($overlappingLeaves > 0) {
        return redirect()->route('assignLeave')->with('warning', "Leaves are overlapping with existing Approved or Awaiting leaves.");
    }

    // Main leave assignment logic
    $datetime1 = new DateTime($startDate);
    $datetime2 = new DateTime($endDate);
    $interval = $datetime1->diff($datetime2);
    $leaveEntitlement = LeaveEntitlement::find($req->type);

    // dd($leaveEntitlement , "leave entitlemetn");

    if (!$leaveEntitlement) {
        return redirect()->route('assignLeave')->with('error', 'Invalid leave entitlement.');
    }

    $leaveType = LeaveType::where('id', $leaveEntitlement->leave_type_id)
                          ->where('company_id', $user->company_id)
                          ->first();

    if (!$leaveType) {
        return redirect()->route('assignLeave')->with('error', 'Leave type not found.');
    }

    $daysCount = 0;
    if ($req->fromDate) {
        $daysCount = (int) $interval->format('%a') + 1;
    } else {
        $daysCount = ($req->day == '0.5') ? 0.5 : 1;
    }

    $leaveData = [
        'user_id' => $req->user_id,
        'user_name' => $req->employee,
        'company_id' => $user->company_id,
        'site_id' => $site->site_id,
        'role_id' => $emp->role_id,
        'leave_type_id' => $leaveType->id,
        'leave_type_name' => $leaveType->name,
        'duration' => $req->day == '0.5' ? 'Half Day' : ($req->fromDate ? $req->day : 'Full Day'),
        'fromDate' => $startDate,
        'toDate' => $endDate,
        'requested_on' => $currDate,
        'reason' => $req->reason,
        'status' => 'Awaiting',
        'is_paid' => 1,
        'action_on' => null,
        'actionById' => null,
        'actionByName' => null,
        'actionRemark' => null,
    ];

    if ($req->flag == 'confirm' && $req->fromDate && $leaveEntitlement->no_of_days < $daysCount) {
        $paidDays = $leaveEntitlement->no_of_days;
        $unpaidDays = $daysCount - $paidDays;

        $leaveData['toDate'] = $datetime1->add(new DateInterval('P' . ($paidDays - 1) . 'D'))->format('Y-m-d');
        Leave::create($leaveData);

        $leaveData['fromDate'] = $datetime2->sub(new DateInterval('P' . ($unpaidDays - 1) . 'D'))->format('Y-m-d');
        $leaveData['toDate'] = $endDate;
        $leaveData['is_paid'] = 0;
        Leave::create($leaveData);
    } else {
        Leave::create($leaveData);
    }

    $leaveEntitlement->days_used += $daysCount;
    $leaveEntitlement->save();

    ActivityLog::create([
        'company_id' => $user->company_id,
        'user_id' => $user->id,
        'user_name' => $user->name,
        'type' => "Assign Leave",
        'message' => "Leave assigned by {$user->name}",
        'date_time' => now(),
    ]);

    return redirect()->route('assignLeave')->with('success', 'Leave successfully assigned.');
}


    public function editComment(Request $req)
    {
        //dd($req);L
        $empId = $req->empId;

        return $empId;
    }

    public function leavePeriodAction(Request $req)
    {
        //dd($req);
        $user = session('user');
        $cur_date = new DateTime();
        $currDateTime = $cur_date->format('Y-m-d H:i:m');
        //dd($currDateTime);
        $currDate = $cur_date->format('Y-m-d');
        //dd( $currDateTime);
        //$currDate = date('d-m-Y', strtotime($cur_date));
        $leavePeriod = new LeavePeriodHistory();
        $leavePeriod->leave_period_start_month = $req->month;
        $leavePeriod->leave_period_start_day = $req->date;
        $leavePeriod->created_at = $currDateTime;
        $leavePeriod->company_id = $user->company_id;
        $leavePeriod->save();
        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Create Leave Period",
            'message' => "Leave period created by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->route('leavePeriod')->with('success', '"Leave period successfully assigned."');
    }

    public function empEntitlements()
    {
        $user = session('user');
        // $leave = LeaveType::all();
        // dd($leave);
        //$entitlement = LeaveEntitlement::all();
        //dd($entitlement);
        return view('leaves.empEntitlements');
    }

    public function dataTableFilter(Request $req, $flag)
    {

        // dd($req->all() , "all");

        $statusSelected = $req->statusSelected;

        $user = session('user');
        if ($flag == 'empEntitlement') {

            if ($req->emp && $req->leaveId) {

                $leave = LeaveEntitlement::where('user_id', $req->emp)->where('company_id', $user->company_id)->where('leave_type_id', $req->leaveId)->with('leaveType')->get();
                //dd($leave);
            } elseif ($req->emp) {

                $leave = LeaveEntitlement::where('user_id', $req->emp)->where('company_id', $user->company_id)->with('leaveType')->get();
            }
        } elseif ($flag == 'myEntitlement') {

            $leave = LeaveEntitlement::where('leave_type_id', $req->leaveId)->where('company_id', $user->company_id)->where('user_id', $user->id)->with('leaveType')->get();
            // dd($leave);
        } elseif ($flag == 'leave') {
            // dd($req,$flag);
            $fromDate = date('Y-m-d', strtotime($req->startDate));
            $toDate = date('Y-m-d', strtotime($req->endDate));
            //dd($fromDate,$toDate);

            $query = 'leaves.*, (select SUM(no_of_days) from leave_entitlements le where le.user_id = leaves.user_id and le.leave_type_id = leaves.leave_type_id) as no_of_days 
        , (select SUM(days_used) from leave_entitlements le where le.user_id = leaves.user_id and le.leave_type_id = leaves.leave_type_id) as days_used';
            if ($user->role_id == 1) {

                $leave = Leave::when(!empty($req->startDate) && !empty($req->endDate), function ($query) use ($fromDate, $toDate) {
                    return $query->whereBetween('fromDate', [$fromDate, $toDate]);
                })
                    ->when(!empty($req->startDate) && empty($req->endDate), function ($query) use ($fromDate) {
                        return $query->where('fromDate', $fromDate);
                    })
                    ->when(!empty($req->type), function ($query) use ($req) {
                        return $query->where('leave_type_id', $req->type);
                    })
                    ->when(!empty($req->empId), function ($query) use ($req) {
                        return $query->where('user_id', $req->empId);
                    })
                    ->when(!empty($req->statusSelected) && ! ($req->statusSelected == 'All'), function ($query) use ($req) {
                        return $query->where('status', $req->statusSelected);
                    })->with('leaveType')->selectRaw($query)->orderBy('created_at', 'DESC')->get();
                    

                // dd($leave);
            } elseif ($user->role_id == 2) {
                // dd($req->statusIds);
                $sites = SiteAssign::where('user_id', $user->id)->first();
                // dd($req->statusIds);
                $siteArray = json_decode($sites['site_id'], true);
                // dd($siteArray);
                $leave = Leave::when(!empty($req->startDate) && !empty($req->endDate), function ($query) use ($fromDate, $toDate) {
                    return $query->whereBetween('fromDate', [$fromDate, $toDate]);
                })
                    ->when(!empty($req->startDate) && empty($req->endDate), function ($query) use ($fromDate) {
                        return $query->where('fromDate', $fromDate);
                    })
                    ->when(!empty($req->type), function ($query) use ($req) {
                        return $query->where('leave_type_id', $req->type);
                    })
                    ->when(!empty($req->empId), function ($query) use ($req) {
                        return $query->where('user_id', $req->empId);
                    })
                    ->when(!empty($req->statusIds), function ($query) use ($req) {
                        return $query->whereIn('status', $req->statusIds);
                    })
                    ->with('leaveType')->selectRaw($query)->whereIn('site_id', $siteArray)->where('role_id', 3)->get();

                // dd($leave);
            }
        } elseif ($flag == 'myLeave') {
            // dd($req , $flag);
            $user = session('user');
            $fromDate = date('Y-m-d', strtotime($req->startDate));

            $toDate = date('Y-m-d', strtotime($req->endDate));
            $query = 'leaves.*, (select SUM(no_of_days) from leave_entitlements le where le.user_id = leaves.user_id and le.leave_type_id = leaves.leave_type_id) as no_of_days 
            , (select SUM(days_used) from leave_entitlements le where le.user_id = leaves.user_id and le.leave_type_id = leaves.leave_type_id) as days_used';
            //  dd( $query );

            if ($user->role_id == 1) {

                $leave = Leave::when(!empty($req->startDate) && !empty($req->endDate), function ($query) use ($fromDate, $toDate) {
                    return $query->whereBetween('fromDate', [$fromDate, $toDate]);
                })
                    ->when(!empty($req->startDate) && empty($req->endDate), function ($query) use ($fromDate) {
                        return $query->where('fromDate', $fromDate);
                    })
                    ->when(!empty($req->type), function ($query) use ($req) {
                        return $query->where('leave_type_id', $req->type);
                    })
                    ->when(!empty($req->statusIds), function ($query) use ($req) {
                        return $query->whereIn('status', $req->statusIds);
                    })->with('leaveType')->selectRaw($query)->where('user_id', $user->id)->get();
            } elseif ($user->role_id == 2) {
                $leave = Leave::when(!empty($req->startDate) && !empty($req->endDate), function ($query) use ($fromDate, $toDate) {
                    return $query->whereBetween('fromDate', [$fromDate, $toDate]);
                })
                    ->when(!empty($req->startDate) && empty($req->endDate), function ($query) use ($fromDate) {
                        return $query->where('fromDate', $fromDate);
                    })
                    ->when(!empty($req->type), function ($query) use ($req) {
                        return $query->where('leave_type_id', $req->type);
                    })
                    ->when(!empty($req->statusIds), function ($query) use ($req) {
                        return $query->whereIn('status', $req->statusIds);
                    })->with('leaveType')->selectRaw($query)->where('role_id', 2)->get();
            }


            // dd($leave);
        }

        //dd($leave);
        return response()->json($leave);
        //return $leave;
    }

    public function addEntitlements()
    {
        $user = session('user');
        //  dd($user);
        $leave = LeaveType::where('company_id', $user->company_id)->get();

        $states = States::all();


        $All_Client = ClientDetails::where('company_id', $user->company_id)->get();

        // dd($client);
        // dd('add entitlement' , $leave  , $All_Client);
        return view('leaves.addEntitlements')->with('leave', $leave)->with('states', $states)->with('client', $All_Client);
    }


    public function city(Request $req)
    {
        //dd($req);
        $city = Cities::where('state_code', $req->state_code)->get();
        //dd($city);
        return $city;
    }

    public function editLeaveEntitlement($id)
    {
        //  dd($id);
        $user = session('user');
        $leave = LeaveEntitlement::find($id);
        //dd($leave);
        $startDate = $leave->from_date;
        $endDate = $leave->to_date;
        $startYear =  date('Y-m-d', strtotime($startDate));
        //dd($startYear);
        $endYear =  date('Y-m-d', strtotime($endDate));
        //dd($endYear);
        $emp = Users::where('id', $leave->user_id)->first();
        $leaveType = LeaveType::where('id', $leave->leave_type_id)->where('company_id', $user->company_id)->first();
        // dd($leaveType);
        return view('leaves.editLeaveEntitlement')->with('leave', $leave)->with('startYear', $startYear)->with('endYear', $endYear)
            ->with('leaveType', $leaveType)->with('emp', $emp);
    }

    public function actionEditLeaveEntitlement(Request $req)
    {

        $user = session('user');
        $dates = explode(' - ', $req->period);
        $leaveType = LeaveType::where('id', $req->leave_type_id)->where('company_id', $user->company_id)->first();
        //dd($leaveType);
        $leave = LeaveEntitlement::find($req->entitlementId);

        $leave->id = $req->entitlementId;
        $leave->user_id = $req->empId;
        $leave->leave_type_id = $req->leave_type_id;
        // $leave->from_date = $dates[0];
        // $leave->to_date = $dates[1];
        $leave->company_id = $user->company_id;
        $leave->credited_date = date('Y-m-d');
        $leave->no_of_days = $req->no_of_days;
        $leave->entitlement_type = $leaveType->exclude_in_reports_if_no_entitlement;
        $leave->created_by_id = $user->id;
        $leave->update();

        $adjustment = new LeaveAdjustment();
        $adjustment->user_id = $req->empId;
        $adjustment->leave_type_id = $req->leave_type_id;
        // $leave->from_date = $dates[0];
        // $leave->to_date = $dates[1];
        $leave->company_id = $user->company_id;
        $adjustment->credited_date = date('Y-m-d');
        $adjustment->no_of_days = $req->no_of_days;
        $adjustment->created_by_id = $user->id;
        $adjustment->created_by_name = $user->name;
        $adjustment->save();

        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Update Leave Entitlement",
            'message' => "Leave entitlement updated by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->route('empEntitlements')->with('success', '"Leave entitlement successfully updated."');
    }

    public function site(Request $req)
    {
        //dd($req);
        $site = SiteDetails::where('client_id', $req->clientId)->get();
        //dd($site);
        return $site;
    }

    public function employees(Request $req)
    {
        $user = session('user');
        if ($user->role_id == 1) {
            $employee = SiteAssign::where('site_id', $req->siteId)->where('company_id', $user->company_id)->get();
        } elseif ($user->role_id == 2) {
            $employee = SiteAssign::where('site_id', $req->siteId)->where('company_id', $user->company_id)->where('role_id', 3)->get();
        }

        // dd($employee);
        return $employee;
    }

    public function myEntitlements()
    {
        $user = session('user');
        //dd($user);
        $myEntitlement = LeaveEntitlement::where('user_id', $user->id)->get();
        //dd($myEntitlement);
        $leave = LeaveType::where('company_id', $user->company_id)->get();
        return view('leaves.myEntitlements')->with('leave', $leave)->with('myEntitlement', $myEntitlement);
    }

    public function deleteAllMyEntitlement(Request $req)
    {
        //dd($req);
        LeaveEntitlement::whereIn('id', $req->deleteIds)->delete();
        return 'success';
    }

    public function myEntitlementSearch(Request $req)
    {
        // dd($req);
        $dates = explode(' - ', $req->period);
        $dates = [];

        foreach ($dates as $d) {
            //dd($dates);
            $dates[] = $d->format("Y-m-d");
        }
        // dd($dates);
        return $dates;
    }

    public function onChangeEmp(Request $req)
    {
        // dd($req->all);
        $user = session('user');
        $leaveEntitlements = LeaveEntitlement::where('user_id', $req->id)->where('company_id', $user->company_id)->with('leaveType')->get();

        return $leaveEntitlements;
    }

    public function deleteMyEntitlement($id)
    {
        $l =  LeaveEntitlement::where('id', $id)->delete();
        $user = session('user');
        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Delete my leave Entitlement",
            'message' => "Leave entitlement deleted by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->back()->with('success', '"My entitlement successfully deleted.""');
    }

    public function employeeHint(Request $req)
    {
        //dd($req);
        $user = session('user');
        $sites = SiteAssign::where('user_id', $user->id)->first();
        //$results = Subreddit::select('id', 'name')->where('name', 'LIKE', '%' . $query . '%')->get();
        $query = $req->get('query');
        if ($user->role_id == 1) {

            // dd('when 1');
            $filterResult = Users::where('name', 'LIKE', '%' . $query . '%')->where('company_id', $user->company_id)->where('role_id', '!=', 1)->limit(10)->get();
        } elseif ($user->role_id == 2) {
            dd('when 2');
            if ($sites) {


                $siteArray = json_decode($sites['site_id'], true);
                //dd($siteArray);
                $filterResult = SiteAssign::whereIn('site_id', $siteArray)->where('role_id', 3)->where('user_name', 'LIKE', '%' . $query . '%')
                    ->select('*', 'user_name as name', 'user_id as id')->limit(10)->get();
                // dd($filterResult);
            }
        }

        // dd($filterResult);
        return response()->json($filterResult);
    }

    public function commentAction(Request $req)
    {
        // dd($req->all());
        $leaves = Leave::find($req->id);
        // dd($leaves);
        $leaves->reason = $req->comment;
        $leaves->save();

        return 'success';
    }

    public function deleteEmpLeaveEntitlement($id)
    {
        // dd($id);
        $user = session('user');
        LeaveEntitlement::where('id', $id)->delete();

        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Delete Leave Employee Entitlement",
            'message' => "Leave employee entitlement deleted by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->route('empEntitlements')->with('success', '"Leave employee entitlement successfully deleted."');
    }
    public function deleteAllEntitlement(Request $req)
    {
        //dd($req);
        LeaveEntitlement::whereIn('id', $req->deleteIds)->delete();
        return 'success';
    }
    public function addEntitlementsAction(Request $req)
    {
        // dd($req->all() , "all request add entitlement");
        $user = session('user');
        if ($req->emp == 2) {
            if ($req->type == 1) {

                if ($req->state != '' && $req->state != 'all') {
                    if ($req->city != '' && $req->city != 'all') {

                        $city = Cities::where('id', $req->city)->first();

                        $siteDetails = SiteDetails::where('city', $city->name)->where('company_id', $user->company_id)->get();

                        //dd($siteDetails);
                    } else {
                        $state = States::where('code', $req->state)->first();
                        //dd($state);
                        $siteDetails = SiteDetails::where('state', $state->name)->where('company_id', $user->company_id)->get();
                        //dd($siteDetails);
                    }
                    foreach ($siteDetails as $key => $value) {
                        if ($user->role_id == 1) {

                            $siteAssign = SiteAssign::where('site_id', $value->id)->where('company_id', $user->company_id)
                                ->pluck('user_id');
                        } elseif ($user->role_id == 2) {

                            $siteAssign = SiteAssign::where('site_id', $value->id)->where('company_id', $user->company_id)
                                ->where('role_id', 3)->pluck('user_id');
                            //dd($siteAssign);
                        }
                        //dd($siteAssign);
                        foreach ($siteAssign as $item) {
                            $data = $req->all();
                            // dd($item);
                            $data['user_id'] = $item;

                            unset($data['_token']);
                            unset($data['emp']);
                            unset($data['employee']);
                            unset($data['type']);
                            unset($data['state']);
                            unset($data['city']);

                            $user = session('user');
                            $leaveType = LeaveType::where('id', $req->leave_type_id)->where('company_id', $user->company_id)->first();

                            $leavePeriod  =  LeavePeriodHistory::where('company_id', $user->company_id)->first();

                            $data['from_date'] = date('Y-m-d', strtotime(date('Y') . '-' . $leavePeriod->leave_period_start_month . '-' . $leavePeriod->leave_period_start_day));

                            $startDate = date('Y-m-d', strtotime(date('Y') . '-' . $leavePeriod->leave_period_start_month . '-' . $leavePeriod->leave_period_start_day));

                            $endDate = date('Y-m-d', strtotime($startDate . ' +  364 days'));

                            $data['entitlement_type'] = $leaveType->exclude_in_reports_if_no_entitlement;

                            $data['to_date'] = $endDate;
                            $data['credited_date'] = date('Y-m-d');
                            $data['created_by_id'] = $user->id;
                            $data['company_id'] = $user->company_id;
                            //dd($data);
                            //$users = Users::where('company_id', $user->company_id)->get();
                            //dd($users);
                            $entitlement = LeaveEntitlement::where('user_id', $item)->where('leave_type_id', $leaveType->id)->first();

                            if ($entitlement != '') {

                                LeaveEntitlement::where('user_id', $item)->where('leave_type_id', $leaveType->id)->update($data);
                                unset($data['entitlement_type']);
                                $data['created_by_name'] = $user->name;
                                $data['company_id'] = $user->company_id;
                                LeaveAdjustment::where('user_id', $item)->where('leave_type_id', $leaveType->id)->update($data);
                            } else {
                                //dd($req->leave_type_id);
                                $data['leave_type_id'] = $req->leave_type_id;
                                LeaveEntitlement::create($data);
                                unset($data['entitlement_type']);
                                $data['created_by_name'] = $user->name;
                                $data['company_id'] = $user->company_id;
                                LeaveAdjustment::create($data);
                            }
                        }
                    }
                } else {
                    $siteDetails = SiteDetails::where('company_id', $user->company_id)->get();
                    // dd($siteDetails);
                    foreach ($siteDetails as $key => $value) {
                        //dd($user);
                        if ($user->role_id == 1) {
                            $siteAssign = SiteAssign::where('site_id', $value->id)->where('company_id', $user->company_id)
                                ->pluck('user_id');
                        } elseif ($user->role_id == 2) {
                            $siteAssign = SiteAssign::where('site_id', $value->id)->where('company_id', $user->company_id)
                                ->where('role_id', 3)->pluck('user_id');
                            //dd($siteAssign);
                        }

                        foreach ($siteAssign as $item) {
                            //dd($item);
                            // dd($item);
                            $data = $req->all();
                            $data['user_id'] = $item;

                            unset($data['_token']);
                            unset($data['emp']);
                            unset($data['employee']);
                            // dd($data);
                            $user = session('user');
                            $leaveType = LeaveType::where('id', $req->leave_type_id)->where('company_id', $user->company_id)->first();
                            $leavePeriod  =  LeavePeriodHistory::where('company_id', $user->company_id)->first();

                            $data['from_date'] = date('Y-m-d', strtotime(date('Y') . '-' . $leavePeriod->leave_period_start_month . '-' . $leavePeriod->leave_period_start_day));

                            $startDate = date('Y-m-d', strtotime(date('Y') . '-' . $leavePeriod->leave_period_start_month . '-' . $leavePeriod->leave_period_start_day));

                            $endDate = date('Y-m-d', strtotime($startDate . ' +  364 days'));

                            $data['entitlement_type'] = $leaveType->exclude_in_reports_if_no_entitlement;
                            $data['to_date'] = $endDate;
                            $data['company_id'] = $user->company_id;
                            $data['credited_date'] = date('Y-m-d');
                            $data['created_by_id'] = $user->id;

                            //dd($users);
                            $entitlement = LeaveEntitlement::where('user_id', $item)->where('company_id', $user->company_id)->where('leave_type_id', $leaveType->id)
                                ->first();

                            //dd($entitlement);
                            $data['created_by_name'] = $user->name;
                            if ($entitlement != '') {
                                //  dd($entitlement);
                                unset($data['type']);
                                unset($data['state']);
                                unset($data['created_by_name']);
                                LeaveEntitlement::where('user_id', $item)->where('company_id', $user->company_id)->where('leave_type_id', $leaveType->id)->update($data);

                                unset($data['entitlement_type']);
                                $data['company_id'] = $user->company_id;
                                $data['created_by_name'] = $user->name;
                                LeaveAdjustment::where('user_id', $item)->where('leave_type_id', $leaveType->id)->update($data);
                            } else {

                                $data['leave_type_id'] = $req->leave_type_id;
                                // dd($data);
                                LeaveEntitlement::create($data);
                                unset($data['entitlement_type']);
                                $data['company_id'] = $user->company_id;

                                $data['created_by_name'] = $user->name;
                                LeaveAdjustment::create($data);
                            }
                        }
                    }
                    // dd('hii');
                }
            } else {

                if ($req->client != '' && $req->client != 'all') {
                    if ($req->site != '' && $req->site != 'all') {

                        if ($req->employees != '' && $req->employees != 'all') {

                            $data = $req->all();

                            unset($data['_token']);
                            unset($data['emp']);
                            unset($data['employee']);
                            $data['company_id'] = $user->company_id;
                            $data['user_id'] = $req->employees;
                            $user = session('user');
                            $leaveType = LeaveType::where('id', $req->leave_type_id)->where('company_id', $user->company_id)->first();
                            $leavePeriod  =  LeavePeriodHistory::where('company_id', $user->company_id)->first();

                            $data['from_date'] = date('Y-m-d', strtotime(date('Y') . '-' . $leavePeriod->leave_period_start_month . '-' . $leavePeriod->leave_period_start_day));

                            $startDate = date('Y-m-d', strtotime(date('Y') . '-' . $leavePeriod->leave_period_start_month . '-' . $leavePeriod->leave_period_start_day));

                            $endDate = date('Y-m-d', strtotime($startDate . ' +  364 days'));

                            $data['entitlement_type'] = $leaveType->exclude_in_reports_if_no_entitlement;

                            $data['to_date'] = $endDate;
                            $data['credited_date'] = date('Y-m-d');
                            $data['created_by_id'] = $user->id;
                            $data['created_by_name'] = $user->name;
                            //dd($data);
                            if ($user->role_id == 1) {

                                $users = Users::where('company_id', $user->company_id)->get();
                            } elseif ($user->role_id == 2) {

                                $users = Users::where('company_id', $user->company_id)->where('role_id', 3)->get();
                            }
                            // dd($users);
                            foreach ($users as $i) {
                                //dd($i->id);
                                $entitlement = LeaveEntitlement::where('user_id', $i->id)->where('company_id', $user->company_id)->where('leave_type_id', $leaveType->id)->first();
                                //dd($entitlement);
                                $data['user_id'] = $i->id;
                                $data['company_id'] = $user->company_id;
                                $data['created_by_name'] = $user->name;


                                if ($entitlement != '') {
                                    unset($data['type']);
                                    unset($data['client']);
                                    unset($data['site']);
                                    unset($data['employees']);
                                    unset($data['created_by_name']);
                                    LeaveEntitlement::where('user_id', $i->id)->where('leave_type_id', $leaveType->id)->update($data);
                                    unset($data['entitlement_type']);
                                    $data['created_by_name'] = $user->name;
                                    LeaveAdjustment::where('user_id', $i->id)->where('leave_type_id', $leaveType->id)->update($data);
                                } else {

                                    $data['leave_type_id'] = $req->leave_type_id;
                                    //dd($data);
                                    LeaveEntitlement::create($data);
                                    unset($data['entitlement_type']);
                                    $data['created_by_name'] = $user->name;
                                    LeaveAdjustment::create($data);
                                }
                            }
                        } else {
                            $siteAssign = SiteAssign::where('site_id', $req->site)->pluck('user_id');
                            foreach ($siteAssign as $item) {
                                $data = $req->all();

                                unset($data['_token']);
                                unset($data['emp']);
                                unset($data['employee']);
                                $data['company_id'] = $user->company_id;
                                $data['user_id'] = $item;
                                $user = session('user');
                                $leaveType = LeaveType::where('id', $req->leave_type_id)->where('company_id', $user->company_id)->first();
                                $leavePeriod  =  LeavePeriodHistory::where('company_id', $user->company_id)->first();

                                $data['from_date'] = date('Y-m-d', strtotime(date('Y') . '-' . $leavePeriod->leave_period_start_month . '-' . $leavePeriod->leave_period_start_day));

                                $startDate = date('Y-m-d', strtotime(date('Y') . '-' . $leavePeriod->leave_period_start_month . '-' . $leavePeriod->leave_period_start_day));

                                $endDate = date('Y-m-d', strtotime($startDate . ' +  364 days'));

                                $data['entitlement_type'] = $leaveType->exclude_in_reports_if_no_entitlement;
                                $data['to_date'] = $endDate;
                                $data['company_id'] = $user->company_id;
                                $data['credited_date'] = date('Y-m-d');
                                $data['created_by_id'] = $user->id;

                                // $users = Users::where('company_id', $user->company_id)->get();
                                //dd($users);
                                $entitlement = LeaveEntitlement::where('user_id', $item)->where('leave_type_id', $leaveType->id)->first();

                                $data['created_by_name'] = $user->name;
                                if ($entitlement != '') {

                                    LeaveEntitlement::where('user_id', $item)->where('leave_type_id', $leaveType->id)->update($data);
                                    unset($data['entitlement_type']);
                                    $data['company_id'] = $user->company_id;
                                    $data['created_by_name'] = $user->name;

                                    LeaveAdjustment::where('user_id', $item)->where('leave_type_id', $leaveType->id)->update($data);
                                } else {

                                    //dd($req->leave_type_id);
                                    $data['leave_type_id'] = $req->leave_type_id;
                                    // dd($data);
                                    LeaveEntitlement::create($data);
                                    unset($data['entitlement_type']);
                                    $data['company_id'] = $user->company_id;
                                    $data['created_by_name'] = $user->name;

                                    LeaveAdjustment::create($data);
                                }
                            }
                        }
                    } else {
                        $siteAssign = SiteAssign::where('client_id', $req->client)->pluck('user_id');
                        // dd($siteAssign);
                        foreach ($siteAssign as $item) {
                            $data = $req->all();

                            unset($data['_token']);
                            unset($data['emp']);
                            unset($data['employee']);
                            $data['company_id'] = $user->company_id;
                            $data['user_id'] = $item;
                            $user = session('user');
                            $leaveType = LeaveType::where('id', $req->leave_type_id)->where('company_id', $user->company_id)->first();
                            $leavePeriod  =  LeavePeriodHistory::where('company_id', $user->company_id)->first();

                            $data['from_date'] = date('Y-m-d', strtotime(date('Y') . '-' . $leavePeriod->leave_period_start_month . '-' . $leavePeriod->leave_period_start_day));

                            $startDate = date('Y-m-d', strtotime(date('Y') . '-' . $leavePeriod->leave_period_start_month . '-' . $leavePeriod->leave_period_start_day));

                            $endDate = date('Y-m-d', strtotime($startDate . ' +  364 days'));
                            $data['entitlement_type'] = $leaveType->exclude_in_reports_if_no_entitlement;

                            $data['to_date'] = $endDate;
                            $data['credited_date'] = date('Y-m-d');
                            $data['created_by_id'] = $user->id;

                            LeaveEntitlement::create($data);
                        }
                    }
                } else {
                    $siteDetails = SiteDetails::all();
                    foreach ($siteDetails as $key => $value) {

                        $siteAssign = SiteAssign::where('site_id', $value->id)->pluck('user_id');
                        //dd($siteAssign);
                        foreach ($siteAssign as $item) {

                            $data = $req->all();
                            unset($data['_token']);
                            unset($data['emp']);
                            unset($data['employee']);
                            $data['user_id'] = $item;
                            $user = session('user');
                            $leaveType = LeaveType::where('id', $req->leave_type_id)->where('company_id', $user->company_id)->first();
                            $leavePeriod  =  LeavePeriodHistory::where('company_id', $user->company_id)->first();

                            $data['from_date'] = date('Y-m-d', strtotime(date('Y') . '-' . $leavePeriod->leave_period_start_month . '-' . $leavePeriod->leave_period_start_day));

                            $startDate = date('Y-m-d', strtotime(date('Y') . '-' . $leavePeriod->leave_period_start_month . '-' . $leavePeriod->leave_period_start_day));

                            $endDate = date('Y-m-d', strtotime($startDate . ' +  364 days'));

                            $data['entitlement_type'] = $leaveType->exclude_in_reports_if_no_entitlement;

                            $data['to_date'] = $endDate;
                            $data['company_id'] = $user->company_id;
                            $data['credited_date'] = date('Y-m-d');
                            $data['created_by_id'] = $user->id;

                            LeaveEntitlement::create($data);
                        }
                    }
                }
            }
        } elseif ($req->emp ==  3) {
            // dd($req);
            $leaveType = LeaveType::where('id', $req->leave_type_id)->where('company_id', $user->company_id)->first();

            $leavePeriod  =  LeavePeriodHistory::where('company_id', $user->company_id)->first();

            $startDate = date('Y-m-d', strtotime(date('Y') . '-' . $leavePeriod->leave_period_start_month . '-' . $leavePeriod->leave_period_start_day));

            $endDate = date('Y-m-d', strtotime($startDate . ' +  364 days'));

            if ($user->role_id == 1) {
                $users = Users::where('company_id', $user->company_id)->get();

                //dd($users);
            } elseif ($user->role_id == 2) {
                $users = Users::where('company_id', $user->company_id)->where('role_id', 3)->get();
                //dd($users);
            }

            //dd($users);
            foreach ($users as $i) {
                $data = $req->all();
                unset($data['_token']);
                unset($data['emp']);
                unset($data['employee']);
                $data['from_date'] = date('Y-m-d', strtotime(date('Y') . '-' . $leavePeriod->leave_period_start_month . '-' . $leavePeriod->leave_period_start_day));

                $data['to_date'] = $endDate;
                $data['entitlement_type'] = $leaveType->exclude_in_reports_if_no_entitlement;
                $data['leave_type_id'] = $req->leave_type_id;
                $data['credited_date'] = date('Y-m-d');
                $data['created_by_id'] = $user->id;
                $data['user_id'] = $i->id;
                $data['company_id'] = $user->company_id;
                //dd($leaveType);
                $entitlement = LeaveEntitlement::where('user_id', $i->id)->where('leave_type_id', $leaveType->id)->first();
                // dd($entitlement);
                if ($entitlement != '') {
                    LeaveEntitlement::where('user_id', $i->id)->where('leave_type_id', $leaveType->id)->update($data);
                    unset($data['entitlement_type']);
                    $data['created_by_name'] = $user->name;
                    LeaveAdjustment::where('user_id', $i->id)->where('leave_type_id', $leaveType->id)->update($data);
                } else {
                    //dd($req->leave_type_id);
                    LeaveEntitlement::create($data);
                    unset($data['entitlement_type']);

                    $data['created_by_name'] = $user->name;
                    LeaveAdjustment::create($data);
                }
            }
        } else {

            //when emp is 1 --> single employee
            // dd($req);
            $data = $req->all();
            // dd($data);
            $user = session('user');
            //  dd($req->leave_type_id);
            $leaveType = LeaveType::where('id', $req->leave_type_id)->where('company_id', $user->company_id)->first();

            $leavePeriod  =  LeavePeriodHistory::where('company_id', $user->company_id)->first();

            $dates = explode(' - ', $req->period);
            $data['leave_type_id'] = $req->leave_type_id;
            $data['entitlement_type'] = $leaveType->exclude_in_reports_if_no_entitlement;

            $data['from_date'] = date('Y-m-d', strtotime(date('Y') . '-' . $leavePeriod->leave_period_start_month . '-' . $leavePeriod->leave_period_start_day));

            $startDate = date('Y-m-d', strtotime(date('Y') . '-' . $leavePeriod->leave_period_start_month . '-' . $leavePeriod->leave_period_start_day));

            $endDate = date('Y-m-d', strtotime($startDate . ' +  364 days'));
            //dd($endDate);
            $data['to_date'] = $endDate;
            $data['credited_date'] = date('Y-m-d');
            $data['created_by_id'] = $user->id;
            $data['company_id'] = $user->company_id;
            $data['created_by_name'] = $user->name;


            // $user = Users::where('company_id' , $user->company_id)->first();
            // dd($user , "user");
            // dd($data , $leavePeriod,   "Data ");
            $existingEntitlement = LeaveEntitlement::where('user_id', $req->user_id)
                ->where('leave_type_id', $req->leave_type_id)
                ->where('company_id', $user->company_id)
                ->first();

            // dd($existingEntitlement, $req->user_id, $req->leave_type_id, $user->company_id,   "exist");
            if ($existingEntitlement) {
                $existingEntitlement->no_of_days = $req->no_of_days;
                $existingEntitlement->save();
            } else {
                LeaveEntitlement::create($data);
                LeaveAdjustment::create($data);
            }


            //dd($data);
        }

        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Create Leave Entitlement",
            'message' => "Leave entitlement created by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
            'date_time' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->route('addEntitlements')->with('success', '"Entitlement successfully added."');
    }

    public function myLeave()
    {
        $user = session('user');
        $leaveStatus = LeaveStatus::all();
        $leaveType = LeaveType::where('company_id', $user->company_id)->get();
        $query = 'leaves.*, (select SUM(no_of_days) from leave_entitlements le where le.user_id = leaves.user_id and le.leave_type_id = leaves.leave_type_id) as no_of_days 
        , (select SUM(days_used) from leave_entitlements le where le.user_id = leaves.user_id and le.leave_type_id = leaves.leave_type_id) as days_used';

        $leave = Leave::where('company_id', $user->company_id)
            ->where('user_id', $user->id)
            ->selectRaw($query)->get();

        // ->leftjoin('leave_entitlements', 'leaves.user_id', '=', 'user_id')
        // ->select('leaves.*', 'leave_entitlements.no_of_days as no_of_days', 'leave_entitlements.days_used as days_used')
        // ->distinct()
        // ->selectRaw('leaves.*', DB::raw())

        // dd($leave , "leave");

        return view('leaves.myLeave')->with('leaveStatus', $leaveStatus)->with('leaveType', $leaveType)->with('leave', $leave);
    }


    public function myLeaveAction(Request $req)
    {
        $type = $req->type;
        //$leaveType = LeaveType::where('id' , $type)->get();
        //$leaveName=  $leaveType->name;
        return $type;
        // dd($type);
    }

    public function leaveEntitlements()
    {
        $user = session('user');
        $leave = LeaveType::where('company_id', $user->company_id)->get();
        return view('leaves.leaveEntitlements')->with('leave', $leave);
    }
    public function myLeaveEntitlements()
    {
        return view('leaves.myLeaveEntitlements');
    }

    public function holidayList()
    {
        $user = session('user');
        $holidayList = LeaveHoliday::where('company_id', $user->company_id)->get();
        return view('leaves.holidayList')->with('holidayList', $holidayList);
    }

    public function editHoliday($dayId)
    {
        $holiday = LeaveHoliday::find($dayId);
        return view('leaves.editHoliday')->with('holiday', $holiday);
    }
    // public function holidaySearchList(Request $req)
    // {
    //     dd($req);
    // }
    public function holidaySearch(Request $req)
    {
        //dd($req);
        $user = session('user');
        $startDate = date('Y-m-d', strtotime($req->fromDate));
        $endDate = date('Y-m-d', strtotime($req->toDate));
        $holiday = LeaveHoliday::whereBetween('date', [$startDate, $endDate])->where('company_id', $user->company_id)->get();

        // $dateRange =  CarbonPeriod::create($startDate, $endDate);
        // $date =  $dateRange->toArray();

        // $dates = [];
        // foreach ($date as $d) {

        //     $dates[] = $d->format("Y-m-d");
        // }
        //dd($dates);
        return response()->json($holiday);
    }
    public function workWeek()
    {
        return view('leaves.workWeek');
    }

    public function leaveTypes()
    {
        $user = session('user');
        $leave = LeaveType::where('company_id', $user->company_id)->get();
        //dd($leave);
        return view('leaves.leaveTypes')->with('leave', $leave);
    }

    public function editLeave($leaveId)
    {
        $leave = LeaveType::find($leaveId);
        //dd($leave);
        return view('leaves.editLeave')->with('leave', $leave);
    }
    public function editLeaveType($leaveId)
    {
        //dd($leaveId);
        $leave = LeaveType::find($leaveId);

        // dd($leave);
        //dd($leave);
        //$year = date('Y');
        //$date = Carbon::now()->month($leave->accural_month)->daysInMonth;
        //dd($date);
        return view('leaves.editLeaveType')->with('leave', $leave);
    }

    public function actionEditLeave(Request $req)
    {
        //dd($req);
        $user = session('user');
        $leave = LeaveType::find($req->leaveId);
        $leave->id = $req->leaveId;
        $leave->name = $req->name;
        $leave->deleted = $req->deleted;
        $leave->company_id = $user->company_id;
        $leave->exclude_in_reports_if_no_entitlement = $req->situation;
        $leave->operational_country_id = $req->country_id;
        $leave->update();
        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Updated Leave Type",
            'message' => "Leave type updated by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->route('leaveTypes')->with('success', '"Leave type successfully updated."');
    }

    public function createLeaveType(Request $req)
    {
        $user = session('user');
        $leave = new LeaveType();
        $leave->name = $req->name;
        $leave->deleted = 0;
        $leave->company_id = $user->company_id;
        $leave->exclude_in_reports_if_no_entitlement = $req->situation;
        $leave->save();
        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Created Leave Type",
            'message' => "Leave type created by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->route('leaveTypes')->with('success', '"Leave type successfully added."');
    }

    public function onChangeLeaveType(Request $req)
    {
        //   dd($req);
        $entitlement = LeaveEntitlement::where('user_id', $req->empId)->where('leave_type_id', $req->leaveId)->first();

        return  $entitlement;
    }

    public function deleteLeaveType($id)
    {
        $user = session('user');
        //dd($user->company_id);
        LeaveType::where('id', $id)->where('company_id', $user->company_id)->delete();
        LeaveEntitlement::where('leave_type_id', $id)->delete();

        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Deleted Leave Type",
            'message' => "Leave type deleted by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->route('leaveTypes')->with('success', '"Leave type successfully deleted."');
    }

    public function addLeave()
    {
        return view('leaves.addLeave');
    }

    public function getMonth(Request $req)
    {

        $day =  Carbon::now()->month($req->month)->daysInMonth;
        //dd($day);
        return $day;
    }

    public function createLeaveAction(Request $req)
    {
        //dd($req);
        $user = session('user');
        $data = $req->all();
        if ($req->accural == 'on') {
            $data['accural'] = 1;
        }
        if ($req->reset == 'on') {
            $data['reset'] = 1;
        }
        $data['company_id'] = $user->company_id;
        if ($req->accural_date == null) {
            $data['accural_date'] = 1;
        }
        // dd($data);
        LeaveType::create($data);

        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Created Leave Type",
            'message' => "Leave type created by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->route('advanceLeaveCreate')->with('success', '"Leave type successfully addded."');
    }

    public function updateLeaveAction(Request $req)
    {
        // dd($req);
        $user = session('user');
        $data = $req->all();
        $data['company_id'] = $user->company_id;
        $data['accural'] = $req->has('accural');
        $data['reset'] = $req->has('reset');
        // dd($data);
        // dd($accural);
        // if ($req->accural) {
        //     $data['accural'] = 1;
        // }
        // if ($req->reset == 'on') {
        //     $data['reset'] = 1;
        // }
        unset($data['_token']);
        LeaveType::where('id', $req->id)->update($data);


        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Updated leave ",
            'message' => "Leave updated by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->route('advanceLeaveCreate')->with('success', '"Leave successfully updated."');
    }

    public function leavePeriod()
    {
        $startYear =   Carbon::now()->startOfYear();
        $endYear =  Carbon::now()->endOfYear();
        $fromYear = $startYear->toDateString();
        $toYear = $endYear->toDateString();
        //dd($fromYear);
        return view('leaves.leavePeriod')->with('fromYear', $fromYear)->with('toYear', $toYear);
    }

    public function addHoliday()
    {
        return view('leaves.addHoliday');
    }

    public function addHolidayAction(Request $req)
    {
        //dd($req->day);
        $user = session('user');
        $date = date('Y-m-d', strtotime($req->date));
        $leaveHoliday = new LeaveHoliday();
        $leaveHoliday->name = $req->name;
        $leaveHoliday->date = $date;
        $leaveHoliday->company_id = $user->company_id;
        $leaveHoliday->recurring = $req->annual;
        $leaveHoliday->length = $req->day;
        $leaveHoliday->save();
        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "created Holiday",
            'message' => "Holiday created by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->route('holidayList')->with('success', '"Holiday successfully added."');
    }

    public function deleteMyLeave($id)
    {
        Leave::where('id', $id)->delete();
        $user = session('user');

        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Deleted Leave",
            'message' => "Leave deleted by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->route('myLeave')->with('success', '"Leave successfully deleted."');
    }
    public function deleteHoliday($dayId)
    {
        // dd($dayId);
        LeaveHoliday::where('id', $dayId)->delete();
        $user = session('user');
        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Deleted Holiday",
            'message' => "Holiday deleted by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->route('holidayList')->with('success', '"Holiday successfully deleted."');
    }

    public function deleteHolidayLeave(Request $req)
    {
        //dd($dayId);
        LeaveHoliday::whereIn('id', $req->deleteIds)->delete();
        return 'success';
    }

    public function deleteLeave(Request $req)
    {
        //dd($req);
        $user = session('user');
        LeaveType::whereIn('id', $req->deleteIds)->where('company_id', $user->company_id)->delete();
        LeaveEntitlement::whereIn('leave_type_id', $req->deleteIds)->delete();
        return 'success';
    }

    public function editHolidayAction(Request $req)
    {
        //dd($req);
        $user = session('user');
        $date = date('Y-m-d', strtotime($req->date));
        $holiday = LeaveHoliday::find($req->dayId);
        $holiday->id = $req->dayId;
        $holiday->name = $req->name;
        $holiday->date = $date;
        $holiday->company_id = $user->company_id;
        $holiday->recurring = $req->annual;
        $holiday->length = $req->day;
        $holiday->update();

        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Updated Holiday",
            'message' => "Holiday updated by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);
        // dd($holiday);
        return redirect()->route('holidayList')->with('success', '"Holiday successfully updated."');
    }

    public function leaveDashboard()
    {
        return view('leaves.leaveDashboard');
    }

    public function calendarView()
    {
        $user = session('user');
        $data = SiteAssign::where('company_id', $user->company_id)->limit(10)->get();
        return view('leaves.calendarView', compact('data'));
    }

    public function leaveRequests()
    {
        return view('leaves.leaveRequests');
    }

    public function leaveRequestDetails($id)
    {
        // dump($id);
        $leave = Leave::where('id', $id)->first();

        return view('leaves.leaveRequestDetails')->with('leave', $leave);
    }

    public function status($id, $flag, $leave_type_id)
    {
        $leave = Leave::find($id);
        $leaveUser = LeaveEntitlement::find($leave->id);
        $LeaveEntitlement = LeaveEntitlement::where('user_id', $leave->user_id)->where('leave_type_id', $leave_type_id)->first();

        // dd($LeaveEntitlement ,$id, $flag, $leave_type_id , "entitlement ");

        // dd($leave, $id, $flag, $leaveUser, $leave_type_id, $LeaveEntitlement, "flag");
        //dd($id);
        $user = session('user');
        if ($flag == "reject") {

            $leave->status = 'Declined';
            if ($leave->duration == "Half Day") {
                // dd($LeaveEntitlement , "entitle ment");
                $LeaveEntitlement->days_used = $LeaveEntitlement->days_used - 0.5;
                // dd($LeaveEntitlement, $LeaveEntitlement->days_used - 0.5, "leave entitlement");
            } elseif ($leave->duration == "Full Day") {
                $LeaveEntitlement->days_used = $LeaveEntitlement->days_used - 1;
                // dd($LeaveEntitlement, $LeaveEntitlement->days_used - 0.5, "leave entitlement");
            }
        $LeaveEntitlement->save();

        } elseif ($flag == "approve") {
            $leave->status = 'Approved';
        } elseif ($flag == 'cancel') {
            $leave->status = 'Cancelled';
        } else {
        }


        // dd($leave, $id, $flag, $leaveUser, $leave_type_id, $LeaveEntitlement, "flag");

        $leave->update();
        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Updated Status",
            'message' => "Status updated by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->route('leaveList')->with('success', "Status successfully updated.");
        //return view('leaves.leaveDetails');
    }

    public function deleteMultiLeave(Request $req)
    {
        //dd($req->deleteIds);
        Leave::whereIn('id', $req->deleteIds)->delete();
        return 'success';
    }
    public function deleteMultiLeaveList(Request $req)
    {
        Leave::whereIn('id', $req->deleteIds)->delete();
        //dd($leave);
        return 'success';
    }

    public function holiday()
    {
        return view('leaves.holiday');
    }

    public function customizeBalance()
    {
        return view('leaves.customizeBalance');
    }
    public function customizeEdit()
    {
        return view('leaves.customizeEdit');
    }
}

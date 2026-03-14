<?php

namespace App\Http\Controllers;

use DateTime;
use DateTimeZone;
use App\Exports\IncidentExport;
use App\Exports\VisitorExport;
use App\Exports\TourExport;
use App\Exports\GuardMonthlyExport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Http\Request;
use App\Geofence;
use App\GetDays;
use App\IncidenceDetails;
use Maatwebsite\Excel\Excel;
use App\Exports\View;
use App\SiteAssign;
use App\GuardTour;
use App\SiteDetails;
use App\CompanyDetails;
use App\Attendance;
use App\GuardTourLog;
use App\Notifications;
use App\IncidenceComment;
use App\IncidenceChecklist;
use App\ActivityLog;
use App\Users;
use App\ClientVisit;
use App\PatrolSession;
use App\PatrolLog;
use App\ClientDetails;
use App\Models\TourDiary;
use App\FCMNotify;
use Illuminate\Support\Facades\Log;
use Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;

class IncidenceController extends Controller
{
    private $excel;
    private $generatedOn;

    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
        $this->generatedOn = now()->format('d F Y , h:i:a');
    }

    public function calculateWeekOffDates(array $weekOffDays, DateTime $fromDate, DateTime $toDate)
    {
        $weekOffDates = [];
        foreach ($weekOffDays as $day) {
            $currentDate = clone $fromDate;
            while ($currentDate <= $toDate) {
                if ($currentDate->format('l') === $day) {
                    $weekOffDates[] = $currentDate->format('Y-m-d');
                }
                $currentDate->modify('+1 day');
            }
        }
        return $weekOffDates;
    }


    //site list - for incidence
    public function index()
    {
        $user = session('user');
        Log::info($user->name . 'view incidence site list, User_id: ' . $user->id);
        if ($user->role_id == "1" || $user->role_id == "7") {

            $siteIds = IncidenceDetails::where('company_id', $user->company_id)->whereNotIn("statusFlag", [1, 2])->select('site_id')->distinct()->orderBy('site_id', 'DESC')->pluck('site_id')->toArray();
            //$site_id = IncidenceDetails::where('company_id', $user->company_id)->pluck('site_id')->toArray();
            $sites = DB::table('site_details as site')->whereIn('site.id', $siteIds)
                ->join('incidence_details as inci', 'inci.site_id', '=', 'site.id')
                ->whereNotIn("statusFlag", [1, 2])
                ->select(DB::raw("count(inci.id) as count"))
                ->groupBy('site.id')
                ->selectRaw('site.*')->get();

            //dd($sites->count);exit;
            return view('incidencesitelist')->with('sites', $sites);
        } elseif ($user->role_id == '2') {

            $sites = SiteAssign::where('user_id', $user->id)->first();
            $siteArray = json_decode($sites['site_id'], true);
            $siteIds = IncidenceDetails::whereIn('site_id', $siteArray)->whereNotIn("statusFlag", [1, 2])->select('site_id')->distinct()->orderBy('site_id', 'DESC')->pluck('site_id')->toArray();
            $sites = DB::table('site_details as site')->whereIn('site.id', $siteIds)
                ->join('incidence_details as inci', 'inci.site_id', '=', 'site.id')
                ->whereNotIn("statusFlag", [1, 2])
                ->select(DB::raw("count(inci.id) as count"))
                ->groupBy('site.id')
                ->selectRaw('site.*')->get();
            return view('incidencesitelist')->with('sites', $sites);
        } elseif ($user->role_id == '4') {
            $site = SiteDetails::where('client_id', $user->client_id)->pluck('id')->toArray();

            $siteIds = IncidenceDetails::whereIn('site_id', $site)->whereNotIn("statusFlag", [1, 2])->select('site_id')->distinct()->orderBy('site_id', 'DESC')->pluck('site_id')->toArray();

            $sites = DB::table('site_details as site')->whereIn('site.id', $siteIds)
                ->join('incidence_details as inci', 'inci.site_id', '=', 'site.id')
                ->whereNotIn("statusFlag", [1, 2])
                ->select(DB::raw("count(inci.id) as count"))
                ->groupBy('site.id')
                ->selectRaw('site.*')->get();

            return view('incidencesitelist')->with('sites', $sites);
        }

        //elseif($user->role_id  == '7'){
        //}

    }

    public function incidenceTypeCreate()
    {
        $user = session('user');

        return view('incidenceTypeCreate');
    }

    public function incidenceTypeCreateAction(Request $request)
    {
        //dd($request);
        $user = session('user');
        if ($user) {

            $messages = [
                'name.required' => '*Required',
            ];

            $validator = Validator::make($request->all(), [
                'name' => 'required',
            ], $messages);

            if ($validator->fails()) {

                return redirect('/incidence/type')
                    ->withErrors($validator)
                    ->withInput();
            } else {

                ActivityLog::create([
                    'company_id' => $user->company_id,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'type' => "Create Incidence Type",
                    'message' => "Incidence type created by " . $user->name,
                    'date_time' => date('Y-m-d H:i:s'),
                ]);

                $type = new IncidenceChecklist();
                $type->name = $request->name;
                $type->company_id = $user->company_id;
                $type->save();

                return \Redirect::route('incidence.type')->with('success', 'Incidence type created successfully.');
            }
        }
    }


    public function incidenceSubTypeCreate()
    {
        //dd($id);
        $user = session('user');

        return view('incidenceSubTypeCreate');
    }


    public function incidenceSubTypeCreateAction(Request $request)
    {
        //dd($request);
        $user = session('user');

        if ($user) {
            $messages = [
                'name.required' => '*Required',
            ];

            $validator = Validator::make($request->all(), [
                'name' => 'required',
            ], $messages);

            if ($validator->fails()) {

                return redirect('/incidence/type')
                    ->withErrors($validator)
                    ->withInput();
            } else {

                ActivityLog::create([
                    'company_id' => $user->company_id,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'type' => "Create Incidence Type",
                    'message' => "Incidence type created by " . $user->name,
                    'date_time' => date('Y-m-d H:i:s'),
                ]);

                $type = new IncidenceChecklist();
                $type->name = $request->name;
                $type->type_id = $request->incidence_id;
                $type->company_id = $user->company_id;

                $type->save();

                $incidenceType = IncidenceChecklist::where('id', $request->incidence_id)->first();
                // dd($incidenceType);
                $incidenceChecklist = IncidenceChecklist::where('company_id', $user->company_id)->where('type_id', $request->incidence_id)->get();

                return view('incidenceSubType')->with('incidenceType', $incidenceType)->with('incidenceChecklist', $incidenceChecklist)->with('success', 'Incidence sub type created successfully.');
            }
        }
    }


    public function deleteIncidenceSubType($typeId, $rowId)
    {
        $user = session('user');
        if ($user) {
            ActivityLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Delete Incidence Type",
                'message' => "Incidence type deleted by " . $user->name,
                'date_time' => date('Y-m-d H:i:s'),
            ]);
            if ($rowId) {

                IncidenceCheckList::where('id', $rowId)->where('type_id', $typeId)->delete();

                return redirect()->route('getIncidence.type', [$typeId])->with('error', 'Incidence type deleted successfully !');
            }
        }
    }


    public function editIncidenceTypeAction(Request $request)
    {
        //dd($request);
        $user = session('user');
        if ($user) {

            $messages = [
                'name.required' => '*Required',
            ];

            $validator = Validator::make($request->all(), [
                'name' => 'required',
            ], $messages);

            if ($validator->fails()) {

                return redirect('/incidence/Type')
                    ->withErrors($validator)
                    ->withInput();
            } else {

                ActivityLog::create([

                    'company_id' => $user->company_id,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'type' => "Update Incidence Type",
                    'message' => "Incidence type updated by " . $user->name,
                    'date_time' => date('Y-m-d H:i:s'),
                ]);

                $type = IncidenceChecklist::where('id', $request->id)->first();

                //dd($request,$type);
                $type->name = $request->name;
                //dd($type);
                $type->update();
                return $type;
            }
        }
    }


    public function getIncidenceType($id)
    {
        //dd($id);
        $user = session('user');
        Log::info($user->name . 'view incidence sub type list, User_id: ' . $user->id);

        $flag = 'subType';

        $incidenceType = IncidenceChecklist::where('id', $id)->first();
        //dd($incidenceType);
        $incidenceChecklist = IncidenceChecklist::where('company_id', $user->company_id)->where('type_id', $id)->get();
        //dd($incidenceChecklist);
        return view('incidenceSubType')->with('incidenceType', $incidenceType)->with('incidenceChecklist', $incidenceChecklist);
    }


    public function incidenceType()
    {

        $user = session('user');
        Log::info($user->name . 'view incidence type list, User_id: ' . $user->id);
        $incidenceChecklist = IncidenceChecklist::where('company_id', $user->company_id)->where('type_id', '=', Null)->get();

        return view('incidenceType')->with('incidenceChecklist', $incidenceChecklist);
    }

    public function deleteIncidenceType($rowId)
    {
        $user = session('user');

        if ($user) {

            ActivityLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Delete Incidence Type",
                'message' => "Incidence type deleted by " . $user->name,
                'date_time' => date('Y-m-d H:i:s'),
            ]);
            if ($rowId) {

                IncidenceCheckList::where('id', $rowId)->delete();

                return redirect()->route('incidence.type')->with('error', 'Incidence type deleted successfully !');
            }
        }
    }

    public function editIncidenceType($id)
    {
        //dd($id);
        $user = session('user');
        if ($user) {

            Log::channel('create')->info($user->name . 'view incidence type edit form');

            $incidenceChecklist = IncidenceChecklist::find($id);
            // dd($id);
            return view('incidenceTypeEdit')->with('incidenceChecklist', $incidenceChecklist)->with('id', $id);
        }
    }

    //details of incidence
    public function getIncidence($site_id)
    {
        $user = session('user');
        Log::info($user->name . 'view incidence list, User_id: ' . $user->id);
        $incidences = IncidenceDetails::where('site_id', $site_id)->whereNotIn("statusFlag", [1, 2])->get();
        $checkList = DB::table('checkList')->where('id', 2)->first();

        foreach ($incidences as $inc) {
            $checkArray = json_decode($checkList->checkList, true);
            $incCheck = json_decode($inc->checkList, true);
            // dd($incCheck);
            foreach ($incCheck as $checked) {
                foreach ($checkArray as $key => $check) {
                    if ($check['name'] == $checked) {
                        $checkArray[$key]['checked'] = true;
                    }
                }
            }
            $inc['checkList'] = $checkArray;
            //dd($inc->checkList);
        }
        //dd($incidences);exit;
        return view('incidencelist')->with('incidences', $incidences);
    }

    // incidence list
    public function incidences($status, $date)
    {
        //dd($status, $date);
        $todayDate = date('Y-m-d', strtotime($date));
        if ($status == 'Pending with supervisor' || $status == '1') {
            $incidences = IncidenceDetails::where('dateFormat', $todayDate)->where("statusFlag", '0')->get();
        } elseif ($status == 'Resolved' || $status == '0') {
            $incidences = IncidenceDetails::where('dateFormat', $todayDate)->where("statusFlag", '1')->get();
        } elseif ($status == 'Ignored' || $status == '2') {
            $incidences = IncidenceDetails::where('dateFormat', $todayDate)->where("statusFlag", '2')->get();
        } elseif ($status == 'Escalated To Admin' || $status == '3') {
            $incidences = IncidenceDetails::where('dateFormat', $todayDate)->where("statusFlag", '3')->get();
        } elseif ($status == 'Pending With Admin' || $status == '4') {
            $incidences = IncidenceDetails::where('dateFormat', $todayDate)->where("statusFlag", '4')->get();
        } elseif ($status == 'Escalate To Client' || $status == '5') {
            $incidences = IncidenceDetails::where('dateFormat', $todayDate)->where("statusFlag", '5')->get();
        }
        return view('incidencelist')->with('incidences', $incidences);
    }

    // incidence action page
    public function incidenceActionTaken($site_id, $incidence_id)
    {
        return view("incidenceactiontaken")->with('site_id', $site_id)->with('incidence_id', $incidence_id);
    }

    // fetch incidence using ajax
    public function fetchIncidences()
    {
        $user = session('user');
        Log::info($user->name . ' fetch incidences, User_id: ' . $user->id);
        if ($user->role_id == '1') {
            $incidence = DB::table('incidence_details')->where('company_id', $user->company_id)->whereNotIn('statusFlag', [1, 2])->select('count(*) as allcount')->count();
            return $incidence;
        } elseif ($user->role_id == '2') {
            $sites = SiteAssign::where('user_id', $user->id)->first();
            $siteArray = json_decode($sites['site_id'], true);
            $incidence = DB::table('incidence_details')
                ->whereIn('site_id', $siteArray)
                ->whereNotIn("statusFlag", [1, 2])
                ->select('count(*) as allcount')
                ->count();
            return $incidence;
        }
    }

    // incidece view log details
    public function viewDetails($log_id, $type)
    {

        $flag = $type;
        // dd($log_id,$type);  
        $user = session('user');
        $cur_date = new DateTime();
        $date = $cur_date->format('Y-m-d');
        if ($type == 'incidence') {
            $log = IncidenceDetails::where('id', $log_id)->first();
            // return redirect()->route('notification.incidentRead',$log_id, $flag);
            return Redirect::to('/notification/incidentRead/' . $log_id . '/' . $type);
        } else {
            $log = Attendance::where('id', $log_id)->first();
            // return redirect()->route('notification.incidentRead',$log_id, $flag);
            return Redirect::to('/notification/incidentRead/' . $log_id . '/' . $type);
        }
        //$viewAllLog = ActivityLog::where('company_id', $user->company_id)->->orderBy('created_at', 'desc')->whereDate('created_at', Carbon::today())
        //         ->get();
        // dd($log);

    }
    // incidence detail page
    public function incidentRead($notification_id, $flag)
    {
        // dd($notification_id, $flag);
        $user = session('user');
        Log::info($user->name . 'view incidence detail, User_id: ' . $user->id);
        $notificationID = Notifications::where('notification_id', '=', $notification_id)->first();
        $incidenceDetails = IncidenceDetails::where('id', '=', $notification_id)->first();
        $revertAction = IncidenceComment::where('incidence_id', $incidenceDetails->id)->get();

        // dd($revertAction);
        return view("incidenceread")->with('revertAction', $revertAction)->with('incidenceDetails', $incidenceDetails)->with('notificationID', $notificationID)->with('flag', $flag);
    }



    private function getWeekoffData($userIds)
    {
        // Query to get weekoff data
        $weekoffData = SiteAssign::whereIn('user_id', $userIds)->groupBy(['user_id', 'weekoff']);
        // siteUserIds
        // Process the weekoff data
        $weekoffs = $weekoffData->get()->mapToGroups(function ($item, $key) {
            return [$item['user_id'] => $item['weekoff']];
        })->toArray();

        // Get the site user IDs
        $siteUserIds = $weekoffData->pluck('user_id')->toArray();

        // Return both weekoffs and siteUser Ids
        return [
            'weekoffs' => $weekoffs,
            'siteUserIds' => $siteUserIds,
        ];
    }



    protected function processAttendanceForOtherRoles($user, $startDate, $endDate, $guard, $geofences, $site_UserIds)
    {
        // Your implementation for other roles
        // Example logic:
        // dump($geofences, $guard, "site data");
        // dd($site_UserIds , "sit user ids");
        if ($geofences == "all") {
            //keep this only
            // dump('when geo is all', $user->role_id == 2);
            $test = Users::when($user->role_id == 2, fn($query) => $query->whereNotIn('users.role_id', [1, 2, 4]))
                ->when($user->role_id != 2, fn($query) => $query->whereNotIn('users.role_id', [1, 4]))
                ->rightJoin('attendance', 'users.id', '=', 'attendance.user_id')
                ->rightJoin('site_assign', 'users.id', '=', 'site_assign.user_id')
                ->whereIn('users.id', $site_UserIds)
                ->whereBetween('attendance.dateFormat', [$startDate, $endDate])
                ->select(
                    'users.name as name',
                    'users.id as user_id',
                    'attendance.date as date',
                    'attendance.site_name as site_name',
                    'attendance.time_difference as duration',
                    'site_assign.client_name as client_name'
                )
                ->orderBy('name');
            // dd($test->pluck('name')->toArray() , 'test');
            return $test;
        } else {
            // tempo
            // dd('here2');

            if ($guard != "all") {
                // dump('here1');
                return Users::where('users.company_id', $user->company_id)
                    ->where('users.id', $guard)
                    ->rightJoin('attendance', 'users.id', '=', 'attendance.user_id')
                    ->whereIn('users.id', $site_UserIds)
                    ->whereBetween('attendance.dateFormat', [$startDate, $endDate])
                    ->select('users.name as name', 'users.id as user_id', 'attendance.date as date', 'attendance.site_name as site_name', 'attendance.time_difference as duration')
                    ->orderBy('name');
            } else {
                // dd('here');
                // tempo


                return Users::where('users.company_id', $user->company_id)
                    ->rightjoin('attendance', 'users.id', '=', 'attendance.user_id')
                    ->rightjoin('site_assign', 'users.id', '=', 'site_assign.user_id')
                    ->whereBetween('attendance.dateFormat', [$startDate, $endDate])
                    ->whereIn('users.id', $site_UserIds)
                    ->select('users.name as name', 'users.id as user_id', 'attendance.date as date', 'site_assign.site_name as site_name', 'attendance.time_difference as duration', 'site_assign.client_name as client_name')
                    // ->orderBy('client_name')
                    // ->orderBy('site_name')
                    ->orderBy('name');
                // dump($test->get() , 'test');

                // dd($data->pluck('name') , "data , here");
            }
        }
    }

    private function fetchSiteData($user, $request, $site_UserIds, $geofences, $client)
    {
        // dd($user->company_id, $request->all(), $site_UserIds, $geofences);
        // Check if geofences is set to 'all'
        if ($client == 'all' && $geofences == "all") {
            // dump('client all geo all',$user->company_id);
            return SiteAssign::where('company_id', $user->company_id)
                // ->where('client_id', $request->client)
                ->select('user_id', 'site_name', 'client_name')
                ->get()
                ->mapToGroups(function ($item, $key) {
                    return [
                        $item['user_id'] => [
                            'site' => $item['site_name'],
                            'client' => $item['client_name']
                        ]
                    ];
                })
                ->toArray();
        } else {
            // Fetch specific site data
            return SiteAssign::where('company_id', $user->company_id)
                ->select('user_id', 'site_name', 'client_name')
                ->get()
                ->mapToGroups(function ($item, $key) {
                    return [
                        $item['user_id'] => [
                            'site' => $item['site_name'],
                            'client' => $item['client_name']
                        ]
                    ];
                })
                ->toArray();
        }
    }


    private function fetchAttendanceData($user, $startDate, $endDate, $site_UserIds, $geofences)
    {
        // Base query for attendance
        // dump($user->name, $startDate, $endDate, $site_UserIds, $geofences);
        $attendanceQuery = Attendance::where('company_id', $user->company_id)->whereIn('user_id', $site_UserIds)
            ->whereBetween('dateFormat', [$startDate, $endDate])
            ->whereIn('role_id', [2, 3]); // Filter by roles if required

        // Modify query based on geofence selection
        if ($geofences == "all") {
            // If all geofences are selected, filter by site_UserIds
            $attendanceQuery->whereIn('user_id', $site_UserIds);
        }

        // Return the query result
        // dd(    $attendanceQuery->get()  , "attdenqury ");
        return $attendanceQuery;
    }


    private function allGuardReportMethod($user, $request, $startDate, $endDate, $attendanceSubType, $geofences, $guard, $type, $client, $subType, $dateRange, $generatedOn)
    {
        // dd(' in all guard');

        // dump($generatedOn, "generated on");
        $pra = SiteAssign::where('company_id', $user->company_id)
            ->where('user_name', 'Laxman Bansode')
            ->get();

        // dump($pra, 'pratiks');
        // Calculate days count


        $datetime1 = new DateTime($startDate);
        $datetime2 = new DateTime($endDate);
        $interval = $datetime1->diff($datetime2);
        $daysCount = (int) $interval->format('%a') + 1;

        $dateFormat = "Range";
        $date = date('d M Y', strtotime($startDate)) . " to " . date('d M Y', strtotime($endDate));
        $startDatee = date('d-m-Y', strtotime($startDate));
        $currentDate = (new DateTime('now', new DateTimeZone('Asia/Kolkata')))->format("d-m-Y");

        // Determine site user IDs based on user role and request parameters

        // dump($user->role_id, "role of user");
        if ($user->role_id == 7) {
            $dateRange = [$datetime1->format('Y-m-d'), $datetime2->format('Y-m-d')];

            $siteAssigned = SiteAssign::where('user_id', $user->id)->first();

            // $client_ids = json_decode($siteAssigned, true);
            $client_ids = json_decode($siteAssigned['site_id'], true);
            // dd($siteAssigned ,  $client_ids,"site Assigned");

            $siteArray = SiteDetails::whereIn('client_id', $client_ids)->pluck('id')->toArray();
            // dd($siteArray , "array");

            $supervisorIds = Users::where([['users.company_id', '=', $user->company_id], ['users.role_id', '=', 2]])
                ->leftJoin('site_assign as site', 'users.id', '=', 'site.user_id')
                ->where(function ($query) use ($siteArray) {
                    foreach ($siteArray as $siteId) {
                        $query->orWhereRaw('JSON_CONTAINS(site.site_id, ?)', [json_encode([$siteId])]);
                    }
                })->pluck('user_id')->toArray();

            $userIds = SiteAssign::whereIn('client_id', $client_ids)->pluck('user_id')->toArray();

            // $userIds = array_merge($userIds, $supervisorIds);
            $site_UserIds = array_merge($userIds, $supervisorIds);

            // dd($site_UserIds);
            // dd($dateRange , "date range");
            // $data = Attendance::where('emergency_attend', 1)
            //     ->whereIn('user_id', $userIds)
            //     ->whereBetween('dateFormat', $dateRange)
            //     ->get();

            //     // dd($data , "data");

        } else if ($user->role_id == 1) {
            // dump('role is not 3 ', $user->role_id != 2);
            $site_UserIds = SiteAssign::where('company_id', $user->company_id)
                ->whereNotIn('role_id', [1, 4])
                ->get()
                ->pluck('user_id')
                ->unique();
            // dump($site_UserIds , "site user ids");
        } else {

            $site = SiteAssign::where('user_id', $user->id)->first();
            // dd($site , "site");/
            $siteArray = json_decode($site['site_id'], true);
            $site_UserIds = SiteAssign::where('company_id', $user->company_id)->whereIn('site_id', $siteArray)->where('role_id', 3)
                ->get()->pluck('user_id')->unique();
            // dump($site_UserIds, "when cli all");

            // dd($siteArray,"site array");
        }

        // Process attendance data based on role and guard
        $test = $this->processAttendanceForOtherRoles($user, $startDate, $endDate, $guard, $geofences, $site_UserIds);

        // dd($test->pluck('name')->toArray() , 'roles');

        // Generate attendance-related data
        $names = $test->groupBy(['user_id', 'name', 'date'])
            ->get()
            ->mapToGroups(fn($item, $key) => [$item['user_id'] => $item['name']])
            ->toArray();

        $hours = $test->groupBy(['user_id', 'duration', 'date'])
            ->get()
            ->mapToGroups(fn($item, $key) => [$item['user_id'] => $item['duration']])
            ->toArray();

        // $sites =   $test->groupBy(['user_id', 'site_name', 'client_name'])
        //     ->get()
        //     ->mapToGroups(function ($item, $key) {
        //         return [
        //             $item['user_id'] => [
        //                 'site' => $item['site_name'],
        //                 'client' => $item['client_name']
        //             ]
        //         ];
        //     })
        //     ->toArray();

        $userIds = array_unique($test->pluck('user_id')->toArray());

        $sites = $this->fetchSiteData($user, $request, $site_UserIds, $geofences, $client);
        // dump($sites, "site data");

        $data = $test->groupBy(['user_id', 'name', 'date'])
            ->get()
            ->mapToGroups(fn($item, $key) => [$item['user_id'] => $item['date']])
            ->map(function ($dates) {
                return array_unique($dates->toArray());
            })
            ->toArray();

        $attendSites = $test->groupBy(['user_id', 'site_name'])
            ->get()->mapToGroups(function ($item, $key) {
                return [$item['user_id'] => $item['site_name']];
            })->toArray();
        // dump($attendSites, "sites");
        // dd($test->pluck('name')->toArray() , "data");
        // dd($data , "data");


        // dump($data , 'data');


        $supervisorAssignedSites =
            SiteAssign::where('company_id', $user->company_id)
                ->whereIn('role_id', [7, 2])
                ->select('user_id', 'site_id', 'role_id')
                ->get();
        $supervisorSites = [];


        foreach ($supervisorAssignedSites as $assign) {

            $siteIds = is_string($assign->site_id)
                ? json_decode($assign->site_id, true)
                : (array) $assign->site_id;

            if (empty($siteIds))
                continue;

            // dump($assign->role_id , "role id");
            if ($assign->role_id === 7) {
                // Admin: treat site_id field as client_id(s)

                // dd('here' , 'here');
                $clientIds = $siteIds;

                // Get client names only (no site names shown)
                $clientNames = ClientDetails::whereIn('id', $clientIds)
                    ->pluck('name')
                    ->unique()
                    ->values()
                    ->toArray();
                // dd($clientNames, "client name");
                $supervisorSites[$assign->user_id] = [
                    'site' => [], // No site names for admins
                    'client' => $clientNames
                ];
            } else {
                // dd('supervisor');
                // Supervisor: use site IDs to fetch site and client info
                $sitesx = SiteDetails::whereIn('id', $siteIds)->get();

                $siteNames = $sitesx->pluck('name')->unique()->values()->toArray();

                $clientNames = $sitesx->pluck('client_name')
                    ->filter(fn($name) => !is_null($name))
                    ->unique()
                    ->values()
                    ->toArray();

                // Fallback if client_name missing in site table
                if (count($clientNames) === 0) {
                    $clientIds = $sitesx->pluck('client_id')
                        ->filter(fn($id) => !is_null($id))
                        ->unique()
                        ->values()
                        ->toArray();

                    $clientNames = ClientDetails::whereIn('id', $clientIds)
                        ->pluck('name')
                        ->unique()
                        ->values()
                        ->toArray();
                }

                $supervisorSites[$assign->user_id] = [
                    'site' => $siteNames,
                    'client' => $clientNames
                ];
            }
        }


        // dump($sites , "sites");

        // dump($supervisorSites , 'supervisor sites');
        $users = Users::where('users.company_id', $user->company_id)
            ->rightjoin('site_assign', 'users.id', '=', 'site_assign.user_id')
            ->where('users.showUser', 1)
            ->whereNotIn('users.id', $userIds)
            ->when($user->role_id == 2, fn($query) => $query->whereNotIn('users.role_id', [1, 2, 4])->whereIn('users.id', $site_UserIds))
            ->when($user->role_id == 1, fn($query) => $query->whereIn('users.role_id', [2, 3, 7]))
            ->when($user->role_id == 7, fn($query) => $query->whereIn('users.id', $site_UserIds))
            ->orderBy('site_assign.client_name', 'ASC')
            ->select('users.id', 'users.name')
            ->get();



        foreach ($users as $key => $value) {
            //$data[$value['id']] = [];
            $data[$value['id']] = [];
            $names[$value['id']] = [$value['name']];
        }


        // foreach ($siteUsers as $key => $value) {
        //     $weekoffs[$value['id']] = [];
        //     $names[$value['id']] = [$value['name']];
        // }
        // dd($data , "data");

        // Weekoff data
        $weekoffData = SiteAssign::whereIn('user_id', $userIds)->groupBy(['user_id', 'weekoff']);
        $weekoffs = $weekoffData->get()
            ->mapToGroups(fn($item, $key) => [$item['user_id'] => $item['weekoff']])
            ->toArray();

        $siteUserIds = $weekoffData->pluck('user_id')->toArray();

        $siteUsers = Users::where('company_id', $user->company_id)
            ->whereNotIn('id', $siteUserIds)
            ->where('showUser', 1)
            ->whereIn('role_id', [2, 3])
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        foreach ($siteUsers as $value) {
            $weekoffs[$value['id']] = [];
            $names[$value['id']] = [$value['name']];
        }
        // dd($user->name, $startDate, $endDate, $site_UserIds, $geofences);

        $attendance = $this->fetchAttendanceData($user, $startDate, $endDate, $site_UserIds, $geofences);
        // dd($attendance,'attendance');
        $attendCount = $attendance
            ->groupBy(['user_id', 'dateFormat'])
            ->get()
            ->mapToGroups(fn($item, $key) => [$item['dateFormat'] => $item['user_id']])
            ->toArray();

        ksort($attendCount);

        // Prepare modal data
        $companyData = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $companyData->name;
        $companyId = $user->company_id;


        // dump($sites, 'sites');
        // dump($daysCount , "dayscount");
        // dump($attendCount , "attend count");
        // dd($supervisorSites , "sites");
        // dd($subType , "type");
        // dump('attendance site', $attendSites);

        return view('AttendanceReport/allGuardReportWithSiteView')->with('geofences', $geofences)
            ->with('attendanceSubType', $attendanceSubType)->with('sites', $sites)->with('guard', $guard)
            ->with('data', $data)->with('weekoffs', $weekoffs)->with('names', $names)->with('attendCount', $attendCount)
            ->with('date', $date)->with('daysCount', $daysCount)->with('startDatee', $startDatee)->with('companyName', $companyName)
            ->with('fromdate', $startDate)->with('todate', $endDate)->with('currentDate', $currentDate)->with('companyId', $companyId)
            ->with('dateFormat', $dateFormat)->with('subType', $subType)->with('type', $type)->with('hours', $hours)->with('client', $request->client)
            ->with('supervisorSites', $supervisorSites)
            ->with('attendSites', $attendSites)
            ->with('generatedOn', $this->generatedOn)
            ->render();
    }


    protected function clientWiseReportMethod($request, $user, $startDate, $endDate, $geofences, $subType)
    {
        // dump('1st user' , $user);
        // Step 1: Initialize variables
        // dd(' in client wise');
        $companyData = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $companyData->name;
        $date = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));

        // $companyName = 'test';
        $companyId = $companyData->id;

        $datetime1 = new DateTime($startDate);
        $datetime2 = new DateTime($endDate);
        $interval = $datetime1->diff($datetime2);
        $daysCount = (int) $interval->format('%a') + 1;

        $dateFormat = "Range";
        $date = date('d M Y', strtotime($startDate)) . " to " . date('d M Y', strtotime($endDate));
        $startDatee = date('d-m-Y', strtotime($startDate));
        $currentDate = (new DateTime('now', new DateTimeZone('Asia/Kolkata')))->format("d-m-Y");

        // Get site assignments for the current user
        $site = SiteAssign::where('user_id', $user->id)->first();

        // Get user IDs based on role and client selection
        if ($user->role_id == 2) {
            $siteArray = json_decode($site['site_id'], true);
            $site_UserIds = SiteAssign::where('company_id', $user->company_id)
                ->whereIn('site_id', $siteArray)
                ->where('role_id', 3)
                ->get()
                ->pluck('user_id')
                ->unique();
        } else {
            $site_UserIds = SiteAssign::where('client_id', $request->client)
                ->get()
                ->pluck('user_id')
                ->unique();

            // dump($site_UserIds , 'site user ids');
        }

        // Query attendance data
        if ($user->role_id == 2) {
            $test = Users::where('users.company_id', $user->company_id)
                ->rightjoin('attendance', 'users.id', '=', 'attendance.user_id')
                ->rightjoin('site_assign', 'users.id', '=', 'site_assign.user_id')
                ->whereIn('users.id', $site_UserIds)
                ->whereBetween('attendance.dateFormat', [$startDate, $endDate])
                ->select(
                    'users.name as name',
                    'users.id as user_id',
                    'attendance.date as date',
                    'attendance.site_name as site_name',
                    'attendance.time_difference as duration',
                    'site_assign.client_name as client_name'

                )
                ->orderBy('name');
        } else {
            $test = Users::where('users.company_id', $user->company_id)
                ->leftjoin('attendance', 'users.id', '=', 'attendance.user_id')
                ->leftjoin('site_assign', 'users.id', '=', 'site_assign.user_id')
                ->whereIn('users.id', $site_UserIds)
                ->whereBetween('attendance.dateFormat', [$startDate, $endDate])
                ->select(
                    'users.name as name',
                    'users.id as user_id',
                    'attendance.date as date',
                    'attendance.site_name as site_name',
                    'attendance.time_difference as duration',
                    'site_assign.client_name as client_name'
                )
                ->orderBy('name');
        }

        // Process user names
        $names = $test->groupBy(['user_id', 'name', 'date'])
            ->get()->mapToGroups(function ($item, $key) {
                return [$item['user_id'] => $item['name']];
            })->toArray();

        // Process duration hours
        $hours = $test->groupBy(['user_id', 'duration', 'date'])
            ->get()->mapToGroups(function ($item, $key) {
                return [$item['user_id'] => $item['duration']];
            })->toArray();

        // Fetch site data
        // dump($test->pluck('user_id')->toArray() ,  $site_UserIds ,"test");


        // $sites =   $test->groupBy(['user_id', 'site_name', 'client_name'])
        //             ->get()
        //             ->mapToGroups(function ($item, $key) {
        //                 return [
        //                     $item['user_id'] => [
        //                         'site' => $item['site_name'],
        //                         'client' => $item['client_name']
        //                     ]
        //                 ];
        //             })
        //             ->toArray();
        $sites = $this->fetchSiteData($user, $request, $site_UserIds, $geofences, $request->client);

        // dump($sites, "sites");
        // Get unique user IDs from attendance data
        $userIds = $test->pluck('user_id')->unique()->toArray();

        // Get all attendance data grouped by user and date

        $data = $test->groupBy(['user_id', 'name', 'date'])
            ->get()
            ->mapToGroups(fn($item, $key) => [$item['user_id'] => $item['date']])
            ->map(function ($dates) {
                return array_unique($dates->toArray());
            })
            ->toArray();

        // Now fetch users who haven't marked attendance but should be in the report
        // This is the key fix - use SiteAssign table to get the correct user data
        // $additionalUsers = SiteAssign::where('client_id', $request->client)
        //     ->where('company_id', $user->company_id)
        //     ->where('role_id', 3)
        //     ->whereNotIn('user_id', $userIds)
        //     ->select('user_id', 'user_name as name')
        //     ->get();

        // // Add these users to the data structure
        // foreach ($additionalUsers as $user) {
        //     $data[$user->user_id] = [];
        //     $names[$user->user_id] = [$user->name];
        // }

        // Get weekoff data
        $result = $this->getWeekoffData($userIds);
        $weekoffs = $result['weekoffs'];
        $siteUserIds = $result['siteUserIds'];

        // Process weekoffs for additional users
        $weekoffData = SiteAssign::whereIn('user_id', $userIds)->groupBy(['user_id', 'weekoff']);
        $weekoffs = $weekoffData->get()->mapToGroups(function ($item, $key) {
            return [$item['user_id'] => $item['weekoff']];
        })->toArray();

        // Set weekoffs for users without weekoff data
        // foreach ($additionalUsers as $user) {
        //     if (!isset($weekoffs[$user->user_id])) {
        //         $weekoffs[$user->user_id] = [];
        //     }
        // }

        // Get client name
        $clientName = 'N/A';
        if ($user->role_id != 2 && $request->client != "all") {
            $clientInfo = ClientDetails::where('id', $request->client)->first();
            $clientName = $clientInfo ? $clientInfo->name : 'N/A';
        }

        // Fetch attendance data
        $attendance = $this->fetchAttendanceData($user, $startDate, $endDate, $site_UserIds, $geofences);

        $attendCount = $attendance->groupBy(['user_id', 'dateFormat'])
            ->get()
            ->mapToGroups(fn($item, $key) => [$item['dateFormat'] => $item['user_id']])
            ->toArray();

        ksort($attendCount);


        $users = Users::where('users.company_id', $user->company_id)
            ->rightjoin('site_assign', 'users.id', '=', 'site_assign.user_id')
            ->where('users.showUser', 1)
            ->whereNotIn('users.id', $userIds)
            ->when($user->role_id == 2, fn($query) => $query->whereNotIn('users.role_id', [1, 2, 4])->whereIn('users.id', $site_UserIds))
            ->when($user->role_id == 1, fn($query) => $query->whereIn('users.role_id', [2, 3])->whereIn('users.id', $site_UserIds))
            ->when($user->role_id == 7, fn($query) => $query->whereIn('users.id', $site_UserIds))
            ->orderBy('site_assign.client_name')
            ->orderBy('site_assign.site_name')
            ->select('users.id', 'users.name')
            ->get();


        // dump($data, 'prev');

        foreach ($users as $key => $value) {
            //$data[$value['id']] = [];
            $data[$value['id']] = [];
            $names[$value['id']] = [$value['name']];
        }

        // dump($data, 'after');

        // Get company data

        // dump($sites, 'sites');
        // dump($companyData , $user);

        // Return the view with all necessary data
        return view('AttendanceReport/clientWiseReportView')
            ->with('clientName', $clientName)
            ->with('attendanceSubType', $request->attendanceSubType)
            ->with('sites', $sites)
            ->with('guard', $request->guard)
            ->with('data', $data)
            ->with('weekoffs', $weekoffs)
            ->with('names', $names)
            ->with('attendCount', $attendCount)
            ->with('date', $date)
            ->with('daysCount', $daysCount)
            ->with('startDatee', $startDatee)
            ->with('companyName', $companyName)
            ->with('fromdate', $startDate)
            ->with('todate', $endDate)
            ->with('currentDate', $currentDate)
            ->with('companyId', $companyId)
            ->with('dateFormat', $dateFormat)
            ->with('subType', $subType)
            ->with('type', $request->type)
            ->with('hours', $hours)
            ->with('client', $request->client)
            ->with('generatedOn', $this->generatedOn);
    }


    private function siteWiseReportMethod($client, $geofences, $guard, $request, $user, $startDate, $endDate, $attendanceSubType, $daysCount, $type, $subType)
    {
        // When the client is set to single, site is single, and Guard/emp is set to all
        // dump('Client is single and employee is all');
        // dd(' in siteWise');
        // Fetch user IDs assigned to the selected client

        $clientName = '';
        if ($user->role_id !== 2) {

            $clientName = ClientDetails::where('company_id', $user->company_id)->where('id', $client)->first();
            //    dump($client->name , "client");

            $clientName = $clientName->name;
        }

        if ($user->role_id == 2) {
            // $siteArray = json_decode($site['site_id'], true);
            $siteUserIds = SiteAssign::where('company_id', $user->company_id)
                ->where('site_id', $geofences)
                ->where('role_id', 3)
                ->get()
                ->pluck('user_id')
                ->unique();
        } else {
            $siteUserIds = SiteAssign::where('client_id', $request->client)->where('site_id', $geofences)
                ->pluck('user_id')
                ->unique();
        }
        // Base query for attendance data
        $test = Users::where('users.company_id', $user->company_id)
            ->rightJoin('attendance', 'users.id', '=', 'attendance.user_id')
            ->rightJoin('site_assign', 'users.id', '=', 'site_assign.user_id')
            ->whereIn('users.id', $siteUserIds)
            ->whereBetween('attendance.dateFormat', [$startDate, $endDate])
            ->select(
                'users.name as name',
                'users.id as user_id',
                'attendance.date as date',
                'attendance.site_name as site_name',
                'attendance.time_difference as duration',
                'site_assign.client_name as client_name'
            )
            ->orderBy('name');
        // dump($test->pluck('site_name')->toArray());


        // Format date range
        $dateFormat = "Range";
        $date = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));
        $startDatee = date('d-m-Y', strtotime($startDate));
        $currentDate = now()->timezone('Asia/Kolkata')->format("d-m-Y");

        // Fetch site IDs for the user (if role_id is 2)
        $siteIds = $user->role_id == 2
            ? SiteAssign::where('user_id', $user->id)
                ->pluck('site_id')
                ->map(function ($item) {
                    return is_string($item) ? json_decode($item, true) : $item;
                })
                ->flatten()
                ->unique()
                ->values()
            : SiteAssign::where('client_id', $client)->where('site_id', $geofences)->get();
        // dd($siteIds, "is");
        // Check if the user is authorized to access the selected site
        if ($user->role_id == 2 && $geofences != 'all' && !$siteIds->contains($geofences)) {
            return response()->json(['error' => 'You are not authorized to access this site'], 403);
        }

        // Fetch user data based on role and site selection
        $userData = SiteAssign::where('site_assign.company_id', $user->company_id)
            ->leftJoin('attendance', 'site_assign.user_id', '=', 'attendance.user_id')
            ->when($user->role_id == 2, function ($query) use ($siteIds, $geofences) {
                $query->whereIn('site_assign.site_id', $geofences != 'all' ? [$geofences] : $siteIds);
            })
            ->when($user->role_id != 2 && $geofences != 'all', function ($query) use ($geofences) {
                $query->where('site_assign.site_id', $geofences);
            })
            ->whereBetween('attendance.dateFormat', [$startDate, $endDate])
            ->select(
                'site_assign.user_name as name',
                'site_assign.user_id as user_id',
                'attendance.date as date',
                'site_assign.client_name as client_name',
                'attendance.site_name as site_name',
            )
            ->distinct()
            ->orderBy('site_assign.user_name', 'ASC');


        // dd($userData->pluck('client_name')->toArray() , "userData");        // Group user data by user_id, name, and date
        $names = $userData->get()
            ->mapToGroups(function ($item) {
                return [$item['user_id'] => $item['name']];
            })
            ->toArray();

        // Fetch unique user IDs
        $userIds = $userData->pluck('user_id')->unique()->toArray();

        // Fetch sites assigned to users
        if ($attendanceSubType == 'EmployeeAttendanceReportwithSite') {

            $sites = $test->get()
                ->mapToGroups(function ($item) {
                    return [
                        $item['user_id'] => [
                            'site' => $item['site_name'],
                            'client' => $item['client_name']
                        ]
                    ];
                })
                ->toArray();
            // dump($sites, "sites");
        } else {
            // $siteCollection = SiteAssign::where('company_id',$user->company_id)->where('site_id',$geofences);
            // $sites = $siteCollection->name;
            // dump('site Name')
            $sites = '';
        }

        // Fetch site data for the selected geofence
        $siteData = SiteAssign::where('site_id', $geofences)
            ->whereNotIn('user_id', $userIds)
            ->get();

        // Group user data by user_id and date
        $data = $userData->get()
            ->mapToGroups(function ($item) {
                return [$item['user_id'] => $item['date']];
            })
            ->toArray();

        // Determine site name
        $siteName = $geofences == 'all' || $geofences === null
            ? 'All Sites'
            : SiteAssign::where('site_id', $geofences)->value('site_name');

        // Fetch weekoff data for users
        $weekoffs = SiteAssign::whereIn('user_id', $userIds)
            ->groupBy(['user_id', 'weekoff'])
            ->get()
            ->mapToGroups(function ($item) {
                return [$item['user_id'] => $item['weekoff']];
            })
            ->toArray();

        // Add missing users to weekoffs, data, and names
        foreach ($siteData as $value) {
            $weekoffs[$value['user_id']] = $value['weekoff'] ? [$value['weekoff']] : [];
            $data[$value['user_id']] = [];
            $names[$value['user_id']] = [$value['user_name']];
        }

        // Fetch user IDs in order
        $users = Users::whereIn('id', array_keys($data))
            ->orderBy('name', 'ASC')
            ->pluck('id')
            ->toArray();

        // Fetch attendance counts
        $attendCount = Attendance::whereIn('user_id', $userIds)
            ->whereBetween('dateFormat', [$startDate, $endDate])
            ->groupBy(['user_id', 'dateFormat'])
            ->get()
            ->mapToGroups(function ($item) {
                return [$item['dateFormat'] => $item['user_id']];
            })
            ->toArray();
        // dd($data , "attendCount");
        // Fetch hours data
        $hours = $test->get()
            ->mapToGroups(function ($item) {
                return [$item['user_id'] => $item['duration']];
            })
            ->toArray();

        // Fetch company data
        $companyData = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $companyData->name;
        $companyId = $companyData->id;

        // Sort attendance counts by date
        ksort($attendCount);

        // Render the view
        $modaldata = view('reports.siteWiseGuardReportView', [
            'sites' => $sites,
            'subType' => $subType,
            'users' => $users,
            'data' => $data,
            'weekoffs' => $weekoffs,
            'names' => $names,
            'attendCount' => $attendCount,
            'date' => $date,
            'daysCount' => $daysCount,
            'startDatee' => $startDatee,
            'companyName' => $companyName,
            'fromDate' => $startDate,
            'toDate' => $endDate,
            'currentDate' => $currentDate,
            'companyId' => $companyId,
            'dateFormat' => $dateFormat,
            'geofences' => $geofences,
            'siteName' => $siteName,
            'attendanceSubType' => $attendanceSubType,
            'guard' => $guard,
            'type' => $type,
            'hours' => $hours,
            'clientName' => $clientName,
            'client' => $client,
            'generatedOn' => $this->generatedOn

        ])->render();

        echo $modaldata;
    }


    private function guardReportMethod($guard, $client, $startDate, $endDate, $user, $subType, $daysCount, $datetime1, $datetime2, $attendanceSubType)
    {
        // When guard/employee is single
        // dump('When guard/employee is single' , $daysCount);

        $GuardDetails = DB::table('attendance')->where('user_id', $guard);
        $subType = "Employee Attendance Report";


        // Fetch distinct dates the guard was present
        $datePresent = Attendance::where('user_id', $guard)
            ->whereBetween('dateFormat', [$startDate, $endDate])
            ->select('dateFormat')
            ->distinct()
            ->pluck('dateFormat')
            ->toArray();

        // Fetch user info
        $userinfo = SiteAssign::where('user_id', $guard)->first();
        $days = json_decode($userinfo->weekoff);

        // Calculate week-off dates
        $weekOffDates = [];
        if ($days && count($days) > 0) {
            foreach ($days as $value) {
                $utility = new GetDays();
                $mondays = $utility->getDays($value, $datetime1->format('F'), $datetime1->format('Y'), $datetime2->format('F'), $datetime2->format('Y'));
                foreach ($mondays as $monday) {
                    if (is_object($monday)) {
                        $monday = $monday->format('Y-m-d');
                    }
                    if (strtotime($monday) > strtotime(date('Y-m-d')) || strtotime($monday) < strtotime($startDate)) {
                        continue;
                    }
                    $weekOffDates[] = date('d-m-Y', strtotime($monday));
                }

                // Add the next week-off date if within the range
                if (count($weekOffDates) > 0) {
                    $lastSundayDate = date('d-m-Y', strtotime('+7 days ' . $weekOffDates[count($weekOffDates) - 1]));
                    $lastSunday = new DateTime($lastSundayDate, new DateTimeZone('Asia/Kolkata'));
                    if ($lastSunday <= $datetime2) {
                        $weekOffDates[] = $lastSundayDate;
                    }
                }
            }
        }

        // Fetch attendance data grouped by date
        $data = Attendance::where('user_id', $guard)
            ->whereBetween('dateFormat', [$startDate, $endDate])
            ->get()
            ->groupBy('dateFormat');

        // dump('Data for guard:', $data, $guard, $startDate, $endDate);

        // Calculate total actual and GPS time
        $attendance = Attendance::where('user_id', $guard)
            ->whereBetween('dateFormat', [$startDate, $endDate])
            ->selectRaw('sum(TIME_TO_SEC(time_calculation)) as actualTime, sum(TIME_TO_SEC(gpsTime)) as gpsTime')
            ->first();

        $actualTime = $attendance->actualTime;
        $gpsTime = $attendance->gpsTime;

        $hours = floor($actualTime / 3600);
        $mins = floor(($actualTime / 60) % 60);

        $gpshours = floor($gpsTime / 3600);
        $gpsmins = floor(($gpsTime / 60) % 60);

        $actualTimeformat = sprintf('%02dhr %02dmin', $hours, $mins);
        $gpsTimeformat = sprintf('%02dhr %02dmin', $gpshours, $gpsmins);

        // Fetch company details
        $company = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $company->name;

        $clientData = SiteAssign::where('user_id', $guard)->where('role_id', 3)->first();
        $clientName = $clientData->client_name;
        $siteName = $clientData->site_name;

        $modaldata = view('AttendanceReport.guardReportView', [
            'subType' => $subType,
            'companyName' => $companyName,
            'fromDate' => $startDate,
            'toDate' => $endDate,
            'daysCount' => $daysCount,
            'data' => $data,
            'datePresent' => $datePresent,
            'weekOffDates' => $weekOffDates,
            'actualTimeformat' => $actualTimeformat,
            'gpsTimeformat' => $gpsTimeformat,
            'guardId' => $guard,
            'attendanceSubType' => $attendanceSubType,
            'clientName' => $clientName,
            'siteName' => $siteName,
            'client' => $client,
            'generatedOn' => $this->generatedOn,
            'flag' => 'guard_report_view',
            'siteClientNames' => 'N/A'
        ])->render();

        echo $modaldata;
    }


    private function onSiteAttendanceMethod($attendanceSubType, $geofences, $client, $user, $startDate, $endDate, $dateRange)
    {
        $companyId = $user->company_id;
        $roleId = $user->role_id;
        $geofencesNew = $geofences ?? 'all'; // Default to 'all' if null
        $subType = 'On site Attendance Report';
        $reportMonth = $startDate . " to " . $endDate;

        // Fetch company details
        $company = CompanyDetails::find($companyId);
        $companyName = $company->name;

        // Determine site IDs based on role and client
        $siteQuery = SiteAssign::where('company_id', $companyId);

        // $siteEu = SiteAssign::where('client_id', $client)->where('site_id', 490);

        $siteInfo = [
            'name' => 'All Sites',
            'client' => [
                'id' => $client,
                'name' => 'All Clients'
            ]
        ];

        if ($client !== 'all' && $geofences == 'all') {
            $clientDetails = ClientDetails::find($client);
            $siteInfo['client']['name'] = $clientDetails->name ?? 'Unknown Client';
            // dump($siteInfo, "siteInfo");
        } else {
            $clientDetails = ClientDetails::find($client);
            $site = SiteAssign::where('site_id', $geofences)->first();
            // dd($site->site_name , "site");
            $siteInfo['client']['name'] = $clientDetails->name ?? 'All Client';
            $siteInfo['name'] = $site->site_name ?? 'All Site';
            // dump($siteInfo, "info when cli all");
        }



        // Fetch site IDs for the user (if role_id is 2)
        $siteIds = $user->role_id == 2
            ? SiteAssign::where('user_id', $user->id)
                ->pluck('site_id')
                ->map(function ($item) {
                    return is_string($item) ? json_decode($item, true) : $item;
                })
                ->flatten()
                ->unique()
                ->values()
            : collect();
        // dump($siteIds , "site eu");


        $siteIdsTest = SiteAssign::where('user_id', $user->id)
            ->pluck('site_id');

        // dump($siteIdsTest, "test");



        if ($roleId === 1) {
            // Admin role can view all sites, optionally filtered by client


            $userIds = SiteAssign::where('client_id', $client)->pluck('user_id')->toArray();
            // dd($client , $userIds , "user ids");
            $siteArray = SiteDetails::where('client_id', $client)->pluck('id')->toArray();
            // dd($siteArray , "siteArray");
            $supervisorIds = Users::where([['users.company_id', '=', $user->company_id], ['users.role_id', '=', 2]])
                ->leftJoin('site_assign as site', 'users.id', '=', 'site.user_id')
                ->where(function ($query) use ($siteArray) {
                    foreach ($siteArray as $siteId) {
                        $query->orWhereRaw('JSON_CONTAINS(site.site_id, ?)', [json_encode([$siteId])]);
                    }
                })->pluck('users.id')->toArray();
            // dump($userIds, $supervisorIds, "ids");

            $userIds = array_merge($userIds, $supervisorIds);
            // dump($userIds , "user ids");



            if ($client !== 'all') {
                // dump('here');

                // dump($siteQuery->pluck('user_name')->toArray());
                $siteQuery->when(
                    $geofences !== 'all' && $geofences !== null,
                    fn($query) => $query->where('site_id', $geofences)

                )
                    ->whereIn('user_id', $userIds);
                // ->where('company_id', $user->company_id);


                // dd($siteQuery->pluck('user_name')->toArray());
            }
        } else if ($user->role_id == 7) {

            // dump('in else if');
            // Fetch site assignments for the user
            $siteAssigned = SiteAssign::where('user_id', $user->id)->first();

            // $client_ids = json_decode($siteAssigned, true);
            $client_ids = json_decode($siteAssigned['site_id'], true);
            // dd($siteAssigned ,  $client_ids,"site Assigned");

            if ($client == 'all') {
                // dd('not loading 2');
                $siteArray = SiteDetails::whereIn('client_id', $client_ids)->pluck('id')->toArray();
                $userIds = SiteAssign::whereIn('client_id', $client_ids)->pluck('user_id')->toArray();
            } else {

                $siteArray = SiteDetails::where('client_id', $client)->pluck('id')->toArray();
                $userIds = SiteAssign::where('client_id', $client)->pluck('user_id')->toArray();
                // dd('not loading' , $siteArray , $userIds);
            }


            // dd($siteArray , "array");

            $supervisorIds = Users::where([['users.company_id', '=', $user->company_id], ['users.role_id', '=', 2]])
                ->leftJoin('site_assign as site', 'users.id', '=', 'site.user_id')
                ->where(function ($query) use ($siteArray) {
                    foreach ($siteArray as $siteId) {
                        $query->orWhereRaw('JSON_CONTAINS(site.site_id, ?)', [json_encode([$siteId])]);
                    }
                })->pluck('site.user_id')->toArray();


            // dd($userIds, "user ids");

            $site_UserIds = array_merge($userIds, $supervisorIds);

            // dump($siteQuery->pluck('user_name')->toArray());
            $siteQuery->when(
                $geofences !== 'all' && $geofences !== null,
                fn($query) => $query->where('site_id', $geofences)

            )
                ->whereIn('user_id', $site_UserIds);

            // dd($siteQuery->pluck('client_id')->toArray());
            // dd($siteQuery->pluck('user_id')->toArray());
        } else {
            // Supervisor role can only view sites assigned to them
            // dump('in else');
            // dump($siteIds , "site ids");
            $siteQuery->whereIn('site_id', $siteIds);
            if ($geofences !== 'all' && $geofences !== null) {
                // dump(10);
                $siteQuery->where('site_id', $geofences);
            }
            ;
            // dump($siteQuery ->get() , "query");

        }


        $site_UserIds = $siteQuery
            ->pluck('user_id')
            ->map(fn($item) => is_string($item) ? json_decode($item, true) : $item)
            ->flatten()
            ->unique()
            ->values();
        // dump($site_UserIds, "site usr ids");

        // Fetch client details
        $clientDetails = ClientDetails::where('company_id', $companyId)->first();

        // Fetch attendance data
        $attendanceQuery = Attendance::where('emergency_attend', 1)
            ->where('company_id', $companyId)
            // ->whereBetween('dateFormat', $dateRange);
            ->whereBetween('dateFormat', [$startDate, $endDate]);


        if ($roleId === 1 || $roleId === 7) {
            // dump('role 1 and 7');
            // Admin role attendance filtering
            $attendanceQuery
                ->whereIn('user_id', $site_UserIds);
            // dd($site_UserIds , "site ids");
            // dd($attendanceQuery->pluck('client_name')->toArray() , "data11");
        } else {
            // Supervisor role attendance filtering
            $attendanceQuery->when(
                $geofences == 'all',
                fn($query) => $query->whereIn('site_id', $siteIds)
            )
                ->when($geofences !== 'all', fn($query) => $query->where('site_id', $geofences));
        }

        $data = $attendanceQuery->get();
        // dump($data, "data");
        // dump($site_UserIds , "site usr ids");
        // dd($data->pluck('user_id')->toArray() , "data");

        if (!isset($data) || count($data) === 0) {
            // dd('stop');
            return 'No records found';
        }


        // Render the modal view if data exists
        if ($data->isNotEmpty()) {
            $modalData = view('AttendanceReport/onSiteAttendanceReportView', [
                'subType' => $subType,
                'data' => $data,
                'geofences' => $geofencesNew,
                'dateRange' => $reportMonth,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'companyName' => $companyName,
                'site' => $geofences,
                'client' => $client,
                'clientName' => $siteInfo['client']['name'],
                'siteName' => $siteInfo['name'],
                'generatedOn' => $this->generatedOn

            ])->render();

            echo $modalData;
        } else {
            return 'error';
        }
    }


    private function forgetToMarkExitMethod($attendanceSubType, $geofences, $client, $datetime1, $datetime2, $user, $startDate, $endDate, $companyName)
    {

        if ($attendanceSubType !== 'forgetToMarkExit') {
            return response()->json(['error' => 'Invalid request type'], 400);
        }

        $date = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));


        $subType = 'Forget To Mark Exit';
        $reportMonth = date('d M Y', strtotime($startDate)) . " to " . date('d M Y', strtotime($endDate));

        // Initialize site and client info
        $siteInfo = [
            'name' => 'All Sites',
            'client' => [
                'id' => $client,
                'name' => 'All Clients'
            ]
        ];

        // dd($user, "company_id");
        // Base attendance query


        // $attendanceQuery = Attendance::where('company_id', $user->company_id)
        //     ->whereNull('exit_time')
        //     ->whereBetween('dateFormat', [$startDate, $endDate]);

        $attendanceQuery = Attendance::where('attendance.company_id', $user->company_id)
            ->leftjoin('site_assign as site', 'attendance.user_id', '=', 'site.user_id')
            ->whereBetween('attendance.dateFormat', [$startDate, $endDate])
            ->whereNotIn('site.role_id', [1, 2, 7, 4])
            ->whereNull('attendance.exit_time')
            ->orderBy('attendance.name')
            ->orderBy('attendance.dateFormat');

        // dump($client , $geofences , "site state");
        // Filter based on user role
        if ($user->role_id == 7) {
            $siteQuery = SiteAssign::where('company_id', $user->company_id);

            // dump('in else if');
            // Fetch site assignments for the user
            $siteAssigned = SiteAssign::where('user_id', $user->id)->first();

            // $client_ids = json_decode($siteAssigned, true);
            $client_ids = json_decode($siteAssigned['site_id'], true);
            // dd($siteAssigned ,  $client_ids,"site Assigned");

            if ($client == 'all') {
                $siteArray = SiteDetails::whereIn('client_id', $client_ids)->pluck('id')->toArray();
                $userIds = SiteAssign::whereIn('client_id', $client_ids)->pluck('user_id')->toArray();
            } elseif ($client !== 'all' && $geofences == 'all') {

                $siteArray = SiteDetails::where('client_id', $client)->pluck('id')->toArray();
                $userIds = SiteAssign::where('client_id', $client)->pluck('user_id')->toArray();
            } else if ($client !== 'all' && $geofences !== 'all') {

                $siteArray = SiteDetails::where('client_id', $client)->pluck('id')->toArray();
                $userIds = SiteAssign::where('site_id', $geofences)->pluck('user_id')->toArray();
            }


            // dd($siteArray , "array");

            $supervisorIds = Users::where([['users.company_id', '=', $user->company_id], ['users.role_id', '=', 2]])
                ->leftJoin('site_assign as site', 'users.id', '=', 'site.user_id')
                ->where(function ($query) use ($siteArray) {
                    foreach ($siteArray as $siteId) {
                        $query->orWhereRaw('JSON_CONTAINS(site.site_id, ?)', [json_encode([$siteId])]);
                    }
                })->pluck('site.user_id')->toArray();


            // dd($userIds, "user ids");

            $site_UserIds = array_merge($userIds, $supervisorIds);

            // dump($site_UserIds, $supervisorIds ,'user id');
            // dump($siteQuery->pluck('user_name')->toArray());
            $siteQuery->when(
                $geofences !== 'all' && $geofences !== null,
                fn($query) => $query->where('site_id', $geofences)

            )
                ->whereIn('user_id', $site_UserIds);

            // dump($siteQuery->pluck('client_id')->toArray(),'clie');
            // dd($siteQuery->pluck('user_id')->toArray());
        } else if ($user->role_id == 1) {
            if ($client == "all") {
                $site_UserIds = SiteAssign::where('company_id', $user->company_id)->whereNotIn('role_id', [1, 2, 4])
                    ->pluck('user_id')->unique();
            } elseif ($client != "all" && $geofences == "all") {
                $site_UserIds = SiteAssign::where('client_id', $client)
                    ->pluck('user_id')->unique();
            } elseif ($client != "all" && $geofences != "all") {
                $site_UserIds = SiteAssign::where('client_id', $client)
                    ->where('site_id', $geofences)
                    ->pluck('user_id')->unique();
                // dump($site_UserIds, "usr id");
            } else {
                $site_UserIds = [];
            }
        } else {
            // dump('the role id is 2');
            $siteIds = $user->role_id == 2
                ? SiteAssign::where('user_id', $user->id)
                    ->pluck('site_id')
                    ->map(function ($item) {
                        return is_string($item) ? json_decode($item, true) : $item;
                    })
                    ->flatten()
                    ->unique()
                    ->values()
                : collect();
            // dump($siteIds, "siteIds arr");

            $site_UserIds_query = SiteAssign::where('company_id', $user->company_id)->whereIn('site_id', $siteIds);
            // dd($site_UserIds);

            if ($client == 'all') {
                $site_UserIds = $site_UserIds_query->pluck('user_id');
                // dump($client, $geofences, $site_UserIds ,"All client");
            } else if ($client !== 'all' && $geofences == 'all') {
                // $attendanceQuery->whereIn('user_id', $site_UserIds_query->pluck('user_id'));
                $site_UserIds = $site_UserIds_query->pluck('user_id');
                // dump($client, $geofences, $site_UserIds ,"client");
            } else if ($client !== 'all' && $geofences !== 'all') {
                $site_UserIds = $site_UserIds_query->where('site_id', $geofences)->pluck('user_id');
            }
        }

        // Handle site and client filtering
        if ($client !== 'all' && $geofences == 'all') {
            $clientDetails = ClientDetails::find($client);
            $siteInfo['client']['name'] = $clientDetails->name ?? 'All Client';
            // dump($siteInfo, "siteInfo");
        } else {
            $clientDetails = ClientDetails::find($client);
            $site = SiteDetails::where('id', $geofences)->first();
            // dd($site->site_name , "site");
            // dump($site );
            $siteInfo['client']['name'] = $clientDetails->name ?? 'All Client';
            $siteInfo['name'] = $site->name ?? 'All site';
            // dump($siteInfo , "siteINfo");
        }


        if (!empty($site_UserIds)) {
            $attendanceQuery->whereIn('attendance.user_id', $site_UserIds);
        }
        // dd($attendanceQuery->get());
        $data = $attendanceQuery->get();
        // dump($data , "Data");


        // dd($data->pluck('client_name')->toArray() , "data");
        // dd($attendanceQuery , "data");

        if ($data->isEmpty()) {
            return response()->json(['error' => 'No records found'], 404);
        }

        return view('AttendanceReport.forgotToExitView', [
            'client' => $client,
            'clientName' => $siteInfo['client']['name'],
            'subType' => $subType,
            'data' => $data,
            'geofences' => $geofences,
            'dateRange' => $reportMonth,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'companyName' => $user->name,
            'date' => $date,
            'site' => $siteInfo['name'],
            'generatedOn' => $this->generatedOn
        ]);
    }

    public function absentReportMethod($attendanceSubType, $user, $startDate, $endDate, $geofences, $guard, $request, $companyName, $client)
    {
        $subType = 'Absent Attendance Report';
        $date = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));

        $userIds = SiteAssign::where('company_id', $user->company_id)
            // ->where('client_id', $request->client)
            ->pluck('user_id')
            ->toArray();
        $attend = Attendance::where('company_id', $user->company_id)
            ->whereIn('user_id', $userIds)
            ->whereBetween('dateFormat', [$startDate, $endDate])
            ->select('user_id')
            ->distinct()
            ->pluck('user_id')
            ->toArray();
        // dd($attend, "attend");

        if ($user->role_id == 2) {

            // dump('the role id is 2');
            $siteIds = $user->role_id == 2
                ? SiteAssign::where('user_id', $user->id)
                    ->pluck('site_id')
                    ->map(function ($item) {
                        return is_string($item) ? json_decode($item, true) : $item;
                    })
                    ->flatten()
                    ->unique()
                    ->values()
                : collect();
            // dump($siteIds, "siteIds arr");

            $site_UserIds_query =
                SiteAssign::where('company_id', $user->company_id)
                    ->whereIn('site_id', $siteIds);
            //  dump($site_UserIds_query->pluck('user_id') , "site user ids");

            if ($client !== 'all' && $geofences == 'all') {
                // dump($client, $geofences, $siteIds, "client");
                // $attendanceQuery->whereIn('user_id', $site_UserIds_query->pluck('user_id'));
                $site_UserIds = $site_UserIds_query
                    ->pluck('user_id');

                // dump($site_UserIds, "site userids 0");
            } else if ($client !== 'all' && $geofences !== 'all') {
                // dump($client, $geofences, "client 1");
                $site_UserIds = $site_UserIds_query
                    ->where('site_id', $geofences)
                    ->pluck('user_id');
                // dump($site_UserIds, 'site_userids 1');
            }

            // dump($site_UserIds, 'site_userids');
            if ($geofences == null || $geofences == 'all') {

                // dump('in geofence is all');
                $attend = Attendance::where('company_id', $user->company_id)
                    ->whereIn('user_id', $site_UserIds)
                    ->whereBetween('dateFormat', [$startDate, $endDate])
                    ->select('user_id')
                    ->distinct()
                    ->pluck('user_id')
                    ->toArray();

                if (empty($attend)) {
                    return 'No records found';
                }

                // dd($userIds, $attend, "attend");

                $data = Users::where('users.company_id', $user->company_id)
                    ->leftjoin('site_assign as site', 'users.id', '=', 'site.user_id')
                    ->whereIn('users.id', $site_UserIds)
                    ->whereNotIn('users.id', $attend)
                    // ->where('users.role_id', 3)
                    ->select('users.*', 'site.site_name as site_name', 'site.client_name as client_name')
                    ->where('users.showUser', 1)
                    ->orderBy('site.client_name', 'ASC')
                    ->orderBy('users.name', 'ASC')
                    ->get();

                // $data = Attendance::where('attendance.company_id', $user->company_id)
                //     ->leftJoin('site_assign as site', 'Attendance.user_id', '=', 'site.user_id')
                //     ->whereIn('Attendance.user_id', $site_UserIds)
                //     ->where('Attendance.role_id', 3)
                //     ->whereNotBetween('Attendance.dateFormat', [$startDate, $endDate])
                //     ->orderBy('site.user_name', 'ASC')
                //     ->get();

            } elseif ($geofences != 'all' && $guard == 'all') {

                // dump('geofences is not all');
                $attend = Attendance::where('company_id', $user->company_id)
                    ->whereIn('user_id', $site_UserIds)
                    ->whereBetween('dateFormat', [$startDate, $endDate])
                    ->select('user_id')
                    ->distinct()
                    ->pluck('user_id')
                    ->toArray();

                // dump($attend , "attend");
                if (empty($attend)) {
                    return 'No records found';
                }

                // dd($userIds, $attend, "attend");

                $data = Users::where('users.company_id', $user->company_id)
                    ->leftjoin('site_assign as site', 'users.id', '=', 'site.user_id')
                    ->whereIn('users.id', $site_UserIds)
                    ->whereNotIn('users.id', $attend)
                    // ->where('users.role_id', 3)
                    ->select('users.*', 'site.site_name as site_name', 'site.client_name as client_name')
                    ->where('users.showUser', 1)
                    ->orderBy('site.client_name', 'ASC')
                    ->orderBy('users.name', 'ASC')
                    ->get();


                // $data = Attendance::where('attendance.company_id', $user->company_id)
                //     ->leftJoin('site_assign as site', 'Attendance.user_id', '=', 'site.user_id')
                //     // ->where('site.client_id', $client)
                //     ->whereIn('Attendance.user_id', $site_UserIds)
                //     ->where('Attendance.role_id', 3)
                //     ->where('site.site_id', $geofences)
                //     ->whereNotBetween('Attendance.dateFormat', [$startDate, $endDate])
                //     ->orderBy('site.user_name', 'ASC')
                //     ->get();


                // $site = SiteDetails::where('id', $geofences)->value('site_name') ?? 'Unknown Site';
            } elseif ($geofences != 'all' && $guard != 'all') {

                // Confirm the guard is assigned to the selected geofence
                $userSiteAssignment = SiteAssign::where('user_id', $guard)
                    ->where('site_id', $request->geofences)
                    ->first();

                // If the guard is not assigned to the site, return no records
                if (!$userSiteAssignment) {
                    return 'No records found';
                }

                // Get attendance for that guard
                $attend = Attendance::where('company_id', $user->company_id)
                    ->where('user_id', $guard)
                    ->whereBetween('dateFormat', [$startDate, $endDate])
                    ->pluck('user_id')
                    ->toArray();

                $data = Users::where('users.company_id', $user->company_id)
                    ->leftJoin('site_assign as site', 'users.id', '=', 'site.user_id')
                    ->where('users.id', $guard)
                    ->whereNotIn('users.id', $attend)
                    ->where('site.site_id', $request->geofences) // Ensure the guard is part of the selected geofence
                    ->select('users.*', 'site.site_name as site_name', 'site.client_name as client_name')
                    ->where('users.showUser', 1)
                    ->orderBy('site.client_name', 'ASC')
                    ->orderBy('users.name', 'ASC')
                    ->get();

                if ($data->isEmpty()) {
                    return 'No records found';
                }

                // $site = SiteDetails::where('id', $geofences)->value('site_name') ?? 'Unknown Site';
            } else {
                $data = collect();
                $site = 'No Site';
            }
        } else if ($user->role_id == 7) {
            $siteQuery = SiteAssign::where('company_id', $user->company_id);

            // dump('in else if');
            // Fetch site assignments for the user
            $siteAssigned = SiteAssign::where('user_id', $user->id)->first();

            // $client_ids = json_decode($siteAssigned, true);
            $client_ids = json_decode($siteAssigned['site_id'], true);
            // dd($siteAssigned ,  $client_ids,"site Assigned");

            if ($client == 'all') {
                $siteArray = SiteDetails::whereIn('client_id', $client_ids)->pluck('id')->toArray();
                $userIds = SiteAssign::whereIn('client_id', $client_ids)->where('role_id', 3)
                    ->pluck('user_id')->toArray();

                $attend = Attendance::where('company_id', $user->company_id)
                    ->whereIn('user_id', $userIds)
                    ->whereBetween('dateFormat', [$startDate, $endDate])
                    ->select('user_id')
                    ->distinct()
                    ->pluck('user_id')
                    ->toArray();

                // dd($userIds, $attend, "attend");

                $data = Users::where('users.company_id', $user->company_id)
                    ->leftjoin('site_assign as site', 'users.id', '=', 'site.user_id')
                    ->whereIn('users.id', $userIds)
                    ->whereNotIn('users.id', $attend)
                    // ->where('users.role_id', 3)
                    ->select('users.*', 'site.site_name as site_name', 'site.client_name as client_name')
                    ->where('users.showUser', 1)
                    ->orderBy('site.client_name', 'ASC')
                    ->orderBy('users.name', 'ASC')
                    ->get();
            } elseif ($request->geofences == 'all') {

                $siteArray = SiteDetails::where('client_id', $client)->pluck('id')->toArray();
                $userIds = SiteAssign::where('client_id', $client)
                    ->where('role_id', 3)
                    ->pluck('user_id')
                    ->toArray();

                $attend = Attendance::where('company_id', $user->company_id)
                    ->whereIn('user_id', $userIds)
                    ->whereBetween('dateFormat', [$startDate, $endDate])
                    ->select('user_id')
                    ->distinct()
                    ->pluck('user_id')
                    ->toArray();

                // dd($userIds, $attend, "attend");

                $data = Users::where('users.company_id', $user->company_id)
                    ->leftjoin('site_assign as site', 'users.id', '=', 'site.user_id')
                    ->whereIn('users.id', $userIds)
                    ->whereNotIn('users.id', $attend)
                    // ->where('users.role_id', 3)
                    ->select('users.*', 'site.site_name as site_name', 'site.client_name as client_name')
                    ->where('users.showUser', 1)
                    ->orderBy('site.client_name', 'ASC')
                    ->orderBy('users.name', 'ASC')
                    ->get();

                // $data = Attendance::where('attendance.company_id', $user->company_id)
                //     ->leftJoin('site_assign as site', 'Attendance.user_id', '=', 'site.user_id')
                //     ->where('site.client_id', $client)
                //     ->whereIn('Attendance.user_id', $userIds)
                //     ->where('Attendance.role_id', 3)
                //     ->whereNotBetween('Attendance.dateFormat', [$startDate, $endDate])
                //     ->orderBy('site.user_name', 'ASC')
                //     ->get();

            } elseif ($geofences != 'all' && $guard == 'all') {
                $userIds = SiteAssign::where('site_id', $request->geofences)->where('role_id', 3)->pluck('user_id')->toArray();


                $attend = Attendance::where('company_id', $user->company_id)
                    ->whereIn('user_id', $userIds)
                    ->whereBetween('dateFormat', [$startDate, $endDate])
                    ->select('user_id')
                    ->distinct()
                    ->pluck('user_id')
                    ->toArray();

                // dd($userIds, $attend, "attend");

                $data = Users::where('users.company_id', $user->company_id)
                    ->leftjoin('site_assign as site', 'users.id', '=', 'site.user_id')
                    ->whereIn('users.id', $userIds)
                    ->whereNotIn('users.id', $attend)
                    // ->where('users.role_id', 3)
                    ->select('users.*', 'site.site_name as site_name', 'site.client_name as client_name')
                    ->where('users.showUser', 1)
                    ->orderBy('site.client_name', 'ASC')
                    ->orderBy('users.name', 'ASC')
                    ->get();

                // $attend = Attendance::where('company_id', $user->company_id)
                //     ->whereIn('user_id', $userIds)
                //     ->whereBetween('dateFormat', [$startDate, $endDate])
                //     ->pluck('user_id')
                //     ->toArray();

                // $data = Attendance::where('attendance.company_id', $user->company_id)
                //     ->leftJoin('site_assign as site', 'Attendance.user_id', '=', 'site.user_id')
                //     ->where('site.client_id', $client)
                //     // ->whereIn('user_id', $userIds)
                //     ->where('Attendance.role_id', 3)
                //     ->where('site.site_id', $geofences)
                //     ->whereNotBetween('Attendance.dateFormat', [$startDate, $endDate])
                //     ->orderBy('site.user_name', 'ASC')
                //     ->get();

                if ($data->isEmpty()) {
                    return 'No records found';
                }
            } elseif ($geofences != 'all' && $guard != 'all') {


                // Confirm the guard is assigned to the selected geofence
                $userSiteAssignment = SiteAssign::where('user_id', $guard)
                    ->where('site_id', $request->geofences)
                    ->first();

                // If the guard is not assigned to the site, return no records
                if (!$userSiteAssignment) {
                    return 'No records found';
                }

                // Get attendance for that guard
                $attend = Attendance::where('company_id', $user->company_id)
                    ->where('user_id', $guard)
                    ->whereBetween('dateFormat', [$startDate, $endDate])
                    ->pluck('user_id')
                    ->toArray();

                $data = Users::where('users.company_id', $user->company_id)
                    ->leftJoin('site_assign as site', 'users.id', '=', 'site.user_id')
                    ->where('users.id', $guard)
                    ->whereNotIn('users.id', $attend)
                    ->where('site.site_id', $request->geofences) // Ensure the guard is part of the selected geofence
                    ->select('users.*', 'site.site_name as site_name', 'site.client_name as client_name')
                    ->where('users.showUser', 1)
                    ->orderBy('site.client_name', 'ASC')
                    ->orderBy('users.name', 'ASC')
                    ->get();

                if ($data->isEmpty()) {
                    return 'No records found';
                }
            }

            if ($data->isEmpty()) {
                return 'No records found';
            }
        } else {

            if ($request->client == 'all') {
                if (empty($attend)) {
                    return 'No records found';
                }
                $userIds = SiteAssign::where('company_id', $user->company_id)
                    ->where('role_id', 3)
                    ->pluck('user_id')
                    ->toArray();

                $attend = Attendance::where('company_id', $user->company_id)
                    ->whereIn('user_id', $userIds)
                    ->whereBetween('dateFormat', [$startDate, $endDate])
                    ->select('user_id')
                    ->distinct()
                    ->pluck('user_id')
                    ->toArray();

                // dd($userIds, $attend, "attend");

                $data = Users::where('users.company_id', $user->company_id)
                    ->leftjoin('site_assign as site', 'users.id', '=', 'site.user_id')
                    ->whereIn('users.id', $userIds)
                    ->whereNotIn('users.id', $attend)
                    // ->where('users.role_id', 3)
                    ->select('users.*', 'site.site_name as site_name', 'site.client_name as client_name')
                    ->where('users.showUser', 1)
                    ->orderBy('site.client_name', 'ASC')
                    ->orderBy('users.name', 'ASC')
                    ->get();
                // dd($data->pluck('user_id')->distinct()
                // ->toArray(), $user->company_id, $startDate, $endDate, "data");

                if ($data->isEmpty()) {
                    return 'No records found';
                }
            } elseif ($request->geofences == 'all') {
                // dd('when geo is all');

                $userIds = SiteAssign::where('company_id', $user->company_id)
                    ->where('client_id', $request->client)
                    ->pluck('user_id')
                    ->toArray();
                // dump($userIds, "user ids");

                $attend = Attendance::where('company_id', $user->company_id)
                    ->whereIn('user_id', $userIds)
                    ->whereBetween('dateFormat', [$startDate, $endDate])
                    ->select('user_id')
                    ->distinct()
                    ->pluck('user_id')
                    ->toArray();
                // dd($attend , "attend");

                $data = Users::where('users.company_id', $user->company_id)
                    ->leftjoin('site_assign as site', 'users.id', '=', 'site.user_id')
                    ->whereIn('users.id', $userIds)
                    ->whereNotIn('users.id', $attend)
                    ->where('users.role_id', 3)
                    ->select('users.*', 'site.site_name as site_name', 'site.client_name as client_name')
                    ->where('users.showUser', 1)
                    ->orderBy('users.name', 'ASC')
                    ->get();

                if ($data->isEmpty()) {
                    return 'No records found';
                }
            } elseif ($geofences != 'all' && $guard == 'all') {
                $userIds = SiteAssign::where('site_id', $request->geofences)
                    ->pluck('user_id')
                    ->toArray();
                $attend = Attendance::where('company_id', $user->company_id)
                    ->whereIn('user_id', $userIds)
                    ->whereBetween('dateFormat', [$startDate, $endDate])
                    ->pluck('user_id')
                    ->toArray();

                $data = Users::where('users.company_id', $user->company_id)
                    ->leftjoin('site_assign as site', 'users.id', '=', 'site.user_id')
                    ->whereIn('users.id', $userIds)
                    ->whereNotIn('users.id', $attend)
                    // ->where('users.role_id', 3)
                    ->select('users.*', 'site.site_name as site_name', 'site.client_name as client_name')
                    ->where('users.showUser', 1)
                    ->orderBy('site.client_name', 'ASC')
                    ->orderBy('users.name', 'ASC')
                    ->get();

                if ($data->isEmpty()) {
                    return 'No records found';
                }
            } elseif ($geofences != 'all' && $guard != 'all') {

                // Confirm the guard is assigned to the selected geofence
                $userSiteAssignment = SiteAssign::where('user_id', $guard)
                    ->where('site_id', $request->geofences)
                    ->first();

                // If the guard is not assigned to the site, return no records
                if (!$userSiteAssignment) {
                    return 'No records found';
                }

                // Get attendance for that guard
                $attend = Attendance::where('company_id', $user->company_id)
                    ->where('user_id', $guard)
                    ->whereBetween('dateFormat', [$startDate, $endDate])
                    ->pluck('user_id')
                    ->toArray();

                $data = Users::where('users.company_id', $user->company_id)
                    ->leftJoin('site_assign as site', 'users.id', '=', 'site.user_id')
                    ->where('users.id', $guard)
                    ->whereNotIn('users.id', $attend)
                    ->where('site.site_id', $request->geofences) // Ensure the guard is part of the selected geofence
                    ->select('users.*', 'site.site_name as site_name', 'site.client_name as client_name')
                    ->where('users.showUser', 1)
                    ->orderBy('site.client_name', 'ASC')
                    ->orderBy('users.name', 'ASC')
                    ->get();

                if ($data->isEmpty()) {
                    return 'No records found';
                }
            }
        }


        if ($data->isEmpty()) {
            return 'No records found';
        }
        // return view('AttendanceReport.absentReportView', compact('geofences', 'guard', 'subType', 'data', 'attendanceSubType', 'date', 'startDate', 'endDate', 'companyName', 'client' ));
        return view('AttendanceReport.absentReportView')
            ->with('geofences', $request->geofences)
            ->with('guard', $request->guard)
            ->with('subType', $subType)
            ->with('data', $data)
            ->with('attendanceSubType', $attendanceSubType)
            ->with('date', $date)
            ->with('fromdate', $startDate)
            ->with('todate', $endDate)
            ->with('companyName', $companyName)
            ->with('client', $request->client)
            ->with('generatedOn', $this->generatedOn)
            ->render();
    }


    private function generateLateAttendanceReport($request, $attendanceSubType, $geofences, $client, $user, $startDate, $endDate, $guard, $dateRange)
    {


        $subType = 'Late Attendance Report';
        $data = collect(); // Initialize empty data collection
        $site = 'No Site';


        // $data = Attendance::where('attendance.company_id', $user->company_id)
        // ->leftjoin('site_assign as site', 'attendance.user_id', '=', 'site.user_id')
        // ->whereBetween('attendance.dateFormat', [$startDate, $endDate])
        // ->whereNotNull('attendance.lateTime')
        // ->orderBy('attendance.name')
        // ->orderBy('attendance.dateFormat')
        // ->get();


        $attendanceQuery = Attendance::where('attendance.company_id', $user->company_id)
            ->leftjoin('site_assign as site', 'attendance.user_id', '=', 'site.user_id')
            ->whereBetween('attendance.dateFormat', [$startDate, $endDate])
            ->whereNotNull('attendance.lateTime')
            ->where('site.role_id', 3)
            ->orderBy('attendance.name')
            ->orderBy('attendance.dateFormat');

        if ($user->role_id == 7) {
            $siteQuery = SiteAssign::where('company_id', $user->company_id);


            // dump('in else if');
            // Fetch site assignments for the user
            $siteAssigned = SiteAssign::where('user_id', $user->id)->first();

            // $client_ids = json_decode($siteAssigned, true);
            $client_ids = json_decode($siteAssigned['site_id'], true);
            // dd($siteAssigned ,  $client_ids,"site Assigned");

            if ($client == 'all') {
                $siteArray = SiteDetails::whereIn('client_id', $client_ids)->pluck('id')->toArray();
                $userIds = SiteAssign::whereIn('client_id', $client_ids)->pluck('user_id')->toArray();
            } else {

                $siteArray = SiteDetails::where('client_id', $client)->pluck('id')->toArray();
                $userIds = SiteAssign::where('client_id', $client)->pluck('user_id')->toArray();
            }


            // dd($siteArray , "array");

            $supervisorIds = Users::where([['users.company_id', '=', $user->company_id], ['users.role_id', '=', 2]])
                ->leftJoin('site_assign as site', 'users.id', '=', 'site.user_id')
                ->where(function ($query) use ($siteArray) {
                    foreach ($siteArray as $siteId) {
                        $query->orWhereRaw('JSON_CONTAINS(site.site_id, ?)', [json_encode([$siteId])]);
                    }
                })->pluck('site.user_id')->toArray();


            // dd($userIds, "user ids");

            $userIds = array_merge($userIds, $supervisorIds);

            // dd($site_UserIds, "ids");
            // dump($siteQuery->pluck('user_name')->toArray());
            $siteQuery->when(
                $geofences !== 'all' && $geofences !== null,
                fn($query) => $query->where('site_id', $geofences)

            )
                ->whereIn('user_id', $userIds);

            // dd($siteQuery->pluck('client_id')->toArray());
            // dd($siteQuery->pluck('user_id')->toArray());
            if ($client == 'all') {
                // Case 1: All clients
                // dump('cli is all');
                $data = $attendanceQuery
                    ->whereIn('attendance.user_id', $userIds)
                    ->get();

                // dd($data->pluck('client_name')->toArray());
            } elseif ($geofences == 'all') {
                // Case 2: All sites for a specific client
                $userIds = SiteAssign::where('company_id', $user->company_id)
                    ->where('client_id', $client)
                    ->pluck('user_id');
                // dump($geofences, "geofences");
                $data = $attendanceQuery
                    ->whereIn('attendance.user_id', $userIds)
                    ->get();
            } elseif ($guard == 'all') {
                // Case 3: All guards for a specific site
                $userIds = SiteAssign::where('company_id', $user->company_id)
                    ->where('site_id', $geofences)
                    ->pluck('user_id');

                $data = $attendanceQuery
                    ->whereIn('attendance.user_id', $userIds)
                    ->get();
            } elseif ($guard != 'all') {
                // Case 4: Specific guard for a specific site
                $data = $attendanceQuery
                    ->where('attendance.user_id', $guard)
                    ->get();
            }
        } else if ($user->role_id == 2) {

            // dump('the role id is 2');
            $siteIds = $user->role_id == 2
                ? SiteAssign::where('user_id', $user->id)
                    ->pluck('site_id')
                    ->map(function ($item) {
                        return is_string($item) ? json_decode($item, true) : $item;
                    })
                    ->flatten()
                    ->unique()
                    ->values()
                : collect();
            // dump($siteIds, "siteIds arr");

            $site_UserIds_query = SiteAssign::where('company_id', $user->company_id)->whereIn('site_id', $siteIds);
            // dd($site_UserIds);

            if ($client == 'all') {
                // will not be applicable in the supervisor login
                $site_UserIds = $site_UserIds_query->pluck('user_id');
                $data = $attendanceQuery->where('attendance.user_id', $site_UserIds)->get();
            } else if ($client !== 'all' && $geofences == 'all') {
                // $attendanceQuery->whereIn('user_id', $site_UserIds_query->pluck('user_id'));
                $site_UserIds = $site_UserIds_query->pluck('user_id');
                // dump($client, $geofences, $site_UserIds, "client");
                $data = $attendanceQuery->whereIn('attendance.user_id', $site_UserIds)->get();
                // dd($data->pluck('user_id')->toArray() , "daata");
            } else if ($client !== 'all' && $geofences !== 'all' && $guard == 'all') {
                // dump('in 3rd' , $geofences , );
                $site_UserIds = $site_UserIds_query->where('site_id', $geofences)->pluck('user_id');
                // dump( $site_UserIds , "site user ids");
                $data = $attendanceQuery->whereIn('attendance.user_id', $site_UserIds)->get();
            } elseif ($geofences != 'all' && $guard != 'all') {
                // Case 3: Single site, single employee
                // dd($guard , "guard");
                // dd('in eles if last');
                $data = $attendanceQuery
                    ->where('attendance.user_id', $guard)
                    ->get();

                $siteDetails = SiteDetails::find($geofences);
                $site = $siteDetails ? $siteDetails->site_name : 'Unknown Site';
            }
        } else {
            // Other roles (e.g., role ID != 2)
            if ($client == 'all') {
                // Case 1: All clients
                // dump('cli is all');
                $data = $attendanceQuery
                    ->get();
            } elseif ($geofences == 'all') {
                // Case 2: All sites for a specific client
                $userIds = SiteAssign::where('company_id', $user->company_id)
                    ->where('client_id', $client)
                    ->pluck('user_id');
                // dump($geofences, "geofences" , $guard);
                $data = $attendanceQuery
                    ->whereIn('attendance.user_id', $userIds)
                    ->get();
            } elseif ($guard == 'all') {
                // Case 3: All guards for a specific site
                // dump($geofences, "geofences", $guard, "ono");
                $userIds = SiteAssign::where('company_id', $user->company_id)
                    ->where('site_id', $geofences)
                    ->pluck('user_id');

                $data = $attendanceQuery
                    ->whereIn('attendance.user_id', $userIds)
                    ->get();
            } elseif ($guard != 'all') {
                // Case 4: Specific guard for a specific site
                $data = $attendanceQuery
                    ->where('attendance.user_id', $guard)
                    ->get();
            }
        }

        // dd($data->pluck('role_id')->toArray() , "data");
        if ($data->isEmpty()) {
            return 'No records found';
        }

        // dd($data->pluck('client_name')->toArray() , "data");

        if ($user->role_id !== 2) {
            // dd($client , 'all');
            $clientName = ClientDetails::where('company_id', $user->company_id)->where('id', $client)->first();
            $clientName = ($client !== 'all') ? $clientName->name : 'All';

            $siteName = SiteDetails::where('company_id', $user->company_id)->where('id', $geofences)->first();

            $siteName = ($client !== 'all' && $geofences !== 'all') ? $siteName->name : 'All';
        } else {
            $clientName = '';
            $siteName = SiteDetails::where('company_id', $user->company_id)->where('id', $geofences)->first();

            $siteName = ($geofences !== 'all') ? $siteName->name : 'All';
        }

        return view('AttendanceReport.lateReportView', [
            'geofences' => $geofences,
            'guard' => $guard,
            'subType' => $subType,
            'data' => $data,
            'attendanceSubType' => 'lateReport',
            'date' => $dateRange,
            'fromdate' => $startDate,
            'todate' => $endDate,
            'companyName' => CompanyDetails::find($user->company_id)->name ?? 'Unknown Company',
            'client' => $client,
            'clientName' => $clientName,
            'siteName' => $siteName,
            'generatedOn' => $this->generatedOn

        ]);
    }

    private function supervisorAttendanceReportMethod($subType, $interval, $daysCount, $user, $geofences, $client, $dateRange, $guard, $request, $currentDate)
    {
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];
        $startDatee = date('d-m-Y', strtotime($startDate));

        $daysCount = (int) $interval->format('%a') + 1;
        $reportMonth = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));
        $clientName = "";


        // dump('in supervisor' , $request->supervisorSelect);
        $site_UserIds = [];
        if ($user->role_id == 1) {

            if ($request->supervisorSelect == "all") {
                $site_UserIds = SiteAssign::where('company_id', $user->company_id)->where('role_id', 2)->get()->pluck('user_id')->unique();
            } else {
                // dump('here');
                $site_UserIds = SiteAssign::where('company_id', $user->company_id)->where('user_id', $request->supervisorSelect)->where('role_id', 2)->get()->pluck('user_id')->unique();
            }
        } else if ($user->role_id == 7) {
            $siteQuery = SiteAssign::where('company_id', $user->company_id);


            // dump('in else if');
            // Fetch site assignments for the user
            $siteAssigned = SiteAssign::where('user_id', $user->id)->first();

            // $client_ids = json_decode($siteAssigned, true);
            $client_ids = json_decode($siteAssigned['site_id'], true);
            // dd($siteAssigned ,  $client_ids,"site Assigned");


            if ($request->supervisorSelect == 'all') {
                $siteArray = SiteDetails::whereIn('client_id', $client_ids)->pluck('id')->toArray();
                $supervisorIds = Users::where([['users.company_id', '=', $user->company_id], ['users.role_id', '=', 2]])
                    ->leftJoin('site_assign as site', 'users.id', '=', 'site.user_id')
                    ->where(function ($query) use ($siteArray) {
                        foreach ($siteArray as $siteId) {
                            $query->orWhereRaw('JSON_CONTAINS(site.site_id, ?)', [json_encode([$siteId])]);
                        }
                    })->pluck('site.user_id')->toArray();
                $site_UserIds = $supervisorIds;

                //    dd($site_UserIds, $client_ids, "ids");
            } else {
                $site_UserIds = SiteAssign::where('company_id', $user->company_id)->where('user_id', $request->supervisorSelect)->where('role_id', 2)->get()->pluck('user_id')->unique();

                // dump('in else', $site_UserIds, $request->supervisorSelect);
            }


            // dd($siteArray , "array");



            // dd($siteArray , $supervisorIds , "ids");
            // dd($userIds, "user ids");


            // dd($site_UserIds, "ids");
            // dump($siteQuery->pluck('user_name')->toArray());
            $siteQuery->when(
                $geofences !== 'all' && $geofences !== null,
                fn($query) => $query->where('site_id', $geofences)

            )
                ->whereIn('user_id', $site_UserIds);

            // dd($siteQuery->pluck('client_id')->toArray());
            // dd($siteQuery->pluck('user_id')->toArray());


        }



        // dump($site_UserIds, "siteIds");

        // dump('when supervisor selct is all',$request->supervisorSelect );
        $test = Users::where('users.company_id', $user->company_id)
            ->whereIn('users.id', $site_UserIds)
            ->where('users.role_id', 2)
            ->leftJoin('attendance', function ($join) use ($startDate, $endDate) {
                $join->on('users.id', '=', 'attendance.user_id')
                    ->whereBetween('attendance.dateFormat', [$startDate, $endDate]);
            })
            ->select('users.name as name', 'users.id as user_id', 'attendance.dateFormat as date', 'attendance.site_name as site_name', 'attendance.duration_for_calc as duration', 'attendance.entry_time', 'attendance.exit_date_time', 'attendance.gpsTime')
            ->orderBy('name');


        // dump($test->get() ,   "test"); 
        // dump($site_UserIds , ' test 2');

        $names = $test->groupBy(['user_id', 'name', 'date', 'site_name'])
            ->get()->mapToGroups(function ($item) {
                return [$item['user_id'] => $item['name']];
            })->toArray();

        $sites = $test->groupBy(['user_id', 'name', 'date', 'site_name'])
            ->get()->mapToGroups(function ($item) {
                return [$item['user_id'] => $item['site_name']];
            })->toArray();

        // dd($sites , "sites");
        // $hours = $test->groupBy(['user_id', 'duration', 'date'])
        //     ->get()->mapToGroups(function ($item) {
        //         return [$item['user_id'] => $item['duration']];
        //     })->toArray();

        $userIds = array_unique($test->pluck('user_id')->toArray());

        $weekoffData = SiteAssign::whereIn('user_id', $userIds)->groupBy(['user_id', 'weekoff']);

        $clients = $weekoffData->get()->mapToGroups(function ($item) {
            return [$item['user_id'] => $item['client_name']];
        })->toArray();

        $weekoffs = $weekoffData->get()->mapToGroups(function ($item) {
            return [$item['user_id'] => $item['weekoff']];
        })->toArray();

        $userIdsArrays = Users::where('company_id', $user->company_id)->where('role_id', 2)->where('showUser', 1)->pluck('id')->toArray();
        $attendCount = [];
        // dump('before data' ,$data);
        if ($request->supervisorSelect == "all") {
            // dump('when supervisor select is all', $request->supervisorSelect);
            $data = $test->groupBy(['user_id', 'name', 'date', 'site_name'])
                ->get()->mapToGroups(function ($item) {
                    return [$item['user_id'] => $item['date']];
                })->toArray();

            // dump($data ,'data 1');
            // dump($test->get() , 'test data 1');
            $data1 = Attendance::whereBetween('dateFormat', [$startDate, $endDate])->whereIn('user_id', $userIdsArrays)->where('company_id', $user->company_id);

            $attendance = Attendance::whereBetween('dateFormat', [$startDate, $endDate])
                ->selectRaw('sum(TIME_TO_SEC(time_calculation)) as actualTime, sum(TIME_TO_SEC(gpsTime)) as gpsTime')->get();
            $actualTime = $attendance[0]['actualTime'];
            $gpsTime = $attendance[0]['gpsTime'];

            $attendCount = $data1->groupBy(['user_id', 'dateFormat'])
                ->get()->mapToGroups(function ($item) {
                    return [$item['dateFormat'] => $item['user_id']];
                })->toArray();
            ksort($attendCount);
        } else {
            // dump('when clietn is all');
            // dd($test->get() ,"test data");
            // $data = $test->groupBy(['user_id', 'name', 'date', 'site_name'])
            //     ->get()
            //     ->mapToGroups(function ($item) {
            //         return [$item['user_id'] => $item['date']];
            //     })
            //     ->map(function ($dates) {
            //         // dd($dates); // Inspect the structure of $dates
            //         // Convert the collection to an array and format each date in the array to Y-m-d
            //         return collect($dates)->map(function ($date) {
            //             return Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
            //         })->toArray(); // Convert back to array if needed
            //     });
            $data = $test->groupBy(['user_id', 'name', 'date', 'site_name'])
                ->get()
                ->mapToGroups(function ($item) {
                    // Create an array with all the required information
                    $attendanceInfo = [
                        'date' => $item['date'],
                        'site_name' => $item['site_name'],
                        'entry_time' => $item['entry_time'] ?? null,
                        'exit_date_time' => $item['exit_date_time'] ?? null,
                        'time_difference' => $item['duration'] ?? null,
                        'gpsTime' => $item['gpsTime'] ?? null
                    ];

                    // Return with user_id as key and attendance info as value
                    return [$item['user_id'] => $attendanceInfo];
                });
            $attendance = Attendance::where('user_id', $request->supervisorSelect)->whereBetween('dateFormat', [$startDate, $endDate])
                ->selectRaw('sum(TIME_TO_SEC(time_calculation)) as actualTime, sum(TIME_TO_SEC(gpsTime)) as gpsTime')->first();
            $actualTime = $attendance->actualTime;
            $gpsTime = $attendance->gpsTime;
        }
        // dd($data, "data");


        $hours = floor($actualTime / 3600);
        $mins = floor(($actualTime / 60) % 60);
        $gpshours = floor($gpsTime / 3600);
        $gpsmins = floor(($gpsTime / 60) % 60);

        $actualTimeformat = sprintf('%02dhr %02dmin', $hours, $mins);
        $gpsTimeformat = sprintf('%02dhr %02dmin', $gpshours, $gpsmins);

        $company = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $company->name;

        $datePresent = Attendance::whereBetween('dateFormat', [$startDate, $endDate])
            ->select('dateFormat')->distinct()->pluck('dateFormat')->toArray();

        $date = date('d M Y', strtotime($startDate)) . " to " . date('d M Y', strtotime($endDate));

        $userinfo = SiteAssign::where('role_id', 2)->where('company_id', $user->company_id)->get();
        $weekOffDates = [];

        // dd($daysCount , "daysCount");

        // dump($clients , "clients");
        $supervisorAssignedSites = SiteAssign::where('company_id', $user->company_id)
            ->where('role_id', 2)
            ->select('user_name', 'user_id', 'site_id')
            ->orderBy('user_name', 'ASC')
            ->get();
        // dump($supervisorAssignedSites , "super assignd sites");
        $supervisorSites = [];

        foreach ($supervisorAssignedSites as $assign) {
            $siteIds = is_string($assign->site_id)
                ? json_decode($assign->site_id, true)
                : (array) $assign->site_id;

            if (empty($siteIds))
                continue;

            $sites = SiteDetails::whereIn('id', $siteIds)->get();

            // dump($sites , 'sites');
            $siteNames = $sites->pluck('name')->unique()->values()->toArray();

            // Try to get client names directly
            $clientNames = $sites->pluck('client_name')
                ->filter(fn($name) => !is_null($name)) // remove nulls
                ->unique()
                ->values()
                ->toArray();

            // Fallback: for sites missing client_name, get it from ClientDetails
            if (count($clientNames) === 0) {
                $clientIds = $sites->pluck('client_id')
                    ->filter(fn($id) => !is_null($id))
                    ->unique()
                    ->values()
                    ->toArray();

                $clients = ClientDetails::whereIn('id', $clientIds)->pluck('name')->toArray();
                $clientNames = array_values(array_unique($clients));
            }

            $supervisorSites[$assign->user_id] = [
                'site' => $siteNames,
                'client' => $clientNames
            ];
        }


        // dump($attendCount , "attendace count");
        if ($request->supervisorSelect == 'all') {

            // dump($data , 'data');
            $modaldata = view('reports.clientSupervisorReportView')->with([
                'date' => $date,
                'guard' => $guard,
                'weekoffs' => $weekoffs,
                'startDatee' => $startDatee,
                'subType' => $subType,
                'companyName' => $companyName,
                'attendCount' => $attendCount,
                'clients' => $clients,
                'sites' => $sites,
                'fromDate' => $startDate,
                'toDate' => $endDate,
                'daysCount' => $daysCount,
                'attendanceSubType' => $subType,
                'data' => $data,
                'datePresent' => $datePresent,
                'weekOffDates' => $weekOffDates,
                'names' => $names,
                'actualTimeformat' => $actualTimeformat,
                'gpsTimeformat' => $gpsTimeformat,
                'type' => $subType,
                'supervisorId' => $request->supervisorSelect,
                'geofences' => $geofences,
                'client' => $client,
                'currentDate' => $currentDate,
                'supervisorSelect' => $request->supervisorSelect,
                'supervisorSites' => $supervisorSites,
                'generatedOn' => $this->generatedOn

            ])->render();
        } else {

            // dd($data , "data");

            $variables = [
                'subType' => $subType,
                'companyName' => $companyName,
                'fromDate' => $startDate,
                'toDate' => $endDate,
                'daysCount' => $daysCount,
                'data' => $data,
                'datePresent' => $datePresent,
                'weekOffDates' => $weekOffDates,
                'actualTimeformat' => $actualTimeformat,
                'gpsTimeformat' => $gpsTimeformat,
                'supervisorId' => $request->supervisorSelect,
                'geofences' => $geofences,
                'guardId' => $request->supervisorSelect,
                'guardName' => $user,

            ];

            // Dump all variables at once
            // dump($variables);

            // dump($request->supervisorSelect, "id");
            $modaldata = view('reports.userMonthlyReportView')
                ->with('subType', $subType)
                ->with('companyName', $companyName)
                ->with('fromDate', $startDate)
                ->with('toDate', $endDate)
                ->with('daysCount', $daysCount)
                ->with('data', $data)
                ->with('datePresent', $datePresent)
                ->with('weekOffDates', $weekOffDates)
                ->with('actualTimeformat', $actualTimeformat)
                ->with('gpsTimeformat', $gpsTimeformat)
                ->with('supervisorId', $request->supervisorSelect)
                ->with('geofences', $geofences)
                ->with('guardId', $request->supervisorSelect)
                ->with('guardId', $request->supervisorSelect)
                ->render();
            // dd($modaldata , "modaldata");
        }


        echo $modaldata;
    }


    private function workingSummaryReportMethod($attendanceSubType, $client, $geofences, $startDate, $endDate, $user, $site)
    {
        // dd($attendanceSubType, $client, $geofences, $startDate, $endDate, $user, $site, $attendanceSubType == 'workingSummary' , "all data");
        // dump($geofences, $client, "geofences");

        $date = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));

        if ($attendanceSubType != 'workingSummary') {
            return null;
        }

        $siteInfo = [
            'name' => 'All Sites',
            'client' => [
                'id' => $client,
                'name' => 'All Clients'
            ]
        ];


        if ($client !== 'all' && $geofences == 'all') {
            $clientDetails = ClientDetails::find($client);
            $siteInfo['client']['name'] = $clientDetails->name ?? 'Unknown Client';
            // dump($siteInfo, "siteInfo");
        } else {
            $clientDetails = ClientDetails::find($client);
            $site = SiteAssign::where('site_id', $geofences)->first();
            // dd($site->site_name , "site");
            $siteInfo['client']['name'] = $clientDetails->name ?? 'All Client';
            $siteInfo['name'] = $site->site_name ?? 'All site';
        }


        // Set default values for site and subtype
        $subType = 'Working Summary Report';
        $site = $site ?? 'all';

        // Fetch company and site details
        $companyName = CompanyDetails::where('id', $user->company_id)->first();
        $siteDetails = ($site !== 'all') ? SiteDetails::where('id', $site)->first() : null;

        // Prepare date range
        $fromDate = new DateTime($startDate);
        $toDate = new DateTime($endDate);
        $dateRange = [$startDate, $endDate];


        // Fetch guards based on user role

        $guardsQuery = collect();
        if ($user->role_id == 1) {

            $guardsQuery = SiteAssign::query()
                ->where('company_id', $user->company_id)
                ->when($client != 'all' && $geofences == 'all', fn($query) => $query->where('client_id', $client))
                ->when($client != 'all' && $geofences != 'all', fn($query) => $query->where('site_id', $geofences))
                ->orderBy('user_name');
        } else if ($user->role_id == 7) {
            $siteQuery = SiteAssign::where('company_id', $user->company_id);

            // dump('in else if');
            // Fetch site assignments for the user
            $siteAssigned = SiteAssign::where('user_id', $user->id)->first();

            // $client_ids = json_decode($siteAssigned, true);
            $client_ids = json_decode($siteAssigned['site_id'], true);
            // dd($siteAssigned ,  $client_ids,"site Assigned");

            if ($client == 'all') {
                $siteArray = SiteDetails::whereIn('client_id', $client_ids)->pluck('id')->toArray();
                $userIds = SiteAssign::whereIn('client_id', $client_ids)->pluck('user_id')->toArray();
            } else {

                $siteArray = SiteDetails::where('client_id', $client)->pluck('id')->toArray();
                $userIds = SiteAssign::where('client_id', $client)->pluck('user_id')->toArray();
            }


            // dd($siteArray , "array");

            $supervisorIds = Users::where([['users.company_id', '=', $user->company_id], ['users.role_id', '=', 2]])
                ->leftJoin('site_assign as site', 'users.id', '=', 'site.user_id')
                ->where(function ($query) use ($siteArray) {
                    foreach ($siteArray as $siteId) {
                        $query->orWhereRaw('JSON_CONTAINS(site.site_id, ?)', [json_encode([$siteId])]);
                    }
                })->pluck('site.user_id')->toArray();


            // dd($userIds, "user ids");

            $site_UserIds = array_merge($userIds, $supervisorIds);

            // dump($siteQuery->pluck('user_name')->toArray());
            $siteQuery->when(
                $geofences !== 'all' && $geofences !== null,
                fn($query) => $query->where('site_id', $geofences)

            )
                ->whereIn('user_id', $site_UserIds);

            // dd($siteQuery->pluck('client_id')->toArray());
            // dd($siteQuery->pluck('user_id')->toArray());
            // dump('the role id is 7 last');

            $site_UserIds_query = SiteAssign::where('company_id', $user->company_id);
            // dd($site_UserIds);

            if ($client !== 'all' && $geofences == 'all') {
                // dump($client, $geofences, "client");
                // $attendanceQuery->whereIn('user_id', $site_UserIds_query->pluck('user_id'));
                $site_UserIds = $site_UserIds_query->where('client_id', $client)->pluck('user_id');
            } else if ($client !== 'all' && $geofences !== 'all') {
                $site_UserIds = $site_UserIds_query->where('site_id', $geofences)->pluck('user_id');
            }

            $guardsQuery = SiteAssign::query()
                ->where('company_id', $user->company_id)
                ->whereIn('user_id', $site_UserIds)
                ->orderBy('user_name');


            if (!isset($site_UserIds) || count($site_UserIds) === 0) {
                // dd('stop');
                return 'No records found';
            }
        } else {

            // dump('the role id is 2');
            $siteIds = $user->role_id == 2
                ? SiteAssign::where('user_id', $user->id)
                    ->pluck('site_id')
                    ->map(function ($item) {
                        return is_string($item) ? json_decode($item, true) : $item;
                    })
                    ->flatten()
                    ->unique()
                    ->values()
                : collect();
            // dump($siteIds, "siteIds arr");

            $site_UserIds_query = SiteAssign::where('company_id', $user->company_id)->whereIn('site_id', $siteIds);
            // dd($site_UserIds);

            if ($client == 'all') {
                $site_UserIds = $site_UserIds_query->pluck('user_id');
            } else if ($client !== 'all' && $geofences == 'all') {
                // dump($client, $geofences, "client");
                // $attendanceQuery->whereIn('user_id', $site_UserIds_query->pluck('user_id'));
                $site_UserIds = $site_UserIds_query->pluck('user_id');
            } else if ($client !== 'all' && $geofences !== 'all') {
                $site_UserIds = $site_UserIds_query->where('site_id', $geofences)->pluck('user_id');
            }

            $guardsQuery = SiteAssign::query()
                ->where('company_id', $user->company_id)
                ->whereIn('user_id', $site_UserIds)
                ->orderBy('user_name');
        }



        // Fetch guards in bulk
        $guards = $guardsQuery->get();

        // Prepare user IDs and weekoff data
        $guardUserIds = $guards->pluck('user_id')->toArray();
        $guardWeekOffs = $guards->pluck('weekoff', 'user_id')->map(fn($weekoff) => json_decode($weekoff, true) ?? []);

        // Bulk fetch attendance records for guards in the date range
        $attendanceData = Attendance::whereIn('user_id', $guardUserIds)
            ->whereBetween('dateFormat', $dateRange)
            ->distinct()
            ->get()
            ->groupBy('user_id');

        // dump($attendanceData[ 1183] , "attendance data");
        // dump($attendanceData , "attendance data");
        // dump($attendanceData->get(1183), "Attendance data for 1183");
        // Calculate attendance and working data
        $totalDays = $fromDate->diff($toDate)->days + 1;
        $groupedData = $guards->map(function ($guard) use ($attendanceData, $guardWeekOffs, $fromDate, $toDate, $totalDays) {
            $userId = $guard->user_id;

            // Calculate weekoff dates for the guard
            $weekOffDays = $guardWeekOffs[$userId] ?? [];
            $weekOffDates = $this->calculateWeekOffDates($weekOffDays, $fromDate, $toDate);

            // Calculate total working days
            $totalWorkingDays = $totalDays - count($weekOffDates);

            // Fetch attendance records for the guard
            // $presentDays = $attendanceData[$userId]->count() ?? 0;
            $presentDays = isset($attendanceData[$userId])
                ? $attendanceData[$userId]->unique('dateFormat')->count()
                : 0;

            // dump($attendanceData, "present days");
            // Adjust total working days if attendance exceeds the calculation
            $totalWorkingDays = max($totalWorkingDays, $presentDays);
            $absentDays = $totalWorkingDays - $presentDays;

            return [
                'user_name' => $guard->user_name,
                'user_id' => $guard->user_id,
                'totalWorkingDays' => $totalWorkingDays,
                'daysWorked' => $presentDays,
                'absentDays' => $absentDays,
                'weekOffCount' => count($weekOffDates),
            ];
        });

        $dateFormat = config('app.date_format', 'd M Y');

        // dd($companyName->name , "name");

        // dump($attendanceSubType, $groupedData,   "grouped data");
        // Return the rendered view
        return view('reports.workingSummaryReportView')->with([
            'companyName' => $companyName,
            'clientName' => $siteInfo['client']['name'],
            'client' => $client,
            'siteName' => $siteInfo['name'],
            'site' => $siteDetails,
            'guard' => 'all',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'daysCount' => $totalDays,
            'groupedData' => $groupedData,
            'dateRange' => "$startDate to $endDate",
            'sites' => $guards,
            'subType' => $subType,
            'currentDate' => date($dateFormat),
            'companyId' => $user->company_id,
            'dateFormat' => $dateFormat,
            'geofences' => $geofences,
            'attendanceSubType' => $attendanceSubType,
            'date' => $date,
            'generatedOn' => $this->generatedOn
        ]);
    }



    public function generateTourDiaryMethod($subType, $user, $startDate, $endDate, $requestData)
    {
        // dd($subType , 'data');
        $reportMonth = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));
        $client = $requestData['client'] ?? 'all';
        $geofences = $requestData['geofences'] ?? 'all';
        $guard = $requestData['guard'] ?? 'all';

        if ($user->role_id == 1) {
            if ($client === "all") {
                $userIds = Users::where('company_id', $user->company_id)->pluck('id')->toArray();
            } elseif ($geofences === "all") {
                $userIds = SiteAssign::where('client_id', $client)->pluck('user_id')->toArray();
            } elseif ($guard === "all") {
                $userIds = SiteAssign::where('site_id', $geofences)->pluck('user_id')->toArray();
            } else {
                $userIds = [$guard];
            }
        } elseif ($user->role_id == 7) {
            $siteAssigned = SiteAssign::where('user_id', $user->id)->first();
            $client_ids = json_decode($siteAssigned['site_id'] ?? '[]', true);

            if ($client === "all") {
                $siteArray = SiteDetails::whereIn('client_id', $client_ids)->pluck('id')->toArray();
                $userIds = SiteAssign::whereIn('client_id', $client_ids)->pluck('user_id')->toArray();
            } elseif ($geofences === "all") {
                $userIds = SiteAssign::where('client_id', $client)->pluck('user_id')->toArray();
            } elseif ($guard === "all") {
                $userIds = SiteAssign::where('site_id', $geofences)->pluck('user_id')->toArray();
            } else {
                $userIds = [$guard];
            }
        } else {
            $requestData['client'] = null;

            $siteIds = $user->role_id == 2
                ? SiteAssign::where('user_id', $user->id)
                    ->pluck('site_id')
                    ->map(fn($item) => is_string($item) ? json_decode($item, true) : $item)
                    ->flatten()
                    ->unique()
                    ->values()
                : collect();

            $site_UserIds_query = SiteAssign::where('company_id', $user->company_id)->whereIn('site_id', $siteIds);

            if ($geofences === 'all') {
                $userIds = $site_UserIds_query->pluck('user_id');
            } elseif ($geofences !== 'all' && $guard === 'all') {
                $userIds = $site_UserIds_query->where('site_id', $geofences)->pluck('user_id');
            } else {
                $userIds = [$guard];
            }
        }


        $data = TourDiary::leftJoin('client_details', 'tour_diary.client_id', '=', 'client_details.id')
            ->leftJoin('site_assign as site', 'tour_diary.user_id', '=', 'site.user_id')
            ->leftJoin('client_details as site_clients', 'site.client_id', '=', 'site_clients.id')
            ->whereIn('tour_diary.user_id', $userIds)
            ->whereBetween('tour_diary.start_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('tour_diary.start_time', 'ASC')
            ->selectRaw('
            tour_diary.*,
            client_details.id as tour_client_id,
            site.site_name as site_name,
            site_clients.id as site_client_id,
            COALESCE(client_details.name, site_clients.name) as client_name')
            ->get();


        $company = CompanyDetails::find($user->company_id);
        $companyName = $company->name ?? '';

        $filteredData = collect($requestData)->except([
            '_token',
            'fromDate',
            'toDate',
            'incidencePriority',
            'incidentSubType',
            'visitorSubType',
            'tourSubType',
            'attendanceSubType',
            'tourDate'
        ])->toArray();

        // dd($data ,'data');

        if ($data->count() > 0) {

            // dump($subType , $companyName , "subType");
            return view('reports/tourDiaryReportView', [
                'data' => $data,
                'fromDate' => $startDate,
                'toDate' => $endDate,
                'reportMonth' => $reportMonth,
                'companyName' => $companyName,
                'flagType' => 'tour',
                'subType' => $subType,
                'allData' => json_encode($filteredData, true),
            ])->render();
        }

        return "error";
    }

    public function generateSelfTourDiaryMethod($subType, $user, $startDate, $endDate, $requestData)
    {
        // dd($subType , 'data');

        $reportMonth = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));
        $client = $requestData['client'] ?? 'all';
        $geofences = $requestData['geofences'] ?? 'all';
        $guard = $requestData['guard'] ?? 'all';

        // $user_data = Users::where('name', 'DA Ghuge')->first();
        // dd($user_data, "user data");
        $requestData['client'] = null;

        $siteIds = $user->role_id == 2
            ? SiteAssign::where('user_id', $user->id)
                ->pluck('site_id')
                ->map(fn($item) => is_string($item) ? json_decode($item, true) : $item)
                ->flatten()
                ->unique()
                ->values()
            : collect();


        $userIds = $user->id;

        $data = TourDiary::leftJoin('client_details', 'tour_diary.client_id', '=', 'client_details.id')
            ->leftJoin('site_assign as site', 'tour_diary.user_id', '=', 'site.user_id')
            ->leftJoin('client_details as site_clients', 'site.client_id', '=', 'site_clients.id')
            ->where('tour_diary.user_id', $userIds)
            ->whereBetween('tour_diary.start_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('tour_diary.start_time', 'ASC')
            ->selectRaw('
            tour_diary.*,
            client_details.id as tour_client_id,
            site.site_name as site_name,
            site_clients.id as site_client_id,
            COALESCE(client_details.name, site_clients.name) as client_name')
            ->get();


        $company = CompanyDetails::find($user->company_id);
        $companyName = $company->name ?? '';

        $filteredData = collect($requestData)->except([
            '_token',
            'fromDate',
            'toDate',
            'incidencePriority',
            'incidentSubType',
            'visitorSubType',
            'tourSubType',
            'attendanceSubType',
            'tourDate'
        ])->toArray();

        // dd($data ,'data');

        if ($data->count() > 0) {
            return view('reports/tourDiaryReportView', [
                'data' => $data,
                'fromDate' => $startDate,
                'toDate' => $endDate,
                'reportMonth' => $reportMonth,
                'companyName' => $companyName,
                'flagType' => 'self',
                'subType' => $subType,
                'allData' => json_encode($filteredData, true),
            ])->render();
        }

        return "error";
    }

    public function generateSuperVisorTourDiaryMethod($request, $subType, $user, $startDate, $endDate, $requestData)
    {
        // dd($subType , 'data');
        // if ($subType !== 'supervisortourdiaryreport') return;


        $reportMonth = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));
        $client = $requestData['client'] ?? 'all';
        $geofences = $requestData['geofences'] ?? 'all';
        $guard = $requestData['guard'] ?? 'all';

        // $user_data = Users::where('name', 'DA Ghuge')->first();
        // dd($user_data, "user data");

        $requestData['client'] = null;

        if ($user->role_id == '7') {
            $siteAssigned = SiteAssign::where('user_id', $user->id)->first();

            $client_ids = json_decode($siteAssigned['site_id'], true);
            // dd($siteAssigned ,  $client_ids,"site Assigned");

            $siteArray = SiteDetails::whereIn('client_id', $client_ids)->pluck('id')->toArray();
            // dd($siteArray , "array");

            if ($request->supervisorSelect == 'all') {
                $userIds = Users::where([['users.company_id', '=', $user->company_id], ['users.role_id', '=', 2]])
                    ->leftJoin('site_assign as site', 'users.id', '=', 'site.user_id')
                    ->where(function ($query) use ($siteArray) {
                        foreach ($siteArray as $siteId) {
                            $query->orWhereRaw('JSON_CONTAINS(site.site_id, ?)', [json_encode([$siteId])]);
                        }
                    })->pluck('site.user_id')->toArray();
            } else {

                $userIds = $request->supervisorSelect;
            }
        } else {
            // for role id 1
            if ($request->supervisorSelect == "all") {
                $userIds = SiteAssign::where('company_id', $user->company_id)->where('role_id', 2)->get()->pluck('user_id')->unique();
            } else {
                // dump('here');
                $userIds = $request->supervisorSelect;
            }
        }
        // dd($request->supervisorSelect, $userIds, "supervisor select");

        $data = TourDiary::leftJoin('client_details', 'tour_diary.client_id', '=', 'client_details.id')
            ->leftJoin('site_assign as site', 'tour_diary.user_id', '=', 'site.user_id')
            ->leftJoin('client_details as site_clients', 'site.client_id', '=', 'site_clients.id')
            ->when($request->supervisorSelect !== 'all', function ($query) use ($request) {
                return $query->where('tour_diary.user_id', $request->supervisorSelect);
            }, function ($query) use ($userIds) {
                return $query->whereIn('tour_diary.user_id', $userIds);
            })
            ->whereBetween('tour_diary.start_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('tour_diary.start_time', 'ASC')
            ->selectRaw('
        tour_diary.*,
        client_details.id as tour_client_id,
        site.site_name as site_name,
        site_clients.id as site_client_id,
        COALESCE(client_details.name, site_clients.name) as client_name')
            ->get();



        $company = CompanyDetails::find($user->company_id);
        $companyName = $company->name ?? '';


        $filteredData = collect($requestData)->except([
            '_token',
            'fromDate',
            'toDate',
            'incidencePriority',
            'incidentSubType',
            'visitorSubType',
            'tourSubType',
            'attendanceSubType',
            'tourDate'
        ])->toArray();

        // dd($data ,'data');

        if ($data->count() > 0) {
            return view('reports/tourDiaryReportView', [
                'data' => $data,
                'fromDate' => $startDate,
                'toDate' => $endDate,
                'reportMonth' => $reportMonth,
                'companyName' => $companyName,
                'flagType' => 'supervisor',
                'supervisorSelect' => $request->supervisorSelect,
                'subType' => $subType,
                'allData' => json_encode($filteredData, true),
            ])->render();
        }

        return "error";
    }

    public function generateAdminTourDiaryMethod($request, $subType, $user, $startDate, $endDate, $requestData)
    {
        // dd($subType , 'data');
        // if ($subType !== 'supervisortourdiaryreport') return;


        $reportMonth = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));
        $client = $requestData['client'] ?? 'all';
        $geofences = $requestData['geofences'] ?? 'all';
        $guard = $requestData['guard'] ?? 'all';

        // $user_data = Users::where('name', 'DA Ghuge')->first();
        // dd($user_data, "user data");

        $requestData['client'] = null;


        if ($request->adminSelect == 'all') {
            // $userIds = Users::where('company_id', $user->company_id)->where('role_id', $user->role_id)->pluck('id')->toArray();
            $userIds = SiteAssign::where('company_id', $user->company_id)->where('role_id', 7)->get()->pluck('user_id')->unique();
        } else {
            $userIds = $request->adminSelect;
        }
        // dd($userIds, "userIds");


        // dd($request->supervisorSelect, $userIds, "supervisor select");

        $data = TourDiary::leftJoin('client_details', 'tour_diary.client_id', '=', 'client_details.id')
            ->leftJoin('site_assign as site', 'tour_diary.user_id', '=', 'site.user_id')
            ->leftJoin('client_details as site_clients', 'site.client_id', '=', 'site_clients.id')
            ->when($request->supervisorSelect !== 'all', function ($query) use ($request) {
                return $query->where('tour_diary.user_id', $request->supervisorSelect);
            }, function ($query) use ($userIds) {
                return $query->whereIn('tour_diary.user_id', $userIds);
            })
            ->whereBetween('tour_diary.start_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('tour_diary.start_time', 'ASC')
            ->selectRaw('
        tour_diary.*,
        client_details.id as tour_client_id,
        site.site_name as site_name,
        site_clients.id as site_client_id,
        COALESCE(client_details.name, site_clients.name) as client_name')
            ->get();

        // dd($data->first(), 'data');

        $company = CompanyDetails::find($user->company_id);
        $companyName = $company->name ?? '';


        $filteredData = collect($requestData)->except([
            '_token',
            'fromDate',
            'toDate',
            'incidencePriority',
            'incidentSubType',
            'visitorSubType',
            'tourSubType',
            'attendanceSubType',
            'tourDate'
        ])->toArray();

        // dd($data ,'data');

        if ($data->count() > 0) {
            // dd($data, 'data');

            return view('reports/tourDiaryReportView', [
                'data' => $data,
                'fromDate' => $startDate,
                'toDate' => $endDate,
                'reportMonth' => $reportMonth,
                'companyName' => $companyName,
                'flagType' => 'admin',
                'subType' => $subType,
                'adminSelect' => $request->adminSelect,
                'supervisorSelect' => $request->supervisorSelect,
                'allData' => json_encode($filteredData, true),
            ])->render();
        }

        return "error";
    }
    // export of report
    public function incidenceExport(Request $request)
    {
        // dd($request);
        $user = session('user');
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        $sub_type = isset($_GET['sub_type']) ? $_GET['sub_type'] : '';
        $supervisor = isset($_GET['supervisor']) ? $_GET['supervisor'] : '';
        // $geofences = isset($_GET['geofences']) ? $_GET['geofences'] : '';

        $attendanceSubType = $request->attendanceSubType;
        $geofences = $request->geofences;
        $client = $request->client;


        $generatedOn = now()->format('d F Y, h:i A');
        // Example output: 28 May 2025, 01:33 PM


        // if ($request->client == "all" && ($geofences == null || $geofences == 'all')) {
        //     $guard = 'all';
        // } else {
        //     $guard = isset($_GET['guard']) ? $_GET['guard'] : '';
        // }
        if ($request->client == "all" && ($request->geofences == null || $request->geofences == 'all')) {
            // Case 1: Client is "all" and geofences are null or "all"
            $guard = 'all';
        } elseif ($request->client != "all" && ($request->geofences == null || $request->geofences == 'all')) {
            // Case 2: Single client and geofences are "all"
            $guard = 'all';
        } elseif ($request->client != "all" && $request->geofences != null && $request->geofences != 'all') {
            // Case 3: Single client and single geofence (site)
            $guard = isset($_GET['guard']) ? $_GET['guard'] : 'none';
            // dd($guard , "guard");
        } else {
            // Fallback case
            $guard = isset($_GET['guard']) ? $_GET['guard'] : '';
        }

        $subType = '';

        // dump($attendanceSubType);
        if ($attendanceSubType == 'EmployeeAttendanceReport') {
            // dump($attendanceSubType);
            $subType = 'Employee Attendance Report';
            // dump($subType , 1417);
            // $subType = $attendanceSubType;
        } else if ($attendanceSubType == 'EmployeeAttendanceReportwithHours') {
            $subType = 'Employee Attendance Report With Hours';
            // dump($subType , 1417);
        } else {
            $subType = 'Employee Attendance Report With Site';
            // dump($subType , 1421);
        }

        $client = $request->client;
        // dump($client , " main client");

        $geofences = $request->geofences;
        if ($client == 'all' && $geofences == null) {
            $geofences = 'all';
        }

        $fromDate = isset($_GET['fromDate']) ? $_GET['fromDate'] : '';
        $toDate = isset($_GET['toDate']) ? $_GET['toDate'] : '';
        $duration = $request->duration;
        $month = $request->month;
        $supervisorId = $request->supervisor;
        $geoName = DB::table('incidence_details')->where('site_id', '=', $geofences)->first();
        $companyData = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $companyData->name;
        $startDate = date('Y-m-d', strtotime($fromDate));
        $endDate = date('Y-m-d', strtotime($toDate));


        $datetime1 = new DateTime($startDate);
        $datetime2 = new DateTime($endDate);
        $interval = $datetime1->diff($datetime2);
        $dateRange = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));


        if ($type == 'incident') {
            $client = $request->client;
            $user = session('user');

            // dump($client , " main client");
            $startDate = date('Y-m-d', strtotime($request->fromDate));
            $endDate = date('Y-m-d', strtotime($request->toDate));
            $incidenceSubType = $request->incidentSubType;
            $priority = $request->incidencePriority;
            $datetime1 = new DateTime($startDate);
            $datetime2 = new DateTime($endDate);
            $interval = $datetime1->diff($datetime2);
            $daysCount = (int) $interval->format('%a');
            $daysCount = $daysCount + 1;
            $reportMonth = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));
            $geoName = DB::table('incidence_details')->where('site_id', '=', $geofences)->first();
            // dump($geoName , "geoNamae");
            Log::info($user->name . ' view incidence report, User_id: ' . $user->id);
            $IncidenceDetails = DB::table('incidence_details');
            // $companyName = SiteAssign::where('company_id' , $user->company_id)->first();
            $companyData = CompanyDetails::where('id', $user->company_id)->first();
            $companyName = $companyData->name;
            if ($incidenceSubType == 'incidenceReport') {


                $endDate = date('Y-m-d', strtotime($request->toDate));
                $startDate = date('Y-m-d', strtotime($request->fromDate));

                // dump($client , $geofences  ,"data about client and geofences");
                if ($client == 'all' || $client != 'all' && $geofences == 'all') {
                    $siteName = 'All';
                    // dump($companyName , "companyName");

                } else {
                    $site = SiteAssign::where('site_id', $geofences)->first();
                    $siteName = $site->site_name;
                    // dump($siteName , $geofences , $companyName , "company data");

                }

                if ($client == "all") {
                    $IncidenceDetails = DB::table('incidence_details as inci')
                        // ->join('site_details as site', 'site.id', '=', 'inci.site_id')
                        ->where('inci.company_id', $user->company_id)
                        ->whereBetween('inci.dateFormat', [$startDate, $endDate]);
                } elseif ($geofences == 'all') {
                    // dd($request->client,$startDate,$endDate);
                    $IncidenceDetails = DB::table('incidence_details as inci')
                        // ->join('site_details as site', 'site.id', '=', 'inci.site_id')
                        ->where('inci.client_id', $request->client)
                        ->whereBetween('inci.dateFormat', [$startDate, $endDate]);
                    //->where('inci.site_id', '=', $geofences)
                    // ->whereBetween('dateFormat', [$startDate, $endDate])->get();

                } else {
                    $IncidenceDetails = DB::table('incidence_details')->where('site_id', '=', $geofences);
                }


                if (isset($request->toDate) && isset($request->fromDate)) {


                    if ($priority == 'All') {

                        $IncidenceDetails = $IncidenceDetails;
                        // dd($startDate, $endDate, $IncidenceDetails);
                    } else {

                        $IncidenceDetails = $IncidenceDetails->where('inci.priority', $priority);
                    }
                }
                $IncidenceDetails = $IncidenceDetails->get();

                // dd($IncidenceDetails);
                if (count($IncidenceDetails) > 0) {

                    $modaldata = view('reports/incidenceReportView')->with('IncidenceDetails', $IncidenceDetails)->with('client', $request->client)
                        ->with('geoName', $geoName)->with('geofences', $geofences)->with('toDate', $endDate)
                        ->with('fromDate', $startDate)->with('priority', $priority)->with('incidenceSubType', $incidenceSubType)
                        ->with('siteName', $siteName)
                        ->with('companyName', $companyName)
                        ->render();
                    echo $modaldata;
                } else {
                    return "error";
                }
            } else {
                // dd('When In Incidence Report , subType is set to  Incidence Summary report');
                // dd($request);
                if ($geofences == 'all') {
                    // dd($geofences);
                    if ($request->client == 'all') {
                        $IncidenceDetails = DB::table('incidence_details as inci')
                            // ->join('site_details as site', 'site.id', '=', 'inci.site_id')
                            ->where('inci.company_id', $user->company_id)
                            ->whereBetween('dateFormat', [$startDate, $endDate]);
                        // dd($IncidenceDetails->get());
                    } else {
                        $IncidenceDetails = DB::table('incidence_details as inci')
                            ->join('site_assign as site', 'site.id', '=', 'inci.site_id')
                            ->where('site.client_id', $request->client)
                            ->whereBetween('dateFormat', [$startDate, $endDate]);
                    }
                } else {
                    $IncidenceDetails = DB::table('incidence_details')->where('site_id', '=', $geofences)->whereBetween('dateFormat', [$startDate, $endDate]);
                }

                if (!isset($IncidenceDetails)) {
                    return 'No records found';
                }

                // $IncidenceDetails = DB::table('incidence_details')->where('site_id', '=', $geofences)->whereBetween('dateFormat', [$startDate, $endDate]);
                $IncidenceDetails = $IncidenceDetails->get();
                // dd($IncidenceDetails, "Incidnece data");
                // dump($IncidenceDetails->pluck('checkList')->toArray() , "Incidnece data");
                //if(count($IncidenceDetails) > 0) {
                // dump("How a " ,$client);
                $modaldata = view('reports/incidenceSummaryReportView')->with('client', $client)
                    ->with('data', $IncidenceDetails)
                    ->with('geoName', $geoName)->with('client', $request->client)->with('geofences', $geofences)->with('incidenceSubType', $incidenceSubType)->with('toDate', $endDate)->with('daysCount', $daysCount)->with('fromDate', $startDate)->render();
                echo $modaldata;
                //} else {
                //     return "error";
                //}
            }
        } elseif ($type == 'visitor') {
            $startDate = date('Y-m-d', strtotime($request->fromDate));
            $endDate = date('Y-m-d', strtotime($request->toDate));
            Log::info($user->name . ' view visitor report, User_id: ' . $user->id);
            $visitorSubType = $request->visitorSubType;
            $VisitorDetails = DB::table('visitor_details');
            if ($request->visitorSubType == 'visitorReport') {
                $VisitorDetails = $VisitorDetails->where('site_id', '=', $geofences)->whereBetween('dateFormat', [$startDate, $endDate]);
                $VisitorDetails = $VisitorDetails->get();
                if (count($VisitorDetails) > 0) {
                    $modaldata = view('reports/visitorDailyView')->with('VisitorDetails', $VisitorDetails)->with('geofences', $geofences)->with('fromDate', $startDate)->with('toDate', $endDate)->render();
                    echo $modaldata;
                } else {
                    return "error";
                }
            } else {

                $VisitorDetails = $VisitorDetails->where('site_id', '=', $geofences)->whereBetween('dateFormat', [$startDate, $endDate]);
                $VisitorDetails = $VisitorDetails->get()->unique('date');
                // dd(count($VisitorDetails));
                if (count($VisitorDetails) > 0) {
                    $modaldata = view('reports/visitorReportCount')->with('VisitorDetails', $VisitorDetails)->with('geofences', $geofences)->with('fromDate', $startDate)->with('toDate', $endDate)->render();
                    echo $modaldata;
                } else {
                    return "error";
                }
            }
        } elseif ($type == 'tour') {

            Log::info($user->name . ' view tour report, User_id: ' . $user->id);
            $startDate = date('Y-m-d', strtotime($request->fromDate));
            $endDate = date('Y-m-d', strtotime($request->toDate));
            $datetime1 = new DateTime($startDate);
            $datetime2 = new DateTime($endDate);
            $interval = $datetime1->diff($datetime2);
            $daysCount = (int) $interval->format('%a');
            $daysCount = $daysCount + 1;
            $tourSubType = $request->tourSubType;

            if ($tourSubType == 'DailyTour') {

                $tourDate = date('Y-m-d', strtotime($request->tourDate));
                $cur_date = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
                $userId = $request->guard;
                $date = $cur_date->format('d-m-Y');
                $subtype = "DailyTour";
                $GuardTourLog = DB::table('guard_tour');
                if ($userId == 'all') {
                    $GuardTourLog = $GuardTourLog->where('site_id', $geofences);
                    $geo = SiteDetails::where('id', $geofences)->first();
                } else {
                    $attendance = Attendance::where('user_id', $userId)
                        ->where('dateFormat', $tourDate)
                        ->first();
                    //dd($userId);
                    if (isset($attendance)) {
                        $guardTour = GuardTourLog::where('guardId', $userId)
                            ->where('tourDate', $tourDate)->select('tourId')->distinct()
                            ->get()->toArray();
                        $geo = SiteDetails::where('id', $geofences)->first();
                        $GuardTourLog = $GuardTourLog->whereIn('id', $guardTour);
                    } else {
                        return "error";
                    }
                }

                $GuardTourLog = $GuardTourLog->get();
                // dd($GuardTourLog);

                if (count($GuardTourLog) > 0) {
                    $modaldata = view('reports/tourDailyNew')->with('GuardTourLog', $GuardTourLog)->with('date', $date)->with('userId', $userId)->with('geo', $geofences)->with('geofences', $geofences)->with('tourDate', $tourDate)->with('tourDate', $tourDate)->with('subtype', $subtype)->render();
                    echo $modaldata;
                } else {
                    return "error";
                }
            } elseif ($request->tourSubType == 'tourDayWise') {

                $reportMonth = $datetime1->format('d M Y') . " to " . $datetime2->format('d M Y');
                $company = CompanyDetails::where('id', $user->company_id)->first();
                $companyName = $company->name;
                $site = SiteDetails::where('id', $geofences)->first();
                if ($guard == 'all') {
                    $data = GuardTourLog::where('site_id', $geofences)->whereBetween('date', [$datetime1->format('Y-m-d'), $datetime2->format('Y-m-d')])->orderBy('date', 'ASC')->orderBy('tourName', 'ASC')->orderBy('round', 'ASC')->get();
                    if (count($data) > 0) {
                        $modaldata = view('TourReport/tourDayWiseView')->with('data', $data)->with('site', $site)->with('dateRange', $reportMonth)->with('companyName', $companyName)->with('startDate', $startDate)->with('endDate', $endDate)->with('siteId', $geofences)->render();
                        echo $modaldata;
                    } else {
                        return "error";
                    }
                } else {
                    $data = GuardTourLog::where('guardId', $guard)->whereBetween('date', [$datetime1->format('Y-m-d'), $datetime2->format('Y-m-d')])->orderBy('date', 'ASC')->orderBy('tourName', 'ASC')->orderBy('round', 'ASC')->get();
                    if (count($data) > 0) {
                        $modaldata = view('TourReport/guardTourReportView')->with('data', $data)->with('site', $site)->with('dateRange', $reportMonth)->with('companyName', $companyName)->with('startDate', $startDate)->with('endDate', $endDate)->with('siteId', $geofences)->with('guard', $guard)->render();
                        echo $modaldata;
                    } else {
                        return "error";
                    }
                }
            } elseif ($request->tourSubType == 'guardTourReport') {
                $guard = $request->guard;
                $siteId = $request->geofences;
                $reportMonth = $datetime1->format('d M Y') . " to " . $datetime2->format('d M Y');
                $data = GuardTourLog::where('guardId', $guard)->whereBetween('date', [$datetime1->format('Y-m-d'), $datetime2->format('Y-m-d')])->orderBy('date', 'ASC')->orderBy('tourName', 'ASC')->orderBy('round', 'ASC')->get();
                $company = CompanyDetails::where('id', $user->company_id)->first();
                $companyName = $company->name;
                $site = SiteDetails::where('id', $request->geofences)->first();

                if (count($data) > 0) {
                    return response()->json([
                        'type' => $type,
                        'subtype' => $tourSubType,
                        'geofences' => $geofences,
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                        'guard' => $guard
                    ]);
                } else {
                    return "error";
                }
            } else {
                $GuardTourLog = DB::table('guard_tour');
                // if ($guard == 'all') {
                $GuardTourLog = $GuardTourLog->where('site_id', $geofences);
                $geo = SiteDetails::where('id', $geofences)->first();
                // } else {
                //     $attendance = Attendance::where('user_id', $guard)
                //         ->whereBetween('dateFormat', [$startDate, $endDate])
                //         ->first();
                //     if (isset($attendance)) {
                //         $guardTour = GuardTourLog::where('guardId', $guard)
                //             ->whereBetween('tourDate', [$startDate, $endDate])->select('tourId')->distinct()
                //             ->get()->toArray();
                //         $geo = SiteDetails::where('id', $attendance->site_id)->first();
                //         $GuardTourLog = $GuardTourLog->whereIn('id', $guardTour);
                //     } else {
                //         return "error";
                //         // return redirect()->back()->with('alert', 'Records not found');
                //     }
                // }
                $GuardTourLog = $GuardTourLog->get();
                // dd($GuardTourLog);
                if (count($GuardTourLog) > 0) {

                    $modaldata = view('reports/tourSummaryView')->with('GuardTourLog', $GuardTourLog)->with('userId', $guard)->with('geo', $geo)->with('geofences', $geofences)->with('startDate', $startDate)->with('endDate', $endDate)->with('daysCount', $daysCount)->render();
                    echo $modaldata;
                } else {
                    return "error";
                }
            }
        } elseif ($type == 'attendance') {

            //dd($type);
            Log::info($user->name . ' view attendance report, User_id: ' . $user->id);
            $startDate = date('Y-m-d', strtotime($request->fromDate));
            $endDate = date('Y-m-d', strtotime($request->toDate));
            $datetime1 = new DateTime($startDate);
            $datetime2 = new DateTime($endDate);
            $interval = $datetime1->diff($datetime2);
            $daysCount = (int) $interval->format('%a');
            $daysCount = $daysCount + 1;
            $attendanceSubType = $request->attendanceSubType;
            // dd($attendanceSubType , "sub type");

            $company = CompanyDetails::where('id', $user->company_id)->first();
            $companyName = $company->name;

            $date = date('d M Y', strtotime($startDate)) . " to " . date('d M Y', strtotime($endDate));

            $currentDate = new DateTime('now', new DateTimeZone('Asia/Kolkata'));

            $currentDate = $currentDate->format("d-m-Y");
            //dd($attendanceSubType);



            if ($attendanceSubType == "workingSummary") {

                $result = $this->workingSummaryReportMethod($attendanceSubType, $client, $geofences, $startDate, $endDate, $user, $geofences, $generatedOn);
                echo $result;
            } elseif ($attendanceSubType == 'onSiteAttendanceReport') {
                // dd(2);
                $geofencesNew = $request->geofences ?? 'all'; // Default to 'all' if null
                $subType = 'Emergency Attendance Report';
                $reportMonth = $datetime1->format('d M Y') . " to " . $datetime2->format('d M Y');
                $dateRange = [$datetime1->format('Y-m-d'), $datetime2->format('Y-m-d')];

                // Fetch company and site details
                $company = CompanyDetails::find($user->company_id);
                $companyName = $company->name;

                $result = $this->onSiteAttendanceMethod($attendanceSubType, $geofences, $client, $user, $startDate, $endDate, $dateRange);
                echo $result;
            } elseif ($attendanceSubType == 'forgetToMarkExit') {

                $result = $this->forgetToMarkExitMethod($attendanceSubType, $geofences, $client, $datetime1, $datetime2, $user, $startDate, $endDate, $companyName);
                echo $result;
            } elseif ($attendanceSubType == 'supervisorAttendance') {

                $dateRange = [
                    'start' => $startDate, // Start date
                    'end' => $endDate    // End date
                ];
                $subType = 'Supervisor Attendance Report';
                $result = $this->supervisorAttendanceReportMethod($subType, $interval, $daysCount, $user, $geofences, $client, $dateRange, $guard, $request, $currentDate);
            } elseif ($attendanceSubType == 'EmployeeAttendanceReport' || $attendanceSubType == 'EmployeeAttendanceReportwithSite' || $attendanceSubType == 'EmployeeAttendanceReportwithHours') {
                if ($client == 'all') {
                    $result = $this->allGuardReportMethod($user, $request, $startDate, $endDate, $attendanceSubType, $geofences, $guard, $type, $client, $subType, $dateRange, $generatedOn);
                    echo $result;
                } else {

                    if ($request->geofences == 'all') {
                        $result = $this->clientWiseReportMethod($request, $user, $startDate, $endDate, $geofences, $subType);
                        echo $result;
                    } else if ($client != 'all' && $geofences != 'all' && $guard == 'all') {
                        $result = $this->siteWiseReportMethod($client, $geofences, $guard, $request, $user, $startDate, $endDate, $attendanceSubType, $daysCount, $type, $subType);
                        echo $result;
                    }
                    if ($guard != 'all') {
                        $result = $this->guardReportMethod($guard, $client, $startDate, $endDate, $user, $subType, $daysCount, $datetime1, $datetime2, $attendanceSubType);
                    }
                }
            } elseif ($attendanceSubType == 'absentReport') {
                $result = $this->absentReportMethod($attendanceSubType, $user, $startDate, $endDate, $geofences, $guard, $request, $companyName, $client);
                echo $result;
            } elseif ($attendanceSubType == 'lateReport') {
                $subType = 'Late Attendance Report';
                $date = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));

                $result = $this->generateLateAttendanceReport($request, $attendanceSubType, $geofences, $client, $user, $startDate, $endDate, $guard, $dateRange);
                echo $result;
            } elseif ($attendanceSubType == 'self') {
                // dd('name');

                $subType = "Single Supervisor Attendance";
                $GuardDetails = DB::table('attendance');
                $guard = $user->id;
                // $guard = 1163;
                $GuardDetails = $GuardDetails->where('user_id', $guard);
                $datePresent = Attendance::where('user_id', $guard)
                    ->whereBetween('dateFormat', [$startDate, $endDate])
                    ->select('dateFormat')->distinct()->pluck('dateFormat')->toArray();
                $userinfo = SiteAssign::where('user_id', $guard)->first();
                $days = [];
                if ($userinfo) {
                    $days = json_decode($userinfo->weekoff, true);
                }
                $weekOffDates = [];
                if ($days && count($days) > 0) {
                    foreach ($days as $key => $value) {
                        $utility = new GetDays();
                        $mondays = $utility->getDays($value, $datetime1->format('F'), $datetime1->format('Y'), $datetime2->format('F'), $datetime2->format('Y'));
                        foreach ($mondays as $index => $monday) {
                            $weekOffDates[] = date('d-m-Y', strtotime($monday));
                        }
                        $lastSundayDate = date('d-m-Y', strtotime('+7 days ' . $weekOffDates[count($weekOffDates) - 1]));
                        $lastSunday = new DateTime($lastSundayDate, new DateTimeZone('Asia/Kolkata'));
                        if ($lastSunday < $datetime2) {
                            $weekOffDates[] = $lastSundayDate;
                        }
                    }
                }
                $data = Attendance::where('user_id', $guard)
                    ->whereBetween('dateFormat', [$startDate, $endDate])
                    ->get()->groupBy('dateFormat');
                $attendance = Attendance::where('user_id', $guard)->whereBetween('dateFormat', [$startDate, $endDate])
                    ->selectRaw('sum(TIME_TO_SEC(time_calculation)) as actualTime, sum(TIME_TO_SEC(gpsTime)) as gpsTime')->first();

                $actualTime = $attendance->actualTime;
                $gpsTime = $attendance->gpsTime;

                $hours = floor($actualTime / 3600);
                $mins = floor(($actualTime / 60) % 60);

                $gpshours = floor($gpsTime / 3600);
                $gpsmins = floor(($gpsTime / 60) % 60);

                $actualTimeformat = sprintf('%02dhr %02dmin', $hours, $mins);
                $gpsTimeformat = sprintf('%02dhr %02dmin', $gpshours, $gpsmins);
                $company = CompanyDetails::where('id', $user->company_id)->first();
                $companyName = $company->name;

                // $siteName = 'none';

                $siteArray = SiteAssign::where('company_id', $user->company_id)->where('user_id', $user->id)->pluck('site_id')->toArray();
                // dd($siteArray , "site array");
                $site_Ids = json_decode($siteArray[0], true);
                $siteClientNames = [
                    'sites' => [],
                    'client' => []
                ];

                foreach ($site_Ids as $site) {
                    $siteName = SiteDetails::where('id', $site)->value('name');
                    $siteClientNames['sites'][] = $siteName;

                    $clientNames = SiteAssign::where('site_id', $site)->value('client_name');
                    $siteClientNames['client'][] = $clientNames;


                    // dd($siteClientNames , "clientNames");
                }
                // dd($siteClientNames , "site clients names");
                // dd($site_Ids, "site ids");

                $siteClientNames['sites'] = array_unique($siteClientNames['sites']);
                $siteClientNames['client'] = array_unique($siteClientNames['client']);

                // dd($siteClientNames , "site clie names");
                $modaldata = view('AttendanceReport/guardReportView')
                    ->with('subType', $subType)
                    ->with('companyName', $companyName)
                    ->with('fromDate', $startDate)
                    ->with('toDate', $endDate)
                    ->with('daysCount', $daysCount)
                    ->with('data', $data)
                    ->with('datePresent', $datePresent)
                    ->with('weekOffDates', $weekOffDates)
                    ->with('actualTimeformat', $actualTimeformat)
                    ->with('gpsTimeformat', $gpsTimeformat)
                    ->with('guardId', $guard)
                    ->with('siteName', $siteName)
                    ->with('attendanceSubType', $attendanceSubType)
                    ->with('generatedOn', $this->generatedOn)
                    ->with('siteClientsNames', $siteClientNames)
                    ->with('flag', 'self')
                    ->render();
                echo $modaldata;
            } elseif ($type == 'visits') {

                // dd($request->all());
                $reportMonth = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));
                // $data = ClientVisit::where('company_id', $user->company_id)->whereBetween('date', [$startDate, $endDate])->get();
                if ($request->client == "all") {
                    $data = ClientVisit::where('company_id', $user->company_id)->whereBetween('date', [$startDate, $endDate])
                        ->orderBy('date', 'ASC')->get();
                } elseif ($request->geofences == 'all') {
                    $data = ClientVisit::where('client_id', $request->client)->whereBetween('date', [$startDate, $endDate])
                        ->orderBy('date', 'ASC')->get();
                } elseif ($request->guard == 'all') {
                    $data = ClientVisit::where('site_id', $request->geofences)->whereBetween('date', [$startDate, $endDate])
                        ->orderBy('date', 'ASC')->get();
                } else {
                    $data = ClientVisit::where('user_id', $request->guard)->whereBetween('date', [$startDate, $endDate])
                        ->orderBy('date', 'ASC')->get();
                }

                // dd($data);
                $company = CompanyDetails::where('id', $user->company_id)->first();
                $companyName = $company->name;
                $allData = $request->all();
                unset($allData['_token']);
                unset($allData['fromDate']);
                unset($allData['toDate']);
                unset($allData['incidencePriority']);
                unset($allData['incidentSubType']);
                unset($allData['visitorSubType']);
                unset($allData['tourSubType']);
                unset($allData['attendanceSubType']);
                unset($allData['tourDate']);
                //dd($allData);
                if (count($data) > 0) {
                    $modaldata = view('reports/clientVisitReportView')->with('data', $data)->with('fromDate', $startDate)->with('toDate', $endDate)->with('reportMonth', $reportMonth)->with('companyName', $companyName)->with('allData', json_encode($allData, true))->render();
                    echo $modaldata;
                } else {
                    return "error";
                }
            }
        } elseif ($type == 'tourdiary') {
            $requestData = $request->all();
            $tourSubType = $request->tourSubType;
            // dd($requestData , "requested data");                

            if ($tourSubType === 'tourdiaryreport') {
                $subType = 'All_Employee_Tour_Diary_Report';
                $result = $this->generateTourDiaryMethod($subType, $user, $startDate, $endDate, $requestData);
                echo $result;
            } elseif ($tourSubType === 'selftourdiaryreport') {
                $subType = 'Self_Tour_Diary_Report';
                $result = $this->generateSelfTourDiaryMethod($subType, $user, $startDate, $endDate, $requestData);
                echo $result;
            } elseif ($tourSubType === 'admintourdiaryreport') {
                $subType = 'Admin_Tour_Diary_Report';
                $result = $this->generateAdminTourDiaryMethod($request, $subType, $user, $startDate, $endDate, $requestData);
                echo $result;
            } else {
                $subType = 'Supervisor_Tour_Diary_Report';
                $result = $this->generateSuperVisorTourDiaryMethod($request, $subType, $user, $startDate, $endDate, $requestData);
                echo $result;
            }
        } elseif ($type == 'patrolling') {
            $patrolSubType = $request->patrollingReportSubType;
            // dd($patrolSubType);
            // $requestData = $request->all();
            if ($patrolSubType === 'patrolling_status_report')
                $result = $this->patrollingStatusMethod($request);
            else if ($patrolSubType === 'patrolling_summary_report')
                $result = $this->patrollingSummaryMethod($request);
            else
                $result = $this->patrollingLogsReportView($request);
            echo $result;
        }
    }



    public function patrollingStatusMethod(Request $request)
    {

        // dd('stat');
        $user = session('user');

        // --- 1. Get Filters from Request ---
        $startDate = Carbon::parse($request->input('fromDate'))->startOfDay();
        $endDate = Carbon::parse($request->input('toDate'))->endOfDay();
        $patrolSubType = $request->input('patrollingReportSubType'); // e.g., 'all_patrols', 'ongoing_patrols'
        $clientId = $request->input('client');
        $beatId = $request->input('geofences'); // Assuming geofences corresponds to beat_id
        $employeeId = $request->input('guard');

        // dd($clientId, $beatId, $patrolSubType, "beat id");

        // --- 2. Build the Database Query ---
        // $query = PatrolSession::query()
        //     // ->with(['user:id,name', 'beat.range:id,name']) // Eager load relationships for efficiency
        //     ->with(['user:id,name', 'site:id,name']) // Eager load relationships for efficiency
        //     ->where('company_id', '56')
        //     ->whereBetween('started_at', [$startDate, $endDate]);


        $query = PatrolSession::query()
            ->with([
                'user:id,name,role_id',
                'site',
            ])
            ->where('company_id', '56')
            ->whereBetween('started_at', [$startDate, $endDate]);
        // dd($startDate, $endDate , "dates");

        // $sess = $query->get();
        // dd($sess, "sess");

        $org = CompanyDetails::where('id', $user->company_id)->pluck('name')->first();

        // $sdata = SiteAssign::where('user_id', 2477)->first();
        // dd($sdata , "Data");

        // dd("org", $org[0]);
        // Apply status filter
        // if ($patrolStatus === 'ongoing_patrols') {
        //     $query->whereNull('ended_at');
        // } elseif ($patrolStatus === 'completed_patrols') {
        //     $query->whereNotNull('ended_at');
        // }

        // dd($patrolSubType, $clientId, $beatId, $employeeId, $query->get(), "get", );


        // Apply client/range filter
        if ($clientId && $clientId !== 'all') {
            $query->whereHas('site', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            });
        }

        // Apply beat/site filter
        if ($beatId && $beatId !== 'all') {
            $query->where('site_id', $beatId);
        }

        // Apply employee filter
        if ($employeeId && $employeeId !== 'all') {
            $query->where('user_id', $employeeId);
        }

        $patrols = $query->orderBy('started_at', 'desc')->get();

        $user = Users::where('name', 'Mamta Devi Gond')->first();
        // dd($user , "user");

        $siteData = SiteAssign::where('user_name', 'Sharad Kumar Nagar')->first();
        // dd(SiteDetails::where('id' , 738)->first() , "site details");
        // dd('site data' , $siteData);
        // dd($patrols[15]->user , "patrols");


        // dd("patrols", $patrols);
        // --- 3. Prepare Data for the View ---
        $reportData = [
            'patrols' => $patrols,
            'companyName' => $user->company->name ?? 'N/A', // Assuming a company relationship exists
            'dateRange' => $startDate->format('d M, Y') . ' to ' . $endDate->format('d M, Y'),
            'generatedOn' => now()->format('d F Y, h:i A'),
            'reportTitle' => ucwords(str_replace('_', ' ', $patrolSubType)) . ' Report',
            // Pass all request inputs to the view for the download form
            'requestParams' => $request->all(),
            'org' => $org,
            'clientId' => $clientId,
            'beatId' => $beatId,
            'employeeId' => $employeeId,
            'startDate' => $startDate,
            'endDate' => $endDate
        ];

        // --- 4. Return the View Partial ---
        return view('reports.patrollingStatusReportView', $reportData);
    }

    public function patrollingSummaryMethod(Request $request)
    {
        $user = session('user');
        $companyId = $user->company_id;

        $startDate = Carbon::parse($request->fromDate)->startOfDay();
        $endDate = Carbon::parse($request->toDate)->endOfDay();

        $clientId = $request->client;
        $beatId = $request->geofences;
        $employeeId = $request->guard;

        // Fetch raw sessions
        $query = PatrolSession::query()
            ->with(['user:id,name', 'site'])
            ->where('company_id', $companyId)
            ->whereBetween('started_at', [$startDate, $endDate]);
        // dd($clientId);
        if ($clientId && $clientId != 'all') {
            $query->whereHas('site', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            });
        }

        if ($beatId && $beatId != 'all') {
            $query->where('site_id', $beatId);
        }

        if ($employeeId && $employeeId != 'all') {
            $query->where('user_id', $employeeId);
        }

        $patrols = $query->get();

        // --- Build Summary ---
        $summary = $patrols
            ->groupBy('user_id')
            ->map(function ($items) {

                $completed = $items->whereNotNull('ended_at')->count();
                $ongoing = $items->whereNull('ended_at')->count();

                return [
                    'guard' => $items->first()->user->name ?? 'N/A',
                    'range' => $items->first()->site->client_name ?? 'N/A',
                    'beat' => $items->first()->site->name ?? 'N/A',
                    'total_sessions' => $items->count(),
                    'completed' => $completed,
                    'ongoing' => $ongoing,
                    'total_distance' => round($items->sum('distance') / 1000, 2),
                    'avg_distance' => round(($items->avg('distance') ?? 0) / 1000, 2),
                ];
            })
            ->values();

        return view('reports.patrollingSummaryView', [
            'summary' => $summary,
            'companyName' => $user->company_name ?? 'N/A',
            'dateRange' => $startDate->format('d M Y') . ' to ' . $endDate->format('d M Y'),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'requestParams' => $request->all(),
        ]);
    }

    public function patrollingLogsReportView(Request $request)
    {
        $user = session('user');
        $companyId = $user->company_id;

        $startDate = Carbon::parse($request->fromDate)->startOfDay();
        $endDate = Carbon::parse($request->toDate)->endOfDay();
        $logType = $request->patrolLogsType;

        $query = PatrolLog::with(['media', 'session.user', 'session.site'])
            ->where('company_id', $companyId)
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($logType && $logType !== 'all') {
            $query->where('type', $logType);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        return view('reports.patrolLogsReportView', [
            'logs' => $logs,
            'companyName' => $user->company_name ?? 'N/A',
            'dateRange' => $startDate->format('d M Y') . ' to ' . $endDate->format('d M Y'),
            'logType' => $logType,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }



    public function animalSightingMethod(Request $request)
    {
    }
    public function waterResourcesMethod(Request $request)
    {
    }
    public function HumanImpactMethod(Request $request)
    {
    }

    // incidence resolve
    public function incidenceResolve(Request $request, $incidence_id)
    {
        $user = session('user');

        if ($user->role_id == '1') {
            IncidenceDetails::where('id', '=', $incidence_id)
                ->update(['actionRemark' => $request->remark, 'action' => 'Resolve', 'actionDate' => date('Y-m-d'), 'actionTime' => date('H:i:s'), 'status' => 'Resolve by Admin ' . $user->name, 'statusFlag' => '1']);
        } else {
            IncidenceDetails::where('id', '=', $incidence_id)
                ->update(['actionRemark' => $request->remark, 'action' => 'Resolve', 'actionDate' => date('Y-m-d'), 'actionTime' => date('H:i:s'), 'status' => 'Resolve by Supervisor ' . $user->name, 'statusFlag' => '1']);
        }
        $log = ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'log_id' => $incidence_id,
            'log_type' => "Incidence",
            'user_name' => $user->name,
            'type' => "Incidence Resolved",
            'message' => "Incidence Resolved by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);



        $incidence = IncidenceDetails::find($incidence_id);

        $notification = new Notifications();
        $notification->notification = $log->message;
        $notification->type = 'Incidence Action';
        $notification->notification_id = $incidence->id;
        $notification->user_id = $incidence->guard_id;
        $notification->site_id = $incidence->site_id;
        $notification->status = 'resolved';
        if ($user->_role_id == 2) {
            $notification->supervisor_id = $user->id;
        } elseif ($user->role_id == 1) {
            $notification->admin_id = $user->id;
        }
        $notification->company_id = $user->company_id;
        $notification->date = date('Y-m-d');
        $notification->dateFormat = date('Y-m-d');
        $notification->dateTime = date('d-m-Y h:i a');
        $notification->time = date('h:i a');

        $notification->save();

        $fcm_tokens = [];

        $adminFcm = SiteAssign::where('company_id', $user->company_id)->where('role_id', 1)->pluck('fcm_token')->toArray();
        $supervisorArray = SiteAssign::where('site_id', 'like', '%' . $incidence->site_id . '%')->where('role_id', 2)->get();

        $assignedSupervisorsArray = [];

        foreach ($supervisorArray as $item) {
            $geoArray = json_decode($item['site_id'], true);
            foreach ($geoArray as $geo) {
                if ($incidence->site_id == $geo) {
                    $assignedSupervisorsArray[] = $item['user_id'];
                }
            }
        }

        $guardFcm = Users::where('id', $incidence->guard_id)->pluck('fcm_token')->toArray();

        // $fcm_tokens = [];

        $supervisorFcm = Users::whereIn('id', $assignedSupervisorsArray)->pluck('fcm_token')->toArray();

        $fcm_tokens = array_merge($guardFcm, $supervisorFcm, $adminFcm);

        if (count($fcm_tokens) > 0) {
            $title = "Incidence Alert";
            $fcm = new FCMNotify();
            $fcm->sendNotification($title, $log->message, $fcm_tokens);
        }
    }

    // incidence ignored
    public function incidenceIgnore(Request $request, $incidence_id)
    {
        $user = session('user');

        if ($user->role_id == '1') {
            IncidenceDetails::where('id', '=', $incidence_id)
                ->update(['actionRemark' => $request->remark, 'action' => 'Ignore', 'actionDate' => date('Y-m-d'), 'actionTime' => date('H:i:s'), 'status' => 'Ignore By Admin ' . $user->name, 'statusFlag' => '2']);
        } else {
            IncidenceDetails::where('id', '=', $incidence_id)
                ->update(['actionRemark' => $request->remark, 'action' => 'Ignore', 'actionDate' => date('Y-m-d'), 'actionTime' => date('H:i:s'), 'status' => 'Ignore By Supervisor ' . $user->name, 'statusFlag' => '2']);
        }
        $log = ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'log_id' => $incidence_id,
            'log_type' => "Incidence",
            'type' => "Incidence Ignored",
            'message' => "Incidence Ignored by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);

        $fcm_tokens = [];
        $incidence = IncidenceDetails::find($incidence_id);

        $notification = new Notifications();
        $notification->notification = $log->message;
        $notification->type = 'Incidence Action';
        $notification->notification_id = $incidence->id;
        $notification->user_id = $incidence->guard_id;
        $notification->site_id = $incidence->site_id;
        $notification->status = 'resolved';
        if ($user->_role_id == 2) {
            $notification->supervisor_id = $user->id;
        } elseif ($user->role_id == 1) {
            $notification->admin_id = $user->id;
        }
        $notification->company_id = $user->company_id;
        $notification->date = date('Y-m-d');
        $notification->dateFormat = date('Y-m-d');
        $notification->dateTime = date('d-m-Y h:i a');
        $notification->time = date('h:i a');

        $notification->save();

        $adminFcm = SiteAssign::where('company_id', $user->company_id)->where('role_id', 1)->pluck('fcm_token')->toArray();
        $supervisorArray = SiteAssign::where('site_id', 'like', '%' . $incidence->site_id . '%')->where('role_id', 2)->get();

        $assignedSupervisorsArray = [];

        foreach ($supervisorArray as $item) {
            $geoArray = json_decode($item['site_id'], true);
            foreach ($geoArray as $geo) {
                if ($incidence->site_id == $geo) {
                    $assignedSupervisorsArray[] = $item['user_id'];
                }
            }
        }

        $guardFcm = Users::where('id', $incidence->guard_id)->pluck('fcm_token')->toArray();

        // $fcm_tokens = [];

        $supervisorFcm = Users::whereIn('id', $assignedSupervisorsArray)->pluck('fcm_token')->toArray();

        $fcm_tokens = array_merge($guardFcm, $supervisorFcm, $adminFcm);

        if (count($fcm_tokens) > 0) {
            $title = "Incidence Alert";
            $fcm = new FCMNotify();
            $fcm->sendNotification($title, $log->message, $fcm_tokens);
        }
    }

    // incidence escalate
    public function incidenceEscalate(Request $request, $incidence_id)
    {
        $user = session('user');
        $log = ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'log_id' => $incidence_id,
            'log_type' => "Incidence",
            'type' => "Incidence Escalated",
            'message' => "Incidence Escalated by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);

        $incidence = IncidenceDetails::where('id', $incidence_id)->first();
        $fcm_tokens = [];

        $notification = new Notifications();
        $notification->notification = $log->message;
        $notification->type = 'Incidence Action';
        $notification->notification_id = $incidence->id;
        $notification->user_id = $incidence->guard_id;
        $notification->site_id = $incidence->site_id;
        $notification->status = 'resolved';
        if ($user->_role_id == 2) {
            $notification->supervisor_id = $user->id;
        } elseif ($user->role_id == 1) {
            $notification->admin_id = $user->id;
        }
        $notification->company_id = $user->company_id;
        $notification->date = date('Y-m-d');
        $notification->dateFormat = date('Y-m-d');
        $notification->dateTime = date('d-m-Y h:i a');
        $notification->time = date('h:i a');

        $notification->save();

        if ($user->role_id == '1') {
            IncidenceDetails::where('id', '=', $incidence_id)
                ->update(['actionRemark' => $request->remark, 'action' => 'escalate', 'actionDate' => date('Y-m-d'), 'actionTime' => date('H:i:s'), 'status' => 'Escalated to Client by ' . $user->name, 'statusFlag' => '5', 'admin_id' => $user->id]);
        } else {
            IncidenceDetails::where('id', '=', $incidence_id)
                ->update(['actionRemark' => $request->remark, 'action' => 'escalate', 'actionDate' => date('Y-m-d'), 'actionTime' => date('H:i:s'), 'status' => 'Escalated to Admin by ' . $user->name, 'statusFlag' => '3', 'supervisor_id' => $user->id]);
        }

        $adminFcm = SiteAssign::where('company_id', $user->company_id)->where('role_id', 1)->pluck('fcm_token')->toArray();
        $supervisorArray = SiteAssign::where('site_id', 'like', '%' . $incidence->site_id . '%')->where('role_id', 2)->get();

        $assignedSupervisorsArray = [];

        foreach ($supervisorArray as $item) {
            $geoArray = json_decode($item['site_id'], true);
            foreach ($geoArray as $geo) {
                if ($incidence->site_id == $geo) {
                    $assignedSupervisorsArray[] = $item['user_id'];
                }
            }
        }

        $guardFcm = Users::where('id', $incidence->guard_id)->pluck('fcm_token')->toArray();

        // $fcm_tokens = [];

        $supervisorFcm = Users::whereIn('id', $assignedSupervisorsArray)->pluck('fcm_token')->toArray();

        $fcm_tokens = array_merge($guardFcm, $supervisorFcm, $adminFcm);

        if (count($fcm_tokens) > 0) {
            $title = "Incidence Alert";
            $fcm = new FCMNotify();
            $fcm->sendNotification($title, $log->message, $fcm_tokens);
        }
    }
}

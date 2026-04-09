<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FilterDataTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Services\RoleBasedFilterService;
use App\Attendance;
use App\SiteGeofences;

class AttendanceController extends Controller
{
    use FilterDataTrait;

    protected $analyticsService;

    public function __construct(\App\Services\AnalyticsDataService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function explorer(Request $request)
    {

        try {

            $user = session('user');
            $companyId = $user->company_id;

            $accessibleUserIds = RoleBasedFilterService::getAccessibleUserIds();

            /* ================= FILTER DATE ================= */

            $filterDate = $request->date
                ? Carbon::parse($request->date)
                : now();

            $startDate = $filterDate->copy()->startOfDay();
            $endDate = $filterDate->copy()->endOfDay();

            $dates = collect([$filterDate->copy()]);
            $totalDaysInRange = 1;


            /* ================= USERS ================= */

            $users = DB::table('users')
                ->where('users.company_id', $companyId)
                ->where('users.isActive', 1)
                ->whereIn('users.id', $accessibleUserIds)
                ->leftJoin('site_assign', 'users.id', '=', 'site_assign.user_id')
                ->select(
                    'users.id',
                    'users.name',
                    'users.profile_pic',
                    'site_assign.client_name as range',
                    'site_assign.site_id',
                    'site_assign.site_name'
                )
                ->groupBy(
                    'users.id',
                    'users.name',
                    'users.profile_pic',
                    'site_assign.client_name',
                    'site_assign.site_id',
                    'site_assign.site_name'
                )
                ->get()
                ->keyBy('id');

            $userIds = $users->keys()->toArray();


            /* ================= ATTENDANCE ================= */

            $attendance = DB::table('attendance')
                ->where('company_id', $companyId)
                ->whereBetween('dateFormat', [$startDate, $endDate])
                ->whereIn('user_id', $userIds)
                ->select('user_id', DB::raw('DATE(dateFormat) as date'))
                ->get()
                ->mapWithKeys(function ($r) {
                    return [
                        $r->user_id . '_' . $r->date => true
                    ];
                });


            /* ================= GRID ================= */

            $grid = [];

            foreach ($users as $u) {

                $presentCount = 0;
                $dayData = [];

                foreach ($dates as $dt) {

                    $dStr = $dt->toDateString();
                    $key = $u->id . '_' . $dStr;

                    $present = isset($attendance[$key]);

                    if ($present) {
                        $presentCount++;
                    }

                    $dayData[$dStr] = [
                        'present' => $present
                    ];
                }

                $grid[$u->id] = [

                    'user' => $u,

                    'meta' => [
                        'range' => $u->range ?? 'NA',
                        'beat' => $u->site_name ?? 'NA'
                    ],

                    'days' => $dayData,

                    'summary' => [
                        'present' => $presentCount,
                        'total' => $totalDaysInRange
                    ]
                ];
            }


            /* ================= KPIs ================= */

            $defaulters = collect($grid)
                ->map(function ($g) {

                    return [
                        'user_id' => $g['user']->id,
                        'name' => $g['user']->name,
                        'days_present' => $g['summary']['present'],
                        'days_absent' => $g['summary']['total'] - $g['summary']['present']
                    ];
                })
                ->sortByDesc('days_absent')
                ->take(10)
                ->values();

            $totalGuards = $users->count();

            $totalPresentManDays = collect($grid)
                ->sum(fn($g) => $g['summary']['present']);

            $totalPossibleManDays = $totalGuards * $totalDaysInRange;

            $totalAbsentManDays = $totalPossibleManDays - $totalPresentManDays;

            $presentPct = $totalPossibleManDays > 0
                ? round(($totalPresentManDays / $totalPossibleManDays) * 100, 1)
                : 0;

            $presentToday = DB::table('attendance')
                ->where('company_id', $companyId)
                ->whereBetween('dateFormat', [$startDate, $endDate])
                ->distinct()
                ->count('user_id');

            $absentToday = max(0, $totalGuards - $presentToday);

            $lateToday = DB::table('attendance')
                ->where('company_id', $companyId)
                ->whereBetween('dateFormat', [$startDate, $endDate])
                ->whereNotNull('lateTime')
                ->where('lateTime', '!=', '0')
                ->count();

            $activeSites = DB::table('attendance')
                ->where('company_id', $companyId)
                ->whereBetween('dateFormat', [$startDate, $endDate])
                ->distinct()
                ->count('site_id');

            $pendingRequests = \Illuminate\Support\Facades\Schema::hasTable('leave_requests')
                ? DB::table('leave_requests')->where('status', 'pending')->count()
                : 0;

            $recentCheckins = DB::table('attendance')
                ->join('users', 'attendance.user_id', '=', 'users.id')
                ->whereBetween('attendance.dateFormat', [$startDate, $endDate])
                ->orderByDesc('attendance.entryDateTime')
                ->limit(6)
                ->select(
                    'users.name',
                    'users.profile_pic',
                    DB::raw("TIME(attendance.entryDateTime) as time"),
                    'attendance.site_id as site'
                )
                ->get();


            /* ================= WEEKLY TREND ================= */

            $dailyTrend = collect([
                [
                    'date' => $filterDate->format('d M'),
                    'full_date' => $filterDate->toDateString(),
                    'present' => $presentToday,
                    'absent' => $absentToday
                ]
            ]);
            /* ================= WEEKLY TREND ================= */

            $weekStart = $filterDate->copy()->subDays(6)->startOfDay();
            $weekEnd = $filterDate->copy()->endOfDay();

            $weeklyData = DB::table('attendance')
                ->select(
                    DB::raw('DATE(dateFormat) as date'),
                    DB::raw('COUNT(DISTINCT user_id) as present')
                )
                ->where('company_id', $companyId)
                ->whereBetween('dateFormat', [$weekStart, $weekEnd])
                ->groupBy(DB::raw('DATE(dateFormat)'))
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            $weeklyLabels = [];
            $weeklyPresent = [];
            $weeklyAbsent = [];

            for ($i = 6; $i >= 0; $i--) {

                $date = $filterDate->copy()->subDays($i)->toDateString();

                $present = $weeklyData[$date]->present ?? 0;
                $absent = max(0, $totalGuards - $present);

                $weeklyLabels[] = Carbon::parse($date)->format('d M');
                $weeklyPresent[] = $present;
                $weeklyAbsent[] = $absent;
            }
            $filterData = $this->filterData();
        } catch (\Exception $e) {

            $grid = [];
            $dates = [];
            $dailyTrend = collect([]);

            $presentToday = 0;
            $absentToday = 0;
            $lateToday = 0;
            $activeSites = 0;
            $pendingRequests = 0;
            $recentCheckins = collect([]);
            $presentPct = 0;
            $totalPresentManDays = 0;
            $defaulters = collect([]);
            $totalAbsentManDays = 0;
            $totalGuards = 0;
            $weeklyLabels = [];
            $weeklyPresent = [];
            $weeklyAbsent = [];

            $filterData = ['ranges' => [], 'beats' => [], 'users' => []];
        }


        return view('attendance.explorer', array_merge(
            $filterData,
            compact(
                'grid',
                'dates',
                'totalGuards',
                'presentPct',
                'totalPresentManDays',
                'totalAbsentManDays',
                'dailyTrend',
                'defaulters',
                'startDate',
                'endDate',
                'presentToday',
                'absentToday',
                'lateToday',
                'activeSites',
                'pendingRequests',
                'recentCheckins',
                'weeklyLabels',
                'weeklyPresent',
                'weeklyAbsent',
                'filterDate'
            )
        ));
    }

    public function logs(Request $request)
    {
        $user = session('user');
        $companyId = $user->company_id;

        $employee = $request->employee;
        $site = $request->site;
        $client = $request->client;
        $range = $request->range;

        /* ---------- BASE QUERY ---------- */

        $query = DB::table('attendance')
            ->where('company_id', $companyId)
            ->where('inOutStatus', 'IN');

        /* ---------- EMPLOYEE FILTER ---------- */

        if ($employee) {
            $query->where('user_id', $employee);
        }

        /* ---------- SITE FILTER ---------- */

        if ($site) {
            $query->where('site_id', $site);
        }

        /* ---------- CLIENT FILTER ---------- */

        if ($client) {
            $query->where('client_name', $client);
        }

        /* ---------- DATE RANGE FILTER ---------- */

        if ($range == 'today') {
            $query->whereDate('dateFormat', today());
        }

        if ($range == 'week') {
            $query->whereBetween('dateFormat', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]);
        }

        if (!$range || $range == '30days') {
            $query->whereBetween('dateFormat', [
                now()->subDays(30),
                now()
            ]);
        }

        /* ---------- LOGS TABLE ---------- */

        $logs = $query
            ->orderByDesc('entryDateTime')
            ->paginate(10);

        /* ---------- METRICS (USE SAME FILTERED QUERY) ---------- */

        $filtered = clone $query;

        $total = $filtered->count();

        $onTime = (clone $query)
            ->where(function ($q) {
                $q->whereNull('lateTime')
                    ->orWhere('lateTime', '0');
            })
            ->count();

        $onTimePercent = $total > 0
            ? round(($onTime / $total) * 100, 1)
            : 0;

        $avgLate = (clone $query)
            ->where('lateTime', '>', 0)
            ->avg('lateTime');

        $avgLate = $avgLate ? round($avgLate) : 0;

        $incidents = (clone $query)
            ->where('emergency_attend', 1)
            ->count();
        $sites = DB::table('site_details')
            ->where('company_id', $companyId)
            ->select('id', 'name')
            ->get();
        $employees = DB::table('users')
            ->where('company_id', $companyId)
            ->where('isActive', 1)
            ->select('id', 'name')
            ->get();

        $clients = DB::table('site_details')
            ->where('company_id', $companyId)
            ->select('client_name')
            ->distinct()
            ->get();

        return view('attendance.logs', compact(
            'logs',
            'onTimePercent',
            'avgLate',
            'incidents',
            'sites',
            'employees',
            'clients'
        ));
    }

    public function requests()
    {
        $user = session('user');
        $companyId = $user->company_id;

        $cutoff = Carbon::now()->subDays(2)->startOfDay();

        $requests = DB::table('attendance_requests')
            ->where('company_id', $companyId)
            ->where('entryDateTime', '>=', $cutoff)
            ->orderByDesc('entryDateTime')
            ->paginate(10);

        return view('attendance.requests', compact('requests'));
    }

    public function approveRequest($id)
    {
        $requestData = DB::table('attendance_requests')->where('id', $id)->first();

        if (!$requestData) {
            return back()->with('error', 'Request not found');
        }

        DB::transaction(function () use ($requestData, $id) {

            DB::table('attendance')->updateOrInsert(
                [
                    'user_id' => $requestData->guard_id,
                    'dateFormat' => $requestData->dateFormat
                ],
                [
                    'name' => $requestData->guard_name,
                    'company_id' => $requestData->company_id,
                    'geo_id' => $requestData->geo_id,
                    'geo_name' => $requestData->geo_name,

                    'entryDateTime' => $requestData->entryDateTime,
                    'entry_time' => $requestData->time,
                    'entry_date_time' => $requestData->dateTime,

                    'date' => $requestData->date,
                    'dateFormat' => $requestData->dateFormat,

                    'site_id' => $requestData->site_id,
                    'site_name' => $requestData->site_name,

                    'client_id' => $requestData->client_id,
                    'role_id' => $requestData->role_id,

                    'photo' => $requestData->photo,
                    'location' => $requestData->location,

                    'inOutStatus' => 'IN',
                    'attendance_flag' => 1,

                    'approvedById' => session('user')->id ?? null,
                    'approvedBy' => session('user')->name ?? null
                ]
            );

            DB::table('attendance_requests')
                ->where('id', $id)
                ->update([
                    'status' => 'Approved',
                    'action_by' => session('user')->id ?? null
                ]);
        });

        return back()->with('success', 'Request approved & attendance added');
    }

    public function rejectRequest(Request $request, $id)
    {
        $request->validate([
            'remark' => 'required|string|max:500'
        ]);

        $updated = DB::table('attendance_requests')
            ->where('id', $id)
            ->update([
                'status' => 'Rejected',
                'remark' => $request->remark,
                'action_by' => session('user')->id ?? null
            ]);

        if (!$updated) {
            return back()->with('error', 'Request not found');
        }

        return back()->with('success', 'Request rejected');
    }

    public function mapView()
    {
        $user = session('user');
        $companyId = $user->company_id;

        // Get sites for the company
        $sites = DB::table('site_details')
            ->where('company_id', $companyId)
            ->select(
                'id',
                'name',
                'address',
                'city',
                'state',
                'pincode',
                'client_name'
            )
            ->get();

        // Guards currently checked in
        $guards = DB::table('attendance')
            ->where('company_id', $companyId)
            ->where('inOutStatus', 'IN')
            ->select('name', 'site_id', 'entryDateTime')
            ->get()
            ->groupBy('site_id');

        return view('attendance.map', compact('sites', 'guards'));
    }

    public function export(Request $request)
    {
        $user = session('user');
        $companyId = $user->company_id;

        $date = $request->date
            ? Carbon::parse($request->date)->toDateString()
            : today()->toDateString();

        $data = DB::table('attendance')
            ->join('users', 'attendance.user_id', '=', 'users.id')
            ->where('attendance.company_id', $companyId)
            ->whereDate('attendance.dateFormat', $date)
            ->select(
                'users.name',
                'attendance.site_id',
                'attendance.dateFormat',
                'attendance.entryDateTime',
                'attendance.lateTime'
            )
            ->get();

        $filename = "attendance_$date.csv";

        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
        ];

        $callback = function () use ($data) {

            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Name',
                'Site',
                'Date',
                'Check In Time',
                'Late Minutes'
            ]);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->name,
                    $row->site_id,
                    $row->dateFormat,
                    $row->entryDateTime,
                    $row->lateTime
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


    public function showAttendanceMap(Request $request)
    {
        $guardId = $request->input('guardId');
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');
        $user = session('user');
        // Fetch attendance data for the guard
        if ($guardId == 0) {
            $attendanceData = Attendance::where('company_id', $user->company_id)
                ->whereBetween('dateFormat', [$fromDate, $toDate])
                ->get();
        } else {
            $attendanceData = Attendance::where('user_id', $guardId)
                ->whereBetween('dateFormat', [$fromDate, $toDate])
                ->get();
        }

        $geofences = SiteGeofences::where('company_id', $user->company_id)->select('name', 'center', 'radius', 'type', 'poly_lat_lng', 'site_name')->get();
        // dump($attendanceData  , "attendance data");
        return view('maps.userAttendanceMap', compact('attendanceData', 'geofences'));
    }
}

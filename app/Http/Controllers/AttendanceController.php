<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FilterDataTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Services\RoleBasedFilterService;

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
            $companyId = $user->company_id ?? 56;

            $accessibleUserIds = RoleBasedFilterService::getAccessibleUserIds();

            /* ================= DATE RANGE ================= */

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $startDate = Carbon::parse($request->start_date)->startOfDay();
                $endDate = Carbon::parse($request->end_date)->endOfDay();
            }
            else {
                $startDate = now()->subDays(30)->startOfDay();
                $endDate = now()->endOfDay();
            }

            $dates = collect(CarbonPeriod::create($startDate, $endDate))
                ->map(fn($d) => $d->copy());

            $totalDaysInRange = $dates->count();


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
                ->whereBetween('dateFormat', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ])
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

            $totalGuards = $users->count();

            $totalPresentManDays = collect($grid)
                ->sum(fn($g) => $g['summary']['present']);

            $totalPossibleManDays = $totalGuards * $totalDaysInRange;

            $totalAbsentManDays = $totalPossibleManDays - $totalPresentManDays;

            $presentPct = $totalPossibleManDays > 0
                ? round(($totalPresentManDays / $totalPossibleManDays) * 100, 1)
                : 0;


            /* ================= DAILY TREND ================= */

            $dailyTrend = $dates->map(function ($dt) use ($grid) {

                $dateStr = $dt->toDateString();

                $presentList = [];
                $absentList = [];

                foreach ($grid as $uid => $data) {

                    $userObj = [
                        'id' => $uid,
                        'name' => \App\Helpers\FormatHelper::formatName($data['user']->name)
                    ];

                    if (!empty($data['days'][$dateStr]['present'])) {
                        $presentList[] = $userObj;
                    }
                    else {
                        $absentList[] = $userObj;
                    }
                }

                return [
                'date' => $dt->format('d M'),
                'full_date' => $dateStr,
                'present' => count($presentList),
                'absent' => count($absentList),
                'present_list' => $presentList,
                'absent_list' => $absentList
                ];
            });


            /* ================= TOP DEFAULTERS ================= */

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


            /* ================= DASHBOARD KPIs ================= */

            $presentToday = DB::table('attendance')
                ->where('company_id', $companyId)
                ->whereDate('dateFormat', today())
                ->distinct()
                ->count('user_id');

            $absentToday = max(0, $totalGuards - $presentToday);

            $lateToday = DB::table('attendance')
                ->where('company_id', $companyId)
                ->whereDate('dateFormat', today())
                ->whereNotNull('lateTime')
                ->where('lateTime', '!=', '0')
                ->count();

            $activeSites = DB::table('attendance')
                ->where('company_id', $companyId)
                ->whereDate('dateFormat', today())
                ->distinct()
                ->count('site_id');

            $pendingRequests = \Illuminate\Support\Facades\Schema::hasTable('leave_requests')
                ?DB::table('leave_requests')->where('status', 'pending')->count()
                : 0;

            $recentCheckins = DB::table('attendance')
                ->join('users', 'attendance.user_id', '=', 'users.id')
                ->whereDate('attendance.dateFormat', today())
                ->orderByDesc('attendance.entryDateTime')
                ->limit(6)
                ->select(
                'users.name',
                'users.profile_pic',
                DB::raw("TIME(attendance.entryDateTime) as time"),
                'attendance.site_id as site'
            )
                ->get();

            $last7 = $dailyTrend->sortByDesc('full_date')->take(7)->sortBy('full_date')->values();
            $weeklyLabels = $last7->pluck('date')->toArray();
            $weeklyPresent = $last7->pluck('present')->toArray();
            $weeklyAbsent = $last7->pluck('absent')->toArray();

            $filterData = $this->filterData();
        }
        catch (\Exception $e) {

            $grid = [];
            $dates = [];
            $dailyTrend = collect([]);
            $defaulters = collect([]);

            $presentPct = 0;
            $totalPresentManDays = 0;
            $totalAbsentManDays = 0;
            $totalGuards = 0;

            $startDate = now();
            $endDate = now();
            $presentToday = 0;
            $absentToday = 0;
            $lateToday = 0;
            $activeSites = 0;
            $pendingRequests = 0;
            $recentCheckins = collect([]);
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
            'weeklyAbsent'
        )
        ));
    }

    public function logs()
    {
        $user = session('user');
        $companyId = $user->company_id ?? 56;

        $logs = DB::table('attendance')
            ->where('company_id', $companyId)
            ->where('inOutStatus', 'IN')
            ->orderByDesc('entryDateTime')
            ->paginate(10);

        /* ---------- METRICS ---------- */

        $total = DB::table('attendance')
            ->where('company_id', $companyId)
            ->where('inOutStatus', 'IN')
            ->count();

        $onTime = DB::table('attendance')
            ->where('company_id', $companyId)
            ->where('inOutStatus', 'IN')
            ->where(function ($q) {
            $q->whereNull('lateTime')
                ->orWhere('lateTime', '0');
        })
            ->count();

        $onTimePercent = $total > 0
            ? round(($onTime / $total) * 100, 1)
            : 0;

        $avgLate = DB::table('attendance')
            ->where('company_id', $companyId)
            ->where('lateTime', '>', 0)
            ->avg('lateTime');

        $avgLate = $avgLate ? round($avgLate) : 0;

        $incidents = DB::table('attendance')
            ->where('company_id', $companyId)
            ->where('emergency_attend', 1)
            ->count();

        return view('attendance.logs', compact(
            'logs',
            'onTimePercent',
            'avgLate',
            'incidents'
        ));
    }
    public function requests()
    {
        $user = session('user');
        $companyId = $user->company_id ?? 56;

        $requests = DB::table('attendance_requests')
            ->where('company_id', $companyId)
            ->orderByDesc('entryDateTime')
            ->paginate(5);

        return view('attendance.requests', compact('requests'));
    }

    public function approveRequest($id)
    {
        DB::table('attendance_requests')
            ->where('id', $id)
            ->update([
            'status' => 'Approved'
        ]);

        return back()->with('success', 'Request approved');
    }

    public function rejectRequest(Request $request, $id)
    {
        DB::table('attendance_requests')
            ->where('id', $id)
            ->update([
            'status' => 'Rejected',
            'remark' => $request->remark
        ]);

        return back()->with('success', 'Request rejected');
    }

    public function mapView()
    {
        $user = session('user');
        $companyId = $user->company_id ?? 56;

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
}
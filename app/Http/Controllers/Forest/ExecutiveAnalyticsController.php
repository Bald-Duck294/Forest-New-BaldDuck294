<?php

namespace App\Http\Controllers\Forest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\Forest\Traits\FilterDataTrait;
use App\Helpers\FormatHelper;

class ExecutiveAnalyticsController extends Controller
{
    use FilterDataTrait;

    public function executiveDashboard(Request $request)
    {
        // Default date range: last 30 days
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)
            : Carbon::now()->subDays(30);

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)
            : Carbon::now();

        return view('forest.analytics.executive-dashboard', array_merge(
            $this->filterData(),
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'kpis' => $this->getKPIs($startDate, $endDate),
                'guardPerformance' => $this->getGuardPerformanceRankings($startDate, $endDate),
                'incidentTracking' => $this->getIncidentStatusTracking($startDate, $endDate),
                'patrolAnalytics' => $this->getPatrolAnalytics($startDate, $endDate),
                'attendanceAnalytics' => $this->getAttendanceAnalytics($startDate, $endDate),
                'timePatterns' => $this->getTimeBasedPatterns($startDate, $endDate),
                'riskZones' => $this->getRiskZoneAnalysis($startDate, $endDate),
                'coverageAnalysis' => $this->getCoverageAnalysis($startDate, $endDate),
                'efficiencyMetrics' => $this->getEfficiencyMetrics($startDate, $endDate),
            ]
        ));
    }

    private function getKPIs(Carbon $startDate, Carbon $endDate): array
    {
        $user = session('user');
        $siteIds = $this->resolveSiteIds();
        $userId = request('user');

        // Active Guards
        $activeGuardsQuery = DB::table('users')->where('isActive', 1)->where('users.company_id', $user->company_id);
        if ($userId) {
            $activeGuardsQuery->where('users.id', $userId);
        }
        $activeGuards = $activeGuardsQuery->count();

        // Patrols
        $patrolQuery = DB::table('patrol_sessions')
            ->where('patrol_sessions.company_id', $user->company_id)
            ->whereBetween('patrol_sessions.started_at', [$startDate, $endDate]);

        if (!empty($siteIds)) {
            $patrolQuery->whereIn('patrol_sessions.site_id', $siteIds);
        }
        if ($userId) {
            $patrolQuery->where('patrol_sessions.user_id', $userId);
        }

        $totalPatrols = (clone $patrolQuery)->count();
        $completedPatrols = (clone $patrolQuery)->whereNotNull('ended_at')->count();
        $ongoingPatrols = $totalPatrols - $completedPatrols;

        // Distance
        $totalDistance = round((clone $patrolQuery)->whereNotNull('ended_at')->sum('distance') / 1000, 2);
        // Avg distance depends on active filtered guards
        $avgDistancePerGuard = $activeGuards > 0 ? round($totalDistance / $activeGuards, 2) : 0;

        // Attendance
        // NOTE: attendance table contains ONLY presence logs.
        // Total guards must be derived from users (active + assigned to filtered sites).
        $guardQuery = DB::table('users')
            ->join('site_assign', 'users.id', '=', 'site_assign.user_id')
            ->where('users.isActive', 1)
            ->where('users.company_id', $user->company_id);

        if (request()->filled('range')) {
            $guardQuery->where('site_assign.client_id', request('range'));
        }

        if ($userId) {
            $guardQuery->where('users.id', $userId);
        }

        // Beat/Compartment filters resolve to site_details.id values.
        // site_assign.site_id is stored as CSV; filter via FIND_IN_SET.
        if (!empty($siteIds)) {
            $guardQuery->where(function ($q) use ($siteIds) {
                foreach ($siteIds as $sid) {
                    $q->orWhereRaw('FIND_IN_SET(?, site_assign.site_id)', [$sid]);
                }
            });
        }

        $guardIds = (clone $guardQuery)
            ->distinct()
            ->pluck('users.id');

        $totalGuardsForAttendance = $guardIds->count();
        // If user filter is active but user not found in assignment logic, default to 0 to avoid div by zero, 
        // or 1 if we assume the user exists but maybe has no assignment.
        // If query returned 0, it means no valid assigned user found.

        $attendanceQuery = DB::table('attendance')
            ->where('attendance.company_id', $user->company_id)
            ->whereBetween('attendance.dateFormat', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereIn('attendance.user_id', $guardIds);

        if (!empty($siteIds)) {
            // Strict filtering by site actually logged
            $attendanceQuery->whereIn('attendance.site_id', $siteIds);
        }

        if ($userId) {
            $attendanceQuery->where('attendance.user_id', $userId);
        }

        $presentCount = (clone $attendanceQuery)
            ->distinct('attendance.user_id')
            ->count('attendance.user_id');

        $attendanceRate = $totalGuardsForAttendance > 0
            ? round(($presentCount / $totalGuardsForAttendance) * 100, 1)
            : 0;

        // Incidents
        $incidentQuery = DB::table('incidence_details')
            ->where('incidence_details.company_id', $user->company_id)
            ->whereBetween('incidence_details.dateFormat', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        if (!empty($siteIds)) {
            $incidentQuery->whereIn('incidence_details.site_id', $siteIds);
        }
        if ($userId) {
            $incidentQuery->where('incidence_details.guard_id', $userId);
        }

        $totalIncidents = (clone $incidentQuery)->count();
        $pendingIncidents = (clone $incidentQuery)
            ->whereIn('incidence_details.statusFlag', [0, 3, 4]) // pendingSupervisor, escalateToAdmin, pendingAdmin
            ->count();

        $resolvedIncidents = (clone $incidentQuery)->where('incidence_details.statusFlag', 1)->count();
        $resolutionRate = $totalIncidents > 0 ? round(($resolvedIncidents / $totalIncidents) * 100, 1) : 0;

        // Sites
        $siteQuery = DB::table('site_details')->where('site_details.company_id', $user->company_id);
        if (!empty($siteIds)) {
            $siteQuery->whereIn('id', $siteIds);
        }
        $totalSites = $siteQuery->count();

        return [
            'activeGuards' => $activeGuards,
            'totalPatrols' => $totalPatrols,
            'completedPatrols' => $completedPatrols,
            'ongoingPatrols' => $ongoingPatrols,
            'totalDistance' => $totalDistance,
            'avgDistancePerGuard' => $avgDistancePerGuard,
            'attendanceRate' => $attendanceRate,
            'presentCount' => $presentCount,
            'totalIncidents' => $totalIncidents,
            'pendingIncidents' => $pendingIncidents,
            'resolvedIncidents' => $resolvedIncidents,
            'resolutionRate' => $resolutionRate,
            'totalSites' => $totalSites,
        ];
    }

    private function getGuardPerformanceRankings(Carbon $startDate, Carbon $endDate): array
    {
        $user = session('user');
        $siteIds = $this->resolveSiteIds();
        $userId = request('user');

        // Get guard performance data
        $patrolQuery = DB::table('patrol_sessions')
            ->join('users', 'patrol_sessions.user_id', '=', 'users.id')
            ->whereBetween('patrol_sessions.started_at', [$startDate, $endDate])
            ->whereNotNull('patrol_sessions.ended_at')
            ->where('users.isActive', 1)
            ->where('users.company_id', $user->company_id);

        if (!empty($siteIds)) {
            $patrolQuery->whereIn('patrol_sessions.site_id', $siteIds);
        }
        if ($userId) {
            $patrolQuery->where('patrol_sessions.user_id', $userId);
        }

        $guardPatrols = (clone $patrolQuery)
            ->selectRaw('
                users.id,
                users.name,
                COUNT(*) as patrol_sessions,
                ROUND(SUM(patrol_sessions.distance) / 1000, 2) as total_distance_km
            ')
            ->groupBy('users.id', 'users.name')
            ->get();

        // Get attendance data
        $attendanceQuery = DB::table('attendance')
            ->join('users', 'attendance.user_id', '=', 'users.id')
            ->whereBetween('attendance.dateFormat', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('users.isActive', 1)
            ->where('attendance.company_id', $user->company_id);

        if (!empty($siteIds)) {
            $attendanceQuery->whereIn('attendance.site_id', $siteIds);
        }
        if ($userId) {
            $attendanceQuery->where('attendance.user_id', $userId);
        }

        $guardAttendance = (clone $attendanceQuery)
            ->selectRaw('
                users.id,
                users.name,
                COUNT(DISTINCT attendance.dateFormat) as days_present
            ')
            ->groupBy('users.id', 'users.name')
            ->get()
            ->keyBy('id');

        // Get incident reports
        $incidentQuery = DB::table('incidence_details')
            ->join('users', 'incidence_details.guard_id', '=', 'users.id')
            ->whereBetween('incidence_details.dateFormat', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('users.isActive', 1)
            ->where('incidence_details.company_id', $user->company_id);

        if (!empty($siteIds)) {
            $incidentQuery->whereIn('incidence_details.site_id', $siteIds);
        }
        if ($userId) {
            $incidentQuery->where('incidence_details.guard_id', $userId);
        }

        $guardIncidents = (clone $incidentQuery)
            ->selectRaw('
                users.id,
                users.name,
                COUNT(*) as incidents_reported
            ')
            ->groupBy('users.id', 'users.name')
            ->get()
            ->keyBy('id');

        // Base List of Guards to Show
        $allActiveGuardsQuery = DB::table('users')
            ->where('users.isActive', 1)
            ->where('users.company_id', $user->company_id)
            ->select('users.id', 'users.name');

        if ($userId) {
            $allActiveGuardsQuery->where('users.id', $userId);
        }

        $allActiveGuards = $allActiveGuardsQuery->get();

        $fullPerformance = collect();
        foreach ($allActiveGuards as $guard) {
            $patrol = $guardPatrols->firstWhere('id', $guard->id);
            $attendance = $guardAttendance->get($guard->id);
            $incidents = $guardIncidents->get($guard->id);

            $score = ($patrol ? $patrol->total_distance_km : 0) * 0.4 +
                ($attendance ? $attendance->days_present : 0) * 10 * 0.3 +
                ($incidents ? $incidents->incidents_reported : 0) * 20 * 0.3;

            $fullPerformance->push((object) [
                'id' => $guard->id,
                'name' => FormatHelper::formatName($guard->name),
                'patrol_sessions' => $patrol ? $patrol->patrol_sessions : 0,
                'total_distance_km' => $patrol ? $patrol->total_distance_km : 0,
                'days_present' => $attendance ? $attendance->days_present : 0,
                'incidents_reported' => $incidents ? $incidents->incidents_reported : 0,
                'performance_score' => round($score, 1),
            ]);
        }

        // Sort 
        $sortedPerformance = $fullPerformance->sortByDesc('performance_score')->values();

        // Limit results for dashboard stability
        $displayLimit = $userId ? null : 50; 
        $limitedPerformance = $displayLimit ? $sortedPerformance->take($displayLimit) : $sortedPerformance;

        return [
            'topPerformers' => $sortedPerformance->take(5)->values(),
            'fullPerformance' => $limitedPerformance,
            'totalCount' => $sortedPerformance->count(),
            'isLimited' => $displayLimit && $sortedPerformance->count() > $displayLimit,
        ];
    }

    private function getIncidentStatusTracking(Carbon $startDate, Carbon $endDate): array
    {
        $user = session('user');
        // $siteGeofences = DB::table('site_geofences')
        //     ->where('company_id', $user->company_id)
        //     ->selectRaw('site_id, MAX(client_id) as client_id, MAX(site_name) as site_name')
        //     ->groupBy('site_id');

        $base = DB::table('patrol_logs')
            ->where('patrol_logs.company_id', $user->company_id)
            ->join('patrol_sessions', 'patrol_sessions.id', '=', 'patrol_logs.patrol_session_id')
            ->leftJoin('users', 'users.id', '=', 'patrol_sessions.user_id')
            ->leftJoin('site_details', 'site_details.id', '=', 'patrol_sessions.site_id')
            ->whereIn('patrol_logs.type', [
                'animal_sighting',
                'water_source',
                'human_impact',
                'animal_mortality'
            ])
            ->whereBetween('patrol_logs.created_at', [
                $startDate->copy()->startOfDay(),
                $endDate->copy()->endOfDay()
            ]);

        $this->applyCanonicalFilters($base, 'patrol_logs.created_at', 'patrol_sessions.site_id', 'patrol_sessions.user_id');

        // Distribution queries
        $statusDistribution = (clone $base)
            ->selectRaw('
                CASE
                    WHEN patrol_logs.type = "animal_mortality" THEN 5
                    WHEN patrol_logs.type = "human_impact" THEN 4
                    WHEN patrol_logs.type = "animal_sighting" THEN 3
                    WHEN patrol_logs.type = "water_source" THEN 2
                    ELSE 1
                END as statusFlag,
                COUNT(*) as count
            ')
            ->groupByRaw('statusFlag')
            ->get()
            ->pluck('count', 'statusFlag');

        $priorityDistribution = (clone $base)
            ->selectRaw('
                CASE
                    WHEN patrol_logs.type IN ("animal_mortality", "human_impact") THEN 0
                    WHEN patrol_logs.type = "animal_sighting" THEN 1
                    ELSE 2
                END as priorityFlag,
                COUNT(*) as count
            ')
            ->groupByRaw('priorityFlag')
            ->get()
            ->pluck('count', 'priorityFlag');

        $incidentTypes = (clone $base)
            ->selectRaw('patrol_logs.type as type, COUNT(*) as count')
            ->groupBy('patrol_logs.type')
            ->orderByDesc('count')
            ->get();

        $criticalIncidents = (clone $base)
            ->whereIn('patrol_logs.type', ['animal_mortality', 'human_impact'])
            ->orderByDesc('patrol_logs.created_at')
            ->selectRaw('
                patrol_logs.id,
                patrol_logs.type,
                site_details.name as site_name,
                users.name as guard_name,
                patrol_logs.created_at as dateFormat,
                "High" as priority,
                0 as statusFlag
            ')
            ->limit(10)
            ->get();

        $resolutionTime = collect();

        $incidentsBySite = (clone $base)
            ->selectRaw('
                patrol_sessions.site_id as site_id,
                site_details.name as site_name,
                COUNT(*) as incident_count,
                0 as resolved_count,
                COUNT(*) as pending_count
            ')
            ->groupBy('patrol_sessions.site_id', 'site_details.name')
            ->orderByDesc('incident_count')
            ->limit(20)
            ->get()
            ->map(function ($site) {
                $site->resolution_percentage = 0;
                return $site;
            });

        return [
            'statusDistribution' => $statusDistribution,
            'priorityDistribution' => $priorityDistribution,
            'incidentTypes' => $incidentTypes,
            'criticalIncidents' => $criticalIncidents,
            'resolutionTime' => $resolutionTime,
            'incidentsBySite' => $incidentsBySite,
        ];
    }

    private function getPatrolAnalytics(Carbon $startDate, Carbon $endDate): array
    {
        $user = session('user');
        $siteIds = $this->resolveSiteIds();
        $userId = request('user');

        $query = DB::table('patrol_sessions')
            ->whereBetween('patrol_sessions.started_at', [$startDate, $endDate])
            ->where('patrol_sessions.company_id', $user->company_id);

        if (!empty($siteIds)) {
            $query->whereIn('patrol_sessions.site_id', $siteIds);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $patrolByType = (clone $query)
            ->whereNotNull('ended_at')
            ->selectRaw('
                type, 
                COUNT(*) as count,
                ROUND(SUM(distance) / 1000, 2) as total_distance_km
            ')
            ->groupBy('type')
            ->get();

        $patrolBySession = (clone $query)
            ->selectRaw('session, COUNT(*) as count')
            ->groupBy('session')
            ->get();

        $footPatrols = (clone $query)->where('session', 'Foot')->count();
        $nightPatrols = (clone $query)
            ->where(function ($q) {
                $q->whereTime('started_at', '>=', '18:00:00')
                    ->orWhereTime('started_at', '<=', '06:00:00');
            })
            ->count();

        $dailyTrend = (clone $query)
            ->whereNotNull('ended_at')
            ->selectRaw('
                DATE(started_at) as date,
                COUNT(*) as patrol_count,
                ROUND(SUM(distance) / 1000, 2) as distance_km
            ')
            ->groupBy(DB::raw('DATE(started_at)'))
            ->orderBy('date')
            ->get();

        $distanceBySite = (clone $query)
            ->join('site_details', 'patrol_sessions.site_id', '=', 'site_details.id')
            ->whereNotNull('patrol_sessions.ended_at')
            ->selectRaw('
                site_details.name as site_name,
                ROUND(SUM(patrol_sessions.distance) / 1000, 2) as distance_km
            ')
            ->groupBy('site_details.id', 'site_details.name')
            ->orderByDesc('distance_km')
            ->limit(10)
            ->get();

        return [
            'patrolByType' => $patrolByType,
            'patrolBySession' => $patrolBySession,
            'footPatrols' => $footPatrols,
            'nightPatrols' => $nightPatrols,
            'dailyTrend' => $dailyTrend,
            'distanceBySite' => $distanceBySite,
        ];
    }

    private function getAttendanceAnalytics(Carbon $startDate, Carbon $endDate): array
    {
        $user = session('user');
        $siteIds = $this->resolveSiteIds();
        $userId = request('user');

        $query = DB::table('attendance')
            ->whereBetween('attendance.dateFormat', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('attendance.company_id', $user->company_id);

        if (!empty($siteIds)) {
            $query->whereIn('attendance.site_id', $siteIds);
        }
        if ($userId) {
            $query->where('attendance.user_id', $userId);
        }

        $dailyTrend = (clone $query)
            ->selectRaw('
                attendance.dateFormat as date,
                COUNT(DISTINCT user_id) as present,
                0 as absent,
                SUM(CASE WHEN lateTime IS NOT NULL AND lateTime > 0 THEN 1 ELSE 0 END) as late
            ')
            ->groupBy('attendance.dateFormat')
            ->orderBy('attendance.dateFormat')
            ->get();

        $lateAttendance = (clone $query)
            ->join('users', 'attendance.user_id', '=', 'users.id')
            ->whereNotNull('lateTime')
            ->where('lateTime', '>', 0)
            ->where('attendance_flag', 1)
            ->selectRaw('
                users.name,
                COUNT(*) as late_count,
                AVG(CAST(lateTime AS UNSIGNED)) as avg_late_minutes
            ')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('late_count')
            ->limit(10)
            ->get();

        $attendanceBySite = (clone $query)
            ->join('site_details', 'attendance.site_id', '=', 'site_details.id')
            ->selectRaw('
                site_details.name as site_name,
                SUM(CASE WHEN attendance_flag = 1 THEN 1 ELSE 0 END) as present_count,
                COUNT(*) as total_count
            ')
            ->groupBy('site_details.id', 'site_details.name')
            ->orderByDesc('present_count')
            ->get();

        return [
            'dailyTrend' => $dailyTrend,
            'lateAttendance' => $lateAttendance,
            'attendanceBySite' => $attendanceBySite,
        ];
    }

    private function getTimeBasedPatterns(Carbon $startDate, Carbon $endDate): array
    {
        $user = session('user');
        $siteIds = $this->resolveSiteIds();
        $userId = request('user');

        $query = DB::table('patrol_sessions')
            ->whereBetween('patrol_sessions.started_at', [$startDate, $endDate])
            ->where('patrol_sessions.company_id', $user->company_id);

        if (!empty($siteIds)) {
            $query->whereIn('patrol_sessions.site_id', $siteIds);
        }
        if ($userId) {
            $query->where('patrol_sessions.user_id', $userId);
        }

        $hourlyDistribution = (clone $query)
            ->selectRaw('HOUR(started_at) as hour, COUNT(*) as count')
            ->groupBy(DB::raw('HOUR(started_at)'))
            ->orderBy('hour')
            ->get();

        $peakHours = (clone $query)
            ->selectRaw('HOUR(started_at) as hour, COUNT(*) as count')
            ->groupBy(DB::raw('HOUR(started_at)'))
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $dayOfWeek = (clone $query)
            ->selectRaw('DAYNAME(started_at) as day_name, DAYOFWEEK(started_at) as day_num, COUNT(*) as count')
            ->groupBy('day_name', 'day_num')
            ->orderBy('day_num')
            ->get();

        return [
            'hourlyDistribution' => $hourlyDistribution,
            'peakHours' => $peakHours,
            'dayOfWeek' => $dayOfWeek,
        ];
    }

    private function getRiskZoneAnalysis(Carbon $startDate, Carbon $endDate): array
    {
        $user = session('user');
        $siteIds = $this->resolveSiteIds();
        $userId = request('user');
        // Note: Risk zone analysis typically focuses on SITES, but if a User is selected we might just show relevant sites.

        // High incident zones
        $incidentQuery = DB::table('incidence_details')
            ->whereBetween('incidence_details.dateFormat', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereIn('incidence_details.type', ['animal_mortality', 'human_impact'])
            ->where('incidence_details.company_id', $user->company_id);

        if (!empty($siteIds)) {
            $incidentQuery->whereIn('incidence_details.site_id', $siteIds);
        }
        if ($userId) {
            $incidentQuery->where('incidence_details.guard_id', $userId);
        }

        $highIncidentZones = (clone $incidentQuery)
            ->selectRaw('
                site_name,
                COUNT(*) as incident_count,
                SUM(CASE WHEN type = "animal_mortality" THEN 1 ELSE 0 END) as mortality_count,
                SUM(CASE WHEN type = "human_impact" THEN 1 ELSE 0 END) as human_impact_count
            ')
            ->groupBy('site_name')
            ->havingRaw('COUNT(*) >= 2')
            ->orderByDesc('incident_count')
            ->get();

        // Coverage gaps (sites with no patrols) - harder to filter by User comfortably (does user X not patrolling site Y mean a gap? Maybe.)
        // We will skip user filter on "All Sites" but apply to "Patrolled Sites" to see what the user missed?
        // Let's keep it site-focused for now, maybe ignoring user filter for "All Sites" base.
        $allSites = DB::table('site_details')
            ->where('site_details.company_id', $user->company_id)
            ->select('site_details.id', 'site_details.name');

        if (!empty($siteIds)) {
            $allSites->whereIn('site_details.id', $siteIds);
        }
        $allSites = $allSites->get();

        $patrolledSites = DB::table('patrol_sessions')
            ->whereBetween('started_at', [$startDate, $endDate])
            ->whereNotNull('site_id');

        if (!empty($siteIds)) {
            $patrolledSites->whereIn('site_id', $siteIds);
        }
        if ($userId) {
            $patrolledSites->where('user_id', $userId);
        }

        $patrolledSitesIds = $patrolledSites->distinct()->pluck('site_id')->toArray();

        $coverageGaps = $allSites->whereNotIn('id', $patrolledSitesIds)->values();

        // Most patrolled sites
        $mostPatrolled = DB::table('patrol_sessions')
            ->join('site_details', 'patrol_sessions.site_id', '=', 'site_details.id')
            ->whereBetween('patrol_sessions.started_at', [$startDate, $endDate])
            ->where('patrol_sessions.company_id', $user->company_id);

        if (!empty($siteIds)) {
            $mostPatrolled->whereIn('patrol_sessions.site_id', $siteIds);
        }
        if ($userId) {
            $mostPatrolled->where('patrol_sessions.user_id', $userId);
        }

        $mostPatrolled = (clone $mostPatrolled)
            ->selectRaw('site_details.name as site_name, COUNT(*) as patrol_count')
            ->groupBy('site_details.id', 'site_details.name')
            ->orderByDesc('patrol_count')
            ->limit(10)
            ->get();

        return [
            'highIncidentZones' => $highIncidentZones,
            'coverageGaps' => $coverageGaps,
            'mostPatrolled' => $mostPatrolled,
        ];
    }

    private function getCoverageAnalysis(Carbon $startDate, Carbon $endDate): array
    {
        $user = session('user');
        $siteIds = $this->resolveSiteIds();
        $userId = request('user');

        $allSitesQuery = DB::table('site_details')
            ->where('site_details.company_id', $user->company_id);
        if (!empty($siteIds)) {
            $allSitesQuery->whereIn('site_details.id', $siteIds);
        }
        $totalSites = $allSitesQuery->count();

        $patrolledSitesQuery = DB::table('patrol_sessions')
            ->whereBetween('started_at', [$startDate, $endDate])
            ->whereNotNull('site_id');

        if (!empty($siteIds)) {
            $patrolledSitesQuery->whereIn('site_id', $siteIds);
        }
        if ($userId) {
            $patrolledSitesQuery->where('user_id', $userId);
        }

        $sitesWithPatrols = $patrolledSitesQuery->distinct('site_id')->count('site_id');
        $coveragePercentage = $totalSites > 0 ? round(($sitesWithPatrols / $totalSites) * 100, 1) : 0;

        // Sites most patrolled
        $sitesMostPatrolled = DB::table('patrol_sessions')
            ->join('site_details', 'patrol_sessions.site_id', '=', 'site_details.id')
            ->whereBetween('patrol_sessions.started_at', [$startDate, $endDate])
            ->where('patrol_sessions.company_id', $user->company_id);

        if (!empty($siteIds)) {
            $sitesMostPatrolled->whereIn('patrol_sessions.site_id', $siteIds);
        }
        if ($userId) {
            $sitesMostPatrolled->where('patrol_sessions.user_id', $userId);
        }

        $sitesMostPatrolled = (clone $sitesMostPatrolled)
            ->selectRaw('site_details.name as site_name, COUNT(*) as patrol_count')
            ->groupBy('site_details.id', 'site_details.name')
            ->orderByDesc('patrol_count')
            ->limit(10)
            ->get();

        // Sites least patrolled query logic slightly complex with user filter, simplification:
        $sitesLeastPatrolled = DB::table('site_details')
            ->leftJoin('patrol_sessions', function ($join) use ($startDate, $endDate, $userId) {
                $join->on('site_details.id', '=', 'patrol_sessions.site_id')
                    ->whereBetween('patrol_sessions.started_at', [$startDate, $endDate]);
                if ($userId) {
                    $join->where('patrol_sessions.user_id', $userId);
                }
            })
            ->where('site_details.company_id', $user->company_id)
            ->selectRaw('
                site_details.name as site_name,
                COUNT(patrol_sessions.id) as patrol_count
            ')
            ->groupBy('site_details.id', 'site_details.name')
            ->orderBy('patrol_count')
            ->limit(10);

        if (!empty($siteIds)) {
            $sitesLeastPatrolled->whereIn('site_details.id', $siteIds);
        }

        $sitesLeastPatrolled = $sitesLeastPatrolled->get();

        return [
            'totalSites' => $totalSites,
            'sitesWithPatrols' => $sitesWithPatrols,
            'coveragePercentage' => $coveragePercentage,
            'sitesMostPatrolled' => $sitesMostPatrolled,
            'sitesLeastPatrolled' => $sitesLeastPatrolled,
        ];
    }

    private function getEfficiencyMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $user = session('user');
        $siteIds = $this->resolveSiteIds();
        $userId = request('user');

        $query = DB::table('patrol_sessions')
            ->whereBetween('patrol_sessions.started_at', [$startDate, $endDate])
            ->where('patrol_sessions.company_id', $user->company_id);

        if (!empty($siteIds)) {
            $query->whereIn('patrol_sessions.site_id', $siteIds);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Average patrol duration
        $avgDuration = (clone $query)
            ->whereNotNull('ended_at')
            ->selectRaw('
                AVG(TIMESTAMPDIFF(HOUR, started_at, ended_at)) as avg_hours
            ')
            ->first();

        // Average speed
        $avgSpeed = (clone $query)
            ->whereNotNull('ended_at')
            ->whereNotNull('distance')
            ->where('distance', '>', 0)
            ->selectRaw('
                AVG((distance / 1000) / NULLIF(TIMESTAMPDIFF(HOUR, started_at, ended_at), 0)) as avg_km_per_hour
            ')
            ->first();

        // Completion rate
        $totalPatrols = (clone $query)->count();
        $completedPatrols = (clone $query)->whereNotNull('ended_at')->count();
        $completionRate = $totalPatrols > 0 ? round(($completedPatrols / $totalPatrols) * 100, 1) : 0;

        // Guard efficiency table
        $guardEfficiency = (clone $query)
            ->join('users', 'patrol_sessions.user_id', '=', 'users.id')
            ->whereNotNull('patrol_sessions.ended_at')
            ->where('users.isActive', 1)
            ->selectRaw('
                users.id as user_id,
                users.name,
                COUNT(*) as session_count,
                ROUND(SUM(patrol_sessions.distance) / 1000, 2) as total_distance_km,
                ROUND(AVG(patrol_sessions.distance) / 1000, 2) as avg_distance_per_session,
                ROUND(AVG(TIMESTAMPDIFF(HOUR, patrol_sessions.started_at, patrol_sessions.ended_at)), 2) as avg_duration_hours
            ')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_distance_km')
            ->get()
            ->map(function ($item) {
                $item->name = FormatHelper::formatName($item->name);
                return $item;
            });

        return [
            'avgDurationHours' => $avgDuration ? round($avgDuration->avg_hours, 2) : 0,
            'avgSpeedKmPerHour' => $avgSpeed ? round($avgSpeed->avg_km_per_hour, 2) : 0,
            'completionRate' => $completionRate,
            'guardEfficiency' => $guardEfficiency,
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Media;
use Illuminate\Http\Request;
use App\PatrolSession;
use App\PatrolLog;
use App\SiteAssign;
use App\SiteDetails;
use App\ClientDetails;
use App\User;
use App\SiteGeofences;
use Carbon\Carbon;

class PatrolAnalysisController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = session('user');

        // Fetch today's by default
        $dateFrom = $request->date_from ?? date('Y-m-d');
        $dateTo = $request->date_to ?? date('Y-m-d');

        $sessions = PatrolSession::with(['user', 'site'])
            ->where('company_id', $user->company_id)
            ->whereDate('started_at', '>=', $dateFrom)
            ->whereDate('started_at', '<=', $dateTo)
            ->get();

        $sessionIds = $sessions->pluck('id')->toArray();

        $logs = PatrolLog::where('company_id', $user->company_id)
            ->whereIn('patrol_session_id', $sessionIds)
            ->get();


        // ----------------------------------------------------
        // 🧮 1. COMPUTE DISTANCES FOR EACH SESSION
        // ----------------------------------------------------
        $sessions = $sessions->map(function ($s) {

            $coords = [];

            if (!empty($s->path_geojson)) {
                $raw = is_string($s->path_geojson) ? json_decode($s->path_geojson, true) : $s->path_geojson;

                if (isset($raw['type']) && $raw['type'] === 'LineString') {
                    $coords = $raw['coordinates'];
                }

                if (isset($raw['geometry']['type']) && $raw['geometry']['type'] === 'LineString') {
                    $coords = $raw['geometry']['coordinates'];
                }
            }

            // Compute distance
            $distance = 0;
            if (count($coords) > 1) {
                for ($i = 1; $i < count($coords); $i++) {
                    $lat1 = $coords[$i - 1][1];
                    $lng1 = $coords[$i - 1][0];
                    $lat2 = $coords[$i][1];
                    $lng2 = $coords[$i][0];

                    $distance += $this->haversineMeters($lat1, $lng1, $lat2, $lng2);
                }
            }

            $s->distance_m = $distance;
            return $s;
        });


        // ----------------------------------------------------
        // 2️⃣ GLOBAL METRICS
        // ----------------------------------------------------
        $totalDistance = $sessions->sum('distance');
        $totalSessions = $sessions->count();
        $totalLogs = $logs->count();

        // ----------------------------------------------------
        // 3️⃣ USER ANALYTICS
        // ----------------------------------------------------
        $distanceByUser = $sessions->groupBy('user_id')->map->sum('distance_m');
        $activityByUser = $sessions->groupBy('user_id')->map->count();

        $topUser = $distanceByUser->sortDesc()->take(5);
        $leastUser = $distanceByUser->sort()->take(5);

        $mostActiveUser = $activityByUser->sortDesc()->take(5);
        $leastActiveUser = $activityByUser->sort()->take(5);

        // ----------------------------------------------------
        // 4️⃣ SITE ANALYTICS
        // ----------------------------------------------------
        $activityBySite = $sessions->groupBy('site_id')->map->count();
        $logsBySite = $logs->groupBy('patrolSession.site_id')->map->count();

        $mostActiveSite = $activityBySite->sortDesc()->take(5);
        $leastActiveSite = $activityBySite->sort()->take(5);

        $maxLogsSite = $logsBySite->sortDesc()->take(5);
        $minLogsSite = $logsBySite->sort()->take(5);

        return view('patrolling.dashboard', [
            'totalSessions' => $totalSessions,
            'totalLogs' => $totalLogs,
            'totalDistance' => $totalDistance,
            'avgDistance' => $totalSessions > 0
                ? round($totalDistance / $totalSessions, 2)
                : 0,
            'avgLogs' => $totalSessions > 0 ? $totalLogs / $totalSessions : 0,

            'topUser' => $topUser,
            'leastUser' => $leastUser,
            'mostActiveUser' => $mostActiveUser,
            'leastActiveUser' => $leastActiveUser,

            'mostActiveSite' => $mostActiveSite,
            'leastActiveSite' => $leastActiveSite,
            'maxLogsSite' => $maxLogsSite,
            'minLogsSite' => $minLogsSite,

            'sessions' => $sessions,
            'logs' => $logs,
        ]);
    }

    public function analyticsDashboard(Request $request)
    {
        $user = session('user');

        $dateFrom = $request->date_from ?? date('Y-m-d');
        $dateTo = $request->date_to ?? date('Y-m-d');

        $sessions = PatrolSession::with(['user', 'site'])
            ->where('company_id', $user->company_id)
            ->whereDate('started_at', '>=', $dateFrom)
            ->whereDate('started_at', '<=', $dateTo)
            ->get();

        $logs = PatrolLog::with('patrolSession')
            ->where('company_id', $user->company_id)
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->get();

        // --------------------------
        // DISTANCE PER USER
        // --------------------------
        $distanceByUser = [];
        foreach ($sessions as $s) {
            $distanceByUser[$s->user_id] = ($distanceByUser[$s->user_id] ?? 0) + ($s->distance_m ?? 0);
        }

        // --------------------------
        // SESSIONS PER SITE
        // --------------------------
        $siteActivity = $sessions->groupBy('site_id')->map->count();

        // --------------------------
        // HOURLY DENSITY
        // --------------------------
        $hourlyDensity = array_fill(0, 24, 0);
        foreach ($logs as $log) {
            $hour = intval(Carbon::parse($log->created_at)->format('H'));
            $hourlyDensity[$hour]++;
        }

        // --------------------------
        // HEATMAP POINTS
        // --------------------------
        $heatmapPoints = $logs->map(function ($log) {
            return ['lat' => floatval($log->lat), 'lng' => floatval($log->lng)];
        });

        return view('patrolling.analytics-dashboard', [
            'sessions' => $sessions,
            'logs' => $logs,
            'distanceByUser' => $distanceByUser,
            'siteActivity' => $siteActivity,
            'hourlyDensity' => $hourlyDensity,
            'heatmapPoints' => $heatmapPoints,
        ]);
    }

    public function analyticsPro(Request $request)
    {
        $user = session('user');

        $dateFrom = $request->date_from ?? '2025-12-01';
        $dateTo = $request->date_to ?? '2025-12-05';

        $sessions = PatrolSession::with(['user', 'site'])
            ->where('company_id', $user->company_id)
            ->whereDate('started_at', '>=', $dateFrom)
            ->whereDate('started_at', '<=', $dateTo)
            ->get();

        $logs = PatrolLog::with('patrolSession.site')
            ->where('company_id', $user->company_id)
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->get();

        // --------------------------
        // USER DISTANCE + ACTIVITY
        // --------------------------
        $userDistance = [];
        $userSessions = [];
        $userLabels = [];

        foreach ($sessions as $s) {
            // Build label map for display
            if ($s->user) {
                $userLabels[$s->user_id] = $s->user->name;
            }

            // Calculation using ID internally
            $userDistance[$s->user_id] = ($userDistance[$s->user_id] ?? 0) + ($s->distance ?? 0);
            $userSessions[$s->user_id] = ($userSessions[$s->user_id] ?? 0) + 1;
        }

        // --------------------------
        // SITE ACTIVITY
        // --------------------------
        $siteSessions = [];
        $siteLogs = [];
        $siteLabels = [];

        foreach ($sessions as $s) {
            if ($s->site) {
                $siteLabels[$s->site_id] = $s->site->name;
            }

            $siteSessions[$s->site_id] = ($siteSessions[$s->site_id] ?? 0) + 1;
        }

        foreach ($logs as $log) {
            $siteId = $log->patrolSession->site_id ?? null;

            if (!$siteId)
                continue;

            if ($log->patrolSession->site) {
                $siteLabels[$siteId] = $log->patrolSession->site->name;
            }

            $siteLogs[$siteId] = ($siteLogs[$siteId] ?? 0) + 1;
        }

        // --------------------------
        // HOURLY LOGS
        // --------------------------
        $hourly = array_fill(0, 24, 0);
        foreach ($logs as $log) {
            $hour = intval(\Carbon\Carbon::parse($log->created_at)->format('H'));
            $hourly[$hour]++;
        }

        // --------------------------
        // Comparison dropdown
        // --------------------------
        $compareUsers = User::where('company_id', $user->company_id)
            ->where('showUser', 1)
            ->whereNotIn('role_id', [1])
            ->get();

        return view('patrolling.analytics-pro', [
            'sessions' => $sessions,
            'logs' => $logs,

            // analytics data
            'userDistance' => $userDistance,
            'userSessions' => $userSessions,
            'siteSessions' => $siteSessions,
            'siteLogs' => $siteLogs,
            'hourly' => $hourly,

            // label maps for charts
            'userLabels' => $userLabels,
            'siteLabels' => $siteLabels,

            'compareUsers' => $compareUsers,
        ]);
    }

    public function analyticsProAdvanced(Request $request)
    {
        $user = session('user');

        // Filters (default last 7 days)
        $dateFrom = $request->date_from ?? now()->subDays(6)->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();
        $filterUser = $request->user_id ?? null;
        $filterSite = $request->site_id ?? null;

        // Base queries
        $sessionsQ = PatrolSession::with(['user', 'site'])
            ->where('company_id', $user->company_id)
            ->whereDate('started_at', '>=', $dateFrom)
            ->whereDate('started_at', '<=', $dateTo);

        $logsQ = PatrolLog::with(['patrolSession', 'patrolSession.site'])
            ->where('company_id', $user->company_id)
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        $siteIds = $sessionsQ->pluck('site_id')->toArray();

        $geofences = SiteGeofences::whereIn('site_id', $siteIds)->where('type', 'Polygon')->distinct('name')->get();

        if ($filterUser) {
            $sessionsQ->where('user_id', $filterUser);
            $logsQ->whereHas('patrolSession', fn($q) => $q->where('user_id', $filterUser));
        }
        if ($filterSite) {
            $sessionsQ->where('site_id', $filterSite);
            $logsQ->whereHas('patrolSession', fn($q) => $q->where('site_id', $filterSite));
        }

        $sessions = $sessionsQ->get();
        $sessions = $sessions->map(function ($s) {

            $coords = [];

            if (!empty($s->path_geojson)) {
                $raw = is_string($s->path_geojson)
                    ? json_decode($s->path_geojson, true)
                    : $s->path_geojson;

                if (isset($raw['coordinates'])) {
                    $coords = $raw['coordinates'];
                }
            }

            $distance = 0;

            if (count($coords) > 1) {
                for ($i = 1; $i < count($coords); $i++) {
                    $lat1 = $coords[$i - 1][1];
                    $lng1 = $coords[$i - 1][0];
                    $lat2 = $coords[$i][1];
                    $lng2 = $coords[$i][0];

                    $distance += $this->haversineMeters($lat1, $lng1, $lat2, $lng2);
                }
            }

            $s->distance_m = $distance;

            return $s;
        });
        // dd($sessions->pluck('distance_m'));
        $logs = $logsQ->get();

        // LABEL MAPS (id -> name)
        $userLabels = $sessions->pluck('user.name', 'user_id')->toArray();
        $siteLabels = $sessions->pluck('site.name', 'site_id')->toArray();
        // $users = $sessions->pluck('user_id as id', 'user.name as name')->toArray();
        // $sites = $sessions->pluck('site_id as id', 'site.name as name')->toArray();

        $users = $sessions->pluck('user.name', 'user_id')->toArray();
        $sites = $sessions->pluck('site.name', 'site_id')->toArray();

        // Ensure we include users/sites present only in logs
        foreach ($logs as $log) {
            if ($log->patrolSession && $log->patrolSession->site) {
                $siteLabels[$log->patrolSession->site_id] = $log->patrolSession->site->name;
            }
        }

        // USER METRICS (by id)
        $userDistance = []; // meters
        $userSessions = []; // count
        $userLogs = [];     // count
        foreach ($sessions as $s) {
            $uid = $s->user_id;
            $userDistance[$uid] = ($userDistance[$uid] ?? 0) + ($s->distance ?? 0);
            $userSessions[$uid] = ($userSessions[$uid] ?? 0) + 1;
        }
        foreach ($logs as $l) {
            $uid = $l->patrolSession->user_id ?? null;
            if ($uid)
                $userLogs[$uid] = ($userLogs[$uid] ?? 0) + 1;
        }

        // SITE METRICS (by id)
        $siteSessions = []; // count
        $siteLogs = [];     // count
        foreach ($sessions as $s) {
            $sid = $s->site_id;
            $siteSessions[$sid] = ($siteSessions[$sid] ?? 0) + 1;
        }
        foreach ($logs as $l) {
            $sid = $l->patrolSession->site_id ?? null;
            if ($sid)
                $siteLogs[$sid] = ($siteLogs[$sid] ?? 0) + 1;
        }

        // Multi-day trend: sessions per day (labels = dates)
        $period = new \DatePeriod(
            new \DateTime($dateFrom),
            new \DateInterval('P1D'),
            (new \DateTime($dateTo))->modify('+1 day')
        );
        $dates = [];
        foreach ($period as $dt)
            $dates[] = $dt->format('Y-m-d');
        $sessionsPerDay = array_fill_keys($dates, 0);
        foreach ($sessions as $s) {
            $d = \Carbon\Carbon::parse($s->started_at)->toDateString();
            if (isset($sessionsPerDay[$d]))
                $sessionsPerDay[$d]++;
        }

        // Patrol density by hour (0..23)
        $hourly = array_fill(0, 24, 0);
        foreach ($logs as $l) {
            $h = (int) \Carbon\Carbon::parse($l->created_at)->format('H');
            $hourly[$h]++;
        }

        // Heatmap points from logs (lat/lng)
        $heatmapPoints = $logs->filter(fn($l) => $l->lat && $l->lng)->map(fn($l) => [
            'lat' => (float) $l->lat,
            'lng' => (float) $l->lng,
            'user_id' => $l->patrolSession->user_id ?? null,
            'site_id' => $l->patrolSession->site_id ?? null
        ])->values();

        // Guard productivity ranking: compute score = weighted combination
        // score = (normalized sessions * 0.4) + (normalized distance * 0.4) + (normalized logs * 0.2)
        $productivity = [];
        $uids = array_unique(array_merge(array_keys($userSessions), array_keys($userDistance), array_keys($userLogs)));
        $maxSessions = max($userSessions ?: [1]) ?: 1;
        $maxDistance = max($userDistance ?: [1]) ?: 1;
        $maxLogs     = max($userLogs ?: [1]) ?: 1;

        foreach ($uids as $uid) {
            // Divisions are now safe because max is guaranteed to be at least 1
            $ns = ($userSessions[$uid] ?? 0) / $maxSessions;
            $nd = ($userDistance[$uid] ?? 0) / $maxDistance;
            $nl = ($userLogs[$uid] ?? 0) / $maxLogs;

            $score = ($ns * 0.4) + ($nd * 0.4) + ($nl * 0.2);
            $productivity[$uid] = round($score * 10, 2);
        }
        arsort($productivity); // high to low

        // AI Insights – simple rule-based heuristics
        $aiInsights = [];
        // rule 1: guards with very low productivity (<0.2)
        foreach ($productivity as $uid => $score) {
            if ($score < 2) {
                $aiInsights[] = [
                    'type' => 'underperforming_guard',
                    'user_id' => $uid,
                    'user_name' => $userLabels[$uid] ?? 'Unknown',
                    'score' => $score,
                    'note' => 'Low activity & distance in selected range'
                ];
            }
        }
        // rule 2: sites with no sessions (but in siteLabels)
        foreach ($siteLabels as $sid => $sname) {
            if (!isset($siteSessions[$sid]) || $siteSessions[$sid] == 0) {
                $aiInsights[] = [
                    'type' => 'dormant_site',
                    'site_id' => $sid,
                    'site_name' => $sname,
                    'note' => 'No sessions recorded in range'
                ];
            }
        }

        $rawSessions = $sessions->map(function ($s) {
            return [
                'id' => $s->id,
                'user_id' => $s->user_id,
                'site_id' => $s->site_id,
                'path' => $this->extractPathForJs($s),
                'distance_m' => $s->distance_m ?? 0,
                'started_at' => $s->started_at,
                'ended_at' => $s->ended_at,
            ];
        })->values();

        $geoMapped = $geofences->map(function ($s) {
            return [
                'id' => $s->id,
                'site_id' => $s->site_id,
                'coords' => json_decode($s->poly_lat_lng, true),
            ];
        })->values();

        $weeklyStats = PatrolSession::selectRaw("
    WEEK(started_at, 1) AS week_number,
    COUNT(*) AS sessions,
    SUM(distance) AS total_distance
     ")
            ->where('company_id', $user->company_id)
            ->groupBy('week_number')
            ->orderBy('week_number')
            ->get();

        foreach ($weeklyStats as $w) {
            $coverage = rand(40, 95); // replace with real coverage calc
            $zonesVisited = rand(3, 10); // geofences intersected
            $wlogs = PatrolLog::whereIn('patrol_session_id', $sessions->pluck('id'))->count();

            $w->score =
                (0.40 * $coverage) +
                (0.30 * min($w->total_distance / 20000, 1) * 100) +
                (0.20 * min($zonesVisited / 10, 1) * 100) +
                (0.10 * min($wlogs / 30, 1) * 100);
        }


        // Data to view
        return view('patrolling.analytics-pro-advanced', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'filterUser' => $filterUser,
            'filterSite' => $filterSite,
            'sessions' => $sessions,
            'rawSessions' => $rawSessions,
            'logs' => $logs,
            'userDistance' => $userDistance,
            'userSessions' => $userSessions,
            'userLogs' => $userLogs,
            'userLabels' => $userLabels,
            'siteSessions' => $siteSessions,
            'siteLogs' => $siteLogs,
            'siteLabels' => $siteLabels,
            'sessionsPerDay' => $sessionsPerDay,
            'hourly' => $hourly,
            'heatmapPoints' => $heatmapPoints,
            'productivity' => $productivity,
            'aiInsights' => $aiInsights,
            'compareUsers' => User::where('company_id', $user->company_id)->whereNotIn('role_id', [1])->where('showUser', 1)->get(),
            'sitesList' => \App\SiteDetails::where('company_id', $user->company_id)->get(),
            'clientsList' => \App\ClientDetails::where('company_id', $user->company_id)->get(),
            'geofences' => $geoMapped,
            'users' => $users,
            'sites' => $sites,
            'weeklyStats' => $weeklyStats,
        ]);
    }

    // Drilldown endpoints (AJAX) — returns JSON with hourly and site breakdown for user
    public function userDrilldown($id, Request $request)
    {
        $user = session('user');
        $dateFrom = $request->date_from ?? now()->subDays(6)->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        $sessions = PatrolSession::with('site')
            ->where('company_id', $user->company_id)
            ->where('user_id', $id)
            ->whereDate('started_at', '>=', $dateFrom)
            ->whereDate('started_at', '<=', $dateTo)
            ->get();

        $logs = PatrolLog::whereHas('patrolSession', fn($q) => $q->where('user_id', $id))
            ->where('company_id', $user->company_id)
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->get();

        $hourly = array_fill(0, 24, 0);
        foreach ($logs as $l) {
            $h = (int) \Carbon\Carbon::parse($l->created_at)->format('H');
            $hourly[$h]++;
        }

        $siteCounts = $sessions->groupBy('site_id')->map->count();

        return response()->json([
            'sessions' => $sessions,
            'hourly' => $hourly,
            'siteCounts' => $siteCounts,
        ]);
    }

    // Site drilldown similar to userDrilldown
    public function siteDrilldown($id, Request $request)
    {
        $user = session('user');
        $dateFrom = $request->date_from ?? now()->subDays(6)->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        $sessions = PatrolSession::with('user')
            ->where('company_id', $user->company_id)
            ->where('site_id', $id)
            ->whereDate('started_at', '>=', $dateFrom)
            ->whereDate('started_at', '<=', $dateTo)
            ->get();

        $logs = PatrolLog::whereHas('patrolSession', fn($q) => $q->where('site_id', $id))
            ->where('company_id', $user->company_id)
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->get();

        $hourly = array_fill(0, 24, 0);
        foreach ($logs as $l) {
            $h = (int) \Carbon\Carbon::parse($l->created_at)->format('H');
            $hourly[$h]++;
        }

        $userCounts = $sessions->groupBy('user_id')->map->count();

        return response()->json([
            'sessions' => $sessions,
            'hourly' => $hourly,
            'userCounts' => $userCounts,
        ]);
    }

    // Live polling endpoint for near real-time updates (call every 10-30s from front-end)
    public function liveData(Request $request)
    {
        $user = session('user');

        $sessions = PatrolSession::where('company_id', $user->company_id)->get();
        $logs = PatrolLog::where('company_id', $user->company_id)->get();

        $weekly = [];

        foreach ($sessions as $s) {
            $date = \Carbon\Carbon::parse($s->created_at);

            $year = $date->year;
            $week = $date->isoWeek(); // ✅ correct standard

            $key = $year . '-W' . $week;

            if (!isset($weekly[$key])) {
                $weekly[$key] = [
                    'year' => $year,
                    'week' => $week,
                    'distance' => 0,
                    'sessions' => 0,
                    'logs' => 0
                ];
            }

            $weekly[$key]['distance'] += $s->distance_m ?? 0;
            $weekly[$key]['sessions']++;
        }

        foreach ($logs as $l) {
            $date = \Carbon\Carbon::parse($l->created_at);

            $year = $date->year;
            $week = $date->isoWeek();

            $key = $year . '-W' . $week;

            if (!isset($weekly[$key])) {
                $weekly[$key] = [
                    'year' => $year,
                    'week' => $week,
                    'distance' => 0,
                    'sessions' => 0,
                    'logs' => 0
                ];
            }

            $weekly[$key]['logs']++;
        }

        $result = [];

        foreach ($weekly as $key => $data) {

            // 🔥 Normalize values (fix crazy spikes)
            $distanceScore = min(($data['distance'] / 1000) / 20, 1) * 100; // max ~20km
            $logScore = min($data['logs'] / 50, 1) * 100;                  // max ~50 logs
            $sessionScore = min($data['sessions'] / 20, 1) * 100;          // max ~20 sessions

            // Weighted score (0–100)
            $score =
                (0.4 * $distanceScore) +
                (0.3 * $logScore) +
                (0.3 * $sessionScore);

            $result[] = [
                'year' => $data['year'],
                'week_number' => $data['week'],
                'label' => $data['year'] . ' W' . str_pad($data['week'], 2, '0', STR_PAD_LEFT),
                'score' => round($score, 2)
            ];
        }

        // Sort by year + week
        usort($result, function ($a, $b) {
            return [$a['year'], $a['week_number']] <=> [$b['year'], $b['week_number']];
        });

        return response()->json($result);
    }
    // EXPORTS
    public function exportCsv(Request $request)
    {
        $user = session('user');
        $dateFrom = $request->date_from ?? now()->subDays(6)->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        $sessions = PatrolSession::with('user', 'site')
            ->where('company_id', $user->company_id)
            ->whereDate('started_at', '>=', $dateFrom)
            ->whereDate('started_at', '<=', $dateTo)
            ->get();

        $filename = "patrols_{$dateFrom}_{$dateTo}.csv";
        $handle = fopen('php://memory', 'w');
        // header
        fputcsv($handle, ['session_id', 'user_id', 'user_name', 'site_id', 'site_name', 'started_at', 'ended_at', 'distance_km']);
        foreach ($sessions as $s) {
            fputcsv($handle, [
                $s->id,
                $s->user_id,
                $s->user->name ?? '',
                $s->site_id,
                $s->site->name ?? '',
                $s->started_at,
                $s->ended_at,
                number_format(($s->distance_m ?? 0) / 1000, 3)
            ]);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }

    public function exportExcel(Request $request)
    {
        $user = session('user');
        $dateFrom = $request->date_from ?? now()->subDays(6)->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        // Use a simple export class (below). Pass filters as needed.
        return Excel::download(new AnalyticsExport($user->company_id, $dateFrom, $dateTo), "patrols_{$dateFrom}_{$dateTo}.xlsx");
    }

    public function exportPdf(Request $request)
    {
        $user = session('user');
        $dateFrom = $request->date_from ?? now()->subDays(6)->toDateString();
        $dateTo = $request->date_to ?? now()->toDateString();

        $sessions = PatrolSession::with('user', 'site')
            ->where('company_id', $user->company_id)
            ->whereDate('started_at', '>=', $dateFrom)
            ->whereDate('started_at', '<=', $dateTo)
            ->get();

        $data = [
            'title' => 'Patrolling Analytics',
            'sessions' => $sessions,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ];

        $pdf = PDF::loadView('patrolling.pdf.advanced', $data);
        return $pdf->download("patrols_{$dateFrom}_{$dateTo}.pdf");
    }

    public function analyticsPdf(Request $request)
    {
        $data = [
            'date' => now()->format('d M Y'),
            'sessions' => PatrolSession::count(),
            'logs' => PatrolLog::count(),
        ];

        $pdf = \PDF::loadView('patrolling.pdf.analytics', $data);
        return $pdf->download('Patrolling-Analytics.pdf');
    }


    /**
     * Sum haversine distances across consecutive coords array of [ [lng,lat], ... ] -> meters
     */
    private function sumPathDistanceMeters(array $coords)
    {
        if (count($coords) < 2)
            return 0;
        $total = 0.0;
        for ($i = 1; $i < count($coords); $i++) {
            $a = $coords[$i - 1];
            $b = $coords[$i];
            // ensure indexes: assume [lng,lat]
            $lat1 = floatval($a[1]);
            $lon1 = floatval($a[0]);
            $lat2 = floatval($b[1]);
            $lon2 = floatval($b[0]);
            $total += $this->haversineMeters($lat1, $lon1, $lat2, $lon2);
        }
        return $total;
    }

    private function haversineMeters($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371000; // metres
        $φ1 = deg2rad($lat1);
        $φ2 = deg2rad($lat2);
        $Δφ = deg2rad($lat2 - $lat1);
        $Δλ = deg2rad($lon2 - $lon1);

        $a = sin($Δφ / 2) * sin($Δφ / 2) + cos($φ1) * cos($φ2) * sin($Δλ / 2) * sin($Δλ / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $R * $c;
    }

    private function extractPathForJs($session)
    {
        $coords = [];

        if (!empty($session->path_geojson)) {
            try {
                $raw = is_string($session->path_geojson)
                    ? json_decode($session->path_geojson, true)
                    : $session->path_geojson;

                // CASE 1: Feature -> geometry -> LineString
                if (isset($raw['type']) && $raw['type'] === 'Feature' && isset($raw['geometry'])) {
                    if ($raw['geometry']['type'] === 'LineString') {
                        $coords = $raw['geometry']['coordinates'];
                    }
                }

                // CASE 2: Plain LineString
                elseif (isset($raw['type']) && $raw['type'] === 'LineString') {
                    $coords = $raw['coordinates'];
                }

                // CASE 3: raw coordinate array []
                elseif (is_array($raw) && isset($raw[0]) && count($raw[0]) === 2) {
                    $coords = $raw;
                }
            } catch (\Exception $e) {
                $coords = [];
            }
        }

        // Convert → {lat, lng}
        return array_map(fn($c) => [
            'lat' => (float) $c[1],
            'lng' => (float) $c[0]
        ], $coords);
    }
}

<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

use App\PatrolSession;
use App\PatrolLog;
use App\SiteAssign;
use App\SiteDetails;
use App\Attendance;
use App\User;
use App\SiteGeofences;
use Carbon\Carbon;
use App\IncidenceDetails;
use App\ClientDetails;

use App\Helpers\Geo;

use geoPHP;

class ForestController extends Controller
{
    // Dashboard
    public function index(Request $request)
    {
        $user = session('user');

        /**********************************************
         * NORMALIZE MULTI-SELECT INPUTS
         **********************************************/
        $filterClientIds = array_filter((array) $request->get('client_id', []));
        $filterSiteIds = array_filter((array) $request->get('site_id', []));
        $filterUserIds = array_filter((array) $request->get('user_id', []));
        $filterMethods = array_filter((array) $request->get('method', []));

        /**********************************************
         * 1️⃣ ROLE-BASED FILTERING (UNCHANGED LOGIC)
         **********************************************/
        $siteIds = [];
        $userIds = [];

        switch ($user->role_id) {

            case 1: // SUPERADMIN
                $siteIds = SiteDetails::where('company_id', $user->company_id)->pluck('id')->toArray();
                $userIds = User::where('company_id', $user->company_id)->pluck('id')->toArray();
                break;

            case 2: // SUPERVISOR
                $assign = SiteAssign::where('user_id', $user->id)->first();
                $siteIds = $assign ? json_decode($assign->site_id, true) : [];
                $userIds = SiteAssign::where('company_id', $user->company_id)
                    ->whereIn('site_id', $siteIds)
                    ->where('role_id', 3)
                    ->pluck('user_id')->toArray();
                break;

            case 7: // ADMIN
                $assigned = SiteAssign::where('user_id', $user->id)->first();
                if ($assigned) {
                    $clientIds = json_decode($assigned->site_id, true);

                    $siteIds = SiteDetails::whereIn('client_id', $clientIds)->pluck('id')->toArray();

                    $supervisors = SiteAssign::where('role_id', 2)
                        ->where(function ($q) use ($siteIds) {
                            foreach ($siteIds as $sid) {
                                $q->orWhereRaw("JSON_CONTAINS(site_id, ?)", [json_encode($sid)]);
                            }
                        })
                        ->pluck('user_id')->toArray();

                    $guards = SiteAssign::where('role_id', 3)
                        ->whereIn('client_id', $clientIds)
                        ->pluck('user_id')->toArray();

                    $userIds = array_unique(array_merge($supervisors, $guards));
                }
                break;
        }

        /**********************************************
         * 2️⃣ APPLY MULTI-SELECT FILTERS (SAFE)
         **********************************************/

        // CLIENT → SITES
        if (!empty($filterClientIds)) {
            $siteIds = SiteDetails::whereIn('client_id', $filterClientIds)
                ->pluck('id')->toArray();
        }

        // SITE FILTER
        if (!empty($filterSiteIds)) {
            $siteIds = array_intersect($siteIds, $filterSiteIds);
        }

        // USER FILTER
        if (!empty($filterUserIds)) {
            $userIds = array_intersect($userIds, $filterUserIds);
        } else if (!empty($filterSiteIds)) {
            $userIds = SiteAssign::whereIn('site_id', $filterSiteIds)
                ->pluck('user_id')->toArray();
        } else if (!empty($filterClientIds)) {
            $userIds = SiteAssign::whereIn('client_id', $filterClientIds)
                ->pluck('user_id')->toArray();
        }
        /**********************************************
         * 3️⃣ DATE RANGE (UNCHANGED)
         **********************************************/
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)
            : now()->subDays(1);

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)
            : now();


        // dd($dateFrom, $dateTo);
        /**********************************************
         * 4️⃣ FETCH PATROL SESSIONS
         **********************************************/
        $sessions = PatrolSession::with(['user', 'site'])
            ->where('company_id', $user->company_id)
            ->when($siteIds, fn($q) => $q->whereIn('site_id', $siteIds))
            ->when($userIds, fn($q) => $q->whereIn('user_id', $userIds))
            ->when($filterMethods, fn($q) => $q->whereIn('method', $filterMethods))
            ->whereDate('started_at', '>=', $dateFrom)
            ->whereDate('started_at', '<=', $dateTo)
            ->get();

        /**********************************************
         * 5️⃣ FETCH ATTENDANCE
         **********************************************/
        $attendance = Attendance::where('company_id', $user->company_id)
            ->when($siteIds, fn($q) => $q->whereIn('site_id', $siteIds))
            ->when($userIds, fn($q) => $q->whereIn('user_id', $userIds))
            ->whereDate('dateFormat', '>=', $dateFrom)
            ->whereDate('dateFormat', '<=', $dateTo)
            ->get();

        $sessionIds = $sessions->pluck('id')->toArray();

        /**********************************************
         * 6️⃣ FETCH LOGS
         **********************************************/
        $logs = PatrolLog::with('patrolSession')
            ->where('company_id', $user->company_id)
            ->when($sessionIds, fn($q) => $q->whereIn('patrol_session_id', $sessionIds))
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->get();

        /**********************************************
         * 7️⃣ FETCH INCIDENTS
         **********************************************/
        $incidents = IncidenceDetails::where('company_id', $user->company_id)
            ->when($siteIds, fn($q) => $q->whereIn('site_id', $siteIds))
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        /**********************************************
         * 8️⃣ FETCH GEOFENCES (UNCHANGED)
         **********************************************/
        $gfs = SiteGeofences::where('company_id', $user->company_id)
            ->where('type', 'Polygon')
            ->when($siteIds, fn($q) => $q->whereIn('site_id', $siteIds))
            ->get()
            ->unique('poly_lat_lng')
            ->values();
        /**********************************************
         * REMAINING LOGIC (PATHS, COVERAGE, KPI, VIEW)
         * ⛔ UNCHANGED — EXACTLY AS YOUR ORIGINAL CODE
         **********************************************/

        // ⬇️ everything from here onward stays EXACTLY the same
        // (extractPathForJs, coverage, KPIs, siteNames, return view)

        $rangeNames = ClientDetails::where('company_id', $user->company_id)
            ->orderBy('id', 'desc')
            ->get();

        // $userList = User::where('company_id', $user->company_id)
        //     ->where('showUser', 1)
        //     ->whereNotIn('role_id', [1])
        //     ->when($userIds, fn($q) => $q->whereIn('id', $userIds))
        //     ->get();

        /**********************************************
         * 7️⃣ ORGANIZE PATROL PATHS PER SITE
         **********************************************/
        $sessionPathsBySite = [];
        foreach ($sessions as $s) {
            $path = $this->extractPathForJs($s);
            if (!$path || count($path) < 2)
                continue;

            // 🔽 Optional: downsample for speed (keeps ~300 points)
            $step = max(1, floor(count($path) / 300));
            $simple = [];
            for ($i = 0; $i < count($path); $i += $step) {
                $simple[] = $path[$i];
            }
            $sessionPathsBySite[$s->site_id][] = $simple;
        }
        /**********************************************
         * 8️⃣ COMPUTE COVERAGE FOR EACH GEOFENCE (FAST)
         **********************************************/
        $geofences = [];

        // Precompute buffered paths ONCE
        $buffersBySite = [];

        foreach ($sessionPathsBySite as $siteId => $paths) {
            foreach ($paths as $p) {

                // convert points to lat/lng numeric
                $clean = array_map(fn($pt) => [
                    'lat' => (float) $pt['lat'],
                    'lng' => (float) $pt['lng']
                ], $p);

                $buffersBySite[$siteId][] = $clean;
            }
        }

        $geofenceSummary = [];
        $geofenceDetails = [];
        foreach ($gfs as $g) {

            $coords = json_decode($g->poly_lat_lng ?? "[]", true) ?: [];
            if (count($coords) < 3)
                continue;

            $paths = $buffersBySite[$g->site_id] ?? [];

            // if no paths -> coverage 0
            if (empty($paths)) {
                $geofences[] = [
                    'id' => $g->id,
                    'site_id' => $g->site_id,
                    'site_name' => $g->site->name ?? $g->name,
                    'name' => $g->name,
                    'coords' => $coords,
                    'coverage' => 0,
                    'color' => '#ff4d4f',
                ];
                continue;
            }

            // fast-reject via bounding box
            [$minLat, $minLng, $maxLat, $maxLng] = Geo::bboxFromCoords($coords);

            // Check if ANY patrol point falls inside expanded bbox
            $bboxHit = false;
            foreach ($paths as $path) {
                foreach ($path as $pt) {
                    if (
                        $pt['lat'] >= $minLat - 0.0005 &&
                        $pt['lat'] <= $maxLat + 0.0005 &&
                        $pt['lng'] >= $minLng - 0.0005 &&
                        $pt['lng'] <= $maxLng + 0.0005
                    ) {
                        $bboxHit = true;
                        break 2;
                    }
                }
            }

            // If patrol never enters bbox → coverage = 0
            if (!$bboxHit) {
                $geofences[] = [
                    'id' => $g->id,
                    'site_id' => $g->site_id,
                    'site_name' => $g->site->name ?? $g->name,
                    'name' => $g->name,
                    'coords' => $coords,
                    'coverage' => 0,
                    'color' => '#ff4d4f',
                ];
                continue;
            }

            // CACHE coverage computation
            $cacheKey = "cov_{$g->id}_{$dateFrom}_{$dateTo}";

            $coverage = Cache::remember($cacheKey, 60, function () use ($coords, $paths, $minLat, $minLng, $maxLat, $maxLng) {

                // VERY FAST grid sampling restricted to geofence only
                $result = Geo::coverageFastRestricted(
                    polygonCoords: $coords,
                    paths: $paths,
                    minLat: $minLat,
                    minLng: $minLng,
                    maxLat: $maxLat,
                    maxLng: $maxLng,
                    gridStepMeters: 18     // 18m grid, fast + good accuracy
                );

                return round($result * 100, 4); // percent
            });

            $color =
                $coverage >= 70 ? '#28a745' :
                ($coverage >= 0 ? '#ffc107' : '#ff4d4f');

            $geofences[] = [
                'id' => $g->id,
                'site_id' => $g->site_id,
                'site_name' => $g->site->name ?? $g->name,
                'name' => $g->name,
                'coords' => $coords,
                'coverage' => $coverage,
                'color' => $color,
            ];


            $coords = json_decode($g->poly_lat_lng ?? "[]", true) ?: [];
            if (count($coords) < 3)
                continue;

            $polygon = Geo::polygonFromLatLng($coords);

            // 1) Patrol sessions count
            $patrolCount = 0;
            foreach ($sessions as $s) {
                if ($s->site_id == $g->site_id)
                    $patrolCount++;
                // if (!$s->path)
                //     continue;
                // foreach ($s->path as $pt) {
                //     if (Geo::pointInPolygonLatLng($pt, $polygon)) {
                //         $patrolCount++;
                //         break;
                //         break;
                //     }
                // }
            }

            // 2) Logs count
            $logCount = 0;
            foreach ($logs as $l) {
                if (isset($l->patrol)) {
                    if ($l->patrol->site_id == $g->site_id) {
                        // if ($l->lat && $l->lng) {
                        //     if ($this->pointInPolygon($l->lat, $l->lng, $polygon)) {
                        $logCount++;
                        //     }
                        // }
                    }
                }
            }

            // 3) Attendance stats
            $siteId = $g->site_id;
            $siteGuards = SiteAssign::where('site_id', $siteId)->where('role_id', 3)->pluck('user_id')->toArray();
            $present = Attendance::where('site_id', $siteId)->where('dateFormat', $dateTo)->distinct()->count('user_id');
            $total = count($siteGuards);
            $absent = max($total - $present, 0);

            $geofenceSummary[$g->id] = [
                'patrol_sessions' => $patrolCount,
                'log_count' => $logCount,
                'present' => $present,
                'absent' => $absent,
                'total_guards' => $total
            ];


            $geofenceDetails[$g->id] = [
                'summary' => [
                    'patrol_sessions' => $patrolCount,
                    'log_count' => $logCount,
                    'present' => $present,
                    'absent' => $absent,
                    'total_guards' => $total
                ],

                // PATROL SESSIONS THAT TOUCHED THIS GEOFENCE
                'patrols' => collect($sessions)->filter(function ($s) use ($polygon, $g) {
                    if ($s->site_id == $g->site_id)
                        return true;
                    //     return false;
                    // foreach ($s->path as $pt) {
                    //     if (Geo::pointInPolygonLatLng($pt, $polygon))
                    //         return true;
                    // }
                    // return false;
                })->values(),

                // LOGS THAT FELL INSIDE THIS GEOFENCE
                'logs' => collect($logs)->filter(function ($l) use ($polygon, $g) {
                    if (!isset($l->patrol))
                        return false;
                    if ($l->patrol->site_id == $g->site_id)
                        return true;
                    return false;
                })->values()
            ];

        }



        /**********************************************
         * 9️⃣ HEATMAP POINTS
         **********************************************/
        $heatPoints = $logs->filter(fn($l) => $l->lat && $l->lng)
            ->map(fn($l) => [
                'lat' => (float) $l->lat,
                'lng' => (float) $l->lng
            ])
            ->values();

        /**********************************************
         * 🔟 LIVE SESSIONS (Last 10 minutes)
         **********************************************/
        $liveSessions = PatrolSession::with(['user', 'site'])
            ->where('company_id', $user->company_id)
            ->where('updated_at', '>=', now()->subMinutes(10))
            ->get();

        /**********************************************
         * 1️⃣1️⃣ RAW SESSIONS FOR JS
         **********************************************/
        $rawSessions = $sessions->map(function ($s) {
            return [
                'id' => $s->id,
                'site_id' => $s->site_id,
                'user_id' => $s->user_id,
                'user_name' => $s->user->name ?? null,
                'path' => $this->extractPathForJs($s),
                'distance_m' => $s->distance ?? 0,
                'started_at' => $s->started_at,
                'ended_at' => $s->ended_at
            ];
        });

        /**********************************************
         * 1️⃣2️⃣ KPI COUNTS
         **********************************************/
        $sessionCount = $sessions->count();
        $logCount = $logs->count();
        $incidentCount = $incidents->count();
        $attendanceCount = $attendance->count();

        /**********************************************
         * 1️⃣3️⃣ SITE NAME MAP
         **********************************************/

        if ($filterSiteIds) {
            // even if one site id is passed , we need to show all of the sites of the company
            $siteNames = SiteDetails::where('company_id', $user->company_id)->whereIn('client_id', $filterClientIds)->pluck('name', 'id')->toArray();
        } else {
            $siteNames = SiteDetails::whereIn('id', $siteIds)->pluck('name', 'id')->toArray();
        }

        if ($filterUserIds) {
            $userList = SiteAssign::where('company_id', $user->company_id)
                ->whereIn('site_id', $filterSiteIds)
                ->select('user_id as id', 'user_name as name')
                ->distinct()
                ->get();

            // dump($userList);
        }
        /**********************************************
         * 1️⃣4️⃣ RETURN VIEW
         **********************************************/
        return view('forest.new-forest-dashboard', [
            'sessions' => $rawSessions,
            'logs' => $heatPoints,
            'incidents' => $incidents,
            'geofences' => $geofences,
            'geofenceSummary' => $geofenceSummary,
            'geofenceDetails' => $geofenceDetails,
            'kpi' => compact('sessionCount', 'logCount', 'incidentCount', 'attendanceCount'),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'liveSessions' => $liveSessions,
            'siteNames' => $siteNames,
            'userList' => ($filterUserIds) ? $userList : [],
            'rangeNames' => $rangeNames,
            'attendances' => $attendance,
        ]);
    }




    /**
     * Extract path from path_geojson -> [{lat,lng},...]
     */
    private function extractPathForJs($session)
    {
        $coords = [];

        if (!empty($session->path_geojson)) {
            try {
                $raw = is_string($session->path_geojson) ? json_decode($session->path_geojson, true) : $session->path_geojson;

                if (isset($raw['type']) && $raw['type'] === 'Feature' && isset($raw['geometry'])) {
                    $geom = $raw['geometry'];
                    if ($geom['type'] === 'LineString')
                        $coords = $geom['coordinates'];
                } elseif (isset($raw['type']) && $raw['type'] === 'LineString') {
                    $coords = $raw['coordinates'];
                } elseif (is_array($raw) && isset($raw[0]) && count($raw[0]) === 2) {
                    $coords = $raw;
                }
            } catch (\Exception $ex) {
                $coords = [];
            }
        }

        // convert [lng,lat] -> {lat,lng}
        return array_map(fn($c) => ['lat' => (float) $c[1], 'lng' => (float) $c[0]], $coords);
    }

    /**
     * Approximate coverage computation:
     * - samples points inside polygon bounding box on gridResolution (gridResolution^2 samples)
     * - for each sample point checks distance to path (point-segment distance)
     * - marks covered if distance <= bufferMeters
     * - returns percent covered (0..100)
     *
     * Good balance: gridResolution 40-60 for typical polygons. Increase for accuracy.
     */
    private function computeCoverageApprox(array $path, array $polyCoords, float $bufferMeters = 20, int $gridResolution = 50)
    {
        // path: array of ['lat'=>..,'lng'=>..]
        // polyCoords: array of ['lat'=>..,'lng'=>..]
        if (count($path) < 2 || count($polyCoords) < 3)
            return 0;

        // bounding box of polygon
        $lats = array_column($polyCoords, 'lat');
        $lngs = array_column($polyCoords, 'lng');
        $minLat = min($lats);
        $maxLat = max($lats);
        $minLng = min($lngs);
        $maxLng = max($lngs);

        // prepare path segments as arrays of [lat,lng]
        $segments = [];
        for ($i = 1; $i < count($path); $i++) {
            $a = $path[$i - 1];
            $b = $path[$i];
            $segments[] = [(float) $a['lat'], (float) $a['lng'], (float) $b['lat'], (float) $b['lng']];
        }

        // sample grid (gridResolution x gridResolution)
        $covered = 0;
        $total = 0;

        // guard tiny bbox
        if ($minLat == $maxLat || $minLng == $maxLng)
            return 0;

        for ($i = 0; $i < $gridResolution; $i++) {
            $tLat = $minLat + ($i / ($gridResolution - 1)) * ($maxLat - $minLat);
            for ($j = 0; $j < $gridResolution; $j++) {
                $tLng = $minLng + ($j / ($gridResolution - 1)) * ($maxLng - $minLng);

                // check point-in-polygon first (fastish)
                if (!$this->pointInPolygon($tLat, $tLng, $polyCoords))
                    continue; // not inside polygon → skip sample point

                $total++;

                // compute min distance to path (meters)
                $minDist = INF;
                foreach ($segments as $seg) {
                    $d = $this->pointToSegmentDistanceMeters($tLat, $tLng, $seg[0], $seg[1], $seg[2], $seg[3]);
                    if ($d < $minDist)
                        $minDist = $d;
                    if ($minDist <= $bufferMeters)
                        break;
                }

                if ($minDist <= $bufferMeters)
                    $covered++;
            }
        }

        if ($total === 0)
            return 0;

        return round(($covered / $total) * 100, 2);
    }

    // point in polygon (ray casting) expects $polyCoords as array [ ['lat'=>..,'lng'=>..], ... ]
    private function pointInPolygon($lat, $lng, $polyCoords)
    {

        if (!isset($polyCoords[0]) || !isset($polyCoords[0]))
            return false;
        $inside = false;
        $n = count($polyCoords);
        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $polyCoords[$i]['lat'];
            $yi = $polyCoords[$i]['lng'];
            $xj = $polyCoords[$j]['lat'];
            $yj = $polyCoords[$j]['lng'];

            $intersect = (($yi > $lng) != ($yj > $lng))
                && ($lat < ($xj - $xi) * ($lng - $yi) / ($yj - $yi + 0.0) + $xi);
            if ($intersect)
                $inside = !$inside;
        }
        return $inside;
    }

    // haversine meters
    private function haversineMeters($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371000;
        $φ1 = deg2rad($lat1);
        $φ2 = deg2rad($lat2);
        $Δφ = deg2rad($lat2 - $lat1);
        $Δλ = deg2rad($lon2 - $lon1);
        $a = sin($Δφ / 2) * sin($Δφ / 2) + cos($φ1) * cos($φ2) * sin($Δλ / 2) * sin($Δλ / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }

    /**
     * Distance from point P to segment AB (in meters).
     * All coordinates as lat/lng.
     * Uses simple projection to compute closest point, then haversine.
     */
    private function pointToSegmentDistanceMeters($plat, $plng, $alat, $alng, $blat, $blng)
    {
        // convert to Cartesian-like using lat/lng on sphere approx by using equirectangular projection
        // reference: approximation suitable for small distances
        $x1 = $alng;
        $y1 = $alat;
        $x2 = $blng;
        $y2 = $blat;
        $x0 = $plng;
        $y0 = $plat;

        $dx = $x2 - $x1;
        $dy = $y2 - $y1;
        if (abs($dx) < 1e-12 && abs($dy) < 1e-12) {
            return $this->haversineMeters($plat, $plng, $alat, $alng);
        }

        $t = (($x0 - $x1) * $dx + ($y0 - $y1) * $dy) / ($dx * $dx + $dy * $dy);
        if ($t < 0) {
            return $this->haversineMeters($plat, $plng, $alat, $alng);
        } elseif ($t > 1) {
            return $this->haversineMeters($plat, $plng, $blat, $blng);
        }

        $projLng = $x1 + $t * $dx;
        $projLat = $y1 + $t * $dy;
        return $this->haversineMeters($plat, $plng, $projLat, $projLng);
    }

    /**
     * Live endpoint: returns sessions/logs updated/created in last X minutes
     */
    public function liveData(Request $request)
    {
        $user = session('user');
        $minutes = (int) $request->get('minutes', 10);
        $since = Carbon::now()->subMinutes($minutes);

        // Role-based filtering same as index - for brevity applying company-wide, but you can apply same site filter
        $sessions = PatrolSession::with('user', 'site')
            ->where('company_id', $user->company_id)
            ->where('updated_at', '>=', $since)
            ->get();

        $logs = PatrolLog::where('company_id', $user->company_id)
            ->where('created_at', '>=', $since)
            ->get();

        return response()->json([
            'ts' => now()->toDateTimeString(),
            'sessions' => $sessions,
            'logs' => $logs,
        ]);
    }

    public function userSummary(Request $req)
    {
        $userId = $req->user_id;
        $dateFrom = $req->date_from ?? now()->subDays(7)->format('Y-m-d');
        $dateTo = $req->date_to ?? now()->format('Y-m-d');

        if (!$userId) {
            return redirect()->back()->with('error', 'Please select a user.');
        }

        // Fetch user
        $user = User::find($userId);

        // Patrol sessions
        $sessions = PatrolSession::with('site')
            ->where('user_id', $userId)
            ->whereBetween('started_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->orderBy('started_at', 'desc')
            ->get();

        $totalDistance = $sessions->sum('distance') / 1000;

        // Logs
        $logs = PatrolLog::whereHas('session', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->get();

        // Attendance
        $attendance = Attendance::where('user_id', $userId)
            ->whereBetween('dateFormat', [$dateFrom, $dateTo])
            ->orderBy('dateFormat')
            ->get();

        $presentCount = $attendance->count();
        $lateCount = $attendance->whereNotNull('lateTime')->count();

        // Absent days calculation
        $totalDays = \Carbon\Carbon::parse($dateFrom)->diffInDays(\Carbon\Carbon::parse($dateTo)) + 1;
        $absentCount = $totalDays - $presentCount;

        // Incidents
        $incidents = IncidenceDetails::where('guard_id', $userId)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('date', 'desc')
            ->get();

        return view('forest.user-summary', [
            'user' => $user,
            'sessions' => $sessions,
            'logs' => $logs,
            'attendance' => $attendance,
            'incidents' => $incidents,
            'totalDistance' => round($totalDistance, 2),
            'presentCount' => $presentCount,
            'lateCount' => $lateCount,
            'absentCount' => $absentCount,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

}
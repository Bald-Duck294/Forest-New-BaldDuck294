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

class PatrollingController extends Controller
{
    /**
     * Display a listing of the patrols.
     */
    /**
     * Display a listing of the patrols.
     */
    public function index(Request $request)
    {
        $user = session('user');

        $query = PatrolSession::where('company_id', $user->company_id);

        /*
        |--------------------------------------------------------------------------
        | Normalize multi-select inputs
        |--------------------------------------------------------------------------
        */
        $clientIds = array_filter((array) $request->get('client_id', []));
        $siteIds = array_filter((array) $request->get('site_id', []));
        $userIds = array_filter((array) $request->get('user_id', []));
        $methods = array_filter((array) $request->get('method', []));

        $rangeCount = count($clientIds);
        $beatCount = count($siteIds);

        /*
        |--------------------------------------------------------------------------
        | Enforce UI Rules (Backend Safety)
        |--------------------------------------------------------------------------
        */
        // Multiple ranges → ignore beat & user
        if ($rangeCount > 1) {
            $siteIds = [];
            $userIds = [];
        }

        // Single range + multiple beats → ignore user
        if ($rangeCount === 1 && $beatCount > 1) {
            $userIds = [];
        }

        /*
        |--------------------------------------------------------------------------
        | Role Restrictions
        |--------------------------------------------------------------------------
        */
        if ($user->role_id == 3) {
            // Guard → only own patrols
            $query->where('user_id', $user->id);
        }

        if ($user->role_id == 2) {
            // Supervisor
            $siteAssign = SiteAssign::where('user_id', $user->id)->first();
            $siteIdsAllowed = $siteAssign ? json_decode($siteAssign->site_id, true) : [];
            $query->whereIn('site_id', $siteIdsAllowed ?: [0]);
        }

        if ($user->role_id == 7) {
            // Client Admin
            $clientAssign = SiteAssign::where('user_id', $user->id)->first();
            $clientIdsAllowed = $clientAssign ? json_decode($clientAssign->site_id, true) : [];

            $siteIdsAllowed = SiteDetails::whereIn('client_id', $clientIdsAllowed)->pluck('id')->toArray();
            $query->whereIn('site_id', $siteIdsAllowed ?: [0]);
        }

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        // Range (Client)
        if (!empty($clientIds)) {
            $siteIdsFromClients = SiteDetails::whereIn('client_id', $clientIds)->pluck('id');
            $query->whereIn('site_id', $siteIdsFromClients);
        }

        // Beat (Site)
        if (!empty($siteIds)) {
            $query->whereIn('site_id', $siteIds);
        }

        // User
        if (!empty($userIds)) {
            $query->whereIn('user_id', $userIds);
        }

        // Method
        if (!empty($methods)) {
            $query->whereIn('method', $methods);
        }

        // Date filters
        if ($request->filled('date_from') && !$request->filled('date_to')) {
            $query->whereDate('started_at', '>=', Carbon::parse($request->date_from));
        }

        if ($request->filled('date_to') && !$request->filled('date_from')) {
            $query->whereDate('started_at', '<=', Carbon::parse($request->date_to));
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('started_at', [
                Carbon::parse($request->date_from),
                Carbon::parse($request->date_to),
            ]);
        }

        // Default → today
        if (!$request->filled('date_from') && !$request->filled('date_to')) {
            $query->whereDate('started_at', today());
        }

        /*
        |--------------------------------------------------------------------------
        | Global Search
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                    ->orWhereHas('site', fn($q) => $q->where('name', 'like', "%$search%"))
                    ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%$search%"));
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */
        $perPage = $request->get('per_page', 50);

        $patrols = $query->with(['user', 'site'])
            ->latest('started_at')
            ->paginate($perPage)
            ->appends($request->all());

        /*
        |--------------------------------------------------------------------------
        | Dropdown Data (UI Sync)
        |--------------------------------------------------------------------------
        */
        $clients = ClientDetails::where('company_id', $user->company_id)->get();

        $sites = [];
        if ($rangeCount === 1) {
            $sites = SiteDetails::where('client_id', $clientIds[0])->get();
        }

        $users = [];
        if ($rangeCount === 1 && $beatCount === 1) {
            $users = SiteAssign::where('site_id', $siteIds[0])
                ->select('user_id as id', 'user_name as name')
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */
        return view('patrolling.patrollingView', compact(
            'patrols',
            'clients',
            'sites',
            'users'
        ));
    }


    public function show($id)
    {
        $user = session('user');

        $patrol = PatrolSession::with([
            'user',
            'site',
            'logs.media',
            'media'
        ])->where('company_id', $user->company_id)
            ->findOrFail($id);

        $baseUrl = "https://fms.pugarch.in/public/storage/";

        // Logs media
        $patrol->logs->each(function ($log) use ($baseUrl) {
            $log->media->each(function ($media) use ($baseUrl) {
                $media->url = $baseUrl . ltrim($media->path, '/');
            });
        });
        $geofences = SiteGeofences::where('site_id', $patrol->site_id)->select('name', 'poly_lat_lng')->get();
        // dd($geofences);
        // Session media
        $patrol->media->each(function ($media) use ($baseUrl) {
            $media->url = $baseUrl . ltrim($media->path, '/');
        });

        return view('patrolling.show', compact('patrol', 'geofences'));
    }


    public function logs($flag, Request $request)
    {

        // $company = session('company');
        $user = session('user');
        $clientId = $request->get('client_id');
        $beatId = $request->get('site_id'); // beat = site
        $userId = $request->get('user_id');
        // dd($user);
        $query = PatrolLog::with(['patrolSession.user', 'patrolSession.site', 'media'])
            ->where('company_id', $user->company_id);
        // dd($query->get());

        // filter by type
        if ($flag && $flag !== 'all') {
            $query->where('type', $flag);
        }

        // filters
        if ($request->date_from) {
            // dd(1);
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            // dd(2);
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($clientId) {
            $query->whereHas('patrolSession.site', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            });
            $userIds = SiteAssign::where('client_id', $clientId)->pluck('user_id')->toArray();
            // dd($userIds);
        }

        if ($request->site_id) {
            // dd(2);
            $query->whereHas('patrolSession', function ($q) use ($request) {
                $q->where('site_id', $request->site_id);
            });

            $userIds = SiteAssign::where('site_id', $request->site_id)->pluck('user_id')->toArray();
        }
        if ($request->user_id) {
            // dd(3);
            $query->whereHas('patrolSession', function ($q) use ($request) {
                $q->where('user_id', $request->user_id);
            });
        }
        if ($request->search) {
            // dd(4);
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                    ->orWhereHas('patrolSession.user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // dd($query->get());

        $logs = $query
            ->latest('created_at')
            ->paginate($request->per_page ?? 50);

        if ($user->role_id == 1|| $user->role_id == 8) {
            // Admin: full access to company data
            $clients = ClientDetails::where('company_id', $user->company_id)->get();

            $sites = SiteDetails::where('company_id', $user->company_id)
                ->when($clientId, fn($q) => $q->where('client_id', $clientId))
                ->get();

            $users = User::where('company_id', $user->company_id)
                ->where('showUser', 1)
                ->when($beatId, fn($q) => $q->whereIn('id', $userIds))
                ->when($clientId, fn($q) => $q->whereIn('id', $userIds))
                ->when($userId, fn($q) => $q->where('id', $userId))
                ->get();
        } elseif ($user->role_id == 2) {
            // Supervisor: access limited to assigned sites
            $siteAssign = SiteAssign::where('user_id', $user->id)->first();
            $siteIds = $siteAssign && $siteAssign->site_id ? json_decode($siteAssign->site_id) : [];

            $sites = SiteDetails::whereIn('id', $siteIds)
                ->when($clientId, fn($q) => $q->where('client_id', $clientId))
                ->get();

            $clients = []; // Supervisors don't fetch clients

            $users = SiteAssign::whereIn('site_id', $siteIds)
                ->when($beatId, fn($q) => $q->whereIn('id', $userIds))
                ->when($clientId, fn($q) => $q->whereIn('id', $userIds))
                ->when($userId, fn($q) => $q->where('user_id', $userId))
                ->select('user_id as id', 'user_name as name')
                ->get();
        } elseif ($user->role_id == 7) {
            // Client role: access limited to assigned clients
            $clientAssign = SiteAssign::where('user_id', $user->id)->first();
            $clientIds = $clientAssign && $clientAssign->site_id ? json_decode($clientAssign->site_id) : [];

            $sites = SiteDetails::whereIn('client_id', $clientIds)
                ->when($clientId, fn($q) => $q->where('client_id', $clientId))
                ->get();

            $clients = []; // Clients don't fetch other clients

            $users = SiteAssign::whereIn('client_id', $clientIds)
                ->when($beatId, fn($q) => $q->whereIn('user_id', $userIds))
                ->when($clientId, fn($q) => $q->whereIn('user_id', $userIds))
                ->when($userId, fn($q) => $q->where('user_id', $userId))
                ->select('user_id as id', 'user_name as name')
                ->get();
        }

        return view('patrolling.logs', compact('logs', 'clients', 'sites', 'users', 'flag'));
    }

    public function logDetails($id)
    {
        $user = session('user');

        $log = PatrolLog::with([
            'patrolSession.user',
            'patrolSession.site',
            'media'
        ])->where('company_id', $user->company_id)
            ->findOrFail($id);

        $baseUrl = "https://fms.pugarch.in/public/storage/";

        // Log media
        $log->media->each(function ($media) use ($baseUrl) {
            $media->url = $baseUrl . ltrim($media->path, '/');
        });

        // dd($log);

        return view('patrolling.log-details', compact('log'));
    }

    public function analysis(Request $request)
    {
        $user = session('user');
        $today = Carbon::now();

        /**
         * Range filter
         */
        $range = $request->get('range', 'daily');

        switch ($range) {
            case 'weekly':
                $start = $request->filled('start')
                    ? Carbon::parse($request->get('start'))->startOfDay()
                    : $today->copy()->startOfWeek();
                $end = $request->filled('end')
                    ? Carbon::parse($request->get('end'))->endOfDay()
                    : $today->copy()->endOfWeek();
                break;

            case 'monthly':
                $start = $request->filled('start')
                    ? Carbon::parse($request->get('start'))->startOfDay()
                    : $today->copy()->startOfMonth();
                $end = $request->filled('end')
                    ? Carbon::parse($request->get('end'))->endOfDay()
                    : $today->copy()->endOfMonth();
                break;

            case 'custom':
                $start = $request->filled('start')
                    ? Carbon::parse($request->get('start'))->startOfDay()
                    : $today->copy()->startOfDay();
                $end = $request->filled('end')
                    ? Carbon::parse($request->get('end'))->endOfDay()
                    : $today->copy()->endOfDay();
                break;

            case 'daily':
            default:
                $start = $today->copy()->startOfDay();
                $end = $today->copy()->endOfDay();
                break;
        }

        $end = $end->endOfDay(); // always include full day

        /**
         * Extra filters
         */
        $clientId = $request->get('client_id');
        $beatId = $request->get('site_id'); // beat = site
        $userId = $request->get('user_id');

        /**
         * Sessions
         */
        $sessionsQuery = PatrolSession::with(['user', 'site', 'logs', 'media'])
            ->where('company_id', $user->company_id);
        // ->whereBetween('started_at', [$start, $end]);

        if ($request->filled('date_from')) {
            $sessionsQuery->whereDate('started_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $sessionsQuery->whereDate('started_at', '<=', $request->date_to);
        }

        if (!$request->filled('date_from') && !$request->filled('date_to')) {
            $sessionsQuery->whereDate('started_at', '=', date('Y-m-d'));
        }


        if ($clientId) {
            // $sessionsQuery->where('client_id', $clientId);
            $userIds = SiteAssign::where('client_id', $clientId)->pluck('user_id')->toArray();
        }
        if ($beatId) {
            $userIds = SiteAssign::where('site_id', $clientId)->pluck('user_id')->toArray();
            // $sessionsQuery->where('site_id', $beatId);
        }
        if ($userId) {
            // $sessionsQuery->where('user_id', $userId);
            $userIds = [$userId];
        }
        if (isset($userIds) && is_array($userIds) && count($userIds) > 0) {
            // dd($userIds);
            $sessionsQuery->whereIn('user_id', $userIds);
        }

        $sessions = $sessionsQuery->get();
        $sessionIds = $sessions->pluck('id')->toArray();

        /**
         * Logs
         */
        $logsQuery = PatrolLog::with(['patrolSession', 'patrolSession.site', 'media'])
            ->where('company_id', $user->company_id)
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('patrol_session_id', $sessionIds);

        // if ($clientId) {
        //     $logsQuery->whereHas('patrolSession', fn($q) => $q->where('client_id', $clientId));
        // }
        // if ($beatId) {
        //     $logsQuery->whereHas('patrolSession', fn($q) => $q->where('site_id', $beatId));
        // }
        // if ($userId) {
        //     $logsQuery->where('patrol_session_id', $sessionIds);
        // }

        $logs = $logsQuery->get();

        /**
         * Geofences
         */
        $geofences = SiteGeofences::where('company_id', $user->company_id)
            ->where('type', 'Polygon')
            ->distinct('name')
            ->get();

        /**
         * Prepare sessions + compute total distance
         */
        $totalDistanceMeters = 0;
        $sessionsPrepared = $sessions->map(function ($s) use (&$totalDistanceMeters) {
            $coords = [];

            if (!empty($s->path_geojson)) {
                try {
                    $raw = is_string($s->path_geojson) ? json_decode($s->path_geojson, true) : $s->path_geojson;

                    if (isset($raw['type']) && $raw['type'] === 'Feature' && isset($raw['geometry'])) {
                        $geom = $raw['geometry'];
                        if ($geom['type'] === 'LineString') {
                            $coords = $geom['coordinates'];
                        }
                    } elseif (isset($raw['type']) && $raw['type'] === 'LineString') {
                        $coords = $raw['coordinates'];
                    } elseif (is_array($raw) && isset($raw[0]) && count($raw[0]) === 2) {
                        $coords = $raw; // raw [[lng,lat],...]
                    }
                } catch (\Exception $ex) {
                    $coords = [];
                }
            }

            // Distance
            $distanceMeters = $this->sumPathDistanceMeters($coords);
            $totalDistanceMeters += $distanceMeters;

            // Convert coords for JS
            $path_for_js = array_map(fn($c) => ['lat' => (float) $c[1], 'lng' => (float) $c[0]], $coords);

            $s->path_for_js = $path_for_js;
            $s->distance_m = $distanceMeters;

            return $s;
        });

        /**
         * Stats
         */
        $stats = [
            'total_sessions' => $sessions->count(),
            'total_logs' => $logs->count(),
            'total_distance_m' => $totalDistanceMeters,
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
        ];

        /**
         * Prepare geofences
         */
        $geofencesPrepared = $geofences->map(function ($g) {
            $poly = [];
            if (!empty($g->poly_lat_lng)) {
                try {
                    $raw = is_string($g->poly_lat_lng) ? json_decode($g->poly_lat_lng, true) : $g->poly_lat_lng;
                    if (isset($raw[0]['lat']) && isset($raw[0]['lng'])) {
                        $poly = $raw;
                    }
                } catch (\Exception $ex) {
                    $poly = [];
                }
            }
            return [
                'id' => $g->id,
                'name' => $g->name,
                'coords' => $poly,
            ];
        });

        /**
         * Filter dropdown options
         */
        // $clients = ClientDetails::where('company_id', $user->company_id)->get();
        // $sites   = SiteDetails::where('company_id', $user->company_id)
        //     ->when($clientId, fn($q) => $q->where('client_id', $clientId))
        //     ->get();
        // $users   = User::where('company_id', $user->company_id)
        //     // ->when($beatId, fn($q) => $q->where('site_id', $beatId))
        //     ->get();

        if ($user->role_id == 1 || $user->role_id == 8) {
            $clients = ClientDetails::where('company_id', $user->company_id)->orderBy('name', 'ASC')->get();
            $sites = SiteDetails::where('company_id', $user->company_id)
                ->when($clientId, fn($q) => $q->where('client_id', $clientId))
                ->orderBy('name', 'ASC')
                ->get();
            $users = User::where('company_id', $user->company_id)
                ->where('showUser', 1)
                ->orderBy('name', 'ASC')
                ->get();
        } elseif ($user->role_id == 2) {
            $siteAssign = SiteAssign::where('user_id', $user->id)->first();
            $siteIds = $siteAssign->site_id ? json_decode($siteAssign->site_id) : [];
            $sites = SiteDetails::whereIn('id', $siteIds)
                ->when($clientId, fn($q) => $q->where('client_id', $clientId))
                ->orderBy('name', 'ASC')
                ->get();
            $clients = []; // No clients for role_id 2
            $users = SiteAssign::whereIn('site_id', $siteIds)
                ->select('user_id as id', 'user_name as name')
                ->orderBy('name', 'ASC')
                ->get();
        } elseif ($user->role_id == 7) {
            $clientAssign = SiteAssign::where('user_id', $user->id)->first();
            $clientIds = $clientAssign->site_id ? json_decode($clientAssign->site_id) : [];
            $sites = SiteDetails::whereIn('client_id', $clientIds)
                ->when($clientId, fn($q) => $q->where('client_id', $clientId))
                ->orderBy('name', 'ASC')
                ->get();
            $clients = []; // No clients for role_id 7
            $users = SiteAssign::whereIn('client_id', $clientIds)
                ->select('user_id as id', 'user_name as name')
                ->orderBy('name', 'ASC')
                ->get();
        }


        return view('patrolling.analysis', [
            'sessions' => $sessionsPrepared,
            'logs' => $logs,
            'geofences' => $geofencesPrepared,
            'stats' => $stats,
            'range' => $range,
            'clients' => $clients,
            'sites' => $sites,
            'users' => $users,
            'filters' => [
                'client_id' => $clientId,
                'site_id' => $beatId,
                'user_id' => $userId,
            ],
        ]);
    }




    /**
     * Simple helper: determine if an array looks like [[lng,lat], ...] or [[lat,lng], ...]
     */
    private function looksLikeCoordinateArray($arr)
    {
        if (!is_array($arr) || count($arr) === 0)
            return false;
        $first = $arr[0];
        return is_array($first) && count($first) >= 2 && is_numeric($first[0]) && is_numeric($first[1]);
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
}

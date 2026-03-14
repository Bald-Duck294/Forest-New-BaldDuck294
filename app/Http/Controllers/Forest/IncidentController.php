<?php

namespace App\Http\Controllers\Forest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Forest\Traits\FilterDataTrait;

class IncidentController extends Controller
{
    use FilterDataTrait;

    /* ================= INCIDENT SUMMARY ================= */
    public function summary(Request $request)
    {
        $user = session('user');
        $base = DB::table('patrol_logs')
            ->join('patrol_sessions', 'patrol_sessions.id', '=', 'patrol_logs.patrol_session_id')
            ->leftJoin('users', 'users.id', '=', 'patrol_sessions.user_id')
            ->leftJoin('site_geofences', 'site_geofences.site_id', '=', 'patrol_sessions.site_id')
            ->whereIn('patrol_logs.type', [
                'animal_sighting',
                'water_source',
                'human_impact',
                'animal_mortality'
            ])
            ->where('patrol_sessions.company_id', $user->company_id);

        $this->applyCanonicalFilters($base, 'patrol_logs.created_at');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $base->whereBetween('patrol_logs.created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        /* KPIs */
        $kpis = [
            'total_incidents' => (clone $base)->count(),
            'animal_sightings' => (clone $base)->where('patrol_logs.type', 'animal_sighting')->count(),
            'human_impact' => (clone $base)->where('patrol_logs.type', 'human_impact')->count(),
            'water_sources' => (clone $base)->where('patrol_logs.type', 'water_source')->count(),
            'mortality' => (clone $base)->where('patrol_logs.type', 'animal_mortality')->count(),
        ];

        $typeStats = (clone $base)
            ->selectRaw('patrol_logs.type, COUNT(*) as total')
            ->groupBy('patrol_logs.type')
            ->get();

        $densityBySite = (clone $base)
            ->whereNotNull('site_geofences.site_name')
            ->selectRaw('site_geofences.site_name, COUNT(*) as incidents')
            ->groupBy('site_geofences.site_name')
            ->orderByDesc('incidents')
            ->limit(10)
            ->get();

        $repeatZones = (clone $base)
            ->whereNotNull('site_geofences.site_name')
            ->leftJoin('client_details', 'client_details.id', '=', 'site_geofences.client_id')
            ->leftJoin('site_details', 'site_details.id', '=', 'patrol_sessions.site_id')
            ->selectRaw('
                site_geofences.client_id as range_id,
                COALESCE(client_details.name, site_geofences.client_id) as range_name,
                patrol_sessions.site_id as beat_id,
                COALESCE(site_details.name, patrol_sessions.site_id) as beat_name,
                site_geofences.site_name as compartment,
                site_geofences.lat,
                site_geofences.lng,
                COUNT(*) as incidents
            ')
            ->groupBy(
                'site_geofences.client_id',
                'client_details.name',
                'patrol_sessions.site_id',
                'site_details.name',
                'site_geofences.site_name',
                'site_geofences.lat',
                'site_geofences.lng'
            )
            ->having('incidents', '>=', 3)
            ->orderByDesc('incidents')
            ->paginate(10);

        $heatmap = (clone $base)
            ->whereNotNull('patrol_logs.lat')
            ->whereNotNull('patrol_logs.lng')
            ->select('patrol_logs.lat', 'patrol_logs.lng', 'patrol_logs.type')
            ->get();

        return view('forest.incidents.summary', array_merge(
            $this->filterData(),
            compact('kpis', 'typeStats', 'densityBySite', 'repeatZones', 'heatmap')
        ));
    }

    /* ================= INCIDENT EXPLORER ================= */
    public function explorer(Request $request)
    {
        $user = session('user');
        $siteGeofences = DB::table('site_geofences')
            ->where('company_id', $user->company_id)
            ->selectRaw('site_id, MAX(client_id) as client_id, MAX(site_name) as site_name')
            ->groupBy('site_id');

        $base = DB::table('patrol_logs')
            ->join('patrol_sessions', 'patrol_sessions.id', '=', 'patrol_logs.patrol_session_id')
            ->leftJoin('users', 'users.id', '=', 'patrol_sessions.user_id')
            ->leftJoin('site_details', 'site_details.id', '=', 'patrol_sessions.site_id')
            ->leftJoin('client_details', 'client_details.id', '=', 'site_details.client_id')
            ->where('patrol_sessions.company_id', $user->company_id)
            ->whereIn('patrol_logs.type', [
                'animal_sighting',
                'water_source',
                'human_impact',
                'animal_mortality'
            ]);

        $this->applyCanonicalFilters($base, 'patrol_logs.created_at', 'patrol_sessions.site_id');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $base->whereBetween('patrol_logs.created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        /* ALL INCIDENTS */
        $incidents = (clone $base)
            ->selectRaw('
                patrol_logs.id,
                patrol_logs.type,
                patrol_logs.payload,
                patrol_logs.notes,
                patrol_logs.created_at,
                users.id as guard_id,
                users.name as guard,
                site_details.client_id as range_id,
                client_details.name as range_name,
                patrol_sessions.site_id as beat_id,
                site_details.name as beat_name,
                site_details.name as compartment,
                patrol_sessions.session,
                CASE
                    WHEN patrol_logs.type = "animal_mortality" THEN 5
                    WHEN patrol_logs.type = "human_impact" THEN 4
                    WHEN patrol_logs.type = "animal_sighting" THEN 3
                    WHEN patrol_logs.type = "water_source" THEN 2
                    ELSE 1
                END as severity
            ')
            ->orderByDesc('patrol_logs.created_at')
            ->orderByDesc('patrol_logs.id')
            ->paginate(25)
            ->withQueryString();

        /* LATEST 10 */
        $latestTop10 = (clone $base)
            ->selectRaw('
                patrol_logs.id,
                patrol_logs.type,
                users.id as guard_id,
                users.name as guard,
                site_details.client_id as range_id,
                client_details.name as range_name,
                patrol_sessions.site_id as beat_id,
                site_details.name as beat_name,
                site_details.name as compartment,
                patrol_logs.notes,
                patrol_logs.payload,
                patrol_logs.created_at,
                CASE
                    WHEN patrol_logs.type = "animal_mortality" THEN 5
                    WHEN patrol_logs.type = "human_impact" THEN 4
                    WHEN patrol_logs.type = "animal_sighting" THEN 3
                    WHEN patrol_logs.type = "water_source" THEN 2
                    ELSE 1
                END as severity
            ')
            ->orderByDesc('patrol_logs.created_at')
            ->orderByDesc('patrol_logs.id')
            ->limit(10)
            ->get();

        $typeStats = (clone $base)
            ->selectRaw('patrol_logs.type, COUNT(*) as total')
            ->groupBy('patrol_logs.type')
            ->get();

        $sessionStats = (clone $base)
            ->selectRaw('patrol_sessions.session, COUNT(*) as total')
            ->groupBy('patrol_sessions.session')
            ->get();

        return view('forest.incidents.explorer', array_merge(
            $this->filterData(),
            compact('incidents', 'latestTop10', 'typeStats', 'sessionStats')
        ));
    }

    /* ================= INCIDENT NEARBY (for map clicks) ================= */
    public function nearby(Request $request)
    {
        if (!$request->filled('lat') || !$request->filled('lng')) {
            return response()->json(['error' => 'Location required'], 400);
        }

        $user = session('user');

        $lat = $request->lat;
        $lng = $request->lng;
        $radius = $request->get('radius', 5); // km radius, default 5km

        $base = DB::table('patrol_logs')
            ->join('patrol_sessions', 'patrol_sessions.id', '=', 'patrol_logs.patrol_session_id')
            ->leftJoin('users', 'users.id', '=', 'patrol_sessions.user_id')
            ->leftJoin('site_details', 'site_details.id', '=', 'patrol_sessions.site_id')
            ->leftJoin('client_details', 'client_details.id', '=', 'site_details.client_id')
            ->whereIn('patrol_logs.type', [
                'animal_sighting',
                'water_source',
                'human_impact',
                'animal_mortality'
            ])
            ->where('patrol_sessions.company_id', $user->company_id)
            ->whereNotNull('patrol_logs.lat')
            ->whereNotNull('patrol_logs.lng');

        $this->applyCanonicalFilters($base, 'patrol_logs.created_at', 'patrol_sessions.site_id');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $base->whereBetween('patrol_logs.created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        // Calculate distance using Haversine formula
        $incidents = $base
            ->selectRaw("
                patrol_logs.id,
                patrol_logs.type,
                patrol_logs.payload,
                patrol_logs.notes,
                patrol_logs.lat,
                patrol_logs.lng,
                patrol_logs.created_at,
                users.name as guard,
                users.contact as guard_contact,
                site_details.client_id as range_id,
                client_details.name as range_name,
                patrol_sessions.site_id as beat_id,
                site_details.name as beat_name,
                site_details.name as compartment,
                patrol_sessions.session,
                patrol_sessions.type as patrol_type,
                (6371 * acos(cos(radians(?)) 
                    * cos(radians(patrol_logs.lat)) 
                    * cos(radians(patrol_logs.lng) - radians(?)) 
                    + sin(radians(?)) 
                    * sin(radians(patrol_logs.lat)))) AS distance,
                CASE
                    WHEN patrol_logs.type = 'animal_mortality' THEN 5
                    WHEN patrol_logs.type = 'human_impact' THEN 4
                    WHEN patrol_logs.type = 'animal_sighting' THEN 3
                    WHEN patrol_logs.type = 'water_source' THEN 2
                    ELSE 1
                END as severity
            ", [$lat, $lng, $lat])
            ->having('distance', '<=', $radius)
            ->orderBy('distance')
            ->orderByDesc('severity')
            ->orderByDesc('patrol_logs.created_at')
            ->limit(20)
            ->get();

        return view('forest.incidents.nearby', compact('incidents', 'lat', 'lng', 'radius'));
    }
}
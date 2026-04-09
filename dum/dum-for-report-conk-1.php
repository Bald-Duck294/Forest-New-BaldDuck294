<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ForestReportConfig;
use App\Models\Plantation;
use Illuminate\Support\Facades\DB;
use App\Asset;
use Carbon\Carbon;
use App\Users;
use App\PatrolSession;

class ForestReportConfigController extends Controller
{

    // public function reportsDashboard(Request $request)
    // {
    //     // dd('in controller of forest config');
    //     $authUser = session('user') ?? auth()->user();
    //     // dd($authUser , "auth user");
    //     $companyId = $authUser ? $authUser->company_id : 46;
    //     // $dum = Users::where('company_id', 62)->where('role_id', 1)->get();
    //     //    dd($dum , "dum");
    //     // 🔥 If Global Admin is simulating another company, prioritize the simulated ID
    //     if ($authUser && $authUser->role_id == 8 && session()->has('simulated_company_id')) {
    //         $companyId = session('simulated_company_id');
    //     }

    //     // =======================================================================
    //     // 1. BUILD BASE QUERIES FOR REPORTS & ASSETS
    //     // =======================================================================
    //     $query = DB::table('forest_reports')->where('company_id', $companyId);
    //     $assetQuery = Asset::where('company_id', $companyId);
    //     // $patrolQuery = DB::table('forest_reports')->where('company_id', $companyId)->where('created_at', '>=', now()->subDay());
    //     $plantationQuery = Plantation::where('user_id', '!=', 0); // Base query

    //     // $patrolQuery = DB::table('forest_reports')->where('company_id', $companyId)->where('created_at', '>=', now()->subDay());
    //     $patrolQuery = PatrolSession::where('company_id', $authUser->company_id)
    //         ->whereDate('created_at', today());

    //     // --- A. Range/Beat Filters (Now using IDs) ---
    //     if ($request->filled('range_id') && $request->range_id !== '0' && $request->range_id !== 'all') {
    //         $query->where('client_id', $request->range_id);
    //         $patrolQuery->where('client_id', $request->range_id);
    //         $plantationQuery->whereHas('site', function ($q) use ($request) {
    //             $q->where('client_id', $request->range_id);
    //         });
    //     }

    //     if ($request->filled('site_id') && $request->site_id !== '0' && $request->site_id !== 'all') {
    //         $query->where('site_id', $request->site_id);
    //         $patrolQuery->where('site_id', $request->site_id);
    //         $plantationQuery->where('site_id', $request->site_id);
    //     }

    //     // --- B. Date Filters (Applied to both Reports and Assets) ---
    //     if ($request->filled('date_filter') && $request->date_filter !== 'overall') {
    //         $dateFilter = $request->date_filter;

    //         if ($dateFilter === 'today') {
    //             $query->whereDate('created_at', Carbon::today());
    //             $assetQuery->whereDate('created_at', Carbon::today());
    //         } elseif ($dateFilter === 'week') {
    //             $query->where('created_at', '>=', Carbon::now()->subWeek());
    //             $assetQuery->where('created_at', '>=', Carbon::now()->subWeek());
    //         } elseif ($dateFilter === 'month') {
    //             $query->where('created_at', '>=', Carbon::now()->subMonth());
    //             $assetQuery->where('created_at', '>=', Carbon::now()->subMonth());
    //         }
    //     }

    //     // Custom Date Range
    //     if ($request->filled('from_date')) {
    //         $query->whereDate('created_at', '>=', $request->from_date);
    //         $assetQuery->whereDate('created_at', '>=', $request->from_date);
    //     }
    //     if ($request->filled('to_date')) {
    //         $query->whereDate('created_at', '<=', $request->to_date);
    //         $assetQuery->whereDate('created_at', '<=', $request->to_date);
    //     }

    //     // =======================================================================
    //     // 2. FETCH FILTERED DATA & CALCULATE DYNAMIC ATTENDANCE
    //     // =======================================================================
    //     $allReports = $query->get();
    //     // dd($allReports, "all reports");
    //     $allPlantations = $plantationQuery->get();

    //     $activePatrols = (clone $patrolQuery)->count();
    //     $totalAssets = (clone $assetQuery)->count();
    //     // dd($totalAssets);

    //     $totalGuards = Users::where('company_id', $authUser->company_id)->count();
    //     // --- DYNAMIC ATTENDANCE LOGIC ---
    //     // Denominator: Total registered guards (Role 3) assigned to these locations
    //     $totalOfficersQuery = DB::table('users')
    //         ->leftJoin('site_assign', 'users.id', '=', 'site_assign.user_id')
    //         ->where('users.company_id', $companyId)
    //         ->where('users.role_id', 3)
    //         ->where('users.showUser', 1);

    //     // Numerator: Guards (Role 3) who actually checked in, joined with site_assign for filtering
    //     $attendanceQuery = DB::table('attendance')
    //         ->join('site_assign', 'attendance.user_id', '=', 'site_assign.user_id')
    //         ->where('attendance.company_id', $companyId)
    //         ->where('attendance.role_id', 3);

    //     // Apply Range & Site Filters to Officers
    //     if ($request->filled('range_id') && $request->range_id !== '0' && $request->range_id !== 'all') {
    //         $totalOfficersQuery->where('site_assign.client_id', $request->range_id);
    //         $attendanceQuery->where('site_assign.client_id', $request->range_id);
    //     }
    //     if ($request->filled('site_id') && $request->site_id !== '0' && $request->site_id !== 'all') {
    //         $totalOfficersQuery->where('site_assign.site_id', $request->site_id);
    //         $attendanceQuery->where('site_assign.site_id', $request->site_id);
    //     }

    //     // Apply Date Filters (ONLY to Attendance)
    //     if ($request->filled('date_filter') && $request->date_filter !== 'overall') {
    //         $dateFilter = $request->date_filter;
    //         if ($dateFilter === 'today') {
    //             $attendanceQuery->where('attendance.dateFormat', date('Y-m-d'));
    //         } elseif ($dateFilter === 'week') {
    //             $attendanceQuery->where('attendance.dateFormat', '>=', Carbon::now()->subWeek()->format('Y-m-d'));
    //         } elseif ($dateFilter === 'month') {
    //             $attendanceQuery->where('attendance.dateFormat', '>=', Carbon::now()->subMonth()->format('Y-m-d'));
    //         }
    //     } elseif ($request->filled('from_date')) {
    //         $attendanceQuery->where('attendance.dateFormat', '>=', $request->from_date);
    //         if ($request->filled('to_date')) {
    //             $attendanceQuery->where('attendance.dateFormat', '<=', $request->to_date);
    //         }
    //     } else {
    //         // Default: Show Today's attendance if no specific date filter is applied
    //         $attendanceQuery->where('attendance.dateFormat', date('Y-m-d'));
    //     }

    //     // Execute queries cleanly
    //     $totalOfficers = $totalOfficersQuery->distinct('users.id')->count('users.id');
    //     $activeOfficersCount = $attendanceQuery->distinct('attendance.user_id')->count('attendance.user_id');
    //     $attendanceRate = $totalGuards > 0 ? round(($activeOfficersCount / $totalGuards) * 100) : 0;


    //     // 🔥 FETCH REAL RANGES & BEATS FOR DROPDOWNS
    //     $ranges = DB::table('client_details')->where('company_id', $companyId)->pluck('name', 'id');
    //     $beats = DB::table('site_details')->where('company_id', $companyId)->select('id', 'name', 'client_id')->get();

    //     // =======================================================================
    //     // 3. KPI CALCULATIONS (Using the filtered $allReports collection)
    //     // =======================================================================
    //     $stats = (object) [
    //         'criminal_count' => $allReports->whereIn('report_type', ['felling', 'illegal_felling', 'transport', 'storage', 'poaching', 'encroachment', 'mining'])->count(),
    //         'events_count' => $allReports->whereIn('category', ['events', 'Events & Monitoring', 'Wildlife Sighting', 'Water Body', 'Public Grievance'])->count(),
    //         'fire_count' => $allReports->whereIn('category', ['fire', 'Fire Incident'])->count(),
    //         'felling' => $allReports->whereIn('report_type', ['felling', 'illegal_felling'])->count(),
    //         'transport' => $allReports->where('report_type', 'transport')->count(),
    //         'storage' => $allReports->where('report_type', 'storage')->count(),
    //         'poaching' => $allReports->where('report_type', 'poaching')->count(),
    //         'encroachment' => $allReports->where('report_type', 'encroachment')->count(),
    //         'mining' => $allReports->where('report_type', 'mining')->count(),
    //         'wildlife' => $allReports->where('report_type', 'sighting')->count(),
    //         'water' => $allReports->where('report_type', 'water_status')->count(),
    //         'compensation' => $allReports->where('report_type', 'compensation')->count(),
    //     ];

    //     // =======================================================================
    //     // NEW: FETCH PLANTATION DATA (Respecting existing Range/Beat filters)
    //     // =======================================================================
    //     $plantationStats = [
    //         'phases' => $allPlantations->groupBy('current_phase')->map->count()->toArray(),
    //         'species' => $allPlantations->whereNotNull('plant_species')->groupBy('plant_species')->map->sum('plant_count')->toArray(),
    //         'fenced' => [
    //             'Fenced' => $allPlantations->where('is_fenced', 1)->count(),
    //             'Unfenced' => $allPlantations->where('is_fenced', 0)->count(),
    //         ],
    //         'soil_area' => $allPlantations->whereNotNull('soil_type')->groupBy('soil_type')->map->sum('area')->toArray(),
    //     ];

    //     // =======================================================================
    //     // 4. PARSE JSON DATA FOR ANALYTICAL CHARTS
    //     // =======================================================================
    //     $analytics = [
    //         'felling' => ['species_qty' => [], 'species_vol' => [], 'species_girth' => [], 'reasons' => [], 'ranges' => []],
    //         'transport' => ['vehicles_qty' => [], 'vehicles_trips' => [], 'routes' => [], 'trend' => []],
    //         'storage' => ['species_godown' => [], 'species_open' => [], 'proportion' => [], 'time_godown' => [], 'time_open' => []],
    //         'poaching' => ['species' => [], 'gender' => [], 'age' => [], 'trend' => []],
    //         'encroachment' => ['types' => [], 'area_by_range' => [], 'occupants_by_range' => [], 'trend' => []],
    //         'mining' => ['minerals' => [], 'methods' => [], 'volume_by_range' => []],
    //         'wildlife' => ['type' => [], 'gender' => [], 'evidence' => [], 'trend' => []],
    //         'water' => ['availability' => [], 'ranges' => []],
    //         'compensation' => ['claims_qty' => [], 'claims_amt' => [], 'trend' => []],
    //         'fire' => ['ranges_incidents' => [], 'ranges_area' => [], 'causes' => [], 'trend_incidents' => [], 'trend_area' => [], 'ranges_resp_time' => [], 'ranges_resp_count' => []],
    //         'plantations' => $plantationStats
    //     ];

    //     foreach ($allReports as $r) {
    //         $data = json_decode($r->report_data, true) ?? [];
    //         $type = strtolower(trim($r->report_type));

    //         $rng = $r->client_id ?? 'Unknown';
    //         $range_name = $r->range ?? $r->beat;
    //         $date = \Carbon\Carbon::parse($r->created_at)->format('M d');

    //         if ($type === 'felling') {
    //             $sp = $data['species'] ?? 'Unknown';
    //             $analytics['felling']['species_qty'][$sp] = ($analytics['felling']['species_qty'][$sp] ?? 0) + (float) ($data['qty'] ?? 0);
    //             $analytics['felling']['species_vol'][$sp] = ($analytics['felling']['species_vol'][$sp] ?? 0) + (float) ($data['volume'] ?? 0);
    //             $analytics['felling']['species_girth'][$sp] = ($analytics['felling']['species_girth'][$sp] ?? 0) + (float) ($data['girth'] ?? 0);
    //             $reason = $data['reason'] ?? 'Others';
    //             $analytics['felling']['reasons'][$reason] = ($analytics['felling']['reasons'][$reason] ?? 0) + 1;
    //             $analytics['felling']['ranges'][$range_name] = ($analytics['felling']['ranges'][$range_name] ?? 0) + 1;
    //         } elseif ($type === 'transport') {
    //             $veh = $data['vehicle_type'] ?? 'Others';
    //             $route = $data['route'] ?? 'Unknown';
    //             $raw_qty = $data['qty_final'] ?? 0;
    //             $qty = is_numeric($raw_qty) ? (float) $raw_qty : 0;

    //             $analytics['transport']['vehicles_qty'][$veh] = ($analytics['transport']['vehicles_qty'][$veh] ?? 0) + $qty;
    //             $analytics['transport']['vehicles_trips'][$veh] = ($analytics['transport']['vehicles_trips'][$veh] ?? 0) + 1;
    //             $analytics['transport']['routes'][$route] = ($analytics['transport']['routes'][$route] ?? 0) + 1;
    //             $analytics['transport']['trend'][$date] = ($analytics['transport']['trend'][$date] ?? 0) + $qty;
    //         } elseif ($type === 'storage') {
    //             $sp = $data['species'] ?? 'Unknown';
    //             $raw_qty = $data['qty_cmt'] ?? 0;
    //             $qty = is_numeric($raw_qty) ? (float) $raw_qty : 0;
    //             $storageType = $data['storage_type'] ?? 'Open Space';

    //             if ($storageType === 'Godown') {
    //                 $analytics['storage']['species_godown'][$sp] = ($analytics['storage']['species_godown'][$sp] ?? 0) + $qty;
    //                 $analytics['storage']['time_godown'][$date] = ($analytics['storage']['time_godown'][$date] ?? 0) + $qty;
    //             } else {
    //                 $analytics['storage']['species_open'][$sp] = ($analytics['storage']['species_open'][$sp] ?? 0) + $qty;
    //                 $analytics['storage']['time_open'][$date] = ($analytics['storage']['time_open'][$date] ?? 0) + $qty;
    //             }
    //             $analytics['storage']['proportion'][$sp] = ($analytics['storage']['proportion'][$sp] ?? 0) + $qty;
    //         } elseif ($type === 'poaching') {
    //             $sp = $data['species'] ?? 'Unknown';
    //             $gen = $data['gender'] ?? 'Unknown';
    //             $age = $data['age_class'] ?? 'Unknown';
    //             $analytics['poaching']['species'][$sp] = ($analytics['poaching']['species'][$sp] ?? 0) + 1;
    //             $analytics['poaching']['gender'][$gen] = ($analytics['poaching']['gender'][$gen] ?? 0) + 1;
    //             $analytics['poaching']['age'][$age] = ($analytics['poaching']['age'][$age] ?? 0) + 1;
    //             $analytics['poaching']['trend'][$date] = ($analytics['poaching']['trend'][$date] ?? 0) + 1;
    //         } elseif ($type === 'encroachment') {
    //             $encType = $data['encroachment_type'] ?? 'Unknown';
    //             $area = (float) ($data['area_hectare'] ?? 0);
    //             $occ = (int) ($data['occupants'] ?? 0);
    //             $analytics['encroachment']['types'][$encType] = ($analytics['encroachment']['types'][$encType] ?? 0) + 1;
    //             $analytics['encroachment']['area_by_range'][$range_name] = ($analytics['encroachment']['area_by_range'][$range_name] ?? 0) + $area;
    //             $analytics['encroachment']['occupants_by_range'][$range_name] = ($analytics['encroachment']['occupants_by_range'][$range_name] ?? 0) + $occ;
    //             $analytics['encroachment']['trend'][$date] = ($analytics['encroachment']['trend'][$date] ?? 0) + $area;
    //         } elseif ($type === 'mining') {
    //             $minType = $data['mineral_type'] ?? 'Unknown';
    //             $method = $data['mining_method'] ?? 'Unknown';
    //             $vol = (float) ($data['volume_cum'] ?? 0);
    //             $analytics['mining']['minerals'][$minType] = ($analytics['mining']['minerals'][$minType] ?? 0) + 1;
    //             $analytics['mining']['methods'][$method] = ($analytics['mining']['methods'][$method] ?? 0) + $vol;
    //             $analytics['mining']['volume_by_range'][$range_name] = ($analytics['mining']['volume_by_range'][$range_name] ?? 0) + $vol;
    //         } elseif ($type === 'sighting') {
    //             $sp = $data['species'] ?? 'Unknown';
    //             // dd($sp, "sp");
    //             $sType = $data['sighting_type'] ?? 'Unknown';
    //             $gender = $data['gender'] ?? 'Unknown';
    //             $evType = $data['evidence_type'] ?? 'Unknown';
    //             $qty = (int) ($data['num_animals'] ?? 1);
    //             $analytics['wildlife']['type'][$sp][$sType] = ($analytics['wildlife']['type'][$sp][$sType] ?? 0) + $qty;
    //             $analytics['wildlife']['gender'][$sp][$gender] = ($analytics['wildlife']['gender'][$sp][$gender] ?? 0) + $qty;
    //             $analytics['wildlife']['evidence'][$evType] = ($analytics['wildlife']['evidence'][$evType] ?? 0) + 1;
    //             $analytics['wildlife']['trend'][$date] = ($analytics['wildlife']['trend'][$date] ?? 0) + $qty;
    //         } elseif ($type === 'water_status') {
    //             $src = $data['source_type'] ?? 'Unknown';
    //             $isDry = $data['is_dry'] ?? 'Unknown';
    //             $analytics['water']['availability'][$src][$isDry] = ($analytics['water']['availability'][$src][$isDry] ?? 0) + 1;
    //             $analytics['water']['ranges'][$rng] = ($analytics['water']['ranges'][$rng] ?? 0) + 1;
    //         } elseif ($type === 'compensation') {
    //             $compType = $data['comp_type'] ?? 'Unknown';
    //             $amt = (float) ($data['amount_claimed'] ?? 0);
    //             $analytics['compensation']['claims_qty'][$compType] = ($analytics['compensation']['claims_qty'][$compType] ?? 0) + 1;
    //             $analytics['compensation']['claims_amt'][$compType] = ($analytics['compensation']['claims_amt'][$compType] ?? 0) + $amt;
    //             $analytics['compensation']['trend'][$date] = ($analytics['compensation']['trend'][$date] ?? 0) + 1;
    //         } elseif ($type === 'fire') {
    //             $cause = $data['fire_cause'] ?? 'Unknown';
    //             $raw_area = $data['area_burnt'] ?? 0;
    //             $area = is_numeric($raw_area) ? (float) $raw_area : 0;
    //             $raw_resp = $data['response_time'] ?? 0;
    //             $respTime = is_numeric($raw_resp) ? (float) $raw_resp : 0;

    //             $analytics['fire']['ranges_incidents'][$range_name] = ($analytics['fire']['ranges_incidents'][$range_name] ?? 0) + 1;
    //             $analytics['fire']['ranges_area'][$range_name] = ($analytics['fire']['ranges_area'][$range_name] ?? 0) + $area;
    //             $analytics['fire']['causes'][$cause] = ($analytics['fire']['causes'][$cause] ?? 0) + 1;
    //             $analytics['fire']['trend_incidents'][$date] = ($analytics['fire']['trend_incidents'][$date] ?? 0) + 1;
    //             $analytics['fire']['trend_area'][$date] = ($analytics['fire']['trend_area'][$date] ?? 0) + $area;

    //             if ($respTime > 0) {
    //                 $analytics['fire']['ranges_resp_time'][$range_name] = ($analytics['fire']['ranges_resp_time'][$range_name] ?? 0) + $respTime;
    //                 $analytics['fire']['ranges_resp_count'][$range_name] = ($analytics['fire']['ranges_resp_count'][$range_name] ?? 0) + 1;
    //             }
    //         }
    //     }

    //     // =======================================================================
    //     // 5. ASSET ANALYTICS DATA 
    //     // =======================================================================
    //     $assetDistribution = (clone $assetQuery)->select('category', DB::raw('count(*) as total'))->groupBy('category')->pluck('total', 'category')->toArray();

    //     $operationalStatus = (clone $assetQuery)->select('category', 'condition', DB::raw('count(*) as total'))->groupBy('category', 'condition')->get();
    //     $statusData = [];
    //     foreach ($operationalStatus as $os) {
    //         $statusData[$os->category][$os->condition] = $os->total;
    //     }

    //     $deploymentTrend = (clone $assetQuery)
    //         ->select(DB::raw('WEEK(created_at) as week'), DB::raw('count(*) as total'))
    //         ->groupBy('week')
    //         ->orderBy('week')
    //         ->get()
    //         ->mapWithKeys(function ($item) {
    //             return ["Wk " . $item->week => $item->total];
    //         })->toArray();

    //     // =======================================================================
    //     // 6. MAIN CHART DATA FIX 
    //     // =======================================================================
    //     $chartLabels = $allReports->groupBy('report_type')->keys()->toArray();
    //     $chartValues = $allReports->groupBy('report_type')->map->count()->values()->toArray();

    //     $beatsMap = $beats->pluck('name', 'id')->toArray();
    //     $rangesMap = $ranges->toArray(); // $ranges is already [id => name]

    //     $enhancedMapData = $allReports->whereNotNull('latitude')->whereNotNull('longitude')->map(function ($report) use ($rangesMap, $beatsMap) {
    //         // Check if IDs exist natively on the row, or hidden inside the JSON report_data
    //         $parsedData = is_string($report->report_data) ? json_decode($report->report_data, true) : [];
    //         $clientId = $report->client_id ?? $parsedData['client_id'] ?? null;
    //         $siteId = $report->site_id ?? $parsedData['site_id'] ?? null;

    //         // Resolve Range Name: Try row -> JSON -> ID Lookup -> Fallback
    //         $report->resolved_range = $report->range ?? $report->client_name ?? ($clientId ? ($rangesMap[$clientId] ?? null) : null) ?? 'Unknown Range';

    //         // Resolve Beat Name: Try row -> JSON -> ID Lookup -> Fallback
    //         $report->resolved_beat = $report->beat ?? $report->site_name ?? ($siteId ? ($beatsMap[$siteId] ?? null) : null) ?? 'Unknown Beat';

    //         return $report;
    //     })->values()->toArray();

    //     return view('dashboard.index', [
    //         'ranges' => $ranges,
    //         'beats' => $beats,
    //         'kpis' => [
    //             'officers' => (int) $activeOfficersCount, // ONLY active officers who checked in
    //             'totalOfficers' => (int) $totalGuards,       // The full denominator
    //             'attendanceRate' => $attendanceRate,           // The calculated % 
    //             'activeGuards' => (int) $activeOfficersCount,
    //             'patrols' => (int) $activePatrols,
    //             'totalPatrols' => (int) $activePatrols,
    //             'criminal' => (int) ($stats->criminal_count ?? 0),
    //             'totalIncidents' => (int) ($stats->criminal_count ?? 0),
    //             'events' => (int) ($stats->events_count ?? 0),
    //             'fire' => (int) ($stats->fire_count ?? 0),
    //             'assets' => (int) $totalAssets,
    //             'inventory' => (int) $totalAssets, // 🔥 ADD THIS LINE!
    //             'felling' => (int) ($stats->felling ?? 0),
    //             'transport' => (int) ($stats->transport ?? 0),
    //             'storage' => (int) ($stats->storage ?? 0),
    //             'poaching' => (int) ($stats->poaching ?? 0),
    //             'encroachment' => (int) ($stats->encroachment ?? 0),
    //             'mining' => (int) ($stats->mining ?? 0),
    //             'wildlife' => (int) ($stats->wildlife ?? 0),
    //             'water' => (int) ($stats->water ?? 0),
    //             'compensation' => (int) ($stats->compensation ?? 0),
    //             'totalDistance' => 0,
    //             'resolutionRate' => 0,
    //             'siteCoverage' => 0,
    //             'totalSites' => (int) $beats->count(),
    //             'plantations' => (int) $allPlantations->count(),
    //         ],
    //         'mapData' => $enhancedMapData,
    //         'chartLabels' => $chartLabels,
    //         'chartValues' => $chartValues,
    //         'analytics' => array_merge($analytics, [
    //             'assets' => ['distribution' => $assetDistribution, 'status' => $statusData, 'trend' => $deploymentTrend]
    //         ])
    //     ]);
    // }



    public function reportsDashboard(Request $request)
    {
        // dd('in controller of forest config');
        $authUser = session('user') ?? auth()->user();
        // dd($authUser , "auth user");
        $companyId = $authUser ? $authUser->company_id : 46;
        // $dum = Users::where('company_id', 62)->where('role_id', 1)->get();
        //    dd($dum , "dum");
        // 🔥 If Global Admin is simulating another company, prioritize the simulated ID
        if ($authUser && $authUser->role_id == 8 && session()->has('simulated_company_id')) {
            $companyId = session('simulated_company_id');
        }

        // =======================================================================
        // 1. BUILD BASE QUERIES FOR REPORTS & ASSETS
        // =======================================================================
        $query = DB::table('forest_reports')->where('company_id', $companyId);
        // dd($query->where('report_type', 'storage')->get());

        $assetQuery = Asset::where('company_id', $companyId);
        // $patrolQuery = DB::table('forest_reports')->where('company_id', $companyId)->where('created_at', '>=', now()->subDay());
        $plantationQuery = Plantation::where('user_id', '!=', 0); // Base query

        // $patrolQuery = DB::table('forest_reports')->where('company_id', $companyId)->where('created_at', '>=', now()->subDay());
        $patrolQuery = PatrolSession::where('company_id', $authUser->company_id)
            ->whereDate('created_at', today());

        // --- A. Range/Beat Filters (Now using IDs) ---
        if ($request->filled('range_id') && $request->range_id !== '0' && $request->range_id !== 'all') {
            $query->where('client_id', $request->range_id);
            $patrolQuery->where('client_id', $request->range_id);
            $plantationQuery->whereHas('site', function ($q) use ($request) {
                $q->where('client_id', $request->range_id);
            });
        }

        if ($request->filled('site_id') && $request->site_id !== '0' && $request->site_id !== 'all') {
            $query->where('site_id', $request->site_id);
            $patrolQuery->where('site_id', $request->site_id);
            $plantationQuery->where('site_id', $request->site_id);
        }

        // --- B. Date Filters (Applied to both Reports and Assets) ---
        if ($request->filled('date_filter') && $request->date_filter !== 'overall') {
            $dateFilter = $request->date_filter;

            if ($dateFilter === 'today') {
                $query->whereDate('created_at', Carbon::today());
                $assetQuery->whereDate('created_at', Carbon::today());
            } elseif ($dateFilter === 'week') {
                $query->where('created_at', '>=', Carbon::now()->subWeek());
                $assetQuery->where('created_at', '>=', Carbon::now()->subWeek());
            } elseif ($dateFilter === 'month') {
                $query->where('created_at', '>=', Carbon::now()->subMonth());
                $assetQuery->where('created_at', '>=', Carbon::now()->subMonth());
            }
        }

        // Custom Date Range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
            $assetQuery->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
            $assetQuery->whereDate('created_at', '<=', $request->to_date);
        }

        // =======================================================================
        // 2. FETCH FILTERED DATA & CALCULATE DYNAMIC ATTENDANCE
        // =======================================================================
        $allReports = $query->get();
        // dd($allReports, "all reports");
        $allPlantations = $plantationQuery->get();

        $activePatrols = (clone $patrolQuery)->count();
        $totalAssets = (clone $assetQuery)->count();
        // dd($totalAssets);

        $totalGuards = Users::where('company_id', $authUser->company_id)->count();
        // --- DYNAMIC ATTENDANCE LOGIC ---
        // Denominator: Total registered guards (Role 3) assigned to these locations
        $totalOfficersQuery = DB::table('users')
            ->leftJoin('site_assign', 'users.id', '=', 'site_assign.user_id')
            ->where('users.company_id', $companyId)
            ->where('users.role_id', 3)
            ->where('users.showUser', 1);

        // Numerator: Guards (Role 3) who actually checked in, joined with site_assign for filtering
        $attendanceQuery = DB::table('attendance')
            ->join('site_assign', 'attendance.user_id', '=', 'site_assign.user_id')
            ->where('attendance.company_id', $companyId)
            ->where('attendance.role_id', 3);

        // Apply Range & Site Filters to Officers
        if ($request->filled('range_id') && $request->range_id !== '0' && $request->range_id !== 'all') {
            $totalOfficersQuery->where('site_assign.client_id', $request->range_id);
            $attendanceQuery->where('site_assign.client_id', $request->range_id);
        }
        if ($request->filled('site_id') && $request->site_id !== '0' && $request->site_id !== 'all') {
            $totalOfficersQuery->where('site_assign.site_id', $request->site_id);
            $attendanceQuery->where('site_assign.site_id', $request->site_id);
        }

        // Apply Date Filters (ONLY to Attendance)
        if ($request->filled('date_filter') && $request->date_filter !== 'overall') {
            $dateFilter = $request->date_filter;
            if ($dateFilter === 'today') {
                $attendanceQuery->where('attendance.dateFormat', date('Y-m-d'));
            } elseif ($dateFilter === 'week') {
                $attendanceQuery->where('attendance.dateFormat', '>=', Carbon::now()->subWeek()->format('Y-m-d'));
            } elseif ($dateFilter === 'month') {
                $attendanceQuery->where('attendance.dateFormat', '>=', Carbon::now()->subMonth()->format('Y-m-d'));
            }
        } elseif ($request->filled('from_date')) {
            $attendanceQuery->where('attendance.dateFormat', '>=', $request->from_date);
            if ($request->filled('to_date')) {
                $attendanceQuery->where('attendance.dateFormat', '<=', $request->to_date);
            }
        } else {
            // Default: Show Today's attendance if no specific date filter is applied
            $attendanceQuery->where('attendance.dateFormat', date('Y-m-d'));
        }

        // Execute queries cleanly
        $totalOfficers = $totalOfficersQuery->distinct('users.id')->count('users.id');
        $activeOfficersCount = $attendanceQuery->distinct('attendance.user_id')->count('attendance.user_id');
        $attendanceRate = $totalGuards > 0 ? round(($activeOfficersCount / $totalGuards) * 100) : 0;


        // 🔥 FETCH REAL RANGES & BEATS FOR DROPDOWNS
        $ranges = DB::table('client_details')->where('company_id', $companyId)->pluck('name', 'id');
        $beats = DB::table('site_details')->where('company_id', $companyId)->select('id', 'name', 'client_id')->get();

        // =======================================================================
        // 3. KPI CALCULATIONS (Using the filtered $allReports collection)
        // =======================================================================
        $stats = (object) [
            'criminal_count' => $allReports->whereIn('report_type', ['felling', 'illegal_felling', 'transport', 'storage', 'poaching', 'encroachment', 'mining'])->count(),
            'events_count' => $allReports->whereIn('category', ['events', 'Events & Monitoring', 'Wildlife Sighting', 'Water Body', 'Public Grievance'])->count(),
            'fire_count' => $allReports->whereIn('category', ['fire', 'Fire Incident'])->count(),
            'felling' => $allReports->whereIn('report_type', ['felling', 'illegal_felling'])->count(),
            'transport' => $allReports->where('report_type', 'transport')->count(),
            'storage' => $allReports->where('report_type', 'storage')->count(),
            'poaching' => $allReports->where('report_type', 'poaching')->count(),
            'encroachment' => $allReports->where('report_type', 'encroachment')->count(),
            'mining' => $allReports->where('report_type', 'mining')->count(),
            'wildlife' => $allReports->where('report_type', 'sighting')->count(),
            'water' => $allReports->where('report_type', 'water_status')->count(),
            'compensation' => $allReports->where('report_type', 'compensation')->count(),
        ];

        // =======================================================================
        // NEW: FETCH PLANTATION DATA (Respecting existing Range/Beat filters)
        // =======================================================================
        $plantationStats = [
            'phases' => $allPlantations->groupBy('current_phase')->map->count()->toArray(),
            'species' => $allPlantations->whereNotNull('plant_species')->groupBy('plant_species')->map->sum('plant_count')->toArray(),
            'fenced' => [
                'Fenced' => $allPlantations->where('is_fenced', 1)->count(),
                'Unfenced' => $allPlantations->where('is_fenced', 0)->count(),
            ],
            'soil_area' => $allPlantations->whereNotNull('soil_type')->groupBy('soil_type')->map->sum('area')->toArray(),
        ];

        // =======================================================================
        // 4. PARSE JSON DATA FOR ANALYTICAL CHARTS
        // =======================================================================
        $analytics = [
            'felling' => ['species_qty' => [], 'species_vol' => [], 'species_girth' => [], 'reasons' => [], 'ranges' => []],
            'transport' => ['vehicles_qty' => [], 'vehicles_trips' => [], 'routes' => [], 'trend' => []],
            'storage' => ['species_godown' => [], 'species_open' => [], 'proportion' => [], 'time_godown' => [], 'time_open' => []],
            'poaching' => ['species' => [], 'gender' => [], 'age' => [], 'trend' => []],
            'encroachment' => ['types' => [], 'area_by_range' => [], 'occupants_by_range' => [], 'trend' => []],
            'mining' => ['minerals' => [], 'methods' => [], 'volume_by_range' => []],
            'wildlife' => ['type' => [], 'gender' => [], 'evidence' => [], 'trend' => []],
            'water' => ['availability' => [], 'ranges' => []],
            'compensation' => ['claims_qty' => [], 'claims_amt' => [], 'trend' => []],
            'fire' => ['ranges_incidents' => [], 'ranges_area' => [], 'causes' => [], 'trend_incidents' => [], 'trend_area' => [], 'ranges_resp_time' => [], 'ranges_resp_count' => []],
            'plantations' => $plantationStats
        ];

        foreach ($allReports as $r) {
            $data = json_decode($r->report_data, true) ?? [];
            $type = strtolower(trim($r->report_type));

            $rng = $r->client_id ?? 'Unknown';
            $range_name = $r->range ?? $r->beat;
            $date = \Carbon\Carbon::parse($r->created_at)->format('M d');

            if ($type === 'felling') {
                // Defensive: Support old flat JSON OR new dynamic species_group
                // If the new 'species_group' exists, we loop through it. If not, we pretend the old data is just a 1-item group.
                $speciesList = isset($data['species_group']) ? $data['species_group'] : [
                    [
                        'species' => $data['species'] ?? 'Unknown',
                        'qty' => $data['qty'] ?? 0,
                        'girth' => $data['girth'] ?? 0,
                        'volume' => $data['volume'] ?? 0
                    ]
                ];

                foreach ($speciesList as $item) {
                    $sp = $item['species'] ?? 'Unknown';
                    $analytics['felling']['species_qty'][$sp] = ($analytics['felling']['species_qty'][$sp] ?? 0) + (float) ($item['qty'] ?? 0);
                    $analytics['felling']['species_vol'][$sp] = ($analytics['felling']['species_vol'][$sp] ?? 0) + (float) ($item['volume'] ?? 0);
                    $analytics['felling']['species_girth'][$sp] = ($analytics['felling']['species_girth'][$sp] ?? 0) + (float) ($item['girth'] ?? 0);
                }

                $reason = $data['reason'] ?? 'Others';
                $analytics['felling']['reasons'][$reason] = ($analytics['felling']['reasons'][$reason] ?? 0) + 1;
                $analytics['felling']['ranges'][$range_name] = ($analytics['felling']['ranges'][$range_name] ?? 0) + 1;

            } elseif ($type === 'transport') {
                $veh = $data['vehicle_type'] ?? 'Others';
                $route = $data['route'] ?? 'Unknown';

                // Defensive: Check for new 'qty_volume' OR old 'qty_final'
                $raw_qty = $data['qty_volume'] ?? $data['qty_final'] ?? 0;
                $qty = is_numeric($raw_qty) ? (float) $raw_qty : 0;

                $analytics['transport']['vehicles_qty'][$veh] = ($analytics['transport']['vehicles_qty'][$veh] ?? 0) + $qty;
                $analytics['transport']['vehicles_trips'][$veh] = ($analytics['transport']['vehicles_trips'][$veh] ?? 0) + 1;
                $analytics['transport']['routes'][$route] = ($analytics['transport']['routes'][$route] ?? 0) + 1;
                $analytics['transport']['trend'][$date] = ($analytics['transport']['trend'][$date] ?? 0) + $qty;

            } elseif ($type === 'storage') {
                $sp = $data['species'] ?? 'Unknown';
                $raw_qty = $data['qty_cmt'] ?? 0;
                $qty = is_numeric($raw_qty) ? (float) $raw_qty : 0;
                $storageType = $data['storage_type'] ?? 'Open Space';

                if ($storageType === 'Godown') {
                    $analytics['storage']['species_godown'][$sp] = ($analytics['storage']['species_godown'][$sp] ?? 0) + $qty;
                    $analytics['storage']['time_godown'][$date] = ($analytics['storage']['time_godown'][$date] ?? 0) + $qty;
                } else {
                    $analytics['storage']['species_open'][$sp] = ($analytics['storage']['species_open'][$sp] ?? 0) + $qty;
                    $analytics['storage']['time_open'][$date] = ($analytics['storage']['time_open'][$date] ?? 0) + $qty;
                }
                $analytics['storage']['proportion'][$sp] = ($analytics['storage']['proportion'][$sp] ?? 0) + $qty;

            } elseif ($type === 'poaching') {
                $sp = $data['species'] ?? 'Unknown';
                $gen = $data['gender'] ?? 'Unknown';
                $age = $data['age_class'] ?? 'Unknown';
                $analytics['poaching']['species'][$sp] = ($analytics['poaching']['species'][$sp] ?? 0) + 1;
                $analytics['poaching']['gender'][$gen] = ($analytics['poaching']['gender'][$gen] ?? 0) + 1;
                $analytics['poaching']['age'][$age] = ($analytics['poaching']['age'][$age] ?? 0) + 1;
                $analytics['poaching']['trend'][$date] = ($analytics['poaching']['trend'][$date] ?? 0) + 1;

            } elseif ($type === 'encroachment') {
                $encType = $data['encroachment_type'] ?? 'Unknown';
                $area = (float) ($data['area_hectare'] ?? 0);

                // Defensive: Support old 'occupants' (number) OR new 'occupant_name' (string count)
                $occ = isset($data['occupant_name']) ? 1 : (int) ($data['occupants'] ?? 0);

                $analytics['encroachment']['types'][$encType] = ($analytics['encroachment']['types'][$encType] ?? 0) + 1;
                $analytics['encroachment']['area_by_range'][$range_name] = ($analytics['encroachment']['area_by_range'][$range_name] ?? 0) + $area;
                $analytics['encroachment']['occupants_by_range'][$range_name] = ($analytics['encroachment']['occupants_by_range'][$range_name] ?? 0) + $occ;
                $analytics['encroachment']['trend'][$date] = ($analytics['encroachment']['trend'][$date] ?? 0) + $area;

            } elseif ($type === 'mining') {
                $minType = $data['mineral_type'] ?? 'Unknown';

                // Defensive: Mining method is being removed, so we only group by it if it exists
                $method = $data['mining_method'] ?? 'Unknown';
                $vol = (float) ($data['volume_cum'] ?? 0);

                $analytics['mining']['minerals'][$minType] = ($analytics['mining']['minerals'][$minType] ?? 0) + 1;
                if (isset($data['mining_method'])) {
                    $analytics['mining']['methods'][$method] = ($analytics['mining']['methods'][$method] ?? 0) + $vol;
                }
                $analytics['mining']['volume_by_range'][$range_name] = ($analytics['mining']['volume_by_range'][$range_name] ?? 0) + $vol;

            } elseif ($type === 'sighting') {
                $sp = $data['species'] ?? 'Unknown';
                $sType = $data['sighting_type'] ?? 'Unknown';
                $gender = $data['gender'] ?? 'Unknown';
                $evType = $data['evidence_type'] ?? 'Unknown';
                $qty = 1; // We agreed earlier to just count the report (1) instead of summing the animals

                $analytics['wildlife']['type'][$sp][$sType] = ($analytics['wildlife']['type'][$sp][$sType] ?? 0) + $qty;
                $analytics['wildlife']['gender'][$sp][$gender] = ($analytics['wildlife']['gender'][$sp][$gender] ?? 0) + $qty;
                $analytics['wildlife']['evidence'][$evType] = ($analytics['wildlife']['evidence'][$evType] ?? 0) + 1;
                $analytics['wildlife']['trend'][$date] = ($analytics['wildlife']['trend'][$date] ?? 0) + $qty;

            } elseif ($type === 'water_status') {
                $src = $data['source_type'] ?? 'Unknown';
                $isDry = $data['is_dry'] ?? 'Unknown';
                $analytics['water']['availability'][$src][$isDry] = ($analytics['water']['availability'][$src][$isDry] ?? 0) + 1;
                $analytics['water']['ranges'][$rng] = ($analytics['water']['ranges'][$rng] ?? 0) + 1;

            } elseif ($type === 'compensation') {
                $compType = $data['comp_type'] ?? 'Unknown';
                $amt = (float) ($data['amount_claimed'] ?? 0);
                $analytics['compensation']['claims_qty'][$compType] = ($analytics['compensation']['claims_qty'][$compType] ?? 0) + 1;
                $analytics['compensation']['claims_amt'][$compType] = ($analytics['compensation']['claims_amt'][$compType] ?? 0) + $amt;
                $analytics['compensation']['trend'][$date] = ($analytics['compensation']['trend'][$date] ?? 0) + 1;

            } elseif ($type === 'jfmc') {
                $village = $data['village'] ?? 'Unknown';
                $analytics['jfmc']['villages'][$village] = ($analytics['jfmc']['villages'][$village] ?? 0) + 1;
                $analytics['jfmc']['ranges'][$range_name] = ($analytics['jfmc']['ranges'][$range_name] ?? 0) + 1;
                $analytics['jfmc']['trend'][$date] = ($analytics['jfmc']['trend'][$date] ?? 0) + 1;

            } elseif ($type === 'fire') {
                $cause = $data['fire_cause'] ?? 'Unknown';
                $raw_area = $data['area_burnt'] ?? 0;
                $area = is_numeric($raw_area) ? (float) $raw_area : 0;
                $raw_resp = $data['response_time'] ?? 0;
                $respTime = is_numeric($raw_resp) ? (float) $raw_resp : 0;

                $analytics['fire']['ranges_incidents'][$range_name] = ($analytics['fire']['ranges_incidents'][$range_name] ?? 0) + 1;
                $analytics['fire']['ranges_area'][$range_name] = ($analytics['fire']['ranges_area'][$range_name] ?? 0) + $area;
                $analytics['fire']['causes'][$cause] = ($analytics['fire']['causes'][$cause] ?? 0) + 1;
                $analytics['fire']['trend_incidents'][$date] = ($analytics['fire']['trend_incidents'][$date] ?? 0) + 1;
                $analytics['fire']['trend_area'][$date] = ($analytics['fire']['trend_area'][$date] ?? 0) + $area;

                if ($respTime > 0) {
                    $analytics['fire']['ranges_resp_time'][$range_name] = ($analytics['fire']['ranges_resp_time'][$range_name] ?? 0) + $respTime;
                    $analytics['fire']['ranges_resp_count'][$range_name] = ($analytics['fire']['ranges_resp_count'][$range_name] ?? 0) + 1;
                }
            }
        }
        // =======================================================================
        // 5. ASSET ANALYTICS DATA 
        // =======================================================================
        $assetDistribution = (clone $assetQuery)->select('category', DB::raw('count(*) as total'))->groupBy('category')->pluck('total', 'category')->toArray();

        $operationalStatus = (clone $assetQuery)->select('category', 'condition', DB::raw('count(*) as total'))->groupBy('category', 'condition')->get();
        $statusData = [];
        foreach ($operationalStatus as $os) {
            $statusData[$os->category][$os->condition] = $os->total;
        }

        $deploymentTrend = (clone $assetQuery)
            ->select(DB::raw('WEEK(created_at) as week'), DB::raw('count(*) as total'))
            ->groupBy('week')
            ->orderBy('week')
            ->get()
            ->mapWithKeys(function ($item) {
                return ["Wk " . $item->week => $item->total];
            })->toArray();

        // =======================================================================
        // 6. MAIN CHART DATA FIX 
        // =======================================================================
        $chartLabels = $allReports->groupBy('report_type')->keys()->toArray();
        $chartValues = $allReports->groupBy('report_type')->map->count()->values()->toArray();

        $beatsMap = $beats->pluck('name', 'id')->toArray();
        $rangesMap = $ranges->toArray(); // $ranges is already [id => name]

        $enhancedMapData = $allReports->whereNotNull('latitude')->whereNotNull('longitude')->map(function ($report) use ($rangesMap, $beatsMap) {
            // Check if IDs exist natively on the row, or hidden inside the JSON report_data
            $parsedData = is_string($report->report_data) ? json_decode($report->report_data, true) : [];
            $clientId = $report->client_id ?? $parsedData['client_id'] ?? null;
            $siteId = $report->site_id ?? $parsedData['site_id'] ?? null;

            // Resolve Range Name: Try row -> JSON -> ID Lookup -> Fallback
            $report->resolved_range = $report->range ?? $report->client_name ?? ($clientId ? ($rangesMap[$clientId] ?? null) : null) ?? 'Unknown Range';

            // Resolve Beat Name: Try row -> JSON -> ID Lookup -> Fallback
            $report->resolved_beat = $report->beat ?? $report->site_name ?? ($siteId ? ($beatsMap[$siteId] ?? null) : null) ?? 'Unknown Beat';

            return $report;
        })->values()->toArray();

        return view('dashboard.index', [
            'ranges' => $ranges,
            'beats' => $beats,
            'kpis' => [
                'officers' => (int) $activeOfficersCount, // ONLY active officers who checked in
                'totalOfficers' => (int) $totalGuards,       // The full denominator
                'attendanceRate' => $attendanceRate,           // The calculated % 
                'activeGuards' => (int) $activeOfficersCount,
                'patrols' => (int) $activePatrols,
                'totalPatrols' => (int) $activePatrols,
                'criminal' => (int) ($stats->criminal_count ?? 0),
                'totalIncidents' => (int) ($stats->criminal_count ?? 0),
                'events' => (int) ($stats->events_count ?? 0),
                'fire' => (int) ($stats->fire_count ?? 0),
                'assets' => (int) $totalAssets,
                'inventory' => (int) $totalAssets, // 🔥 ADD THIS LINE!
                'felling' => (int) ($stats->felling ?? 0),
                'transport' => (int) ($stats->transport ?? 0),
                'storage' => (int) ($stats->storage ?? 0),
                'poaching' => (int) ($stats->poaching ?? 0),
                'encroachment' => (int) ($stats->encroachment ?? 0),
                'mining' => (int) ($stats->mining ?? 0),
                'wildlife' => (int) ($stats->wildlife ?? 0),
                'water' => (int) ($stats->water ?? 0),
                'compensation' => (int) ($stats->compensation ?? 0),
                'totalDistance' => 0,
                'resolutionRate' => 0,
                'siteCoverage' => 0,
                'totalSites' => (int) $beats->count(),
                'plantations' => (int) $allPlantations->count(),
            ],
            'mapData' => $enhancedMapData,
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
            'analytics' => array_merge($analytics, [
                'assets' => ['distribution' => $assetDistribution, 'status' => $statusData, 'trend' => $deploymentTrend]
            ])
        ]);
    }

    public function reportsTable()
    {
        $reports = DB::table('forest_reports')->latest()->paginate(10);
        return view('events.reports_table', compact('reports'));
    }

    public function show($id)
    {
        $report = DB::table('forest_reports')->where('id', $id)->first();
        return view('events.report_show', compact('report'));
    }

    public function updateStatus(Request $request, $id)
    {
        DB::table('forest_reports')->where('id', $id)->update([
            'status' => $request->status,
            'final_remarks' => $request->final_remarks
        ]);
        return redirect()->back()->with('success', 'Report updated');
    }

    // =========================================================================
    // 1. MODAL QUICK VIEW API (Latest 20 Rows)
    // =========================================================================
    public function getKpiQuickView(Request $request)
    {
        $type = $request->type; // 'criminal', 'events', 'fire', 'assets', 'forestry'
        $companyId = session('user')->company_id ?? auth()->user()->company_id ?? 46;
        $data = [];

        if (in_array($type, ['criminal', 'events', 'fire'])) {
            // Mapping UI types to DB categories
            $catMap = [
                'criminal' => ['crimes', 'Criminal Activity'],
                'events' => ['events', 'Events & Monitoring'],
                'fire' => ['fire']
            ];

            $records = DB::table('forest_reports')
                ->where('company_id', $companyId)
                ->whereIn('category', $catMap[$type] ?? [$type])
                ->latest()
                ->limit(20)
                ->get();

            foreach ($records as $r) {
                $data[] = [
                    'id' => $r->report_id ?? 'RPT-' . $r->id,
                    'title' => $r->report_type,
                    'date' => Carbon::parse($r->created_at)->format('d M Y, h:i A'),
                    'location' => $r->beat ?? $r->range ?? 'Unknown Location',
                    'status' => $r->status ?? 'Pending'
                ];
            }
        } elseif ($type === 'assets') {
            $records = Asset::where('company_id', $companyId)->latest()->limit(20)->get();
            //    dd($records , $companyId);
            foreach ($records as $r) {
                $data[] = [
                    'id' => 'AST-' . $r->id,
                    'title' => $r->category ?? 'Equipment',
                    'date' => \Carbon\Carbon::parse($r->created_at)->format('d M Y'),
                    'location' => $r->condition ?? 'N/A',
                    'status' => 'Active'
                ];
            }
        } elseif ($type === 'forestry') {
            $records = Plantation::latest()->limit(20)->get();
            foreach ($records as $r) {
                $data[] = [
                    'id' => $r->code,
                    'title' => $r->name . ' (' . ($r->plant_species ?? 'Mixed') . ')',
                    'date' => \Carbon\Carbon::parse($r->created_at)->format('d M Y'),
                    'location' => 'Area: ' . ($r->area ?? 0) . ' Ha',
                    'status' => ucfirst($r->current_phase)
                ];
            }
        }

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    // =========================================================================
    // 2. DETAILED DATA TABLE VIEW (Full Page)
    // =========================================================================
    // public function detailedDataTable(Request $request)
    // {
    //     $companyId = session('user')->company_id ?? auth()->user()->company_id ?? 46;
    //     $category = $request->get('category', 'criminal'); // Default to criminal
    //     $search = $request->get('search');
    //     $fromDate = $request->get('from_date');
    //     $toDate = $request->get('to_date');
    //     $subType = $request->get('sub_type'); // specific event type

    //     $records = collect(); // Empty collection to start

    //     // We route the query based on the Master Category because the tables are different
    //     if (in_array($category, ['criminal', 'events', 'fire'])) {
    //         $catMap = [
    //             'criminal' => ['crimes', 'Criminal Activity'],
    //             'events' => ['events', 'Events & Monitoring'],
    //             'fire' => ['fire']
    //         ];

    //         $query = \Illuminate\Support\Facades\DB::table('forest_reports')
    //             ->where('company_id', $companyId)
    //             ->whereIn('category', $catMap[$category] ?? [$category]);

    //         if ($search) {
    //             $query->where(function($q) use ($search) {
    //                 $q->where('report_id', 'like', "%{$search}%")
    //                   ->orWhere('report_type', 'like', "%{$search}%")
    //                   ->orWhere('beat', 'like', "%{$search}%");
    //             });
    //         }
    //         if ($subType) {
    //             $query->where('report_type', $subType);
    //         }
    //         if ($fromDate) $query->whereDate('created_at', '>=', $fromDate);
    //         if ($toDate) $query->whereDate('created_at', '<=', $toDate);

    //         $records = $query->latest()->paginate(15);
    //         $viewType = 'reports';

    //     } elseif ($category === 'assets') {
    //         $query = Asset::where('company_id', $companyId);
    //         if ($search) $query->where('category', 'like', "%{$search}%");
    //         if ($fromDate) $query->whereDate('created_at', '>=', $fromDate);
    //         if ($toDate) $query->whereDate('created_at', '<=', $toDate);

    //         $records = $query->latest()->paginate(15);
    //         dd($records , "records" , $companyId);
    //         $viewType = 'assets';

    //     } elseif ($category === 'plantations') {
    //         $query = Plantation::query();
    //         if ($search) {
    //             $query->where('code', 'like', "%{$search}%")
    //                   ->orWhere('name', 'like', "%{$search}%")
    //                   ->orWhere('plant_species', 'like', "%{$search}%");
    //         }
    //         if ($fromDate) $query->whereDate('created_at', '>=', $fromDate);
    //         if ($toDate) $query->whereDate('created_at', '<=', $toDate);

    //         $records = $query->latest()->paginate(15);
    //         $viewType = 'plantations';
    //     }

    //     return view('reports.detailed', compact('records', 'category', 'search', 'fromDate', 'toDate', 'subType', 'viewType'));
    // }


    // public function detailedDataTable(Request $request)
    // {
    //     $companyId = session('user')->company_id ?? auth()->user()->company_id ?? 46;
    //     $category = $request->get('category', 'criminal'); // Default to criminal
    //     $search = $request->get('search');
    //     $fromDate = $request->get('from_date');
    //     $toDate = $request->get('to_date');
    //     $subType = $request->get('sub_type'); // specific event type

    //     $records = collect(); // Empty collection to start
    //     $viewType = $category; // By default, viewType matches category

    //     // We route the query based on the Master Category because the tables are different
    //     if (in_array($category, ['criminal', 'events', 'fire', 'onduty', 'patrol'])) {
    //         $catMap = [
    //             'criminal' => ['crimes', 'Criminal Activity'],
    //             'events' => ['events', 'Events & Monitoring'],
    //             'fire' => ['fire']
    //         ];

    //         $query = \Illuminate\Support\Facades\DB::table('forest_reports') // <-- Declared here
    //             ->where('company_id', $companyId)
    //             ->whereIn('category', $catMap[$category] ?? [$category]);
    //         // 1. FOREST REPORTS (Criminal, Events, Fire)

    //         // dd($category, "category");
    //         if (in_array($category, ['criminal', 'events', 'fire'])) {
    //             $catMap = [
    //                 'criminal' => ['crimes', 'Criminal Activity'],
    //                 'events' => ['events', 'Events & Monitoring', 'Wildlife Sighting', 'Water Body', 'Public Grievance'],
    //                 'fire' => ['fire', 'Fire Incident']
    //             ];

    //             $query = \Illuminate\Support\Facades\DB::table('forest_reports')
    //                 ->where('company_id', $companyId)
    //                 ->whereIn('category', $catMap[$category] ?? [$category]);

    //             if ($search) {
    //                 $query->where(function ($q) use ($search) {
    //                     $q->where('report_id', 'like', "%{$search}%")
    //                         ->orWhere('report_type', 'like', "%{$search}%")
    //                         ->orWhere('beat', 'like', "%{$search}%");
    //                 });
    //             }
    //             if ($subType)
    //                 $query->where('report_type', $subType);
    //             if ($fromDate)
    //                 $query->whereDate('created_at', '>=', $fromDate);
    //             if ($toDate)
    //                 $query->whereDate('created_at', '<=', $toDate);

    //             $records = $query->latest()->paginate(15);
    //             $viewType = 'reports';
    //         }
    //         // 2. ASSETS
    //         elseif ($category === 'assets') {
    //             $query = \App\Models\Asset::where('company_id', $companyId);
    //             if ($search)
    //                 $query->where('category', 'like', "%{$search}%");
    //             if ($fromDate)
    //                 $query->whereDate('created_at', '>=', $fromDate);
    //             if ($toDate)
    //                 $query->whereDate('created_at', '<=', $toDate);

    //             $records = $query->latest()->paginate(15);
    //         }
    //         // 3. PLANTATIONS
    //         elseif ($category === 'plantations') {
    //             $query = \App\Models\Plantation::query();
    //             if ($search) {
    //                 $query->where('code', 'like', "%{$search}%")
    //                     ->orWhere('name', 'like', "%{$search}%")
    //                     ->orWhere('plant_species', 'like', "%{$search}%");
    //             }
    //             if ($fromDate)
    //                 $query->whereDate('created_at', '>=', $fromDate);
    //             if ($toDate)
    //                 $query->whereDate('created_at', '<=', $toDate);

    //             $records = $query->latest()->paginate(15);
    //         }
    //         // 4. ON DUTY OFFICERS (Using your attendance logic)
    //         elseif ($category === 'onduty') {
    //             // If no date is selected, default to today to only show CURRENTLY on-duty staff
    //             $targetDate = $fromDate ? $fromDate : date('Y-m-d');

    //             // Find users who have checked in on the target date
    //             $checkInUserIds = \Illuminate\Support\Facades\DB::table('attendance')
    //                 ->where('company_id', $companyId)
    //                 ->where('dateFormat', $targetDate)
    //                 ->pluck('user_id')
    //                 ->toArray();

    //             // Use 'contact' instead of 'phone' based on your Prisma schema
    //             $query = Users::leftJoin('site_assign', 'users.id', '=', 'site_assign.user_id')
    //                 ->where('users.company_id', $companyId)
    //                 ->whereIn('users.id', $checkInUserIds)
    //                 ->select('users.id', 'users.name', 'users.contact', 'site_assign.site_name');

    //             if ($search) {
    //                 $query->where(function ($q) use ($search) {
    //                     $q->where('users.name', 'like', "%{$search}%")
    //                         ->orWhere('site_assign.site_name', 'like', "%{$search}%");
    //                 });
    //             }

    //             // Group by ALL selected columns to satisfy strict mode
    //             $records = $query->groupBy('users.id', 'users.name', 'users.contact', 'site_assign.site_name')->paginate(15);
    //         }

    //         // 5. PATROLS
    //         elseif ($category === 'patrol') {

    //             // 🔥 Change this if your actual database table is named something else (e.g., 'patrol_sessions')
    //             $tableName = 'patrolling';

    //             $query = \Illuminate\Support\Facades\DB::table($tableName)
    //                 ->leftJoin('users', "{$tableName}.user_id", '=', 'users.id')
    //                 ->leftJoin('site_details', "{$tableName}.site_id", '=', 'site_details.id')
    //                 ->where("{$tableName}.company_id", $companyId)
    //                 ->select("{$tableName}.*", 'users.name as user_name', 'site_details.name as site_name');

    //             if ($search) {
    //                 // Wrapped in a closure so it doesn't bypass the company_id where clause!
    //                 $query->where(function ($q) use ($search) {
    //                     $q->where('users.name', 'like', "%{$search}%")
    //                         ->orWhere('site_details.name', 'like', "%{$search}%");
    //                 });
    //             }

    //             if ($fromDate)
    //                 $query->whereDate("{$tableName}.created_at", '>=', $fromDate);
    //             if ($toDate)
    //                 $query->whereDate("{$tableName}.created_at", '<=', $toDate);

    //             $records = $query->latest("{$tableName}.created_at")->paginate(15);
    //         }

    //         return view('reports.detailed', compact('records', 'category', 'search', 'fromDate', 'toDate', 'subType', 'viewType'));
    //     }
    // }


    public function detailedDataTable(Request $request)
    {
        $companyId = session('user')->company_id ?? auth()->user()->company_id ?? 46;
        $category = $request->get('category', 'criminal');
        $search = $request->get('search');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $subType = $request->get('sub_type');
        $perPage = $request->get('per_page', 10);

        // 🔥 NEW: Sorting Parameters
        $sort = $request->get('sort');
        $dir = strtolower($request->get('dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        if ($subType === 'wildlife') {
            $subType = 'sighting';
        }

        $records = collect();
        $viewType = $category;

        if (in_array($category, ['criminal', 'events', 'fire', 'onduty', 'patrol', 'assets', 'plantations'])) {

            // 1. FOREST REPORTS
            if (in_array($category, ['criminal', 'events', 'fire'])) {
                $catMap = [
                    'criminal' => ['crimes', 'Criminal Activity'],
                    'events' => ['events', 'Events & Monitoring', 'Wildlife Sighting', 'Water Body', 'Public Grievance'],
                    'fire' => ['fire', 'Fire Incident']
                ];

                $query = \Illuminate\Support\Facades\DB::table('forest_reports')
                    ->where('company_id', $companyId)
                    ->whereIn('category', $catMap[$category] ?? [$category]);

                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('report_id', 'like', "%{$search}%")
                            ->orWhere('report_type', 'like', "%{$search}%")
                            ->orWhere('beat', 'like', "%{$search}%")
                            ->orWhere('range', 'like', "%{$search}%");
                    });
                }
                if ($subType)
                    $query->where('report_type', $subType);
                if ($fromDate)
                    $query->whereDate('created_at', '>=', $fromDate);
                if ($toDate)
                    $query->whereDate('created_at', '<=', $toDate);

                // Apply Sorting
                $orderBy = $sort ?: 'created_at';
                $records = $query->orderBy($orderBy, $dir)->paginate($perPage);
                $viewType = 'reports';
            }
            // 2. ASSETS
            elseif ($category === 'assets') {
                $query = \App\Asset::where('company_id', $companyId); // 🔥 FIXED NAMESPACE
                if ($search)
                    $query->where('category', 'like', "%{$search}%");
                if ($fromDate)
                    $query->whereDate('created_at', '>=', $fromDate);
                if ($toDate)
                    $query->whereDate('created_at', '<=', $toDate);

                $orderBy = $sort ?: 'created_at';
                $records = $query->orderBy($orderBy, $dir)->paginate($perPage);
            }
            // 3. PLANTATIONS
            elseif ($category === 'plantations') {
                $query = Plantation::query(); // 🔥 FIXED NAMESPACE
                if ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('plant_species', 'like', "%{$search}%");
                }
                if ($fromDate)
                    $query->whereDate('created_at', '>=', $fromDate);
                if ($toDate)
                    $query->whereDate('created_at', '<=', $toDate);

                $orderBy = $sort ?: 'created_at';
                $records = $query->orderBy($orderBy, $dir)->paginate($perPage);
            }
            // 4. ON DUTY OFFICERS
            elseif ($category === 'onduty') {
                $targetDate = $fromDate ? $fromDate : date('Y-m-d');

                $query = \Illuminate\Support\Facades\DB::table('attendance')
                    ->join('users', 'attendance.user_id', '=', 'users.id')
                    ->leftJoin('site_assign', 'users.id', '=', 'site_assign.user_id')
                    ->where('attendance.company_id', $companyId)
                    ->where('attendance.dateFormat', $targetDate)
                    ->select(
                        'users.name',
                        'site_assign.site_name',
                        'attendance.in_time',
                        'attendance.out_time',
                        'attendance.dateFormat as date',
                        'attendance.latitude',
                        'attendance.longitude',
                        'attendance.status as geofence_status'
                    );

                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('users.name', 'like', "%{$search}%")
                            ->orWhere('site_assign.site_name', 'like', "%{$search}%");
                    });
                }

                $orderBy = $sort ?: 'attendance.in_time';
                $records = $query->orderBy($orderBy, $dir)->paginate($perPage);
            }
            // 5. PATROLS
            elseif ($category === 'patrol') {
                $tableName = 'patrolling';
                $query = \Illuminate\Support\Facades\DB::table($tableName)
                    ->leftJoin('users', "{$tableName}.user_id", '=', 'users.id')
                    ->leftJoin('site_details', "{$tableName}.site_id", '=', 'site_details.id')
                    ->where("{$tableName}.company_id", $companyId)
                    ->select("{$tableName}.*", 'users.name as user_name', 'site_details.name as site_name');

                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('users.name', 'like', "%{$search}%")
                            ->orWhere('site_details.name', 'like', "%{$search}%");
                    });
                }

                if ($fromDate)
                    $query->whereDate("{$tableName}.created_at", '>=', $fromDate);
                if ($toDate)
                    $query->whereDate("{$tableName}.created_at", '<=', $toDate);

                $orderBy = $sort ?: "{$tableName}.created_at";
                $records = $query->orderBy($orderBy, $dir)->paginate($perPage);
            }

            return view('reports.detailed', compact('records', 'category', 'search', 'fromDate', 'toDate', 'subType', 'viewType', 'perPage', 'sort', 'dir'));
        }

        return redirect()->back()->with('error', 'Invalid Category');
    }
}

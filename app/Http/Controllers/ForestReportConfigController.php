<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ForestReportConfig;
use Illuminate\Support\Facades\DB;
use App\Asset;
use Carbon\Carbon;
class ForestReportConfigController extends Controller
{
    /**
     * Display the Protection Analytics Dashboard
     */
   
    // public function reportsDashboard(Request $request)
    // {
    //     $authUser = session('user');
    //     $companyId = $authUser ? $authUser->company_id : 46;

    //     $assetDistribution = Asset::where('company_id', $companyId)
    //     ->select('category', DB::raw('count(*) as total'))
    //     ->groupBy('category')
    //     ->pluck('total', 'category')->toArray();

    // // 2. Operational Status (Condition vs Category)
    // // We'll map "Good" as Active and others as Maintenance/Poor
    // $operationalStatus = Asset::where('company_id', $companyId)
    //     ->select('category', 'condition', DB::raw('count(*) as total'))
    //     ->groupBy('category', 'condition')
    //     ->get();

    // $statusData = [];
    // foreach ($operationalStatus as $os) {
    //     $statusData[$os->category][$os->condition] = $os->total;
    // }

    // // 3. Deployment Trend (Created at over last 10 weeks)
    // $deploymentTrend = Asset::where('company_id', $companyId)
    //     ->select(DB::raw('WEEK(created_at) as week'), DB::raw('count(*) as total'))
    //     ->where('created_at', '>', Carbon::now()->subWeeks(10))
    //     ->groupBy('week')
    //     ->orderBy('week')
    //     ->get()
    //     ->mapWithKeys(function($item) {
    //         return ["Wk " . $item->week => $item->total];
    //     })->toArray();


    //     // Base Query with Range and Beat Filtering
    //     $query = DB::table('forest_reports')->where('company_id', $companyId);
        
    //     if ($request->filled('range_id')) {
    //         $query->where('range', $request->range_id);
    //     }
    //     if ($request->filled('site_id')) {
    //         $query->where('beat', $request->site_id);
    //     }
        
    //     $allReports = $query->get();
    //     $totalOfficers = DB::table('users')->where('company_id', $companyId)->count();

    //     // Pluck unique Ranges and Beats for dropdowns
    //     $ranges = DB::table('forest_reports')->where('company_id', $companyId)->pluck('range')->unique()->filter()->map(function ($name, $index) {
    //         return (object)['id' => $name, 'name' => $name]; // Using name as ID for easier filtering
    //     })->values();

    //     $beats = DB::table('forest_reports')->where('company_id', $companyId)->pluck('beat')->unique()->filter()->map(function ($name, $index) {
    //         return (object)['id' => $name, 'name' => $name];
    //     })->values();

    //     // KPIs
    //     $stats = DB::table('forest_reports')->where('company_id', $companyId)
    //         ->selectRaw("
    //             SUM(CASE WHEN category = 'criminal' THEN 1 ELSE 0 END) as criminal_count,
    //             SUM(CASE WHEN category = 'events' THEN 1 ELSE 0 END) as events_count,
    //             SUM(CASE WHEN category = 'fire' THEN 1 ELSE 0 END) as fire_count,
    //             SUM(CASE WHEN report_type = 'Illegal Felling' THEN 1 ELSE 0 END) as felling,
    //             SUM(CASE WHEN report_type = 'Timber Transport' THEN 1 ELSE 0 END) as transport,
    //             SUM(CASE WHEN report_type = 'Poaching' THEN 1 ELSE 0 END) as poaching,
    //             SUM(CASE WHEN report_type = 'Encroachment' THEN 1 ELSE 0 END) as encroachment,
    //             SUM(CASE WHEN report_type = 'Illegal Mining' THEN 1 ELSE 0 END) as mining,
    //             SUM(CASE WHEN report_type = 'Animal Sighting' THEN 1 ELSE 0 END) as wildlife,
    //             SUM(CASE WHEN report_type = 'Water Status' THEN 1 ELSE 0 END) as water,
    //             SUM(CASE WHEN report_type = 'Compensation' THEN 1 ELSE 0 END) as compensation
    //         ")->first();

    //     if (!$stats) {
    //         $stats = (object)array_fill_keys(['criminal_count', 'events_count', 'fire_count', 'felling', 'transport', 'poaching', 'encroachment', 'mining', 'wildlife', 'water', 'compensation'], 0);
    //     }

    //     $activePatrols = DB::table('forest_reports')->where('company_id', $companyId)->where('created_at', '>=', now()->subDay())->count();
    //     $totalAssets = DB::table('forest_report_configs')->where('is_active', 1)->count();

    //     // -------------------------------------------------------------------------
    //     // PARSE JSON DATA FOR REAL ANALYTICAL CHARTS
    //     // -------------------------------------------------------------------------
    //    $analytics = [
    //         // ... (keep your existing criminal arrays) ...
    //         'felling'      => ['species_qty' => [], 'species_vol' => [], 'species_girth' => [], 'reasons' => [], 'ranges' => []],
    //         'transport'    => ['vehicles_qty' => [], 'vehicles_trips' => [], 'trend' => []],
    //         'storage'      => ['species_godown' => [], 'species_open' => [], 'proportion' => [], 'time_godown' => [], 'time_open' => []],
    //         'poaching'     => ['species' => [], 'gender' => [], 'age' => [], 'trend' => []],
    //         'encroachment' => ['types' => [], 'area_by_range' => [], 'occupants_by_range' => [], 'trend' => []],
    //         'mining'       => ['minerals' => [], 'methods' => [], 'volume_by_range' => []],
            
    //         // --- NEW EVENTS ARRAYS ---
    //         'wildlife'     => ['type' => [], 'gender' => [], 'evidence' => [], 'trend' => []],
    //         'water'        => ['availability' => [], 'ranges' => []],
    //         'compensation' => ['claims_qty' => [], 'claims_amt' => [], 'trend' => []]
    //     ];

    //     foreach ($allReports as $r) {
    //         $data = json_decode($r->report_data, true) ?? [];
    //         $type = $r->report_type;
    //         $rng = $r->range ?? 'Unknown';
    //         $date = \Carbon\Carbon::parse($r->created_at)->format('M d');

    //         if ($type === 'Illegal Felling') {
    //             $sp = $data['species'] ?? 'Unknown';
    //             $analytics['felling']['species_qty'][$sp] = ($analytics['felling']['species_qty'][$sp] ?? 0) + (float)($data['qty'] ?? 0);
    //             $analytics['felling']['species_vol'][$sp] = ($analytics['felling']['species_vol'][$sp] ?? 0) + (float)($data['volume'] ?? 0);
    //             $analytics['felling']['species_girth'][$sp] = ($analytics['felling']['species_girth'][$sp] ?? 0) + (float)($data['girth'] ?? 0);
    //             $reason = $data['reason'] ?? 'Others'; 
    //             $analytics['felling']['reasons'][$reason] = ($analytics['felling']['reasons'][$reason] ?? 0) + 1;
    //             $analytics['felling']['ranges'][$rng] = ($analytics['felling']['ranges'][$rng] ?? 0) + 1;
    //         } 
    //         elseif ($type === 'Timber Transport') {
    //             $veh = $data['vehicle_type'] ?? 'Others';
    //             $qty = (float)($data['qty_final'] ?? 0);
    //             $analytics['transport']['vehicles_qty'][$veh] = ($analytics['transport']['vehicles_qty'][$veh] ?? 0) + $qty;
    //             $analytics['transport']['vehicles_trips'][$veh] = ($analytics['transport']['vehicles_trips'][$veh] ?? 0) + 1;
    //             $analytics['transport']['trend'][$date] = ($analytics['transport']['trend'][$date] ?? 0) + $qty;
    //         }
    //         elseif ($type === 'Timber Storage') {
    //             $sp = $data['species'] ?? 'Unknown';
    //             $qty = (float)($data['qty_cmt'] ?? 0);
    //             $storageType = $data['storage_type'] ?? 'Open Space';
    //             if ($storageType === 'Godown') {
    //                 $analytics['storage']['species_godown'][$sp] = ($analytics['storage']['species_godown'][$sp] ?? 0) + $qty;
    //             } else {
    //                 $analytics['storage']['species_open'][$sp] = ($analytics['storage']['species_open'][$sp] ?? 0) + $qty;
    //             }
    //             $analytics['storage']['proportion'][$sp] = ($analytics['storage']['proportion'][$sp] ?? 0) + $qty;
    //         }
    //         elseif ($type === 'Poaching') {
    //             $sp = $data['species'] ?? 'Unknown';
    //             $gen = $data['gender'] ?? 'Unknown';
    //             $age = $data['age_class'] ?? 'Unknown';

    //             $analytics['poaching']['species'][$sp] = ($analytics['poaching']['species'][$sp] ?? 0) + 1;
    //             $analytics['poaching']['gender'][$gen] = ($analytics['poaching']['gender'][$gen] ?? 0) + 1;
    //             $analytics['poaching']['age'][$age] = ($analytics['poaching']['age'][$age] ?? 0) + 1;
    //             $analytics['poaching']['trend'][$date] = ($analytics['poaching']['trend'][$date] ?? 0) + 1;
    //         }
    //         elseif ($type === 'Encroachment') {
    //             $encType = $data['encroachment_type'] ?? 'Unknown';
    //             $area = (float)($data['area_hectare'] ?? 0);
    //             $occ = (int)($data['occupants'] ?? 0);

    //             $analytics['encroachment']['types'][$encType] = ($analytics['encroachment']['types'][$encType] ?? 0) + 1;
    //             $analytics['encroachment']['area_by_range'][$rng] = ($analytics['encroachment']['area_by_range'][$rng] ?? 0) + $area;
    //             $analytics['encroachment']['occupants_by_range'][$rng] = ($analytics['encroachment']['occupants_by_range'][$rng] ?? 0) + $occ;
    //             $analytics['encroachment']['trend'][$date] = ($analytics['encroachment']['trend'][$date] ?? 0) + $area;
    //         }
    //         elseif ($type === 'Illegal Mining') {
    //             $minType = $data['mineral_type'] ?? 'Unknown';
    //             $method = $data['mining_method'] ?? 'Unknown';
    //             $vol = (float)($data['volume_cum'] ?? 0);

    //             $analytics['mining']['minerals'][$minType] = ($analytics['mining']['minerals'][$minType] ?? 0) + 1;
    //             $analytics['mining']['methods'][$method] = ($analytics['mining']['methods'][$method] ?? 0) + $vol;
    //             $analytics['mining']['volume_by_range'][$rng] = ($analytics['mining']['volume_by_range'][$rng] ?? 0) + $vol;
    //         }
    //         if ($type === 'Animal Sighting') {
    //             $sp = $data['species'] ?? 'Unknown';
    //             $sType = $data['sighting_type'] ?? 'Unknown';
    //             $gender = $data['gender'] ?? 'Unknown';
    //             $evType = $data['evidence_type'] ?? 'Unknown';
    //             $qty = (int)($data['num_animals'] ?? 1);

    //             // Group by Species, then by sub-type (Direct/Indirect or Male/Female)
    //             $analytics['wildlife']['type'][$sp][$sType] = ($analytics['wildlife']['type'][$sp][$sType] ?? 0) + $qty;
    //             $analytics['wildlife']['gender'][$sp][$gender] = ($analytics['wildlife']['gender'][$sp][$gender] ?? 0) + $qty;
    //             $analytics['wildlife']['evidence'][$evType] = ($analytics['wildlife']['evidence'][$evType] ?? 0) + 1;
    //             $analytics['wildlife']['trend'][$date] = ($analytics['wildlife']['trend'][$date] ?? 0) + $qty;
    //         }
    //         elseif ($type === 'Water Status') {
    //             $src = $data['source_type'] ?? 'Unknown';
    //             $isDry = $data['is_dry'] ?? 'Unknown'; // Expecting 'Yes' or 'No'
                
    //             $analytics['water']['availability'][$src][$isDry] = ($analytics['water']['availability'][$src][$isDry] ?? 0) + 1;
    //             $analytics['water']['ranges'][$rng] = ($analytics['water']['ranges'][$rng] ?? 0) + 1;
    //         }
    //         elseif ($type === 'Compensation') {
    //             $compType = $data['comp_type'] ?? 'Unknown';
    //             $amt = (float)($data['amount_claimed'] ?? 0);
                
    //             $analytics['compensation']['claims_qty'][$compType] = ($analytics['compensation']['claims_qty'][$compType] ?? 0) + 1;
    //             $analytics['compensation']['claims_amt'][$compType] = ($analytics['compensation']['claims_amt'][$compType] ?? 0) + $amt;
    //             $analytics['compensation']['trend'][$date] = ($analytics['compensation']['trend'][$date] ?? 0) + 1;
    //         }
    //     }

    //     $chartStats = DB::table('forest_reports')->where('company_id', $companyId)
    //         ->select('report_type', DB::raw('count(*) as aggregate'))->groupBy('report_type')->get();

    //     return view('dashboard.index', [
    //         'ranges' => $ranges,
    //         'beats' => $beats,
    //         'kpis' => [
    //             'officers' => (int)$totalOfficers,
    //             'patrols' => (int)$activePatrols,
    //             'criminal' => (int)($stats->criminal_count ?? 0),
    //             'events' => (int)($stats->events_count ?? 0),
    //             'fire' => (int)($stats->fire_count ?? 0),
    //             'assets' => (int)$totalAssets,
    //             'felling' => (int)($stats->felling ?? 0),
    //             'transport' => (int)($stats->transport ?? 0),
    //             'poaching' => (int)($stats->poaching ?? 0),
    //             'encroachment' => (int)($stats->encroachment ?? 0),
    //             'mining' => (int)($stats->mining ?? 0),
    //             'wildlife' => (int)($stats->wildlife ?? 0),
    //             'water' => (int)($stats->water ?? 0),
    //             'compensation' => (int)($stats->compensation ?? 0),
    //         ],
    //         'mapData' => $allReports->whereNotNull('latitude')->whereNotNull('longitude')->values()->toArray(),
    //         'chartLabels' => $chartStats->pluck('report_type')->toArray(),
    //         'chartValues' => $chartStats->pluck('aggregate')->toArray(),
    //         'analytics' => array_merge($analytics, [
    //         'assets' => [
    //             'distribution' => $assetDistribution,
    //             'status' => $statusData,
    //             'trend' => $deploymentTrend
    //         ]
    //         ])
    //     ]);
    // }


// public function reportsDashboard(Request $request)
//     {
//         $authUser = session('user') ?? auth()->user();
//         $companyId = $authUser ? $authUser->company_id : 46;
// // dd($authUser , $companyId);
//         // 🔥 FIXED: If Global Admin is simulating another company, prioritize the simulated ID
//         if ($authUser && $authUser->role_id == 8 && session()->has('simulated_company_id')) {
//             $companyId = session('simulated_company_id');
//         }

//         $query = DB::table('forest_reports')->where('company_id', $companyId);
//         // dd($query->get() , "query" ,  $companyId);
//         // --- 1. Range/Beat Filters ---
//         if ($request->filled('range_id')) {
//             $query->where('range', $request->range_id);
//         }
//         if ($request->filled('site_id')) {
//             $query->where('beat', $request->site_id);
//         }
        
//         // --- 2. Date Filters ---
//         if ($request->filled('date_filter')) {
//             $dateFilter = $request->date_filter;
//             if ($dateFilter === 'today') {
//                 $query->whereDate('created_at', Carbon::today());
//             } elseif ($dateFilter === 'week') {
//                 $query->where('created_at', '>=', Carbon::now()->subWeek());
//             } elseif ($dateFilter === 'month') {
//                 $query->where('created_at', '>=', Carbon::now()->subMonth());
//             }
//         }

//         if ($request->filled('from_date')) {
//             $query->whereDate('created_at', '>=', $request->from_date);
//         }
//         if ($request->filled('to_date')) {
//             $query->whereDate('created_at', '<=', $request->to_date);
//         }
        
//         $allReports = $query->get();
//         // dd($allReports , "allReports" );   
//         $totalOfficers = DB::table('users')->where('company_id', $companyId)->count();

//         // 🔥 FETCH REAL RANGES & BEATS
//         $ranges = DB::table('client_details')
//             ->where('company_id', $companyId)
//             ->select('id', 'name')
//             ->get();

//             // dd($ranges , "ranges");

//         $beats = DB::table('site_details')
//             ->where('company_id', $companyId)
//             ->select('id', 'name', 'client_id')
//             ->get();

//             // dd($ranges , $beats , "beats and reanges");


//         // 🔥 FIXED: Calculate stats directly from the filtered $allReports collection.
//         // This solves the 'null' issue, makes it faster, AND ensures KPIs update when filtering by Range/Beat!
//         $stats = (object)[
//             'criminal_count' => $allReports->whereIn('category', ['crimes', 'Criminal Activity'])->count(),
//             'events_count'   => $allReports->whereIn('category', ['events', 'Events & Monitoring'])->count(),
//             'fire_count'     => $allReports->where('category', 'fire')->count(),
//             'felling'        => $allReports->where('report_type', 'felling')->count(),
//             'transport'      => $allReports->where('report_type', 'transport')->count(),
//             'storage'        => $allReports->where('report_type', 'storage')->count(),
//             'poaching'       => $allReports->where('report_type', 'poaching')->count(),
//             'encroachment'   => $allReports->where('report_type', 'encroachment')->count(),
//             'mining'         => $allReports->where('report_type', 'mining')->count(),
//             'wildlife'       => $allReports->where('report_type', 'sighting')->count(),
//             'water'          => $allReports->where('report_type', 'water_status')->count(),
//             'compensation'   => $allReports->where('report_type', 'compensation')->count(),
//         ];

//          if (!$stats) {
//             $stats = (object)array_fill_keys(['criminal_count', 'events_count', 'fire_count', 'felling', 'transport', 'storage', 'poaching', 'encroachment', 'mining', 'wildlife', 'water', 'compensation'], 0);
//         }
//         // Add filters to activePatrols as well
//         $patrolQuery = DB::table('forest_reports')
//             ->where('company_id', $companyId)
//             ->where('created_at', '>=', now()->subDay());

//         if ($request->filled('range_id')) {
//             $patrolQuery->where('range', $request->range_id);
//         }
//         if ($request->filled('site_id')) {
//             $patrolQuery->where('beat', $request->site_id);
//         }

//         $activePatrols = $patrolQuery->count();
            
//         $totalAssets = DB::table('forest_report_configs')
//             ->where('is_active', 1)
//             // ->where('company_id', $companyId) // Uncomment this if assets are also company specific
//             ->count();
       

      

//         // -------------------------------------------------------------------------
//         // PARSE JSON DATA FOR CHARTS
//         // -------------------------------------------------------------------------
//         $analytics = [
//             'felling'      => ['species_qty' => [], 'species_vol' => [], 'species_girth' => [], 'reasons' => [], 'ranges' => []],
//             'transport'    => ['vehicles_qty' => [], 'vehicles_trips' => [], 'trend' => []],
//             'storage'      => ['species_godown' => [], 'species_open' => [], 'proportion' => [], 'time_godown' => [], 'time_open' => []],
//             'poaching'     => ['species' => [], 'gender' => [], 'age' => [], 'trend' => []],
//             'encroachment' => ['types' => [], 'area_by_range' => [], 'occupants_by_range' => [], 'trend' => []],
//             'mining'       => ['minerals' => [], 'methods' => [], 'volume_by_range' => []],
//             'wildlife'     => ['type' => [], 'gender' => [], 'evidence' => [], 'trend' => []],
//             'water'        => ['availability' => [], 'ranges' => []],
//             'compensation' => ['claims_qty' => [], 'claims_amt' => [], 'trend' => []],
        
//             'fire'         => [
//                 'ranges_incidents' => [], 'ranges_area' => [], 
//                 'causes' => [], 'trend_incidents' => [], 'trend_area' => [], 
//                 'ranges_resp_time' => [], 'ranges_resp_count' => []
//             ]
        
//         ];

//         foreach ($allReports as $r) {
//             // $data = json_decode($r->report_data, true) ?? [];
//             // $type = $r->report_type; // Now parsing lowercase names based on DB
//             // $rng = $r->range ?? 'Unknown';
//             // $date = \Carbon\Carbon::parse($r->created_at)->format('M d');
//         //    dd($r , "r");
//             // 1. Decode the JSON
//             $data = json_decode($r->report_data, true) ?? [];
            
//             // 🔥 BULLETPROOF FIX: Force lowercase and trim spaces
//             $type = strtolower(trim($r->report_type)); 
            
//             $rng = $r->range ?? 'Unknown';
//             $date = \Carbon\Carbon::parse($r->created_at)->format('M d');

//             if ($type === 'felling') {
//                 $sp = $data['species'] ?? 'Unknown';
//                 $analytics['felling']['species_qty'][$sp] = ($analytics['felling']['species_qty'][$sp] ?? 0) + (float)($data['qty'] ?? 0);
//                 $analytics['felling']['species_vol'][$sp] = ($analytics['felling']['species_vol'][$sp] ?? 0) + (float)($data['volume'] ?? 0);
//                 $analytics['felling']['species_girth'][$sp] = ($analytics['felling']['species_girth'][$sp] ?? 0) + (float)($data['girth'] ?? 0);
//                 $reason = $data['reason'] ?? 'Others'; 
//                 $analytics['felling']['reasons'][$reason] = ($analytics['felling']['reasons'][$reason] ?? 0) + 1;
//                 $analytics['felling']['ranges'][$rng] = ($analytics['felling']['ranges'][$rng] ?? 0) + 1;
//             } 
//            elseif ($type === 'transport') {
//                 $veh = $data['vehicle_type'] ?? 'Others' ;
//                 $route = $data['route'] ?? 'Unknown'; // 🔥 ADDED: Capture the Smuggling Route
                
//                 $raw_qty = $data['qty_final'] ?? 0;
//                 $qty = is_numeric($raw_qty) ? (float)$raw_qty : 0; // Protects against text

//                 $analytics['transport']['vehicles_qty'][$veh] = ($analytics['transport']['vehicles_qty'][$veh] ?? 0) + $qty;
//                 $analytics['transport']['vehicles_trips'][$veh] = ($analytics['transport']['vehicles_trips'][$veh] ?? 0) + 1;
//                 $analytics['transport']['routes'][$route] = ($analytics['transport']['routes'][$route] ?? 0) + 1; // 🔥 ADDED: Count Routes
//                 $analytics['transport']['trend'][$date] = ($analytics['transport']['trend'][$date] ?? 0) + $qty;
//             }
//           elseif ($type === 'storage') {
//                 $sp = $data['species'] ?? 'Unknown';
                
//                 $raw_qty = $data['qty_cmt'] ?? 0;
//                 $qty = is_numeric($raw_qty) ? (float)$raw_qty : 0; // Protects against text like "ads"
                
//                 $storageType = $data['storage_type'] ?? 'Open Space';
                
//                 if ($storageType === 'Godown') {
//                     $analytics['storage']['species_godown'][$sp] = ($analytics['storage']['species_godown'][$sp] ?? 0) + $qty;
//                     $analytics['storage']['time_godown'][$date] = ($analytics['storage']['time_godown'][$date] ?? 0) + $qty; // 🔥 ADDED: Capture Date
//                 } else {
//                     $analytics['storage']['species_open'][$sp] = ($analytics['storage']['species_open'][$sp] ?? 0) + $qty;
//                     $analytics['storage']['time_open'][$date] = ($analytics['storage']['time_open'][$date] ?? 0) + $qty; // 🔥 ADDED: Capture Date
//                 }
//                 $analytics['storage']['proportion'][$sp] = ($analytics['storage']['proportion'][$sp] ?? 0) + $qty;
//             }
//             elseif ($type === 'poaching') {
            
//                 // Safely grab the data. Check your DB to make sure keys are 'species', 'gender', 'age_class'
//                 $sp = $data['species'] ?? 'Unknown';
//                 $gen = $data['gender'] ?? 'Unknown';
//                 $age = $data['age_class'] ?? 'Unknown';
//                 // dump(  $age , "data ");
//                 $analytics['poaching']['species'][$sp] = ($analytics['poaching']['species'][$sp] ?? 0) + 1;
//                 $analytics['poaching']['gender'][$gen] = ($analytics['poaching']['gender'][$gen] ?? 0) + 1;
//                 $analytics['poaching']['age'][$age] = ($analytics['poaching']['age'][$age] ?? 0) + 1;
//                 $analytics['poaching']['trend'][$date] = ($analytics['poaching']['trend'][$date] ?? 0) + 1;
//             }
//                 elseif ($type === 'encroachment') {
//                     $encType = $data['encroachment_type'] ?? 'Unknown';
//                     $area = (float)($data['area_hectare'] ?? 0);
//                     $occ = (int)($data['occupants'] ?? 0);
//                     $analytics['encroachment']['types'][$encType] = ($analytics['encroachment']['types'][$encType] ?? 0) + 1;
//                     $analytics['encroachment']['area_by_range'][$rng] = ($analytics['encroachment']['area_by_range'][$rng] ?? 0) + $area;
//                     $analytics['encroachment']['occupants_by_range'][$rng] = ($analytics['encroachment']['occupants_by_range'][$rng] ?? 0) + $occ;
//                     $analytics['encroachment']['trend'][$date] = ($analytics['encroachment']['trend'][$date] ?? 0) + $area;
//                 }
//             elseif ($type === 'mining') {
//                 $minType = $data['mineral_type'] ?? 'Unknown';
//                 $method = $data['mining_method'] ?? 'Unknown';
//                 $vol = (float)($data['volume_cum'] ?? 0);
//                 $analytics['mining']['minerals'][$minType] = ($analytics['mining']['minerals'][$minType] ?? 0) + 1;
//                 $analytics['mining']['methods'][$method] = ($analytics['mining']['methods'][$method] ?? 0) + $vol;
//                 $analytics['mining']['volume_by_range'][$rng] = ($analytics['mining']['volume_by_range'][$rng] ?? 0) + $vol;
//             }
//             elseif ($type === 'sighting') {
//                 $sp = $data['species'] ?? 'Unknown';
//                 $sType = $data['sighting_type'] ?? 'Unknown';
//                 $gender = $data['gender'] ?? 'Unknown';
//                 $evType = $data['evidence_type'] ?? 'Unknown';
//                 $qty = (int)($data['num_animals'] ?? 1);
//                 $analytics['wildlife']['type'][$sp][$sType] = ($analytics['wildlife']['type'][$sp][$sType] ?? 0) + $qty;
//                 $analytics['wildlife']['gender'][$sp][$gender] = ($analytics['wildlife']['gender'][$sp][$gender] ?? 0) + $qty;
//                 $analytics['wildlife']['evidence'][$evType] = ($analytics['wildlife']['evidence'][$evType] ?? 0) + 1;
//                 $analytics['wildlife']['trend'][$date] = ($analytics['wildlife']['trend'][$date] ?? 0) + $qty;
//             }
//             elseif ($type === 'water_status') {
//                 $src = $data['source_type'] ?? 'Unknown';
//                 $isDry = $data['is_dry'] ?? 'Unknown'; // Yes/No
//                 $analytics['water']['availability'][$src][$isDry] = ($analytics['water']['availability'][$src][$isDry] ?? 0) + 1;
//                 $analytics['water']['ranges'][$rng] = ($analytics['water']['ranges'][$rng] ?? 0) + 1;
//             }
//             elseif ($type === 'compensation') {
//                 $compType = $data['comp_type'] ?? 'Unknown';
//                 $amt = (float)($data['amount_claimed'] ?? 0);
//                 $analytics['compensation']['claims_qty'][$compType] = ($analytics['compensation']['claims_qty'][$compType] ?? 0) + 1;
//                 $analytics['compensation']['claims_amt'][$compType] = ($analytics['compensation']['claims_amt'][$compType] ?? 0) + $amt;
//                 $analytics['compensation']['trend'][$date] = ($analytics['compensation']['trend'][$date] ?? 0) + 1;
//             } 
//             elseif ($type === 'fire') {
//                 $cause = $data['fire_cause'] ?? 'Unknown';
                
//                 // Safe numeric conversions to prevent "0" crashes
//                 $raw_area = $data['area_burnt'] ?? 0;
//                 $area = is_numeric($raw_area) ? (float)$raw_area : 0;
                
//                 $raw_resp = $data['response_time'] ?? 0;
//                 $respTime = is_numeric($raw_resp) ? (float)$raw_resp : 0;

//                 // Group by Range
//                 $analytics['fire']['ranges_incidents'][$rng] = ($analytics['fire']['ranges_incidents'][$rng] ?? 0) + 1;
//                 $analytics['fire']['ranges_area'][$rng] = ($analytics['fire']['ranges_area'][$rng] ?? 0) + $area;

//                 // Group by Cause
//                 $analytics['fire']['causes'][$cause] = ($analytics['fire']['causes'][$cause] ?? 0) + 1;

//                 // Timeline Trend
//                 $analytics['fire']['trend_incidents'][$date] = ($analytics['fire']['trend_incidents'][$date] ?? 0) + 1;
//                 $analytics['fire']['trend_area'][$date] = ($analytics['fire']['trend_area'][$date] ?? 0) + $area;

//                 // Response Time Average Prep
//                 if ($respTime > 0) {
//                     $analytics['fire']['ranges_resp_time'][$rng] = ($analytics['fire']['ranges_resp_time'][$rng] ?? 0) + $respTime;
//                     $analytics['fire']['ranges_resp_count'][$rng] = ($analytics['fire']['ranges_resp_count'][$rng] ?? 0) + 1;
//                 }
//             }
//         }
// // dd($analytics['poaching']);
//         // --- ASSET ANALYTICS DATA ---
//         $assetDistribution = Asset::where('company_id', $companyId)->select('category', DB::raw('count(*) as total'))->groupBy('category')->pluck('total', 'category')->toArray();
//         $operationalStatus = Asset::where('company_id', $companyId)->select('category', 'condition', DB::raw('count(*) as total'))->groupBy('category', 'condition')->get();
//         $statusData = [];
//         foreach ($operationalStatus as $os) { $statusData[$os->category][$os->condition] = $os->total; }
//         $deploymentTrend = Asset::where('company_id', $companyId)->select(DB::raw('WEEK(created_at) as week'), DB::raw('count(*) as total'))->where('created_at', '>', \Carbon\Carbon::now()->subWeeks(10))->groupBy('week')->orderBy('week')->get()->mapWithKeys(function($item) { return ["Wk " . $item->week => $item->total]; })->toArray();

//         $chartStats = DB::table('forest_reports')->where('company_id', $companyId)
//             ->select('report_type', DB::raw('count(*) as aggregate'))->groupBy('report_type')->get();

//             // dd($stats , $stats->criminal_count );
//         return view('dashboard.index', [
//             'ranges' => $ranges,
//             'beats' => $beats,
//             'kpis' => [
//                 'officers' => (int)$totalOfficers,
//                 'activeGuards' => (int)$totalOfficers,
//                 'patrols' => (int)$activePatrols,
//                 'totalPatrols' => (int)$activePatrols,
//                 'criminal' => (int)($stats->criminal_count ?? 0),
//                 'totalIncidents' => (int)($stats->criminal_count ?? 0),
//                 'events' => (int)($stats->events_count ?? 0),
//                 'fire' => (int)($stats->fire_count ?? 0),
//                 'assets' => (int)$totalAssets,
//                 'felling' => (int)($stats->felling ?? 0),
//                 'transport' => (int)($stats->transport ?? 0),
//                 'storage' => (int)($stats->storage ?? 0),
//                 'poaching' => (int)($stats->poaching ?? 0),
//                 'encroachment' => (int)($stats->encroachment ?? 0),
//                 'mining' => (int)($stats->mining ?? 0),
//                 'wildlife' => (int)($stats->wildlife ?? 0),
//                 'water' => (int)($stats->water ?? 0),
//                 'compensation' => (int)($stats->compensation ?? 0),
//                 // Fallback for missing keys in kpi-cards.blade.php
//                 'totalDistance' => 0,
//                 'attendanceRate' => 0,
//                 'resolutionRate' => 0,
//                 'siteCoverage' => 0,
//                 'totalSites' => (int)$beats->count(),
//             ],
//             'mapData' => $allReports->whereNotNull('latitude')->whereNotNull('longitude')->values()->toArray(),
//             'chartLabels' => $chartStats->pluck('report_type')->toArray(),
//             'chartValues' => $chartStats->pluck('aggregate')->toArray(),
//             'analytics' => array_merge($analytics, [
//                 'assets' => ['distribution' => $assetDistribution, 'status' => $statusData, 'trend' => $deploymentTrend]
//             ])
//         ]);
//     }


public function reportsDashboard(Request $request)
    {
        $authUser = session('user') ?? auth()->user();
        $companyId = $authUser ? $authUser->company_id : 46;

        // 🔥 If Global Admin is simulating another company, prioritize the simulated ID
        if ($authUser && $authUser->role_id == 8 && session()->has('simulated_company_id')) {
            $companyId = session('simulated_company_id');
        }

        // =======================================================================
        // 1. BUILD BASE QUERIES FOR REPORTS & ASSETS
        // =======================================================================
        $query = DB::table('forest_reports')->where('company_id', $companyId);
        $assetQuery = Asset::where('company_id', $companyId); 
        $patrolQuery = DB::table('forest_reports')->where('company_id', $companyId)->where('created_at', '>=', now()->subDay());

        // --- A. Range/Beat Filters (Now using IDs) ---
        // Ensure "0" or "all" values from dropdowns don't break the query
        if ($request->filled('range_id') && $request->range_id !== '0' && $request->range_id !== 'all') {
            $query->where('client_id', $request->range_id);
            $patrolQuery->where('client_id', $request->range_id);
            // WARNING: Ensure your 'assets' table has a 'client_id' column, otherwise comment this out:
            // $assetQuery->where('client_id', $request->range_id); 
        }
        
        if ($request->filled('site_id') && $request->site_id !== '0' && $request->site_id !== 'all') {
          
            $query->where('site_id', $request->site_id);
            $patrolQuery->where('site_id', $request->site_id);
            // $assetQuery->where('site_id', $request->site_id);
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
        // 2. FETCH FILTERED DATA
        // =======================================================================
        $allReports = $query->get();
        $activePatrols = $patrolQuery->count();
        $totalOfficers = DB::table('users')->where('company_id', $companyId)->count();
        $totalAssets = DB::table('forest_report_configs')->where('is_active', 1)->count();

        // 🔥 FETCH REAL RANGES & BEATS FOR DROPDOWNS
        $ranges = DB::table('client_details')->where('company_id', $companyId)->pluck('name', 'id');
        $beats  = DB::table('site_details')->where('company_id', $companyId)->select('id', 'name', 'client_id')->get();

        // =======================================================================
        // 3. KPI CALCULATIONS (Using the filtered $allReports collection)
        // =======================================================================
        $stats = (object)[
            'criminal_count' => $allReports->whereIn('category', ['crimes', 'Criminal Activity'])->count(),
            'events_count'   => $allReports->whereIn('category', ['events', 'Events & Monitoring'])->count(),
            'fire_count'     => $allReports->where('category', 'fire')->count(),
            'felling'        => $allReports->where('report_type', 'felling')->count(),
            'transport'      => $allReports->where('report_type', 'transport')->count(),
            'storage'        => $allReports->where('report_type', 'storage')->count(),
            'poaching'       => $allReports->where('report_type', 'poaching')->count(),
            'encroachment'   => $allReports->where('report_type', 'encroachment')->count(),
            'mining'         => $allReports->where('report_type', 'mining')->count(),
            'wildlife'       => $allReports->where('report_type', 'sighting')->count(),
            'water'          => $allReports->where('report_type', 'water_status')->count(),
            'compensation'   => $allReports->where('report_type', 'compensation')->count(),
        ];


        // =======================================================================
        // NEW: FETCH PLANTATION DATA (Respecting existing Range/Beat filters)
        // =======================================================================
        $plantationQuery = \App\Models\Plantation::where('user_id', '!=', 0); // Base query
        
        // Apply Range/Beat filters to Plantations if they exist
        if ($request->filled('range_id') && $request->range_id !== '0' && $request->range_id !== 'all') {
             // Assuming plantations link to site_details which links to client_details(range)
             $plantationQuery->whereHas('site', function($q) use ($request) {
                 $q->where('client_id', $request->range_id);
             });
        }
        if ($request->filled('site_id') && $request->site_id !== '0' && $request->site_id !== 'all') {
            $plantationQuery->where('site_id', $request->site_id);
        }

        $allPlantations = $plantationQuery->get();

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
            'felling'      => ['species_qty' => [], 'species_vol' => [], 'species_girth' => [], 'reasons' => [], 'ranges' => []],
            'transport'    => ['vehicles_qty' => [], 'vehicles_trips' => [], 'routes' => [], 'trend' => []],
            'storage'      => ['species_godown' => [], 'species_open' => [], 'proportion' => [], 'time_godown' => [], 'time_open' => []],
            'poaching'     => ['species' => [], 'gender' => [], 'age' => [], 'trend' => []],
            'encroachment' => ['types' => [], 'area_by_range' => [], 'occupants_by_range' => [], 'trend' => []],
            'mining'       => ['minerals' => [], 'methods' => [], 'volume_by_range' => []],
            'wildlife'     => ['type' => [], 'gender' => [], 'evidence' => [], 'trend' => []],
            'water'        => ['availability' => [], 'ranges' => []],
            'compensation' => ['claims_qty' => [], 'claims_amt' => [], 'trend' => []],
            'fire'         => ['ranges_incidents' => [], 'ranges_area' => [], 'causes' => [], 'trend_incidents' => [], 'trend_area' => [], 'ranges_resp_time' => [], 'ranges_resp_count' => []],
            'plantations' => $plantationStats
        ];

        foreach ($allReports as $r) {
            $data = json_decode($r->report_data, true) ?? [];
            $type = strtolower(trim($r->report_type)); 
            
            // 🔥 Use IDs for the charts instead of names to match your new filtering logic
            $rng = $r->client_id ?? 'Unknown'; 
            $date = \Carbon\Carbon::parse($r->created_at)->format('M d');

            if ($type === 'felling') {
                $sp = $data['species'] ?? 'Unknown';
                $analytics['felling']['species_qty'][$sp] = ($analytics['felling']['species_qty'][$sp] ?? 0) + (float)($data['qty'] ?? 0);
                $analytics['felling']['species_vol'][$sp] = ($analytics['felling']['species_vol'][$sp] ?? 0) + (float)($data['volume'] ?? 0);
                $analytics['felling']['species_girth'][$sp] = ($analytics['felling']['species_girth'][$sp] ?? 0) + (float)($data['girth'] ?? 0);
                $reason = $data['reason'] ?? 'Others'; 
                $analytics['felling']['reasons'][$reason] = ($analytics['felling']['reasons'][$reason] ?? 0) + 1;
                $analytics['felling']['ranges'][$rng] = ($analytics['felling']['ranges'][$rng] ?? 0) + 1;
            } 
            elseif ($type === 'transport') {
                $veh = $data['vehicle_type'] ?? 'Others' ;
                $route = $data['route'] ?? 'Unknown'; 
                $raw_qty = $data['qty_final'] ?? 0;
                $qty = is_numeric($raw_qty) ? (float)$raw_qty : 0; 
                
                $analytics['transport']['vehicles_qty'][$veh] = ($analytics['transport']['vehicles_qty'][$veh] ?? 0) + $qty;
                $analytics['transport']['vehicles_trips'][$veh] = ($analytics['transport']['vehicles_trips'][$veh] ?? 0) + 1;
                $analytics['transport']['routes'][$route] = ($analytics['transport']['routes'][$route] ?? 0) + 1; 
                $analytics['transport']['trend'][$date] = ($analytics['transport']['trend'][$date] ?? 0) + $qty;
            }
            elseif ($type === 'storage') {
                $sp = $data['species'] ?? 'Unknown';
                $raw_qty = $data['qty_cmt'] ?? 0;
                $qty = is_numeric($raw_qty) ? (float)$raw_qty : 0; 
                $storageType = $data['storage_type'] ?? 'Open Space';
                
                if ($storageType === 'Godown') {
                    $analytics['storage']['species_godown'][$sp] = ($analytics['storage']['species_godown'][$sp] ?? 0) + $qty;
                    $analytics['storage']['time_godown'][$date] = ($analytics['storage']['time_godown'][$date] ?? 0) + $qty; 
                } else {
                    $analytics['storage']['species_open'][$sp] = ($analytics['storage']['species_open'][$sp] ?? 0) + $qty;
                    $analytics['storage']['time_open'][$date] = ($analytics['storage']['time_open'][$date] ?? 0) + $qty; 
                }
                $analytics['storage']['proportion'][$sp] = ($analytics['storage']['proportion'][$sp] ?? 0) + $qty;
            }
            elseif ($type === 'poaching') {
                $sp = $data['species'] ?? 'Unknown';
                $gen = $data['gender'] ?? 'Unknown';
                $age = $data['age_class'] ?? 'Unknown';
                $analytics['poaching']['species'][$sp] = ($analytics['poaching']['species'][$sp] ?? 0) + 1;
                $analytics['poaching']['gender'][$gen] = ($analytics['poaching']['gender'][$gen] ?? 0) + 1;
                $analytics['poaching']['age'][$age] = ($analytics['poaching']['age'][$age] ?? 0) + 1;
                $analytics['poaching']['trend'][$date] = ($analytics['poaching']['trend'][$date] ?? 0) + 1;
            }
            elseif ($type === 'encroachment') {
                $encType = $data['encroachment_type'] ?? 'Unknown';
                $area = (float)($data['area_hectare'] ?? 0);
                $occ = (int)($data['occupants'] ?? 0);
                $analytics['encroachment']['types'][$encType] = ($analytics['encroachment']['types'][$encType] ?? 0) + 1;
                $analytics['encroachment']['area_by_range'][$rng] = ($analytics['encroachment']['area_by_range'][$rng] ?? 0) + $area;
                $analytics['encroachment']['occupants_by_range'][$rng] = ($analytics['encroachment']['occupants_by_range'][$rng] ?? 0) + $occ;
                $analytics['encroachment']['trend'][$date] = ($analytics['encroachment']['trend'][$date] ?? 0) + $area;
            }
            elseif ($type === 'mining') {
                $minType = $data['mineral_type'] ?? 'Unknown';
                $method = $data['mining_method'] ?? 'Unknown';
                $vol = (float)($data['volume_cum'] ?? 0);
                $analytics['mining']['minerals'][$minType] = ($analytics['mining']['minerals'][$minType] ?? 0) + 1;
                $analytics['mining']['methods'][$method] = ($analytics['mining']['methods'][$method] ?? 0) + $vol;
                $analytics['mining']['volume_by_range'][$rng] = ($analytics['mining']['volume_by_range'][$rng] ?? 0) + $vol;
            }
            elseif ($type === 'sighting') {
                $sp = $data['species'] ?? 'Unknown';
                $sType = $data['sighting_type'] ?? 'Unknown';
                $gender = $data['gender'] ?? 'Unknown';
                $evType = $data['evidence_type'] ?? 'Unknown';
                $qty = (int)($data['num_animals'] ?? 1);
                $analytics['wildlife']['type'][$sp][$sType] = ($analytics['wildlife']['type'][$sp][$sType] ?? 0) + $qty;
                $analytics['wildlife']['gender'][$sp][$gender] = ($analytics['wildlife']['gender'][$sp][$gender] ?? 0) + $qty;
                $analytics['wildlife']['evidence'][$evType] = ($analytics['wildlife']['evidence'][$evType] ?? 0) + 1;
                $analytics['wildlife']['trend'][$date] = ($analytics['wildlife']['trend'][$date] ?? 0) + $qty;
            }
            elseif ($type === 'water_status') {
                $src = $data['source_type'] ?? 'Unknown';
                $isDry = $data['is_dry'] ?? 'Unknown'; 
                $analytics['water']['availability'][$src][$isDry] = ($analytics['water']['availability'][$src][$isDry] ?? 0) + 1;
                $analytics['water']['ranges'][$rng] = ($analytics['water']['ranges'][$rng] ?? 0) + 1;
            }
            elseif ($type === 'compensation') {
                $compType = $data['comp_type'] ?? 'Unknown';
                $amt = (float)($data['amount_claimed'] ?? 0);
                $analytics['compensation']['claims_qty'][$compType] = ($analytics['compensation']['claims_qty'][$compType] ?? 0) + 1;
                $analytics['compensation']['claims_amt'][$compType] = ($analytics['compensation']['claims_amt'][$compType] ?? 0) + $amt;
                $analytics['compensation']['trend'][$date] = ($analytics['compensation']['trend'][$date] ?? 0) + 1;
            } 
            elseif ($type === 'fire') {
                $cause = $data['fire_cause'] ?? 'Unknown';
                $raw_area = $data['area_burnt'] ?? 0;
                $area = is_numeric($raw_area) ? (float)$raw_area : 0;
                $raw_resp = $data['response_time'] ?? 0;
                $respTime = is_numeric($raw_resp) ? (float)$raw_resp : 0;

                $analytics['fire']['ranges_incidents'][$rng] = ($analytics['fire']['ranges_incidents'][$rng] ?? 0) + 1;
                $analytics['fire']['ranges_area'][$rng] = ($analytics['fire']['ranges_area'][$rng] ?? 0) + $area;
                $analytics['fire']['causes'][$cause] = ($analytics['fire']['causes'][$cause] ?? 0) + 1;
                $analytics['fire']['trend_incidents'][$date] = ($analytics['fire']['trend_incidents'][$date] ?? 0) + 1;
                $analytics['fire']['trend_area'][$date] = ($analytics['fire']['trend_area'][$date] ?? 0) + $area;

                if ($respTime > 0) {
                    $analytics['fire']['ranges_resp_time'][$rng] = ($analytics['fire']['ranges_resp_time'][$rng] ?? 0) + $respTime;
                    $analytics['fire']['ranges_resp_count'][$rng] = ($analytics['fire']['ranges_resp_count'][$rng] ?? 0) + 1;
                }
            }
        }

        // =======================================================================
        // 5. ASSET ANALYTICS DATA (Utilizing the filtered $assetQuery)
        // =======================================================================
        $assetDistribution = (clone $assetQuery)->select('category', DB::raw('count(*) as total'))->groupBy('category')->pluck('total', 'category')->toArray();
        
        $operationalStatus = (clone $assetQuery)->select('category', 'condition', DB::raw('count(*) as total'))->groupBy('category', 'condition')->get();
        $statusData = [];
        foreach ($operationalStatus as $os) { 
            $statusData[$os->category][$os->condition] = $os->total; 
        }
        
        // Deployment trend automatically respects date filters now, but overrides weeks based on existing bounds
        $deploymentTrend = (clone $assetQuery)
            ->select(DB::raw('WEEK(created_at) as week'), DB::raw('count(*) as total'))
            ->groupBy('week')
            ->orderBy('week')
            ->get()
            ->mapWithKeys(function($item) { return ["Wk " . $item->week => $item->total]; })->toArray();

        // =======================================================================
        // 6. MAIN CHART DATA FIX (Calculate directly from Collection)
        // =======================================================================
        // 🔥 FIXED: This used to be a separate DB query that ignored filters. Now it respects them!
        $chartLabels = $allReports->groupBy('report_type')->keys()->toArray();
        $chartValues = $allReports->groupBy('report_type')->map->count()->values()->toArray();

        return view('dashboard.index', [
            'ranges' => $ranges,
            'beats'  => $beats,
            'kpis'   => [
                'officers'       => (int)$totalOfficers,
                'activeGuards'   => (int)$totalOfficers,
                'patrols'        => (int)$activePatrols,
                'totalPatrols'   => (int)$activePatrols,
                'criminal'       => (int)($stats->criminal_count ?? 0),
                'totalIncidents' => (int)($stats->criminal_count ?? 0),
                'events'         => (int)($stats->events_count ?? 0),
                'fire'           => (int)($stats->fire_count ?? 0),
                'assets'         => (int)$totalAssets,
                'felling'        => (int)($stats->felling ?? 0),
                'transport'      => (int)($stats->transport ?? 0),
                'storage'        => (int)($stats->storage ?? 0),
                'poaching'       => (int)($stats->poaching ?? 0),
                'encroachment'   => (int)($stats->encroachment ?? 0),
                'mining'         => (int)($stats->mining ?? 0),
                'wildlife'       => (int)($stats->wildlife ?? 0),
                'water'          => (int)($stats->water ?? 0),
                'compensation'   => (int)($stats->compensation ?? 0),
                'totalDistance'  => 0,
                'attendanceRate' => 0,
                'resolutionRate' => 0,
                'siteCoverage'   => 0,
                'totalSites'     => (int)$beats->count(),
                'plantations' => (int)$allPlantations->count(),
            ],
            'mapData'     => $allReports->whereNotNull('latitude')->whereNotNull('longitude')->values()->toArray(),
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
            'analytics'   => array_merge($analytics, [
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
}

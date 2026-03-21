<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ForestReportConfig;
use Illuminate\Support\Facades\DB;

class ForestReportConfigController extends Controller
{
    /**
     * Display the Protection Analytics Dashboard
     */
    public function reportsDashboard()
    {
        // 1. Fetch Total Officers
        $totalOfficers = DB::table('users')->count();

        // 2. Fetch Ranges and Beats (Fixed for "property on null" error)
        // We pluck unique names, filter out NULLs, and map them into objects
        $allReports = DB::table('forest_reports')->select('report_type', 'category')->get();

        $ranges = $allReports->pluck('report_type')
            ->unique()
            ->filter()
            ->map(function ($name, $index) {
                return (object) ['id' => $index, 'name' => $name];
            })->values();

        $beats = $allReports->pluck('category')
            ->unique()
            ->filter()
            ->map(function ($name, $index) {
                return (object) ['id' => $index, 'name' => $name];
            })->values();

        // 3. Aggregated Stats for KPIs
        $stats = DB::table('forest_reports')
            ->selectRaw("
                SUM(CASE WHEN category = 'criminal' THEN 1 ELSE 0 END) as criminal_count,
                SUM(CASE WHEN category = 'events' THEN 1 ELSE 0 END) as events_count,
                SUM(CASE WHEN category = 'fire' THEN 1 ELSE 0 END) as fire_count,
                SUM(CASE WHEN report_type = 'Illegal Felling' THEN 1 ELSE 0 END) as felling,
                SUM(CASE WHEN report_type = 'Timber Transport' THEN 1 ELSE 0 END) as transport,
                SUM(CASE WHEN report_type = 'Poaching' THEN 1 ELSE 0 END) as poaching,
                SUM(CASE WHEN report_type = 'Encroachment' THEN 1 ELSE 0 END) as encroachment,
                SUM(CASE WHEN report_type = 'Illegal Mining' THEN 1 ELSE 0 END) as mining,
                SUM(CASE WHEN report_type = 'Animal Sighting' THEN 1 ELSE 0 END) as wildlife,
                SUM(CASE WHEN report_type = 'Water Status' THEN 1 ELSE 0 END) as water,
                SUM(CASE WHEN report_type = 'Compensation' THEN 1 ELSE 0 END) as compensation
            ")
            ->first();

        // Safety fallback if table is empty
        if (!$stats) {
            $stats = (object) array_fill_keys(['criminal_count', 'events_count', 'fire_count', 'felling', 'transport', 'poaching', 'encroachment', 'mining', 'wildlife', 'water', 'compensation'], 0);
        }

        // 4. Dynamic Patrols (Reports in last 24h)
        $activePatrols = DB::table('forest_reports')->where('created_at', '>=', now()->subDay())->count();

        // 5. Dynamic Assets
        $totalAssets = DB::table('forest_report_configs')->where('is_active', 1)->count();

        // 6. Data for Main Overview Chart
        $chartStats = DB::table('forest_reports')
            ->select('report_type', DB::raw('count(*) as aggregate'))
            ->groupBy('report_type')
            ->get();

        // 7. Breakdown Details for Analytical View
        $criminalBreakdown = DB::table('forest_reports')
            ->where('category', 'criminal')
            ->select('report_type', DB::raw('count(*) as count'))
            ->groupBy('report_type')->get();

        $eventsBreakdown = DB::table('forest_reports')
            ->where('category', 'events')
            ->select('report_type', DB::raw('count(*) as count'))
            ->groupBy('report_type')->get();

        // 8. Fetch 100 Recent Reports for Map
        $reports = DB::table('forest_reports')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return view('events.reports-dashboard', [
            'ranges' => $ranges,
            'beats'  => $beats,
            'kpis' => [
                'officers'     => (int)$totalOfficers,
                'patrols'      => (int)$activePatrols,
                'criminal'     => (int)($stats->criminal_count ?? 0),
                'events'       => (int)($stats->events_count ?? 0),
                'fire'         => (int)($stats->fire_count ?? 0),
                'assets'       => (int)$totalAssets,
                'felling'      => (int)($stats->felling ?? 0),
                'transport'    => (int)($stats->transport ?? 0),
                'poaching'     => (int)($stats->poaching ?? 0),
                'encroachment' => (int)($stats->encroachment ?? 0),
                'mining'       => (int)($stats->mining ?? 0),
                'wildlife'     => (int)($stats->wildlife ?? 0),
                'water'        => (int)($stats->water ?? 0),
                'compensation' => (int)($stats->compensation ?? 0),
            ],
            'reports'     => $reports,
            'mapData'     => $reports->whereNotNull('latitude')->whereNotNull('longitude')->values()->toArray(),
            'chartLabels' => $chartStats->pluck('report_type')->toArray(),
            'chartValues' => $chartStats->pluck('aggregate')->toArray(),
            'details'     => [
                'criminalLabels' => $criminalBreakdown->pluck('report_type')->toArray(),
                'criminalValues' => $criminalBreakdown->pluck('count')->toArray(),
                'eventsLabels'   => $eventsBreakdown->pluck('report_type')->toArray(),
                'eventsValues'   => $eventsBreakdown->pluck('count')->toArray(),
            ]
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

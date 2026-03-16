<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ForestReportConfig;
use Illuminate\Support\Facades\DB;

class ForestReportConfigController extends Controller
{

    public function index()
    {
        $configs = ForestReportConfig::latest()->get();

        return view('events.report_config', compact('configs'));
    }


    public function create()
    {
        return view('events.report_config');
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required',
            'report_type' => 'required',
            'fields' => 'required|array'
        ]);

        ForestReportConfig::create([
            'category' => $request->category,
            'report_type' => $request->report_type,
            'fields' => json_encode($request->fields),
            'is_active' => $request->is_active ?? 1,
            'company_id' => session('user')->company_id ?? 56
        ]);

        return redirect()->route('report-configs.index')
            ->with('success', 'Configuration Created');
    }


    public function edit($id)
    {
        $config = ForestReportConfig::findOrFail($id);

        $fields = json_decode($config->fields, true);

        return view('events.report_configs.edit', compact('config', 'fields'));
    }


    public function update(Request $request, $id)
    {
        $config = ForestReportConfig::findOrFail($id);

        $config->update([
            'category' => $request->category,
            'report_type' => $request->report_type,
            'fields' => json_encode($request->fields),
            'is_active' => $request->is_active ?? 1
        ]);

        return redirect()->route('report-configs.index')
            ->with('success', 'Configuration Updated');
    }


    public function destroy($id)
    {
        ForestReportConfig::destroy($id);

        return back()->with('success', 'Configuration Deleted');
    }

    public function reportsDashboard()
    {
        $reports = DB::table('forest_reports')
            ->select('report_type', 'category', 'status', 'latitude', 'longitude', 'created_at')
            ->orderByDesc('created_at')
            ->limit(50)   // better for map markers
            ->get();

        return view('events.reports-dashboard', [
            'totalReports' => DB::table('forest_reports')->count(),
            'pendingReports' => DB::table('forest_reports')
                ->where('status', 'Pending') // match DB value case
                ->count(),
            'activePatrols' => 15,
            'reports' => $reports
        ]);
    }

    public function reportsTable()
    {
        $reports = DB::table('forest_reports')
            ->latest()
            ->paginate(10);

        return view('events.reports_table', compact('reports'));
    }

    public function show($id)
    {
        $report = DB::table('forest_reports')
            ->where('id', $id)
            ->first();

        return view('events.report_show', compact('report'));
    }

    public function updateStatus(Request $request, $id)
    {
        DB::table('forest_reports')
            ->where('id', $id)
            ->update([
                'status' => $request->status,
                'final_remarks' => $request->final_remarks
            ]);

        return redirect()->back()->with('success', 'Report updated');
    }
}

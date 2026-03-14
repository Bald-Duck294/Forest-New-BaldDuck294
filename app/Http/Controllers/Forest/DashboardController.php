<?php
namespace App\Http\Controllers\Forest;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('forest.analytics.dashboard', [
            'hideFilters' => true,

            'kpis' => [
                'guards' => DB::table('users')->where('isActive', 1)->count(),
                'sites' => DB::table('site_details')->count(),
                'patrols' => DB::table('patrol_sessions')->count(),
                'distance' => round(DB::table('patrol_sessions')->sum('distance'), 2),
            ],

            'attendanceChart' => DB::table('attendance')
                ->selectRaw('attendance_flag, COUNT(*) as total')
                ->groupBy('attendance_flag')
                ->pluck('total', 'attendance_flag'),

            'monthlyDistance' => DB::table('patrol_sessions')
                ->selectRaw('MONTH(started_at) as month, SUM(distance) as total')
                ->groupByRaw('MONTH(started_at)')
                ->orderBy('month')
                ->get(),
        ]);
    }
}

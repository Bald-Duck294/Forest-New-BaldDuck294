<?php

namespace App\Http\Controllers\Forest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MonthlyPatrolExport;
use App\Http\Controllers\Forest\Traits\FilterDataTrait;

class ReportController extends Controller
{
    use FilterDataTrait;

    /* ================= MONTHLY REPORT ================= */
    /* ================= MONTHLY REPORT (Unified Hub) ================= */
    public function monthly(Request $request)
    {
        $user = session('user');
        $reportType = $request->input('report_type');
        $export = $request->input('export');
        $data = null;
        $title = '';
        $summary = null;

        // Fetch Filter Data Early
        $ranges = DB::table('client_details')->where('company_id', $user->company_id)->orderBy('name')->pluck('name', 'id');
        $beats = DB::table('site_details')->where('company_id', $user->company_id)->orderBy('name')->pluck('name', 'id');
        $users = DB::table('users')->where('company_id', $user->company_id)->where('isActive', 1)->orderBy('name')->pluck('name', 'id');

        if ($reportType) {
            if ($reportType === 'attendance') {
                $title = 'Attendance Report';

                // Base Query with Filters
                $baseQuery = DB::table('attendance')->where('company_id', $user->company_id);

                $this->applyCanonicalFilters($baseQuery, 'attendance.timestamp', 'attendance.site_id');

                if ($request->filled('start_date')) {
                    $baseQuery->whereDate('attendance.date', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $baseQuery->whereDate('attendance.date', '<=', $request->end_date);
                }

                // Detailed Logs
                $data = (clone $baseQuery)
                    ->select(
                        'attendance.name as guard_name',
                        'attendance.site_name',
                        'attendance.client_name',
                        'attendance.date',
                        'attendance.entry_time',
                        'attendance.exit_time',
                        'attendance.duration_for_calc as duration',
                        'attendance.status'
                    )
                    ->orderByDesc('attendance.date')
                    ->limit(300) // Reduced limit for PDF performance
                    ->get();

                // Summary Aggregation
                $summary = (clone $baseQuery)
                    ->select(
                        'name as guard_name',
                        DB::raw('COUNT(*) as total_logs'),
                        DB::raw('SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as present_days'),
                        DB::raw('SUM(CASE WHEN status != 1 THEN 1 ELSE 0 END) as absent_days') // Assuming non-1 is absent/leave
                    )
                    ->groupBy('name')
                    ->orderByDesc('present_days')
                    ->limit(100) // Limit summary to top 100 to prevent PDF timeout
                    ->get();

            } elseif ($reportType === 'patrol') {
                $title = 'Patrol Report';
                $query = DB::table('patrol_sessions')->where('patrol_sessions.company_id', $user->company_id)
                    ->join('users', 'users.id', '=', 'patrol_sessions.user_id')
                    ->leftJoin('site_details', 'site_details.id', '=', 'patrol_sessions.site_id')
                    ->select(
                        'patrol_sessions.session',
                        'users.name as guard_name',
                        'site_details.name as site_name',
                        'patrol_sessions.started_at',
                        'patrol_sessions.ended_at',
                        'patrol_sessions.distance'
                    );

                if ($request->filled('range')) {
                    $query->where('site_details.client_id', $request->range);
                } elseif ($request->filled('client_id')) {
                    $query->where('site_details.client_id', $request->client_id);
                }

                $this->applyCanonicalFilters($query, 'patrol_sessions.started_at', 'patrol_sessions.site_id');

                if ($request->filled('start_date')) {
                    $query->whereDate('patrol_sessions.started_at', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $query->whereDate('patrol_sessions.started_at', '<=', $request->end_date);
                }

                $data = $query->orderByDesc('patrol_sessions.started_at')->limit(300)->get();

            } elseif ($reportType === 'night_patrol') {
                $title = 'Night Patrolling Report';
                $query = DB::table('patrol_sessions')->where('patrol_sessions.company_id', $user->company_id)
                    ->join('users', 'users.id', '=', 'patrol_sessions.user_id')
                    ->leftJoin('site_details', 'site_details.id', '=', 'patrol_sessions.site_id')
                    ->where(function ($q) {
                        $q->whereTime('patrol_sessions.started_at', '>=', '18:00:00')
                            ->orWhereTime('patrol_sessions.started_at', '<', '06:00:00');
                    })
                    ->select(
                        'patrol_sessions.session',
                        'users.name as guard_name',
                        'site_details.name as site_name',
                        'patrol_sessions.started_at',
                        'patrol_sessions.ended_at',
                        'patrol_sessions.distance'
                    );

                if ($request->filled('range')) {
                    $query->where('site_details.client_id', $request->range);
                }

                $this->applyCanonicalFilters($query, 'patrol_sessions.started_at', 'patrol_sessions.site_id');

                if ($request->filled('start_date')) {
                    $query->whereDate('patrol_sessions.started_at', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $query->whereDate('patrol_sessions.started_at', '<=', $request->end_date);
                }

                $data = $query->orderByDesc('patrol_sessions.started_at')->limit(300)->get();
            }
        }

        if ($export === 'pdf' && $data) {
            $pdf = Pdf::loadView('forest.reports.pdf_export', [
                // Ensure units are processed in view or processed here.
                // We'll process formatting in View for flexibility.
                'data' => $data,
                'summary' => $summary,
                'title' => $title,
                'type' => $reportType,
                'filters' => [
                    'Date Range' => ($request->start_date ?? 'All') . ' to ' . ($request->end_date ?? 'All'),
                    'Range' => $request->range ? 'Specific Range' : 'All',
                    'Generated On' => now()->format('d M Y h:i A') // Fixed format
                ]
            ]);
            return $pdf->download(\Illuminate\Support\Str::slug($title) . '_' . now()->format('Ymd') . '.pdf');
        }

        return view('forest.reports.monthly', compact('data', 'reportType', 'title', 'summary', 'ranges', 'beats', 'users'));
    }


    /* ================= FOOT REPORT ================= */
    public function footReport(Request $request)
    {
        $user = session('user');
        $base = DB::table('patrol_sessions')->where('patrol_sessions.company_id', $user->company_id)
            ->whereIn('session', ['Foot', 'Vehicle']);

        $totalSessions = (clone $base)->count();
        $completed = (clone $base)->whereNotNull('ended_at')->count();
        $ongoing = (clone $base)->whereNull('ended_at')->count();

        $totalDistance = round(
            (clone $base)->whereNotNull('ended_at')->sum('distance') / 1000,
            2
        );

        $guardStats = (clone $base)
            ->join('users', 'users.id', '=', 'patrol_sessions.user_id')
            ->groupBy('users.id', 'users.name')
            ->selectRaw('
                users.name as guard,
                COUNT(*) as total_sessions,
                SUM(patrol_sessions.ended_at IS NOT NULL) as completed,
                SUM(patrol_sessions.ended_at IS NULL) as ongoing,
                ROUND(SUM(distance)/1000,2) as total_distance,
                ROUND(
                    (SUM(distance)/1000) /
                    NULLIF(SUM(TIMESTAMPDIFF(MINUTE, started_at, ended_at))/60,0),
                2) as km_per_hour
            ')
            ->orderByDesc('total_distance')
            ->paginate(15);

        return view('forest.reports.foot-report', compact(
            'totalSessions',
            'completed',
            'ongoing',
            'totalDistance',
            'guardStats'
        ));
    }

    /* ================= NIGHT REPORT ================= */
    public function nightReport(Request $request)
    {
        $user = session('user');
        $base = DB::table('patrol_sessions')->where('patrol_sessions.company_id', $user->company_id)
            ->whereIn('session', ['Foot', 'Vehicle'])
            ->where(function ($q) {
                $q->whereTime('started_at', '>=', '18:00:00')
                    ->orWhereTime('started_at', '<', '06:00:00');
            });

        $totalSessions = (clone $base)->count();
        $completed = (clone $base)->whereNotNull('ended_at')->count();
        $ongoing = (clone $base)->whereNull('ended_at')->count();

        $totalDistance = round(
            (clone $base)->whereNotNull('ended_at')->sum('distance') / 1000,
            2
        );

        $topGuards = (clone $base)
            ->join('users', 'users.id', '=', 'patrol_sessions.user_id')
            ->whereNotNull('ended_at')
            ->groupBy('users.id', 'users.name')
            ->selectRaw('
                users.name as guard,
                ROUND(SUM(distance)/1000,2) as distance
            ')
            ->orderByDesc('distance')
            ->limit(5)
            ->get();

        return view('forest.reports.night-report', compact(
            'totalSessions',
            'completed',
            'ongoing',
            'totalDistance',
            'topGuards'
        ));
    }


    /* ================= CAMERA TRACKING ================= */
    public function cameraTracking(Request $request)
    {
        $user = session('user');
        $base = DB::table('patrol_sessions')->where('patrol_sessions.company_id', $user->company_id)
            ->join('users', 'users.id', '=', 'patrol_sessions.user_id');

        $stats = [
            'total_guards' => DB::table('users')->count(),
            'active_patrols' => (clone $base)->whereNull('ended_at')->count(),
            'completed_patrols' => (clone $base)->whereNotNull('ended_at')->count(),
            'total_distance_km' => round(
                (clone $base)->whereNotNull('ended_at')->sum('distance') / 1000,
                2
            )
        ];

        $guards = (clone $base)
            ->groupBy('users.id', 'users.name', 'users.role_id')
            ->select(
                'users.name',
                DB::raw("
                CASE 
                    WHEN users.role_id = 2 THEN 'Circle Incharge'
                    ELSE 'Forest Guard'
                END as designation
            ")
            )
            ->orderBy('users.name')
            ->get();

        return view('forest.reports.camera-tracking', compact('stats', 'guards'));
    }

}

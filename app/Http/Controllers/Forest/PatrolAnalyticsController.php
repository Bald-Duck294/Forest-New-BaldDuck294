<?php

namespace App\Http\Controllers\Forest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PatrolAnalyticsController extends Controller
{
    public function patrolAnalytics(Request $request)
    {
        $user = session('user');
        $from = $request->start_date;
        $to = $request->end_date;

        /* ===============================
           BASE QUERY
        ================================ */
        $base = DB::table('patrol_sessions')
            ->where('patrol_sessions.company_id', $user->company_id)
            ->join('users', 'patrol_sessions.user_id', '=', 'users.id')
            ->where('patrol_sessions.session', 'Foot');

        if ($from && $to) {
            $base->whereBetween('patrol_sessions.started_at', [$from, $to]);
        }

        /* ===============================
           PER GUARD STATS
        ================================ */
        $guards = (clone $base)
            ->selectRaw('
                users.name as guard,
                COUNT(patrol_sessions.id) as total_sessions,
                SUM(CASE WHEN patrol_sessions.ended_at IS NOT NULL THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN patrol_sessions.ended_at IS NULL THEN 1 ELSE 0 END) as ongoing,
                ROUND(SUM(COALESCE(patrol_sessions.distance,0)),2) as total_distance,
                ROUND(AVG(COALESCE(patrol_sessions.distance,0)),2) as avg_distance
            ')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_distance')
            ->get();

        /* ===============================
           STATUS COUNTS (PIE)
        ================================ */
        $status = (clone $base)->selectRaw('
            SUM(CASE WHEN ended_at IS NOT NULL THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN ended_at IS NULL THEN 1 ELSE 0 END) as ongoing,
            SUM(CASE WHEN ended_at IS NULL OR distance IS NULL OR distance = 0 THEN 1 ELSE 0 END) as incomplete
        ')->first();

        return view('patrol.analytics', compact('guards', 'status'));
    }
}

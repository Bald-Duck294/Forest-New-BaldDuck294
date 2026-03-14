<?php

namespace App\Http\Controllers\Forest;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Helpers\FormatHelper;

class GuardDetailController extends Controller
{
    public function getGuardDetails($guardId)
    {
        try {

            /* ================= BASIC GUARD ================= */
            $guard = DB::table('users')
                ->where('id', $guardId)
                ->where('isActive', 1)
                ->first();

            if (!$guard) {
                return response()->json(['success' => false], 404);
            }

            /* ================= DATE RANGE (LAST MONTH) ================= */
            $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth()->toDateString();
            $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth()->toDateString();

            /* ================= ATTENDANCE (LAST MONTH – CANONICAL) ================= */

            $attendanceBase = DB::table('attendance')
                ->where('user_id', $guardId)
                ->whereBetween('dateFormat', [$startOfLastMonth, $endOfLastMonth]);

            $presentDays = (clone $attendanceBase)
                ->select('dateFormat')
                ->distinct()
                ->count('dateFormat');
            $totalDays = $presentDays;

            $daysInMonth = Carbon::parse($startOfLastMonth)->daysInMonth;

            $absentDays = max($daysInMonth - $presentDays, 0);

            $lateDays = (clone $attendanceBase)
                ->whereNotNull('lateTime')
                ->whereRaw('CAST(lateTime AS UNSIGNED) > 0')
                ->distinct('dateFormat')
                ->count('dateFormat');

            $attendanceRate = $daysInMonth > 0
                ? round(($presentDays / $daysInMonth) * 100, 1)
                : 0;

            /* ================= PATROL STATS ================= */
            $patrolBase = DB::table('patrol_sessions')
                ->where('user_id', $guardId);

            $totalSessions = (clone $patrolBase)->count();
            $completedSessions = (clone $patrolBase)->whereNotNull('ended_at')->count();
            $ongoingSessions = $totalSessions - $completedSessions;

            $totalDistanceKm = round(
                (clone $patrolBase)->whereNotNull('ended_at')->sum('distance') / 1000,
                2
            );

            $avgDistanceKm = $completedSessions > 0
                ? round(
                    (clone $patrolBase)->whereNotNull('ended_at')->avg('distance') / 1000,
                    2
                )
                : 0;

            /* ================= INCIDENTS ================= */
            $incidents = DB::table('incidence_details')
                ->where('guard_id', $guardId)
                ->orderByDesc('dateFormat')
                ->limit(10)
                ->get()
                ->map(function ($i) {
                    return [
                        'type' => $i->type,
                        'priority' => $i->priority,
                        'status' => $i->status,
                        'site_name' => $i->site_name,
                        'remark' => $i->remark,
                        'date' => $i->date,
                        'time' => $i->time,
                    ];
                });

            $totalIncidents = DB::table('incidence_details')
                ->where('guard_id', $guardId)
                ->count();

            /* ================= PATROL PATHS ================= */

            $patrolSessions = DB::table('patrol_sessions')
                ->where('user_id', $guardId)
                ->orderByDesc('started_at')
                ->limit(10)
                ->get();

            $patrolPaths = $patrolSessions->map(function ($p) {

                $path = null;

                /* ================= 1️⃣ USE path_geojson IF PRESENT ================= */
                if (!empty($p->path_geojson)) {
                    $path = $p->path_geojson;
                }

                /* ================= 2️⃣ BUILD FROM patrol_logs ================= */ else {
                    $logs = DB::table('patrol_logs')
                        ->where('patrol_session_id', $p->id)
                        ->whereNotNull('lat')
                        ->whereNotNull('lng')
                        ->orderBy('created_at')
                        ->get(['lat', 'lng']);

                    if ($logs->count() >= 2) {
                        $path = json_encode([
                            'type' => 'LineString',
                            'coordinates' => $logs->map(fn($l) => [
                                (float) $l->lng,
                                (float) $l->lat
                            ])->toArray()
                        ]);
                    }
                }

                /* ================= 3️⃣ FALLBACK: START → END ================= */
                if (!$path && $p->start_lat && $p->start_lng && $p->end_lat && $p->end_lng) {
                    $path = json_encode([
                        'type' => 'LineString',
                        'coordinates' => [
                            [(float) $p->start_lng, (float) $p->start_lat],
                            [(float) $p->end_lng, (float) $p->end_lat],
                        ]
                    ]);
                }

                /* ================= DROP IF STILL NO PATH ================= */
                if (!$path)
                    return null;

                return [
                    'id' => $p->id,
                    'path_geojson' => $path,
                    'started_at' => $p->started_at
                        ? Carbon::parse($p->started_at)->toDateTimeString()
                        : null,
                    'ended_at' => $p->ended_at
                        ? Carbon::parse($p->ended_at)->toDateTimeString()
                        : null,
                    'start_lat' => $p->start_lat,
                    'start_lng' => $p->start_lng,
                    'end_lat' => $p->end_lat,
                    'end_lng' => $p->end_lng,
                    'distance' => (float) ($p->distance ?? 0),
                    'session' => $p->session,
                    'type' => $p->type,
                ];
            })
                ->filter()   // remove nulls
                ->values();


            /* ================= RESPONSE ================= */
            return response()->json([
                'success' => true,
                'guard' => [
                    'id' => $guard->id,
                    'name' => FormatHelper::formatName($guard->name),
                    'gen_id' => $guard->gen_id,
                    'designation' => $guard->designation,
                    'contact' => $guard->contact,
                    'email' => $guard->email,
                    'company_name' => $guard->company_name,

                    'attendance_stats' => [
                        'month' => Carbon::now()->subMonth()->format('F Y'),
                        'total_days' => $totalDays,
                        'present_days' => $presentDays,
                        'absent_days' => $absentDays,
                        'late_days' => $lateDays,
                        'attendance_rate' => $attendanceRate,
                    ],

                    'patrol_stats' => [
                        'total_sessions' => $totalSessions,
                        'completed_sessions' => $completedSessions,
                        'ongoing_sessions' => $ongoingSessions,
                        'total_distance_km' => $totalDistanceKm,
                        'avg_distance_km' => $avgDistanceKm,
                    ],

                    'incident_stats' => [
                        'total_incidents' => $totalIncidents,
                        'latest' => $incidents,
                    ],

                    'patrol_paths' => $patrolPaths,
                ]
            ]);

        } catch (\Throwable $e) {
            Log::error('Guard Detail Error', ['exception' => $e]);
            return response()->json(['success' => false], 500);
        }
    }
}

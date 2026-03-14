<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Helpers\FormatHelper;
use App\Http\Controllers\Traits\FilterDataTrait;

class GuardDetailController extends Controller
{
    use FilterDataTrait;

    public function getGuardDetails($guardId, Request $request)
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

            /* ================= ASSIGNMENT (RANGE / SITE / COMPARTMENT) ================= */
            $assignment = DB::table('site_assign')
                ->where('user_id', $guardId)
                ->first();

            $rangeName = $assignment->client_name ?? null;
            $siteName = $assignment->site_name ?? null; // Beat
            $compartmentName = null;

            if (!empty($assignment->site_id)) {
                $compartment = DB::table('site_geofences')
                    ->where('site_id', $assignment->site_id)
                    ->orderBy('id')
                    ->first();
                $compartmentName = $compartment->name ?? null;
            }

            /* ================= DATE RANGE ================= */
            // Use request filters if provided, otherwise default to last 30 days
            // This ensures data is shown even when no global filters are applied
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $startDate = $request->start_date;
                $endDate = $request->end_date;
            } elseif ($request->filled('start_date')) {
                // Only start date provided - use today as end date
                $startDate = $request->start_date;
                $endDate = Carbon::now()->toDateString();
            } elseif ($request->filled('end_date')) {
                // Only end date provided - use 30 days ago as start date
                $startDate = Carbon::parse($request->end_date)->subDays(30)->toDateString();
                $endDate = $request->end_date;
            } else {
                // No dates provided - use last 30 days (default behavior to match global filters)
                $startDate = Carbon::now()->subDays(30)->toDateString();
                $endDate = Carbon::now()->toDateString();
            }

            $companyId = session('user')->company_id ?? 56;

            /* ================= ATTENDANCE (FILTERED) ================= */

            $attendanceBase = DB::table('attendance')
                ->where('user_id', $guardId)
                ->whereBetween('dateFormat', [$startDate, $endDate]);

            $presentDays = (clone $attendanceBase)
                ->select('dateFormat')
                ->distinct()
                ->count('dateFormat');
            $totalDays = $presentDays;

            // Calculate total days in the selected date range
            $daysInRange = (int)(Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1);

            $absentDays = max($daysInRange - $presentDays, 0);

            $lateDays = (clone $attendanceBase)
                ->whereNotNull('lateTime')
                ->whereRaw('CAST(lateTime AS UNSIGNED) > 0')
                ->distinct('dateFormat')
                ->count('dateFormat');

            $attendanceRate = $daysInRange > 0
                ? round(($presentDays / $daysInRange) * 100, 1)
                : 0;

            /* ================= PATROL STATS ================= */
            $patrolBase = DB::table('patrol_sessions')
                ->where('user_id', $guardId);

            // Apply date filter first
            $patrolBase->whereBetween('started_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);

            // Apply other global filters (range, beat, user) but skip date filter
            $this->applyCanonicalFilters(
                $patrolBase,
                'patrol_sessions.started_at',
                'patrol_sessions.site_id',
                'patrol_sessions.user_id',
                true // Skip date filter since we already applied it
            );

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
            $incidentsBase = DB::table('incidence_details');

            // Apply canonical filters (Site, User, Role-based)
            $this->applyCanonicalFilters(
                $incidentsBase,
                'incidence_details.dateFormat',
                'incidence_details.site_id',
                'incidence_details.guard_id',
                false, // date filter
                true   // strict mode
            );

            $incidentsBase->where('incidence_details.company_id', $companyId)
                ->where('incidence_details.guard_id', $guardId)
                ->whereNotNull('incidence_details.type')
                ->whereNotIn('incidence_details.type', ['Other', 'other', '']);

            $totalIncidents = (clone $incidentsBase)->count();
            
            // Fallback for incidents if zero but we might have some in patrol_logs (to handle legacy data)
            if ($totalIncidents === 0) {
                 $logQuery = DB::table('patrol_logs')
                    ->join('patrol_sessions', 'patrol_sessions.id', '=', 'patrol_logs.patrol_session_id')
                    ->where('patrol_sessions.user_id', $guardId)
                    ->where('patrol_sessions.company_id', $companyId)
                    ->whereIn('patrol_logs.type', [
                        'animal_sighting',
                        'water_source',
                        'human_impact',
                        'animal_mortality'
                    ])
                    ->whereBetween('patrol_logs.created_at', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay()
                    ]);
                $siteIds = $this->resolveSiteIds();
                if (!empty($siteIds)) {
                    $logQuery->whereIn('patrol_sessions.site_id', $siteIds);
                } else {
                    $logQuery->whereRaw('1 = 0');
                }
                
                $totalIncidents = (clone $logQuery)->count();
                
                $incidents = $logQuery->orderByDesc('patrol_logs.created_at')
                    ->limit(10)
                    ->select([
                        'patrol_logs.id',
                        'patrol_logs.type',
                        'patrol_logs.created_at as date',
                        'patrol_sessions.site_id',
                        'patrol_logs.notes as remark'
                    ])
                    ->get()
                    ->map(function($i) {
                         $site = DB::table('site_details')->where('id', $i->site_id)->first();
                         return [
                            'id' => $i->id,
                            'type' => ucwords(str_replace('_', ' ', $i->type)),
                            'priority' => 'Normal',
                            'status' => 'Logged',
                            'site_name' => $site->name ?? 'NA',
                            'remark' => $i->remark,
                            'date' => Carbon::parse($i->date)->format('Y-m-d'),
                            'time' => Carbon::parse($i->date)->format('H:i:s'),
                         ];
                    });
            } else {
                $incidents = (clone $incidentsBase)
                    ->select('incidence_details.*')
                    ->orderByDesc('dateFormat')
                    ->limit(10)
                    ->get()
                    ->map(function ($i) {
                        return [
                            'id' => $i->id,
                            'type' => $i->type,
                            'priority' => $i->priority,
                            'status' => $i->status,
                            'site_name' => $i->site_name,
                            'remark' => $i->remark,
                            'date' => $i->date,
                            'time' => $i->time,
                        ];
                    });
            }

            /* ================= PATROL PATHS ================= */

            $patrolSessionsBase = DB::table('patrol_sessions')
                ->where('user_id', $guardId);

            // Apply date filter first
            $patrolSessionsBase->whereBetween('started_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);

            // Apply other global filters (range, beat, user) but skip date filter
            $this->applyCanonicalFilters(
                $patrolSessionsBase,
                'patrol_sessions.started_at',
                'patrol_sessions.site_id',
                'patrol_sessions.user_id',
                true // Skip date filter since we already applied it
            );

            // Get ALL patrol sessions (no limit) - filtered by global filters
            $patrolSessions = $patrolSessionsBase
                ->orderByDesc('started_at')
                ->get(); // Removed limit to show all patrol paths

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
                    'range' => $rangeName,
                    'site' => $siteName,
                    'compartment' => $compartmentName,

                    'attendance_stats' => [
                        'month' => Carbon::parse($startDate)->format('M d') . ' - ' . Carbon::parse($endDate)->format('M d, Y'),
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

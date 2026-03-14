{{-- Key Performance Indicators --}}
@include('analytics.partials.kpi-cards', ['kpis' => $kpis, 'coverageAnalysis' => $coverageAnalysis])

{{-- Guard Performance Rankings --}}
<div class="mb-2">
    <small class="text-uppercase fw-bold text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">Performance
        Metrics</small>
</div>
@include('analytics.partials.guard-performance', ['guardPerformance' => $guardPerformance])


{{-- Patrol Analytics --}}
<div class="mt-4 mb-2">
    <small class="text-uppercase fw-bold text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">Patrol
        Operations</small>
</div>
@include('analytics.partials.patrol-analytics', ['patrolAnalytics' => $patrolAnalytics])


{{-- Incident Tracking --}}
<div class="mt-4 mb-2">
    <small class="text-uppercase fw-bold text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">Safety &
        Incidents</small>
</div>
@include('analytics.partials.incident-tracking', ['incidentTracking' => $incidentTracking])

<script>
    // Embed data for charts to pick up
    window.incidentTrackingData = {
        statusLabels: {!! json_encode($incidentTracking['statusDistribution']->keys()->map(fn($f) => [
            0 => 'Pending Supervisor',
            1 => 'Resolved',
            3 => 'Escalated to Admin',
            4 => 'Pending Admin',
            5 => 'Critical Pending',
            6 => 'Forwarded'
        ][$f] ?? 'Unknown')) !!},
        statusData: {!! json_encode($incidentTracking['statusDistribution']->values()) !!},
        priorityLabels: {!! json_encode($incidentTracking['priorityDistribution']->keys()) !!},
        priorityData: {!! json_encode($incidentTracking['priorityDistribution']->values()) !!},
        typeLabels: {!! json_encode($incidentTracking['incidentTypes']->pluck('type')) !!},
        typeData: {!! json_encode($incidentTracking['incidentTypes']->pluck('count')) !!}
    };

    window.patrolAnalyticsData = {
        typeLabels: {!! json_encode($patrolAnalytics['patrolByType']->pluck('type')) !!},
        typeCounts: {!! json_encode($patrolAnalytics['patrolByType']->pluck('count')) !!},
        typeDistances: {!! json_encode($patrolAnalytics['patrolByType']->pluck('total_distance_km')) !!},
        dailyLabels: {!! json_encode($patrolAnalytics['dailyTrend']->pluck('date')) !!},
        dailyCounts: {!! json_encode($patrolAnalytics['dailyTrend']->pluck('patrol_count')) !!},
        dailyDistances: {!! json_encode($patrolAnalytics['dailyTrend']->pluck('distance_km')) !!}
    };

    window.attendanceData = {
        dailyLabels: {!! json_encode($attendanceAnalytics['dailyTrend']->pluck('date')) !!},
        presentData: {!! json_encode($attendanceAnalytics['dailyTrend']->pluck('present')) !!},
        lateData: {!! json_encode($attendanceAnalytics['dailyTrend']->pluck('late')) !!},
        absentData: {!! json_encode($attendanceAnalytics['dailyTrend']->map(fn($item) => 0)) !!} {{-- Absent data not tracked by row yet --}}
    };

    window.timePatternsData = {
        hourlyLabels: {!! json_encode($timePatterns['hourlyDistribution']->pluck('hour')->map(fn($h) => sprintf("%02d:00", $h))) !!},
        hourlyData: {!! json_encode($timePatterns['hourlyDistribution']->pluck('count')) !!}
    };

    // Re-initialize charts if they already exist
    if (typeof window.initializeCharts === 'function') {
        window.initializeCharts();
    }
</script>

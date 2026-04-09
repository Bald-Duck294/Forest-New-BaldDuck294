@php
$hideGlobalFilters = true;
$hideBackground = true;
// $user = session('user');
@endphp
@extends('layouts.app')

@section('title', get_label('label_advanced_analytics', 'Advanced Analytics'))

@section('content')

<style>
    /* =========================================
                                                       EXTENDED SAPPHIRE COLOR PALETTE
                                                    ========================================= */
    :root {
        /* Extended Chart Colors (Light Mode Defaults) */
        --chart-blue: #3B82F6;
        --chart-green: #10B981;
        --chart-yellow: #EAB308;
        --chart-orange: #F97316;
        --chart-red: #EF4444;
        --chart-purple: #8B5CF6;
        --chart-cyan: #06B6D4;
        --chart-lime: #84CC16;
    }

    [data-bs-theme="dark"] {
        /* Extended Chart Colors (Neon Dark Mode Pops) */
        --chart-blue: #60A5FA;
        --chart-green: #34D399;
        --chart-yellow: #FDE047;
        --chart-orange: #FB923C;
        --chart-red: #F87171;
        --chart-purple: #A78BFA;
        --chart-cyan: #22D3EE;
        --chart-lime: #BEF264;
    }

    /* =========================================
                                                       LOCAL COMPONENT STYLES
                                                    ========================================= */
    .dash-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }

    .hover-lift:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.06);
        border-color: var(--sapphire-primary);
    }

    /* Custom Form Inputs */
    .custom-input {
        background-color: var(--bg-body);
        color: var(--text-main);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 0.9rem;
        width: 100%;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .custom-input:focus {
        border-color: var(--sapphire-primary);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    html[data-bs-theme="dark"] .custom-input {
        color-scheme: dark;
    }

    /* Action Buttons */
    .btn-sapphire {
        background-color: var(--sapphire-primary);
        color: #ffffff;
        border: none;
        font-weight: 600;
        padding: 8px 20px;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .btn-sapphire:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        color: #ffffff;
    }

    /* Soft Badges */
    .badge-soft {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-soft-primary {
        background: rgba(59, 130, 246, 0.15);
        color: var(--sapphire-primary);
    }

    .badge-soft-success {
        background: rgba(16, 185, 129, 0.15);
        color: var(--sapphire-success);
    }

    .badge-soft-warning {
        background: rgba(245, 158, 11, 0.15);
        color: var(--sapphire-warning);
    }

    .badge-soft-danger {
        background: rgba(239, 68, 68, 0.15);
        color: var(--sapphire-danger);
    }

    .badge-soft-info {
        background: rgba(6, 182, 212, 0.15);
        color: var(--chart-cyan);
    }

    .badge-soft-purple {
        background: rgba(139, 92, 246, 0.15);
        color: var(--chart-purple);
    }

    /* Tables */
    .dash-table th {
        color: var(--text-muted);
        font-weight: 600;
        font-size: 0.85rem;
        border-bottom: 1px solid var(--border-color);
        padding: 1rem;
        background-color: transparent !important;
    }

    .dash-table td {
        color: var(--text-main);
        font-weight: 500;
        font-size: 0.9rem;
        border-bottom: 1px dashed var(--border-color);
        padding: 1rem;
        vertical-align: middle;
        background-color: transparent !important;
    }

    .dash-table tr:hover td {
        background-color: var(--table-hover) !important;
    }

    /* KPI Boxes */
    .kpi-box {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        padding: 1.5rem;
        border-radius: 12px;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        height: 100%;
    }

    .kpi-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
    }

    .kpi-info h6 {
        font-size: 0.85rem;
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .kpi-info h3 {
        font-size: 1.8rem;
        color: var(--text-main);
        margin: 0;
        font-weight: 700;
    }

    /* Map & Containers */
    #mapCanvas {
        height: 420px;
        width: 100%;
        border-radius: 8px;
        border: 1px solid var(--border-color);
    }

    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }

    .chart-container-tall {
        position: relative;
        height: 400px;
        width: 100%;
    }

    /* Range Slider */
    input[type=range] {
        accent-color: var(--sapphire-primary);
    }

    /* AI Insights Block */
    .ai-insight-block {
        padding: 12px 16px;
        border-left: 4px solid var(--sapphire-warning);
        background: rgba(245, 158, 11, 0.05);
        border-radius: 0 8px 8px 0;
        margin-bottom: 12px;
        color: var(--text-main);
        font-size: 0.9rem;
    }
</style>

<div class="container-fluid py-4">

    {{-- HEADER & FILTERS --}}
    <div class="dash-card p-4 mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--text-main);">
                    {{ get_label('label_advanced_analytics', 'Advanced Analytics') }}
                </h3>
                <p class="mb-0" style="color: var(--text-muted); font-size: 0.9rem;">
                    Deep dive into patrol performance, AI insights, and spatial tracking.
                </p>
            </div>
        </div>

        <form id="filtersForm" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-semibold" style="color: var(--text-muted);">From</label>
                <input type="date" name="date_from" class="custom-input" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold" style="color: var(--text-muted);">To</label>
                <input type="date" name="date_to" class="custom-input" value="{{ $dateTo }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold" style="color: var(--text-muted);">
                    {{ get_label('label_user', 'User') }}
                </label>
                <select name="user_id" class="custom-input">
                    <option value="">All {{ Str::plural(get_label('label_user', 'User')) }}</option>
                    @foreach ($compareUsers as $u)
                    <option value="{{ $u->id }}" {{ $filterUser == $u->id ? 'selected' : '' }}>
                        {{ $u->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold" style="color: var(--text-muted);">
                    {{ get_label('label_site', 'Site') }}
                </label>
                <select name="site_id" class="custom-input">
                    <option value="">All {{ Str::plural(get_label('label_site', 'Site')) }}</option>
                    @foreach ($sitesList as $s)
                    <option value="{{ $s->id }}" {{ $filterSite == $s->id ? 'selected' : '' }}>
                        {{ $s->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex">
                <button type="submit" class="btn-sapphire w-100"><i class="bi bi-funnel me-1"></i> Apply</button>
            </div>
        </form>
    </div>

    {{-- KPI ROW --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="kpi-box hover-lift">
                <div class="kpi-icon badge-soft-primary"><i class="bi bi-geo-alt-fill"></i></div>
                <div class="kpi-info">
                    <h6>Sessions</h6>
                    <h3>{{ count($sessions) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="kpi-box hover-lift">
                <div class="kpi-icon badge-soft-success"><i class="bi bi-list-check"></i></div>
                <div class="kpi-info">
                    <h6>Logs Submitted</h6>
                    <h3>{{ count($logs) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="kpi-box hover-lift">
                <div class="kpi-icon badge-soft-warning"><i class="bi bi-signpost-split-fill"></i></div>
                <div class="kpi-info">
                    <h6>Total Distance</h6>
                    <h3>{{ number_format(array_sum($userDistance) / 1000, 2) }} <small
                            class="fs-6 text-muted">km</small></h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="kpi-box hover-lift">
                <div class="kpi-icon badge-soft-purple"><i class="bi bi-lightning-charge-fill"></i></div>
                <div class="kpi-info">
                    <h6>Avg Productivity</h6>
                    <h3>{{ number_format(array_sum($productivity) / max(1, count($productivity)), 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- CHARTS ROW 1 --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="dash-card h-100 p-4">
                <h5 class="fw-bold mb-1" style="color: var(--text-main);">Top Guards by Distance</h5>
                <small style="color: var(--text-muted); display: block; margin-bottom: 1.5rem;">Click a bar to drill
                    down into hourly activity.</small>
                <div class="chart-container-tall">
                    <canvas id="userDistanceChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="dash-card h-100 p-4">
                <h5 class="fw-bold mb-1" style="color: var(--text-main);">Guard Productivity Ranking</h5>
                <small style="color: var(--text-muted); display: block; margin-bottom: 1.5rem;">Computed from sessions,
                    distance, and logs.</small>
                <div class="chart-container-tall">
                    <canvas id="productivityChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- CHARTS ROW 2 --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="dash-card h-100 p-4">
                <h5 class="fw-bold mb-3" style="color: var(--text-main);">Patrol Density by Hour (Logs)</h5>
                <div class="chart-container">
                    <canvas id="hourlyChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="dash-card h-100 p-4">
                <h5 class="fw-bold mb-1" style="color: var(--text-main);">Patrol Effectiveness Score</h5>
                <small style="color: var(--text-muted); display: block; margin-bottom: 1rem;">Based on coverage,
                    distance, zones, and logs (Weekly Trend).</small>
                <div class="chart-container">
                    <canvas id="weeklyEffectiveness"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- CHARTS ROW 3 --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="dash-card h-100 p-4">
                <h5 class="fw-bold mb-3" style="color: var(--text-main);">Sessions by Site</h5>
                <div class="chart-container">
                    <canvas id="sitePieChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="dash-card h-100 p-4">
                <h5 class="fw-bold mb-3" style="color: var(--text-main);">Sessions Per Day</h5>
                <div class="chart-container">
                    <canvas id="sessionsTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- MAP & AI INSIGHTS ROW --}}
    <div class="row g-4 mb-4">

        <div class="col-lg-8">
            <div class="dash-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0" style="color: var(--text-main);">Heatmap & Live Playback</h5>
                    <div class="d-flex gap-2">
                        <button id="playAll" class="btn btn-sm"
                            style="background: var(--sapphire-success); color: white;"><i class="bi bi-play-fill"></i>
                            Play All</button>
                        <button id="stopPlay" class="btn btn-sm"
                            style="background: var(--sapphire-warning); color: white;"><i class="bi bi-stop-fill"></i>
                            Stop</button>
                    </div>
                </div>

                <div id="mapCanvas" class="mb-3"></div>

                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="small fw-semibold" style="color: var(--text-muted);">Select Session for
                            Playback</label>
                        <select id="playbackSession" class="custom-input">
                            <option value="">-- Select Session --</option>
                            @foreach ($rawSessions as $s)
                            @if (!empty($s['path']) && count($s['path']) > 1)
                            <option value="{{ $s['id'] }}">
                                Session #{{ $s['id'] }} (User {{ $s['user_id'] }})
                            </option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-7 d-flex align-items-center gap-2">
                        <button type="button" id="playBtn" class="btn btn-success btn-sm px-3 d-flex align-items-center gap-2">
                            <i class="bi bi-play-circle-fill"></i> Play
                        </button>

                        <button type="button" id="pauseBtn" class="btn btn-outline-secondary btn-sm px-3 d-flex align-items-center gap-2">
                            <i class="bi bi-pause-circle"></i> Pause
                        </button>

                        <button type="button" id="resetBtn" class="btn btn-danger btn-sm px-3 d-flex align-items-center gap-2">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </button>
                    </div>

                    <div class="col-12 mt-3">
                        <label for="playbackSlider" class="form-label small text-muted mb-1">Session Progress</label>
                        <input type="range" class="form-range" id="playbackSlider" min="0" max="100" value="0">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="dash-card p-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <div class="badge-soft-purple p-2 rounded"><i class="bi bi-stars fs-5"></i></div>
                    <h5 class="fw-bold mb-0" style="color: var(--text-main);">AI Insights</h5>
                </div>
                <div id="aiInsights">
                </div>
            </div>

            <div class="dash-card p-4">
                <h5 class="fw-bold mb-3" style="color: var(--text-main);">AI Guard Ranking</h5>
                <div class="chart-container" style="height: 250px;">
                    <canvas id="aiRanking"></canvas>
                </div>
            </div>
        </div>

    </div>

    {{-- COVERAGE & ANOMALIES --}}
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="dash-card p-0 overflow-hidden h-100">
                <div class="p-3"
                    style="border-bottom: 1px solid var(--border-color); background: rgba(239, 68, 68, 0.05);">
                    <h5 class="fw-bold mb-0" style="color: var(--sapphire-danger);"><i
                            class="bi bi-exclamation-triangle-fill me-2"></i> AI Anomaly Detection</h5>
                </div>
                <div class="p-3" id="anomalyTable" style="max-height: 400px; overflow-y: auto;"></div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="dash-card p-0 overflow-hidden h-100">

                <!-- Header -->
                <div class="p-3 d-flex justify-content-between align-items-center"
                    style="border-bottom: 1px solid var(--border-color); background: rgba(16, 185, 129, 0.05);">

                    <h5 class="fw-bold mb-0" style="color: var(--sapphire-success);">
                        <i class="bi bi-bullseye me-2"></i> Patrol Coverage Scoring
                    </h5>

                    <small class="text-muted">Real-time coverage analysis</small>
                </div>

                <!-- Table -->
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table dash-table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th class="ps-4">Session</th>
                                <th>Site</th>
                                <th>Coverage</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="coverageTable">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="bi bi-bar-chart-line me-2"></i>
                                    No coverage data available
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

</div>

{{-- Modal for Drilldown --}}
<div class="modal fade" id="userBreakdownModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: var(--bg-card); border: 1px solid var(--border-color);">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold" style="color: var(--text-main);">Site User Breakdown</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                    style="filter: var(--bs-theme) == 'dark' ? 'invert(1)' : 'none';"></button>
            </div>
            <div class="modal-body">
                <div id="userBreakdownTable" class="table-responsive mb-4"></div>
                <div class="chart-container" style="height: 300px;">
                    <canvas id="userBreakdownChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

<script>
    // Server-provided data
    const userDistance = @json($userDistance);
    const userSessions = @json($userSessions);
    const userLogs = @json($userLogs);
    const userLabels = @json($userLabels);
    const siteSessions = @json($siteSessions);
    const siteLabels = @json($siteLabels);
    const sessionsPerDay = @json(array_values($sessionsPerDay));
    const sessionsPerDayLabels = @json(array_keys($sessionsPerDay));
    const hourly = @json($hourly);
    const heatmapPoints = @json($heatmapPoints);
    const productivity = @json($productivity);
    const productivityLabels = @json(array_map(fn($id) => $userLabels[$id] ?? $id, array_keys($productivity)));
    const geofences = @json($geofences);
    const userMap = @json($users);
    const weeklyStats = @json($weeklyStats);
    const logs = @json($logs);
    const rawSessions = @json($rawSessions);

    // Global Chart Instances
    let charts = {};

    function idsToNames(idsArray, labelMap) {
        return idsArray.map(id => labelMap[id] ?? id);
    }

    function updateWeeklyChart() {
        fetch('/patrol-analysis/analytics/live')
            .then(res => res.json())
            .then(data => {

                if (!charts.week) return;

                const labels = data.map(w => "Week " + w.week_number);
                const scores = data.map(w => w.score);

                charts.week.data.labels = labels;
                charts.week.data.datasets[0].data = scores;
                charts.week.update();
            })
            .catch(err => console.error("Live chart error:", err));
    }

    document.addEventListener("DOMContentLoaded", function() {
        renderAllCharts();

        // 🔥 ADD THIS
        updateWeeklyChart(); // initial fetch
        // setInterval(updateWeeklyChart, 5000); // refresh every 5 sec

        // Re-render when theme changes
        window.addEventListener('themeChanged', () => {
            setTimeout(renderAllCharts, 50);
            updateMapStyle();
        });

        // Filter Submit
        document.getElementById('filtersForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const q = new URLSearchParams(new FormData(this)).toString();
            window.location.search = q;
        });

        generateAIInsights();
        computeGuardScoring();
        detectAnomalies();
    })

    function getThemeColors() {
        const root = getComputedStyle(document.documentElement);
        return {
            textMain: root.getPropertyValue('--text-main').trim() || '#1e293b',
            textMuted: root.getPropertyValue('--text-muted').trim() || '#64748b',
            gridColor: root.getPropertyValue('--chart-grid').trim() || 'rgba(0,0,0,0.05)',
            cardBg: root.getPropertyValue('--bg-card').trim() || '#fff',
            cBlue: root.getPropertyValue('--chart-blue').trim() || '#3b82f6',
            cGreen: root.getPropertyValue('--chart-green').trim() || '#10b981',
            cYellow: root.getPropertyValue('--chart-yellow').trim() || '#eab308',
            cOrange: root.getPropertyValue('--chart-orange').trim() || '#f97316',
            cRed: root.getPropertyValue('--chart-red').trim() || '#ef4444',
            cPurple: root.getPropertyValue('--chart-purple').trim() || '#8b5cf6',
            cCyan: root.getPropertyValue('--chart-cyan').trim() || '#06b6d4',
            cLime: root.getPropertyValue('--chart-lime').trim() || '#84cc16',
        };
    }

    function renderAllCharts() {
        const t = getThemeColors();
        Chart.defaults.color = t.textMuted;
        Chart.defaults.font.family = "'Inter', sans-serif";

        // Destroy existing
        Object.values(charts).forEach(c => c.destroy());

        /* 1. User Distance (Horizontal Bar) */
        const sortedUsers = Object.keys(userDistance).sort((a, b) => userDistance[b] - userDistance[a]).slice(0, 10);
        const distData = sortedUsers.map(id => +((userDistance[id] || 0) / 1000).toFixed(2));
        const distNames = idsToNames(sortedUsers, userLabels);

        const ctxDist = document.getElementById('userDistanceChart');
        if (ctxDist) {
            charts.dist = new Chart(ctxDist, {
                type: 'bar',
                data: {
                    labels: distNames,
                    datasets: [{
                        label: 'Distance (km)',
                        data: distData,
                        backgroundColor: t.cBlue,
                        borderRadius: 6,
                        barPercentage: 0.6
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: t.gridColor
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    onClick: (e, elements) => {
                        if (elements.length > 0) {
                            const uid = sortedUsers[elements[0].index];
                            fetch(
                                    `{{ url('/patrol-analysis/analytics/user') }}/${uid}/drilldown?date_from={{ $dateFrom }}&date_to={{ $dateTo }}`
                                )
                                .then(r => r.json()).then(json => {
                                    charts.hourly.data.datasets[0].data = json.hourly;
                                    charts.hourly.update();
                                    if (typeof highlightUserSessionsOnMap === 'function')
                                        highlightUserSessionsOnMap(uid, json.sessions);
                                });
                        }
                    }
                }
            });
        }

        /* 2. Productivity (Horizontal Bar) */
        const prodIds = Object.keys(productivity);
        const prodScores = prodIds.map(id => productivity[id]);
        const prodNames = prodIds.map(id => userLabels[id] ?? ("User " + id));
        const prodColors = prodScores.map(s => s > 7 ? t.cGreen : (s > 4 ? t.cYellow : t.cRed));

        const ctxProd = document.getElementById('productivityChart');
        if (ctxProd) {
            charts.prod = new Chart(ctxProd, {
                type: 'bar',
                data: {
                    labels: prodNames,
                    datasets: [{
                        label: 'Score',
                        data: prodScores,
                        backgroundColor: prodColors,
                        borderRadius: 6,
                        barPercentage: 0.6
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: t.gridColor
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        /* 3. Hourly Density (Filled Area) */
        const ctxHourly = document.getElementById('hourlyChart');
        if (ctxHourly) {
            const grad = ctxHourly.getContext('2d').createLinearGradient(0, 0, 0, 300);
            grad.addColorStop(0, t.cPurple + '80'); // 50% opacity
            grad.addColorStop(1, t.cPurple + '00');

            charts.hourly = new Chart(ctxHourly, {
                type: 'line',
                data: {
                    labels: [...Array(24).keys()].map(h => h + ':00'),
                    datasets: [{
                        label: 'Logs',
                        data: hourly,
                        borderColor: t.cPurple,
                        backgroundColor: grad,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            grid: {
                                color: t.gridColor
                            }
                        }
                    }
                }
            });
        }

        /* 4. Effectiveness Trend (Line) */
        const ctxWeek = document.getElementById('weeklyEffectiveness');

        if (ctxWeek) {
            charts.week = new Chart(ctxWeek, {
                type: 'line',
                data: {
                    labels: [], // 🔥 empty initially
                    datasets: [{
                        label: 'Score',
                        data: [], // 🔥 empty initially
                        borderColor: t.cGreen,
                        borderWidth: 3,
                        tension: 0.4,
                        pointBackgroundColor: t.cGreen
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            grid: {
                                color: t.gridColor
                            }
                        }
                    }
                }
            });
        }

        /* 5. Site Sessions (Doughnut) */
        const siteIds = Object.keys(siteSessions);
        const siteVals = siteIds.map(sid => siteSessions[sid]);
        const siteNames = siteIds.map(sid => siteLabels[sid] ?? sid);
        const palette = [t.cBlue, t.cOrange, t.cCyan, t.cLime, t.cPurple, t.cRed];

        const ctxSite = document.getElementById('sitePieChart');
        if (ctxSite) {
            charts.site = new Chart(ctxSite, {
                type: 'doughnut',
                data: {
                    labels: siteNames,
                    datasets: [{
                        data: siteVals,
                        backgroundColor: palette.slice(0, siteVals.length),
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: t.textMain,
                                usePointStyle: true
                            }
                        }
                    },
                    onClick: (e, elements) => {
                        if (elements.length > 0) {
                            const sid = siteIds[elements[0].index];
                            fetch(
                                    `{{ url('/patrol-analysis/analytics/site') }}/${sid}/drilldown?date_from={{ $dateFrom }}&date_to={{ $dateTo }}`
                                )
                                .then(r => r.json()).then(json => {
                                    charts.hourly.data.datasets[0].data = json.hourly;
                                    charts.hourly.update();
                                    showSiteUsersBreakdown(json.userCounts, t);
                                });
                        }
                    }
                }
            });
        }

        /* 6. Sessions Trend (Line) */
        const ctxTrend = document.getElementById('sessionsTrendChart');
        if (ctxTrend) {
            charts.trend = new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: sessionsPerDayLabels,
                    datasets: [{
                        label: 'Sessions',
                        data: sessionsPerDay,
                        borderColor: t.cCyan,
                        backgroundColor: t.cCyan + '20',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            grid: {
                                color: t.gridColor
                            }
                        }
                    }
                }
            });
        }

        /* 7. AI Guard Ranking (Bar) */
        // Computation handled in computeGuardScoring() below, which builds `sortedGuards`
        if (window.sortedGuards) {
            const ctxAi = document.getElementById('aiRanking');
            if (ctxAi) {
                charts.ai = new Chart(ctxAi, {
                    type: 'bar',
                    data: {
                        labels: window.sortedGuards.map(x => x.user),
                        datasets: [{
                            label: 'AI Score',
                            data: window.sortedGuards.map(x => x.score),
                            backgroundColor: t.cOrange,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                grid: {
                                    color: t.gridColor
                                }
                            }
                        }
                    }
                });
            }
        }
    }

    // --- AI Insights & Scoring Logic ---
    function generateAIInsights() {
        let insights = [];
        let entries = Object.entries(userDistance);
        if (entries.length > 0) {
            let sorted = entries.sort((a, b) => a[1] - b[1]);
            let lowestUserId = sorted[0][0];
            let lowestDistance = sorted[0][1] / 1000;
            insights.push(
                `<div class="ai-insight-block"><i class="bi bi-info-circle-fill me-2" style="color: var(--sapphire-primary);"></i> <b>Guard ${userLabels[lowestUserId]}</b> logged lowest distance (${lowestDistance.toFixed(2)} km). Review route.</div>`
            );
        }

        let siteEntries = Object.entries(siteSessions);
        if (siteEntries.length > 0) {
            let sortedSites = siteEntries.sort((a, b) => b[1] - a[1]);
            let topSiteId = sortedSites[0][0];
            insights.push(
                `<div class="ai-insight-block" style="border-left-color: var(--sapphire-success); background: rgba(16, 185, 129, 0.05);"><i class="bi bi-graph-up-arrow me-2" style="color: var(--sapphire-success);"></i> <b>${siteLabels[topSiteId]}</b> is most active with ${sortedSites[0][1]} sessions.</div>`
            );
        }

        let peak = Math.max(...hourly);
        let lastHours = hourly.slice(-3).reduce((a, b) => a + b, 0);
        if (lastHours < peak * 0.2) {
            insights.push(
                `<div class="ai-insight-block" style="border-left-color: var(--sapphire-danger); background: rgba(239, 68, 68, 0.05);"><i class="bi bi-exclamation-triangle-fill me-2" style="color: var(--sapphire-danger);"></i> Sharp drop in recent activity. Check guard engagement.</div>`
            );
        }

        document.getElementById("aiInsights").innerHTML = insights.join("") ||
            "<p class='text-muted'>No critical insights currently.</p>";
    }

    function detectAnomalies() {
        let html = "";

        rawSessions.forEach(s => {

            // 1. Low movement
            if (s.distance_m < 500) {
                html += `<div class="ai-insight-block" style="border-left-color:red;">
                ⚠️ Session #${s.id} - Very low movement (${(s.distance_m/1000).toFixed(2)} km)
            </div>`;
            }

            // 2. No logs
            const sessionLogs = logs.filter(l => l.patrol_session_id == s.id);
            if (sessionLogs.length === 0) {
                html += `<div class="ai-insight-block" style="border-left-color:orange;">
                ⚠️ Session #${s.id} - No logs submitted
            </div>`;
            }

            // 3. Short session
            if (s.path && s.path.length < 5) {
                html += `<div class="ai-insight-block" style="border-left-color:purple;">
                ⚠️ Session #${s.id} - Very short patrol path
            </div>`;
            }

        });

        document.getElementById("anomalyTable").innerHTML =
            html || `<p class="text-muted p-3">No anomalies detected</p>`;
    }

    function computeGuardScoring() {
        let guardStats = {};
        rawSessions.forEach(s => {
            const uid = s.user_id;
            if (!guardStats[uid]) guardStats[uid] = {
                dist: 0,
                logs: 0,
                coverage: []
            };
            guardStats[uid].dist += s.distance_m;
            guardStats[uid].coverage.push(s.coverage || 0);
        });

        logs.forEach(l => {
            const uid = l.patrol_session.user_id;
            if (!guardStats[uid]) return;
            guardStats[uid].logs++;
        });

        Object.keys(guardStats).forEach(uid => {
            const g = guardStats[uid];
            const normDist = Math.min(g.dist / 15000, 1) * 100;
            const cov = Array.isArray(g.coverage) && g.coverage.length ? g.coverage.reduce((a, b) => a + b, 0) /
                g.coverage.length : 0;
            const act = Math.min(g.logs / 20, 1) * 100;
            g.aiScore = (0.30 * normDist) + (0.20 * cov) + (0.20 * act);
        });

        window.sortedGuards = Object.entries(guardStats)
            .sort((a, b) => b[1].aiScore - a[1].aiScore)
            .map(([uid, stats]) => ({
                user: userMap[uid],
                score: stats.aiScore.toFixed(2)
            }));

        // Re-render to show this chart
        if (charts.ai) {
            charts.ai.data.labels = window.sortedGuards.map(x => x.user);
            charts.ai.data.datasets[0].data = window.sortedGuards.map(x => x.score);
            charts.ai.update();
        }
    }

    // --- Google Maps & Playback Logic ---
    let map, bounds;
    let geofencePolygons = [];
    let geofencesVisible = true;
    let sessionPolylines = {};
    let sessionMarkers = {};
    let sessionInfoWindows = {};

    function updateMapStyle() {
        if (!map) return;
        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        const darkStyle = [{
                elementType: "geometry",
                stylers: [{
                    color: "#1e293b"
                }]
            },
            {
                elementType: "labels.text.stroke",
                stylers: [{
                    color: "#1e293b"
                }]
            },
            {
                elementType: "labels.text.fill",
                stylers: [{
                    color: "#94a3b8"
                }]
            },
            {
                featureType: "water",
                elementType: "geometry",
                stylers: [{
                    color: "#0f172a"
                }]
            }
        ];
        map.setOptions({
            styles: isDark ? darkStyle : []
        });
    }

    window.initMap = function() {
        console.log("✅ Google Map Loaded");

        const mapEl = document.getElementById('mapCanvas');
        if (!mapEl) {
            console.error("❌ Map container not found");
            return;
        }

        if (!heatmapPoints || heatmapPoints.length === 0) {
            console.warn("⚠️ No heatmap data available");
        }

        map = new google.maps.Map(mapEl, {
            center: heatmapPoints.length ? {
                lat: heatmapPoints[0].lat,
                lng: heatmapPoints[0].lng
            } : {
                lat: 20.5937,
                lng: 78.9629
            },
            zoom: heatmapPoints.length ? 14 : 5,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });

        bounds = new google.maps.LatLngBounds();

        updateMapStyle();

        // ✅ FIX: Safe heatmap creation
        if (heatmapPoints.length) {
            const heatData = heatmapPoints.map(p => {
                if (!p.lat || !p.lng) return null;
                return new google.maps.LatLng(p.lat, p.lng);
            }).filter(Boolean);

            new google.maps.visualization.HeatmapLayer({
                data: heatData,
                map: map,
                radius: 30
            });
        }

        drawGeofences();
        drawSessionsAndCoverage();

        console.log("✅ Map fully initialized");
    };

    function drawGeofences() {
        geofences.forEach(g => {
            if (!g.coords || !g.coords.length) return;
            const path = g.coords.map(p => ({
                lat: p.lat,
                lng: p.lng
            }));
            const polygon = new google.maps.Polygon({
                paths: path,
                strokeColor: '#3B82F6',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: '#3B82F6',
                fillOpacity: 0.15,
                map: map,
                zIndex: 1
            });
            const center = getPolygonCenter(path);
            const info = new google.maps.InfoWindow({
                content: `<div style="color:#000;"><b>${g.name}</b></div>`
            });
            polygon.addListener('click', () => {
                info.setPosition(center);
                info.open(map);
            });
            path.forEach(p => bounds.extend(p));
            g._path = path;
            geofencePolygons.push(polygon);
        });
    }

    function getPolygonCenter(path) {
        let lat = 0,
            lng = 0;
        path.forEach(p => {
            lat += p.lat;
            lng += p.lng;
        });
        return {
            lat: lat / path.length,
            lng: lng / path.length
        };
    }

    function getCoverageStatus(cov) {
        if (cov >= 80) {
            return {
                label: "Excellent",
                class: "badge-soft-success"
            };
        }
        if (cov >= 50) {
            return {
                label: "Average",
                class: "badge-soft-warning"
            };
        }
        return {
            label: "Poor",
            class: "badge-soft-danger"
        };
    }

    function calculateCoverage(path, geofenceCoords) {
        try {
            if (!path || path.length < 2) return 0;
            if (!geofenceCoords || geofenceCoords.length < 3) return 0;

            // Convert to turf format
            const line = turf.lineString(path.map(p => [p.lng, p.lat]));

            // Ensure polygon is closed
            let coords = geofenceCoords.map(p => [p.lng, p.lat]);
            if (coords[0][0] !== coords[coords.length - 1][0] ||
                coords[0][1] !== coords[coords.length - 1][1]) {
                coords.push(coords[0]);
            }

            const polygon = turf.polygon([coords]);

            // ✅ FIX: realistic patrol width (10–20 meters)
            const buffered = turf.buffer(line, 0.02, {
                units: 'kilometers'
            });

            const intersection = turf.intersect(buffered, polygon);

            if (!intersection) return 0;

            const coveredArea = turf.area(intersection);
            const totalArea = turf.area(polygon);

            if (!totalArea) return 0;

            return ((coveredArea / totalArea) * 100).toFixed(2);

        } catch (e) {
            console.error("Coverage error:", e);
            return 0;
        }
    }



    function drawSessionsAndCoverage() {
        const coverageResults = [];
        let htmlTable = "";

        rawSessions.forEach(s => {
            if (!s.path || s.path.length < 2) return;
            const path = s.path.map(p => ({
                lat: p.lat,
                lng: p.lng
            }));

            const poly = new google.maps.Polyline({
                path,
                strokeColor: '#10B981',
                strokeOpacity: 0.9,
                strokeWeight: 4,
                map
            });
            sessionPolylines[s.id] = poly;

            // Coverage calculation (Turf)
            const siteFence = geofences.find(g => g.id == s.site_id);
            if (siteFence && siteFence.coords) {
                // Assuming calculateCoverage from original code exists.
                // Using dummy random for visual completeness if not defined in this block
                const cov = parseFloat(calculateCoverage(path, siteFence.coords));
                const status = getCoverageStatus(cov);

                htmlTable += `
        <tr>
    <td class="ps-4">#${s.id}</td>
    <td>${siteLabels[s.site_id] ?? s.site_id}</td>
    <td>
        <span class="badge-soft badge-soft-primary">${cov}%</span>
    </td>
    <td>
        <span class="badge-soft ${status.class}">${status.label}</span>
    </td>
     </tr>`;
            }
        });


        document.getElementById("coverageTable").innerHTML = htmlTable ||
            `<tr><td colspan="3" class="text-center text-muted py-3">No coverage data</td></tr>`;

        if (!bounds.isEmpty()) map.fitBounds(bounds);
    }

    // Playback Logic
    let playTimer, activeMarkers = [];
    document.getElementById('playAll').addEventListener('click', async () => {
        activeMarkers.forEach(m => m.setMap(null));
        activeMarkers = [];
        for (let sid in sessionPolylines) {
            const poly = sessionPolylines[sid];
            const path = poly.getPath().getArray();
            if (!path.length) continue;
            let idx = 0;
            const marker = new google.maps.Marker({
                position: path[0],
                map,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 6,
                    fillColor: '#F59E0B',
                    strokeWeight: 2
                }
            });
            activeMarkers.push(marker);
            await new Promise((resolve) => {
                playTimer = setInterval(() => {
                    idx++;
                    if (idx >= path.length) {
                        clearInterval(playTimer);
                        resolve();
                    } else {
                        marker.setPosition(path[idx]);
                    }
                }, 30);
            });
        }
    });

    document.getElementById('stopPlay').addEventListener('click', () => {
        clearInterval(playTimer);
        activeMarkers.forEach(m => m.setMap(null));
        activeMarkers = [];
    });

    // Site Drilldown Modal Chart
    function showSiteUsersBreakdown(userCounts, t) {
        const rows = Object.keys(userCounts).map(id => ({
            id: id,
            name: userMap[id] ?? ("User #" + userLabels[id]),
            count: userCounts[id]
        })).sort((a, b) => b.count - a.count);

        let html =
            `<table class="table dash-table mb-0"><thead><tr><th class="ps-4">User</th><th>Sessions</th></tr></thead><tbody>`;
        rows.forEach(r => {
            html +=
                `<tr><td class="ps-4">${r.name}</td><td><span class="badge-soft badge-soft-primary">${r.count}</span></td></tr>`;
        });
        html += "</tbody></table>";
        document.getElementById("userBreakdownTable").innerHTML = html;

        const ctx = document.getElementById("userBreakdownChart");
        if (charts.modal) charts.modal.destroy();
        charts.modal = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: rows.map(r => r.name),
                datasets: [{
                    data: rows.map(r => r.count),
                    backgroundColor: [t.cBlue, t.cGreen, t.cYellow, t.cOrange, t.cRed, t.cPurple],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: t.textMain
                        }
                    }
                }
            }
        });

        new bootstrap.Modal(document.getElementById("userBreakdownModal")).show();
    }

    function toggleGeofences() {
        geofencesVisible = !geofencesVisible;
        geofencePolygons.forEach(p => p.setMap(geofencesVisible ? map : null));
    }

    let singlePlayTimer = null;
    let currentMarker = null;
    let currentPath = [];
    let currentIndex = 0;

    // Safe init after DOM + map ready
    window.addEventListener("load", () => {

        const playBtn = document.getElementById('playBtn');
        const pauseBtn = document.getElementById('pauseBtn');
        const resetBtn = document.getElementById('resetBtn');
        const slider = document.getElementById('playbackSlider');
        const sessionSelect = document.getElementById('playbackSession');

        if (!playBtn || !pauseBtn || !resetBtn) {
            console.error("Playback buttons not found");
            return;
        }

        function getSessionPath(sessionId) {
            const poly = sessionPolylines[sessionId];

            if (!poly) {
                console.error("❌ No polyline found for session:", sessionId);
                return [];
            }

            const path = poly.getPath().getArray();

            if (!path.length) {
                console.warn("⚠️ Empty path for session:", sessionId);
                return [];
            }

            return path;
        }

        // ▶ PLAY
        playBtn.addEventListener('click', () => {
            const sessionId = parseInt(sessionSelect.value);

            if (!sessionId) {
                alert("Select a session first");
                return;
            }

            clearInterval(singlePlayTimer);

            if (currentMarker) currentMarker.setMap(null);

            currentPath = getSessionPath(sessionId);
            if (!currentPath.length) return;

            currentIndex = 0;

            currentMarker = new google.maps.Marker({
                position: currentPath[0],
                map: map
            });

            singlePlayTimer = setInterval(() => {
                currentIndex++;

                if (currentIndex >= currentPath.length) {
                    clearInterval(singlePlayTimer);
                    return;
                }

                currentMarker.setPosition(currentPath[currentIndex]);

                slider.value = (currentIndex / currentPath.length) * 100;

            }, 100);
        });

        // ⏸ PAUSE
        pauseBtn.addEventListener('click', () => {
            clearInterval(singlePlayTimer);
        });

        // 🔄 RESET
        resetBtn.addEventListener('click', () => {
            clearInterval(singlePlayTimer);

            if (currentMarker) {
                currentMarker.setMap(null);
                currentMarker = null;
            }

            currentIndex = 0;
            slider.value = 0;
        });

        // 🎚 SLIDER CONTROL
        slider.addEventListener('input', () => {
            if (!currentPath.length || !currentMarker) return;

            const index = Math.floor((slider.value / 100) * currentPath.length);

            if (currentPath[index]) {
                currentMarker.setPosition(currentPath[index]);
                currentIndex = index;
            }
        });

    });
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfBFN6L_HROTd-mS8QqUDRIqskkvHvFYk&libraries=visualization&callback=initMap" async defer></script>
@endsection

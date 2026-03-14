{{-- Key Performance Indicators --}}
<div class="row g-3 mb-4">
    {{-- 1. Active Guards - Opens Modal --}}
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100 kpi-card kpi-clickable" 
             data-bs-toggle="modal" 
             data-bs-target="#activeGuardsModal" 
             style="cursor: pointer;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-2">Active Guards</h6>
                        <h3 class="mb-0 fw-bold text-dark kpi-value" data-kpi="activeGuards">{{ number_format($kpis['activeGuards'] ?? 0) }}</h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center kpi-icon-wrapper" style="background: rgba(13, 110, 253, 0.1); color: #0d6efd;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Total Patrols - Opens Modal --}}
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100 kpi-card kpi-clickable" 
             data-bs-toggle="modal" 
             data-bs-target="#patrolAnalyticsModal" 
             style="cursor: pointer;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-2">Total Patrols</h6>
                        <h3 class="mb-0 fw-bold text-dark kpi-value" data-kpi="totalPatrols">{{ number_format($kpis['totalPatrols'] ?? 0) }}</h3>
                        <div class="mt-2 small text-muted"><span class="fw-bold">{{ $kpis['completedPatrols'] ?? 0 }}</span> completed</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center kpi-icon-wrapper" style="background: rgba(25, 135, 84, 0.1); color: #198754;">
                        <i class="bi bi-person-walking"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Total Distance - Opens Modal --}}
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100 kpi-card kpi-clickable" 
             data-bs-toggle="modal" 
             data-bs-target="#totalDistanceModal" 
             style="cursor: pointer;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-2">Total Distance</h6>
                        <h3 class="mb-0 fw-bold text-dark kpi-value" data-kpi="totalDistance">{{ number_format($kpis['totalDistance'] ?? 0, 2) }} <small class="fs-6 fw-normal">km</small></h3>
                        <div class="mt-2 small text-muted">Avg: {{ number_format($kpis['avgDistancePerGuard'] ?? 0, 2) }} km/guard</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center kpi-icon-wrapper" style="background: rgba(13, 202, 240, 0.1); color: #0dcaf0;">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Attendance Rate - Modal Trigger --}}
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100 kpi-card kpi-clickable" 
             data-bs-toggle="modal" 
             data-bs-target="#attendanceDetailsModal"
             style="cursor: pointer;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-2">Attendance Rate</h6>
                        <h3 class="mb-0 fw-bold text-dark kpi-value" data-kpi="attendanceRate">{{ number_format($kpis['attendanceRate'] ?? 0, 1) }}%</h3>
                        <div class="mt-2 small text-muted"><span class="fw-bold">{{ $kpis['presentCount'] ?? 0 }}</span> present today</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center kpi-icon-wrapper" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. Total Incidents - Modal trigger --}}
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100 kpi-card kpi-clickable" 
             data-bs-toggle="modal" 
             data-bs-target="#totalIncidentsModal" 
             style="cursor: pointer;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-2">Total Incidents</h6>
                        <h3 class="mb-0 fw-bold text-dark kpi-value" data-kpi="totalIncidents">{{ number_format($kpis['totalIncidents'] ?? 0) }}</h3>
                        <div class="mt-2 small text-muted"><span class="fw-bold text-danger" data-kpi="pendingIncidents">{{ $kpis['pendingIncidents'] ?? 0 }}</span> pending review</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center kpi-icon-wrapper" style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 6. Resolution Rate - Opens Modal --}}
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100 kpi-card kpi-clickable" 
             data-bs-toggle="modal" 
             data-bs-target="#resolutionRateModal" 
             style="cursor: pointer;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-2">Resolution Rate</h6>
                        <h3 class="mb-0 fw-bold text-dark kpi-value" data-kpi="resolutionRate">{{ number_format($kpis['resolutionRate'] ?? 0, 1) }}%</h3>
                        <div class="mt-2 small text-muted"><span class="fw-bold text-success">{{ $kpis['resolvedIncidents'] ?? 0 }}</span> cases resolved</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center kpi-icon-wrapper" style="background: rgba(25, 135, 84, 0.1); color: #198754;">
                        <i class="bi bi-check-square-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 7. Beat Coverage - Opens Modal --}}
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100 kpi-card kpi-clickable" 
             data-bs-toggle="modal" 
             data-bs-target="#beatCoverageModal" 
             style="cursor: pointer;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-2">Beat Coverage</h6>
                        <h3 class="mb-0 fw-bold text-dark kpi-value" data-kpi="siteCoverage">{{ number_format(isset($coverageAnalysis) && isset($coverageAnalysis['coveragePercentage']) ? $coverageAnalysis['coveragePercentage'] : ($kpis['siteCoverage'] ?? 0), 1) }}%</h3>
                        <div class="mt-2 small text-muted"><span class="fw-bold">{{ isset($coverageAnalysis) && isset($coverageAnalysis['sitesWithPatrols']) ? $coverageAnalysis['sitesWithPatrols'] : 0 }}</span> / {{ isset($coverageAnalysis) && isset($coverageAnalysis['totalSites']) ? $coverageAnalysis['totalSites'] : ($kpis['totalSites'] ?? 0) }} active</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center kpi-icon-wrapper" style="background: rgba(13, 202, 240, 0.1); color: #0dcaf0;">
                        <i class="bi bi-map-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 8. Total Beats - Opens Modal --}}
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100 kpi-card kpi-clickable" 
             data-bs-toggle="modal" 
             data-bs-target="#totalBeatsModal" 
             style="cursor: pointer;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-2">Total Managed Beats</h6>
                        <h3 class="mb-0 fw-bold text-dark kpi-value" data-kpi="totalSites">{{ number_format($kpis['totalSites'] ?? 0) }}</h3>
                        <div class="mt-2 small text-muted">Across all ranges</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center kpi-icon-wrapper" style="background: rgba(25, 135, 84, 0.1); color: #198754;">
                        <i class="bi bi-tree-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('modals')
{{-- Modal 1: Active Guards Details --}}
<div class="modal fade" id="activeGuardsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">👥 Active Guards Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>Total Active Guards:</strong> {{ number_format($kpis['activeGuards'] ?? 0) }}
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">SR.NO</th>
                                <th>GUARD NAME</th>
                                <th>CONTACT</th>
                                <th>EMAIL</th>
                                <th class="text-center">STATUS</th>
                            </tr>
                        </thead>
                        <tbody id="activeGuardsList">
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="text-muted mt-2">Fetching active guards...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal 2: Patrol Analytics --}}
<div class="modal fade" id="patrolAnalyticsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">🚶 Patrol Analytics Overview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Top Summary Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-success text-white">
                            <div class="card-body text-center py-3">
                                <h6 class="text-white-50 text-uppercase small fw-bold mb-2">Total Patrols</h6>
                                <h2 class="mb-0 fw-bold" id="modalTotalPatrols">{{ number_format($kpis['totalPatrols']) }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-primary text-white">
                            <div class="card-body text-center py-3">
                                <h6 class="text-white-50 text-uppercase small fw-bold mb-2">Completed</h6>
                                <h3 class="mb-0 fw-bold" id="modalCompletedPatrols">{{ number_format($kpis['completedPatrols']) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-warning text-dark">
                            <div class="card-body text-center py-3">
                                <h6 class="text-dark-50 text-uppercase small fw-bold mb-2">Ongoing</h6>
                                <h3 class="mb-0 fw-bold" id="modalOngoingPatrols">{{ number_format($kpis['totalPatrols'] - $kpis['completedPatrols']) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-info text-white">
                            <div class="card-body text-center py-3">
                                <h6 class="text-white-50 text-uppercase small fw-bold mb-2">Total Distance</h6>
                                <h3 class="mb-0 fw-bold" id="modalTotalDistance">{{ number_format($kpis['totalDistance'], 2) }} km</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Completion Progress Bar -->
                <div class="card border shadow-sm mb-4 bg-light">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0">Patrol Completion Rate</h6>
                            <span class="badge bg-success" id="modalCompletionRateBadge">{{ $kpis['totalPatrols'] > 0 ? number_format(($kpis['completedPatrols'] / $kpis['totalPatrols']) * 100, 1) : 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 15px;">
                            <div id="modalCompletionProgressBar" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $kpis['totalPatrols'] > 0 ? ($kpis['completedPatrols'] / $kpis['totalPatrols']) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Day vs Night Breakdown -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3">
                                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-brightness-high text-warning me-2"></i>Day vs Night Distribution</h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center mt-2">
                                    <div class="col-6 border-end">
                                        <div class="display-6 text-warning mb-1"><i class="bi bi-sun-fill"></i></div>
                                        <h4 class="fw-bold mb-0" id="modalDayPatrols">0</h4>
                                        <small class="text-muted text-uppercase small fw-bold">Day Patrols</small>
                                    </div>
                                    <div class="col-6">
                                        <div class="display-6 text-primary mb-1"><i class="bi bi-moon-stars-fill"></i></div>
                                        <h4 class="fw-bold mb-0" id="modalNightPatrols">0</h4>
                                        <small class="text-muted text-uppercase small fw-bold">Night Patrols</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Patrol Type Breakdown -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3">
                                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-list-stars text-primary me-2"></i>Patrol Type Breakdown</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light small">
                                            <tr>
                                                <th class="ps-3 py-2">Type</th>
                                                <th class="text-center py-2">Count</th>
                                                <th class="text-end pe-3 py-2">Distance</th>
                                            </tr>
                                        </thead>
                                        <tbody id="modalPatrolTypeTable" class="small">
                                            <tr><td colspan="3" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Loading breakdown...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Sites by Distance -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3">
                                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-geo-alt text-success me-2"></i>Top Sites by Patrol Distance</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light sticky-top small">
                                            <tr>
                                                <th class="ps-3 py-2" style="width: 60px;">#</th>
                                                <th class="py-2">Site Name</th>
                                                <th class="text-end pe-3 py-2">Total Distance</th>
                                            </tr>
                                        </thead>
                                        <tbody id="modalTopSitesTable" class="small">
                                            <tr><td colspan="3" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-success me-2"></div>Loading site rankings...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal 3: Resolution Rate Details --}}
<div class="modal fade" id="resolutionRateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">✅ Resolution Rate Calculation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h4 class="text-center mb-3">Resolution Rate: <span id="modalResRateTitle" class="text-success">{{ number_format($kpis['resolutionRate'], 1) }}%</span></h4>
                        <div class="text-center mb-3">
                            <div class="progress" style="height: 30px;">
                                <div id="modalResRateProgressBar" class="progress-bar bg-success" role="progressbar" style="width: {{ $kpis['resolutionRate'] }}%">
                                    {{ number_format($kpis['resolutionRate'], 1) }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h6 class="fw-bold mb-3">Calculation Formula:</h6>
                <div class="alert alert-info">
                    <code>Resolution Rate = (Resolved Incidents / Total Incidents) × 100</code>
                </div>
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card border-danger">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Total Incidents</h6>
                                <h3 id="modalResRateTotal" class="text-danger">{{ number_format($kpis['totalIncidents']) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Resolved</h6>
                                <h3 id="modalResRateResolved" class="text-success">{{ number_format($kpis['resolvedIncidents']) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-warning">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Pending</h6>
                                <h3 id="modalResRatePending" class="text-warning">{{ number_format($kpis['pendingIncidents']) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <h6 class="fw-bold">Calculation:</h6>
                    <p id="modalResRateCalculation" class="mb-1">{{ number_format($kpis['resolvedIncidents']) }} ÷ {{ number_format($kpis['totalIncidents']) }} × 100 = <strong>{{ number_format($kpis['resolutionRate'], 1) }}%</strong></p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal 4: Beat Coverage Details --}}
<div class="modal fade" id="beatCoverageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">🗺️ Beat Coverage Analysis</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Data Summary Cards -->
                <div class="row g-3 mb-4 text-center">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-primary text-white">
                            <div class="card-body py-2">
                                <h6 class="text-white-50 small mb-1">Total Beats</h6>
                                <h3 class="mb-0 fw-bold" id="modalCoverageTotalBeats">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-success text-white">
                            <div class="card-body py-2">
                                <h6 class="text-white-50 small mb-1">Covered</h6>
                                <h3 class="mb-0 fw-bold" id="modalCoverageCoveredBeats">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-danger text-white">
                            <div class="card-body py-2">
                                <h6 class="text-white-50 small mb-1">Unpatrolled</h6>
                                <h3 class="mb-0 fw-bold" id="modalCoverageUnpatrolledBeats">0</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4 bg-light">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0">Site Coverage Percentage</h6>
                            <span id="modalCoveragePercentageText" class="badge bg-info text-dark">0.0%</span>
                        </div>
                        <div class="progress" style="height: 15px;">
                            <div id="modalCoverageProgressBar" class="progress-bar bg-info" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="row g-3">
                    <!-- Column 1: Gaps -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-danger text-white py-2 d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 small fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>0 Patrol Gaps</h6>
                                <span class="badge bg-white text-danger" id="modalCoverageGapsCount">0</span>
                            </div>
                            <div class="card-body p-0" style="max-height: 250px; overflow-y: auto;">
                                <div id="coverageGapsList" class="list-group list-group-flush">
                                    <!-- Dynamic -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Most Visited -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-success text-white py-2">
                                <h6 class="mb-0 small fw-bold"><i class="bi bi-graph-up-arrow me-1"></i>Most Visited</h6>
                            </div>
                            <div class="card-body p-0" style="max-height: 250px; overflow-y: auto;">
                                <div id="mostVisitedBeatsList" class="list-group list-group-flush">
                                    <!-- Dynamic -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column 3: Least Visited -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-warning text-dark py-2">
                                <h6 class="mb-0 small fw-bold"><i class="bi bi-graph-down-arrow me-1"></i>Least Visited</h6>
                            </div>
                            <div class="card-body p-0" style="max-height: 250px; overflow-y: auto;">
                                <div id="leastVisitedBeatsList" class="list-group list-group-flush">
                                    <div class="text-center py-4 text-muted small">
                                        <div class="spinner-border spinner-border-sm text-warning mb-2"></div>
                                        <p class="mb-0">Loading...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-4 bg-light">
                    <div class="card-body py-3">
                        <h6 class="fw-bold mb-2 small text-muted text-uppercase">Calculation Logic</h6>
                        <div class="alert alert-info py-2 mb-2 small border-0 shadow-sm">
                            <code class="fw-bold">Coverage Rate = (Covered Sites / Total Sites) × 100</code>
                        </div>
                        <div class="fw-bold text-dark font-monospace" id="modalCoverageCalculationText">
                            0 ÷ 0 × 100 = 0.0%
                        </div>
                    </div>
                </div>

                <div class="d-grid mt-3">
                    <a href="{{ route('patrol.kml.view') }}" class="btn btn-primary btn-lg shadow-sm">
                        <i class="bi bi-map"></i> View Detailed KML Map
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal 4b: Total Incidents Breakdown --}}
<div class="modal fade" id="totalIncidentsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-shield-exclamation me-2"></i>Total Incidents Analytics</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <!-- Top Summary Row -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm text-center h-100">
                            <div class="card-body py-3 d-flex flex-column justify-content-center align-items-center">
                                <div class="mb-2 text-danger opacity-25">
                                    <i class="bi bi-shield-exclamation fs-4"></i>
                                </div>
                                <h2 class="fw-bold text-dark mb-0" id="modalIncidentsTotal">0</h2>
                                <p class="text-muted extra-small text-uppercase fw-bold mb-0">Total Incidents</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card shadow-sm text-center border-start border-danger border-4 h-100">
                            <div class="card-body py-3 d-flex flex-column justify-content-center align-items-center">
                                <div class="mb-2 text-warning opacity-75">
                                    <i class="bi bi-hourglass-split fs-4"></i>
                                </div>
                                <h2 class="fw-bold text-danger mb-0" id="modalIncidentsPending">0</h2>
                                <p class="text-muted extra-small text-uppercase fw-bold mb-0">Pending Review</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card shadow-sm text-center border-start border-success border-4 h-100">
                            <div class="card-body py-3 d-flex flex-column justify-content-center align-items-center">
                                <div class="mb-2 text-success opacity-75">
                                    <i class="bi bi-check-circle-fill fs-4"></i>
                                </div>
                                <h2 class="fw-bold text-success mb-0" id="modalIncidentsResolved">0</h2>
                                <p class="text-muted extra-small text-uppercase fw-bold mb-0">Resolved</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card shadow-sm text-center border-start border-info border-4 h-100">
                            <div class="card-body py-3 d-flex flex-column justify-content-center align-items-center">
                                <div class="mb-2 text-info opacity-75">
                                    <i class="bi bi-graph-up-arrow fs-4"></i>
                                </div>
                                <h2 class="fw-bold text-info mb-0" id="modalIncidentsRate">0.0%</h2>
                                <p class="text-muted extra-small text-uppercase fw-bold mb-0">Resolution Rate</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Incidents Table -->
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0 text-uppercase small text-muted"><i class="bi bi-list-ul me-2"></i>Recent Critical Incidents</h6>
                                <span class="badge bg-light text-dark border">Latest 50</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light sticky-top small">
                                            <tr>
                                                <th class="ps-3 py-2 text-nowrap" style="min-width: 90px;">Date / Time</th>
                                                <th class="py-2">Details</th>
                                                <th class="py-2 d-none d-md-table-cell">Site</th>
                                                <th class="py-2 d-none d-md-table-cell">Guard</th>
                                                <th class="text-center py-2" style="width: 100px;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="modalIncidentsRecentTable" class="small">
                                            <tr><td colspan="5" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-danger me-2"></div>Loading recent records...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column 3: Site Performance -->
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3 border-0">
                                <h6 class="fw-bold mb-0 text-uppercase small text-muted">Site-Level Incident Distribution</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light small">
                                            <tr>
                                                <th class="ps-3 py-2">Site Name</th>
                                                <th class="text-center py-2">Total</th>
                                                <th class="text-center py-2">Resolved</th>
                                                <th class="text-center py-2">Pending</th>
                                                <th class="text-end pe-3 py-2">Performance</th>
                                            </tr>
                                        </thead>
                                        <tbody id="modalIncidentsSiteTable" class="small">
                                            <tr><td colspan="5" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-danger me-2"></div>Loading site data...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid mt-4">
                    <a href="{{ url('/incidents/summary') }}" class="btn btn-danger btn-lg shadow-sm">
                        <i class="bi bi-journal-text me-2"></i>Open Full Incident Management
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal 8: Total Beats Details --}}
<div class="modal fade" id="totalBeatsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">🌲 Total Beats Information</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-primary">
                    <strong>Total Beats:</strong> <span id="modalTotalBeatsCount">{{ number_format($kpis['totalSites']) }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Sr.No</th>
                                <th>Beat Name</th>
                                <th>Range</th>
                            </tr>
                        </thead>
                        <tbody id="totalBeatsList">
                            <tr>
                                <td colspan="3" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal 9: Total Distance Details --}}
<div class="modal fade" id="totalDistanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">📏 Total Patrolling Distance</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Summary Overview --}}
                <div class="row g-3 mb-4 text-center">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-info text-white">
                            <div class="card-body py-3">
                                <h6 class="text-white-50 text-uppercase extra-small fw-bold mb-1">Total Distance</h6>
                                <h3 class="mb-0 fw-bold" id="modalDistanceTotal">{{ number_format($kpis['totalDistance'] ?? 0, 1) }} km</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-primary text-white">
                            <div class="card-body py-3">
                                <h6 class="text-white-50 text-uppercase extra-small fw-bold mb-1">Active Guards</h6>
                                <h3 class="mb-0 fw-bold" id="modalDistanceGuards">{{ number_format($kpis['activeGuards'] ?? 0) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-success text-white">
                            <div class="card-body py-3">
                                <h6 class="text-white-50 text-uppercase extra-small fw-bold mb-1">Avg Efficiency</h6>
                                <h3 class="mb-0 fw-bold" id="modalDistanceAvg">{{ number_format($kpis['avgDistancePerGuard'] ?? 0, 1) }} <small class="fs-6 fw-normal">km</small></h3>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Distance Breakdown Table --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold m-0 text-dark text-uppercase small" style="letter-spacing: 0.5px;"><i class="bi bi-bar-chart-fill text-info me-2"></i>Distance Breakdown by Guard</h6>
                    <span class="badge bg-light text-dark border extra-small">Top 50 Record Holders</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 60px;">#</th>
                                <th>GUARD NAME</th>
                                <th>SITE / BEAT</th>
                                <th class="text-end pe-3">DISTANCE (KM)</th>
                            </tr>
                        </thead>
                        <tbody id="totalDistanceList">
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="spinner-border text-info" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="text-muted mt-2 small">Aggregating distance data...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <a href="{{ route('patrol.foot.summary') }}" class="btn btn-info text-white shadow-sm">View Full Foot Summary</a>
                <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Attendance Rate Modal --}}
<div class="modal fade" id="attendanceDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-check-fill me-2"></i>Attendance Analytics</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                {{-- Summary Overview --}}
                <div class="row g-3 mb-4 text-center">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-warning-subtle text-warning-emphasis">
                            <div class="card-body py-3">
                                <h6 class="text-uppercase extra-small fw-bold mb-1">Attendance Rate</h6>
                                <h3 class="mb-0 fw-bold" id="modalAttendanceRate">{{ number_format($kpis['attendanceRate'] ?? 0, 1) }}%</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-success-subtle text-success-emphasis">
                            <div class="card-body py-3">
                                <h6 class="text-uppercase extra-small fw-bold mb-1">Total Present</h6>
                                <h3 class="mb-0 fw-bold" id="modalAttendancePresent">{{ number_format($kpis['presentCount'] ?? 0) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-info-subtle text-info-emphasis">
                            <div class="card-body py-3">
                                <h6 class="text-uppercase extra-small fw-bold mb-1">Active Staff</h6>
                                <h3 class="mb-0 fw-bold" id="modalAttendanceStaff">{{ number_format($kpis['activeGuards'] ?? 0) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Detailed List --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold m-0 text-dark text-uppercase small" style="letter-spacing: 0.5px;">Attendance Consistency by Guard</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light extra-small text-uppercase text-muted">
                                    <tr>
                                        <th class="ps-3 py-2">Guard Details</th>
                                        <th class="py-2">Site Context</th>
                                        <th class="text-center py-2">Days Present</th>
                                        <th class="text-end pe-3 py-2">Late Freq.</th>
                                    </tr>
                                </thead>
                                <tbody id="attendanceDetailsList">
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="spinner-border text-warning"></div>
                                            <p class="text-muted mt-2 small">Analyzing attendance patterns...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <a href="{{ url('/attendance/summary') }}" id="fullAttendanceLedgerBtn" class="btn btn-warning shadow-sm fw-bold">Open Full Attendance Ledger</a>
                <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Executive Analytics JS Loaded');

    // Helper to get current filters - Exposed to window for global access
    window.getCurrentFilters = function() {
        const range = document.getElementById('rangeSelect')?.value || '';
        const beat = document.getElementById('beatSelect')?.value || '';
        const startDate = document.getElementById('startDateInput')?.value || '';
        const endDate = document.getElementById('endDateInput')?.value || '';
        const user = document.getElementById('userSelect')?.value || '';
        
        let params = new URLSearchParams();
        if (range) params.append('range', range);
        if (beat) params.append('beat', beat);
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (user) params.append('user', user);
        
        return params.toString();
    };

    // Load Active Guards when modal opens
    document.getElementById('activeGuardsModal')?.addEventListener('show.bs.modal', function() {
        const listContainer = document.getElementById('activeGuardsList');
        listContainer.innerHTML = '<tr><td colspan="5" class="text-center py-5"><div class="spinner-border text-primary"></div><p class="text-muted mt-2">Loading guards...</p></td></tr>';
        
        const filters = window.getCurrentFilters ? window.getCurrentFilters() : '';
        fetch(`/api/active-guards?${filters}`)
            .then(response => response.json())
            .then(data => {
                let html = '';
                if (data.guards && data.guards.length > 0) {
                    data.guards.forEach((guard, index) => {
                        html += `
                            <tr>
                                <td class="ps-3 text-muted">${index + 1}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">
                                            ${guard.name.charAt(0).toUpperCase()}
                                        </div>
                                        <a href="#" class="guard-name-link text-decoration-none fw-bold" data-guard-id="${guard.id}">
                                            ${guard.name}
                                        </a>
                                    </div>
                                </td>
                                <td>${guard.phone || '<span class="text-muted small">N/A</span>'}</td>
                                <td>${guard.email || '<span class="text-muted small">N/A</span>'}</td>
                                <td class="text-center"><span class="badge bg-success-subtle text-success border border-success-subtle px-3">Active</span></td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="5" class="text-center py-5 text-muted">No active guards found for current filters</td></tr>';
                }
                listContainer.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                listContainer.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-danger">Error loading data</td></tr>';
            });
    });

    // Load Total Beats when modal opens
    document.getElementById('totalBeatsModal')?.addEventListener('show.bs.modal', function() {
        const listContainer = document.getElementById('totalBeatsList');
        const countSpan = document.getElementById('modalTotalBeatsCount');
        
        listContainer.innerHTML = '<tr><td colspan="3" class="text-center py-5"><div class="spinner-border text-primary"></div><p class="text-muted mt-2">Loading beats...</p></td></tr>';
        
        const filters = window.getCurrentFilters ? window.getCurrentFilters() : '';
        fetch(`/api/beats-details?${filters}`)
            .then(response => response.json())
            .then(data => {
                if (data.beats && countSpan) {
                    countSpan.innerText = data.beats.length;
                }
                let html = '';
                if (data.beats && data.beats.length > 0) {
                    data.beats.forEach((beat, index) => {
                        html += `
                            <tr>
                                <td class="ps-3 text-muted">${index + 1}</td>
                                <td><strong>${beat.name}</strong></td>
                                <td>${beat.range_name || 'N/A'}</td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="3" class="text-center py-5 text-muted">No beats found for current filters</td></tr>';
                }
                listContainer.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                listContainer.innerHTML = '<tr><td colspan="3" class="text-center py-5 text-danger">Error loading data</td></tr>';
            });
    });

    // Load Beat Coverage Analysis when modal opens
    // Load Beat Coverage Analysis when modal opens
    document.getElementById('beatCoverageModal')?.addEventListener('show.bs.modal', function() {
        // Summary elements
        const totalSitesEl = document.getElementById('modalCoverageTotalBeats');
        const coveredSitesEl = document.getElementById('modalCoverageCoveredBeats');
        const unpatrolledSitesEl = document.getElementById('modalCoverageUnpatrolledBeats');
        const percentageText = document.getElementById('modalCoveragePercentageText');
        const progressBar = document.getElementById('modalCoverageProgressBar');
        const calculationText = document.getElementById('modalCoverageCalculationText');
        const gapsCountBadge = document.getElementById('modalCoverageGapsCount');
        
        // List elements
        const gapsList = document.getElementById('coverageGapsList');
        const visitedList = document.getElementById('mostVisitedBeatsList');
        const leastVisitedList = document.getElementById('leastVisitedBeatsList');
        
        // Clear summary and show loading states immediately
        if (totalSitesEl) totalSitesEl.innerText = '--';
        if (coveredSitesEl) coveredSitesEl.innerText = '--';
        if (unpatrolledSitesEl) unpatrolledSitesEl.innerText = '--';
        if (percentageText) percentageText.innerText = '0.0%';
        if (progressBar) progressBar.style.width = '0%';
        if (calculationText) calculationText.innerText = 'Calculating...';
        if (gapsCountBadge) gapsCountBadge.innerText = '...';

        const loadingHtml = (color, text) => `<div class="text-center py-4 text-muted small"><div class="spinner-border spinner-border-sm text-${color} mb-2"></div><p class="mb-0">${text}...</p></div>`;
        
        gapsList.innerHTML = loadingHtml('danger', 'Analyzing gaps');
        visitedList.innerHTML = loadingHtml('success', 'Calculating density');
        leastVisitedList.innerHTML = loadingHtml('warning', 'Evaluating performance');
        
        const filters = window.getCurrentFilters ? window.getCurrentFilters() : '';
        fetch(`/api/coverage-analysis?${filters}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 1. Update Header & Summary Cards
                    if (data.summary) {
                        const total = parseInt(data.summary.totalSites || 0);
                        const covered = parseInt(data.summary.sitesWithPatrols || 0);
                        const unpatrolled = total - covered;
                        const pctValue = parseFloat(data.summary.coveragePercentage || 0);
                        const pctStr = pctValue.toFixed(1) + '%';

                        if (totalSitesEl) totalSitesEl.innerText = total.toLocaleString();
                        if (coveredSitesEl) coveredSitesEl.innerText = covered.toLocaleString();
                        if (unpatrolledSitesEl) unpatrolledSitesEl.innerText = unpatrolled.toLocaleString();
                        if (percentageText) percentageText.innerText = pctStr;
                        if (progressBar) progressBar.style.width = pctStr;
                        
                        // Update calculation visual
                        if (calculationText) {
                            calculationText.innerText = `${covered} ÷ ${total} × 100 = ${pctStr}`;
                        }
                    }

                    // 2. Populate Gaps (Column 1)
                    let gapsHtml = '';
                    const gaps = data.gaps || [];
                    if (gapsCountBadge) gapsCountBadge.innerText = gaps.length;
                    
                    if (gaps.length > 0) {
                        gaps.forEach(site => {
                            gapsHtml += `
                                <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 border-0 border-bottom">
                                    <span class="small fw-bold text-danger"><i class="bi bi-geo-alt-fill me-1"></i>${site.site_name || 'Unknown Site'}</span>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small">0</span>
                                </div>
                            `;
                        });
                    } else {
                        gapsHtml = '<div class="text-center py-4 text-success small fw-bold"><i class="bi bi-check-circle-fill d-block fs-4 mb-2"></i>Perfect Coverage</div>';
                    }
                    gapsList.innerHTML = gapsHtml;

                    // 3. Populate Most Visited (Column 2)
                    let visitedHtml = '';
                    if (data.mostPatrolled && data.mostPatrolled.length > 0) {
                        data.mostPatrolled.forEach((site, index) => {
                            visitedHtml += `
                                <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 border-0 border-bottom">
                                    <span class="small text-truncate me-2"><strong>${site.site_name}</strong></span>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">${site.patrol_count}</span>
                                </div>
                            `;
                        });
                    } else {
                        visitedHtml = '<div class="text-center py-4 text-muted small">No data recorded</div>';
                    }
                    visitedList.innerHTML = visitedHtml;

                    // 4. Populate Least Visited (Column 3)
                    let leastVisitedHtml = '';
                    if (data.leastPatrolled && data.leastPatrolled.length > 0) {
                        data.leastPatrolled.forEach((site, index) => {
                            leastVisitedHtml += `
                                <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 border-0 border-bottom">
                                    <span class="small text-truncate me-2">${site.site_name}</span>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill small">${site.patrol_count}</span>
                                </div>
                            `;
                        });
                    } else {
                        leastVisitedHtml = '<div class="text-center py-4 text-muted small">No data recorded</div>';
                    }
                    leastVisitedList.innerHTML = leastVisitedHtml;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const errorHtml = '<div class="text-center py-4 text-danger small">Error loading data</div>';
                gapsList.innerHTML = errorHtml;
                visitedList.innerHTML = errorHtml;
                leastVisitedList.innerHTML = errorHtml;
            });
    });

    // Load Patrol Analytics when modal opens
    document.getElementById('patrolAnalyticsModal')?.addEventListener('show.bs.modal', function() {
        const totalEl = document.getElementById('modalTotalPatrols');
        const completedEl = document.getElementById('modalCompletedPatrols');
        const ongoingEl = document.getElementById('modalOngoingPatrols');
        const distanceEl = document.getElementById('modalTotalDistance');
        const rateBadge = document.getElementById('modalCompletionRateBadge');
        const progressBar = document.getElementById('modalCompletionProgressBar');
        const dayPatrolsEl = document.getElementById('modalDayPatrols');
        const nightPatrolsEl = document.getElementById('modalNightPatrols');
        const typeTable = document.getElementById('modalPatrolTypeTable');
        const sitesTable = document.getElementById('modalTopSitesTable');

        // Show loading spinners in tables
        typeTable.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Loading...</td></tr>';
        sitesTable.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-success me-2"></div>Loading...</td></tr>';

        const filters = window.getCurrentFilters ? window.getCurrentFilters() : '';
        fetch(`/api/patrol-analytics?${filters}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update Summary
                    if (data.summary) {
                        totalEl.innerText = data.summary.totalPatrols.toLocaleString();
                        completedEl.innerText = data.summary.completedPatrols.toLocaleString();
                        ongoingEl.innerText = data.summary.ongoingPatrols.toLocaleString();
                        distanceEl.innerText = data.summary.totalDistance.toLocaleString() + ' km';
                        
                        const rate = data.summary.completionRate + '%';
                        if (rateBadge) rateBadge.innerText = rate;
                        if (progressBar) {
                            progressBar.style.width = rate;
                        }
                    }

                    // Update Day/Night
                    if (data.dayNight) {
                        dayPatrolsEl.innerText = data.dayNight.day.toLocaleString();
                        nightPatrolsEl.innerText = data.dayNight.night.toLocaleString();
                    }

                    // Populate Type Breakdown
                    let typeHtml = '';
                    if (data.breakdown && data.breakdown.length > 0) {
                        data.breakdown.forEach(item => {
                            typeHtml += `
                                <tr>
                                    <td class="ps-3"><span class="badge bg-light text-dark border">${item.type}</span></td>
                                    <td class="text-center fw-bold">${item.count}</td>
                                    <td class="text-end pe-3 text-muted">${item.total_distance_km} km</td>
                                </tr>
                            `;
                        });
                    } else {
                        typeHtml = '<tr><td colspan="3" class="text-center py-3 text-muted">No data available</td></tr>';
                    }
                    typeTable.innerHTML = typeHtml;

                    // Populate Top Sites
                    let sitesHtml = '';
                    if (data.topSites && data.topSites.length > 0) {
                        data.topSites.forEach((site, index) => {
                            sitesHtml += `
                                <tr>
                                    <td class="ps-3 text-muted">${index + 1}</td>
                                    <td><strong>${site.site_name}</strong></td>
                                    <td class="text-end pe-3 fw-bold text-success">${site.distance_km} km</td>
                                </tr>
                            `;
                        });
                    } else {
                        sitesHtml = '<tr><td colspan="3" class="text-center py-3 text-muted">No records found</td></tr>';
                    }
                    sitesTable.innerHTML = sitesHtml;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                typeTable.innerHTML = '<tr><td colspan="3" class="text-center py-3 text-danger">Error loading data</td></tr>';
                sitesTable.innerHTML = '<tr><td colspan="3" class="text-center py-3 text-danger">Error loading data</td></tr>';
            });
    });

    // Load Total Incidents when modal opens
    document.getElementById('totalIncidentsModal')?.addEventListener('show.bs.modal', function() {
        const totalEl = document.getElementById('modalIncidentsTotal');
        const pendingEl = document.getElementById('modalIncidentsPending');
        const resolvedEl = document.getElementById('modalIncidentsResolved');
        const rateEl = document.getElementById('modalIncidentsRate');
        const typeTable = document.getElementById('modalIncidentsTypeTable');
        const recentTable = document.getElementById('modalIncidentsRecentTable');
        const siteTable = document.getElementById('modalIncidentsSiteTable');

        // Show loading states
        if (totalEl) totalEl.innerText = '--';
        if (pendingEl) pendingEl.innerText = '--';
        if (resolvedEl) resolvedEl.innerText = '--';
        if (rateEl) rateEl.innerText = '--%';
        
        if (rateEl) rateEl.innerText = '--%';
        
        recentTable.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-danger me-2"></div>Loading...</td></tr>';
        siteTable.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-danger me-2"></div>Loading...</td></tr>';

        const filters = window.getCurrentFilters ? window.getCurrentFilters() : '';
        fetch(`/api/incidents-details?${filters}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update Summary
                    if (data.summary) {
                        totalEl.innerText = data.summary.total.toLocaleString();
                        pendingEl.innerText = data.summary.pending.toLocaleString();
                        resolvedEl.innerText = data.summary.resolved.toLocaleString();
                        rateEl.innerText = data.summary.rate.toFixed(1) + '%';
                    }


                    // Populate Recent Incidents
                    let recentHtml = '';
                    if (data.recent && data.recent.length > 0) {
                        data.recent.forEach(item => {
                            const statusBadge = item.statusFlag == 1 
                                ? '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Resolved</span>'
                                : '<span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">Pending</span>';
                            
                            // Format Type nicely
                            const typeLabel = (item.type || '').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                            
                            // Split date and time if possible
                            const dateParts = (item.dateFormat || '').split(' ');
                            const dateStr = dateParts[0] || item.dateFormat;
                            const timeStr = dateParts.length > 1 ? dateParts[1] : '';

                            recentHtml += `
                                <tr style="cursor: pointer;" onclick="window.openIncidentDetail(${item.id})">
                                    <td class="ps-3 text-muted small text-nowrap">
                                        <div class="fw-bold text-dark">${dateStr}</div>
                                        <div class="text-muted extra-small">${timeStr}</div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-primary d-block mb-1" style="font-size: 0.85rem;">${typeLabel}</span>
                                        <!-- Mobile Context (Visible only on small screens) -->
                                        <div class="d-md-none text-muted extra-small">
                                            <div class="mb-1"><i class="bi bi-geo-alt-fill text-secondary me-1"></i>${item.site_name}</div>
                                            <div><i class="bi bi-person-fill text-secondary me-1"></i>${item.guard_name}</div>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell"><small class="text-dark fw-medium">${item.site_name}</small></td>
                                    <td class="d-none d-md-table-cell"><small>${item.guard_name}</small></td>
                                    <td class="text-center">${statusBadge}</td>
                                </tr>
                            `;
                        });
                    } else {
                        recentHtml = '<tr><td colspan="5" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-4 d-block mb-2"></i>No recent incidents recorded</td></tr>';
                    }
                    recentTable.innerHTML = recentHtml;

                    // Populate Site Performance
                    let siteHtml = '';
                    if (data.sites && data.sites.length > 0) {
                        data.sites.forEach(item => {
                            siteHtml += `
                                <tr>
                                    <td class="ps-3"><strong>${item.site_name}</strong></td>
                                    <td class="text-center fw-bold">${item.incident_count}</td>
                                    <td class="text-center text-success">${item.resolved_count}</td>
                                    <td class="text-center text-danger">${item.pending_count}</td>
                                    <td class="text-end pe-3">
                                        <div class="d-flex align-items-center justify-content-end">
                                            <span class="me-2 fw-bold ${item.resolution_percentage > 70 ? 'text-success' : 'text-warning'}">${item.resolution_percentage}%</span>
                                            <div class="progress" style="width: 60px; height: 6px;">
                                                <div class="progress-bar ${item.resolution_percentage > 70 ? 'bg-success' : 'bg-warning'}" role="progressbar" style="width: ${item.resolution_percentage}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        siteHtml = '<tr><td colspan="5" class="text-center py-3 text-muted">No site data available</td></tr>';
                    }
                    siteTable.innerHTML = siteHtml;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const err = '<tr><td colspan="5" class="text-center py-4 text-danger">Failed to load data</td></tr>';
                typeTable.innerHTML = err;
                recentTable.innerHTML = err;
                siteTable.innerHTML = err;
            });
    });

    // Load Total Distance Details when modal opens
    document.getElementById('totalDistanceModal')?.addEventListener('show.bs.modal', function() {
        const listContainer = document.getElementById('totalDistanceList');
        const totalSpan = document.getElementById('modalDistanceTotal');
        const avgSpan = document.getElementById('modalDistanceAvg');
        const guardsSpan = document.getElementById('modalDistanceGuards');
        
        listContainer.innerHTML = '<tr><td colspan="4" class="text-center py-5"><div class="spinner-border text-info"></div><p class="text-muted mt-2 small">Analyzing performance data...</p></td></tr>';
        
        const filters = window.getCurrentFilters ? window.getCurrentFilters() : '';
        fetch(`/api/distance-details?${filters}`)
            .then(response => response.json())
            .then(data => {
                if (data.summary) {
                    if (totalSpan) totalSpan.innerText = (data.summary.total_distance_km || 0).toLocaleString(undefined, {minimumFractionDigits: 1, maximumFractionDigits: 1}) + ' km';
                    if (avgSpan) avgSpan.innerText = (data.summary.avg_distance_km || 0).toLocaleString(undefined, {minimumFractionDigits: 1, maximumFractionDigits: 1}) + ' km';
                    if (guardsSpan) guardsSpan.innerText = (data.summary.active_guards || 0).toLocaleString();
                }
                
                let html = '';
                const totalDist = data.summary?.total_distance_km || 0;
                
                if (data.breakdown && data.breakdown.length > 0) {
                    data.breakdown.forEach((item, index) => {
                        const dist = parseFloat(item.total_distance_km || 0);
                        const pct = totalDist > 0 ? (dist / totalDist * 100).toFixed(1) : 0;
                        const initial = (item.guard_name || 'U').charAt(0).toUpperCase();
                        
                        html += `
                            <tr>
                                <td class="ps-3 text-muted small">${index + 1}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-info text-white rounded-circle d-flex justify-content-center align-items-center me-2" style="width: 32px; height: 32px; font-size: 0.8rem; flex-shrink: 0;">
                                            ${initial}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark small">${item.guard_name || 'Unknown'}</div>
                                            <div class="text-muted" style="font-size: 0.65rem;"><i class="bi bi-telephone text-info me-1"></i>${item.phone || 'No phone'}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small text-dark fw-bold"><i class="bi bi-geo-alt-fill text-danger me-1"></i>${item.site_name || 'N/A'}</div>
                                    <div class="text-muted extra-small" style="font-size: 0.65rem;">${item.range_name || ''}</div>
                                </td>
                                <td class="pe-3">
                                    <div class="d-flex flex-column align-items-end">
                                        <div class="fw-bold text-info small mb-1">${dist.toFixed(2)} km</div>
                                        <div class="progress" style="width: 80px; height: 4px; background-color: #e9ecef;">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: ${pct}%"></div>
                                        </div>
                                        <div class="extra-small text-muted mt-1" style="font-size: 0.6rem;">${pct}% of total</div>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="4" class="text-center py-5 text-muted small"><i class="bi bi-info-circle me-1"></i>No patrolling activity found for this period</td></tr>';
                }
                listContainer.innerHTML = html;
            })
            .catch(error => {
                console.error('Error fetching distance details:', error);
                listContainer.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-danger small"><i class="bi bi-exclamation-triangle me-1"></i>Error loading distance breakdown. Please try again later.</td></tr>';
            });
    });

    // Load Attendance Details when modal opens
    document.getElementById('attendanceDetailsModal')?.addEventListener('show.bs.modal', function() {
        const listContainer = document.getElementById('attendanceDetailsList');
        const rateSpan = document.getElementById('modalAttendanceRate');
        const presentSpan = document.getElementById('modalAttendancePresent');
        const staffSpan = document.getElementById('modalAttendanceStaff');
        
        listContainer.innerHTML = '<tr><td colspan="4" class="text-center py-5"><div class="spinner-border text-warning"></div><p class="text-muted mt-2 small">Fetching consistency data...</p></td></tr>';
        
        const filters = window.getCurrentFilters ? window.getCurrentFilters() : '';
        
        // Update "Full Ledger" button with current filters
        const fullLedgerBtn = document.getElementById('fullAttendanceLedgerBtn');
        if (fullLedgerBtn) {
            fullLedgerBtn.href = `/attendance/summary?${filters}`;
        }

        fetch(`/api/attendance-details?${filters}`)
            .then(response => response.json())
            .then(data => {
                if (data.summary) {
                    if (rateSpan) rateSpan.innerText = (data.summary.attendance_rate || 0).toFixed(1) + '%';
                    if (presentSpan) presentSpan.innerText = (data.summary.present_count || 0).toLocaleString();
                    if (staffSpan) staffSpan.innerText = (data.summary.active_guards || 0).toLocaleString();
                }
                
                let html = '';
                const rawDays = data.summary?.days_in_range || 1;
                const daysInRange = Math.round(rawDays);

                if (data.breakdown && data.breakdown.length > 0) {
                    data.breakdown.forEach((item, index) => {
                        const present = parseInt(item.present_days || 0);
                        const pct = (present / daysInRange * 100).toFixed(0);
                        const late = parseInt(item.late_days || 0);
                        const initial = (item.guard_name || 'U').charAt(0).toUpperCase();
                        
                        // Format average late time: mins -> Hrs/Mins
                        let lateStr = '';
                        if (late > 0) {
                            const min = Math.round(item.avg_late_mins || 0);
                            if (min >= 60) {
                                const h = Math.floor(min / 60);
                                const m = min % 60;
                                lateStr = `<div class="text-muted extra-small">Avg: ${h}h ${m > 0 ? m + 'm' : ''} late</div>`;
                            } else {
                                lateStr = `<div class="text-muted extra-small">Avg: ${min} min late</div>`;
                            }
                        } else {
                            lateStr = '<div class="text-success extra-small">Punctual</div>';
                        }
                        
                        html += `
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-warning text-dark fw-bold rounded-circle d-flex justify-content-center align-items-center me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                            ${initial}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark small">${item.guard_name || 'Unknown'}</div>
                                            <div class="text-muted extra-small" style="font-size: 0.65rem;">${item.phone || 'No contact'}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small fw-bold text-dark">${item.site_name || 'N/A'}</div>
                                    <div class="text-muted extra-small" style="font-size: 0.65rem;">${item.range_name || ''}</div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center">
                                        <span class="badge ${pct > 80 ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning-emphasis'} border px-2 mb-1">
                                            ${present} / ${daysInRange} Days
                                        </span>
                                        <div class="progress" style="width: 50px; height: 4px;">
                                            <div class="progress-bar ${pct > 80 ? 'bg-success' : 'bg-warning'}" style="width: ${pct}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="pe-3 text-end">
                                    <div class="text-dark fw-bold small">${late} Times</div>
                                    ${lateStr}
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="4" class="text-center py-5 text-muted small"><i class="bi bi-info-circle me-1"></i>No attendance records found for this period</td></tr>';
                }
                listContainer.innerHTML = html;
            })
            .catch(error => {
                console.error('Error fetching attendance details:', error);
                listContainer.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-danger small"><i class="bi bi-exclamation-triangle me-1"></i>Error loading attendance data.</td></tr>';
            });
    });
});
</script>

@endpush
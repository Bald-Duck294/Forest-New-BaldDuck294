{{-- Patrol Analytics --}}
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">🚶 Patrol Analytics</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <a href="{{ route('patrol.foot.summary') }}" class="text-decoration-none">
                            <div class="kpi-card p-3 shadow-sm" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                <h6 class="mb-1">Foot Patrols</h6>
                                <h4 class="mb-0 text-white">{{ $patrolAnalytics['footPatrols'] ?? 0 }}</h4>
                            </div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('patrol.night.summary') }}" class="text-decoration-none">
                            <div class="kpi-card p-3 shadow-sm" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                <h6 class="mb-1">Night Patrols</h6>
                                <h4 class="mb-0 text-white">{{ $patrolAnalytics['nightPatrols'] ?? 0 }}</h4>
                            </div>
                        </a>
                    </div>
                </div>
                @if(($patrolAnalytics['footPatrols'] ?? 0) == 0 && ($patrolAnalytics['nightPatrols'] ?? 0) == 0)
                    <div class="text-center py-2 text-muted">
                        <i class="bi bi-info-circle opacity-50"></i>
                        <small>No patrol activity recorded</small>
                    </div>
                @endif
                <div style="height: 250px;">
                    <canvas id="patrolTypeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">📈 Daily Patrol Trend</h5>
            </div>
            <div class="card-body">
                <div style="height: 310px;">
                    <canvas id="dailyPatrolTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>


@push('modals')
{{-- Modal for Patrol List by Type --}}
<div class="modal fade" id="patrolTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-success text-white" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title" id="patrolTypeModalTitle">🚶 Patrol Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="extra-small text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                <th class="ps-3 py-3" style="width: 50px;">#</th>
                                <th>Guard</th>
                                <th>Location / Range</th>
                                <th class="text-center">Distance</th>
                                <th class="text-end pe-3">Started At</th>
                            </tr>
                        </thead>
                        <tbody id="patrolTypeListBody">
                            <!-- Dynamic content -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
window.patrolAnalyticsData = {
    typeLabels: {!! json_encode($patrolAnalytics['patrolByType']->pluck('type')->toArray()) !!},
    typeCounts: {!! json_encode($patrolAnalytics['patrolByType']->pluck('count')->toArray()) !!},
    typeDistances: {!! json_encode($patrolAnalytics['patrolByType']->pluck('total_distance_km')->toArray()) !!},
    dailyLabels: {!! json_encode($patrolAnalytics['dailyTrend']->pluck('date')->toArray()) !!},
    dailyCounts: {!! json_encode($patrolAnalytics['dailyTrend']->pluck('patrol_count')->toArray()) !!},
    dailyDistances: {!! json_encode($patrolAnalytics['dailyTrend']->pluck('distance_km')->toArray()) !!}
};

/**
 * Shows a list of patrols filtered by type from chart click
 */
window.showPatrolsByType = async function(type, titleLabel) {
    const modalEl = document.getElementById('patrolTypeModal');
    const title = document.getElementById('patrolTypeModalTitle');
    const body = document.getElementById('patrolTypeListBody');
    
    title.innerText = titleLabel || `Patrols: ${type}`;
    body.innerHTML = '<tr><td colspan="5" class="text-center py-5"><div class="spinner-border text-success"></div><p class="text-muted mt-2 small">Loading patrol data...</p></td></tr>';
    
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    try {
        const globalFilters = window.getCurrentFilters ? window.getCurrentFilters() : '';
        const response = await fetch(`/api/patrols-by-type?type=${encodeURIComponent(type)}&${globalFilters}`);
        const data = await response.json();
        
        if (!data.patrols || data.patrols.length === 0) {
            body.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted small"><i class="bi bi-info-circle me-1"></i>No patrols found for this selection</td></tr>';
            return;
        }

        body.innerHTML = data.patrols.map((patrol, index) => {
            const initial = (patrol.guard_name || 'G').charAt(0).toUpperCase();
            return `
                <tr>
                    <td class="ps-3 text-muted small">${index + 1}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-success text-white rounded-circle d-flex justify-content-center align-items-center me-2" style="width: 32px; height: 32px; font-size: 0.8rem; flex-shrink: 0;">
                                ${initial}
                            </div>
                            <div>
                                <div class="fw-bold text-dark small">${patrol.guard_name || 'Unknown'}</div>
                                <div class="text-muted extra-small" style="font-size: 0.65rem;">${patrol.phone || 'No contact'}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="small fw-bold text-dark">${patrol.site_name || 'N/A'}</div>
                        <div class="text-muted extra-small" style="font-size: 0.65rem;">${patrol.range_name || ''}</div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                            ${patrol.distance_km} km
                        </span>
                    </td>
                    <td class="text-end pe-3">
                        <div class="small text-dark fw-bold">${patrol.formatted_start}</div>
                        <div class="text-muted extra-small">${patrol.duration}</div>
                    </td>
                </tr>
            `;
        }).join('');
    } catch (error) {
        console.error('Error fetching patrols:', error);
        body.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-danger small"><i class="bi bi-exclamation-triangle me-1"></i>Error loading data.</td></tr>';
    }
};
</script>
@endpush


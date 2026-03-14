{{-- Incident Tracking Section --}}
<div class="row g-4 mb-4">
    {{-- Left Card: Status Tracking (8 Column) --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header text-white" style="background-color: #ff7675; border-radius: 12px 12px 0 0;">
                <h6 class="mb-0 py-1"><i class="bi bi-alarm-fill me-2"></i>Incident Status Tracking</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 mb-4 align-items-center">
                    {{-- Critical KPI --}}
                    <div class="col-md-4">
                        <div class="p-4 text-center rounded-4 shadow-sm" style="background-color: #ff9999; border: 1px solid #ff7675;">
                            <div class="text-white small fw-bold text-uppercase mb-2">Critical Pending</div>
                            <div class="display-4 fw-bold text-white">{{ isset($incidentTracking['criticalIncidents']) ? count($incidentTracking['criticalIncidents']) : 0 }}</div>
                        </div>
                    </div>
                    {{-- Status Chart --}}
                    <div class="col-md-8">
                        <div style="height:150px;">
                            <canvas id="incidentStatusChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Recent Critical Alert --}}
                @if(isset($incidentTracking['criticalIncidents']) && count($incidentTracking['criticalIncidents']) > 0)
                <div class="alert mb-4 border-0" style="background-color: #fef9e7; border-left: 4px solid #f1c40f !important;">
                    <div class="fw-bold text-dark mb-2">Recent Critical Incidents Requiring Attention:</div>
                    <ul class="mb-0 ps-3">
                        @foreach($incidentTracking['criticalIncidents']->take(3) as $incident)
                        <li class="mb-1" onclick="window.openIncidentDetail({{ $incident->id }})" style="cursor:pointer; font-size: 1.1rem; color: #b09115;">
                            <span class="fw-bold" style="color: #b09115; font-style: italic;">{{ $incident->type }}</span> 
                            <span class="text-dark">at - </span> 
                            <span class="text-muted">{{ \Carbon\Carbon::parse($incident->dateFormat)->format('Y-m-d') }}</span> 
                            (<span class="fw-bold" style="color: #4A90E2;">{{ $incident->guard_name }}</span>)
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Resolution Table --}}
                <div class="table-responsive mt-2">
                    <table class="table align-middle sortable-table">
                        <thead style="background-color: #f8f9fa;">
                            <tr class="small text-uppercase text-muted" style="font-size: 0.75rem;">
                                <th class="text-center py-3" width="70">SR.NO</th>
                                <th class="py-3">SITE NAME <i class="bi bi-arrow-down-up ms-1" style="font-size: 0.6rem;"></i></th>
                                <th class="text-center py-3">TOTAL <i class="bi bi-arrow-down-up ms-1" style="font-size: 0.6rem;"></i></th>
                                <th class="text-center py-3">RESOLVED <i class="bi bi-arrow-down-up ms-1" style="font-size: 0.6rem;"></i></th>
                                <th class="text-center py-3">PENDING <i class="bi bi-arrow-down-up ms-1" style="font-size: 0.6rem;"></i></th>
                                <th class="text-center py-3 border-end-0">RESOLUTION % <i class="bi bi-arrow-down-up ms-1" style="font-size: 0.6rem;"></i></th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse(isset($incidentTracking['incidentsBySite']) ? $incidentTracking['incidentsBySite'] : [] as $site)
                                <tr onclick="window.showIncidentsByType('all', 'Incidents at {{ addslashes($site->site_name) }}', {site_name: '{{ addslashes($site->site_name) }}'})" style="cursor:pointer" class="hover-bg-light border-bottom">
                                    <td class="text-center py-3 text-muted">{{ $loop->iteration }}</td>
                                    <td class="fw-bold text-muted py-3">{{ $site->site_name }}</td>
                                    <td class="text-center py-3 fw-bold text-muted">{{ $site->incident_count }}</td>
                                    <td class="text-center py-3">
                                        <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle shadow-sm" style="width: 24px; height: 24px; font-size: 0.75rem; font-weight: bold;">
                                            {{ $site->resolved_count }}
                                        </div>
                                    </td>
                                    <td class="text-center py-3">
                                        <div class="d-inline-flex align-items-center justify-content-center bg-warning text-white rounded-circle shadow-sm" style="width: 24px; height: 24px; font-size: 0.75rem; font-weight: bold;">
                                            {{ $site->pending_count }}
                                        </div>
                                    </td>
                                    <td class="text-center py-3 fw-bold text-muted">{{ $site->resolution_percentage }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted small">No incident records found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Card: Incident Types (4 Column) --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-auto overflow-hidden" style="border-radius: 12px;">
            <div class="card-header text-white" style="background-color: #00cec9; border-bottom: none;">
                <h6 class="mb-0 py-1 fw-bold">Incident Types</h6>
            </div>
            <div class="card-body p-6">
                <div style="height:350px;">
                    <canvas id="incidentTypeChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('modals')
{{-- Modal for Incident List by Type/Site --}}
<div class="modal fade" id="incidentTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" style="max-width: 650px;">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white py-3">
                <h5 class="modal-title fw-bold" id="incidentTypeModalTitle">📌 Incidents List</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="extra-small text-uppercase">
                                <th class="ps-3" style="width: 50px;">#</th>
                                <th>Type</th>
                                <th>Guard</th>
                                <th>Location</th>
                                <th class="text-end pe-3">Date</th>
                            </tr>
                        </thead>
                        <tbody id="incidentTypeListBody">
                            <!-- Dynamic content -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal for Single Incident Detail --}}
<div class="modal fade" id="incidentDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 650px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-primary text-white py-3" style="border-radius: 16px 16px 0 0;">
                <h5 class="modal-title fw-bold"><i class="bi bi-shield-exclamation me-2"></i>Incident Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div id="incidentDetailContent" class="modal-body bg-light-subtle p-0">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Fetching details...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
/**
 * Shows a list of incidents filtered by type, site, or status
 */
window.showIncidentsByType = async function(key, label, extraParams = {}) {
    const modalEl = document.getElementById('incidentTypeModal');
    const title = document.getElementById('incidentTypeModalTitle');
    const body = document.getElementById('incidentTypeListBody');
    
    // Fallback values
    const cleanKey = (key === undefined || key === null || key === '') ? 'all' : key;
    const cleanLabel = (label === undefined || label === null || label === '' || label === ' Details') ? 'Incident Details' : label;
    
    title.innerText = cleanLabel;
    body.innerHTML = '<tr><td colspan="5" class="text-center py-5"><div class="spinner-border text-info"></div><p class="text-muted mt-2 small">Fetching incidents...</p></td></tr>';
    
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    try {
        // Build URL with canonical analytics filters
        const globalFilters = window.getCurrentFilters ? window.getCurrentFilters() : '';
        const baseUrl = "{{ route('incidents.by-type', ['type' => ':type']) }}".replace(':type', encodeURIComponent(cleanKey)).replace(/\/$/, ""); 
        let url = `${baseUrl}?source=patrol_logs&${globalFilters}&`;
        
        Object.keys(extraParams).forEach(k => {
            url += `${k}=${encodeURIComponent(extraParams[k])}&`;
        });
        
        const response = await fetch(url);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const data = await response.json();
        
        if (!data.incidents || data.incidents.length === 0) {
            body.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted small">No incidents found for this selection</td></tr>';
            return;
        }

        body.innerHTML = data.incidents.map((inc, index) => `
            <tr onclick="openIncidentDetail(${inc.id})" style="cursor:pointer" class="hover-bg-info-subtle">
                <td class="ps-3 text-muted small">${index + 1}</td>
                <td><span class="badge bg-light text-info-emphasis border border-info-subtle">${(inc.type || '').replace(/_/g, ' ').toUpperCase()}</span></td>
                <td>
                    <div class="fw-bold text-dark small">${inc.guard || 'N/A'}</div>
                </td>
                <td>
                    <div class="small text-muted">${inc.beat_name || 'N/A'}</div>
                    <div class="extra-small text-muted" style="font-size: 0.7rem;">${inc.range_name || ''}</div>
                </td>
                <td class="text-end pe-3">
                    <div class="small fw-bold">${new Date(inc.created_at).toLocaleDateString()}</div>
                    <div class="extra-small text-muted" style="font-size: 0.7rem;">${new Date(inc.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                </td>
            </tr>
        `).join('');
    } catch (err) {
        console.error('Error fetching incidents:', err);
        body.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-5 small">Error: ${err.message}</td></tr>`;
    }
}

/**
 * Opens a modal with details for a single incident
 */
window.openIncidentDetail = async function(id) {
    const listModalEl = document.getElementById('incidentTypeModal');
    const existingListModal = bootstrap.Modal.getInstance(listModalEl);
    if (existingListModal) existingListModal.hide();
    
    const detailModalEl = document.getElementById('incidentDetailModal');
    const detailModal = new bootstrap.Modal(detailModalEl);
    const content = document.getElementById('incidentDetailContent');
    
    content.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" style="width: 3.5rem; height: 3.5rem;"></div><p class="mt-3 text-muted fw-bold">Gathering incident evidence...</p></div>';
    
    if (!id) {
        content.innerHTML = '<div class="alert alert-warning border-0 shadow-sm">Invalid Incident ID. Unable to load details.</div>';
        return;
    }
    
    detailModal.show();

    const statusMap = {
        0: { label: 'Pending (Supervisor)', color: 'warning', icon: 'bi-hourglass-split' },
        1: { label: 'Resolved', color: 'success', icon: 'bi-check-circle-fill' },
        2: { label: 'Ignored', color: 'secondary', icon: 'bi-slash-circle' },
        3: { label: 'Escalated (Admin)', color: 'danger', icon: 'bi-arrow-up-circle-fill' },
        4: { label: 'Pending (Admin)', color: 'warning', icon: 'bi-person-badge-fill' },
        5: { label: 'Escalated (Client)', color: 'danger', icon: 'bi-megaphone-fill' },
        6: { label: 'Reverted', color: 'info', icon: 'bi-arrow-left-right' }
    };

    try {
        const baseUrl = "{{ route('incidents.details', ['id' => ':id']) }}".replace(':id', id);
        const response = await fetch(baseUrl);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const data = await response.json();
        
        if (data.error) throw new Error(data.error);

        const inc = data.incident || {};
        const comments = data.comments || [];
        const typeText = (inc.type || 'Incident').replace(/_/g, ' ').toUpperCase();
        const status = statusMap[inc.statusFlag] || { label: 'Pending', color: 'warning', icon: 'bi-hourglass' };
        
        const getRelativeTime = (dateString) => {
            const date = new Date(dateString);
            const now = new Date();
            const diffInSeconds = Math.floor((now - date) / 1000);
            if (diffInSeconds < 60) return 'Just now';
            const diffInMinutes = Math.floor(diffInSeconds / 60);
            if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
            const diffInHours = Math.floor(diffInMinutes / 60);
            if (diffInHours < 24) return `${diffInHours}h ago`;
            const diffInDays = Math.floor(diffInHours / 24);
            if (diffInDays < 30) return `${diffInDays}d ago`;
            return date.toLocaleDateString();
        };

        content.innerHTML = `
            <!-- Header Badges -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex gap-2">
                    <span class="badge rounded-pill bg-${status.color} shadow-sm px-3 py-2 d-flex align-items-center">
                        <i class="bi ${status.icon} me-2"></i> ${status.label}
                    </span>
                    <span class="badge rounded-pill bg-dark shadow-sm px-3 py-2 d-flex align-items-center text-white">
                        <i class="bi ${inc.session === 'Night' ? 'bi-moon-stars-fill' : 'bi-sun-fill'} me-2"></i> ${inc.session || 'Day'} Patrol
                    </span>
                </div>
                <div class="text-end">
                    <div class="text-muted extra-small text-uppercase fw-bold" style="font-size: 0.6rem;">Priority</div>
                    <div class="fw-bold text-${inc.priority === 'High' ? 'danger' : (inc.priority === 'Medium' ? 'warning' : 'success')}">${inc.priority || 'Low'}</div>
                </div>
            </div>

            <!-- Primary Content Card -->
            <div class="card border-0 bg-white shadow-sm overflow-hidden mb-4" style="border-radius: 12px;">
                <div class="p-4 border-bottom bg-light-subtle d-flex justify-content-between align-items-start">
                    <div>
                        <h4 class="fw-bold mb-1 text-primary d-flex align-items-center">
                            <i class="bi bi-shield-exclamation me-2"></i> ${typeText}
                        </h4>
                        <div class="d-flex align-items-center mt-2">
                            <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                            <span class="text-dark fw-semibold">${inc.beat_name || 'Unknown Beat'}</span>
                            <span class="px-2 text-muted">/</span>
                            <span class="text-muted small">${inc.range_name || 'No Range'}</span>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-dark">${new Date(inc.created_at).toLocaleString('en-IN', { dateStyle: 'medium', timeStyle: 'short' })}</div>
                        <div class="text-primary small fw-semibold">${getRelativeTime(inc.created_at)}</div>
                    </div>
                </div>

                <div class="row g-0">
                    <!-- Reporter Column -->
                    <div class="col-12 col-md-5 border-bottom border-md-bottom-0 border-md-end">
                        <div class="p-4 bg-light-subtle h-100">
                            <h6 class="fw-bold text-muted text-uppercase mb-3 d-flex align-items-center" style="font-size: 0.7rem; letter-spacing: 1px;">
                                <i class="bi bi-person-badge-fill me-2 text-primary"></i> Reporter Details
                            </h6>
                            
                            <!-- Guard Profile -->
                            <div class="d-flex align-items-center mb-4 p-3 bg-white rounded-4 shadow-sm border">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm flex-shrink-0" style="width: 45px; height: 45px; font-size: 1.1rem; font-weight: bold;">
                                    ${(inc.guard_name || 'A').charAt(0)}
                                </div>
                                <div class="overflow-hidden">
                                    <div class="fw-bold text-dark text-truncate">${inc.guard_name || 'Anonymous Guard'}</div>
                                    <div class="text-primary small text-truncate"><i class="bi bi-telephone-fill me-1"></i> ${inc.guard_contact || 'N/A'}</div>
                                </div>
                            </div>

                            <!-- Location / Coordinates -->
                            <div class="mb-0">
                                <div class="text-muted extra-small mb-1 font-monospace text-uppercase">Incident Location</div>
                                <div class="bg-white p-2 rounded-3 border shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <code class="text-dark small fw-bold">${inc.lat ? `${parseFloat(inc.lat).toFixed(5)}, ${parseFloat(inc.lng).toFixed(5)}` : 'No GPS Data'}</code>
                                        ${inc.lat ? `<a href="https://www.google.com/maps?q=${inc.lat},${inc.lng}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.7rem;"><i class="bi bi-map-fill me-1"></i>Map</a>` : ''}
                                    </div>
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <span class="extra-small text-muted">Accuracy</span>
                                        <span class="extra-small text-success fw-bold">High</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Evidence Column -->
                    <div class="col-12 col-md-7">
                        <div class="p-4 h-100">
                            <h6 class="fw-bold text-muted text-uppercase mb-3 d-flex align-items-center" style="font-size: 0.7rem; letter-spacing: 1px;">
                                <i class="bi bi-camera-fill me-2 text-primary"></i> Incident Evidence
                            </h6>
                            <div class="rounded-4 overflow-hidden border shadow-sm position-relative bg-light d-flex align-items-center justify-content-center" style="min-height: 200px; max-height: 300px;">
                                ${inc.photo ? 
                                    `<img src="${inc.photo.startsWith('data:') ? inc.photo : '/storage/'+inc.photo}" class="img-fluid w-100 h-100 object-fit-contain" style="max-height: 280px;" onerror="this.src='https://placehold.co/600x400?text=Evidence+Missing'">
                                     <div class="position-absolute bottom-0 end-0 p-2">
                                         <button class="btn btn-dark btn-sm rounded-pill opacity-75 shadow" onclick="window.open('${inc.photo.startsWith('data:') ? inc.photo : '/storage/'+inc.photo}', '_blank')">
                                             <i class="bi bi-arrows-fullscreen me-1"></i> Expand
                                         </button>
                                     </div>` : 
                                    `<div class="text-center p-4">
                                        <div class="mb-2 text-muted opacity-25"><i class="bi bi-image fs-1"></i></div>
                                        <span class="small text-muted fw-medium">No photo evidence attached</span>
                                    </div>`
                                }
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <h6 class="fw-bold text-muted text-uppercase mb-3 d-flex align-items-center" style="font-size: 0.7rem; letter-spacing: 1px;">
                    <i class="bi bi-chat-left-dots-fill me-2 text-primary"></i> Observations
                </h6>
                <div class="p-4 bg-white border-start border-4 border-primary rounded-end-4 shadow-sm">
                    <p class="text-dark mb-0 fs-6" style="line-height: 1.6;">
                        "${inc.notes || 'No specific observations provided.'}"
                    </p>
                </div>
            </div>

            <div class="mt-4 pt-4 border-top">
                <h6 class="fw-bold text-muted text-uppercase mb-3 d-flex align-items-center" style="font-size: 0.7rem; letter-spacing: 1px;">
                    <i class="bi bi-journal-text me-2 text-secondary"></i> Supervisor Timeline (${comments.length})
                </h6>
                <div class="timeline-container ps-2">
                    ${comments.length > 0 ? comments.map(c => `
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0 me-3 mt-1">
                                <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                    <i class="bi bi-person"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark small">${c.user_name}</span>
                                    <span class="text-muted extra-small">${getRelativeTime(c.created_at)}</span>
                                </div>
                                <div class="p-3 bg-white border rounded-4 rounded-tl-0 shadow-sm small text-muted">
                                    ${c.comment}
                                </div>
                            </div>
                        </div>
                    `).join('') : '<div class="text-muted small italic p-3 text-center bg-white border rounded-4 shadow-sm">No supervisor comments on this incident.</div>'}
                </div>
            </div>
        `;
    } catch (err) {
        content.innerHTML = `<div class="alert alert-danger shadow-sm border-0 d-flex align-items-center rounded-4">
            <i class="bi bi-exclamation-triangle-fill me-3 fs-3"></i>
            <div>
                <div class="fw-bold">Failed to load details</div>
                <div class="small opacity-75">${err.message}</div>
            </div>
        </div>`;
    }
}
</script>
@endpush

@php
    $statusMap = [
        0 => 'Pending (Supervisor)',
        1 => 'Resolved',
        2 => 'Ignored',
        3 => 'Escalated (Admin)',
        4 => 'Pending (Admin)',
        5 => 'Escalated (Client)',
        6 => 'Reverted'
    ];
    
    $statusLabels = []; $statusData = [];
    foreach (isset($incidentTracking['statusDistribution']) ? $incidentTracking['statusDistribution'] : [] as $flag => $count) {
        if ($count > 0) {
            $statusLabels[] = $statusMap[$flag] ?? 'Unknown';
            $statusData[] = $count;
        }
    }
    
    $typeLabels = $incidentTracking['incidentTypes']->pluck('type')->map(fn($t) => ucwords(str_replace('_', ' ', $t)))->values()->toArray();
    $typeData = $incidentTracking['incidentTypes']->pluck('count')->values()->toArray();
@endphp

<script>
window.incidentTrackingData = {
    statusLabels: {!! json_encode($statusLabels) !!},
    statusData: {!! json_encode($statusData) !!},
    typeLabels: {!! json_encode($typeLabels) !!},
    typeKeys: {!! json_encode($incidentTracking['incidentTypes']->pluck('type')->values()) !!},
    typeData: {!! json_encode($typeData) !!}
};
</script>
{{-- ================= GUARD DETAIL MODAL ================= --}}
{{-- Higher z-index so it appears ON TOP of Active Guards Details when opened from that modal --}}
<div class="modal fade guard-intelligence-modal" id="guardDetailModal" tabindex="-1" style="z-index: 1000002 !important;">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            {{-- HEADER --}}
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-person-badge me-2"></i> Guard Intelligence Profile
                </h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            {{-- BODY --}}
            <div class="modal-body">
                <div id="guardDetailContent" class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-3">Loading guard data…</p>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ================= LEAFLET ================= --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let guardMapInstance = null;

/* ================= GLOBAL CLICK HANDLER ================= */
// Works for any guard name link anywhere on the page
document.addEventListener('click', function (e) {
    // Check multiple ways to identify guard links
    let link = e.target.closest('.guard-name-link, [data-guard-id], [data-guard-name]');
    
    // Also check if clicked on guard name in tables/cells
    if (!link) {
        const cell = e.target.closest('td, .guard-name, [class*="guard"]');
        if (cell) {
            const guardId = cell.dataset.guardId || cell.getAttribute('data-guard-id');
            if (guardId) {
                link = cell;
            }
        }
    }
    
    // Check if it's a link with guard ID in href
    if (!link && e.target.tagName === 'A') {
        const href = e.target.href || e.target.getAttribute('href');
        const guardMatch = href?.match(/guard[_-]?details?\/(\d+)/i);
        if (guardMatch) {
            link = e.target;
        }
    }
    
    if (!link) return;

    e.preventDefault();
    e.stopPropagation();

    // Get guard ID from various possible attributes
    const guardId = link.dataset.guardId || 
                    link.getAttribute('data-guard-id') || 
                    link.closest('[data-guard-id]')?.dataset.guardId ||
                    link.href?.match(/guard[_-]?details?\/(\d+)/i)?.[1] ||
                    link.href?.match(/\/guard[_-]?details?\/(\d+)/i)?.[1];

    if (!guardId) {
        console.warn('Guard ID not found');
        return;
    }

    const modal = new bootstrap.Modal(document.getElementById('guardDetailModal'));
    modal.show();

    const content = document.getElementById('guardDetailContent');
    content.innerHTML = loadingBlock();

    // Collect current filter parameters from global filters
    const params = new URLSearchParams();
    
    // Prioritize IDs as they are unique to the global filter form
    const startInput = document.getElementById('startDateInput');
    const endInput = document.getElementById('endDateInput');
    
    const startDate = startInput ? startInput.value : document.querySelector('[name="start_date"]')?.value;
    const endDate = endInput ? endInput.value : document.querySelector('[name="end_date"]')?.value;
    const range = document.querySelector('[name="range"]')?.value;
    const beat = document.querySelector('[name="beat"]')?.value;
    const user = document.querySelector('[name="user"]')?.value;
    const guardSearch = document.querySelector('[name="guard_search"]')?.value;

    // Only add filters if they are set (don't send empty values)
    if (startDate) params.append('start_date', startDate);
    if (endDate) params.append('end_date', endDate);
    if (range) params.append('range', range);
    if (beat) params.append('beat', beat);
    if (user) params.append('user', user);
    if (guardSearch) params.append('guard_search', guardSearch);

    // Provide a default period text if dates are missing but display shows them
    if (!startDate && !endDate) {
        const periodText = document.getElementById('globalPeriodBadge')?.innerText || '';
        if (periodText) {
             console.log('Using period from display badge:', periodText);
        }
    }
    fetch(`/api/guard-details/${guardId}?${params}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success || !res.guard) throw 'Invalid response';
            content.innerHTML = renderGuard(res.guard, startDate, endDate);
            setTimeout(() => {
                initGuardMap(res.guard.patrol_paths || []);
                setTimeout(() => {
                    guardMapInstance.invalidateSize(); // ⭐ CRITICAL
                    
                    // Initialize fullscreen button after map is loaded
                    setTimeout(() => {
                        initGuardMapFullscreen();
                    }, 100);
                }, 200);
            }, 300);
        })
        .catch(err => {
            console.error(err);
            content.innerHTML = `<div class="alert alert-danger">Unable to load guard profile. Please try again.</div>`;
        });
});

/* ================= UI ================= */
function loadingBlock() {
    return `
        <div class="py-5 text-center">
            <div class="spinner-border text-primary"></div>
            <p class="mt-3">Loading guard data…</p>
        </div>
    `;
}

/* ================= RENDER ================= */
function renderGuard(g, startDate = null, endDate = null) {
    const a = g.attendance_stats || {};
    const p = g.patrol_stats || {};
    const i = g.incident_stats || {};
    
    // Format date range display
    let dateRangeText = a.month || 'Selected Period';
    
    if (startDate && endDate) {
        try {
            const start = new Date(startDate);
            const end = new Date(endDate);
            if (!isNaN(start) && !isNaN(end)) {
                dateRangeText = start.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' }) + 
                               ' - ' + 
                               end.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
            }
        } catch (e) {
            console.error('Date parsing error', e);
        }
    }

    return `
    {{-- DATE RANGE DISPLAY --}}
    <div class="alert alert-info d-flex justify-content-between align-items-center mb-3 py-2" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-left: 4px solid #2196f3;">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-calendar-range fs-5"></i>
            <span class="fw-semibold">Data Period:</span>
            <span class="badge bg-primary">${dateRangeText}</span>
        </div>
        <small class="text-muted">
            <i class="bi bi-info-circle"></i> Data filtered according to global filters
        </small>
    </div>
    
    <div class="row g-3">

        {{-- PROFILE --}}
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white fw-semibold">Profile</div>
                <div class="card-body small">
                    <p><strong>Name:</strong> ${g.name || 'NA'}</p>
                    <p><strong>Designation:</strong> ${g.designation || 'NA'}</p>
                    <p><strong>Contact:</strong> ${g.contact || 'NA'}</p>
                    <p><strong>Email:</strong> ${g.email || 'NA'}</p>
                    <p><strong>Department:</strong> ${g.company_name || 'NA'}</p>
                    <p><strong>Range:</strong> ${g.range || 'NA'}</p>
                    <p><strong>Site:</strong> ${g.site || 'NA'}</p>
                    <p><strong>Compartment:</strong> ${g.compartment || 'NA'}</p>
                </div>
            </div>
        </div>

        {{-- ATTENDANCE --}}
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white fw-semibold">
                    Attendance (${a.month ?? 'Last Month'})
                </div>
                <div class="card-body small">
                    <p><strong>Total Days:</strong> ${a.total_days ?? 0}</p>
                    <p><strong>Present:</strong> ${a.present_days ?? 0}</p>
                    <p><strong>Absent:</strong> ${a.absent_days ?? 0}</p>
                    <p><strong>Late:</strong> ${a.late_days ?? 0}</p>
                    <p>
                        <strong>Attendance %:</strong>
                        <span class="badge bg-success">${a.attendance_rate ?? 0}%</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- PATROL --}}
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white fw-semibold">Patrol Performance</div>
                <div class="card-body small">
                    <p><strong>Total Sessions:</strong> ${p.total_sessions ?? 0}</p>
                    <p><strong>Completed:</strong> ${p.completed_sessions ?? 0}</p>
                    <p><strong>Ongoing:</strong> ${p.ongoing_sessions ?? 0}</p>
                    <p><strong>Total Distance:</strong> ${p.total_distance_km ?? 0} km</p>
                    <p><strong>Avg Distance:</strong> ${p.avg_distance_km ?? 0} km</p>
                </div>
            </div>
        </div>

        {{-- INCIDENTS --}}
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white fw-semibold">Incidents</div>
                <div class="card-body small">
                    <h4 class="fw-bold text-danger text-center">
                        ${i.total_incidents ?? 0}
                    </h4>
                    <p class="text-center mb-2">Total Incidents</p>
                </div>
            </div>
        </div>

    </div>

    {{-- MAP --}}
    <div class="card shadow-sm mt-3">
        <div class="card-header bg-info text-white fw-semibold d-flex justify-content-between align-items-center">
            <span>Patrol Paths (All Sessions - Filtered by Global Filters)</span>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark">${(g.patrol_paths || []).length} paths</span>
                <button class="btn btn-sm btn-light" id="guardMapFullscreenBtn" title="Toggle Fullscreen">
                    <i class="bi bi-arrows-fullscreen"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0 position-relative">
            <div id="guardPatrolMap" style="height:500px;"></div>
        </div>
    </div>

    {{-- INCIDENT LIST --}}
    <div class="card shadow-sm mt-3">
        <div class="card-header fw-semibold">Latest Incidents</div>
        <div class="card-body small">
            ${
                (i.latest || []).length
                ? i.latest.map(x => `
                    <div class="incident-card-item border rounded p-3 mb-2" 
                         onclick="openIncidentDetail('${x.id}')"
                         style="cursor: pointer; transition: all 0.2s ease; border-left: 4px solid #dc3545 !important;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-dark fs-6">${x.type}</strong>
                            <span class="badge bg-light text-dark border">${x.priority}</span>
                        </div>
                        <div class="text-muted small mb-2">
                            <i class="bi bi-geo-alt me-1"></i>${x.site_name} · 
                            <i class="bi bi-calendar3 me-1"></i>${x.date} ${x.time}
                        </div>
                        <div class="text-dark bg-light p-2 rounded small">
                            ${x.remark || '<span class="text-muted italic">No remarks provided</span>'}
                        </div>
                        <div class="text-end mt-2">
                            <small class="text-primary fw-semibold">View Details <i class="bi bi-arrow-right"></i></small>
                        </div>
                    </div>
                `).join('')
                : `<p class="text-muted text-center py-4">No recent incidents recorded</p>`
            }
        </div>
    </div>

    <style>
    .incident-card-item:hover {
        background-color: #f8f9fa;
        transform: translateX(4px);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        border-color: #dc3545 !important;
    }
    </style>
    `;
}


function normalizePathGeoJson(raw) {
    let data = raw;

    if (!data) return null;

    // Parse JSON string
    if (typeof data === 'string') {
        try {
            data = JSON.parse(data);
        } catch {
            return null;
        }
    }

    let coords = [];

    /* Case 1: Proper GeoJSON */
    if (data.type === 'LineString' && Array.isArray(data.coordinates)) {
        coords = data.coordinates;
    }

    /* Case 2: [{lat, lng}] */
    else if (Array.isArray(data) && data[0]?.lat !== undefined) {
        coords = data.map(p => [Number(p.lng), Number(p.lat)]);
    }

    /* Case 3: [[x,y]] */
    else if (Array.isArray(data) && Array.isArray(data[0])) {
        coords = data.map(p => [Number(p[0]), Number(p[1])]);
    }

    if (!coords.length) return null;

    /* ================= VALIDATION & AUTO-FIX ================= */

    const fixed = coords.map(([a, b]) => {
        // Latitude must be -90..90, longitude -180..180
        const aIsLat = Math.abs(a) <= 90;
        const bIsLng = Math.abs(b) <= 180;

        const aIsLng = Math.abs(a) <= 180;
        const bIsLat = Math.abs(b) <= 90;

        // If [lat, lng] → swap
        if (aIsLat && bIsLng && !bIsLat) {
            return [b, a]; // → [lng, lat]
        }

        // Already [lng, lat]
        if (aIsLng && bIsLat) {
            return [a, b];
        }

        return null;
    }).filter(Boolean);

    if (fixed.length < 2) return null;

    return {
        type: 'LineString',
        coordinates: fixed
    };
}




/* ================= MAP ================= */
function initGuardMap(paths) {
    const el = document.getElementById('guardPatrolMap');
    if (!el) return;

    if (guardMapInstance) {
        guardMapInstance.remove();
    }

    guardMapInstance = L.map(el, {
        scrollWheelZoom: false,
        zoomControl: true
    });

    // Ctrl + Scroll zoom only
    el.addEventListener('wheel', e => {
        if (e.ctrlKey) {
            e.preventDefault();
            guardMapInstance.scrollWheelZoom.enable();
        } else {
            guardMapInstance.scrollWheelZoom.disable();
        }
    }, { passive: false });

    document.addEventListener('keyup', () => {
        guardMapInstance.scrollWheelZoom.disable();
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png')
        .addTo(guardMapInstance);

    const focusLayers = [];

    const colors = ['#28a745', '#e91e63', '#9c27b0', '#2196f3', '#00bcd4', '#4caf50', '#ff9800', '#f44336', '#3cb44b', '#f58231'];

    paths.forEach((p, index) => {
        const geo = normalizePathGeoJson(p.path_geojson);
        if (!geo || !geo.coordinates || geo.coordinates.length < 2) return;

        const coords = geo.coordinates.map(c => [c[1], c[0]]);

        // Dynamic Color
        const color = colors[(p.id || index) % colors.length];

        // Highlight path
        const mainPath = L.polyline(coords, {
            color: color,
            weight: 6,
            opacity: 0.95,
            lineCap: 'round',
             interactive: true
        }).addTo(guardMapInstance);


        /* ================= HOVER TOOLTIP ================= */
mainPath.bindTooltip(`
    <div style="font-size:12px; line-height:1.4">
        <strong>Patrol Session</strong><br>
        📅 ${new Date(p.started_at).toLocaleDateString()}<br>
        ▶ ${new Date(p.started_at).toLocaleTimeString()}<br>
        ■ ${p.ended_at ? new Date(p.ended_at).toLocaleTimeString() : 'Ongoing'}<br>
        📏 ${(p.distance / 1000).toFixed(2)} km
    </div>
`, {
    sticky: true,
    opacity: 0.9
});

/* ================= CLICK → DETAILS ================= */
mainPath.on('click', () => {
    showPatrolSessionDetails(p);
});
        // // Glow layer
        L.polyline(coords, {
    color: color,
    weight: 12,
    opacity: 0.25,
    interactive: false   // ⭐ IMPORTANT
}).addTo(guardMapInstance);


        focusLayers.push(mainPath);

        // Start marker
        if (p.start_lat && p.start_lng) {
            focusLayers.push(
                L.circleMarker([p.start_lat, p.start_lng], {
                    radius: 7,
                    color: '#fff',
                    weight: 2,
                    fillColor: '#d00000',
                    fillOpacity: 1
                }).addTo(guardMapInstance)
            );
        }

        // End marker + dashed connector
        if (p.end_lat && p.end_lng && p.start_lat && p.start_lng) {
            focusLayers.push(
                L.circleMarker([p.end_lat, p.end_lng], {
                    radius: 7,
                    color: '#fff',
                    weight: 2,
                    fillColor: '#2d6a4f',
                    fillOpacity: 1
                }).addTo(guardMapInstance)
            );

            // L.polyline(
            //     [[p.start_lat, p.start_lng], [p.end_lat, p.end_lng]],
            //     { color: '#6c757d', dashArray: '6,6', weight: 2 }
            // ).addTo(guardMapInstance);
        }
    });

    if (focusLayers.length) {
        const group = L.featureGroup(focusLayers);
        guardMapInstance.fitBounds(group.getBounds(), {
            padding: [60, 60],
            maxZoom: 17
        });
    } else {
        el.innerHTML = `
            <p class="text-muted text-center py-5">
                No patrol paths available
            </p>
        `;
    }
}
function showPatrolSessionDetails(p) {
    const durationMinutes = p.ended_at
        ? Math.round((new Date(p.ended_at) - new Date(p.started_at)) / 60000)
        : null;

    const html = `
        <div style="min-width:240px; font-size:13px">
            <h6 style="margin-bottom:6px">Patrol Session</h6>
            <p><strong>Date:</strong> ${new Date(p.started_at).toLocaleDateString()}</p>
            <p><strong>Start:</strong> ${new Date(p.started_at).toLocaleString()}</p>
            <p><strong>End:</strong> ${p.ended_at ? new Date(p.ended_at).toLocaleString() : 'In Progress'}</p>
            <p><strong>Duration:</strong> ${durationMinutes ? durationMinutes + ' mins' : '—'}</p>
            <p><strong>Distance:</strong> ${(p.distance / 1000).toFixed(2)} km</p>
            <p><strong>Mode:</strong> ${p.session}</p>
            <p><strong>Type:</strong> ${p.type}</p>
        </div>
    `;

    L.popup({ maxWidth: 320 })
        .setLatLng([p.start_lat, p.start_lng])
        .setContent(html)
        .openOn(guardMapInstance);
}

/* ================= FULLSCREEN FUNCTIONALITY FOR GUARD MAP ================= */
let guardMapFullscreen = false;

function initGuardMapFullscreen() {
    const guardMapFullscreenBtn = document.getElementById('guardMapFullscreenBtn');
    const guardMapContainer = document.getElementById('guardPatrolMap')?.parentElement;
    
    if (!guardMapFullscreenBtn || !guardMapContainer) return;
    
    // Remove existing listeners by cloning the button
    const newBtn = guardMapFullscreenBtn.cloneNode(true);
    guardMapFullscreenBtn.parentNode.replaceChild(newBtn, guardMapFullscreenBtn);
    
    newBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const mapEl = document.getElementById('guardPatrolMap');
        if (!mapEl || !guardMapContainer) return;
        
        if (!guardMapFullscreen) {
            // Store original dimensions
            mapEl.dataset.originalHeight = mapEl.style.height || '500px';
            mapEl.dataset.originalWidth = mapEl.style.width || '100%';
            
            // Request fullscreen on the container
            const requestFullscreen = guardMapContainer.requestFullscreen || 
                                     guardMapContainer.webkitRequestFullscreen || 
                                     guardMapContainer.mozRequestFullScreen ||
                                     guardMapContainer.msRequestFullscreen;
            
            if (requestFullscreen) {
                requestFullscreen.call(guardMapContainer).then(() => {
                    guardMapFullscreen = true;
                    newBtn.innerHTML = '<i class="bi bi-fullscreen-exit"></i>';
                    newBtn.title = 'Exit Fullscreen';
                    
                    // Make map fill the fullscreen container
                    mapEl.style.height = '100vh';
                    mapEl.style.width = '100vw';
                    
                    // Resize map
                    setTimeout(() => {
                        if (guardMapInstance) guardMapInstance.invalidateSize();
                    }, 100);
                }).catch(err => {
                    console.error('Error entering fullscreen:', err);
                });
            }
        } else {
            // Exit fullscreen
            const exitFullscreen = document.exitFullscreen || 
                                  document.webkitExitFullscreen || 
                                  document.mozCancelFullScreen ||
                                  document.msExitFullscreen;
            
            if (exitFullscreen) {
                exitFullscreen.call(document).then(() => {
                    guardMapFullscreen = false;
                    newBtn.innerHTML = '<i class="bi bi-arrows-fullscreen"></i>';
                    newBtn.title = 'Toggle Fullscreen';
                    
                    // Restore original map size
                    mapEl.style.height = mapEl.dataset.originalHeight || '500px';
                    mapEl.style.width = mapEl.dataset.originalWidth || '100%';
                    
                    // Resize map
                    setTimeout(() => {
                        if (guardMapInstance) guardMapInstance.invalidateSize();
                    }, 100);
                }).catch(err => {
                    console.error('Error exiting fullscreen:', err);
                });
            }
        }
    });

    // Listen for fullscreen changes
    const handleFullscreenChange = function() {
        const mapEl = document.getElementById('guardPatrolMap');
        if (!mapEl) return;
        
        const isFullscreen = !!(document.fullscreenElement || 
                                document.webkitFullscreenElement || 
                                document.mozFullScreenElement ||
                                document.msFullscreenElement);
        
        if (!isFullscreen && guardMapFullscreen) {
            guardMapFullscreen = false;
            newBtn.innerHTML = '<i class="bi bi-arrows-fullscreen"></i>';
            newBtn.title = 'Toggle Fullscreen';
            
            // Restore original map size
            mapEl.style.height = mapEl.dataset.originalHeight || '500px';
            mapEl.style.width = mapEl.dataset.originalWidth || '100%';
            
            // Resize map
            setTimeout(() => {
                if (guardMapInstance) guardMapInstance.invalidateSize();
            }, 100);
        } else if (isFullscreen && !guardMapFullscreen) {
            guardMapFullscreen = true;
            newBtn.innerHTML = '<i class="bi bi-fullscreen-exit"></i>';
            newBtn.title = 'Exit Fullscreen';
            
            // Make map fill the fullscreen container
            mapEl.style.height = '100vh';
            mapEl.style.width = '100vw';
            
            // Resize map
            setTimeout(() => {
                if (guardMapInstance) guardMapInstance.invalidateSize();
            }, 100);
        }
    };
    
    // Remove old listeners and add new ones
    document.removeEventListener('fullscreenchange', handleFullscreenChange);
    document.removeEventListener('webkitfullscreenchange', handleFullscreenChange);
    document.removeEventListener('mozfullscreenchange', handleFullscreenChange);
    document.removeEventListener('msfullscreenchange', handleFullscreenChange);
    
    document.addEventListener('fullscreenchange', handleFullscreenChange);
    document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
    document.addEventListener('mozfullscreenchange', handleFullscreenChange);
    document.addEventListener('msfullscreenchange', handleFullscreenChange);
}

</script>

<style>
.guard-name-link,
[data-guard-id],
[data-guard-name],
.guard-name,
td[data-guard-id] {
    cursor: pointer;
    color: #0d6efd;
    font-weight: 600;
    transition: all 0.2s ease;
}
.guard-name-link:hover,
[data-guard-id]:hover,
[data-guard-name]:hover,
.guard-name:hover,
td[data-guard-id]:hover {
    text-decoration: underline;
    color: #0a58ca;
    background-color: rgba(13, 110, 253, 0.1);
}

/* Fullscreen map container styling */
#guardPatrolMap:fullscreen,
#guardPatrolMap:-webkit-full-screen,
#guardPatrolMap:-moz-full-screen,
#guardPatrolMap:-ms-fullscreen {
    width: 100vw;
    height: 100vh;
    background: #fff;
}

#guardPatrolMap:fullscreen ~ *,
#guardPatrolMap:-webkit-full-screen ~ *,
#guardPatrolMap:-moz-full-screen ~ *,
#guardPatrolMap:-ms-fullscreen ~ * {
    display: none;
}
</style>

@php
$hideGlobalFilters = true;
$hideBackground = true;
// $user = session('user');
@endphp

@extends('layouts.app')

@section('title', get_label('label_patrol_analysis', 'Patrol Analysis'))

@section('content')

<style>
    /* =========================================
                       LOCAL COMPONENT STYLES
                       (Hooked to Global Sapphire Variables)
                    ========================================= */

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
        background-color: var(--bg-body);
        color: var(--text-main);
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
        font-weight: 500;
        padding: 8px 16px;
        border-radius: 8px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .btn-sapphire:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        color: #ffffff;
    }

    .btn-sapphire-outline {
        background-color: transparent;
        color: var(--text-main);
        border: 1px solid var(--border-color);
        font-weight: 500;
        padding: 8px 16px;
        border-radius: 8px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .btn-sapphire-outline:hover {
        background-color: var(--table-hover);
        color: var(--sapphire-primary);
        border-color: var(--sapphire-primary);
    }

    /* Soft Badges */
    .badge-soft {
        padding: 4px 10px;
        border-radius: 6px;
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

    .badge-soft-muted {
        background: rgba(148, 163, 184, 0.15);
        color: var(--text-muted);
    }

    /* KPI Cards (Matches Image) */
    .kpi-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
    }

    .kpi-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        font-weight: 700;
        color: var(--text-muted);
        letter-spacing: 0.5px;
        display: block;
        margin-bottom: 5px;
    }

    .kpi-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--text-main);
        margin: 0;
    }

    .kpi-icon-wrap {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .bg-primary-soft {
        background: rgba(59, 130, 246, 0.15);
        color: #3B82F6;
    }

    .bg-success-soft {
        background: rgba(16, 185, 129, 0.15);
        color: #10B981;
    }

    .bg-warning-soft {
        background: rgba(245, 158, 11, 0.15);
        color: #F59E0B;
    }

    .bg-info-soft {
        background: rgba(6, 182, 212, 0.15);
        color: #06B6D4;
    }

    /* Map Container */
    #analysisMap {
        height: 580px;
        width: 100%;
        border-radius: 12px;
    }

    /* Session List & Scrollbar */
    .session-list {
        max-height: 580px;
        overflow-y: auto;
        padding-right: 8px;
    }

    .session-list::-webkit-scrollbar {
        width: 6px;
    }

    .session-list::-webkit-scrollbar-track {
        background: transparent;
    }

    .session-list::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 10px;
    }

    .session-list::-webkit-scrollbar-thumb:hover {
        background: var(--text-muted);
    }

    /* Session Card Styling */
    .session-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .session-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
        border-color: var(--sapphire-primary);
    }

    .session-card.highlight {
        border-color: var(--sapphire-primary);
        box-shadow: 0 0 15px 2px rgba(59, 130, 246, 0.4);
    }

    /* Stat Blocks below map */
    .map-stat-block {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 16px;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
</style>

<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1" style="color: var(--text-main);">
                {{ get_label('label_patrol_analysis', 'Patrol Analysis') }}
            </h3>
            <p class="mb-0" style="color: var(--text-muted); font-size: 0.9rem;">
                Geospatial tracking and route analysis for completed and ongoing sessions.
            </p>
        </div>
        <div>
            @if (count($sessions) > 0)
            <button id="toggleGeofences" class="btn-sapphire-outline shadow-sm" onclick="toggleGeofences()">
                <i class="bi bi-layers me-1"></i> Hide Geofences
            </button>
            @endif
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="dash-card mb-4 p-4">
        <form method="GET" action="{{ route('patrolling.analysis') }}" class="row g-3 align-items-end">

            <div class="col-md-2">
                <label for="client_id" class="form-label small fw-semibold" style="color: var(--text-muted);">
                    {{ get_label('label_client', 'Range') }}
                </label>
                <select class="custom-input" name="client_id" id="rangeSelect">
                    <option value="">All {{ Str::plural(get_label('label_client', 'Range')) }}</option>
                    @foreach ($clients as $client)
                    <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                        {{ $client->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="site_id" class="form-label small fw-semibold" style="color: var(--text-muted);">
                    {{ get_label('label_site', 'Beat') }}
                </label>
                <select class="custom-input" name="site_id" id="siteSelect">
                    <option value="">All {{ Str::plural(get_label('label_site', 'Beat')) }}</option>
                    @foreach ($sites as $site)
                    <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                        {{ $site->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="user_id" class="form-label small fw-semibold" style="color: var(--text-muted);">
                    {{ get_label('label_user', 'User') }}
                </label>
                <select class="custom-input" name="user_id" id="userSelect">
                    <option value="">All {{ Str::plural(get_label('label_user', 'User')) }}</option>
                    @foreach ($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="date_from" class="form-label small fw-semibold"
                    style="color: var(--text-muted);">From</label>
                <input type="date" class="custom-input" name="date_from" value="{{ request('date_from') }}">
            </div>

            <div class="col-md-2">
                <label for="date_to" class="form-label small fw-semibold" style="color: var(--text-muted);">To</label>
                <input type="date" class="custom-input" name="date_to" value="{{ request('date_to') }}">
            </div>

            {{-- Apply & Clear Buttons --}}
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn-sapphire flex-grow-1" title="Apply Filters">
                    <i class="bi bi-funnel"></i> Apply
                </button>
                <a href="{{ route('patrolling.analysis') }}" class="btn-sapphire-outline flex-grow-1"
                    title="Clear Filters">
                    <i class="bi bi-arrow-clockwise"></i> Clear
                </a>
            </div>

        </form>
    </div>

    {{-- KPI CARDS TOP ROW --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="kpi-card shadow-sm h-100">
                <div class="d-flex justify-content-between align-items-center h-100">
                    <div>
                        <span class="kpi-label">Total Sessions</span>
                        <h3 class="kpi-value">{{ $stats['total_sessions'] ?? 0 }}</h3>
                    </div>
                    <div class="kpi-icon-wrap bg-primary-soft">
                        <i class="bi bi-collection-play-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="kpi-card shadow-sm h-100">
                <div class="d-flex justify-content-between align-items-center h-100">
                    <div>
                        <span class="kpi-label">Completed</span>
                        <h3 class="kpi-value">{{ $stats['completed_sessions'] ?? 0 }}</h3>
                    </div>
                    <div class="kpi-icon-wrap bg-success-soft">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="kpi-card shadow-sm h-100">
                <div class="d-flex justify-content-between align-items-center h-100">
                    <div>
                        <span class="kpi-label">Ongoing</span>
                        <h3 class="kpi-value">{{ $stats['ongoing_sessions'] ?? 0 }}</h3>
                    </div>
                    <div class="kpi-icon-wrap bg-warning-soft">
                        <i class="bi bi-play-circle-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="kpi-card shadow-sm h-100">
                <div class="d-flex justify-content-between align-items-center h-100">
                    <div>
                        <span class="kpi-label">Total Distance</span>
                        {{-- ID added here so turf.js can dynamically update it if needed --}}
                        <h3 class="kpi-value" id="kpiTotalDistance">
                            {{ number_format(($stats['total_distance_m'] ?? 0) / 1000, 1) }}
                            <span style="font-size: 1rem; color: var(--text-muted);">km</span>
                        </h3>
                    </div>
                    <div class="kpi-icon-wrap bg-info-soft">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CONDITIONAL CONTENT (Map & Sessions vs Empty State) --}}
    @if (count($sessions) > 0)
    <div class="row g-4">

        {{-- MAP COLUMN --}}
        <div class="col-lg-8">
            <div class="dash-card p-0 overflow-hidden mb-3">
                <div id="analysisMap"></div>
            </div>

            <div class="d-flex flex-column flex-md-row gap-3">
                <div class="map-stat-block shadow-sm w-100">
                    <small class="text-uppercase fw-bold"
                        style="color: var(--text-muted); font-size: 0.75rem;">Unattended Geofences</small>
                    <ul id="unattendedList" class="mb-0 ps-3 mt-1"
                        style="color: var(--text-main); font-size: 0.9rem;"></ul>
                </div>
            </div>
        </div>

        {{-- SESSIONS LIST COLUMN --}}
        <div class="col-lg-4">
            <div class="session-list">
                @php $colors = []; @endphp

                @foreach ($sessions as $index => $s)
                @php
                if (!isset($colors[$s->id])) {
                $colors[$s->id] = sprintf('#%06X', mt_rand(0, 0xffffff));
                }
                $sessionColor = $colors[$s->id];
                @endphp

                <div id="session-card-{{ $s->id }}" class="session-card mb-3 shadow-sm p-3">
                    <div
                        style="position: absolute; left: 0; top: 0; bottom: 0; width: 5px; background-color: {{ $sessionColor }};">
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3 ms-2">
                        <div class="fw-bold" style="color: var(--text-main); font-size: 0.95rem;">
                            <i class="bi bi-circle-fill me-1"
                                style="color: {{ $sessionColor }}; font-size: 0.5rem;"></i>
                            Session #{{ $s->id }}
                        </div>

                        @if ($s->ended_at)
                        <span class="badge-soft badge-soft-success">Completed</span>
                        @else
                        <span class="badge-soft badge-soft-warning">Ongoing</span>
                        @endif
                    </div>

                    <div class="d-flex align-items-center gap-3 mb-3 ms-2">
                        @if (!empty($s->user->profile_pic))
                        <img src="{{ $s->user->profile_pic }}" class="rounded-circle shadow-sm"
                            style="width: 48px; height: 48px; object-fit: cover; border: 2px solid var(--border-color);">
                        @else
                        <div class="rounded-circle shadow-sm d-flex align-items-center justify-content-center fw-bold"
                            style="width: 48px; height: 48px; background: var(--bg-body); border: 1px solid var(--border-color); color: var(--text-muted); font-size: 1.1rem;">
                            {{ strtoupper(substr($s->user->name ?? 'U', 0, 1)) }}
                        </div>
                        @endif

                        <div>
                            <h6 class="mb-0 fw-semibold" style="color: var(--text-main);">
                                {{ $s->user->name ?? 'Unknown User' }}
                            </h6>
                            <small style="color: var(--text-muted); font-size: 0.8rem;"><i
                                    class="bi bi-geo-alt-fill opacity-75"></i>
                                {{ $s->site->name ?? 'N/A' }}</small>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-3 ms-2">
                        <span class="badge-soft badge-soft-muted" title="Start Time">
                            <i class="bi bi-play-circle-fill"></i>
                            {{ \Carbon\Carbon::parse($s->started_at)->format('d M H:i') }}
                        </span>

                        <span class="badge-soft badge-soft-muted" title="End Time">
                            <i class="bi bi-stop-circle-fill"></i>
                            {{ $s->ended_at ? \Carbon\Carbon::parse($s->ended_at)->format('d M H:i') : 'In Progress' }}
                        </span>

                        <span class="badge-soft badge-soft-primary" title="Distance">
                            <i class="bi bi-rulers"></i> {{ number_format($s->distance_m / 1000, 2) }} km
                        </span>
                    </div>

                    <div class="d-flex gap-2 ms-2 pt-3 mt-auto"
                        style="border-top: 1px dashed var(--border-color);">
                        <button class="btn btn-sm btn-sapphire-outline w-100 zoom-session"
                            data-session="{{ $s->id }}">
                            <i class="bi bi-search"></i> Zoom
                        </button>
                        <a href="{{ route('patrolling.details', $s->id) }}"
                            class="btn btn-sm btn-sapphire w-100 text-center text-decoration-none">
                            <i class="bi bi-eye"></i> View
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @else
    {{-- EMPTY STATE (Shown when no sessions match filter) --}}
    <div class="dash-card py-5 text-center">
        <div class="py-4">
            <div class="mb-3">
                <i class="bi bi-map-fill" style="font-size: 4rem; color: var(--text-muted); opacity: 0.3;"></i>
            </div>
            <h4 class="fw-bold mb-2" style="color: var(--text-main);">No Patrol Sessions Found</h4>
            <p style="color: var(--text-muted); max-width: 400px; margin: 0 auto;">
                There is no geospatial tracking data available for the selected filters. Try adjusting your date
                range, user, or location settings.
            </p>
            <a href="{{ route('patrolling.analysis') }}" class="btn-sapphire mt-4">
                <i class="bi bi-arrow-clockwise"></i> Clear Filters
            </a>
        </div>
    </div>
    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfBFN6L_HROTd-mS8QqUDRIqskkvHvFYk&callback=initMap" async defer></script>
<script>
    $(document).ready(function() {

        // When range changes — load sites + users
        $('#rangeSelect').on('change', function() {
            let clientID = $(this).val();

            $('#siteSelect').html('<option value="">All Beats</option>');
            $('#userSelect').html('<option value="">All Users</option>');

            if (!clientID) return;

            $.get('/ajax/client-sites/' + clientID, function(data) {
                data.forEach(function(s) {
                    $('#siteSelect').append(
                        `<option value="${s.id}">${s.name}</option>`);
                });
            });

            $.get('/ajax/client-users/' + clientID, function(data) {
                data.forEach(function(u) {
                    $('#userSelect').append(
                        `<option value="${u.id}">${u.name}</option>`);
                });
            });
        });

        // When site changes — load only users under that site
        $('#siteSelect').on('change', function() {
            let siteID = $(this).val();

            $('#userSelect').html('<option value="">All Users</option>');

            if (!siteID) return;

            $.get('/ajax/site-users/' + siteID, function(data) {
                data.forEach(function(u) {
                    $('#userSelect').append(
                        `<option value="${u.id}">${u.name}</option>`);
                });
            });
        });

    });

    // MAP LOGIC (Only runs if map element exists)
    @if(count($sessions) > 0)
    const sessions = @json($sessions);
    const geofences = @json($geofences);
    const sessionColors = @json($colors);

    let map, bounds;
    let geofencePolygons = [];
    let geofencesVisible = true;
    let sessionPolylines = {};
    let sessionMarkers = {};
    let sessionInfoWindows = {};

    window.initMap = function() {
        const mapEl = document.getElementById('analysisMap');
        if (!mapEl) return;

        const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';

        const darkMapStyle = [{
                elementType: "geometry",
                stylers: [{
                    color: "#242f3e"
                }]
            },
            {
                elementType: "labels.text.stroke",
                stylers: [{
                    color: "#242f3e"
                }]
            },
            {
                elementType: "labels.text.fill",
                stylers: [{
                    color: "#746855"
                }]
            },
            {
                featureType: "water",
                elementType: "geometry",
                stylers: [{
                    color: "#17263c"
                }]
            }
        ];

        map = new google.maps.Map(mapEl, {
            center: {
                lat: 20.0,
                lng: 78.0
            },
            zoom: 6,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            styles: isDarkMode ? darkMapStyle : [],
            mapTypeControl: true,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
                position: google.maps.ControlPosition.TOP_LEFT,
            }
        });

        bounds = new google.maps.LatLngBounds();

        drawGeofences();
        drawSessions();
        computeUnattended();
    };

    function getPolygonCenter(path) {
        let lat = 0,
            lng = 0;
        path.forEach(point => {
            lat += point.lat;
            lng += point.lng;
        });
        return {
            lat: lat / path.length,
            lng: lng / path.length
        };
    }

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

            const infowindow = new google.maps.InfoWindow({
                content: `<div style="font-size:13px; color: #000;"><strong>${g.name}</strong></div>`
            });

            polygon.addListener('click', () => {
                infowindow.setPosition(center);
                infowindow.open(map);
            });

            path.forEach(p => bounds.extend(p));
            g._path = path;
            geofencePolygons.push(polygon);
        });
    }

    function drawSessions() {
        let totalClientDistance = 0;

        sessions.forEach(s => {
            if (!s.path_for_js || s.path_for_js.length < 2) return;

            const path = s.path_for_js;
            const color = sessionColors[s.id] || "#3B82F6";

            const polyline = new google.maps.Polyline({
                path,
                strokeColor: color,
                strokeOpacity: 1,
                strokeWeight: 4,
                map,
                zIndex: 2
            });
            sessionPolylines[s.id] = polyline;

            const startTime = moment(s.started_at).format("DD-MM-YYYY HH:mm");
            const endTime = s.ended_at ? moment(s.ended_at).format("DD-MM-YYYY HH:mm") : 'Ongoing';
            const distance = (s.distance_m / 1000).toFixed(2);

            const infoContent = `
                    <div style="font-size:13px; color: #333; padding: 4px;">
                        <b style="color: ${color};">Session #${s.id}</b><br>
                        <span style="color: #475569;">👤 ${s.user ? s.user.name : 'Unknown'}</span><br>
                        <span style="color: #475569;">📍 ${s.site ? s.site.name : 'N/A'}</span><br>
                        <span style="color: #10B981;">▶ ${startTime}</span><br>
                        <span style="color: #EF4444;">⏹ ${endTime}</span><br>
                        <span style="color: #3B82F6;">📏 ${distance} km</span>
                    </div>
                `;
            const infowindow = new google.maps.InfoWindow({
                content: infoContent
            });
            sessionInfoWindows[s.id] = infowindow;

            const start = path[0];
            const end = path[path.length - 1];

            const startMarker = new google.maps.Marker({
                position: start,
                map,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 6,
                    fillColor: "#10B981",
                    fillOpacity: 1,
                    strokeColor: "#fff",
                    strokeWeight: 2
                }
            });

            const endMarker = new google.maps.Marker({
                position: end,
                map,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 6,
                    fillColor: "#EF4444",
                    fillOpacity: 1,
                    strokeColor: "#fff",
                    strokeWeight: 2
                }
            });

            sessionMarkers[s.id] = {
                start: startMarker,
                end: endMarker
            };

            polyline.addListener("click", (e) => {
                infowindow.setPosition(e.latLng || start);
                infowindow.open(map);
            });

            path.forEach(p => bounds.extend(p));

            const turfLine = turf.lineString(path.map(p => [p.lng, p.lat]));
            totalClientDistance += turf.length(turfLine, {
                units: 'kilometers'
            });
        });

        if (!bounds.isEmpty()) {
            map.fitBounds(bounds);
        }

        // Dynamically update the top KPI distance to ensure exact turf.js calculation matches UI
        const distEl = document.getElementById('kpiTotalDistance');
        if (distEl) {
            distEl.innerHTML = totalClientDistance.toFixed(1) + ' <span style="font-size: 1rem; color: var(--text-muted);">km</span>';
        }
    }

    document.addEventListener("click", e => {
        if (e.target.closest(".zoom-session")) {
            const id = e.target.closest(".zoom-session").dataset.session;
            const polyline = sessionPolylines[id];
            const markers = sessionMarkers[id];
            const card = document.getElementById(`session-card-${id}`);

            if (polyline) {
                const path = polyline.getPath();
                const sessionBounds = new google.maps.LatLngBounds();
                path.forEach(p => sessionBounds.extend(p));
                map.fitBounds(sessionBounds);

                const listener = google.maps.event.addListener(map, "idle", function() {
                    if (map.getZoom() > 16) map.setZoom(16);
                    google.maps.event.removeListener(listener);
                });

                if (markers?.start) {
                    markers.start.setAnimation(google.maps.Animation.BOUNCE);
                    setTimeout(() => markers.start.setAnimation(null), 2000);
                }
                if (markers?.end) {
                    markers.end.setAnimation(google.maps.Animation.BOUNCE);
                    setTimeout(() => markers.end.setAnimation(null), 2000);
                }

                const originalColor = sessionColors[id] || "#3B82F6";
                polyline.setOptions({
                    strokeWeight: 8,
                    strokeColor: "#F59E0B"
                });
                setTimeout(() => {
                    polyline.setOptions({
                        strokeWeight: 4,
                        strokeColor: originalColor
                    });
                }, 2000);

                document.querySelectorAll(".session-card").forEach(c => c.classList.remove("highlight"));
                card.classList.add("highlight");
                setTimeout(() => card.classList.remove("highlight"), 3000);

                card.scrollIntoView({
                    behavior: "smooth",
                    block: "center"
                });

                if (sessionInfoWindows[id]) {
                    sessionInfoWindows[id].setPosition(markers.start.getPosition());
                    sessionInfoWindows[id].open(map);
                }
            }
        }
    });

    function computeUnattended() {
        const unattended = [];

        geofences.forEach(g => {
            if (!g._path || !g._path.length) return;

            const polyLngLat = g._path.map(p => [p.lng, p.lat]);
            const polygon = turf.polygon([polyLngLat]);

            let anyInside = false;
            for (let s of sessions) {
                if (!s.path_for_js) continue;
                for (let p of s.path_for_js) {
                    if (turf.booleanPointInPolygon(turf.point([p.lng, p.lat]), polygon)) {
                        anyInside = true;
                        break;
                    }
                }
                if (anyInside) break;
            }

            if (!anyInside) unattended.push(g.name);
        });

        const ul = document.getElementById('unattendedList');
        ul.innerHTML = unattended.length === 0 ?
            '<li style="color: var(--sapphire-success);"><i class="bi bi-check-circle-fill me-1"></i> All geofences attended</li>' :
            unattended.map(n =>
                `<li style="color: var(--sapphire-danger); padding-bottom:4px;"><i class="bi bi-exclamation-circle-fill me-1"></i> ${n}</li>`
            ).join('');
    }

    window.toggleGeofences = function() {
        geofencesVisible = !geofencesVisible;
        geofencePolygons.forEach(polygon => {
            polygon.setMap(geofencesVisible ? map : null);
        });
        const btn = document.getElementById('toggleGeofences');
        btn.innerHTML = geofencesVisible ? '<i class="bi bi-layers me-1"></i> Hide Geofences' :
            '<i class="bi bi-layers-half me-1"></i> Show Geofences';
    }

    window.addEventListener('themeChanged', function() {
        if (map) {
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            const darkMapStyle = [{
                    elementType: "geometry",
                    stylers: [{
                        color: "#242f3e"
                    }]
                },
                {
                    elementType: "labels.text.stroke",
                    stylers: [{
                        color: "#242f3e"
                    }]
                },
                {
                    elementType: "labels.text.fill",
                    stylers: [{
                        color: "#746855"
                    }]
                },
                {
                    featureType: "water",
                    elementType: "geometry",
                    stylers: [{
                        color: "#17263c"
                    }]
                }
            ];
            map.setOptions({
                styles: isDark ? darkMapStyle : []
            });
        }
    });

    window.addEventListener("load", () => {
        if (typeof google !== "undefined" && google.maps) {
            initMap();
        }
    });
    @endif
</script>

@endsection
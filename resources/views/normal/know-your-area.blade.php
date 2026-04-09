@php
$hideGlobalFilters = true;
$hideBackground = true;
// $user = session('user');
@endphp
@extends('layouts.app')

@section('title', get_label('label_know_your_area', 'Know Your Area'))

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>

<style>
    /* =========================================
                   LOCAL COMPONENT STYLES
                   (Hooked to Global Sapphire Variables)
                ========================================= */

    /* Inner Wrapper to handle Flexbox stacking without breaking the global layout */
    .map-view-wrapper {
        display: flex;
        flex-direction: column;
        /* Force it to take the full viewport minus the top navbar height */
        height: calc(100vh - 70px);
        width: 100%;
        background-color: var(--bg-body);
        position: relative;
    }

    .map-container-wrapper {
        position: relative;
        flex-grow: 1;
        /* Forces the map to fill all remaining space below the header */
        width: 100%;
        overflow: hidden;
    }

    #map {
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    /* Glass Panel (Sidebar) */
    .glass-panel {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    .filter-sidebar {
        position: absolute;
        top: 15px;
        right: 15px;
        width: 360px;
        max-height: calc(100% - 30px);
        z-index: 1000;
        display: flex;
        flex-direction: column;
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        transform: translateX(calc(100% + 40px));
    }

    .filter-sidebar.open {
        transform: translateX(0);
    }

    /* Floating Drawer Toggle */
    .drawer-toggle {
        position: absolute;
        top: 30%;
        right: 0;
        transform: translateY(-50%);
        z-index: 1001;
        width: 48px;
        height: 56px;
        background: var(--sapphire-primary);
        color: #ffffff;
        border: none;
        border-radius: 12px 0 0 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        cursor: pointer;
        box-shadow: -4px 0 15px rgba(0, 0, 0, 0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .drawer-toggle i {
        margin-bottom: 2px;
    }

    .drawer-toggle span {
        font-size: 0.6rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .drawer-toggle:hover {
        opacity: 0.9;
        padding-right: 5px;
    }

    .drawer-toggle.active {
        right: 375px;
        background: var(--sapphire-danger);
        border-radius: 12px;
    }

    /* Sidebar Content */
    .sidebar-header {
        padding: 20px 20px 10px;
        border-bottom: 1px solid var(--border-color);
        position: relative;
    }

    .sidebar-header h5 {
        color: var(--text-main);
        font-weight: 800;
        font-size: 1.2rem;
        margin-bottom: 2px;
    }

    .sidebar-header .sub-title {
        color: var(--sapphire-primary);
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
    }

    .sidebar-content {
        padding: 15px;
        overflow-y: auto;
        flex: 1;
    }

    /* Custom Inputs inside Sidebar */
    .custom-input {
        background-color: var(--bg-body);
        color: var(--text-main);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 0.85rem;
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

    /* Layer Items */
    .layer-item {
        background: var(--bg-body);
        border-radius: 12px;
        margin-bottom: 10px;
        padding: 10px 14px;
        border: 1px solid var(--border-color);
        transition: all 0.2s ease-out;
        cursor: pointer;
        display: flex;
        align-items: center;
    }

    .layer-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
        border-color: var(--sapphire-primary);
    }

    .layer-item.active {
        background: var(--bg-card);
        border-color: var(--sapphire-primary);
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }

    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 12px;
    }

    .layer-icon-box {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        margin-right: 12px;
        background: rgba(0, 0, 0, 0.03);
        border-radius: 8px;
    }

    .layer-label {
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--text-main);
        flex: 1;
    }

    .count-pill {
        background: var(--table-hover);
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--text-muted);
        margin: 0 10px;
        min-width: 32px;
        text-align: center;
    }

    .eye-toggle {
        font-size: 1.2rem;
        color: var(--border-color);
        transition: color 0.2s, transform 0.2s;
    }

    .eye-toggle:hover {
        transform: scale(1.1);
    }

    .eye-toggle.active {
        color: var(--sapphire-success);
    }

    /* Map Loader */
    .custom-loader {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--bg-card);
        opacity: 0.9;
        z-index: 2000;
        display: none;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        border-radius: 16px;
    }

    /* Clusterer Overrides */
    .marker-cluster-small div {
        background-color: var(--sapphire-success) !important;
        color: white;
    }

    .marker-cluster-medium div {
        background-color: var(--sapphire-warning) !important;
        color: white;
    }

    .marker-cluster-large div {
        background-color: var(--sapphire-danger) !important;
        color: white;
    }

    /* Popup Overrides */
    .leaflet-popup-content-wrapper,
    .gm-style-iw {
        background-color: var(--bg-card) !important;
        color: var(--text-main) !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
        padding: 0 !important;
        overflow: hidden;
    }

    .gm-style-iw-d {
        overflow: hidden !important;
    }

    .gm-style-iw button.gm-ui-hover-effect {
        filter: var(--bs-theme)=='dark' ? 'invert(1)': 'none';
        top: 10px !important;
        right: 10px !important;
    }

    .popup-header {
        padding: 16px;
        color: #ffffff;
    }

    .popup-layer-badge {
        font-size: 0.65rem;
        background: rgba(255, 255, 255, 0.2);
        padding: 4px 8px;
        border-radius: 6px;
        text-transform: uppercase;
        font-weight: 700;
        margin-bottom: 6px;
        display: inline-block;
    }

    .popup-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0;
    }

    .popup-body {
        padding: 12px 16px;
        max-height: 250px;
        overflow-y: auto;
        background-color: var(--bg-card);
        color: var(--text-main);
    }

    .popup-table {
        width: 100%;
        font-size: 0.85rem;
    }

    .popup-table tr {
        border-bottom: 1px dashed var(--border-color);
    }

    .popup-table tr:last-child {
        border-bottom: none;
    }

    .popup-table td {
        padding: 8px 0;
        color: var(--text-main);
    }

    .popup-table .popup-label {
        color: var(--text-muted);
        font-weight: 600;
        width: 40%;
        font-size: 0.75rem;
        text-transform: uppercase;
    }

    /* Button inside Header */
    .btn-sapphire {
        background-color: var(--sapphire-primary);
        color: white;
        border: none;
        font-weight: 600;
        padding: 6px 16px;
        border-radius: 8px;
        transition: all 0.2s;
    }

    .btn-sapphire:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        color: white;
    }
</style>

{{-- FIXED: Removed the extra .content div that was causing double margins --}}
<div class="map-view-wrapper">

    {{-- THE NEW SAPPHIRE HEADER SECTION --}}
    <div class="px-4 py-3 d-flex justify-content-between align-items-center shadow-sm"
        style="background: var(--bg-card); border-bottom: 1px solid var(--border-color); z-index: 10;">
        <div>
           <h4 class="fw-bold mb-1" style="color: var(--text-main);">
    {{ get_label('label_know_your_area', 'Know Your Area') }}
</h4>
            <p class="mb-0" style="color: var(--text-muted); font-size: 0.85rem;">
                Interactive map layers, geospatial data, and beat boundaries.
            </p>
        </div>
        <div>
            <button class="btn-sapphire shadow-sm" onclick="resetMap()">
                <i class="bi bi-arrow-clockwise me-1"></i> Reset Map
            </button>
        </div>
    </div>

    {{-- MAP CONTAINER (Takes remaining height automatically) --}}
    <div class="map-container-wrapper">
        <div id="map"></div>

        {{-- Drawer Toggle --}}
        <button class="drawer-toggle" id="drawerToggle">
            <i class="bi bi-layers-half"></i>
            <span>Layers</span>
        </button>

        {{-- Sidebar Layers Panel --}}
        <div class="filter-sidebar glass-panel">
            <div class="sidebar-header">
                <h5 class="mb-0">Map Layers</h5>
                <div class="sub-title">Select to show/hide</div>
            </div>

            <div class="sidebar-content">
                <form id="filterForm" class="mb-4">
                    <div class="row g-2 mb-3">

                        @if ($userRole == 1 || $userRole == 7)
                        <div class="col-6">
                            <label class="form-label small fw-bold mb-1"
                                style="color: var(--text-muted);">Range</label>
                            <select id="rangeSelect" name="range_id" class="custom-input">
                                <option value="">Range</option>
                                @foreach ($availableRanges as $range)
                                <option value="{{ $range->id }}"
                                    {{ $selectedRange == $range->id ? 'selected' : '' }}>
                                    {{ $range->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <input type="hidden" name="range_id" value="{{ $selectedRange }}">
                        @endif

                        @if ($userRole == 1 || $userRole == 7 || $userRole == 2)
                        <div class="col-6">
                            <label class="form-label small fw-bold mb-1"
                                style="color: var(--text-muted);">Beat</label>
                            <select id="beatSelect" name="site_id" class="custom-input">
                                <option value="">Beat</option>
                                @foreach ($availableBeats as $beat)
                                <option value="{{ $beat->id }}"
                                    {{ $selectedBeat == $beat->id ? 'selected' : '' }}>
                                    {{ $beat->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <input type="hidden" name="site_id" id="beatSelect" value="{{ $selectedBeat }}">
                        @endif

                        <div class="col-12 mt-2">
                            <label class="form-label small fw-bold mb-1" style="color: var(--text-muted);">Year</label>
                            <select id="yearSelect" name="year" class="custom-input">
                                <option value="">All</option>
                                @foreach ($availableYears as $year)
                                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-sapphire w-100 justify-content-center shadow-sm py-2">
                        <i class="bi bi-search me-2"></i> Apply Filters
                    </button>
                </form>

                <div id="layerControls">
                    <div class="layer-item" id="item_geofences" onclick="toggleLayerUI('geofences')">
                        <div class="status-dot" style="background-color: var(--sapphire-primary);"></div>
                        <div class="layer-icon-box" style="color: var(--sapphire-primary);"><i
                                class="bi bi-shield-check"></i></div>
                        <div class="layer-label">Beat Boundary</div>
                        <div id="count_geofences" class="count-pill">0</div>
                        <div class="eye-toggle" id="eye_geofences"><i class="bi bi-eye-fill"></i></div>
                        <input type="checkbox" class="layer-toggle d-none" value="geofences" id="check_geofences">
                    </div>

                    @php
                    // Mapping logical names to Sapphire hex values for consistency
                    $layers = [
                    [
                    'id' => 'drainage',
                    'label' => 'Drainage',
                    'color' => '#3B82F6',
                    'icon' => 'bi-droplet-half',
                    ],
                    [
                    'id' => 'elephant_movement',
                    'label' => 'Elephant Movements',
                    'color' => '#F59E0B',
                    'icon' => 'bi-paw',
                    ],
                    [
                    'id' => 'fire_point',
                    'label' => 'Fire Points',
                    'color' => '#EF4444',
                    'icon' => 'bi-fire',
                    ],
                    [
                    'id' => 'forest_boundary',
                    'label' => 'Forest Boundary',
                    'color' => '#10B981',
                    'icon' => 'bi-leaf-fill',
                    ],
                    [
                    'id' => 'plantation_site',
                    'label' => 'Plantation Sites',
                    'color' => '#06B6D4',
                    'icon' => 'bi-flower1',
                    ],
                    [
                    'id' => 'revenue_forest_land',
                    'label' => 'Revenue Forest Land',
                    'color' => '#8B5CF6',
                    'icon' => 'bi-globe',
                    ],
                    [
                    'id' => 'water_body',
                    'label' => 'Water Bodies',
                    'color' => '#3B82F6',
                    'icon' => 'bi-cloud-rain-fill',
                    ],
                    ];
                    @endphp
                    @foreach ($layers as $layer)
                    <div class="layer-item" id="item_{{ $layer['id'] }}"
                        onclick="toggleLayerUI('{{ $layer['id'] }}')">
                        <div class="status-dot" style="background-color: {{ $layer['color'] }}"></div>
                        <div class="layer-icon-box" style="color: {{ $layer['color'] }}">
                            <i class="bi {{ $layer['icon'] }}"></i>
                        </div>
                        <div class="layer-label">{{ $layer['label'] }}</div>
                        <div id="count_{{ $layer['id'] }}" class="count-pill">0</div>
                        <div class="eye-toggle" id="eye_{{ $layer['id'] }}"><i class="bi bi-eye-fill"></i></div>
                        <div id="spinner_{{ $layer['id'] }}"
                            class="spinner-border spinner-border-sm text-primary ms-2" role="status"
                            style="display: none; width: 0.8rem; height: 0.8rem;"></div>
                        <input type="checkbox" class="layer-toggle d-none" value="{{ $layer['id'] }}"
                            id="check_{{ $layer['id'] }}">
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Custom Loader --}}
        <div id="customLoader" class="custom-loader">
            <div class="spinner-border mb-2" style="color: var(--sapphire-primary);" role="status"></div>
            <span class="small fw-bold" style="color: var(--text-main);">Loading Data...</span>
        </div>

    </div>
</div>

@push('scripts')
<script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfBFN6L_HROTd-mS8QqUDRIqskkvHvFYk&libraries=visualization">
</script>
<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>

<script>
    let map;
    let infoWindow;
    let clusterer;
    let layerDataCollections = {};
    let layerMarkers = {};
    let layerShapes = {};
    let loadedLayers = {};

    // Hardcoded hex values matching the Sapphire Theme for Google Maps JS API
    const LAYER_STYLES = {
        'drainage': {
            strokeColor: '#3B82F6',
            strokeWeight: 3,
            fillOpacity: 0
        },
        'elephant_movement': {
            strokeColor: '#F59E0B',
            strokeWeight: 4,
            fillOpacity: 0
        },
        'fire_point': {
            icon: '🔥'
        },
        'forest_boundary': {
            strokeColor: '#10B981',
            strokeWeight: 3,
            fillOpacity: 0.1
        },
        'plantation_site': {
            strokeColor: '#06B6D4',
            strokeWeight: 2,
            fillOpacity: 0.3
        },
        'revenue_forest_land': {
            strokeColor: '#8B5CF6',
            strokeWeight: 2,
            fillOpacity: 0.3
        },
        'water_body': {
            strokeColor: '#3B82F6',
            strokeWeight: 3,
            fillOpacity: 0.4
        },
        'geofences': {
            strokeColor: '#3B82F6',
            strokeWeight: 2,
            fillOpacity: 0.1
        }
    };

    const LAYER_ICONS = {
        'elephant_movement': '🐘',
        'fire_point': '🔥',
        'plantation_site': '🌱',
        'drainage': '🌊',
        'water_body': '💧',
        'forest_boundary': '🌳',
        'revenue_forest_land': '📜'
    };

    function toggleLayerUI(layerType) {
        const cb = document.getElementById('check_' + layerType);
        if (cb) {
            cb.checked = !cb.checked;
            cb.dispatchEvent(new Event('change', {
                bubbles: true
            }));
            updateLayerUIState(layerType, cb.checked);
        }
    }

    function updateLayerUIState(layerType, active) {
        const item = document.getElementById('item_' + layerType);
        const eye = document.getElementById('eye_' + layerType);
        if (item) item.classList.toggle('active', active);
        if (eye) eye.classList.toggle('active', active);
    }

    document.addEventListener('DOMContentLoaded', function() {
        initMap();
        loadLayerCounts();

        const sidebar = document.querySelector('.filter-sidebar');
        const toggleBtn = document.getElementById('drawerToggle');

        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            this.classList.toggle('active');
            const icon = this.querySelector('i');
            if (sidebar.classList.contains('open')) {
                icon.className = 'bi bi-x-lg';
            } else {
                icon.className = 'bi bi-layers-half';
            }
        });

        document.getElementById('filterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            resetMap();
            loadLayerCounts();
        });

        document.body.addEventListener('change', function(e) {
            if (e.target.classList.contains('layer-toggle')) {
                handleLayerToggle(e.target.value, e.target.checked);
            }
        });

        // Range/Beat AJAX
        const rangeSelect = document.getElementById('rangeSelect');
        if (rangeSelect) {
            rangeSelect.addEventListener('change', function() {
                const rangeId = this.value;
                const beatSelect = document.getElementById('beatSelect');
                if (beatSelect && beatSelect.tagName === 'SELECT') {
                    beatSelect.innerHTML = '<option value="">Loading...</option>';
                    if (rangeId) {
                        fetch(`{{ url('/filters/beats') }}/${rangeId}`)
                            .then(res => res.json())
                            .then(data => {
                                beatSelect.innerHTML = '<option value="">Select Beat</option>';

                                // FIX: Some Laravel responses wrap data in a 'data' key or return an object
                                // This logic ensures we are looping over an actual array
                                let beatsArray = Array.isArray(data) ? data : (data.data || []);

                                if (beatsArray.length > 0) {
                                    beatsArray.forEach(beat => {
                                        beatSelect.innerHTML +=
                                            `<option value="${beat.id}">${beat.name}</option>`;
                                    });
                                } else {
                                    console.warn('No beats found for this range.');
                                }
                            })
                            .catch(err => {
                                console.error('Error fetching beats:', err);
                                beatSelect.innerHTML = '<option value="">Error loading beats</option>';
                            });
                    } else {
                        beatSelect.innerHTML = '<option value="">Select Beat</option>';
                    }
                }
            });
        }

        // Hook map style to global theme changes
        window.addEventListener('themeChanged', function() {
            if (map) {
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
        });
    });

    function initMap() {
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

        const center = {
            lat: 20.5937,
            lng: 78.9629
        };
        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 6,
            center: center,
            mapTypeId: 'roadmap',
            styles: isDark ? darkStyle : [],
            mapTypeControl: true,
            mapTypeControlOptions: {
                position: google.maps.ControlPosition.TOP_LEFT
            },
            zoomControl: true,
            zoomControlOptions: {
                position: google.maps.ControlPosition.RIGHT_CENTER
            },
            streetViewControl: false,
            fullscreenControl: true,
        });

        infoWindow = new google.maps.InfoWindow();
        clusterer = new markerClusterer.MarkerClusterer({
            map
        });
    }

    window.resetMap = function() {
        Object.values(layerMarkers).forEach(markers => {
            markers.forEach(m => m.setMap(null));
        });
        clusterer.clearMarkers();
        layerMarkers = {};

        Object.values(layerDataCollections).forEach(data => data.setMap(null));
        layerDataCollections = {};

        Object.values(layerShapes).forEach(shapes => {
            shapes.forEach(s => s.setMap(null));
        });
        layerShapes = {};
        loadedLayers = {};

        document.querySelectorAll('[id^="count_"]').forEach(el => el.textContent = '0');
        document.querySelectorAll('.layer-toggle').forEach(cb => {
            cb.checked = false;
            updateLayerUIState(cb.value, false);
        });
    }

    function loadLayerCounts() {
        document.getElementById('customLoader').style.display = 'flex';
        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams(formData);
        params.append('only_counts', '1');

        fetch(`{{ route('know-your-area.data') }}?${params.toString()}`)
            .then(res => res.json())
            .then(response => {
                document.getElementById('customLoader').style.display = 'none';
                if (response.status === 'SUCCESS') {
                    const counts = response.counts || {};
                    Object.keys(counts).forEach(layerType => {
                        const countEl = document.getElementById('count_' + layerType);
                        if (countEl) countEl.textContent = counts[layerType];
                    });

                    if (response.geofences) {
                        const countGeo = document.getElementById('count_geofences');
                        if (countGeo) countGeo.textContent = response.geofences.length;
                        processGeofences(response.geofences);
                    }
                }
            })
            .catch(err => {
                document.getElementById('customLoader').style.display = 'none';
                console.error('Counts fetch error:', err);
            });
    }

    function handleLayerToggle(layerType, show) {
        updateLayerUIState(layerType, show);
        if (layerType === 'geofences') {
            if (layerShapes.geofences) {
                layerShapes.geofences.forEach(s => s.setMap(show ? map : null));
            }
            return;
        }

        if (show) {
            if (loadedLayers[layerType]) {
                showLayer(layerType);
            } else {
                fetchLayerData(layerType);
            }
        } else {
            hideLayer(layerType);
        }
    }

    function fetchLayerData(layerType) {
        const spinner = document.getElementById('spinner_' + layerType);
        if (spinner) spinner.style.display = 'inline-block';

        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams(formData);
        params.append('layer_types[]', layerType);

        fetch(`{{ route('know-your-area.data') }}?${params.toString()}`)
            .then(res => res.json())
            .then(response => {
                if (spinner) spinner.style.display = 'none';
                if (response.status === 'SUCCESS' && response.data[layerType]) {
                    processLayerFeatures(layerType, response.data[layerType]);
                    loadedLayers[layerType] = true;
                    showLayer(layerType);
                    fitMapToLayers();
                }
            })
            .catch(err => {
                if (spinner) spinner.style.display = 'none';
                console.error(`Error fetching ${layerType}:`, err);
            });
    }

    function processLayerFeatures(layerType, features) {
        const style = LAYER_STYLES[layerType] || {
            strokeColor: '#3b82f6'
        };
        const iconEmoji = LAYER_ICONS[layerType] || '📍';
        const markers = [];

        const dataLayer = new google.maps.Data();
        dataLayer.addGeoJson({
            type: 'FeatureCollection',
            features: features
        });

        dataLayer.setStyle(feature => {
            const isPoint = feature.getGeometry().getType() === 'Point';
            if (isPoint) return {
                visible: false
            };
            return style;
        });

        dataLayer.addListener('click', event => bindPopup(event.feature, event.latLng));
        layerDataCollections[layerType] = dataLayer;

        features.forEach(feature => {
            if (feature.geometry.type === 'Point') {
                if (layerType === 'elephant_movement') return;

                const marker = new google.maps.Marker({
                    position: {
                        lat: feature.geometry.coordinates[1],
                        lng: feature.geometry.coordinates[0]
                    },
                    title: feature.properties.name,
                    icon: {
                        url: `data:image/svg+xml;charset=UTF-8,${encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30"><text y="20" font-size="20">' + iconEmoji + '</text></svg>')}`,
                        scaledSize: new google.maps.Size(30, 30)
                    }
                });

                marker.addListener('click', () => bindPopup(feature, marker.getPosition(), true));
                markers.push(marker);
            }
        });

        layerMarkers[layerType] = markers;
        clusterer.addMarkers(markers);
    }

    function processGeofences(geofences) {
        const shapes = [];
        geofences.forEach(geo => {
            let shape;
            const lat = parseFloat(geo.latitude || geo.lat);
            const lng = parseFloat(geo.longitude || geo.lng);

            const popupContent = `
                <div class="premium-popup">
                    <div class="popup-header" style="background: #3B82F6">
                        <div class="popup-layer-badge">Beat Boundary</div>
                        <h3 class="popup-title" style="color: white; margin: 0;">${geo.name || 'Beat Boundary'}</h3>
                    </div>
                    <div class="popup-body" style="padding: 15px; font-size: 0.9rem;">
                        <table class="popup-table">
                            <tr><td class="popup-label">Address</td><td class="popup-value">${geo.address || 'N/A'}</td></tr>
                            <tr><td class="popup-label">Type</td><td class="popup-value">${geo.type || 'Polygon'}</td></tr>
                            ${geo.radius ? `<tr><td class="popup-label">Radius</td><td class="popup-value">${geo.radius}m</td></tr>` : ''}
                        </table>
                    </div>
                </div>
                `;

            if (geo.type === 'Circle' && lat && lng) {
                shape = new google.maps.Circle({
                    strokeColor: '#3B82F6',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: '#3B82F6',
                    fillOpacity: 0.15,
                    center: {
                        lat: lat,
                        lng: lng
                    },
                    radius: parseFloat(geo.radius),
                    map: null
                });
            } else if (geo.poly_lat_lng) {
                const coords = typeof geo.poly_lat_lng === 'string' ? JSON.parse(geo.poly_lat_lng) : geo
                    .poly_lat_lng;
                const polygonPath = coords.map(p => ({
                    lat: parseFloat(p.lat),
                    lng: parseFloat(p.lng)
                }));
                shape = new google.maps.Polygon({
                    paths: polygonPath,
                    strokeColor: '#3B82F6',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: '#3B82F6',
                    fillOpacity: 0.15,
                    map: null
                });
            }

            if (shape) {
                shape.addListener('click', (event) => {
                    infoWindow.setContent(popupContent);
                    infoWindow.setPosition(event.latLng);
                    infoWindow.open(map);
                });
                shapes.push(shape);
            }
        });
        layerShapes.geofences = shapes;

        const cb = document.getElementById('check_geofences');
        if (cb && cb.checked) {
            layerShapes.geofences.forEach(s => s.setMap(map));
        }
    }

    function showLayer(layerType) {
        if (layerDataCollections[layerType]) layerDataCollections[layerType].setMap(map);
        if (layerMarkers[layerType]) {
            layerMarkers[layerType].forEach(m => m.setMap(map));
            clusterer.addMarkers(layerMarkers[layerType]);
        }
    }

    function hideLayer(layerType) {
        if (layerDataCollections[layerType]) layerDataCollections[layerType].setMap(null);
        if (layerMarkers[layerType]) {
            layerMarkers[layerType].forEach(m => m.setMap(null));
            clusterer.removeMarkers(layerMarkers[layerType]);
        }
    }

    function fitMapToLayers() {
        const bounds = new google.maps.LatLngBounds();
        let hasPoints = false;

        Object.keys(layerDataCollections).forEach(lt => {
            const dataLayer = layerDataCollections[lt];
            if (dataLayer.getMap()) {
                dataLayer.forEach(feature => {
                    feature.getGeometry().forEachLatLng(latLng => {
                        bounds.extend(latLng);
                        hasPoints = true;
                    });
                });
            }
        });

        Object.keys(layerMarkers).forEach(lt => {
            layerMarkers[lt].forEach(m => {
                if (m.getMap()) {
                    bounds.extend(m.getPosition());
                    hasPoints = true;
                }
            });
        });

        Object.keys(layerShapes).forEach(lt => {
            layerShapes[lt].forEach(s => {
                if (s.getMap()) {
                    if (s.getBounds) {
                        bounds.union(s.getBounds());
                    } else if (s.getPath) {
                        s.getPath().forEach(p => bounds.extend(p));
                    }
                    hasPoints = true;
                }
            });
        });

        if (hasPoints) {
            map.fitBounds(bounds);
        }
    }

    function bindPopup(feature, position, isRawFeature = false) {
        const props = isRawFeature ? feature.properties : {};
        if (!isRawFeature) {
            feature.forEachProperty((v, k) => props[k] = v);
        }

        const layerType = props.layer_type || 'Feature';
        const style = LAYER_STYLES[layerType] || {};
        const color = style.strokeColor || '#3B82F6';
        const label = layerType.replace(/_/g, ' ').toUpperCase();

        let popup = `
            <div class="premium-popup">
                <div class="popup-header" style="background: ${color}">
                    <div class="popup-layer-badge">${label}</div>
                    <h3 class="popup-title" style="color: white; margin: 0;">${props.name || 'Details'}</h3>
                </div>
                <div class="popup-body">
                    <table class="popup-table">
            `;

        const skipKeys = ['id', 'name', 'layer_type', 'geometry'];
        Object.keys(props).forEach(key => {
            if (skipKeys.includes(key) || !props[key] || props[key] === 'null') return;
            const displayKey = key.replace(/_/g, ' ').toUpperCase();
            popup +=
                `<tr><td class="popup-label">${displayKey}</td><td class="popup-value">${props[key]}</td></tr>`;
        });

        popup += `</table></div></div>`;
        infoWindow.setContent(popup);
        infoWindow.setPosition(position);
        infoWindow.open(map);
    }
</script>
@endpush

@endsection

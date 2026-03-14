@php
    $hideSidebar = false;
    $hideGlobalFilters = true;
    $hideNavbar = false; // Keep navbar as requested or implied by "header visible"
@endphp

@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>

    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.95);
            --glass-border: rgba(255, 255, 255, 0.3);
            --primary-accent: #6366f1;
            --primary-color: #6366f1;
        }

        .map-container-wrapper {
            position: relative;
            height: calc(100vh - 120px);
            margin: -20px -25px;
            overflow: hidden;
            background: #f8fafc;
        }

        #map {
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .glass-panel {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
        }

        .filter-sidebar {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 380px;
            max-height: calc(100% - 40px);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateX(calc(100% + 40px));
        }

        .filter-sidebar.open {
            transform: translateX(0);
        }

        .drawer-toggle {
            position: absolute;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
            z-index: 1001;
            width: 48px;
            height: 56px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 14px 0 0 14px;
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
            background: #4f46e5;
            padding-right: 5px;
        }

        .drawer-toggle.active {
            right: 400px;
            background: #ef4444;
            border-radius: 14px;
        }

        .sidebar-header {
            padding: 24px 24px 12px;
            border-bottom: none !important;
            position: relative;
        }

        .sidebar-header h5 {
            color: #111827;
            font-weight: 800;
            font-size: 1.4rem;
            margin-bottom: 2px;
            letter-spacing: -0.5px;
        }

        .sidebar-header .sub-title {
            color: #6366f1;
            font-weight: 700;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .sidebar-content {
            padding: 0 20px 20px;
            overflow-y: auto;
            flex: 1;
        }

        .layer-item {
            background: white;
            border-radius: 18px;
            margin-bottom: 10px;
            padding: 12px 18px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            border: 1px solid rgba(0, 0, 0, 0.03);
            transition: all 0.2s ease-out;
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        .layer-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            border-color: rgba(99, 102, 241, 0.1);
        }

        .layer-item.active {
            background: #fdfdfd;
            border-color: rgba(99, 102, 241, 0.2);
        }

        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 14px;
            box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.02);
        }

        .layer-icon-box {
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-right: 14px;
            background: rgba(0, 0, 0, 0.02);
            border-radius: 10px;
        }

        .layer-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #111827;
            flex: 1;
        }

        .count-pill {
            background: #f1f5f9;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 800;
            color: #64748b;
            margin: 0 12px;
            min-width: 36px;
            text-align: center;
        }

        .eye-toggle {
            font-size: 1.3rem;
            color: #e5e7eb;
            transition: color 0.2s, transform 0.2s;
        }

        .eye-toggle:hover {
            transform: scale(1.1);
        }

        .eye-toggle.active {
            color: #10b981;
        }

        .custom-loader {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(6px);
            z-index: 2000;
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            border-radius: 24px;
        }

        .form-select-sm,
        .btn-sm {
            border-radius: 12px;
        }

        .marker-cluster-small div {
            background-color: #10b981 !important;
            color: white;
        }

        .marker-cluster-medium div {
            background-color: #f59e0b !important;
            color: white;
        }

        .marker-cluster-large div {
            background-color: #ef4444 !important;
            color: white;
        }

        /* Premium Popup Styling */
        .leaflet-popup-content-wrapper {
            padding: 0;
            overflow: hidden;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }

        .leaflet-popup-content {
            margin: 0;
            width: 320px !important;
        }

        .popup-header {
            padding: 18px 20px;
            color: white;
            position: relative;
        }

        .popup-layer-badge {
            font-size: 0.65rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 10px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 800;
            margin-bottom: 8px;
            display: inline-block;
        }

        .popup-title {
            font-size: 1.2rem;
            font-weight: 800;
            margin: 0;
            line-height: 1.2;
        }

        .popup-body {
            padding: 10px 0;
            max-height: 350px;
            overflow-y: auto;
        }

        .popup-table {
            width: 100%;
            margin-bottom: 0;
        }

        .popup-table tr {
            border-bottom: 1px solid #f1f5f9;
        }

        .popup-table tr:last-child {
            border-bottom: none;
        }

        .popup-table td {
            padding: 10px 20px;
            font-size: 0.85rem;
        }

        .popup-label {
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            width: 40%;
            font-size: 0.7rem;
            letter-spacing: 0.5px;
        }

        .popup-value {
            color: #1e293b;
            font-weight: 600;
            word-break: break-all;
        }

        .leaflet-popup-tip {
            box-shadow: none;
        }
    </style>

    <div class="map-container-wrapper">
        <div id="map"></div>

        <button class="drawer-toggle" id="drawerToggle">
            <i class="bi bi-layers-half"></i>
            <span>Layers</span>
        </button>

        <div class="filter-sidebar glass-panel shadow-lg">
            <div class="sidebar-header">
                <h5>Map Layers</h5>
                <div class="sub-title">Select to show/hide</div>
            </div>

            <div class="sidebar-content">
                <form id="filterForm" class="mb-4">
                    <div class="row g-2 mb-3">
                        @if ($userRole == 1 || $userRole == 7)
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted mb-1">Region</label>
                                <select id="rangeSelect" name="range_id"
                                    class="form-select form-select-sm border-0 bg-light">
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
                                <label class="form-label small fw-bold text-muted mb-1">Site</label>
                                <select id="beatSelect" name="site_id" class="form-select form-select-sm border-0 bg-light">
                                    <option value="">Site</option>
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

                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted mb-1">Compartment</label>
                            <select id="geofenceSelect" name="geofence_id"
                                class="form-select form-select-sm border-0 bg-light">
                                <option value="">All</option>
                                <!-- Populated via AJAX -->
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted mb-1">Year</label>
                            <select id="yearSelect" name="year" class="form-select form-select-sm border-0 bg-light">
                                <option value="">All</option>
                                @foreach ($availableYears as $year)
                                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100 shadow-sm py-2"
                        style="border-radius: 12px; font-weight: 700;">
                        <i class="bi bi-search me-2"></i>Apply Filters
                    </button>
                </form>

                <div id="layerControls">
                    <div class="layer-item active" id="item_geofences" onclick="toggleLayerUI('geofences')">
                        <div class="status-dot" style="background-color: #6366f1"></div>
                        <div class="layer-icon-box" style="color: #6366f1">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="layer-label">Geofences</div>
                        <div id="count_geofences" class="count-pill">0</div>
                        <div class="eye-toggle active" id="eye_geofences">
                            <i class="bi bi-eye-fill"></i>
                        </div>
                        <input type="checkbox" class="layer-toggle d-none" value="geofences" id="check_geofences" checked>
                    </div>

                    @php
                        $layers = [
                            [
                                'id' => 'drainage',
                                'label' => 'Drainage',
                                'color' => '#3b82f6',
                                'icon' => 'bi-droplet-half',
                            ],
                            [
                                'id' => 'elephant_movement',
                                'label' => 'Elephant Movements',
                                'color' => '#f59e0b',
                                'icon' => 'bi-paw',
                            ],
                            ['id' => 'fire_point', 'label' => 'Fire Points', 'color' => '#ef4444', 'icon' => 'bi-fire'],
                            [
                                'id' => 'forest_boundary',
                                'label' => 'Forest Boundary',
                                'color' => '#10b981',
                                'icon' => 'bi-leaf-fill',
                            ],
                            [
                                'id' => 'plantation_site',
                                'label' => 'Plantation Sites',
                                'color' => '#0ea5e9',
                                'icon' => 'bi-flower1',
                            ],
                            [
                                'id' => 'revenue_forest_land',
                                'label' => 'Revenue Forest Land',
                                'color' => '#a855f7',
                                'icon' => 'bi-globe',
                            ],
                            [
                                'id' => 'water_body',
                                'label' => 'Water Bodies',
                                'color' => '#6366f1',
                                'icon' => 'bi-cloud-rain-fill',
                            ],
                            [
                                'id' => 'beat_boundary',
                                'label' => 'Beat Boundary',
                                'color' => '#eab308',
                                'icon' => 'bi-circle-fill',
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
                            <div class="eye-toggle" id="eye_{{ $layer['id'] }}">
                                <i class="bi bi-eye-fill"></i>
                            </div>
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

        <div id="customLoader" class="custom-loader">
            <div class="spinner-border text-primary mb-2" role="status"></div>
            <span class="small fw-bold">Loading Counts...</span>
        </div>
    </div>

    @push('scripts')
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfBFN6L_HROTd-mS8QqUDRIqskkvHvFYk&libraries=visualization"></script>
        <script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>

        <script>
            let map;
            let infoWindow;
            let clusterer;
            let layerDataCollections = {};
            let layerMarkers = {};
            let layerShapes = {};
            let loadedLayers = {};

            const LAYER_STYLES = {
                'drainage': {
                    strokeColor: '#3b82f6',
                    strokeWeight: 3,
                    fillOpacity: 0
                },
                'elephant_movement': {
                    strokeColor: '#E67E22',
                    strokeWeight: 4,
                    fillOpacity: 0
                },
                'fire_point': {
                    icon: '🔥'
                },
                'forest_boundary': {
                    strokeColor: '#10b981',
                    strokeWeight: 3,
                    fillOpacity: 0.1
                },
                'plantation_site': {
                    strokeColor: '#0ea5e9',
                    strokeWeight: 2,
                    fillOpacity: 0.3
                },
                'revenue_forest_land': {
                    strokeColor: '#a855f7',
                    strokeWeight: 2,
                    fillOpacity: 0.3
                },
                'water_body': {
                    strokeColor: '#6366f1',
                    strokeWeight: 3,
                    fillOpacity: 0.4
                },
                'beat_boundary': {
                    strokeColor: '#eab308',
                    strokeWeight: 4,
                    fillOpacity: 0
                },
                'geofences': {
                    strokeColor: '#6366f1',
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
                'revenue_forest_land': '📜',
                'beat_boundary': '🟡'
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
                                        data.forEach(beat => {
                                            beatSelect.innerHTML +=
                                                `<option value="${beat.id}">${beat.name}</option>`;
                                        });
                                    });
                            } else {
                                beatSelect.innerHTML = '<option value="">Select Beat</option>';
                            }
                        }
                    });
                }

                // Beat/Compartment AJAX
                const beatSelect = document.getElementById('beatSelect');
                if (beatSelect) {
                    const populateCompartments = (beatId) => {
                        const geoSelect = document.getElementById('geofenceSelect');
                        if (geoSelect) {
                            geoSelect.innerHTML = '<option value="">Loading...</option>';
                            if (beatId) {
                                fetch(`{{ url('/filters/compartments') }}/${beatId}`)
                                    .then(res => res.json())
                                    .then(data => {
                                        geoSelect.innerHTML = '<option value="">All</option>';
                                        data.forEach(geo => {
                                            geoSelect.innerHTML +=
                                                `<option value="${geo.id}">${geo.name}</option>`;
                                        });
                                    });
                            } else {
                                geoSelect.innerHTML = '<option value="">All</option>';
                            }
                        }
                    };

                    beatSelect.addEventListener('change', function() {
                        populateCompartments(this.value);
                    });

                    // Trigger on load for pre-selected beat
                    if (beatSelect.value) {
                        populateCompartments(beatSelect.value);
                    }
                }
            });

            function initMap() {
                const center = {
                    lat: 20.5937,
                    lng: 78.9629
                };
                map = new google.maps.Map(document.getElementById("map"), {
                    zoom: 6,
                    center: center,
                    mapTypeId: 'roadmap',
                    mapTypeControl: true,
                    mapTypeControlOptions: {
                        position: google.maps.ControlPosition.TOP_RIGHT,
                    },
                    zoomControl: true,
                    zoomControlOptions: {
                        position: google.maps.ControlPosition.RIGHT_CENTER,
                    },
                    streetViewControl: false,
                    fullscreenControl: true,
                });

                infoWindow = new google.maps.InfoWindow();

                // Initialize marker clusterer
                clusterer = new markerClusterer.MarkerClusterer({
                    map
                });
            }

            function resetMap() {
                // Clear all markers from clusterer and map
                Object.values(layerMarkers).forEach(markers => {
                    markers.forEach(m => m.setMap(null));
                });
                clusterer.clearMarkers();
                layerMarkers = {};

                // Remove all data layer features
                Object.values(layerDataCollections).forEach(data => data.setMap(null));
                layerDataCollections = {};

                // Remove all shapes
                Object.values(layerShapes).forEach(shapes => {
                    shapes.forEach(s => s.setMap(null));
                });
                layerShapes = {};

                loadedLayers = {};

                // Reset counts in UI
                document.querySelectorAll('[id^="count_"]').forEach(el => el.textContent = '0');
                // Uncheck all toggles and reset styles
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
                    strokeColor: '#333'
                };
                const iconEmoji = LAYER_ICONS[layerType] || '📍';
                const markers = [];

                // For Polygons/Lines, we use the Data layer
                const dataLayer = new google.maps.Data();
                dataLayer.addGeoJson({
                    type: 'FeatureCollection',
                    features: features
                });

                dataLayer.setStyle(feature => {
                    const isPoint = feature.getGeometry().getType() === 'Point';
                    if (isPoint) return {
                        visible: false
                    }; // Hide points in Data layer, use Markers instead
                    return style;
                });

                dataLayer.addListener('click', event => {
                    bindPopup(event.feature, event.latLng);
                });

                layerDataCollections[layerType] = dataLayer;

                // For Points, we use Markers and Clusterer
                features.forEach(feature => {
                    if (feature.geometry.type === 'Point') {
                        // Skip elephant movement points as per user request (they are now lines)
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

                        marker.addListener('click', () => {
                            bindPopup(feature, marker.getPosition(), true);
                        });

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
                                                    <div class="popup-header" style="background: #6366f1">
                                                        <div class="popup-layer-badge">GEOFENCE</div>
                                                        <h3 class="popup-title" style="color: white; margin: 0;">${geo.name || 'Geofence'}</h3>
                                                    </div>
                                                    <div class="popup-body" style="padding: 15px; font-size: 0.9rem;">
                                                        <div class="mb-2"><strong>Address:</strong> ${geo.address || 'N/A'}</div>
                                                        <div class="mb-2"><strong>Type:</strong> ${geo.type || 'Polygon'}</div>
                                                        ${geo.radius ? `<div class="mb-0"><strong>Radius:</strong> ${geo.radius}m</div>` : ''}
                                                    </div>
                                                </div>
                                            `;

                    if (geo.type === 'Circle' && lat && lng) {
                        shape = new google.maps.Circle({
                            strokeColor: LAYER_STYLES.geofences.strokeColor,
                            strokeOpacity: 0.8,
                            strokeWeight: LAYER_STYLES.geofences.strokeWeight,
                            fillColor: LAYER_STYLES.geofences.strokeColor,
                            fillOpacity: 0.2,
                            center: {
                                lat: lat,
                                lng: lng
                            },
                            radius: parseFloat(geo.radius),
                            map: null // Controlled by toggle
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
                            strokeColor: LAYER_STYLES.geofences.strokeColor,
                            strokeOpacity: 0.8,
                            strokeWeight: LAYER_STYLES.geofences.strokeWeight,
                            fillColor: LAYER_STYLES.geofences.strokeColor,
                            fillOpacity: 0.2,
                            map: null // Controlled by toggle
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

                // Initial visibility
                const cb = document.getElementById('check_geofences');
                if (cb && cb.checked) {
                    layerShapes.geofences.forEach(s => s.setMap(map));
                }
            }

            function showLayer(layerType) {
                if (layerDataCollections[layerType]) {
                    layerDataCollections[layerType].setMap(map);
                }
                if (layerMarkers[layerType]) {
                    // Add markers to clusterer, which handles adding to map
                    clusterer.addMarkers(layerMarkers[layerType]);
                }
            }

            function hideLayer(layerType) {
                if (layerDataCollections[layerType]) {
                    layerDataCollections[layerType].setMap(null);
                }
                if (layerMarkers[layerType]) {
                    // Remove markers from clusterer, which handles removing from map
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
                        if (m.getMap()) { // Check if marker is currently on the map
                            bounds.extend(m.getPosition());
                            hasPoints = true;
                        }
                    });
                });

                // Add shape bounds
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
                const color = style.strokeColor || '#3b82f6';
                const label = layerType.replace(/_/g, ' ').toUpperCase();

                let popup = `
                                                                        <div class="premium-popup">
                                                                            <div class="popup-header" style="background: ${color}">
                                                                                <div class="popup-layer-badge">${label}</div>
                                                                                <h3 class="popup-title" style="color: white; margin: 0;">${props.name || 'Details'}</h3>
                                                                            </div>
                                                                            <div class="popup-body" style="max-height: 250px; overflow-y: auto; padding: 10px;">
                                                                                <table class="popup-table" style="width: 100%; font-size: 0.85rem;">
                                                                    `;

                const skipKeys = ['id', 'name', 'layer_type', 'geometry'];
                Object.keys(props).forEach(key => {
                    if (skipKeys.includes(key) || !props[key] || props[key] === 'null') return;
                    const displayKey = key.replace(/_/g, ' ').toUpperCase();
                    popup += `
                                                                            <tr style="border-bottom: 1px solid #eee;">
                                                                                <td style="padding: 5px; color: #64748b; font-weight: 700; font-size: 0.7rem;">${displayKey}</td>
                                                                                <td style="padding: 5px; color: #1e293b; font-weight: 600;">${props[key]}</td>
                                                                            </tr>
                                                                        `;
                });

                popup += `
                                                                                </table>
                                                                            </div>
                                                                        </div>
                                                                    `;

                infoWindow.setContent(popup);
                infoWindow.setPosition(position);
                infoWindow.open(map);
            }
        </script>
    @endpush
@endsection

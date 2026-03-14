@extends('layouts.app')

@php
    $hideGlobalFilters = true;
    $hideBackground = true;
@endphp

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f6f8;
        }

        .glass-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid rgba(0, 0, 0, 0.04);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
        }

        .kpi-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #8792a2;
        }

        .kpi-value {
            font-size: 2rem;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.5px;
        }

        .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .map-container {
            height: 500px;
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
        }

        .map-overlay {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 5;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            padding: 8px 16px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .chart-box {
            height: 260px;
            width: 100%;
            position: relative;
        }

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        [data-bs-theme="dark"] body {
            background-color: #0f172a;
        }

        [data-bs-theme="dark"] .glass-card {
            background-color: #1e293b;
            border-color: #334155;
        }

        [data-bs-theme="dark"] .kpi-value {
            color: #f8fafc;
        }

        [data-bs-theme="dark"] .map-overlay {
            background: rgba(30, 41, 59, 0.9);
            border-color: #475569;
            color: #fff;
        }
    </style>

    <div class="container-fluid py-4 px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.5px;">Spatial Intelligence</h3>
                <p class="text-muted small mb-0">Monitor forest compartments, firelines, and critical zones. Analytics
                    represent 100% of data.</p>
            </div>
            <button class="btn btn-dark rounded-pill px-4 py-2 shadow-sm fw-medium d-flex align-items-center transition"
                data-bs-toggle="offcanvas" data-bs-target="#addFeatureCanvas">
                <i data-lucide="plus" class="me-2" style="width: 18px; height: 18px;"></i> Add Feature
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-3"><i data-lucide="check-circle" class="me-2"
                    style="width:18px;"></i> {{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-3">{{ $errors->first() }}</div>
        @endif

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-title mb-1">Total Features</div>
                            <div class="kpi-value">{{ number_format($kpis['total']) }}</div>
                        </div>
                        <div class="icon-box bg-primary bg-opacity-10 text-primary"><i data-lucide="layers"
                                stroke-width="2.5"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-title mb-1">Fire Zones & Points</div>
                            <div class="kpi-value">{{ number_format($kpis['fire_points']) }}</div>
                        </div>
                        <div class="icon-box bg-danger bg-opacity-10 text-danger"><i data-lucide="flame"
                                stroke-width="2.5"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-title mb-1">Forest Blocks</div>
                            <div class="kpi-value">{{ number_format($kpis['forest_land']) }}</div>
                        </div>
                        <div class="icon-box bg-success bg-opacity-10 text-success"><i data-lucide="tree-pine"
                                stroke-width="2.5"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-title mb-1">Drainage Lines</div>
                            <div class="kpi-value">{{ number_format($kpis['drainage']) }}</div>
                        </div>
                        <div class="icon-box bg-info bg-opacity-10 text-info"><i data-lucide="waves" stroke-width="2.5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="glass-card p-2 position-relative">
                    <div class="position-absolute bottom-0 start-0 m-3 z-3">
                        <span class="badge bg-white text-dark border shadow-sm px-3 py-2 opacity-75"
                            title="Showing max 1500 features per category to ensure smooth browser performance.">
                            <i data-lucide="zap" style="width: 14px; margin-right: 4px;" class="text-warning"></i> Map
                            Optimized (Sampled)
                        </span>
                    </div>

                    <div class="map-overlay">
                        <div class="d-flex align-items-center gap-2 border-end pe-3">
                            <span class="small fw-bold text-muted">View:</span>
                            <div class="btn-group btn-group-sm">
                                <input type="radio" class="btn-check" name="mapView" id="viewStandard" checked
                                    onchange="toggleHeatmap(false)">
                                <label class="btn btn-outline-dark rounded-start-pill px-3"
                                    for="viewStandard">Standard</label>
                                <input type="radio" class="btn-check" name="mapView" id="viewHeatmap"
                                    onchange="toggleHeatmap(true)">
                                <label class="btn btn-outline-danger rounded-end-pill px-3" for="viewHeatmap"><i
                                        data-lucide="flame" style="width: 14px; margin-bottom: 2px;"></i> Heatmap</label>
                            </div>
                        </div>

                        <div class="dropdown">
                            <button class="btn btn-sm btn-light rounded-pill border fw-medium px-3" type="button"
                                data-bs-toggle="dropdown">
                                <i data-lucide="filter" class="me-1" style="width: 14px;"></i> Filter Layers
                            </button>
                            <ul class="dropdown-menu shadow-lg border-0 rounded-3 p-2" style="min-width: 200px;">
                                <li><label class="dropdown-item rounded py-2"><input type="checkbox"
                                            class="form-check-input me-2" value="all" checked> <b>All Layers</b></label>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><label class="dropdown-item rounded"><input type="checkbox"
                                            class="form-check-input me-2 layer-cb" value="revenue_forest_land" checked>
                                        <span class="badge bg-success rounded-circle d-inline-block p-1"></span> Forest
                                        Land</label></li>
                                <li><label class="dropdown-item rounded"><input type="checkbox"
                                            class="form-check-input me-2 layer-cb" value="kisam_land" checked> <span
                                            class="badge bg-warning rounded-circle d-inline-block p-1"></span> Kisam
                                        Land</label></li>
                                <li><label class="dropdown-item rounded"><input type="checkbox"
                                            class="form-check-input me-2 layer-cb" value="drainage" checked> <span
                                            class="badge bg-info rounded-circle d-inline-block p-1"></span>
                                        Drainage</label></li>
                                <li><label class="dropdown-item rounded"><input type="checkbox"
                                            class="form-check-input me-2 layer-cb" value="fire_point" checked> <span
                                            class="badge bg-danger rounded-circle d-inline-block p-1"></span> Fire
                                        Points</label></li>
                                <li><label class="dropdown-item rounded"><input type="checkbox"
                                            class="form-check-input me-2 layer-cb" value="fire_lines" checked> <span
                                            class="badge bg-danger rounded-circle d-inline-block p-1"></span> Fire
                                        Lines</label></li>
                            </ul>
                        </div>

                        <div class="btn-group btn-group-sm ms-2">
                            <button onclick="map.setMapTypeId('terrain')"
                                class="btn btn-light border fw-medium px-3 rounded-start-pill">Terrain</button>
                            <button onclick="map.setMapTypeId('satellite')"
                                class="btn btn-light border fw-medium px-3 rounded-end-pill">Sat</button>
                        </div>
                    </div>

                    <div id="googleMap" class="map-container"></div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-4">
                <div class="glass-card p-4 h-100">
                    <h6 class="fw-bold mb-4 text-dark">Feature Distribution</h6>
                    <div class="chart-box"><canvas id="layerChart"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="glass-card p-4 h-100">
                    <h6 class="fw-bold mb-4 text-dark">Mapping Activity Trend</h6>
                    <div class="chart-box"><canvas id="timelineChart"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="glass-card p-4 h-100">
                    <h6 class="fw-bold mb-4 text-dark">Top Active Sites</h6>
                    <div class="chart-box"><canvas id="siteChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="addFeatureCanvas"
        style="width: 420px; border-left: none; box-shadow: -10px 0 30px rgba(0,0,0,0.05);">
        <div class="offcanvas-header border-bottom bg-light">
            <h5 class="offcanvas-title fw-bold text-dark">Add Spatial Feature</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body p-4 bg-white">
            <form method="POST" action="{{ route('beat_features.store') }}">
                @csrf

                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted text-uppercase tracking-wide">1.
                        Classification</label>
                    <select name="layer_type" class="form-select form-select-lg mb-3 fs-6 bg-light border-0" required>
                        <option value="" disabled selected>Select Layer Type...</option>
                        <option value="revenue_forest_land">Revenue Forest Land (Polygon)</option>
                        <option value="kisam_land">Kisam Land (Polygon)</option>
                        <option value="drainage">Drainage (Line)</option>
                        <option value="fire_point">Fire Point (Point)</option>
                        <option value="fire_lines">Fire Lines (Line)</option>
                        <option value="plantation_site">Plantation Site (Polygon)</option>
                    </select>
                    <input type="text" name="name" class="form-control bg-light border-0"
                        placeholder="Feature Name (Optional)">
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted text-uppercase tracking-wide">2. Identifiers</label>
                    <div class="row g-2">
                        <div class="col-6"><input type="number" name="site_id" class="form-control bg-light border-0"
                                placeholder="Site ID"></div>
                        <div class="col-6"><input type="number" name="geofence_id"
                                class="form-control bg-light border-0" placeholder="Geofence ID"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted text-uppercase tracking-wide">3. Spatial Data</label>
                    <select name="geometry_type" class="form-select bg-light border-0 mb-2" required>
                        <option value="Point">Point (Single Location)</option>
                        <option value="LineString">LineString (Path/Line)</option>
                        <option value="Polygon">Polygon (Boundary)</option>
                    </select>
                    <textarea name="coordinates" rows="3" class="form-control bg-light border-0 font-monospace small" required
                        placeholder="[[lat, lng], [lat, lng]]"></textarea>
                    <div class="form-text" style="font-size: 0.7rem;">JSON Array Format.</div>
                </div>

                <div class="mb-5">
                    <label class="form-label small fw-bold text-muted text-uppercase tracking-wide">4. Attributes
                        (JSON)</label>
                    <textarea name="attributes" rows="3" class="form-control bg-light border-0 font-monospace small"
                        placeholder='{"plot_no": "194", "area": "0.09"}'></textarea>
                </div>

                <button type="submit" class="btn btn-dark w-100 py-3 rounded-pill fw-bold shadow-sm">Save to
                    Database</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfBFN6L_HROTd-mS8QqUDRIqskkvHvFYk&libraries=visualization">
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            lucide.createIcons();
        });

        // IMPORTANT: We use $mapFeatures here, NOT $features
        const rawFeatures = @json($mapFeatures);
        const layerDist = @json($layerDistribution);
        const timelineDist = @json($timelineData);
        const siteDist = @json($topSites);

        // ==========================================
        // GOOGLE MAPS LOGIC
        // ==========================================
        let map, infoWindow, heatmapLayer;
        let mapObjects = [];

        const styleConfig = {
            'revenue_forest_land': {
                color: '#10b981',
                fillOpacity: 0.35,
                type: 'polygon'
            },
            'kisam_land': {
                color: '#f59e0b',
                fillOpacity: 0.35,
                type: 'polygon'
            },
            'drainage': {
                color: '#0ea5e9',
                weight: 4,
                type: 'line'
            },
            'fire_lines': {
                color: '#ef4444',
                weight: 4,
                type: 'line'
            },
            'fire_point': {
                color: '#ef4444',
                type: 'point',
                icon: createSvgMarker('#ef4444')
            },
            'plantation_site': {
                color: '#8b5cf6',
                fillOpacity: 0.4,
                type: 'polygon'
            },
            'default': {
                color: '#64748b',
                fillOpacity: 0.2,
                weight: 2,
                icon: createSvgMarker('#64748b')
            }
        };

        function createSvgMarker(color) {
            return {
                path: google.maps.SymbolPath.CIRCLE,
                fillColor: color,
                fillOpacity: 1,
                strokeColor: '#ffffff',
                strokeWeight: 2,
                scale: 6
            };
        }

        function initMap() {
            map = new google.maps.Map(document.getElementById('googleMap'), {
                zoom: 10,
                center: {
                    lat: 21.785,
                    lng: 83.901
                },
                mapTypeId: 'terrain',
                disableDefaultUI: true,
                zoomControl: true,
            });

            infoWindow = new google.maps.InfoWindow();
            renderFeatures();
            setupLayerFilters();
        }

        function parseCoordinates(coordString, geomType) {
            try {
                const arr = JSON.parse(coordString);
                if (geomType === 'Point') {
                    const pt = Array.isArray(arr[0]) ? arr[0] : arr;
                    return {
                        lat: parseFloat(pt[0]),
                        lng: parseFloat(pt[1])
                    };
                }
                return arr.map(pt => ({
                    lat: parseFloat(pt[0]),
                    lng: parseFloat(pt[1])
                }));
            } catch (e) {
                return null;
            }
        }

        function renderFeatures() {
            const heatPoints = [];

            // 1. Setup Heatmap first (hidden initially)
            heatmapLayer = new google.maps.visualization.HeatmapLayer({
                data: heatPoints,
                radius: 35,
                opacity: 0.8,
                map: null
            });

            // 2. Asynchronous Chunking (Prevents browser freeze)
            let currentIndex = 0;
            const chunkSize = 50; // Draw 50 shapes at a time

            function processChunk() {
                const end = Math.min(currentIndex + chunkSize, rawFeatures.length);

                for (; currentIndex < end; currentIndex++) {
                    const feature = rawFeatures[currentIndex];
                    const path = parseCoordinates(feature.coordinates, feature.geometry_type);
                    if (!path) continue;

                    const style = styleConfig[feature.layer_type] || styleConfig['default'];
                    let mapItem;

                    if (feature.geometry_type === 'Polygon') {
                        mapItem = new google.maps.Polygon({
                            paths: path,
                            strokeColor: style.color,
                            strokeWeight: 2,
                            fillColor: style.color,
                            fillOpacity: style.fillOpacity,
                            map: map
                        });
                    } else if (feature.geometry_type === 'LineString') {
                        mapItem = new google.maps.Polyline({
                            path: path,
                            strokeColor: style.color,
                            strokeWeight: style.weight || 3,
                            map: map
                        });
                    } else if (feature.geometry_type === 'Point') {
                        mapItem = new google.maps.Marker({
                            position: path,
                            icon: style.icon,
                            map: map
                        });
                        // Add point to heatmap array (heatmap will update automatically because it uses MVCArray)
                        heatPoints.push(new google.maps.LatLng(path.lat, path.lng));
                    }

                    if (mapItem) {
                        mapItem.addListener('click', (e) => {
                            let attrs = {};
                            try {
                                attrs = JSON.parse(feature.attributes);
                            } catch (err) {}
                            let html = `<div style="font-family: 'Inter', sans-serif; min-width: 200px;">
                            <h6 style="margin-bottom:8px; font-weight:700; color:#111827; border-bottom:1px solid #e5e7eb; padding-bottom:5px;">
                                ${feature.layer_type.replace(/_/g, ' ').toUpperCase()}
                            </h6><div style="font-size:12px; color:#4b5563;">`;

                            html += `<div><strong>Site ID:</strong> ${feature.site_id || 'N/A'}</div>`;
                            for (const [k, v] of Object.entries(attrs)) {
                                if (!['OID_', 'FolderPath', 'SymbolID', 'AltMode'].includes(k)) {
                                    html +=
                                        `<div style="margin-top:3px;"><strong>${k.replace(/_/g, ' ')}:</strong> ${v}</div>`;
                                }
                            }
                            html += `</div></div>`;
                            infoWindow.setContent(html);
                            infoWindow.setPosition(e.latLng);
                            infoWindow.open(map);
                        });

                        mapObjects.push({
                            item: mapItem,
                            type: feature.layer_type
                        });
                    }
                }

                // If there are more features to process, delay the next chunk by 10ms
                if (currentIndex < rawFeatures.length) {
                    setTimeout(processChunk, 10);
                }
            }

            // Start processing the first chunk
            processChunk();
        }

        function toggleHeatmap(enable) {
            if (enable) {
                mapObjects.forEach(obj => obj.item.setMap(null));
                heatmapLayer.setMap(map);
            } else {
                heatmapLayer.setMap(null);
                const active = Array.from(document.querySelectorAll('.layer-cb')).filter(i => i.checked).map(i => i.value);
                mapObjects.forEach(obj => obj.item.setMap(active.includes(obj.type) ? map : null));
            }
        }

        function setupLayerFilters() {
            const checkboxes = document.querySelectorAll('.layer-cb');
            const allCheckbox = document.querySelector('input[value="all"]');

            checkboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    const active = Array.from(checkboxes).filter(i => i.checked).map(i => i.value);
                    if (!heatmapLayer.getMap()) {
                        mapObjects.forEach(obj => obj.item.setMap(active.includes(obj.type) ? map : null));
                    }
                    allCheckbox.checked = active.length === checkboxes.length;
                });
            });

            allCheckbox.addEventListener('change', (e) => {
                checkboxes.forEach(cb => cb.checked = e.target.checked);
                if (!heatmapLayer.getMap()) {
                    mapObjects.forEach(obj => obj.item.setMap(e.target.checked ? map : null));
                }
            });
        }

        // ==========================================
        // MODERN CHART.JS LOGIC
        // ==========================================
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#6b7280';

        function initCharts() {
            // 1. Layer Distribution (Doughnut)
            new Chart(document.getElementById('layerChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(layerDist).map(l => l.replace(/_/g, ' ').toUpperCase()),
                    datasets: [{
                        data: Object.values(layerDist),
                        backgroundColor: ['#10b981', '#f59e0b', '#0ea5e9', '#ef4444', '#8b5cf6', '#64748b'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 12,
                                usePointStyle: true
                            }
                        }
                    }
                }
            });

            // 2. Timeline (Line)
            new Chart(document.getElementById('timelineChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: Object.keys(timelineDist),
                    datasets: [{
                        label: 'Features Added',
                        data: Object.values(timelineDist),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#3b82f6',
                        pointBorderWidth: 2,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            border: {
                                dash: [4, 4]
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // 3. Top Sites (Bar)
            new Chart(document.getElementById('siteChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: Object.keys(siteDist).map(id => `Site ${id}`),
                    datasets: [{
                        label: 'Features',
                        data: Object.values(siteDist),
                        backgroundColor: '#8b5cf6',
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            border: {
                                dash: [4, 4]
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        window.addEventListener('load', () => {
            initMap();
            initCharts();
        });
    </script>
@endsection

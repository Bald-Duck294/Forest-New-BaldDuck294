@extends('layouts.app')

@php
    $hideGlobalFilters = true;
    $hideBackground = true;
@endphp

@section('title', 'Compensation Tracking')

@section('content')
    <style>
        /* =========================================
           LOCAL COMPONENT STYLES 
           (Hooked to Global Sapphire Variables)
        ========================================= */

        /* Premium Card Styling */
        .dash-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }

        .hover-lift { cursor: pointer; }
        .hover-lift:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
            border-color: var(--sapphire-primary);
        }

        /* KPI Typography */
        .kpi-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .kpi-value {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--text-main);
            line-height: 1;
        }

        .kpi-icon-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.8;
        }

        /* Soft Badges */
        .badge-soft {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-soft-success { background: rgba(16, 185, 129, 0.15); color: var(--sapphire-success); }
        .badge-soft-primary { background: rgba(59, 130, 246, 0.15); color: var(--sapphire-primary); }
        .badge-soft-info    { background: rgba(6, 182, 212, 0.15); color: #06b6d4; }
        .badge-soft-warning { background: rgba(245, 158, 11, 0.15); color: var(--sapphire-warning); }
        .badge-soft-danger  { background: rgba(239, 68, 68, 0.15); color: var(--sapphire-danger); }
        .badge-soft-muted   { background: rgba(100, 116, 139, 0.15); color: var(--text-muted); }

        /* Action Buttons */
        .btn-sapphire {
            background-color: var(--sapphire-primary);
            color: #ffffff;
            border: none;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
        }
        .btn-sapphire:hover { opacity: 0.9; transform: translateY(-1px); color: #ffffff; }

        .btn-sapphire-outline {
            background-color: transparent;
            color: var(--text-main);
            border: 1px solid var(--border-color);
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
        }
        .btn-sapphire-outline:hover, .btn-sapphire-outline.active { 
            background-color: var(--table-hover); 
            color: var(--sapphire-primary); 
            border-color: var(--sapphire-primary); 
        }

        /* Map & Chart Containers */
        .map-container {
            height: 450px;
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .chart-container {
            position: relative;
            height: 380px;
            width: 100%;
        }

        /* Dash Tables */
        .dash-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        .dash-table th { 
            color: var(--text-muted); font-weight: 600; font-size: 0.75rem; 
            border-bottom: 1px solid var(--border-color); padding: 1rem; 
            background-color: transparent !important; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .dash-table td { 
            color: var(--text-main); font-weight: 500; font-size: 0.85rem; 
            border-bottom: 1px dashed var(--border-color); padding: 1rem; 
            vertical-align: middle; background-color: transparent !important; 
        }
        .dash-table tr:hover td { background-color: var(--table-hover) !important; }
        .dash-table tr:last-child td { border-bottom: none; }

        /* Map Toggle Switches */
        .map-switch-group {
            display: inline-flex;
            background: var(--bg-body);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
        }
        .map-switch-btn {
            background: transparent;
            color: var(--text-muted);
            border: none;
            padding: 4px 12px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .map-switch-btn.active {
            background: var(--table-hover);
            color: var(--sapphire-primary);
        }
        .map-switch-btn:not(:last-child) {
            border-right: 1px solid var(--border-color);
        }
        
        /* Checkbox overrides for map layers */
        .layer-btn-check { display: none; }
        .layer-btn-label {
            padding: 4px 12px; font-size: 0.8rem; font-weight: 600; border-radius: 6px; cursor: pointer;
            border: 1px solid var(--border-color); color: var(--text-muted); background: transparent; transition: all 0.2s ease;
        }
        .layer-btn-check:checked + .layer-btn-label.heat { background: rgba(239, 68, 68, 0.1); color: var(--sapphire-danger); border-color: var(--sapphire-danger); }
        .layer-btn-check:checked + .layer-btn-label.pin { background: rgba(59, 130, 246, 0.1); color: var(--sapphire-primary); border-color: var(--sapphire-primary); }

    </style>

    <div class="container-fluid py-4 px-4">
        
        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--text-main);">Compensation Tracking</h3>
                <p class="text-muted small mb-0">Manage wildlife conflict & damage claims</p>
            </div>
            <div>
                <button class="btn-sapphire shadow-sm" data-bs-toggle="offcanvas" data-bs-target="#newIncidentOffcanvas">
                    <i data-lucide="plus" style="width: 16px; height: 16px;"></i> New Incident
                </button>
            </div>
        </div>

        {{-- KPI CARDS --}}
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="dash-card hover-lift h-100 p-4">
                    <div class="d-flex justify-content-between h-100">
                        <div class="d-flex flex-column justify-content-between">
                            <span class="kpi-title">Total Incidents</span>
                            <span class="kpi-value">{{ $kpis['total'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex flex-column justify-content-between align-items-end">
                            <span class="badge-soft badge-soft-success">Updated Live</span>
                            <div class="kpi-icon-wrapper" style="color: var(--text-muted);">
                                <i data-lucide="layers" style="width: 32px; height: 32px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="dash-card hover-lift h-100 p-4">
                    <div class="d-flex justify-content-between h-100">
                        <div class="d-flex flex-column justify-content-between">
                            <span class="kpi-title">Crop Damage</span>
                            <span class="kpi-value">{{ $kpis['crop'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex flex-column justify-content-between align-items-end">
                            <span class="badge-soft badge-soft-muted">Verified</span>
                            <div class="kpi-icon-wrapper" style="color: var(--sapphire-success);">
                                <i data-lucide="wheat" style="width: 32px; height: 32px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="dash-card hover-lift h-100 p-4">
                    <div class="d-flex justify-content-between h-100">
                        <div class="d-flex flex-column justify-content-between">
                            <span class="kpi-title">House Damage</span>
                            <span class="kpi-value">{{ $kpis['house'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex flex-column justify-content-between align-items-end">
                            <span class="badge-soft badge-soft-muted">Verified</span>
                            <div class="kpi-icon-wrapper" style="color: #06b6d4;">
                                <i data-lucide="home" style="width: 32px; height: 32px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="dash-card hover-lift h-100 p-4">
                    <div class="d-flex justify-content-between h-100">
                        <div class="d-flex flex-column justify-content-between">
                            <span class="kpi-title">Pending Verification</span>
                            <span class="kpi-value">{{ $kpis['pending'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex flex-column justify-content-between align-items-end">
                            <span class="badge-soft badge-soft-danger">Requires Survey</span>
                            <div class="kpi-icon-wrapper" style="color: var(--sapphire-danger);">
                                <i data-lucide="alert-circle" style="width: 32px; height: 32px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MAP & CHART --}}
        <div class="row g-4 mb-4">
            {{-- Map Column --}}
            <div class="col-lg-8">
                <div class="dash-card h-100 p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
                        <h5 class="fw-bold mb-0 d-flex align-items-center" style="color: var(--text-main);">
                            <i data-lucide="map" style="color: var(--sapphire-success); width: 20px; height: 20px;" class="me-2"></i> Geospatial Hotspots
                        </h5>

                        <div class="d-flex gap-2 flex-wrap">
                            {{-- Layer Toggles --}}
                            <div class="d-flex gap-1">
                                <input type="checkbox" class="layer-btn-check" id="toggleHeatmap" checked onchange="toggleMapLayer('heatmap', this.checked)">
                                <label class="layer-btn-label heat shadow-sm" for="toggleHeatmap">Heatmap</label>

                                <input type="checkbox" class="layer-btn-check" id="togglePins" checked onchange="toggleMapLayer('pins', this.checked)">
                                <label class="layer-btn-label pin shadow-sm" for="togglePins">Pins</label>
                            </div>

                            {{-- Filter Group --}}
                            <div class="map-switch-group shadow-sm">
                                <button onclick="filterMapData('all', this)" class="map-switch-btn map-filter-btn active">All</button>
                                <button onclick="filterMapData('Crop Damage', this)" class="map-switch-btn map-filter-btn">Crop</button>
                                <button onclick="filterMapData('House Damage', this)" class="map-switch-btn map-filter-btn">House</button>
                            </div>
                        </div>
                    </div>
                    <div id="map-anukampa" class="map-container"></div>
                </div>
            </div>

            {{-- Chart Column --}}
            <div class="col-lg-4">
                <div class="dash-card h-100 p-4 d-flex flex-column">
                    <h5 class="fw-bold mb-4 d-flex align-items-center" style="color: var(--text-main);">
                        <i data-lucide="bar-chart-2" style="color: var(--sapphire-warning); width: 20px; height: 20px;" class="me-2"></i> Incidents by Range
                    </h5>
                    <div class="chart-container flex-grow-1">
                        <canvas id="rangeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE: RECENT CLAIMS --}}
        <div class="dash-card p-0 overflow-hidden mb-4">
            <div class="d-flex justify-content-between align-items-center p-4" style="border-bottom: 1px solid var(--border-color);">
                <h5 class="fw-bold mb-0" style="color: var(--text-main);">Recent Claims Database</h5>
                <a href="{{ route('anukampa.claims') }}" class="btn-sapphire-outline px-3 shadow-sm">View All</a>
            </div>

            <div class="table-responsive">
                <table class="table dash-table align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4">Victim Name</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Est. Loss</th>
                            <th>Date</th>
                            <th class="pe-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentClaims ?? [] as $claim)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold" style="color: var(--text-main);">{{ $claim->victim_name }}</div>
                                    <div class="font-monospace" style="color: var(--text-muted); font-size: 0.75rem;">{{ $claim->contact_number }}</div>
                                </td>
                                <td>
                                    <div style="color: var(--text-main);">{{ $claim->range }}</div>
                                    <div style="color: var(--text-muted); font-size: 0.8rem;">{{ $claim->village_name }}</div>
                                </td>
                                <td>
                                    <span class="badge-soft {{ $claim->incident_type == 'Crop Damage' ? 'badge-soft-success' : 'badge-soft-info' }}">
                                        {{ $claim->incident_type }}
                                    </span>
                                </td>
                                <td class="font-monospace fw-semibold" style="color: var(--text-muted);">
                                    ₹ {{ $claim->estimated_loss ? number_format($claim->estimated_loss, 2) : '--' }}
                                </td>
                                <td style="color: var(--text-muted); font-size: 0.85rem;">
                                    {{ \Carbon\Carbon::parse($claim->incident_date)->format('M d, Y') }}
                                </td>
                                <td class="pe-4">
                                    <span class="badge-soft 
                                        {{ $claim->status == 'Pending' ? 'badge-soft-warning' : '' }}
                                        {{ $claim->status == 'Verified' ? 'badge-soft-info' : '' }}
                                        {{ $claim->status == 'Compensated' ? 'badge-soft-success' : '' }}
                                        {{ $claim->status == 'Rejected' ? 'badge-soft-danger' : '' }}">
                                        {{ $claim->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="py-3">
                                        <i data-lucide="database" style="width: 48px; height: 48px; color: var(--text-muted); opacity: 0.3;"></i>
                                        <p class="mt-2 mb-0" style="color: var(--text-muted);">No recent claims found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfBFN6L_HROTd-mS8QqUDRIqskkvHvFYk&libraries=visualization"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            lucide.createIcons();
            initMap();
            initChart();

            // Re-render chart and map when theme changes
            window.addEventListener('themeChanged', () => {
                initChart();
                updateMapStyle();
            });
        });

        // ==========================================
        // GOOGLE MAPS LOGIC
        // ==========================================
        let map, heatmap;
        let markers = [];
        let infoWindow;
        const mapData = @json($recentClaims ?? []);

        function updateMapStyle() {
            if (!map) return;
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            const darkStyle = [
                { elementType: "geometry", stylers: [{ color: "#1e293b" }] },
                { elementType: "labels.text.stroke", stylers: [{ color: "#1e293b" }] },
                { elementType: "labels.text.fill", stylers: [{ color: "#94a3b8" }] },
                { featureType: "water", elementType: "geometry", stylers: [{ color: "#0f172a" }] },
                { featureType: "poi", elementType: "labels", stylers: [{ visibility: "off" }] }
            ];
            map.setOptions({ styles: isDark ? darkStyle : [] });
        }

        function initMap() {
            const centerLat = mapData.length > 0 ? parseFloat(mapData[0].latitude) : 21.1458;
            const centerLng = mapData.length > 0 ? parseFloat(mapData[0].longitude) : 79.0882;

            map = new google.maps.Map(document.getElementById('map-anukampa'), {
                zoom: 10,
                center: { lat: centerLat, lng: centerLng },
                mapTypeId: 'roadmap',
                streetViewControl: false,
                mapTypeControlOptions: { mapTypeIds: ['roadmap', 'satellite', 'terrain'] }
            });

            updateMapStyle(); // Apply dark mode if active
            infoWindow = new google.maps.InfoWindow();
            renderMapData('all');
        }

        function renderMapData(filterType) {
            markers.forEach(m => m.marker.setMap(null));
            markers = [];
            if (heatmap) heatmap.setMap(null);

            const filteredData = filterType === 'all' ? mapData : mapData.filter(loc => loc.incident_type === filterType);
            const heatPoints = filteredData.map(loc => new google.maps.LatLng(parseFloat(loc.latitude), parseFloat(loc.longitude)));

            // Heatmap Layer
            heatmap = new google.maps.visualization.HeatmapLayer({
                data: heatPoints,
                map: document.getElementById('toggleHeatmap').checked ? map : null,
                radius: 25,
                opacity: 0.7,
                gradient: [
                    'rgba(0, 255, 255, 0)', 'rgba(0, 255, 255, 1)', 'rgba(0, 191, 255, 1)',
                    'rgba(0, 127, 255, 1)', 'rgba(0, 63, 255, 1)', 'rgba(0, 0, 255, 1)',
                    'rgba(0, 0, 223, 1)', 'rgba(0, 0, 191, 1)', 'rgba(0, 0, 159, 1)',
                    'rgba(0, 0, 127, 1)', 'rgba(63, 0, 91, 1)', 'rgba(127, 0, 63, 1)',
                    'rgba(191, 0, 31, 1)', 'rgba(255, 0, 0, 1)'
                ]
            });

            // Pins
            const showPins = document.getElementById('togglePins').checked;
            filteredData.forEach(loc => {
                const isCrop = loc.incident_type === 'Crop Damage';
                const marker = new google.maps.Marker({
                    position: { lat: parseFloat(loc.latitude), lng: parseFloat(loc.longitude) },
                    map: showPins ? map : null,
                    title: loc.victim_name,
                    icon: isCrop ? 'http://maps.google.com/mapfiles/ms/icons/green-dot.png' : 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
                });

                const contentString = `
                    <div style="padding: 5px; min-width: 200px; font-family: 'Inter', sans-serif;">
                        <h6 style="font-weight: 700; margin-bottom: 5px; color: #1e293b;">${loc.victim_name}</h6>
                        <p style="margin: 0; font-size: 13px; color: #64748b;"><b>Village:</b> ${loc.village_name}</p>
                        <p style="margin: 0; font-size: 13px; color: #64748b;"><b>Type:</b> ${loc.incident_type}</p>
                        <p style="margin: 8px 0 0 0; font-size: 11px; text-transform: uppercase; font-weight: 700; display: inline-block; padding: 4px 8px; background: #f1f5f9; border-radius: 6px; color: #475569;">
                            ${loc.status}
                        </p>
                    </div>
                `;

                marker.addListener('click', () => {
                    infoWindow.setContent(contentString);
                    infoWindow.open(map, marker);
                });

                markers.push({ marker: marker, data: loc });
            });
        }

        function toggleMapLayer(layer, isVisible) {
            if (layer === 'heatmap' && heatmap) heatmap.setMap(isVisible ? map : null);
            if (layer === 'pins') markers.forEach(m => m.marker.setMap(isVisible ? map : null));
        }

        function filterMapData(type, btnElement) {
            // Update Active Class on filter group
            document.querySelectorAll('.map-filter-btn').forEach(el => el.classList.remove('active'));
            btnElement.classList.add('active');
            renderMapData(type);
        }

        // ==========================================
        // CHART LOGIC (Incidents By Range)
        // ==========================================
        let rangeChartInstance;
        const chartDataRaw = @json($chartData ?? []);

        function initChart() {
            const root = getComputedStyle(document.documentElement);
            const textColor = root.getPropertyValue('--text-muted').trim() || '#64748b';
            const gridColor = root.getPropertyValue('--border-color').trim() || '#e2e8f0';
            const cardBg = root.getPropertyValue('--bg-card').trim() || '#ffffff';
            const colorSuccess = root.getPropertyValue('--sapphire-success').trim() || '#10b981';
            const colorPrimary = root.getPropertyValue('--sapphire-primary').trim() || '#3b82f6';

            const ranges = [...new Set(chartDataRaw.map(item => item.range))];
            const cropData = ranges.map(range => {
                const match = chartDataRaw.find(item => item.range === range && item.incident_type === 'Crop Damage');
                return match ? match.total : 0;
            });
            const houseData = ranges.map(range => {
                const match = chartDataRaw.find(item => item.range === range && item.incident_type === 'House Damage');
                return match ? match.total : 0;
            });

            const ctx = document.getElementById('rangeChart').getContext('2d');
            if (rangeChartInstance) rangeChartInstance.destroy();

            Chart.defaults.color = textColor;
            Chart.defaults.font.family = "'Inter', sans-serif";

            rangeChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ranges.length > 0 ? ranges : ['Central', 'North', 'South', 'East'],
                    datasets: [
                        {
                            label: 'Crop Damage',
                            data: cropData.length > 0 ? cropData : [5, 2, 1, 1],
                            backgroundColor: colorSuccess,
                            borderRadius: 50,         // Pill style
                            borderSkipped: false,     // Complete pill shape
                            borderColor: cardBg,      // Creates a visual gap between stacked bars
                            borderWidth: 2
                        },
                        {
                            label: 'House Damage',
                            data: houseData.length > 0 ? houseData : [0, 1, 2, 3],
                            backgroundColor: colorPrimary,
                            borderRadius: 50,         // Pill style
                            borderSkipped: false,
                            borderColor: cardBg,
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true, // Stacked to utilize the pill border logic
                            grid: { display: false }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            border: { dash: [4, 4], display: false },
                            grid: { color: gridColor, drawBorder: false }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { usePointStyle: true, boxWidth: 10, color: textColor }
                        }
                    }
                }
            });
        }
    </script>
@endsection
@extends('layouts.app')

@php
    $hideGlobalFilters = true;
    $hideBackground = true;
@endphp

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f6;
            /* Clean light gray background */
        }

        /* Premium Card Styling */
        .dashboard-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .dashboard-card:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        }

        .kpi-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .kpi-value {
            font-size: 2.25rem;
            font-weight: 800;
            color: #212529;
            line-height: 1;
        }

        .kpi-icon-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.2;
            /* Subtle background icon look like the screenshot */
        }

        .kpi-badge {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 6px;
        }

        /* Map & Chart Containers */
        .map-container {
            height: 450px;
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
        }

        .chart-container {
            position: relative;
            height: 380px;
            width: 100%;
        }

        /* Dark Mode Support for text */
        [data-bs-theme="dark"] body {
            background-color: #0f172a;
        }

        [data-bs-theme="dark"] .dashboard-card {
            background-color: #1e293b;
            box-shadow: none;
            border: 1px solid #334155;
        }

        [data-bs-theme="dark"] .kpi-value {
            color: #f8fafc;
        }

        [data-bs-theme="dark"] .kpi-title {
            color: #94a3b8;
        }
    </style>

    <div class="container-fluid py-4 px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="letter-spacing: -0.5px;">Compensation Tracking</h2>
                <p class="text-muted small mb-0">Manage wildlife conflict & damage claims</p>
            </div>
            <div>
                <button class="btn btn-success d-flex align-items-center shadow-sm fw-semibold" data-bs-toggle="offcanvas"
                    data-bs-target="#newIncidentOffcanvas">
                    <i data-lucide="plus" style="width: 18px; height: 18px;" class="me-1"></i> New Incident
                </button>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card dashboard-card h-100 p-3">
                    <div class="d-flex justify-content-between h-100">
                        <div class="d-flex flex-column justify-content-between">
                            <span class="kpi-title">Total Incidents</span>
                            <span class="kpi-value">{{ $kpis['total'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex flex-column justify-content-between align-items-end">
                            <span class="kpi-badge bg-success-subtle text-success">Updated Live</span>
                            <div class="kpi-icon-wrapper text-secondary">
                                <i data-lucide="layers" style="width: 40px; height: 40px; stroke-width: 1.5;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card dashboard-card h-100 p-3">
                    <div class="d-flex justify-content-between h-100">
                        <div class="d-flex flex-column justify-content-between">
                            <span class="kpi-title">Crop Damage</span>
                            <span class="kpi-value">{{ $kpis['crop'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex flex-column justify-content-between align-items-end">
                            <span class="kpi-badge bg-light text-muted border">Verified</span>
                            <div class="kpi-icon-wrapper text-success">
                                <i data-lucide="wheat" style="width: 40px; height: 40px; stroke-width: 1.5;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card dashboard-card h-100 p-3">
                    <div class="d-flex justify-content-between h-100">
                        <div class="d-flex flex-column justify-content-between">
                            <span class="kpi-title">House Damage</span>
                            <span class="kpi-value">{{ $kpis['house'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex flex-column justify-content-between align-items-end">
                            <span class="kpi-badge bg-light text-muted border">Verified</span>
                            <div class="kpi-icon-wrapper text-info">
                                <i data-lucide="home" style="width: 40px; height: 40px; stroke-width: 1.5;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card dashboard-card h-100 p-3">
                    <div class="d-flex justify-content-between h-100">
                        <div class="d-flex flex-column justify-content-between">
                            <span class="kpi-title">Pending Verification</span>
                            <span class="kpi-value">{{ $kpis['pending'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex flex-column justify-content-between align-items-end">
                            <span class="kpi-badge bg-danger-subtle text-danger">Requires Survey</span>
                            <div class="kpi-icon-wrapper text-danger">
                                <i data-lucide="alert-circle" style="width: 40px; height: 40px; stroke-width: 1.5;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card dashboard-card h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0 d-flex align-items-center">
                                <i data-lucide="map" class="text-success me-2" style="width: 20px; height: 20px;"></i>
                                Geospatial Hotspots
                            </h5>

                            <div class="d-flex gap-2">
                                <div class="btn-group btn-group-sm shadow-sm" role="group">
                                    <input type="checkbox" class="btn-check" id="toggleHeatmap" checked
                                        onchange="toggleMapLayer('heatmap', this.checked)">
                                    <label class="btn btn-outline-danger fw-medium" for="toggleHeatmap">Heatmap</label>

                                    <input type="checkbox" class="btn-check" id="togglePins" checked
                                        onchange="toggleMapLayer('pins', this.checked)">
                                    <label class="btn btn-outline-primary fw-medium" for="togglePins">Pins</label>
                                </div>

                                <div class="btn-group btn-group-sm shadow-sm" role="group">
                                    <button onclick="filterMapData('all')" id="btn-map-all"
                                        class="btn btn-dark fw-medium">All</button>
                                    <button onclick="filterMapData('Crop Damage')" id="btn-map-crop"
                                        class="btn btn-outline-secondary fw-medium">Crop</button>
                                    <button onclick="filterMapData('House Damage')" id="btn-map-house"
                                        class="btn btn-outline-secondary fw-medium">House</button>
                                </div>
                            </div>
                        </div>
                        <div id="map-anukampa" class="map-container border"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <h5 class="fw-bold mb-4 d-flex align-items-center">
                            <i data-lucide="bar-chart-2" class="text-warning me-2"
                                style="width: 20px; height: 20px;"></i> Incidents by Range
                        </h5>
                        <div class="chart-container flex-grow-1">
                            <canvas id="rangeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card dashboard-card mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Recent Claims Database</h5>
                    <a href="{{ route('anukampa.claims') }}"
                        class="btn btn-sm btn-outline-primary fw-medium px-3 shadow-sm">View All</a>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-muted small fw-bold text-uppercase pb-3">Victim Name</th>
                                <th class="text-muted small fw-bold text-uppercase pb-3">Location</th>
                                <th class="text-muted small fw-bold text-uppercase pb-3">Type</th>
                                <th class="text-muted small fw-bold text-uppercase pb-3">Est. Loss</th>
                                <th class="text-muted small fw-bold text-uppercase pb-3">Date</th>
                                <th class="text-muted small fw-bold text-uppercase pb-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($recentClaims ?? [] as $claim)
                                <tr>
                                    <td class="py-3">
                                        <p class="fw-bold mb-0 text-dark">{{ $claim->victim_name }}</p>
                                        <p class="text-muted small mb-0">{{ $claim->contact_number }}</p>
                                    </td>
                                    <td class="py-3 text-secondary">{{ $claim->range }} <br>
                                        <small>{{ $claim->village_name }}</small>
                                    </td>
                                    <td class="py-3">
                                        <span
                                            class="badge {{ $claim->incident_type == 'Crop Damage' ? 'bg-success-subtle text-success' : 'bg-info-subtle text-info' }} rounded-pill px-2 py-1">
                                            {{ $claim->incident_type }}
                                        </span>
                                    </td>
                                    <td class="py-3 font-monospace fw-medium text-secondary">
                                        ₹ {{ $claim->estimated_loss ? number_format($claim->estimated_loss, 2) : '--' }}
                                    </td>
                                    <td class="py-3 small text-secondary fw-medium">
                                        {{ \Carbon\Carbon::parse($claim->incident_date)->format('M d, Y') }}</td>
                                    <td class="py-3">
                                        <span
                                            class="badge rounded-pill px-2 py-1
                                    {{ $claim->status == 'Pending' ? 'bg-warning-subtle text-warning-emphasis' : '' }}
                                    {{ $claim->status == 'Verified' ? 'bg-info-subtle text-info-emphasis' : '' }}
                                    {{ $claim->status == 'Compensated' ? 'bg-success-subtle text-success' : '' }}
                                    {{ $claim->status == 'Rejected' ? 'bg-danger-subtle text-danger' : '' }}">
                                            {{ $claim->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No recent claims found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfBFN6L_HROTd-mS8QqUDRIqskkvHvFYk&libraries=visualization">
    </script>

    <script>
        // Initialize Icons
        document.addEventListener("DOMContentLoaded", function() {
            lucide.createIcons();
        });

        // ==========================================
        // GOOGLE MAPS LOGIC
        // ==========================================
        let map, heatmap;
        let markers = [];
        let infoWindow;

        // Convert Laravel collection to JS Array. 
        // We use recentClaims here because it contains all the rich data (lat, lng, name, type) needed for info windows.
        const mapData = @json($recentClaims ?? []);

        function initMap() {
            const centerLat = mapData.length > 0 ? parseFloat(mapData[0].latitude) : 21.1458;
            const centerLng = mapData.length > 0 ? parseFloat(mapData[0].longitude) : 79.0882;

            // Initialize Map
            map = new google.maps.Map(document.getElementById('map-anukampa'), {
                zoom: 10,
                center: {
                    lat: centerLat,
                    lng: centerLng
                },
                mapTypeId: 'terrain',
                streetViewControl: false,
                mapTypeControlOptions: {
                    mapTypeIds: ['roadmap', 'satellite', 'terrain']
                }
            });

            infoWindow = new google.maps.InfoWindow();

            // Initialize Data Layers
            renderMapData('all');
        }

        function renderMapData(filterType) {
            // Clear existing markers and heatmap
            markers.forEach(m => m.marker.setMap(null));
            markers = [];
            if (heatmap) heatmap.setMap(null);

            // Filter Data
            const filteredData = filterType === 'all' ?
                mapData :
                mapData.filter(loc => loc.incident_type === filterType);

            // 1. Build Heatmap Array
            const heatPoints = filteredData.map(loc => new google.maps.LatLng(parseFloat(loc.latitude), parseFloat(loc
                .longitude)));

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

            // 2. Build Markers (Pins)
            const showPins = document.getElementById('togglePins').checked;
            filteredData.forEach(loc => {
                const isCrop = loc.incident_type === 'Crop Damage';

                const marker = new google.maps.Marker({
                    position: {
                        lat: parseFloat(loc.latitude),
                        lng: parseFloat(loc.longitude)
                    },
                    map: showPins ? map : null,
                    title: loc.victim_name,
                    // Custom colored pins based on type
                    icon: isCrop ? 'http://maps.google.com/mapfiles/ms/icons/green-dot.png' :
                        'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
                });

                // InfoWindow Content
                const contentString = `
                <div style="padding: 5px; min-width: 200px; font-family: 'Inter', sans-serif;">
                    <h6 style="font-weight: 700; margin-bottom: 5px; color: #212529;">${loc.victim_name}</h6>
                    <p style="margin: 0; font-size: 13px; color: #6c757d;"><b>Village:</b> ${loc.village_name}</p>
                    <p style="margin: 0; font-size: 13px; color: #6c757d;"><b>Type:</b> ${loc.incident_type}</p>
                    <p style="margin: 5px 0 0 0; font-size: 12px; display: inline-block; padding: 2px 6px; background: #f8f9fa; border-radius: 4px; border: 1px solid #dee2e6;">
                        <b>Status:</b> ${loc.status}
                    </p>
                </div>
            `;

                marker.addListener('click', () => {
                    infoWindow.setContent(contentString);
                    infoWindow.open(map, marker);
                });

                markers.push({
                    marker: marker,
                    data: loc
                });
            });
        }

        // Toggle Specific Layers (Heatmap or Pins)
        function toggleMapLayer(layer, isVisible) {
            if (layer === 'heatmap' && heatmap) {
                heatmap.setMap(isVisible ? map : null);
            }
            if (layer === 'pins') {
                markers.forEach(m => m.marker.setMap(isVisible ? map : null));
            }
        }

        // Filter Data (All / Crop / House)
        function filterMapData(type) {
            // Update Button UI
            document.getElementById('btn-map-all').className = type === 'all' ? 'btn btn-dark fw-medium' :
                'btn btn-outline-secondary fw-medium';
            document.getElementById('btn-map-crop').className = type === 'Crop Damage' ? 'btn btn-success fw-medium' :
                'btn btn-outline-secondary fw-medium';
            document.getElementById('btn-map-house').className = type === 'House Damage' ?
                'btn btn-info fw-medium text-white' : 'btn btn-outline-secondary fw-medium';

            // Re-render map points
            renderMapData(type);
        }

        // ==========================================
        // CHART LOGIC (Incidents By Range)
        // ==========================================
        let rangeChartInstance;
        const chartDataRaw = @json($chartData ?? []);

        function initChart() {
            const ranges = [...new Set(chartDataRaw.map(item => item.range))];
            const cropData = ranges.map(range => {
                const match = chartDataRaw.find(item => item.range === range && item.incident_type ===
                    'Crop Damage');
                return match ? match.total : 0;
            });
            const houseData = ranges.map(range => {
                const match = chartDataRaw.find(item => item.range === range && item.incident_type ===
                    'House Damage');
                return match ? match.total : 0;
            });

            const ctx = document.getElementById('rangeChart').getContext('2d');
            rangeChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ranges.length > 0 ? ranges : ['Central', 'North', 'South', 'East'],
                    datasets: [{
                            label: 'Crop Damage',
                            data: cropData.length > 0 ? cropData : [5, 2, 1, 1],
                            backgroundColor: '#198754',
                            borderRadius: 4
                        },
                        {
                            label: 'House Damage',
                            data: houseData.length > 0 ? houseData : [0, 1, 2, 3],
                            backgroundColor: '#0dcaf0',
                            borderRadius: 4
                        }
                    ]
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
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 10
                            }
                        }
                    }
                }
            });
        }

        // Load Maps and Charts
        window.addEventListener('load', () => {
            initMap();
            initChart();
        });
    </script>
@endsection

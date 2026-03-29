<script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfBFN6L_HROTd-mS8QqUDRIqskkvHvFYk&libraries=visualization">
</script>
<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>

<script>
    // 1. DATA BRIDGE & GLOBAL STATE
    window.dashboardData = {
        kpis: @json($kpis) || {},
        mapData: @json($mapData) || [],
        chartLabels: @json($chartLabels) || [],
        chartValues: @json($chartValues) || [],
        details: @json($details) || {}
    };

    let activeMainTab = 'events';
    let activeSubTab = 'wildlife';

    // Map State
    let overallMap = null;
    let infoWindow = null;
    let clusterer = null;
    let layerMarkers = {}; // Stores arrays of google.maps.Marker
    let activeMapLayers = new Set(); // Tracks active layers
    let compartmentsLayer = null; // Used if you add polygon overlays
    let showCompartments = false;

    // Chart State
    let overallChart = null;
    let activeCharts = {};

    // Pulling Theme Colors safely
    const getThemeColor = (varName, fallback) => {
        return getComputedStyle(document.documentElement).getPropertyValue(varName).trim() || fallback;
    };

    // Configuration for Analytical Tabs
    const config = {
        categories: [{
                id: 'criminal',
                label: 'Criminal Activity',
                icon: 'bi-hammer',
                sub: ['felling', 'transport', 'storage', 'poaching', 'encroachment', 'mining']
            },
            {
                id: 'events',
                label: 'Events & Monitoring',
                icon: 'bi-eye',
                sub: ['wildlife', 'water', 'compensation']
            },
            {
                id: 'fire',
                label: 'Fire Incidents',
                icon: 'bi-fire',
                sub: ['fire']
            },
            {
                id: 'assets',
                label: 'Assets',
                icon: 'bi-shield-check',
                sub: ['inventory']
            }
        ],
        labels: {
            felling: 'Illegal Felling',
            transport: 'Timber Transport',
            storage: 'Timber Storage',
            poaching: 'Poaching',
            encroachment: 'Encroachment',
            mining: 'Illegal Mining',
            wildlife: 'Animal Sighting',
            water: 'Water Status',
            compensation: 'Compensation',
            fire: 'Fire Alerts',
            inventory: 'Asset Inventory'
        },
        icons: {
            wildlife: 'bi-eye',
            water: 'bi-droplet',
            compensation: 'bi-cash-stack',
            felling: 'bi-hammer',
            transport: 'bi-truck',
            poaching: 'bi-exclamation-octagon',
            storage: 'bi-box',
            encroachment: 'bi-border-style',
            mining: 'bi-minecart-loaded',
            fire: 'bi-fire',
            inventory: 'bi-shield-check'
        }
    };

    // Configuration for Map Layers
    const LAYER_CONFIG = {
        'criminal': {
            label: 'Criminal Activity',
            icon: '🪓',
            colorVar: '--sapphire-danger',
            fallback: '#ef4444'
        },
        'events': {
            label: 'Events & Tracking',
            icon: '🐾',
            colorVar: '--sapphire-success',
            fallback: '#10b981'
        },
        'fire': {
            label: 'Fire Alerts',
            icon: '🔥',
            colorVar: '--sapphire-warning',
            fallback: '#f59e0b'
        },
        'default': {
            label: 'Other Reports',
            icon: '📍',
            colorVar: '--text-muted',
            fallback: '#64748b'
        }
    };

    /* =================================================================
       INITIALIZATION
    ================================================================= */
    document.addEventListener('DOMContentLoaded', () => {
        initOverallMap();
        initOverallChart();
        renderMainTabs();

        // Listen for Theme Changes
        window.addEventListener('themeChanged', () => {
            updateMapTheme();
            initOverallChart(); // Re-render to fetch new CSS variables
            if (document.getElementById('analytical-container') && !document.getElementById(
                    'analytical-container').classList.contains('d-none')) {
                renderAnalyticalCharts();
            }
        });
    });

    /* =================================================================
       GOOGLE MAPS LOGIC
    ================================================================= */
    function initOverallMap() {
        const mapEl = document.getElementById('map');
        if (!mapEl) return; // Null Safety Check

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
            },
            {
                featureType: "poi",
                elementType: "labels",
                stylers: [{
                    visibility: "off"
                }]
            }
        ];

        overallMap = new google.maps.Map(mapEl, {
            zoom: 10,
            center: {
                lat: 21.640,
                lng: 79.560
            }, // Default center
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
            map: overallMap
        });

        processMapData();
        initMapSidebar();
    }

    function updateMapTheme() {
        if (!overallMap) return;
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
            },
            {
                featureType: "poi",
                elementType: "labels",
                stylers: [{
                    visibility: "off"
                }]
            }
        ];
        overallMap.setOptions({
            styles: isDark ? darkStyle : []
        });
    }

    function processMapData() {
        const features = window.dashboardData.mapData || [];
        const legendContainer = document.getElementById('map-legend-content');

        // Clear existing markers
        Object.values(layerMarkers).forEach(markers => markers.forEach(m => m.setMap(null)));
        if (clusterer) clusterer.clearMarkers();
        layerMarkers = {};
        if (legendContainer) legendContainer.innerHTML = '';

        const bounds = new google.maps.LatLngBounds();
        let hasPoints = false;

        // Create Markers
        features.forEach(f => {
            // Group by category (criminal, events, fire)
            const type = f.category || 'default';
            if (!layerMarkers[type]) layerMarkers[type] = [];

            const lat = parseFloat(f.latitude);
            const lng = parseFloat(f.longitude);

            if (!isNaN(lat) && !isNaN(lng)) {
                const config = LAYER_CONFIG[type] || LAYER_CONFIG['default'];
                const color = getThemeColor(config.colorVar, config.fallback);

                // Create SVG Icon
                const svgMarkup =
                    `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><circle cx="16" cy="16" r="14" fill="${color}" stroke="#ffffff" stroke-width="2"/><text x="16" y="21" font-size="14" text-anchor="middle" fill="white">${config.icon}</text></svg>`;
                const iconUrl = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svgMarkup);

                const marker = new google.maps.Marker({
                    position: {
                        lat: lat,
                        lng: lng
                    },
                    title: f.report_type || 'Report',
                    icon: {
                        url: iconUrl,
                        scaledSize: new google.maps.Size(32, 32)
                    }
                });

                marker.addListener('click', () => {
                    infoWindow.setContent(`
                        <div style="font-family: 'Inter', sans-serif; padding: 8px; min-width: 180px;">
                            <span style="font-size: 0.65rem; background: ${color}22; color: ${color}; padding: 3px 8px; border-radius: 12px; font-weight: 700; text-transform: uppercase;">
                                ${type}
                            </span>
                            <h6 style="margin: 8px 0 4px 0; font-weight: 800; font-size: 1rem; color: var(--text-main);">${f.report_type || 'Incident'}</h6>
                            <p style="margin: 0; font-size: 0.8rem; color: var(--text-muted);">${f.description || 'No description provided.'}</p>
                            <small style="display: block; margin-top: 8px; color: var(--text-muted); font-size: 0.7rem;">Lat: ${lat.toFixed(4)} | Lng: ${lng.toFixed(4)}</small>
                        </div>
                    `);
                    infoWindow.open(overallMap, marker);
                });

                layerMarkers[type].push(marker);
                bounds.extend(marker.getPosition());
                hasPoints = true;
            }
        });

        // Build Sidebar Layer Controls & Legend
        const controlContainer = document.getElementById('layerControlsContainer');
        if (controlContainer) controlContainer.innerHTML = '';

        Object.keys(layerMarkers).forEach(type => {
            const config = LAYER_CONFIG[type] || LAYER_CONFIG['default'];
            const color = getThemeColor(config.colorVar, config.fallback);
            const count = layerMarkers[type].length;

            // Add Sidebar Filter Item
            if (controlContainer) {
                controlContainer.insertAdjacentHTML('beforeend', `
                    <div class="layer-item active" id="layer-item-${type}" onclick="toggleMapLayer('${type}')">
                        <div class="layer-icon-box" style="color: ${color}; background: ${color}22;">${config.icon}</div>
                        <div class="layer-label">${config.label}</div>
                        <div class="count-pill">${count}</div>
                        <div class="eye-toggle active" id="layer-eye-${type}"><i class="bi bi-eye-fill"></i></div>
                    </div>
                `);
            }

            // Add Legend Item
            if (legendContainer) {
                legendContainer.insertAdjacentHTML('beforeend', `
                    <div class="col-6 d-flex align-items-center mb-2">
                        <div style="width:12px;height:12px;border-radius:50%;background:${color};margin-right:8px;"></div>
                        <span>${config.label}</span>
                    </div>
                `);
            }

            // Default to showing all layers
            activeMapLayers.add(type);
            if (clusterer) clusterer.addMarkers(layerMarkers[type]);
        });

        // Fit map to show all markers
        if (hasPoints && overallMap) {
            overallMap.fitBounds(bounds);
        }
    }

    window.toggleMapLayer = function(type) {
        const item = document.getElementById(`layer-item-${type}`);
        const eye = document.getElementById(`layer-eye-${type}`);

        if (activeMapLayers.has(type)) {
            // Hide Layer
            activeMapLayers.delete(type);
            if (clusterer) clusterer.removeMarkers(layerMarkers[type]);
            layerMarkers[type].forEach(m => m.setMap(null));
            if (item) item.classList.remove('active');
            if (eye) eye.classList.remove('active');
        } else {
            // Show Layer
            activeMapLayers.add(type);
            layerMarkers[type].forEach(m => m.setMap(overallMap));
            if (clusterer) clusterer.addMarkers(layerMarkers[type]);
            if (item) item.classList.add('active');
            if (eye) eye.classList.add('active');
        }
    };

    function initMapSidebar() {
        const sidebar = document.getElementById('mapFilterSidebar');
        const toggleBtn = document.getElementById('mapDrawerToggle');

        if (!sidebar || !toggleBtn) return;

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
    }

    /* =================================================================
       NAVIGATION & VIEW MODES
    ================================================================= */
    window.setViewMode = function(mode) {
        const overallBtn = document.getElementById('view-overall');
        const analyticalBtn = document.getElementById('view-analytical');

        if (overallBtn) overallBtn.className = `view-toggle-btn ${mode==='overall'?'active':''}`;
        if (analyticalBtn) analyticalBtn.className = `view-toggle-btn ${mode==='analytical'?'active':''}`;

        const overallContainer = document.getElementById('overall-container');
        const analyticalContainer = document.getElementById('analytical-container');
        const kpiGrid = document.getElementById('main-kpi-grid');

        if (mode === 'overall') {
            if (overallContainer) overallContainer.classList.remove('d-none');
            if (analyticalContainer) analyticalContainer.classList.add('d-none');
            if (kpiGrid) kpiGrid.classList.remove('d-none');
            // Gmaps handles resizing automatically, but just in case:
            if (overallMap) google.maps.event.trigger(overallMap, 'resize');
        } else {
            if (overallContainer) overallContainer.classList.add('d-none');
            if (analyticalContainer) analyticalContainer.classList.remove('d-none');
            if (kpiGrid) kpiGrid.classList.add('d-none');
            buildAnalyticalUI();
        }
    };

    window.navigateTo = function(cat) {
        if (!cat) return;
        activeMainTab = cat;
        const currentCat = config.categories.find(c => c.id === cat);
        if (currentCat) activeSubTab = currentCat.sub[0];
        renderMainTabs();
        setViewMode('analytical');
    };

    function renderMainTabs() {
        const nav = document.getElementById('main-tabs-nav');
        if (!nav) return;

        // Mapping icons/emojis exactly as they appear in your image
        const mainTabLabels = {
            'criminal': '🌲 Criminal Activity',
            'events': '🐾 Events & Monitoring',
            'fire': '🔥 Fire Incidents',
            'assets': '🛡️ Assets'
        };

        nav.innerHTML = config.categories.map(c => `
        <button onclick="activeMainTab='${c.id}'; activeSubTab=config.categories.find(x=>x.id==='${c.id}').sub[0]; renderMainTabs(); buildAnalyticalUI();"
            class="ana-main-tab ${activeMainTab === c.id ? 'active' : ''}">
            ${mainTabLabels[c.id] || c.label}
        </button>
    `).join('');
    }

    /* =================================================================
       CHARTS & ANALYTICAL UI
    ================================================================= */
    function buildAnalyticalUI() {
        const container = document.getElementById('sub-tabs-container');
        const currentCat = config.categories.find(c => c.id === activeMainTab);

        const header = document.getElementById('breakdown-header');
        const title = document.getElementById('breakdown-title');

        if (!currentCat || !container) return;

        if (header) header.classList.remove('d-none');

        // Format title (e.g. "Criminal Activity Breakdown")
        if (title) title.innerText = `${currentCat.label} Breakdown`;

        container.innerHTML = currentCat.sub.map(s => `
        <div class="ana-tile ${activeSubTab === s ? 'active' : ''}" onclick="activeSubTab='${s}'; buildAnalyticalUI();">
            <div class="ana-tile-header">
                <div class="ana-tile-icon">
                    <i class="bi ${config.icons[s] || 'bi-activity'}"></i>
                </div>
                <span class="ana-tile-label">${config.labels[s] || s}</span>
            </div>
            <h4 class="ana-tile-value">${window.dashboardData.kpis[s] || Math.floor(Math.random() * 50) + 10}</h4>
        </div>
    `).join('');

        renderAnalyticalCharts();
    }

    function renderAnalyticalCharts() {
        const grid = document.getElementById('charts-grid');
        if (!grid) return;
        grid.innerHTML = '';

        Object.values(activeCharts).forEach(c => {
            if (c) c.destroy();
        });
        activeCharts = {};

        const textColor = '#64748b';
        const gridColor = '#e2e8f0';
        const brandColor = '#10b981'; // Emerald 500
        const cardBg = '#ffffff';

        Chart.defaults.color = textColor;
        Chart.defaults.font.family = "'Inter', sans-serif";

        let currentLabels = activeMainTab === 'criminal' ? window.dashboardData.details.criminalLabels : window.dashboardData.details.eventsLabels;
        let currentValues = activeMainTab === 'criminal' ? window.dashboardData.details.criminalValues : window.dashboardData.details.eventsValues;

        if (!currentLabels || currentLabels.length === 0) {
            currentLabels = ['Sample A', 'Sample B', 'Sample C', 'Sample D', 'Sample E'];
            currentValues = [143, 162, 79, 199, 130];
        }

        const chartConfigs = [{
                title: "Volume Analysis by Species",
                id: 'chart1',
                type: 'bar',
                pillText: "Total: 1,249",
                toggles: ['Quantity', 'Volume(cmt)', 'Girth']
            },
            {
                title: "Probable Reason of Felling",
                id: 'chart2',
                type: 'doughnut',
                pillText: "Avg Trade: 45%",
                toggles: []
            },
            {
                title: "Range Wise Felling Data",
                id: 'chart3',
                type: 'bar',
                pillText: "Highest: 36",
                toggles: []
            }
        ];

        chartConfigs.forEach(cfg => {
            let togglesHtml = '';
            if (cfg.toggles && cfg.toggles.length > 0) {
                const buttons = cfg.toggles.map((t, i) => `
                <button class="ana-chart-toggle-btn ${i === 0 ? 'active' : ''}">
                    ${t}
                </button>
            `).join('');
                togglesHtml = `<div class="ana-chart-toggles hide-scrollbar">${buttons}</div>`;
            }

            grid.innerHTML += `
            <div class="col-lg-4">
                <div class="ana-chart-card">
                    <div class="ana-chart-header">
                        <h3 class="ana-chart-title">${cfg.title}</h3>
                        <span class="ana-chart-pill">${cfg.pillText}</span>
                    </div>
                    ${togglesHtml}
                    <div class="ana-chart-body">
                        <canvas id="${cfg.id}"></canvas>
                    </div>
                </div>
            </div>`;
        });

        setTimeout(() => {
            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        grid: {
                            color: gridColor
                        },
                        border: {
                            dash: [4, 4]
                        }
                    }
                }
            };

            // Chart 1: Bar
            activeCharts.c1 = new Chart(document.getElementById('chart1'), {
                type: 'bar',
                data: {
                    labels: currentLabels,
                    datasets: [{
                        data: currentValues,
                        backgroundColor: brandColor,
                        borderRadius: 3,
                        borderSkipped: false
                    }]
                },
                options: commonOptions
            });

            // Chart 2: Doughnut
            activeCharts.c2 = new Chart(document.getElementById('chart2'), {
                type: 'doughnut',
                data: {
                    labels: ['Trade', 'Fuel', 'Agri Land', 'Others'],
                    datasets: [{
                        data: [46, 26, 25, 13],
                        backgroundColor: ['#f43f5e', '#f59e0b', '#10b981', '#64748b'],
                        borderWidth: 2,
                        borderColor: cardBg
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                font: {
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });

            // Chart 3: Horizontal Bar
            activeCharts.c3 = new Chart(document.getElementById('chart3'), {
                type: 'bar',
                data: {
                    labels: ['North Beat A', 'West Ridge', 'River Buffer', 'East Plateau', 'South Valley'],
                    datasets: [{
                        label: 'Incidents',
                        data: [28, 18, 36, 19, 21],
                        backgroundColor: '#14b8a6', // Teal
                        borderRadius: 3
                    }]
                },
                options: {
                    ...commonOptions,
                    indexAxis: 'y', // Makes it horizontal
                    scales: {
                        x: {
                            grid: {
                                color: gridColor
                            },
                            border: {
                                dash: [4, 4]
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }, 50);
    }

    function initOverallChart() {
        const ctx = document.getElementById('overall-summary-chart');
        if (!ctx) return;
        if (overallChart) overallChart.destroy();

        const textColor = getThemeColor('--text-muted', '#64748b');
        const gridColor = getThemeColor('--border-color', '#e2e8f0');
        const brandColor = getThemeColor('--sapphire-primary', '#3b82f6');
        const cardBg = getThemeColor('--bg-card', '#ffffff');

        Chart.defaults.color = textColor;

        overallChart = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: window.dashboardData.chartLabels.length ? window.dashboardData.chartLabels : [
                    'No Data'
                ],
                datasets: [{
                    label: 'Total Incidents',
                    data: window.dashboardData.chartValues.length ? window.dashboardData.chartValues : [
                        0
                    ],
                    backgroundColor: brandColor,
                    borderRadius: 50,
                    borderSkipped: false,
                    borderColor: cardBg,
                    borderWidth: 2
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        border: {
                            dash: [4, 4]
                        },
                        grid: {
                            color: gridColor
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    window.updateOverallChart = function(category, btnElement) {
        document.querySelectorAll('#overall-chart-toggles .view-toggle-btn').forEach(b => b.classList.remove(
            'active'));
        btnElement.classList.add('active');

        let newLabels = category === 'criminal' ? window.dashboardData.details.criminalLabels : window.dashboardData
            .details.eventsLabels;
        let newValues = category === 'criminal' ? window.dashboardData.details.criminalValues : window.dashboardData
            .details.eventsValues;

        overallChart.data.labels = newLabels;
        overallChart.data.datasets[0].data = newValues;
        overallChart.data.datasets[0].backgroundColor = category === 'criminal' ? getThemeColor('--sapphire-danger',
            '#ef4444') : getThemeColor('--sapphire-success', '#10b981');
        overallChart.update();
    };

    window.refreshData = function() {
        const loader = document.getElementById('loader');
        if (loader) loader.classList.remove('d-none');
        setTimeout(() => location.reload(), 800);
    };
</script>
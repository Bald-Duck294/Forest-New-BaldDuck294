{{-- <script>
    // 1. DATA BRIDGE & GLOBAL STATE
    window.dashboardData = {
        kpis: @json($kpis) || {},
        mapData: @json($mapData) || [],
        chartLabels: @json($chartLabels) || [],
        chartValues: @json($chartValues) || [],
        details: @json($details) || {}
    };

    // 2. DASHBOARD STATE (Initialized from URL)
    const urlParams = new URLSearchParams(window.location.search);
    window.activeMainTab = urlParams.get('cat') || 'events';
    window.activeSubTab = urlParams.get('sub') || 'wildlife';
    window.viewMode = urlParams.get('view') || 'overall';
    console.log('--- SESSION START ---');
    console.log('URL parameters:', { cat: window.activeMainTab, sub: window.activeSubTab, view: window.viewMode });
    let mapInstance = null;
    let overallChart = null;
    let activeCharts = {};
    let compartmentsLayer = null;
    let showCompartments = false;

    // Pulling Theme Colors for Charts
    const getThemeColor = (varName, fallback) => getComputedStyle(document.documentElement).getPropertyValue(varName)
        .trim() || fallback;

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
        },
        // 🔥 NEW: Chart Configurations Mapping for each Sub-Tab
        views: {
            'felling': [
                { id: 'c1', title: 'Species Impact', type: 'bar', data: (db) => db.felling?.species },
                { id: 'c2', title: 'Reasons for Felling', type: 'doughnut', data: (db) => db.felling?.reason },
                { id: 'c3', title: 'Felling Trend', type: 'line', data: (db) => db.felling?.trend }
            ],
            'transport': [
                { id: 'c1', title: 'Vehicle Types', type: 'bar', data: (db) => db.transport?.vehicle },
                { id: 'c2', title: 'Transported Species', type: 'doughnut', data: (db) => db.transport?.species },
                { id: 'c3', title: 'Transport Trend', type: 'line', data: (db) => db.transport?.trend }
            ],
            'storage': [
                { id: 'c1', title: 'Species Stored', type: 'bar', data: (db) => db.storage?.species },
                { id: 'c2', title: 'Storage Reason', type: 'doughnut', data: (db) => db.storage?.reason },
                { id: 'c3', title: 'Storage Trend', type: 'line', data: (db) => db.storage?.trend }
            ],
            'poaching': [
                { id: 'c1', title: 'Wildlife Poached', type: 'bar', data: (db) => db.poaching?.species },
                { id: 'c2', title: 'Evidence Type', type: 'doughnut', data: (db) => db.poaching?.evidence },
                { id: 'c3', title: 'Poaching Trend', type: 'line', data: (db) => db.poaching?.trend }
            ],
            'encroachment': [
                { id: 'c1', title: 'Encroachment Type', type: 'bar', data: (db) => db.encroachment?.type },
                { id: 'c2', title: 'Action Taken', type: 'doughnut', data: (db) => db.encroachment?.action },
                { id: 'c3', title: 'Encroachment Trend', type: 'line', data: (db) => db.encroachment?.trend }
            ],
            'mining': [
                { id: 'c1', title: 'Mineral Type', type: 'bar', data: (db) => db.mining?.mineral },
                { id: 'c2', title: 'Excavation Method', type: 'doughnut', data: (db) => db.mining?.method },
                { id: 'c3', title: 'Mining Trend', type: 'line', data: (db) => db.mining?.trend }
            ],
            'wildlife': [
                { id: 'c1', title: 'Species Sighting', type: 'bar', data: (db) => db.wildlife?.species },
                { id: 'c2', title: 'Evidence Analysis', type: 'doughnut', data: (db) => db.wildlife?.evidence },
                { id: 'c3', title: 'Sighting Trend', type: 'line', data: (db) => db.wildlife?.trend }
            ],
            'water': [
                { id: 'c1', title: 'Source Distribution', type: 'bar', data: (db) => db.water?.availability },
                { id: 'c2', title: 'Range Overview', type: 'doughnut', data: (db) => db.water?.ranges }
            ],
            'compensation': [
                { id: 'c1', title: 'Claims by Type', type: 'bar', data: (db) => db.compensation?.claims_qty },
                { id: 'c2', title: 'Financial Claim Amount', type: 'doughnut', data: (db) => db.compensation?.claims_amt },
                { id: 'c3', title: 'Compensation Trend', type: 'line', data: (db) => db.compensation?.trend }
            ],
            'fire': [
                { id: 'c1', title: 'Burnt Area by Range', type: 'bar', data: (db) => db.fire?.ranges_area },
                { id: 'c2', title: 'Fire Causes', type: 'doughnut', data: (db) => db.fire?.causes },
                { id: 'c3', title: 'Fire Alert Trend', type: 'line', data: (db) => db.fire?.trend_incidents }
            ],
            'inventory': [
                { id: 'c1', title: 'Category Distribution', type: 'bar', data: (db) => db.inventory?.distribution },
                { id: 'c2', title: 'Asset Status', type: 'doughnut', data: (db) => db.inventory?.status },
                { id: 'c3', title: 'Deployment Rate', type: 'line', data: (db) => db.inventory?.trend }
            ]
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        initMap();
        initOverallChart();
        
        // Apply view and tab state
        setViewMode(viewMode);
        renderMainTabs();
        if (viewMode === 'analytical') {
            buildAnalyticalUI();
        }

        // Listen for Theme Changes to update Charts dynamically
        window.addEventListener('themeChanged', () => {
            initOverallChart(); // Re-render to fetch new CSS variables
            if (document.getElementById('analytical-container').classList.contains('d-none') ===
                false) {
                renderAnalyticalCharts();
            }
        });



    });

    // --- MAP LOGIC ---
    function initMap() {
        const mapContainer = document.getElementById('map');
        if (!mapContainer) return;

        mapInstance = L.map('map', {
            scrollWheelZoom: false
        }).setView([21.640, 79.560], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapInstance);

        const legendContainer = document.getElementById('map-legend-content');
        legendContainer.innerHTML = '';

        if (Array.isArray(window.dashboardData.mapData)) {
            window.dashboardData.mapData.forEach(p => {
                if (p.latitude && p.longitude) {
                    L.circleMarker([parseFloat(p.latitude), parseFloat(p.longitude)], {
                        radius: 6,
                        color: '#fff',
                        weight: 1,
                        fillColor: getThemeColor('--sapphire-danger', '#ef4444'),
                        fillOpacity: 0.9
                    }).addTo(mapInstance).bindPopup(
                        `<div class="fw-bold" style="color: var(--text-main); font-family: 'Inter', sans-serif;">${p.report_type || 'Alert'}</div>`
                    );
                }
            });
            legendContainer.innerHTML +=
                `<div class="col-6 d-flex align-items-center"><div style="width:10px;height:10px;border-radius:50%;background:${getThemeColor('--sapphire-danger', '#ef4444')};margin-right:6px;"></div> Incidents</div>`;
        }
    }

    function toggleCompartments() {
        showCompartments = !showCompartments;
        const btn = document.getElementById('btn-compartments');
        const activeColor = getThemeColor('--sapphire-primary', '#3b82f6');

        if (showCompartments) {
            btn.style.backgroundColor = activeColor;
            btn.style.color = '#fff';
            if (!compartmentsLayer) {
                compartmentsLayer = L.layerGroup();
                const p1 = L.polygon([
                    [21.65, 79.55],
                    [21.67, 79.56],
                    [21.66, 79.58]
                ], {
                    color: activeColor,
                    weight: 2,
                    fillOpacity: 0.15
                });
                compartmentsLayer.addLayer(p1);
            }
            mapInstance.addLayer(compartmentsLayer);
        } else {
            btn.style.backgroundColor = '';
            btn.style.color = '';
            if (compartmentsLayer) mapInstance.removeLayer(compartmentsLayer);
        }
    }

    // --- NAVIGATION ---
    function setViewMode(mode) {
        viewMode = mode;
        const overallBtn = document.getElementById('view-overall');
        const analyticalBtn = document.getElementById('view-analytical');

        if (overallBtn) overallBtn.className = `view-toggle-btn ${mode==='overall'?'active':''}`;
        if (analyticalBtn) analyticalBtn.className = `view-toggle-btn ${mode==='analytical'?'active':''}`;

        if (mode === 'overall') {
            document.getElementById('overall-container').classList.remove('d-none');
            document.getElementById('analytical-container').classList.add('d-none');
            if (mapInstance) setTimeout(() => mapInstance.invalidateSize(), 200);
        } else {
            document.getElementById('overall-container').classList.add('d-none');
            document.getElementById('analytical-container').classList.remove('d-none');
            buildAnalyticalUI();
        }
    }

    function navigateTo(cat) {
        if (!cat) return;
        activeMainTab = cat;
        const currentCat = config.categories.find(c => c.id === cat);
        if (currentCat) activeSubTab = currentCat.sub[0];
        renderMainTabs();
        setViewMode('analytical');
    }

    function renderMainTabs() {
        const nav = document.getElementById('main-tabs-nav');
        if (!nav) return;
        nav.innerHTML = config.categories.map(c => `
            <a href="javascript:void(0)" onclick="activeMainTab='${c.id}'; activeSubTab=config.categories.find(x=>x.id==='${c.id}').sub[0]; renderMainTabs(); buildAnalyticalUI();"
               class="main-tab-link ${activeMainTab === c.id ? 'active' : ''}">
               <i class="bi ${c.icon}"></i> ${c.label}
            </a>
        `).join('');
    }

    // --- CHARTS & UI ---
    function buildAnalyticalUI() {
        const container = document.getElementById('sub-tabs-container');
        const currentCat = config.categories.find(c => c.id === activeMainTab);
        if (!currentCat || !container) return;

        document.getElementById('breakdown-header').classList.remove('d-none');
        document.getElementById('breakdown-title').innerText = `${currentCat.label} Breakdown`;

        container.innerHTML = currentCat.sub.map(s => `
            <div class="breakdown-tile ${activeSubTab === s ? 'active' : ''}" onclick="activeSubTab='${s}'; buildAnalyticalUI();">
                <div class="d-flex align-items-center gap-2 mb-2 text-muted">
                    <i class="bi ${config.icons[s] || 'bi-activity'}"></i>
                    <span class="text-uppercase fw-bold" style="font-size: 10px;">${config.labels[s] || s}</span>
                </div>
                <h2>${window.dashboardData.kpis[s] || 0}</h2>
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

        // Use global variables for colors
        const textColor = getThemeColor('--text-muted', '#64748b');
        const gridColor = getThemeColor('--border-color', '#e2e8f0');
        const brandColor = getThemeColor('--sapphire-primary', '#3b82f6');
        const cardBg = getThemeColor('--bg-card', '#ffffff');

        Chart.defaults.color = textColor;
        Chart.defaults.font.family = "'Inter', sans-serif";

        let currentLabels = activeMainTab === 'criminal' ? window.dashboardData.details.criminalLabels : window
            .dashboardData.details.eventsLabels;
        let currentValues = activeMainTab === 'criminal' ? window.dashboardData.details.criminalValues : window
            .dashboardData.details.eventsValues;

        if (!currentLabels || currentLabels.length === 0) {
            currentLabels = ['Sample A', 'Sample B', 'Sample C'];
            currentValues = [10, 20, 30];
        }

        const chartConfigs = [{
                title: "Volume Analysis",
                id: 'chart1',
                type: 'bar'
            },
            {
                title: "Distribution Overview",
                id: 'chart2',
                type: 'doughnut'
            },
            {
                title: "Trend Analysis",
                id: 'chart3',
                type: 'line'
            }
        ];

        chartConfigs.forEach(cfg => {
            grid.innerHTML += `
                <div class="col-lg-4">
                    <div class="dash-card p-4 h-100 d-flex flex-column">
                        <h6 class="fw-bold mb-4" style="color: var(--text-main); font-size: 0.9rem;">${cfg.title}</h6>
                        <div class="flex-grow-1" style="min-height: 250px;"><canvas id="${cfg.id}"></canvas></div>
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
                        border: {
                            dash: [4, 4]
                        },
                        grid: {
                            color: gridColor
                        }
                    }
                }
            };

            // Chart 1: Bar (Pill Style)
            activeCharts.c1 = new Chart(document.getElementById('chart1'), {
                type: 'bar',
                data: {
                    labels: currentLabels,
                    datasets: [{
                        data: currentValues,
                        backgroundColor: brandColor,
                        borderRadius: 50,
                        borderSkipped: false,
                        borderColor: cardBg,
                        borderWidth: 2
                    }]
                },
                options: commonOptions
            });

            // Chart 2: Doughnut
            activeCharts.c2 = new Chart(document.getElementById('chart2'), {
                type: 'doughnut',
                data: {
                    labels: currentLabels,
                    datasets: [{
                        data: currentValues,
                        backgroundColor: [brandColor, getThemeColor('--sapphire-warning',
                                '#f59e0b'), getThemeColor('--sapphire-success', '#10b981'),
                            getThemeColor('--sapphire-danger', '#ef4444')
                        ],
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
                                color: textColor,
                                usePointStyle: true
                            }
                        }
                    }
                }
            });

            // Chart 3: Line
            activeCharts.c3 = new Chart(document.getElementById('chart3'), {
                type: 'line',
                data: {
                    labels: currentLabels,
                    datasets: [{
                        data: currentValues,
                        borderColor: getThemeColor('--sapphire-success', '#10b981'),
                        tension: 0.4,
                        fill: true,
                        backgroundColor: 'rgba(16, 185, 129, 0.05)'
                    }]
                },
                options: commonOptions
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

    // Toggle Overall Chart
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
    }

    function refreshData() {
        const loader = document.getElementById('loader');
        if (loader) loader.classList.remove('d-none');

        const range = document.getElementById('range_id')?.value || '';
        const site = document.getElementById('site_id')?.value || '';
        const date = document.getElementById('date_filter')?.value || '';

        const url = new URL(window.location.href);
        if (range) url.searchParams.set('range_id', range);
        else url.searchParams.delete('range_id');

        if (site) url.searchParams.set('site_id', site);
        else url.searchParams.delete('site_id');

        if (date) url.searchParams.set('date_filter', date);
        else url.searchParams.delete('date_filter');

        // 🔥 Persist view state on refresh
        url.searchParams.set('view', viewMode);
        url.searchParams.set('cat', activeMainTab);
        url.searchParams.set('sub', activeSubTab);

        window.location.href = url.toString();
    }



    document.addEventListener('DOMContentLoaded', () => {
        /* =================================================================
   OVERALL DASHBOARD LOGIC (Map & Territory Chart)
================================================================= */

        // 1. Data Definitions & Color Mappings (Sapphire Theme)
        const getCssVar = (name, fallback) => getComputedStyle(document.documentElement).getPropertyValue(name)
            .trim() || fallback;

        const LAYER_CONFIG = {
            'fire_point': {
                label: 'Fire Points',
                icon: '🔥',
                colorVar: '--sapphire-danger',
                fallback: '#ef4444'
            },
            'fire_lines': {
                label: 'Fire Lines',
                icon: '〰️',
                colorVar: '--sapphire-danger',
                fallback: '#ef4444'
            },
            'animal_sighting': {
                label: 'Animal Sighting',
                icon: '🐾',
                colorVar: '--sapphire-warning',
                fallback: '#f59e0b'
            },
            'elephant_movement': {
                label: 'Elephant Movement',
                icon: '🐘',
                colorVar: '--sapphire-warning',
                fallback: '#f59e0b'
            },
            'drainage': {
                label: 'Drainage',
                icon: '🌊',
                colorVar: '--sapphire-primary',
                fallback: '#3b82f6'
            },
            'water_body': {
                label: 'Water Bodies',
                icon: '💧',
                colorVar: '--sapphire-primary',
                fallback: '#3b82f6'
            },
            'plantation_site': {
                label: 'Plantation Sites',
                icon: '🌱',
                colorVar: '--sapphire-success',
                fallback: '#10b981'
            },
            'forest_boundary': {
                label: 'Forest Boundary',
                icon: '🌳',
                colorVar: '--sapphire-success',
                fallback: '#10b981'
            },
            'revenue_forest_land': {
                label: 'Revenue Land',
                icon: '📜',
                colorVar: '--text-muted',
                fallback: '#8b5cf6'
            },
            'default': {
                label: 'Other Features',
                icon: '📍',
                colorVar: '--text-muted',
                fallback: '#64748b'
            }
        };

        let overallMap;
        let mapLayerGroups = {}; // Stores L.layerGroup() for each type
        let activeMapLayers = new Set(); // Tracks which layers are turned ON
        let territoryChartInstance = null;

        document.addEventListener('DOMContentLoaded', () => {
            initOverallMap();
            initMapSidebar();
            initTerritoryChart();

            // Listen for global theme switches to redraw charts and map styles
            window.addEventListener('themeChanged', () => {
                updateMapTheme();
                initTerritoryChart(); // Redraws chart to fetch new CSS variable colors
            });
        });

        /* =========================================
           MAP INITIALIZATION
        ========================================= */
        function initOverallMap() {
            const mapEl = document.getElementById('map');
            if (!mapEl) return;

            // Initialize Map (Scroll wheel disabled until Ctrl is pressed)
            overallMap = L.map('map', {
                scrollWheelZoom: false
            }).setView([21.640, 79.560], 10);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(overallMap);

            updateMapTheme();
            setupCtrlScroll(mapEl);
            processMapData();
        }

        function updateMapTheme() {
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            const pane = overallMap.getPane('tilePane');
            // Dark mode trick for Leaflet without loading new tiles:
            if (isDark) {
                pane.style.filter = 'invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%)';
            } else {
                pane.style.filter = 'none';
            }
        }

        function setupCtrlScroll(mapEl) {
            const scrollMsg = document.getElementById('map-scroll-msg');
            let scrollTimeout;

            mapEl.addEventListener('wheel', (e) => {
                if (e.ctrlKey) {
                    if (!overallMap.scrollWheelZoom.enabled()) overallMap.scrollWheelZoom.enable();
                } else {
                    if (overallMap.scrollWheelZoom.enabled()) overallMap.scrollWheelZoom.disable();
                    scrollMsg.classList.remove('d-none');
                    scrollMsg.classList.add('show');
                    clearTimeout(scrollTimeout);
                    scrollTimeout = setTimeout(() => {
                        scrollMsg.classList.remove('show');
                        setTimeout(() => scrollMsg.classList.add('d-none'), 300);
                    }, 1500);
                }
            });
        }

        /* =========================================
           MAP DATA PROCESSING & SIDEBAR
        ========================================= */
        function processMapData() {
            const features = window.dashboardData.mapFeatures || [];
            const layerDistribution = window.dashboardData.layerDistribution || {};

            // 1. Group features by layer_type
            features.forEach(f => {
                const type = f.layer_type || 'default';
                if (!mapLayerGroups[type]) mapLayerGroups[type] = L.layerGroup();

                let layerObj = null;
                const color = getCssVar(LAYER_CONFIG[type]?.colorVar || LAYER_CONFIG['default']
                    .colorVar, LAYER_CONFIG['default'].fallback);

                try {
                    const coords = typeof f.coordinates === 'string' ? JSON.parse(f.coordinates) : f
                        .coordinates;

                    // Render Points as Emoji Icons
                    if (f.geometry_type === 'Point' || f.geometry_type === 'Point') {
                        const iconHtml = LAYER_CONFIG[type]?.icon || '📍';
                        const customIcon = L.divIcon({
                            html: `<div class="custom-map-marker" style="background-color: ${color};">${iconHtml}</div>`,
                            className: 'custom-leaflet-icon',
                            iconSize: [28, 28],
                            iconAnchor: [14, 14]
                        });

                        // Leaflet expects [lat, lng], GeoJSON is usually [lng, lat]. Assuming [lat, lng] based on previous code.
                        const latlng = Array.isArray(coords) && coords.length === 2 && coords[0] < 100 ?
                            coords : [coords.lat, coords.lng];
                        if (latlng && latlng[0]) {
                            layerObj = L.marker(latlng, {
                                icon: customIcon
                            });
                        }
                    }
                    // Render Polygons/Lines
                    else if (f.geometry_type === 'Polygon' || f.geometry_type === 'LineString') {
                        // Simplified extraction - actual implementation depends on your JSON structure
                        const latlngs = coords.map(p => Array.isArray(p) ? [p[1], p[0]] : [p.lat, p
                            .lng
                        ]);
                        layerObj = f.geometry_type === 'Polygon' ?
                            L.polygon(latlngs, {
                                color: color,
                                weight: 2,
                                fillOpacity: 0.15
                            }) :
                            L.polyline(latlngs, {
                                color: color,
                                weight: 3
                            });
                    }

                    if (layerObj) {
                        layerObj.bindPopup(`
                    <div style="font-family: 'Inter', sans-serif;">
                        <h6 style="margin:0; font-weight:700; color:var(--text-main);">${f.name || 'Unnamed Feature'}</h6>
                        <small style="color:var(--text-muted); text-transform:uppercase;">${type.replace('_', ' ')}</small>
                    </div>
                `);
                        mapLayerGroups[type].addLayer(layerObj);
                    }
                } catch (e) {
                    console.error("Error parsing geometry for feature", f.id, e);
                }
            });

            // 2. Build the Sidebar UI
            const container = document.getElementById('layerControlsContainer');
            container.innerHTML = '';

            Object.keys(mapLayerGroups).forEach(type => {
                const config = LAYER_CONFIG[type] || LAYER_CONFIG['default'];
                const color = getCssVar(config.colorVar, config.fallback);
                const count = layerDistribution[type] || mapLayerGroups[type].getLayers().length;

                const itemHtml = `
            <div class="layer-item" id="layer-item-${type}" onclick="toggleMapLayer('${type}')">
                <div class="layer-icon-box" style="color: ${color}; background: ${color}22;">${config.icon}</div>
                <div class="layer-label">${config.label}</div>
                <div class="layer-count-pill">${count}</div>
                <div class="layer-eye" id="layer-eye-${type}"><i class="bi bi-eye-fill"></i></div>
            </div>
        `;
                container.insertAdjacentHTML('beforeend', itemHtml);
            });

            // Turn on the first two layers by default
            const types = Object.keys(mapLayerGroups);
            if (types.length > 0) toggleMapLayer(types[0]);
            if (types.length > 1) toggleMapLayer(types[1]);
        }

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

        window.toggleMapLayer = function(type) {
            const item = document.getElementById(`layer-item-${type}`);

            if (activeMapLayers.has(type)) {
                activeMapLayers.delete(type);
                overallMap.removeLayer(mapLayerGroups[type]);
                if (item) item.classList.remove('active');
            } else {
                activeMapLayers.add(type);
                overallMap.addLayer(mapLayerGroups[type]);
                if (item) item.classList.add('active');
            }
        };


        /* =========================================
           TERRITORY OVERVIEW CHART (Pill Style)
        ========================================= */
        function initTerritoryChart() {
            const ctx = document.getElementById('overall-summary-chart');
            if (!ctx) return;
            if (territoryChartInstance) territoryChartInstance.destroy();

            // Dynamically fetch theme colors
            const colorText = getCssVar('--text-muted', '#64748b');
            const colorGrid = getCssVar('--border-color', '#e2e8f0');
            const colorBg = getCssVar('--bg-card', '#ffffff');
            const colorDanger = getCssVar('--sapphire-danger', '#ef4444');

            Chart.defaults.color = colorText;
            Chart.defaults.font.family = "'Inter', sans-serif";

            // Use topSites from controller, fallback to dummy data
            const topSites = window.dashboardData.topSites || {
                'North Range': 45,
                'South Valley': 32,
                'East Plateau': 20
            };
            const labels = Object.keys(topSites);
            const data = Object.values(topSites);

            territoryChartInstance = new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels.length ? labels : ['No Data'],
                    datasets: [{
                        label: 'Reported Incidents',
                        data: data.length ? data : [0],
                        backgroundColor: colorDanger,
                        borderRadius: 50, // PILL STYLE
                        borderSkipped: false, // Complete rounded edges
                        borderColor: colorBg, // Gap creation
                        borderWidth: 2
                    }]
                },
                options: {
                    indexAxis: 'y', // Horizontal Bar Chart
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            padding: 10,
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            border: {
                                dash: [4, 4],
                                display: false
                            },
                            grid: {
                                color: colorGrid
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
            // Update Button UI
            document.querySelectorAll('#overall-chart-toggles .view-toggle-btn').forEach(b => b.classList
                .remove('active'));
            btnElement.classList.add('active');

            if (!territoryChartInstance) return;

            // Swap datasets (Using details breakdown from controller)
            let newLabels, newValues, newColor;

            if (category === 'criminal') {
                const bd = window.dashboardData.details.criminalLabels || [];
                const vl = window.dashboardData.details.criminalValues || [];
                newLabels = bd.length ? bd : ['Felling', 'Poaching', 'Mining'];
                newValues = vl.length ? vl : [12, 5, 8];
                newColor = getCssVar('--sapphire-danger', '#ef4444');
            } else {
                const bd = window.dashboardData.details.eventsLabels || [];
                const vl = window.dashboardData.details.eventsValues || [];
                newLabels = bd.length ? bd : ['Wildlife', 'Water', 'Fire'];
                newValues = vl.length ? vl : [25, 10, 3];
                newColor = getCssVar('--sapphire-success', '#10b981');
            }

            territoryChartInstance.data.labels = newLabels;
            territoryChartInstance.data.datasets[0].data = newValues;
            territoryChartInstance.data.datasets[0].backgroundColor = newColor;
            territoryChartInstance.update();
        };


    });
</script> --}}


{{-- <script>
    // 1. DATA BRIDGE & GLOBAL STATE
    window.dashboardData = {
        kpis: @json($kpis ?? []),
        mapData: @json($mapData ?? []),
        chartLabels: @json($chartLabels ?? []),
        chartValues: @json($chartValues ?? []),
        details: @json($details ?? [])
    };

    let activeMainTab = 'events';
    let activeSubTab = 'wildlife';

    // Map State
    let overallMap = null;
    let infoWindow = null;
    let clusterer = null;
    let layerDataCollections = {};
    let layerMarkers = {};
    let layerShapes = {};
    let loadedLayers = {};

    // Chart State
    let overallChart = null;
    let activeCharts = {};

    // Map Styling Constants
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

    const getThemeColor = (varName, fallback) => {
        return getComputedStyle(document.documentElement).getPropertyValue(varName).trim() || fallback;
    };

    /* =================================================================
       INITIALIZATION
    ================================================================= */
    document.addEventListener('DOMContentLoaded', () => {
        initOverallMap();
        initOverallChart();

        window.addEventListener('themeChanged', () => {
            updateMapTheme();
            initOverallChart();
        });

        // Initialize Sidebar Events
        const sidebar = document.querySelector('.map-filter-sidebar');
        const toggleBtn = document.getElementById('mapDrawerToggle');

        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('open');
                this.classList.toggle('active');
                const icon = this.querySelector('i');
                icon.className = sidebar.classList.contains('open') ? 'bi bi-x-lg' :
                    'bi bi-layers-half';
            });
        }

        // Fetch initial map layer counts
        loadLayerCounts();
    });

    /* =================================================================
       GOOGLE MAPS LOGIC
    ================================================================= */
    function initOverallMap() {
        const mapEl = document.getElementById('map');
        if (!mapEl) return;

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
            },
            mapTypeId: 'roadmap',
            styles: isDark ? darkStyle : [],
            mapTypeControl: false,
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

        setupCtrlScroll(mapEl);
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

    function setupCtrlScroll(mapEl) {
        const scrollMsg = document.getElementById('map-scroll-msg');
        if (!scrollMsg) return;

        overallMap.setOptions({
            scrollwheel: false
        });
        let scrollTimeout;

        mapEl.addEventListener('wheel', (e) => {
            if (e.ctrlKey) {
                overallMap.setOptions({
                    scrollwheel: true
                });
            } else {
                overallMap.setOptions({
                    scrollwheel: false
                });
                scrollMsg.classList.remove('d-none');
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    scrollMsg.classList.add('d-none');
                }, 1500);
            }
        });
    }

    /* =================================================================
       AJAX LAYER FETCHING LOGIC
    ================================================================= */
    window.toggleLayerUI = function(layerType) {
        const cb = document.getElementById('check_' + layerType);
        if (cb) {
            cb.checked = !cb.checked;
            updateLayerUIState(layerType, cb.checked);

            if (layerType === 'geofences') {
                if (layerShapes.geofences) {
                    layerShapes.geofences.forEach(s => s.setMap(cb.checked ? overallMap : null));
                }
            } else {
                if (cb.checked) {
                    if (loadedLayers[layerType]) {
                        showLayer(layerType);
                    } else {
                        fetchLayerData(layerType);
                    }
                } else {
                    hideLayer(layerType);
                }
            }
        }
    };

    function updateLayerUIState(layerType, active) {
        const item = document.getElementById('item_' + layerType);
        if (item) item.classList.toggle('active', active);
    }

    function loadLayerCounts() {
        const loader = document.getElementById('customMapLoader');
        if (loader) loader.classList.remove('d-none');

        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams(formData);
        params.append('only_counts', '1');

        fetch(`{{ route('know-your-area.data') }}?${params.toString()}`)
            .then(res => res.json())
            .then(response => {
                if (loader) loader.classList.add('d-none');
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
            }).catch(err => {
                if (loader) loader.classList.add('d-none');
                console.error('Counts fetch error:', err);
            });
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
            }).catch(err => {
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
            if (feature.getGeometry().getType() === 'Point') return {
                visible: false
            };
            return style;
        });

        dataLayer.addListener('click', event => bindPopup(event.feature, event.latLng));
        layerDataCollections[layerType] = dataLayer;

        features.forEach(feature => {
            if (feature.geometry.type === 'Point' && layerType !== 'elephant_movement') {
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
        if (clusterer) clusterer.addMarkers(markers);
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
                        <h3 class="popup-title">${geo.name || 'Beat Boundary'}</h3>
                    </div>
                    <div class="popup-body">
                        <table class="popup-table">
                            <tr><td class="popup-label">Address</td><td class="popup-value">${geo.address || 'N/A'}</td></tr>
                            <tr><td class="popup-label">Type</td><td class="popup-value">${geo.type || 'Polygon'}</td></tr>
                        </table>
                    </div>
                </div>`;

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
                    infoWindow.open(overallMap);
                });
                shapes.push(shape);
            }
        });
        layerShapes.geofences = shapes;

        const cb = document.getElementById('check_geofences');
        if (cb && cb.checked) {
            layerShapes.geofences.forEach(s => s.setMap(overallMap));
        }
    }

    function showLayer(layerType) {
        if (layerDataCollections[layerType]) layerDataCollections[layerType].setMap(overallMap);
        if (layerMarkers[layerType]) {
            layerMarkers[layerType].forEach(m => m.setMap(overallMap));
            if (clusterer) clusterer.addMarkers(layerMarkers[layerType]);
        }
    }

    function hideLayer(layerType) {
        if (layerDataCollections[layerType]) layerDataCollections[layerType].setMap(null);
        if (layerMarkers[layerType]) {
            layerMarkers[layerType].forEach(m => m.setMap(null));
            if (clusterer) clusterer.removeMarkers(layerMarkers[layerType]);
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

        if (hasPoints && overallMap) {
            overallMap.fitBounds(bounds);
        }
    }

    function bindPopup(feature, position, isRawFeature = false) {
        const props = isRawFeature ? feature.properties : {};
        if (!isRawFeature) feature.forEachProperty((v, k) => props[k] = v);

        const layerType = props.layer_type || 'Feature';
        const style = LAYER_STYLES[layerType] || {};
        const color = style.strokeColor || '#3B82F6';
        const label = layerType.replace(/_/g, ' ').toUpperCase();

        let popup = `
            <div class="premium-popup">
                <div class="popup-header" style="background: ${color}">
                    <div class="popup-layer-badge">${label}</div>
                    <h3 class="popup-title">${props.name || 'Details'}</h3>
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
        infoWindow.open(overallMap);
    }


    /* =================================================================
       CHART LOGIC
    ================================================================= */
    function initOverallChart() {
        const ctx = document.getElementById('overall-summary-chart');
        if (!ctx) return;
        if (overallChart) overallChart.destroy();

        // Fetch dynamic theme colors
        const textColor = getThemeColor('--text-muted', '#64748b');
        const gridColor = getThemeColor('--border-color', '#e2e8f0');
        const brandColor = getThemeColor('--sapphire-danger', '#ef4444');
        const cardBg = getThemeColor('--bg-card', '#ffffff');

        Chart.defaults.color = textColor;

        // 1. Fetch data safely from the global object passed by the Controller
        let initialLabels = window.dashboardData.details?.criminalLabels || [];
        let initialValues = window.dashboardData.details?.criminalValues || [];

        // 2. Handle Empty Database State
        if (!initialLabels || initialLabels.length === 0) {
            initialLabels = ['No Incidents Recorded'];
            initialValues = [0];
        }

        overallChart = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: initialLabels,
                datasets: [{
                    label: 'Total Incidents',
                    data: initialValues,
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
                        beginAtZero: true,
                        border: {
                            dash: [4, 4]
                        },
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            stepSize: 1, // FIX: Forces whole numbers (1, 2, 3) instead of (0.2, 0.4)
                            precision: 0
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
        // Update Button UI
        document.querySelectorAll('#overall-chart-toggles .view-toggle-btn').forEach(b => b.classList.remove(
            'active'));
        btnElement.classList.add('active');

        // Fetch the respective arrays from the controller payload
        let newLabels = category === 'criminal' ? window.dashboardData.details?.criminalLabels : window
            .dashboardData.details?.eventsLabels;
        let newValues = category === 'criminal' ? window.dashboardData.details?.criminalValues : window
            .dashboardData.details?.eventsValues;

        // Fallback if data is empty
        if (!newLabels || newLabels.length === 0) {
            newLabels = ['No Incidents Recorded'];
            newValues = [0];
        }

        // Apply new data and change color based on category
        overallChart.data.labels = newLabels;
        overallChart.data.datasets[0].data = newValues;
        overallChart.data.datasets[0].backgroundColor = category === 'criminal' ?
            getThemeColor('--sapphire-danger', '#ef4444') :
            getThemeColor('--sapphire-success', '#10b981');

        overallChart.update();
    };
</script> --}}


<script>
    // =================================================================
    // 1. DATA BRIDGE & GLOBAL STATE
    // =================================================================
    window.dashboardData = {
        kpis: @json($kpis ?? []),
        mapData: @json($mapData ?? []),
        chartLabels: @json($chartLabels ?? []),
        chartValues: @json($chartValues ?? []),
        details: @json($details ?? []),
        analytics: @json($analytics ?? []) // Contains criminalLabels, criminalValues, eventsLabels, eventsValues
    };

    // let activeMainTab = 'events';
    // let activeSubTab = 'wildlife';
    let activeMainTab = 'criminal';
    let activeSubTab = 'felling';
    // Map State (Restored for AJAX Logic)
    let overallMap = null;
    let infoWindow = null;
    let clusterer = null;
    let layerDataCollections = {};
    let layerMarkers = {};
    let layerShapes = {};
    let loadedLayers = {};
    const CHART_COLORS = {
        emerald: '#10b981',
        rose: '#f43f5e',
        amber: '#f59e0b',
        blue: '#3b82f6',
        indigo: '#6366f1',
        teal: '#14b8a6',
        slate: '#64748b'
    };
    const COLOR_PALETTE = [
        CHART_COLORS.emerald,
        CHART_COLORS.amber,
        CHART_COLORS.rose,
        CHART_COLORS.blue,
        CHART_COLORS.indigo
    ];
    // Chart State
    let overallChart = null;
    let activeCharts = {};

    const getThemeColor = (varName, fallback) => {
        return getComputedStyle(document.documentElement).getPropertyValue(varName).trim() || fallback;
    };

    // Map Styling Constants (Restored)
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
        },
        views: {
            // --- FELLING ---
            'criminal.felling': [{
                    id: 'fell-c1',
                    title: 'Volume Analysis by Species',
                    type: 'bar',
                    toggles: ['Quantity', 'Volume(cmt)', 'Girth'],
                    generator: (idx, db) => {
                        let dataObj = idx === 0 ? db.felling?.species_qty : idx === 1 ? db.felling
                            ?.species_vol : db.felling?.species_girth;
                        let lbls = Object.keys(dataObj || {});
                        let vals = Object.values(dataObj || {});
                        // NEW CORRECT LOGIC:
                        if (!lbls.length) {
                            lbls = ['No Data'];
                            vals = [0];
                        }
                        return {
                            labels: lbls,
                            datasets: [{
                                label: ['Quantity', 'Volume', 'Girth'][idx],
                                data: vals,
                                backgroundColor: '#10b981',
                                borderRadius: 4
                            }]
                        };
                    },
                    calcPill: (data) => `Total: ${data.datasets[0].data.reduce((a,b)=>a+b,0).toFixed(0)}`
                },
                {
                    id: 'fell-c2',
                    title: 'Probable Reason of Felling',
                    type: 'pie',
                    toggles: [],
                    generator: (idx, db) => {
                        let lbls = Object.keys(db.felling?.reasons || {});
                        let vals = Object.values(db.felling?.reasons || {});
                        if (!lbls.length) {
                            lbls = ['Trade', 'Fuel', 'Agri Land', 'Others'];
                            vals = [27, 28, 21, 13];
                        }
                        return {
                            labels: lbls,
                            datasets: [{
                                data: vals,
                                backgroundColor: ['#ef4444', '#f59e0b', '#10b981', '#64748b']
                            }]
                        };
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false
                            }
                        }
                    },
                    calcPill: (data) => {
                        let maxIdx = data.datasets[0].data.indexOf(Math.max(...data.datasets[0].data));
                        return `Top: ${data.labels[maxIdx] || 'N/A'}`;
                    }
                },
                {
                    id: 'fell-c3',
                    title: 'Range Wise Felling Data',
                    type: 'bar',
                    toggles: [],
                    generator: (idx, db) => {
                        let lbls = Object.keys(db.felling?.ranges || {});
                        let vals = Object.values(db.felling?.ranges || {});
                        if (!lbls.length) {
                            lbls = ['North Beat A', 'West Ridge', 'River Buffer', 'East Plateau'];
                            vals = [31, 9, 6, 7];
                        }
                        return {
                            labels: lbls,
                            datasets: [{
                                label: 'Incidents',
                                data: vals,
                                backgroundColor: '#14b8a6',
                                borderRadius: 4
                            }]
                        };
                    },
                    options: {
                        indexAxis: 'y'
                    },
                    calcPill: (data) => `Highest: ${Math.max(...data.datasets[0].data)}`
                }
            ],
            // --- TRANSPORT ---
            'criminal.transport': [{
                    id: 'trans-c1',
                    title: 'Transport Vehicle Analytics',
                    type: 'bar',
                    toggles: ['Quantity', 'Trips'],
                    generator: (idx, db) => {
                        let dataObj = idx === 0 ? db.transport?.vehicles_qty : db.transport?.vehicles_trips;
                        let lbls = Object.keys(dataObj || {});
                        let vals = Object.values(dataObj || {}).map(Number);

                        // 🔥 FIXED: Use "No Data" instead of "Truck/Tractor/Tempo"
                        if (!lbls.length || vals.reduce((a, b) => a + b, 0) === 0) {
                            lbls = ['No Data'];
                            vals = [0];
                        }
                        return {
                            labels: lbls,
                            datasets: [{
                                label: idx === 0 ? 'Quantity' : 'Trips',
                                data: vals,
                                backgroundColor: '#6366f1',
                                borderRadius: 4
                            }]
                        };
                    },
                    calcPill: (data) => `Total: ${data.datasets[0].data.reduce((a,b)=>a+b,0).toFixed(0)}`
                },
                {
                    id: 'trans-c2',
                    title: 'Smuggling Routes',
                    type: 'doughnut',
                    toggles: [],
                    generator: (idx, db) => {
                        let dataObj = idx === 0 ? db.transport?.vehicles_qty : db.transport?.vehicles_trips;
                        let lbls = Object.keys(dataObj || {});
                        let vals = Object.values(dataObj || {}).map(Number);

                        // 🔥 FIXED: Use "No Data" instead of "Truck/Tractor/Tempo"
                        if (!lbls.length || vals.reduce((a, b) => a + b, 0) === 0) {
                            lbls = ['No Data'];
                            vals = [0];
                        }
                        return {
                            labels: lbls,
                            datasets: [{
                                label: idx === 0 ? 'Quantity' : 'Trips',
                                data: vals,
                                backgroundColor: '#6366f1',
                                borderRadius: 4
                            }]
                        };
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false
                            }
                        }
                    },
                    calcPill: (data) => `Main: ${data.labels[0]}`
                },
                {
                    id: 'trans-c3',
                    title: '30-Day Transport Trend',
                    type: 'line',
                    toggles: [],
                    generator: (idx, db) => {
                        let dataObj = idx === 0 ? db.transport?.vehicles_qty : db.transport?.vehicles_trips;
                        let lbls = Object.keys(dataObj || {});
                        let vals = Object.values(dataObj || {}).map(Number);

                        // 🔥 FIXED: Use "No Data" instead of "Truck/Tractor/Tempo"
                        if (!lbls.length || vals.reduce((a, b) => a + b, 0) === 0) {
                            lbls = ['No Data'];
                            vals = [0];
                        }
                        return {
                            labels: lbls,
                            datasets: [{
                                label: idx === 0 ? 'Quantity' : 'Trips',
                                data: vals,
                                backgroundColor: '#6366f1',
                                borderRadius: 4
                            }]
                        };
                    },
                    calcPill: (data) => {
                        let total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                        let avg = data.datasets[0].data.length ? (total / data.datasets[0].data.length) : 0;
                        return `Avg: ${avg.toFixed(1)}`;
                    }
                }
            ],
            // --- STORAGE ---
            // --- TRANSPORT ---
            'criminal.transport': [{
                    id: 'trans-c1',
                    title: 'Transport Vehicle Analytics',
                    type: 'bar',
                    toggles: ['Quantity', 'Trips'],
                    generator: (idx, db) => {
                        let dataObj = idx === 0 ? db.transport?.vehicles_qty : db.transport?.vehicles_trips;
                        let l = Object.keys(dataObj || {});
                        let v = Object.values(dataObj || {}).map(Number);

                        if (!l.length) return {
                            labels: ['No Data'],
                            datasets: [{
                                data: [0],
                                backgroundColor: '#64748b'
                            }]
                        };
                        return {
                            labels: l,
                            datasets: [{
                                label: idx === 0 ? 'Quantity' : 'Trips',
                                data: v,
                                backgroundColor: '#6366f1',
                                borderRadius: 4
                            }]
                        };
                    },
                    calcPill: (data) => `Total: ${data.datasets[0].data.reduce((a,b)=>a+b,0).toFixed(0)}`
                },
                {
                    id: 'trans-c2',
                    title: 'Smuggling Routes',
                    type: 'doughnut',
                    toggles: [],
                    generator: (idx, db) => {
                        // 🔥 FIXED: Now pulling from routes instead of vehicles
                        let l = Object.keys(db.transport?.routes || {});
                        let v = Object.values(db.transport?.routes || {}).map(Number);

                        if (!l.length || v.reduce((a, b) => a + b, 0) === 0) return {
                            labels: ['No Data'],
                            datasets: [{
                                data: [1],
                                backgroundColor: ['#64748b']
                            }]
                        };
                        return {
                            labels: l,
                            datasets: [{
                                data: v,
                                backgroundColor: COLOR_PALETTE
                            }]
                        };
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false
                            }
                        }
                    },
                    calcPill: (data) => `Main: ${data.labels[0] || 'N/A'}`
                },
                {
                    id: 'trans-c3',
                    title: '30-Day Transport Trend',
                    type: 'line',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.transport?.trend || {});
                        let v = Object.values(db.transport?.trend || {}).map(Number);
                        if (!l.length) return {
                            labels: ['No Data'],
                            datasets: [{
                                data: [0],
                                backgroundColor: 'rgba(100,116,139,0.1)',
                                borderColor: '#64748b'
                            }]
                        };

                        return {
                            labels: l,
                            datasets: [{
                                label: 'Quantity',
                                data: v,
                                borderColor: '#f43f5e',
                                backgroundColor: 'rgba(244,63,94,0.1)',
                                fill: true,
                                tension: 0.4
                            }]
                        };
                    },
                    options: {
                        plugins: {
                            datalabels: {
                                display: false
                            }
                        }
                    },
                    calcPill: (data) => {
                        let total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                        let avg = data.datasets[0].data.length ? (total / data.datasets[0].data.length) : 0;
                        return `Avg: ${avg.toFixed(1)}`;
                    }
                }
            ],
            // --- STORAGE ---
            'criminal.storage': [{
                    id: 'stor-c1',
                    title: 'Storage by Species',
                    type: 'bar',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.storage?.proportion || {});
                        if (!l.length) return {
                            labels: ['No Data'],
                            datasets: [{
                                label: 'None',
                                data: [0],
                                backgroundColor: '#64748b'
                            }]
                        };

                        // 🔥 FIXED: Removed Math.random(). Used ?? 0 so it correctly graphs actual zeros.
                        let godown = l.map(x => Number(db.storage?.species_godown?.[x] ?? 0));
                        let open = l.map(x => Number(db.storage?.species_open?.[x] ?? 0));

                        return {
                            labels: l,
                            datasets: [{
                                    label: 'Godown',
                                    data: godown,
                                    backgroundColor: '#f59e0b'
                                },
                                {
                                    label: 'Open Space',
                                    data: open,
                                    backgroundColor: '#10b981'
                                }
                            ]
                        };
                    },
                    options: {
                        scales: {
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true
                            }
                        }
                    },
                    calcPill: (data) => {
                        let total = data.datasets.reduce((sum, ds) => sum + ds.data.reduce((a, b) => a + b,
                            0), 0);
                        return `Vol: ~${total.toFixed(0)} Cmt`;
                    }
                },
                {
                    id: 'stor-c2',
                    title: 'Storage Proportion',
                    type: 'pie',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.storage?.proportion || {});
                        let v = Object.values(db.storage?.proportion || {}).map(Number);

                        if (!l.length || v.reduce((a, b) => a + b, 0) === 0) return {
                            labels: ['No Data'],
                            datasets: [{
                                data: [1],
                                backgroundColor: ['#64748b']
                            }]
                        };
                        return {
                            labels: l,
                            datasets: [{
                                data: v,
                                backgroundColor: COLOR_PALETTE
                            }]
                        };
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false
                            }
                        }
                    },
                    calcPill: (data) => {
                        let maxIdx = data.datasets[0].data.indexOf(Math.max(...data.datasets[0].data));
                        return `Top: ${data.labels[maxIdx] || 'N/A'}`;
                    }
                },
                {
                    id: 'stor-c3',
                    title: 'Volume Over Time',
                    type: 'bar',
                    toggles: [],
                    generator: (idx, db) => {
                        // 🔥 FIXED: Combine dates from both Godown and Open space
                        let dates = new Set([
                            ...Object.keys(db.storage?.time_godown || {}),
                            ...Object.keys(db.storage?.time_open || {})
                        ]);
                        let l = Array.from(dates).sort();

                        if (!l.length) return {
                            labels: ['No Data'],
                            datasets: [{
                                data: [0],
                                backgroundColor: '#64748b'
                            }]
                        };

                        let godown = l.map(x => Number(db.storage?.time_godown?.[x] ?? 0));
                        let open = l.map(x => Number(db.storage?.time_open?.[x] ?? 0));

                        return {
                            labels: l,
                            datasets: [{
                                    label: 'Godown',
                                    data: godown,
                                    backgroundColor: '#3b82f6'
                                },
                                {
                                    label: 'Open Space',
                                    data: open,
                                    backgroundColor: '#14b8a6'
                                }
                            ]
                        };
                    },
                    options: {
                        scales: {
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true
                            }
                        }
                    },
                    calcPill: () => `Trend: Active`
                }
            ],
            // --- ENCROACHMENT ---
            'criminal.encroachment': [{
                    id: 'enc-c1',
                    title: 'Scale Analytics (By Range)',
                    type: 'bar',
                    toggles: ['Area (Ha)', 'Occupants'],
                    generator: (idx, db) => {
                        let obj = idx === 0 ? db.encroachment?.area_by_range : db.encroachment
                            ?.occupants_by_range;
                        let l = Object.keys(obj || {});
                        let v = Object.values(obj || {});
                        if (!l.length) {
                            l = ['No Data'];
                            v = [0];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                label: idx === 0 ? 'Area' : 'Occupants',
                                data: v,
                                backgroundColor: idx === 0 ? '#f43f5e' : '#f59e0b',
                                borderRadius: 4
                            }]
                        };
                    },
                    calcPill: (data) => `Total: ${data.datasets[0].data.reduce((a,b)=>a+b,0).toFixed(0)}`
                },
                {
                    id: 'enc-c2',
                    title: 'Type Distribution',
                    type: 'doughnut',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.encroachment?.types || {});
                        let v = Object.values(db.encroachment?.types || {});
                        if (!l.length) {
                            l = ['No Data'];
                            v = [1];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                data: v,
                                backgroundColor: ['#10b981', '#f59e0b', '#f43f5e', '#3b82f6']
                            }]
                        };
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false
                            }
                        }
                    },
                    calcPill: (data) => {
                        let maxIdx = data.datasets[0].data.indexOf(Math.max(...data.datasets[0].data));
                        return `Major: ${data.labels[maxIdx] || 'N/A'}`;
                    }
                },
                {
                    id: 'enc-c3',
                    title: 'Encroachment Trend',
                    type: 'line',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.encroachment?.trend || {});
                        let v = Object.values(db.encroachment?.trend || {});
                        if (!l.length) {
                            l = ['No Data'];
                            v = [0];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                label: 'Area',
                                data: v,
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59,130,246,0.1)',
                                fill: true,
                                tension: 0.4
                            }]
                        };
                    },
                    calcPill: () => `Active`
                }
            ],
            // --- MINING ---
            'criminal.mining': [{
                    id: 'min-c1',
                    title: 'Extraction Volume by Range',
                    type: 'bar',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.mining?.volume_by_range || {});
                        let v = Object.values(db.mining?.volume_by_range || {});
                        if (!l.length) {
                            l = ['No Data'];
                            v = [0];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                label: 'Volume (CuM)',
                                data: v,
                                backgroundColor: '#f59e0b',
                                borderRadius: 4
                            }]
                        };
                    },
                    calcPill: (data) => `Total: ${data.datasets[0].data.reduce((a,b)=>a+b,0).toFixed(0)}`
                },
                {
                    id: 'min-c2',
                    title: 'Mineral Distribution',
                    type: 'doughnut',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.mining?.minerals || {});
                        let v = Object.values(db.mining?.minerals || {});
                        if (!l.length) {
                            l = ['No Data'];
                            v = [1];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                data: v,
                                backgroundColor: ['#10b981', '#f59e0b', '#f43f5e', '#3b82f6']
                            }]
                        };
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false
                            }
                        }
                    },
                    calcPill: (data) => {
                        let maxIdx = data.datasets[0].data.indexOf(Math.max(...data.datasets[0].data));
                        return `Dom: ${data.labels[maxIdx] || 'N/A'}`;
                    }
                },
                {
                    id: 'min-c3',
                    title: 'Volume by Method',
                    type: 'bar',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.mining?.methods || {});
                        let v = Object.values(db.mining?.methods || {});
                        if (!l.length) {
                            l = ['No Data'];
                            v = [0];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                label: 'Volume',
                                data: v,
                                backgroundColor: '#14b8a6',
                                borderRadius: 4
                            }]
                        };
                    },
                    calcPill: () => `Avg: 65%`
                }
            ],
            // --- WILDLIFE SIGHTING ---
            'events.wildlife': [{
                    id: 'wl-c1',
                    title: 'Species Sighting Analysis',
                    type: 'bar',
                    toggles: ['Type', 'Gender'],
                    generator: (idx, db) => {
                        let obj = idx === 0 ? db.wildlife?.type : db.wildlife?.gender;
                        let labels = Object.keys(obj || {});
                        if (!labels.length) return {
                            labels: ['No Data'],
                            datasets: [{
                                data: [0]
                            }]
                        };

                        let keys = idx === 0 ? ['Direct', 'Indirect'] : ['Male', 'Female', 'Unknown'];
                        let colors = idx === 0 ? [CHART_COLORS.emerald, CHART_COLORS.teal] : [CHART_COLORS
                            .blue, CHART_COLORS.rose, CHART_COLORS.slate
                        ];

                        let datasets = keys.map((k, i) => {
                            return {
                                label: k,
                                data: labels.map(sp => obj[sp]?.[k] || 0),
                                backgroundColor: colors[i]
                            };
                        });
                        return {
                            labels,
                            datasets
                        };
                    },
                    options: {
                        scales: {
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true
                            }
                        }
                    },
                    calcPill: (data) =>
                        `Records: ${data.datasets.reduce((sum, ds) => sum + ds.data.reduce((a,b)=>a+b,0), 0)}`
                },
                {
                    id: 'wl-c2',
                    title: 'Evidence Type Distribution',
                    type: 'pie',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.wildlife?.evidence || {});
                        let v = Object.values(db.wildlife?.evidence || {}).map(Number);
                        if (!l.length || v.reduce((a, b) => a + b, 0) === 0) {
                            l = ['No Data'];
                            v = [1];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                data: v,
                                backgroundColor: l[0] === 'No Data' ? [CHART_COLORS.slate] :
                                    COLOR_PALETTE
                            }]
                        };
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false
                            }
                        }
                    },
                    calcPill: (data) =>
                        `Pri: ${data.labels[data.datasets[0].data.indexOf(Math.max(...data.datasets[0].data))] || 'N/A'}`
                },
                {
                    id: 'wl-c3',
                    title: 'Sighting Timeline',
                    type: 'line',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.wildlife?.trend || {});
                        let v = Object.values(db.wildlife?.trend || {}).map(Number);
                        if (!l.length) {
                            l = ['No Data'];
                            v = [0];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                label: 'Sightings',
                                data: v,
                                borderColor: CHART_COLORS.emerald,
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                fill: true,
                                tension: 0.4
                            }]
                        };
                    },
                    calcPill: () => `Active`
                }
            ],
            // --- WATER STATUS ---
            'events.water': [{
                    id: 'wat-c1',
                    title: 'Water Availability',
                    type: 'bar',
                    toggles: [],
                    generator: (idx, db) => {
                        let obj = db.water?.availability || {};
                        let labels = Object.keys(obj);
                        if (!labels.length) return {
                            labels: ['No Data'],
                            datasets: [{
                                data: [0]
                            }]
                        };

                        let wet = labels.map(l => obj[l]?.['No'] || 0); // 'No' means NOT dry (Wet)
                        let dry = labels.map(l => obj[l]?.['Yes'] || 0); // 'Yes' means dry

                        return {
                            labels,
                            datasets: [{
                                    label: 'Has Water',
                                    data: wet,
                                    backgroundColor: CHART_COLORS.blue
                                },
                                {
                                    label: 'Dry',
                                    data: dry,
                                    backgroundColor: CHART_COLORS.amber
                                }
                            ]
                        };
                    },
                    options: {
                        scales: {
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true
                            }
                        }
                    },
                    calcPill: (data) => `Sources: ${data.labels.length}`
                },
                {
                    id: 'wat-c2',
                    title: 'Water Quality (Pending Update)',
                    type: 'doughnut',
                    toggles: [],
                    generator: () => ({
                        labels: ['Clear', 'Turbid', 'Contaminated'],
                        datasets: [{
                            data: [60, 25, 15],
                            backgroundColor: [CHART_COLORS.teal, CHART_COLORS.amber,
                                CHART_COLORS.rose
                            ]
                        }]
                    }),
                    options: {
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false
                            }
                        }
                    },
                    calcPill: () => `Good: 60%`
                },
                {
                    id: 'wat-c3',
                    title: 'Reports by Range',
                    type: 'bar',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.water?.ranges || {});
                        let v = Object.values(db.water?.ranges || {}).map(Number);
                        if (!l.length) {
                            l = ['No Data'];
                            v = [0];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                label: 'Reports',
                                data: v,
                                backgroundColor: CHART_COLORS.indigo,
                                borderRadius: 4
                            }]
                        };
                    },
                    options: {
                        indexAxis: 'y'
                    },
                    calcPill: (data) =>
                        `Max: ${Math.max(...(data.datasets[0].data.length ? data.datasets[0].data : [0]))}`
                }
            ],
            // --- COMPENSATION ---
            'events.compensation': [{
                    id: 'comp-c1',
                    title: 'Claims by Category',
                    type: 'bar',
                    toggles: ['Cases', 'Amount (₹)'],
                    generator: (idx, db) => {
                        let obj = idx === 0 ? db.compensation?.claims_qty : db.compensation?.claims_amt;
                        let l = Object.keys(obj || {});
                        let v = Object.values(obj || {}).map(Number);
                        if (!l.length) {
                            l = ['No Data'];
                            v = [0];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                label: idx === 0 ? 'Cases' : 'Amount',
                                data: v,
                                backgroundColor: idx === 0 ? CHART_COLORS.rose : CHART_COLORS.amber,
                                borderRadius: 4
                            }]
                        };
                    },
                    calcPill: (data, idx) => idx === 1 ?
                        `Total: ₹${(data.datasets[0].data.reduce((a,b)=>a+b,0)/1000).toFixed(1)}k` :
                        `Total: ${data.datasets[0].data.reduce((a,b)=>a+b,0)}`
                },
                {
                    id: 'comp-c2',
                    title: 'Distribution Overview',
                    type: 'pie',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.compensation?.claims_qty || {});
                        let v = Object.values(db.compensation?.claims_qty || {}).map(Number);
                        if (!l.length || v.reduce((a, b) => a + b, 0) === 0) {
                            l = ['No Data'];
                            v = [1];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                data: v,
                                backgroundColor: l[0] === 'No Data' ? [CHART_COLORS.slate] :
                                    COLOR_PALETTE
                            }]
                        };
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false
                            }
                        }
                    },
                    calcPill: (data) =>
                        `Top: ${data.labels[data.datasets[0].data.indexOf(Math.max(...data.datasets[0].data))] || 'N/A'}`
                },
                {
                    id: 'comp-c3',
                    title: 'Claims Timeline',
                    type: 'line',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.compensation?.trend || {});
                        let v = Object.values(db.compensation?.trend || {}).map(Number);
                        if (!l.length) {
                            l = ['No Data'];
                            v = [0];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                label: 'Cases',
                                data: v,
                                borderColor: CHART_COLORS.blue,
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                fill: true,
                                tension: 0.4
                            }]
                        };
                    },
                    calcPill: () => `Active`
                }
            ],

            // --- WILDLIFE SIGHTING ---
            'events.wildlife': [{
                    id: 'wl-c1',
                    title: 'Species Sighting Analysis',
                    type: 'bar',
                    toggles: ['Type', 'Gender'],
                    generator: (idx, db) => {
                        let obj = idx === 0 ? db.wildlife?.type : db.wildlife?.gender;
                        let labels = Object.keys(obj || {});
                        if (!labels.length) return {
                            labels: ['No Data'],
                            datasets: [{
                                data: [0]
                            }]
                        };

                        let keys = idx === 0 ? ['Direct', 'Indirect'] : ['Male', 'Female', 'Unknown'];
                        let colors = idx === 0 ? [CHART_COLORS.emerald, CHART_COLORS.teal] : [CHART_COLORS
                            .blue, CHART_COLORS.rose, CHART_COLORS.slate
                        ];

                        let datasets = keys.map((k, i) => {
                            return {
                                label: k,
                                data: labels.map(sp => obj[sp]?.[k] || 0),
                                backgroundColor: colors[i]
                            };
                        });
                        return {
                            labels,
                            datasets
                        };
                    },
                    options: {
                        scales: {
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true
                            }
                        }
                    },
                    calcPill: (data) =>
                        `Records: ${data.datasets.reduce((sum, ds) => sum + ds.data.reduce((a,b)=>a+b,0), 0)}`
                },
                {
                    id: 'wl-c2',
                    title: 'Evidence Type Distribution',
                    type: 'pie',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.wildlife?.evidence || {});
                        let v = Object.values(db.wildlife?.evidence || {}).map(Number);
                        if (!l.length || v.reduce((a, b) => a + b, 0) === 0) {
                            l = ['No Data'];
                            v = [1];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                data: v,
                                backgroundColor: l[0] === 'No Data' ? [CHART_COLORS.slate] :
                                    COLOR_PALETTE
                            }]
                        };
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false
                            }
                        }
                    },
                    calcPill: (data) =>
                        `Pri: ${data.labels[data.datasets[0].data.indexOf(Math.max(...data.datasets[0].data))] || 'N/A'}`
                },
                {
                    id: 'wl-c3',
                    title: 'Sighting Timeline',
                    type: 'line',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.wildlife?.trend || {});
                        let v = Object.values(db.wildlife?.trend || {}).map(Number);
                        if (!l.length) {
                            l = ['No Data'];
                            v = [0];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                label: 'Sightings',
                                data: v,
                                borderColor: CHART_COLORS.emerald,
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                fill: true,
                                tension: 0.4
                            }]
                        };
                    },
                    calcPill: () => `Active`
                }
            ],
            // --- WATER STATUS ---
            'events.water': [{
                    id: 'wat-c1',
                    title: 'Water Availability',
                    type: 'bar',
                    toggles: [],
                    generator: (idx, db) => {
                        let obj = db.water?.availability || {};
                        let labels = Object.keys(obj);
                        if (!labels.length) return {
                            labels: ['No Data'],
                            datasets: [{
                                data: [0]
                            }]
                        };

                        let wet = labels.map(l => obj[l]?.['No'] || 0); // 'No' means NOT dry (Wet)
                        let dry = labels.map(l => obj[l]?.['Yes'] || 0); // 'Yes' means dry

                        return {
                            labels,
                            datasets: [{
                                    label: 'Has Water',
                                    data: wet,
                                    backgroundColor: CHART_COLORS.blue
                                },
                                {
                                    label: 'Dry',
                                    data: dry,
                                    backgroundColor: CHART_COLORS.amber
                                }
                            ]
                        };
                    },
                    options: {
                        scales: {
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true
                            }
                        }
                    },
                    calcPill: (data) => `Sources: ${data.labels.length}`
                },
                {
                    id: 'wat-c2',
                    title: 'Water Quality (Pending Update)',
                    type: 'doughnut',
                    toggles: [],
                    generator: () => ({
                        labels: ['Clear', 'Turbid', 'Contaminated'],
                        datasets: [{
                            data: [60, 25, 15],
                            backgroundColor: [CHART_COLORS.teal, CHART_COLORS.amber,
                                CHART_COLORS.rose
                            ]
                        }]
                    }),
                    options: {
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false
                            }
                        }
                    },
                    calcPill: () => `Good: 60%`
                },
                {
                    id: 'wat-c3',
                    title: 'Reports by Range',
                    type: 'bar',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.water?.ranges || {});
                        let v = Object.values(db.water?.ranges || {}).map(Number);
                        if (!l.length) {
                            l = ['No Data'];
                            v = [0];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                label: 'Reports',
                                data: v,
                                backgroundColor: CHART_COLORS.indigo,
                                borderRadius: 4
                            }]
                        };
                    },
                    options: {
                        indexAxis: 'y'
                    },
                    calcPill: (data) =>
                        `Max: ${Math.max(...(data.datasets[0].data.length ? data.datasets[0].data : [0]))}`
                }
            ],
            // --- COMPENSATION ---
            'events.compensation': [{
                    id: 'comp-c1',
                    title: 'Claims by Category',
                    type: 'bar',
                    toggles: ['Cases', 'Amount (₹)'],
                    generator: (idx, db) => {
                        let obj = idx === 0 ? db.compensation?.claims_qty : db.compensation?.claims_amt;
                        let l = Object.keys(obj || {});
                        let v = Object.values(obj || {}).map(Number);
                        if (!l.length) {
                            l = ['No Data'];
                            v = [0];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                label: idx === 0 ? 'Cases' : 'Amount',
                                data: v,
                                backgroundColor: idx === 0 ? CHART_COLORS.rose : CHART_COLORS.amber,
                                borderRadius: 4
                            }]
                        };
                    },
                    calcPill: (data, idx) => idx === 1 ?
                        `Total: ₹${(data.datasets[0].data.reduce((a,b)=>a+b,0)/1000).toFixed(1)}k` :
                        `Total: ${data.datasets[0].data.reduce((a,b)=>a+b,0)}`
                },
                {
                    id: 'comp-c2',
                    title: 'Distribution Overview',
                    type: 'pie',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.compensation?.claims_qty || {});
                        let v = Object.values(db.compensation?.claims_qty || {}).map(Number);
                        if (!l.length || v.reduce((a, b) => a + b, 0) === 0) {
                            l = ['No Data'];
                            v = [1];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                data: v,
                                backgroundColor: l[0] === 'No Data' ? [CHART_COLORS.slate] :
                                    COLOR_PALETTE
                            }]
                        };
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false
                            }
                        }
                    },
                    calcPill: (data) =>
                        `Top: ${data.labels[data.datasets[0].data.indexOf(Math.max(...data.datasets[0].data))] || 'N/A'}`
                },
                {
                    id: 'comp-c3',
                    title: 'Claims Timeline',
                    type: 'line',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.compensation?.trend || {});
                        let v = Object.values(db.compensation?.trend || {}).map(Number);
                        if (!l.length) {
                            l = ['No Data'];
                            v = [0];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                label: 'Cases',
                                data: v,
                                borderColor: CHART_COLORS.blue,
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                fill: true,
                                tension: 0.4
                            }]
                        };
                    },
                    calcPill: () => `Active`
                }
            ],
            // --- FIRE INCIDENTS ---
            // --- FIRE INCIDENTS ---
            'fire.fire': [{
                    id: 'fire-c1',
                    title: 'Incidents by Region & Area',
                    type: 'bar',
                    toggles: ['Incidents', 'Area (Ha)'],
                    generator: (idx, db) => {
                        let obj = idx === 0 ? db.fire?.ranges_incidents : db.fire?.ranges_area;
                        let l = Object.keys(obj || {});
                        let v = Object.values(obj || {}).map(Number);

                        if (!l.length) return {
                            labels: ['No Data'],
                            datasets: [{
                                data: [0],
                                backgroundColor: '#64748b'
                            }]
                        };

                        return {
                            labels: l,
                            datasets: [{
                                label: idx === 0 ? 'Incidents' : 'Area Burnt',
                                data: v,
                                backgroundColor: idx === 0 ? '#ef4444' : '#f97316',
                                borderRadius: 4
                            }]
                        };
                    },
                    calcPill: (data) => `Alerts: ${data.datasets[0].data.reduce((a,b)=>a+b,0).toFixed(0)}`
                },
                {
                    id: 'fire-c2',
                    title: 'Fire Cause Distribution',
                    type: 'doughnut',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.fire?.causes || {});
                        let v = Object.values(db.fire?.causes || {}).map(Number);

                        if (!l.length || v.reduce((a, b) => a + b, 0) === 0) return {
                            labels: ['No Data'],
                            datasets: [{
                                data: [1],
                                backgroundColor: ['#64748b']
                            }]
                        };

                        // Fire Theme Colors
                        let fireColors = ['#ef4444', '#f97316', '#f59e0b', '#eab308', '#64748b'];
                        return {
                            labels: l,
                            datasets: [{
                                data: v,
                                backgroundColor: fireColors
                            }]
                        };
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false
                            }
                        }
                    },
                    calcPill: (data) => {
                        let maxIdx = data.datasets[0].data.indexOf(Math.max(...data.datasets[0].data));
                        return `Top: ${data.labels[maxIdx] || 'N/A'}`;
                    }
                },
                {
                    id: 'fire-c3',
                    title: '30-Day Trend',
                    type: 'line',
                    toggles: ['Incidents', 'Area (Ha)'],
                    generator: (idx, db) => {
                        let obj = idx === 0 ? db.fire?.trend_incidents : db.fire?.trend_area;
                        let l = Object.keys(obj || {});
                        let v = Object.values(obj || {}).map(Number);

                        if (!l.length) return {
                            labels: ['No Data'],
                            datasets: [{
                                data: [0],
                                borderColor: '#64748b',
                                backgroundColor: 'rgba(100,116,139,0.1)'
                            }]
                        };

                        return {
                            labels: l,
                            datasets: [{
                                label: idx === 0 ? 'Incidents' : 'Area',
                                data: v,
                                borderColor: idx === 0 ? '#ef4444' : '#f59e0b',
                                backgroundColor: idx === 0 ? 'rgba(239,68,68,0.1)' :
                                    'rgba(245,158,11,0.1)',
                                fill: true,
                                tension: 0.4
                            }]
                        };
                    },
                    options: {
                        plugins: {
                            datalabels: {
                                display: false
                            }
                        }
                    },
                    calcPill: (data) => `Peak: ${Math.max(...data.datasets[0].data).toFixed(0)}`
                },
                {
                    id: 'fire-c4',
                    title: 'Avg Response Time (Mins) by Region',
                    type: 'bar',
                    toggles: [],
                    generator: (idx, db) => {
                        let totalTimes = db.fire?.ranges_resp_time || {};
                        let counts = db.fire?.ranges_resp_count || {};

                        let l = Object.keys(totalTimes);
                        if (!l.length) return {
                            labels: ['No Data'],
                            datasets: [{
                                data: [0],
                                backgroundColor: '#64748b'
                            }]
                        };

                        let v = l.map(rng => {
                            let t = Number(totalTimes[rng] || 0);
                            let c = Number(counts[rng] || 1);
                            return Math.round(t / c);
                        });

                        return {
                            labels: l,
                            datasets: [{
                                label: 'Avg Mins',
                                data: v,
                                backgroundColor: '#6366f1',
                                borderRadius: 4
                            }]
                        };
                    },
                    options: {
                        indexAxis: 'y'
                    },
                    calcPill: (data) => {
                        let total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                        let avg = data.datasets[0].data.length ? (total / data.datasets[0].data.length) : 0;
                        return `Avg: ${avg.toFixed(0)}m`;
                    }
                }
            ],
            // Inside the config.views object, add this:

            'assets.inventory': [{
                    id: 'ast-c1',
                    title: 'Asset Distribution',
                    type: 'pie',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.assets?.distribution || {});
                        let v = Object.values(db.assets?.distribution || {}).map(Number);
                        if (!l.length) {
                            l = ['No Data'];
                            v = [1];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                data: v,
                                backgroundColor: COLOR_PALETTE
                            }]
                        };
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false
                            }
                        }
                    },
                    calcPill: (data) => `Total: ${data.datasets[0].data.reduce((a,b)=>a+b,0)}`
                },
                {
                    id: 'ast-c2',
                    title: 'Operational Status',
                    type: 'bar',
                    toggles: [],
                    generator: (idx, db) => {
                        let cats = Object.keys(db.assets?.status || {});
                        if (!cats.length) cats = ['Vehicles', 'Equipment', 'Checkposts'];

                        // Map "Good" to Active, others to Maintenance
                        let active = cats.map(c => db.assets?.status?.[c]?.['Good'] || 0);
                        let maintenance = cats.map(c => {
                            let total = Object.values(db.assets?.status?.[c] || {}).reduce((a, b) =>
                                a + b, 0);
                            return total - (db.assets?.status?.[c]?.['Good'] || 0);
                        });

                        return {
                            labels: cats,
                            datasets: [{
                                    label: 'Active',
                                    data: active,
                                    backgroundColor: CHART_COLORS.emerald
                                },
                                {
                                    label: 'Maintenance',
                                    data: maintenance,
                                    backgroundColor: CHART_COLORS.amber
                                }
                            ]
                        };
                    },
                    options: {
                        scales: {
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true
                            }
                        }
                    },
                    calcPill: (data) => {
                        let total = data.datasets[0].data.reduce((a, b) => a + b, 0) + data.datasets[1].data
                            .reduce((a, b) => a + b, 0);
                        let active = data.datasets[0].data.reduce((a, b) => a + b, 0);
                        let perc = total > 0 ? Math.round((active / total) * 100) : 0;
                        return `Active: ${perc}%`;
                    }
                },
                {
                    id: 'ast-c3',
                    title: 'Deployment Trend',
                    type: 'line',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.assets?.trend || {});
                        let v = Object.values(db.assets?.trend || {}).map(Number);
                        if (!l.length) {
                            l = ['Wk 1', 'Wk 2', 'Wk 3'];
                            v = [0, 0, 0];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                label: 'Deployed',
                                data: v,
                                borderColor: CHART_COLORS.indigo,
                                backgroundColor: CHART_COLORS.indigo + '22',
                                fill: true,
                                tension: 0.4
                            }]
                        };
                    },
                    calcPill: () => `Status: Stable`
                }
            ],
            // --- FIRE INCIDENTS (Restored from Image 4 & 5) ---
            'fire.none': [{
                    id: 'fire-c1',
                    title: 'Incidents by Region & Severity',
                    type: 'bar',
                    toggles: ['Incidents', 'Area', 'Severity'],
                    generator: (idx) => {
                        if (idx < 2) return {
                            labels: REGIONS,
                            datasets: [{
                                label: idx === 0 ? 'Incidents' : 'Area(Ha)',
                                data: [2, 3, 17, 18, 9],
                                backgroundColor: CHART_COLORS.rose,
                                borderRadius: 4
                            }]
                        };
                        return {
                            labels: REGIONS,
                            datasets: [{
                                    label: 'High',
                                    data: [1, 1, 10, 8, 4],
                                    backgroundColor: CHART_COLORS.rose
                                },
                                {
                                    label: 'Med',
                                    data: [1, 2, 5, 6, 3],
                                    backgroundColor: CHART_COLORS.amber
                                },
                                {
                                    label: 'Low',
                                    data: [0, 0, 2, 4, 2],
                                    backgroundColor: CHART_COLORS.amber + '88'
                                }
                            ]
                        };
                    },
                    options: (idx) => idx === 2 ? {
                        scales: {
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true
                            }
                        }
                    } : {},
                    calcPill: () => `Alerts: 45`
                },
                {
                    id: 'fire-c2',
                    title: 'Fire Cause Distribution',
                    type: 'doughnut',
                    toggles: [],
                    generator: () => ({
                        labels: ['Natural', 'Negligent', 'Intentional', 'Unknown'],
                        datasets: [{
                            data: [20, 40, 30, 10],
                            backgroundColor: [CHART_COLORS.rose, CHART_COLORS.amber,
                                CHART_COLORS.orange, CHART_COLORS.slate
                            ],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    }),
                    options: {
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false
                            }
                        }
                    },
                    calcPill: () => `Top: Negligent`
                },
                {
                    id: 'fire-c3',
                    title: '30-Day Trend',
                    type: 'line',
                    toggles: ['Incidents', 'Area'],
                    generator: (idx) => {
                        const days = Array.from({
                            length: 15
                        }, (_, i) => `D${i+1}`);
                        return {
                            labels: days,
                            datasets: [{
                                label: idx === 0 ? 'Incidents' : 'Area Burnt',
                                data: [2, 0, 4, 3, 2, 2, 4, 1, 1, 4, 3, 3, 2, 1, 3],
                                borderColor: CHART_COLORS.rose,
                                backgroundColor: CHART_COLORS.rose + '33',
                                fill: true,
                                tension: 0.4
                            }]
                        };
                    },
                    calcPill: () => `Peak: D14`
                },
                {
                    id: 'fire-c4',
                    title: 'Response Time & Risk Zones',
                    type: 'bar',
                    toggles: ['Mins', 'Index'],
                    generator: (idx) => ({
                        labels: REGIONS,
                        datasets: [{
                            label: idx === 0 ? 'Avg Mins' : 'Risk Index',
                            data: [117, 24, 94, 95, 94],
                            backgroundColor: idx === 0 ? CHART_COLORS.indigo : CHART_COLORS
                                .rose,
                            borderRadius: 4
                        }]
                    }),
                    options: {
                        indexAxis: 'y'
                    },
                    calcPill: () => `Avg: 42m`
                }
            ],
            // --- EVENTS: WILDLIFE ---
            'events.wildlife': [{
                    id: 'wl-c1',
                    title: 'Species Sighting Analysis',
                    type: 'bar',
                    toggles: ['Type', 'Gender'],
                    generator: (idx, db) => {
                        let obj = idx === 0 ? db.wildlife?.type : db.wildlife?.gender;
                        let labels = Object.keys(obj || {});
                        if (!labels.length) return {
                            labels: ['No Data'],
                            datasets: [{
                                data: [0]
                            }]
                        };

                        let keys = idx === 0 ? ['Direct', 'Indirect'] : ['Male', 'Female', 'Unknown'];
                        let colors = idx === 0 ? [CHART_COLORS.emerald, CHART_COLORS.teal] : [CHART_COLORS
                            .blue, CHART_COLORS.rose, CHART_COLORS.slate
                        ];

                        let datasets = keys.map((k, i) => {
                            return {
                                label: k,
                                data: labels.map(sp => obj[sp]?.[k] || 0),
                                backgroundColor: colors[i],
                                borderRadius: 4
                            };
                        });
                        return {
                            labels,
                            datasets
                        };
                    },
                    options: {
                        scales: {
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true
                            }
                        }
                    },
                    calcPill: (data) =>
                        `Total: ${data.datasets.reduce((sum, ds) => sum + ds.data.reduce((a,b)=>a+b,0), 0)}`
                },
                {
                    id: 'wl-c2',
                    title: 'Evidence Type Distribution',
                    type: 'pie',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.wildlife?.evidence || {});
                        let v = Object.values(db.wildlife?.evidence || {}).map(Number);
                        if (!l.length || v.reduce((a, b) => a + b, 0) === 0) {
                            l = ['No Data'];
                            v = [1];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                data: v,
                                backgroundColor: l[0] === 'No Data' ? [CHART_COLORS.slate] :
                                    COLOR_PALETTE
                            }]
                        };
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false
                            }
                        }
                    },
                    calcPill: (data) =>
                        `Pri: ${data.labels[data.datasets[0].data.indexOf(Math.max(...data.datasets[0].data))] || 'N/A'}`
                },
                {
                    id: 'wl-c3',
                    title: 'Sighting Timeline',
                    type: 'line',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.wildlife?.trend || {});
                        let v = Object.values(db.wildlife?.trend || {}).map(Number);
                        if (!l.length) {
                            l = ['No Data'];
                            v = [0];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                label: 'Sightings',
                                data: v,
                                borderColor: CHART_COLORS.emerald,
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                fill: true,
                                tension: 0.4
                            }]
                        };
                    },
                    options: {
                        plugins: {
                            datalabels: {
                                display: false
                            }
                        }
                    }, // Disable datalabels for lines
                    calcPill: () => `Active`
                }
            ],
            // --- EVENTS: WATER STATUS ---
            'events.water': [{
                    id: 'wat-c1',
                    title: 'Water Availability',
                    type: 'bar',
                    toggles: [],
                    generator: (idx, db) => {
                        let obj = db.water?.availability || {};
                        let labels = Object.keys(obj);
                        if (!labels.length) return {
                            labels: ['No Data'],
                            datasets: [{
                                data: [0]
                            }]
                        };

                        let wet = labels.map(l => obj[l]?.['No'] || 0); // 'No' means NOT dry (Wet)
                        let dry = labels.map(l => obj[l]?.['Yes'] || 0); // 'Yes' means dry

                        return {
                            labels,
                            datasets: [{
                                    label: 'Has Water',
                                    data: wet,
                                    backgroundColor: CHART_COLORS.blue,
                                    borderRadius: 4
                                },
                                {
                                    label: 'Dry',
                                    data: dry,
                                    backgroundColor: CHART_COLORS.amber,
                                    borderRadius: 4
                                }
                            ]
                        };
                    },
                    options: {
                        scales: {
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true
                            }
                        }
                    },
                    calcPill: (data) => `Sources: ${data.labels.length}`
                },
                {
                    id: 'wat-c2',
                    title: 'Water Quality',
                    type: 'doughnut',
                    toggles: [],
                    generator: () => ({
                        labels: ['Clear', 'Turbid', 'Contaminated'],
                        datasets: [{
                            data: [60, 25, 15],
                            backgroundColor: [CHART_COLORS.teal, CHART_COLORS.amber,
                                CHART_COLORS.rose
                            ]
                        }]
                    }),
                    options: {
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false
                            }
                        }
                    },
                    calcPill: () => `Good: 60%`
                },
                {
                    id: 'wat-c3',
                    title: 'Reports by Range',
                    type: 'bar',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.water?.ranges || {});
                        let v = Object.values(db.water?.ranges || {}).map(Number);
                        if (!l.length) {
                            l = ['No Data'];
                            v = [0];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                label: 'Reports',
                                data: v,
                                backgroundColor: CHART_COLORS.indigo,
                                borderRadius: 4
                            }]
                        };
                    },
                    options: {
                        indexAxis: 'y'
                    },
                    calcPill: (data) =>
                        `Max: ${Math.max(...(data.datasets[0].data.length ? data.datasets[0].data : [0]))}`
                }
            ],
            // --- EVENTS: COMPENSATION ---
            'events.compensation': [{
                    id: 'comp-c1',
                    title: 'Claims by Category',
                    type: 'bar',
                    toggles: ['Amount (₹)', 'Cases'],
                    generator: (idx, db) => {
                        let obj = idx === 0 ? db.compensation?.claims_amt : db.compensation?.claims_qty;
                        let l = Object.keys(obj || {});
                        let v = Object.values(obj || {}).map(Number);
                        if (!l.length) {
                            l = ['No Data'];
                            v = [0];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                label: idx === 0 ? 'Amount' : 'Cases',
                                data: v,
                                backgroundColor: idx === 0 ? CHART_COLORS.amber : CHART_COLORS.rose,
                                borderRadius: 4
                            }]
                        };
                    },
                    calcPill: (data, idx) => idx === 0 ?
                        `Total: ₹${(data.datasets[0].data.reduce((a,b)=>a+b,0)/1000).toFixed(1)}k` :
                        `Total: ${data.datasets[0].data.reduce((a,b)=>a+b,0)}`
                },
                {
                    id: 'comp-c2',
                    title: 'Distribution Overview',
                    type: 'pie',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.compensation?.claims_qty || {});
                        let v = Object.values(db.compensation?.claims_qty || {}).map(Number);
                        if (!l.length || v.reduce((a, b) => a + b, 0) === 0) {
                            l = ['No Data'];
                            v = [1];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                data: v,
                                backgroundColor: l[0] === 'No Data' ? [CHART_COLORS.slate] :
                                    COLOR_PALETTE
                            }]
                        };
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false
                            }
                        }
                    },
                    calcPill: (data) =>
                        `Top: ${data.labels[data.datasets[0].data.indexOf(Math.max(...data.datasets[0].data))] || 'N/A'}`
                },
                {
                    id: 'comp-c3',
                    title: 'Claims Timeline',
                    type: 'line',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.compensation?.trend || {});
                        let v = Object.values(db.compensation?.trend || {}).map(Number);
                        if (!l.length) {
                            l = ['No Data'];
                            v = [0];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                label: 'Cases',
                                data: v,
                                borderColor: CHART_COLORS.blue,
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                fill: true,
                                tension: 0.4
                            }]
                        };
                    },
                    options: {
                        plugins: {
                            datalabels: {
                                display: false
                            }
                        }
                    },
                    calcPill: () => `Active`
                }
            ],
            // --- ASSETS ---
            'assets.inventory': [{
                    id: 'ast-c1',
                    title: 'Asset Distribution',
                    type: 'pie',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.assets?.distribution || {});
                        let v = Object.values(db.assets?.distribution || {}).map(Number);
                        if (!l.length || v.reduce((a, b) => a + b, 0) === 0) {
                            l = ['No Data'];
                            v = [1];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                data: v,
                                backgroundColor: l[0] === 'No Data' ? [CHART_COLORS.slate] :
                                    COLOR_PALETTE
                            }]
                        };
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false
                            }
                        }
                    },
                    calcPill: (data) => `Total: ${data.datasets[0].data.reduce((a,b)=>a+b,0)}`
                },
                {
                    id: 'ast-c2',
                    title: 'Operational Status',
                    type: 'bar',
                    toggles: [],
                    generator: (idx, db) => {
                        let cats = Object.keys(db.assets?.status || {});
                        if (!cats.length) return {
                            labels: ['No Data'],
                            datasets: [{
                                data: [0]
                            }]
                        };

                        let active = cats.map(c => db.assets?.status?.[c]?.['Good'] || 0);
                        let maintenance = cats.map(c => {
                            let total = Object.values(db.assets?.status?.[c] || {}).reduce((a, b) =>
                                a + b, 0);
                            return total - (db.assets?.status?.[c]?.['Good'] || 0);
                        });

                        return {
                            labels: cats,
                            datasets: [{
                                    label: 'Active',
                                    data: active,
                                    backgroundColor: CHART_COLORS.emerald,
                                    borderRadius: 4
                                },
                                {
                                    label: 'Maintenance',
                                    data: maintenance,
                                    backgroundColor: CHART_COLORS.amber,
                                    borderRadius: 4
                                }
                            ]
                        };
                    },
                    options: {
                        scales: {
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true
                            }
                        }
                    },
                    calcPill: (data) => {
                        if (data.labels[0] === 'No Data') return '0%';
                        let total = data.datasets[0].data.reduce((a, b) => a + b, 0) + data.datasets[1].data
                            .reduce((a, b) => a + b, 0);
                        let active = data.datasets[0].data.reduce((a, b) => a + b, 0);
                        let perc = total > 0 ? Math.round((active / total) * 100) : 0;
                        return `Active: ${perc}%`;
                    }
                },
                {
                    id: 'ast-c3',
                    title: 'Deployment Trend',
                    type: 'line',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.assets?.trend || {});
                        let v = Object.values(db.assets?.trend || {}).map(Number);
                        if (!l.length) {
                            l = ['No Data'];
                            v = [0];
                        }
                        return {
                            labels: l,
                            datasets: [{
                                label: 'Deployed',
                                data: v,
                                borderColor: CHART_COLORS.indigo,
                                backgroundColor: CHART_COLORS.indigo + '22',
                                fill: true,
                                tension: 0.4
                            }]
                        };
                    },
                    options: {
                        plugins: {
                            datalabels: {
                                display: false
                            }
                        }
                    },
                    calcPill: () => `Stable`
                }
            ]
        }
    };


    /* =================================================================
       2. INITIALIZATION
    ================================================================= */
    document.addEventListener('DOMContentLoaded', () => {
        initOverallMap();
        initOverallChart();
        renderMainTabs();

        window.addEventListener('themeChanged', () => {
            updateMapTheme();
            initOverallChart();
            if (document.getElementById('analytical-container') && !document.getElementById(
                    'analytical-container').classList.contains('d-none')) {
                window.renderAnalyticalCharts();
            }
        });

        // Initialize Sidebar Events
        const sidebar = document.querySelector('.map-filter-sidebar');
        const toggleBtn = document.getElementById('mapDrawerToggle');

        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('open');
                this.classList.toggle('active');
                const icon = this.querySelector('i');
                icon.className = sidebar.classList.contains('open') ? 'bi bi-x-lg' :
                    'bi bi-layers-half';
            });
        }

        // Fetch initial map layer counts from backend
        loadLayerCounts();
    });

    /* =================================================================
       3. GOOGLE MAPS LOGIC & AJAX DATA (Restored)
    ================================================================= */
    function initOverallMap() {
        const mapEl = document.getElementById('map');
        if (!mapEl) return;

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
            },
            mapTypeId: 'roadmap',
            styles: isDark ? darkStyle : [],
            mapTypeControl: false,
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

        setupCtrlScroll(mapEl);
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

    function setupCtrlScroll(mapEl) {
        const scrollMsg = document.getElementById('map-scroll-msg');
        if (!scrollMsg) return;

        overallMap.setOptions({
            scrollwheel: false
        });
        let scrollTimeout;

        mapEl.addEventListener('wheel', (e) => {
            if (e.ctrlKey) {
                overallMap.setOptions({
                    scrollwheel: true
                });
            } else {
                overallMap.setOptions({
                    scrollwheel: false
                });
                scrollMsg.classList.remove('d-none');
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    scrollMsg.classList.add('d-none');
                }, 1500);
            }
        });
    }

    // --- AJAX LAYER FUNCTIONS ---
    window.toggleLayerUI = function(layerType) {
        const cb = document.getElementById('check_' + layerType);
        if (cb) {
            cb.checked = !cb.checked;
            updateLayerUIState(layerType, cb.checked);

            if (layerType === 'geofences') {
                if (layerShapes.geofences) {
                    layerShapes.geofences.forEach(s => s.setMap(cb.checked ? overallMap : null));
                }
            } else {
                if (cb.checked) {
                    if (loadedLayers[layerType]) {
                        showLayer(layerType);
                    } else {
                        fetchLayerData(layerType);
                    }
                } else {
                    hideLayer(layerType);
                }
            }
        }
    };

    function updateLayerUIState(layerType, active) {
        const item = document.getElementById('item_' + layerType);
        const eye = document.getElementById('eye_' + layerType);
        if (item) item.classList.toggle('active', active);
        if (eye) eye.classList.toggle('active', active);
    }

    function loadLayerCounts() {
        const loader = document.getElementById('customMapLoader');
        if (loader) loader.classList.remove('d-none');

        // Note: Make sure you have the hidden #filterForm in your blade file
        const formEl = document.getElementById('filterForm');
        const formData = formEl ? new FormData(formEl) : new FormData();
        const params = new URLSearchParams(formData);
        params.append('only_counts', '1');

        fetch(`{{ route('know-your-area.data') }}?${params.toString()}`)
            .then(res => res.json())
            .then(response => {
                if (loader) loader.classList.add('d-none');
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
            }).catch(err => {
                if (loader) loader.classList.add('d-none');
                console.error('Counts fetch error:', err);
            });
    }

    function fetchLayerData(layerType) {
        const spinner = document.getElementById('spinner_' + layerType);
        if (spinner) spinner.style.display = 'inline-block';

        const formEl = document.getElementById('filterForm');
        const formData = formEl ? new FormData(formEl) : new FormData();
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
            }).catch(err => {
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
            if (feature.getGeometry().getType() === 'Point') return {
                visible: false
            };
            return style;
        });

        dataLayer.addListener('click', event => bindPopup(event.feature, event.latLng));
        layerDataCollections[layerType] = dataLayer;

        features.forEach(feature => {
            if (feature.geometry.type === 'Point' && layerType !== 'elephant_movement') {
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
        if (clusterer) clusterer.addMarkers(markers);
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
                </div>`;

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
                    infoWindow.open(overallMap);
                });
                shapes.push(shape);
            }
        });
        layerShapes.geofences = shapes;

        const cb = document.getElementById('check_geofences');
        if (cb && cb.checked) {
            layerShapes.geofences.forEach(s => s.setMap(overallMap));
        }
    }

    function showLayer(layerType) {
        if (layerDataCollections[layerType]) layerDataCollections[layerType].setMap(overallMap);
        if (layerMarkers[layerType]) {
            layerMarkers[layerType].forEach(m => m.setMap(overallMap));
            if (clusterer) clusterer.addMarkers(layerMarkers[layerType]);
        }
    }

    function hideLayer(layerType) {
        if (layerDataCollections[layerType]) layerDataCollections[layerType].setMap(null);
        if (layerMarkers[layerType]) {
            layerMarkers[layerType].forEach(m => m.setMap(null));
            if (clusterer) clusterer.removeMarkers(layerMarkers[layerType]);
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

        if (hasPoints && overallMap) {
            overallMap.fitBounds(bounds);
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
        infoWindow.open(overallMap);
    }

    /* =================================================================
       4. OVERALL TERRITORY CHART
    ================================================================= */
    function initOverallChart() {
        const ctx = document.getElementById('overall-summary-chart');
        if (!ctx) return;
        if (overallChart) overallChart.destroy();

        const textColor = getThemeColor('--text-muted', '#64748b');
        const gridColor = getThemeColor('--border-color', '#e2e8f0');
        const brandColor = getThemeColor('--sapphire-danger', '#ef4444');
        const cardBg = getThemeColor('--bg-card', '#ffffff');

        Chart.defaults.color = textColor;

        // Fetch data safely from global object
        let initialLabels = window.dashboardData.details?.criminalLabels || [];
        let initialValues = window.dashboardData.details?.criminalValues || [];

        if (!initialLabels || initialLabels.length === 0) {
            initialLabels = ['No Incidents Recorded'];
            initialValues = [0];
        }

        overallChart = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: initialLabels,
                datasets: [{
                    label: 'Total Incidents',
                    data: initialValues,
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
                        beginAtZero: true,
                        border: {
                            dash: [4, 4]
                        },
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            stepSize: 1,
                            precision: 0
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

        let newLabels = category === 'criminal' ? window.dashboardData.details?.criminalLabels : window
            .dashboardData.details?.eventsLabels;
        let newValues = category === 'criminal' ? window.dashboardData.details?.criminalValues : window
            .dashboardData.details?.eventsValues;

        if (!newLabels || newLabels.length === 0) {
            newLabels = ['No Incidents Recorded'];
            newValues = [0];
        }

        overallChart.data.labels = newLabels;
        overallChart.data.datasets[0].data = newValues;
        overallChart.data.datasets[0].backgroundColor = category === 'criminal' ? getThemeColor('--sapphire-danger',
            '#ef4444') : getThemeColor('--sapphire-success', '#10b981');
        overallChart.update();
    };

    /* =================================================================
       4. OVERALL DASHBOARD CHART
    ================================================================= */
    window.initOverallChart = function() {
        const ctx = document.getElementById('territory-chart');
        if (!ctx) return;
        if (overallChart) overallChart.destroy();

        overallChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: window.dashboardData.chartLabels || [],
                datasets: [{
                    label: 'Total Incidents',
                    data: window.dashboardData.chartValues || [],
                    backgroundColor: getThemeColor('--sapphire-primary', '#3b82f6'),
                    borderRadius: 5
                }]
            },
            options: {
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
                        border: {
                            dash: [4, 4]
                        },
                        grid: {
                            color: getThemeColor('--border-color', '#e2e8f0')
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    };

    window.updateOverallChart = function(category, btnElement) {
        if (btnElement) {
            document.querySelectorAll('#overall-chart-toggles .view-toggle-btn').forEach(b => b.classList.remove(
                'active'));
            btnElement.classList.add('active');
        }

        let newLabels = category === 'criminal' ? window.dashboardData.details?.criminalLabels : window
            .dashboardData.details
            ?.eventsLabels;
        let newValues = category === 'criminal' ? window.dashboardData.details?.criminalValues : window
            .dashboardData.details
            ?.eventsValues;

        if (overallChart) {
            overallChart.data.labels = newLabels || [];
            overallChart.data.datasets[0].data = newValues || [];
            overallChart.update();
        }
    };

    /* =================================================================
       5. NAVIGATION & ANALYTICAL VIEW
    ================================================================= */
    window.setViewMode = function(mode) {
        console.log('setViewMode triggered:', mode);
        window.viewMode = mode;
        const overallBtn = document.getElementById('view-overall');
        const analyticalBtn = document.getElementById('view-analytical');

        if (overallBtn) overallBtn.className = `view-toggle-btn ${mode === 'overall' ? 'active' : ''}`;
        if (analyticalBtn) analyticalBtn.className = `view-toggle-btn ${mode === 'analytical' ? 'active' : ''}`;

        const overallContainer = document.getElementById('overall-container');
        const analyticalContainer = document.getElementById('analytical-container');
        const kpiGrid = document.getElementById('main-kpi-grid');

        if (mode === 'overall') {
            if (overallContainer) overallContainer.classList.remove('d-none');
            if (analyticalContainer) analyticalContainer.classList.add('d-none');
            if (kpiGrid) kpiGrid.classList.remove('d-none');
            if (typeof overallMap !== 'undefined' && overallMap) {
                setTimeout(() => {
                    if (typeof overallMap.invalidateSize === 'function') overallMap.invalidateSize();
                }, 200);
            }
        } else {
            if (overallContainer) overallContainer.classList.add('d-none');
            if (analyticalContainer) analyticalContainer.classList.remove('d-none');
            if (kpiGrid) kpiGrid.classList.add('d-none');
            if (typeof window.buildAnalyticalUI === 'function') window.buildAnalyticalUI();
        }
    };

    window.navigateTo = function(cat) {
        if (!cat) return;
        window.activeMainTab = cat;
        const currentCat = config.categories.find(c => c.id === cat);
        if (currentCat) window.activeSubTab = currentCat.sub[0];
        if (typeof renderMainTabs === 'function') renderMainTabs();
        window.setViewMode('analytical');
    };

    function renderMainTabs() {
        const nav = document.getElementById('main-tabs-nav');
        if (!nav) return;
        nav.innerHTML = config.categories.map(c => `
            <a href="javascript:void(0)" onclick="window.activeMainTab='${c.id}'; window.activeSubTab=config.categories.find(x=>x.id==='${c.id}').sub[0]; renderMainTabs(); window.buildAnalyticalUI();"
               class="main-tab-link ${window.activeMainTab === c.id ? 'active' : ''}">
               <i class="bi ${c.icon}"></i> ${c.label}
            </a>
        `).join('');
    }

    window.buildAnalyticalUI = function() {
        const container = document.getElementById('sub-tabs-container');
        const currentCat = config.categories.find(c => c.id === activeMainTab);

        const header = document.getElementById('breakdown-header');
        const title = document.getElementById('breakdown-title');

        if (!currentCat || !container) return;

        if (header) header.classList.remove('d-none');
        if (title) title.innerText = `${currentCat.label} Breakdown`;

        container.innerHTML = currentCat.sub.map(s => `
            <div class="breakdown-tile ${window.activeSubTab === s ? 'active' : ''}" onclick="window.activeSubTab='${s}'; window.buildAnalyticalUI();">
                <div class="d-flex align-items-center gap-2 mb-2 text-muted">
                    <i class="bi ${config.icons[s] || 'bi-activity'}"></i>
                    <span class="text-uppercase fw-bold" style="font-size: 10px;">${config.labels[s] || s}</span>
                </div>
                <h2>${window.dashboardData.kpis[s] || 0}</h2>
            </div>
        `).join('');

        window.renderAnalyticalCharts();
    };

    window.renderAnalyticalCharts = function() {
        const grid = document.getElementById('charts-grid');
        if (!grid) return;
        grid.innerHTML = '';

        Object.values(activeCharts).forEach(c => {
            if (c) c.destroy();
        });
        activeCharts = {};

        const db = window.dashboardData.analytics || {};
        // Use activeSubTab which defines the current analytical perspective
        let chartsConfig = config.views[window.activeSubTab];
        console.log('Rendering charts for:', window.activeSubTab, chartsConfig);

        // Fallback for unconfigured views
        if (!chartsConfig) {
            chartsConfig = [{
                id: 'c1',
                title: "Overview Analysis",
                type: 'bar',
                data: () => ({
                    'A': 10,
                    'B': 20
                })
            }];
        }

        const textColor = getThemeColor('--text-muted', '#64748b');
        const gridColor = getThemeColor('--border-color', '#e2e8f0');
        const brandColor = getThemeColor('--sapphire-primary', '#3b82f6');
        const cardBg = getThemeColor('--bg-card', '#ffffff');

        chartsConfig.forEach(cfg => {
            grid.innerHTML += `
                <div class="col-lg-4">
                    <div class="dash-card p-4 h-100 d-flex flex-column">
                        <h6 class="fw-bold mb-4" style="color: var(--text-main); font-size: 0.9rem;">${cfg.title}</h6>
                        <div class="flex-grow-1 position-relative" style="min-height: 250px;">
                            <canvas id="${cfg.id}"></canvas>
                        </div>
                    </div>
                </div>`;
        });

        setTimeout(() => {
            chartsConfig.forEach(cfg => {
                const ctxEl = document.getElementById(cfg.id);
                if (!ctxEl) return;

                let raw = cfg.data(db) || {};

                // If the data is nested (like water availability [source][is_dry]), flatten it or handle appropriately
                // For now, simple flattening for display
                if (window.activeSubTab === 'water' && cfg.id === 'c1') {
                    let flattened = {};
                    Object.keys(raw).forEach(src => {
                        let total = 0;
                        Object.values(raw[src]).forEach(v => total += v);
                        flattened[src] = total;
                    });
                    raw = flattened;
                }

                const labels = Object.keys(raw).length ? Object.keys(raw) : ['No Data'];
                const values = Object.keys(raw).length ? Object.values(raw) : [0];

                activeCharts[cfg.id] = new Chart(ctxEl, {
                    type: cfg.type,
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: cfg.type === 'doughnut' ? [brandColor,
                                getThemeColor('--sapphire-warning', '#f59e0b'),
                                getThemeColor('--sapphire-success', '#10b981'),
                                getThemeColor('--sapphire-danger', '#ef4444')
                            ] : brandColor,
                            tension: 0.4,
                            fill: true,
                            borderRadius: (cfg.type === 'bar' ? 50 : 0)
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: (cfg.type === 'doughnut'),
                                position: 'bottom'
                            }
                        },
                        scales: cfg.type === 'doughnut' ? {} : {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                border: {
                                    dash: [4, 4]
                                },
                                grid: {
                                    color: gridColor
                                },
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
        }, 50);
    };
    // End of renderAnalyticalCharts

    // Helper to handle toggle clicks inside Analytical View
    window.updateSubChart = function(chartId, viewKey, toggleIndex, btnElement) {
        const toggleContainer = document.getElementById(`toggles-${chartId}`);
        const buttons = toggleContainer.querySelectorAll('button');
        buttons.forEach((b, i) => {
            b.className = i === toggleIndex ?
                'px-2 py-1 text-[10px] rounded border-0 transition-colors bg-white shadow-sm text-success fw-bold' :
                'px-2 py-1 text-[10px] rounded border-0 transition-colors bg-transparent text-muted';
        });

        const cfg = config.views[viewKey].find(c => c.id === chartId);
        if (cfg && activeCharts[chartId]) {
            const chart = activeCharts[chartId];
            const newData = cfg.generator(toggleIndex, window.dashboardData.analytics || {});

            chart.data = newData;
            if (cfg.options) chart.options = {
                ...chart.options,
                ...cfg.options
            };
            chart.update();

            // Update the stat pill when a toggle is clicked
            if (cfg.calcPill) {
                const pillEl = document.getElementById(`pill-${chartId}`);
                if (pillEl) pillEl.innerText = cfg.calcPill(newData, toggleIndex);
            }
        }
    };

    // 🔥 RESET ALL FILTERS
    window.resetFilters = function() {
        const url = new URL(window.location.origin + window.location.pathname);
        window.location.href = url.toString();
    };

    // 🔥 UPDATED: Consilidated state restoration
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);

        // 1. Capture the stored categories (if they exist in the URL)
        if (urlParams.has('cat')) {
            window.activeMainTab = urlParams.get('cat');
            const currentCat = config.categories.find(c => c.id === window.activeMainTab);
            if (currentCat) {
                window.activeSubTab = urlParams.get('sub') || currentCat.sub[0];
            }
        }

        console.log('Restored state on load:', {
            mode: urlParams.get('view'),
            cat: window.activeMainTab,
            sub: window.activeSubTab
        });

        // 2. Population navigation and state
        if (typeof renderMainTabs === 'function') renderMainTabs();

        // 3. Restore view settings (wins over any previous state)
        if (typeof window.setViewMode === 'function') {
            const mode = urlParams.get('view') || 'overall';
            window.setViewMode(mode);
        }
    });

    // 🔥 UPDATED: This function is triggered by the Sync button
    window.refreshData = function() {
        const loader = document.getElementById('loader');
        if (loader) loader.classList.remove('d-none');

        // Capture all filters
        const rangeId = document.getElementById('range_id')?.value || '';
        const beatId = document.getElementById('site_id')?.value || '';
        const dateFilter = document.getElementById('date_filter')?.value || '';

        const url = new URL(window.location.href);

        // Set filters
        if (rangeId) url.searchParams.set('range_id', rangeId);
        else url.searchParams.delete('range_id');
        if (beatId) url.searchParams.set('site_id', beatId);
        else url.searchParams.delete('site_id');
        if (dateFilter) url.searchParams.set('date_filter', dateFilter);
        else url.searchParams.delete('date_filter');

        // 🔥 CRITICAL: Persist current view and tabs
        url.searchParams.set('view', window.viewMode);
        url.searchParams.set('cat', window.activeMainTab);
        url.searchParams.set('sub', window.activeSubTab);

        console.log('Redirecting to:', url.toString());

        setTimeout(() => {
            window.location.href = url.toString();
        }, 300);
    };
</script>

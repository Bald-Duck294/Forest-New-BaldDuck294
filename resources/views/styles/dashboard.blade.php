<style>
    body {
        background-color: #f8fafc;
        font-family: 'Inter', sans-serif;
    }

    /* Original Tab Styling */
    .tab-btn.active {
        background-color: #10b981;
        color: white;
        border-color: #10b981;
        box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);
    }

    .tab-btn.inactive {
        background-color: transparent;
        color: #64748b;
        border-color: #e2e8f0;
    }

    /* Main Category Navigation Styling */
    .main-tab-link {
        color: #64748b;
        font-weight: 600;
        border-bottom: 3px solid transparent;
        padding-bottom: 12px;
        text-decoration: none;
        transition: 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .main-tab-link.active {
        color: #059669;
        border-bottom-color: #059669;
    }

    /* Breakdown Tiles - Restoration from Image 1 */
    .breakdown-tile {
        min-width: 220px;
        padding: 20px;
        border-radius: 15px;
        border: 2px solid #e2e8f0;
        background: white;
        transition: 0.2s;
        cursor: pointer;
    }

    .breakdown-tile.active {
        border-color: #10b981;
        background-color: white;
        box-shadow: 0 0 0 1px #10b981;
    }

    .breakdown-tile h2 {
        font-size: 32px;
        font-weight: 800;
        margin-top: 10px;
        color: #1e293b;
    }

    /* Loader leaf animation */
    @keyframes float {

        0%,
        100% {
            transform: translateY(0) rotate(-5deg);
        }

        50% {
            transform: translateY(-15px) rotate(10deg);
        }
    }

    .animate-float {
        animation: float 2s ease-in-out infinite;
    }

    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .map-scroll-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s;
        z-index: 1000;
    }

    .map-scroll-overlay.show {
        opacity: 1;
    }

    .kpi-card {
        border: 1px solid #f1f5f9 !important;
        /* Very subtle border */
        border-radius: 15px;
        transition: all 0.3s ease;
        background: #ffffff;
    }

    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05) !important;
        border-color: #e2e8f0 !important;
    }

    /* Icon Box refined for Light Mode visibility */
    .icon-box-refined {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.2rem;
    }

    /* Specific accent colors based on your reference images */
    .bg-blue-light {
        background-color: #eff6ff;
        color: #2563eb;
    }

    .bg-indigo-light {
        background-color: #eef2ff;
        color: #4f46e5;
    }

    .bg-rose-light {
        background-color: #fff1f2;
        color: #e11d48;
    }

    .bg-emerald-light {
        background-color: #ecfdf5;
        color: #059669;
    }

    .bg-orange-light {
        background-color: #fff7ed;
        color: #ea580c;
    }

    .bg-teal-light {
        background-color: #f0fdfa;
        color: #0d9488;
    }
</style>


{{-- Scripts --}}
<script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfBFN6L_HROTd-mS8QqUDRIqskkvHvFYk&libraries=visualization">
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
    let mapInstance = null;
    let overallChart = null;
    let activeCharts = {};

    const config = {
        categories: [{
                id: 'criminal',
                label: 'Criminal Activity',
                icon: 'bi-tree',
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
            mining: 'bi-minecart-loaded'
        }
    };

    // 2. INITIALIZATION
    document.addEventListener('DOMContentLoaded', () => {
        try {
            initMap();
            initOverallChart();
            renderMainTabs();
        } catch (e) {
            console.error("Dashboard initialization failed:", e);
        }
    });

    // 3. MAP LOGIC
    function initMap() {
        const mapContainer = document.getElementById('map');
        if (!mapContainer) return;

        mapInstance = L.map('map', {
            scrollWheelZoom: false
        }).setView([21.640, 79.560], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapInstance);

        if (Array.isArray(window.dashboardData.mapData)) {
            window.dashboardData.mapData.forEach(p => {
                if (p.latitude && p.longitude && !isNaN(p.latitude) && !isNaN(p.longitude)) {
                    L.circleMarker([parseFloat(p.latitude), parseFloat(p.longitude)], {
                        radius: 7,
                        color: '#fff',
                        weight: 2,
                        fillColor: '#f43f5e',
                        fillOpacity: 0.9
                    }).addTo(mapInstance).bindPopup(`<b>${p.report_type || 'Forest Alert'}</b>`);
                }
            });
        }
    }

    // 4. NAVIGATION & VIEW MODES
    function setViewMode(mode) {
        const overallBtn = document.getElementById('view-overall');
        const analyticalBtn = document.getElementById('view-analytical');

        if (overallBtn) overallBtn.className = `btn btn-sm px-3 tab-btn ${mode==='overall'?'active':'inactive'}`;
        if (analyticalBtn) analyticalBtn.className =
            `btn btn-sm px-3 tab-btn ${mode==='analytical'?'active':'inactive'}`;

        if (mode === 'overall') {
            document.getElementById('overall-container').classList.remove('d-none');
            document.getElementById('analytical-container').classList.add('d-none');
            document.getElementById('main-kpi-grid').classList.remove('d-none');
            if (mapInstance) setTimeout(() => mapInstance.invalidateSize(), 200);
        } else {
            document.getElementById('overall-container').classList.add('d-none');
            document.getElementById('analytical-container').classList.remove('d-none');
            document.getElementById('main-kpi-grid').classList.add('d-none');
            buildAnalyticalUI();
        }
    }

    function navigateTo(cat) {
        activeMainTab = cat;
        const currentCat = config.categories.find(c => c.id === cat);
        if (currentCat) {
            activeSubTab = currentCat.sub[0];
        }
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

    // 5. ANALYTICAL UI & DYNAMIC CHARTS
    function buildAnalyticalUI() {
        const container = document.getElementById('sub-tabs-container');
        const currentCat = config.categories.find(c => c.id === activeMainTab);
        if (!currentCat || !container) return;

        document.getElementById('breakdown-title').innerText = `${currentCat.label} Breakdown`;

        container.innerHTML = currentCat.sub.map(s => `
            <div class="breakdown-tile ${activeSubTab === s ? 'active' : ''}"
                 onclick="activeSubTab='${s}'; buildAnalyticalUI();">
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

        // DATA LOGIC: Select data sources based on active selection
        let currentLabels = activeMainTab === 'criminal' ? window.dashboardData.details.criminalLabels : window
            .dashboardData.details.eventsLabels;
        let currentValues = activeMainTab === 'criminal' ? window.dashboardData.details.criminalValues : window
            .dashboardData.details.eventsValues;

        let t1, t2, t3, chart3Type = 'line';

        // Custom Mapping for Titles and Layout
        switch (activeSubTab) {
            case 'felling':
                t1 = "Volume Analysis by Species";
                t2 = "Probable Reason of Felling";
                t3 = "Range Wise Felling Data";
                currentLabels = ['Sal', 'Saja', 'Sagaon', 'Beeja', 'Haldu', 'Dhawda'];
                chart3Type = 'bar';
                break;
            case 'transport':
                t1 = "Transport Vehicle Analytics";
                t2 = "Top 5 Smuggling Routes";
                t3 = "30-Day Transport Trend";
                currentLabels = ['Truck', 'Tractor', 'Tempo', 'Private', 'Others'];
                break;
            case 'wildlife':
                t1 = "Species Sighting Analysis";
                t2 = "Evidence Type Distribution";
                t3 = "Detection vs Evidence Strength";
                currentLabels = ['Sloth Bear', 'Leopard', 'Hyena', 'Jackal', 'Wild Boar', 'Sambar'];
                break;
            case 'water':
                t1 = "Water Availability Status";
                t2 = "Water Quality Index";
                t3 = "Quality vs Usage Pressure";
                currentLabels = ['Pond', 'Dam', 'Stream', 'Well', 'Other'];
                break;
            case 'compensation':
                t1 = "Claims Analysis by Category";
                t2 = "Distribution Overview";
                t3 = "Burden vs Efficiency";
                currentLabels = ['Crop', 'Cattle', 'H.Inj', 'H.Death', 'House'];
                break;
            default:
                t1 = "Breakdown Analysis";
                t2 = "Distribution Overview";
                t3 = "Trend Analysis";
        }

        const chartConfigs = [{
                title: t1,
                id: 'chart1',
                type: 'bar'
            },
            {
                title: t2,
                id: 'chart2',
                type: 'doughnut'
            },
            {
                title: t3,
                id: 'chart3',
                type: chart3Type
            }
        ];

        chartConfigs.forEach(cfg => {
            grid.innerHTML += `
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 15px;">
                        <h6 class="fw-bold mb-4" style="font-size: 14px;">${cfg.title}</h6>
                        <div style="height: 250px;"><canvas id="${cfg.id}"></canvas></div>
                    </div>
                </div>`;
        });

        setTimeout(() => {
            const common = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            };

            activeCharts.c1 = new Chart(document.getElementById('chart1'), {
                type: 'bar',
                data: {
                    labels: currentLabels,
                    datasets: [{
                        data: currentValues,
                        backgroundColor: '#10b981',
                        borderRadius: 4
                    }]
                },
                options: common
            });

            activeCharts.c2 = new Chart(document.getElementById('chart2'), {
                type: (activeSubTab === 'felling' || activeSubTab === 'compensation') ? 'pie' :
                    'doughnut',
                data: {
                    labels: currentLabels,
                    datasets: [{
                        data: currentValues,
                        backgroundColor: ['#10b981', '#f59e0b', '#3b82f6', '#f43f5e']
                    }]
                },
                options: {
                    ...common,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        }
                    }
                }
            });

            activeCharts.c3 = new Chart(document.getElementById('chart3'), {
                type: chart3Type,
                data: {
                    labels: currentLabels,
                    datasets: [{
                        data: currentValues,
                        borderColor: '#10b981',
                        tension: 0.4,
                        fill: true,
                        backgroundColor: 'rgba(16, 185, 129, 0.05)'
                    }]
                },
                options: {
                    ...common,
                    indexAxis: activeSubTab === 'felling' ? 'y' : 'x'
                }
            });
        }, 50);
    }

    function initOverallChart() {
        const ctx = document.getElementById('overall-summary-chart');
        if (!ctx) return;
        overallChart = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: window.dashboardData.chartLabels,
                datasets: [{
                    label: 'Total',
                    data: window.dashboardData.chartValues,
                    backgroundColor: '#f43f5e',
                    borderRadius: 5
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    function refreshData() {
        document.getElementById('loader').classList.remove('d-none');
        setTimeout(() => location.reload(), 800);
    }
</script>

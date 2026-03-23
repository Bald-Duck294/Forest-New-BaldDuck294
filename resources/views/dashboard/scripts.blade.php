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
        analytics: @json($analytics ?? [])
    };

    window.activeMainTab = 'criminal';
    window.activeSubTab = 'felling';
    window.viewMode = 'overall';

    const CHART_COLORS = {
        emerald: '#10b981',
        rose: '#f43f5e',
        amber: '#f59e0b',
        blue: '#3b82f6',
        indigo: '#6366f1',
        teal: '#14b8a6',
        slate: '#64748b'
    };
    const COLOR_PALETTE = [CHART_COLORS.emerald, CHART_COLORS.amber, CHART_COLORS.rose, CHART_COLORS.blue, CHART_COLORS
        .indigo
    ];

    let overallChart = null;
    let activeCharts = {};

    const getThemeColor = (varName, fallback) => {
        return getComputedStyle(document.documentElement).getPropertyValue(varName).trim() || fallback;
    };

    // =================================================================
    // 2. CONFIGURATIONS
    // =================================================================
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
            },
            {
                id: 'forestry',
                label: 'Plantations',
                icon: 'bi-tree',
                sub: ['plantations']
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
            inventory: 'Asset Inventory',
            plantations: 'Plantation Overview'
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
            inventory: 'bi-shield-check',
            plantations: 'bi-tree-fill'
        },
        views: {
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
            'criminal.transport': [{
                    id: 'trans-c1',
                    title: 'Transport Vehicle Analytics',
                    type: 'bar',
                    toggles: ['Quantity', 'Trips'],
                    generator: (idx, db) => {
                        let dataObj = idx === 0 ? db.transport?.vehicles_qty : db.transport?.vehicles_trips;
                        let lbls = Object.keys(dataObj || {});
                        let vals = Object.values(dataObj || {}).map(Number);
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
                        let godown = l.map(x => Number(db.storage?.species_godown?.[x] ?? 0));
                        let open = l.map(x => Number(db.storage?.species_open?.[x] ?? 0));
                        return {
                            labels: l,
                            datasets: [{
                                label: 'Godown',
                                data: godown,
                                backgroundColor: '#f59e0b'
                            }, {
                                label: 'Open Space',
                                data: open,
                                backgroundColor: '#10b981'
                            }]
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
                        if (data.labels[0] === 'No Data') return 'N/A';
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
                        let dates = new Set([...Object.keys(db.storage?.time_godown || {}), ...Object.keys(
                            db.storage?.time_open || {})]);
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
                            }, {
                                label: 'Open Space',
                                data: open,
                                backgroundColor: '#14b8a6'
                            }]
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
            'criminal.poaching': [{
                    id: 'poach-c1',
                    title: 'Demographics Analysis',
                    type: 'bar',
                    toggles: ['Age Class', 'Gender'],
                    generator: (idx, db) => {
                        let obj = idx === 0 ? db.poaching?.age : db.poaching?.gender;
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
                                label: idx === 0 ? 'By Age' : 'By Gender',
                                data: v,
                                backgroundColor: idx === 0 ? CHART_COLORS.amber : CHART_COLORS
                                    .indigo,
                                borderRadius: 4
                            }]
                        };
                    },
                    calcPill: (data) => `Total: ${data.datasets[0].data.reduce((a,b)=>a+b,0)}`
                },
                {
                    id: 'poach-c2',
                    title: 'Incident by Species',
                    type: 'pie',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.poaching?.species || {});
                        let v = Object.values(db.poaching?.species || {}).map(Number);
                        if (!l.length || v.reduce((a, b) => a + b, 0) === 0) return {
                            labels: ['No Data'],
                            datasets: [{
                                data: [1],
                                backgroundColor: [CHART_COLORS.slate]
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
                        if (data.labels[0] === 'No Data') return 'N/A';
                        let maxIdx = data.datasets[0].data.indexOf(Math.max(...data.datasets[0].data));
                        return `Top: ${data.labels[maxIdx] || 'N/A'}`;
                    }
                },
                {
                    id: 'poach-c3',
                    title: 'Poaching Timeline',
                    type: 'line',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.poaching?.trend || {});
                        let v = Object.values(db.poaching?.trend || {}).map(Number);
                        if (!l.length) return {
                            labels: ['No Data'],
                            datasets: [{
                                data: [0],
                                borderColor: CHART_COLORS.slate,
                                backgroundColor: 'rgba(100,116,139,0.1)'
                            }]
                        };
                        return {
                            labels: l,
                            datasets: [{
                                label: 'Incidents',
                                data: v,
                                borderColor: CHART_COLORS.rose,
                                backgroundColor: CHART_COLORS.rose + '22',
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

            // --- EVENTS ---
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
                        let wet = labels.map(l => obj[l]?.['No'] || 0);
                        let dry = labels.map(l => obj[l]?.['Yes'] || 0);
                        return {
                            labels,
                            datasets: [{
                                label: 'Has Water',
                                data: wet,
                                backgroundColor: CHART_COLORS.blue,
                                borderRadius: 4
                            }, {
                                label: 'Dry',
                                data: dry,
                                backgroundColor: CHART_COLORS.amber,
                                borderRadius: 4
                            }]
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

            // --- FIRE ---
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
                            }, {
                                label: 'Maintenance',
                                data: maintenance,
                                backgroundColor: CHART_COLORS.amber,
                                borderRadius: 4
                            }]
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
                    options: {
                        plugins: {
                            datalabels: {
                                display: false
                            }
                        }
                    },
                    calcPill: () => `Stable`
                }
            ],

            // --- PLANTATIONS ---
            'forestry.plantations': [{
                    id: 'plt-c1',
                    title: 'Current Workflow Phase',
                    type: 'doughnut',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.plantations?.phases || {});
                        let v = Object.values(db.plantations?.phases || {}).map(Number);
                        if (!l.length) return {
                            labels: ['No Data'],
                            datasets: [{
                                data: [1],
                                backgroundColor: [CHART_COLORS.slate]
                            }]
                        };
                        let formattedLabels = l.map(word => word.charAt(0).toUpperCase() + word.slice(1));
                        return {
                            labels: formattedLabels,
                            datasets: [{
                                data: v,
                                backgroundColor: COLOR_PALETTE,
                                borderWidth: 0,
                                hoverOffset: 4
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
                        },
                        cutout: '70%'
                    },
                    calcPill: (data) => {
                        let maxIdx = data.datasets[0].data.indexOf(Math.max(...data.datasets[0].data));
                        return `Most: ${data.labels[maxIdx] || 'N/A'}`;
                    }
                },
                {
                    id: 'plt-c2',
                    title: 'Planted Species (Qty)',
                    type: 'bar',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.plantations?.species || {});
                        let v = Object.values(db.plantations?.species || {}).map(Number);
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
                                label: 'Total Plants',
                                data: v,
                                backgroundColor: CHART_COLORS.emerald,
                                borderRadius: 4,
                                barPercentage: 0.6
                            }]
                        };
                    },
                    options: {
                        indexAxis: 'y'
                    },
                    calcPill: (data) => `Total: ${data.datasets[0].data.reduce((a,b)=>a+b,0)}`
                },
                {
                    id: 'plt-c3',
                    title: 'Fencing Status',
                    type: 'pie',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.plantations?.fenced || {});
                        let v = Object.values(db.plantations?.fenced || {}).map(Number);
                        if (!l.length || v.reduce((a, b) => a + b, 0) === 0) return {
                            labels: ['No Data'],
                            datasets: [{
                                data: [1],
                                backgroundColor: [CHART_COLORS.slate]
                            }]
                        };
                        return {
                            labels: l,
                            datasets: [{
                                data: v,
                                backgroundColor: [CHART_COLORS.indigo, CHART_COLORS.amber],
                                borderWidth: 0
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
                        let total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                        let fenced = data.datasets[0].data[0] || 0;
                        let perc = total > 0 ? Math.round((fenced / total) * 100) : 0;
                        return `Secured: ${perc}%`;
                    }
                },
                {
                    id: 'plt-c4',
                    title: 'Area by Soil Type (Ha)',
                    type: 'bar',
                    toggles: [],
                    generator: (idx, db) => {
                        let l = Object.keys(db.plantations?.soil_area || {});
                        let v = Object.values(db.plantations?.soil_area || {}).map(Number);
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
                                label: 'Area (Hectares)',
                                data: v,
                                backgroundColor: CHART_COLORS.amber,
                                borderRadius: 4,
                                barPercentage: 0.5
                            }]
                        };
                    },
                    calcPill: (data) => `Total Ha: ${data.datasets[0].data.reduce((a,b)=>a+b,0).toFixed(1)}`
                }
            ]
        }
    };


    // =================================================================
    // 3. GOOGLE MAPS LOGIC & DYNAMIC DATA OVERLAYS
    // =================================================================
    const mapLayerDefinitions = {
        criminal: [{
                id: 'felling',
                label: 'Illegal Felling',
                emoji: '🪓',
                color: '#f43f5e'
            },
            {
                id: 'transport',
                label: 'Timber Transport',
                emoji: '🚛',
                color: '#f59e0b'
            },
            {
                id: 'storage',
                label: 'Timber Storage',
                emoji: '📦',
                color: '#f97316'
            },
            {
                id: 'poaching',
                label: 'Poaching',
                emoji: '🐾',
                color: '#b91c1c'
            },
            {
                id: 'encroachment',
                label: 'Encroachment',
                emoji: '🚧',
                color: '#9333ea'
            },
            {
                id: 'mining',
                label: 'Illegal Mining',
                emoji: '⛏️',
                color: '#475569'
            },
            {
                id: 'jfmc',
                label: 'JFMC Felling',
                emoji: '📋',
                color: '#4f46e5'
            }
        ],
        events: [{
                id: 'sighting',
                label: 'Wild Animal Sighting',
                emoji: '🦌',
                color: '#059669'
            },
            {
                id: 'water_status',
                label: 'Water Source Status',
                emoji: '💧',
                color: '#3b82f6'
            },
            {
                id: 'compensation',
                label: 'Wildlife Compensation',
                emoji: '💰',
                color: '#0d9488'
            }
        ],
        fire: [{
            id: 'fire',
            label: 'Fire Alerts',
            emoji: '🔥',
            color: '#f97316'
        }]
    };

    let overallMap = null;
    let heatmapLayer = null;
    let overlayMapGroups = {};
    let isHeatmapActive = false;
    let infoWindow = null;

    function createGoogleEmojiIcon(emoji, hexColor) {
        const svg = `
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32">
                <circle cx="16" cy="16" r="14" fill="${hexColor}" stroke="white" stroke-width="2"/>
                <text x="16" y="21" font-size="14" font-family="sans-serif" text-anchor="middle" fill="white">${emoji}</text>
            </svg>`;
        return {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
            scaledSize: new google.maps.Size(32, 32),
            anchor: new google.maps.Point(16, 16)
        };
    }

    function initOverallMap() {
        const mapEl = document.getElementById('map');
        if (!mapEl) return;

        overallMap = new google.maps.Map(mapEl, {
            zoom: 11,
            center: {
                lat: 21.640,
                lng: 79.560
            },
            mapTypeId: 'terrain',
            scrollwheel: false,
            mapTypeControl: true,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
                mapTypeIds: ['roadmap', 'terrain', 'satellite', 'hybrid']
            },
            streetViewControl: false,
            fullscreenControl: true
        });

        infoWindow = new google.maps.InfoWindow();

        const dbMapData = window.dashboardData.mapData || [];
        const heatMapDataPoints = [];
        dbMapData.forEach(p => {
            if (p.latitude && p.longitude) {
                const lat = parseFloat(p.latitude);
                const lng = parseFloat(p.longitude);

                // Only push to heatmap if both are valid numbers
                if (!isNaN(lat) && !isNaN(lng)) {
                    heatMapDataPoints.push(new google.maps.LatLng(lat, lng));
                }
            }
        });
        try {
            heatmapLayer = new google.maps.visualization.HeatmapLayer({
                data: heatMapDataPoints,
                map: null,
                radius: 25,
                opacity: 0.8
            });
        } catch (e) {
            console.error(
                "Heatmap library missing. Add '&libraries=visualization' to your Google Maps API script tag.");
        }

        setupCtrlScroll(mapEl);
        initAllMapLayers(dbMapData);
        createLegend(dbMapData);
    }

    function setupCtrlScroll(mapEl) {
        if (!document.getElementById('map-scroll-msg')) {
            mapEl.parentElement.style.position = 'relative';
            mapEl.insertAdjacentHTML('afterend', `
                <div id="map-scroll-msg" class="d-none" style="position: absolute; inset: 0; background: rgba(15, 23, 42, 0.6); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 600; pointer-events: none; z-index: 1000; transition: opacity 0.3s;">
                    Use Ctrl + Scroll to zoom
                </div>
            `);
        }

        const scrollMsg = document.getElementById('map-scroll-msg');
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
                if (scrollMsg) {
                    scrollMsg.classList.remove('d-none');
                    scrollMsg.style.opacity = '1';
                    clearTimeout(scrollTimeout);
                    scrollTimeout = setTimeout(() => {
                        scrollMsg.style.opacity = '0';
                        setTimeout(() => scrollMsg.classList.add('d-none'), 300);
                    }, 1500);
                }
            }
        });
    }

    function initAllMapLayers(dbMapData) {
        const bounds = new google.maps.LatLngBounds();
        let hasPoints = false;

        Object.keys(mapLayerDefinitions).forEach(category => {
            const layers = mapLayerDefinitions[category];

            layers.forEach((layerDef) => {
                const layerRecords = dbMapData.filter(record =>
                    record.report_type.toLowerCase() === layerDef.id.toLowerCase() &&
                    record.latitude && record.longitude
                );

                let markerArray = [];

                layerRecords.forEach(record => {
                    const lat = parseFloat(record.latitude);
                    const lng = parseFloat(record.longitude);
                    const pos = new google.maps.LatLng(lat, lng);

                    const marker = new google.maps.Marker({
                        position: pos,
                        map: overallMap,
                        icon: createGoogleEmojiIcon(layerDef.emoji, layerDef.color),
                        title: layerDef.label
                    });

                    let popupDetails = '';
                    try {
                        const parsedData = typeof record.report_data === 'string' ? JSON.parse(
                            record.report_data) : record.report_data;

                        if (layerDef.id === 'felling' || layerDef.id === 'jfmc') {
                            popupDetails =
                                `Species: <b>${parsedData.species || 'N/A'}</b><br>Qty: ${parsedData.qty || 'N/A'} | Vol: ${parsedData.volume || 'N/A'} Cmt`;
                        } else if (layerDef.id === 'transport') {
                            popupDetails =
                                `Vehicle: <b>${parsedData.vehicle_type || 'N/A'}</b><br>Route: ${parsedData.route || 'N/A'}`;
                        } else if (layerDef.id === 'storage') {
                            popupDetails =
                                `Species: <b>${parsedData.species || 'N/A'}</b><br>Stored in: ${parsedData.storage_type || 'N/A'}`;
                        } else if (layerDef.id === 'sighting') {
                            popupDetails =
                                `Species: <b>${parsedData.species || 'N/A'}</b><br>Type: ${parsedData.sighting_type || 'N/A'} (${parsedData.num_animals || 1})`;
                        } else if (layerDef.id === 'water_status') {
                            const isDryStyle = parsedData.is_dry === 'Yes' ?
                                'color: #ef4444; font-weight: bold;' :
                                'color: #3b82f6; font-weight: bold;';
                            popupDetails =
                                `Source: <b>${parsedData.source_type || 'N/A'}</b><br>Is Dry: <span style="${isDryStyle}">${parsedData.is_dry || 'N/A'}</span>`;
                        } else if (layerDef.id === 'encroachment') {
                            popupDetails =
                                `Type: <b>${parsedData.encroachment_type || 'N/A'}</b><br>Area: ${parsedData.area_hectare || 'N/A'} Ha`;
                        } else if (layerDef.id === 'mining') {
                            popupDetails =
                                `Mineral: <b>${parsedData.mineral_type || 'N/A'}</b><br>Vol: ${parsedData.volume_cum || 'N/A'} CuM`;
                        } else if (layerDef.id === 'compensation') {
                            popupDetails =
                                `Type: <b>${parsedData.comp_type || 'N/A'}</b><br>Claim: ₹${parsedData.amount_claimed || 'N/A'}`;
                        } else if (layerDef.id === 'fire') {
                            popupDetails =
                                `Cause: <b>${parsedData.fire_cause || 'N/A'}</b><br>Burnt Area: ${parsedData.area_burnt || 'N/A'} Ha`;
                        } else if (layerDef.id === 'poaching') {
                            popupDetails =
                                `Species: <b>${parsedData.species || 'N/A'}</b><br>Gender: ${parsedData.gender || 'N/A'}`;
                        }
                    } catch (e) {
                        console.error("Could not parse JSON for record", record.id);
                    }

                    const infoContent = `<div style="font-family: 'Inter', sans-serif; min-width: 160px; padding: 5px;">
                        <h6 style="font-weight: 700; color: #1e293b; font-size: 0.9rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin-bottom: 6px;">${layerDef.emoji} ${layerDef.label}</h6>
                        <p style="font-size: 0.8rem; color: #475569; margin-bottom: 6px; line-height: 1.4;">${popupDetails}</p>
                        <p style="font-size: 0.65rem; color: #94a3b8; margin: 0;">ID: ${record.report_id || 'N/A'} | Loc: ${lat.toFixed(4)}, ${lng.toFixed(4)}</p>
                    </div>`;

                    marker.addListener('click', () => {
                        infoWindow.setContent(infoContent);
                        infoWindow.open(overallMap, marker);
                    });

                    markerArray.push(marker);
                    bounds.extend(pos);
                    hasPoints = true;
                });

                overlayMapGroups[layerDef.id] = markerArray;
            });
        });

        if (hasPoints) {
            overallMap.fitBounds(bounds);
        }
    }

    function createLegend(dbMapData) {
        const mapContainer = document.getElementById('map');
        if (!mapContainer || document.getElementById('dynamic-map-legend')) return;

        let legendItemsHtml = '';

        Object.values(mapLayerDefinitions).flat().forEach(layerDef => {
            const count = dbMapData.filter(record => record.report_type.toLowerCase() === layerDef.id
                .toLowerCase() && record.latitude).length;

            if (count > 0) {
                legendItemsHtml += `
                    <div onclick="toggleMapLayer('${layerDef.id}', this)" style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 6px; cursor: pointer; padding: 4px; border-radius: 4px; transition: background 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <div style="width: 12px; height: 12px; border-radius: 50%; background-color: ${layerDef.color}; flex-shrink: 0;"></div>
                            <span style="color: #475569; font-size: 0.75rem; font-weight: 500;" title="${layerDef.label}">${layerDef.label}</span>
                        </div>
                        <span style="font-size: 0.65rem; font-weight: 700; color: #94a3b8; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">${count}</span>
                    </div>`;
            }
        });

        const heatMapToggleHtml = `
            <div onclick="toggleHeatmap(this)" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; gap: 6px; cursor: pointer; font-size: 0.75rem; font-weight: bold; color: #ef4444;">
                <i class="bi bi-fire"></i> Toggle Heatmap
            </div>
        `;

        mapContainer.insertAdjacentHTML('beforeend', `
            <div id="dynamic-map-legend" style="position: absolute; bottom: 25px; left: 10px; background: rgba(255, 255, 255, 0.95); padding: 12px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; z-index: 1; min-width: 180px; backdrop-filter: blur(4px);">
                <h6 style="font-weight: 700; color: #1e293b; font-size: 0.8rem; margin-bottom: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px;">Map Filters</h6>
                <div style="max-height: 250px; overflow-y: auto;">
                    ${legendItemsHtml || '<span style="font-size: 0.75rem; color: #94a3b8;">No data points available</span>'}
                </div>
                ${heatMapToggleHtml}
            </div>
        `);
    }

    window.toggleMapLayer = function(layerId, element) {
        const markers = overlayMapGroups[layerId];
        if (!markers || markers.length === 0) return;

        const isVisible = markers[0].getMap() !== null;

        markers.forEach(m => {
            m.setMap(isVisible ? null : overallMap);
        });

        if (isVisible) {
            element.style.opacity = '0.5';
        } else {
            element.style.opacity = '1';
        }
    };

    window.toggleHeatmap = function(element) {
        if (!heatmapLayer) return;

        isHeatmapActive = !isHeatmapActive;

        if (isHeatmapActive) {
            heatmapLayer.setMap(overallMap);
            element.style.background = '#fee2e2';
            element.style.borderRadius = '4px';
            element.style.padding = '4px';

            Object.values(overlayMapGroups).flat().forEach(m => m.setMap(null));
        } else {
            heatmapLayer.setMap(null);
            element.style.background = 'transparent';

            Object.values(overlayMapGroups).flat().forEach(m => m.setMap(overallMap));

            const legendDiv = document.getElementById('dynamic-map-legend');
            Array.from(legendDiv.children[1].children).forEach(child => child.style.opacity = '1');
        }
    };

    // =================================================================
    // 4. OVERALL TERRITORY CHART
    // =================================================================
    function getTerritoryData(category) {
        const criminalKeys = ['felling', 'transport', 'storage', 'poaching', 'encroachment', 'mining'];
        const eventsKeys = ['wildlife', 'water', 'compensation'];

        const labelsMap = {
            felling: 'Felling',
            transport: 'Transport',
            storage: 'Storage',
            poaching: 'Poaching',
            encroachment: 'Encroach',
            mining: 'Mining',
            wildlife: 'Wild Sighting',
            water: 'Water Status',
            compensation: 'Compensation'
        };

        const keys = category === 'criminal' ? criminalKeys : eventsKeys;
        const kpis = window.dashboardData.kpis || {};

        return {
            labels: keys.map(k => labelsMap[k]),
            data: keys.map(k => kpis[k] || 0),
            color: category === 'criminal' ? '#f43f5e' : '#f59e0b'
        };
    }

    function initOverallChart() {
        const ctx = document.getElementById('overall-summary-chart');
        if (!ctx) return;
        if (overallChart) overallChart.destroy();

        const initialData = getTerritoryData('criminal');

        overallChart = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: initialData.labels,
                datasets: [{
                    data: initialData.data,
                    backgroundColor: initialData.color,
                    borderRadius: 4,
                    barPercentage: 0.6
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'right',
                        color: getThemeColor('--text-main', '#333'),
                        font: {
                            weight: 'bold',
                            family: "'Inter', sans-serif"
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            display: false
                        },
                        suggestedMax: Math.max(...initialData.data) * 1.15
                    },
                    y: {
                        grid: {
                            color: getThemeColor('--border-color', '#e2e8f0')
                        },
                        border: {
                            dash: [4, 4],
                            display: false
                        },
                        ticks: {
                            color: getThemeColor('--text-muted', '#64748b'),
                            font: {
                                family: "'Inter', sans-serif"
                            }
                        }
                    }
                },
                layout: {
                    padding: {
                        right: 40
                    }
                }
            }
        });
    }

    function updateOverallChart(category, btnElement) {
        if (btnElement) {
            const container = btnElement.parentElement;
            container.querySelectorAll('button').forEach(b => {
                b.className = 'btn btn-sm w-50 text-muted bg-transparent border-0';
            });
            btnElement.className = 'btn btn-sm w-50 active text-success fw-bold bg-white shadow-sm border-0';
            btnElement.style.borderRadius = '8px';
        }

        const newData = getTerritoryData(category);

        if (overallChart) {
            overallChart.data.labels = newData.labels;
            overallChart.data.datasets[0].data = newData.data;
            overallChart.data.datasets[0].backgroundColor = newData.color;
            overallChart.options.scales.x.suggestedMax = Math.max(...newData.data) * 1.15;
            overallChart.update();
        }
    }

    // =================================================================
    // 5. NAVIGATION & ANALYTICAL VIEW
    // =================================================================
    function setViewMode(mode) {
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
        } else {
            if (overallContainer) overallContainer.classList.add('d-none');
            if (analyticalContainer) analyticalContainer.classList.remove('d-none');
            if (kpiGrid) kpiGrid.classList.add('d-none');
            buildAnalyticalUI();
        }
    }

    window.navigateTo = function(cat) {
        if (!cat) return;
        window.activeMainTab = cat;
        const currentCat = config.categories.find(c => c.id === cat);
        if (currentCat) window.activeSubTab = currentCat.sub[0];
        renderMainTabs();
        setViewMode('analytical');
    };

    function renderMainTabs() {
        const nav = document.getElementById('main-tabs-nav');
        if (!nav) return;
        nav.innerHTML = config.categories.map(c => `
            <a href="javascript:void(0)" onclick="window.activeMainTab='${c.id}'; window.activeSubTab=config.categories.find(x=>x.id==='${c.id}').sub[0]; renderMainTabs(); buildAnalyticalUI();"
               class="main-tab-link ${window.activeMainTab === c.id ? 'active' : ''}">
               <i class="bi ${c.icon}"></i> ${c.label}
            </a>
        `).join('');
    }

    window.buildAnalyticalUI = function() {
        const container = document.getElementById('sub-tabs-container');
        const currentCat = config.categories.find(c => c.id === window.activeMainTab);

        const header = document.getElementById('breakdown-header');
        const title = document.getElementById('breakdown-title');

        if (!currentCat || !container) return;

        if (header) header.classList.remove('d-none');
        if (title) title.innerText = `${currentCat.label} Breakdown`;

        container.innerHTML = currentCat.sub.map(s => `
            <div class="breakdown-tile ${window.activeSubTab === s ? 'active' : ''}" onclick="window.activeSubTab='${s}'; buildAnalyticalUI();">
                <div class="d-flex align-items-center gap-2 mb-2 text-muted">
                    <i class="bi ${config.icons[s] || 'bi-activity'}"></i>
                    <span class="text-uppercase fw-bold" style="font-size: 10px;">${config.labels[s] || s}</span>
                </div>
                <h2>${window.dashboardData.kpis[s] || 0}</h2>
            </div>
        `).join('');

        renderAnalyticalCharts();
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
        const viewKey = window.activeMainTab + '.' + window.activeSubTab;
        let chartsConfig = config.views[viewKey];

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
            let togglesHtml = '';
            if (cfg.toggles && cfg.toggles.length > 0) {
                togglesHtml = `<div id="toggles-${cfg.id}" class="d-flex gap-2 mb-3">`;
                cfg.toggles.forEach((t, idx) => {
                    let activeClass = idx === 0 ? 'bg-white shadow-sm text-success fw-bold' :
                        'bg-transparent text-muted';
                    togglesHtml +=
                        `<button onclick="updateSubChart('${cfg.id}', '${viewKey}', ${idx}, this)" class="px-2 py-1 text-[10px] rounded border-0 transition-colors ${activeClass}">${t}</button>`;
                });
                togglesHtml += `</div>`;
            }

            let pillHtml = cfg.calcPill ?
                `<span id="pill-${cfg.id}" class="badge" style="background-color: var(--bg-body); color: var(--text-muted); font-size: 0.75rem;">Loading...</span>` :
                '';

            grid.innerHTML += `
                <div class="col-lg-4">
                    <div class="dash-card p-4 h-100 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="fw-bold m-0" style="color: var(--text-main); font-size: 0.9rem;">${cfg.title}</h6>
                            ${pillHtml}
                        </div>
                        ${togglesHtml}
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

                let raw = cfg.generator ? cfg.generator(0, db) : (cfg.data ? cfg.data(db) : {});

                if (window.activeSubTab === 'water' && cfg.id === 'wat-c1' && !raw.datasets) {
                    let flattened = {};
                    Object.keys(raw).forEach(src => {
                        let total = 0;
                        Object.values(raw[src]).forEach(v => total += v);
                        flattened[src] = total;
                    });
                    raw = {
                        labels: Object.keys(flattened),
                        datasets: [{
                            data: Object.values(flattened)
                        }]
                    };
                }

                const chartData = raw.datasets ? raw : {
                    labels: Object.keys(raw).length ? Object.keys(raw) : ['No Data'],
                    datasets: [{
                        data: Object.keys(raw).length ? Object.values(raw) : [0],
                        backgroundColor: cfg.type === 'doughnut' || cfg.type === 'pie' ?
                            [
                                brandColor, getThemeColor('--sapphire-warning',
                                    '#f59e0b'),
                                getThemeColor('--sapphire-success', '#10b981'),
                                getThemeColor('--sapphire-danger', '#ef4444')
                            ] : brandColor,
                        tension: 0.4,
                        fill: true,
                        borderRadius: (cfg.type === 'bar' ? 4 : 0)
                    }]
                };

                activeCharts[cfg.id] = new Chart(ctxEl, {
                    type: cfg.type,
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: (cfg.type === 'doughnut' || cfg.type === 'pie'),
                                position: 'bottom'
                            }
                        },
                        scales: (cfg.type === 'doughnut' || cfg.type === 'pie') ? {} : {
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
                        },
                        ...(cfg.options || {})
                    }
                });

                if (cfg.calcPill) {
                    const pillEl = document.getElementById(`pill-${cfg.id}`);
                    if (pillEl) pillEl.innerText = cfg.calcPill(chartData, 0);
                }
            });
        }, 50);
    };

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

            if (cfg.calcPill) {
                const pillEl = document.getElementById(`pill-${chartId}`);
                if (pillEl) pillEl.innerText = cfg.calcPill(newData, toggleIndex);
            }
        }
    };

    // =================================================================
    // 6. INITIALIZATION & REFRESH LOGIC
    // =================================================================
    window.resetFilters = function() {
        const url = new URL(window.location.origin + window.location.pathname);
        window.location.href = url.toString();
    };

    window.refreshData = function() {
        const loader = document.getElementById('loader');
        if (loader) loader.classList.remove('d-none');

        const rangeId = document.getElementById('range_id')?.value || '';
        const beatId = document.getElementById('site_id')?.value || '';
        const dateFilter = document.getElementById('date_filter')?.value || '';

        const url = new URL(window.location.href);

        if (rangeId) url.searchParams.set('range_id', rangeId);
        else url.searchParams.delete('range_id');
        if (beatId) url.searchParams.set('site_id', beatId);
        else url.searchParams.delete('site_id');
        if (dateFilter) url.searchParams.set('date_filter', dateFilter);
        else url.searchParams.delete('date_filter');

        url.searchParams.set('view', window.viewMode);
        url.searchParams.set('cat', window.activeMainTab);
        url.searchParams.set('sub', window.activeSubTab);

        setTimeout(() => {
            window.location.href = url.toString();
        }, 300);
    };

    document.addEventListener('DOMContentLoaded', () => {
        // FIXED: Added 'URL' before SearchParams
        const urlParams = new URLSearchParams(window.location.search);

        if (urlParams.has('cat')) {
            window.activeMainTab = urlParams.get('cat');
            const currentCat = config.categories.find(c => c.id === window.activeMainTab);
            if (currentCat) window.activeSubTab = urlParams.get('sub') || currentCat.sub[0];
        }

        renderMainTabs();

        if (urlParams.has('view')) {
            setViewMode(urlParams.get('view'));
        }

        // Initialize Charts and Map
        initOverallChart();
        initOverallMap();

        window.addEventListener('themeChanged', () => {
            initOverallChart();
            if (document.getElementById('analytical-container') && !document.getElementById(
                    'analytical-container').classList.contains('d-none')) {
                window.renderAnalyticalCharts();
            }
        });
    });
</script>

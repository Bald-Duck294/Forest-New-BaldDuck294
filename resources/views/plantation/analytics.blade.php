@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    $user = session('user');
    // dd($user);
@endphp
@extends('layouts.app')

@section('title', 'Plantation Analytics')

@section('content')

    <style>
        /* =========================================
                                           LOCAL COMPONENT STYLES
                                           (Hooked to Global Sapphire Variables)
                                        ========================================= */

        /* Custom Filter Buttons */
        .custom-filter-btn {
            background-color: var(--bg-card);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .custom-filter-btn:hover {
            background-color: var(--table-hover);
        }

        .btn-export-gold {
            background-color: #F59E0B;
            /* Vibrant Amber/Gold */
            color: #111827 !important;
            /* Always dark text for contrast */
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-export-gold:hover {
            background-color: #D97706;
            transform: translateY(-1px);
        }

        /* Soft Badges */
        .badge-soft {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
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

        .badge-soft-primary {
            background: rgba(59, 130, 246, 0.15);
            color: var(--sapphire-primary);
        }

        /* Custom Progress Bars */
        .progress-group {
            margin-bottom: 1.25rem;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        .progress-label span:last-child {
            color: var(--text-main);
        }

        .progress-track {
            width: 100%;
            height: 8px;
            background: var(--table-hover);
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        /* Chart Containers */
        .chart-container {
            position: relative;
            height: 320px;
            width: 100%;
            margin-top: 1rem;
        }

        .chart-container-small {
            position: relative;
            height: 230px;
            width: 100%;
            display: flex;
            justify-content: center;
        }
    </style>

    <div class="container-fluid py-4">

        {{-- HEADER --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <a href="{{ route('plantation.dashboard') }}" class="text-decoration-none">
                <div>
                    <h3 class="fw-bold mb-1" style="color: var(--text-main);">Plantation Analytics</h3>
                    <p class="mb-0" style="color: var(--text-muted); font-size: 0.9rem;">
                        Real-time monitoring of crop performance and resource distribution.
                    </p>
                </div>
            </a>

            <div class="d-flex gap-2">
                <button class="custom-filter-btn shadow-sm">
                    <i class="bi bi-calendar"></i> Last 6 Months
                </button>
                <button class="btn-export-gold shadow-sm">
                    <i class="bi bi-download"></i> Export Report
                </button>
            </div>
        </div>

        {{-- TOP GRID (CHARTS) --}}
        <div class="row g-4 mb-4">

            {{-- YIELD CHART --}}
            <div class="col-lg-8">
                <div class="dash-card h-100 p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h5 class="fw-bold mb-1" style="color: var(--text-main);">Yield Forecast vs Actual</h5>
                            <small style="color: var(--text-muted);">Comparison of projected growth vs measured
                                output</small>
                        </div>
                        <div class="text-end">
                            <h4 class="fw-bold mb-1" style="color: var(--sapphire-warning);">420 Tons</h4>
                            <small style="color: var(--sapphire-success); font-weight: 600;">+12.5% vs target</small>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="yieldChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- HEALTH DISTRIBUTION --}}
            <div class="col-lg-4">
                <div class="dash-card h-100 p-4">
                    <h5 class="fw-bold mb-4 text-center" style="color: var(--text-main);">Crop Health Distribution</h5>

                    <div class="chart-container-small">
                        <canvas id="healthChart"></canvas>
                    </div>

                    <div class="mt-4 px-3" id="health-legend">
                    </div>
                </div>
            </div>

        </div>

        {{-- BOTTOM GRID --}}
        <div class="row g-4">

            {{-- RESOURCE UTILIZATION --}}
            <div class="col-lg-4">
                <div class="dash-card h-100 p-4">
                    <h5 class="fw-bold mb-4" style="color: var(--text-main);">Resource Utilization</h5>

                    <div class="progress-group">
                        <div class="progress-label">
                            <span style="color: var(--text-main);">Water Irrigation</span>
                            <span>{{ $waterUsage ?? 72 }}%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill"
                                style="width: {{ $waterUsage ?? 72 }}%; background: var(--text-muted);"></div>
                        </div>
                    </div>

                    <div class="progress-group">
                        <div class="progress-label">
                            <span style="color: var(--text-main);">Fertilizer Stock</span>
                            <span>{{ $fertilizerStock ?? 66 }}%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill"
                                style="width: {{ $fertilizerStock ?? 66 }}%; background: var(--sapphire-success);"></div>
                        </div>
                    </div>

                    <div class="progress-group mb-0">
                        <div class="progress-label">
                            <span style="color: var(--text-main);">Labor Efficiency</span>
                            <span>{{ $laborEfficiency ?? 94 }}%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill"
                                style="width: {{ $laborEfficiency ?? 94 }}%; background: var(--sapphire-warning);"></div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- TOP PLANTATIONS TABLE --}}
            <div class="col-lg-8">
                <div class="dash-card p-0 overflow-hidden h-100">
                    <div class="d-flex justify-content-between align-items-center p-4 pb-3"
                        style="border-bottom: 1px solid var(--border-color);">
                        <h5 class="fw-bold mb-0" style="color: var(--text-main);">Top Performing Plantations</h5>
                        <a href="/plantation/dashboard" class="text-decoration-none fw-semibold"
                            style="color: var(--sapphire-warning); font-size: 0.9rem;">View All &rarr;</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-borderless dash-table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-4">Plantation</th>
                                    <th>Crop</th>
                                    <th class="text-center">Efficiency</th>
                                    <th class="text-end pe-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topPlantations ?? [] as $pln)
                                    <tr>
                                        <td class="ps-4 fw-semibold" style="color: var(--text-main);">
                                            {{ $pln->name ?? 'Unknown' }}
                                        </td>
                                        <td>
                                            {{ $pln->plant_species ?? 'N/A' }}
                                        </td>
                                        <td class="text-center fw-bold" style="color: var(--sapphire-success);">
                                            {{ $pln->observations_count ?? 0 }}
                                        </td>
                                        <td class="text-end pe-4">
                                            <span class="badge-soft badge-soft-success">Active</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="ps-4 fw-semibold" style="color: var(--text-main);">Plantation 1</td>
                                        <td>Neem</td>
                                        <td class="text-center fw-bold" style="color: var(--sapphire-success);">1</td>
                                        <td class="text-end pe-4"><span class="badge-soft badge-soft-success">Active</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4 fw-semibold" style="color: var(--text-main);">dfhusdn 2</td>
                                        <td>N/A</td>
                                        <td class="text-center fw-bold" style="color: var(--sapphire-success);">0</td>
                                        <td class="text-end pe-4"><span class="badge-soft badge-soft-success">Active</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4 fw-semibold" style="color: var(--text-main);">Kartik Kanzode</td>
                                        <td>N/A</td>
                                        <td class="text-center fw-bold" style="color: var(--sapphire-success);">0</td>
                                        <td class="text-end pe-4"><span class="badge-soft badge-soft-success">Active</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    </div>

    {{-- PHP Arrays to JS --}}
    @php
        $monthlyData = $monthlyObservations ?? [0.96, 0.98, 1.05, 1.04, 1.01, 1.02, 1.06, 1.02, 1.05, 1.07, 1.08, 1.08];
        $healthData = [$optimal ?? 75, $actionRequired ?? 15, $critical ?? 10];
    @endphp

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const obsData = @json($monthlyData);
            const healthData = @json($healthData);

            let yieldChartInstance = null;
            let healthChartInstance = null;

            function renderCharts() {
                // Fetch Global Sapphire Theme Variables
                const rootStyle = getComputedStyle(document.documentElement);
                const textColor = rootStyle.getPropertyValue('--text-muted').trim() || '#64748B';
                const gridColor = rootStyle.getPropertyValue('--chart-grid').trim() || 'rgba(255,255,255,0.05)';

                // Map Colors
                const colorPrimary = rootStyle.getPropertyValue('--sapphire-primary').trim() ||
                    '#3B82F6'; // Vibrant Blue
                const colorSuccess = rootStyle.getPropertyValue('--sapphire-success').trim() ||
                    '#10B981'; // Emerald
                const colorWarning = rootStyle.getPropertyValue('--sapphire-warning').trim() || '#F59E0B'; // Amber
                const colorDanger = rootStyle.getPropertyValue('--sapphire-danger').trim() || '#EF4444'; // Red

                Chart.defaults.font.family = "'Inter', sans-serif";
                Chart.defaults.color = textColor;

                // Destroy old instances
                if (yieldChartInstance) yieldChartInstance.destroy();
                if (healthChartInstance) healthChartInstance.destroy();

                /* --- 1. YIELD LINE CHART (Glowing Blue Gradient) --- */
                const ctxYield = document.getElementById('yieldChart');
                if (ctxYield) {
                    const ctx = ctxYield.getContext("2d");

                    // Create a dynamic gradient based on the primary color
                    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                    gradient.addColorStop(0, "rgba(59, 130, 246, 0.4)");
                    gradient.addColorStop(1, "rgba(59, 130, 246, 0.0)");

                    yieldChartInstance = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct',
                                'Nov', 'Dec'
                            ],
                            datasets: [{
                                label: 'Observations',
                                data: obsData,
                                borderColor: colorPrimary,
                                backgroundColor: gradient,
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4, // Smooth curvy line
                                pointRadius: 0,
                                pointHoverRadius: 6,
                                pointBackgroundColor: colorPrimary,
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
                                    min: 0.94,
                                    max: 1.08,
                                    grid: {
                                        color: gridColor,
                                        drawBorder: false
                                    },
                                }
                            }
                        }
                    });
                }

                /* --- 2. HEALTH DONUT CHART --- */
                const ctxHealth = document.getElementById('healthChart');
                if (ctxHealth) {
                    healthChartInstance = new Chart(ctxHealth, {
                        type: 'doughnut',
                        data: {
                            labels: ['Optimal', 'Action Required', 'Critical'],
                            datasets: [{
                                data: healthData,
                                backgroundColor: [colorSuccess, colorWarning, colorDanger],
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
                                    display: false
                                }
                            }
                        }
                    });
                }

                // Sync Donut Legend HTML
                const legendEl = document.getElementById('health-legend');
                if (legendEl) {
                    legendEl.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span style="color: var(--text-muted); font-size: 0.85rem;"><i class="bi bi-circle-fill me-2" style="color: ${colorSuccess}; font-size: 0.65rem;"></i> Optimal</span>
                        <strong style="color: var(--text-main); font-size: 0.85rem;">${healthData[0]}%</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span style="color: var(--text-muted); font-size: 0.85rem;"><i class="bi bi-circle-fill me-2" style="color: ${colorWarning}; font-size: 0.65rem;"></i> Action Required</span>
                        <strong style="color: var(--text-main); font-size: 0.85rem;">${healthData[1]}%</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="color: var(--text-muted); font-size: 0.85rem;"><i class="bi bi-circle-fill me-2" style="color: ${colorDanger}; font-size: 0.65rem;"></i> Critical</span>
                        <strong style="color: var(--text-main); font-size: 0.85rem;">${healthData[2]}%</strong>
                    </div>
                `;
                }
            }

            // Render on initial load
            renderCharts();

            // Re-render instantly when the theme is toggled
            window.addEventListener('themeChanged', function() {
                setTimeout(renderCharts, 50);
            });
        });
    </script>

@endsection

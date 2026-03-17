@php
    $hideBackground = true;
    $hideGlobalFilters = true;
@endphp

@extends('layouts.app')

@section('title', 'Attendance Dashboard')

@section('content')

    <style>
        /* Chart Containers */
        .chart-container {
            position: relative;
            height: 320px;
            width: 100%;
        }

        .chart-container-small {
            position: relative;
            height: 260px;
            width: 100%;
        }

        /* Custom Filter Button Styles */
        .custom-date-input {
            background-color: var(--bg-card, #fff);
            color: var(--text-main, #000);
            border: 1px solid var(--border-color, #dee2e6);
            border-radius: 8px;
            padding: 8px 12px;
            outline: none;
            transition: all 0.2s ease;
        }

        /* Make calendar icon white in dark mode */
        html[data-bs-theme="dark"] .custom-date-input {
            color-scheme: dark;
        }

        html[data-bs-theme="light"] .custom-date-input {
            color-scheme: light;
        }

        .custom-filter-btn {
            background-color: var(--bg-card, #fff);
            color: var(--text-main, #000);
            border: 1px solid var(--border-color, #dee2e6);
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .custom-filter-btn:hover {
            background-color: var(--table-hover, #f8f9fa);
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
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-export-gold:hover {
            background-color: #D97706;
            transform: translateY(-1px);
        }

        /* Fix for badge colors in KPI boxes to match the image */
        .badge-soft-blue {
            background: rgba(59, 130, 246, 0.15);
            color: #3B82F6;
        }

        .badge-soft-amber {
            background: rgba(245, 158, 11, 0.15);
            color: #F59E0B;
        }

        .badge-soft-red {
            background: rgba(239, 68, 68, 0.15);
            color: #EF4444;
        }
    </style>

    <div class="container-fluid py-4">

        {{-- KPI CARDS ROW --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-lg-3 col-md-6">
                <div class="kpi-box dash-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="kpi-icon badge-soft-blue">
                            <i class="bi bi-person-check-fill"></i>
                        </div>
                        <div class="kpi-info">
                            <h4>Present</h4>
                            <h2>{{ $presentToday ?? 152 }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-3 col-md-6">
                <div class="kpi-box dash-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="kpi-icon badge-soft-amber">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="kpi-info">
                            <h4>Late</h4>
                            <h2>{{ $lateToday ?? 12 }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-3 col-md-6">
                <div class="kpi-box dash-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="kpi-icon badge-soft-blue">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <div class="kpi-info">
                            <h4>Requests</h4>
                            <h2>{{ $pendingRequests ?? 5 }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-3 col-md-6">
                <div class="kpi-box dash-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="kpi-icon badge-soft-red">
                            <i class="bi bi-building-x"></i>
                        </div>
                        <div class="kpi-info">
                            <h4>Absent</h4>
                            <h2>{{ $absentToday ?? 3 }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- FILTER BAR --}}
        <div class="d-flex justify-content-end mb-4">
            <form method="GET" class="d-flex align-items-center gap-2">

                <input type="date" name="date" value="{{ request('date') }}" class="custom-date-input shadow-sm">

                <button type="submit" class="custom-filter-btn shadow-sm">
                    <i class="bi bi-calendar"></i> Filter Date
                </button>

                <a href="{{ route('attendance.export', ['date' => request('date')]) }}" class="btn-export-gold shadow-sm">
                    <i class="bi bi-download"></i> Export Data
                </a>

            </form>
        </div>

        {{-- CHARTS ROW --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="dash-card h-100 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="fw-bold mb-1" style="color: var(--text-main);">Weekly Attendance Trends</h5>
                            <small style="color: var(--text-muted);">Stacked pill chart representation</small>
                        </div>
                        <select class="custom-date-input shadow-sm">
                            <option>Total Attendance</option>
                            <option>By Site</option>
                        </select>
                    </div>
                    <div class="chart-container">
                        <canvas id="weeklyChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="dash-card h-100 p-4">
                    <h5 class="fw-bold mb-4 text-center" style="color: var(--text-main);">Status Distribution</h5>

                    <div class="chart-container-small">
                        <canvas id="statusChart"></canvas>
                    </div>

                    <div class="mt-4 px-3" id="donut-legend">
                    </div>
                </div>
            </div>
        </div>

        {{-- RECENT CHECKINS TABLE --}}
        <div class="dash-card p-0 overflow-hidden">
            <div class="p-4 pb-3" style="border-bottom: 1px solid var(--border-color);">
                <h5 class="fw-bold mb-0" style="color: var(--text-main);">Recent Check-ins</h5>
            </div>

            <div class="table-responsive">
                <table class="table dash-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4">User</th>
                            <th>Time</th>
                            <th>Site</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentCheckins ?? [] as $check)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ asset($check->profile_pic ?? 'images/user-placeholder.png') }}"
                                            width="36" height="36" class="rounded-circle shadow-sm"
                                            style="object-fit: cover; border: 2px solid var(--border-color);">
                                        <span class="fw-semibold">{{ $check->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $check->time }}</td>
                                <td>{{ $check->site }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white shadow-sm"
                                            style="width: 36px; height: 36px;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <span class="fw-semibold">Ramesh Singh</span>
                                    </div>
                                </td>
                                <td>09:02 AM</td>
                                <td>North Zone</td>
                            </tr>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white shadow-sm"
                                            style="width: 36px; height: 36px;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <span class="fw-semibold">Amit Kumar</span>
                                    </div>
                                </td>
                                <td>09:15 AM</td>
                                <td>South Gate</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @php
        // Fallback array for chart if vars are missing
        $chartData = [
            'labels' => $weeklyLabels ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'present' => $weeklyPresent ?? [4000, 3500, 3000, 2500, 4200, 2500, 3200],
            'absent' => $weeklyAbsent ?? [1000, 3000, 4000, 3000, 2800, 3000, 2200],
            'status' => [$presentToday ?? 152, $lateToday ?? 12, $absentToday ?? 3],
        ];
    @endphp

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const chartData = @json($chartData);
            let weeklyChartInstance = null;
            let statusChartInstance = null;

            function renderCharts() {
                // Check Bootstrap Theme attribute
                const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';

                // Get root styles for grid lines and borders
                const rootStyle = getComputedStyle(document.body);
                const textColor = rootStyle.getPropertyValue('--text-muted').trim() || (isDark ? '#94A3B8' :
                    '#64748B');
                const gridColor = rootStyle.getPropertyValue('--chart-grid').trim() || (isDark ?
                    'rgba(255,255,255,0.05)' : '#E2E8F0');
                const cardBg = rootStyle.getPropertyValue('--bg-card').trim() || (isDark ? '#111827' : '#FFFFFF');

                // Exact Graph Colors based on Light/Dark Mode (Matching the reference images)
                const colorPresent = isDark ? '#3B82F6' : '#1E3A8A'; // Vibrant Blue vs Deep Navy
                const colorLate = isDark ? '#93C5FD' : '#3B82F6'; // Soft Ice Blue vs Vibrant Blue
                const colorAbsent = '#EF4444'; // Red is constant

                Chart.defaults.font.family = "'Inter', sans-serif";
                Chart.defaults.color = textColor;

                if (weeklyChartInstance) weeklyChartInstance.destroy();
                if (statusChartInstance) statusChartInstance.destroy();

                /* --- 1. WEEKLY PILL BAR CHART --- */
                const weeklyCtx = document.getElementById('weeklyChart');
                if (weeklyCtx) {
                    weeklyChartInstance = new Chart(weeklyCtx, {
                        type: 'bar',
                        data: {
                            labels: chartData.labels,
                            datasets: [{
                                    label: 'Present',
                                    data: chartData.present,
                                    backgroundColor: colorPresent,
                                    borderColor: cardBg, // Creates the separation gap
                                    borderWidth: 3,
                                    borderRadius: 50, // Pill shape
                                    borderSkipped: false,
                                    barPercentage: 0.45
                                },
                                {
                                    label: 'Late/Absent',
                                    data: chartData.absent,
                                    backgroundColor: colorLate,
                                    borderColor: cardBg,
                                    borderWidth: 3,
                                    borderRadius: 50,
                                    borderSkipped: false,
                                    barPercentage: 0.45
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        usePointStyle: true,
                                        boxWidth: 8,
                                        color: textColor
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    stacked: true,
                                    grid: {
                                        color: gridColor,
                                        drawBorder: false
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            return value >= 1000 ? (value / 1000) + 'k' : value;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                /* --- 2. STATUS DONUT CHART --- */
                const statusCtx = document.getElementById('statusChart');
                if (statusCtx) {
                    statusChartInstance = new Chart(statusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Present', 'Late', 'Absent'],
                            datasets: [{
                                data: chartData.status,
                                backgroundColor: [colorPresent, colorLate, colorAbsent],
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

                // Sync the Donut Legend Colors Dynamically
                document.getElementById('donut-legend').innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span style="color: var(--text-muted); font-size: 0.85rem;"><i class="bi bi-circle-fill me-2" style="color: ${colorPresent}; font-size: 0.65rem;"></i> Present</span>
                    <strong style="color: var(--text-main); font-size: 0.85rem;">${chartData.status[0]}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span style="color: var(--text-muted); font-size: 0.85rem;"><i class="bi bi-circle-fill me-2" style="color: ${colorLate}; font-size: 0.65rem;"></i> Late</span>
                    <strong style="color: var(--text-main); font-size: 0.85rem;">${chartData.status[1]}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span style="color: var(--text-muted); font-size: 0.85rem;"><i class="bi bi-circle-fill me-2" style="color: ${colorAbsent}; font-size: 0.65rem;"></i> Absent</span>
                    <strong style="color: var(--text-main); font-size: 0.85rem;">${chartData.status[2]}</strong>
                </div>
            `;
            }

            // Render on initial load
            renderCharts();

            // -------------------------------------------------------------
            // AUTOMATIC THEME DETECTION (Listens to Bootstrap Toggle)
            // -------------------------------------------------------------
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === "data-bs-theme") {
                        renderCharts(); // Redraw charts instantly when theme changes
                    }
                });
            });

            observer.observe(document.documentElement, {
                attributes: true
            });
        });
    </script>

@endsection

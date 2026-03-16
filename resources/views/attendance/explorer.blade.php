@php
$hideBackground = true;
$hideGlobalFilters = true;
@endphp

@extends('layouts.app')

@section('title', 'Attendance Dashboard')

@section('content')

<style>
    .chart-container {
        height: 320px;
    }

    .chart-container-small {
        height: 260px;
    }
</style>

<div class="container-fluid py-4">

    {{-- KPI CARDS --}}
    <div class="row g-3 mb-3">

        <!-- Present -->
        <div class=" col-12 col-lg-2 col-md-6">
            <div class="card shadow-sm border">
                <div class="card-body py-4 px-3 d-flex align-items-center justify-content-between">

                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-success-subtle text-success p-2 rounded">
                            <i class="bi bi-person-check"></i>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="text-body-secondary small">Present</span>
                            <span class="fw-bold">{{ $presentToday }}</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>


        <!-- Late -->
        <div class="col-lg-2 col-md-6">
            <div class="card shadow-sm border">
                <div class="card-body py-4 px-3 d-flex align-items-center justify-content-between">

                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-warning-subtle text-warning p-2 rounded">
                            <i class="bi bi-clock"></i>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="text-body-secondary small">Late</span>
                            <span class="fw-bold">{{ $lateToday }}</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>


        <!-- Requests -->
        <div class="col-lg-2 col-md-6">
            <div class="card shadow-sm border">
                <div class="card-body py-4 px-3 d-flex align-items-center justify-content-between">

                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary-subtle text-primary p-2 rounded">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="text-body-secondary small">Requests</span>
                            <span class="fw-bold">{{ $pendingRequests }}</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>


        <!-- Sites -->
        <div class="col-lg-2 col-md-6">
            <div class="card shadow-sm border">
                <div class="card-body py-4 px-3 d-flex align-items-center gap-2">

                    <div class="bg-secondary-subtle text-secondary p-2 rounded">
                        <i class="bi bi-building"></i>
                    </div>

                    <span class="text-body-secondary small">Sites</span>
                    <span class="fw-bold">{{ $activeSites }}</span>

                </div>
            </div>
        </div>

        <div class="col-lg-auto col-md-6 ms-auto d-flex align-items-center gap-2">

            <form method="GET" class="d-flex align-items-center gap-2">

                <input type="date" name="date" value="{{ request('date') }}" class="form-control">

                <button class="btn btn-outline-secondary">
                    <i class="bi bi-calendar"></i> Filter Date
                </button>

            </form>

            <a href="{{ route('attendance.export', ['date' => request('date')]) }}"
                class="btn btn-warning text-white">

                <i class="bi bi-download"></i> Export Data

            </a>

        </div>

    </div>

    {{-- CHARTS --}}
    <div class="row g-4 mb-4">

        <div class="col-lg-8">

            <div class="card shadow-sm border-1">

                <div class="card-body">

                    <div class="d-flex justify-content-between mb-4">

                        <div>
                            <h6 class="fw-bold mb-0">Weekly Attendance Trends</h6>
                            <small class="text-body-secondary">
                                Comparing last 7 days of site activity
                            </small>
                        </div>

                        <select class="form-select w-auto">
                            <option>Total Attendance</option>
                            <option>By Site</option>
                        </select>

                    </div>

                    <div class="chart-container">
                        <canvas id="weeklyChart"></canvas>
                    </div>


                </div>
            </div>

        </div>


        <div class="col-lg-4">

            <div class="card shadow-sm border-1">

                <div class="card-body text-center">

                    <h6 class="fw-bold mb-4">Status Distribution</h6>

                    <div class="chart-container-small">
                        <canvas id="statusChart"></canvas>
                    </div>

                    <div class="mt-4">

                        <div class="d-flex justify-content-between">
                            <span><span class="badge bg-success">&nbsp;</span> Present</span>
                            <span>{{ $presentToday }}</span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span><span class="badge bg-danger">&nbsp;</span> Absent</span>
                            <span>{{ $absentToday }}</span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span><span class="badge bg-warning">&nbsp;</span> Late</span>
                            <span>{{ $lateToday }}</span>
                        </div>

                    </div>

                </div>
            </div>

        </div>

    </div>


    {{-- RECENT CHECKINS --}}
    <div class="card shadow-sm border-1">

        <div class="card-header bg-body-tertiary fw-semibold">
            Recent Check-ins
        </div>

        <div class="table-responsive">

            <table class="table align-middle mb-0">

                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Time</th>
                        <th>Site</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($recentCheckins as $check)

                    <tr>

                        <td>
                            <div class="d-flex align-items-center gap-2">

                                <img
                                    src="{{ asset($check->profile_pic ?? 'images/user-placeholder.png') }}"
                                    width="30"
                                    height="30"
                                    class="rounded-circle">

                                <span class="fw-semibold">
                                    {{ $check->name }}
                                </span>

                            </div>
                        </td>

                        <td>{{ $check->time }}</td>
                        <td>{{ $check->site }}</td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

</div>


@php
$chartData = [
'labels' => $weeklyLabels,
'present' => $weeklyPresent,
'absent' => $weeklyAbsent,
'status' => [
$presentToday,
$absentToday,
$lateToday
]
];
@endphp

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {

        const chartData = @json($chartData);

        const success = '#75c893'; // soft green
        const danger = '#f28b82'; // soft red
        const warning = '#ffe066';
        const weeklyCtx = document.getElementById('weeklyChart');
        const statusCtx = document.getElementById('statusChart');


        if (weeklyCtx) {

            new Chart(weeklyCtx, {

                type: 'bar',

                data: {
                    labels: chartData.labels,
                    datasets: [{
                            label: 'Present',
                            data: chartData.present,
                            backgroundColor: success
                        },
                        {
                            label: 'Absent',
                            data: chartData.absent,
                            backgroundColor: danger
                        }
                    ]
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }

            });

        }


        if (statusCtx) {

            new Chart(statusCtx, {

                type: 'doughnut',

                data: {
                    labels: ['Present', 'Absent', 'Late'],
                    datasets: [{
                        data: chartData.status,
                        backgroundColor: [success, danger, warning]
                    }]
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%'
                }

            });

        }

    });
</script>

@endsection

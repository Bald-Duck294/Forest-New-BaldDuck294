@php
    $hideBackground = true;
    $hideGlobalFilters = true;

@endphp
@extends('layouts.app')

@section('title', 'Attendance Dashboard')

@section('content')

    <div class="container-fluid py-4">

        {{-- ================= HEADER ================= --}}
        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <h3 class="fw-bold mb-0">Dashboard Overview</h3>
                <p class="text-muted mb-0">
                    Real-time attendance insights for {{ now()->format('l, M d') }}
                </p>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-light border">
                    <i class="bi bi-calendar"></i> Filter Date
                </button>

                <button class="btn btn-warning text-white">
                    <i class="bi bi-download"></i> Export Data
                </button>
            </div>

        </div>


        {{-- ================= KPI CARDS ================= --}}
        <div class="row g-4 mb-4">

            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">

                        <div class="d-flex justify-content-between mb-3">
                            <div class="bg-success-subtle text-success p-2 rounded">
                                <i class="bi bi-person-check"></i>
                            </div>

                            <span class="badge bg-success-subtle text-success">
                                +4%
                            </span>
                        </div>

                        <p class="text-muted small mb-1">Total Present Today</p>

                        <h3 class="fw-bold">{{ $presentToday }}</h3>

                        <small class="text-muted">
                            Active across {{ $activeSites }} sites
                        </small>

                    </div>
                </div>
            </div>


            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">

                        <div class="d-flex justify-content-between mb-3">
                            <div class="bg-warning-subtle text-warning p-2 rounded">
                                <i class="bi bi-clock"></i>
                            </div>

                            <span class="badge bg-warning-subtle text-warning">
                                -2
                            </span>
                        </div>

                        <p class="text-muted small mb-1">Late Arrivals</p>

                        <h3 class="fw-bold">{{ $lateToday }}</h3>

                        <small class="text-muted">Requires review in logs</small>

                    </div>
                </div>
            </div>


            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">

                        <div class="d-flex justify-content-between mb-3">
                            <div class="bg-primary-subtle text-primary p-2 rounded">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>

                            <span class="badge bg-primary-subtle text-primary">
                                New
                            </span>
                        </div>

                        <p class="text-muted small mb-1">Pending Requests</p>

                        <h3 class="fw-bold">{{ $pendingRequests }}</h3>

                        <small class="text-muted">Leave or transfers</small>

                    </div>
                </div>
            </div>


            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">

                        <div class="d-flex justify-content-between mb-3">
                            <div class="bg-secondary-subtle text-secondary p-2 rounded">
                                <i class="bi bi-building"></i>
                            </div>
                        </div>

                        <p class="text-muted small mb-1">Active Sites</p>

                        <h3 class="fw-bold">{{ $activeSites }}</h3>

                        <small class="text-muted">All operational</small>

                    </div>
                </div>
            </div>

        </div>



        {{-- ================= CHART SECTION ================= --}}
        <div class="row g-4 mb-4">

            {{-- WEEKLY TREND --}}
            <div class="col-lg-8">

                <div class="card shadow-sm border-0">

                    <div class="card-body">

                        <div class="d-flex justify-content-between mb-4">

                            <div>
                                <h6 class="fw-bold mb-0">Weekly Attendance Trends</h6>
                                <small class="text-muted">
                                    Comparing last 7 days of site activity
                                </small>
                            </div>

                            <select class="form-select w-auto">
                                <option>Total Attendance</option>
                                <option>By Site</option>
                            </select>

                        </div>

                        <canvas id="weeklyChart" height="120"></canvas>

                    </div>
                </div>

            </div>


            {{-- STATUS DISTRIBUTION --}}
            <div class="col-lg-3">

                <div class="card shadow-sm border-0">

                    <div class="card-body text-center">

                        <h6 class="fw-bold mb-4">Status Distribution</h6>

                        <canvas id="statusChart" height="160"></canvas>

                        <div class="mt-4">

                            <div class="d-flex justify-content-between">
                                <span><span class="badge bg-success">&nbsp;</span> Present</span>
                                <span>{{ $presentToday }}</span>
                            </div>

                            <div class="d-flex justify-content-between">
                                <span>
                                    <span class="badge" style="background-color:#fca5a5;">&nbsp;</span>
                                    Absent
                                </span>
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



        {{-- ================= RECENT CHECKINS ================= --}}
        <div class="card shadow-sm border-0">

            <div class="card-header bg-white fw-semibold">
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

                                        <img src="{{ asset($check->profile_pic ?? 'images/user-placeholder.png') }}"
                                            width="30" height="30" class="rounded-circle">

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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const weeklyLabels = {!! json_encode($weeklyLabels ?? []) !!};
        const weeklyPresent = {!! json_encode($weeklyPresent ?? []) !!};
        const weeklyAbsent = {!! json_encode($weeklyAbsent ?? []) !!};

        new Chart(document.getElementById('weeklyChart'), {
            type: 'bar',
            data: {
                labels: weeklyLabels,
                datasets: [{
                        label: 'Present',
                        data: weeklyPresent,
                        backgroundColor: '#22c55e'
                    },
                    {
                        label: 'Absent',
                        data: weeklyAbsent,
                        backgroundColor: '#fca5a5'
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });


        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent', 'Late'],
                datasets: [{
                    data: [
                        {{ $presentToday ?? 0 }},
                        {{ $absentToday ?? 0 }},
                        {{ $lateToday ?? 0 }}
                    ],
                    backgroundColor: ['#22c55e', '#fca5a5', '#facc15']
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
@endsection

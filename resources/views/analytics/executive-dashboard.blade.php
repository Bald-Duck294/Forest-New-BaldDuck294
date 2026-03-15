@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    $user = session('user');
@endphp
@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        {{-- Key Performance Indicators --}}
        @include('analytics.partials.kpi-cards', [
            'kpis' => $kpis,
            'coverageAnalysis' => $coverageAnalysis,
        ])

        {{-- Header - Moved below filters/KPIs for better focus --}}
        <!-- <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
                                                <div class="bg-white p-3 shadow-sm border rounded-3 w-100">
                                                    <h2 class="fw-bold mb-1" style="color: #2f4f3f;">Executive Analytics Dashboard</h2>
                                                    <p class="text-muted mb-0">
                                                        Comprehensive forest guard analytics including Patrol Efficiency, Attendance Reliability, and Incident Management.
                                                    </p>
                                                </div>
                                            </div> -->

        {{-- Dashboard Context / Help --}}
        <!-- <div class="alert alert-light border shadow-sm mb-4">
                                                <div class="d-flex gap-3 align-items-start">
                                                    <div class="text-success fs-4"><i class="bi bi-info-circle-fill"></i></div>
                                                    <div>
                                                        <h6 class="fw-bold mb-1 text-dark">Dashboard Insights Guide</h6>
                                                        <p class="mb-0 small text-muted">
                                                            • <strong>KPI Cards:</strong> Top-level metrics comparing current period performance.<br>
                                                            • <strong>Guard Performance:</strong> Scoring based on Patrol Distance (50%), Attendance (30%), and Incidents Reported (20%).<br>
                                                            • <strong>Incident Tracking:</strong> Heatmap of incident types and status distribution.<br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div> -->

        {{-- Guard Performance Rankings --}}
        <div class="mb-2">
            <small class="text-uppercase fw-bold text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">Performance
                Metrics</small>
        </div>
        @include('analytics.partials.guard-performance', ['guardPerformance' => $guardPerformance])


        {{-- Patrol Analytics --}}
        <div class="mt-4 mb-2">
            <small class="text-uppercase fw-bold text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">Patrol
                Operations</small>
        </div>
        @include('analytics.partials.patrol-analytics', ['patrolAnalytics' => $patrolAnalytics])


        {{-- Incident Tracking --}}
        <div class="mt-4 mb-2">
            <small class="text-uppercase fw-bold text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">Safety &
                Incidents</small>
        </div>
        @include('analytics.partials.incident-tracking', ['incidentTracking' => $incidentTracking])




    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="{{ asset('js/executive-dashboard-charts.js') }}"></script>
@endpush

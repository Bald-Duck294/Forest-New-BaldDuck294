@php
$hideGlobalFilters = true;
@endphp

@extends('layouts.app')

@section('title','Forest Reports Dashboard')

@section('content')

<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">

        <div>
            <h2 class="fw-bold text-success mb-1">
                <i class="bi bi-shield-check me-2"></i>
                Forest Reports Dashboard
            </h2>

            <small class="text-muted">
                Real-time monitoring and field data analysis
            </small>
        </div>

        <div class="mt-3 mt-md-0">
            <button class="btn btn-outline-secondary me-2">
                Export Data
            </button>

            <a href="{{ route('report-configs.create') }}" class="btn btn-success">
                + New Report
            </a>
        </div>

    </div>


    {{-- STATISTICS --}}
    <div class="row g-4 mb-4">

        <div class="col-md-4">

            <a href="{{ route('report-configs.table')}}" class="text-decoration-none">

                <div class="card shadow-sm border-1 h-100 hover-shadow">

                    <div class="card-body">

                        <small class="text-muted">
                            Total Reports
                        </small>

                        <h3 class="fw-bold mt-1">
                            {{ number_format($totalReports) }}
                        </h3>

                    </div>

                </div>

            </a>

        </div>


        <div class="col-md-4">
            <div class="card shadow-sm border-1 h-100">
                <div class="card-body">

                    <small class="text-muted">
                        Pending Review
                    </small>

                    <h3 class="fw-bold mt-1 text-warning">
                        {{ number_format($pendingReports) }}
                    </h3>

                </div>
            </div>
        </div>


        <div class="col-md-4">
            <div class="card shadow-sm border-1 h-100">
                <div class="card-body">

                    <small class="text-muted">
                        Active Patrols
                    </small>

                    <h3 class="fw-bold mt-1 text-success">
                        {{ number_format($activePatrols) }}
                    </h3>

                </div>
            </div>
        </div>

    </div>



    <div class="row g-4">

        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

        {{-- MAP SECTION --}}
        <div class="col-lg-8">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-header  border-0">
                    <h5 class="mb-0 fw-bold">
                        Geospatial Distribution
                    </h5>

                    <small class="text-muted">
                        Live coordinates of active alerts
                    </small>
                </div>

                <div class="card-body p-0">

                    {{-- Replace this image later with real map --}}
                    <div id="forestMap" style="height:450px; width:100%;"></div>

                </div>

            </div>

        </div>



        {{-- RECENT REPORTS --}}
        <div class="col-lg-4">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-header ">
                    <h5 class="fw-bold mb-0">
                        Recent Reports
                    </h5>

                    <small class="text-muted">
                        Latest report configurations
                    </small>
                </div>


                <div class="card-body" style="max-height:450px; overflow-y:auto;">

                    @forelse($reports as $report)

                    <div class="border rounded p-3 mb-3">

                        <div class="d-flex justify-content-between align-items-start">

                            <h6 class="fw-semibold mb-1">
                                {{ $report->report_type }}
                            </h6>

                            @if($report->status == 'Pending')
                            <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($report->status == 'Approved')
                            <span class="badge bg-success">Approved</span>
                            @else
                            <span class="badge bg-danger">Rejected</span>
                            @endif

                        </div>

                        <small class="text-muted">
                            Category: {{ $report->category }}
                        </small>

                        <div class="mt-1 text-muted small">
                            Created:
                            {{ \Carbon\Carbon::parse($report->created_at)->format('d M Y') }}
                        </div>

                    </div>

                    @empty

                    <div class="text-center py-4 text-muted">
                        No reports found
                    </div>

                    @endforelse

                </div>

            </div>

        </div>

    </div>

</div>


<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        var map = L.map('forestMap').setView([21.1458, 79.0882], 11); // Nagpur center

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        var reports = @json($reports);

        reports.forEach(function(report) {

            if (report.latitude && report.longitude) {

                var marker = L.marker([
                    parseFloat(report.latitude),
                    parseFloat(report.longitude)
                ]).addTo(map);

                marker.bindPopup(
                    "<b>" + report.report_type + "</b><br>" +
                    "Category: " + report.category + "<br>" +
                    "Status: " + report.status
                );

            }

        });

    });
</script>
@endsection

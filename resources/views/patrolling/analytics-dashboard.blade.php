<style>
    #heatmap {
        height: 400px;
        width: 100%;
        border: 1px solid #ddd;
        border-radius: .25rem;
    }
</style>
@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    // $user = session('user');
@endphp
@extends('layouts.app')

<div class="content">
    <div class="container-fluid">

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <h4 class="mb-0">Patrolling Analytics Dashboard</h4>
                <a href="{{ route('patrolling.analytics.pdf') }}" class="btn btn-danger">
                    <i class="fa fa-file-pdf-o"></i> Export PDF
                </a>
            </div>
        </div>

        <!-- ============================ -->
        <!--        TOP GRAPHS ROW        -->
        <!-- ============================ -->
        <div class="row">

            <!-- BAR CHART -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><b>Top Users by Distance</b></div>
                    <div class="card-body">
                        <canvas id="distanceBarChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- PIE CHART -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><b>Site Activity Distribution</b></div>
                    <div class="card-body">
                        <canvas id="sitePieChart"></canvas>
                    </div>
                </div>
            </div>

        </div>

        <!-- ============================ -->
        <!--        HOURLY DENSITY         -->
        <!-- ============================ -->
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header"><b>Patrol Density by Hour</b></div>
                    <div class="card-body">
                        <canvas id="hourlyLineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================ -->
        <!--         HEATMAP MAP          -->
        <!-- ============================ -->
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header"><b>Patrol Heatmap</b></div>
                    <div class="card-body">
                        <div id="heatmap"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const distanceData = @json($distanceByUser);
    const siteActivity = @json($siteActivity);
    const hourlyDensity = @json($hourlyDensity);
    const heatmapPoints = @json($heatmapPoints);

    // -------------------------------
    // BAR CHART → USER DISTANCE
    // -------------------------------
    new Chart(document.getElementById("distanceBarChart"), {
        type: 'bar',
        data: {
            labels: Object.keys(distanceData),
            datasets: [{
                label: 'Distance (km)',
                data: Object.values(distanceData).map(v => (v / 1000).toFixed(2)),
                backgroundColor: '#007bff'
            }]
        }
    });

    // -------------------------------
    // PIE CHART → SITE ACTIVITY
    // -------------------------------
    new Chart(document.getElementById("sitePieChart"), {
        type: 'pie',
        data: {
            labels: Object.keys(siteActivity),
            datasets: [{
                data: Object.values(siteActivity),
                backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#20c997']
            }]
        }
    });

    // -------------------------------
    // LINE CHART → HOURLY DENSITY
    // -------------------------------
    new Chart(document.getElementById("hourlyLineChart"), {
        type: 'line',
        data: {
            labels: [...Array(24).keys()],
            datasets: [{
                label: "Logs / Hour",
                data: hourlyDensity,
                borderColor: '#28a745',
                fill: false,
                tension: 0.2
            }]
        }
    });

    // -------------------------------
    // GOOGLE MAPS HEATMAP
    // -------------------------------
    let map = new google.maps.Map(document.getElementById("heatmap"), {
        zoom: 12,
        center: {
            lat: 19.0760,
            lng: 72.8777
        },
        mapTypeId: "roadmap"
    });

    let heatmapData = heatmapPoints.map(p => new google.maps.LatLng(p.lat, p.lng));

    new google.maps.visualization.HeatmapLayer({
        data: heatmapData,
        map: map,
        radius: 25
    });
</script>

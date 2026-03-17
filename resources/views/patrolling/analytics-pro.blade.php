<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css"> -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    // $user = session('user');
@endphp
@extends('layouts.app')
<div class="content">
    <div class="container-fluid">

        <h3 class="mb-3">Patrolling Analytics – Advanced Dashboard</h3>

        <!-- ---------------------- -->
        <!-- ADMINLTE KPI CARDS -->
        <!-- ---------------------- -->

        <div class="row">

            <div class="col-md-3">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ count($sessions) }}</h3>
                        <p>Total Sessions</p>
                    </div>
                    <div class="icon"><i class="fas fa-route"></i></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ count($logs) }}</h3>
                        <p>Total Logs</p>
                    </div>
                    <div class="icon"><i class="fas fa-list-alt"></i></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ number_format(array_sum($userDistance) / 1000, 2) }}</h3>
                        <p>Total Distance (km)</p>
                    </div>
                    <div class="icon"><i class="fas fa-walking"></i></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ round(array_sum($hourly) / 24) }}</h3>
                        <p>Avg Activity / Hour</p>
                    </div>
                    <div class="icon"><i class="fas fa-clock"></i></div>
                </div>
            </div>

        </div>


        <!-- --------------------------- -->
        <!-- APEXCHARTS: USER DISTANCE -->
        <!-- --------------------------- -->
        <div class="card">
            <div class="card-header">
                <h4>User Distance Covered (Bar Chart)</h4>
            </div>
            <div class="card-body">
                <div id="userDistanceChart"></div>
            </div>
        </div>


        <!-- --------------------------- -->
        <!-- APEXCHARTS: SITE ACTIVITY -->
        <!-- --------------------------- -->
        <div class="card mt-3">
            <div class="card-header">
                <h4>Site Activity Distribution (Pie)</h4>
            </div>
            <div class="card-body">
                <div id="sitePieChart"></div>
            </div>
        </div>


        <!-- ------------------------------ -->
        <!-- APEXCHARTS: HOURLY DRILLDOWN -->
        <!-- ------------------------------ -->
        <div class="card mt-3">
            <div class="card-header">
                <h4>Patrol Density by Hour (Drilldown Line Chart)</h4>
            </div>
            <div class="card-body">
                <div id="hourlyChart"></div>
            </div>
        </div>


        <!-- ------------------------------ -->
        <!-- USER VS USER COMPARISON -->
        <!-- ------------------------------ -->
        <div class="card mt-4">
            <div class="card-header">
                <h4>User vs User Comparison</h4>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label>Select User A</label>
                        <select id="userA" class="form-control">
                            @foreach ($compareUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Select User B</label>
                        <select id="userB" class="form-control">
                            @foreach ($compareUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="userCompareChart" class="mt-4"></div>
            </div>
        </div>

    </div>
</div>



<script>
    const userDistance = @json($userDistance);
    const userSessions = @json($userSessions);
    const userLabels = @json($userLabels);
    const siteSessions = @json($siteSessions);
    const siteLabels = @json($siteLabels);
    const hourly = @json($hourly);

    // --------------------------
    // BAR CHART – USER DISTANCE
    // --------------------------
    new ApexCharts(document.querySelector("#userDistanceChart"), {
        chart: {
            type: 'bar',
            height: 350
        },
        series: [{
            name: 'Distance (km)',
            data: Object.keys(userDistance).map(id => (userDistance[id] / 1000).toFixed(2))
        }],
        xaxis: {
            categories: Object.keys(userDistance).map(id => userLabels[id])
        }
    }).render();



    // --------------------------
    // PIE CHART – SITE ACTIVITY
    // --------------------------
    new ApexCharts(document.querySelector("#sitePieChart"), {
        chart: {
            type: 'pie',
            height: 350
        },
        series: Object.keys(siteSessions).map(id => siteSessions[id]),
        labels: Object.keys(siteSessions).map(id => siteLabels[id])
    }).render();


    // --------------------------
    // LINE CHART – HOURLY ACTIVITY
    // --------------------------
    new ApexCharts(document.querySelector("#hourlyChart"), {
        chart: {
            type: 'line',
            height: 350
        },
        series: [{
            name: "Logs",
            data: hourly
        }],
        xaxis: {
            categories: [...Array(24).keys()]
        }
    }).render();


    // --------------------------
    // USER VS USER COMPARISON
    // --------------------------
    const compareChart = new ApexCharts(document.querySelector("#userCompareChart"), {
        chart: {
            type: 'radar',
            height: 350
        },
        series: [{
                name: "User A",
                data: []
            },
            {
                name: "User B",
                data: []
            }
        ],
        xaxis: {
            categories: ["Sessions", "Distance (km)"]
        }
    });

    compareChart.render();

    function updateComparison() {
        let A = document.getElementById("userA").value;
        let B = document.getElementById("userB").value;

        compareChart.updateSeries([{
                name: "User A",
                data: [
                    userSessions[A] ?? 0,
                    ((userDistance[A] ?? 0) / 1000).toFixed(2)
                ]
            },
            {
                name: "User B",
                data: [
                    userSessions[B] ?? 0,
                    ((userDistance[B] ?? 0) / 1000).toFixed(2)
                ]
            }
        ]);
    }

    document.getElementById("userA").onchange = updateComparison;
    document.getElementById("userB").onchange = updateComparison;
    updateComparison();
</script>

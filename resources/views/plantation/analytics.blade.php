@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    $user = session('user');
@endphp
@extends('layouts.app')

@section('title', 'Plantation Analytics')

@section('content')

    <div class="container-fluid py-4">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <h3 class="fw-bold mb-0">Plantation Analytics</h3>
                <p class="text-muted mb-0">Real-time monitoring of crop performance and resource distribution.</p>
            </div>

            <div>
                <button class="btn btn-light border me-2">
                    <i class="bi bi-calendar"></i> Last 6 Months
                </button>

                <button class="btn btn-warning text-white">
                    <i class="bi bi-download"></i> Export Report
                </button>
            </div>

        </div>


        {{-- TOP GRID --}}
        <div class="row g-4 mb-4">

            {{-- YIELD CHART --}}
            <div class="col-lg-8">

                <div class="card shadow-sm border-0">

                    <div class="card-body">

                        <div class="d-flex justify-content-between mb-3">

                            <div>
                                <h5 class="fw-bold mb-0">Yield Forecast vs Actual</h5>
                                <small class="text-muted">Comparison of projected growth vs measured output</small>
                            </div>

                            <div class="text-end">
                                <h4 class="fw-bold text-warning mb-0">420 Tons</h4>
                                <small class="text-success">+12.5% vs target</small>
                            </div>

                        </div>

                        <canvas id="yieldChart" height="120"></canvas>

                    </div>

                </div>

            </div>


            {{-- HEALTH DISTRIBUTION --}}
            <div class="col-lg-3">

                <div class="card shadow-sm border-0">

                    <div class="card-body text-center p-3">

                        <h6 class="fw-bold mb-2">Crop Health Distribution</h6>

                        <canvas id="healthChart" height="140"></canvas>

                        <div class="mt-3 small">

                            <div class="d-flex justify-content-between">
                                <span><span class="badge bg-success">&nbsp;</span> Optimal</span>
                                <span>75%</span>
                            </div>

                            <div class="d-flex justify-content-between">
                                <span><span class="badge bg-warning">&nbsp;</span> Action Required</span>
                                <span>15%</span>
                            </div>

                            <div class="d-flex justify-content-between">
                                <span><span class="badge bg-danger">&nbsp;</span> Critical</span>
                                <span>10%</span>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        {{-- BOTTOM GRID --}}
        <div class="row g-4">

            {{-- RESOURCE UTILIZATION --}}
            <div class="col-lg-4">

                <div class="card shadow-sm border-0">

                    <div class="card-body">

                        <h5 class="fw-bold mb-4">Resource Utilization</h5>

                        <div class="mb-4">

                            <div class="d-flex justify-content-between">
                                <span>Water Irrigation</span>
                                <span class="fw-bold">{{ $waterUsage }}%</span>
                            </div>

                            <div class="progress">
                                <div class="progress-bar bg-primary" style=`width:{{ $waterUsage }}%`></div>
                            </div>

                        </div>


                        <div class="mb-4">

                            <div class="d-flex justify-content-between">
                                <span>Fertilizer Stock</span>
                                <span class="fw-bold">{{ $fertilizerStock }}%</span>
                            </div>

                            <div class="progress">
                                <div class="progress-bar bg-success" style="width:{{ $fertilizerStock }}%"></div>
                            </div>

                        </div>


                        <div>

                            <div class="d-flex justify-content-between">
                                <span>Labor Efficiency</span>
                                <span class="fw-bold">{{ $laborEfficiency }}%</span>
                            </div>

                            <div class="progress">
                                <div class="progress-bar bg-warning" style="width:{{ $laborEfficiency }}%"></div>
                            </div>

                        </div>

                    </div>

                </div>

            </div>



            {{-- TOP PLANTATIONS --}}
            <div class="col-lg-8">

                <div class="card shadow-sm border-0">

                    <div class="card-header bg-white d-flex justify-content-between">

                        <h5 class="fw-bold mb-0">Top Performing Plantations</h5>

                        <a href="#" class="text-warning fw-semibold text-decoration-none">View All</a>

                    </div>

                    <div class="card-body p-0">

                        <table class="table align-middle mb-0">

                            <thead class="table-light">

                                <tr>
                                    <th>Plantation</th>
                                    <th>Crop</th>
                                    <th class="text-end">Efficiency</th>
                                    <th class="text-end">Status</th>
                                </tr>

                            </thead>

                            <tbody>

                                @foreach ($topPlantations as $pln)
                                    <tr>
                                        <td class="fw-semibold">{{ $pln->name }}</td>

                                        <td>{{ $pln->plant_species ?? 'N/A' }}</td>

                                        <td class="text-end text-success fw-bold">
                                            {{ $pln->observations_count }}
                                        </td>

                                        <td class="text-end">
                                            <span class="badge bg-success">Active</span>
                                        </td>

                                    </tr>
                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>



    {{-- CHARTS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // LINE CHART (Real Data)
        new Chart(document.getElementById('yieldChart'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Observations',
                    data: @json($monthlyObservations),
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249,115,22,0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });


        // DOUGHNUT CHART (Real Health Distribution)
        new Chart(document.getElementById('healthChart'), {
            type: 'doughnut',
            data: {
                labels: ['Optimal', 'Action Required', 'Critical'],
                datasets: [{
                    data: @json([$optimal, $actionRequired, $critical]),
                    backgroundColor: ['#22c55e', '#facc15', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>

@endsection

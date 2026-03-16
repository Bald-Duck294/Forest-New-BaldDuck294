@php
$hideGlobalFilters = true;
@endphp
@extends('layouts.app')

@section('title', 'Attendance Logs')

@section('content')

<div class="container-fluid py-4">

    {{-- FILTERS --}}
    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <form method="GET">

                <div class="row g-3 align-items-end">

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Date Range</label>
                        <select class="form-select" name="range">
                            <option value="30days" {{ request('range') == '30days' ? 'selected' : '' }}>Last 30 Days</option>
                            <option value="today" {{ request('range') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="week" {{ request('range') == 'week' ? 'selected' : '' }}>This Week</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Employee</label>
                        <select class="form-select" name="employee">

                            <option value="">All Employees</option>

                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}"
                                {{ request('employee') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }}
                            </option>
                            @endforeach

                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Site</label>
                        <select class="form-select" name="site">

                            <option value="">All Sites</option>

                            @foreach($sites as $site)
                            <option value="{{ $site->id }}"
                                {{ request('site') == $site->id ? 'selected' : '' }}>
                                {{ $site->name }}
                            </option>
                            @endforeach

                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Client</label>
                        <select class="form-select" name="client">

                            <option value="">All Clients</option>

                            @foreach($clients as $client)
                            <option value="{{ $client->client_name }}"
                                {{ request('client') == $client->client_name ? 'selected' : '' }}>
                                {{ $client->client_name }}
                            </option>
                            @endforeach

                        </select>
                    </div>

                    <!-- Apply Button -->
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">
                            Apply
                        </button>
                    </div>

                    <!-- Export Buttons -->
                    <!-- <div class="col-md-2 d-flex gap-2">
                        <button type="submit" name="export" value="csv" class="btn btn-outline-success">
                            <i class="bi bi-filetype-csv"></i>
                        </button>

                        <button type="submit" name="export" value="pdf" class="btn btn-outline-danger">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </button>
                    </div> -->

                </div>

            </form>

        </div>

    </div>

    {{-- SUMMARY CARDS --}}

    <div class="row mt-4 g-3 mb-4">

        <!-- On Time -->
        <div class="col-md-3">
            <div class="card shadow-sm border-1 h-100 hover-card"
                onclick="window.location.href='{{ route('attendance.logs') }}'">

                <div class="card-body d-flex align-items-center gap-3">

                    <div class="bg-success-subtle text-success p-3 rounded">
                        <i class="bi bi-check-circle fs-4"></i>
                    </div>

                    <div>
                        <div class="text-muted small">On-time Completion</div>
                        <h5 class="fw-bold mb-0">{{ $onTimePercent }}%</h5>
                    </div>

                </div>
            </div>
        </div>


        <!-- Avg Late -->
        <div class="col-md-3">
            <div class="card shadow-sm border-1 h-100 hover-card">

                <div class="card-body d-flex align-items-center gap-3">

                    <div class="bg-warning-subtle text-warning p-3 rounded">
                        <i class="bi bi-clock fs-4"></i>
                    </div>

                    <div>
                        <div class="text-muted small">Avg Lateness</div>
                        <h5 class="fw-bold mb-0">{{ $avgLate }}m</h5>
                    </div>

                </div>
            </div>
        </div>


        <!-- Incidents -->
        <div class="col-md-3">
            <div class="card shadow-sm border-1 h-100 hover-card">

                <div class="card-body d-flex align-items-center gap-3">

                    <div class="bg-danger-subtle text-danger p-3 rounded">
                        <i class="bi bi-exclamation-triangle fs-4"></i>
                    </div>

                    <div>
                        <div class="text-muted small">Unresolved Incidents</div>
                        <h5 class="fw-bold mb-0">{{ $incidents }}</h5>
                    </div>

                </div>
            </div>
        </div>

    </div>

    {{-- TABLE --}}
    <div class="card shadow-sm">

        <div class="table-responsive">

            <table class="table align-middle mb-0">

                <thead class="table">
                    <tr>
                        <th>Employee Name</th>
                        <th>Site Name</th>
                        <th>Entry Time</th>
                        <th>Exit Time</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($logs as $log)
                    <tr>

                        <td>
                            <div class="d-flex align-items-center gap-2">

                                <div class="rounded-circle bg-secondary" style="width:36px;height:36px;"></div>

                                <div>
                                    <div class="fw-semibold">{{ $log->name }}</div>
                                    <small class="text-muted">{{ $log->client_name }}</small>
                                </div>

                            </div>
                        </td>

                        <td>
                            <div>{{ $log->site_name }}</div>
                            <small class="text-muted">{{ $log->client_name }}</small>
                        </td>

                        <td>{{ $log->entry_time ?? '-' }}</td>

                        <td>{{ $log->exit_time ?? '-' }}</td>

                        <td>{{ $log->duration_for_calc ?? '-' }}</td>

                        <td>

                            @if ($log->emergency_attend == 1)
                            <span class="badge bg-danger-subtle text-danger">
                                Emergency
                            </span>
                            @elseif($log->lateTime > 0)
                            <span class="badge bg-warning-subtle text-warning">
                                Late ({{ $log->lateTime }}m)
                            </span>
                            @else
                            <span class="badge bg-success-subtle text-success">
                                On-time
                            </span>
                            @endif

                        </td>

                        <td class="text-end">
                            <a class="text-primary fw-semibold">View Details</a>
                        </td>

                    </tr>

                    @empty

                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            No attendance records found
                        </td>
                    </tr>
                    @endforelse

                </tbody>

            </table>

        </div>

    </div>
    <div class="p-3">
        {{ $logs->links('pagination::bootstrap-5') }}
    </div>


</div>

@endsection

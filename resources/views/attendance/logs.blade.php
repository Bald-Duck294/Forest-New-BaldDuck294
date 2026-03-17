@php
    $hideGlobalFilters = true;
@endphp
@extends('layouts.app')

@section('title', 'Attendance Logs')

@section('content')

    <style>
        /* Custom Filter Button Styles to match Overview */
        .custom-date-input,
        .custom-select-input {
            background-color: var(--bg-card, #fff);
            color: var(--text-main, #000);
            border: 1px solid var(--border-color, #dee2e6);
            border-radius: 8px;
            padding: 8px 12px;
            outline: none;
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }

        html[data-bs-theme="dark"] .custom-date-input {
            color-scheme: dark;
        }

        html[data-bs-theme="light"] .custom-date-input {
            color-scheme: light;
        }

        .custom-filter-btn {
            background-color: var(--sapphire-primary, #3B82F6);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .custom-filter-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            color: #ffffff;
        }

        /* Adjust KPI boxes for 3-column layout */
        .kpi-box-logs {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem 1.5rem;
            border-radius: 12px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            transition: transform 0.2s ease, border-color 0.2s ease;
            height: 100%;
            cursor: pointer;
        }

        .kpi-box-logs:hover {
            transform: translateY(-3px);
            border-color: var(--sapphire-primary);
        }
    </style>

    <div class="container-fluid py-4">

        {{-- FILTERS SECTION --}}
        <div class="dash-card mb-4 p-3">
            <form method="GET">
                <div class="row g-3 align-items-end">

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold" style="color: var(--text-muted);">Date Range</label>
                        <select class="form-select custom-select-input w-100" name="range">
                            <option value="30days" {{ request('range') == '30days' ? 'selected' : '' }}>Last 30 Days
                            </option>
                            <option value="today" {{ request('range') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="week" {{ request('range') == 'week' ? 'selected' : '' }}>This Week</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold" style="color: var(--text-muted);">Employee</label>
                        <select class="form-select custom-select-input w-100" name="employee">
                            <option value="">All Employees</option>
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}" {{ request('employee') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold" style="color: var(--text-muted);">Site</label>
                        <select class="form-select custom-select-input w-100" name="site">
                            <option value="">All Sites</option>
                            @foreach ($sites as $site)
                                <option value="{{ $site->id }}" {{ request('site') == $site->id ? 'selected' : '' }}>
                                    {{ $site->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold" style="color: var(--text-muted);">Client</label>
                        <select class="form-select custom-select-input w-100" name="client">
                            <option value="">All Clients</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->client_name }}"
                                    {{ request('client') == $client->client_name ? 'selected' : '' }}>
                                    {{ $client->client_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="custom-filter-btn w-100 h-100">
                            Apply Filters
                        </button>
                    </div>

                </div>
            </form>
        </div>

        {{-- SUMMARY CARDS --}}
        <div class="row g-4 mb-4">
            <div class="col-12 col-md-4">
                <div class="kpi-box-logs">
                    <div class="kpi-icon badge-soft-success">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="kpi-info">
                        <h4>On-time Completion</h4>
                        <h2>{{ $onTimePercent ?? 0 }}%</h2>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="kpi-box-logs">
                    <div class="kpi-icon badge-soft-warning">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="kpi-info">
                        <h4>Avg Lateness</h4>
                        <h2>{{ $avgLate ?? 0 }}m</h2>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="kpi-box-logs">
                    <div class="kpi-icon badge-soft-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div class="kpi-info">
                        <h4>Unresolved Incidents</h4>
                        <h2>{{ $incidents ?? 0 }}</h2>
                    </div>
                </div>
            </div>
        </div>

        {{-- LOGS TABLE --}}
        <div class="dash-card p-0 overflow-hidden">

            <div class="table-responsive">
                <table class="table table-borderless dash-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4">Employee Name</th>
                            <th>Site Name</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        @if (isset($log->profile_pic))
                                            <img src="{{ asset($log->profile_pic) }}" width="40" height="40"
                                                class="rounded-circle shadow-sm"
                                                style="object-fit: cover; border: 2px solid var(--border-color);">
                                        @else
                                            <div class="rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                                                style="width:40px; height:40px; background-color: var(--border-color); color: var(--text-muted);">
                                                <i class="bi bi-person-fill fs-5"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold" style="color: var(--text-main);">
                                                {{ $log->name ?? 'Unknown User' }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div style="color: var(--text-main);">{{ $log->site_name ?? 'N/A' }}</div>
                                </td>

                                <td style="color: var(--text-main);">
                                    {{ $log->duration_for_calc ?? '-' }}
                                </td>

                                <td>
                                    @if (($log->emergency_attend ?? 0) == 1)
                                        <span class="badge badge-soft-danger rounded-pill px-3">
                                            Emergency
                                        </span>
                                    @elseif(($log->lateTime ?? 0) > 0)
                                        <span class="badge badge-soft-warning rounded-pill px-3">
                                            Late ({{ $log->lateTime }}m)
                                        </span>
                                    @else
                                        <span class="badge badge-soft-success rounded-pill px-3">
                                            On-time
                                        </span>
                                    @endif
                                </td>

                                <td class="text-end pe-4">
                                    <a href="#" class="fw-semibold text-decoration-none"
                                        style="color: var(--sapphire-primary); font-size: 0.9rem;">
                                        View Details
                                    </a>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5" style="color: var(--text-muted);">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                    No attendance records found for the selected criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 px-2">
            {{ $logs->links('pagination::bootstrap-5') }}
        </div>

    </div>

@endsection

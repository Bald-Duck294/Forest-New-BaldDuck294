@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Reports Hub</h3>
            <p class="text-muted mb-0">Advanced analytical reporting and performance tracking</p>
        </div>
        @if($reportType && $data && count($data) > 0)
        <div>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" target="_blank" class="btn btn-danger d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-file-pdf fs-5"></i>
                <span class="fw-bold">Export Report</span>
            </a>
        </div>
        @endif
    </div>

    {{-- Report Selection Tabs --}}
    <div class="row g-3 mb-5">
        @php
            $types = [
                'attendance' => ['title' => 'Attendance', 'icon' => 'bi-calendar-check', 'color' => 'primary', 'desc' => 'Daily cycles & shift compliance'],
                'patrol' => ['title' => 'Day Patrol', 'icon' => 'bi-shield-shaded', 'color' => 'success', 'desc' => 'Route efficiency & coverage'],
                'night_patrol' => ['title' => 'Night Ops', 'icon' => 'bi-moon-stars-fill', 'color' => 'indigo', 'desc' => 'Night shift vigilance tracking'],
                'incident' => ['title' => 'Incidents', 'icon' => 'bi-exclamation-octagon-fill', 'color' => 'danger', 'desc' => 'Security & wildlife sightings']
            ];
        @endphp

        @foreach($types as $key => $info)
        <div class="col-md-3">
            <a href="{{ url()->current() }}?report_type={{ $key }}&start_date={{ request('start_date') }}&end_date={{ request('end_date') }}&range={{ request('range') }}&beat={{ request('beat') }}" 
               class="text-decoration-none h-100 d-block">
                <div class="card h-100 border-0 shadow-sm transition-hover {{ $reportType == $key ? 'bg-'.$info['color'].' text-white' : 'bg-white' }}">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="icon-shape rounded-3 {{ $reportType == $key ? 'bg-white text-'.$info['color'] : 'bg-light text-'.$info['color'] }}">
                                <i class="bi {{ $info['icon'] }} fs-4"></i>
                            </div>
                            <h5 class="fw-bold mb-0">{{ $info['title'] }}</h5>
                        </div>
                        <p class="small mb-0 {{ $reportType == $key ? 'text-white-50' : 'text-muted' }}">{{ $info['desc'] }}</p>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>


    @if($reportType)
        {{-- Analytical Summaries --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-pie-chart text-primary"></i> Performance Summary
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 custom-report-table">
                        <thead class="bg-light">
                            @if($reportType == 'attendance')
                                <tr>
                                    <th class="ps-4">Rank</th>
                                    <th class="text-start">Guard Name</th>
                                    <th>Days Active</th>
                                    <th>Late Marks</th>
                                    <th>Absents</th>
                                    <th class="pe-4">Compliance</th>
                                </tr>
                            @elseif($reportType == 'patrol' || $reportType == 'night_patrol')
                                <tr>
                                    <th class="ps-4">Guard Name</th>
                                    <th>Total Sessions</th>
                                    <th>Distance covered</th>
                                    <th>Avg Speed</th>
                                    <th class="pe-4">Total Vigilance Time</th>
                                </tr>
                            @elseif($reportType == 'incident')
                                <tr>
                                    <th class="ps-4">Date & Time</th>
                                    <th>Guard</th>
                                    <th>Incident Type</th>
                                    <th>Location</th>
                                    <th class="pe-4">Observation Notes</th>
                                </tr>
                            @endif
                        </thead>
                        <tbody>
                            @forelse($summary as $s)
                                @if($reportType == 'attendance')
                                    <tr>
                                        <td class="ps-4 text-muted">#{{ $loop->iteration }}</td>
                                        <td class="fw-bold text-start">{{ $s->guard_name }}</td>
                                        <td>{{ $s->present_days }} / {{ $s->total_days }}</td>
                                        <td><span class="badge {{ $s->late_count > 0 ? 'bg-soft-warning text-warning' : 'bg-soft-success text-success' }}">{{ $s->late_count }} times</span></td>
                                        <td>{{ $s->absent_days }} days</td>
                                        <td class="pe-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress grow" style="height: 6px;">
                                                    <div class="progress-bar bg-primary" style="width: {{ $s->attendance_rate }}%"></div>
                                                </div>
                                                <span class="fw-bold">{{ $s->attendance_rate }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @elseif($reportType == 'patrol' || $reportType == 'night_patrol')
                                    <tr>
                                        <td class="ps-4 fw-bold text-start">{{ $s->guard_name }}</td>
                                        <td>{{ $s->total_sessions }}</td>
                                        <td><span class="badge bg-soft-success text-success">{{ $s->total_dist }} km</span></td>
                                        <td>{{ $s->avg_speed }} km/h</td>
                                        <td class="pe-4 text-muted">{{ $s->total_time }} hours</td>
                                    </tr>
                                @elseif($reportType == 'incident')
                                    <tr>
                                        <td class="ps-4 text-muted small">{{ \Carbon\Carbon::parse($s->created_at)->format('d M y, H:i') }}</td>
                                        <td class="fw-bold">{{ $s->guard_name }}</td>
                                        <td><span class="badge bg-soft-danger text-danger">{{ ucwords(str_replace('_', ' ', $s->type)) }}</span></td>
                                        <td>{{ $s->site_name ?? 'N/A' }}</td>
                                        <td class="pe-4 small text-muted text-wrap" style="max-width: 250px;">{{ $s->notes }}</td>
                                    </tr>
                                @endif
                            @empty
                                <tr><td colspan="6" class="text-center py-5 text-muted">No analysis data available</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Detailed Activity Logs --}}
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">Activity Audit Logs</h5>
                <span class="badge bg-light text-dark">Showing latest {{ count($data) }} entries</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 custom-report-table">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Date & Time</th>
                                <th class="text-start">Guard</th>
                                <th>Location (Beat)</th>
                                @if($reportType == 'attendance')
                                    <th>Status</th>
                                    <th class="pe-4">Late Mins</th>
                                @elseif($reportType == 'incident')
                                    <th>Incident Type</th>
                                    <th class="pe-4">Observation Notes</th>
                                @else
                                    <th>Mode</th>
                                    <th>Distance</th>
                                    <th>Duration</th>
                                    <th class="pe-4">Avg Speed</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $row)
                                <tr>
                                    <td class="ps-4 text-muted small">
                                        @if($reportType == 'attendance')
                                            {{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}
                                        @elseif($reportType == 'incident')
                                            {{ \Carbon\Carbon::parse($row->created_at)->format('d M Y, h:i A') }}
                                        @else
                                            {{ \Carbon\Carbon::parse($row->started_at)->format('d M Y, h:i A') }}
                                        @endif
                                    </td>
                                    <td class="fw-bold text-start">{{ $row->guard_name }}</td>
                                    <td>{{ $row->site_name ?? 'N/A' }}</td>
                                    
                                    @if($reportType == 'attendance')
                                        <td>
                                            <span class="badge rounded-pill {{ $row->status == 1 ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }}">
                                                {{ $row->status == 1 ? 'Present' : 'Absent' }}
                                            </span>
                                        </td>
                                        <td class="pe-4">
                                            @if($row->late_minutes > 0)
                                                <span class="text-danger fw-bold"><i class="bi bi-clock-history"></i> {{ $row->late_minutes }}m</span>
                                            @else
                                                <span class="text-success small">On Time</span>
                                            @endif
                                        </td>
                                    @elseif($reportType == 'incident')
                                        <td><span class="badge bg-soft-dark text-dark border">{{ ucwords(str_replace('_', ' ', $row->type)) }}</span></td>
                                        <td class="pe-4 small text-muted text-wrap" style="max-width: 300px;">{{ $row->notes ?: 'No details provided' }}</td>
                                    @else
                                        <td><span class="badge bg-light text-dark">{{ $row->mode }}</span></td>
                                        <td><span class="fw-bold text-primary">{{ $row->distance_km }} km</span></td>
                                        <td class="small">{{ $row->duration_formatted }}</td>
                                        <td class="pe-4 fw-bold">{{ $row->avg_speed }} <small class="text-muted">km/h</small></td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-5 text-muted">No transactional records found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        {{-- Empty State --}}
        <div class="text-center py-5">
          
            <h4 class="fw-bold text-dark">Selection Required</h4>
            <p class="text-muted">Please select a report category above to drill down into the analytics.</p>
        </div>
    @endif
</div>

<style>
/* Modern Report Hub Styles */
:root {
    --bg-indigo: #6610f2;
    --indigo: #6610f2;
}
.bg-indigo { background-color: var(--indigo) !important; }
.text-indigo { color: var(--indigo) !important; }

.transition-hover {
    transition: all 0.3s cubic-bezier(.25,.8,.25,1);
    cursor: pointer;
}
.transition-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

.icon-shape {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.custom-report-table th {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 700;
    color: #6c757d;
    border-bottom-width: 0;
}
.custom-report-table td {
    padding-top: 1rem;
    padding-bottom: 1rem;
    font-size: 0.875rem;
}

.bg-soft-primary { background-color: rgba(13, 110, 253, 0.1) !important; }
.bg-soft-success { background-color: rgba(25, 135, 84, 0.1) !important; }
.bg-soft-danger { background-color: rgba(220, 53, 69, 0.1) !important; }
.bg-soft-warning { background-color: rgba(255, 193, 7, 0.1) !important; }
.bg-soft-dark { background-color: rgba(33, 37, 41, 0.1) !important; }

.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
}
</style>
@endsection

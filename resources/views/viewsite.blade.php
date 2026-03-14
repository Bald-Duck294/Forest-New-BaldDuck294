@php
    $hideGlobalFilters = true;
    $hideBackground = true;
@endphp

@extends('layouts.app')

@section('content')
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --border: #e2e8f0;
            --bg: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        .view-site-container {
            padding: 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-main);
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .btn-back:hover {
            background: #f1f5f9;
            color: var(--primary);
            border-color: var(--primary);
        }

        .page-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .details-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            background: transparent;
            border-bottom: 1px solid var(--border);
        }

        .card-header h5 {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .info-group {
            margin-bottom: 1.25rem;
        }

        .info-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 1rem;
            color: var(--text-main);
            font-weight: 500;
        }

        .management-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .management-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border);
        }

        .management-item:last-child {
            border-bottom: none;
        }

        .management-item i {
            color: var(--primary);
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }

        .management-item .name {
            font-weight: 600;
            color: var(--text-main);
        }

        .management-item .detail {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-left: auto;
        }

        .empty-state {
            text-align: center;
            color: var(--text-muted);
            padding: 1.5rem 0;
        }

        .shift-badge {
            background: #eff6ff;
            color: #1e40af;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.8125rem;
            font-weight: 600;
            margin-left: auto;
        }

        @media (max-width: 992px) {
            .details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="view-site-container">
        <div class="page-header">
            <a href="javascript:history.back()" class="btn-back" title="Go Back">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="page-title">Site Profile</h1>
        </div>

        <div class="details-grid">
            <div class="main-info">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-info-circle text-primary"></i> General Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Site Name</span>
                                    <div class="info-value">{{ $sites->name }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Client Name</span>
                                    <div class="info-value text-primary">{{ $sites->client_name }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Site Type</span>
                                    <div class="info-value">{{ $sites->siteType }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Late Time Allowance</span>
                                    <div class="info-value">{{ $sites->lateTime }} Minutes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-geo-alt text-primary"></i> Contact & Location</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="info-group">
                                    <span class="info-label">Address</span>
                                    <div class="info-value">{{ $sites->address }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">City, State</span>
                                    <div class="info-value">{{ $sites->city }}, {{ $sites->state }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Pincode</span>
                                    <div class="info-value">{{ $sites->pincode }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Contact Person</span>
                                    <div class="info-value">{{ $sites->contactPerson }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Contact Number</span>
                                    <div class="info-value">{{ $sites->mobile }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="side-info">
                <!-- Supervisors -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-person-badge text-primary"></i> Supervisors</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $assignedSupervisorsFromSiteAssigned = App\SiteAssign::where(
                                'site_id',
                                'like',
                                '%' . $sites->id . '%',
                            )
                                ->where('role_id', 2)
                                ->get();

                            $assignedSupervisorsArray = [];
                            foreach ($assignedSupervisorsFromSiteAssigned as $item) {
                                $geoArray = json_decode($item['site_id'], true);
                                if (is_array($geoArray)) {
                                    foreach ($geoArray as $geo) {
                                        if ($sites->id == $geo) {
                                            $assignedSupervisorsArray[] = $item['user_id'];
                                        }
                                    }
                                }
                            }
                            $assignedSupervisors = App\Users::whereIn('id', $assignedSupervisorsArray)->get();
                        @endphp

                        @if (count($assignedSupervisors) > 0)
                            <ul class="management-list">
                                @foreach ($assignedSupervisors as $row)
                                    <li class="management-item">
                                        <i class="bi bi-person-circle"></i>
                                        <span class="name">{{ ucfirst($row->name) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="empty-state">
                                <i class="bi bi-person-x d-block fs-3 mb-2"></i>
                                No supervisor assigned.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Shifts -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-clock text-primary"></i> Assigned Shifts</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $shifts = App\ShiftAssigned::where('site_id', $sites->id)->get();
                        @endphp

                        @if (count($shifts) > 0)
                            <ul class="management-list">
                                @foreach ($shifts as $row)
                                    @php
                                        $times = json_decode($row->shift_time);
                                    @endphp
                                    <li class="management-item">
                                        <i class="bi bi-calendar-event"></i>
                                        <div class="d-flex flex-column">
                                            <span class="name">{{ $row->shift_name }}</span>
                                            <span class="detail">{{ $times->start }} - {{ $times->end }}</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="empty-state">
                                <i class="bi bi-clock-history d-block fs-3 mb-2"></i>
                                No shift assigned.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

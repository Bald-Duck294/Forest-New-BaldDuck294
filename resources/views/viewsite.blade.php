@php
    $hideGlobalFilters = true;
    $hideBackground = true;
@endphp

@extends('layouts.app')

@section('content')
    <style>
        /* Scoped Light Theme Variables */
        .custom-theme-wrapper {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --border: #e2e8f0;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --bg-hover: #f1f5f9;
            --text-main: #0f172a;
            --text-muted: #64748b;

            --btn-back-bg: #ffffff;
            --btn-back-hover: #f1f5f9;

            --badge-active-bg: #dcfce7;
            --badge-active-text: #166534;
            --badge-inactive-bg: #fee2e2;
            --badge-inactive-text: #991b1b;

            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);

            font-family: 'Inter', sans-serif;
            color: var(--text-main);
        }

        /* Scoped Dark Theme Variables */
        html[data-theme="dark"] .custom-theme-wrapper,
        html[data-bs-theme="dark"] .custom-theme-wrapper,
        body.dark .custom-theme-wrapper,
        body.dark-mode .custom-theme-wrapper,
        body.dark-theme .custom-theme-wrapper {
            --primary: #3b82f6;
            --primary-hover: #60a5fa;
            --border: #334155;
            --bg-body: #0f172a;
            --bg-card: #1e293b;
            --bg-hover: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;

            --btn-back-bg: #334155;
            --btn-back-hover: #475569;

            --badge-active-bg: rgba(34, 197, 94, 0.15);
            --badge-active-text: #4ade80;
            --badge-inactive-bg: rgba(239, 68, 68, 0.15);
            --badge-inactive-text: #f87171;

            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.4);
        }

        /* Layout & Typography */
        .custom-theme-wrapper .page-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
            margin-top: 1rem;
        }

        .custom-theme-wrapper .page-title {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            color: var(--text-main);
            letter-spacing: -0.01em;
        }

        .custom-theme-wrapper .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: var(--btn-back-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-main);
            text-decoration: none !important;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .custom-theme-wrapper .btn-back:hover {
            background: var(--btn-back-hover);
            transform: translateX(-2px);
        }

        /* Cards */
        .custom-theme-wrapper .card {
            background: var(--bg-card);
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-md);
            margin-bottom: 24px;
            overflow: hidden;
            transition: background-color 0.3s, border-color 0.3s;
        }

        .custom-theme-wrapper .card-header {
            padding: 16px 24px;
            background: transparent;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .custom-theme-wrapper .card-header h5 {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .custom-theme-wrapper .card-header i {
            color: var(--text-muted);
            font-size: 16px;
        }

        .custom-theme-wrapper .card-body {
            padding: 24px;
        }

        /* Data Display */
        .custom-theme-wrapper .info-group {
            margin-bottom: 20px;
        }

        .custom-theme-wrapper .info-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .custom-theme-wrapper .info-value {
            font-size: 14px;
            color: var(--text-main);
            font-weight: 500;
            line-height: 1.5;
            word-break: break-word;
        }

        /* Structured List Items (Supervisors & Shifts) */
        .custom-theme-wrapper .list-item-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: var(--bg-body);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-main);
            margin-bottom: 8px;
            transition: all 0.2s ease;
        }

        .custom-theme-wrapper .list-item-card:last-child {
            margin-bottom: 0;
        }

        .custom-theme-wrapper .list-item-card:hover {
            border-color: var(--primary);
            background: var(--bg-hover);
        }

        .custom-theme-wrapper .list-item-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--primary);
            font-size: 18px;
        }

        .custom-theme-wrapper .list-item-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .custom-theme-wrapper .list-item-title {
            font-weight: 600;
            font-size: 14px;
            color: var(--text-main);
        }

        .custom-theme-wrapper .list-item-subtitle {
            font-size: 12px;
            color: var(--text-muted);
        }

        .custom-theme-wrapper .badge-pill {
            background: var(--bg-card);
            color: var(--text-main);
            border: 1px solid var(--border);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Empty State */
        .custom-theme-wrapper .empty-state {
            text-align: center;
            color: var(--text-muted);
            padding: 32px 0;
            font-size: 14px;
        }

        .custom-theme-wrapper .empty-state i {
            font-size: 32px;
            display: block;
            margin-bottom: 12px;
            opacity: 0.5;
        }
    </style>

    <div class="custom-theme-wrapper">
        <div class="page-header">
            <a href="javascript:history.back()" class="btn-back" title="Go Back">
                <i class="la la-arrow-left"></i>
            </a>
            <h1 class="page-title">Beat Profile</h1>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">

                <div class="card">
                    <div class="card-header">
                        <h5><i class="la la-info-circle"></i> General Information</h5>
                    </div>
                    <div class="card-body pb-0">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Beat Name</span>
                                    <div class="info-value">{{ $sites->name ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Range Name</span>
                                    <div class="info-value" style="color: var(--primary); font-weight: 600;">
                                        {{ $sites->client_name ?: '-' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Beat Type</span>
                                    <div class="info-value">{{ $sites->siteType ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Late Time Allowance</span>
                                    <div class="info-value">{{ $sites->lateTime ?: '0' }} Minutes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-lg-0">
                    <div class="card-header">
                        <h5><i class="la la-map-marker"></i> Contact & Location</h5>
                    </div>
                    <div class="card-body pb-0">
                        <div class="row">
                            <div class="col-12">
                                <div class="info-group">
                                    <span class="info-label">Address</span>
                                    <div class="info-value">{{ $sites->address ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">City, State</span>
                                    <div class="info-value">{{ $sites->city ?: '-' }}, {{ $sites->state ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Pincode</span>
                                    <div class="info-value">{{ $sites->pincode ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Contact Person</span>
                                    <div class="info-value">{{ $sites->contactPerson ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Contact Number</span>
                                    <div class="info-value">{{ $sites->mobile ?: '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-lg-4">

                <div class="card">
                    <div class="card-header">
                        <h5><i class="la la-user-tie"></i> Supervisors</h5>
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
                            <div class="list-container">
                                @foreach ($assignedSupervisors as $row)
                                    <div class="list-item-card">
                                        <div class="list-item-icon">
                                            <i class="la la-user"></i>
                                        </div>
                                        <div class="list-item-content">
                                            <span class="list-item-title">{{ ucfirst($row->name) }}</span>
                                            <span class="list-item-subtitle">Supervisor</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="la la-user-slash"></i>
                                <span>No supervisors assigned.</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card mb-0">
                    <div class="card-header">
                        <h5><i class="la la-clock"></i> Assigned Shifts</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $shifts = App\ShiftAssigned::where('site_id', $sites->id)->get();
                        @endphp

                        @if (count($shifts) > 0)
                            <div class="list-container">
                                @foreach ($shifts as $row)
                                    @php
                                        $times = json_decode($row->shift_time);
                                    @endphp
                                    <div class="list-item-card">
                                        <div class="list-item-icon">
                                            <i class="la la-calendar-check"></i>
                                        </div>
                                        <div class="list-item-content">
                                            <span class="list-item-title">{{ $row->shift_name }}</span>
                                            <span class="list-item-subtitle">{{ $times->start ?? '-' }} to
                                                {{ $times->end ?? '-' }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="la la-history"></i>
                                <span>No shifts assigned.</span>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

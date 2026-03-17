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

        /* Badges */
        .custom-theme-wrapper .badge-status {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .custom-theme-wrapper .badge-active {
            background: var(--badge-active-bg);
            color: var(--badge-active-text);
        }

        .custom-theme-wrapper .badge-inactive {
            background: var(--badge-inactive-bg);
            color: var(--badge-inactive-text);
        }

        /* Site List (Menu Style) */
        .custom-theme-wrapper .site-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .custom-theme-wrapper .site-item {
            display: block;
        }

        .custom-theme-wrapper .site-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: var(--bg-body);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-main);
            text-decoration: none !important;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .custom-theme-wrapper .site-link-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .custom-theme-wrapper .site-link i {
            color: var(--text-muted);
            transition: color 0.2s ease;
        }

        .custom-theme-wrapper .site-link:hover {
            border-color: var(--primary);
            background: var(--bg-hover);
            color: var(--primary);
            transform: translateY(-1px);
        }

        .custom-theme-wrapper .site-link:hover i {
            color: var(--primary);
        }

        .custom-theme-wrapper .empty-sites {
            text-align: center;
            color: var(--text-muted);
            padding: 32px 0;
            font-size: 14px;
        }

        .custom-theme-wrapper .empty-sites i {
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
            <h1 class="page-title">Range Profile</h1>
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
                                    <span class="info-label">Name</span>
                                    <div class="info-value">{{ $clients->name }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Status</span>
                                    <div class="info-value">
                                        @if ($clients->isActive == 1)
                                            <span class="badge-status badge-active">Active</span>
                                        @else
                                            <span class="badge-status badge-inactive">Inactive</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Email Address</span>
                                    <div class="info-value">{{ $clients->email ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Phone Number</span>
                                    <div class="info-value">{{ $clients->contact ?: '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5><i class="la la-map-marker"></i> Location Details</h5>
                    </div>
                    <div class="card-body pb-0">
                        <div class="row">
                            <div class="col-12">
                                <div class="info-group">
                                    <span class="info-label">Street Address</span>
                                    <div class="info-value">{{ $clients->address ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-group">
                                    <span class="info-label">City</span>
                                    <div class="info-value">{{ $clients->city ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-group">
                                    <span class="info-label">State</span>
                                    <div class="info-value">{{ $clients->state ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-group">
                                    <span class="info-label">Pincode</span>
                                    <div class="info-value">{{ $clients->pincode ?: '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-lg-0">
                    <div class="card-header">
                        <h5><i class="la la-user-tie"></i> Management Contact</h5>
                    </div>
                    <div class="card-body pb-0">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Contact Person</span>
                                    <div class="info-value">{{ $clients->spokesperson ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Relationship Manager</span>
                                    <div class="info-value">{{ $clients->relationManager ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="info-group">
                                    <span class="info-label">Relationship Manager Contact</span>
                                    <div class="info-value">{{ $clients->relationManagerContact ?: '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-lg-4">
                <div class="card h-100 mb-0">
                    <div class="card-header">
                        <h5><i class="la la-building"></i> Associated Sites</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $sites = App\SiteDetails::where('client_id', $clients->id)->get();
                        @endphp

                        @if (count($sites) > 0)
                            <ul class="site-list">
                                @foreach ($sites as $row)
                                    <li class="site-item">
                                        <a href="{{ route('sites.site_view', [$clients->id, $row->id]) }}"
                                            class="site-link">
                                            <div class="site-link-left">
                                                <i class="la la-map-pin"></i>
                                                <span>{{ $row->name }}</span>
                                            </div>
                                            <i class="la la-angle-right"></i>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="empty-sites">
                                <i class="la la-folder-open"></i>
                                <span>No sites associated with this range yet.</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

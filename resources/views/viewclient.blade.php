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

        .view-client-container {
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
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
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

        .badge-status {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.8125rem;
            font-weight: 600;
        }

        .badge-active {
            background: #dcfce7;
            color: #166534;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .site-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .site-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border);
        }

        .site-item:last-child {
            border-bottom: none;
        }

        .site-item i {
            color: var(--primary);
            margin-right: 0.75rem;
        }

        .site-link {
            color: var(--text-main);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .site-link:hover {
            color: var(--primary);
        }

        .empty-sites {
            text-align: center;
            color: var(--text-muted);
            padding: 2rem 0;
        }

        @media (max-width: 992px) {
            .details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="view-client-container">
        <div class="page-header">
            <a href="javascript:history.back()" class="btn-back" title="Go Back">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="page-title">Client Profile</h1>
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
                                    <span class="info-label">Client Name</span>
                                    <div class="info-value">{{ $clients->name }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Status</span>
                                    <div>
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
                                    <div class="info-value">{{ $clients->email }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Phone Number</span>
                                    <div class="info-value">{{ $clients->contact }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-geo-alt text-primary"></i> Location Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="info-group">
                                    <span class="info-label">Street Address</span>
                                    <div class="info-value">{{ $clients->address }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-group">
                                    <span class="info-label">City</span>
                                    <div class="info-value">{{ $clients->city }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-group">
                                    <span class="info-label">State</span>
                                    <div class="info-value">{{ $clients->state }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-group">
                                    <span class="info-label">Pincode</span>
                                    <div class="info-value">{{ $clients->pincode }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-person-badge text-primary"></i> Management Contact</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Contact Person</span>
                                    <div class="info-value">{{ $clients->spokesperson }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Relationship Manager</span>
                                    <div class="info-value">{{ $clients->relationManager }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Relationship Manager Contact</span>
                                    <div class="info-value">{{ $clients->relationManagerContact }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="side-info">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-building text-primary"></i> Associated Sites</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $sites = App\SiteDetails::where('client_id', $clients->id)->get();
                        @endphp

                        @if (count($sites) > 0)
                            <ul class="site-list">
                                @foreach ($sites as $row)
                                    <li class="site-item">
                                        <i class="bi bi-geo-fill"></i>
                                        <a href="{{ route('sites.site_view', [$clients->id, $row->id]) }}" class="site-link">
                                            {{ $row->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="empty-sites">
                                <i class="bi bi-building-exclamation fs-1 d-block mb-2"></i>
                                No sites associated with this client.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

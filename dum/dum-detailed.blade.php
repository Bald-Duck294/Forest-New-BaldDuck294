@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    $user = session('user');
@endphp
@extends('layouts.app')

@section('title', 'Detailed Data View')

@section('content')

    <style>
        /* =========================================
                       SAPPHIRE THEME - DETAILED VIEW
                    ========================================= */
        .detailed-header-btn {
            background-color: var(--bg-card);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .detailed-header-btn:hover {
            background-color: var(--sapphire-primary);
            color: white;
            border-color: var(--sapphire-primary);
        }

        /* Custom Nav Pills */
        .sapphire-nav-pills {
            display: flex;
            gap: 8px;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 12px;
            overflow-x: auto;
            flex-wrap: nowrap;
            scrollbar-width: none;
        }

        .sapphire-nav-pills::-webkit-scrollbar {
            display: none;
        }

        .sapphire-nav-link {
            color: var(--text-muted);
            background-color: transparent;
            border: 1px solid transparent;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            white-space: nowrap;
            transition: all 0.2s ease;
        }

        .sapphire-nav-link:hover {
            color: var(--text-main);
            background-color: var(--bg-card);
        }

        .sapphire-nav-link.active {
            color: white;
            background-color: var(--sapphire-primary);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
        }

        /* Filters */
        .custom-filter-input {
            background-color: var(--bg-body);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.85rem;
            width: 100%;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .custom-filter-input:focus {
            border-color: var(--sapphire-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .input-group-text-sapphire {
            background-color: var(--bg-body);
            color: var(--text-muted);
            border: 1px solid var(--border-color);
            border-right: none;
            border-radius: 8px 0 0 8px;
        }

        .search-input {
            border-left: none;
            border-radius: 0 8px 8px 0;
        }

        /* Table Adjustments */
        .table-sapphire th {
            color: var(--text-muted);
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border-color);
            padding: 1rem;
            background-color: var(--bg-card);
        }

        .table-sapphire td {
            color: var(--text-main);
            font-weight: 500;
            font-size: 0.9rem;
            border-bottom: 1px dashed var(--border-color);
            padding: 1rem;
            vertical-align: middle;
            background-color: transparent;
        }

        .table-sapphire tr:hover td {
            background-color: var(--table-hover);
        }

        .table-sapphire tr:last-child td {
            border-bottom: none;
        }

        /* Soft Badges */
        .badge-soft-pending {
            background: rgba(245, 158, 11, 0.15);
            color: var(--sapphire-warning);
        }

        .badge-soft-success {
            background: rgba(16, 185, 129, 0.15);
            color: var(--sapphire-success);
        }

        .badge-soft-info {
            background: rgba(59, 130, 246, 0.15);
            color: var(--sapphire-primary);
        }

        .badge-soft-neutral {
            background: var(--bg-body);
            color: var(--text-main);
            border: 1px solid var(--border-color);
        }
    </style>

    <div class="container-fluid py-4">

        {{-- Page Header --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--text-main);">Detailed Data Records</h3>
                <p class="mb-0 text-muted" style="font-size: 0.9rem;">View, filter, and export all system records.</p>
            </div>
            <a href="/report-configs/reports-dashboard"
                class="btn detailed-header-btn shadow-sm d-flex align-items-center gap-2 px-4 py-2">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        {{-- Master Category Tabs --}}
        <nav class="sapphire-nav-pills mb-4">
            <a class="sapphire-nav-link {{ $category == 'onduty' ? 'active' : '' }}" href="?category=onduty">
                <i class="bi bi-people me-2"></i>On Duty
            </a>
            <a class="sapphire-nav-link {{ $category == 'criminal' ? 'active' : '' }}" href="?category=criminal">
                <i class="bi bi-hammer me-2"></i>Criminal Activity
            </a>
            <a class="sapphire-nav-link {{ $category == 'events' ? 'active' : '' }}" href="?category=events">
                <i class="bi bi-eye me-2"></i>Events & Monitoring
            </a>
            <a class="sapphire-nav-link {{ $category == 'fire' ? 'active' : '' }}" href="?category=fire">
                <i class="bi bi-fire me-2"></i>Fire Incidents
            </a>
            <a class="sapphire-nav-link {{ $category == 'assets' ? 'active' : '' }}" href="?category=assets">
                <i class="bi bi-shield-check me-2"></i>Assets & Tools
            </a>
            <a class="sapphire-nav-link {{ $category == 'plantations' ? 'active' : '' }}" href="?category=plantations">
                <i class="bi bi-tree me-2"></i>Plantations
            </a>
        </nav>

        {{-- Filters & Search Bar --}}
        <div class="dash-card p-3 mb-4">
            <form method="GET" action="{{ route('reports.detailed') }}" class="row g-3 align-items-end">
                <input type="hidden" name="category" value="{{ $category }}">

                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold mb-2">Search ID, Name, or Location</label>
                    <div class="input-group">
                        <span class="input-group-text input-group-text-sapphire"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="custom-filter-input search-input"
                            placeholder="Search records..." value="{{ $search }}">
                    </div>
                </div>

                @if (in_array($category, ['criminal', 'events']))
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold mb-2">Specific Event Type</label>
                        <select name="sub_type" class="custom-filter-input" style="cursor: pointer;">
                            <option value="">All Types</option>
                            @if ($category == 'criminal')
                                <option value="felling" {{ $subType == 'felling' ? 'selected' : '' }}>Illegal Felling
                                </option>
                                <option value="poaching" {{ $subType == 'poaching' ? 'selected' : '' }}>Poaching</option>
                                <option value="encroachment" {{ $subType == 'encroachment' ? 'selected' : '' }}>
                                    Encroachment</option>
                                <option value="mining" {{ $subType == 'mining' ? 'selected' : '' }}>Mining</option>
                                <option value="storage" {{ $subType == 'storage' ? 'selected' : '' }}>Storage</option>
                                <option value="transport" {{ $subType == 'transport' ? 'selected' : '' }}>Transport
                                </option>
                            @elseif($category == 'events')
                                <option value="sighting" {{ $subType == 'sighting' ? 'selected' : '' }}>Animal Sighting
                                </option>
                                <option value="water_status" {{ $subType == 'water_status' ? 'selected' : '' }}>Water
                                    Status</option>
                                <option value="compensation" {{ $subType == 'compensation' ? 'selected' : '' }}>
                                    Compensation</option>
                            @endif
                        </select>
                    </div>
                @endif

                <div class="col-md-2">
                    <label class="form-label text-muted small fw-bold mb-2">From Date</label>
                    <input type="date" name="from_date" class="custom-filter-input" value="{{ $fromDate }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label text-muted small fw-bold mb-2">To Date</label>
                    <input type="date" name="to_date" class="custom-filter-input" value="{{ $toDate }}">
                </div>

                <div class="col-md-1">
                    <button type="submit" class="btn w-100 fw-bold"
                        style="background-color: var(--sapphire-primary); color: white; padding: 10px; border-radius: 8px;">Filter</button>
                </div>
            </form>
        </div>

        {{-- Data Table --}}
        <div class="dash-card p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-sapphire mb-0">
                    <thead>
                        <tr>
                            @if ($viewType == 'reports')
                                <th class="ps-4">Report ID</th>
                                <th>Report Type</th>
                                <th>Beat / Range</th>
                                <th>Date / Time</th>
                                <th class="text-end pe-4">Status</th>
                            @elseif($viewType == 'assets')
                                <th class="ps-4">Asset ID</th>
                                <th>Category</th>
                                <th>Condition</th>
                                <th>Date Added</th>
                            @elseif($viewType == 'plantations')
                                <th class="ps-4">Code</th>
                                <th>Plantation Name</th>
                                <th>Species</th>
                                <th>Area (Ha)</th>
                                <th class="text-end pe-4">Phase</th>
                            @elseif($viewType == 'onduty')
                                <th class="ps-4">Officer Name</th>
                                <th>Phone Number</th>
                                <th>Assigned Site</th>
                                <th class="text-end pe-4">Status</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $row)
                            <tr>
                                @if ($viewType == 'reports')
                                    <td class="ps-4 fw-bold" style="color: var(--sapphire-primary);">
                                        {{ $row->report_id ?? 'RPT-' . $row->id }}</td>
                                    <td><span class="badge badge-soft-neutral px-2 py-1"><i
                                                class="bi bi-tag me-1"></i>{{ $row->report_type }}</span></td>
                                    <td>{{ $row->beat ?? ($row->range ?? 'Unknown') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y, h:i A') }}</td>
                                    <td class="text-end pe-4">
                                        <span
                                            class="badge {{ $row->status == 'Pending' ? 'badge-soft-pending' : 'badge-soft-success' }} px-3 py-2 rounded-pill">
                                            {{ $row->status }}
                                        </span>
                                    </td>
                                @elseif($viewType == 'assets')
                                    <td class="ps-4 fw-bold" style="color: var(--sapphire-primary);">
                                        AST-{{ $row->id }}</td>
                                    <td>{{ $row->category ?? 'Equipment' }}</td>
                                    <td><span
                                            class="badge badge-soft-neutral px-2 py-1">{{ $row->condition ?? 'N/A' }}</span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y') }}</td>
                                @elseif($viewType == 'plantations')
                                    <td class="ps-4 fw-bold" style="color: var(--sapphire-primary);">{{ $row->code }}
                                    </td>
                                    <td class="fw-bold">{{ $row->name }}</td>
                                    <td>{{ $row->plant_species ?? 'Mixed' }}</td>
                                    <td>{{ $row->area ?? 0 }} Ha</td>
                                    <td class="text-end pe-4"><span
                                            class="badge badge-soft-info px-3 py-2 rounded-pill">{{ ucfirst($row->current_phase) }}</span>
                                    </td>
                                @elseif($viewType == 'onduty')
                                    <td class="ps-4 fw-bold" style="color: var(--sapphire-primary);">{{ $row->name }}
                                    </td>
                                    <td>{{ $row->contact ?? 'N/A' }}</td>
                                    <td><i class="bi bi-geo-alt me-1 text-muted"></i>
                                        {{ $row->site_name ?? 'Floating/Unassigned' }}</td>
                                    <td class="text-end pe-4"><span
                                            class="badge badge-soft-success px-3 py-2 rounded-pill">Present</span></td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3" style="opacity: 0.5;"></i>
                                    <h5 class="fw-bold mb-1">No records found</h5>
                                    <p class="mb-0 small">Try adjusting your filters or search query.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($records->hasPages())
                <div class="p-3 border-top" style="border-color: var(--border-color); background-color: var(--bg-body);">
                    {{ $records->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>

    </div>
@endsection

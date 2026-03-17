@php
    $hideGlobalFilters = true;
    $hideBackground = true;

    // Helper functions for clean sortable column links
    $getSortUrl = function ($column) {
        $order = request('sort_by') == $column && request('sort_order') == 'asc' ? 'desc' : 'asc';
        return request()->fullUrlWithQuery(['sort_by' => $column, 'sort_order' => $order]);
    };

    $getSortIcon = function ($column) {
        if (request('sort_by') != $column) {
            return 'la la-sort opacity-50';
        }
        return request('sort_order') == 'asc'
            ? 'la la-sort-amount-asc text-primary'
            : 'la la-sort-amount-desc text-primary';
    };
@endphp

@extends('layouts.app')

@section('content')
    <style>
        /* Scoped Light Theme Variables */
        .custom-theme-wrapper {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --border: #e2e8f0;
            --bg-card: #ffffff;
            --bg-body: #f8fafc;
            --bg-header: #f1f5f9;
            --bg-hover: #f8fafc;
            --bg-input: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;

            --success: #10b981;
            --success-hover: #059669;

            --badge-registered-bg: #dcfce7;
            --badge-registered-text: #166534;
            --badge-pending-bg: #fef9c3;
            --badge-pending-text: #854d0e;

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
            --bg-card: #1e293b;
            --bg-body: #0f172a;
            --bg-header: #0f172a;
            --bg-hover: #334155;
            --bg-input: #0f172a;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;

            --badge-registered-bg: rgba(34, 197, 94, 0.15);
            --badge-registered-text: #4ade80;
            --badge-pending-bg: rgba(234, 179, 8, 0.15);
            --badge-pending-text: #facc15;

            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.4);
        }

        /* Base Card Styles */
        .custom-theme-wrapper .card {
            margin-top: 1rem !important;
            /* Forces the requested margin */
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-md);
            background: var(--bg-card);
            margin-bottom: 24px;
            transition: background-color 0.3s, border-color 0.3s;
            overflow: hidden;
        }

        /* Header & Filters */
        .custom-theme-wrapper .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border);
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 20px;
        }

        .custom-theme-wrapper .header-title h4 {
            margin: 0 0 4px 0;
            font-weight: 700;
            color: var(--text-main);
            font-size: 1.25rem;
        }

        .custom-theme-wrapper .header-title p {
            margin: 0;
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        /* Renamed to prevent global CSS collisions */
        .custom-theme-wrapper .member-filter-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .custom-theme-wrapper .member-search-form {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
            background: var(--bg-hover);
            padding: 6px;
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        .custom-theme-wrapper .member-search-input,
        .custom-theme-wrapper .member-search-select {
            border-radius: 8px;
            border: 1px solid var(--border) !important;
            padding: 8px 16px;
            font-size: 0.875rem;
            transition: all 0.2s;
            background: var(--bg-input) !important;
            color: var(--text-main) !important;
            outline: none;
            box-shadow: none !important;
        }

        .custom-theme-wrapper .member-search-input:focus,
        .custom-theme-wrapper .member-search-select:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
        }

        /* Buttons */
        .custom-theme-wrapper .btn-premium {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--primary) !important;
            color: #fff !important;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
            font-size: 0.875rem;
            border: none;
            transition: all 0.2s ease;
            text-decoration: none !important;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
        }

        .custom-theme-wrapper .btn-premium:hover {
            background: var(--primary-hover) !important;
            transform: translateY(-1px);
        }

        .custom-theme-wrapper .btn-export {
            background: var(--success) !important;
        }

        .custom-theme-wrapper .btn-export:hover {
            background: var(--success-hover) !important;
        }

        .custom-theme-wrapper .btn-icon-only {
            padding: 8px 12px;
        }

        .custom-theme-wrapper .btn-secondary {
            background: transparent !important;
            color: var(--text-muted) !important;
            border: 1px solid var(--border) !important;
            box-shadow: none;
        }

        .custom-theme-wrapper .btn-secondary:hover {
            background: var(--bg-hover) !important;
            color: var(--text-main) !important;
        }

        /* Table */
        .custom-theme-wrapper .card-body {
            padding: 0;
        }

        .custom-theme-wrapper .table {
            width: 100%;
            margin-bottom: 0;
            color: var(--text-main);
        }

        .custom-theme-wrapper .table thead th {
            background: var(--bg-header);
            border-bottom: 1px solid var(--border);
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 14px 24px;
            white-space: nowrap;
        }

        /* Sortable Columns */
        .custom-theme-wrapper .sortable-col {
            color: var(--text-muted);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s;
        }

        .custom-theme-wrapper .sortable-col:hover {
            color: var(--primary);
        }

        .custom-theme-wrapper .sortable-col i {
            font-size: 14px;
        }

        .custom-theme-wrapper .table tbody td {
            padding: 16px 24px;
            vertical-align: middle;
            color: var(--text-main);
            font-size: 0.875rem;
            border-bottom: 1px solid var(--border);
            background: var(--bg-card);
        }

        .custom-theme-wrapper .table tbody tr:last-child td {
            border-bottom: none;
        }

        .custom-theme-wrapper .table tbody tr:hover td {
            background-color: var(--bg-hover);
        }

        /* Table Components */
        .custom-theme-wrapper .contact-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .custom-theme-wrapper .contact-info i {
            color: var(--text-muted);
            width: 16px;
            text-align: center;
        }

        .custom-theme-wrapper .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .custom-theme-wrapper .status-registered {
            background: var(--badge-registered-bg);
            color: var(--badge-registered-text);
        }

        .custom-theme-wrapper .status-pending {
            background: var(--badge-pending-bg);
            color: var(--badge-pending-text);
        }

        /* Action Buttons Cell */
        .custom-theme-wrapper .actions-cell {
            display: flex;
            gap: 6px;
        }

        .custom-theme-wrapper .btn-action-small {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--text-muted);
            transition: all 0.2s ease;
            text-decoration: none !important;
            cursor: pointer;
        }

        .custom-theme-wrapper .btn-action-small:hover {
            background: var(--bg-hover);
            color: var(--primary);
            border-color: var(--primary);
        }

        .custom-theme-wrapper .btn-action-delete:hover {
            color: #ef4444;
            border-color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
        }

        /* Pagination & Footer */
        .custom-theme-wrapper .pagination-container {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            background: var(--bg-card);
            border-bottom-left-radius: 16px;
            border-bottom-right-radius: 16px;
        }

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

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .custom-theme-wrapper .member-filter-wrapper {
                width: 100%;
                justify-content: space-between;
            }

            .custom-theme-wrapper .member-search-form {
                width: 100%;
            }

            .custom-theme-wrapper .member-search-input {
                flex: 1;
            }
        }
    </style>

    <div class="custom-theme-wrapper">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3 mb-0" role="alert" style="border-radius: 12px;">
                <i class="la la-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <div class="header-title">
                    <h4>Member Registrations</h4>
                    <p>Manage users in the registration table</p>
                </div>

                <div class="member-filter-wrapper">
                    <form action="{{ route('registrations.index') }}" method="GET" class="member-search-form">
                        <select name="status" class="member-search-select">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                            <option value="registered" {{ request('status') == 'registered' ? 'selected' : '' }}>Registered
                            </option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>

                        <input type="text" name="search" class="member-search-input" placeholder="Search members..."
                            value="{{ request('search') }}">

                        <input type="hidden" name="sort_by" value="{{ request('sort_by', 'id') }}">
                        <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">

                        <button type="submit" class="btn-premium btn-icon-only" title="Search">
                            <i class="la la-search"></i>
                        </button>
                        <a href="{{ route('registrations.index') }}" class="btn-premium btn-secondary btn-icon-only"
                            title="Reset Filters">
                            <i class="la la-refresh"></i>
                        </a>
                    </form>

                    <div class="d-flex gap-2">
                        <a href="{{ route('registrations.create') }}" class="btn-premium">
                            <i class="la la-user-plus"></i> Add Member
                        </a>
                        <a href="{{ route('registrations.export') }}" class="btn-premium btn-export">
                            <i class="la la-download"></i> Export
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th>
                                    <a href="{{ $getSortUrl('name') }}" class="sortable-col">
                                        Name <i class="{{ $getSortIcon('name') }}"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ $getSortUrl('contact') }}" class="sortable-col">
                                        Contact Info <i class="{{ $getSortIcon('contact') }}"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ $getSortUrl('department') }}" class="sortable-col">
                                        Department / Designation <i class="{{ $getSortIcon('department') }}"></i>
                                    </a>
                                </th>
                                <th style="width: 140px;">
                                    <a href="{{ $getSortUrl('status') }}" class="sortable-col">
                                        Status <i class="{{ $getSortIcon('status') }}"></i>
                                    </a>
                                </th>
                                <th style="width: 120px; text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($registrations) && count($registrations) > 0)
                                @php $sr = ($registrations->currentPage() - 1) * $registrations->perPage() + 1; @endphp
                                @foreach ($registrations as $row)
                                    <tr>
                                        <td>{{ $sr++ }}</td>
                                        <td>
                                            <div style="font-weight: 600; color: var(--text-main);">
                                                {{ $row->firstName }} {{ $row->lastName }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="contact-info">
                                                <span><i class="la la-phone"></i> {{ $row->mobile ?: 'N/A' }}</span>
                                                @if ($row->email)
                                                    <span style="font-size: 0.8125rem; color: var(--text-muted);">
                                                        <i class="la la-envelope"></i> {{ $row->email }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 500;">{{ $row->department ?: 'N/A' }}</div>
                                            <div style="font-size: 0.8125rem; color: var(--text-muted);">
                                                {{ $row->designation ?: 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            @if ($row->registrationFlag == 1)
                                                <span class="status-badge status-registered">Registered</span>
                                            @else
                                                <span class="status-badge status-pending">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="actions-cell justify-content-center">
                                                <a href="{{ route('registrations.edit', $row->id) }}"
                                                    class="btn-action-small" title="Edit Member">
                                                    <i class="la la-pencil"></i>
                                                </a>
                                                <button onclick="deleteMember('{{ $row->id }}')"
                                                    class="btn-action-small btn-action-delete" title="Delete Member">
                                                    <i class="la la-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <i class="la la-user-times"></i>
                                            <h5 style="color: var(--text-main); font-weight:600;">No members found</h5>
                                            <p>No members matched your search or filter criteria.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if (isset($registrations) && method_exists($registrations, 'links'))
                    <div class="pagination-container">
                        <div style="font-size: 0.875rem; color: var(--text-muted);">
                            Showing <strong>{{ $registrations->firstItem() ?? 0 }}</strong> to
                            <strong>{{ $registrations->lastItem() ?? 0 }}</strong> of
                            <strong>{{ $registrations->total() }}</strong> entries
                        </div>
                        <div>
                            {{ $registrations->appends(request()->input())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // Safe Delete Function with Fallback
            window.deleteMember = function(id) {
                var title = 'Delete Confirmation';
                var msg = 'Are you sure you want to delete this member registration?';

                // Check if SweetAlert is loaded
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: title,
                        text: msg,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Delete'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            executeDelete(id);
                        }
                    });
                } else {
                    if (confirm(msg)) {
                        executeDelete(id);
                    }
                }
            };

            function executeDelete(id) {
                var url = '{{ route('registrations.destroy', [':id']) }}';
                url = url.replace(':id', id);
                window.location = url;
            }

        });
    </script>
@endpush

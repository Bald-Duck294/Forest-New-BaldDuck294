@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    $user = session('user');
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
            --text-main: #0f172a;
            --text-muted: #64748b;

            /* Button Colors */
            --btn-view-bg: #eff6ff;
            --btn-view-text: #3b82f6;
            --btn-edit-bg: #f0fdf4;
            --btn-edit-text: #22c55e;
            --btn-delete-bg: #fef2f2;
            --btn-delete-text: #ef4444;
            --btn-back-bg: #f1f5f9;
            --btn-back-hover: #e2e8f0;

            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);

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
            --text-main: #f8fafc;
            --text-muted: #94a3b8;

            --btn-view-bg: rgba(59, 130, 246, 0.15);
            --btn-view-text: #60a5fa;
            --btn-edit-bg: rgba(34, 197, 94, 0.15);
            --btn-edit-text: #4ade80;
            --btn-delete-bg: rgba(239, 68, 68, 0.15);
            --btn-delete-text: #f87171;
            --btn-back-bg: #334155;
            --btn-back-hover: #475569;

            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }

        /* Base Card Styles */
        .custom-theme-wrapper .card {
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            background: var(--bg-card);
            margin-bottom: 24px;
            margin-top: 1rem;
            transition: background-color 0.3s, border-color 0.3s;
            overflow: hidden;
        }

        /* Responsive Header */
        .custom-theme-wrapper .card-header {
            margin-top: 1rem;
            background: transparent;
            border-bottom: 1px solid var(--border);
            padding: 18px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .custom-theme-wrapper .header-title-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .custom-theme-wrapper .card-header h4 {
            margin: 0;
            font-weight: 700;
            color: var(--text-main);
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .custom-theme-wrapper .header-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .custom-theme-wrapper .card-body {
            padding: 24px;
        }

        /* Table Styling */
        .custom-theme-wrapper .table {
            width: 100%;
            margin-bottom: 0;
            color: var(--text-main);
        }

        .custom-theme-wrapper .table thead th {
            background: var(--bg-header);
            border-bottom: 2px solid var(--border);
            color: var(--text-muted);
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 14px 16px;
            white-space: nowrap;
        }

        .custom-theme-wrapper .table tbody td {
            padding: 16px;
            vertical-align: middle;
            color: var(--text-main);
            font-size: 14px;
            border-bottom: 1px solid var(--border);
        }

        .custom-theme-wrapper .table tbody tr:last-child td {
            border-bottom: none;
        }

        .custom-theme-wrapper .table tbody tr:hover td {
            background-color: var(--bg-hover);
        }

        /* Action Buttons */
        .custom-theme-wrapper .actions-cell {
            display: flex;
            justify-content: center;
            gap: 6px;
            flex-wrap: nowrap;
        }

        .custom-theme-wrapper .btn-action {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: none;
            margin-right: 4px;
            transition: all 0.2s ease;
            cursor: pointer;
            text-decoration: none !important;
            font-size: 16px;
        }

        .custom-theme-wrapper .btn-action i {
            color: inherit;
            line-height: 1;
        }

        .custom-theme-wrapper .btn-view {
            background: var(--btn-view-bg);
            color: var(--btn-view-text);
        }

        .custom-theme-wrapper .btn-view:hover {
            background: var(--btn-view-text);
            color: #fff;
            transform: translateY(-2px);
        }

        .custom-theme-wrapper .btn-edit {
            background: var(--btn-edit-bg);
            color: var(--btn-edit-text);
        }

        .custom-theme-wrapper .btn-edit:hover {
            background: var(--btn-edit-text);
            color: #fff;
            transform: translateY(-2px);
        }

        .custom-theme-wrapper .btn-delete {
            background: var(--btn-delete-bg);
            color: var(--btn-delete-text);
        }

        .custom-theme-wrapper .btn-delete:hover {
            background: var(--btn-delete-text);
            color: #fff;
            transform: translateY(-2px);
        }

        /* Primary Button */
        .custom-theme-wrapper .simple-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary);
            color: #fff !important;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            font-size: 14px;
            border: none;
            transition: all 0.2s ease;
            text-decoration: none !important;
            white-space: nowrap;
            cursor: pointer;
        }

        .custom-theme-wrapper .simple-button:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        /* Back Button */
        .custom-theme-wrapper .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            background: var(--btn-back-bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-main);
            text-decoration: none !important;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .custom-theme-wrapper .btn-back:hover {
            background: var(--btn-back-hover);
            color: var(--text-main);
            transform: translateX(-2px);
        }

        /* Status Pills for Times */
        .custom-theme-wrapper .time-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--bg-hover);
            border: 1px solid var(--border);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-main);
        }

        /* Empty State */
        .custom-theme-wrapper .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }

        .custom-theme-wrapper .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            display: block;
            opacity: 0.5;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .custom-theme-wrapper .card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .custom-theme-wrapper .header-actions {
                width: 100%;
            }

            .custom-theme-wrapper .simple-button {
                flex: 1;
                justify-content: center;
            }
        }
    </style>

    <div class="custom-theme-wrapper">
        <div class="card">
            <div class="card-header">
                <div class="header-title-wrapper">
                    <a href="{{ route('sites.getsites', $client_id) }}" class="btn-back" title="Go Back">
                        <i class="la la-arrow-left"></i>
                    </a>
                    <h4>
                        <i class="la la-clock" style="color: var(--primary);"></i>
                        @if (isset($siteName))
                            {{ $siteName->name }} —
                        @endif Shifts
                    </h4>
                </div>

                <div class="header-actions">
                    @if ($user->role_id != '4')
                        <a href="{{ route('clients.getshiftscreate', [$client_id, $site_id]) }}" class="simple-button">
                            <i class="la la-plus"></i> Add Shift
                        </a>
                    @endif
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width:60px;">#</th>
                                <th>Shift Name</th>
                                <th>Shift Start Time</th>
                                <th>Shift End Time</th>
                                @if ($user->role_id != '4')
                                    <th style="width:140px; text-align:center;">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($shifts) && count($shifts) > 0)
                                @php $sr = 1; @endphp
                                @foreach ($shifts as $row)
                                    @php
                                        $timeData = json_decode($row->shift_time);
                                        $startTime =
                                            $timeData && isset($timeData->start)
                                                ? date('h:i A', strtotime($timeData->start))
                                                : '-';
                                        $endTime =
                                            $timeData && isset($timeData->end)
                                                ? date('h:i A', strtotime($timeData->end))
                                                : '-';
                                    @endphp
                                    <tr>
                                        <td>{{ $sr++ }}</td>
                                        <td style="font-weight: 500;">{{ $row->shift_name }}</td>
                                        <td>
                                            <span class="time-pill">
                                                <i class="la la-sun text-warning"></i> {{ $startTime }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="time-pill">
                                                <i class="la la-moon text-primary"></i> {{ $endTime }}
                                            </span>
                                        </td>
                                        @if ($user->role_id != '4')
                                            <td class="actions-cell">
                                                <a href="{{ route('clients.shift_edit', [$row->id, $client_id, $site_id]) }}"
                                                    class="btn-action btn-edit" title="Edit Shift">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>

                                                <button class="btn-action btn-delete"
                                                    onclick="deleteShift('{{ $row->id }}','{{ $client_id }}','{{ $site_id }}')"
                                                    title="Delete Shift">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="{{ $user->role_id != '4' ? '5' : '4' }}">
                                        <div class="empty-state">
                                            <i class="la la-history"></i>
                                            <h4>No shifts found</h4>
                                            <p>No shifts have been assigned to this site yet.</p>

                                            @if ($user->role_id != '4')
                                                <div class="mt-3">
                                                    <a href="{{ route('clients.getshiftscreate', [$client_id, $site_id]) }}"
                                                        class="simple-button">
                                                        <i class="la la-plus"></i> Add First Shift
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            // Safe Delete Function with Fallback
            window.deleteShift = function(id, client_id, site_id) {
                var title = 'Delete Confirmation';
                var msg = 'Are you sure you want to delete this shift?';

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
                            executeDelete(id, client_id, site_id);
                        }
                    });
                } else {
                    if (confirm(msg)) {
                        executeDelete(id, client_id, site_id);
                    }
                }
            };

            // Extracted logic to keep code DRY
            function executeDelete(id, client_id, site_id) {
                var url = '{{ route('clients.shift_delete', [':id', ':client_id', ':site_id']) }}';
                url = url.replace(':id', id)
                    .replace(':client_id', client_id)
                    .replace(':site_id', site_id);
                window.location = url;
            }

        });
    </script>
@endpush

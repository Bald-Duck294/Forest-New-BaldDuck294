@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    $user = session('user');
@endphp
@extends('layouts.app')

@section('content')
    <style>
        /* Define Theme Variables */
        :root,
        [data-theme="light"],
        [data-bs-theme="light"] {
            /* Light Theme */
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
        }

        /* Dark Theme Support (Manual Class/Attribute) */
        :root[data-theme="dark"],
        [data-bs-theme="dark"],
        .dark,
        .dark-mode,
        body.dark-theme {
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

        /* Dark Theme Support (Auto-detect OS Preference) */
        @media (prefers-color-scheme: dark) {
            :root:not([data-theme="light"]):not([data-bs-theme="light"]):not(.light) {
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
        }

        /* Base Card Styles */
        .card {
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            background: var(--bg-card);
            margin-bottom: 24px;
            color: var(--text-main);
            transition: background-color 0.3s, border-color 0.3s, color 0.3s;
            margin-top: 1rem;
        }

        /* Responsive Header */
        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border);
            padding: 18px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .header-title-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .card-header h4 {
            margin: 0;
            font-weight: 700;
            color: var(--text-main);
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-body {
            padding: 24px;
        }

        /* Table Styling */
        .table {
            width: 100%;
            margin-bottom: 0;
            color: var(--text-main);
        }

        .table thead th {
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

        .table tbody td {
            padding: 16px;
            vertical-align: middle;
            color: var(--text-main);
            font-size: 14px;
            border-bottom: 1px solid var(--border);
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .table tbody tr:hover {
            background-color: var(--bg-hover);
        }

        /* Action Buttons */
        .btn-action {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: none;
            margin-right: 4px;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            text-decoration: none !important;
            font-size: 16px;
        }

        .btn-view {
            background: var(--btn-view-bg);
            color: var(--btn-view-text);
        }

        .btn-view:hover {
            background: var(--btn-view-text);
            color: #fff;
            transform: translateY(-2px);
        }

        .btn-edit {
            background: var(--btn-edit-bg);
            color: var(--btn-edit-text);
        }

        .btn-edit:hover {
            background: var(--btn-edit-text);
            color: #fff;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: var(--btn-delete-bg);
            color: var(--btn-delete-text);
        }

        .btn-delete:hover {
            background: var(--btn-delete-text);
            color: #fff;
            transform: translateY(-2px);
        }

        /* Primary Button */
        .simple-button {
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

        .simple-button:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        /* Back Button */
        .btn-back {
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

        .btn-back:hover {
            background: var(--btn-back-hover);
            color: var(--text-main);
            transform: translateX(-2px);
        }

        .actions-cell {
            display: flex;
            justify-content: center;
            gap: 6px;
            flex-wrap: nowrap;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            display: block;
            opacity: 0.5;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .simple-button {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <div class="card">
        <div class="card-header">
            <div class="header-title-wrapper">
                <a href="javascript:history.back()" class="btn-back" title="Go Back">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h4>
                    <i class="bi bi-people-fill" style="color: var(--primary);"></i>
                    @if (isset($clientName))
                        {{ $clientName->name }} —
                    @endif
                    @if (isset($siteName))
                        {{ ucfirst($siteName->name) }} —
                    @endif
                    Assigned Employees
                </h4>
            </div>

            @if ($user->role_id != 4)
                <a href="{{ route('clients.clientguard_create', [$client_id, $site_id]) }}" class="simple-button">
                    <i class="bi bi-person-plus-fill"></i> Assign Employee
                </a>
            @endif
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width:60px;">#</th>
                            <th>Employee Name</th>
                            <th>Duration</th>
                            <th>Shift</th>
                            <th style="width:140px; text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($guards && count($guards) > 0)
                            @php $sr = 1; @endphp
                            @foreach ($guards as $row)
                                @php
                                    $shiftRows = DB::table('shift_assigned')
                                        ->whereRaw('id in (' . $row->shift_id . ')')
                                        ->get();
                                    $dateRange = json_decode($row->date_range);
                                @endphp
                                <tr>
                                    <td>{{ $sr++ }}</td>
                                    <td><strong>{{ $row->user_name }}</strong></td>
                                    <td>
                                        @if ($dateRange)
                                            <span style="white-space: nowrap;">{{ date('d M Y', strtotime($dateRange->from)) }}</span>
                                            <span style="color: var(--text-muted); margin: 0 4px;">&rarr;</span>
                                            <span style="white-space: nowrap;">{{ date('d M Y', strtotime($dateRange->to)) }}</span>
                                        @else
                                            <span style="color: var(--text-muted);">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @foreach ($shiftRows as $shiftRow)
                                            @php $timing = json_decode($shiftRow->shift_time); @endphp
                                            <div style="margin-bottom: {{ !$loop->last ? '4px' : '0' }}">
                                                <span style="font-weight: 500;">{{ $shiftRow->shift_name }}</span>
                                                @if ($timing)
                                                    <span style="color: var(--text-muted); font-size: 0.9em; margin-left: 4px;">
                                                        ({{ $timing->start }} – {{ $timing->end }})
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </td>
                                    <td class="actions-cell">
                                        <a class="btn-action btn-view"
                                            href="{{ route('clients.clientguard_read', [$client_id, $site_id, $row->user_id]) }}"
                                            title="View Details">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        @if ($user->role_id != 4)
                                            <a class="btn-action btn-edit"
                                                href="{{ route('guards.guard_edit', [$client_id, $row->id]) }}"
                                                title="Edit Employee">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            <button class="btn-action btn-delete"
                                                onclick="deleteGuard('{{ $client_id }}','{{ $site_id }}','{{ $row->id }}')"
                                                title="Release Employee">
                                                <i class="bi bi-person-dash-fill"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="bi bi-people"></i>
                                        <h4>No employees found</h4>
                                        <p>There are no employees currently assigned to this site.</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        async function deleteGuard(client_id, site_id, id) {
            var deleted = await showSweetAlert(
                'Release Confirmation',
                'Are you sure you want to release this employee?',
                'Release', true, 'Cancel'
            );
            if (deleted) {
                var url = '{{ route('clients.guardDelete', [':client_id', ':site_id', ':id']) }}';
                url = url.replace(':client_id', client_id)
                    .replace(':site_id', site_id)
                    .replace(':id', id);
                window.location = url;
            }
        }
    </script>
@endpush

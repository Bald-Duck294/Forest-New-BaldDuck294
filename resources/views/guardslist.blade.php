@php
    $hideGlobalFilters = true;
    $hideGlobalFilters = true;
    $hideBackground = true;
@endphp
@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        /* ==== LIGHT & DARK THEME VARIABLES ==== */
        :root {
            --primary: #2563eb;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --table-header: #f1f5f9;
            --table-hover: #f8fafc;
        }

        /* Target standard bootstrap dark mode attribute or custom dark class */
        [data-bs-theme="dark"],
        body.dark-mode,
        body.dark {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --table-header: #0f172a;
            --table-hover: #334155;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
        }

        /* ==== CORE LAYOUT ==== */
        .main-content-wrapper {
            padding: 24px;
            width: 100%;
            overflow-x: hidden;
        }

        /* ==== KPI CARDS ==== */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .kpi-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            cursor: pointer;
            /* Added for clickability */
            transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.05);
            border-color: var(--primary);
        }

        .kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .kpi-info h6 {
            margin: 0;
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
        }

        .kpi-info h3 {
            margin: 4px 0 0;
            font-size: 24px;
            font-weight: 700;
            color: var(--text-main);
        }

        /* ==== TABLE CARD & FILTERS ==== */
        .card {
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            background: var(--card-bg);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border-color);
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .card-header h4 {
            margin: 0;
            font-weight: 700;
            color: var(--text-main);
            font-size: 18px;
        }

        /* Filter Buttons */
        .filter-group {
            display: flex;
            gap: 8px;
            background: var(--table-header);
            padding: 4px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
        }

        .filter-btn {
            border: none;
            background: transparent;
            padding: 6px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-btn.active {
            background: var(--card-bg);
            color: var(--primary);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
        }

        /* ==== TABLE STYLING ==== */
        .card-body {
            padding: 24px;
        }

        .table {
            color: var(--text-main);
        }

        .table thead th {
            background: var(--table-header) !important;
            border-bottom: 1px solid var(--border-color) !important;
            color: var(--text-muted) !important;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            padding: 14px 16px;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 16px;
            vertical-align: middle;
            color: var(--text-main);
            font-size: 14px;
            border-bottom: 1px solid var(--border-color) !important;
        }

        .table tbody tr:hover td {
            background-color: var(--table-hover) !important;
            color: var(--text-main);
        }

        .table tbody tr:last-child td {
            border-bottom: none !important;
        }

        .table a.employee-name {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }

        .table a.employee-name:hover {
            text-decoration: underline;
        }

        /* Badges */
        .badge-status {
            padding: 6px 12px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-success {
            background: #dcfce7;
            color: #16a34a;
        }

        .badge-danger {
            background: #fef2f2;
            color: #dc2626;
        }

        /* Action Button */
        .btn-action-view {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 6px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: background 0.2s;
        }

        .btn-action-view:hover {
            background: #1d4ed8;
            color: #fff;
        }

        /* Export Button */
        .btn-export {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
        }

        .btn-export:hover {
            background: #1d4ed8;
            color: #fff;
        }

        /* DataTables Dark Mode Overrides */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info {
            color: var(--text-main) !important;
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            background: var(--card-bg);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            padding: 4px 8px;
            outline: none;
            border-radius: 6px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: var(--text-main) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary) !important;
            color: white !important;
            border: none !important;
            border-radius: 6px;
        }
    </style>
@endpush

@section('content')
    <?php $company = session('company'); ?>

    <div class="main-content-wrapper">

        <div class="kpi-row">
            <div class="kpi-card" onclick="clearFilters()">
                <div class="kpi-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;"><i
                        class="bi bi-people-fill"></i></div>
                <div class="kpi-info">
                    <h6>Total Users</h6>
                    <h3>{{ $totalUsersCount ?? 0 }}</h3>
                </div>
            </div>

            <div class="kpi-card" onclick="filterRole('Admin')">
                <div class="kpi-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;"><i
                        class="bi bi-shield-lock-fill"></i></div>
                <div class="kpi-info">
                    <h6>Admins</h6>
                    <h3>{{ $adminsCount ?? 0 }}</h3>
                </div>
            </div>

            <div class="kpi-card" onclick="filterRole('Supervisor')">
                <div class="kpi-icon" style="background: rgba(217, 119, 6, 0.1); color: #d97706;"><i
                        class="bi bi-person-badge-fill"></i></div>
                <div class="kpi-info">
                    <h6>Supervisors</h6>
                    <h3>{{ $supervisorsCount ?? 0 }}</h3>
                </div>
            </div>

            <div class="kpi-card"
                onclick="filterTable('Unassigned'); $('.filter-btn').removeClass('active'); $('#btn-unassigned').addClass('active');">
                <div class="kpi-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;"><i
                        class="bi bi-person-dash-fill"></i></div>
                <div class="kpi-info">
                    <h6>Unassigned</h6>
                    <h3>{{ count($unassigned ?? []) }}</h3>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4>Employees</h4>

                <div class="d-flex align-items-center gap-3">
                    <div class="filter-group">
                        <button id="btn-all" class="filter-btn active"
                            onclick="filterTable(''); filterRole('');">All</button>
                        <button id="btn-assigned" class="filter-btn" onclick="filterTable('Assigned')">Assigned</button>
                        <button id="btn-unassigned" class="filter-btn"
                            onclick="filterTable('Unassigned')">Unassigned</button>
                    </div>

                    <a href="{{ route('assigned.export') }}" class="btn-export">
                        <i class="bi bi-download"></i> Export
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="employeeTable">
                        <thead>
                            <tr>
                                <th style="width: 60px;">SR. NO.</th>
                                <th>EMPLOYEE NAME</th>
                                <th>ROLE</th>
                                <th>SITE ASSIGN</th>
                                <th>CONTACT</th>
                                <th>DURATION</th>
                                <th>SHIFT</th>
                                <th>STATUS</th>
                                <th style="display:none;">ASSIGNMENT_FILTER</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sr = 1; ?>

                            {{-- === PRESENT / LATE / ABSENT (ASSIGNED) === --}}
                            @php
                                $assignedUsers = collect();
                                if (isset($present)) {
                                    $assignedUsers = $assignedUsers->concat($present);
                                }
                                if (isset($late)) {
                                    $assignedUsers = $assignedUsers->concat($late);
                                }
                                if (isset($absent)) {
                                    $assignedUsers = $assignedUsers->concat($absent);
                                }
                                $assignedUsers = $assignedUsers->unique('id');
                            @endphp

                            @foreach ($assignedUsers as $row)
                                @php
                                    $isActive = isset($row->showUser) && $row->showUser == 1;
                                @endphp
                                <tr>
                                    <td>{{ $sr++ }}</td>
                                    <td><a class="employee-name"
                                            href="{{ route('clients.clientguard_read', [0, 0, $row->id]) }}">{{ $row->name ?? $row->user_name }}</a>
                                    </td>
                                    <td>
                                        <span class="text-muted fw-bold"
                                            style="font-size: 12px; text-transform: uppercase;">
                                            @if ($row->role_id == 2)
                                                Admin
                                            @elseif($row->role_id == 3)
                                                Supervisor
                                            @else
                                                Guard
                                            @endif
                                        </span>
                                    </td>
                                    <td>{{ $row->site_name ?? 'N/A' }}</td>
                                    <td>{{ $row->contact ?? 'N/A' }}</td>
                                    <td>
                                        @if (isset($row->date_range))
                                            <?php $range = json_decode($row->date_range); ?>
                                            {{ date('d M Y', strtotime($range->from)) }} <br>to<br>
                                            {{ date('d M Y', strtotime($range->to)) }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $row->shift_name ?? 'N/A' }}</td>
                                    <td>
                                        @if ($isActive)
                                            <span class="badge-status badge-success">Active</span>
                                        @else
                                            <span class="badge-status badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td style="display:none;">Assigned</td>
                                    <td><a href="{{ route('clients.clientguard_read', [0, 0, $row->id]) }}"
                                            class="btn-action-view">View</a></td>
                                </tr>
                            @endforeach

                            {{-- === UNASSIGNED === --}}
                            @if (isset($unassigned))
                                @foreach ($unassigned as $row)
                                    @php
                                        $isActive = isset($row->showUser) && $row->showUser == 1;
                                    @endphp
                                    <tr>
                                        <td>{{ $sr++ }}</td>
                                        <td><a class="employee-name"
                                                href="{{ route('clients.clientguard_read', [0, 0, $row->id]) }}">{{ $row->name }}</a>
                                        </td>
                                        <td>
                                            <span class="text-muted fw-bold"
                                                style="font-size: 12px; text-transform: uppercase;">
                                                @if ($row->role_id == 2)
                                                    Admin
                                                @elseif($row->role_id == 3)
                                                    Supervisor
                                                @else
                                                    Guard
                                                @endif
                                            </span>
                                        </td>
                                        <td><span class="text-muted fst-italic">No Site Linked</span></td>
                                        <td>{{ $row->contact ?? 'N/A' }}</td>
                                        <td>N/A</td>
                                        <td>N/A</td>
                                        <td>
                                            @if ($isActive)
                                                <span class="badge-status badge-success">Active</span>
                                            @else
                                                <span class="badge-status badge-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td style="display:none;">Unassigned</td>
                                        <td><a href="{{ route('clients.clientguard_read', [0, 0, $row->id]) }}"
                                                class="btn-action-view">View</a></td>
                                    </tr>
                                @endforeach
                            @endif

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        let table;
        $(document).ready(function() {
            table = $('#employeeTable').DataTable({
                "pageLength": 10,
                "lengthMenu": [10, 25, 50, 100],
                "language": {
                    "lengthMenu": "Show _MENU_ entries",
                    "search": "Search:"
                },
                "columnDefs": [{
                        "orderable": false,
                        "targets": 9
                    }, // Action column is now index 9
                    {
                        "visible": false,
                        "targets": 8
                    } // Hidden Assignment filter is now index 8
                ]
            });

            // Filter button UI states
            $('.filter-btn').on('click', function() {
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');
            });
        });

        // Search the exact Role string in the 3rd Column (index 2)
        function filterRole(role) {
            if (table) {
                // regex strict match '^Admin$' to prevent partial overlaps
                let regexQuery = role ? '^' + role + '$' : '';
                table.column(2).search(regexQuery, true, false).draw();
            }
        }

        // Search the hidden Assignment string in the 9th Column (index 8)
        function filterTable(status) {
            if (table) {
                table.column(8).search(status).draw();
            }
        }

        // Reset all custom filters when clicking Total Users
        function clearFilters() {
            if (table) {
                table.search('').columns().search('').draw();
                $('.filter-btn').removeClass('active');
                $('#btn-all').addClass('active');
            }
        }
    </script>
@endpush

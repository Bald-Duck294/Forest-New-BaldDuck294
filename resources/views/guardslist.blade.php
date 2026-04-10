@php
    $hideGlobalFilters = true;
    $hideBackground = true;
@endphp
@extends('layouts.app')
@section('title', get_label('label_all_user', 'All Users'))
@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* =========================================
                                                                                               LOCAL COMPONENT STYLES
                                                                                               (Hooked to Global Sapphire Variables)
                                                                                            ========================================= */

        /* Cards */
        .dash-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }

        /* Interactive Hover Lift */
        .hover-lift {
            cursor: pointer;
        }

        .hover-lift:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
            border-color: var(--sapphire-primary);
        }

        /* KPI Specifics */
        .kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .kpi-info h6 {
            margin: 0;
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .kpi-info h3 {
            margin: 4px 0 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main);
            line-height: 1;
        }

        /* Action Buttons */
        .btn-sapphire-outline {
            background-color: transparent;
            color: var(--text-main);
            border: 1px solid var(--border-color);
            font-weight: 500;
            padding: 6px 14px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            text-decoration: none;
        }

        .btn-sapphire-outline:hover {
            background-color: var(--table-hover);
            color: var(--sapphire-primary);
            border-color: var(--sapphire-primary);
        }

        .btn-view-sm {
            background: var(--table-hover);
            color: var(--sapphire-primary);
            border: 1px solid var(--border-color);
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-view-sm:hover {
            background: var(--sapphire-primary);
            color: #ffffff;
            border-color: var(--sapphire-primary);
        }

        /* Filter Toggle Group */
        .filter-group {
            display: inline-flex;
            background: var(--bg-body);
            padding: 4px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .filter-btn {
            background: transparent;
            color: var(--text-muted);
            border: none;
            padding: 6px 16px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .filter-btn.active {
            background: var(--bg-card);
            color: var(--sapphire-primary);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        /* Soft Badges */
        .badge-soft {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-soft-primary {
            background: rgba(59, 130, 246, 0.15);
            color: var(--sapphire-primary);
        }

        .badge-soft-success {
            background: rgba(16, 185, 129, 0.15);
            color: var(--sapphire-success);
        }

        .badge-soft-warning {
            background: rgba(245, 158, 11, 0.15);
            color: var(--sapphire-warning);
        }

        .badge-soft-danger {
            background: rgba(239, 68, 68, 0.15);
            color: var(--sapphire-danger);
        }

        .badge-soft-muted {
            background: rgba(100, 116, 139, 0.15);
            color: var(--text-muted);
        }

        /* Tables */
        .dash-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .dash-table th {
            color: var(--text-muted) !important;
            font-weight: 600;
            font-size: 0.75rem;
            border-bottom: 1px solid var(--border-color) !important;
            padding: 1rem;
            background-color: var(--bg-card) !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .dash-table td {
            color: var(--text-main);
            font-weight: 500;
            font-size: 0.85rem;
            border-bottom: 1px dashed var(--border-color) !important;
            padding: 1rem;
            vertical-align: middle;
            background-color: transparent !important;
        }

        .dash-table tr:hover td {
            background-color: var(--table-hover) !important;
        }

        .dash-table tr:last-child td {
            border-bottom: none !important;
        }

        .employee-name-link {
            font-weight: 600;
            color: var(--sapphire-primary);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .employee-name-link:hover {
            color: var(--text-main);
            text-decoration: underline;
        }

        /* DataTables Sapphire Integration */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            color: var(--text-muted) !important;
            font-size: 0.85rem;
            padding: 0 1rem;
            margin-top: 1rem;
            margin-bottom: 1rem;
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            background-color: var(--bg-body);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 4px 8px;
            outline: none;
            transition: border-color 0.2s ease;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--sapphire-primary);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: var(--text-main) !important;
            border: 1px solid transparent !important;
            border-radius: 6px;
            padding: 4px 10px;
            margin: 0 2px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--table-hover) !important;
            border-color: var(--border-color) !important;
            color: var(--sapphire-primary) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--sapphire-primary) !important;
            color: white !important;
            border: none !important;
        }
    </style>
@endpush

@section('content')
    <?php $company = session('company'); ?>

    <div class="container-fluid py-4">

        {{-- COMPACT HEADER --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="fw-bold mb-1" style="color: var(--text-main);">
                    {{ Str::plural(get_label('label_user', 'User')) }}
                </h4>
                <p class="mb-0" style="color: var(--text-muted); font-size: 0.85rem;">
                    Manage user roles, site assignments, and operational statuses.
                </p>
            </div>
            <div>
                <a href="{{ route('assigned.export') }}" class="btn-sapphire-outline shadow-sm">
                    <i class="bi bi-download"></i> Export Data
                </a>
            </div>
        </div>

        {{-- KPI CARDS --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="dash-card hover-lift p-3 d-flex align-items-center gap-3" onclick="clearFilters()">
                    <div class="kpi-icon badge-soft-primary"><i class="bi bi-people-fill"></i></div>
                    <div class="kpi-info">
                        <h6>Total {{ Str::plural(get_label('label_user', 'User')) }}</h6>
                        <h3>{{ $totalUsersCount ?? 0 }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="dash-card hover-lift p-3 d-flex align-items-center gap-3" onclick="filterRole('Admin')">
                    <div class="kpi-icon badge-soft-success"><i class="bi bi-shield-lock-fill"></i></div>
                    <div class="kpi-info">
                        <h6>Admins</h6>
                        <h3>{{ $adminsCount ?? 0 }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="dash-card hover-lift p-3 d-flex align-items-center gap-3" onclick="filterRole('Supervisor')">
                    <div class="kpi-icon badge-soft-warning"><i class="bi bi-person-badge-fill"></i></div>
                    <div class="kpi-info">
                        <h6>Supervisors</h6>
                        <h3>{{ $supervisorsCount ?? 0 }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="dash-card hover-lift p-3 d-flex align-items-center gap-3"
                    onclick="filterTable('Unassigned'); $('.filter-btn').removeClass('active'); $('#btn-unassigned').addClass('active');">
                    <div class="kpi-icon badge-soft-danger"><i class="bi bi-person-dash-fill"></i></div>
                    <div class="kpi-info">
                        <h6>Unassigned</h6>
                        <h3>{{ count($unassigned ?? []) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE CARD --}}
        <div class="dash-card p-0 overflow-hidden">

            <div class="p-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3"
                style="border-bottom: 1px solid var(--border-color);">
                <h5 class="fw-bold mb-0 ms-2" style="color: var(--text-main);">
                    {{ get_label('label_user', 'User') }} Directory
                </h5>

                <div class="filter-group shadow-sm">
                    <button id="btn-all" class="filter-btn active" onclick="filterTable(''); filterRole('');">All</button>
                    <button id="btn-assigned" class="filter-btn" onclick="filterTable('Assigned')">Assigned</button>
                    <button id="btn-unassigned" class="filter-btn" onclick="filterTable('Unassigned')">Unassigned</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table dash-table" id="employeeTable">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 60px;">#</th>
                            <th>{{ get_label('label_user', 'Users') }} Name</th>
                            <th>Role</th>
                            <th>{{ get_label('label_site', 'Site') }} Assigned</th>
                            <th>Contact</th>
                            <th>Duration</th>
                            <th>Shift</th>
                            <th>Status</th>
                            <th style="display:none;">Assignment_Filter</th>
                            <th class="text-end pe-4">Action</th>
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
                                <td class="ps-4 fw-semibold" style="color: var(--text-muted);">{{ $sr++ }}</td>
                                <td>
                                    <a class="employee-name-link"
                                        href="{{ route('clients.clientguard_read', [0, 0, $row->id]) }}">
                                        {{ $row->name ?? $row->user_name }}
                                    </a>
                                </td>
                                <td>
                                    @if ($row->role_id == 7)
                                        <span class="badge-soft badge-soft-success">Admin</span>
                                    @elseif($row->role_id == 3)
                                        <span class="badge-soft badge-soft-warning">Guard</span>
                                    @elseif($row->role_id == 1)
                                        <span class="badge-soft badge-soft-warning">SuperAdmin</span>
                                    @elseif($row->role_id == 2)
                                        <span class="badge-soft badge-soft-warning">supervisor</span>
                                    @else
                                        <span class="badge-soft badge-soft-primary">Guard</span>
                                    @endif
                                </td>
                                <td>{{ $row->site_name ?? 'N/A' }}</td>
                                <td class="font-monospace">{{ $row->contact ?? 'N/A' }}</td>
                                <td>
                                    @if (isset($row->date_range))
                                        <?php $range = json_decode($row->date_range); ?>
                                        <div style="font-size: 0.8rem; line-height: 1.4;">
                                            <span
                                                style="color: var(--text-main);">{{ date('d M Y', strtotime($range->from)) }}</span>
                                            <br><span style="color: var(--text-muted); font-size: 0.7rem;">to</span><br>
                                            <span
                                                style="color: var(--text-main);">{{ date('d M Y', strtotime($range->to)) }}</span>
                                        </div>
                                    @else
                                        <span style="color: var(--text-muted);">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $row->shift_name ?? 'N/A' }}</td>
                                <td>
                                    @if ($isActive)
                                        <span class="badge-soft badge-soft-success"><i class="bi bi-circle-fill me-1"
                                                style="font-size: 0.4rem;"></i> Active</span>
                                    @else
                                        <span class="badge-soft badge-soft-danger"><i class="bi bi-circle-fill me-1"
                                                style="font-size: 0.4rem;"></i> Inactive</span>
                                    @endif
                                </td>
                                <td style="display:none;">Assigned</td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('clients.clientguard_read', [0, 0, $row->id]) }}"
                                        class="btn-view-sm">View</a>
                                </td>
                            </tr>
                        @endforeach

                        {{-- === UNASSIGNED === --}}
                        @if (isset($unassigned))
                            @foreach ($unassigned as $row)
                                @php
                                    $isActive = isset($row->showUser) && $row->showUser == 1;
                                @endphp
                                <tr>
                                    <td class="ps-4 fw-semibold" style="color: var(--text-muted);">{{ $sr++ }}</td>
                                    <td>
                                        <a class="employee-name-link"
                                            href="{{ route('clients.clientguard_read', [0, 0, $row->id]) }}">
                                            {{ $row->name }}
                                        </a>
                                    </td>
                                    <td>
                                        @if ($row->role_id == 7)
                                            <span class="badge-soft badge-soft-success">Admin</span>
                                        @elseif($row->role_id == 2)
                                            <span class="badge-soft badge-soft-warning">Supervisor</span>
                                        @else
                                            <span class="badge-soft badge-soft-primary">Guard</span>
                                        @endif
                                    </td>
                                    <td><span style="color: var(--text-muted); font-style: italic;">No Site Linked</span>
                                    </td>
                                    <td class="font-monospace">{{ $row->contact ?? 'N/A' }}</td>
                                    <td><span style="color: var(--text-muted);">N/A</span></td>
                                    <td><span style="color: var(--text-muted);">N/A</span></td>
                                    <td>
                                        @if ($isActive)
                                            <span class="badge-soft badge-soft-success"><i class="bi bi-circle-fill me-1"
                                                    style="font-size: 0.4rem;"></i> Active</span>
                                        @else
                                            <span class="badge-soft badge-soft-danger"><i class="bi bi-circle-fill me-1"
                                                    style="font-size: 0.4rem;"></i> Inactive</span>
                                        @endif
                                    </td>
                                    <td style="display:none;">Unassigned</td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('clients.clientguard_read', [0, 0, $row->id]) }}"
                                            class="btn-view-sm">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        @endif

                    </tbody>
                </table>
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
                    }, // Action column
                    {
                        "visible": false,
                        "targets": 8
                    } // Hidden Assignment filter
                ],
                "drawCallback": function(settings) {
                    // Ensures styling remains after pagination/search
                    $('.dataTables_paginate > .pagination').addClass('pagination-sm');
                }
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
                // Remove the HTML tags from the search by doing a raw text search, DataTables is smart enough
                // But since we use badges, we can just search the text inside
                let regexQuery = role ? role : '';
                table.column(2).search(regexQuery, false, true).draw();
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

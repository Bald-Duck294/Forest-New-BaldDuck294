@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    // $user = session('user');
@endphp
@extends('layouts.app')

@section('title', get_label('label_patrol_logs', 'Patrol Logs'))

@section('content')

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        /* =========================================
                   LOCAL COMPONENT STYLES
                   (Hooked to Global Sapphire Variables)
                ========================================= */

        /* View Toggle Buttons */
        .view-toggle {
            display: inline-flex;
            background: var(--bg-body);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 4px;
        }

        .view-toggle-btn {
            background: transparent;
            color: var(--text-muted);
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .view-toggle-btn:hover {
            color: var(--text-main);
        }

        .view-toggle-btn.active {
            background: var(--sapphire-primary);
            color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Custom Form Inputs */
        .custom-input {
            background-color: var(--bg-body);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.9rem;
            width: 100%;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .custom-input:focus {
            border-color: var(--sapphire-primary);
            background-color: var(--bg-body);
            color: var(--text-main);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        html[data-bs-theme="dark"] .custom-input {
            color-scheme: dark;
        }

        /* =========================================
                   SELECT2 SAPPHIRE THEME OVERRIDES
                ========================================= */
        /* Hide native multi-select box before Select2 initializes */
        select[multiple] {
            max-height: 42px;
            overflow: hidden;
        }

        .select2-container--default .select2-selection--multiple {
            background-color: var(--bg-body) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 8px !important;
            min-height: 40px;
            padding: 2px 4px;
            display: flex;
            align-items: center;
        }

        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: var(--sapphire-primary) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: var(--bg-card) !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-main) !important;
            border-radius: 6px;
            margin-top: 4px;
            padding: 2px 6px;
            font-size: 0.85rem;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: var(--sapphire-danger) !important;
            margin-right: 6px;
            border-right: 1px solid var(--border-color);
            padding-right: 4px;
        }

        .select2-dropdown {
            background-color: var(--bg-card) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            z-index: 9999;
        }

        .select2-container--default .select2-results__option {
            color: var(--text-main) !important;
            padding: 8px 12px;
            font-size: 0.9rem;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--sapphire-primary) !important;
            color: white !important;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: var(--table-hover) !important;
            color: var(--sapphire-primary) !important;
        }

        .select2-search--dropdown .select2-search__field {
            background-color: var(--bg-body) !important;
            color: var(--text-main) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 6px;
        }

        /* Action Buttons */
        .btn-sapphire {
            background-color: var(--sapphire-primary);
            color: #ffffff;
            border: none;
            font-weight: 500;
            padding: 8px 20px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-sapphire:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            color: #ffffff;
        }

        .btn-sapphire-outline {
            background-color: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border-color);
            font-weight: 500;
            padding: 8px 20px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-sapphire-outline:hover {
            background-color: var(--table-hover);
            color: var(--text-main);
            border-color: var(--text-muted);
        }

        /* Soft Badges */
        .badge-soft {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
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

        /* Interactive Hover Lift */
        .hover-lift {
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }

        .hover-lift:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
            border-color: var(--sapphire-primary);
        }

        /* View Switcher Animation */
        .view-section {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .view-section.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <div class="container-fluid py-4">

        {{-- COMBINED HEADER & FILTER CARD --}}
        <div class="dash-card mb-4 p-0 overflow-visible">

            {{-- Inner Header --}}
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center p-4 border-bottom"
                style="border-color: var(--border-color) !important;">
                <div>
                   <h4 class="fw-bold mb-1" style="color: var(--text-main);">
    {{ get_label('label_patrol_logs', 'Patrol Logs') }} ({{ ucfirst($flag ?? 'All') }})
</h4>
                    <p class="mb-0" style="color: var(--text-muted); font-size: 0.85rem;">Overview of
                        {{ strtolower($flag ?? 'all') }} patrol sessions across all ranges.</p>
                </div>

                {{-- Grid / Table View Toggle --}}
                <div class="view-toggle shadow-sm mt-3 mt-md-0">
                    <button class="view-toggle-btn active" id="btnGrid" onclick="setView('grid')">
                        <i class="bi bi-grid-fill me-1"></i> Grid
                    </button>
                    <button class="view-toggle-btn" id="btnTable" onclick="setView('table')">
                        <i class="bi bi-list-ul me-1"></i> List
                    </button>
                </div>
            </div>

            {{-- Filters Body --}}
            <div class="p-4">
                <form method="GET" action="{{ route('patrolling.log', $flag ?? 'all') }}" class="row g-3 align-items-end">

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold" style="color: var(--text-muted);">From</label>
                        <input type="datetime-local" name="date_from" value="{{ request('date_from') }}"
                            class="custom-input">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold" style="color: var(--text-muted);">To</label>
                        <input type="datetime-local" name="date_to" value="{{ request('date_to') }}" class="custom-input">
                    </div>

                    <div class="col-md-3">
                      <label class="form-label small fw-semibold" style="color: var(--text-muted);">
    {{ get_label('label_client', 'Client') }}
</label>
                        <select name="client_id[]" id="selectRange" class="w-100" multiple="multiple">
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}"
                                    {{ in_array($client->id, (array) request('client_id')) ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3" id="beatWrapper">
                        <label class="form-label small fw-semibold" style="color: var(--text-muted);">Beat</label>
                        <select name="site_id[]" id="selectSite" class="w-100" multiple="multiple">
                            @foreach ($sites as $site)
                                <option value="{{ $site->id }}"
                                    {{ in_array($site->id, (array) request('site_id')) ? 'selected' : '' }}>
                                    {{ $site->name }} ({{ $site->client_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3" id="userWrapper">
                        <label class="form-label small fw-semibold" style="color: var(--text-muted);">User</label>
                        <select name="user_id[]" id="selectUser" class="w-100" multiple="multiple">
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}"
                                    {{ in_array($u->id, (array) request('user_id')) ? 'selected' : '' }}>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold" style="color: var(--text-muted);">Method</label>
                        <select name="method[]" id="selectMethod" class="w-100" multiple="multiple">
                            <option value="vehicle" {{ in_array('vehicle', (array) request('method')) ? 'selected' : '' }}>
                                Vehicle</option>
                            <option value="foot" {{ in_array('foot', (array) request('method')) ? 'selected' : '' }}>Foot
                            </option>
                            <option value="bicycle" {{ in_array('bicycle', (array) request('method')) ? 'selected' : '' }}>
                                Bicycle</option>
                            <option value="other" {{ in_array('other', (array) request('method')) ? 'selected' : '' }}>
                                Other</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold" style="color: var(--text-muted);">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="custom-input"
                            placeholder="Search records...">
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn-sapphire flex-grow-1 justify-content-center">
                            <i class="bi bi-funnel"></i> Apply
                        </button>
                        <a href="{{ route('patrolling.log', $flag ?? 'all') }}"
                            class="btn-sapphire-outline flex-grow-1 justify-content-center" title="Reset Filters">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    </div>

                    <input type="hidden" name="per_page" value="{{ request('per_page', 50) }}">
                </form>
            </div>
        </div>

        {{-- =========================================
         GRID VIEW (CARDS)
    ========================================= --}}
        <div id="gridView" class="view-section active">
            <div class="row g-3">
                @forelse($logs as $log)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="dash-card hover-lift h-100 d-flex flex-column p-4">

                            {{-- Card Header --}}
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                                        style="width:48px; height:48px; background-color: var(--bg-body); border: 1px solid var(--border-color); color: var(--sapphire-primary);">
                                        <i class="bi bi-shield-check fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0" style="color: var(--text-main);">
                                            {{ $log->patrolSession->user->name ?? 'N/A' }}</h6>
                                        <small style="color: var(--text-muted);"><i class="bi bi-geo-alt opacity-75"></i>
                                            {{ $log->patrolSession->site->name ?? 'N/A' }}</small>
                                    </div>
                                </div>
                                <span class="badge-soft badge-soft-primary">
                                    {{ ucwords(str_replace('_', ' ', $log->type)) }}
                                </span>
                            </div>

                            {{-- Card Body --}}
                            <div class="flex-grow-1 p-3 rounded mb-3"
                                style="background: var(--bg-body); border: 1px solid var(--border-color);">
                                <small
                                    style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">Notes
                                    & Observations</small>
                                <p class="mb-0 mt-1"
                                    style="color: var(--text-main); font-size: 0.85rem; line-height: 1.5;">
                                    {{ $log->notes ?: 'No notes recorded for this patrol log.' }}
                                </p>
                            </div>

                            {{-- Card Footer --}}
                            <div class="d-flex justify-content-between align-items-center mt-auto pt-2"
                                style="border-top: 1px dashed var(--border-color);">
                                <div style="color: var(--text-muted); font-size: 0.85rem;">
                                    <i class="bi bi-clock-history me-1"></i>
                                    {{ $log->created_at ? $log->created_at->format('d M, Y h:i A') : 'N/A' }}
                                </div>
                                <a href="{{ route('patrolling.log.details', $log->id) }}"
                                    class="btn-sapphire-outline py-1 px-3" style="font-size: 0.85rem;">
                                    View Details
                                </a>
                            </div>

                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-inbox fs-1 d-block mb-3" style="color: var(--text-muted); opacity: 0.5;"></i>
                        <h5 style="color: var(--text-muted);">No patrol log records found.</h5>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- =========================================
         TABLE VIEW (LIST)
    ========================================= --}}
        <div id="tableView" class="view-section">
            <div class="dash-card p-0 overflow-hidden">

                {{-- Table Tools --}}
                <div class="d-flex justify-content-between align-items-center p-3"
                    style="border-bottom: 1px solid var(--border-color); background: var(--bg-body);">
                    <form method="GET" action="{{ route('patrolling.log', $flag ?? 'all') }}"
                        class="d-flex align-items-center gap-2 m-0">
                        {{-- Retain hidden filters --}}
                        <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                        <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        @foreach ((array) request('client_id') as $cid)
                            <input type="hidden" name="client_id[]" value="{{ $cid }}">
                        @endforeach
                        @foreach ((array) request('site_id') as $sid)
                            <input type="hidden" name="site_id[]" value="{{ $sid }}">
                        @endforeach
                        @foreach ((array) request('user_id') as $uid)
                            <input type="hidden" name="user_id[]" value="{{ $uid }}">
                        @endforeach
                        @foreach ((array) request('method') as $meth)
                            <input type="hidden" name="method[]" value="{{ $meth }}">
                        @endforeach

                        <label class="small fw-semibold text-nowrap mb-0" style="color: var(--text-muted);">Rows per
                            page</label>
                        <select name="per_page" class="custom-input py-1 px-2" style="width: auto;"
                            onchange="this.form.submit()">
                            @foreach ([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}"
                                    {{ request('per_page', 50) == $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table dash-table mb-0 align-middle example">
                        <thead>
                            <tr>
                                <th class="ps-4" style="width: 70px;">#</th>
                                <th>Officer Name</th>
                             <th>{{ get_label('label_client', 'Client') }}</th>
<th>{{ get_label('label_site', 'Beat') }}</th>
                                <th>Type</th>
                                <th>Notes</th>
                                <th>Date & Time</th>
                                <th class="text-end pe-4" style="width: 100px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td class="ps-4 fw-semibold" style="color: var(--text-muted);">
                                        {{ ($logs->currentPage() - 1) * $logs->perPage() + $loop->iteration }}
                                    </td>
                                    <td>
                                        <div class="fw-semibold" style="color: var(--text-main);">
                                            {{ $log->patrolSession->user->name ?? 'N/A' }}</div>
                                    </td>
                                    <td style="color: var(--text-main);">
                                        {{ $log->patrolSession->site->client->name ?? 'N/A' }}</td>
                                    <td style="color: var(--text-main);">{{ $log->patrolSession->site->name ?? 'N/A' }}
                                    </td>
                                    <td>
                                        <span class="badge-soft badge-soft-primary">
                                            {{ ucwords(str_replace('_', ' ', $log->type)) }}
                                        </span>
                                    </td>
                                    <td style="color: var(--text-muted); max-width: 200px;" class="text-truncate"
                                        title="{{ $log->notes }}">
                                        {{ $log->notes ?? '-' }}
                                    </td>
                                    <td style="color: var(--text-main);">
                                        {{ $log->created_at ? $log->created_at->format('d M, Y h:i A') : 'N/A' }}
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('patrolling.log.details', $log->id) }}"
                                            class="btn-sapphire-outline py-1 px-3"
                                            style="font-size: 0.8rem; text-decoration: none;">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5" style="color: var(--text-muted);">
                                        No patrol records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-4 px-2 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div>
                <small style="color: var(--text-muted);">
                    Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() ?? 0 }}
                    results
                </small>
            </div>
            <div>
                {{ $logs->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    @push('scripts')
        <script>
            // --- View Toggle Logic (Grid/Table) ---
            function setView(viewType) {
                const gridView = document.getElementById('gridView');
                const tableView = document.getElementById('tableView');
                const btnGrid = document.getElementById('btnGrid');
                const btnTable = document.getElementById('btnTable');

                if (viewType === 'table') {
                    gridView.classList.remove('active');
                    tableView.classList.add('active');
                    btnTable.classList.add('active');
                    btnGrid.classList.remove('active');
                    localStorage.setItem('patrolLogsView', 'table');
                } else {
                    tableView.classList.remove('active');
                    gridView.classList.add('active');
                    btnGrid.classList.add('active');
                    btnTable.classList.remove('active');
                    localStorage.setItem('patrolLogsView', 'grid');
                }
            }

            $(document).ready(function() {

                // Load user's last selected view
                const savedView = localStorage.getItem('patrolLogsView') ||
                    'table'; // Defaulting to table for tabular data
                setView(savedView);

                // Select2 Initialization
                const select2Options = {
                    width: '100%',
                    allowClear: true,
                    closeOnSelect: false,
                    placeholder: "Select Option"
                };

                // Initialize all multiple selects to prevent them from showing open lists
                $('#selectRange').select2({
                    ...select2Options,
                    placeholder: 'Select Range'
                });
                $('#selectSite').select2({
                    ...select2Options,
                    placeholder: 'Select Beat'
                });
                $('#selectUser').select2({
                    ...select2Options,
                    placeholder: 'Select User'
                });
                $('#selectMethod').select2({
                    ...select2Options,
                    placeholder: 'Select Method'
                });

                // DataTables Initialization
                if ($.fn.DataTable) {
                    $('.example').DataTable({
                        responsive: true,
                        paging: false,
                        info: false,
                        searching: false, // Turned off since we have a global search input
                    });
                }

                // Dependent Dropdowns Logic
                $('#selectRange').on('change', function() {
                    toggleFilters();
                    let clientIds = $(this).val();
                    $('#selectSite').empty();
                    $('#selectUser').empty();

                    if (!clientIds || clientIds.length === 0) return;

                    // Loop if array, else single value
                    let clientId = Array.isArray(clientIds) ? clientIds[0] : clientIds;

                    $.get('/ajax/client-sites/' + clientId, function(data) {
                        data.forEach(function(item) {
                            $('#selectSite').append(
                                `<option value="${item.id}">${item.name}</option>`);
                        });
                    });

                    $.get('/ajax/client-users/' + clientId, function(data) {
                        data.forEach(function(item) {
                            $('#selectUser').append(
                                `<option value="${item.id}">${item.name}</option>`);
                        });
                    });
                });

                $('#selectSite').on('change', function() {
                    toggleFilters();
                    let siteIds = $(this).val();
                    $('#selectUser').empty();

                    if (!siteIds || siteIds.length === 0) return;

                    let siteId = Array.isArray(siteIds) ? siteIds[0] : siteIds;

                    $.get('/ajax/site-users/' + siteId, function(data) {
                        data.forEach(function(item) {
                            $('#selectUser').append(
                                `<option value="${item.id}">${item.name}</option>`);
                        });
                    });
                });

            });

            function toggleFilters() {
                const rangeCount = ($('#selectRange').val() || []).length;
                const beatCount = ($('#selectSite').val() || []).length;

                $('#beatWrapper').hide();
                $('#userWrapper').hide();

                if (rangeCount > 1) return;
                if (rangeCount === 1) $('#beatWrapper').show();
                if (rangeCount === 1 && beatCount === 1) $('#userWrapper').show();
            }

            // Initial check for filters on page load
            toggleFilters();
        </script>
    @endpush

@endsection

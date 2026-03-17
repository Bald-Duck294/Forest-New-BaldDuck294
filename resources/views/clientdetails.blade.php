@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    // $user = session('user');
@endphp
@extends('layouts.app')

@section('title', 'Manage Ranges')

@section('content')

    <style>
        /* =========================================
                                                                       LOCAL COMPONENT STYLES
                                                                       (Hooked to Global Sapphire Variables)
                                                                    ========================================= */

        /* Action Buttons */
        .btn-sapphire {
            background-color: var(--sapphire-primary);
            color: #ffffff;
            border: none;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-sapphire:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            color: #ffffff;
        }

        .btn-sapphire-outline {
            background-color: transparent;
            color: var(--text-main);
            border: 1px solid var(--border-color);
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-sapphire-outline:hover {
            background-color: var(--table-hover);
            color: var(--sapphire-primary);
            border-color: var(--sapphire-primary);
        }

        /* Icon Action Buttons (View, Edit, Delete) */
        .btn-icon-soft {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            border: none;
            background: transparent;
            transition: all 0.2s ease;
            font-size: 1.1rem;
            text-decoration: none !important;
        }

        .btn-icon-soft.view {
            color: var(--sapphire-primary);
        }

        .btn-icon-soft.view:hover {
            background: rgba(59, 130, 246, 0.15);
            transform: translateY(-2px);
        }

        .btn-icon-soft.edit {
            color: var(--sapphire-success);
        }

        .btn-icon-soft.edit:hover {
            background: rgba(16, 185, 129, 0.15);
            transform: translateY(-2px);
        }

        .btn-icon-soft.delete {
            color: var(--sapphire-danger);
        }

        .btn-icon-soft.delete:hover {
            background: rgba(239, 68, 68, 0.15);
            transform: translateY(-2px);
        }

        /* Custom Switch Styling */
        .custom-switch .form-check-input {
            width: 2.5em;
            height: 1.25em;
            background-color: var(--border-color);
            border-color: var(--border-color);
            cursor: pointer;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        .custom-switch .form-check-input:checked {
            background-color: var(--sapphire-success);
            border-color: var(--sapphire-success);
        }

        .custom-switch .form-check-input:focus {
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.25);
        }

        /* Table Typography Overrides */
        .client-name-link {
            font-weight: 600;
            color: var(--sapphire-primary);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .client-name-link:hover {
            color: var(--text-main);
            text-decoration: underline;
        }

        /* Tables & Sorting */
        .dash-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .dash-table th {
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.85rem;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem;
            background-color: transparent !important;
            white-space: nowrap;
        }

        .dash-table td {
            color: var(--text-main);
            font-weight: 500;
            font-size: 0.9rem;
            border-bottom: 1px dashed var(--border-color);
            padding: 1rem;
            vertical-align: middle;
            background-color: transparent !important;
        }

        .dash-table tr:hover td {
            background-color: var(--table-hover) !important;
        }

        .dash-table tr:last-child td {
            border-bottom: none;
        }

        .sort-link {
            color: var(--text-muted);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: color 0.2s ease;
        }

        .sort-link:hover {
            color: var(--sapphire-primary);
        }

        .sort-link i {
            font-size: 0.75rem;
            opacity: 0.5;
        }

        .sort-link:hover i {
            opacity: 1;
        }

        /* Tables & Sorting */
        .dash-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .dash-table th {
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.85rem;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem;
            background-color: transparent !important;
            white-space: nowrap;
        }

        .dash-table td {
            color: var(--text-main);
            font-weight: 500;
            font-size: 0.9rem;
            border-bottom: 1px dashed var(--border-color);
            padding: 1rem;
            vertical-align: middle;
            background-color: transparent !important;
        }

        .dash-table tr:hover td {
            background-color: var(--table-hover) !important;
        }

        .dash-table tr:last-child td {
            border-bottom: none;
        }

        .sort-link {
            color: var(--text-muted);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: color 0.2s ease;
        }

        .sort-link:hover {
            color: var(--sapphire-primary);
        }

        .sort-link i {
            font-size: 0.75rem;
            opacity: 0.5;
        }

        .sort-link:hover i {
            opacity: 1;
        }
    </style>

    <div class="container-fluid py-4">

        {{-- HEADER --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <a href="javascript:history.back()" class="text-decoration-none mb-2 d-inline-block"
                    style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">
                    <i class="bi bi-arrow-left me-1"></i> Go Back
                </a>
                <h3 class="fw-bold mb-1" style="color: var(--text-main);">Ranges</h3>
                <p class="mb-0" style="color: var(--text-muted); font-size: 0.9rem;">
                    Manage all assigned ranges, states, cities, and their operational status.
                </p>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('clients.export') }}" class="btn-sapphire-outline shadow-sm">
                    <i class="bi bi-download"></i> Export Data
                </a>
                <a href="{{ route('clients.create') }}" class="btn-sapphire shadow-sm">
                    <i class="bi bi-plus-lg"></i> Add Range
                </a>
            </div>
        </div>

        {{-- MAIN TABLE CARD --}}
        <div class="dash-card p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table dash-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 70px;">#</th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'dir' => request('dir') == 'asc' ? 'desc' : 'asc']) }}"
                                    class="sort-link">
                                    Name <i class="bi bi-arrow-down-up"></i>
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'state', 'dir' => request('dir') == 'asc' ? 'desc' : 'asc']) }}"
                                    class="sort-link">
                                    State <i class="bi bi-arrow-down-up"></i>
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'city', 'dir' => request('dir') == 'asc' ? 'desc' : 'asc']) }}"
                                    class="sort-link">
                                    City <i class="bi bi-arrow-down-up"></i>
                                </a>
                            </th>
                            <th class="text-center" style="width: 120px;">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'dir' => request('dir') == 'asc' ? 'desc' : 'asc']) }}"
                                    class="sort-link justify-content-center">
                                    Status <i class="bi bi-arrow-down-up"></i>
                                </a>
                            </th>
                            <th class="text-center pe-4" style="width: 160px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($clients && count($clients) > 0)
                            @php $sr = ($clients->currentPage() - 1) * $clients->perPage() + 1; @endphp
                            @foreach ($clients as $row)
                                <tr>
                                    <td class="ps-4 fw-semibold" style="color: var(--text-muted);">
                                        {{ $sr++ }}
                                    </td>
                                    <td>
                                        <a href="{{ route('clients.view', $row->id) }}" class="client-name-link">
                                            {{ $row->name }}
                                        </a>
                                    </td>
                                    <td style="color: var(--text-main);">
                                        {{ $row->state ?: '-' }}
                                    </td>
                                    <td style="color: var(--text-main);">
                                        {{ $row->city ?: '-' }}
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch custom-switch d-flex justify-content-center m-0">
                                            <input class="form-check-input status-toggle shadow-sm" type="checkbox"
                                                role="switch" id="statusSwitch{{ $row->id }}"
                                                {{ $row->isActive == 1 ? 'checked' : '' }}
                                                onchange="toggleActive('{{ $row->id }}', this, {{ $row->isActive == 1 ? 'true' : 'false' }})">
                                        </div>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('sites.getsites', $row->id) }}" class="btn-icon-soft view"
                                                title="View Sites">
                                                <i class="bi bi-eye-fill"></i>
                                            </a>
                                            <a href="{{ route('clients.editClient', $row->id) }}"
                                                class="btn-icon-soft edit" title="Edit Range">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button type="button" onclick="deleteClient('{{ $row->id }}')"
                                                class="btn-icon-soft delete" title="Delete Range">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="bi bi-buildings"
                                            style="font-size: 3rem; color: var(--text-muted); opacity: 0.4;"></i>
                                        <h5 class="fw-bold mt-3 mb-1" style="color: var(--text-main);">No ranges found</h5>
                                        <p style="color: var(--text-muted); font-size: 0.9rem;">
                                            There are no items found here. <a href="{{ route('clients.create') }}"
                                                style="color: var(--sapphire-primary); font-weight: 600; text-decoration: none;">Add
                                                your first one</a>.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            @if ($clients && count($clients) > 0)
                <div class="p-3 d-flex justify-content-between align-items-center"
                    style="border-top: 1px solid var(--border-color); background: var(--bg-body);">
                    <small style="color: var(--text-muted);">
                        Showing {{ $clients->firstItem() ?? 0 }} to {{ $clients->lastItem() ?? 0 }} of
                        {{ $clients->total() ?? 0 }} entries
                    </small>
                    <div class="m-0 p-0">
                        {{ $clients->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif

        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function() {

                // Delete Function
                window.deleteClient = function(id) {
                    var title = 'Delete Confirmation';
                    var msg = 'Are you sure you want to delete this range?';

                    // Check if SweetAlert is loaded
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: title,
                            text: msg,
                            icon: 'warning',
                            showCancelButton: true,
                            background: getComputedStyle(document.documentElement).getPropertyValue(
                                '--bg-card').trim() || '#fff',
                            color: getComputedStyle(document.documentElement).getPropertyValue(
                                '--text-main').trim() || '#000',
                            confirmButtonColor: '#EF4444', // Sapphire Danger
                            cancelButtonColor: '#64748B', // Text Muted
                            confirmButtonText: 'Yes, Delete'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                var url = '{{ route('clients.deleteClient', ':id') }}';
                                window.location = url.replace(':id', id);
                            }
                        });
                    } else {
                        if (confirm(msg)) {
                            var url = '{{ route('clients.deleteClient', ':id') }}';
                            window.location = url.replace(':id', id);
                        }
                    }
                };

                // Toggle Status Function
                window.toggleActive = function(id, element, currentlyActive) {
                    // Instantly revert the toggle visually until user confirms
                    element.checked = currentlyActive;

                    var action = currentlyActive ? 'deactivate' : 'activate';
                    var title = action === 'deactivate' ? 'Deactivation Confirmation' : 'Activation Confirmation';
                    var msg = action === 'deactivate' ? 'Are you sure you want to deactivate this range?' :
                        'Are you sure you want to activate this range?';
                    var btnText = action === 'deactivate' ? 'Deactivate' : 'Activate';
                    var btnColor = action === 'deactivate' ? '#EF4444' : '#10B981';

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: title,
                            text: msg,
                            icon: 'warning',
                            showCancelButton: true,
                            background: getComputedStyle(document.documentElement).getPropertyValue(
                                '--bg-card').trim() || '#fff',
                            color: getComputedStyle(document.documentElement).getPropertyValue(
                                '--text-main').trim() || '#000',
                            confirmButtonColor: btnColor,
                            cancelButtonColor: '#64748B',
                            confirmButtonText: btnText
                        }).then((result) => {
                            if (result.isConfirmed) {
                                element.checked = !currentlyActive;
                                var url = action === 'deactivate' ?
                                    '{{ route('clients.inactive', ':id') }}' :
                                    '{{ route('clients.active', ':id') }}';
                                window.location = url.replace(':id', id);
                            }
                        });
                    } else {
                        if (confirm(msg)) {
                            element.checked = !currentlyActive;
                            var url = action === 'deactivate' ? '{{ route('clients.inactive', ':id') }}' :
                                '{{ route('clients.active', ':id') }}';
                            window.location = url.replace(':id', id);
                        }
                    }
                };

            });
        </script>
    @endpush

@endsection

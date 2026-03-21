@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    $user = session('user');
    $company = session('company');
    $label = isset($company) && ($company->is_forest ?? 1) == 1 ? 'Beat' : 'Site';
@endphp
@extends('layouts.app')

@section('title', $label . ' List')

@section('content')

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
            margin-top: 1rem;
        }

        /* Custom Form Inputs */
        .custom-input {
            background-color: var(--bg-body);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 0.85rem;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .custom-input:focus {
            border-color: var(--sapphire-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        html[data-bs-theme="dark"] .custom-input {
            color-scheme: dark;
        }

        /* Action Buttons */
        .btn-sapphire {
            background-color: var(--sapphire-primary);
            color: #ffffff;
            border: none;
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

        /* Icon Action Buttons (View, Edit, Delete) */
        .btn-icon-soft {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: transparent;
            transition: all 0.2s ease;
            font-size: 1.05rem;
            text-decoration: none !important;
            cursor: pointer;
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

        /* Soft Badges for Management Links */
        .badge-soft {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            text-decoration: none !important;
            background: var(--bg-body);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            transition: transform 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }

        .badge-soft:hover {
            transform: translateY(-2px);
            border-color: var(--sapphire-primary);
            color: var(--sapphire-primary);
            background: rgba(59, 130, 246, 0.05);
        }

        .site-name-link {
            font-weight: 600;
            color: var(--sapphire-primary);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .site-name-link:hover {
            color: var(--text-main);
            text-decoration: underline;
        }
    </style>

    <div class="container-fluid py-4">

        <div class="dash-card p-0 overflow-hidden">

            {{-- COMPACT HEADER CONTROLS --}}
            <div class="p-3 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3"
                style="border-bottom: 1px solid var(--border-color); background: var(--bg-card);">

                {{-- Title & Back Button --}}
                <div class="d-flex align-items-center gap-3">
                    @if ($client_id != 'playBackSites')
                        <a href="javascript:history.back()" class="btn-sapphire-outline" style="padding: 6px 10px;"
                            title="Go Back">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                    @endif
                    <h5 class="fw-bold mb-0" style="color: var(--text-main);">
                        @if ($client_id == 'playBackSites')
                            <i class="bi bi-play-circle-fill me-2" style="color: var(--sapphire-primary);"></i> Playback
                            {{ $label }}s
                        @elseif (isset($clientName))
                            <i class="bi bi-geo-alt-fill me-2" style="color: var(--sapphire-primary);"></i>
                            {{ ucfirst($clientName->name) }} — {{ $label }} List
                        @else
                            <i class="bi bi-geo-alt-fill me-2" style="color: var(--sapphire-primary);"></i>
                            {{ $label }} List
                        @endif
                    </h5>
                </div>

                {{-- Search & Actions --}}
                <div class="d-flex flex-column flex-md-row align-items-md-center gap-2">
                    {{-- Search Form --}}
                    <form method="GET" class="d-flex gap-2 m-0">
                        <input type="text" name="search" value="{{ request('search') }}" class="custom-input"
                            placeholder="Search..." style="min-width: 200px;">
                        <button type="submit" class="btn-sapphire"><i class="bi bi-search"></i></button>
                        @if (request('search'))
                            <a href="{{ url()->current() }}" class="btn-sapphire-outline px-2" title="Clear Search"><i
                                    class="bi bi-x-lg"></i></a>
                        @endif
                    </form>

                    <div class="vr d-none d-md-block mx-1" style="color: var(--border-color);"></div>

                    {{-- Action Buttons --}}
                    @if (!isset($supervisor_id) && $user->role_id != '4' && $client_id !== 'playBackSites')
                        <a href="{{ route('sites.site_create', $client_id) }}" class="btn-sapphire text-nowrap">
                            <i class="bi bi-plus-lg"></i> Add {{ $label }}
                        </a>
                    @endif
                    @if ($client_id == 0 || $client_id != 'playBackSites')
                        <a href="{{ route('sites.export', $client_id != 'playBackSites' ? $client_id : 0) }}"
                            class="btn-sapphire-outline text-nowrap"
                            style="color: var(--sapphire-success); border-color: var(--sapphire-success);">
                            <i class="bi bi-download"></i> Export
                        </a>
                    @endif
                </div>

            </div>

            {{-- DATA TABLE --}}
            <div class="table-responsive">
                <table class="table dash-table align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 60px;">#</th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'dir' => request('dir') == 'asc' ? 'desc' : 'asc']) }}"
                                    class="sort-link">
                                    {{ $label }} Name <i class="bi bi-arrow-down-up"></i>
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'client', 'dir' => request('dir') == 'asc' ? 'desc' : 'asc']) }}"
                                    class="sort-link">
                                    Client <i class="bi bi-arrow-down-up"></i>
                                </a>
                            </th>

                            @if ($client_id == 'playBackSites')
                                <th>Action</th>
                            @elseif ($client_id == 'daily-update')
                                <th>Daily Update</th>
                            @else
                                <th>Management</th>
                            @endif

                            @if ($client_id != 'playBackSites' && $client_id != 'daily-update')
                                <th class="text-end pe-4" style="width: 140px;">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($Sites) && count($Sites) > 0)
                            @php $sr = method_exists($Sites, 'currentPage') ? ($Sites->currentPage() - 1) * $Sites->perPage() + 1 : 1; @endphp

                            @foreach ($Sites as $row)
                                <tr>
                                    <td class="ps-4 fw-semibold" style="color: var(--text-muted);">{{ $sr++ }}</td>

                                    <td>
                                        @php $viewId = isset($supervisor_id) ? 0 : $row->client_id; @endphp
                                        <a href="{{ route('sites.site_view', [$viewId, $row->id]) }}"
                                            class="site-name-link">
                                            {{ ucfirst($row->name) }}
                                        </a>
                                    </td>

                                    <td>{{ ucfirst($row->client_name ?? '—') }}</td>

                                    <td>
                                        @if ($client_id != 'playBackSites' && $client_id != 'daily-update')
                                            <div class="d-flex flex-wrap gap-2">
                                                <a href="{{ route('clients.getshifts', [$row->client_id, $row->id]) }}"
                                                    class="badge-soft">
                                                    <i class="bi bi-clock" style="color: var(--sapphire-primary);"></i>
                                                    Shifts
                                                </a>
                                                <a href="{{ route('clients.getclientgeofences', [$row->client_id, $row->id]) }}"
                                                    class="badge-soft">
                                                    <i class="bi bi-geo-alt" style="color: var(--sapphire-success);"></i>
                                                    Geofence
                                                </a>
                                                <a href="{{ route('clients.getclientguards', [$row->client_id, $row->id]) }}"
                                                    class="badge-soft">
                                                    <i class="bi bi-person" style="color: var(--sapphire-warning);"></i>
                                                    Employee
                                                </a>

                                                @php
                                                    $features = session('features');
                                                    $hasTour = $features ? array_search('tour', $features) : false;
                                                @endphp

                                                @if ($hasTour !== false)
                                                    <a href="{{ route('clients.gettours', $row->id) }}" class="badge-soft">
                                                        <i class="bi bi-signpost-split"
                                                            style="color: var(--sapphire-danger);"></i> Tour
                                                    </a>
                                                @endif
                                            </div>
                                        @elseif ($client_id == 'playBackSites')
                                            <button type="button" class="btn-sapphire btn-sm"
                                                onclick="playBackOfGuards('{{ $row->id }}')">
                                                <i class="bi bi-play-fill"></i> Playback
                                            </button>
                                        @elseif ($client_id == 'daily-update')
                                            <a href="{{ route('DailyUpdate', $row->id) }}"
                                                class="btn-sapphire-outline btn-sm">
                                                <i class="bi bi-eye"></i> View Updates
                                            </a>
                                        @endif
                                    </td>

                                    @if ($client_id != 'playBackSites' && $client_id != 'daily-update')
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-1">
                                                <a href="{{ route('sites.site_view', [$row->client_id, $row->id]) }}"
                                                    class="btn-icon-soft view" title="View">
                                                    <i class="bi bi-eye-fill"></i>
                                                </a>

                                                @if ($user->role_id != '4')
                                                    <a href="{{ route('sites.site_edit', [$row->client_id, $row->id]) }}"
                                                        class="btn-icon-soft edit" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <button class="btn-icon-soft delete"
                                                        onclick="deleteSite('{{ $row->client_id }}','{{ $row->id }}')"
                                                        title="Delete">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                @php $colSpan = ($client_id != 'playBackSites' && $client_id != 'daily-update') ? 5 : 4; @endphp
                                <td colspan="{{ $colSpan }}" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="bi bi-geo-alt"
                                            style="font-size: 3rem; color: var(--text-muted); opacity: 0.4;"></i>
                                        <h5 class="fw-bold mt-3 mb-1" style="color: var(--text-main);">No
                                            {{ strtolower($label) }}s found</h5>
                                        <p style="color: var(--text-muted); font-size: 0.9rem;">
                                            No {{ strtolower($label) }}s have been added for this client yet.
                                        </p>

                                        @if (!isset($supervisor_id) && $user->role_id != '4' && $client_id !== 'playBackSites')
                                            <div class="mt-3">
                                                <a href="{{ route('sites.site_create', $client_id) }}"
                                                    class="btn-sapphire">
                                                    <i class="bi bi-plus-lg"></i> Add {{ $label }}
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

            {{-- PAGINATION --}}
            @if (isset($Sites) && method_exists($Sites, 'links'))
                <div class="p-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3"
                    style="border-top: 1px solid var(--border-color); background: var(--bg-body);">
                    <small style="color: var(--text-muted);">
                        Showing {{ $Sites->firstItem() ?? 0 }} to {{ $Sites->lastItem() ?? 0 }} of
                        {{ $Sites->total() ?? 0 }} entries
                    </small>
                    <div class="m-0 p-0">
                        {{ $Sites->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif

        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function() {

                // Delete Function (Mapped to Safari/Sapphire styles)
                window.deleteSite = function(client_id, id) {
                    var title = 'Delete Confirmation';
                    var msg = 'Are you sure you want to delete this site?';

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
                            confirmButtonColor: '#EF4444',
                            cancelButtonColor: '#64748B',
                            confirmButtonText: 'Delete'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                var url = '{{ route('sites.site_delete', [':client_id', ':id']) }}';
                                url = url.replace(':client_id', client_id).replace(':id', id);
                                window.location = url;
                            }
                        });
                    } else {
                        if (confirm(msg)) {
                            var url = '{{ route('sites.site_delete', [':client_id', ':id']) }}';
                            url = url.replace(':client_id', client_id).replace(':id', id);
                            window.location = url;
                        }
                    }
                };

                // Playback redirect
                window.playBackOfGuards = function(siteId) {
                    var url = '{{ route('playBackOfGuards', ':site_id') }}';
                    window.location = url.replace(':site_id', siteId);
                }

            });
        </script>
    @endpush

@endsection

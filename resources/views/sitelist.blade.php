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
            --text: #0f172a;
            --muted: #64748b;
        }

        .card {
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            background: #fff;
            margin-bottom: 24px;
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border);
            padding: 18px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h4 {
            margin: 0;
            font-weight: 700;
            color: var(--text);
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-body {
            padding: 24px;
        }

        .table thead th {
            background: #f1f5f9;
            border-bottom: 2px solid var(--border);
            color: var(--muted);
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 16px;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            color: var(--text);
            font-size: 14px;
            border-bottom: 1px solid var(--border);
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .table tbody tr:hover {
            background-color: #f8fafc;
        }

        .btn-action {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: none;
            margin-right: 4px;
            transition: all 0.2s;
            cursor: pointer;
            text-decoration: none !important;
            font-size: 16px;
        }

        .btn-view {
            background: #eff6ff;
            color: #3b82f6;
        }

        .btn-view:hover {
            background: #3b82f6;
            color: #fff;
        }

        .btn-edit {
            background: #f0fdf4;
            color: #22c55e;
        }

        .btn-edit:hover {
            background: #22c55e;
            color: #fff;
        }

        .btn-delete {
            background: #fef2f2;
            color: #ef4444;
        }

        .btn-delete:hover {
            background: #ef4444;
            color: #fff;
        }

        .simple-button {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--primary);
            color: #fff !important;
            border-radius: 10px;
            padding: 8px 16px;
            font-weight: 600;
            font-size: 13px;
            border: none;
            transition: all 0.2s;
            text-decoration: none !important;
            white-space: nowrap;
            cursor: pointer;
        }

        .simple-button:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
            color: #fff !important;
        }

        .btn-export {
            background: #10b981 !important;
        }

        .btn-export:hover {
            background: #059669 !important;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2) !important;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            background: #f1f5f9;
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            text-decoration: none;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .btn-back:hover {
            background: #e2e8f0;
            color: var(--text);
        }

        .btn-back i {
            font-size: 18px;
        }

        .badge-management {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            margin-right: 4px;
            display: inline-block;
            border: 1px solid var(--border);
            background: #f8fafc;
            color: var(--muted);
            transition: all 0.2s;
            text-decoration: none !important;
        }

        .badge-management:hover {
            background: var(--primary);
            color: #fff !important;
            border-color: var(--primary);
        }

        .site-name-link {
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
        }

        .site-name-link:hover {
            text-decoration: underline;
        }

        .actions-cell {
            display: flex;
            justify-content: center;
            gap: 6px;
            flex-wrap: nowrap;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--muted);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 12px;
            display: block;
        }
    </style>

    @php
        $user = session('user');
        $company = session('company');
        $label = isset($company) && ($company->is_forest ?? 1) == 1 ? 'Beat' : 'Site';
    @endphp

    <div class="card">
        <div class="card-header">
            <h4>
                @if ($client_id != 'playBackSites')
                    <a href="{{ route('clients') }}" class="btn-back" title="Back to Clients">
                        <i class="bi bi-arrow-left"></i> </a>
                @endif

                @if ($client_id == 'playBackSites')
                    <i class="la la-play-circle" style="color: var(--primary);"></i> Playback {{ $label }}s
                @elseif (isset($clientName))
                    {{ ucfirst($clientName->name) }} — {{ $label }} List
                @else
                    {{ $label }} List
                @endif
            </h4>

            <div class="d-flex gap-2 align-items-center">
                @if (!isset($supervisor_id) && $user->role_id != '4' && $client_id !== 'playBackSites')
                    <a href="{{ route('sites.site_create', $client_id) }}" class="simple-button">
                        <i class="la la-plus"></i> Add {{ $label }}
                    </a>
                @endif
                @if ($client_id == 0 || $client_id != 'playBackSites')
                    <a href="{{ route('sites.export', $client_id != 'playBackSites' ? $client_id : 0) }}"
                        class="simple-button btn-export">
                        <i class="la la-download"></i> Export
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
                            <th>{{ $label }} Name</th>
                            <th>Client</th>
                            @if ($client_id == 'playBackSites')
                                <th>Action</th>
                            @elseif ($client_id == 'daily-update')
                                <th>Daily Update</th>
                            @else
                                <th>Management</th>
                            @endif
                            @if ($client_id != 'playBackSites' && $client_id != 'daily-update')
                                <th style="width:130px; text-align:center;">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($Sites) && count($Sites) > 0)
                            @php $sr = 1; @endphp
                            @foreach ($Sites as $row)
                                <tr>
                                    <td>{{ $sr++ }}</td>
                                    <td>
                                        @php
                                            $viewId = isset($supervisor_id) ? 0 : $row->client_id;
                                        @endphp
                                        <a href="{{ route('sites.site_view', [$viewId, $row->id]) }}"
                                            class="site-name-link">
                                            {{ ucfirst($row->name) }}
                                        </a>
                                    </td>
                                    <td>{{ ucfirst($row->client_name ?? '—') }}</td>
                                    <td>
                                        @if ($client_id != 'playBackSites' && $client_id != 'daily-update')
                                            <a href="{{ route('clients.getshifts', [$row->client_id, $row->id]) }}"
                                                class="badge-management">Shifts</a>
                                            <a href="{{ route('clients.getclientgeofences', [$row->client_id, $row->id]) }}"
                                                class="badge-management">Geofence</a>
                                            <a href="{{ route('clients.getclientguards', [$row->client_id, $row->id]) }}"
                                                class="badge-management">Employee</a>
                                            @php
                                                $features = session('features');
                                                $hasTour = $features ? array_search('tour', $features) : false;
                                            @endphp
                                            @if ($hasTour !== false)
                                                <a href="{{ route('clients.gettours', $row->id) }}"
                                                    class="badge-management">Tour</a>
                                            @endif
                                        @elseif ($client_id == 'playBackSites')
                                            <button type="button" class="btn-action btn-view"
                                                onclick="playBackOfGuards('{{ $row->id }}')">
                                                <i class="la la-play"></i>
                                            </button>
                                        @elseif ($client_id == 'daily-update')
                                            <a href="{{ route('DailyUpdate', $row->id) }}" class="btn-action btn-view">
                                                <i class="la la-eye"></i>
                                            </a>
                                        @endif
                                    </td>
                                    @if ($client_id != 'playBackSites' && $client_id != 'daily-update')
                                        <td style="" class="actions-cell">
                                            <a href="{{ route('sites.site_view', [$row->client_id, $row->id]) }}"
                                                class="btn-action btn-view" title="View">
                                                <i class="bi bi-eye-fill"></i>
                                            </a>
                                            @if ($user->role_id != '4')
                                                <a href="{{ route('sites.site_edit', [$row->client_id, $row->id]) }}"
                                                    class="btn-action btn-edit" title="Edit">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>
                                                <button class="btn-action btn-delete"
                                                    onclick="deleteSite('{{ $row->client_id }}','{{ $row->id }}')"
                                                    title="Delete">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="la la-map-marker"></i>
                                        <p>No {{ strtolower($label) }}s found for this client.</p>
                                        @if (!isset($supervisor_id) && $user->role_id != '4' && $client_id !== 'playBackSites')
                                            <a href="{{ route('sites.site_create', $client_id) }}" class="simple-button"
                                                style="margin: 0 auto; width: fit-content;">
                                                <i class="la la-plus"></i> Add {{ $label }}
                                            </a>
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
@endsection

@push('scripts')
    <script>
        async function deleteSite(client_id, id) {
            var confirmed = await showSweetAlert('Delete Confirmation', 'Are you sure you want to delete this site?',
                'Delete', true, 'Cancel');
            if (confirmed) {
                var url = '{{ route('sites.site_delete', [':client_id', ':id']) }}';
                url = url.replace(':client_id', client_id).replace(':id', id);
                window.location = url;
            }
        }

        function playBackOfGuards(siteId) {
            var url = '{{ route('playBackOfGuards', ':site_id') }}';
            window.location = url.replace(':site_id', siteId);
        }
    </script>
@endpush

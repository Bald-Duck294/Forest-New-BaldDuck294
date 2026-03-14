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
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
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

        .btn-action i {
            color: inherit;
            line-height: 1;
        }

        .actions-cell {
            display: flex;
            justify-content: center;
            gap: 6px;
            flex-wrap: nowrap;
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
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        .badge-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-block;
        }

        .badge-active {
            background: #dcfce7;
            color: #166534;
        }

        .badge-active:hover {
            background: #166534;
            color: #fff;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-inactive:hover {
            background: #991b1b;
            color: #fff;
        }

        .client-name-link {
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
        }

        .client-name-link:hover {
            text-decoration: underline;
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

    <div class="card">
        <div class="card-header">
            <h4><i class="la la-users me-2" style="color: var(--primary);"></i> Clients</h4>
            <div class="d-flex gap-2 align-items-center">
                <a href="{{ route('clients.create') }}" class="simple-button">
                    <i class="la la-plus"></i> Add Client
                </a>
                <a href="{{ route('clients.export') }}" class="simple-button btn-export">
                    <i class="la la-download"></i> Export
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width:60px;">#</th>
                            <th>Name</th>
                            <th>State</th>
                            <th>City</th>
                            <th style="width:110px;">Status</th>
                            <th style="width:130px; text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($clients && count($clients) > 0)
                            @php $sr = 1; @endphp
                            @foreach ($clients as $row)
                                <tr>
                                    <td>{{ $sr++ }}</td>
                                    <td>
                                        <a href="{{ route('clients.view', $row->id) }}" class="client-name-link">
                                            {{ $row->name }}
                                        </a>
                                    </td>
                                    <td>{{ $row->state }}</td>
                                    <td>{{ $row->city }}</td>
                                    <td>
                                        @if ($row->isActive == 1)
                                            <button class="badge-status badge-active"
                                                onclick="toggleActive('{{ $row->id }}', 'deactivate')">Active</button>
                                        @else
                                            <button class="badge-status badge-inactive"
                                                onclick="toggleActive('{{ $row->id }}', 'activate')">Inactive</button>
                                        @endif
                                    </td>
                                    <td class="actions-cell">
                                        <a href="{{ route('sites.getsites', $row->id) }}" class="btn-action btn-view"
                                            title="View Sites">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>

                                        <a href="{{ route('clients.editClient', $row->id) }}" class="btn-action btn-edit"
                                            title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <button onclick="deleteClient('{{ $row->id }}')" class="btn-action btn-delete"
                                            title="Delete">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="la la-building"></i>
                                        <p>No clients found. <a href="{{ route('clients.create') }}">Add your first
                                                client</a>.</p>
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
        document.addEventListener("DOMContentLoaded", function() {

            console.log("client script loaded");

            window.deleteClient = async function(id) {
                var confirmed = await showSweetAlert(
                    'Delete Confirmation',
                    'Are you sure you want to delete this client?',
                    'Delete',
                    true,
                    'Cancel'
                );

                if (confirmed) {
                    var url = '{{ route('clients.deleteClient', ':id') }}';
                    window.location = url.replace(':id', id);
                }
            };

            window.toggleActive = async function(id, action) {
                var title = action === 'deactivate' ?
                    'Deactivation Confirmation' :
                    'Activation Confirmation';

                var msg = action === 'deactivate' ?
                    'Are you sure you want to deactivate this client?' :
                    'Are you sure you want to activate this client?';

                var btn = action === 'deactivate' ? 'Deactivate' : 'Activate';

                var confirmed = await showSweetAlert(title, msg, btn, true, 'Cancel');

                if (confirmed) {
                    var url = action === 'deactivate' ?
                        '{{ route('clients.active', ':id') }}' :
                        '{{ route('clients.inactive', ':id') }}';
                    window.location = url.replace(':id', id);
                }
            };

        });
    </script>
@endpush

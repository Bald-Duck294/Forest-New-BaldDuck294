@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    $user = session('user');
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

        .btn-view { background: #eff6ff; color: #3b82f6; }
        .btn-view:hover { background: #3b82f6; color: #fff; }

        .btn-edit { background: #f0fdf4; color: #22c55e; }
        .btn-edit:hover { background: #22c55e; color: #fff; }

        .btn-delete { background: #fef2f2; color: #ef4444; }
        .btn-delete:hover { background: #ef4444; color: #fff; }

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

        .btn-back i { font-size: 18px; }

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

    <div class="card">
        <div class="card-header">
            <h4>
                <a href="javascript:history.back()" class="btn-back" title="Back">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <i class="bi bi-people-fill" style="color: var(--primary);"></i>
                @if(isset($clientName)) {{ $clientName->name }} — @endif
                @if(isset($siteName)) {{ ucfirst($siteName->name) }} — @endif
                Assigned Employees
            </h4>

            @if($user->role_id != 4)
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
                            <th style="width:130px; text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($guards && count($guards) > 0)
                            @php $sr = 1; @endphp
                            @foreach($guards as $row)
                                @php
                                    $shiftRows  = DB::table('shift_assigned')->whereRaw('id in (' . $row->shift_id . ')')->get();
                                    $dateRange  = json_decode($row->date_range);
                                @endphp
                                <tr>
                                    <td>{{ $sr++ }}</td>
                                    <td><strong>{{ $row->user_name }}</strong></td>
                                    <td>
                                        @if($dateRange)
                                            {{ date('d M Y', strtotime($dateRange->from)) }}
                                            &rarr;
                                            {{ date('d M Y', strtotime($dateRange->to)) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        @foreach($shiftRows as $shiftRow)
                                            @php $timing = json_decode($shiftRow->shift_time); @endphp
                                            {{ $shiftRow->shift_name }}
                                            @if($timing)
                                                ({{ $timing->start }} – {{ $timing->end }})
                                            @endif
                                            @if(!$loop->last) <br> @endif
                                        @endforeach
                                    </td>
                                    <td class="actions-cell">
                                        <a class="btn-action btn-view"
                                            href="{{ route('clients.clientguard_read', [$client_id, $site_id, $row->user_id]) }}"
                                            title="View">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        @if($user->role_id != 4)
                                            <a class="btn-action btn-edit"
                                                href="{{ route('guards.guard_edit', [$client_id, $row->id]) }}"
                                                title="Edit">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            <button class="btn-action btn-delete"
                                                onclick="deleteGuard('{{ $client_id }}','{{ $site_id }}','{{ $row->id }}')"
                                                title="Release">
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
                                        <p>No employees assigned to this site yet.</p>
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

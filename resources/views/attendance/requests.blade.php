@php
    $hideGlobalFilters = true;
@endphp

@extends('layouts.app')

@section('title', get_label('label_attendance', 'Attendance') . ' ' . Str::plural(get_label('label_request', 'Request')))

@section('content')

    <style>
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
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.9rem;
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

        /* Compact Request Cards */
        .req-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.25rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s ease, border-color 0.2s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        }

        .req-card:hover {
            transform: translateY(-3px);
            border-color: var(--sapphire-primary);
        }

        /* Form Inputs for Dark Mode */
        .req-input {
            background-color: var(--bg-body);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 0.85rem;
            padding: 6px 10px;
        }

        .req-input:focus {
            border-color: var(--sapphire-primary);
            box-shadow: none;
            background-color: var(--bg-body);
            color: var(--text-main);
        }

        .req-input::placeholder {
            color: var(--text-muted);
            opacity: 0.7;
        }

        /* Action Buttons */
        .btn-approve {
            background: var(--sapphire-success);
            color: white;
            border: none;
            font-weight: 500;
        }

        .btn-approve:hover {
            background: #059669;
            color: white;
        }

        .btn-reject {
            background: var(--sapphire-danger);
            color: white;
            border: none;
            font-weight: 500;
        }

        .btn-reject:hover {
            background: #DC2626;
            color: white;
        }
    </style>

    <div class="container-fluid py-4">

        {{-- HEADER & TOGGLE --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
               <h3 class="fw-bold mb-1" style="color: var(--text-main);">
    {{ get_label('label_attendance', 'Attendance') }} {{ Str::plural(get_label('label_request', 'Request')) }}
</h3>
                <p class="mb-0" style="color: var(--text-muted); font-size: 0.9rem;">
                    Review and manage employee clock-in/out exceptions
                </p>
            </div>

            {{-- Grid / Table View Toggle --}}
            <div class="view-toggle shadow-sm">
                <button class="view-toggle-btn active" id="btnGrid" onclick="setView('grid')">
                    <i class="bi bi-grid-fill me-1"></i> Grid
                </button>
                <button class="view-toggle-btn" id="btnTable" onclick="setView('table')">
                    <i class="bi bi-list-ul me-1"></i> List
                </button>
            </div>
        </div>

        {{-- =========================================
         GRID VIEW (COMPACT CARDS)
    ========================================= --}}
        <div id="gridView" class="row g-3">
            @forelse ($requests as $req)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="req-card">

                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $req->photo ? asset('storage/' . $req->photo) : asset('images/user-placeholder.png') }}"
                                    class="rounded-circle shadow-sm bg-light" width="48" height="48"
                                    style="object-fit:cover; border: 2px solid var(--border-color);"
                                    alt="{{ $req->guard_name }}"
                                    onerror="this.onerror=null; this.src='{{ asset('images/user-placeholder.png') }}';">
                                <div>
                                    <h6 class="fw-bold mb-0" style="color: var(--text-main);">{{ $req->guard_name }}</h6>
                                    <small style="color: var(--text-muted);"><i class="bi bi-geo-alt"></i>
                                        {{ $req->site_name }}</small>
                                </div>
                            </div>

                            <div>
                                @if ($req->status == 'Pending')
                                    <span class="badge badge-soft-warning rounded-pill">Pending</span>
                                @elseif ($req->status == 'Approved')
                                    <span class="badge badge-soft-success rounded-pill">Approved</span>
                                @else
                                    <span class="badge badge-soft-danger rounded-pill">Rejected</span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3 flex-grow-1 p-3 rounded"
                            style="background: var(--bg-body); border: 1px solid var(--border-color);">
                            <div class="row g-2">
                                <div class="col-12">
                                    <small
                                        style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">Date
                                        & Time</small>
                                    <div style="color: var(--text-main); font-size: 0.9rem; font-weight: 500;">
                                        {{ \Carbon\Carbon::parse($req->entryDateTime)->format('M d, Y • h:i A') }}
                                    </div>
                                </div>
                                <div class="col-12 mt-2">
                                    <small
                                        style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">Issue</small>
                                    <div style="color: var(--text-main); font-size: 0.9rem; font-weight: 500;">
                                        {{ $req->attendance_type }}
                                    </div>
                                </div>
                                <div class="col-12 mt-2">
                                    <small
                                        style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">Reason</small>
                                    <div style="color: var(--text-main); font-size: 0.85rem; line-height: 1.4;">
                                        {{ $req->remark ?: 'No reason provided.' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-auto pt-2">
                            @if ($req->status == 'Pending')
                                <div class="d-flex flex-column gap-2">

                                    <form method="POST" action="{{ url('attendance/requests/' . $req->id . '/reject') }}"
                                        class="d-flex gap-2 w-100">
                                        @csrf
                                        <input type="text" name="remark" class="form-control req-input w-100"
                                            placeholder="Rejection reason..." required>
                                        <button type="submit" class="btn btn-sm btn-reject px-3" title="Reject Request">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ url('attendance/requests/' . $req->id . '/approve') }}"
                                        class="w-100">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-approve w-100">
                                            <i class="bi bi-check-lg me-1"></i> Approve Request
                                        </button>
                                    </form>

                                </div>
                            @else
                                <div class="w-100 py-2 text-center rounded"
                                    style="background: var(--table-hover); border: 1px dashed var(--border-color); color: var(--text-muted); font-size: 0.85rem;">
                                    Processed
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <h5 style="color: var(--text-muted);">No attendance requests found.</h5>
                </div>
            @endforelse
        </div>

        {{-- =========================================
         LIST VIEW (TABLE)
    ========================================= --}}
        <div id="tableView" class="dash-card p-0 overflow-hidden d-none">
            <div class="table-responsive">
                <table class="table dash-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4">Employee</th>
                            <th>Details</th>
                            <th>Issue & Reason</th>
                            <th>Status</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $req)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $req->photo ? asset('storage/' . $req->photo) : asset('images/user-placeholder.png') }}"
                                            class="rounded-circle shadow-sm bg-light" width="40" height="40"
                                            style="object-fit:cover; border: 2px solid var(--border-color);"
                                            alt="{{ $req->guard_name }}"
                                            onerror="this.onerror=null; this.src='{{ asset('images/user-placeholder.png') }}';">
                                        <div>
                                            <div class="fw-semibold" style="color: var(--text-main);">
                                                {{ $req->guard_name }}
                                            </div>
                                            <small style="color: var(--text-muted);">{{ $req->site_name }}</small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div style="color: var(--text-main); font-weight: 500;">
                                        {{ \Carbon\Carbon::parse($req->entryDateTime)->format('M d, Y') }}
                                    </div>
                                    <small style="color: var(--text-muted);">
                                        {{ \Carbon\Carbon::parse($req->entryDateTime)->format('h:i A') }}
                                    </small>
                                </td>

                                <td>
                                    <div style="color: var(--sapphire-primary); font-weight: 600; font-size: 0.85rem;">
                                        {{ $req->attendance_type }}
                                    </div>
                                    <div style="color: var(--text-muted); font-size: 0.85rem; max-width: 250px;"
                                        class="text-truncate" title="{{ $req->remark }}">
                                        {{ $req->remark ?: 'No reason provided.' }}
                                    </div>
                                </td>

                                <td>
                                    @if ($req->status == 'Pending')
                                        <span class="badge badge-soft-warning rounded-pill">Pending</span>
                                    @elseif ($req->status == 'Approved')
                                        <span class="badge badge-soft-success rounded-pill">Approved</span>
                                    @else
                                        <span class="badge badge-soft-danger rounded-pill">Rejected</span>
                                    @endif
                                </td>

                                <td class="pe-4 text-end">
                                    @if ($req->status == 'Pending')
                                        <div class="d-flex align-items-center justify-content-end gap-2">

                                            <form method="POST"
                                                action="{{ url('attendance/requests/' . $req->id . '/approve') }}"
                                                class="m-0">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-approve" title="Approve">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>

                                            <form method="POST"
                                                action="{{ url('attendance/requests/' . $req->id . '/reject') }}"
                                                class="m-0 d-flex gap-2">
                                                @csrf
                                                <input type="text" name="remark" class="form-control req-input"
                                                    placeholder="Reject reason..." style="width: 140px; height: 31px;"
                                                    required>
                                                <button type="submit" class="btn btn-sm btn-reject" title="Reject">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>

                                        </div>
                                    @else
                                        <span style="color: var(--text-muted); font-size: 0.85rem;">Processed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5" style="color: var(--text-muted);">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                    No attendance requests found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-4 px-2">
            {{ $requests->links('pagination::bootstrap-5') }}
        </div>

    </div>

    <script>
        // Javascript to handle the View Toggle and save preference in localStorage
        function setView(viewType) {
            const gridView = document.getElementById('gridView');
            const tableView = document.getElementById('tableView');
            const btnGrid = document.getElementById('btnGrid');
            const btnTable = document.getElementById('btnTable');

            if (viewType === 'table') {
                gridView.classList.add('d-none');
                tableView.classList.remove('d-none');
                btnTable.classList.add('active');
                btnGrid.classList.remove('active');
                localStorage.setItem('requestViewPreference', 'table');
            } else {
                tableView.classList.add('d-none');
                gridView.classList.remove('d-none');
                btnGrid.classList.add('active');
                btnTable.classList.remove('active');
                localStorage.setItem('requestViewPreference', 'grid');
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            // Load user's last selected view
            const savedView = localStorage.getItem('requestViewPreference') || 'grid';
            setView(savedView);
        });
    </script>

@endsection

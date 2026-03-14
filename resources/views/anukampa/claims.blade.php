@extends('layouts.app')

@php
    $hideGlobalFilters = true;
    $hideBackground = true;
@endphp
@section('content')
    <style>
        /* Custom Toggle Switch matching your screenshot */
        .view-toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 28px;
        }

        .view-toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 26px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            border: 1px solid #cbd5e1;
            transition: .4s;
            border-radius: 16px;
        }

        input:checked+.slider {
            background-color: #0d6efd;
            /* Bootstrap Primary Blue */
            border-color: #0d6efd;
        }

        input:checked+.slider:before {
            transform: translateX(26px);
            border-color: transparent;
        }

        .status-badge {
            cursor: pointer;
            transition: transform 0.2s;
        }

        .status-badge:hover {
            transform: scale(1.05);
            opacity: 0.9;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">All Compensation Claims</h2>

            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted fw-bold">Grid</span>
                <label class="view-toggle-switch">
                    <input type="checkbox" id="viewToggleCheckbox" onchange="toggleView(this.checked)">
                    <span class="slider"></span>
                </label>
                <span class="small text-muted fw-bold">Table</span>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('anukampa.claims') }}" id="filterForm"
                    class="row g-3 align-items-end">

                    <div class="col-md-5">
                        <label class="form-label small text-muted mb-1 fw-semibold">Search</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent"><i data-lucide="search"
                                    style="width: 16px; height: 16px;"></i></span>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Victim, Village, ID... (Press Enter)" class="form-control">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1 fw-semibold">Type</label>
                        <select name="type" class="form-select"
                            onchange="document.getElementById('filterForm').submit()">
                            <option value="">All Types</option>
                            <option value="Crop Damage" {{ request('type') == 'Crop Damage' ? 'selected' : '' }}>Crop Damage
                            </option>
                            <option value="House Damage" {{ request('type') == 'House Damage' ? 'selected' : '' }}>House
                                Damage</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1 fw-semibold">Status</label>
                        <select name="status" class="form-select"
                            onchange="document.getElementById('filterForm').submit()">
                            <option value="">All Statuses</option>
                            <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Verified" {{ request('status') == 'Verified' ? 'selected' : '' }}>Verified
                            </option>
                            <option value="Compensated" {{ request('status') == 'Compensated' ? 'selected' : '' }}>
                                Compensated</option>
                            <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected
                            </option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <a href="{{ route('anukampa.claims') }}" class="btn btn-light w-100 border">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <div id="view-table" class="card shadow-sm mb-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="p-3">Case ID</th>
                            <th class="p-3">Victim</th>
                            <th class="p-3">Location</th>
                            <th class="p-3">Type</th>
                            <th class="p-3">Status (Click to Change)</th>
                            <th class="p-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($claims as $claim)
                            <tr>
                                <td class="p-3 font-monospace text-muted small">#{{ $claim->id }}</td>
                                <td class="p-3 fw-semibold">{{ $claim->victim_name }}</td>
                                <td class="p-3">{{ $claim->range }} / {{ $claim->village_name }}</td>
                                <td class="p-3">{{ $claim->incident_type }}</td>
                                <td class="p-3">
                                    <span onclick="openStatusModal({{ $claim->id }}, '{{ $claim->status }}')"
                                        class="badge rounded-pill status-badge 
                                    {{ $claim->status == 'Pending' ? 'text-bg-warning text-dark' : '' }}
                                    {{ $claim->status == 'Verified' ? 'text-bg-info text-dark' : '' }}
                                    {{ $claim->status == 'Compensated' ? 'text-bg-success' : '' }}
                                    {{ $claim->status == 'Rejected' ? 'text-bg-danger' : '' }}">
                                        {{ $claim->status }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('anukampa.show', $claim->id) }}"
                                            class="btn btn-sm btn-outline-primary">View</a>
                                        <a href="{{ route('anukampa.edit', $claim->id) }}"
                                            class="btn btn-sm btn-outline-secondary">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div id="view-grid" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 d-none mb-4">
            @foreach ($claims as $claim)
                <div class="col">
                    <div
                        class="card h-100 shadow-sm border-top border-3 {{ $claim->incident_type == 'Crop Damage' ? 'border-success' : 'border-info' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title fw-bold mb-1">{{ $claim->victim_name }}</h5>
                                    <p class="card-text small text-muted mb-0">{{ $claim->village_name }}</p>
                                </div>
                                <span onclick="openStatusModal({{ $claim->id }}, '{{ $claim->status }}')"
                                    class="badge status-badge {{ $claim->status == 'Pending' ? 'text-bg-warning' : ($claim->status == 'Verified' ? 'text-bg-info' : ($claim->status == 'Compensated' ? 'text-bg-success' : 'text-bg-danger')) }}">
                                    {{ $claim->status }}
                                </span>
                            </div>
                            <div class="d-flex gap-2 mt-4">
                                <a href="{{ route('anukampa.show', $claim->id) }}"
                                    class="btn btn-primary w-50 btn-sm">View</a>
                                <a href="{{ route('anukampa.edit', $claim->id) }}"
                                    class="btn btn-light border w-50 btn-sm">Edit</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-end mt-4">
            {{ $claims->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="statusUpdateForm" method="POST" action="">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title" id="statusModalLabel">Change Claim Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>You are about to change the status for this claim. Please select the new status below:</p>
                        <select name="status" id="modalStatusSelect" class="form-select">
                            <option value="Pending">Pending</option>
                            <option value="Verified">Verified</option>
                            <option value="Compensated">Compensated</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Confirm Change</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            lucide.createIcons();
            // Set toggle state based on localStorage or default to true (Table)
            const isTable = localStorage.getItem('viewPref') !== 'grid';
            document.getElementById('viewToggleCheckbox').checked = isTable;
            toggleView(isTable);
        });

        function toggleView(isTableChecked) {
            const tableContainer = document.getElementById('view-table');
            const gridContainer = document.getElementById('view-grid');

            if (isTableChecked) {
                tableContainer.classList.remove('d-none');
                gridContainer.classList.add('d-none');
                localStorage.setItem('viewPref', 'table');
            } else {
                tableContainer.classList.add('d-none');
                gridContainer.classList.remove('d-none');
                localStorage.setItem('viewPref', 'grid');
            }
        }

        function openStatusModal(claimId, currentStatus) {
            // Build the correct route dynamically
            const form = document.getElementById('statusUpdateForm');
            form.action = `/anukampa/claims/${claimId}/status`;

            // Pre-select current status
            document.getElementById('modalStatusSelect').value = currentStatus;

            // Show Bootstrap Modal
            var myModal = new bootstrap.Modal(document.getElementById('statusModal'));
            myModal.show();
        }
    </script>
@endsection

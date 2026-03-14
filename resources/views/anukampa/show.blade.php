@extends('layouts.app')
@php
    $hideGlobalFilters = true;
    $hideBackground = true;
@endphp
@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Claim Details: #{{ $claim->id }}</h2>
            <a href="{{ route('anukampa.claims') }}" class="btn btn-outline-secondary">Back to List</a>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light fw-bold">Incident Information</div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted">Victim Name</div>
                            <div class="col-sm-8 fw-semibold">{{ $claim->victim_name }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted">Location</div>
                            <div class="col-sm-8">{{ $claim->range }} / {{ $claim->village_name }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted">Coordinates</div>
                            <div class="col-sm-8 font-monospace">{{ $claim->latitude }}, {{ $claim->longitude }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted">Type / Animal</div>
                            <div class="col-sm-8">{{ $claim->incident_type }} ({{ $claim->animal_responsible ?? 'N/A' }})
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted">Estimated Loss</div>
                            <div class="col-sm-8 text-danger fw-bold">₹
                                {{ $claim->estimated_loss ? number_format($claim->estimated_loss, 2) : 'Not calculated' }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted">Remarks</div>
                            <div class="col-sm-8">{{ $claim->remarks ?? 'No remarks provided.' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light fw-bold">Current Status</div>
                    <div class="card-body text-center">
                        <h4 class="mb-3">
                            <span
                                class="badge rounded-pill 
                                {{ $claim->status == 'Pending' ? 'text-bg-warning text-dark' : '' }}
                                {{ $claim->status == 'Verified' ? 'text-bg-info text-dark' : '' }}
                                {{ $claim->status == 'Compensated' ? 'text-bg-success' : '' }}
                                {{ $claim->status == 'Rejected' ? 'text-bg-danger' : '' }}">
                                {{ $claim->status }}
                            </span>
                        </h4>
                        <button class="btn btn-primary w-100 mb-2"
                            onclick="openStatusModal({{ $claim->id }}, '{{ $claim->status }}')">Change Status</button>
                        <a href="{{ route('anukampa.edit', $claim->id) }}" class="btn btn-outline-secondary w-100">Edit
                            Claim Data</a>
                    </div>
                </div>
            </div>
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
                        <p>You are about to change the status for claim <strong>#<span
                                    id="modalClaimIdDisplay"></span></strong>. Please select the new status below:</p>
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
        function openStatusModal(claimId, currentStatus) {
            // Build the correct route dynamically
            const form = document.getElementById('statusUpdateForm');
            form.action = `/anukampa/claims/${claimId}/status`;

            // Display the ID in the modal text
            document.getElementById('modalClaimIdDisplay').innerText = claimId;

            // Pre-select current status in the dropdown
            document.getElementById('modalStatusSelect').value = currentStatus;

            // Show Bootstrap Modal
            var myModal = new bootstrap.Modal(document.getElementById('statusModal'));
            myModal.show();
        }
    </script>
@endsection

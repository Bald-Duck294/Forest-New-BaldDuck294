@php
    $hideGlobalFilters = true;
@endphp

@extends('layouts.app')

@section('title', 'Admin Details | ' . $admin->name)

@section('content')
<div class="container py-4">
    <nav class="d-flex align-items-center gap-2 mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small fw-medium">
            <li class="breadcrumb-item"><a href="{{ url('/global-dashboard') }}" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('global.admins') }}" class="text-decoration-none">Admins</a></li>
            <li class="breadcrumb-item active text-secondary" aria-current="page">Details</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h1 class="h3 fw-bold tracking-tight text-body mb-0">Admin Details</h1>
        <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary border-secondary-subtle px-4 py-2 fw-bold bg-body shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="max-w-4xl mx-auto">
    
        <div class="tab-content" id="adminTabsContent">
            <div class="tab-pane fade show active" id="details" role="tabpanel">
                <div class="card border-secondary-subtle rounded-4 shadow-sm bg-body overflow-hidden mb-4">
                    <div class="card-body p-4 p-md-5 border-bottom border-secondary-subtle">
                        <div class="row align-items-center g-4">
                            <div class="col-auto">
                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center fw-bold text-primary border border-4 border-body shadow"
                                     style="width: 96px; height: 96px; font-size: 2rem;">
                                    {{ strtoupper(substr($admin->name, 0, 1)) }}{{ strtoupper(substr(strrchr($admin->name, " "), 1, 1)) }}
                                </div>
                            </div>
                            <div class="col text-center text-sm-start">
                                <div class="d-flex flex-column flex-sm-row align-items-center gap-2 mb-1">
                                    <h2 class="h4 fw-bold text-body mb-0">{{ $admin->name }}</h2>
                                    @if($admin->isActive)
                                        <span class="badge rounded-pill theme-badge-active d-flex align-items-center gap-1 px-3 py-1">
                                            <span class="bg-success rounded-circle" style="width: 6px; height: 6px;"></span> Active
                                        </span>
                                    @else
                                        <span class="badge rounded-pill theme-badge-inactive d-flex align-items-center gap-1 px-3 py-1">
                                            <span class="bg-danger rounded-circle" style="width: 6px; height: 6px;"></span> Inactive
                                        </span>
                                    @endif
                                </div>
                                <p class="text-secondary fw-medium mb-0">@<span>{{ $admin->username }}</span></p>
                            </div>
                            <div class="col-12 col-md-auto">
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="{{ route('global.users.edit', $admin->id) }}" class="btn btn-primary px-4 fw-bold">Edit Profile</a>
                                    <button class="btn btn-outline-danger border-danger-subtle px-4 fw-bold">Disable</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="p-2 bg-body-tertiary rounded-3 text-secondary border border-secondary-subtle">
                                        <i class="bi bi-envelope fs-5"></i>
                                    </div>
                                    <div>
                                        <p class="text-uppercase small fw-bold text-secondary tracking-wider mb-1">Email Address</p>
                                        <p class="text-body fw-medium mb-0">{{ $admin->email }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="p-2 bg-body-tertiary rounded-3 text-secondary border border-secondary-subtle">
                                        <i class="bi bi-telephone fs-5"></i>
                                    </div>
                                    <div>
                                        <p class="text-uppercase small fw-bold text-secondary tracking-wider mb-1">Contact Number</p>
                                        <p class="text-body fw-medium mb-0">{{ $admin->contact }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="p-2 bg-body-tertiary rounded-3 text-secondary border border-secondary-subtle">
                                        <i class="bi bi-building fs-5"></i>
                                    </div>
                                    <div>
                                        <p class="text-uppercase small fw-bold text-secondary tracking-wider mb-1">Company Name</p>
                                        <p class="text-body fw-medium mb-0">{{ $admin->company_name }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="p-2 bg-body-tertiary rounded-3 text-secondary border border-secondary-subtle">
                                        <i class="bi bi-shield-lock fs-5"></i>
                                    </div>
                                    <div>
                                        <p class="text-uppercase small fw-bold text-secondary tracking-wider mb-1">Account Role</p>
                                        <p class="text-body fw-medium mb-0">System Administrator</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-secondary-subtle rounded-4 shadow-sm bg-body p-4 p-md-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="h5 fw-bold text-body mb-0">Recent Activity</h3>
                        <button class="btn btn-link text-primary fw-bold text-decoration-none p-0 small">View All</button>
                    </div>
                    <div class="position-relative">
                        <div class="position-absolute start-0 h-100 border-start border-secondary-subtle ms-3"></div>

                        <div class="d-flex gap-4 mb-4 position-relative z-1">
                            <div class="flex-shrink-0 bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;">
                                <i class="bi bi-box-arrow-in-right small"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <p class="small fw-bold text-body mb-0">Successful login</p>
                                    <span class="text-secondary" style="font-size: 0.7rem;">2 hours ago</span>
                                </div>
                                <p class="text-secondary small mb-0">IP: 192.168.1.1 (Chrome on MacOS)</p>
                            </div>
                        </div>

                        <div class="d-flex gap-4 mb-0 position-relative z-1">
                            <div class="flex-shrink-0 bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;">
                                <i class="bi bi-pencil small"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <p class="small fw-bold text-body mb-0">Updated security settings</p>
                                    <span class="text-secondary" style="font-size: 0.7rem;">Yesterday, 10:45 AM</span>
                                </div>
                                <p class="text-secondary small mb-0">Changed password configuration</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .tracking-wider { letter-spacing: 0.05em; }
    .max-w-4xl { max-width: 850px; }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "\F285";
        font-family: "bootstrap-icons";
        font-size: 10px;
        vertical-align: middle;
    }

    /* Theme-specific Badges */
    .theme-badge-active {
        background-color: rgba(5, 150, 105, 0.1) !important;
        color: #059669 !important;
        border: 1px solid rgba(5, 150, 105, 0.2);
    }
    .theme-badge-inactive {
        background-color: rgba(220, 38, 38, 0.1) !important;
        color: #dc2626 !important;
        border: 1px solid rgba(220, 38, 38, 0.2);
    }

    /* Tab Styling */
    .nav-tabs .nav-link:hover { color: var(--bs-primary); }
    .nav-tabs .nav-link.active { color: var(--bs-primary) !important; }

    /* Dark Mode Adjustments */
    [data-bs-theme="dark"] .bg-body-tertiary { background-color: rgba(255, 255, 255, 0.03) !important; }
    [data-bs-theme="dark"] .border-secondary-subtle { border-color: rgba(255, 255, 255, 0.1) !important; }
</style>
@endsection

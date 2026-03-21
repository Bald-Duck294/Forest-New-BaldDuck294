@php
$hideGlobalFilters = true;
@endphp

@extends('layouts.app')

@section('title', 'Superadmin Details')

@section('content')
<div class="container py-5">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2" style="font-size: 0.85rem;">
                <li class="breadcrumb-item"><a href="{{ url('/global-dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('global.superadmins') }}" class="text-decoration-none">Super Admins</a></li>
                <li class="breadcrumb-item active text-secondary" aria-current="page">Details</li>
            </ol>
        </nav>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <h1 class="h2 fw-black tracking-tight text-body mb-0">Super Admin Details</h1>
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary d-flex align-items-center gap-2 px-4 shadow-sm fw-bold">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-secondary-subtle rounded-4 shadow-sm bg-body overflow-hidden">
                <div class="card-body p-4 p-md-5 text-center border-bottom border-secondary-subtle bg-body-tertiary">
                    <div class="position-relative d-inline-block mb-4">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center fw-bold text-primary shadow-inner border border-primary border-opacity-10"
                            style="width: 120px; height: 120px; font-size: 3rem;">
                            {{ strtoupper(substr($superadmin->name, 0, 1)) }}
                        </div>
                        <span class="position-absolute bottom-0 end-0 p-2 border border-4 border-body rounded-circle {{ $superadmin->isActive ? 'bg-success' : 'bg-danger' }}"
                            style="width: 24px; height: 24px;"></span>
                    </div>

                    <div class="mb-2">
                        <h2 class="h3 fw-bold text-body d-inline-block align-middle me-2 mb-0">{{ $superadmin->name }}</h2>
                        @if($superadmin->isActive)
                        <span class="badge rounded-pill bg-success-subtle text-success small px-3">ACTIVE</span>
                        @else
                        <span class="badge rounded-pill bg-danger-subtle text-danger small px-3">INACTIVE</span>
                        @endif
                    </div>
                    <p class="text-secondary fw-medium">@<span>{{ $superadmin->username }}</span></p>
                </div>

                <div class="card-body p-4 p-md-5">
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-3">
                                <div class="p-2 bg-primary bg-opacity-10 rounded-3 text-primary">
                                    <i class="bi bi-envelope fs-5"></i>
                                </div>
                                <div>
                                    <p class="text-uppercase small fw-bold text-secondary tracking-wider mb-1">Email Address</p>
                                    <p class="text-body fw-medium mb-0">{{ $superadmin->email }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-3">
                                <div class="p-2 bg-primary bg-opacity-10 rounded-3 text-primary">
                                    <i class="bi bi-telephone fs-5"></i>
                                </div>
                                <div>
                                    <p class="text-uppercase small fw-bold text-secondary tracking-wider mb-1">Contact Number</p>
                                    <p class="text-body fw-medium mb-0">{{ $superadmin->contact }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-3">
                                <div class="p-2 bg-primary bg-opacity-10 rounded-3 text-primary">
                                    <i class="bi bi-building fs-5"></i>
                                </div>
                                <div>
                                    <p class="text-uppercase small fw-bold text-secondary tracking-wider mb-1">Company Name</p>
                                    <p class="text-body fw-medium mb-0">{{ $superadmin->company_name }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-3">
                                <div class="p-2 bg-primary bg-opacity-10 rounded-3 text-primary">
                                    <i class="bi bi-shield-check fs-5"></i>
                                </div>
                                <div>
                                    <p class="text-uppercase small fw-bold text-secondary tracking-wider mb-1">Role</p>
                                    <p class="text-body fw-medium mb-0">Super Admin</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-3 pt-4 border-top border-secondary-subtle">
                        <a href="{{ route('global.users.edit', $superadmin->id) }}" class="btn btn-primary btn-lg flex-fill fw-bold d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-pencil-square"></i> Edit Profile
                        </a>
                        <button class="btn btn-outline-danger btn-lg flex-fill fw-bold d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-person-x"></i> Deactivate Account
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-secondary-subtle rounded-4 shadow-sm bg-body h-100">
                <div class="card-header bg-transparent border-bottom border-secondary-subtle py-3 px-4 d-flex justify-content-between align-items-center">
                    <h3 class="h6 fw-bold mb-0">Recent Activity</h3>
                    <button class="btn btn-sm btn-link text-primary fw-bold text-decoration-none p-0">View All</button>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item bg-transparent border-secondary-subtle p-4">
                            <div class="d-flex gap-3">
                                <div class="flex-shrink-0 bg-body-tertiary rounded-circle d-flex align-items-center justify-content-center text-secondary" style="width: 36px; height: 36px;">
                                    <i class="bi bi-box-arrow-in-right"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="small text-body fw-medium mb-0">Logged in from San Francisco, CA</p>
                                    <p class="text-secondary mb-0" style="font-size: 0.75rem;">Oct 24, 2023 • 10:45 AM</p>
                                </div>
                                <span class="small text-secondary">2h ago</span>
                            </div>
                        </div>
                        <div class="list-group-item bg-transparent border-secondary-subtle p-4">
                            <div class="d-flex gap-3">
                                <div class="flex-shrink-0 bg-body-tertiary rounded-circle d-flex align-items-center justify-content-center text-secondary" style="width: 36px; height: 36px;">
                                    <i class="bi bi-shield-lock"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="small text-body fw-medium mb-0">Updated security preferences</p>
                                    <p class="text-secondary mb-0" style="font-size: 0.75rem;">Oct 23, 2023 • 04:20 PM</p>
                                </div>
                                <span class="small text-secondary">1d ago</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Styling for the specific high-end feel of the Tailwind design */
    .fw-black {
        font-weight: 900;
    }

    .tracking-tight {
        letter-spacing: -0.025em;
    }

    .tracking-wider {
        letter-spacing: 0.05em;
    }

    .bg-success-subtle {
        background-color: rgba(5, 150, 105, 0.1) !important;
        color: #059669 !important;
        border: 1px solid rgba(5, 150, 105, 0.2);
    }

    .bg-danger-subtle {
        background-color: rgba(220, 38, 38, 0.1) !important;
        color: #dc2626 !important;
        border: 1px solid rgba(220, 38, 38, 0.2);
    }

    /* Card background and border behavior in Dark Mode */
    [data-bs-theme="dark"] .card {
        background-color: var(--bs-body-bg);
        border-color: var(--bs-border-color-translucent) !important;
    }

    [data-bs-theme="dark"] .bg-body-tertiary {
        background-color: rgba(255, 255, 255, 0.02) !important;
    }

    .breadcrumb-item+.breadcrumb-item::before {
        content: "\F285";
        font-family: "bootstrap-icons";
        font-size: 10px;
    }
</style>
@endsection

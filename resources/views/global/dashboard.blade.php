@php
$hideGlobalFilters = true;
@endphp

@extends('layouts.app')


<style>
    /* DARK MODE SUPPORT */

    body.dark-mode {
        background: #0f172a;
        color: #e5e7eb;
    }

    body.dark-mode .card {
        background: #1e293b;
        border-color: #334155;
    }

    body.dark-mode .table {
        color: #e5e7eb;
    }

    body.dark-mode .table-light {
        background: #1e293b !important;
        color: #cbd5f5;
    }

    body.dark-mode .table-hover tbody tr:hover {
        background: #334155;
    }

    body.dark-mode .card-header {
        background: #1e293b !important;
        border-color: #334155;
    }

    body.dark-mode .text-muted {
        color: #94a3b8 !important;
    }

    body.dark-mode .badge.bg-success {
        background: #16a34a;
    }

    body.dark-mode .badge.bg-danger {
        background: #dc2626;
    }

    .kpi-card-hover {
        border: 1px solid var(--bs-border-color-translucent) !important;
    }

    .kpi-card-hover:hover {
        transform: translateY(-5px);
        border-color: var(--bs-primary) !important;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05) !important;
    }

    .transition-all {
        transition: all 0.25s ease-in-out;
    }

    .theme-badge-active {
        background-color: rgba(5, 150, 105, 0.1) !important;
        color: #059669 !important;
        font-size: 0.7rem;
    }

    .rounded-4 {
        border-radius: 20px !important;
    }

    .theme-badge-active {
        background-color: rgba(5, 150, 105, 0.1) !important;
        color: #059669 !important;
        border: 1px solid rgba(5, 150, 105, 0.2);
        font-size: 0.7rem;
    }

    .theme-badge-inactive {
        background-color: rgba(220, 38, 38, 0.1) !important;
        color: #dc2626 !important;
        border: 1px solid rgba(220, 38, 38, 0.2);
        font-size: 0.7rem;
    }

    .hover-primary:hover {
        color: var(--bs-primary) !important;
    }

    [data-bs-theme="dark"] .bg-body-tertiary {
        background-color: rgba(255, 255, 255, 0.03) !important;
    }
</style>
@section('content')

<div class="container-fluid py-4">

    <h4 class="fw-bold mb-4">Global Superadmin Dashboard</h4>

    <div class="row g-4 mb-4">

        <!-- TOTAL COMPANIES -->
        <div class="col-md-3">
            <a href="{{ route('global.companies') }}" class="text-decoration-none h-100 d-block">
                <div class="card shadow-sm border-0 h-100 kpi-card-hover bg-body transition-all">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-3">
                                <i class="bi bi-building text-primary"></i>
                            </div>
                            <span class="badge rounded-pill theme-badge-active fw-bold">+12%</span>
                        </div>

                        <p class="text-secondary small mb-1 fw-medium">Total Companies</p>
                        <h4 class="fw-bold text-body mb-0">{{ $totalCompanies }}</h4>
                    </div>
                </div>
            </a>
        </div>

        <!-- TOTAL SUPERADMIN -->
        <div class="col-md-3">
            <a href="{{ route('global.superadmins') }}" class="text-decoration-none text-dark d-block">
                <div class="card shadow-sm border-1 h-100 hover-shadow">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="bg-primary bg-opacity-10 p-2 rounded">
                                <i class="bi bi-shield-check text-primary"></i>
                            </div>
                        </div>

                        <p class="text-muted small mb-1">Total Superadmins</p>
                        <h4 class="fw-bold">{{ $totalSuperadmins }}</h4>

                    </div>
                </div>
            </a>
        </div>


        <!-- TOTAL ADMINS -->
        <div class="col-md-3">
            <a href="{{ route('global.admins') }}" class="text-decoration-none text-dark d-block">
                <div class="card shadow-sm border-1 h-100 hover-shadow">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="bg-primary bg-opacity-10 p-2 rounded">
                                <i class="bi bi-people text-primary"></i>
                            </div>
                        </div>

                        <p class="text-muted small mb-1">Total Admins</p>
                        <h4 class="fw-bold">{{ $totalAdmins }}</h4>

                    </div>
                </div>
            </a>
        </div>


        <!-- ACTIVE USERS -->
        <div class="col-md-3">
            <div class="card shadow-sm border-1 h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="bg-warning bg-opacity-10 p-2 rounded">
                            <i class="bi bi-lightning text-warning"></i>
                        </div>
                    </div>

                    <p class="text-muted small mb-1">Active Users</p>
                    <h4 class="fw-bold">{{ $activeUsers }}</h4>

                </div>
            </div>
        </div>

    </div>



    <!-- COMPANY TABLE -->


    <div class="card border-0 rounded-4 shadow-sm bg-body overflow-hidden mb-4">
        <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0 text-body">Recent Companies</h5>
                <small class="text-secondary">Latest organizations to join the platform</small>
            </div>
            <a href="{{ route('global.companies') }}" class="btn btn-sm btn-light border-secondary-subtle rounded-pill px-3 fw-bold text-primary shadow-sm">
                View All <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-body-tertiary text-secondary small text-uppercase fw-bold">
                        <tr>
                            <th class="px-4 py-3 border-0">Company</th>
                            <th class="py-3 border-0 text-center">Admins</th>
                            <th class="py-3 border-0 text-center">Status</th>
                            <th class="px-4 py-3 border-0 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($recentCompanies as $company)
                        <tr class="border-secondary-subtle">
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center shadow-sm"
                                        style="width: 38px; height: 38px; font-size: 0.75rem;">
                                        {{ strtoupper(substr($company->name, 0, 2)) }}
                                    </div>
                                    <div class="fw-bold text-body" style="font-size: 0.9rem;">{{ $company->name }}</div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 border-0">
                                    {{ $company->admin_count }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($company->isActive == 1)
                                <span class="badge rounded-pill theme-badge-active">Active</span>
                                @else
                                <span class="badge rounded-pill theme-badge-inactive">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-end">
                                <a href="{{ route('companies.edit', $company->id) }}" class="btn btn-link p-0 text-secondary hover-primary" title="Edit">
                                    <i class="bi bi-pencil-square fs-5"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-secondary bg-body">
                                <i class="bi bi-building fs-2 d-block mb-2 opacity-25"></i>
                                No recent companies found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

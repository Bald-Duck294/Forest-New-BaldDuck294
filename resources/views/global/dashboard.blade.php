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
</style>
@section('content')

<div class="container-fluid py-4">

    <h4 class="fw-bold mb-4">Global Superadmin Dashboard</h4>

    <div class="row g-4 mb-4">

        <!-- TOTAL COMPANIES -->
        <div class="col-md-3">
            <div class="card shadow-sm border-1 h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="bg-primary bg-opacity-10 p-2 rounded">
                            <i class="bi bi-building text-primary"></i>
                        </div>
                        <span class="badge bg-success">+12%</span>
                    </div>

                    <p class="text-muted small mb-1">Total Companies</p>
                    <h4 class="fw-bold">{{ $totalCompanies }}</h4>

                </div>
            </div>
        </div>


        <!-- TOTAL SUPERADMIN -->
        <div class="col-md-3">
            <div class="card shadow-sm border-1 h-100">
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
        </div>


        <!-- TOTAL ADMINS -->
        <div class="col-md-3">
            <div class="card shadow-sm border-1 h-100">
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

    <div class="card shadow-sm border-0">

        <div class="card-header d-flex justify-content-between align-items-center">

            <div>
                <h5 class="mb-0 fw-bold">All Companies</h5>
                <small class="text-muted">Manage all companies</small>
            </div>

            <a href="{{ route('companies.create') }}" class="btn btn-primary btn-sm">
                + Add Company
            </a>

        </div>


        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">
                    <tr>
                        <th>Company</th>
                        <th>Industry</th>
                        <th>Total Admins</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>


                <tbody>

                    @foreach($companies as $company)

                    <tr>

                        <td>
                            <div class="d-flex align-items-center gap-2">

                                <div class="rounded bg-primary bg-opacity-10 text-primary fw-bold d-flex justify-content-center align-items-center"
                                    style="width:40px;height:40px">

                                    {{ strtoupper(substr($company->name,0,2)) }}

                                </div>

                                <div>
                                    <div class="fw-semibold">{{ $company->name }}</div>
                                    <small class="text-muted">Company ID: {{ $company->id }}</small>
                                </div>

                            </div>
                        </td>


                        <td class="text-muted">
                            {{ $company->type ?? '-' }}
                        </td>


                        <td>
                            {{ $company->admin_count }}
                        </td>


                        <td>

                            @if($company->isActive == 1)
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-danger">Inactive</span>
                            @endif

                        </td>


                        <td class="text-end">

                            <a href="{{ route('companies.edit',$company->id) }}"
                                class="btn btn-sm btn-outline-primary">
                                Edit
                            </a>

                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>


</div>
@endsection
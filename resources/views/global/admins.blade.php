@php
$hideGlobalFilters = true;
@endphp

@extends('layouts.app')

@section('title', 'Admin Management')

@section('content')


<div class="container py-4">
    <nav class="d-flex justify-content-between align-items-center mb-4" aria-label="breadcrumb">
        <div class="d-flex align-items-center">
            <ol class="breadcrumb mb-0 small fw-medium">
                <li class="breadcrumb-item">
                    <a href="{{ url('/global-dashboard') }}" class="text-primary text-decoration-none d-flex align-items-center gap-1">
                        <i class="bi bi-house-door"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item active text-secondary" aria-current="page">Admins</li>
            </ol>
        </div>

        <a href="{{ url('/global-dashboard') }}" class="btn btn-sm btn-outline-secondary border-secondary-subtle fw-bold d-flex align-items-center gap-2 px-3 shadow-sm bg-body">
            <i class="bi bi-arrow-left"></i>
            <span class="d-none d-md-inline">Back to Dashboard</span>
        </a>
    </nav>

    <div class="row align-items-end mb-4 g-3">
        <div class="col-md-7">
            <h2 class="h3 fw-black text-body mb-1">Admin management</h2>
            <p class="text-secondary small mb-0">Manage your system administrators and their organizational roles.</p>
        </div>
        <div class="col-md-5 text-md-end">
            <div class="d-flex gap-2 justify-content-md-end">
                <button class="btn btn-outline-secondary btn-sm px-3 d-flex align-items-center gap-2 bg-body">
                    <i class="bi bi-download"></i> Export
                </button>
                <button class="btn btn-primary btn-sm px-3 d-flex align-items-center gap-2 shadow-sm">
                    <i class="bi bi-plus-lg"></i> New Admin
                </button>
            </div>
        </div>
    </div>

    <div class="card border-secondary-subtle rounded-4 shadow-sm mb-4 bg-body">
        <div class="card-body p-3">
            <form action="{{ route('global.admins') }}" method="GET">
                <div class="row g-3 align-items-center">
                    <div class="col-md-5 col-lg-4">
                        <div class="input-group">
                            <span class="input-group-text bg-body-tertiary border-secondary-subtle text-secondary">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control bg-body-tertiary border-secondary-subtle text-body" placeholder="Search admins...">
                        </div>
                    </div>
                    <div class="col-md-7 col-lg-8">
                        <div class="d-flex gap-2 overflow-auto pb-1 pb-md-0">
                            <a href="{{ route('global.admins', ['search' => request('search')]) }}"
                                class="btn btn-sm {{ !request('status') ? 'btn-primary-subtle' : 'btn-outline-secondary' }} rounded-pill px-3 fw-bold border-0">
                                All Admins
                            </a>

                            <a href="{{ route('global.admins', ['status' => 'active', 'search' => request('search')]) }}"
                                class="btn btn-sm {{ request('status') == 'active' ? 'btn-primary-subtle' : 'btn-outline-secondary' }} rounded-pill px-3 fw-bold border-0">
                                Active
                            </a>

                            <a href="{{ route('global.admins', ['status' => 'inactive', 'search' => request('search')]) }}"
                                class="btn btn-sm {{ request('status') == 'inactive' ? 'btn-primary-subtle' : 'btn-outline-secondary' }} rounded-pill px-3 fw-bold border-0">
                                Inactive
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-secondary-subtle rounded-4 shadow-sm bg-body overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-secondary small text-uppercase fw-bold">
                    <tr>
                        <th class="px-4 py-3 border-0">Admin</th>

                        <th class="py-3 border-0">Email</th>
                        <th class="py-3 border-0">Company</th>
                        <th class="py-3 border-0">Status</th>
                        <th class="px-4 py-3 border-0 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($admins as $admin)
                    <tr class="border-secondary-subtle">
                        <td class="px-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center fw-bold text-primary"
                                    style="width: 40px; height: 40px; font-size: 0.85rem;">
                                    {{ strtoupper(substr($admin->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-body mb-0" style="font-size: 0.9rem;">{{ $admin->name }}</div>
                                    <div class="text-secondary" style="font-size: 0.75rem;">
                                        Joined {{ $admin->created_at ? \Carbon\Carbon::parse($admin->created_at)->format('M Y') : 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="text-secondary small">{{ $admin->email }}</td>
                        <td>
                            <span class="text-body-secondary small">{{ $admin->company_name }}</span>
                        </td>
                        <td>
                            @if($admin->isActive)
                            <span class="badge rounded-pill theme-badge-active">Active</span>
                            @else
                            <span class="badge rounded-pill theme-badge-inactive">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('global.admins.view', $admin->id) }}" class="btn btn-outline-secondary border-0 bg-transparent text-secondary p-1 px-2" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('global.users.edit', $admin->id) }}" class="btn btn-outline-secondary border-0 bg-transparent text-secondary p-1 px-2" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <!-- <button class="btn btn-outline-danger border-0 bg-transparent p-1 px-2" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button> -->
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-secondary bg-body">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            No Admins Found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-body-tertiary py-3 px-4 border-top border-secondary-subtle">
            <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 small text-secondary">
                <span>
                    Showing <span class="text-body fw-bold">{{ $admins->firstItem() ?? 0 }}</span>
                    to <span class="text-body fw-bold">{{ $admins->lastItem() ?? 0 }}</span>
                    of <span class="text-body fw-bold">{{ $admins->total() }}</span> results
                </span>
                <div class="theme-pagination">
                    {{ $admins->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .backdrop-blur {
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    .fw-black {
        font-weight: 900;
    }

    .btn-primary-subtle {
        background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
        color: var(--bs-primary) !important;
    }

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

    [data-bs-theme="dark"] .table-hover tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.03);
    }

    [data-bs-theme="dark"] .bg-body-tertiary {
        background-color: #1a232c !important;
    }

    .breadcrumb-item+.breadcrumb-item::before {
        content: "\F285";
        /* Bootstrap Icon Chevron Right */
        font-family: "bootstrap-icons";
        font-size: 10px;
        vertical-align: middle;
        color: var(--bs-secondary-color);
    }

    /* Subtle hover effect for the back button */
    .btn-outline-secondary:hover {
        background-color: var(--bs-tertiary-bg) !important;
        color: var(--bs-primary) !important;
        border-color: var(--bs-primary) !important;
    }
</style>
@endsection

@php
$hideGlobalFilters = true;
@endphp

@extends('layouts.app')

@section('title', 'Super Admins')

@section('content')
<div class="bg-body border-bottom py-3 mb-4 transition-all">
    <div class="container">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="p-2 bg-primary bg-opacity-10 rounded-3 text-primary border border-primary border-opacity-25">
                    <i class="bi bi-shield-lock fs-4"></i>
                </div>
                <div>
                    <h1 class="h4 fw-bold mb-0 text-body">Super Admin Management</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                            <li class="breadcrumb-item"><a href="{{ url('/global-dashboard') }}" class="text-primary text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item active text-secondary">Super Admins</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <form method="GET" action="{{ route('global.superadmins') }}" class="mb-0">
                    <div class="input-group input-group-sm d-none d-sm-flex" style="width: 250px;">
                        <span class="input-group-text bg-body-tertiary border-secondary-subtle">
                            <i class="bi bi-search text-secondary"></i>
                        </span>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            class="form-control bg-body-tertiary border-secondary-subtle text-body"
                            placeholder="Search admins...">
                    </div>
                </form>
                <a href="{{ url('/global-dashboard') }}" class="btn btn-sm btn-outline-secondary fw-semibold d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left"></i> <span class="d-none d-md-inline">Back to Dashboard</span>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="card border-secondary-subtle shadow-sm rounded-4 overflow-hidden bg-body">

        <div class="card-header bg-body-tertiary border-bottom border-secondary-subtle py-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="btn-group btn-group-sm">
                    <a href="{{ route('global.superadmins') }}"
                        class="btn {{ request('status') ? 'btn-outline-secondary' : 'btn-primary' }}">
                        All Status
                    </a>
                    <a href="{{ route('global.superadmins', ['status' => 'active']) }}"
                        class="btn {{ request('status') == 'active' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        Active
                    </a>
                    <a href="{{ route('global.superadmins', ['status' => 'inactive']) }}"
                        class="btn {{ request('status') == 'inactive' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        Inactive
                    </a>
                </div>

                <button class="btn btn-sm btn-link text-secondary text-decoration-none p-0 border-0">
                    <i class="bi bi-filter"></i> Filter
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-secondary small text-uppercase fw-bold">
                    <tr>
                        <th class="px-4 py-3 border-0">Name</th>

                        <th class="py-3 border-0">Email</th>
                        <th class="py-3 border-0">Contact</th>
                        <th class="py-3 border-0">Company</th>
                        <th class="py-3 border-0 text-center">Status</th>
                        <th class="px-4 py-3 border-0 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($superadmins as $admin)
                    <tr class="border-secondary-subtle">
                        <td class="px-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center fw-bold text-primary border border-primary border-opacity-10"
                                    style="width: 40px; height: 40px;">
                                    {{ strtoupper(substr($admin->name, 0, 1)) }}
                                </div>
                                <span class="fw-semibold text-body">{{ $admin->name }}</span>
                            </div>
                        </td>

                        <td class="small">
                            <span class="text-decoration-underline text-secondary-emphasis">
                                {{ $admin->email ?? '-' }}
                            </span>
                        </td>
                        <td class="text-secondary small">{{ $admin->contact }}</td>
                        <td>
                            <span class="badge rounded-1 bg-body-secondary text-body-emphasis border border-secondary-subtle fw-medium px-2 py-1">
                                {{ $admin->company_name }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($admin->isActive)
                            <span class="badge rounded-pill theme-badge-active px-3">Active</span>
                            @else
                            <span class="badge rounded-pill theme-badge-inactive px-3">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-end">
                            <div class="btn-group btn-group-sm">

                                <a href="{{ route('global.superadmins.view', $admin->id) }}" class="btn btn-outline-secondary border-0 bg-transparent text-secondary p-1 px-2" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('global.users.edit', $admin->id) }}" class="btn btn-outline-secondary border-0 bg-transparent text-secondary p-1 px-2" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-secondary">
                            <i class="bi bi-info-circle fs-2 d-block mb-2"></i>
                            No Super Admins Found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-body-tertiary py-3 px-4 border-top border-secondary-subtle">
            <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 small text-secondary">
                <span>
                    Showing <span class="text-body fw-bold">{{ $superadmins->firstItem() }}</span>
                    to <span class="text-body fw-bold">{{ $superadmins->lastItem() }}</span>
                    of <span class="text-body fw-bold">{{ $superadmins->total() }}</span>
                </span>
                <div class="theme-pagination">
                    {{ $superadmins->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Theme-specific Badge Colors */
    .theme-badge-active {
        background-color: rgba(5, 150, 105, 0.15) !important;
        color: #059669 !important;
        border: 1px solid rgba(5, 150, 105, 0.2);
    }

    .theme-badge-inactive {
        background-color: rgba(220, 38, 38, 0.15) !important;
        color: #dc2626 !important;
        border: 1px solid rgba(220, 38, 38, 0.2);
    }

    /* Breadcrumb custom arrow icon */
    .breadcrumb-item+.breadcrumb-item::before {
        content: "\F285";
        font-family: "bootstrap-icons";
        font-size: 10px;
        color: var(--bs-secondary-color);
    }

    /* Transitions for smoother theme switching */
    .transition-all {
        transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
    }

    /* Fix for dark mode pagination borders */
    .theme-pagination .pagination {
        margin-bottom: 0;
    }

    .theme-pagination .page-link {
        background-color: transparent;
        border-color: var(--bs-border-color-translucent);
        color: var(--bs-secondary-color);
    }

    .theme-pagination .page-item.active .page-link {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
        color: #fff;
    }

    /* Table styling for modern dashboard feel */
    .table thead th {
        font-size: 0.7rem;
        letter-spacing: 0.05em;
    }

    .table-hover tbody tr:hover {
        background-color: var(--bs-tertiary-bg);
    }
</style>
@endsection

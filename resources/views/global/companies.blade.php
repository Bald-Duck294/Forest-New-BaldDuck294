@php $hideGlobalFilters = true; @endphp
@extends('layouts.app')

@section('title', 'Company Management')

@section('content')
    <div class="container py-4">
        <nav class="d-flex justify-content-between align-items-center mb-4" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small fw-medium">
                <li class="breadcrumb-item"><a href="{{ url('/global-dashboard') }}"
                        class="text-decoration-none text-primary">Dashboard</a></li>
                <li class="breadcrumb-item active text-secondary">Companies</li>
            </ol>
            <a href="{{ route('companies.create') }}"
                class="btn btn-primary btn-sm px-3 rounded-3 shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i> Add Company
            </a>
        </nav>

        <div class="mb-4">
            <h2 class="h3 fw-black text-body mb-1">Company Directory</h2>
            <p class="text-secondary small">Manage organizational units, industries, and active statuses.</p>
        </div>

        <div class="card border-0 rounded-4 shadow-sm mb-4 bg-body">
            <div class="card-body p-3">
                <form action="{{ route('global.companies') }}" method="GET" class="row g-3 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-body-tertiary border-0 text-secondary"><i
                                    class="bi bi-search"></i></span>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="form-control bg-body-tertiary border-0 text-body"
                                placeholder="Search by company name...">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 rounded-4 shadow-sm bg-body overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-body-tertiary text-secondary small text-uppercase fw-bold">
                        <tr>
                            <th class="px-4 py-3 border-0">Company</th>
                            <th class="py-3 border-0">Industry</th>
                            <th class="py-3 border-0 text-center">Admins</th>
                            <th class="py-3 border-0 text-center">Status</th>
                            <th class="px-4 py-3 border-0 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($companies as $company)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary fw-bold d-flex justify-content-center align-items-center shadow-sm"
                                            style="width:40px; height:40px; font-size: 0.8rem;">
                                            {{ strtoupper(substr($company->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-body mb-0">{{ $company->name }}</div>
                                            <div class="text-secondary tiny">ID: #{{ $company->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-secondary small">{{ $company->type ?? 'General' }}</td>
                                <td class="text-center">
                                    <span class="fw-bold text-primary bg-primary bg-opacity-10 px-2 py-1 rounded-2 small">
                                        {{ $company->admin_count }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if ($company->isActive == 1)
                                        <span class="badge rounded-pill theme-badge-active px-3">Active</span>
                                    @else
                                        <span class="badge rounded-pill theme-badge-inactive px-3">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('companies.edit', $company->id) }}"
                                            class="btn btn-outline-secondary border-0 bg-transparent text-secondary p-1 px-2"
                                            title="Edit">
                                            <i class="bi bi-pencil-square fs-6"></i>
                                        </a>
                                        <button class="btn btn-outline-danger border-0 bg-transparent p-1 px-2"
                                            title="Archive">
                                            <i class="bi bi-archive fs-6"></i>
                                        </button>
                                        <a href="{{ route('global.enter_simulation', $company->id) }}"
                                            class="btn btn-sm btn-outline-primary border-0 bg-primary bg-opacity-10">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-secondary">
                                    <i class="bi bi-building fs-1 d-block mb-3 opacity-25"></i>
                                    No companies found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($companies->hasPages())
                <div class="card-footer bg-body-tertiary border-0 py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center small text-secondary">
                        <span>Showing {{ $companies->firstItem() }} to {{ $companies->lastItem() }}</span>
                        {{ $companies->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <style>
        .fw-black {
            font-weight: 900;
        }

        .tiny {
            font-size: 0.7rem;
        }

        .rounded-4 {
            border-radius: 20px !important;
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

        .table-hover tbody tr:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.02);
        }
    </style>
@endsection

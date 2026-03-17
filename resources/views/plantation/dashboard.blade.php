@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    $user = session('user');
@endphp

@extends('layouts.app')

@section('title', 'Plantation Dashboard')

@section('content')

    <style>
        /* =========================================
                           LOCAL COMPONENT STYLES
                           (Hooked to Global Sapphire Variables)
                        ========================================= */

        /* Interactive Hover Lift */
        .hover-lift {
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
            cursor: pointer;
        }

        .hover-lift:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
            border-color: var(--sapphire-primary);
        }

        /* Primary Action Button */
        .btn-sapphire {
            background-color: var(--sapphire-primary);
            color: #ffffff;
            border: none;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-sapphire:hover {
            background-color: var(--sapphire-primary);
            opacity: 0.9;
            color: #ffffff;
            transform: translateY(-1px);
        }

        /* Soft Badges */
        .badge-soft {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-soft-primary {
            background: rgba(59, 130, 246, 0.15);
            color: var(--sapphire-primary);
        }

        .badge-soft-success {
            background: rgba(16, 185, 129, 0.15);
            color: var(--sapphire-success);
        }

        .badge-soft-warning {
            background: rgba(245, 158, 11, 0.15);
            color: var(--sapphire-warning);
        }

        .badge-soft-muted {
            background: rgba(100, 116, 139, 0.15);
            color: var(--text-muted);
        }

        /* Workflow Link Button */
        .btn-workflow {
            background: rgba(59, 130, 246, 0.1);
            color: var(--sapphire-primary);
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            display: inline-block;
        }

        .btn-workflow:hover {
            background: var(--sapphire-primary);
            color: #ffffff;
        }
    </style>

    <div class="container-fluid py-4">

        {{-- HEADER & ACTION --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--text-main);">Plantation Dashboard</h3>
                <p class="mb-0" style="color: var(--text-muted); font-size: 0.9rem;">Overview of all plantation sites and
                    their current phases.</p>
            </div>
            <div>
                <a href="{{ route('plantation.create') }}" class="btn-sapphire shadow-sm">
                    <i class="bi bi-plus-lg"></i> Add Plantation
                </a>
            </div>
        </div>

        {{-- FLASH MESSAGE --}}
        @if (session('success'))
            <div class="alert alert-dismissible fade show shadow-sm"
                style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--sapphire-success); color: var(--sapphire-success); border-radius: 8px;">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button class="btn-close" data-bs-dismiss="alert" style="filter: opacity(0.5);"></button>
            </div>
        @endif

        {{-- KPI STATS CARDS --}}
        <div class="row g-3 mb-4">

            <div class="col-12 col-md-6 col-lg-3">
                <a href="{{ url('plantation/analytics') }}" class="text-decoration-none">
                    <div class="dash-card hover-lift h-100 p-4">
                        <h6
                            style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; font-weight: 600; margin-bottom: 0.5rem;">
                            Total Plantations
                        </h6>
                        <h2 style="color: var(--text-main); font-weight: 700; margin: 0;">
                            {{ $plantations->count() }}
                        </h2>
                    </div>
                </a>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <a href="{{ url('plantation/analytics?status=active') }}" class="text-decoration-none">
                    <div class="dash-card hover-lift h-100 p-4">
                        <h6
                            style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; font-weight: 600; margin-bottom: 0.5rem;">
                            Active
                        </h6>
                        <h2 style="color: var(--sapphire-primary); font-weight: 700; margin: 0;">
                            {{ $plantations->where('status', 'active')->count() }}
                        </h2>
                    </div>
                </a>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <a href="{{ url('plantation/analytics?status=completed') }}" class="text-decoration-none">
                    <div class="dash-card hover-lift h-100 p-4">
                        <h6
                            style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; font-weight: 600; margin-bottom: 0.5rem;">
                            Completed
                        </h6>
                        <h2 style="color: var(--sapphire-success); font-weight: 700; margin: 0;">
                            {{ $plantations->where('status', 'completed')->count() }}
                        </h2>
                    </div>
                </a>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <a href="{{ url('plantation/analytics?status=pending') }}" class="text-decoration-none">
                    <div class="dash-card hover-lift h-100 p-4">
                        <h6
                            style="color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; font-weight: 600; margin-bottom: 0.5rem;">
                            Pending
                        </h6>
                        <h2 style="color: var(--sapphire-warning); font-weight: 700; margin: 0;">
                            {{ $plantations->where('status', 'pending')->count() }}
                        </h2>
                    </div>
                </a>
            </div>

        </div>

        {{-- PLANTATION TABLE --}}
        <div class="dash-card p-0 overflow-hidden">

            <div class="p-4 pb-3" style="border-bottom: 1px solid var(--border-color);">
                <h5 class="fw-bold mb-0" style="color: var(--text-main);">Plantation List</h5>
            </div>

            <div class="table-responsive">
                <table class="table table-borderless dash-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4">Code</th>
                            <th>Name</th>
                            <th>Site</th>
                            <th>Phase</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plantations as $pln)
                            <tr>
                                <td class="ps-4 fw-bold" style="color: var(--sapphire-primary);">
                                    {{ $pln->code }}
                                </td>

                                <td>
                                    <a href="{{ route('plantation.show', $pln->id) }}"
                                        class="fw-semibold text-decoration-none hover-link"
                                        style="color: var(--text-main); transition: color 0.2s ease;">
                                        {{ $pln->name }}
                                    </a>
                                </td>

                                <td>
                                    <span class="badge-soft badge-soft-muted">
                                        <i class="bi bi-geo-alt-fill me-1 opacity-75"></i> {{ $pln->site->name ?? 'N/A' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge-soft badge-soft-primary">
                                        {{ ucfirst($pln->current_phase) }}
                                    </span>
                                </td>

                                <td>
                                    @if ($pln->status == 'completed')
                                        <span class="badge-soft badge-soft-success">Completed</span>
                                    @elseif($pln->status == 'active')
                                        <span class="badge-soft badge-soft-primary">Active</span>
                                    @else
                                        <span class="badge-soft badge-soft-warning">Pending</span>
                                    @endif
                                </td>

                                <td class="text-end pe-4">
                                    <a href="{{ route('plantation.workflow', $pln->id) }}" class="btn-workflow">
                                        Workflow &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5" style="color: var(--text-muted);">
                                    <i class="bi bi-tree fs-2 d-block mb-2 opacity-50"></i>
                                    No plantations found in the system.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>

@endsection

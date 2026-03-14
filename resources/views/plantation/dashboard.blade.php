@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    $user = session('user');
@endphp
@extends('layouts.app')

@section('title', 'Plantation Dashboard')

@section('content')

    <div class="container-fluid py-4">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">🌳 Plantation Dashboard</h3>
                <small class="text-muted">Monitor and manage plantation workflows</small>
            </div>

            <a href="{{ route('plantation.create') }}" class="btn btn-primary shadow-sm">
                + Add Plantation
            </a>
        </div>


        {{-- STATS CARDS --}}
        <div class="row mb-4">

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="text-muted">Total Plantations</h6>
                        <h3 class="fw-bold">{{ $plantations->count() }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="text-muted">Active</h6>
                        <h3 class="fw-bold text-primary">
                            {{ $plantations->where('status', 'active')->count() }}
                        </h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="text-muted">Completed</h6>
                        <h3 class="fw-bold text-success">
                            {{ $plantations->where('status', 'completed')->count() }}
                        </h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="text-muted">Pending</h6>
                        <h3 class="fw-bold text-warning">
                            {{ $plantations->where('status', 'pending')->count() }}
                        </h3>
                    </div>
                </div>
            </div>

        </div>


        {{-- FLASH MESSAGE --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif


        {{-- PLANTATION TABLE --}}
        <div class="card shadow-sm border-0">

            <div class="card-header bg-white fw-semibold">
                Plantation List
            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Site</th>
                                <th>Phase</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($plantations as $pln)
                                <tr>

                                    <td class="fw-semibold text-primary">
                                        {{ $pln->code }}
                                    </td>

                                    <td>
                                        <a href="{{ route('plantation.show', $pln->id) }}"
                                            class="fw-semibold text-decoration-none">
                                            {{ $pln->name }}
                                        </a>
                                    </td>

                                    <td>
                                        <span class="badge bg-secondary-subtle text-dark">
                                            {{ $pln->site->name ?? 'N/A' }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="badge bg-info-subtle text-dark">
                                            {{ ucfirst($pln->current_phase) }}
                                        </span>
                                    </td>

                                    <td>

                                        @if ($pln->status == 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($pln->status == 'active')
                                            <span class="badge bg-primary">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Pending</span>
                                        @endif

                                    </td>

                                    <td class="text-end">

                                        <a href="{{ route('plantation.workflow', $pln->id) }}"
                                            class="btn btn-success btn-sm">
                                            Workflow →
                                        </a>

                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No plantations found
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

@php
$hideGlobalFilters = true;
$hideBackground = true;
$user = session('user');
@endphp
@extends('layouts.app')

@section('title', 'Plantation Dashboard')


<style>
    .dashboard-card {
        transition: all 0.25s ease;
        cursor: pointer;

    }

    .dashboard-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
    }
</style>
@section('content')

<div class="container-fluid py-4">


    {{-- STATS CARDS --}}
    <div class="row mb-4 align-items-center">

        {{-- CARDS --}}
        <div class="col-md-9">

            <div class="row g-3">

                <div class="col-md-3">

                    <a href="{{ url('plantation/analytics') }}" class="text-decoration-none">

                        <div class="card shadow-sm border h-100 dashboard-card">

                            <div class="card-body">

                                <h6 class="text-body-secondary">Total Plantations</h6>

                                <h3 class="fw-bold">
                                    {{ $plantations->count() }}
                                </h3>

                            </div>

                        </div>

                    </a>

                </div>

                <div class="col-md-3">

                    <a href="{{ url('plantation/analytics?status=active') }}" class="text-decoration-none">

                        <div class="card shadow-sm border h-100 dashboard-card">

                            <div class="card-body">

                                <h6 class="text-body-secondary">Active</h6>

                                <h3 class="fw-bold text-primary">
                                    {{ $plantations->where('status','active')->count() }}
                                </h3>

                            </div>

                        </div>

                    </a>

                </div>

                <div class="col-md-3">

                    <a href="{{ url('plantation/analytics?status=completed') }}" class="text-decoration-none">

                        <div class="card shadow-sm border h-100 dashboard-card">

                            <div class="card-body">

                                <h6 class="text-body-secondary">Completed</h6>

                                <h3 class="fw-bold text-success">
                                    {{ $plantations->where('status','completed')->count() }}
                                </h3>

                            </div>

                        </div>

                    </a>

                </div>

                <div class="col-md-3">

                    <a href="{{ url('plantation/analytics?status=pending') }}" class="text-decoration-none">

                        <div class="card shadow-sm border h-100 dashboard-card">

                            <div class="card-body">

                                <h6 class="text-body-secondary">Pending</h6>

                                <h3 class="fw-bold text-warning">
                                    {{ $plantations->where('status','pending')->count() }}
                                </h3>

                            </div>

                        </div>

                    </a>

                </div>

            </div>

        </div>

        {{-- ADD BUTTON --}}
        <div class="col-md-2 d-flex justify-content-end">

            <a href="{{ route('plantation.create') }}"
                class="btn btn-primary shadow-sm w-100">

                + Add Plantation

            </a>

        </div>

    </div>


    {{-- FLASH MESSAGE --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif


    {{-- PLANTATION TABLE --}}
    <div class="card shadow-sm border">

        <div class="card-header bg-body fw-semibold">
            Plantation List
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-secondary">
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
                                <a href="{{ route('plantation.show',$pln->id) }}"
                                    class="fw-semibold text-decoration-none text-body">
                                    {{ $pln->name }}
                                </a>
                            </td>

                            <td>
                                <span class="badge bg-secondary-subtle text-body">
                                    {{ $pln->site->name ?? 'N/A' }}
                                </span>
                            </td>

                            <td>
                                <span class="badge bg-info-subtle text-body">
                                    {{ ucfirst($pln->current_phase) }}
                                </span>
                            </td>

                            <td>

                                @if($pln->status == 'completed')
                                <span class="badge bg-success">Completed</span>

                                @elseif($pln->status == 'active')
                                <span class="badge bg-primary">Active</span>

                                @else
                                <span class="badge bg-secondary">Pending</span>
                                @endif

                            </td>

                            <td class="text-end">

                                <a href="{{ route('plantation.workflow',$pln->id) }}"
                                    class="btn btn-success btn-sm">
                                    Workflow →
                                </a>

                            </td>

                        </tr>

                        @empty

                        <tr>
                            <td colspan="6" class="text-center text-body-secondary py-4">
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
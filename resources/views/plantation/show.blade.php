@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    $user = session('user');
@endphp
@extends('layouts.app')

@section('title', 'Plantation Details')

@section('content')

    <div class="container py-4">

        <h4 class="fw-bold mb-4">Plantation Details</h4>

        <div class="card shadow-sm mb-4">
            <div class="card-body">

                <div class="row">

                    <div class="col-md-4">
                        <b>Code</b><br>
                        {{ $plantation->code }}
                    </div>

                    <div class="col-md-4">
                        <b>Name</b><br>
                        {{ $plantation->name }}
                    </div>

                    <div class="col-md-4">
                        <b>Site</b><br>
                        {{ $plantation->site->name ?? 'N/A' }}
                    </div>

                </div>

                <hr>

                <div class="row">

                    <div class="col-md-4">
                        <b>Area</b><br>
                        {{ $plantation->area ?? '-' }}
                    </div>

                    <div class="col-md-4">
                        <b>Soil Type</b><br>
                        {{ $plantation->soil_type ?? '-' }}
                    </div>

                    <div class="col-md-4">
                        <b>Plant Species</b><br>
                        {{ $plantation->plant_species ?? '-' }}
                    </div>

                </div>

                <hr>

                <div class="row">

                    <div class="col-md-4">
                        <b>Plant Count</b><br>
                        {{ $plantation->plant_count ?? '-' }}
                    </div>

                    <div class="col-md-4">
                        <b>Start Date</b><br>
                        {{ $plantation->plantation_start_date ?? '-' }}
                    </div>

                    <div class="col-md-4">
                        <b>End Date</b><br>
                        {{ $plantation->plantation_end_date ?? '-' }}
                    </div>

                </div>

                <hr>

                <div class="row">

                    <div class="col-md-4">
                        <b>Fenced</b><br>

                        @if ($plantation->is_fenced)
                            <span class="badge bg-success">Yes</span>
                        @else
                            <span class="badge bg-danger">No</span>
                        @endif

                    </div>

                </div>

            </div>
        </div>

        {{-- Observations --}}

        <div class="card shadow-sm">

            <div class="card-header fw-semibold">
                Observation Records
            </div>

            <div class="card-body">

                <table class="table table-bordered">

                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Inspector</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($plantation->observations as $obs)
                            <tr>

                                <td>{{ $obs->observation_date }}</td>

                                <td>{{ $obs->inspector->name ?? 'N/A' }}</td>

                                <td>{{ $obs->remarks }}</td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="3" class="text-center text-muted">
                                    No observations yet
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

@endsection

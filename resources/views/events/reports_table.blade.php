@php
$hideGlobalFilters = true;
@endphp

@extends('layouts.app')

@section('title','Forest Reports Table')

@section('content')

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="fw-bold">Forest Reports</h2>
            <p class="text-muted">
                Manage and monitor all incoming forest survey and activity reports.
            </p>
        </div>

        <div>


            <a href="{{ route('report-configs.create') }}" class="btn btn-success">
                + Create Report
            </a>
        </div>

    </div>



    <div class="card shadow-sm border-0">

        <div class="card-body p-0">

            <table class="table table-hover mb-0">

                <thead class="table-light">
                    <tr>
                        <th>Report ID</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Date / Time</th>
                        <th>Site</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($reports as $report)

                    <tr>

                        <td class="fw-semibold text-success">
                            {{ $report->report_id }}
                        </td>

                        <td>
                            {{ $report->category }}
                        </td>

                        <td>
                            {{ $report->report_type }}
                        </td>

                        <td>
                            {{ $report->date_time }}
                        </td>

                        <td>
                            {{ $report->site_id ?? '-' }}
                        </td>

                        <td>

                            @if($report->status == 'Pending')
                            <span class="badge bg-warning text-dark">
                                Pending
                            </span>

                            @elseif($report->status == 'Approved')
                            <span class="badge bg-success">
                                Approved
                            </span>

                            @else
                            <span class="badge bg-danger">
                                Rejected
                            </span>
                            @endif

                        </td>

                        <td class="text-end">

                            <a href="{{ route('report-configs.show',$report->id) }}"
                                class="btn btn-sm btn-outline-primary me-2">
                                <i class="bi bi-eye"></i> View
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

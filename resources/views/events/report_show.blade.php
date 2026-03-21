@php
$hideGlobalFilters = true;
@endphp

@extends('layouts.app')

@section('title','Report Details')

@section('content')

<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div class="d-flex align-items-center gap-3">

            <a href="{{ url()->previous() }}" class="btn btn-light">
                <i class="bi bi-arrow-left"></i>
            </a>

            <h4 class="fw-bold mb-0">
                Report #{{ $report->report_id }}
            </h4>

        </div>

        <div>

            @if($report->status == 'Pending')
            <span class="badge bg-warning text-dark">Pending</span>
            @elseif($report->status == 'Approved')
            <span class="badge bg-success">Approved</span>
            @else
            <span class="badge bg-danger">Rejected</span>
            @endif

            <button class="btn btn-outline-success ms-3">
                Export PDF
            </button>

        </div>

    </div>



    {{-- METADATA --}}
    <div class="row g-4 mb-4">

        <div class="col-md-3">

            <div class="card shadow-sm border-0">
                <div class="card-body">

                    <small class="text-muted">Submitted By</small>

                    <h6 class="fw-bold">
                        {{ $report->user_name ?? 'Field User' }}
                    </h6>

                </div>
            </div>

        </div>


        <div class="col-md-3">

            <div class="card shadow-sm border-0">
                <div class="card-body">

                    <small class="text-muted">Company</small>

                    <h6 class="fw-bold">
                        {{ $report->company_id }}
                    </h6>

                </div>
            </div>

        </div>


        <div class="col-md-3">

            <div class="card shadow-sm border-0">
                <div class="card-body">

                    <small class="text-muted">Supervisor</small>

                    <h6 class="fw-bold">
                        {{ $report->supervisor_id ?? '-' }}
                    </h6>

                </div>
            </div>

        </div>


        <div class="col-md-3">

            <div class="card shadow-sm border-0">
                <div class="card-body">

                    <small class="text-muted">Timestamp</small>

                    <h6 class="fw-bold">
                        {{ $report->date_time }}
                    </h6>

                </div>
            </div>

        </div>

    </div>



    <div class="row g-4">

        {{-- LEFT COLUMN --}}
        <div class="col-lg-8">

            <div class="card shadow-sm border-0 mb-4">

                <div class="card-header bg-light fw-bold">
                    Detailed Report Data
                </div>

                <div class="card-body">

                    <pre class="bg-dark text-light p-3 rounded small">
                    {{ json_encode(json_decode($report->report_data), JSON_PRETTY_PRINT) }}
                    </pre>

                </div>

            </div>



            {{-- PHOTOS --}}
            <div class="card shadow-sm border-0">

                <div class="card-header bg-light fw-bold">
                    Photo Gallery
                </div>

                <div class="card-body">

                    <div class="row g-3">

                        @foreach(json_decode($report->photo ?? '[]') as $image)

                        <div class="col-3">

                            <img src="{{ $image }}"
                                class="img-fluid rounded shadow-sm">

                        </div>

                        @endforeach

                    </div>

                </div>

            </div>

        </div>



        {{-- RIGHT COLUMN --}}
        <div class="col-lg-4">

            {{-- MAP --}}
            <div class="card shadow-sm border-0 mb-4">

                <div class="card-header bg-light fw-bold">
                    Location Map
                </div>

                <div class="card-body p-0">

                    <div id="reportMap" style="height:250px;"></div>

                </div>

                <div class="card-footer">

                    <small class="text-muted">
                        {{ $report->latitude }}, {{ $report->longitude }}
                    </small>

                </div>

            </div>



            {{-- ACTION PANEL --}}
            <div class="card shadow-sm border-0">

                <div class="card-header bg-light fw-bold">
                    Report Actions
                </div>

                <div class="card-body">

                    <form method="POST" action="{{route('report-configs.updateStatus', $report->id) }}">
                        @csrf

                        <textarea name="final_remarks" class="form-control mb-3"></textarea>

                        <button name="status" value="Approved" class="btn btn-success">
                            Approve
                        </button>

                        <button name="status" value="Pending" class="btn btn-warning">
                            Request Clarification
                        </button>

                        <button name="status" value="Rejected" class="btn btn-danger">
                            Reject
                        </button>

                    </form>
                </div>

            </div>

        </div>

    </div>

</div>

@endsection

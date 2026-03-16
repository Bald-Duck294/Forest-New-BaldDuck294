@php
$hideGlobalFilters = true;
@endphp

@extends('layouts.app')

@section('title', 'Attendance Requests')

@section('content')

<div class="container-fluid py-4">

    <h3 class="fw-bold mb-1">Attendance Requests</h3>
    <p class="text-muted mb-4">
        Review and manage employee clock-in/out exceptions
    </p>

    <div class="row g-3">

        @foreach ($requests as $req)

        <div class="col-md-6">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <div class="row align-items-center">

                        <!-- Profile Image -->
                        <div class="col-md-3 text-center">

                            <img src="{{ asset('images/user-placeholder.png') }}"
                                class="rounded-circle"
                                width="80"
                                height="80"
                                style="object-fit:cover;">

                        </div>

                        <!-- Request Info -->
                        <div class="col-md-6">

                            <h5 class="fw-bold mb-1">{{ $req->guard_name }}</h5>

                            <small class="text-muted">
                                Site: {{ $req->site_name }}
                            </small>

                            <div class="row mt-3">

                                <div class="col-12 mb-2">
                                    <small class="text-muted">Date & Time</small>
                                    <div>
                                        {{ \Carbon\Carbon::parse($req->entryDateTime)->format('M d Y h:i A') }}
                                    </div>
                                </div>

                                <div class="col-6">
                                    <small class="text-muted">Issue</small>
                                    <div>{{ $req->attendance_type }}</div>
                                </div>

                                <div class="col-6">
                                    <small class="text-muted">Reason</small>
                                    <div class="text-truncate" style="max-width:200px;">
                                        {{ $req->remark }}
                                    </div>
                                </div>

                            </div>

                        </div>

                        <!-- Action Buttons -->
                        <div class="col-md-3 d-flex flex-column justify-content-center text-end">

                            @if ($req->status == 'Pending')

                            <form method="POST" action="{{ url('attendance/requests/' . $req->id . '/reject') }}">
                                @csrf

                                <input type="text"
                                    name="remark"
                                    class="form-control form-control-sm mb-2"
                                    placeholder="Add remark">

                                <button class="btn btn-danger btn-sm w-100">
                                    Reject
                                </button>

                            </form>

                            <form method="POST" action="{{ url('attendance/requests/' . $req->id . '/approve') }}">
                                @csrf

                                <button class="btn btn-success btn-sm w-100 mt-2">
                                    Approve
                                </button>
                            </form>

                            @else

                            <span class="badge bg-success">
                                {{ $req->status }}
                            </span>

                            @endif

                        </div>

                    </div>

                </div>

            </div>

        </div>

        @endforeach

    </div>

    <div class="mt-4">
        {{ $requests->links('pagination::bootstrap-5') }}
    </div>

</div>

@endsection
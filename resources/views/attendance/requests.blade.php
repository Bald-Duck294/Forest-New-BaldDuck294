@extends('layouts.app')

@section('title', 'Attendance Requests')

@section('content')

    <div class="container-fluid py-4">

        <h3 class="fw-bold mb-1">Attendance Requests</h3>
        <p class="text-muted mb-4">
            Review and manage employee clock-in/out exceptions
        </p>

        @foreach ($requests as $req)
            <div class="card shadow-sm mb-4">

                <div class="card-body">

                    <div class="row align-items-center">

                        <div class="col-md-2 text-center">

                            <img src="{{ asset('images/user-placeholder.png') }}" class="rounded" width="90" height="90"
                                style="object-fit:cover;">

                        </div>

                        <div class="col-md-7">

                            <h5 class="fw-bold mb-1">{{ $req->guard_name }}</h5>

                            <small class="text-muted">
                                Site: {{ $req->site_name }}
                            </small>

                            <div class="row mt-3">

                                <div class="col-md-4">

                                    <small class="text-muted">Date & Time</small>

                                    <div>
                                        {{ \Carbon\Carbon::parse($req->entryDateTime)->format('M d Y h:i A') }}
                                    </div>

                                </div>

                                <div class="col-md-4">

                                    <small class="text-muted">Issue</small>

                                    <div>{{ $req->attendance_type }}</div>

                                </div>

                                <div class="col-md-4">

                                    <small class="text-muted">Reason</small>

                                    <div>{{ $req->remark }}</div>

                                </div>

                            </div>

                        </div>

                        <div class="col-md-3 text-end">

                            @if ($req->status == 'Pending')
                                <form method="POST" action="{{ url('attendance/requests/' . $req->id . '/reject') }}">
                                    @csrf

                                    <input type="text" name="remark" class="form-control mb-2" placeholder="Add remark">

                                    <button class="btn btn-danger btn-sm">
                                        Reject
                                    </button>

                                </form>

                                <form method="POST" action="{{ url('attendance/requests/' . $req->id . '/approve') }}">
                                    @csrf

                                    <button class="btn btn-success btn-sm mt-2">
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
        @endforeach

        <div class="mt-4">

            {{ $requests->links('pagination::bootstrap-5') }}

        </div>

    </div>

@endsection

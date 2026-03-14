@php
    $hideGlobalFilters = true;
    $hideBackground = true;
@endphp
@extends('layouts.app')

@section('content')
<style>
    :root {
        --primary: #2563eb;
        --primary-hover: #1d4ed8;
        --border: #e2e8f0;
        --text: #0f172a;
        --muted: #64748b;
    }
    .card {
        border-radius: 16px;
        border: 1px solid var(--border);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        background: #fff;
        margin-bottom: 24px;
    }
    .card-header {
        background: transparent;
        border-bottom: 1px solid var(--border);
        padding: 18px 24px;
        display: flex;
        align-items: center;
    }
    .card-header h4 {
        margin: 0;
        font-weight: 700;
        color: var(--text);
        font-size: 18px;
    }
    .card-body { padding: 24px; }
    .card-footer {
        padding: 18px 24px;
        border-top: 1px solid var(--border);
        background: transparent;
        display: flex;
        gap: 10px;
    }
    .form-group { margin-bottom: 20px; }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--text);
        font-size: 14px;
    }
    .form-control {
        border-radius: 10px !important;
        border: 1px solid var(--border) !important;
        padding: 10px 14px !important;
        height: auto !important;
        font-size: 14px !important;
        transition: all 0.2s !important;
        color: var(--text) !important;
        background: #fff !important;
        width: 100%;
    }
    .form-control:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(37,99,235,0.1) !important;
        outline: none !important;
    }
    .btn-back {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        background: #f1f5f9;
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text);
        text-decoration: none;
        transition: all 0.2s;
        margin-right: 12px;
        flex-shrink: 0;
    }
    .btn-back:hover { background: #e2e8f0; color: var(--text); }
    .btn-back i { font-size: 18px; }
    .btn-primary-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--primary);
        color: #fff;
        border-radius: 10px;
        padding: 10px 22px;
        font-weight: 600;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-primary-action:hover {
        background: var(--primary-hover);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37,99,235,0.25);
    }
    .btn-cancel {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #fff;
        color: var(--muted);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 10px 22px;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-cancel:hover { background: #f8fafc; color: var(--text); }
    .text-danger.small { font-size: 12px; margin-top: 4px; display: block; }
</style>

<div class="card">
    <div class="card-header">
        <a href="{{ route('clients.getshifts', [$client_id, $site_id]) }}" class="btn-back" title="Back to Shifts">
            <i class="la la-arrow-left"></i>
        </a>
        <h4>Add Shift</h4>
    </div>

    <form method="post" action="{{ route('clients.shift_createaction', [$client_id, $site_id]) }}" autocomplete="off">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="shift_name">Shift Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="shift_name" id="shift_name"
                            value="{{ old('shift_name') }}" placeholder="e.g. Morning, Night">
                        <span class="text-danger small">{{ $errors->first('shift_name') }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="start_time">Start Time <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" name="start_time" id="start_time"
                            value="{{ old('start_time') }}">
                        <span class="text-danger small">{{ $errors->first('start_time') }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="end_time">End Time <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" name="end_time" id="end_time"
                            value="{{ old('end_time') }}">
                        <span class="text-danger small">{{ $errors->first('end_time') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('clients.getshifts', [$client_id, $site_id]) }}" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-primary-action">
                <i class="la la-check"></i> Save Shift
            </button>
        </div>
    </form>
</div>
@endsection

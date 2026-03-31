@php
$hideGlobalFilters = true;
$hideBackground = true;
@endphp
@extends('layouts.app')

@push('styles')
<style>
    /* ==== LIGHT & DARK THEME VARIABLES ==== */
    :root {
        --primary: #2563eb;
        --primary-hover: #1d4ed8;
        --success: #10b981;
        --success-hover: #059669;
        --bg-color: #f8fafc;
        --card-bg: #ffffff;
        --text-main: #0f172a;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
        --input-bg: #ffffff;
        --input-focus-ring: rgba(37, 99, 235, 0.1);
    }

    [data-bs-theme="dark"],
    body.dark-mode,
    body.dark {
        --bg-color: #0f172a;
        --card-bg: #1e293b;
        --text-main: #f8fafc;
        --text-muted: #94a3b8;
        --border-color: #334155;
        --input-bg: #0f172a;
        --input-focus-ring: rgba(37, 99, 235, 0.25);
    }

    body {
        background-color: var(--bg-color);
        color: var(--text-main);
    }

    /* ==== CORE LAYOUT ==== */
    .main-content-wrapper {
        padding: 24px;
        width: 100%;
        overflow-x: hidden;
    }

    .edit-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    /* ==== HEADER ==== */
    .edit-card-header {
        border-bottom: 1px solid var(--border-color);
        padding: 20px 28px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .edit-card-header h4 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* ==== FORM ELEMENTS ==== */
    .edit-card-body {
        padding: 32px 28px;
    }

    .section-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        margin-bottom: 20px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 6px;
        display: block;
    }

    .form-control {
        background-color: var(--input-bg);
        color: var(--text-main);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 14px;
        width: 100%;
        transition: all 0.2s ease;
    }

    /* Target specific inputs for dark mode compatibility */
    input.form-control,
    select.form-control,
    textarea.form-control {
        background-color: var(--input-bg);
        color: var(--text-main);
    }

    /* Fix select dropdown options in dark mode */
    select.form-control option {
        background-color: var(--card-bg);
        color: var(--text-main);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--input-focus-ring);
        background-color: var(--input-bg);
        color: var(--text-main);
    }

    .form-control::placeholder {
        color: #94a3b8;
        opacity: 0.7;
    }

    /* ==== BUTTONS & FOOTER ==== */
    .edit-card-footer {
        background: transparent;
        border-top: 1px solid var(--border-color);
        padding: 20px 28px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .btn-modern {
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-cancel {
        background-color: transparent;
        color: var(--text-muted);
        border: 1px solid var(--border-color);
    }

    .btn-cancel:hover {
        background-color: var(--border-color);
        color: var(--text-main);
    }

    .btn-save {
        background-color: var(--primary);
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
    }

    .btn-save:hover {
        background-color: var(--primary-hover);
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(37, 99, 235, 0.3);
    }

    /* ==== ALERTS ==== */
    .alert-modern {
        border-radius: 12px;
        border: none;
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
        border: 1px solid rgba(16, 185, 129, 0.2);
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        font-weight: 500;
    }

    .alert-modern .close {
        background: transparent;
        border: none;
        color: var(--success);
        font-size: 20px;
        cursor: pointer;
        opacity: 0.7;
    }

    .alert-modern .close:hover {
        opacity: 1;
    }
</style>
@endpush

@section('content')
<div class="main-content-wrapper">

    @if (session('success'))
    <div class="alert-modern" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
        </div>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"
            onclick="this.parentElement.style.display='none';">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <div class="edit-card">
        <div class="edit-card-header">
            <a href="{{ route('profile.index') }}" class="text-muted" style="text-decoration: none; font-size: 1.2rem;">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h4>
                <i class="bi bi-person-gear text-primary"></i>
                Edit My Profile: <span class="text-primary ml-1">{{ $user->name }}</span>
            </h4>
        </div>

        <form method="post" action="{{ route('profile.update') }}">
            @csrf
            <div class="edit-card-body">

                <h6 class="section-title"><i class="bi bi-person-lines-fill"></i> Profile Information</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label class="form-label" for="name">Full Name</label>
                            <input type="text" autocomplete="off" class="form-control" id="name" name="name"
                                placeholder="Enter Full Name" value="{{ old('name', $user->name) }}">
                            @if ($errors->has('name'))
                            <span class="text-danger small mt-1 d-block">{{ $errors->first('name') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('contact') ? 'has-error' : '' }}">
                            <label class="form-label" for="contact">Contact Number</label>
                            <input type="text" autocomplete="off" class="form-control Number" id="contact"
                                name="contact" placeholder="Enter Contact Number"
                                value="{{ old('contact', $user->contact) }}">
                            @if ($errors->has('contact'))
                            <span class="text-danger small mt-1 d-block">{{ $errors->first('contact') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('dob') ? 'has-error' : '' }}">
                            <label class="form-label" for="dob">Date of Birth</label>
                            <input type="date" autocomplete="off" class="form-control" id="dob" name="dob"
                                value="{{ old('dob', $user->dob) }}">
                            @if ($errors->has('dob'))
                            <span class="text-danger small mt-1 d-block">{{ $errors->first('dob') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('gender') ? 'has-error' : '' }}">
                            <label class="form-label" for="gender">Gender</label>
                            <select class="form-control" name="gender" id="gender">
                                <option value="" disabled
                                    {{ old('gender', $user->gender) == '' ? 'selected' : '' }}>Select Gender
                                </option>
                                <option value="Male"
                                    {{ old('gender', $user->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female"
                                    {{ old('gender', $user->gender) == 'Female' ? 'selected' : '' }}>Female
                                </option>
                                <option value="Other"
                                    {{ old('gender', $user->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @if ($errors->has('gender'))
                            <span class="text-danger small mt-1 d-block">{{ $errors->first('gender') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group {{ $errors->has('code_name') ? 'has-error' : '' }}">
                            <label class="form-label" for="code_name">Code Name</label>
                            <input type="text" autocomplete="off" class="form-control" id="code_name"
                                name="code_name" placeholder="Enter Code Name"
                                value="{{ old('code_name', $user->code_name) }}">
                            @if ($errors->has('code_name'))
                            <span class="text-danger small mt-1 d-block">{{ $errors->first('code_name') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                            <label class="form-label" for="address">Residential Address</label>
                            <textarea name="address" class="form-control" id="address" placeholder="Enter full address..." rows="2">{{ old('address', $user->address) }}</textarea>
                            @if ($errors->has('address'))
                            <span class="text-danger small mt-1 d-block">{{ $errors->first('address') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <h6 class="section-title mt-4"><i class="bi bi-shield-lock-fill"></i> Account & Security</h6>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                            <label class="form-label" for="email">Email Address</label>
                            <input type="email" autocomplete="off" class="form-control" id="email"
                                name="email" placeholder="Email Address"
                                value="{{ old('email', $user->email) }}">
                            @if ($errors->has('email'))
                            <span class="text-danger small mt-1 d-block">{{ $errors->first('email') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <p class="text-muted small mb-3"><i class="bi bi-info-circle"></i> Leave blank if you don't want to
                    change the password.</p>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                            <label class="form-label" for="password">New Password</label>
                            <input type="password" autocomplete="new-password" class="form-control" id="password"
                                name="password" placeholder="Enter New Password">
                            @if ($errors->has('password'))
                            <span class="text-danger small mt-1 d-block">{{ $errors->first('password') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="password_confirmation">Confirm Password</label>
                            <input type="password" autocomplete="new-password" class="form-control"
                                id="password_confirmation" name="password_confirmation"
                                placeholder="Confirm New Password">
                        </div>
                    </div>
                </div>

            </div>

            <div class="edit-card-footer">
                <a href="{{ route('profile.index') }}" class="btn-modern btn-cancel">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
                <button type="submit" class="btn-modern btn-save">
                    <i class="bi bi-check2-circle"></i> Save Changes
                </button>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Restrict input to numbers only
        $(".Number").attr("maxlength", "15");
        $(".Number").keypress(function(e) {
            var kk = e.which;
            if (kk < 48 || kk > 57) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush
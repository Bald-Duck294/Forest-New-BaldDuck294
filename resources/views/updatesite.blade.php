@php
$hideGlobalFilters = true;
$hideBackground = true;
$label = session('company') && (session('company')->is_forest ?? 1) == 1 ? 'Beat' : 'Site';
@endphp
@extends('layouts.app')

@section('content')
<style>
    /* Scoped Light Theme Variables */
    .custom-theme-wrapper {
        --primary: #2563eb;
        --primary-hover: #1d4ed8;
        --border: #e2e8f0;
        --bg-card: #ffffff;
        --bg-body: #f8fafc;
        --bg-input: #ffffff;
        --text-main: #0f172a;
        --text-muted: #64748b;
        --btn-back-bg: #f1f5f9;
        --btn-back-hover: #e2e8f0;
        --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }

    /* Scoped Dark Theme Variables */
    html[data-theme="dark"] .custom-theme-wrapper,
    html[data-bs-theme="dark"] .custom-theme-wrapper,
    body.dark .custom-theme-wrapper,
    body.dark-mode .custom-theme-wrapper,
    body.dark-theme .custom-theme-wrapper {
        --primary: #3b82f6;
        --primary-hover: #60a5fa;
        --border: #334155;
        --bg-card: #1e293b;
        --bg-body: #0f172a;
        --bg-input: #0f172a;
        --text-main: #f8fafc;
        --text-muted: #94a3b8;
        --btn-back-bg: #334155;
        --btn-back-hover: #475569;
        --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
    }

    .custom-theme-wrapper {
        font-family: 'Inter', sans-serif;
        color: var(--text-main);
    }

    .custom-theme-wrapper .card {
        border-radius: 16px;
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
        background: var(--bg-card);
        margin-bottom: 24px;
        margin-top: 1rem;
        transition: background-color 0.3s, border-color 0.3s;
    }

    .custom-theme-wrapper .card-header {
        background: transparent;
        border-bottom: 1px solid var(--border);
        padding: 20px 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .custom-theme-wrapper .card-header h4 {
        margin: 0;
        font-weight: 700;
        color: var(--text-main);
        font-size: 18px;
    }

    .custom-theme-wrapper .btn-back {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        background: var(--btn-back-bg);
        border: 1px solid var(--border);
        border-radius: 10px;
        color: var(--text-main);
        text-decoration: none !important;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .custom-theme-wrapper .btn-back:hover {
        background: var(--btn-back-hover);
        transform: translateX(-2px);
    }

    .custom-theme-wrapper .card-body {
        padding: 28px 24px;
    }

    .custom-theme-wrapper .form-group {
        margin-bottom: 20px;
    }

    .custom-theme-wrapper .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--text-main);
        font-size: 14px;
    }

    .custom-theme-wrapper .form-control {
        border-radius: 10px !important;
        border: 1px solid var(--border) !important;
        padding: 12px 16px !important;
        height: auto !important;
        font-size: 14px !important;
        transition: all 0.2s ease !important;
        color: var(--text-main) !important;
        background: var(--bg-input) !important;
        width: 100%;
    }

    .custom-theme-wrapper .form-control:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
        outline: none !important;
    }

    .custom-theme-wrapper .form-actions {
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid var(--border);
        display: flex;
        gap: 12px;
        justify-content: flex-start;
    }

    .custom-theme-wrapper .btn-primary-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: var(--primary);
        color: #fff !important;
        border-radius: 10px;
        padding: 12px 24px;
        font-weight: 600;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .custom-theme-wrapper .btn-primary-action:hover {
        background: var(--primary-hover);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    }

    .custom-theme-wrapper .btn-cancel {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: var(--bg-input);
        color: var(--text-muted) !important;
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 12px 24px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }

    .custom-theme-wrapper .text-danger.small {
        font-size: 12px;
        margin-top: 6px;
        display: block;
        color: #ef4444 !important;
    }
</style>

<div class="custom-theme-wrapper">
    <div class="card">
        <div class="card-header">
            <a href="{{ route('sites.getsites', $client_id) }}" class="btn-back" title="Back to {{ $label }} List">
                <i class="la la-arrow-left"></i>
            </a>
            <h4>Update {{ $label }}: {{ $sites->name }}</h4>
        </div>

        <div class="card-body">
            <form method="post" action="{{ route('sites.site_editaction', ['client_id' => $client_id, 'id' => $id]) }}" id="update_site_form" autocomplete="off">
                @csrf
                {{-- Note: If your route uses PUT, add @method('PUT') here --}}

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('client') ? 'has-error' : '' }} mb-0">
                            <label for="client">Client <span class="text-danger">*</span></label>
                            <select class="form-control" name="client" id="client" required>
                                <option value="" disabled>-- Select Client --</option>
                                @foreach ($clients as $client)
                                <option value="{{ $client->id }}" {{ (old('client', $sites->client_id) == $client->id) ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                                @endforeach
                            </select>
                            <span class="text-danger small">{{ $errors->first('client') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }} mb-0">
                            <label for="name">{{ $label }} Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name', $sites->name) }}" placeholder="Enter {{ strtolower($label) }} name" required>
                            <span class="text-danger small">{{ $errors->first('name') }}</span>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }} mb-0">
                            <label for="address">Address <span class="text-danger">*</span></label>
                            <input name="address" class="form-control" id="address" value="{{ old('address', $sites->address) }}"
                                placeholder="Enter full address" required>
                            <span class="text-danger small">{{ $errors->first('address') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('state') ? 'has-error' : '' }} mb-0">
                            <label for="state">State <span class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control"
                                id="state"
                                name="state"
                                value="{{ old('state', $sites->state) }}"
                                placeholder="Enter state name"
                                required>
                            <span class="text-danger small">{{ $errors->first('state') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('city') ? 'has-error' : '' }} mb-0">
                            <label for="city">City <span class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control"
                                id="city"
                                name="city"
                                value="{{ old('city', $sites->city) }}"
                                placeholder="Enter city name"
                                required>
                            <span class="text-danger small">{{ $errors->first('city') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('pincode') ? 'has-error' : '' }} mb-0">
                            <label for="pincode">Pincode <span class="text-danger">*</span></label>
                            <input name="pincode" type="text" class="form-control numeric-only" id="pincode"
                                value="{{ old('pincode', $sites->pincode) }}" placeholder="Enter 6-digit pincode" maxlength="6"
                                inputmode="numeric" required>
                            <span class="text-danger small">{{ $errors->first('pincode') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('contactperson') ? 'has-error' : '' }} mb-0">
                            <label for="contactperson">Contact Person <span class="text-danger">*</span></label>
                            <input name="contactperson" type="text" class="form-control" id="contactperson"
                                value="{{ old('contactperson', $sites->contact_person) }}" placeholder="Enter contact person name" required>
                            <span class="text-danger small">{{ $errors->first('contactperson') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('contactnumber') ? 'has-error' : '' }} mb-0">
                            <label for="contactnumber">Contact Number <span class="text-danger">*</span></label>
                            <input name="contactnumber" type="text" class="form-control numeric-only"
                                id="contactnumber" value="{{ old('contactnumber', $sites->contact_number) }}"
                                placeholder="Enter 10-digit number" maxlength="10" inputmode="numeric" required>
                            <span class="text-danger small">{{ $errors->first('contactnumber') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }} mb-0">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input name="email" type="email" class="form-control" id="email"
                                value="{{ old('email', $sites->email) }}" placeholder="Enter email address" required>
                            <span class="text-danger small">{{ $errors->first('email') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('sos') ? 'has-error' : '' }} mb-0">
                            <label for="sos">SOS Number <span class="text-danger">*</span></label>
                            <input name="sos" type="text" class="form-control numeric-only" id="sos"
                                value="{{ old('sos', $sites->sos_number) }}" placeholder="Enter 10-digit SOS number" maxlength="10"
                                inputmode="numeric" required>
                            <span class="text-danger small">{{ $errors->first('sos') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('earlytime') ? 'has-error' : '' }} mb-0">
                            <label for="earlytime">Early Cut-off (minutes) <span class="text-danger">*</span></label>
                            <input name="earlytime" type="number" class="form-control numeric-only" id="earlytime"
                                value="{{ old('earlytime', $sites->early_cutoff) }}" placeholder="e.g. 15" min="0"
                                inputmode="numeric" required>
                            <span class="text-danger small">{{ $errors->first('earlytime') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('latetime') ? 'has-error' : '' }} mb-0">
                            <label for="latetime">Late Cut-off (minutes) <span class="text-danger">*</span></label>
                            <input name="latetime" type="number" class="form-control numeric-only" id="latetime"
                                value="{{ old('latetime', $sites->late_cutoff) }}" placeholder="e.g. 15" min="0"
                                inputmode="numeric" required>
                            <span class="text-danger small">{{ $errors->first('latetime') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('site_type') ? 'has-error' : '' }} mb-0">
                            <label for="site_type">Type <span class="text-danger">*</span></label>
                            <select class="form-control" name="site_type" id="site_type" required>
                                <option value="" disabled>-- Select Type --</option>
                                <option value="residential" {{ old('site_type', $sites->type) == 'residential' ? 'selected' : '' }}>Residential</option>
                                <option value="commersial" {{ old('site_type', $sites->type) == 'commersial' ? 'selected' : '' }}>Commercial</option>
                                <option value="government" {{ old('site_type', $sites->type) == 'government' ? 'selected' : '' }}>Government</option>
                            </select>
                            <span class="text-danger small">{{ $errors->first('site_type') }}</span>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary-action">
                        <i class="la la-check"></i> Update {{ $label }}
                    </button>
                    <a href="{{ route('sites.getsites', $client_id) }}" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Strict numeric validation for fields with class "numeric-only"
        $('.numeric-only').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Prevent typing non-numbers
        $('.numeric-only').on('keypress', function(e) {
            if (e.which < 48 || e.which > 57) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush
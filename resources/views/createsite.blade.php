@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    $label = session('company') && (session('company')->is_forest ?? 1) == 1 ? 'Beat' : 'Site';
@endphp
@extends('layouts.app')

@section('content')
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --border: #e2e8f0;
            --bg: #f8fafc;
            --text: #0f172a;
            --muted: #64748b;
        }

        .card {
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            background: #fff;
            margin-bottom: 24px;
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border);
            padding: 18px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h4 {
            margin: 0;
            font-weight: 700;
            color: var(--text);
            font-size: 18px;
        }

        .card-body {
            padding: 24px;
        }

        .card-footer {
            padding: 18px 24px;
            border-top: 1px solid var(--border);
            background: transparent;
            display: flex;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

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
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
            outline: none !important;
        }

        select.form-control {
            cursor: pointer;
            -webkit-appearance: auto;
            appearance: auto;
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

        .btn-back:hover {
            background: #e2e8f0;
            color: var(--text);
        }

        .btn-back i {
            font-size: 18px;
        }

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
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
            color: #fff;
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

        .btn-cancel:hover {
            background: #f8fafc;
            border-color: var(--muted);
            color: var(--text);
        }

        .text-danger.small {
            font-size: 12px;
            margin-top: 4px;
            display: block;
        }
    </style>

    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-center">
                <a href="{{ route('sites.getsites', $id) }}" class="btn-back" title="Back to Site List">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h4 class="mb-0">Add New {{ $label }}</h4>
            </div>
        </div>

        <form method="post" action="{{ route('sites.site_createaction', ['id' => $id]) }}" id="create_site_form"
            autocomplete="off">
            @csrf
            <div class="card-body">
                <div class="row">
                    @if ($id == '0')
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="client">Client <span class="text-danger">*</span></label>
                                <select class="form-control" name="client" id="client">
                                    <option value="" disabled selected>-- Select Client --</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}"
                                            {{ old('client') == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger small">{{ $errors->first('client') }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">{{ $label }} Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name') }}" placeholder="Enter {{ strtolower($label) }} name">
                            <span class="text-danger small">{{ $errors->first('name') }}</span>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="address">Address <span class="text-danger">*</span></label>
                            <input name="address" class="form-control" id="address" value="{{ old('address') }}"
                                placeholder="Enter full address">
                            <span class="text-danger small">{{ $errors->first('address') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="state">State <span class="text-danger">*</span></label>
                            <select class="form-control" name="state" id="state"
                                onchange="loadSiteCities(this.value)">
                                <option value="" disabled selected>-- Select State --</option>
                                @foreach ($states as $state)
                                    <option value="{{ $state->code }}"
                                        {{ old('state') == $state->code ? 'selected' : '' }}>
                                        {{ $state->name }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="text-danger small">{{ $errors->first('state') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="city">City <span class="text-danger">*</span></label>
                            <select class="form-control" name="city" id="city">
                                <option value="" disabled selected>-- Select State first --</option>
                                @if (old('city'))
                                    <option value="{{ old('city') }}" selected>{{ old('city') }}</option>
                                @endif
                            </select>
                            <span class="text-danger small">{{ $errors->first('city') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="pincode">Pincode <span class="text-danger">*</span></label>
                            <input name="pincode" type="text" class="form-control" id="pincode"
                                value="{{ old('pincode') }}" placeholder="Enter pincode" maxlength="6">
                            <span class="text-danger small">{{ $errors->first('pincode') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="contactperson">Contact Person <span class="text-danger">*</span></label>
                            <input name="contactperson" type="text" class="form-control" id="contactperson"
                                value="{{ old('contactperson') }}" placeholder="Enter contact person name">
                            <span class="text-danger small">{{ $errors->first('contactperson') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="contactnumber">Contact Number <span class="text-danger">*</span></label>
                            <input name="contactnumber" type="text" class="form-control" id="contactnumber"
                                value="{{ old('contactnumber') }}" placeholder="Enter 10-digit number" maxlength="10">
                            <span class="text-danger small">{{ $errors->first('contactnumber') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input name="email" type="email" class="form-control" id="email"
                                value="{{ old('email') }}" placeholder="Enter email address">
                            <span class="text-danger small">{{ $errors->first('email') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="sos">SOS Number <span class="text-danger">*</span></label>
                            <input name="sos" type="text" class="form-control" id="sos"
                                value="{{ old('sos') }}" placeholder="Enter SOS number" maxlength="10">
                            <span class="text-danger small">{{ $errors->first('sos') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="earlytime">Early Cut-off (minutes) <span class="text-danger">*</span></label>
                            <input name="earlytime" type="number" class="form-control" id="earlytime"
                                value="{{ old('earlytime') }}" placeholder="e.g. 15" min="0">
                            <span class="text-danger small">{{ $errors->first('earlytime') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="latetime">Late Cut-off (minutes) <span class="text-danger">*</span></label>
                            <input name="latetime" type="number" class="form-control" id="latetime"
                                value="{{ old('latetime') }}" placeholder="e.g. 15" min="0">
                            <span class="text-danger small">{{ $errors->first('latetime') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="site_type">Type <span class="text-danger">*</span></label>
                            <select class="form-control" name="site_type" id="site_type">
                                <option value="" disabled selected>-- Select Type --</option>
                                <option value="residential" {{ old('site_type') == 'residential' ? 'selected' : '' }}>
                                    Residential</option>
                                <option value="commersial" {{ old('site_type') == 'commersial' ? 'selected' : '' }}>
                                    Commercial</option>
                                <option value="government" {{ old('site_type') == 'government' ? 'selected' : '' }}>
                                    Government</option>
                            </select>
                            <span class="text-danger small">{{ $errors->first('site_type') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <a href="{{ route('sites.getsites', $id) }}" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-primary-action">
                    <i class="la la-check"></i> Save {{ $label }}
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        // Global function called by inline onchange on state select.
        // Using inline handler because jQuery .on('change') was not firing reliably.
        function loadSiteCities(stateCode) {
            console.log("loadSiteCities called:", stateCode);
            if (!stateCode) return;

            var cityUrl = '{{ route('clients.getCity', ':id') }}';
            cityUrl = cityUrl.replace(':id', stateCode);

            $('#city').html('<option value="" disabled selected>Loading cities...</option>');

            $.ajax({
                type: 'GET',
                url: cityUrl,
                dataType: 'json',
                success: function(data) {
                    console.log("Cities received:", data);
                    $('#city').empty();
                    $('#city').append('<option value="" disabled selected>-- Select City --</option>');
                    if (Array.isArray(data) && data.length > 0) {
                        $.each(data, function(i, city) {
                            $('#city').append('<option value="' + city.name + '">' + city.name +
                                '</option>');
                        });
                    } else {
                        $('#city').append('<option value="" disabled>No cities found</option>');
                    }
                },
                error: function(xhr, status, err) {
                    console.error("AJAX error:", status, err);
                    $('#city').html('<option value="" disabled selected>Error loading cities</option>');
                }
            });
        }

        $(document).ready(function() {
            $('#contactnumber, #sos').on('keypress', function(e) {
                if (e.which < 48 || e.which > 57) e.preventDefault();
            });
            $('#pincode').on('keypress', function(e) {
                if (e.which < 48 || e.which > 57) e.preventDefault();
            });
        });
    </script>
@endpush

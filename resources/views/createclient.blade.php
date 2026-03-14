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
            --bg: #f8fafc;
            --text: #0f172a;
            --muted: #64748b;
        }

        body {
            font-family: 'Inter', sans-serif !important;
            background-color: var(--bg);
        }

        .card {
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            background: #fff;
            margin-bottom: 24px;
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border);
            padding: 20px 24px;
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
            text-decoration: none;
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
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
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
                <a href="{{ route('clients') }}" class="btn-back" title="Back to Clients">
                    <i class="bi bi-arrow-left"></i> </a>
                <h4 class="mb-0">Add New Client</h4>
            </div>
        </div>

        <div class="card-body">
            <form method="post" action="{{ route('clients.createaction') }}" id="create_client_form" autocomplete="off">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label for="name">Client Name <span class="text-danger">*</span></label>
                            <input class="form-control" id="name" type="text" name="name"
                                placeholder="Enter client name" value="{{ old('name') }}" />
                            <span class="text-danger small">{{ $errors->first('name') }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                            <label for="address">Address <span class="text-danger">*</span></label>
                            <input class="form-control" id="address" name="address" type="text"
                                placeholder="Enter address" value="{{ old('address') }}" />
                            <span class="text-danger small">{{ $errors->first('address') }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('state') ? 'has-error' : '' }}">
                            <label for="state">State <span class="text-danger">*</span></label>
                            <select class="form-control" name="state" id="state" onchange="loadCities(this.value)">
                                <option value="" disabled selected>-- Select State --</option>
                                @foreach ($states as $state)
                                    <option value="{{ $state->code }}"
                                        {{ old('state') == $state->code ? 'selected' : '' }}>
                                        {{ $state->name }}
                                    </option>
                                @endforeach
                            </select>
                            {{-- TEST BUTTON: Remove once dropdown is confirmed working --}}
                            {{-- <button type="button" style="margin-top:6px;padding:4px 10px;font-size:12px;"
                                onclick="loadCities('MH'); console.log('Test button clicked');">Test: Load Maharashtra
                                Cities</button> --}}
                            <span class="text-danger small">{{ $errors->first('state') }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('city') ? 'has-error' : '' }}">
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
                        <div class="form-group {{ $errors->has('pincode') ? 'has-error' : '' }}">
                            <label for="pincode">Pincode <span class="text-danger">*</span></label>
                            <input name="pincode" type="text" class="form-control" id="pincode"
                                placeholder="Enter pincode" value="{{ old('pincode') }}" maxlength="6">
                            <span class="text-danger small">{{ $errors->first('pincode') }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('contactperson') ? 'has-error' : '' }}">
                            <label for="contactperson">Contact Person's Name <span class="text-danger">*</span></label>
                            <input name="contactperson" type="text" class="form-control" id="contactperson"
                                placeholder="Enter contact person name" value="{{ old('contactperson') }}">
                            <span class="text-danger small">{{ $errors->first('contactperson') }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('contactnumber') ? 'has-error' : '' }}">
                            <label for="contactnumber">Contact Person's Number <span class="text-danger">*</span></label>
                            <input name="contactnumber" type="text" class="form-control" id="contactnumber"
                                placeholder="Enter 10-digit contact number" value="{{ old('contactnumber') }}"
                                maxlength="10">
                            <span class="text-danger small">{{ $errors->first('contactnumber') }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                            <label for="email">Company Email <span class="text-danger">*</span></label>
                            <input name="email" type="email" class="form-control" id="email"
                                placeholder="Enter email address" value="{{ old('email') }}">
                            <span class="text-danger small">{{ $errors->first('email') }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('relationshipmanager') ? 'has-error' : '' }}">
                            <label for="relationshipmanager">Relationship Manager <span
                                    class="text-danger">*</span></label>
                            <input name="relationshipmanager" type="text" class="form-control"
                                id="relationshipmanager" placeholder="Enter relationship manager name"
                                value="{{ old('relationshipmanager') }}">
                            <span class="text-danger small">{{ $errors->first('relationshipmanager') }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('relationshipmanagercontact') ? 'has-error' : '' }}">
                            <label for="relationshipmanagercontact">Relationship Manager Contact <span
                                    class="text-danger">*</span></label>
                            <input name="relationshipmanagercontact" type="text" class="form-control"
                                id="relationshipmanagercontact" placeholder="Enter relationship manager contact"
                                value="{{ old('relationshipmanagercontact') }}" maxlength="10">
                            <span class="text-danger small">{{ $errors->first('relationshipmanagercontact') }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-top d-flex gap-2">
                    <a href="{{ route('clients') }}" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-primary-action">
                        <i class="la la-check"></i> Create Client
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        console.log("script loaded");

        function loadCities(stateCode) {
            console.log("loadCities called with:", stateCode);
            if (!stateCode) return;

            var cityUrl = '{{ route('clients.getCity', ':id') }}';
            cityUrl = cityUrl.replace(':id', stateCode);
            console.log("AJAX URL:", cityUrl);

            $('#city').html('<option disabled selected>Loading cities...</option>');

            $.ajax({
                type: 'GET',
                url: cityUrl,
                dataType: 'json',
                success: function(data) {
                    console.log("Cities received:", data);
                    $('#city').empty().append('<option value="" disabled selected>-- Select City --</option>');
                    if (Array.isArray(data) && data.length > 0) {
                        $.each(data, function(i, city) {
                            $('#city').append('<option value="' + city.name + '">' + city.name +
                                '</option>');
                        });
                    } else {
                        $('#city').append('<option disabled>No cities found</option>');
                    }
                },
                error: function(xhr, status, err) {
                    console.error("AJAX error:", status, err, xhr.responseText);
                    $('#city').html('<option disabled selected>Error loading cities</option>');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            var stateEl = document.getElementById('state');
            if (stateEl) {
                stateEl.addEventListener('change', function() {
                    console.log("Vanilla change fired:", this.value);
                });
                console.log("Vanilla listener attached to #state. onchange attr:", stateEl.getAttribute(
                    'onchange'));
            } else {
                console.error("#state element NOT FOUND in vanilla search!");
            }
        });

        $(document).ready(function() {
            $('#contactnumber, #relationshipmanagercontact, #pincode').on('keypress', function(e) {
                if (e.which < 48 || e.which > 57) e.preventDefault();
            });
        });
    </script>
@endpush

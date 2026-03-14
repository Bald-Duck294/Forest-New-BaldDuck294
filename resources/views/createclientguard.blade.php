@php
    $hideGlobalFilters = true;
    $hideBackground = true;
@endphp
@extends('layouts.app')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-selection,
        .select2-selection--single {
            border-radius: 0.3rem !important;
        }

        .select2-selection__rendered {
            margin-top: -10px;
        }

        .select2-selection__arrow {
            margin-top: 8px;
            margin-right: 5px;
        }

        .select2-container--default .select2-selection--single {
            padding: 18px;
        }

        .select2,
        .select2-container,
        .select2-container--default {
            margin-top: 7px;
            margin-left: -13px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid create">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-12">
                        <h4>Assign Employees</h4>
                    </div>
                </div>
            </div>

            <!-- form start -->
            <form method="post" action="{{ route('clients.guard_createaction', [$client_id, $site_id]) }}" id='form_id'>
                @csrf
                <div class="card-body">
                    <div class="row">
                        @if ($site_id == 0)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <span class="has-float-label">
                                        <select class="form-control" name="client" id="client"
                                            placeholder="Select client">
                                            <option value="0">Select Client</option>
                                            @foreach ($clients as $key => $client)
                                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                                            @endforeach
                                        </select>
                                        <label for="client">Clients</label>
                                    </span>
                                    <span class="text-danger">{{ $errors->first('client') }}</span>
                                </div>
                            </div>
                            <input type="hidden" name="userId" value="{{ $client_id }}" />
                            <div class="col-md-6">
                                <div class="form-group">
                                    <span class="has-float-label">
                                        <select class="form-control" name="site" id="site"
                                            placeholder="Select site">
                                            <option value="">Select Site</option>
                                        </select>
                                        <label for="site">Sites</label>
                                    </span>
                                    <span class="text-danger">{{ $errors->first('site') }}</span>
                                </div>
                            </div>
                        @endif

                        <div class="col-md-6">
                            <div class="form-group">
                                <span class="has-float-label">
                                    <select class="form-control" name="shift" id="shift" placeholder="Select shift">
                                        <option value="">Select Shift</option>
                                        @foreach ($shifts as $key => $shift)
                                            <option value="{{ $shift->id }}">{{ $shift->shift_name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="shift">Shifts</label>
                                </span>
                                <span class="text-danger">{{ $errors->first('shift') }}</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <span class="has-float-label">
                                    <select class="form-control guards" name="guard[]" id="guard"
                                        placeholder="Select employee" multiple="multiple">
                                    </select>
                                    <label for="guard" class="guardLabel">Employees</label>
                                </span>
                                <span class="text-danger">{{ $errors->first('guard') }}</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <span class="has-float-label">
                                    <div class="datepickers date input-group">
                                        <input type="date" name="startdate" min="{{ date('Y-m-d') }}"
                                            placeholder="Choose a start date" class="form-control"
                                            id="fromdateSelectInput" />
                                    </div>
                                    <label for="fromDate">Start Date</label>
                                </span>
                                <span class="text-danger">{{ $errors->first('datepicker') }}</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <span class="has-float-label">
                                    <div class="datepickers date input-group">
                                        <input type="date" name="enddate" min="{{ date('Y-m-d') }}"
                                            placeholder="Choose an end date" class="form-control" id="todateSelectInput" />
                                    </div>
                                    <label for="enddate">End Date</label>
                                </span>
                                <span class="text-danger">{{ $errors->first('enddate') }}</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="weekoff">Week Offs</label>
                                <div class="row">
                                    <div class="col-md-12" style="margin-left:20px;">
                                        <div><input type="checkbox" name="weekoff[]" value="Sunday" class="weekoff"> Sunday
                                        </div>
                                        <div><input type="checkbox" name="weekoff[]" class="weekoff" value="Monday"> Monday
                                        </div>
                                        <div><input type="checkbox" name="weekoff[]" class="weekoff" value="Tuesday">
                                            Tuesday</div>
                                        <div><input type="checkbox" name="weekoff[]" class="weekoff" value="Wednesday">
                                            Wednesday</div>
                                        <div><input type="checkbox" name="weekoff[]" class="weekoff" value="Thursday">
                                            Thursday</div>
                                        <div><input type="checkbox" name="weekoff[]" class="weekoff" value="Friday">
                                            Friday</div>
                                        <div><input type="checkbox" name="weekoff[]" class="weekoff" value="Saturday">
                                            Saturday</div>
                                    </div>
                                </div>
                                <span class="text-danger">{{ $errors->first('weekoff') }}</span>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                </div>
                <div class="card-footer">
                    <button onclick="goBack()" type="button" class="btn simple-button">Back</button>
                    <button type="submit" class="btn btn-success submit" id="submit">Save</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {

            // Initialize Select2 on the guard multiselect
            $('#guard').select2({
                placeholder: 'Select employees',
                allowClear: true,
                width: '100%'
            });

            $('#shift').on('change', function() {
                let id = $(this).val();
                if (!id) return;
                var url = '{{ route('getNotAssignGuard', ':id') }}';
                url = url.replace(':id', id);
                $.ajax({
                    type: 'GET',
                    url: url,
                    dataType: 'json',
                    success: function(response) {
                        // Destroy Select2 before repopulating
                        if ($('#guard').hasClass('select2-hidden-accessible')) {
                            $('#guard').select2('destroy');
                        }
                        $('#guard').empty();
                        if (response.length === 0) {
                            $('#guard').append(
                                '<option value="" disabled>No employees available</option>');
                        } else {
                            response.forEach(function(element) {
                                $('#guard').append(
                                    '<option value="' + element.id + '">' + element
                                    .name + '</option>'
                                );
                            });
                        }
                        // Re-initialize Select2 after populating
                        $('#guard').select2({
                            placeholder: 'Select employees',
                            allowClear: true,
                            width: '100%'
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Failed to load employees:', xhr.status, xhr
                        .responseText);
                        $('#guard').empty();
                        $('#guard').append(
                            '<option value="" disabled selected>Error loading employees</option>'
                            );
                    }
                });
            });

            $('#client').on('change', function() {
                let id = $(this).val();
                var url = '{{ route('getClientSites', ':id') }}';
                url = url.replace(':id', id);
                $.ajax({
                    type: 'GET',
                    url: url,
                    dataType: 'json',
                    success: function(response) {
                        $('#site').empty();
                        if (response.length != 0) {
                            $('#site').append(
                                '<option value="0" disabled selected>Select site</option>');
                            response.forEach(function(element) {
                                $('#site').append(
                                    '<option value="' + element.id + '">' + element
                                    .name + '</option>'
                                );
                            });
                        } else {
                            $('#site').append(
                                '<option value="" disabled>No sites found</option>');
                        }
                    }
                });
            });

            $('#site').on('change', function() {
                let id = $(this).val();
                var url = '{{ route('guards.getshifts', ':id') }}';
                url = url.replace(':id', id);
                $.ajax({
                    type: 'GET',
                    url: url,
                    dataType: 'json',
                    success: function(response) {
                        $('#shift').empty();
                        $('#shift').append(
                            '<option value="0" disabled selected>Select shift</option>');
                        response.forEach(function(element) {
                            $('#shift').append(
                                '<option value="' + element.id + '">' + element
                                .shift_name + '</option>'
                            );
                        });
                    }
                });
            });

        }); // end document.ready

        function validation() {
            var guard = $("#guard").val();
            var shift = $("#shift").val();
            var site = $("#site").val();
            var startdate = $("#fromdateSelectInput").val();
            var enddate = $("#todateSelectInput").val();

            if (!site || site == 0) {
                Swal.fire({
                    title: "Please select site",
                    icon: "warning",
                    button: "OK"
                });
                return false;
            }
            if (!shift || shift == 0) {
                Swal.fire({
                    title: "Please select shift",
                    icon: "warning",
                    button: "OK"
                });
                return false;
            }
            if (!guard || guard == '') {
                Swal.fire({
                    title: "Please select employee",
                    icon: "warning",
                    button: "OK"
                });
                return false;
            }
            if (!startdate) {
                Swal.fire({
                    title: "Please select start date",
                    icon: "warning",
                    button: "OK"
                });
                return false;
            }
            if (!enddate) {
                Swal.fire({
                    title: "Please select end date",
                    icon: "warning",
                    button: "OK"
                });
                return false;
            }
        }
    </script>
@endpush

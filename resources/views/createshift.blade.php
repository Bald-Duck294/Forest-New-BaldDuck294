@extends('layouts.app');

<div class="content">
    <div class="container-fluid create">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-12">
                        <h4>Add Shift</h4>
                    </div>
                </div>
            </div>
            <form method="post"
                action="{{ route('clients.shift_createaction', ['client_id' => $client_id, 'site_id' => $site_id]) }}"
                id='form_id' onsubmit="return validation()">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <span class="has-float-label">
                                    <input type="text" autocomplete="off" class="form-control" id="name"
                                        name="name" placeholder="Enter Name"
                                        value="{{ old('name', 'General Shift') }}">
                                    <label for="name">Shift Name*</label>
                                </span>
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <span class="has-float-label">
                                    <input type="time" name="start" class="form-control" id="startTime"
                                        value="{{ old('start') }}" required />
                                    <label for="startTime">Shift Start Time*</label>
                                </span>
                                <small class="form-text text-muted" id="startTime12hr"></small>
                                <span class="text-danger">{{ $errors->first('start') }}</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <span class="has-float-label">
                                    <input name="end" type="time" class="form-control" id="end-time"
                                        value="{{ old('end') }}" required />
                                    <label for="end-time">Shift End Time*</label>
                                </span>
                                <small class="form-text text-muted" id="endTime12hr"></small>
                                <span class="text-danger">{{ $errors->first('end') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <a href="{{ route('clients.getshifts', [$client_id, $site_id]) }}">
                        <button type="button" class="btn simple-button">Back</button>
                    </a>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
    console.log("create shift script initiated");

    // Convert 24-hour to 12-hour format
    function convertTo12Hour(time24h) {
        if (!time24h) return '';

        let [hours, minutes] = time24h.split(':');
        hours = parseInt(hours, 10);

        const modifier = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;

        return `${hours.toString().padStart(2, '0')}:${minutes} ${modifier}`;
    }

    $(document).ready(function() {
        // Destroy any mdtimepicker instances
        $('#startTime, #end-time').mdtimepicker('destroy');

        // Update 12-hour display when time changes
        $('#startTime').on('change input', function() {
            var time24 = $(this).val();
            if (time24) {
                var time12 = convertTo12Hour(time24);
                $('#startTime12hr').text('Selected: ' + time12);
            }
        });

        $('#end-time').on('change input', function() {
            var time24 = $(this).val();
            if (time24) {
                var time12 = convertTo12Hour(time24);
                $('#endTime12hr').text('Selected: ' + time12);
            }
        });

        // Show initial values if they exist
        var startVal = $('#startTime').val();
        var endVal = $('#end-time').val();

        if (startVal) {
            $('#startTime12hr').text('Selected: ' + convertTo12Hour(startVal));
        }

        if (endVal) {
            $('#endTime12hr').text('Selected: ' + convertTo12Hour(endVal));
        }
    });

    function validation() {
        var name = $("#name").val().trim();
        var starttime = $("#startTime").val();
        var endtime = $("#end-time").val();

        if (name == '') {
            Swal.fire({
                title: "Please enter shift name",
                icon: "warning",
                confirmButtonText: 'OK',
            });
            return false;
        }
        if (starttime == '' || !starttime) {
            Swal.fire({
                title: "Please select start time",
                icon: "warning",
                confirmButtonText: 'OK',
            });
            return false;
        }
        if (endtime == '' || !endtime) {
            Swal.fire({
                title: "Please select end time",
                icon: "warning",
                confirmButtonText: 'OK',
            });
            return false;
        }

        return true;
    }
</script>

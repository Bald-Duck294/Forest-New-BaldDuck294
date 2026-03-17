@php
    $hideGlobalFilters = true;
    $hideBackground = true;
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

        /* Center and constrain the form */
        .custom-theme-wrapper .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 1rem 0;
        }

        /* Base Card Styles */
        .custom-theme-wrapper .card {
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            background: var(--bg-card);
            margin-bottom: 24px;
            transition: background-color 0.3s, border-color 0.3s;
        }

        /* Header */
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

        /* Back Button */
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

        /* Form Elements */
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

        .custom-theme-wrapper .form-control::placeholder {
            color: var(--text-muted);
            opacity: 0.7;
        }

        .custom-theme-wrapper .form-control:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
            outline: none !important;
        }

        /* Time Input Styling specific fixes */
        .custom-theme-wrapper input[type="time"] {
            cursor: pointer;
        }

        .custom-theme-wrapper input[type="time"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            filter: invert(0.5);
            /* Helps it blend in both light and dark modes */
        }

        .custom-theme-wrapper .time-display {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            color: var(--primary);
            font-weight: 500;
        }

        /* Buttons */
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

        .custom-theme-wrapper .btn-cancel:hover {
            background: var(--btn-back-hover);
            color: var(--text-main) !important;
            border-color: var(--text-muted);
        }

        .custom-theme-wrapper .text-danger {
            font-size: 12px;
            margin-top: 6px;
            display: block;
            color: #ef4444 !important;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .custom-theme-wrapper .form-actions {
                flex-direction: column-reverse;
            }

            .custom-theme-wrapper .btn-primary-action,
            .custom-theme-wrapper .btn-cancel {
                width: 100%;
            }
        }
    </style>

    <div class="custom-theme-wrapper">
        <div class="form-container">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('clients.getshifts', [$client_id, $site_id]) }}" class="btn-back" title="Go Back">
                        <i class="la la-arrow-left"></i>
                    </a>
                    <h4>Add New Shift</h4>
                </div>

                <form method="post"
                    action="{{ route('clients.shift_createaction', ['client_id' => $client_id, 'site_id' => $site_id]) }}"
                    id="form_id" onsubmit="return validation()" autocomplete="off">
                    @csrf
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label for="name">Shift Name <span class="text-danger"
                                            style="display:inline; margin:0;">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="e.g. General Shift, Night Shift"
                                        value="{{ old('name', 'General Shift') }}">
                                    <span class="text-danger">{{ $errors->first('name') }}</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label for="startTime">Shift Start Time <span class="text-danger"
                                            style="display:inline; margin:0;">*</span></label>
                                    <input type="time" name="start" class="form-control" id="startTime"
                                        value="{{ old('start') }}" required />
                                    <span class="time-display" id="startTime12hr"></span>
                                    <span class="text-danger">{{ $errors->first('start') }}</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label for="end-time">Shift End Time <span class="text-danger"
                                            style="display:inline; margin:0;">*</span></label>
                                    <input type="time" name="end" class="form-control" id="end-time"
                                        value="{{ old('end') }}" required />
                                    <span class="time-display" id="endTime12hr"></span>
                                    <span class="text-danger">{{ $errors->first('end') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary-action">
                                <i class="la la-check"></i> Save Shift
                            </button>
                            <a href="{{ route('clients.getshifts', [$client_id, $site_id]) }}" class="btn-cancel">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        console.log("Create shift script initiated");

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
            // Destroy any mdtimepicker instances if they exist
            if ($.fn.mdtimepicker) {
                $('#startTime, #end-time').mdtimepicker('destroy');
            }

            // Update 12-hour display when time changes
            $('#startTime').on('change input', function() {
                var time24 = $(this).val();
                if (time24) {
                    var time12 = convertTo12Hour(time24);
                    $('#startTime12hr').text('Selected: ' + time12);
                } else {
                    $('#startTime12hr').text('');
                }
            });

            $('#end-time').on('change input', function() {
                var time24 = $(this).val();
                if (time24) {
                    var time12 = convertTo12Hour(time24);
                    $('#endTime12hr').text('Selected: ' + time12);
                } else {
                    $('#endTime12hr').text('');
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

        // Form Validation
        function validation() {
            var name = $("#name").val().trim();
            var starttime = $("#startTime").val();
            var endtime = $("#end-time").val();

            // Safe SweetAlert caller
            function showAlert(msg) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: msg,
                        icon: "warning",
                        confirmButtonColor: '#2563eb',
                        confirmButtonText: 'OK',
                    });
                } else {
                    alert(msg);
                }
            }

            if (name === '') {
                showAlert("Please enter a shift name");
                return false;
            }
            if (starttime === '' || !starttime) {
                showAlert("Please select a start time");
                return false;
            }
            if (endtime === '' || !endtime) {
                showAlert("Please select an end time");
                return false;
            }

            return true;
        }
    </script>
@endpush

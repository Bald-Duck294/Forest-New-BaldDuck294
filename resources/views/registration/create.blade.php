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
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);

            font-family: 'Inter', sans-serif;
            color: var(--text-main);
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
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.4);
        }

        /* Form Container Constraint */
        .custom-theme-wrapper .form-container {
            max-width: 850px;
            margin: 0 auto;
            padding: 1rem 0;
        }

        /* Base Card Styles */
        .custom-theme-wrapper .card {
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-md);
            background: var(--bg-card);
            margin-bottom: 24px;
            transition: background-color 0.3s, border-color 0.3s;
            overflow: hidden;
        }

        /* Card Header */
        .custom-theme-wrapper .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border);
            padding: 24px 32px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .custom-theme-wrapper .card-header h4 {
            margin: 0;
            font-weight: 700;
            color: var(--text-main);
            font-size: 1.125rem;
            letter-spacing: -0.01em;
        }

        .custom-theme-wrapper .card-header p {
            margin: 4px 0 0 0;
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        /* Back Button */
        .custom-theme-wrapper .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: var(--btn-back-bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-main);
            text-decoration: none !important;
            transition: all 0.2s ease;
            flex-shrink: 0;
            box-shadow: var(--shadow-sm);
        }

        .custom-theme-wrapper .btn-back:hover {
            background: var(--btn-back-hover);
            transform: translateX(-2px);
        }

        .custom-theme-wrapper .card-body {
            padding: 32px;
        }

        /* Form Elements */
        .custom-theme-wrapper .form-group {
            margin-bottom: 0;
            /* Handled by Bootstrap row gap (g-4) */
        }

        .custom-theme-wrapper .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-main);
            font-size: 0.875rem;
        }

        .custom-theme-wrapper .form-control {
            border-radius: 10px !important;
            border: 1px solid var(--border) !important;
            padding: 12px 16px !important;
            height: auto !important;
            font-size: 0.9375rem !important;
            transition: all 0.2s ease !important;
            color: var(--text-main) !important;
            background: var(--bg-input) !important;
            width: 100%;
            box-shadow: var(--shadow-sm);
        }

        .custom-theme-wrapper .form-control::placeholder {
            color: var(--text-muted);
            opacity: 0.6;
        }

        .custom-theme-wrapper .form-control:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
            outline: none !important;
            background: var(--bg-card) !important;
            /* Slightly brightens in dark mode */
        }

        .custom-theme-wrapper select.form-control {
            cursor: pointer;
            -webkit-appearance: auto;
            appearance: auto;
        }

        /* Buttons & Footer */
        .custom-theme-wrapper .form-actions {
            margin-top: 40px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            align-items: center;
        }

        .custom-theme-wrapper .btn-primary-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--primary) !important;
            color: #fff !important;
            border-radius: 10px;
            padding: 12px 28px;
            font-weight: 600;
            font-size: 0.9375rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.1);
        }

        .custom-theme-wrapper .btn-primary-action:hover {
            background: var(--primary-hover) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }

        .custom-theme-wrapper .btn-cancel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: transparent !important;
            color: var(--text-muted) !important;
            border: 1px solid var(--border) !important;
            border-radius: 10px;
            padding: 12px 24px;
            font-weight: 600;
            font-size: 0.9375rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none !important;
        }

        .custom-theme-wrapper .btn-cancel:hover {
            background: var(--bg-hover) !important;
            color: var(--text-main) !important;
            border-color: var(--text-muted) !important;
        }

        .custom-theme-wrapper .text-danger {
            font-size: 0.75rem;
            margin-top: 6px;
            display: block;
            color: #ef4444 !important;
            font-weight: 500;
        }

        /* Asterisk */
        .custom-theme-wrapper .required-asterisk {
            color: #ef4444;
            margin-left: 2px;
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

            .custom-theme-wrapper .card-header {
                padding: 20px;
            }

            .custom-theme-wrapper .card-body {
                padding: 20px;
            }
        }
    </style>

    <div class="custom-theme-wrapper">
        <div class="form-container">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('registrations.index') }}" class="btn-back" title="Back to Member List">
                        <i class="la la-arrow-left"></i>
                    </a>
                    <div>
                        <h4>Add New Member</h4>
                        <p>Fill in the details below to register a new user in the system.</p>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('registrations.store') }}" autocomplete="off">
                        @csrf

                        <div class="row g-4">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="firstName">First Name <span class="required-asterisk">*</span></label>
                                    <input type="text" name="firstName" id="firstName" class="form-control"
                                        placeholder="e.g. John" value="{{ old('firstName') }}" required>
                                    @error('firstName')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lastName">Last Name</label>
                                    <input type="text" name="lastName" id="lastName" class="form-control"
                                        placeholder="e.g. Doe" value="{{ old('lastName') }}">
                                    @error('lastName')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="mobile">Mobile Number <span class="required-asterisk">*</span></label>
                                    <input type="text" name="mobile" id="mobile" class="form-control numeric-only"
                                        placeholder="Enter 10-digit number" value="{{ old('mobile') }}" maxlength="10"
                                        inputmode="numeric" required>
                                    @error('mobile')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" name="email" id="email" class="form-control"
                                        placeholder="name@company.com" value="{{ old('email') }}">
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department">Department</label>
                                    <input type="text" name="department" id="department" class="form-control"
                                        placeholder="e.g. Engineering" value="{{ old('department') }}">
                                    @error('department')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="designation">Designation</label>
                                    <input type="text" name="designation" id="designation" class="form-control"
                                        placeholder="e.g. Senior Developer" value="{{ old('designation') }}">
                                    @error('designation')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="role_id">System Role <span class="required-asterisk">*</span></label>
                                    <select name="role_id" id="role_id" class="form-control" required>
                                        <option value="" disabled selected>-- Select Member Role --</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}"
                                                {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                                {{ $role->role_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                        </div>

                        <div class="form-actions">
                            <a href="{{ route('registrations.index') }}" class="btn-cancel">Cancel</a>
                            <button type="submit" class="btn-primary-action">
                                <i class="la la-check"></i> Register Member
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Superior numeric validation logic
            $('.numeric-only').on('input', function() {
                // Instantly strip out non-numeric characters on paste/typing
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            $('.numeric-only').on('keypress', function(e) {
                // Prevent typing of non-numbers at the keystroke level
                if (e.which < 48 || e.which > 57) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endpush

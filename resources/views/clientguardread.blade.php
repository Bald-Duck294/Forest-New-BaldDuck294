@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    $sessionUser = session('user'); // Stored safely to check permissions later
@endphp
@extends('layouts.app')

@push('styles')
    <style>
        /* ==== LIGHT & DARK THEME VARIABLES ==== */
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --badge-bg: #f1f5f9;
            --warning: #f59e0b;
            --warning-hover: #d97706;
        }

        [data-bs-theme="dark"],
        body.dark-mode,
        body.dark {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --badge-bg: #334155;
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

        .profile-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        /* ==== HEADER ==== */
        .profile-header {
            border-bottom: 1px solid var(--border-color);
            padding: 20px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .profile-header h4 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-edit-profile {
            background-color: var(--card-bg);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
            text-decoration: none;
        }

        .btn-edit-profile:hover {
            background-color: var(--badge-bg);
            color: var(--primary);
            border-color: var(--primary);
        }

        /* ==== BODY & GRID ==== */
        .profile-body {
            padding: 32px 28px;
        }

        .profile-img-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding-bottom: 24px;
        }

        .profile-img {
            height: 120px;
            width: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid var(--border-color);
            padding: 4px;
            margin-bottom: 16px;
            cursor: pointer;
            transition: transform 0.2s ease;
            background: var(--card-bg);
        }

        .profile-img:hover {
            transform: scale(1.05);
            border-color: var(--primary);
        }

        .profile-name {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 4px;
        }

        .profile-id {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 12px;
            font-family: monospace;
        }

        .role-badge {
            background: var(--badge-bg);
            color: var(--text-main);
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid var(--border-color);
        }

        /* ==== DETAILS SECTION ==== */
        .details-section {
            border-left: 1px solid var(--border-color);
            padding-left: 32px;
        }

        @media (max-width: 768px) {
            .details-section {
                border-left: none;
                border-top: 1px solid var(--border-color);
                padding-left: 0;
                padding-top: 32px;
                margin-top: 16px;
            }
        }

        .section-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .info-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .info-label {
            font-size: 12px;
            font-weight: 500;
            color: var(--text-muted);
        }

        .info-value {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-main);
        }

        .site-value {
            color: var(--primary);
            font-weight: 700;
        }

        /* Action Buttons */
        .action-row {
            display: flex;
            gap: 12px;
            margin-top: 16px;
            border-top: 1px solid var(--border-color);
            padding-top: 24px;
        }

        .btn-action-modify {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: 0.2s;
        }

        .btn-action-modify:hover {
            background: var(--badge-bg);
            border-color: var(--text-muted);
            color: var(--text-main);
        }

        .btn-action-assign {
            background: var(--primary);
            border: 1px solid var(--primary);
            color: #fff;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: 0.2s;
        }

        .btn-action-assign:hover {
            background: var(--primary-hover);
            color: #fff;
        }
    </style>
@endpush

@section('content')
    <div class="main-content-wrapper">
        <div class="profile-card">

            <div class="profile-header">
                <h4>
                    <a href="javascript:history.back()" class="text-muted mr-2" style="text-decoration: none;">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <i class="bi bi-person-badge text-primary"></i> Employee Details
                </h4>

                @if (isset($user))
                    <a href="{{ route('users.edit', $user->id) }}" class="btn-edit-profile">
                        <i class="bi bi-pencil"></i> Edit Profile
                    </a>
                @endif
            </div>

            <div class="profile-body">
                <div class="row m-0">

                    <div class="col-md-4 p-0">
                        <div class="profile-img-container">
                            @if (isset($user->profile_pic) && $user->profile_pic != '')
                                <img class="profile-img" src="{{ $user->profile_pic }}"
                                    onclick="openImageModal('{{ $user->profile_pic }}')" alt="Profile Image" />
                            @else
                                <img class="profile-img" src="{{ asset('assets/img/default-avatar.png') }}"
                                    alt="Default Avatar" />
                            @endif

                            <div class="profile-name">{{ $user->name ?? 'N/A' }}</div>
                            <div class="profile-id">{{ $user->gen_id ?? 'ID: N/A' }}</div>
                            <div class="role-badge">Employee</div>
                        </div>
                    </div>

                    <div class="col-md-8 p-0 details-section">

                        <div class="section-title">
                            <i class="bi bi-person-vcard"></i> Personal Information
                        </div>
                        <div class="info-grid">
                            <div class="info-group">
                                <span class="info-label">Phone Number</span>
                                <span class="info-value">{{ $user->contact ?? 'N/A' }}</span>
                            </div>
                            <div class="info-group">
                                <span class="info-label">Email Address</span>
                                <span class="info-value">{{ $user->email ?? 'N/A' }}</span>
                            </div>
                            <div class="info-group">
                                <span class="info-label">Date of Birth</span>
                                <span
                                    class="info-value">{{ isset($user->dob) ? date('d M Y', strtotime($user->dob)) : 'N/A' }}</span>
                            </div>
                            <div class="info-group">
                                <span class="info-label">Gender</span>
                                <span class="info-value">{{ $user->gender ?? 'N/A' }}</span>
                            </div>
                            <div class="info-group" style="grid-column: 1 / -1;">
                                <span class="info-label">Residential Address</span>
                                <span class="info-value">{{ $user->address ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="section-title mt-4">
                            <i class="bi bi-geo-alt"></i> Current Assignment
                        </div>
                        <div class="info-grid">
                            <div class="info-group">
                                <span class="info-label">Assigned Site</span>
                                @if (isset($site_assign) && $site_assign->site_name)
                                    <span class="info-value site-value">{{ $site_assign->site_name }}</span>
                                @else
                                    <span class="info-value text-muted fst-italic">No Site Assigned</span>
                                @endif
                            </div>
                            <div class="info-group">
                                <span class="info-label">Shift Name</span>
                                <span class="info-value">{{ $site_assign->shift_name ?? 'N/A' }}</span>
                            </div>
                            <div class="info-group">
                                <span class="info-label">Shift Duration</span>
                                <span class="info-value">
                                    @if (isset($site_assign->shift_time))
                                        @php $time = json_decode($site_assign->shift_time); @endphp
                                        {{ $time->start ?? '' }} - {{ $time->end ?? '' }}
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                            <div class="info-group">
                                <span class="info-label">Assignment Period</span>
                                <span class="info-value">
                                    @if (isset($site_assign->date_range))
                                        @php $range = json_decode($site_assign->date_range); @endphp
                                        {{ date('d M Y', strtotime($range->from)) }} to
                                        {{ date('d M Y', strtotime($range->to)) }}
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                        </div>

                        @if ($sessionUser && $sessionUser->role_id != 4)
                            <div class="action-row">
                                @if (isset($site_assign) && $site_assign != '')
                                    <a href="{{ route('guards.guard_edit', [$client_id ?? 0, $site_assign->id]) }}"
                                        class="btn-action-modify">
                                        <i class="bi bi-sliders"></i> Modify Assignment
                                    </a>
                                @else
                                    <a href="{{ route('clients.clientguard_create', [$id ?? 0, 0]) }}"
                                        class="btn-action-assign">
                                        <i class="bi bi-plus-circle"></i> Assign to Site
                                    </a>
                                @endif
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="profileImageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background: transparent; border: none;">
                <div class="modal-header border-0 pb-0 justify-content-end">
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"
                        style="background-color: rgba(255,255,255,0.8); padding: 10px; border-radius: 50%;"></button>
                </div>
                <div class="modal-body text-center pt-0">
                    <img id="modalImage" src=""
                        style="max-width: 100%; max-height: 80vh; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openImageModal(imageUrl) {
            $('#modalImage').attr('src', imageUrl);
            $('#profileImageModal').modal('show');
        }
    </script>
@endpush

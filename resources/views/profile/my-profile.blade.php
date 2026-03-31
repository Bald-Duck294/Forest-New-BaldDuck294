@php
$hideGlobalFilters = true;
$hideBackground = true;
$sessionUser = session('user');
@endphp
@extends('layouts.app')

@section('title', 'My Profile')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    /* =========================================
           LOCAL COMPONENT STYLES
           (Hooked to Global Sapphire Variables)
        ========================================= */

    /* Cards */
    .dash-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }

    /* Action Buttons */
    .btn-sapphire {
        background-color: var(--sapphire-primary);
        color: #ffffff;
        border: none;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 8px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 0.9rem;
        text-decoration: none;
    }

    .btn-sapphire:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        color: #ffffff;
    }

    .btn-sapphire-outline {
        background-color: transparent;
        color: var(--text-main);
        border: 1px solid var(--border-color);
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 8px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 0.9rem;
        text-decoration: none;
    }

    .btn-sapphire-outline:hover {
        background-color: var(--table-hover);
        color: var(--sapphire-primary);
        border-color: var(--sapphire-primary);
    }

    /* Soft Badges */
    .badge-soft {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-soft-primary {
        background: rgba(59, 130, 246, 0.15);
        color: var(--sapphire-primary);
    }

    .badge-soft-muted {
        background: rgba(100, 116, 139, 0.15);
        color: var(--text-muted);
    }

    /* Profile Specifics */
    .profile-img-wrapper {
        position: relative;
        width: 140px;
        height: 140px;
        margin: 0 auto 1.5rem auto;
    }

    .profile-img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid var(--bg-body);
        background: var(--bg-body);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .profile-img:hover {
        transform: scale(1.05);
    }

    /* Info Typography */
    .section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 8px;
        padding-bottom: 12px;
        border-bottom: 1px dashed var(--border-color);
    }

    .info-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .info-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 0;
        line-height: 1.4;
    }

    .info-value.highlight {
        color: var(--sapphire-primary);
    }

    /* Modal Overrides */
    .sapphire-modal {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        overflow: hidden;
    }

    .sapphire-modal .btn-close {
        filter: var(--bs-theme)=='dark' ? 'invert(1)': 'none';
    }

    .btn-back-modern {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        color: var(--text-main);
        font-size: 0.85rem;
        font-weight: 600;
        padding: 6px 16px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        transition: all 0.2s ease;
    }

    .btn-back-modern:hover {
        background-color: var(--bg-body);
        color: var(--sapphire-primary);
        border-color: var(--sapphire-primary);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">


    {{-- PAGE HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div class="d-flex align-items-center gap-3">
            {{-- NEW UPGRADED BACK BUTTON --}}
            <a href="javascript:history.back()" class="text-decoration-none d-inline-flex align-items-center gap-2 btn-back-modern m-0">
                <i class="bi bi-arrow-left" style="font-size: 1rem;"></i> Go Back
            </a>

            <h4 class="fw-bold mb-0" style="color: var(--text-main);">My Profile</h4>
        </div>

        <div>
            @if (isset($user))
            <a href="{{ route('profile.edit') }}" class="btn-sapphire-outline shadow-sm">
                <i class="bi bi-pencil-square"></i> Edit Profile
            </a>
            @endif
        </div>
    </div>

    <div class="row g-4">

        {{-- LEFT COLUMN: PROFILE CARD --}}
        <div class="col-12 col-lg-4 col-xl-3">
            <div class="dash-card p-4 text-center h-100">
                <div class="profile-img-wrapper">
                    @if (isset($user->profile_pic) && $user->profile_pic != '')
                    <img class="profile-img" src="{{ $user->profile_pic }}" onclick="openImageModal('{{ $user->profile_pic }}')" alt="Profile Image" />
                    @else
                    <img class="profile-img" src="{{ asset('assets/img/default-avatar.png') }}" alt="Default Avatar" />
                    @endif
                </div>

                <h4 class="fw-bold mb-1" style="color: var(--text-main);">{{ $user->name ?? 'N/A' }}</h4>
                <p class="font-monospace mb-3" style="color: var(--text-muted); font-size: 0.85rem;">{{ $user->gen_id ?? 'ID: N/A' }}</p>

                <div>
                    {{-- You can display role name here dynamically if needed: $user->role->name --}}
                    <span class="badge-soft badge-soft-primary">My Account</span>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: DETAILS --}}
        <div class="col-12 col-lg-8 col-xl-9">
            <div class="dash-card p-4 h-100 d-flex flex-column">

                {{-- Personal Information Section --}}
                <div class="mb-4">
                    <div class="section-title">
                        <i class="bi bi-person-vcard text-primary" style="color: var(--sapphire-primary) !important;"></i> Personal Information
                    </div>

                    <div class="row g-4">
                        <div class="col-sm-6 col-md-4">
                            <div class="info-label">Phone Number</div>
                            <div class="info-value font-monospace">{{ $user->contact ?? 'N/A' }}</div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-label">Email Address</div>
                            <div class="info-value text-break">{{ $user->email ?? 'N/A' }}</div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-label">Date of Birth</div>
                            <div class="info-value">{{ isset($user->dob) ? date('d M Y', strtotime($user->dob)) : 'N/A' }}</div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="info-label">Gender</div>
                            <div class="info-value">{{ $user->gender ?? 'N/A' }}</div>
                        </div>
                        <div class="col-12 col-md-8">
                            <div class="info-label">Residential Address</div>
                            <div class="info-value">{{ $user->address ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Current Assignment Section --}}
                <div class="mb-4 flex-grow-1">
                    <div class="section-title">
                        <i class="bi bi-geo-alt text-primary" style="color: var(--sapphire-primary) !important;"></i> Current Assignment
                    </div>

                    <div class="row g-4">
                        <div class="col-sm-6 col-md-4">
                            <div class="info-label">Assigned Site</div>
                            @if (isset($site_assign) && $site_assign->site_name)
                            <div class="info-value highlight">{{ $site_assign->site_name }}</div>
                            @else
                            <div class="info-value" style="color: var(--text-muted); font-style: italic;">No Site Assigned</div>
                            @endif
                        </div>

                        <div class="col-sm-6 col-md-4">
                            <div class="info-label">Shift Name</div>
                            <div class="info-value">{{ $site_assign->shift_name ?? 'N/A' }}</div>
                        </div>

                        <div class="col-sm-6 col-md-4">
                            <div class="info-label">Shift Duration</div>
                            <div class="info-value">
                                @if (isset($site_assign->shift_time))
                                @php $time = json_decode($site_assign->shift_time); @endphp
                                {{ $time->start ?? '' }} <span style="color: var(--text-muted); font-size: 0.8rem; margin: 0 4px;">to</span> {{ $time->end ?? '' }}
                                @else
                                N/A
                                @endif
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="info-label">Assignment Period</div>
                            <div class="info-value">
                                @if (isset($site_assign->date_range))
                                @php $range = json_decode($site_assign->date_range); @endphp
                                <span class="badge-soft badge-soft-muted mt-1">
                                    {{ date('d M Y', strtotime($range->from)) }}
                                    <i class="bi bi-arrow-right mx-2"></i>
                                    {{ date('d M Y', strtotime($range->to)) }}
                                </span>
                                @else
                                N/A
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

{{-- Bootstrap 5 Modal for Profile Picture --}}
<div class="modal fade" id="profileImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content sapphire-modal" style="background: transparent; border: none; box-shadow: none;">
            <div class="modal-header border-0 pb-0 justify-content-end">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background-color: var(--bg-card); padding: 12px; border-radius: 50%; opacity: 1; box-shadow: 0 4px 12px rgba(0,0,0,0.15);"></button>
            </div>
            <div class="modal-body text-center pt-2">
                <img id="modalImage" src="" style="max-width: 100%; max-height: 75vh; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); border: 2px solid var(--border-color);">
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
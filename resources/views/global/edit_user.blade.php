@php
$hideGlobalFilters = true;
@endphp

@extends('layouts.app')

@section('title', 'Edit User - ' . $editUser->name)

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ url('/global-dashboard') }}" class="text-decoration-none text-primary">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('global.superadmins') }}" class="text-decoration-none text-primary">Super Admins</a></li>
            <li class="breadcrumb-item active text-secondary">Edit User</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-secondary-subtle shadow-sm rounded-4 bg-body overflow-hidden">

                <div class="card-header bg-body-tertiary border-bottom border-secondary-subtle py-4 px-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-2 bg-primary bg-opacity-10 rounded-3 text-primary border border-primary border-opacity-25">
                            <i class="bi bi-person-gear fs-4"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0 text-body">Account Settings</h4>
                            <p class="text-secondary small mb-0">Updating profile for <span class="text-primary fw-semibold">{{ $editUser->name }}</span></p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('global.users.update', $editUser->id)}}">
                    @csrf
                    <div class="card-body p-4 p-md-5">

                        @if (session('success'))
                        <div class="alert alert-success d-flex align-items-center gap-2 rounded-3 border-0 shadow-sm mb-4">
                            <i class="bi bi-check-circle-fill"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                        @endif

                        <div class="mb-5">
                            <h6 class="text-uppercase small fw-bold text-secondary tracking-wider mb-4 border-bottom pb-2">
                                <i class="bi bi-person-lines-fill me-2"></i>Personal Information
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Full Name</label>
                                    <input type="text" name="name" class="form-control bg-body-tertiary border-secondary-subtle @error('name') is-invalid @enderror"
                                        value="{{ old('name', $editUser->name) }}" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Contact Number</label>
                                    <input type="text" name="contact" class="form-control bg-body-tertiary border-secondary-subtle Number"
                                        value="{{ old('contact', $editUser->contact) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary">Date of Birth</label>
                                    <input type="date" name="dob" class="form-control bg-body-tertiary border-secondary-subtle"
                                        value="{{ old('dob', $editUser->dob) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary">Gender</label>
                                    <select name="gender" class="form-select bg-body-tertiary border-secondary-subtle">
                                        <option value="Male" {{ old('gender', $editUser->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender', $editUser->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                        <option value="Other" {{ old('gender', $editUser->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary">Code Name</label>
                                    <input type="text" name="code_name" class="form-control bg-body-tertiary border-secondary-subtle"
                                        value="{{ old('code_name', $editUser->code_name) }}">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-uppercase small fw-bold text-secondary tracking-wider mb-4 border-bottom pb-2">
                                <i class="bi bi-shield-lock-fill me-2"></i>Security & Access
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Email Address</label>
                                    <input type="email" name="email" class="form-control bg-body-tertiary border-secondary-subtle"
                                        value="{{ old('email', $editUser->email) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">System Role</label>
                                    <select name="role_id" class="form-select bg-body-tertiary border-secondary-subtle">
                                        @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id', $editUser->role_id) == $role->id ? 'selected' : '' }}>
                                            {{ ucfirst($role->role_name) }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary text-primary">Update Password (Optional)</label>
                                    <input type="password" name="password" class="form-control bg-body-tertiary border-secondary-subtle" placeholder="Leave blank to keep current">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary">Confirm New Password</label>
                                    <input type="password" name="password_confirmation" class="form-control bg-body-tertiary border-secondary-subtle">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-body-tertiary border-top border-secondary-subtle py-4 px-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('global.superadmins.view', $editUser->id) }}" class="btn btn-outline-secondary px-4 fw-bold rounded-3">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary px-5 fw-bold rounded-3 shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .tracking-wider {
        letter-spacing: 0.05em;
    }

    .bg-body-tertiary {
        background-color: rgba(var(--bs-tertiary-bg-rgb), 0.5) !important;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.15);
    }

    [data-bs-theme="dark"] .form-control,
    [data-bs-theme="dark"] .form-select {
        color: #f8fafc;
    }
</style>
@endsection

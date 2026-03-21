@php
$hideGlobalFilters = true;
@endphp

@extends('layouts.app')

<style>
    body {
        background: #f6f6f8;
    }

    .header-box {
        /* background: #fff; */
        border-radius: 16px;
        padding: 20px 25px;
        border: 1px solid #e5e7eb;
    }

    .module-card {
        border-radius: 18px;
        transition: 0.25s ease;
        border: 2px solid transparent;
    }

    .module-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
    }

    .module-active {
        border-color: #0d6efd;
        background: rgba(13, 110, 253, 0.03);
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.05);
    }

    .icon-box {
        width: 55px;
        height: 55px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: #fff;
    }

    .badge-enabled {
        background: #e7f1ff;
        color: #0d6efd;
        border: 1px solid #b6d4fe;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1px;
    }

    /* DARK MODE */
    body.dark-mode .badge-enabled {
        background: #1a2b4c;
        color: #6ea8fe;
        border: 1px solid #2f4f8f;
    }

    /* DISABLED */
    .badge-disabled {
        background: #e2e8f0;
        color: #475569;
        border: 1px solid #cbd5e1;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1px;
    }

    /* DARK MODE */
    body.dark-mode .badge-disabled {
        background: #2b3441;
        color: #cbd5e1;
        border: 1px solid #3f4d63;
    }
</style>

@section('content')

<div class="container-fluid py-4">

    <!-- TOP HEADER -->
    <div class="header-box mb-3 px-3 py-3 d-flex justify-content-between align-items-center">

        <!-- LEFT -->
        <div>
            <h5 class="fw-bold mb-1">Module Governance</h5>
            <p class="text-muted small mb-0">
                Configure enterprise-wide feature availability
            </p>
        </div>

        <!-- RIGHT (INLINE FORM, NOT CARD) -->
        <form method="GET"
            action="{{ route('modules.index') }}"
            class="d-flex align-items-end gap-2 m-0">

            <div>
                <label class="form-label small text-muted fw-semibold mb-1">
                    Organization
                </label>

                <select name="company_id"
                    class="form-select form-select-sm"
                    onchange="this.form.submit()">

                    <option value="">Select</option>

                    @foreach($companies as $company)
                    <option value="{{ $company->id }}"
                        @if(isset($selectedCompany) && $selectedCompany->id == $company->id) selected @endif>
                        {{ $company->name }}
                    </option>
                    @endforeach

                </select>
            </div>

        </form>

    </div>


    @if(isset($selectedCompany))

    <!-- FORM -->
    <form method="POST" action="{{ route('modules.update') }}">
        @csrf

        <input type="hidden" name="company_id" value="{{ $selectedCompany->id }}">

        <div class="d-flex gap-2 mb-4">
            <button type="reset" class="btn btn-outline-secondary">
                Reset
            </button>

            <button type="submit" class="btn btn-primary">
                Save Changes
            </button>
        </div>


        <!-- MODULE GRID -->
        <div class="row g-4">

            @foreach($modules as $module)

            @php
            $isEnabled = isset($selectedCompany->features[$module]) && $selectedCompany->features[$module];
            @endphp

            <div class="col-md-4">

                <div class="card module-card h-100 p-3
                        {{ $isEnabled ? 'module-active' : '' }}">

                    <div class="card-body d-flex flex-column justify-content-between">

                        <div>

                            <!-- ICON + STATUS -->
                            <div class="d-flex justify-content-between mb-3">

                                <div class="icon-box bg-primary">
                                    <i class="bi bi-grid"></i>
                                </div>

                                <span class="badge
                                        {{ $isEnabled ? 'badge-enabled' : 'badge-disabled' }}">
                                    {{ $isEnabled ? 'ENABLED' : 'DISABLED' }}
                                </span>

                            </div>

                            <!-- TITLE -->
                            <h5 class="fw-bold mb-2">
                                {{ ucfirst(str_replace('-', ' ', $module)) }}
                            </h5>

                            <p class="text-muted small">
                                Module access control and permissions management.
                            </p>

                        </div>

                        <!-- FOOTER -->
                        <div class="d-flex justify-content-between align-items-center mt-3">

                            <span class="small text-muted">
                                Status
                            </span>

                            <div class="form-check form-switch">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="{{ $module }}"

                                    @if($isEnabled) checked @endif>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            @endforeach

        </div>

    </form>

    @else
    <!-- EMPTY STATE -->
    <div class="card shadow-sm border-0 text-center py-5 mt-4">

        <div class="card-body">

            <!-- ICON -->
            <div class="mb-3">
                <i class="bi bi-building text-primary" style="font-size: 40px;"></i>
            </div>

            <!-- TITLE -->
            <h5 class="fw-bold mb-2">
                No Organization Selected
            </h5>

            <!-- DESC -->
            <p class="text-muted mb-4">
                Select an organization from the dropdown above to manage module permissions.
            </p>

            <!-- OPTIONAL ACTION -->
            <button class="btn btn-primary btn-sm"
                onclick="document.querySelector('select[name=company_id]').focus()">
                Select Organization
            </button>

        </div>

    </div>


    @endif

</div>

@endsection

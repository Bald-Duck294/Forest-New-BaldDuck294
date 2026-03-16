@php
$hideGlobalFilters = true;
@endphp

@extends('layouts.app')


<style>
    .module-card {
        border-radius: 12px;
        transition: 0.2s;
    }

    .module-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .icon-box {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #eef2ff;
        font-size: 20px;
    }

    /* DARK MODE */

    body.dark-mode {
        background: #0f172a;
        color: #e5e7eb;
    }

    body.dark-mode .card {
        background: #1e293b;
        border-color: #334155;
    }

    body.dark-mode .text-muted {
        color: #94a3b8 !important;
    }
</style>

@section('content')

<div class="container-fluid py-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h4 class="fw-bold mb-1">Module Permissions</h4>
            <p class="text-muted mb-0">
                Enable or disable features for the selected company.
            </p>
        </div>

    </div>


    <!-- COMPANY SELECT FORM (GET) -->

    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <form method="GET" action="{{ route('modules.index') }}">

                <div class="row">

                    <div class="col-md-3">

                        <label class="form-label fw-semibold">
                            Select Company
                        </label>

                        <select name="company_id"
                            class="form-select"
                            onchange="this.form.submit()">

                            <option value="">Select Company</option>

                            @foreach($companies as $company)

                            <option value="{{ $company->id }}"
                                @if(isset($selectedCompany) && $selectedCompany->id == $company->id)
                                selected
                                @endif
                                >
                                {{ $company->name }}

                            </option>

                            @endforeach

                        </select>

                    </div>

                </div>

            </form>

        </div>
    </div>


    @if(isset($selectedCompany))

    <!-- MODULE SAVE FORM -->

    <form method="POST" action="{{ route('modules.update') }}">
        @csrf

        <input type="hidden"
            name="company_id"
            value="{{ $selectedCompany->id }}">


        <div class="d-flex gap-2 mb-4">

            <button type="reset"
                class="btn btn-outline-secondary">

                Reset

            </button>

            <button type="submit"
                class="btn btn-primary">

                Save Changes

            </button>

        </div>


        <!-- MODULE GRID -->

        <div class="row g-4">

            @foreach($modules as $module)

            <div class="col-md-4">

                <div class="card shadow-sm module-card h-100">

                    <div class="card-body d-flex flex-column justify-content-between">

                        <div>

                            <div class="icon-box mb-3">
                                <i class="bi bi-grid"></i>
                            </div>

                            <h5 class="fw-bold">

                                {{ ucfirst(str_replace('-', ' ', $module)) }}

                            </h5>

                            <p class="text-muted small">

                                Module access control

                            </p>

                        </div>


                        <div class="d-flex justify-content-between align-items-center mt-3">

                            <span class="small text-muted">

                                Status

                            </span>

                            <div class="form-check form-switch">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="{{ $module }}"

                                    @if(
                                    isset($selectedCompany->features[$module])
                                && $selectedCompany->features[$module]
                                )

                                checked

                                @endif
                                >

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            @endforeach

        </div>

    </form>

    @endif

</div>

@endsection

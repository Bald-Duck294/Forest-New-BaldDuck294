@php
$hideGlobalFilters = true;
@endphp

@extends('layouts.app')

@section('content')

<div class="container py-5" style="max-width:1100px">

    <!-- HEADER -->
    <div class="mb-4">
        <h3 class="fw-bold">Add Company</h3>
        <p class="text-muted mb-0">
            Fill in the information below to register a new company in the system.
        </p>
    </div>


    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif


    {{-- VALIDATION ERRORS --}}
    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif



    <!-- FORM CARD -->
    <div class="card border-0 shadow-sm rounded-4">

        <div class="card-body p-4 p-md-5">

            <form method="POST" action="{{ route('companies.store') }}">
                @csrf

                <div class="row g-4">

                    <!-- COMPANY NAME -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Company Name</label>
                        <input type="text"
                            name="name"
                            class="form-control form-control-lg"
                            placeholder="e.g. Acme Corp">
                    </div>


                    <!-- COMPANY CONTACT -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Company Contact</label>
                        <input type="text"
                            name="contact"
                            class="form-control form-control-lg"
                            placeholder="+91 9876543210">
                    </div>


                    <!-- COMPANY EMAIL -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Company Email</label>
                        <input type="email"
                            name="email"
                            class="form-control form-control-lg"
                            placeholder="contact@company.com">
                    </div>


                    <!-- INDUSTRY TYPE -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Industry Type</label>
                        <select name="type" class="form-select form-select-lg">
                            <option value="">Select Industry</option>
                            <option value="forest">Forest</option>
                            <option value="zp">ZP</option>
                            <option value="org">Organization</option>
                        </select>
                    </div>



                    <!-- CONTACT PERSON -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Contact Person</label>
                        <input type="text"
                            name="contact_person"
                            class="form-control form-control-lg"
                            placeholder="Full Name">
                    </div>



                    <!-- CONTACT PERSON PHONE -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Contact Person Phone</label>
                        <input type="text"
                            name="contact_person_contact"
                            class="form-control form-control-lg"
                            placeholder="+91 9876543210">
                    </div>



                    <!-- DESIGNATION -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Designation</label>
                        <input type="text"
                            name="contact_person_designation"
                            class="form-control form-control-lg"
                            placeholder="Manager">
                    </div>



                    <!-- EMPLOYEE LIMIT -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Employee Limit</label>
                        <input type="number"
                            name="empLimit"
                            class="form-control form-control-lg"
                            placeholder="100">
                    </div>



                    <!-- START DATE -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Start Date</label>
                        <input type="date"
                            name="start_date"
                            class="form-control form-control-lg">
                    </div>



                    <!-- END DATE -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">End Date</label>
                        <input type="date"
                            name="end_date"
                            class="form-control form-control-lg">
                    </div>



                    <!-- ADDRESS -->
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Address</label>
                        <textarea
                            name="address"
                            rows="3"
                            class="form-control form-control-lg"
                            placeholder="Enter company address"></textarea>
                    </div>



                    <!-- STATUS -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="isActive" class="form-select form-select-lg">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                </div>



                <!-- ACTION BUTTONS -->
                <div class="mt-5 pt-4 border-top d-flex flex-column flex-sm-row justify-content-end gap-3">

                    <button type="submit"
                        class="btn btn-dark px-4 py-2">
                        Save Company
                    </button>

                    <a href="{{ route('global.dashboard') }}"
                        class="btn btn-outline-secondary px-4 py-2">
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>


    <!-- FOOTER TEXT -->
    <div class="text-center mt-4">
        <small class="text-muted">
            © {{ date('Y') }} Enterprise Admin System
        </small>
    </div>

</div>

@endsection
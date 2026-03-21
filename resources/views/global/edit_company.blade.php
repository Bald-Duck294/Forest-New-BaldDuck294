@php
$hideGlobalFilters = true;
@endphp

@extends('layouts.app')

@section('content')

<div class="container py-5" style="max-width:1100px">

    <h3 class="fw-bold mb-4">Edit Company</h3>

    <div class="card shadow-sm border-0 rounded-4">

        <div class="card-body p-4 p-md-5">

            <form method="POST" action="{{ route('companies.update',$company->id) }}">
                @csrf


                <div class="row g-4">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Company Name</label>
                        <input type="text"
                            name="name"
                            class="form-control form-control-lg"
                            value="{{ $company->name }}">
                    </div>


                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Company Contact</label>
                        <input type="text"
                            name="contact"
                            class="form-control form-control-lg"
                            value="{{ $company->contact }}">
                    </div>


                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Company Email</label>
                        <input type="email"
                            name="email"
                            class="form-control form-control-lg"
                            value="{{ $company->email }}">
                    </div>


                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Industry Type</label>

                        <select name="type" class="form-select form-select-lg">

                            <option value="forest"
                                @if($company->type=='forest') selected @endif>
                                Forest
                            </option>

                            <option value="zp"
                                @if($company->type=='zp') selected @endif>
                                ZP
                            </option>

                            <option value="org"
                                @if($company->type=='org') selected @endif>
                                Organization
                            </option>

                        </select>

                    </div>



                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Contact Person</label>
                        <input type="text"
                            name="contact_person"
                            class="form-control form-control-lg"
                            value="{{ $company->contact_person }}">
                    </div>


                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Contact Person Phone</label>
                        <input type="text"
                            name="contact_person_contact"
                            class="form-control form-control-lg"
                            value="{{ $company->contact_person_contact }}">
                    </div>



                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Designation</label>
                        <input type="text"
                            name="contact_person_designation"
                            class="form-control form-control-lg"
                            value="{{ $company->contact_person_designation }}">
                    </div>



                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Employee Limit</label>
                        <input type="number"
                            name="empLimit"
                            class="form-control form-control-lg"
                            value="{{ $company->empLimit }}">
                    </div>



                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Start Date</label>
                        <input type="date"
                            name="start_date"
                            class="form-control form-control-lg"
                            value="{{ $company->start_date }}">
                    </div>



                    <div class="col-md-6">
                        <label class="form-label fw-semibold">End Date</label>
                        <input type="date"
                            name="end_date"
                            class="form-control form-control-lg"
                            value="{{ $company->end_date }}">
                    </div>



                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Address</label>
                        <textarea name="address"
                            class="form-control form-control-lg"
                            rows="3">{{ $company->address }}</textarea>
                    </div>



                    <div class="col-md-6">

                        <label class="form-label fw-semibold">Status</label>

                        <select name="isActive" class="form-select form-select-lg">

                            <option value="1"
                                @if($company->isActive==1) selected @endif>
                                Active
                            </option>

                            <option value="0"
                                @if($company->isActive==0) selected @endif>
                                Inactive
                            </option>

                        </select>

                    </div>


                </div>



                <div class="mt-5 pt-4 border-top d-flex gap-3">

                    <button type="submit"
                        class="btn btn-dark px-4 py-2">
                        Update Company
                    </button>


                    <a href="{{ route('global.dashboard') }}"
                        class="btn btn-outline-secondary px-4 py-2">
                        Cancel
                    </a>

                </div>


            </form>

        </div>

    </div>

</div>

@endsection
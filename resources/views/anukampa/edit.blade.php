@extends('layouts.app')
@php
    $hideGlobalFilters = true;
    $hideBackground = true;
@endphp
@section('content')
    <div class="container-fluid py-4">
        <div class="mb-4">
            <h2 class="fw-bold mb-0">Edit Claim #{{ $claim->id }}</h2>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('anukampa.update', $claim->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Victim Name</label>
                            <input type="text" name="victim_name" value="{{ old('victim_name', $claim->victim_name) }}"
                                class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Number</label>
                            <input type="text" name="contact_number"
                                value="{{ old('contact_number', $claim->contact_number) }}" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Estimated Loss (₹)</label>
                            <input type="number" step="0.01" name="estimated_loss"
                                value="{{ old('estimated_loss', $claim->estimated_loss) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Animal Responsible</label>
                            <input type="text" name="animal_responsible"
                                value="{{ old('animal_responsible', $claim->animal_responsible) }}" class="form-control">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" rows="4" class="form-control">{{ old('remarks', $claim->remarks) }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">Save Changes</button>
                        <a href="{{ route('anukampa.claims') }}" class="btn btn-light border">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

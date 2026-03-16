@php
$hideGlobalFilters = true;
$hideBackground = true;
@endphp

@extends('layouts.app')

@section('title','Report Configuration Builder')

@section('content')

<div class="container py-4">

    <h4 class="fw-bold mb-4">
        New Report Configuration
    </h4>

    <form method="POST" action="{{ route('report-configs.store') }}">
        @csrf

        <div class="row g-4">

            {{-- LEFT PANEL --}}
            <div class="col-lg-4">

                <div class="card shadow-sm mb-4">
                    <div class="card-body">

                        <h6 class="fw-bold text-uppercase small mb-3">
                            Base Information
                        </h6>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">
                                Configuration Name
                            </label>

                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                required>
                        </div>


                        <div class="mb-3">
                            <label class="form-label small fw-semibold">
                                Forest Category
                            </label>

                            <select class="form-select" name="category" required>
                                <option value="">Select Category</option>
                                <option value="Harvesting">Harvesting</option>
                                <option value="Planting">Planting</option>
                                <option value="Biodiversity">Biodiversity</option>
                                <option value="Protection">Protection</option>
                            </select>
                        </div>


                        <div class="mb-3">
                            <label class="form-label small fw-semibold">
                                Report Type
                            </label>

                            <select class="form-select" name="report_type" required>
                                <option value="Standard Form">Standard Form</option>
                                <option value="Audit">Audit</option>
                                <option value="Incident">Incident</option>
                            </select>
                        </div>


                        <div class="form-check form-switch">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="is_active"
                                value="1"
                                checked>

                            <label class="form-check-label">
                                Active Status
                            </label>
                        </div>

                    </div>
                </div>


                {{-- FIELD TYPES --}}
                <div class="card shadow-sm">
                    <div class="card-body">

                        <h6 class="fw-bold text-uppercase small mb-3">
                            Add Field
                        </h6>

                        <div class="d-grid gap-2">

                            <button type="button" class="btn btn-outline-secondary" onclick="addField('text')">
                                Text Input
                            </button>

                            <button type="button" class="btn btn-outline-secondary" onclick="addField('number')">
                                Number
                            </button>

                            <button type="button" class="btn btn-outline-secondary" onclick="addField('photo')">
                                Photo
                            </button>

                            <button type="button" class="btn btn-outline-secondary" onclick="addField('location')">
                                Location
                            </button>

                        </div>

                    </div>
                </div>

            </div>



            {{-- RIGHT PANEL --}}
            <div class="col-lg-8">

                <div class="card shadow-sm">

                    <div class="card-header">
                        Report Form Fields
                    </div>

                    <div class="card-body">

                        <div id="fieldsContainer"></div>

                    </div>

                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-success">
                            Save Configuration
                        </button>
                    </div>

                </div>

            </div>

        </div>

    </form>
</div>

@endsection

@push('scripts')
<script>
    let fieldIndex = 0;

    function addField(type) {

        let html = `
<div class="border rounded p-3 mb-3">

<div class="d-flex justify-content-between mb-2">

<strong>${type.toUpperCase()}</strong>

<button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.border').remove()">
Remove
</button>

</div>

<div class="mb-2">
<label class="small text-muted">Label</label>
<input class="form-control"
name="fields[${fieldIndex}][label]"
required>
</div>

<div class="mb-2">
<label class="small text-muted">Key</label>
<input class="form-control"
name="fields[${fieldIndex}][key]"
required>
</div>

<input type="hidden"
name="fields[${fieldIndex}][type]"
value="${type}">

<div class="form-check">
<input type="checkbox"
class="form-check-input"
name="fields[${fieldIndex}][required]"
value="1">

<label class="form-check-label">
Required
</label>
</div>

</div>
`;

        document.getElementById("fieldsContainer").insertAdjacentHTML("beforeend", html);

        fieldIndex++;

    }
</script>
@endpush
<form method="POST" action="/dynamic-labels/company/{{ $companyId }}">

    @csrf

    @foreach ($masters as $field)
        <div class="mb-3">

            <label>{{ $field->field_key }}</label>

            <input type="text" name="labels[{{ $field->field_key }}]"
                value="{{ $companyLabels[$field->field_key] ?? '' }}" placeholder="{{ $field->default_label }}"
                class="form-control">

        </div>
    @endforeach

    <button class="btn btn-primary">
        Save Labels
    </button>

</form>

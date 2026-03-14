@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    $user = session('user');
@endphp
@extends('layouts.app')

@section('title', 'Plantation Workflow')

@section('content')

    <div class="container mt-4">

        {{-- BACK BUTTON --}}
        <a href="{{ route('plantation.dashboard') }}" class="btn btn-outline-secondary btn-sm mb-3">
            ← Back to Dashboard
        </a>

        {{-- FLASH MESSAGE --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- HEADER --}}
        <div class="card mb-4">
            <div class="card-body d-flex justify-content-between align-items-start">

                <div>
                    <h4 class="fw-bold mb-1">{{ $plantation->name }}</h4>
                    <p class="text-muted small mb-0">
                        Code: <strong>{{ $plantation->code }}</strong>
                        @if ($plantation->site)
                            &nbsp;|&nbsp; Site: <strong>{{ $plantation->site->name }}</strong>
                        @endif
                    </p>
                </div>

                <div class="text-end">
                    <div class="text-uppercase small text-muted mb-1">Status</div>
                    <span
                        class="badge
                    @if ($plantation->status == 'completed') bg-success
                    @elseif($plantation->status == 'active') bg-primary
                    @else bg-secondary @endif">
                        {{ ucfirst($plantation->status) }}
                    </span>
                </div>

            </div>
        </div>

        {{-- STEPPER --}}
        <div class="card mb-4">
            <div class="card-body">
                @php
                    $currentIndex = array_search($plantation->current_phase, $phases);
                @endphp

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    @foreach ($phases as $phase)
                        @php $i = $loop->index; @endphp
                        <div class="text-center flex-fill">
                            @if ($i < $currentIndex)
                                <span class="badge bg-success rounded-circle p-2">✓</span>
                            @elseif($i == $currentIndex)
                                <span class="badge bg-primary rounded-circle p-2">{{ $i + 1 }}</span>
                            @else
                                <span class="badge bg-secondary rounded-circle p-2">{{ $i + 1 }}</span>
                            @endif
                            <div
                                class="mt-1 small @if ($i == $currentIndex) fw-bold text-primary @elseif($i < $currentIndex) text-success @else text-muted @endif">
                                {{ ucfirst($phase) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- CONTENT ROW --}}
        <div class="row g-4">

            {{-- LEFT: ACTIVE PHASE FORM --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header fw-bold text-capitalize">
                        Phase: {{ $plantation->current_phase }}
                    </div>
                    <div class="card-body">

                        @if ($plantation->status == 'completed')
                            <div class="alert alert-success mb-0">
                                ✅ This plantation has completed all phases.
                            </div>
                        @else
                            <form method="POST" action="{{ route('plantation.workflow.save', $plantation->id) }}">
                                @csrf

                                {{-- ── IDENTIFICATION ─────────────────────────────── --}}
                                @if ($plantation->current_phase == 'identification')
                                    <p class="text-muted">
                                        Review the plantation details below and confirm to proceed to measurement.
                                    </p>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Plantation Name</label>
                                            <input type="text" class="form-control" value="{{ $plantation->name }}"
                                                disabled>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Code</label>
                                            <input type="text" class="form-control" value="{{ $plantation->code }}"
                                                disabled>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" rows="2" disabled>{{ $plantation->description }}</textarea>
                                        </div>
                                    </div>
                                @endif

                                {{-- ── MEASUREMENT ────────────────────────────────── --}}
                                @if ($plantation->current_phase == 'measurement')
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Total Area (sq m) <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" step="0.01" name="area" class="form-control"
                                                value="{{ $plantation->area }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Soil Type <span class="text-danger">*</span></label>
                                            <input type="text" name="soil_type" class="form-control"
                                                value="{{ $plantation->soil_type }}" required>
                                        </div>
                                    </div>
                                @endif

                                {{-- ── PLANNING ────────────────────────────────────── --}}
                                @if ($plantation->current_phase == 'planning')
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Plant Species <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="plant_species" class="form-control"
                                                value="{{ $plantation->plant_species }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Plant Count <span class="text-danger">*</span></label>
                                            <input type="number" name="plant_count" class="form-control"
                                                value="{{ $plantation->plant_count }}" min="1" required>
                                        </div>
                                    </div>
                                @endif

                                {{-- ── PLANTING ────────────────────────────────────── --}}
                                @if ($plantation->current_phase == 'planting')
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Planting Start Date <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" name="plantation_start_date" class="form-control"
                                                value="{{ $plantation->plantation_start_date ? $plantation->plantation_start_date->format('Y-m-d') : '' }}"
                                                required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Planting End Date</label>
                                            <input type="date" name="plantation_end_date" class="form-control"
                                                value="{{ $plantation->plantation_end_date ? $plantation->plantation_end_date->format('Y-m-d') : '' }}">
                                        </div>
                                    </div>
                                @endif

                                {{-- ── FENCING ─────────────────────────────────────── --}}
                                @if ($plantation->current_phase == 'fencing')
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" name="is_fenced"
                                                    id="is_fenced" value="1"
                                                    {{ $plantation->is_fenced ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold" for="is_fenced">
                                                    Is the plantation fenced?
                                                </label>
                                            </div>
                                            <div class="form-text">Check this if fencing work has been completed.</div>
                                        </div>
                                    </div>
                                @endif

                                {{-- ── OBSERVATION ─────────────────────────────────── --}}
                                @if ($plantation->current_phase == 'observation')
                                    <p class="text-muted small mb-3">
                                        Record a new field observation. Multiple observations can be logged over time.
                                    </p>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Observation Date <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" name="observation_date" class="form-control"
                                                value="{{ date('Y-m-d') }}" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Remarks / Field Findings <span
                                                    class="text-danger">*</span></label>
                                            <textarea name="remarks" class="form-control" rows="4"
                                                placeholder="Describe what was observed in the field..." required></textarea>
                                        </div>
                                    </div>
                                @endif

                                {{-- SUBMIT --}}
                                <div class="d-flex justify-content-end pt-3 border-top mt-3">
                                    <button type="submit" class="btn btn-primary px-5">
                                        @if ($plantation->current_phase == 'observation')
                                            💾 Save Observation
                                        @else
                                            Save &amp; Advance →
                                        @endif
                                    </button>
                                </div>

                            </form>

                        @endif {{-- end status != completed --}}

                    </div>
                </div>
            </div>

            {{-- RIGHT PANEL --}}
            <div class="col-lg-4">

                {{-- PLANTATION SUMMARY --}}
                <div class="card mb-3">
                    <div class="card-header fw-bold">Plantation Summary</div>
                    <div class="card-body small">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted">Area</td>
                                <td>{{ $plantation->area ? $plantation->area . ' sq m' : '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Soil Type</td>
                                <td>{{ $plantation->soil_type ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Species</td>
                                <td>{{ $plantation->plant_species ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Plant Count</td>
                                <td>{{ $plantation->plant_count ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Start Date</td>
                                <td>{{ $plantation->plantation_start_date ? $plantation->plantation_start_date->format('d M Y') : '—' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Fenced</td>
                                <td>
                                    @if ($plantation->is_fenced)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- OBSERVATIONS LOG --}}
                <div class="card mb-3">
                    <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                        <span>Observations</span>
                        <span class="badge bg-secondary">{{ $plantation->observations->count() }}</span>
                    </div>
                    <div class="card-body p-0">
                        @forelse($plantation->observations->sortByDesc('observation_date') as $obs)
                            <div class="px-3 py-2 border-bottom">
                                <div class="d-flex justify-content-between">
                                    <small
                                        class="fw-semibold">{{ \Carbon\Carbon::parse($obs->observation_date)->format('d M Y') }}</small>
                                </div>
                                <p class="small text-muted mb-0">{{ $obs->remarks ?? 'No remarks.' }}</p>
                            </div>
                        @empty
                            <p class="text-muted small p-3 mb-0">No observations yet.</p>
                        @endforelse
                    </div>
                </div>

                {{-- WORKFLOW RULES --}}
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="fw-bold text-primary mb-2">Workflow Rules</h6>
                        <ul class="small text-muted mb-0 ps-3">
                            <li>Phases must be completed in order</li>
                            <li>Completed phase data is read-only</li>
                            <li>Multiple observations can be recorded</li>
                        </ul>
                    </div>
                </div>

            </div>

        </div>

    </div>

@endsection

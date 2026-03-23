@extends('layouts.app')

@section('title', 'Detailed Data View')

@section('content')
    <div class="container-fluid py-4">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--text-main);">Detailed Data Records</h3>
                <p class="mb-0 text-muted" style="font-size: 0.9rem;">View, filter, and export all system records.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-light shadow-sm border">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <ul class="nav nav-pills mb-4" style="border-bottom: 2px solid var(--border-color);">
            <li class="nav-item">
                <a class="nav-link fw-bold px-4 {{ $category == 'criminal' ? 'active bg-danger' : 'text-muted' }}"
                    href="?category=criminal">Criminal Activity</a>
            </li>
            <li class="nav-item">
                <a class="nav-link fw-bold px-4 {{ $category == 'events' ? 'active bg-success' : 'text-muted' }}"
                    href="?category=events">Events & Monitoring</a>
            </li>
            <li class="nav-item">
                <a class="nav-link fw-bold px-4 {{ $category == 'fire' ? 'active bg-warning text-dark' : 'text-muted' }}"
                    href="?category=fire">Fire Incidents</a>
            </li>
            <li class="nav-item">
                <a class="nav-link fw-bold px-4 {{ $category == 'assets' ? 'active bg-primary' : 'text-muted' }}"
                    href="?category=assets">Assets & Tools</a>
            </li>
            <li class="nav-item">
                <a class="nav-link fw-bold px-4 {{ $category == 'plantations' ? 'active bg-success' : 'text-muted' }}"
                    href="?category=plantations">Plantations</a>
            </li>
        </ul>

        <div class="dash-card p-3 mb-4 rounded-3 shadow-sm border-0 bg-white">
            <form method="GET" action="{{ route('reports.detailed') }}" class="row g-3 align-items-end">
                <input type="hidden" name="category" value="{{ $category }}">

                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold">Search ID, Name, or Location</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 bg-light"
                            placeholder="Search..." value="{{ $search }}">
                    </div>
                </div>

                @if (in_array($category, ['criminal', 'events']))
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">Specific Event Type</label>
                        <select name="sub_type" class="form-select bg-light">
                            <option value="">All Types</option>
                            @if ($category == 'criminal')
                                <option value="felling" {{ $subType == 'felling' ? 'selected' : '' }}>Illegal Felling
                                </option>
                                <option value="poaching" {{ $subType == 'poaching' ? 'selected' : '' }}>Poaching</option>
                                <option value="encroachment" {{ $subType == 'encroachment' ? 'selected' : '' }}>
                                    Encroachment</option>
                                <option value="mining" {{ $subType == 'mining' ? 'selected' : '' }}>Mining</option>
                            @elseif($category == 'events')
                                <option value="sighting" {{ $subType == 'sighting' ? 'selected' : '' }}>Animal Sighting
                                </option>
                                <option value="water_status" {{ $subType == 'water_status' ? 'selected' : '' }}>Water
                                    Status</option>
                            @endif
                        </select>
                    </div>
                @endif

                <div class="col-md-2">
                    <label class="form-label text-muted small fw-bold">From Date</label>
                    <input type="date" name="from_date" class="form-control bg-light" value="{{ $fromDate }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted small fw-bold">To Date</label>
                    <input type="date" name="to_date" class="form-control bg-light" value="{{ $toDate }}">
                </div>

                <div class="col-md-1">
                    <button type="submit" class="btn w-100 fw-bold"
                        style="background-color: var(--sapphire-primary); color: white;">Filter</button>
                </div>
            </form>
        </div>

        <div class="dash-card bg-white rounded-3 shadow-sm border-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted" style="font-size: 0.8rem; text-transform: uppercase;">
                        <tr>
                            <th class="ps-4 py-3">ID / Reference</th>
                            @if ($viewType == 'reports')
                                <th>Report Type</th>
                                <th>Beat / Range</th>
                                <th>Date / Time</th>
                                <th class="text-end pe-4">Status</th>
                            @elseif($viewType == 'assets')
                                <th>Category</th>
                                <th>Condition</th>
                                <th>Date Added</th>
                            @elseif($viewType == 'plantations')
                                <th>Plantation Name</th>
                                <th>Species</th>
                                <th>Area (Ha)</th>
                                <th class="text-end pe-4">Phase</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.9rem;">
                        @forelse($records as $row)
                            <tr>
                                @if ($viewType == 'reports')
                                    <td class="ps-4 fw-bold text-dark">{{ $row->report_id ?? 'RPT-' . $row->id }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><i
                                                class="bi bi-tag me-1"></i>{{ $row->report_type }}</span>
                                    </td>
                                    <td class="text-muted">{{ $row->beat ?? ($row->range ?? 'Unknown') }}</td>
                                    <td class="text-muted">
                                        {{ \Carbon\Carbon::parse($row->created_at)->format('d M Y, h:i A') }}</td>
                                    <td class="text-end pe-4">
                                        <span
                                            class="badge {{ $row->status == 'Pending' ? 'bg-warning text-dark' : 'bg-success' }}">{{ $row->status }}</span>
                                    </td>
                                @elseif($viewType == 'assets')
                                    <td class="ps-4 fw-bold text-dark">AST-{{ $row->id }}</td>
                                    <td>{{ $row->category ?? 'Equipment' }}</td>
                                    <td>{{ $row->condition ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y') }}</td>
                                @elseif($viewType == 'plantations')
                                    <td class="ps-4 fw-bold text-dark">{{ $row->code }}</td>
                                    <td class="fw-semibold text-primary">{{ $row->name }}</td>
                                    <td class="text-muted">{{ $row->plant_species ?? 'Mixed' }}</td>
                                    <td class="text-muted">{{ $row->area ?? 0 }} Ha</td>
                                    <td class="text-end pe-4"><span
                                            class="badge bg-info text-dark">{{ ucfirst($row->current_phase) }}</span></td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No records found matching your filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top border-slate-100 d-flex justify-content-center">
                {{ $records->appends(request()->query())->links() }}
            </div>
        </div>

    </div>
@endsection

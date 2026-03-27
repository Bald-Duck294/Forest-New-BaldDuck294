@php
// Expecting $patrols passed from controller (collection of PatrolSession)
//dump($patrols)
//dump($clientId , $org , $beatId , $employeeId , $reportTitle , "report title ")
@endphp

@include('includes.report-header')

<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 p-3 bg-light border rounded shadow-sm">

        <div class="report-meta">
            <h5 class="mb-1 text-primary fw-bold">Patrol Sessions Report</h5>
            <p class="mb-0 text-muted small">
                <i class="la la-building me-1"></i><strong>Organization:</strong> {{ $org ?? 'N/A' }}
                <span class="mx-2">|</span>
                <i class="la la-calendar me-1"></i><strong>Generated On:</strong> {{ date('d M Y') }}
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
            <form method="post" action="{{ route('downloadPatrollingStatusReport') }}" target="_blank" class="d-flex gap-2 mb-0">
                @csrf
                <input type="hidden" name="patrols" value="{{ json_encode($patrols) }}" />
                <input type="hidden" name="client" value="{{ $clientId }}" />
                <input type="hidden" name="geofences" value="{{ $beatId }}" />
                <input type="hidden" name="subType" value="{{ $reportTitle }}" />
                <input type="hidden" name="guard" value="{{ $employeeId }}" />
                <input type="hidden" name="fromDate" value="{{ $startDate }}" />
                <input type="hidden" name="toDate" value="{{ $endDate }}" />

                <button type="submit" class="btn btn-outline-danger btn-sm d-flex align-items-center" name="format" value="pdf">
                    <i class="la la-file-pdf fs-5 me-1"></i> PDF
                </button>
                <button type="submit" class="btn btn-outline-success btn-sm d-flex align-items-center" name="format" value="xlsx">
                    <i class="la la-file-excel fs-5 me-1"></i> Excel
                </button>
            </form>

            <button type="button" class="btn btn-secondary btn-sm d-flex align-items-center" data-bs-dismiss="modal" aria-hidden="true">
                <i class="la la-times fs-5 me-1"></i> Close
            </button>
        </div>
    </div>

    <div class="table-responsive shadow-sm border rounded" style="max-height: 70vh; overflow-y: auto;">
        <table class="table table-hover table-striped table-bordered align-middle mb-0">
            <thead class="bg-dark text-white sticky-top" style="z-index: 1;">
                <tr>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Sr. No.</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">User Name</th>
                    @if($clientId == 'all')
                        <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Range</th>
                    @endif
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Beat Name</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Type</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Session</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Start Time</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">End Time</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Start Location</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">End Location</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Distance</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($patrols as $index => $item)
                    <tr>
                        <td class="text-center fw-bold">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $item->user->name ?? 'No User' }}</td>

                        @if($clientId == 'all')
                            <td class="text-center">
                                {{ $item->site ? $item->site->client_name : 'N/A' }}
                            </td>
                        @endif

                        <td class="text-center">{{ $item->display_site ?? 'No Site' }}</td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $item->type }}</span>
                        </td>
                        <td class="text-center">{{ $item->session }}</td>

                        <td class="text-center text-nowrap">
                            {{ date('d-m-Y h:i A', strtotime($item->started_at)) }}
                        </td>

                        <td class="text-center text-nowrap">
                            @if ($item->ended_at)
                                {{ date('d-m-Y h:i A', strtotime($item->ended_at)) }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td class="text-center text-nowrap">
                            <a href="http://maps.google.com/maps?q={{ $item->start_lat }},{{ $item->start_lng }}"
                               target="_blank" class="text-decoration-none">
                               <i class="la la-map-marker text-danger"></i> {{ $item->start_lat }}, {{ $item->start_lng }}
                            </a>
                        </td>

                        <td class="text-center text-nowrap">
                            @if ($item->end_lat && $item->end_lng)
                                <a href="http://maps.google.com/maps?q={{ $item->end_lat }},{{ $item->end_lng }}"
                                   target="_blank" class="text-decoration-none">
                                   <i class="la la-map-marker text-danger"></i> {{ $item->end_lat }}, {{ $item->end_lng }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td class="text-center fw-bold text-nowrap">
                            {{ $item->distance !== null ? round($item->distance / 1000, 2) . ' km' : '-' }}
                        </td>

                        <td class="text-center">
                            @if($item->ended_at)
                                <span class="badge bg-success px-3 py-2 rounded-pill">Completed</span>
                            @else
                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">Ongoing</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $clientId == 'all' ? 12 : 11 }}" class="text-center py-5 text-muted bg-white">
                            <i class="la la-folder-open fs-1 d-block mb-2 text-secondary"></i>
                            No patrol sessions found for this period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('includes.report-header')

<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 p-3 bg-light border rounded shadow-sm">

        <div class="report-meta">
            <h5 class="mb-1 text-primary fw-bold">Patrolling Summary Report</h5>
            <p class="mb-0 text-muted small">
                <i class="la la-building me-1"></i><strong>Organization:</strong> {{ $companyName ?? 'N/A' }}
                <span class="mx-2">|</span>
                <i class="la la-calendar-alt me-1"></i><strong>Date Range:</strong> {{ $dateRange }}
                <span class="mx-2">|</span>
                <i class="la la-clock me-1"></i><strong>Generated On:</strong> {{ date('d M Y') }}
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
            <form method="POST" action="{{ route('patrollingSummaryDownload') }}" target="_blank" class="d-flex gap-2 mb-0">
                @csrf
                <input type="hidden" name="summary" value="{{ json_encode($summary) }}">
                <input type="hidden" name="dateRange" value="{{ $dateRange }}">

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
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Employee</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Range</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Beat</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Total Sessions</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Completed</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Ongoing</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Total Distance (km)</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Avg Distance (km)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($summary as $s)
                <tr>
                    <td class="text-center fw-medium">{{ $s['guard'] }}</td>
                    <td class="text-center">{{ $s['range'] }}</td>
                    <td class="text-center">{{ $s['beat'] }}</td>
                    <td class="text-center fw-bold">{{ $s['total_sessions'] }}</td>

                    <td class="text-center text-success fw-semibold">{{ $s['completed'] }}</td>
                    <td class="text-center text-warning text-dark fw-semibold">{{ $s['ongoing'] }}</td>

                    <td class="text-center">{{ $s['total_distance'] }}</td>
                    <td class="text-center">{{ $s['avg_distance'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted bg-white">
                        <i class="la la-folder-open fs-1 d-block mb-2 text-secondary"></i>
                        No summary data found for this period.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
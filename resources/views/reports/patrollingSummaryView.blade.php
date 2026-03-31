@include('includes.report-header')

<div class="modal-header border-bottom py-3 px-4 bg-light shadow-sm">
    <div class="w-100 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="report-meta">
            <h5 class="mb-1 text-primary fw-bold">Patrolling Summary Report</h5>
            <div class="d-flex flex-wrap align-items-center gap-2 text-muted small">
                <span><i class="la la-building me-1 text-secondary"></i><strong>Org:</strong> {{ $companyName ?? 'N/A' }}</span>
                <span class="text-light-emphasis">|</span>
                <span><i class="la la-calendar-alt me-1 text-secondary"></i><strong>Range:</strong> {{ $dateRange }}</span>
                <span class="text-light-emphasis">|</span>
                <span><i class="la la-clock me-1 text-secondary"></i><strong>Generated:</strong> {{ date('d M Y') }}</span>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <form method="POST" action="{{ route('patrollingSummaryDownload') }}" class="d-flex gap-2 mb-0">
                @csrf
                <input type="hidden" name="summary" value="{{ json_encode($summary) }}">
                <input type="hidden" name="dateRange" value="{{ $dateRange }}">

                <button type="submit" class="btn btn-outline-danger btn-sm px-3 shadow-sm" name="format" value="pdf">
                    <i class="la la-file-pdf fs-5 me-1"></i> PDF
                </button>
                <button type="submit" class="btn btn-outline-success btn-sm px-3 shadow-sm" name="format" value="xlsx">
                    <i class="la la-file-excel fs-5 me-1"></i> Excel
                </button>
            </form>

            <button type="button" class="btn btn-dark btn-sm px-3 shadow-sm" data-bs-dismiss="modal" aria-label="Close">
                <i class="la la-times fs-5 me-1"></i> Close
            </button>
        </div>
    </div>
</div>

<div class="modal-body p-0">
    <div class="table-responsive" style="max-height: calc(90vh - 150px); overflow-y: auto;">
        <table class="table table-hover table-striped table-bordered align-middle mb-0">
            <thead class="sticky-top shadow-sm" style="z-index: 10;">
                <tr>
                    <th class="text-start px-3 bg-dark text-white fw-bold py-3" style="min-width: 180px;">Employee</th>
                    <th class="text-center bg-dark text-white fw-bold py-3">Range</th>
                    <th class="text-center bg-dark text-white fw-bold py-3">Beat</th>
                    <th class="text-center bg-dark text-white fw-bold py-3">Sessions</th>
                    <th class="text-center bg-dark text-white fw-bold py-3">Completed</th>
                    <th class="text-center bg-dark text-white fw-bold py-3">Ongoing</th>
                    <th class="text-center bg-dark text-white fw-bold py-3">Total Dist (km)</th>
                    <th class="text-center bg-dark text-white fw-bold py-3">Avg Dist (km)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($summary as $s)
                <tr>
                    <td class="text-start fw-bold px-3 text-dark">{{ $s['guard'] }}</td>
                    <td class="text-center text-muted small">{{ $s['range'] ?: '—' }}</td>
                    <td class="text-center text-muted small">{{ $s['beat'] ?: '—' }}</td>
                    <td class="text-center fw-bold text-primary">{{ $s['total_sessions'] }}</td>
                    <td class="text-center">
                        <span class="badge rounded-pill bg-success-subtle text-success px-3">{{ $s['completed'] }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge rounded-pill bg-warning-subtle text-dark px-3">{{ $s['ongoing'] }}</span>
                    </td>
                    <td class="text-center fw-medium">{{ number_format((float)$s['total_distance'], 2) }}</td>
                    <td class="text-center fw-medium">{{ number_format((float)$s['avg_distance'], 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted bg-white">
                        <i class="la la-folder-open fs-1 d-block mb-2 text-secondary opacity-50"></i>
                        No summary data found for the selected period.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
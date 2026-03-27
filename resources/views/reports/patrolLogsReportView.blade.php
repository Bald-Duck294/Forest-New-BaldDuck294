@include('includes.report-header')

<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 p-3 bg-light border rounded shadow-sm">

        <div class="report-meta">
            <h5 class="mb-1 text-primary fw-bold">Patrolling Logs Report</h5>
            <p class="mb-0 text-muted small">
                <i class="la la-building me-1"></i><strong>Organization:</strong> {{ $companyName ?? 'N/A' }}
                <span class="mx-2">|</span>
                <i class="la la-tag me-1"></i><strong>Log Type:</strong> {{ ucfirst(str_replace('_', ' ', $logType)) }}
                <span class="mx-2">|</span>
                <i class="la la-calendar-alt me-1"></i><strong>Date Range:</strong> {{ $dateRange }}
                <span class="mx-2">|</span>
                <i class="la la-clock me-1"></i><strong>Generated On:</strong> {{ date('d M Y') }}
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
            <form method="POST" action="{{ route('patrollingLogsReportDownload') }}" target="_blank" class="d-flex gap-2 mb-0">
                @csrf
                <input type="hidden" name="logs" value="{{ json_encode($logs) }}">
                <input type="hidden" name="companyName" value="{{ $companyName }}">
                <input type="hidden" name="dateRange" value="{{ $dateRange }}">
                <input type="hidden" name="logType" value="{{ $logType }}">

                <button type="submit" name="format" value="pdf" class="btn btn-outline-danger btn-sm d-flex align-items-center">
                    <i class="la la-file-pdf fs-5 me-1"></i> PDF
                </button>
                <button type="submit" name="format" value="xlsx" class="btn btn-outline-success btn-sm d-flex align-items-center">
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
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">#</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Type</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Notes</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Latitude</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Longitude</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Created By</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Range</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Beat</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Created At</th>
                    <th class="text-center text-nowrap text-white" style="background-color: #343a40;">Photos</th>
                    <th class="text-nowrap text-white" style="background-color: #343a40;">Payload</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($logs as $i => $log)
                <tr>
                    <td class="text-center fw-bold">{{ $i + 1 }}</td>
                    <td class="text-center">
                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $log['type'])) }}</span>
                    </td>
                    <td>{{ $log['notes'] ?? '-' }}</td>
                    <td class="text-center">{{ $log['lat'] }}</td>
                    <td class="text-center">{{ $log['lng'] }}</td>
                    <td class="text-center">{{ $log['session']['user']['name'] ?? 'N/A' }}</td>
                    <td class="text-center">{{ $log['session']['site']['client_name'] ?? 'N/A' }}</td>
                    <td class="text-center">{{ $log['session']['site']['name'] ?? 'N/A' }}</td>
                    <td class="text-center text-nowrap">{{ date('d-m-Y h:i A', strtotime($log['created_at'])) }}</td>

                    <td class="text-center">
                        @if(isset($log['media']) && count($log['media']) > 0)
                        <div class="d-flex flex-wrap justify-content-center gap-1">
                            @foreach($log['media'] as $m)
                            <a href="{{ 'https://fms.pugarch.in/public/storage/' . $m['path'] }}" target="_blank" class="badge bg-info text-decoration-none">
                                <i class="la la-image"></i> Photo
                            </a>
                            @endforeach
                        </div>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>

                    <td class="small">
                        @php
                        $payload = $log['payload'] ?? [];
                        @endphp

                        @if(is_array($payload) && count($payload) > 0)
                        @foreach($payload as $key => $value)

                        {{-- Skip created_at --}}
                        @if(in_array($key, ['createdAt', 'created_at']))
                        @continue
                        @endif

                        @php
                        // Make key readable: sightingType → Sighting Type
                        $label = ucfirst(str_replace(['_', '-'], ' ', preg_replace('/([a-z])([A-Z])/', '$1 $2', $key)));

                        // Format value
                        if (is_bool($value)) {
                        $value = $value ? 'Yes' : 'No';
                        } elseif (is_array($value)) {
                        $value = json_encode($value, JSON_PRETTY_PRINT);
                        } elseif ($value === null) {
                        $value = "-";
                        }
                        @endphp

                        <div class="mb-1 text-nowrap">
                            <strong class="text-dark">{{ $label }}:</strong>
                            <span class="text-muted">{{ $value }}</span>
                        </div>

                        @endforeach
                        @else
                        <span class="text-muted text-center d-block">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center py-5 text-muted bg-white">
                        <i class="la la-clipboard-list fs-1 d-block mb-2 text-secondary"></i>
                        No patrolling logs found for this period.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
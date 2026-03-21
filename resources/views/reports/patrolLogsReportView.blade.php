@include('includes.report-header')

<div class="container-fluid">

    <table class="table" style="background:#fcd7a9;">
        <tr>
            <th>Organization</th>
            <th>Log Type</th>
            <th>Date Range</th>
            <th>Generated On</th>
        </tr>
        <tr>
            <td>{{ $companyName }}</td>
            <td>{{ ucfirst(str_replace('_', ' ', $logType)) }}</td>
            <td>{{ $dateRange }}</td>
            <td>{{ date('d M Y') }}</td>
        </tr>
    </table>

    <div class="text-end mb-2">
        <form method="POST" action="{{ route('patrollingLogsReportDownload') }}" target="_blank">
            @csrf
            <input type="hidden" name="logs" value="{{ json_encode($logs) }}">
            <input type="hidden" name="companyName" value="{{ $companyName }}">
            <input type="hidden" name="dateRange" value="{{ $dateRange }}">
            <input type="hidden" name="logType" value="{{ $logType }}">

            <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true"
                style="border: 1px solid grey;padding: 3px 8px;border-radius: 50%;">×</button>
            <button type="submit" name="format" value="pdf" class="btn btn-danger">PDF</button>
            <button type="submit" name="format" value="xlsx" class="btn btn-success">Excel</button>
        </form>
    </div>

    <table class="table table-bordered table-striped">
        <thead style="background:#d97979;color:white;">
            <tr>
                <th>#</th>
                <th>Type</th>
                <th>Notes</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Created By</th>
                <th>Range</th>
                <th>Beat</th>
                <th>Created At</th>
                <th>Photos</th>
                <th>Payload</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($logs as $i => $log)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $log['type'])) }}</td>
                    <td>{{ $log['notes'] ?? '-' }}</td>
                    <td>{{ $log['lat'] }}</td>
                    <td>{{ $log['lng'] }}</td>
                    <td>{{ $log['session']['user']['name'] ?? 'N/A' }}</td>
                    <td>{{ $log['session']['site']['client_name'] ?? 'N/A' }}</td>
                    <td>{{ $log['session']['site']['name'] ?? 'N/A' }}</td>
                    <td>{{ date('d-m-Y h:i A', strtotime($log['created_at'])) }}</td>

                    <td>
                        @if(isset($log['media']))
                            @foreach($log['media'] as $m)
                                <a href="{{ 'https://fms.pugarch.in/public/storage/' . $m['path'] }}" target="_blank">Photo</a><br>
                            @endforeach
                        @else
                            -
                        @endif
                    </td>

                    <td>
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

                                <div><strong>{{ $label }}:</strong> {{ $value }}</div>

                            @endforeach
                        @else
                            -
                        @endif

                    </td>

                </tr>
            @endforeach
        </tbody>

    </table>
</div>
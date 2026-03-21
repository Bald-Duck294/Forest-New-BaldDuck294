<!DOCTYPE html>
<html>

<head>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 4px;
        }

        th {
            background: #d97979;
            color: white;
        }

        .header-table th {
            background: #fcd7a9 !important;
            color: black;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <table class="header-table">
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

    <br>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Type</th>
                <th>Notes</th>
                <th>Location</th>
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
                    <td>
                        @if($log['lat'] && $log['lng'])
                            <a href="https://maps.google.com/?q={{ $log['lat'] }},{{ $log['lng'] }}" target="_blank">
                                Open Location
                            </a>
                        @else
                            -
                        @endif
                    </td>
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
                        @php $payload = $log['payload'] ?? []; @endphp

                        @if(is_array($payload) && count($payload) > 0)
                            @foreach($payload as $key => $value)

                                @if(in_array($key, ['createdAt', 'created_at']))
                                    @continue
                                @endif

                                @php
                                    $label = ucfirst(str_replace(
                                        ['_', '-'],
                                        ' ',
                                        preg_replace('/([a-z])([A-Z])/', '$1 $2', $key)
                                    ));

                                    if (is_bool($value)) {
                                        $value = $value ? 'Yes' : 'No';
                                    } elseif (is_array($value)) {
                                        $value = json_encode($value);
                                    } elseif ($value === null) {
                                        $value = '-';
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

</body>

</html>
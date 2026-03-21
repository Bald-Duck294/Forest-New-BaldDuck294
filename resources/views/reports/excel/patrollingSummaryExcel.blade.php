<table>
    <tr>
        <th colspan="2">Organization</th>
        <th colspan="2">Date Range</th>
        <th colspan="2">Generated On</th>
    </tr>
    <tr>
        <td colspan="2">{{ $companyName }}</td>
        <td colspan="2">{{ $dateRange }}</td>
        <td colspan="2">{{ date('d M Y') }}</td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th>Employee</th>
            <th>Total Sessions</th>
            <th>Completed</th>
            <th>Ongoing</th>
            <th>Total Distance (km)</th>
            <th>Avg Distance (km)</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($summary as $s)
            <tr>
                <td> {{ $s['guard'] }}</td>
                <td>{{ $s['total_sessions'] === 0 || $s['total_sessions'] === "0" || $s['total_sessions'] === null || $s['total_sessions'] ? $s['total_sessions'] : '0' }}
                </td>

                <td>{{ $s['completed'] === 0 || $s['completed'] === "0" || $s['completed'] === null || $s['completed'] ? $s['completed'] : '0' }}
                </td>

                <td>{{ $s['ongoing'] === 0 || $s['ongoing'] === "0" || $s['ongoing'] === null || $s['ongoing'] ? $s['ongoing'] : '0' }}
                </td>

                <td>{{ $s['total_distance'] === 0 || $s['total_distance'] === "0" || $s['total_distance'] === null || $s['total_distance'] ? $s['total_distance'] : '0' }}
                </td>

                <td>{{ $s['avg_distance'] === 0 || $s['avg_distance'] === "0" || $s['avg_distance'] ? $s['avg_distance'] : '0' }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
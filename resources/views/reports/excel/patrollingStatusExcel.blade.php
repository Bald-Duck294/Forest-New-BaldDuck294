<table>
    <tr>
        <th colspan="2">Organization</th>
        <th colspan="2">Date Range</th>
        <th colspan="2">Report Type</th>
        <th colspan="2">Generated On</th>
    </tr>
    <tr>
        <td colspan="2">{{ $companyName }}</td>
        <td colspan="2">{{ $dateRange }}</td>
        <td colspan="2">{{ str_replace('_', ' ', $subType) }}</td>
        <td colspan="2">{{ date('d M Y') }}</td>
    </tr>

    <tr>
        <th>Sr. No</th>
        <th>User Name</th>
        <th>Range</th>
        <th>Beat Name</th>
        <th>Start Time</th>
        <th>End Time</th>
        <th>Start Location</th>
        <th>End Location</th>
        <th>Distance</th>
        <th>Status</th>
    </tr>

    @foreach ($data as $index => $patrol)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $patrol->user->name ?? 'N/A' }}</td>
            <td>{{ $patrol->site->client_name ?? 'N/A' }}</td>
            <td>{{ $patrol->display_site ?? 'N/A' }}</td>

            <td>
                {{ $patrol->started_at ? date('d-m-Y h:i a', strtotime($patrol->started_at)) : 'N/A' }}
            </td>

            <td>
                {{ $patrol->ended_at ? date('d-m-Y h:i a', strtotime($patrol->ended_at)) : 'Ongoing' }}
            </td>

            <td>
                {{ $patrol->start_lat }}, {{ $patrol->start_lng }}
            </td>

            <td>
                @if ($patrol->end_lat && $patrol->end_lng)
                    {{ $patrol->end_lat }}, {{ $patrol->end_lng }}
                @else
                    -
                @endif
            </td>

            <td>
                {{ $patrol->distance !== null ? round($patrol->distance / 1000, 2) . ' km' : '-' }}
            </td>

            <td>{{ $patrol->ended_at ? 'Completed' : 'Ongoing' }}</td>
        </tr>
    @endforeach
</table>
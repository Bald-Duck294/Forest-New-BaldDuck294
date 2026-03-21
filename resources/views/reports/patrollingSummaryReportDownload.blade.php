<table style="width:100%;border-collapse:collapse;">
    <tr style="background:#fcd7a9;">
        <th style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black;">Organization</th>
        <th style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black;">Date Range</th>
        <th style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black;">Generated On</th>
    </tr>
    <tr>
        <td style="text-align: center; padding:5px;border: 1px solid black;">{{ $companyName }}</td>
        <td style="text-align: center; padding:5px;border: 1px solid black;">{{ $dateRange }}</td>
        <td style="text-align: center; padding:5px;border: 1px solid black;">{{ date('d M Y') }}</td>
    </tr>
</table>

<br>

<table style="width:100%;border-collapse:collapse;">
    <thead style="background:#d97979;color:white;">
        <tr>
            <th style="border: 1px solid black;">Guard</th>
            <th style="border: 1px solid black;">Total Sessions</th>
            <th style="border: 1px solid black;">Completed</th>
            <th style="border: 1px solid black;">Ongoing</th>
            <th style="border: 1px solid black;">Total Distance (km)</th>
            <th style="border: 1px solid black;">Avg Distance (km)</th>
        </tr>
    </thead>

    <tbody>
        @foreach($summary as $s)
            <tr>
                <td style="border: 1px solid black;">{{ $s['guard'] }}</td>
                <td style="border: 1px solid black;">{{ $s['total_sessions'] }}</td>
                <td style="border: 1px solid black;">{{ $s['completed'] }}</td>
                <td style="border: 1px solid black;">{{ $s['ongoing'] }}</td>
                <td style="border: 1px solid black;">{{ $s['total_distance'] }}</td>
                <td style="border: 1px solid black;">{{ $s['avg_distance'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
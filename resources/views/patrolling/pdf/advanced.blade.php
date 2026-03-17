<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Patrol Analytics</title>
</head>

<body>
    <h2>Patrol Analytics ({{ $dateFrom }} → {{ $dateTo }})</h2>
    <table border="1" cellpadding="6" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Site</th>
                <th>Start</th>
                <th>End</th>
                <th>Distance (km)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sessions as $s)
                <tr>
                    <td>{{ $s->id }}</td>
                    <td>{{ optional($s->user)->name }}</td>
                    <td>{{ optional($s->site)->name }}</td>
                    <td>{{ $s->started_at }}</td>
                    <td>{{ $s->ended_at }}</td>
                    <td>{{ number_format(($s->distance_m ?? 0) / 1000, 3) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

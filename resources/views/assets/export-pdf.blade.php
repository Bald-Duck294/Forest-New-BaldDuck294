<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #1e293b;
            font-size: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 10px;
        }

        .filters {
            margin-bottom: 20px;
            background: #f8fafc;
            padding: 10px 15px;
            border-left: 4px solid #3b82f6;
            border-radius: 4px;
        }

        .filters p {
            margin: 4px 0;
            font-size: 11px;
            color: #475569;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            text-align: left;
            word-wrap: break-word;
        }

        th {
            background: #f1f5f9;
            color: #475569;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }

        td {
            font-size: 11px;
            vertical-align: top;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            background: #e2e8f0;
            display: inline-block;
        }

        a {
            color: #3b82f6;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <h2>{{ $companyName ?? 'Forest Department' }} - Asset Report</h2>

    @if (isset($filters) && count($filters) > 0)
        <div class="filters">
            <strong style="display:block; margin-bottom:5px; color:#1e293b;">Applied Filters:</strong>
            @foreach ($filters as $key => $val)
                <p><strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> {{ $val }}</p>
            @endforeach
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">Name</th>
                <th style="width: 15%;">Category</th>
                <th style="width: 10%;">Condition</th>
                <th style="width: 10%;">Year</th>
                <th style="width: 25%;">Description</th>
                <th style="width: 10%;">Date Added</th>
                <th style="width: 10%;">Location</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assets as $index => $asset)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="font-weight: bold;">{{ $asset->name }}</td>
                    <td>{{ $asset->category ?? 'N/A' }}</td>
                    <td><span class="badge">{{ $asset->condition ?? 'N/A' }}</span></td>
                    <td>{{ $asset->year ?? 'N/A' }}</td>
                    <td>{{ $asset->description ?? 'No description provided.' }}</td>
                    <td>{{ $asset->created_at->format('d M Y') }}</td>
                    <td>
                        @if ($asset->location && !empty($asset->location['lat']) && !empty($asset->location['lng']))
                            <a href="http://maps.google.com/maps?q={{ $asset->location['lat'] }},{{ $asset->location['lng'] }}"
                                target="_blank">View Map</a>
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; font-style: italic;">No assets found for the selected
                        criteria.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>

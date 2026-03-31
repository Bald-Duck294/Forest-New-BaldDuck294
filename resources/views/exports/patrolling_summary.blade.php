<table>
    <thead>
        <tr>
            <th colspan="8" style="font-size: 16px; font-weight: bold; text-align: center; background-color: #0d6efd; color: #ffffff; height: 40px; vertical-align: middle;">
                Patrolling Summary Report
            </th>
        </tr>

        <tr>
            <th colspan="4" style="font-weight: bold; background-color: #f8f9fa; text-align: left; height: 30px; vertical-align: middle;">
                Organization: <span style="font-weight: normal; color: #333333;">{{ $companyName ?? 'N/A' }}</span>
            </th>
            <th colspan="4" style="font-weight: bold; background-color: #f8f9fa; text-align: right; height: 30px; vertical-align: middle;">
                Date Range: <span style="font-weight: normal; color: #333333;">{{ $dateRange }}</span>
            </th>
        </tr>

        <tr>
            <th colspan="8"></th>
        </tr>

        <tr>
            <th style="font-weight: bold; text-align: center; background-color: #343a40; color: #ffffff; border: 1px solid #000000; vertical-align: middle;">Employee</th>
            <th style="font-weight: bold; text-align: center; background-color: #343a40; color: #ffffff; border: 1px solid #000000; vertical-align: middle;">Range</th>
            <th style="font-weight: bold; text-align: center; background-color: #343a40; color: #ffffff; border: 1px solid #000000; vertical-align: middle;">Beat</th>

            <th style="font-weight: bold; text-align: center; background-color: #343a40; color: #ffffff; border: 1px solid #000000; vertical-align: middle;">Total Sessions</th>

            <th style="font-weight: bold; text-align: center; background-color: #198754; color: #ffffff; border: 1px solid #000000; vertical-align: middle;">Completed</th>
            <th style="font-weight: bold; text-align: center; background-color: #ffc107; color: #000000; border: 1px solid #000000; vertical-align: middle;">Ongoing</th>

            <th style="font-weight: bold; text-align: center; background-color: #343a40; color: #ffffff; border: 1px solid #000000; vertical-align: middle;">Total Distance (km)</th>
            <th style="font-weight: bold; text-align: center; background-color: #343a40; color: #ffffff; border: 1px solid #000000; vertical-align: middle;">Avg Distance (km)</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($summary as $s)
        <tr>
            <td style="border: 1px solid #dddddd; text-align: left; vertical-align: middle;">{{ $s['guard'] ?? 'N/A' }}</td>
            <td style="border: 1px solid #dddddd; text-align: center; vertical-align: middle;">{{ $s['range'] ?? 'N/A' }}</td>
            <td style="border: 1px solid #dddddd; text-align: center; vertical-align: middle;">{{ $s['beat'] ?? 'N/A' }}</td>

            <td style="border: 1px solid #dddddd; text-align: center; vertical-align: middle; font-weight: bold;">{{ $s['total_sessions'] ?? 0 }}</td>

            <td style="border: 1px solid #dddddd; text-align: center; vertical-align: middle; color: #198754; font-weight: bold;">{{ $s['completed'] ?? 0 }}</td>
            <td style="border: 1px solid #dddddd; text-align: center; vertical-align: middle; color: #d39e00; font-weight: bold;">{{ $s['ongoing'] ?? 0 }}</td>

            <td style="border: 1px solid #dddddd; text-align: center; vertical-align: middle;">{{ $s['total_distance'] ?? 0 }}</td>
            <td style="border: 1px solid #dddddd; text-align: center; vertical-align: middle;">{{ $s['avg_distance'] ?? 0 }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="8" style="text-align: center; border: 1px solid #dddddd; color: #6c757d; font-style: italic; height: 40px; vertical-align: middle;">
                No summary data found for this period.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
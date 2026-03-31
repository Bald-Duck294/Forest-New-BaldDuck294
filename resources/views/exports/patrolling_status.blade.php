@php
// Safely catch the data regardless of what the Export class names the variable
$records = $data ?? $patrols ?? [];
@endphp

<table style="border-collapse: collapse;">
    <thead>
        <tr>
            <th colspan="12" style="background-color: #1F4E78; color: #FFFFFF; font-weight: bold; text-align: center; font-size: 18px; height: 40px; vertical-align: middle; border: 1px solid #000000;">
                Patrol Sessions Report - {{ $companyName ?? 'Organization' }}
            </th>
        </tr>

        <tr>
            <th colspan="12" style="background-color: #D9E1F2; color: #000000; text-align: center; font-style: italic; font-size: 12px; height: 25px; vertical-align: middle; border: 1px solid #000000;">
                Generated For: {{ $reportMonth ?? $dateRange ?? 'Selected Period' }}
            </th>
        </tr>

        <tr>
            <th colspan="12"></th>
        </tr>

        <tr>
            <th style="background-color: #4472C4; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000; width: 10px; height: 30px; vertical-align: middle;">Sr. No.</th>
            <th style="background-color: #4472C4; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000; width: 25px; vertical-align: middle;">User Name</th>
            <th style="background-color: #4472C4; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000; width: 25px; vertical-align: middle;">Range (Client)</th>
            <th style="background-color: #4472C4; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000; width: 25px; vertical-align: middle;">Beat Name (Site)</th>
            <th style="background-color: #4472C4; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000; width: 15px; vertical-align: middle;">Type</th>
            <th style="background-color: #4472C4; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000; width: 15px; vertical-align: middle;">Session</th>
            <th style="background-color: #4472C4; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000; width: 20px; vertical-align: middle;">Start Time</th>
            <th style="background-color: #4472C4; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000; width: 20px; vertical-align: middle;">End Time</th>
            <th style="background-color: #4472C4; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000; width: 35px; vertical-align: middle;">Start Location</th>
            <th style="background-color: #4472C4; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000; width: 35px; vertical-align: middle;">End Location</th>
            <th style="background-color: #4472C4; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000; width: 15px; vertical-align: middle;">Distance</th>
            <th style="background-color: #4472C4; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #000000; width: 15px; vertical-align: middle;">Status</th>
        </tr>
    </thead>
    <tbody>
        @if(is_iterable($records) && count($records) > 0)
        @foreach($records as $index => $patrol)
        <tr>
            <td style="text-align: center; border: 1px solid #000000; vertical-align: top;">{{ $index + 1 }}</td>

            <td style="border: 1px solid #000000; vertical-align: top;">{{ data_get($patrol, 'user.name') ?? data_get($patrol, 'user_name') ?? 'N/A' }}</td>
            <td style="border: 1px solid #000000; vertical-align: top;">{{ data_get($patrol, 'site.client.name') ?? data_get($patrol, 'client_name') ?? 'N/A' }}</td>
            <td style="border: 1px solid #000000; vertical-align: top;">{{ data_get($patrol, 'site.name') ?? data_get($patrol, 'site_name') ?? 'N/A' }}</td>

            <td style="text-align: center; border: 1px solid #000000; vertical-align: top;">{{ data_get($patrol, 'type', 'N/A') }}</td>
            <td style="text-align: center; border: 1px solid #000000; vertical-align: top;">{{ data_get($patrol, 'session', 'N/A') }}</td>

            <td style="text-align: center; border: 1px solid #000000; vertical-align: top;">
                @php $start = data_get($patrol, 'started_at'); @endphp
                {{ $start ? \Carbon\Carbon::parse($start)->format('d-m-Y H:i:s') : 'N/A' }}
            </td>
            <td style="text-align: center; border: 1px solid #000000; vertical-align: top;">
                @php $end = data_get($patrol, 'ended_at'); @endphp
                {{ $end ? \Carbon\Carbon::parse($end)->format('d-m-Y H:i:s') : 'N/A' }}
            </td>

            <td style="border: 1px solid #000000; vertical-align: top;">
                {{ data_get($patrol, 'start_lat') }}{{ data_get($patrol, 'start_lat') && data_get($patrol, 'start_lng') ? ', ' : '' }}{{ data_get($patrol, 'start_lng') }}
            </td>
            <td style="border: 1px solid #000000; vertical-align: top;">
                {{ data_get($patrol, 'end_lat') }}{{ data_get($patrol, 'end_lat') && data_get($patrol, 'end_lng') ? ', ' : '' }}{{ data_get($patrol, 'end_lng') }}
            </td>

            <td style="text-align: center; border: 1px solid #000000; vertical-align: top;">{{ data_get($patrol, 'distance', '0') }} km</td>

            <td style="text-align: center; border: 1px solid #000000; vertical-align: top; font-weight: bold; color: {{ strtolower(data_get($patrol, 'status', '')) == 'completed' ? '#008000' : '#FF0000' }};">
                {{ data_get($patrol, 'status', 'Completed') }}
            </td>
        </tr>
        @endforeach
        @else
        <tr>
            <td colspan="12" style="text-align: center; font-weight: bold; border: 1px solid #000000; height: 30px; vertical-align: middle;">No patrol sessions found for this period.</td>
        </tr>
        @endif
    </tbody>
</table>
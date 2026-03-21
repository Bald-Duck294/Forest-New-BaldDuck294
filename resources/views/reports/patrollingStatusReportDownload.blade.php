@php
    //dd('in download')
    //dd( $companyName , $clientId , "client id");

@endphp

<table style="border-collapse:collapse;width:100%;justify-content:center;">
    <tbody>
        <tr style="background-color: #fcd7a9">
            <th colspan="2" style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black;">
                Organization
            </th>
            <th colspan="2" style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black;">
                Date Range
            </th>
            <th colspan="2" style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black;">
                Report Type
            </th>
            <th colspan="2" style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black;">
                Generated On
            </th>
        </tr>
        <tr style="background-color: #fcd7a9">
            <td colspan="2" style="text-align: center; padding:5px;border: 1px solid black;">
                {{ $companyName }}
            </td>
            <td colspan="2" style="text-align: center; padding:5px;border: 1px solid black;">
                {{ $dateRange }}
            </td>
            <td colspan="2" style="text-align: center; padding:5px;border: 1px solid black;">
                {{ str_replace('_', ' ', $subType) }}
            </td>
            <td colspan="2" style="text-align: center; padding:5px;border: 1px solid black;">
                {{ date('d M Y') }}
            </td>
        </tr>

        <tr style="background-color: #d97979;">
            <th style="border: 1px solid black;">Sr. No</th>
            <th style="border: 1px solid black;">User Name</th>

            <th style="border: 1px solid black;">Range</th>
            <th style="border: 1px solid black;">Beat Name </th>
            <th style="border: 1px solid black;">Start Time</th>
            <th style="border: 1px solid black;">End Time</th>
            <th style="text-align: center; white-space: nowrap;">Start Location</th>
            <th style="text-align: center; white-space: nowrap;">End Location</th>
            <th style="text-align: center; white-space: nowrap;">Distance</th>
            <th style="border: 1px solid black;">Status</th>
        </tr>

        @foreach ($data as $index => $patrol)
            <tr>
                <td style="border: 1px solid black;text-align:center;">{{ $index + 1 }}</td>
                <td style="border: 1px solid black;">{{ $patrol->user->name ?? 'N/A' }}</td>
                <td style="border: 1px solid black;">{{ ($patrol->site !== null) ? $patrol->site->client_name : 'N/A' }}
                </td>
                <td style="border: 1px solid black;">{{ $patrol->display_site ?? 'N/A' }}</td>
                <td style="border: 1px solid black;">
                    {{ $patrol->started_at ? date('d-m-Y h:i a', strtotime($patrol->started_at)) : 'N/A' }}
                </td>
                <td style="border: 1px solid black;">
                    {{ $patrol->ended_at ? date('d-m-Y h:i a', strtotime($patrol->ended_at)) : 'Ongoing' }}
                </td>
                <td style="border: 1px solid black;">
                    <a href="https://maps.google.com/?q={{ $patrol->start_lat }},{{ $patrol->start_lng }}" target="_blank">
                        {{ $patrol->start_lat }}, {{ $patrol->start_lng }}
                    </a>
                </td>

                <td style="border: 1px solid black;">
                    @if ($patrol->end_lat && $patrol->end_lng)
                        <a href="https://maps.google.com/?q={{ $patrol->end_lat }},{{ $patrol->end_lng }}" target="_blank">
                            {{ $patrol->end_lat }}, {{ $patrol->end_lng }}
                        </a>
                    @else
                        -
                    @endif
                </td>
                <td style="border: 1px solid black;">
                    {{ $patrol->distance !== null ? round($patrol->distance / 1000, 2) . ' km' : '-' }}
                </td>
                <td style="border: 1px solid black;">
                    {{ $patrol->ended_at ? 'Completed' : 'Ongoing' }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@php

    $user = session('user');

@endphp

<!-- Header Table -->
<table style="border-collapse: collapse; width: 100%; border: 1px solid black;">
    <tr>
        <th colspan="3" style="text-align: center; background-color:#fcd7a9; font-weight:bold; padding:5px; border: 1px solid black;">
            Organization
        </th>
        @if( $user->role_id != '2')
            <th colspan="2" style="text-align: center; background-color:#fcd7a9; font-weight:bold; padding:5px; border: 1px solid black;">
                Client Name
            </th>
        @endif

            <th colspan="3" style="text-align: center; background-color:#fcd7a9; font-weight:bold; padding:5px; border: 1px solid black;">
                Site Name
            </th>

        <th colspan="2" style="text-align: center; background-color:#fcd7a9; font-weight:bold; padding:5px; border: 1px solid black;">
            Date Range
        </th>
        <th colspan="1" style="text-align: center; background-color:#fcd7a9; font-weight:bold; padding:5px; border: 1px solid black;">
            Report Type
        </th>
        <th colspan="1" style="text-align: center; background-color:#fcd7a9; font-weight:bold; padding:5px; border: 1px solid black;">
            Generated On
        </th>
    </tr>
    <tr>
        <td colspan="3" style="text-align: center; background-color:#fcd7a9; padding:5px; border: 1px solid black;">
            {{ $companyName }}
        </td>
        @if( $user->role_id != '2')
            <td colspan="2" style="text-align: center; background-color:#fcd7a9; padding:5px; border: 1px solid black;">
                {{ $clientName }}
            </td>
        @endif
            <td colspan="3" style="text-align: center; background-color:#fcd7a9; padding:5px; border: 1px solid black;">
                {{ $siteName }}
            </td>
        <td colspan="2" style="text-align: center; background-color:#fcd7a9; padding:5px; border: 1px solid black;">
            {{ $dateRange }}
        </td>
        <td colspan="1" style="text-align: center; background-color:#fcd7a9; padding:5px; border: 1px solid black;">
            @if(isset($subType)) {{$subType}} @else N/A @endif
        </td>
        <td colspan="1" style="text-align: center; background-color:#fcd7a9; padding:5px; border: 1px solid black;">
            {{ $generatedOn }}
        </td>
    </tr>
</table>

<!-- Data Table -->
<table id="empTable" class="table empDataTable" style="border-collapse: collapse; width: 100%; page-break-inside: auto; margin-top: 20px;">
    <thead>
        <tr>
            <th style="background-color: #d97979; border:1px solid black; font-weight: bold; padding: 8px;">Sr No</th>
            <th style="background-color: #d97979; border:1px solid black; font-weight: bold; padding: 8px;">Employee Name</th>
            <th style="background-color: #d97979; border:1px solid black; font-weight: bold; padding: 8px;">Role</th>
            @if($client == 'all')
                <th style="background-color: #d97979; border:1px solid black; font-weight: bold; padding: 8px;">Client / Range</th>
            @endif
            <th style="background-color: #d97979; border:1px solid black; font-weight: bold; padding: 8px;">Site / Beat</th>
            <th style="background-color: #d97979; border:1px solid black; font-weight: bold; padding: 8px;">Date</th>
            <th style="background-color: #d97979; border:1px solid black; font-weight: bold; padding: 8px;">Time</th>
            <th style="background-color: #d97979; border:1px solid black; font-weight: bold; padding: 8px;">Late By</th>
        </tr>
    </thead>
    <tbody>
        @php $srNo = 0; @endphp
        @foreach ($data as $var)
            <tr>
                <td style="border:1px solid black; padding: 8px; text-align: center;">{{ ++$srNo }}</td>
                <td style="border:1px solid black; padding: 8px;">{{ $var['name'] }}</td>
                <td style="border:1px solid black; padding: 8px;">
                    {{ $var->role_id == 2 ? 'Supervisor' : 'Employee' }}
                </td>
                @if($client == 'all')
                    <td style="border:1px solid black; padding: 8px; text-align: center;">{{ $var->client_name }}</td>
                @endif
                <td style="border:1px solid black; padding: 8px;">{{ $var->site_name }}</td>
                <td style="border:1px solid black; padding: 8px;">{{ $var->date }}</td>
                <td style="border:1px solid black; padding: 8px;">{{ $var->entry_time }}</td>
                <td style="border:1px solid black; padding: 8px;">{{ gmdate('H:i:s', $var->lateTime * 60) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
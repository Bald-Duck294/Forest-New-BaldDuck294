@php
    // dump($siteName , "name");
    $user = session('user');
@endphp
<table style="border-collapse:collapse;width:100%;border: 1px solid black;">
    <tbody>

        <tr>
            <th colspan="9" style="text-align: center;background-color: #fcd7a9;font-weight:bold;padding:15px;border: 1px solid black; font-size: 22px">
                On-Site Attendance Report
            </th>
        </tr>

        <tr>
            <th colspan="2" style="text-align:center; background-color:#fcd7a9;font-weight:bold;padding:5px;border:1px solid black;">
                Organization
            </th>
            @if($user->role_id != 2)
            <th colspan="2" style="text-align:center; background-color:#fcd7a9;font-weight:bold;padding:5px;border:1px solid black;">
                Client /Range
            </th>
            @endif
            <th colspan="2" style="text-align:center; background-color:#fcd7a9;font-weight:bold;padding:5px;border:1px solid black;">
                Site /Beat
            </th>
            <th colspan="2" style="text-align:center; background-color:#fcd7a9;font-weight:bold;padding:5px;border:1px solid black;">
                Date Range
            </th>
            <th style="text-align:center; background-color:#fcd7a9;font-weight:bold;padding:5px;border:1px solid black;" colspan="<?= ($user->role_id == 2)?3.5:0 ?>">
                Generated On
            </th>
        </tr>


        <tr>
            <td colspan="2" style="text-align:center;background-color:#fcd7a9;padding:5px;border:1px solid black;">
                {{ $companyName }}
            </td>
            @if($user->role_id != 2)
            <td colspan="2" style="text-align:center;background-color:#fcd7a9;padding:5px;border:1px solid black;">
                {{ $clientName }}
            </td>
            @endif
            <td colspan="2" style="text-align:center;background-color:#fcd7a9;padding:5px;border:1px solid black;">
                {{ $siteName }}
            </td>
            <td colspan="2" style="text-align:center;background-color:#fcd7a9;padding:5px;border:1px solid black;">
                {{ $dateRange }}
            </td>
            <td style="text-align:center;background-color:#fcd7a9;padding:5px;border:1px solid black;" colspan="<?= ($user->role_id == 2)?3.5:0 ?>">
                {{ $generatedOn }}
            </td>
        </tr>

        <tr><td colspan="21" style="padding: 10px;"></td></tr>

        <tr>
            <th style="background-color: #d97979;border:1px solid black;padding:5px;text-align:center;font-weight:bold;">
                Sr. No.
            </th>
            <th style="background-color: #d97979;border:1px solid black;padding:5px;text-align:center;font-weight:bold;">
                Date
            </th>
            <th style="background-color: #d97979;border:1px solid black;padding:5px;text-align:center;font-weight:bold;">
                Name of Employee
            </th>
            @if ($siteName)
            <th style="background-color: #d97979;border:1px solid black;padding:5px;text-align:center;font-weight:bold;">
                Site / Beat
            </th>
            @endif
            <th style="background-color: #d97979;border:1px solid black;padding:5px;text-align:center;font-weight:bold;">
                Location
            </th>
            <th style="background-color: #d97979;border:1px solid black;padding:5px;text-align:center;font-weight:bold;">
                Punch-In Time
            </th>
            <th style="background-color: #d97979;border:1px solid black;padding:5px;text-align:center;font-weight:bold;">
                Punch-Out Time
            </th>
            <th style="background-color: #d97979;border:1px solid black;padding:5px;text-align:center;font-weight:bold;">
                Total Time
            </th>
            <th style="background-color: #d97979;border:1px solid black;padding:5px;text-align:center;font-weight:bold;">
                Approved By
            </th>
        </tr>

        
        @foreach ($data as $item)
        <tr>
            <td style="border:1px solid black;text-align:center;">{{ $loop->iteration }}</td>
            <td style="border:1px solid black;text-align:center;">{{ $item->date }}</td>
            <td style="border:1px solid black;">{{ $item->name }}</td>
            @if ($siteName)
            <td style="border:1px solid black;">{{ $item->site_name }}</td>
            @endif
            <td style="border:1px solid black;text-align:center;">
                @if ($item->location && json_decode($item->location)->lat && json_decode($item->location)->lng)
                <a href="https://maps.google.com/?q={{ json_decode($item->location)->lat }},{{ json_decode($item->location)->lng }}" target="_blank">
                    Location
                </a>
                @else
                N/A
                @endif
            </td>
            <td style="border:1px solid black;text-align:center;">{{ $item->entry_time }}</td>
            <td style="border:1px solid black;text-align:center;">{{ $item->exit_time }}</td>
            <td style="border:1px solid black;text-align:center;">{{ $item->time_difference }}</td>
            <td style="border:1px solid black;text-align:center;">{{ $item->approvedBy }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
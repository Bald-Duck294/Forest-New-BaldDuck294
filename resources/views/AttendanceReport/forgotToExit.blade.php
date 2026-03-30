@php
// dd($clientName , $site);
$user = session('user');
@endphp

<table style="border-collapse: collapse; width: 100%; margin: 0 auto;">
    <tbody>
        <tr>
            <th style="background-color: #fcd7a9; text-align: center; border: 1px solid black; padding: 5px; font-weight: bold;">Organization</th>
            @if($user->role_id != 2)
            <th style="background-color: #fcd7a9; text-align: center; border: 1px solid black; padding: 5px; font-weight: bold;">Client / Range</th>
            @endif
            <th style="background-color: #fcd7a9; text-align: center; border: 1px solid black; padding: 5px; font-weight: bold;">Site / Beat</th>
            <th style="background-color: #fcd7a9; text-align: center; border: 1px solid black; padding: 5px; font-weight: bold;">Date Range</th>
            <th style="background-color: #fcd7a9; text-align: center; border: 1px solid black; padding: 5px; font-weight: bold;">Report type</th>
            <th style="background-color: #fcd7a9; text-align: center; border: 1px solid black; padding: 5px; font-weight: bold;">Generated On</th>
        </tr>
        <tr>
            <td style="background-color: #fcd7a9; border: 1px solid black; text-align: center; padding: 5px;">{{ $companyName }}</td>
            @if($user->role_id != 2)
            <td style="background-color: #fcd7a9; border: 1px solid black; text-align: center; padding: 5px;">{{$clientName}}</td>
            @endif
            <td style="background-color: #fcd7a9; border: 1px solid black; text-align: center; padding: 5px;">{{$site}}</td>
            <td style="background-color: #fcd7a9; border: 1px solid black; text-align: center; padding: 5px;">{{$dateRange}}</td>
            <td style="background-color: #fcd7a9; border: 1px solid black; text-align: center; padding: 5px;">Forgot To Mark Exit Report</td>
            <td style="background-color: #fcd7a9; border: 1px solid black; text-align: center; padding: 5px;"> {{ $generatedOn }} </td>
        </tr>

        @if($flag == 'pdf')
        <tr>
            <td colspan="{{ $user->role_id != 2 ? 6 : 5 }}" style="height: 20px;"></td>
        </tr>
        <tr>
            <td colspan="{{ $user->role_id != 2 ? 6 : 5 }}" style="height: 20px;"></td>
        </tr>
        <tr>
            <td colspan="{{ $user->role_id != 2 ? 6 : 5 }}" style="height: 20px;"></td>
        </tr>
        @else
        <tr>
            <td colspan="{{ $user->role_id != 2 ? 6 : 5 }}" style="height: 20px;" ></td>
        </tr>
        @endif

        <tr>
            <th style="background-color: #d97979; border: 1px solid black; padding: 5px; text-align: center; font-weight: bold;" colspan="1">Sr. No.</th>
            <th style="background-color: #d97979; border: 1px solid black; padding: 5px; text-align: center; font-weight: bold;" colspan="2">Date</th>
            <th style="background-color: #d97979; border: 1px solid black; padding: 5px; text-align: center; font-weight: bold;" colspan="1">Name of Employee</th>
            @if($client == 'all' && $user->role_id !== '2')
            <th style="background-color: #d97979; border: 1px solid black; padding: 5px; text-align: center; font-weight: bold;" colspan="1">Client / Range</th>
            @endif
            <th style="background-color: #d97979; border: 1px solid black; padding: 5px; text-align: center; font-weight: bold;" colspan="1">Site</th>
            <th style="background-color: #d97979; border: 1px solid black; padding: 5px; text-align: center; font-weight: bold;" colspan="1">Punch-In Time</th>
        </tr>

        <?php $srNo = 1; ?>

        @foreach ($data as $item)
        <tr>
            <td style="border: 1px solid black; text-align: center; padding: 5px;" colspan="1">{{ $srNo }}</td>
            <td style="border: 1px solid black; text-align: center; padding: 5px;" colspan="2">{{ $item->date }}</td>
            <td style="border: 1px solid black; text-align: center; padding: 5px;" colspan="1">{{ $item->name }}</td>
            @if($client == 'all' && $user->role_id !== '2')
            <td style="border: 1px solid black; text-align: center; padding: 5px;" colspan="1">{{ $item->client_name }}</td>
            @endif
            <td style="border: 1px solid black; text-align: center; padding: 5px;" colspan="1" class="@if(strtolower($item->site_name) === 'current location') current-location @endif">
                {{ $item->site_name }}
            </td>
            <td style="border: 1px solid black; text-align: center; padding: 5px;" colspan="1">{{ $item->entry_time }}</td>
        </tr>
        <?php $srNo = $srNo + 1; ?>
        @endforeach
    </tbody>
</table>
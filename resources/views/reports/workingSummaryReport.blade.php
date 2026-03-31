@php
    $user = session('user');
    $cols = ($user->role_id != 2) ? 6 : 5;
@endphp

<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th colspan="1" style="background-color: #1e293b; color: #94a3b8; font-size: 10px; text-align: center; border: 1px solid #334155; font-weight: bold;">ORGANIZATION</th>
            @if($user->role_id != 2)
            <th colspan="1" style="background-color: #1e293b; color: #94a3b8; font-size: 10px; text-align: center; border: 1px solid #334155; font-weight: bold;">CLIENT / RANGE</th>
            @endif
            <th colspan="1" style="background-color: #1e293b; color: #94a3b8; font-size: 10px; text-align: center; border: 1px solid #334155; font-weight: bold;">SITE / BEAT</th>
            <th colspan="2" style="background-color: #1e293b; color: #94a3b8; font-size: 10px; text-align: center; border: 1px solid #334155; font-weight: bold;">DATE RANGE</th>
            <th colspan="1" style="background-color: #1e293b; color: #94a3b8; font-size: 10px; text-align: center; border: 1px solid #334155; font-weight: bold;">GENERATED ON</th>
        </tr>

        <tr>
            <td colspan="1" style="background-color: #1e293b; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #334155;">{{ $companyName->name ?? $companyName }}</td>
            @if($user->role_id != 2)
            <td colspan="1" style="background-color: #1e293b; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #334155;">{{ $clientName ?? 'All Clients' }}</td>
            @endif
            <td colspan="1" style="background-color: #1e293b; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #334155;">{{ ($geofences == 'all') ? 'All sites' : $siteName }}</td>
            <td colspan="2" style="background-color: #1e293b; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #334155;">{{ $startDate }} to {{ $endDate }}</td>
            <td colspan="1" style="background-color: #1e293b; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #334155;">{{ $generatedOn }}</td>
        </tr>

        <tr><td colspan="{{ $cols }}" style="height: 20px;"></td></tr>

        <tr style="background-color: #334155;">
            <th style="border: 1px solid #000000; text-align: center; font-weight: bold; color: #ffffff; background-color: #334155;">SR NO</th>
            <th style="border: 1px solid #000000; text-align: center; font-weight: bold; color: #ffffff; background-color: #334155;">EMPLOYEE NAME</th>
            <th style="border: 1px solid #000000; text-align: center; font-weight: bold; color: #ffffff; background-color: #334155;">TOTAL DAYS</th>
            <th style="border: 1px solid #000000; text-align: center; font-weight: bold; color: #ffffff; background-color: #334155;">WORKED</th>
            <th style="border: 1px solid #000000; text-align: center; font-weight: bold; color: #ffffff; background-color: #334155;">ABSENT</th>
            <th style="border: 1px solid #000000; text-align: center; font-weight: bold; color: #ffffff; background-color: #334155;">WEEK OFF</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($groupedData as $userId => $userData)
        <tr>
            <td style="border: 1px solid #cccccc; text-align: center;">{{ $loop->iteration }}</td>
            <td style="border: 1px solid #cccccc; text-align: left;">{{ $userData['user_name'] }}</td>
            <td style="border: 1px solid #cccccc; text-align: center;">{{ $userData['totalWorkingDays'] }}</td>

            {{-- Green for Worked --}}
            <td style="border: 1px solid #cccccc; text-align: center; color: #10b981; font-weight: bold;">
                {{ $userData['daysWorked'] == 0 && $fileType == 'xlsx' ? "-0" : $userData['daysWorked'] }}
            </td>

            {{-- Red for Absent --}}
            <td style="border: 1px solid #cccccc; text-align: center; color: #ef4444; font-weight: bold;">
                {{ $userData['absentDays'] == 0 && $fileType == 'xlsx' ? "-0" : $userData['absentDays'] }}
            </td>

            <td style="border: 1px solid #cccccc; text-align: center; color: #64748b;">
                {{ $userData['weekOffCount'] == 0 && $fileType == 'xlsx' ? "-0" : $userData['weekOffCount'] }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

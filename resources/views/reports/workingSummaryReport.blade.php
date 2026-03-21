@include('includes.header')
@php
    $user = session('user');
    // dd($user);
@endphp

<style>
    .report-container {
        margin: 20px;
        padding: 15px;
    }

    .report-header {
        margin-bottom: 25px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        background-color: #fff;
    }

    .report-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 0;
    }

    .header-cell {
        /* background-color: #fcd7a9; */
        padding: 12px 15px;
        text-align: center;
        border: 1px solid #000;
        font-weight: 600;
    }

    .data-cell {
        padding: 10px 15px;
        border: 1px solid #000;
        text-align: center;
    }

    .employee-link {
        color: #003add;
        cursor: pointer;
        text-decoration: none;
    }

    .employee-link:hover {
        text-decoration: underline;
    }

    .table-container {
        border: 1px solid #dee2e6;
        border-radius: 5px;
        overflow: hidden;
        margin-top: 20px;
    }

    .table-scroll {
        overflow: auto;
        max-height: 70vh;
    }

    /* Fix for header alignment */
    .header-row th {
        position: sticky;
        top: 0;
        z-index: 2;
        background-color: #d97979;
    }
</style>

<div class="report-container">
    <!-- Report Header Table -->
    <div class="report-header">
        <table class="report-table">
            <tr style="background-color: #fcd7a9;">
                <th class="header-cell" style="width: 16.66%">Organization</th>
                @if($user->role_id != 2)
                    <th class="header-cell" style="width: 16.66%">Client / Range </th>
                @endif
                <th class="header-cell" style="width: 16.66%">Site / Beat</th>
                <th class="header-cell" style="width: 16.66%">Date Range</th>
                <th class="header-cell" style="width: 16.66%">Report Type</th>
                <th class="header-cell" style="width: 16.66%">Generated On</th>
            </tr>
            <tr>
                <td class="data-cell">{{ $companyName ?? '-' }}</td>
                @if($user->role_id != 2)
                    <td class="data-cell">{{ $clientName ?? '_' }}</td>
                @endif
                <td class="data-cell">{{ ($geofences == 'all') ? 'All sites' : $siteName }}</td>
                <td class="data-cell">{{ $startDate }} to {{ $endDate }}</td>
                <td class="data-cell">Working Summary Report</td>
                <td class="data-cell"> {{ $generatedOn  }} </td>
            </tr>
        </table>
    </div>

    <!-- Main Report Table -->
    <div class="table-container">
        <div class="table-scroll">
            <table class="report-table">
                <thead style="background-color: #d97979;">
                    <tr>
                        <th class="header-cell" style="border:1px solid black;text-align:center;">Sr No</th>
                        <th class="header-cell" style="border:1px solid black;text-align:center;">Employee Name</th>
                        <th class="header-cell" style="border:1px solid black;text-align:center;">Total Working Days
                        </th>
                        <th class="header-cell" style="border:1px solid black;text-align:center;">Days Worked</th>
                        <th class="header-cell" style="border:1px solid black;text-align:center;">Days Absent</th>
                        <th class="header-cell" style="border:1px solid black;text-align:center;">Total WeekOff</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($groupedData as $userId => $userData)
                        <tr>
                            <td class="data-cell" style="border:1px solid black;text-align:center;">{{ $loop->iteration }}
                            </td>
                            <td class="data-cell" style="width: 32%; text-align: left">
                                <a class="employee-link"
                                    onclick="guardAttendanceReport('{{ $userId }}','{{ $startDate }}','{{ $endDate }}')">
                                    {{ $userData['user_name'] }}
                                </a>
                            </td>
                            @if ($fileType != 'xlsx')
                                <td class="data-cell" style="border:1px solid black;text-align:center;">
                                    {{ $userData['totalWorkingDays'] }}</td>
                                <td class="data-cell" style="border:1px solid black;text-align:center;">
                                    {{ $userData['daysWorked']  }}</td>
                                <td class="data-cell" style="border:1px solid black;text-align:center;">
                                    {{ $userData['absentDays']  }}</td>
                                <td class="data-cell" style="border:1px solid black;text-align:center; font-weight: bold;">
                                    {{ $userData['weekOffCount'] }}</td>
                            @else

                                <td class="data-cell" style="border:1px solid black;text-align:center;">
                                    {{ $userData['totalWorkingDays'] == 0 ? "-0" : $userData['totalWorkingDays']  }}</td>
                                <td class="data-cell" style="border:1px solid black;text-align:center;">
                                    {{ $userData['daysWorked'] == 0 ? "-0" : $userData['daysWorked'] }}</td>
                                <td class="data-cell" style="border:1px solid black;text-align:center;">
                                    {{ $userData['absentDays'] == 0 ? "-0" : $userData['absentDays'] }}</td>
                                <td class="data-cell" style="border:1px solid black;text-align:center; font-weight: bold;">
                                    {{ $userData['weekOffCount'] == 0 ? "-0" : $userData['weekOffCount']}}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
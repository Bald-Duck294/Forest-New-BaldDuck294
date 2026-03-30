@php
    $user = session('user');
    
    $dailyCountArray = [];
@endphp
<table style="border-collapse:collapse;width:100%;border: 1px solid black;">
    <tbody>

        <tr>
            <th colspan="7" style="text-align: center;background-color: #fcd7a9;font-weight:bold;padding:15px;border: 1px solid black; font-size: 22px">
                Client-wise Attendance Report
            </th>
        </tr>

        <tr>
            <th colspan="2" style="text-align:center; background-color:#fcd7a9;font-weight:bold;padding:5px;border:1px solid black;">
                Organization
            </th>
            @if($user->role_id !== 2)
            <th colspan="2" style="text-align:center; background-color:#fcd7a9;font-weight:bold;padding:5px;border:1px solid black;">
                Client /Range
            </th>
            @endif
            <th colspan="2" style="text-align:center; background-color:#fcd7a9;font-weight:bold;padding:5px;border:1px solid black;">
                Date Range
            </th>
            <th colspan="2" style="text-align:center; background-color:#fcd7a9;font-weight:bold;padding:5px;border:1px solid black;">
                Report Type
            </th>
            <th style="text-align:center; background-color:#fcd7a9;font-weight:bold;padding:5px;border:1px solid black;" colspan="2 ">
                Generated On
            </th>
        </tr>

        <tr>
            <td colspan="2" style="text-align:center;background-color:#fcd7a9;padding:5px;border:1px solid black;">
                {{ $companyName }}
            </td>
            @if($user->role_id !== 2)
            <td colspan="2" style="text-align:center;background-color:#fcd7a9;padding:5px;border:1px solid black;">
                @if(isset($clientName->name)) {{ $clientName->name }} @else Hours @endif
            </td>
            @endif
            <td colspan="2" style="text-align:center;background-color:#fcd7a9;padding:5px;border:1px solid black;">
                {{ $date }}
            </td>
            <td colspan="2" style="text-align:center;background-color:#fcd7a9;padding:5px;border:1px solid black;">
                @if (isset($subType))
                    {{ $subType }}
                @else
                    N/A
                @endif
            </td>
            <td style="text-align:center;background-color:#fcd7a9;padding:5px;border:1px solid black;" colspan="2">
{{ $generatedOn }}
        </td>
        </tr>

        <tr><td colspan="<?= ($user->role_id != 2) ? (4 + $daysCount + 4) : (4 + $daysCount + 3) ?>" style="padding: 10px;"></td></tr>

        <tr>
            <?php $datee = $startDatee; ?>
            <th style="background-color: #d97979;border:1px solid black;padding:5px;text-align:center;font-weight:bold;">
                Sr No
            </th>
            <th style="background-color: #d97979;border:1px solid black;padding:5px;text-align:center;font-weight:bold;">
                Employee Name
            </th>
            <th style="background-color: #d97979;border:1px solid black;padding:5px;text-align:center;font-weight:bold;">
                Site/Beat
            </th>
            <th style="background-color: #d97979;border:1px solid black;padding:5px;text-align:center;font-weight:bold;">
                Days Worked
            </th>

            <?php
            $daysArray = [];
            $daysArray[] = $datee;
            $dateee = date('d-m-y', strtotime($datee));
            $dateFormat = date('Y-m-d', strtotime($datee));
            $fdate = date('d', strtotime($datee));
            $day = date('D', strtotime($datee));
            ?>
            @php
                if (array_key_exists($dateFormat, $attendCount) !== false) {
                    $dailyCountArray[] = count($attendCount[$dateFormat]);
                } else {
                    $dailyCountArray[] = 0;
                }
            @endphp
            <th style="background-color: #d97979;border:1px solid black;padding:5px;text-align:center;font-weight:bold;">
                {{ $day }}-{{ $fdate }}
            </th>
            @for ($i = 1; $i < $daysCount; $i++)
                <?php
                $dateee = date('d-m-y', strtotime('+1 day', strtotime($datee)));
                $dateFormat = date('Y-m-d', strtotime('+1 day', strtotime($datee)));
                $fdate = date('d', strtotime('+1 day', strtotime($datee)));
                $day = date('D', strtotime('+1 day', strtotime($datee)));
                $datee = date('d-m-Y', strtotime('+1 day', strtotime($datee)));
                $daysArray[] = $datee;
                ?>
                @php
                    if (array_key_exists($dateFormat, $attendCount) !== false) {
                        $dailyCountArray[] = count($attendCount[$dateFormat]);
                    } else {
                        $dailyCountArray[] = 0;
                    }
                @endphp
                <th style="background-color: #d97979;border:1px solid black;padding:5px;text-align:center;font-weight:bold;">
                    {{ $day }}-{{ $fdate }}
                </th>
            @endfor
        </tr>

        <?php $srNo = 0; ?>
        @foreach ($data as $key => $param)
            @php
                if (isset($weekoffs[$key]) && isset($weekoffs[$key][0])) {
                    $days = json_decode($weekoffs[$key][0], true);
                } else {
                    $days = [];
                }
                $param = array_values(array_unique($param));
                $acount = count($param);
                $srNo++;
            @endphp
            <tr>
                <td style="border:1px solid black;text-align:center;">{{ $srNo }}</td>
                <td style="border:1px solid black;">{{ $names[$key][0] }}</td>
                @if (isset($sites[$key]) && isset($sites[$key][0]['site']))
                    <td style="border:1px solid black;text-align:center;">{{ $sites[$key][0]['site'] }}</td>
                @else
                    <td style="border:1px solid black;text-align:center;">NA</td>
                @endif

                <td style="border:1px solid black;text-align:center;">{{ $acount == 0 ? '-' : $acount }}</td>
                
                @for ($i = 0; $i < $daysCount; $i++)
                    @php
                        $index = array_search($daysArray[$i], $param);
                    @endphp

                    @if ($index !== false)
                        @if ($attendanceSubType == 'EmployeeAttendanceReportwithSite')
                            <td style="border:1px solid black;text-align:center;color:#00873d;">
                                @if (isset($sites[$key][0]) && isset($sites[$key][0]['site']))
                                    {{ $sites[$key][0]['site'] }}
                                @else
                                    P
                                @endif
                            </td>
                        @elseif($attendanceSubType == 'EmployeeAttendanceReport')
                            <td style="border:1px solid black;text-align:center;color:#00873d;">P</td>
                        @elseif($attendanceSubType == 'EmployeeAttendanceReportwithHours')
                            @php
                                $colorStyle = 'color:#0062ff;';
                                $displayText = 'Exit Unmarked';
                                
                                if (isset($hours[$key][$index]) && $hours[$key][$index] != null) {
                                    $arr = explode(' ', $hours[$key][$index]);
                                    $minutes = (int) $arr[0] * 60 + (int) $arr[3];
                                    if ($minutes < 480) {
                                        $colorStyle = 'color:#ffc700;';
                                    } else {
                                        $colorStyle = 'color:#00873d;';
                                    }
                                    $displayText = $hours[$key][$index];
                                }
                            @endphp
                            <td style="border:1px solid black;text-align:center;{{ $colorStyle }}">{{ $displayText }}</td>
                        @else
                            <td style="border:1px solid black;text-align:center;color:#00873d;">P</td>
                        @endif
                    @elseif($days && array_search(date('l', strtotime($daysArray[$i])), $days) !== false)
                        <td style="border:1px solid black;text-align:center;color:#D9B611;">WO</td>
                    @else
                        <td style="border:1px solid black;text-align:center;color:#a11d1d;">A</td>
                    @endif
                @endfor
            </tr>
        @endforeach

        <tr>
            <td colspan="4" style="border:1px solid black;text-align:center;background-color:#d97979;font-weight:bold;padding:5px;">Daily Attendance Count</td>
            @for ($i = 0; $i < $daysCount; $i++)
                <td style="border:1px solid black;text-align:center;background-color:#d97979;font-weight:bold;padding:5px;">
                    {{ isset($dailyCountArray[$i]) ? $dailyCountArray[$i] : '0' }}
                </td>
            @endfor
        </tr>
    </tbody>
</table>
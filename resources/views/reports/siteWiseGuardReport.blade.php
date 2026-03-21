@php
    // dump('in site Wise report');
    $user = session('user');
@endphp
<table style="border-collapse:collapse;width:100%;justify-content:center;">
    <?php $datee = $startDatee;
    $dailyCountArray = [];
     ?>
    <tbody>
        <tr style="background-color:#fcd7a9;">
            <th colspan="3" style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black;">
                Organization</th>
                @if($user->role_id !== 2)
                  <th colspan="3" style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black;">
                Client / Range</th>
                @endif
            <th colspan="3" style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black;">
                Site / Beat</th>
            <th colspan="6" style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black;">
                Date Range</th>
            <th colspan="6" style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black;">
                Report Type</th>
            <th colspan="3" style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black; background-color:#fcd7a9;">
                Generated On</th>
        </tr >


        <tr style="background-color:#fcd7a9; margin-bottom:2rem">
            <td colspan="3" style="text-align: center;padding:5px;border: 1px solid black;">
                {{ $companyName }}</td>
                                @if($user->role_id !== 2)
            <td colspan="3" style="text-align: center;padding:5px;border: 1px solid black;">
                {{   $clientName ?? '-' }}</td>
                @endif
                    <td colspan="3" style="text-align: center;padding:5px;border: 1px solid black;">
                {{ $siteName  }}</td>
                <td colspan="6" style="text-align: center;padding:5px;border: 1px solid black; background-color:#fcd7a9;">
                    {{ $date }}</td>
            <td colspan="6" style="text-align: center;padding:5px;border: 1px solid black;">
                @if(isset($subType)) {{$subType}} @else N/A @endif</td>
         

                <td colspan="3" style="text-align: center;padding:5px;border: 1px solid black;">
                    {{ $generatedOn }}</td>
        </tr>

        <tr><td colspan="21" style="padding: 10px;"></td></tr>



        <tr style="background-color:#d97979;">
            <?php $datee = $startDatee; ?>
            <th style="text-align:center;font-weight:bold;border: 1px solid black; ">Sr No</th>
            <th style="text-align:center;font-weight:bold;border: 1px solid black;">Employee Name</th>
            <th style="text-align:center;font-weight:bold;border: 1px solid black;">Days Worked</th>

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
            <th style="text-align:center;font-weight:bold;border: 1px solid black;">
                {{ $day }}-{{ $fdate }}</th>
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
                <th style="text-align:center;font-weight:bold;border: 1px solid black;">
                    {{ $day }}-{{ $fdate }}</th>
            @endfor
        </tr>

        <?php $srNo = 0; ?>
        @foreach ($data as $key => $param)
            @php
            if($weekoffs[$key]) {
                $days = json_decode($weekoffs[$key][0], true);
            } else {
                $days = [];
            }
            $acount = count($param);
            $srNo++;
            @endphp

            <tr>
                <td style="text-align:center;border: 1px solid black;">{{ $srNo }}</td>
                <td style="border: 1px solid black;">{{ $names[$key][0] }}</td>

                <td style="text-align:center;border: 1px solid black;">
                    {{ $flag === 'xlsx' ? ($acount == 0 ? '--' : $acount) : $acount }}
                </td> 
                @for ($i = 0; $i < $daysCount; $i++)
                    @php
                    $index = array_search($daysArray[$i], $param);
                    @endphp
                    
                    @if($index !== false)
                        @if($attendanceSubType == 'EmployeeAttendanceReportwithSite')
                            <td style="border:1px solid black;text-align:center;color: #00873d;">
                                @if(isset($sites[$key][$index]))
                                    @if($sites[$key][$index] != null)
                                        {{ $sites[$key][$index]['site'] }}
                                    @else 
                                        P
                                    @endif
                                @else
                                    P
                                @endif
                            </td>
                        @elseif($attendanceSubType == 'EmployeeAttendanceReport')
                            <td style="border:1px solid black;text-align:center;color: #00873d;">
                                {{-- @if(isset($sites[$key][$index]))
                                    {{ $sites[$key][$index] }}
                                @elseif(isset($sites[$key][0]))
                                    {{ $sites[$key][0] }}
                                @else --}}
                                    P
                                {{-- @endif --}}
                            </td>
                        @elseif($attendanceSubType == 'EmployeeAttendanceReportwithHours')
                            @php
                            if (isset($hours[$key][$index]) && $hours[$key][$index] != null) {
                                $arr = explode(' ', $hours[$key][$index]);
                                $minutes = (int) $arr[0] * 60 + (int) $arr[3];
                                $color = $minutes < 480 ? '#ffc700' : '#00873d';
                            } else {
                                $minutes = 0;
                                $color = '#0062ff';
                            }
                            @endphp
                            <td style="border:1px solid black;text-align:center;color: {{ $color }}">
                                @if ($minutes == 0)
                                    Exit Unmarked
                                @else
                                    {{ $hours[$key][$index] }}
                                @endif
                            </td>
                        @else
                            <td style="border:1px solid black;text-align:center;color: #00873d;">P</td>
                        @endif
                    @elseif($days && array_search(date('l', strtotime($daysArray[$i])), $days) !== false)
                        <td style="border:1px solid black;text-align:center;color: #D9B611;">WO</td>
                    @else
                        <td style="border:1px solid black;text-align:center;color: #a11d1d;">A</td>
                    @endif
                @endfor
            </tr>
        @endforeach

        <tr style="background-color:#d97979;">
            <td style="border:1px solid black;text-align:center;" colspan="3">
                Daily Attendance Count</td>
            @for ($i = 0; $i < $daysCount; $i++)
                <td style="border:1px solid black;text-align:center;">
                    {{ $dailyCountArray[$i] }}</td>
            @endfor
        </tr>
    </tbody>
</table>
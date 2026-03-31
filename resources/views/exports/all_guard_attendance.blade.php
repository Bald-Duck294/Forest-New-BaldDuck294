<table style="border-collapse:collapse; text-align: center;">
    <?php
    // Initialize our variables just like in the modal view
    $datee = $startDatee;
    $dailyCountArray = [];
    ?>
    <thead>
        <tr>
            <th colspan="{{ $fromdate == $todate ? 7 + $daysCount : 6 + $daysCount }}" style="text-align: center; background-color: #fcd7a9; font-weight:bold; font-size: 20px;">
                @if (isset($subType))
                {{ $subType }}
                @else
                Guard Attendance Report
                @endif
            </th>
        </tr>
        <tr>
            <th colspan="{{ $fromdate == $todate ? 7 + $daysCount : 6 + $daysCount }}" style="text-align: left; background-color: #fcd7a9; font-weight:bold;">
                Org: {{ $companyName }} | Date: {{ $date }} | Generated: {{ $generatedOn }}
            </th>
        </tr>

        <tr>
            <th style="background-color: #fcd7a9; font-weight:bold;">Sr No</th>
            <th style="background-color: #fcd7a9; font-weight:bold;">Employee Name</th>
            <th style="background-color: #fcd7a9; font-weight:bold;">Client/Range</th>
            <th style="background-color: #fcd7a9; font-weight:bold;">Site/Beat</th>

            @if ($fromdate == $todate)
            <th style="background-color: #fcd7a9; font-weight:bold;">Location</th>
            @endif

            <th style="background-color: #fcd7a9; font-weight:bold;">Days Worked</th>

            <?php
            $daysArray = [];
            $daysArray[] = $datee;
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

            <th style="background-color: #fcd7a9; font-weight:bold;">{{ $day }}-{{ $fdate }}</th>

            @for ($i = 1; $i < $daysCount; $i++)
                <?php
                $dateFormat = date('Y-m-d', strtotime('+1 day', strtotime($datee)));
                $fdate = date('d', strtotime('+1 day', strtotime($datee)));
                $day = date('D', strtotime('+1 day', strtotime($datee)));
                $datee = date('d-m-Y', strtotime('+1 day', strtotime($datee)));
                $daysArray[] = $datee;
                ?>
                @php
                if (array_key_exists($dateFormat, $attendCount) !==false) {
                $dailyCountArray[]=count($attendCount[$dateFormat]);
                } else {
                $dailyCountArray[]=0;
                }
                @endphp
                <th style="background-color: #fcd7a9; font-weight:bold;">{{ $day }}-{{ $fdate }}</th>
                @endfor
        </tr>
    </thead>

    <tbody>
        <?php $srNo = 0; ?>
        @foreach ($data as $key => $param)
        @php
        if (isset($weekoffs[$key]) && isset($weekoffs[$key][0])) {
        $days = json_decode($weekoffs[$key][0], true);
        } else {
        $days = [];
        }
        $acount = count($param);
        $srNo++;
        @endphp
        <tr>
            <td>{{ $srNo }}</td>
            <td>{{ $names[$key][0] }}</td>

            @if (isset($sites[$key]) && $sites[$key][0]['client'] != null)
            <td>{{ $sites[$key][0]['client'] }}</td>
            <td>{{ $sites[$key][0]['site'] }}</td>
            @elseif(isset($supervisorSites[$key]))
            <td>
                @foreach ($supervisorSites[$key]['client'] as $clientkey => $val)
                {{ $val }} @if ($clientkey != array_key_last($supervisorSites[$key]['client'])), @endif
                @endforeach
            </td>
            <td>
                @if (!empty($supervisorSites[$key]['site']))
                @foreach ($supervisorSites[$key]['site'] as $sitekey => $val)
                {{ !empty($val) ? $val : '-' }}@if ($sitekey !== array_key_last($supervisorSites[$key]['site'])), @endif
                @endforeach
                @else
                -
                @endif
            </td>
            @else
            <td>NA</td>
            <td>NA</td>
            @endif

            @if ($fromdate == $todate)
            @if (isset($attendSites[$key]))
            @if ($attendSites[$key][0] == 'Current Location')
            <td style="color: #10b981; font-weight: bold;">ON SITE</td>
            @else
            <td>{{ $attendSites[$key][0] }}</td>
            @endif
            @else
            <td style="font-style: italic;">Not Marked</td>
            @endif
            @endif

            <td style="font-weight: bold;">{{ $acount }}</td>

            @for ($i = 0; $i < $daysCount; $i++)
                @php
                $index=array_search($daysArray[$i], $param);
                @endphp

                @if ($index !==false)
                @if ($attendanceSubType=='EmployeeAttendanceReport' )
                <td>P</td>
                @elseif($attendanceSubType == 'EmployeeAttendanceReportwithHours')
                @php
                if ($hours[$key][$index] != null) {
                $arr = explode(' ', $hours[$key][$index]);
                $minutes = (int) $arr[0] * 60 + (int) (@$arr[3] ?: 0);
                $color = $minutes < 480 ? '#f59e0b' : '#10b981' ;
                    } else {
                    $minutes=0;
                    $color='#3b82f6' ;
                    }
                    @endphp
                    <td style="color: {{ $color }}; font-weight: bold;">
                    @if ($minutes == 0)
                    Exit Unmarked
                    @else
                    {{ $hours[$key][$index] }}
                    @endif
                    </td>
                    @elseif($attendanceSubType == 'EmployeeAttendanceReportwithSite')
                    <td style="color: #10b981; font-weight: bold;">
                        @if (isset($attendSites[$key][$index]) && $attendSites[$key][$index] !== 'Current Location')
                        {{ $attendSites[$key][$index] }}
                        @else
                        On Site
                        @endif
                    </td>
                    @else
                    <td style="color: #10b981; font-weight: bold;">
                        @if (isset($sites[$key][$index]['site']))
                        {{ $sites[$key][$index]['site'] }}
                        @elseif(isset($sites[$key][0]['site']))
                        {{ $sites[$key][0]['site'] }}
                        @else
                        P
                        @endif
                    </td>
                    @endif
                    @elseif($days && array_search(date('l', strtotime($daysArray[$i])), $days) !== false)
                    <td>WO</td>
                    @else
                    <td>A</td>
                    @endif
                    @endfor
        </tr>
        @endforeach
    </tbody>

    <tfoot>
        <tr>
            <td style="font-weight: bold; text-align: right;" colspan="{{ $fromdate == $todate ? 6 : 5 }}">
                Daily Attendance Count:
            </td>
            @for ($i = 0; $i < $daysCount; $i++)
                <td style="font-weight: bold;">
                {{ $dailyCountArray[$i] == 0 ? '0' : $dailyCountArray[$i] }}
                </td>
                @endfor
        </tr>
    </tfoot>
</table>
<table style="border-collapse:collapse;max-width:100%; text-align: center;">
    <?php $datee = $startDatee;
    $dailyCountArray = []; ?>
    <tbody>
        <tr>
            <th colspan="{{$fromdate == $todate ? 7 : 6}}" style="text-align: center;background-color: #fcd7a9;font-weight:bold;padding:12px;border: 1px solid black; font-size: 20px">
                Employee Attendance Report</th>
        </tr>

        <tr>
            <th style="text-align: center; background-color:#fcd7a9;font-weight:bold;padding:4px;border: 1px solid black;white-space:nowrap;">
                Organization</th>
            <th style="text-align: center; background-color:#fcd7a9;font-weight:bold;padding:4px;border: 1px solid black;white-space:nowrap;">
                Date Range</th>
            <th style="text-align: center; background-color:#fcd7a9;font-weight:bold;padding:4px;border: 1px solid black;white-space:nowrap;">
                Report Type</th>
            <th colspan="{{$fromdate == $todate ? 4 : 3}}" style="text-align: center; background-color:#fcd7a9;font-weight:bold;padding:4px;border: 1px solid black;white-space:nowrap;">
                Generated On</th>
        </tr>
        <tr>
            <td style="text-align: center;background-color:#fcd7a9;padding:4px;border: 1px solid black;white-space:nowrap;">
                {{ $companyName }}
            </td>
            <td style="text-align: center;background-color:#fcd7a9;padding:4px;border: 1px solid black;white-space:nowrap;">
                {{ $date }}
            </td>
            <td style="text-align: center;background-color:#fcd7a9;padding:4px;border: 1px solid black;white-space:nowrap;">
                @if(isset($subType)) {{$subType}} @else N/A @endif
            </td>
            <td colspan="{{$fromdate == $todate ? 4 : 3}}" style="text-align: center;background-color:#fcd7a9;padding:4px;border: 1px solid black;white-space:nowrap;">

                {{ $generatedOn }}
            </td>
        </tr>

        <tr>
            <?php $datee = $startDatee; ?>

            <th style="text-align: center;font-size: 13px;font-weight: bold;border: 1px solid #000;background-color: #d97979;padding:4px;white-space:nowrap;">
                Sr No
            </th>
            <th style="font-size: 13px;font-weight: bold;border: 1px solid #000;background-color: #d97979;padding:4px;white-space:nowrap;">
                Employee Name
            </th>
            <th style="text-align: center;font-size: 13px;font-weight: bold;border: 1px solid #000;background-color: #d97979;padding:4px;white-space:nowrap;">
                Client/Range
            </th>
            <th style="text-align: center;font-size: 13px;font-weight: bold;border: 1px solid #000;background-color: #d97979;padding:4px;white-space:nowrap;">
                Site/Beat
            </th>

            @if($fromdate == $todate)
            <th style="text-align: center;font-size: 13px;font-weight: bold;border: 1px solid #000;background-color: #d97979;padding:4px;white-space:nowrap;">
                Location
            </th>
            @endif

            <th style="text-align: center;font-size: 13px;font-weight: bold;border: 1px solid #000;background-color: #d97979;padding:4px;white-space:nowrap;">
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
            <th style="text-align: center;font-size: 13px;font-weight: bold;border: 1px solid #000;background-color: #d97979;padding:4px;white-space:nowrap;">
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
                if (array_key_exists($dateFormat, $attendCount) !==false) {
                $dailyCountArray[]=count($attendCount[$dateFormat]);
                } else {
                $dailyCountArray[]=0;
                }
                @endphp
                <th style="text-align: center;font-size: 13px;font-weight: bold;border: 1px solid #000;background-color: #d97979;padding:4px;white-space:nowrap;">
                {{ $day }}-{{ $fdate }}</th>
                @endfor
        </tr>

        <?php $srNo = 0; ?>
        @foreach ($data as $key => $param)
        @php
        if (isset($weekoffs[$key]) && isset($weekoffs[$key][0])) {
        $days = json_decode($weekoffs[$key][0], true);
        }
        else {
        $days = [];
        }
        $param = array_values(array_unique($param));
        $acount = count($param);
        $srNo++;
        @endphp

        <tr>
            <td style="border:1px solid black;text-align:center;padding:3px;white-space:nowrap;">{{ $srNo }}</td>
            <td style="border:1px solid black;padding:3px;">{{ $names[$key][0] }}</td>

            @if (isset($sites[$key]) && $sites[$key][0]['site'] != null)
            <td style="border:1px solid black;text-align:left;padding:3px;word-break:break-word;white-space:normal;">
                {{ $sites[$key][0]['client'] }}
            </td>
            <td style="border:1px solid black;text-align:left;padding:3px;word-break:break-word;white-space:normal;">
                {{ $sites[$key][0]['site'] }}
            </td>

            @elseif(isset($supervisorSites[$key]))
            <td style="border:1px solid black;text-align:left;">
                @foreach($supervisorSites[$key]['client'] as $clientkey => $val)
                {{$val}} @if($clientkey != array_key_last($supervisorSites[$key]['client'])) , @endif
                @endforeach
            </td>

            <td style="border:1px solid black;text-align:left;padding:3px;">
                @foreach($supervisorSites[$key]['site'] as $sitekey => $val)
                {{$val}} @if($sitekey != array_key_last($supervisorSites[$key]['site'])) , @endif
                @endforeach
            </td>


            @else
            <td style="border:1px solid black;text-align:left;padding:3px;">NA</td>
            <td style="border:1px solid black;text-align:left;padding:3px;">NA</td>
            @endif

            @if($fromdate == $todate)
            @if(isset($attendSites[$key]))
            @if( $attendSites[$key][0] == 'Current Location')
            <td style="border:1px solid black;text-align:left;padding:3px;color:#00873d;">
                <b>ON SITE </b>
            </td>
            @else
            <td style="border:1px solid black;text-align:left;padding:3px;">
                {{ is_array($attendSites[$key][0]) ? implode(', ', $attendSites[$key][0]) : $attendSites[$key][0] }}
            </td>
            @endif
            @else
            <td style="border:1px solid black;text-align:left;padding:3px;color:#a11d1d;">Not Marked</td>
            @endif
            @endif

            <td style="border:1px solid black;text-align:center;padding:3px;white-space:nowrap;">{{ $acount == 0 ? "-" : $acount }}</td>
            @for ($i = 0; $i < $daysCount; $i++)
                @php $index=array_search($daysArray[$i], $param); @endphp
                @if($index !==false)
                @if ($_REQUEST['attendanceSubType']=='EmployeeAttendanceReport' )
                <td style="border:1px solid black;text-align:center;color:#00873d;padding:3px;white-space:nowrap;">P</td>
                @elseif ($_REQUEST['attendanceSubType'] == 'EmployeeAttendanceReportwithHours')
                @php
                if ($hours[$key][$index] != null) {
                $arr = explode(' ', $hours[$key][$index]);
                $minutes = (int) $arr[0] * 60 + (int) $arr[3];
                if ($minutes < 480) {
                    $color='#ffc700' ;
                    } else {
                    $color='#00873d' ;
                    }
                    } else {
                    $minutes=0;
                    $color='#0062ff' ;
                    }
                    @endphp
                    <td style="border:1px solid black;text-align:center;color:{{ $color }};padding:3px;white-space:nowrap;">
                    @if ($minutes == 0)
                    Exit Unmarked
                    @else
                    {{ $hours[$key][$index] }}
                    @endif
                    </td>
                    @elseif($_REQUEST['attendanceSubType'] == 'EmployeeAttendanceReportwithSite')
                    <td style="border:1px solid black;text-align:center;color:#00873d;padding:3px;">
                        @if(isset($attendSites[$key][$index]))
                        @php
                        $siteData = is_array($attendSites[$key][$index]) ? implode(', ', $attendSites[$key][$index]) : $attendSites[$key][$index];
                        @endphp

                        @if($siteData != "Current Location")
                        {{ $siteData }}
                        @else
                        <span style="color:rgb(49, 104, 175);">On Site </span>
                        @endif
                        @endif
                    </td>
                    @else
                    <td style="border:1px solid black;text-align:center;color:#00873d;padding:3px;white-space:nowrap;">P</td>
                    @endif
                    @elseif($days && array_search(date('l', strtotime($daysArray[$i])), $days) !== false)
                    <td style="border:1px solid black;text-align:center;background-color:#fafafa;padding:3px;white-space:nowrap;">WO</td>
                    @else
                    <td style="border:1px solid black;text-align:center;color:#a11d1d;padding:3px;white-space:nowrap;">A</td>
                    @endif
                    @endfor
        </tr>
        @endforeach

        <tr>
            <td style="border:1px solid black;text-align:center;background-color:#d97979;font-weight:bold;padding:3px;" colspan="{{$fromdate == $todate ? 6 : 5}}">Daily Attendance Count</td>
            @for ($i = 0; $i < $daysCount; $i++)
                <td style="border:1px solid black;text-align:center;background-color:#d97979;font-weight:bold;padding:3px;white-space:nowrap;">
                {{ $dailyCountArray[$i] == 0 ? "-" : $dailyCountArray[$i] }}</td>
                @endfor
        </tr>
    </tbody>
</table>

<style type="text/css">
    @media print {
        table {
            /* width: auto !important; */
            /* table-layout: auto !important; */
            page-break-inside: avoid;
        }

        td,
        th {
            padding: 3px !important;
            font-size: 11px !important;
        }
    }

    .report-info {
        font-weight: bold;
        font-size: 13px;
    }

    table {
        /* width: auto !important; */
        /* table-layout: auto !important; */
    }

    td,
    th {
        padding: 3px;
    }
</style>
@php
//dd('in this file' , "subtuype" , $flag);
$user = session('user');
//dd($siteClientNames , "siteClientNamess");
//dd($clientName , "clientname");
//dump($flagType , "flag");

@endphp
<table style="border-collapse:collapse;width:100%;justify-content:center;">
    <tbody>
        <tr style="background-color:#fcd7a9;">
            <th colspan="2" style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black;">Organization</th>
            @if($user->role_id != '0')
            <th colspan="2" style="border:1px solid black;text-align:center;"> Client Name</th>
            @endif
            <th colspan="2" style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black;"> Site Name</th>
            <th colspan="2" style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black;">Employee</th>
            <th colspan="3" style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black; background-color:#fcd7a9;">Date Range</th>
            <th colspan="4" style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black; background-color:#fcd7a9;">Report type</th>
            <th colspan="3" style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black; background-color:#fcd7a9;">Generated On</th>
        </tr>
        <tr style="background-color:#fcd7a9;">
            <td colspan="2" style="text-align: center;padding:5px;border: 1px solid black;">{{$companyName}}</td>
            @if( $flagType !== 'self')
            <td colspan="2" style="text-align: center; background-color:#fcd7a9;">{{ $clientName }}</td>
            @else
            <td colspan="2" style="text-align: center; background-color:#fcd7a9;">
                @foreach ($siteClientNames['client'] as $client )
                {{ $client }},
                @endforeach

            </td>
            @endif


            @if($subType !== 'Single Supervisor Attendance' && $flag !== 'self')
            <td colspan="2" style="border:1px solid black;text-align:center;">{{ $siteName }}</td>
            @else
            <td colspan="2" style="border:1px solid black;text-align:center;">
                @foreach ($siteClientNames['sites'] as $site )
                {{ $site }},
                @endforeach
            </td>
            @endif
            <td colspan="2" style="text-align: center;padding:5px;border: 1px solid black;"> <?php $guardName = App\Users::where('id', $guardId)->first(); ?>
                {{ $guardName->name }}
            </td>
            <td colspan="3" style="text-align: center;padding:5px;border: 1px solid black; background-color:#fcd7a9;">
                {{ date('d M Y', strtotime($fromDate)) . " to " . date('d M Y', strtotime($toDate)) }}
            </td>
            <td colspan="4" style="text-align: center;padding:5px;border: 1px solid black; background-color:#fcd7a9;">@if(isset($subType)){{$subType}}@else N/A @endif</td>
            <td colspan="3" style="text-align: center;padding:5px;border: 1px solid black; background-color:#fcd7a9;"> {{$generatedOn}} </td>
        </tr>
        @if($flag == 'pdf')
        <tr>
            <td colspan="12"></td>
        </tr>
        <tr>
            <td colspan="12"></td>
        </tr>
        <tr>
            <td colspan="12"></td>
        </tr>
        @else
        <tr>
            <td colspan="12"></td>
        </tr>
        @endif
        <tr style="background-color:#d97979;">
            <th colspan="2" style="border:1px solid black;text-align:center;">Sr. No.</th>
            <th colspan="2" style="border:1px solid black;text-align:center;">Date</th>
            <th colspan="2" style="border:1px solid black;text-align:center;">Attendance Status</th>
            <th colspan="2" style="border:1px solid black;text-align:center;">Site Name</th>
            <th colspan="2" style="border:1px solid black;text-align:center;">Punch-in Time</th>
            <th colspan="2" style="border:1px solid black;text-align:center;">Punch-out Time</th>
            <th colspan="2" style="border:1px solid black;text-align:center;">Total Working Hours (Manual)</th>
            <th colspan="2" style="border:1px solid black;text-align:center;">Total Working Hours (GPS)</th>
        </tr>

        <?php $srNo = 1;
        $leaveCount = 0;
        $weekoffCount = 0;
        $daysWorked = 0;
        $daysArray = [];
        $monthStart = date('d-m-Y', strtotime($fromDate));
        $monthStartt = date('Y-m-d', strtotime($fromDate));
        $daysArray[] = (object) [
            'date' => $monthStart,
            'format' => $monthStartt,
        ];

        $datee = $fromDate;
        $dateee = $fromDate;
        ?>

        @for ($i = 1; $i < $daysCount; $i++)
            <?php
            $srNo++;
            $datee = date('d-m-Y', strtotime('+1 day', strtotime($datee)));
            $dateee = date('Y-m-d', strtotime('+1 day', strtotime($dateee)));
            $daysArray[] = (object) [
                'date' => $datee,
                'format' => $dateee,
            ];
            ?>
            @endfor

            <?php
            $srNo = 1;
            // dd($data);
            // return;
            ?>

            @foreach ($daysArray as $index=> $val)
            @if (array_search($val->format, $datePresent) !== false)
            <?php $daysWorked++; ?>
            @foreach ($data[$val->format] as $item)
            <tr style="width: 100%">
                <td colspan="2" align="center" style="border:1px solid black;">{{ $srNo }}</td>
                <td colspan="2" align="center" style="border:1px solid black;">{{ $item->date }}</td>
                <td colspan="2" align="center" style="border:1px solid black;background-color: #8be08b;">P</td>
                <td colspan="2" align="center" style="border:1px solid black;">{{ $item->site_name }}</td>
                <td colspan="2" align="center" style="border:1px solid black;">{{ $item->entry_time }}</td>
                @if ($item->exit_time)
                <td colspan="2" align="center" style="border:1px solid black;">{{ $item->exit_time }}</td>
                @else
                <td colspan="2" align="center" style="border:1px solid black;">Not marked</td>
                @endif
                @if ($item->time_difference)
                <td colspan="2" align="center" style="border:1px solid black;">{{ $item->time_difference }}</td>
                @else
                <td colspan="2" align="center" style="border:1px solid black;">--</td>
                @endif
                @if ($item->gpsTime)
                <td colspan="2" align="center" style="border:1px solid black;">{{ $item->gpsTime }}</td>
                @else
                <td colspan="2" align="center" style="border:1px solid black;">--</td>
                @endif
                <?php $srNo++; ?>
            </tr>
            @endforeach
            @else
            <tr>
                <?php
                $leaves = App\Leave::where('user_id', $guardId)->whereBetween('fromDate', [$fromDate, $toDate])->whereBetween('toDate', [$fromDate, $toDate])->where('status', 'Approved')->get();
                $leaveDates = [];
                $leaveDateTypes = [];
                foreach ($leaves as $leave) {
                    $leaveStartDate = new DateTime($leave->fromDate);
                    $leaveEndDate = new DateTime($leave->toDate);
                    $interval = $leaveStartDate->diff($leaveEndDate);
                    $daysCountss = (int) $interval->format('%a');
                    $daysCountss = $daysCountss + 1;
                    $leaveDates[] = date('d-m-Y', strtotime($leave->fromDate));
                    $leaveDateTypes[] = $leave->type;
                    $incDate = date('d-m-Y', strtotime($leave->fromDate));
                    for ($i = 1; $i < $daysCountss; $i++) {
                        $incDate = date('d-m-Y', strtotime('+1 day', strtotime($incDate)));
                        $leaveDates[] = $incDate;
                        $leaveDateTypes[] =  $leave->type;
                        if ($key = array_search($incDate, $weekOffDates) !== false) {
                            unset($weekOffDates[$key]);
                            $leaveCount++;
                        }
                    }
                }
                ?>
                <td colspan="2" align="center" style="border:1px solid black;">{{ $srNo }}</td>
                <td colspan="2" align="center" style="border:1px solid black;">{{ $val->date }}</td>
                @if ((array_search($val->date, $leaveDates) !== false))
                <?php $index = array_search($val->date, $leaveDates) ?>
                <td colspan="2" align="center" style="border:1px solid black;background-color: #ffb7b7;">{{$leaveDateTypes[$index]}}</td>
                @elseif((array_search($val->date, $weekOffDates) === false))
                <td colspan="2" align="center" style="border:1px solid black;background-color: #ffb7b7;">A</td>
                @else
                <td colspan="2" align="center" style="border:1px solid black;background-color: #f3f1b4;">WO</td>
                <?php $weekoffCount++; ?>
                @endif
                <td colspan="2" align="center" style="border:1px solid black;">--</td>
                <td colspan="2" align="center" style="border:1px solid black;">--</td>
                <td colspan="2" align="center" style="border:1px solid black;">--</td>
                <td colspan="2" align="center" style="border:1px solid black;">--</td>
                <td colspan="2" align="center" style="border:1px solid black;">--</td>
                <?php $srNo++; ?>
            </tr>
            @endif
            @endforeach

            <tr style="background-color:#d97979;">
                <th colspan="2" style="border:1px solid black;font-weight:bold;">Total Days Worked</th>
                <th colspan="4" style="border:1px solid black;font-weight:bold;">{{ $daysWorked }} /
                    @if($daysWorked > ($daysCount - $weekoffCount - $leaveCount))
                    {{$daysWorked}}
                    @else
                    {{ $daysCount - $weekoffCount - $leaveCount}}
                    @endif
                </th>
                <th colspan="4" style="border:1px solid black;font-weight:bold;">{{ $actualTimeformat }}</th>
                <th colspan="6" style="border:1px solid black;font-weight:bold;" align="center">{{ $gpsTimeformat }}</th>
            </tr>
    </tbody>
</table>
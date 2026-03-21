@include('includes.report-header')
@php
$guardName = App\SiteAssign::where('user_id',$supervisorId )->where('role_id', 2)->first();
    // dd($data , $supervisorId,"ids")
// dd($$endDate , $$startDate , "all dates");
@endphp
<div class="row stickyTable">
    <div class="col-md-11">
        <table class="table">
            <thead style="min-width: 70px;">
                <tr style="background-color:#fcd7a9;">
                    <th colspan="3" style="border:1px solid black;text-align:center;">Organization</th>
                    <th colspan="3" style="border:1px solid black;text-align:center;">Name of Supervisor</th>
                    <th colspan="3" style="border:1px solid black;text-align:center;">Date Range</th>
                    <th colspan="3" style="border:1px solid black;text-align:center;">Report type</th>
                    <th colspan="3" style="border:1px solid black;text-align:center;">Generated On</th>
                </tr>
            </thead>
            <tbody style="min-width: 70px;">
                <tr>
                    <td colspan="3" style="border:1px solid black;text-align:center;">{{ $companyName }}</td>
                    <td colspan="3" style="border:1px solid black;text-align:center;">{{ $guardName->user_name }}</td>
                    <td colspan="3" style="border:1px solid black;text-align:center;">{{ date('d M Y', strtotime($startDate)) . " to " . date('d M Y', strtotime($endDate)) }}</td>
                    <td colspan="3" style="border:1px solid black;text-align:center;">@if(isset($subType)) {{$subType}} @else N/A @endif</td>
                    <td colspan="3" style="border:1px solid black;text-align:center;"><?php echo date("d M Y"); ?></td>
                </tr>
                <tr><td colspan="21" style="padding: 10px;"></td></tr>
            </tbody>
        </table>
    </div>
</div>


<div class="row">
    <div class="col-md-12">
        <table id="empTable" class="table">
            <thead>
                <tr>
                    <th style="background-color: #d97979;font-weight:bold;text-align:center; border:1px solid black;">Sr. No.</th>
                    <th style="background-color: #d97979;font-weight:bold;text-align:center; border:1px solid black;">Date</th>
                    <th style="background-color: #d97979;font-weight:bold;text-align:center; border:1px solid black;">Attendance Status</th>
                    <th style="background-color: #d97979;font-weight:bold;text-align:center; border:1px solid black;">Site Name</th>
                    <th style="background-color: #d97979;font-weight:bold;text-align:center; border:1px solid black;">Punch-in Time</th>
                    <th style="background-color: #d97979;font-weight:bold;text-align:center; border:1px solid black;">Punch-out Time</th>
                    <th style="background-color: #d97979;font-weight:bold;text-align:center; border:1px solid black;">Total Working Hours (Manual)</th>
                    <th style="background-color: #d97979;font-weight:bold;text-align:center; border:1px solid black;">Total Working Hours (GPS)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $srNo = 1;
                $daysWorked = 0;
                $daysArray = [];
                $monthStart = date('Y-m-d', strtotime($startDate));
                $monthStartt = date('Y-m-d', strtotime($startDate));
                $daysArray[] = (object)[
                    'date' => $monthStart,
                    'format' => $monthStartt,
                ];

                $datee = $startDate;
                for ($i = 1; $i < $daysCount; $i++) {
                    $datee = date('Y-m-d', strtotime('+1 day', strtotime($datee)));
                    $daysArray[] = (object)[
                        'date' => $datee,
                        'format' => $datee,
                    ];
                }

                // Get leaves data
                $leaves = App\Leave::where('user_id', $supervisorId)
                    ->whereBetween('fromDate', [$startDate, $endDate])
                    ->whereBetween('toDate', [$startDate, $endDate])
                    ->where('status', 'Approved')
                    ->where('role_id', 2)
                    ->get();

                $leaveDates = [];
                $leaveDateTypes = [];
                $leaveCount = 0;

                foreach ($leaves as $leave) {
                    $leaveStartDate = new DateTime($leave->$startDate);
                    $leaveEndDate = new DateTime($leave->$endDate);
                    $interval = $leaveStartDate->diff($leaveEndDate);
                    $daysCountss = (int)$interval->format('%a') + 1;
                    
                    $currentDate = date('d-m-Y', strtotime($leave->$startDate));
                    for ($i = 0; $i < $daysCountss; $i++) {
                        if ($i > 0) {
                            $currentDate = date('Y-m-d', strtotime('+1 day', strtotime($currentDate)));
                        }
                        $leaveDates[] = $currentDate;
                        $leaveDateTypes[] = $leave->type;
                        
                        if (($key = array_search($currentDate, $weekOffDates)) !== false) {
                            unset($weekOffDates[$key]);
                            $leaveCount++;
                        }
                    }
                }
                ?>

                @foreach ($daysArray as $val)
                @php
                $userPresent = false;
                $attendanceInfo = null;
                
                if (isset($data[$supervisorId])) {
                    // Find matching attendance record
                    foreach ($data[$supervisorId] as $record) {
                        // dd($val->date ,$record['date'] );
                        if ($record['date'] === $val->date) {
                            $userPresent = true;
                            $attendanceInfo = $record;
                            break;
                        }
                    }
                }
                else {
            // dd('hellp'); 
            // dd( $data , $supervisorId,$data[$supervisorId] );

                }
            @endphp

                    @if ($userPresent && $attendanceInfo )

                        <?php $daysWorked++;  ?>
                        <tr style="border:1px solid black; text-align:center;">
                            <td align="center" style="border:1px solid black; text-align:center;" >{{ $srNo }}</td>
                            <td align="center" style="border:1px solid black; text-align:center;" >{{ $val->date }}</td>
                            <td align="center" style="border:1px solid black; text-align:center; color:#20B243;" >P</td>
                            <td>{{ $attendanceInfo['site_name'] ?? '--' }}</td>
                            <td align="center" style="border:1px solid black; text-align:center;" >{{ $attendanceInfo['entry_time'] ?? '--' }}</td>
                            <td align="center" style="border:1px solid black; text-align:center;" >{{ $attendanceInfo['exit_date_time'] ?? 'Not marked' }}</td>
                            <td align="center" style="border:1px solid black; text-align:center;" >{{ $attendanceInfo['time_difference'] ?? '--' }}</td>
                            <td align="center" style="border:1px solid black; text-align:center;" >{{ $attendanceInfo['gpsTime'] ?? '--' }}</td>
                        </tr>
                    @else
                    <tr  style="border:1px solid black; text-align:center;">
                        <td align="center" style="border:1px solid black; text-align:center;" >{{ $srNo }}</td>
                        <td align="center" style="border:1px solid black; text-align:center;">{{ date('d-m-Y', strtotime($val->date)) }}</td>
                        @if (in_array($val->date, $leaveDates))
                            <?php $leaveIndex = array_search($val->date, $leaveDates); ?>
                            <td style="color:red;text-align:center; border:1px solid black; text-align:center;">{{ $leaveDateTypes[$leaveIndex] }}</td>
                        @elseif (!in_array($val->date, $weekOffDates))
                            <td align="center" style="color:red; border:1px solid black; text-align:center;">A</td>
                        @else
                            <td align="center" style="color:#8B8000;border:1px solid black; text-align:center;">WO</td>
                        @endif
                        <td align="center" style="border:1px solid black; text-align:center;">--</td>
                        <td align="center" style="border:1px solid black; text-align:center;">--</td>
                        <td align="center" style="border:1px solid black; text-align:center;">--</td>
                        <td align="center" style="border:1px solid black; text-align:center;">--</td>
                        <td align="center" style="border:1px solid black; text-align:center;">--</td>
                    </tr>
                    @endif
                    <?php $srNo++; ?>
                @endforeach

                <tr>
                    <th colspan="2" style="background-color: #d97979;text-align:center; border:1px solid black; text-align:center;">Total Days Worked</th>
                    <th colspan="4" style="background-color: #d97979; border:1px solid black; text-align:center;">
                        {{ $daysWorked }} / {{ $daysCount - count($weekOffDates ?? []) - $leaveCount }}
                    </th>
                    <th colspan="1" style="background-color: #d97979;text-align:center; border:1px solid black; text-align:center;">{{ $actualTimeformat }}</th>
                    <th colspan="1" style="background-color: #d97979;text-align:center; border:1px solid black; text-align:center;">{{ $gpsTimeformat }}</th>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@include('includes.report-footer')
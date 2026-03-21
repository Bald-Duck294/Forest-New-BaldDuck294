@include('includes.report-header')
@php
$guardName = App\SiteAssign::where('user_id',$supervisorId )->where('role_id', 2)->first();
@endphp
<div class="row stickyTable">
    <div class="col-md-11">
        <table class="table">
            <thead style="min-width: 70px;">
                <tr style="background-color:#fcd7a9;">
                    <th colspan="3" style="text-align: center;">Organization</th>
                    <th colspan="3" style="text-align: center;">Name of Supervisor</th>
                    <th colspan="3" style="text-align: center;">Date Range</th>
                    <th colspan="3" style="text-align: center;">Report type</th>
                    <th colspan="3" style="text-align: center;">Generated On</th>
                </tr>
            </thead>
            <tbody style="min-width: 70px;">
                <tr>
                    <td colspan="3" style="text-align: center;">{{ $companyName }}</td>
                    <td colspan="3" style="text-align: center;">{{ $guardName->user_name }}</td>
                    <td colspan="3" style="text-align: center;">{{ date('d M Y', strtotime($fromDate)) . " to " . date('d M Y', strtotime($toDate)) }}</td>
                    <td colspan="3" style="text-align: center;">@if(isset($subType)) {{$subType}} @else N/A @endif</td>
                    <td colspan="3" style="text-align: center;"><?php echo date("d M Y"); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="col-md-1" style="text-align: center;margin-top: -10px;">
        <div class="row">
            <form method="get" action="{{route('downloadUserAttendanceReport')}}" target="_blank">
                @csrf
                <input type="hidden" name="supervisorId" value={{$supervisorId}} />
                <input type="hidden" name="toDate" value={{$toDate}} />
                <input type="hidden" name="fromDate" value={{$fromDate}} />
                <input type="hidden" name="subType" value={{$subType}} />
                <div class="col-md-12" style="display: flex;justify-content: center;">
                    <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true" style="border: 1px solid grey;padding: 3px 8px;border-radius: 50%;">×</button>
                </div>
                <div class="col-md-12" style="padding: 3px;display: flex;justify-content: center;">
                    <button type="submit" class="btn btn-danger btn-border btn-round" name="xlsx" value="pdf"><i class="la la-download" title="pdf"></i>PDF</button>
                </div>
                <div class="col-md-12" style="padding: 3px;display: flex;justify-content: center;">
                    <button type="submit" class="btn btn-success btn-border btn-round" name="xlsx" value="xlsx"><i class="la la-download" title="excel"></i>Excel</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-12" style="padding: 3px;display: flex;justify-content: start; margin:1rem 8px;">
        @if (isset($datePresent) && count($datePresent) > 0)
        <a href="{{ route('attendanceMap', ['guardId' => $guardId, 'fromDate' => $fromDate, 'toDate' => $toDate]) }}" target="_blank">
            <button class="btn btn-danger btn-border btn-round" name="xlsx" value="pdf">
                <i class="la la-map"></i> View Map
            </button>
        </a>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <table id="empTable" class="table">
            <thead>
                <tr>
                    <th style="background-color: #d97979;font-weight:bold;text-align:center;">Sr. No.</th>
                    <th style="background-color: #d97979;font-weight:bold;text-align:center;">Date</th>
                    <th style="background-color: #d97979;font-weight:bold;text-align:center;">Attendance Status</th>
                    <th style="background-color: #d97979;font-weight:bold;text-align:center;">Site Name</th>
                    <th style="background-color: #d97979;font-weight:bold;text-align:center;">Punch-in Time</th>
                    <th style="background-color: #d97979;font-weight:bold;text-align:center;">Punch-out Time</th>
                    <th style="background-color: #d97979;font-weight:bold;text-align:center;">Total Working Hours (Manual)</th>
                    <th style="background-color: #d97979;font-weight:bold;text-align:center;">Total Working Hours (GPS)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $srNo = 1;
                $daysWorked = 0;
                $daysArray = [];
                $monthStart = date('Y-m-d', strtotime($fromDate));
                $monthStartt = date('Y-m-d', strtotime($fromDate));
                $daysArray[] = (object)[
                    'date' => $monthStart,
                    'format' => $monthStartt,
                ];

                $datee = $fromDate;
                for ($i = 1; $i < $daysCount; $i++) {
                    $datee = date('Y-m-d', strtotime('+1 day', strtotime($datee)));
                    $daysArray[] = (object)[
                        'date' => $datee,
                        'format' => $datee,
                    ];
                }

                // Get leaves data
                $leaves = App\Leave::where('user_id', $supervisorId)
                    ->whereBetween('fromDate', [$fromDate, $toDate])
                    ->whereBetween('toDate', [$fromDate, $toDate])
                    ->where('status', 'Approved')
                    ->where('role_id', 2)
                    ->get();

                $leaveDates = [];
                $leaveDateTypes = [];
                $leaveCount = 0;

                foreach ($leaves as $leave) {
                    $leaveStartDate = new DateTime($leave->fromDate);
                    $leaveEndDate = new DateTime($leave->toDate);
                    $interval = $leaveStartDate->diff($leaveEndDate);
                    $daysCountss = (int)$interval->format('%a') + 1;
                    
                    $currentDate = date('d-m-Y', strtotime($leave->fromDate));
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
                    // dd( $data , $supervisorId,$data[$supervisorId] );
                    foreach ($data[$supervisorId] as $record) {
                        if ($record['date'] === $val->date) {
                            $userPresent = true;
                            $attendanceInfo = $record;
                            break;
                        }
                    }
                }
            @endphp

                    @if ($userPresent && $attendanceInfo )

                        <?php $daysWorked++;  ?>
                        <tr>
                            <td align="center">{{ $srNo }}</td>
                            <td align="center">{{ $val->date }}</td>
                            <td align="center"  style="background-color: #8be08b;">P</td>
                            <td>{{ $attendanceInfo['site_name'] ?? '--' }}</td>
                            <td align="center">{{ $attendanceInfo['entry_time'] ?? '--' }}</td>
                            <td align="center">{{ $attendanceInfo['exit_date_time'] ?? 'Not marked' }}</td>
                            <td align="center">{{ $attendanceInfo['time_difference'] ?? '--' }}</td>
                            <td align="center">{{ $attendanceInfo['gpsTime'] ?? '--' }}</td>
                        </tr>
                    @else
                    <tr>
                        <td align="center">{{ $srNo }}</td>
                        <td align="center">{{ date('d-m-Y', strtotime($val->date)) }}</td>
                        @if (in_array($val->date, $leaveDates))
                            <?php $leaveIndex = array_search($val->date, $leaveDates); ?>
                            <td style="color:red;text-align:center;">{{ $leaveDateTypes[$leaveIndex] }}</td>
                        @elseif (!in_array($val->date, $weekOffDates))
                            <td align="center" style="background-color: #ffcfcf;">A</td>
                        @else
                            <td align="center" style="color:#8B8000;">WO</td>
                        @endif
                        <td align="center">--</td>
                        <td align="center">--</td>
                        <td align="center">--</td>
                        <td align="center">--</td>
                        <td align="center">--</td>
                    </tr>
                    @endif
                    <?php $srNo++; ?>
                @endforeach

                <tr>
                    <th colspan="2" style="background-color: #d97979;text-align:center;">Total Days Worked</th>
                    <th colspan="4" style="background-color: #d97979">
                        {{ $daysWorked }} / {{ $daysCount - count($weekOffDates ?? []) - $leaveCount }}
                    </th>
                    <th colspan="1" style="background-color: #d97979;text-align:center;">{{ $actualTimeformat }}</th>
                    <th colspan="1" style="background-color: #d97979;text-align:center;">{{ $gpsTimeformat }}</th>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@include('includes.report-footer')
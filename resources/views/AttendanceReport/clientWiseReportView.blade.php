@php
    // No layout extension! This is raw HTML to be injected into the modal.
    $user = session('user') ?? auth()->user();
    $index_y = 0;
@endphp

<div class="w-100">
    {{-- Header Section (Organization, Date, Export Buttons) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3"
        style="border-bottom: 1px dashed var(--border-color);">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--text-main); font-size: 1.5rem; text-transform: capitalize;">
                <i class="bi bi-file-earmark-bar-graph me-2" style="color: var(--sapphire-primary);"></i>
                @if (isset($subType))
                    {{ $subType }}
                @else
                    Client Wise Report
                @endif
            </h4>
            <div class="d-flex flex-wrap gap-3 text-muted mt-2" style="font-size: 0.85rem;">
                <div><strong style="color: var(--text-main);">Org:</strong> {{ $companyName }}</div>
                @if ($user->role_id != 2)
                    <div><strong style="color: var(--text-main);">Client/Range:</strong> {{ $clientName }}</div>
                @endif
                <div><strong style="color: var(--text-main);">Date:</strong> {{ $date }}</div>
                <div><strong style="color: var(--text-main);">Generated:</strong> {{ $generatedOn }}</div>
            </div>
        </div>

        {{-- Export Buttons --}}
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <form method="post" action="{{ route('downloadClientWiseReport') }}" target="_blank"
                class="d-flex gap-2 m-0">
                @csrf
                <input type="hidden" name="fromdate" value="{{ $fromdate }}" />
                <input type="hidden" name="todate" value="{{ $todate }}" />
                <input type="hidden" name="guard" value="{{ $guard }}" />
                <input type="hidden" name="attendanceSubType" value="{{ $attendanceSubType }}" />
                <input type="hidden" name="subType" value="{{ $subType }}" />
                <input type="hidden" name="client" value="{{ $client }}" />

                <button type="submit" class="btn text-white shadow-sm d-flex align-items-center gap-2" name="xlsx"
                    value="pdf"
                    style="background-color: #ef4444; border-radius: 8px; font-weight: 600; padding: 8px 16px;">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </button>
                <button type="submit" class="btn text-white shadow-sm d-flex align-items-center gap-2" name="xlsx"
                    value="xlsx"
                    style="background-color: #10b981; border-radius: 8px; font-weight: 600; padding: 8px 16px;">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </button>
            </form>
        </div>
    </div>

    {{-- The Data Table --}}
    <div class="table-responsive" style="overflow-x: auto;">
        <table class="table mb-0">
            <?php
            $datee = $startDatee;
            $dailyCountArray = [];
            ?>
            <thead>
                <tr>
                    <th>Sr No</th>
                    <th>Employee Name</th>
                    <th>Site/Beat</th>
                    <th class="text-center date-col">Days Worked</th>

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

                    <th class="text-center date-col">{{ $day }}-{{ $fdate }}</th>

                    @for ($i = 1; $i < $daysCount; $i++)
                        <?php
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
                        <th class="text-center date-col">{{ $day }}-{{ $fdate }}</th>
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
                        <td class="text-center fw-bold text-muted">{{ $srNo }}</td>
                        <td class="fw-bold">{{ $names[$key][0] }}</td>

                        @if (isset($sites[$key]))
                            <td>{{ $sites[$key][0]['site'] }}</td>
                        @else
                            <td class="text-muted">NA</td>
                        @endif

                        <td class="text-center fw-bold">{{ $acount }}</td>

                        @for ($i = 0; $i < $daysCount; $i++)
                            @php
                                $index = array_search($daysArray[$i], $param);
                            @endphp

                            @if ($index !== false)
                                @if ($attendanceSubType == 'EmployeeAttendanceReportwithSite')
                                    <td class="text-center date-col fw-bold" style="color: #10b981;">
                                        @if (isset($sites[$key][0]) && isset($sites[$key][0]['site']))
                                            {{ $sites[$key][0]['site'] }}
                                        @else
                                            P
                                        @endif
                                    </td>
                                @elseif($attendanceSubType == 'EmployeeAttendanceReport')
                                    <td class="text-center date-col fw-bold" style="color: #10b981;">P</td>
                                @elseif($attendanceSubType == 'EmployeeAttendanceReportwithHours')
                                    @php
                                        if ($hours[$key][$index] != null) {
                                            $arr = explode(' ', $hours[$key][$index]);
                                            $minutes = (int) $arr[0] * 60 + (int) (@$arr[3] ?: 0);
                                            $color = $minutes < 480 ? '#f59e0b' : '#10b981';
                                        } else {
                                            $minutes = 0;
                                            $color = '#3b82f6';
                                        }
                                    @endphp
                                    <td class="text-center date-col fw-bold" style="color: {{ $color }}">
                                        @if ($minutes == 0)
                                            Exit Unmarked
                                        @else
                                            {{ $hours[$key][$index] }}
                                        @endif
                                    </td>
                                @else
                                    <td class="text-center date-col">P</td>
                                @endif
                            @elseif($days && array_search(date('l', strtotime($daysArray[$i])), $days) !== false)
                                <td class="text-center date-col">WO</td>
                            @else
                                <td class="text-center date-col">A</td>
                            @endif

                            @php $index_y++; @endphp
                        @endfor
                    </tr>
                @endforeach
            </tbody>

            {{-- Footer Summary Row --}}
            <tfoot>
                <tr style="background-color: var(--table-hover) !important;">
                    <td class="fw-bold text-end pe-4" colspan="4">Daily Attendance Count:</td>
                    @for ($i = 0; $i < $daysCount; $i++)
                        <td class="text-center date-col fw-bold" style="color: var(--text-main);">
                            {{ isset($dailyCountArray[$i]) ? $dailyCountArray[$i] : '0' }}
                        </td>
                    @endfor
                </tr>
            </tfoot>
        </table>
    </div>
</div>

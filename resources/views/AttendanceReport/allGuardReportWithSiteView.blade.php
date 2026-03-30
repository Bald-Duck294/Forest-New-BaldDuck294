@php
    // No layout extension! This is just raw HTML to be injected into the modal.
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
                    Guard Attendance Report
                @endif
            </h4>
            <div class="d-flex flex-wrap gap-3 text-muted mt-2" style="font-size: 0.85rem;">
                <div><strong style="color: var(--text-main);">Org:</strong> {{ $companyName }}</div>
                <div><strong style="color: var(--text-main);">Date:</strong> {{ $date }}</div>
                <div><strong style="color: var(--text-main);">Generated:</strong> {{ $generatedOn }}</div>
            </div>
        </div>

        {{-- Export Buttons --}}
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <form method="post" action="{{ route('downloadAllGuardAttendance') }}" target="_blank"
                class="d-flex gap-2 m-0">
                @csrf
                <input type="hidden" name="fromdate" value="{{ $fromdate }}" />
                <input type="hidden" name="todate" value="{{ $todate }}" />
                <input type="hidden" name="client" value={{ $client }} />
                <input type="hidden" name="attendanceSubType" value="{{ $attendanceSubType }}" />
                <input type="hidden" name="subType" value="{{ $subType }}" />
                <input type="hidden" name="geofences" value="{{ $geofences }}" />
                <input type="hidden" name="guard" value={{ $guard }} />

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

            @if ($fromdate == $todate)
                <a href="{{ route('attendanceMap', ['guardId' => 0, 'fromDate' => $fromdate, 'toDate' => $todate]) }}"
                    target="_blank" class="btn btn-primary shadow-sm d-flex align-items-center gap-2"
                    style="background-color: var(--sapphire-primary); border: none; border-radius: 8px; font-weight: 600; padding: 8px 16px;">
                    <i class="bi bi-map"></i> View Map
                </a>
            @endif
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
                    <th>Client/Range</th>
                    <th>Site/Beat</th>
                    @if ($fromdate == $todate)
                        <th>Location</th>
                    @endif
                    <th class="text-center date-col">Days Worked</th>

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

                    <th class="text-center  date-col">{{ $day }}-{{ $fdate }}</th>
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
                        <th class="text-center  date-col">{{ $day }}-{{ $fdate }}</th>
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
                        <td class="text-center date-col fw-bold text-muted">{{ $srNo }}</td>
                        <td class="fw-bold">{{ $names[$key][0] }}</td>

                        @if (isset($sites[$key]) && $sites[$key][0]['client'] != null)
                            <td>{{ $sites[$key][0]['client'] }}</td>
                            <td>{{ $sites[$key][0]['site'] }}</td>
                        @elseif(isset($supervisorSites[$key]))
                            <td>
                                @foreach ($supervisorSites[$key]['client'] as $clientkey => $val)
                                    {{ $val }} @if ($clientkey != array_key_last($supervisorSites[$key]['client']))
                                        ,
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                @if (!empty($supervisorSites[$key]['site']))
                                    @foreach ($supervisorSites[$key]['site'] as $sitekey => $val)
                                        {{ !empty($val) ? $val : '-' }}@if ($sitekey !== array_key_last($supervisorSites[$key]['site']))
                                            ,
                                        @endif
                                    @endforeach
                                @else
                                    -
                                @endif
                            </td>
                        @else
                            <td class="text-muted">NA</td>
                            <td class="text-muted">NA</td>
                        @endif

                        @if ($fromdate == $todate)
                            @if (isset($attendSites[$key]))
                                @if ($attendSites[$key][0] == 'Current Location')
                                    <td class="fw-bold" style="color: #10b981;">ON SITE</td>
                                @else
                                    <td>{{ $attendSites[$key][0] }}</td>
                                @endif
                            @else
                                <td class="text-muted fst-italic">Not Marked</td>
                            @endif
                        @endif

                        <td class="text-center date-col fw-bold">{{ $acount }}</td>

                        @for ($i = 0; $i < $daysCount; $i++)
                            @php
                                $index = array_search($daysArray[$i], $param);
                            @endphp

                            @if ($index !== false)
                                @if ($attendanceSubType == 'EmployeeAttendanceReport')
                                    <td class="text-center date-col">P</td>
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
                                @elseif($attendanceSubType == 'EmployeeAttendanceReportwithSite')
                                    <td class="text-center date-col fw-bold" style="color: #10b981;">
                                        @if (isset($attendSites[$key][$index]) && $attendSites[$key][$index] !== 'Current Location')
                                            {{ $attendSites[$key][$index] }}
                                        @else
                                            On Site
                                        @endif
                                    </td>
                                @else
                                    <td class="text-center date-col fw-bold" style="color: #10b981;">
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
                                <td class="text-center date-col">WO</td>
                            @else
                                <td class="text-center date-col">A</td>
                            @endif
                        @endfor
                    </tr>
                @endforeach
            </tbody>

            {{-- Footer Summary Row --}}
            <tfoot>
                <tr style="background-color: var(--table-hover) !important;">
                    <td class="fw-bold text-end pe-4" colspan="<?= $fromdate == $todate ? '6' : '5' ?>">
                        Daily Attendance Count:
                    </td>
                    @for ($i = 0; $i < $daysCount; $i++)
                        <td class="text-center date-col fw-bold" style="color: var(--text-main);">
                            {{ $dailyCountArray[$i] == 0 ? '0' : $dailyCountArray[$i] }}
                        </td>
                    @endfor
                </tr>
            </tfoot>
        </table>
    </div>
</div>

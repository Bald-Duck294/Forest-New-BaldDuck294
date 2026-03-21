@php
    $user = session('user');
@endphp
@include('includes.report-header')

<div class="report-container p-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm overflow-hidden">
                <table class="table table-sm mb-0 info-table">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-3 py-2 text-muted small text-uppercase">Organization</th>
                            @if($user->role_id !== 2)
                                <th class="px-3 py-2 text-muted small text-uppercase">Client / Range</th>
                            @endif
                            <th class="px-3 py-2 text-muted small text-uppercase">Site / Beat</th>
                            <th class="px-3 py-2 text-muted small text-uppercase">Date Range</th>
                            <th class="px-3 py-2 text-muted small text-uppercase">Report Type</th>
                            <th class="px-3 py-2 text-muted small text-uppercase">Generated On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="px-3 py-2 fw-bold">{{ $companyName }}</td>
                            @if($user->role_id !== 2)
                                <td class="px-3 py-2 fw-bold">{{ $clientName }}</td>
                            @endif
                            <td class="px-3 py-2 fw-bold">{{ $siteName }}</td>
                            <td class="px-3 py-2 fw-bold text-primary">{{ $date }}</td>
                            <td class="px-3 py-2 fw-bold"><span
                                    class="badge bg-soft-primary text-primary">{{ $subType ?? 'N/A' }}</span></td>
                            <td class="px-3 py-2 fw-bold text-muted small">{{ $generatedOn }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-2 d-flex justify-content-end gap-2">
            <form method="post" action="{{route('downloadSiteWiseGuardReport')}}" target="_blank" class="d-flex gap-2">
                @csrf
                <input type="hidden" name="fromDate" value="{{$fromDate}}" />
                <input type="hidden" name="toDate" value="{{$toDate}}" />
                <input type="hidden" name="client" value="{{$client}}" />
                <input type="hidden" name="attendanceSubType" value="{{$attendanceSubType}}" />
                <input type="hidden" name="subType" value="{{$subType}}" />
                <input type="hidden" name="geofences" value="{{$geofences}}" />
                <input type="hidden" name="siteName" value="{{$siteName}}" />
                <input type="hidden" name="clientName" value="{{$clientName}}" />

                <button type="submit" name="xlsx" value="pdf"
                    class="btn btn-icon btn-outline-danger btn-round shadow-sm" title="Download PDF">
                    <!-- <i class="la la-file-pdf"></i> -->
                    PDF
                </button>
                <button type="submit" name="xlsx" value="xlsx"
                    class="btn btn-icon btn-outline-success btn-round shadow-sm" title="Download Excel">
                    <!-- <i class="la la-file-excel"></i> -->
                    EXCEL
                </button>
                <button type="button" class="btn btn-icon btn-outline-secondary btn-round shadow-sm"
                    data-bs-dismiss="modal" title="Close">
                    <i class="la la-times"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive attendance-container" style="max-height: 65vh;">
            <table class="table table-hover table-bordered mb-0 attendance-report-table">
                <thead class="sticky-top bg-white shadow-sm" style="z-index: 10;">
                    <tr class="bg-light">
                        <th class="text-center bg-light fw-bold" style="width: 50px;">#</th>
                        <th class="bg-light fw-bold" style="min-width: 180px;">Employee</th>
                        <th class="text-center bg-light fw-bold" style="width: 80px;">Worked</th>
                        @php
                            $datee = $startDatee;
                            $daysArray = [];
                            $dailyCountArray = [];
                        @endphp

                        @for ($i = 0; $i < $daysCount; $i++)
                            @php
                                $currentDate = date('d-m-Y', strtotime("+$i day", strtotime($startDatee)));
                                $dateFormat = date('Y-m-d', strtotime($currentDate));
                                $fdate = date('d', strtotime($currentDate));
                                $dayName = date('D', strtotime($currentDate));
                                $daysArray[] = $currentDate;

                                if (isset($attendCount[$dateFormat])) {
                                    $dailyCountArray[] = count($attendCount[$dateFormat]);
                                } else {
                                    $dailyCountArray[] = 0;
                                }
                            @endphp
                            <th class="text-center fw-bold date-column">
                                <span class="small text-muted">{{ $dayName }}</span><br>{{ $fdate }}
                            </th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $key => $param)
                        @php
                            $days = $weekoffs[$key] ? json_decode($weekoffs[$key][0], true) : [];
                            $acount = count($param);
                        @endphp
                        <tr>
                            <td class="text-center text-muted small">{{ $loop->iteration }}</td>
                            <td class="fw-bold employee-col">{{ $names[$key][0] }}</td>
                            <td class="text-center"><span class="badge bg-soft-info text-info">{{ $acount }}</span></td>
                            @foreach($daysArray as $date)
                                @php
                                    $index = array_search($date, $param);
                                @endphp
                                @if($index !== false)
                                    <td class="text-center attendance-cell present-cell">
                                        @if($attendanceSubType == 'EmployeeAttendanceReportwithSite')
                                            <span class="small fw-bold">{{ $sites[$key][$index]['site'] ?? 'P' }}</span>
                                        @elseif($attendanceSubType == 'EmployeeAttendanceReport')
                                            <span class="present-mark">P</span>
                                        @elseif($attendanceSubType == 'EmployeeAttendanceReportwithHours')
                                            @php
                                                $minutes = 0;
                                                $color = 'text-primary';
                                                if (isset($hours[$key][$index]) && $hours[$key][$index] != null) {
                                                    $arr = explode(' ', $hours[$key][$index]);
                                                    $minutes = (int) $arr[0] * 60 + (int) ($arr[3] ?? 0);
                                                    $color = $minutes < 480 ? 'text-warning' : 'text-success';
                                                }
                                            @endphp
                                            <span class="small fw-bold {{ $color }}">
                                                {{ $minutes == 0 ? 'Ex Unmarked' : $hours[$key][$index] }}
                                            </span>
                                        @else
                                            <span class="present-mark">P</span>
                                        @endif
                                    </td>
                                @elseif($days && in_array(date('l', strtotime($date)), $days))
                                    <td class="text-center attendance-cell wo-cell">WO</td>
                                @else
                                    <td class="text-center attendance-cell absent-cell">A</td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="sticky-bottom bg-light shadow-sm" style="z-index: 5;">
                    <tr class="fw-bold">
                        <td colspan="3" class="text-end px-3">Daily Count</td>
                        @foreach($dailyCountArray as $count)
                            <td class="text-center bg-soft-primary">{{ $count }}</td>
                        @endforeach
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@include('includes.report-footer')

<style>
    .report-container {
        background: #f8fafc;
    }

    .info-table td {
        font-size: 0.9rem;
    }

    .bg-soft-primary {
        background: #e0e7ff;
    }

    .bg-soft-info {
        background: #e0f2fe;
    }

    .attendance-report-table th,
    .attendance-report-table td {
        padding: 0.75rem !important;
        border-color: #e2e8f0 !important;
    }

    .date-column {
        min-width: 60px;
        font-size: 0.8rem;
    }

    .employee-col {
        color: #0f172a;
        white-space: nowrap;
    }

    .attendance-cell {
        vertical-align: middle !important;
        transition: background 0.2s;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .present-cell {
        color: #059669;
    }

    .absent-cell {
        color: #dc2626;
        background: #fef2f2;
    }

    .wo-cell {
        color: #94a3b8;
        background: #f8fafc;
    }

    .present-mark {
        font-weight: 800;
    }

    .bg-soft-primary {
        background: #eff6ff !important;
        color: #2563eb !important;
    }

    .btn-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .attendance-container::-webkit-scrollbar {
        height: 8px;
        width: 8px;
    }

    .attendance-container::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    .attendance-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    .attendance-container::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
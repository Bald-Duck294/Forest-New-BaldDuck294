@php
    // No layout extension! This is just raw HTML to be injected into the modal.
@endphp

<div class="w-100">
    {{-- Header Section (Organization, Date, Export Buttons) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3"
        style="border-bottom: 1px dashed var(--border-color);">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--text-main); font-size: 1.5rem; text-transform: capitalize;">
                <i class="bi bi-person-badge me-2" style="color: var(--sapphire-primary);"></i>
                @if (isset($subType))
                    {{ $subType }}
                @else
                    Guard Report
                @endif
            </h4>
            <div class="d-flex flex-wrap gap-3 text-muted mt-2" style="font-size: 0.85rem;">
                <div><strong style="color: var(--text-main);">Org:</strong> {{ $companyName }}</div>
                @if ($user->role_id != '0')
                    <div>
                        <strong style="color: var(--text-main);">Client/Range:</strong>
                        @if ($flag !== 'self')
                            {{ $clientName }}
                        @else
                            @foreach ($siteClientsNames['client'] as $client)
                                {{ $client }}{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        @endif
                    </div>
                @endif
                <div>
                    <strong style="color: var(--text-main);">Site/Beat:</strong>
                    @if ($subType !== 'Single Supervisor Attendance' && $flag !== 'self')
                        {{ $siteName }}
                    @else
                        @foreach ($siteClientsNames['sites'] as $site)
                            {{ $site }}{{ !$loop->last ? ', ' : '' }}
                        @endforeach
                    @endif
                </div>
                <div>
                    <strong style="color: var(--text-main);">Employee:</strong>
                    @php $guardName = App\Users::where('id', $guardId)->first(); @endphp
                    <span class="text-primary fw-bold">{{ $guardName->name ?? 'N/A' }}</span>
                </div>
                <div><strong style="color: var(--text-main);">Date:</strong>
                    {{ date('d M Y', strtotime($fromDate)) }} - {{ date('d M Y', strtotime($toDate)) }}</div>
                <div><strong style="color: var(--text-main);">Generated:</strong> {{ $generatedOn }}</div>
            </div>
        </div>

        {{-- Export Buttons --}}
        <div class="d-flex gap-2 mt-3 mt-md-0">
            @if (isset($datePresent) && count($datePresent) > 0)
                <a href="{{ route('attendanceMap', ['guardId' => $guardId, 'fromDate' => $fromDate, 'toDate' => $toDate]) }}"
                    target="_blank" class="btn btn-outline-danger shadow-sm d-flex align-items-center gap-2"
                    style="border-radius: 8px; font-weight: 600; padding: 8px 16px;">
                    <i class="bi bi-geo-alt"></i> View Map
                </a>
            @endif

            <form method="get" action='{{ route('downloadGuardReport') }}' target="_blank" class="d-flex gap-2 m-0">
                <input type="hidden" name="guardId" value="{{ $guardId }}" />
                <input type="hidden" name="attendanceSubType" value="{{ $attendanceSubType }}" />
                <input type="hidden" name="flagType" value="{{ $flag }}" />
                @if ($flag !== 'self')
                    <input type="hidden" name="clientName" value="{{ $clientName ?? '-' }}" />
                @endif
                <input type="hidden" name="siteName" value="{{ $siteName }}" />
                <input type="hidden" name="toDate" value="{{ $toDate }}" />
                <input type="hidden" name="fromDate" value="{{ $fromDate }}" />
                <input type="hidden" name="subType" value="{{ $subType }}" />

                <button type="submit" name="xlsx" value="pdf"
                    class="btn text-white shadow-sm d-flex align-items-center gap-2"
                    style="background-color: #ef4444; border-radius: 8px; font-weight: 600; padding: 8px 16px;">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </button>
                <button type="submit" name="xlsx" value="xlsx"
                    class="btn text-white shadow-sm d-flex align-items-center gap-2"
                    style="background-color: #10b981; border-radius: 8px; font-weight: 600; padding: 8px 16px;">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </button>
            </form>
        </div>
    </div>

    {{-- The Data Table --}}
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">#</th>
                    <th class="text-center">Date</th>
                    <th class="text-center">Status</th>
                    <th>Site / Beat</th>
                    <th class="text-center">Punch-in</th>
                    <th class="text-center">Punch-out</th>
                    <th class="text-center">Working (Manual)</th>
                    <th class="text-center">Working (GPS)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $srNo = 1;
                    $leaveCount = 0;
                    $weekoffCount = 0;
                    $daysWorked = 0;
                    $daysArray = [];
                    $currentDate = $fromDate;
                    for ($i = 0; $i < $daysCount; $i++) {
                        $daysArray[] = (object) [
                            'date' => date('d-m-Y', strtotime($currentDate)),
                            'format' => date('Y-m-d', strtotime($currentDate)),
                        ];
                        $currentDate = date('Y-m-d', strtotime('+1 day', strtotime($currentDate)));
                    }
                    
                    $leaves = App\Leave::where('user_id', $guardId)
                        ->whereBetween('fromDate', [$fromDate, $toDate])
                        ->where('status', 'Approved')
                        ->get();
                    
                    $leaveData = [];
                    foreach ($leaves as $leave) {
                        $lStart = new DateTime($leave->fromDate);
                        $lEnd = new DateTime($leave->toDate);
                        while ($lStart <= $lEnd) {
                            $leaveData[$lStart->format('d-m-Y')] = $leave->type;
                            $lStart->modify('+1 day');
                        }
                    }
                @endphp

                @foreach ($daysArray as $val)
                    @if (isset($data[$val->format]))
                        @php $daysWorked++; @endphp
                        @foreach ($data[$val->format] as $item)
                            <tr>
                                <td class="text-center text-muted small">{{ $srNo++ }}</td>
                                <td class="text-center fw-bold">{{ $item->date }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success px-3 py-1"
                                        style="border-radius: 6px; font-size: 0.7rem; font-weight: 700;">PRESENT</span>
                                </td>
                                <td class="fw-medium">{{ $item->site_name }}</td>
                                <td class="text-center fw-bold text-primary">{{ $item->entry_time }}</td>
                                <td
                                    class="text-center fw-bold {{ $item->exit_time ? 'text-primary' : 'text-danger small' }}">
                                    {{ $item->exit_time ?? 'NOT MARKED' }}
                                </td>
                                <td class="text-center">{{ $item->time_difference ?? '--' }}</td>
                                <td class="text-center">{{ $item->gpsTime ?? '--' }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class="text-center text-muted small">{{ $srNo++ }}</td>
                            <td class="text-center fw-bold text-muted">{{ $val->date }}</td>
                            <td class="text-center">
                                @if (isset($leaveData[$val->date]))
                                    <span class="badge bg-warning-subtle text-warning px-3 py-1"
                                        style="border-radius: 6px; font-size: 0.7rem; font-weight: 700;">{{ $leaveData[$val->date] }}</span>
                                @elseif(!in_array($val->date, $weekOffDates))
                                    <span class="badge bg-danger-subtle text-danger px-3 py-1"
                                        style="border-radius: 6px; font-size: 0.7rem; font-weight: 700;">ABSENT</span>
                                @else
                                    @php $weekoffCount++; @endphp
                                    <span class="badge bg-secondary-subtle text-secondary px-3 py-1"
                                        style="border-radius: 6px; font-size: 0.7rem; font-weight: 700;">WEEK OFF</span>
                                @endif
                            </td>
                            <td colspan="5" class="text-center text-muted small fst-italic">-- No Records --</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: var(--table-hover) !important;">
                    <td colspan="3" class="fw-bold text-end pe-4">Attendance Summary:</td>
                    <td colspan="3" class="fw-bold">
                        <span class="text-success">{{ $daysWorked }} Worked</span> /
                        <span class="text-primary">{{ max($daysWorked, $daysCount - $weekoffCount - $leaveCount) }}
                            Total Expected</span>
                    </td>
                    <td class="text-center fw-bold text-primary">{{ $actualTimeformat }}</td>
                    <td class="text-center fw-bold text-primary">{{ $gpsTimeformat }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
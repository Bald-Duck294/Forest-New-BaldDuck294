@php
    // No layout extension! This is just raw HTML to be injected into the modal.
@endphp

<div class="w-100">
    {{-- Header Section (Organization, Date, Export Buttons) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3"
        style="border-bottom: 1px dashed var(--border-color);">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--text-main); font-size: 1.5rem; text-transform: capitalize;">
                <i class="bi bi-clock-history me-2" style="color: var(--sapphire-warning);"></i>
                @if (isset($subType))
                    {{ $subType }}
                @else
                    Late Report
                @endif
            </h4>
            <div class="d-flex flex-wrap gap-3 text-muted mt-2" style="font-size: 0.85rem;">
                <div><strong style="color: var(--text-main);">Org:</strong> {{ $companyName }}</div>
                @if ($user->role_id != '2')
                    <div><strong style="color: var(--text-main);">Client:</strong> {{ $clientName }}</div>
                @endif
                <div><strong style="color: var(--text-main);">Site:</strong> {{ $siteName }}</div>
                <div><strong style="color: var(--text-main);">Date:</strong> {{ $date }}</div>
                <div><strong style="color: var(--text-main);">Generated:</strong> {{ $generatedOn }}</div>
            </div>
        </div>

        {{-- Export Buttons --}}
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <form method="post" action="{{ route('downloadLateReport') }}" target="_blank"
                class="d-flex gap-2 m-0">
                @csrf
                <input type="hidden" name="fromdate" value="{{ $fromdate ?? '' }}" />
                <input type="hidden" name="clientName" value="{{ $clientName ?? '' }}" />
                <input type="hidden" name="siteName" value="{{ $siteName ?? '' }}" />
                <input type="hidden" name="todate" value="{{ $todate ?? '' }}" />
                <input type="hidden" name="attendanceSubType" value="{{ $attendanceSubType ?? '' }}" />
                <input type="hidden" name="subType" value="{{ $subType ?? '' }}" />
                <input type="hidden" name="guard" value="{{ $guard ?? '' }}" />
                <input type="hidden" name="geofences" value="{{ $geofences ?? '' }}" />
                <input type="hidden" name="client" value="{{ $client ?? '' }}" />

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
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th class="text-center">Sr No</th>
                    <th>Employee Name</th>
                    <th>Role</th>
                    @if ($client == 'all' && $user->role_id != '2')
                        <th>Client / Range</th>
                    @endif
                    <th>Site / Beat</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Late By</th>
                </tr>
            </thead>
            <tbody>
                <?php $srNo = 1; ?>
                @foreach ($data as $key => $param)
                    <tr>
                        <td class="text-center fw-bold text-muted">{{ $srNo++ }}</td>
                        <td class="fw-bold">{{ $param->name }}</td>
                        <td>
                            @if ($param->role_id == 2)
                                <span class="badge bg-info-subtle text-info px-3 py-2" style="border-radius: 6px;">Supervisor</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary px-3 py-2" style="border-radius: 6px;">Employee</span>
                            @endif
                        </td>
                        @if ($client == 'all' && $user->role_id != '2')
                            <td>{{ $param->client_name }}</td>
                        @endif
                        <td>{{ $param->site_name }}</td>
                        <td>{{ $param->date }}</td>
                        <td class="fw-bold">{{ $param->entry_time }}</td>
                        <td>
                            <span class="text-danger fw-bold">
                                {{ gmdate('H:i:s', $param->lateTime * 60) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
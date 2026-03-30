@php
    // No layout extension! This is just raw HTML to be injected into the modal.
@endphp

<div class="w-100">
    {{-- Header Section (Organization, Date, Export Buttons) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3"
        style="border-bottom: 1px dashed var(--border-color);">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--text-main); font-size: 1.5rem; text-transform: capitalize;">
                <i class="bi bi-geo me-2" style="color: var(--sapphire-primary);"></i>
                On-Site Attendance Report
            </h4>
            <div class="d-flex flex-wrap gap-3 text-muted mt-2" style="font-size: 0.85rem;">
                <div><strong style="color: var(--text-main);">Org:</strong> {{ $companyName }}</div>
                @if ($user->role_id != 2)
                    <div><strong style="color: var(--text-main);">Client:</strong> {{ $clientName }}</div>
                @endif
                <div><strong style="color: var(--text-main);">Site:</strong> {{ $siteName }}</div>
                <div><strong style="color: var(--text-main);">Date:</strong> {{ $dateRange }}</div>
                <div><strong style="color: var(--text-main);">Generated:</strong> {{ $generatedOn }}</div>
            </div>
        </div>

        {{-- Export Buttons --}}
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <form method="post" action="{{ route('downloadOnSiteReport') }}" target="_blank"
                class="d-flex gap-2 m-0">
                @csrf
                <input type="hidden" name="startDate" value="{{ $startDate }}" />
                <input type="hidden" name="endDate" value="{{ $endDate }}" />
                <input type="hidden" name="client" value="{{ $client }}" />
                <input type="hidden" name="geofences" value="{{ $geofences }}" />

                <button type="submit" class="btn text-white shadow-sm d-flex align-items-center gap-2" name="pdf"
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
                    <th class="text-center">Sr. No.</th>
                    <th>Date</th>
                    <th>Name of Employee</th>
                    <th>Site / Beat</th>
                    <th class="text-center">Location</th>
                    <th class="text-center">Punch-In</th>
                    <th class="text-center">Punch-Out</th>
                    <th class="text-center">Total Time</th>
                    <th class="text-center">Approved By</th>
                </tr>
            </thead>
            <tbody>
                <?php $srNo = 1; ?>
                @foreach ($data as $item)
                    <tr>
                        <td class="text-center fw-bold text-muted">{{ $srNo++ }}</td>
                        <td class="fw-bold">{{ $item->date }}</td>
                        <td class="fw-bold text-primary">{{ $item->name }}</td>
                        <td>{{ $item->site_name }}</td>
                        <td class="text-center">
                            @php
                                $loc = json_decode($item->location);
                                if (isset($loc->lat, $loc->lng)) {
                                    $url = 'https://maps.google.com/?q=' . $loc->lat . ',' . $loc->lng;
                                } else {
                                    $url = '';
                                }
                            @endphp
                            @if ($url)
                                <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-outline-info"
                                    style="border-radius: 6px; font-size: 0.75rem; padding: 2px 8px;">
                                    <i class="bi bi-map"></i> View
                                </a>
                            @else
                                <span class="text-muted small">N/A</span>
                            @endif
                        </td>
                        <td class="text-center fw-bold">{{ $item->entry_time }}</td>
                        <td class="text-center fw-bold">{{ $item->exit_time }}</td>
                        <td class="text-center">{{ $item->time_difference }}</td>
                        <td class="text-center">
                            @if ($item->approvedBy)
                                <span class="badge bg-success-subtle text-success px-2 py-1"
                                    style="border-radius: 4px;">{{ $item->approvedBy }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>



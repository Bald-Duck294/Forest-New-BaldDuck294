@php
    // No layout extension! This is just raw HTML to be injected into the modal.
@endphp

<div class="w-100">
    {{-- Header Section (Organization, Date, Export Buttons) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3"
        style="border-bottom: 1px dashed var(--border-color);">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--text-main); font-size: 1.5rem; text-transform: capitalize;">
                <i class="bi bi-door-open me-2" style="color: var(--sapphire-danger);"></i>
                Forgot To Mark Exit Report
            </h4>
            <div class="d-flex flex-wrap gap-3 text-muted mt-2" style="font-size: 0.85rem;">
                <div><strong style="color: var(--text-main);">Org:</strong> {{ $companyName }}</div>
                @if ($user->role_id != 2)
                    <div><strong style="color: var(--text-main);">Client:</strong> {{ $clientName }}</div>
                @endif
                <div><strong style="color: var(--text-main);">Site:</strong> {{ $site }}</div>
                <div><strong style="color: var(--text-main);">Date:</strong> {{ $date }}</div>
                <div><strong style="color: var(--text-main);">Generated:</strong> {{ $generatedOn }}</div>
            </div>
        </div>

        {{-- Export Buttons --}}
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <form method="post" action="{{ route('downloadForgotToMarkExit') }}" target="_blank"
                class="d-flex gap-2 m-0">
                @csrf
                <input type="hidden" name="geofences" value="{{ $geofences }}" />
                <input type="hidden" name="site" value="{{ $site }}" />
                <input type="hidden" name="client" value="{{ $client }}" />
                <input type="hidden" name="clientName" value="{{ $clientName }}" />
                <input type="hidden" name="startDate" value="{{ $startDate }}" />
                <input type="hidden" name="endDate" value="{{ $endDate }}" />

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
                    <th class="text-center">Sr. No.</th>
                    <th>Date</th>
                    <th>Name of Employee</th>
                    @if ($client == 'all' && $user->role_id !== '2')
                        <th>Client/Range</th>
                    @endif
                    <th>Site</th>
                    <th class="text-center">Punch-In Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $index => $item)
                    <tr>
                        <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $item->date }}</td>
                        <td class="fw-bold text-primary">{{ $item->name }}</td>
                        @if ($client == 'all' && $user->role_id !== '2')
                            <td>{{ $item->client_name }}</td>
                        @endif
                        <td class="@if (strtolower($item->site_name) === 'current location') text-success fw-bold @endif">
                            {{ $item->site_name }}
                        </td>
                        <td class="text-center fw-bold text-muted">{{ $item->entry_time }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@include('includes.header')
<style>
    :root {
        --primary: #2563eb;
        --primary-hover: #1d4ed8;
        --border: #e2e8f0;
        --bg: #f8fafc;
        --text: #0f172a;
        --muted: #64748b;
    }

    body {
        font-family: 'Inter', sans-serif !important;
        background-color: var(--bg);
    }

    .report-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid var(--border);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        padding: 24px;
        margin-bottom: 24px;
    }

    .report-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
        background: #f1f5f9;
        padding: 20px;
        border-radius: 12px;
    }

    .info-item .label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--muted);
        font-weight: 600;
        margin-bottom: 4px;
    }

    .info-item .value {
        font-size: 14px;
        color: var(--text);
        font-weight: 600;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        background: #f8fafc;
        border-bottom: 2px solid var(--border);
        color: var(--muted);
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 12px 16px;
        text-align: center;
        white-space: nowrap;
    }

    .table tbody td {
        padding: 14px 16px;
        vertical-align: middle;
        color: var(--text);
        font-size: 14px;
        border-bottom: 1px solid var(--border);
    }

    .btn-report {
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        border: none;
    }

    .btn-pdf {
        background: #fee2e2;
        color: #dc2626;
    }

    .btn-excel {
        background: #dcfce7;
        color: #166534;
    }

    .btn-report:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .sticky-header {
        position: sticky;
        top: 0;
        z-index: 10;
        background: white;
    }

    .close-btn {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 1px solid var(--border);
        background: #fff;
        color: var(--muted);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .close-btn:hover {
        background: #f1f5f9;
        color: var(--text);
    }

    /* Clickable Status Badge */
    .status-clickable {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .status-clickable:hover {
        opacity: 0.8;
        transform: scale(1.02);
    }
</style>

<div class="container-fluid py-4">
    <div class="report-card">

        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h3 class="mb-1" style="font-weight: 700; color: var(--text);">Incidence Report</h3>
                <p class="text-muted small mb-0">Record of all reported incidents and their status.</p>
            </div>
            <div class="d-flex gap-3 align-items-center">
                <form method="post" action='{{ route("downloadIncidenceReport") }}' target="_blank" class="d-flex gap-2 mb-0">
                    @csrf
                    <input type="hidden" name="geofences" value="{{ $geofences }}" />
                    <input type="hidden" name="toDate" value="{{ $toDate }}" />
                    <input type="hidden" name="fromDate" value="{{ $fromDate }}" />
                    <input type="hidden" name="priority" value="{{ $priority }}" />
                    <input type="hidden" name="client" value="{{ $client }}" />
                    <input type="hidden" name="incidenceSubType" value="{{ $incidenceSubType }}" />
                    <input type="hidden" name="siteName" value="{{ $siteName }}" />
                    <input type="hidden" name="incidenceData" value="{{ json_encode($IncidenceDetails) }}" />

                    <button type="submit" class="btn-report btn-pdf" name="xlsx" value="pdf">
                        <i class="la la-file-pdf"></i> PDF
                    </button>
                    <button type="submit" class="btn-report btn-excel" name="xlsx" value="xlsx">
                        <i class="la la-file-excel"></i> Excel
                    </button>
                </form>

                <button type="button" class="close-btn" data-bs-dismiss="modal" aria-label="Close">
                    <i class="la la-times"></i>
                </button>
            </div>
        </div>

        <div class="report-info-grid">
            <div class="info-item">
                <div class="label">Organization</div>
                <div class="value">{{ $companyName }}</div>
            </div>
            <div class="info-item">
                <div class="label">Site</div>
                <div class="value">{{ $siteName }}</div>
            </div>
            <div class="info-item">
                <div class="label">Date Range</div>
                <div class="value">{{ date('d M Y', strtotime($fromDate)) }} to {{ date('d M Y', strtotime($toDate)) }}</div>
            </div>
            <div class="info-item">
                <div class="label">Generated On</div>
                <div class="value">{{ date("d M Y") }}</div>
            </div>
        </div>

        <div class="table-responsive rounded border shadow-sm" style="max-height: 60vh; overflow-y: auto;">
            <table class="table table-hover align-middle">
                <thead class="sticky-header">
                    <tr>
                        <th>Sr. No.</th>
                        <th>Location</th>
                        <th class="text-start">Guard Remark</th>
                        <th class="text-start">Supervisor Remark</th>
                        <th>Supervisor Action</th>
                        <th class="text-start">Admin Remark</th>
                        <th>Admin Action</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($IncidenceDetails as $index => $item)
                        @php
                            // Setup URL if applicable (adjust mapping to your actual DB column if needed)
                            $url = $item->location_url ?? null;
                        @endphp
                        <tr>
                            <td class="text-center fw-bold">{{ $index + 1 }}</td>

                            <td class="text-center">
                                @if($url)
                                    <a href="{{ $url }}" target="_blank" class="badge bg-light text-primary border text-decoration-none px-2 py-1">
                                        <i class="la la-map-marker"></i> Map
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td>{{ $item->remark ?? '-' }}</td>

                            <td>{{ $item->supervisorRemark ?? '-' }}</td>

                            <td class="text-center text-nowrap">
                                @if(!empty($item->supervisorActionDateTime))
                                    {{ date('d M Y g:i a', strtotime($item->supervisorActionDateTime)) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td>{{ $item->adminRemark ?? '-' }}</td>

                            <td class="text-center text-nowrap">
                                @if(!empty($item->adminActionDateTime))
                                    {{ date('d M Y g:i a', strtotime($item->adminActionDateTime)) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <span class="badge bg-primary px-3 py-2 rounded-pill status-clickable"
                                      onclick="IncidenceRead('{{ $item->id }}','report')"
                                      title="Click to view details">
                                    {{ $item->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted bg-light">
                                <i class="la la-folder-open fs-1 d-block mb-2 text-secondary"></i>
                                No incidence records found for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

@include('includes.report-footer')

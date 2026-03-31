<style>
    /* =========================================
       THEME VARIABLES (LIGHT MODE DEFAULT)
       ========================================= */
    :root {
        --rp-primary: #2563eb;
        --rp-primary-hover: #1d4ed8;

        /* Backgrounds */
        --rp-bg-body: #f8fafc;
        --rp-bg-card: #ffffff;
        --rp-bg-info-grid: #f1f5f9;
        --rp-bg-th: #f8fafc;
        --rp-bg-hover: #f1f5f9;

        /* Borders & Text */
        --rp-border: #e2e8f0;
        --rp-text-main: #0f172a;
        --rp-text-muted: #64748b;

        /* Buttons & Badges */
        --rp-btn-pdf-bg: #fee2e2;
        --rp-btn-pdf-text: #dc2626;
        --rp-btn-excel-bg: #dcfce7;
        --rp-btn-excel-text: #166534;
        --rp-map-bg: #f8fafc;
        --rp-map-text: #2563eb;
    }

    /* =========================================
       THEME VARIABLES (DARK MODE)
       Triggered via <html data-bs-theme="dark"> or <body class="dark-mode">
       ========================================= */
    html[data-bs-theme="dark"],
    body.dark-mode,
    [data-theme="dark"] {
        --rp-primary: #3b82f6;
        --rp-primary-hover: #60a5fa;

        /* Backgrounds */
        --rp-bg-body: #0f172a;
        --rp-bg-card: #1e293b;
        --rp-bg-info-grid: #0f172a;
        /* Darker inset for info grid */
        --rp-bg-th: #0f172a;
        --rp-bg-hover: #334155;

        /* Borders & Text */
        --rp-border: #334155;
        --rp-text-main: #f8fafc;
        --rp-text-muted: #94a3b8;

        /* Buttons & Badges (Soft transparency for dark mode) */
        --rp-btn-pdf-bg: rgba(220, 38, 38, 0.15);
        --rp-btn-pdf-text: #fca5a5;
        --rp-btn-excel-bg: rgba(22, 101, 52, 0.2);
        --rp-btn-excel-text: #86efac;
        --rp-map-bg: #0f172a;
        --rp-map-text: #60a5fa;
    }

    /* =========================================
       COMPONENT STYLES
       ========================================= */
    .report-card {
        background-color: var(--rp-bg-card);
        border-radius: 16px;
        border: 1px solid var(--rp-border);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        padding: 24px;
        margin-bottom: 24px;
        transition: background-color 0.3s, border-color 0.3s;
    }

    .report-header h3 {
        font-weight: 700;
        color: var(--rp-text-main);
    }

    .report-header p {
        color: var(--rp-text-muted);
    }

    .report-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
        background-color: var(--rp-bg-info-grid);
        padding: 20px;
        border-radius: 12px;
        border: 1px solid var(--rp-border);
        transition: background-color 0.3s;
    }

    .info-item .label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--rp-text-muted);
        font-weight: 600;
        margin-bottom: 4px;
    }

    .info-item .value {
        font-size: 14px;
        color: var(--rp-text-main);
        font-weight: 600;
    }

    /* Table Adjustments */
    .theme-table {
        margin-bottom: 0;
        color: var(--rp-text-main);
    }

    .theme-table thead th {
        background-color: var(--rp-bg-th);
        border-bottom: 2px solid var(--rp-border);
        color: var(--rp-text-muted);
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 12px 16px;
        text-align: center;
        white-space: nowrap;
    }

    .theme-table tbody td {
        padding: 14px 16px;
        vertical-align: middle;
        color: var(--rp-text-main);
        font-size: 14px;
        border-bottom: 1px solid var(--rp-border);
        background-color: var(--rp-bg-card);
    }

    .theme-table tbody tr:hover td {
        background-color: var(--rp-bg-hover);
    }

    /* Action Buttons */
    .btn-report {
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        border: 1px solid transparent;
    }

    .btn-pdf {
        background-color: var(--rp-btn-pdf-bg);
        color: var(--rp-btn-pdf-text);
        border-color: rgba(220, 38, 38, 0.1);
    }

    .btn-excel {
        background-color: var(--rp-btn-excel-bg);
        color: var(--rp-btn-excel-text);
        border-color: rgba(22, 101, 52, 0.1);
    }

    .btn-report:hover {
        transform: translateY(-1px);
        filter: brightness(0.95);
    }

    .close-btn {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 1px solid var(--rp-border);
        background-color: var(--rp-bg-card);
        color: var(--rp-text-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .close-btn:hover {
        background-color: var(--rp-bg-hover);
        color: var(--rp-text-main);
    }

    /* Specific UI Elements */
    .map-badge {
        background-color: var(--rp-map-bg);
        color: var(--rp-map-text);
        border: 1px solid var(--rp-border);
        text-decoration: none;
        transition: all 0.2s;
    }

    .map-badge:hover {
        background-color: var(--rp-bg-hover);
        color: var(--rp-primary);
    }

    .status-clickable {
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .status-clickable:hover {
        opacity: 0.8;
        transform: scale(1.02);
    }

    .empty-state-row td {
        background-color: var(--rp-bg-info-grid) !important;
        color: var(--rp-text-muted) !important;
    }
</style>

<div class="container-fluid py-4">
    <div class="report-card">

        <div class="d-flex justify-content-between align-items-start mb-4 report-header">
            <div>
                <h3 class="mb-1">Incidence Report</h3>
                <p class="small mb-0">Record of all reported incidents and their status.</p>
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

        <div class="table-responsive rounded shadow-sm" style="max-height: 60vh; overflow-y: auto; border: 1px solid var(--rp-border);">
            <table class="table theme-table align-middle">
                <thead class="sticky-top" style="z-index: 10;">
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
                            <a href="{{ $url }}" target="_blank" class="badge px-2 py-1 map-badge">
                                <i class="la la-map-marker"></i> Map
                            </a>
                            @else
                            <span style="color: var(--rp-text-muted);">-</span>
                            @endif
                        </td>

                        <td>{{ $item->remark ?? '-' }}</td>

                        <td>{{ $item->supervisorRemark ?? '-' }}</td>

                        <td class="text-center text-nowrap">
                            @if(!empty($item->supervisorActionDateTime))
                            {{ date('d M Y g:i a', strtotime($item->supervisorActionDateTime)) }}
                            @else
                            <span style="color: var(--rp-text-muted);">-</span>
                            @endif
                        </td>

                        <td>{{ $item->adminRemark ?? '-' }}</td>

                        <td class="text-center text-nowrap">
                            @if(!empty($item->adminActionDateTime))
                            {{ date('d M Y g:i a', strtotime($item->adminActionDateTime)) }}
                            @else
                            <span style="color: var(--rp-text-muted);">-</span>
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
                    <tr class="empty-state-row">
                        <td colspan="8" class="text-center py-5">
                            <i class="la la-folder-open fs-1 d-block mb-2" style="opacity: 0.5;"></i>
                            No incidence records found for this period.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
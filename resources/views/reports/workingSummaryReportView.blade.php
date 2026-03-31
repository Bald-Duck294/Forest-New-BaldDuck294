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
        --rp-bg-hover: #f8fafc;

        /* Borders & Text */
        --rp-border: #e2e8f0;
        --rp-text-main: #0f172a;
        --rp-text-muted: #64748b;

        /* Specific Status Colors */
        --rp-success-text: #059669;
        --rp-danger-text: #dc2626;

        /* Buttons & Badges */
        --rp-btn-pdf-bg: #fee2e2;
        --rp-btn-pdf-text: #dc2626;
        --rp-btn-excel-bg: #dcfce7;
        --rp-btn-excel-text: #166534;
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
        --rp-bg-th: #0f172a;
        --rp-bg-hover: #334155;

        /* Borders & Text */
        --rp-border: #334155;
        --rp-text-main: #f8fafc;
        --rp-text-muted: #94a3b8;

        /* Specific Status Colors (Lighter for dark backgrounds) */
        --rp-success-text: #34d399;
        --rp-danger-text: #f87171;

        /* Buttons & Badges (Soft transparency) */
        --rp-btn-pdf-bg: rgba(220, 38, 38, 0.15);
        --rp-btn-pdf-text: #fca5a5;
        --rp-btn-excel-bg: rgba(22, 101, 52, 0.2);
        --rp-btn-excel-text: #86efac;
    }

    /* =========================================
       COMPONENT STYLES
       ========================================= */
    body {
        font-family: 'Inter', sans-serif !important;
        background-color: var(--rp-bg-body);
    }

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
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
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

    /* Dynamic Data Cells */
    .text-worked {
        color: var(--rp-success-text) !important;
        font-weight: 600;
    }

    .text-absent {
        color: var(--rp-danger-text) !important;
        font-weight: 600;
    }

    .employee-link {
        cursor: pointer;
        color: var(--rp-primary);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }

    .employee-link:hover {
        color: var(--rp-primary-hover);
        text-decoration: underline;
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

    .sticky-header {
        position: sticky;
        top: 0;
        z-index: 10;
    }
</style>

<div class="container py-4">
    <div class="report-card">
        <div class="d-flex justify-content-between align-items-start mb-4 report-header">
            <div>
                <h3 class="mb-1">Working Summary Report</h3>
                <p class="small mb-0">Detailed attendance summary for the selected period.</p>
            </div>
            <div class="d-flex gap-3 align-items-center">
                <form method="get" action="{{ route('downloadWorkingSummaryReport') }}" target="_blank"
                    class="d-flex gap-2 mb-0">
                    <input type="hidden" name="geofences" value="{{$geofences}}">
                    <input type="hidden" name="client" value="{{ $client }}">
                    <input type="hidden" name="startDate" value={{ $startDate }}>
                    <input type="hidden" name="endDate" value={{ $endDate }}>
                    <input type="hidden" name="clientName" value="{{ $clientName }}">
                    <input type="hidden" name="companyName" value="{{ $companyName->name }}">
                    <input type="hidden" name="subType" value="{{ $subType }}">

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
                <div class="value">{{ $companyName->name ?? '-' }}</div>
            </div>
            @if($user->role_id != 2)
            <div class="info-item">
                <div class="label">Client / Range</div>
                <div class="value">{{ $clientName ?? '-' }}</div>
            </div>
            @endif
            <div class="info-item">
                <div class="label">Site / Beat</div>
                <div class="value">{{ ($site == 'all') ? 'All sites' : $siteName }}</div>
            </div>
            <div class="info-item">
                <div class="label">Date Range</div>
                <div class="value">{{$date}}</div>
            </div>
            <div class="info-item">
                <div class="label">Generated On</div>
                <div class="value">{{ $generatedOn }}</div>
            </div>
        </div>

        <div class="table-responsive" style="max-height: 60vh; border: 1px solid var(--rp-border); border-radius: 12px; overflow-y: auto;">
            <table class="table theme-table">
                <thead class="sticky-header">
                    <tr>
                        <th style="width: 80px;">Sr No</th>
                        <th style="text-align: left;">Employee Name</th>
                        <th>Total Days</th>
                        <th>Worked</th>
                        <th>Absent</th>
                        <th>Week Off</th>
                    </tr>
                </thead>
                <tbody>
                    @php $srNo = 1; @endphp
                    @foreach ($groupedData as $userId => $userData)
                    <tr>
                        <td class="text-center">{{ $srNo++ }}</td>
                        <td class="text-start">
                            <a onclick="guardAttendanceReport('{{ $userData['user_id'] }}','{{ $startDate }}','{{ $endDate }}' , '{{ $attendanceSubType }}' )"
                                class="employee-link">
                                {{ $userData['user_name'] }}
                            </a>
                        </td>
                        <td class="text-center">{{ $userData['totalWorkingDays'] }}</td>
                        <td class="text-center text-worked">{{ $userData['daysWorked'] }}</td>
                        <td class="text-center text-absent">{{ $userData['absentDays'] }}</td>
                        <td class="text-center" style="color: var(--rp-text-muted);">{{ $userData['weekOffCount'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @php
    // dump( $clientName, "another client")
    @endphp
    @include('includes.report-footer')
</div>
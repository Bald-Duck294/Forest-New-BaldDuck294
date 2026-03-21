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
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
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
    }

    .table tbody td {
        padding: 14px 16px;
        vertical-align: middle;
        color: var(--text);
        font-size: 14px;
        border-bottom: 1px solid var(--border);
    }

    .table tbody tr:hover {
        background-color: #f8fafc;
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
</style>

<div class="container py-4">
    <div class="report-card">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h3 class="mb-1" style="font-weight: 700; color: var(--text);">Working Summary Report</h3>
                <p class="text-muted small mb-0">Detailed attendance summary for the selected period.</p>
            </div>
            <div class="d-flex gap-3 align-items-center">
                <form method="get" action="{{ route('downloadWorkingSummaryReport') }}" target="_blank"
                    class="d-flex gap-2">
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

        <div class="table-responsive" style="max-height: 60vh; border: 1px solid var(--border); border-radius: 12px;">
            <table class="table table-hover">
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
                            <td style="text-align: center;">{{ $srNo++ }}</td>
                            <td style="text-align: left; font-weight: 500;">
                                <a onclick="guardAttendanceReport('{{ $userData['user_id'] }}','{{ $startDate }}','{{ $endDate }}' , '{{ $attendanceSubType  }}' )"
                                    style="cursor: pointer; color: var(--primary); text-decoration: none;">
                                    {{ $userData['user_name'] }}
                                </a>
                            </td>
                            <td style="text-align: center;">{{ $userData['totalWorkingDays'] }}</td>
                            <td style="text-align: center; color: #059669; font-weight: 600;">{{ $userData['daysWorked'] }}
                            </td>
                            <td style="text-align: center; color: #dc2626; font-weight: 600;">{{ $userData['absentDays'] }}
                            </td>
                            <td style="text-align: center; color: var(--muted);">{{ $userData['weekOffCount'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @php
        // dump( $clientName, "anothe client")
    @endphp
    @include('includes.report-footer')
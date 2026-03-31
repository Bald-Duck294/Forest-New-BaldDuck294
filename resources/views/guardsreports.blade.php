@extends('layouts.app')

@php
$hideGlobalFilters = true;
$hideBackground = true;
@endphp

@section('content')
<style>
    /* =========================================
                                                                                   SAPPHIRE MODAL & THEME OVERRIDES
                                                                                ========================================= */
    .sapphire-modal-dialog {
        max-width: 96vw !important;
        height: 94vh !important;
        margin: 3vh auto !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sapphire-modal-dialog.zen-active {
        max-width: 100vw !important;
        height: 100vh !important;
        margin: 0 !important;
    }

    .sapphire-modal-dialog.zen-active .sapphire-modal {
        border-radius: 0 !important;
    }

    .sapphire-modal {
        height: 100%;
        background: var(--bg-card) !important;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }



    /* Action Bar (Top Right) */
    .modal-action-bar {
        position: absolute;
        top: 15px;
        right: 15px;
        display: flex;
        gap: 10px;
        z-index: 1050;
    }

    .modal-top-btn {
        background: var(--bg-body);
        border: 1px solid var(--border-color);
        color: var(--text-muted);
        border-radius: 8px;
        padding: 8px 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .modal-top-btn:hover {
        background: var(--table-hover);
        color: var(--text-main);
    }

    .modal-btn-close:hover {
        background: var(--sapphire-danger);
        color: white;
        border-color: var(--sapphire-danger);
    }

    /* Fix Export Buttons */
    #siteWiseGuardReports .dt-buttons {
        position: absolute !important;
        top: 15px !important;
        right: 180px !important;
        display: flex;
        gap: 8px;
        z-index: 10;
    }

    #siteWiseGuardReports .dt-buttons .btn {
        border-radius: 8px !important;
        padding: 6px 16px !important;
        font-size: 0.85rem !important;
        font-weight: 600;
        border: none;
        color: white;
    }

    #siteWiseGuardReports .buttons-pdf {
        background-color: #ef4444 !important;
    }

    #siteWiseGuardReports .buttons-excel {
        background-color: #10b981 !important;
    }


    #siteWiseGuardReports h4 {
        display: block !important;
        font-size: 18px;
        margin-bottom: 6px;
    }

    #siteWiseGuardReports .d-flex.flex-column.flex-md-row {
        display: flex !important;
        margin-bottom: 12px;
    }

    #siteWiseGuardReports .content-wrapper,
    #siteWiseGuardReports .main-panel {
        margin-left: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        background: transparent !important;
    }

    #siteWiseGuardReports .card {
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
        margin: 0 !important;
    }

    /* Modern Table Headers */
    #siteWiseGuardReports table {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    #siteWiseGuardReports table th {
        background-color: var(--bg-body) !important;
        color: var(--text-muted) !important;
        font-weight: 700 !important;
        font-size: 0.75rem !important;
        text-transform: uppercase !important;
        border-bottom: 2px solid var(--border-color) !important;
        padding: 1rem !important;
    }

    #siteWiseGuardReports table td {
        background-color: var(--bg-card) !important;
        color: var(--text-main) !important;
        border-bottom: 1px solid var(--border-color) !important;
        padding: 1rem !important;
        font-size: 0.875rem !important;
    }

    /* Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-weight: 700;
        padding: 3px 8px;
        font-size: 0.75rem;
        min-width: 28px;
    }

    .badge-a {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .badge-p {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .badge-wo {
        background: rgba(100, 116, 139, 0.1);
        color: #94a3b8;
        border: 1px solid rgba(100, 116, 139, 0.2);
    }

    .badge-routine {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .badge-vehicle {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }



    /* =========================================
                                                                                                                                   LOCAL COMPONENT STYLES
                                                                                                                                   (Hooked to Global Sapphire Variables)
                                                                                                                                ========================================= */

    /* Cards */
    .dash-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }

    .dash-card-header {
        background: transparent;
        border-bottom: 1px solid var(--border-color);
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .dash-card-header h4 {
        margin: 0;
        font-weight: 700;
        color: var(--text-main);
        font-size: 1.25rem;
    }

    /* Form Inputs */
    .custom-input {
        background-color: var(--bg-body) !important;
        color: var(--text-main) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 8px !important;
        height: 48px !important;
        padding: 0.5rem 1rem !important;
        font-size: 0.875rem !important;
        width: 100%;
        outline: none;
        transition: all 0.2s ease;
    }

    .custom-input:focus {
        border-color: var(--sapphire-primary) !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }

    html[data-bs-theme="dark"] .custom-input {
        color-scheme: dark;
    }

    /* Floating Labels - Theme Aware */
    .has-float-label {
        display: block;
        position: relative;
        margin-bottom: 0;
    }

    .has-float-label label {
        position: absolute;
        left: 12px;
        top: -8px;
        color: var(--text-muted) !important;
        font-weight: 600 !important;
        font-size: 0.75rem !important;
        background: var(--bg-card) !important;
        padding: 0 4px !important;
        z-index: 5;
        transition: color 0.2s ease;
    }

    /* Select2 Overrides for Sapphire Theme (Light/Dark Support) */
    .select2-container--default .select2-selection--single {
        background-color: var(--bg-body) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 8px !important;
        height: 48px !important;
        display: flex;
        align-items: center;
        transition: all 0.2s ease;
    }

    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: var(--sapphire-primary) !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: var(--text-main) !important;
        line-height: normal !important;
        padding-left: 1rem !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px !important;
        right: 8px !important;
    }

    .select2-dropdown {
        background-color: var(--bg-card) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 8px !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
        z-index: 1060;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        background-color: var(--bg-body) !important;
        color: var(--text-main) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 6px;
    }

    .select2-container--default .select2-results__option {
        color: var(--text-main) !important;
        font-size: 0.85rem;
        padding: 8px 16px;
    }

    .select2-container--default .select2-results__option[aria-selected=true],
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: var(--table-hover) !important;
        color: var(--sapphire-primary) !important;
        font-weight: 600;
    }

    /* Action Buttons */
    .btn-sapphire {
        background-color: var(--sapphire-primary);
        color: #ffffff;
        border: none;
        font-weight: 600;
        padding: 10px 24px;
        border-radius: 8px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-sapphire:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        color: #ffffff;
    }

    .btn-icon-soft {
        background: transparent;
        color: var(--text-muted);
        border: 1px solid transparent;
        border-radius: 8px;
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .btn-icon-soft:hover {
        background: var(--table-hover);
        border-color: var(--border-color);
        color: var(--sapphire-danger);
    }

    /* Error States */
    .errorMsg,
    .guardErrorMsg {
        color: var(--sapphire-danger);
        font-size: 0.75rem;
        margin-top: 0.4rem;
        margin-left: 0.25rem;
        font-weight: 600;
    }

    .makeRedd {
        border-color: var(--sapphire-danger) !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
    }

    /* Custom Loader */
    .custom-loader-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(var(--bg-body-rgb, 255, 255, 255), 0.8);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }

    /* Modal Overrides */
    .sapphire-modal {
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    /* Hide sub-dropdowns by default to prevent flicker */
    .visitorSubType,
    .subType,
    .incidentSubType,
    .tourSubType,
    .incidencePriority,
    .attendanceSubType,
    .patrollingReportSubType,
    .patrolLogsType,
    .supervisorSelect,
    .adminSelect,
    .supervisorID,
    .guardSelect,
    #tourdateSelect,
    #fromdateSelect,
    #todateSelect {
        display: none;
    }

    .form-group-wrap {
        margin-bottom: 1.5rem;
    }

    /* =========================================
                                                                                       AJAX MODAL HIJACK STYLES (THEME AWARE & ZEN MODE)
                                                                                    ========================================= */

    /* 1. The Zen Mode & Base Modal Sizing */
    .sapphire-modal-dialog {
        max-width: 96vw !important;
        height: 94vh !important;
        margin: 3vh auto !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sapphire-modal-dialog.zen-active {
        max-width: 100vw !important;
        height: 100vh !important;
        margin: 0 !important;
    }

    .sapphire-modal-dialog.zen-active .sapphire-modal {
        border-radius: 0 !important;
        border: none !important;
    }

    .sapphire-modal {
        height: 100%;
        background: var(--bg-card) !important;
        /* Adapts to Dark/Light mode */
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        border: 1px solid var(--border-color) !important;
    }

    .modal-backdrop.show {
        opacity: 0.7 !important;
        backdrop-filter: blur(5px);
        background-color: rgba(0, 0, 0, 0.6) !important;
    }

    /* 2. Top Action Bar */
    .modal-action-bar {
        position: absolute;
        top: 15px;
        right: 15px;
        display: flex;
        gap: 10px;
        z-index: 1050;
    }

    .modal-top-btn {
        background: var(--bg-body);
        border: 1px solid var(--border-color);
        color: var(--text-muted);
        border-radius: 8px;
        padding: 8px 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .modal-top-btn:hover {
        background: var(--table-hover);
        color: var(--text-main);
    }

    .modal-btn-close:hover {
        background: var(--sapphire-danger);
        color: white;
        border-color: var(--sapphire-danger);
    }

    /* DataTables Export Buttons */
    #siteWiseGuardReports .dt-buttons {
        position: absolute !important;
        top: -55px !important;
        right: 200px !important;
        display: flex;
        gap: 8px;
    }

    #siteWiseGuardReports .dt-buttons .btn {
        border-radius: 8px !important;
        padding: 6px 16px !important;
        font-size: 0.85rem !important;
        font-weight: 600;
        border: none;
        color: white;
    }

    #siteWiseGuardReports .buttons-pdf {
        background-color: #ef4444 !important;
    }

    #siteWiseGuardReports .buttons-excel {
        background-color: #10b981 !important;
    }

    /* 3. AGGRESSIVE LAYOUT KILLER (Ensures no wrappers mess up the table) */
    #siteWiseGuardReports>.container,
    #siteWiseGuardReports>.container-fluid {
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
    }

    /* Clean up Organization Header text to fit dark mode */
    #siteWiseGuardReports h4,
    #siteWiseGuardReports h3 {
        color: var(--text-main) !important;
        font-size: 1.25rem !important;
        font-weight: 700 !important;
        margin-bottom: 1rem !important;
    }

    #siteWiseGuardReports table:first-of-type {
        border: none !important;
        margin-bottom: 15px !important;
        background: transparent !important;
    }

    #siteWiseGuardReports table:first-of-type td,
    #siteWiseGuardReports table:first-of-type th {
        background: transparent !important;
        color: var(--text-muted) !important;
        border: none !important;
        padding: 4px !important;
    }

    /* 4. Modern Theme-Aware Data Table */
    #siteWiseGuardReports .table-responsive {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        background: var(--bg-card);
    }

    #siteWiseGuardReports table.table {
        width: 100%;
        table-layout: fixed;
        /* 🔥 KEY FIX */
    }

    #siteWiseGuardReports td.date-col,
    #siteWiseGuardReports th.date-col {
        text-align: center;
        width: 60px;
    }

    #siteWiseGuardReports th,
    #siteWiseGuardReports td {
        /* white-space: nowrap; */
        padding: 10px 14px;
    }

    #siteWiseGuardReports table {
        font-size: 13px;
    }

    #siteWiseGuardReports table.table thead th {
        background-color: var(--bg-body) !important;
        /* Soft dark/light background */
        color: var(--text-muted) !important;
        font-weight: 700 !important;
        font-size: 0.75rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        padding: 1rem !important;
        border-bottom: 1px solid var(--border-color) !important;
        border-top: none !important;
    }

    #siteWiseGuardReports table.table tbody td {
        background-color: var(--bg-card) !important;
        color: var(--text-main) !important;
        border-bottom: 1px solid var(--border-color) !important;
        padding: 8px 10px !important;
        font-size: 0.875rem !important;
        vertical-align: middle;
    }

    #siteWiseGuardReports .date-col {
        padding: 6px 8px !important;
        text-align: center;
        min-width: 50px;
    }

    #siteWiseGuardReports .date-col .status-badge {
        margin: 0 auto;
    }

    #siteWiseGuardReports th.date-col {
        position: sticky;
        top: 0;
        z-index: 5;
    }

    #siteWiseGuardReports .date-col {
        width: 60px;
    }

    #siteWiseGuardReports table.table tbody tr:hover td {
        background-color: var(--table-hover) !important;
    }

    #siteWiseGuardReports th:nth-child(1),
    #siteWiseGuardReports td:nth-child(1) {
        width: 60px;
    }

    #siteWiseGuardReports th:nth-child(2),
    #siteWiseGuardReports td:nth-child(2) {
        min-width: 180px;
    }

    #siteWiseGuardReports th:nth-child(3),
    #siteWiseGuardReports td:nth-child(3),
    #siteWiseGuardReports th:nth-child(4),
    #siteWiseGuardReports td:nth-child(4) {
        min-width: 160px;
    }


    .badge-a {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .badge-p {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .badge-wo {
        background: rgba(100, 116, 139, 0.1);
        color: #94a3b8;
        border: 1px solid rgba(100, 116, 139, 0.2);
    }

    .badge-routine {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .badge-vehicle {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .modal {
        z-index: 1060 !important;
    }

    .modal-backdrop {
        z-index: 1055 !important;
    }

    body.modal-open-custom {
        overflow: hidden !important;
    }

    body.modal-open-custom #sidebar {
        display: none !important;
    }

    body.modal-open-custom #sidebarBackdrop {
        display: none !important;
    }
</style>

<div class="container-fluid py-4">
    <div class="dash-card">

        <div class="dash-card-header">
            <h4 class="d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-bar-graph text-primary"
                    style="color: var(--sapphire-primary) !important;"></i>
                Reports Optimization
            </h4>
            {{-- <div class="header-actions">
                    <a href="javascript:history.back()" class="btn-icon-soft" title="Close">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div> --}}
        </div>

        <div class="card-body p-4 p-xl-5">
            <form name="downloadExcel" method="GET" action="" target="_blank" id="reportForm">
                <?php
                $features = session('features');
                $user = session('user');

                $tours = false;
                $atten = true;
                $patrol = true;
                $inci = true;
                $visi = false;
                $clvisit = false;
                $tourdiary = false;
                ?>

                <div class="row g-4">

                    {{-- TYPE SELECT --}}
                    <div class="col-md-4 col-lg-3 typeSelect validationCheck">
                        <div class="form-group-wrap">
                            <span class="has-float-label">
                                <select class="custom-input checkAll changeEvent" name="type" id="typeSelect">
                                    <option value="0" selected disabled>Select Type</option>
                                    @if ($atten !== false)
                                    <option value="attendance">Attendance Report</option>
                                    @endif
                                    @if ($patrol !== false)
                                    <option value="patrolling">Patrolling Report</option>
                                    @endif
                                    @if ($inci !== false)
                                    <option value="incident">Incidence Report</option>
                                    @endif
                                    @if ($tours !== false)
                                    <option value="tour">Tour Report</option>
                                    @endif
                                    @if ($visi !== false)
                                    <option value="visitor">Visitor Report</option>
                                    @endif
                                    @if ($clvisit !== false)
                                    <option value="visits">Client Visits Report</option>
                                    @endif
                                    @if ($tourdiary !== false)
                                    <option value="tourdiary">Tour Diary Report</option>
                                    @endif
                                </select>
                                <label>Report Type</label>
                            </span>
                            <div class="errorMsg" id="typeSelectError"></div>
                        </div>
                    </div>

                    {{-- VISITOR SUBTYPE --}}
                    @if ($visi !== false)
                    <div class="col-md-4 col-lg-3 visitorSubType validationCheck">
                        <div class="form-group-wrap">
                            <span class="has-float-label">
                                <select class="custom-input checkAll changeEvent" name="visitorSubType"
                                    id="visitorSubType">
                                    <option value="0" selected disabled>Select Sub-Type</option>
                                    <option value="visitorReport">Visitor Report</option>
                                    <option value="visitorSummaryReport">Visitor Summary Report</option>
                                </select>
                                <label>Sub Type</label>
                            </span>
                            <div class="errorMsg" id="visitorSubTypeError"></div>
                        </div>
                    </div>
                    @endif

                    {{-- SUBTYPE --}}
                    @if ($tours !== false)
                    <div class="col-md-4 col-lg-3 subType validationCheck">
                        <div class="form-group-wrap">
                            <span class="has-float-label">
                                <select class="custom-input checkAll changeEvent" name="subtype" id="subtype">
                                    <option value="0" selected disabled>Select Sub-Type</option>
                                    <option value="DailyTour">Daily Tour Report</option>
                                    <option value="tourDayWise">Day Wise Tour Report</option>
                                    <option value="SummaryReport">Summary Report</option>
                                </select>
                                <label>Sub Type</label>
                            </span>
                            <div class="errorMsg" id="subTypeError"></div>
                        </div>
                    </div>
                    @endif

                    {{-- INCIDENT SUBTYPE --}}
                    @if ($inci !== false)
                    <div class="col-md-4 col-lg-3 incidentSubType validationCheck">
                        <div class="form-group-wrap">
                            <span class="has-float-label">
                                <select class="custom-input checkAll changeEvent" name="incidentSubType"
                                    id="incidentSubType">
                                    <option value="0" selected disabled>Select Sub-Type</option>
                                    <option value="incidenceReport">Incidence Report</option>
                                    <option value="incidenceSummaryReport">Incidence Summary Report</option>
                                </select>
                                <label>Sub Type</label>
                            </span>
                            <div class="errorMsg" id="incidentSubTypeError"></div>
                        </div>
                    </div>

                    {{-- INCIDENCE PRIORITY --}}
                    <div class="col-md-4 col-lg-3 incidencePriority validationCheck">
                        <div class="form-group-wrap">
                            <span class="has-float-label">
                                <select class="custom-input checkAll changeEvent" name="incidencePriority"
                                    id="incidencePriority">
                                    <option value="0" selected disabled>Select priority</option>
                                    <option value="All">All</option>
                                    <option value="High">High</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Low">Low</option>
                                </select>
                                <label>Incidence Priority</label>
                            </span>
                            <div class="errorMsg" id="incidencePriorityError"></div>
                        </div>
                    </div>
                    @endif

                    {{-- TOUR SUBTYPE --}}
                    @if ($tourdiary !== false || $clvisit !== false)
                    <div class="col-md-4 col-lg-3 tourSubType validationCheck">
                        <div class="form-group-wrap">
                            <span class="has-float-label">
                                <select class="custom-input checkAll changeEvent" name="tourSubType"
                                    id="tour-subtype">
                                    <option value="0" selected disabled>Select Sub-Type</option>
                                    @if ($user->role_id !== 1)
                                    <option value="selftourdiaryreport">Self Tour Diary Report</option>
                                    @endif
                                    <option value="tourdiaryreport">All Employee Tour Diary Report</option>
                                    @if ($user->role_id !== 2)
                                    <option value="supervisortourdiaryreport">Supervisor Tour Diary Report
                                    </option>
                                    @endif
                                    @if ($user->role_id == 1)
                                    <option value="admintourdiaryreport">Admin Tour Diary Report</option>
                                    @endif
                                </select>
                                <label>Sub Type</label>
                            </span>
                            <div class="errorMsg" id="tour-subtype"></div>
                        </div>
                    </div>
                    @endif

                    {{-- ATTENDANCE SUBTYPE --}}
                    @if ($atten !== false)
                    <div class="col-md-4 col-lg-3 attendanceSubType validationCheck">
                        <div class="form-group-wrap">
                            <span class="has-float-label">
                                <select class="custom-input checkAll changeEvent" name="attendanceSubType"
                                    id="attendanceSubType">
                                    <option value="0" selected disabled>Select Sub-Type</option>
                                    @if ($user->role_id == 2)
                                    <option value="self">Self Attendance Report</option>
                                    @endif
                                    <option value="EmployeeAttendanceReport">Employee Attendance Report</option>
                                    <option value="EmployeeAttendanceReportwithSite">Employee Attendance with Site
                                    </option>
                                    <option value="EmployeeAttendanceReportwithHours">Employee Attendance with
                                        Hours
                                    </option>
                                    <option value="onSiteAttendanceReport">On-Site Attendance Report</option>
                                    <option value="forgetToMarkExit">Forgot to Mark Exit</option>
                                    <option value="absentReport">Absent Report</option>
                                    <option value="lateReport">Late Report</option>
                                    @if ($user->role_id == '1' || $user->role_id == 7)
                                    <option value="supervisorAttendance">Supervisor Attendance</option>
                                    @endif
                                    <option value="workingSummary">Working Summary Report</option>
                                </select>
                                <label>Sub Type</label>
                            </span>
                            <div class="errorMsg" id="attendanceSubTypeError"></div>
                        </div>
                    </div>
                    @endif

                    {{-- PATROLLING SUBTYPE --}}
                    @if ($patrol !== false)
                    <div class="col-md-4 col-lg-3 patrollingReportSubType validationCheck">
                        <div class="form-group-wrap">
                            <span class="has-float-label">
                                <select class="custom-input checkAll changeEvent" name="patrollingReportSubType"
                                    id="patrollingReportSubType">
                                    <option value="0" selected disabled>Select Sub-Type</option>
                                    <option value="patrolling_status_report">Patrolling Status Report</option>
                                    <option value="patrolling_summary_report">Patrolling Summary Report</option>
                                    <option value="patrol_logs">Patrol Logs</option>
                                </select>
                                <label>Patrolling Sub-Type</label>
                            </span>
                            <div class="errorMsg" id="patrollingReportSubTypeError"></div>
                        </div>
                    </div>

                    {{-- PATROL LOGS TYPE --}}
                    <div class="col-md-4 col-lg-3 patrolLogsType validationCheck">
                        <div class="form-group-wrap">
                            <span class="has-float-label">
                                <select class="custom-input checkAll changeEvent" name="patrolLogsType"
                                    id="patrolLogsType">
                                    <option value="0" selected disabled>Select Log Type</option>
                                    <option value="all">All Logs</option>
                                    <option value="animal_sighting">Animal Sighting</option>
                                    <option value="animal_mortality">Animal Mortality</option>
                                    <option value="water_source">Water Source</option>
                                    <option value="human_impact">Human Impact</option>
                                </select>
                                <label>Patrol Log Type</label>
                            </span>
                            <div class="errorMsg" id="patrolLogsTypeError"></div>
                        </div>
                    </div>
                    @endif

                    {{-- CLIENT SELECT --}}
                    @if ($user->role_id == 1 || $user->role_id == 7)
                    <div class="col-md-4 col-lg-3 clientSelect validationCheck">
                        <div class="form-group-wrap">
                            <span class="has-float-label">
                                <select class="custom-input checkAll changeEvent" name="client"
                                    id="clientSelect">
                                    <option value="0" selected disabled>Select Client / Range</option>
                                    @foreach ($clients as $key => $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                                <label>Client / Range</label>
                            </span>
                            <div class="errorMsg" id="clientSelectError"></div>
                        </div>
                    </div>

                    <div class="col-md-4 col-lg-3 geofencesSelect validationCheck">
                        <div class="form-group-wrap">
                            <span class="has-float-label">
                                <select class="custom-input checkAll changeEvent" name="geofences"
                                    id="geofencesSelect">
                                    <option value="0" selected disabled>Select Site / Beat</option>
                                    <option value="all">All</option>
                                </select>
                                <label>Site / Beat</label>
                            </span>
                            <div class="errorMsg" id="geofencesSelectError"></div>
                        </div>
                    </div>
                    @else
                    <div class="col-md-4 col-lg-3 geofencesSelect validationCheck">
                        <div class="form-group-wrap">
                            <span class="has-float-label">
                                <select class="custom-input checkAll changeEvent clientGeofence" name="geofences"
                                    id="geofencesSelect">
                                    <option value="0" selected disabled>Select Site</option>
                                    <option value="all">All</option>
                                    @foreach ($sites as $key => $site)
                                    <option value="{{ $site->id }}">{{ $site->name }}</option>
                                    @endforeach
                                </select>
                                <label>Site</label>
                            </span>
                            <div class="errorMsg" id="geofencesSelectError"></div>
                        </div>
                    </div>
                    @endif

                    {{-- SUPERVISOR SELECT --}}
                    <div class="col-md-4 col-lg-3 supervisorSelect validationCheck">
                        <div class="form-group-wrap">
                            <span class="has-float-label">
                                <select class="custom-input checkAll changeEvent" name="supervisor"
                                    id="supervisorSelect">
                                    <option value="0" selected disabled>Select Supervisor</option>
                                    <option value="all">All</option>
                                    @if ($user->role_id == 7)
                                    @foreach ($supervisors as $key => $supervisor)
                                    <option value="{{ $supervisor->user_id }}">{{ $supervisor->name }}</option>
                                    @endforeach
                                    @else
                                    @foreach ($supervisors as $key => $supervisor)
                                    <option value="{{ $supervisor->id }}">{{ $supervisor->name }}</option>
                                    @endforeach
                                    @endif
                                </select>
                                <label>Supervisor</label>
                            </span>
                            <div class="errorMsg" id="supervisorSelectError"></div>
                        </div>
                    </div>

                    {{-- ADMIN SELECT --}}
                    @if ($user->role_id == 1)
                    <div class="col-md-4 col-lg-3 adminSelect validationCheck">
                        <div class="form-group-wrap">
                            <span class="has-float-label">
                                <select class="custom-input checkAll changeEvent" name="admin"
                                    id="adminSelect">
                                    <option value="0" selected disabled>Select Admin</option>
                                    <option value="all">All</option>
                                    @foreach ($admins as $key => $admin)
                                    <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                                    @endforeach
                                </select>
                                <label>Admin</label>
                            </span>
                            <div class="errorMsg" id="adminSelectError"></div>
                        </div>
                    </div>
                    @endif

                    {{-- SUPERVISOR ID --}}
                    <div class="col-md-4 col-lg-3 supervisorID validationCheck">
                        <div class="form-group-wrap">
                            <span class="has-float-label">
                                <select class="custom-input changeEvent" name="supervisorID" id="supervisorID">
                                    <option value="0" selected disabled>Select Supervisor</option>
                                </select>
                                <label>Supervisor Level 2</label>
                            </span>
                            <div class="errorMsg" id="supervisorIDError"></div>
                        </div>
                    </div>

                    {{-- GUARD SELECT --}}
                    <div class="col-md-4 col-lg-3 guardSelect validationCheck">
                        <div class="form-group-wrap">
                            <span class="has-float-label">
                                <select class="custom-input changeEvent" name="guard" id="guardSelect">
                                    <option value="0" selected disabled>Select Employee</option>
                                </select>
                                <label>Employee</label>
                            </span>
                            <div class="guardErrorMsg" id="guardSelectError"></div>
                        </div>
                    </div>

                    {{-- DATES --}}
                    <div class="col-md-4 col-lg-3 tourdateSelectInput validationCheck" id="tourdateSelect">
                        <div class="form-group-wrap">
                            <span class="has-float-label">
                                <input type="date" name="tourDate" class="custom-input changeEvent"
                                    id="tourdateSelectInput" />
                                <label for="tourDate">Tour Date</label>
                            </span>
                            <div class="errorMsg" id="tourdateSelectInputError"></div>
                        </div>
                    </div>

                    <div class="col-md-4 col-lg-3 fromdateSelectInput validationCheck" id="fromdateSelect">
                        <div class="form-group-wrap">
                            <span class="has-float-label">
                                <input type="date" name="fromDate" class="custom-input changeEvent"
                                    id="fromdateSelectInput" />
                                <label for="fromDate">From Date</label>
                            </span>
                            <div class="errorMsg" id="fromdateSelectInputError"></div>
                        </div>
                    </div>

                    <div class="col-md-4 col-lg-3 todateSelectInput validationCheck" id="todateSelect">
                        <div class="form-group-wrap">
                            <span class="has-float-label">
                                <input type="date" name="toDate" class="custom-input changeEvent"
                                    id="todateSelectInput" />
                                <label for="toDate">To Date</label>
                            </span>
                            <div class="errorMsg" id="todateSelectInputError"></div>
                        </div>
                    </div>

                </div>
            </form>
        </div>

        <div class="p-4"
            style="background: var(--table-header); border-top: 1px solid var(--border-color); border-radius: 0 0 12px 12px; display: flex; justify-content: flex-end;">
            <button class="btn-sapphire" onclick="showReport()" type="button">
                <i class="bi bi-search"></i> Generate Report
            </button>
        </div>
    </div>
</div>

{{-- Loading Overlay --}}
<div id="pageLoader" class="custom-loader-overlay" style="display:none;">
    <div class="spinner-border mb-3" style="color: var(--sapphire-primary); width: 3rem; height: 3rem;"
        role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <h5 style="color: var(--text-main); font-weight: 600;">Processing Report...</h5>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Fetching data based on your filters.</p>
</div>



{{-- Model View --}}
<div id="ajaxLoader" class="loader" style="display:none;"></div>

{{-- Modern Sapphire Modal 1 --}}
<div id="viewModal" class="modal fade" data-bs-backdrop="static" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog sapphire-modal-dialog" id="reportModalDialog">
        <div class="modal-content sapphire-modal border-0">

            {{-- Action Bar (Zen Mode + Close) --}}
            <div class="modal-action-bar">
                <button class="modal-top-btn" onclick="toggleZenMode()" title="Toggle Fullscreen">
                    <i class="bi bi-arrows-fullscreen" id="zenIcon"></i> <span class="d-none d-md-inline">Zen
                        Mode</span>
                </button>
                <button class="modal-top-btn modal-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            {{-- Scrolling Content Body --}}
            <div class="modal-body p-4 d-flex flex-column" style="overflow: auto; margin-top: 40px;">
                <div id="siteWiseGuardReports" class="w-100"></div>
            </div>
        </div>
    </div>
</div>

{{-- Modern Sapphire Modal 2 (Sub Reports) --}}
<div id="stack2" class="modal fade" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog sapphire-modal-dialog">
        <div class="modal-content sapphire-modal border-0">
            <div class="modal-action-bar">
                <button class="modal-top-btn modal-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body p-4 d-flex flex-column" style="overflow-y: auto; margin-top: 40px;">
                <div id="subReport" class="w-100"></div>
            </div>
        </div>
    </div>
</div>




<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Keep ALL original jQuery/AJAX logic exactly intact
    $('.visitorSubType').hide();
    $('.subType').hide();
    $('.incidentSubType').hide();
    $('.incidencePriority').hide();
    $('.attendanceSubType').hide();
    $('.tourSubType').hide();
    $('.clientSelect').hide();
    $('.geofencesSelect').hide();
    $('.supervisorSelect').hide();
    $('.adminSelect').hide();
    $('.supervisorID').hide();
    $('.guardSelect').hide();
    $('#tourdateSelect').hide();
    $('#fromdateSelect').hide();
    $('#todateSelect').hide();
    $('.patrollingReportSubType').hide();
    $('.patrolLogsType').hide();

    $(document).ready(function() {
        const userRoleId = '{{ $user->role_id }}';

        // Initialize Select2 with strict class scoping to avoid overriding globals
        if ($.fn.select2) {
            $("#guardSelect").select2({
                width: '100%'
            });
        }

        $('#typeSelect').on('change', function() {
            // Reset all sub-type containers
            $('.visitorSubType, .subType, .incidentSubType, .tourSubType, .incidencePriority, .attendanceSubType, .patrollingReportSubType, .patrolLogsType')
                .hide();

            document.getElementById("geofencesSelect").value = "0";
            let type = $(this).val();

            if (userRoleId == '2') {
                $('.clientSelect').hide()
                if (type) {
                    $('.geofencesSelect').show()
                    $('.guardSelect').show()
                }
            } else {
                $('.clientSelect').show()
                $('.geofencesSelect').hide()
                $('.guardSelect').hide();
            }

            $("#clientSelect option[value='all']").remove();

            if (type == 'visitor') {
                $('#visitorSubType').on('change', function() {
                    let subtype = $(this).val();
                    $('.subTypeSelect').hide();
                    $('.guardSelect').hide();
                    $('.supervisorSelect').hide();
                    $('.adminSelect').hide();
                    $('.geofencesSelect').show();
                    $('#todateSelect').show();
                    $('#fromdateSelect').show();
                    $('.durationSelect').hide();
                    $('#tourdateSelect').hide();
                });
                $('.attendanceSubType').hide();
                $('.patrollingSubType').hide();
                $('.tourSubType').hide();
                $('.subType').hide();
                $('.incidentSubType').hide();
                $('.visitorSubType').show();
                $('.incidencePriority').hide();
                $('.supervisorID').hide();

            } else if (type == 'tour') {
                $('#subtype').on('change', function() {
                    let subtype = $(this).val();
                    if (subtype == 'DailyTour') {
                        $('#tourdateSelect').show();
                        $('#todateSelect').hide();
                        $('#fromdateSelect').hide();
                    } else if (subtype == 'tourDayWise' || subtype == 'guardTourReport') {
                        $('#tourdateSelect').hide();
                        $('#todateSelect').show();
                        $('#fromdateSelect').show();
                    } else {
                        $('#tourdateSelect').hide();
                        $('#todateSelect').show();
                        $('#fromdateSelect').show();
                    }
                });
                $('.geofencesSelect').show();
                $('.supervisorSelect').hide();
                $('.adminSelect').hide();
                $('.durationSelect').hide();
                $('.guardSelect').hide();
                $('.subType').show();
                $('.incidentSubType').hide();
                $('.attendanceSubType').hide();
                $('.patrollingSubType').hide();

                $('.tourSubType').show();
                $('.visitorSubType').hide();
                $('.incidencePriority').hide();
                $('.supervisorID').hide();

            } else if (type == 'patrolling') {
                $('.patrollingReportSubType').show();
                $('.patrolLogsType').hide();
                $('.attendanceSubType').hide();
                $('.tourSubType').hide();

                $('#patrollingReportSubType').val('0');
                $('#clientSelect').val('0');
                $('#geofencesSelect').empty().append(
                    '<option value="0" selected disabled>Select site/ beat</option>');
                $('#guardSelect').empty().append(
                    '<option value="0" selected disabled>Select employee</option>');

                $('.clientSelect').hide();
                $('.geofencesSelect').hide();
                $('.guardSelect').hide();
                $('#fromdateSelect').show();
                $('#todateSelect').show();

                $('.supervisorSelect').hide();
                $('.supervisorID').hide();

                $('#patrollingReportSubType').on('change', function() {
                    let subType = $(this).val();

                    if (subType === 'patrol_logs') {
                        $('.patrolLogsType').show();
                        $('.clientSelect').hide();
                        $('.geofencesSelect').hide();
                        $('.guardSelect').hide();
                        $('.supervisorSelect').hide();
                        $('.supervisorID').hide();
                    } else {
                        $('.supervisorSelect').hide();
                        $('.supervisorID').hide();
                        $('.patrolLogsType').hide();
                        $('.clientSelect').show();
                        $("#clientSelect option[value='all']").remove();
                        $("#clientSelect option:first").after(new Option("All Clients", "all"));
                    }
                });

                $('#patrolLogsType').on('change', function() {
                    if ($(this).val() !== '0') {
                        $('.clientSelect').show();
                        $("#clientSelect option[value='all']").remove();
                        $("#clientSelect option:first").after(new Option("All Clients", "all"));
                    } else {
                        $('.clientSelect').hide();
                    }
                    $('.geofencesSelect').hide();
                    $('.guardSelect').hide();
                });

            } else if (type == 'attendance') {
                if (userRoleId == '2') {
                    $('.geofencesSelect').show();
                    $('.guardSelect').show();
                }

                $('#attendanceSubType').on('change', function() {
                    var siteValue = $('#geofencesSelect').val();
                    var attendancesubtype = $(this).val();
                    $("#clientSelect option[value='all']").remove();

                    if (attendancesubtype == 'EmployeeAttendanceReportwithSite') {
                        $("#clientSelect option:first").after(new Option("All", "all"));
                        $("#clientSelect option[value='all']").attr("selected", "selected");
                        $('.clientSelect').show();
                        $('.supervisorSelect').hide();
                        $('.adminSelect').hide();
                        $('.supervisorID').hide();
                    } else if (attendancesubtype == 'EmployeeAttendanceReportwithHours') {
                        $("#clientSelect option:first").after(new Option("All", "all"));
                        $("#clientSelect option[value='all']").attr("selected", "selected");
                        $(".clientSelect").show();
                        $('.supervisorID').hide();
                        $('.supervisorSelect').hide();
                        $('.adminSelect').hide();
                        $('.guardSelect').show();
                    } else if (attendancesubtype == 'supervisorAttendance') {
                        var attendancesubtype = $(this).val();
                        $(".clientSelect").hide();
                        $(".geofencesSelect").hide();
                        $(".guardSelect").hide();
                        $(".supervisorSelect").show();
                        $('.adminSelect').hide();
                    } else if (attendancesubtype == 'onSiteAttendanceReport' ||
                        attendancesubtype == 'forgetToMarkExit') {
                        $("#clientSelect option:first").after(new Option("All", "all"));
                        $("#clientSelect option[value='all']").attr("selected", "selected");
                        $(".clientSelect").show();
                        $('.geofencesSelect').show();
                        $('.guardSelect').show();
                    } else if (attendancesubtype == 'EmployeeAttendanceReport') {
                        $("#clientSelect option:first").after(new Option("All", "all"));
                        $("#clientSelect option[value='all']").attr("selected", "selected");
                        $(".clientSelect").show();
                        $('.supervisorID').hide();
                        $('.guardSelect').show();
                        $('.supervisorSelect').hide();
                        $('.adminSelect').hide();
                    } else if (attendancesubtype == 'lateReport' || attendancesubtype ==
                        'absentReport' || attendancesubtype == 'workingSummary') {
                        if ($("#clientSelect option[value='all']").length === 0) {
                            $("#clientSelect option:first").after(new Option("All", "all"));
                        }
                        $("#clientSelect option[value='all']").attr("selected", "selected");
                        $(".clientSelect").show();
                        $(".geofencesSelect").show();
                        $('.supervisorSelect').hide();
                        $('.adminSelect').hide();
                        $(".guardSelect").show();
                        $(".supervisorID").hide();
                    } else if (attendancesubtype == 'self') {
                        $(".clientSelect").hide();
                        $(".geofencesSelect").hide();
                        $('.supervisorSelect').hide();
                        $('.adminSelect').hide();
                        $(".guardSelect").hide();
                        $(".supervisorID").hide();
                    } else {
                        $(".clientSelect").show();
                        $("#clientSelect option[value='all']").remove();
                        $('.guardSelect').hide();
                        $('.supervisorID').hide();
                        $('.supervisorSelect').hide();
                        $('.adminSelect').hide();
                    }

                    $('#clientSelect').trigger("change");
                    $('.subTypeSelect').show();
                    $('.durationSelect').show();
                    $('.guardSelect').hide();
                    $('#todateSelect').show();
                    $('#fromdateSelect').show();
                    $('#tourdateSelect').hide();
                });
                $('.attendanceSubType').show();
                $('.patrollingReportSubType').hide();
                $('.subType').hide();
                $('.incidentSubType').hide();
                $('.visitorSubType').hide();
                $('.incidencePriority').hide();
                $('.supervisorID').hide();
                $('.tourSubType').hide();

            } else if (type == 'incident') {
                $('#incidentSubType').on('change', function() {
                    var priority = $(this).val();
                    if (priority == 'incidenceSummaryReport') {
                        $(".clientSelect").show();
                        $("#clientSelect option:first").after(new Option("All", "all"));
                        $("#clientSelect option[value='all']").attr("selected", "selected");
                        $('.subTypeSelect').hide();
                        $('.durationSelect').show();
                        $('.supervisorSelect').hide();
                        $('.adminSelect').hide();
                        $('#todateSelect').show();
                        $('#fromdateSelect').show();
                        $('#tourdateSelect').hide();
                        $('.guardSelect').hide();
                        $('.geofencesSelect').show();
                        $('.subType').hide();
                        $('.incidencePriority').hide();
                        $('.tourSubType').hide();
                    } else {
                        $("#clientSelect option:first").after(new Option("All", "all"));
                        $("#clientSelect option[value='all']").attr("selected", "selected");
                        $('.subTypeSelect').hide();
                        $('.durationSelect').show();
                        $('.supervisorSelect').hide();
                        $('.adminSelect').hide();
                        $('#todateSelect').show();
                        $('.supervisorID').hide();
                        $('#fromdateSelect').show();
                        $('#tourdateSelect').hide();
                        $('.guardSelect').hide();
                        $('.clientSelect').show();
                        $('.subType').hide();
                        $('.incidencePriority').show();
                        $('.tourSubType').hide();
                    }
                });
                $('.incidentSubType').show();
                $('.subType').hide();
                $('.attendanceSubType').hide();
                $('.visitorSubType').hide();
                $('.supervisorID').hide();
                $('.tourSubType').hide();

            } else if (type == 'performance') {
                $("#clientSelect option:first").after(new Option("All", "all"));
                $("#clientSelect option[value='all']").attr("selected", "selected");
                $('.clientSelect').show();
                $('.geofencesSelect').hide();
                $('.subTypeSelect').hide();
                $('.durationSelect').hide();
                $('.supervisorSelect').hide();
                $('.adminSelect').hide();
                $('#todateSelect').show();
                $('#fromdateSelect').show();
                $('#tourdateSelect').hide();
                $('.guardSelect').hide();
                $('.subType').hide();
                $('.incidencePriority').hide();
                $('.attendanceSubType').hide();
                $('.incidentSubType').hide();
                $('.visitorSubType').hide();
                $('.tourSubType').hide();

            } else if (type == 'visits' || type == 'tourdiary') {
                $(".clientSelect").show();
                $("#clientSelect option:first").after(new Option("All", "all"));
                $("#clientSelect option[value='all']").attr("selected", "selected");
                $('#todateSelect').show();
                $('#fromdateSelect').show();
                $('.subTypeSelect').hide();
                $('.durationSelect').hide();
                $('.supervisorSelect').hide();
                $('.adminSelect').hide();
                $('#tourdateSelect').hide();
                $('.guardSelect').hide();
                $('.subType').hide();
                $('.incidencePriority').hide();
                $('.attendanceSubType').hide();
                $('.tourSubType').show();
                $('.incidentSubType').hide();
                $('.visitorSubType').hide();
                $('.geofencesSelect').hide();

                $('#tour-subtype').on('change', function() {
                    var tourdiarysubtype = $(this).val();
                    if (tourdiarysubtype == 'selftourdiaryreport') {
                        $('.clientSelect').hide();
                        $('.supervisorSelect').hide();
                        $('.adminSelect').hide();
                        $('.supervisorID').hide();
                        $('.guardSelect').hide();
                    } else if (tourdiarysubtype == 'tourdiaryreport') {
                        $(".clientSelect").show();
                        $('.supervisorID').hide();
                        $('.supervisorSelect').hide();
                        $('.adminSelect').hide();
                        $('.guardSelect').hide();
                    } else if (tourdiarysubtype == 'supervisortourdiaryreport') {
                        $(".clientSelect").hide();
                        $('.supervisorSelect').show();
                        $('.adminSelect').hide();
                        $('.guardSelect').hide();
                    } else if (tourdiarysubtype == 'admintourdiaryreport') {
                        $(".clientSelect").hide();
                        $('.supervisorSelect').hide();
                        $('.adminSelect').show();
                        $('.guardSelect').hide();
                    }
                });

            }

            if (userRoleId == '2') {
                $('.clientSelect').hide();
                $('.geofencesSelect').show();
                $('.guardSelect').show();
            }
        });

        $('#durationSelect').on('change', function() {
            let duration = $(this).val();
            if (duration == 'Monthly') {
                $('#monthSelect').show();
                $('#todateSelect').hide();
                $('#fromdateSelect').hide();
                $('#tourdateSelect').hide();
            } else {
                $('#monthSelect').hide();
                $('#todateSelect').show();
                $('#fromdateSelect').show();
                $('#tourdateSelect').hide();
            }
        });

        $('#clientSelect').on('change', function() {
            let id = $(this).val();
            let reportType = $('#typeSelect').val();

            if (reportType === 'patrolling') {
                $('.supervisorID').hide();
                if (id === 'all') {
                    $('.geofencesSelect').hide();
                    $('.guardSelect').hide();
                } else {
                    $('.geofencesSelect').show();
                    $('.guardSelect').hide();

                    var url = `{{ route('clientSite', ':id') }}`;
                    url = url.replace(':id', id);
                    $.ajax({
                        type: 'GET',
                        url: url,
                        success: function(response) {
                            var response = JSON.parse(response);
                            $('#geofencesSelect').empty();
                            $('#geofencesSelect').append(
                                `<option value="0" disabled selected>Select site/ beat</option>`
                            );
                            $('#geofencesSelect').append(
                                `<option value="all">All</option>`);
                            if (response != '') {
                                response.forEach(element => {
                                    $('#geofencesSelect').append(
                                        `<option value="${element['id']}">${element['name']}</option>`
                                    );
                                });
                            }
                        }
                    });
                }
                return;
            }

            if (userRoleId != '2') {
                if (id == 'all') {
                    $('.guardSelect').hide();
                    $('.geofencesSelect').hide();
                    $('.supervisorID').hide();
                } else {
                    $('.guardSelect').show();
                    $('.supervisorID').show();
                    if ($('#attendanceSubType').val() != "supervisorAttendance")
                        $('.geofencesSelect').show();
                }
            }

            var url = `{{ route('clientSite', ':id') }}`;
            url = url.replace(':id', id);
            $.ajax({
                type: 'GET',
                url: url,
                data: {
                    id: id
                },
                success: function(response) {
                    var response = JSON.parse(response);
                    $('#geofencesSelect').empty();
                    if ($('#incidentSubType').val() == 'incidenceSummaryReport' ||
                        $('#incidentSubType').val() == 'incidenceReport' ||
                        $('#typeSelect').val() == 'visits' ||
                        $('#typeSelect').val() == 'performance' ||
                        $('#attendanceSubType').val() ==
                        'EmployeeAttendanceReportwithHours' ||
                        $('#attendanceSubType').val() == 'EmployeeAttendanceReport' ||
                        $('#attendanceSubType').val() ==
                        'EmployeeAttendanceReportwithSite' ||
                        $('#attendanceSubType').val() == 'onSiteAttendanceReport' ||
                        $('#attendanceSubType').val() == 'workingSummary' ||
                        $('#attendanceSubType').val() == 'absentReport' ||
                        $('#attendanceSubType').val() == 'lateReport' ||
                        $('#attendanceSubType').val() == 'forgetToMarkExit' ||
                        $('#attendanceSubType').val() == 'supervisorAttendance' ||
                        $('#typeSelect').val() == 'tourdiary') {

                        $('#geofencesSelect').append(
                            `<option value="all" selected>All</option>`);
                        $('.supervisorID').hide();
                        $('.guardSelect').hide();
                    } else {
                        $('#geofencesSelect').append(
                            `<option value="0" disabled selected>Select site</option>`);
                    }
                    if (response != '') {
                        response.forEach(element => {
                            $('#geofencesSelect').append(
                                `<option value="${element['id']}">${element['name']}</option>`
                            );
                        });
                    } else {
                        $('#geofencesSelect').empty();
                        $('#geofencesSelect').append(
                            `<option value="0" disabled selected>Select site</option>`);
                    }
                }
            });
        });

        $('#geofencesSelect').on('change', function() {
            let id = $(this).val();
            var type = $('#typeSelect').val();
            var subtype = $('#subtype').val();
            var client = $('#clientSelect').val();
            var attendanceSubType = $('#attendanceSubType').val();

            var url = `{{ route('report.supervisor', ':id') }}`;
            url = url.replace(':id', id);
            $.ajax({
                type: 'GET',
                url: url,
                data: {
                    id: id
                },
                success: function(response) {
                    var response = JSON.parse(response);
                    if (id == 'all') {
                        $('.guardSelect').hide();
                    }
                    if (response != '') {
                        $('#supervisorID').empty();
                        $('#supervisorID').append(
                            `<option value="0" disabled selected>Select supervisor</option>`
                        );
                        response.forEach(element => {
                            $('#supervisorID').append(
                                `<option value="${element['id']}">${element['name']}</option>`
                            );
                        });
                    } else {
                        $('#supervisorID').empty();
                        $('#supervisorID').append(
                            `<option value="0" selected disabled>No Supervisor Assigned</option>`
                        );
                    }
                }
            });

            var urlGuard = `{{ route('report.guard', ':id') }}`;
            urlGuard = urlGuard.replace(':id', id);
            $.ajax({
                type: 'GET',
                url: urlGuard,
                data: {
                    id: id
                },
                success: function(response) {
                    var response = JSON.parse(response);
                    $('#guardSelect').empty();
                    if (response != '') {
                        $('#guardSelect').append(
                            `<option value="0" selected disabled>Select employee</option>`
                        );
                        $('#guardSelect').append(`<option value="all">All</option>`);
                        response.forEach(element => {
                            $('#guardSelect').append(
                                `<option value="${element['id']}">${element['name']}</option>`
                            );
                        });
                    } else {
                        $('#guardSelect').append(
                            `<option value="0" selected disabled>No Employee</option>`);
                    }
                    var guards = $('#guardSelect').val();
                }
            });

            if (type == 'visitor') {
                $('.guardSelect').hide();
            } else if (type == 'incident') {
                $('.guardSelect').hide();
            } else if (type == 'performance') {
                $('.guardSelect').hide();
            } else if (type == 'tour') {
                $('.guardSelect').hide();
                if (subtype == "tourDayWise" || subtype == 'DailyTour') {
                    $('.guardSelect').show();
                }
            } else if (id == 'all') {
                $('.guardSelect').hide();
            } else {
                $('.guardSelect').show();
            }

            if (type == 'attendance') {
                if (attendanceSubType == 'onSiteAttendanceReport' || attendanceSubType ==
                    'forgetToMarkExit' || attendanceSubType == 'workingSummary' || attendanceSubType ==
                    'supervisorAttendance') {
                    $('.guardSelect').hide();
                } else {
                    if (client == 'all') {
                        $('.guardSelect').hide();
                    } else {
                        $('.guardSelect').show();
                    }
                }
            }
        });
    });

    $(".changeEvent").change(function() {
        validation('onchange');
    });

    $('#viewModal').on('show.bs.modal', function() {
        $(this).appendTo('body'); // ✅ THIS FIXES EVERYTHING
        document.body.classList.add('modal-open-custom');
    });

    $('#viewModal').on('hidden.bs.modal', function() {
        document.body.classList.remove('modal-open-custom');
    });

    $('#stack2').on('show.bs.modal', function() {
        $(this).appendTo('body');
    });


    function validation(flag) {
        var divs = document.getElementsByClassName("validationCheck");

        for (var i = 0; i < divs.length; i++) {

            var fieldClass = divs[i].classList[1];
            var selector = '#' + fieldClass;
            var errorSelector = selector + 'Error';

            var dataCheck = $(selector).val();

            // ✅ Skip if field not visible
            if (!$(selector).is(":visible")) {
                continue;
            }

            // ✅ Normalize value
            if (typeof dataCheck === "string") {
                dataCheck = dataCheck.trim();
            }

            // ✅ FIXED CONDITION (NO "0" CHECK)
            if (dataCheck === null || dataCheck === "" || dataCheck === undefined) {

                if (flag === 'show') {
                    var errorElem = document.getElementById(fieldClass);
                    if (errorElem) errorElem.style.visibility = "visible";

                    $(errorSelector).html("* Required");
                    $(selector).addClass("makeRedd");
                }

            } else {

                $(selector).removeClass("makeRedd");

                var errorElem = document.getElementById(fieldClass + "Error");
                if (errorElem) errorElem.style.visibility = "hidden";

                $(errorSelector).html("");
            }
        }
    }

    function toggleZenMode() {
        const dialog = document.getElementById('reportModalDialog');
        const icon = document.getElementById('zenIcon');

        dialog.classList.toggle('zen-active');

        if (dialog.classList.contains('zen-active')) {
            icon.classList.replace('bi-arrows-fullscreen', 'bi-fullscreen-exit');
        } else {
            icon.classList.replace('bi-fullscreen-exit', 'bi-arrows-fullscreen');
        }
    }


    window.showReport = function() {
        console.log("CLICK WORKING");

        validation('show');

        var noError = document.getElementsByClassName("makeRedd");

        console.log("Errors found:", noError.length); // 👈 ADD THIS

        if (noError.length === 0) {
            console.log("Calling showModal()");
            showModal();
        } else {
            console.log("Blocked by validation");
        }
    };

    function showModal() {
        var type = $('#typeSelect').val();
        var supervisor = $('#supervisorID').val();
        var guard = $('#guardSelect').val();
        var geofences = $('#geofencesSelect').val();
        var fromDate = $('#fromdateSelectInput').val();
        var toDate = $('#todateSelectInput').val();
        var incidentSubType = $('#incidentSubType').val();
        var incidencePriority = $('#incidencePriority').val();
        var visitorSubType = $('#visitorSubType').val();
        var tourSubType = $('#tour-subtype').val();
        var tourDate = $('#tourdateSelectInput').val();
        var attendanceSubType = $('#attendanceSubType').val();
        var clientSelect = $('#clientSelect').val();
        var supervisorSelect = $('#supervisorSelect').val();
        var adminSelect = $('#adminSelect').val();
        var patrollingReportSubType = $('#patrollingReportSubType').val();
        var patrolLogsType = $('#patrolLogsType').val();

        var url = `{{ route('incidence.incidenceExport') }}`;
        console.log("TYPE:", type);
        console.log("ATTENDANCE SUBTYPE:", attendanceSubType);
        $.ajax({
            type: 'get',
            datatype: 'json',
            url: url,
            data: {
                _token: '{{ csrf_token() }}',
                type: type,
                supervisor: supervisor,
                guard: guard,
                geofences: geofences,
                fromDate: fromDate,
                toDate: toDate,
                incidentSubType: incidentSubType,
                incidencePriority: incidencePriority,
                visitorSubType: visitorSubType,
                tourSubType: tourSubType,
                patrollingReportSubType: patrollingReportSubType,
                patrolLogsType: patrolLogsType,
                tourDate: tourDate,
                attendanceSubType: attendanceSubType,
                client: clientSelect,
                supervisorSelect: supervisorSelect,
                adminSelect: adminSelect
            },

            beforeSend: function() {
                $('#loader').css('display', 'flex');
            },
            success: function(response) {
                $('.loader').hide();

                // Better check for empty responses
                if (response === 'error' || response.trim() === '' || (Array.isArray(response) && response.length === 0)) {
                    // Using standard SweetAlert2 syntax
                    Swal.fire({
                        icon: 'info',
                        title: 'No Data Found',
                        text: 'No data found for the given inputs.',
                        confirmButtonColor: 'var(--sapphire-primary)'
                    });
                    return false;
                } else {
                    var modal = new bootstrap.Modal(document.getElementById('viewModal'));
                    modal.show();
                    document.getElementById("siteWiseGuardReports").innerHTML = response;

                    // 🔥 THE MAGIC SAUCE: Turn plain text into beautiful colored badges
                    setTimeout(() => {
                        $('#siteWiseGuardReports table tbody td').each(function() {
                            let text = $(this).text().trim();
                            if (text === 'A') $(this).html(
                                '<span class="status-badge badge-a">A</span>');
                            else if (text === 'P') $(this).html(
                                '<span class="status-badge badge-p">P</span>');
                            else if (text === 'WO') $(this).html(
                                '<span class="status-badge badge-wo">WO</span>');
                            else if (text === 'Routine') $(this).html(
                                '<span class="status-badge badge-routine">Routine</span>'
                            );
                            else if (text === 'Vehicle') $(this).html(
                                '<span class="status-badge badge-vehicle">Vehicle</span>'
                            );
                        });
                    }, 100); // Slight delay to ensure DOM is rendered
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX ERROR:", xhr.responseText);

                // 1. Hide the infinite loading spinner/loader
                $('#loader').hide();
                $('.loader').hide();

                // 2. Initialize and show the modal even though there is an error
                var modalElement = document.getElementById('viewModal');
                var myModal = bootstrap.Modal.getOrCreateInstance(modalElement);
                myModal.show();

                // 3. Clean and User-Friendly error message instead of JSON
                document.getElementById("siteWiseGuardReports").innerHTML = `
                        <div class="d-flex flex-column align-items-center justify-content-center py-5">
                            <i class="bi bi-exclamation-circle text-warning mb-3" style="font-size: 3rem;"></i>
                            <h5 style="color: var(--text-main); font-weight: 600;">Report Generation Issue</h5>
                            <p class="text-muted text-center px-4" style="max-width: 400px;">
                                We encountered a problem retrieving the data for these filters.
                                Please ensure the selected date range has recorded data or try a different filter.
                            </p>
                            <button class="btn btn-sm btn-outline-secondary mt-2" data-bs-dismiss="modal">Close & Retry</button>
                        </div>`;
            }
        });
    }
</script>
@endsection
@php
$hideGlobalFilters = true;
$hideBackground = true;
$user = session('user');
@endphp
@extends('layouts.app')

@section('content')
<style>
    :root,
    [data-theme="light"],
    [data-bs-theme="light"],
    body.light-mode {
        --primary-color: #3b82f6;
        --primary-hover: #2563eb;
        --bg-page: #f8fafc;
        --bg-card: #ffffff;
        --bg-input: #f1f5f9;
        --border-color: #e2e8f0;
        --text-main: #1e293b;
        --text-muted: #64748b;

        --icon-view: #3b82f6;
        --icon-edit: #22c55e;
        --icon-delete: #ef4444;

        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        --radius-md: 0.5rem;
        --radius-lg: 0.75rem;
    }

    .dark,
    .dark-mode,
    [data-theme="dark"],
    [data-bs-theme="dark"] {
        --primary-color: #3b82f6;
        --primary-hover: #60a5fa;
        --bg-page: #0f172a;
        --bg-card: #1e293b;
        --bg-input: #334155;
        --border-color: #334155;
        --text-main: #f8fafc;
        --text-muted: #94a3b8;

        --icon-view: #60a5fa;
        --icon-edit: #4ade80;
        --icon-delete: #f87171;
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.5);
    }

    body,
    html {
        overflow-y: hidden !important;
    }

    .content {

        background-color: transparent;
        color: var(--text-main);
        transition: all 0.3s ease;
        max-height: calc(100vh - 70px);
        overflow-y: auto;
        padding: 0;
        /* Let container-fluid handle horizontal padding */
    }

    .modern-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-top: 15px;
        /* Aligns correctly with the breadcrumb/header area */
        margin-bottom: 30px;
    }

    .modern-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        flex-wrap: wrap;
        gap: 16px;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .btn-back {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        color: var(--text-muted);
        text-decoration: none !important;
        transition: all 0.2s ease;
        font-size: 18px;
    }

    .btn-back:hover {
        background: var(--bg-input);
        color: var(--text-main);
    }

    .header-title-container {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .header-icon {
        color: var(--primary-color);
        font-size: 22px;
    }

    .header-title {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: var(--text-main);
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }


    .search-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-wrapper i {
        position: absolute;
        left: 12px;
        color: var(--text-muted);
    }

    .search-input {
        background: var(--bg-input);
        border: 1px solid transparent;
        border-radius: var(--radius-md);
        padding: 8px 12px 8px 36px;
        color: var(--text-main);
        font-size: 14px;
        width: 200px;
        outline: none;
        transition: background-color 0.2s, border-color 0.2s, color 0.2s;
    }

    .search-input:focus {
        border-color: var(--primary-color);
    }

    /* Buttons */
    .btn-primary {
        background: var(--primary-color);
        color: #ffffff !important;
        border: none;
        border-radius: var(--radius-md);
        padding: 9px 16px;
        font-size: 14px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
    }

    .btn-primary:hover {
        background: var(--primary-hover);
    }

    /* Table Styling */
    .table-container {
        width: 100%;
        overflow-x: auto;
    }

    .modern-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .modern-table th {
        background: transparent;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 600;
        padding: 16px 24px;
        border-bottom: 1px solid var(--border-color);
    }

    .modern-table td {
        padding: 16px 24px;
        font-size: 14px;
        color: var(--text-main);
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
    }

    .modern-table tr:last-child td {
        border-bottom: none;
    }

    .modern-table tbody tr:hover {
        background-color: var(--bg-input);
    }

    /* Action Icons */
    .action-icons-cell {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 16px;
    }

    .icon-action {
        background: transparent;
        border: none;
        cursor: pointer;
        font-size: 18px;
        padding: 0;
        transition: transform 0.2s ease, opacity 0.2s;
        display: inline-flex;
        align-items: center;
        text-decoration: none !important;
    }

    .icon-action:hover {
        transform: scale(1.1);
        opacity: 0.8;
    }

    .icon-view {
        color: var(--icon-view);
    }

    .icon-edit {
        color: var(--icon-edit);
    }

    .icon-delete {
        color: var(--icon-delete);
    }

    /* Footer Info */
    .table-footer {
        padding: 16px 24px;
        background: transparent;
        color: var(--text-muted);
        font-size: 13px;
        border-top: 1px solid var(--border-color);
    }

    @media (max-width: 768px) {
        .modern-header {
            flex-direction: column;
            align-items: stretch;
        }

        .header-right {
            justify-content: space-between;
        }

        .search-input {
            width: 100%;
        }
    }
</style>

<div class="content">
    <div class="container-fluid">
        <div class="modern-card">

            <div class="modern-header">
                <div class="header-left">
                    <a href="javascript:history.back()" class="btn-back" title="Go Back">
                        <i class="la la-arrow-left"></i>
                    </a>
                    <div class="header-title-container">
                        <i class="la la-map-marker header-icon"></i>
                        <h4 class="header-title">
                            @if(isset($siteName))
                            {{ ucfirst($siteName->name) }} —
                            @endif
                            Geofences
                        </h4>
                    </div>
                </div>

                <div class="header-right">
                    <div class="search-wrapper">
                        <i class="la la-search"></i>
                        <input type="text" class="search-input" id="tableSearch" placeholder="Search...">
                    </div>

                    @if($user->role_id != '4')
                    <a href="{{ route('clients.geofence_create', [$client_id, $site_id]) }}" class="btn-primary">
                        <i class="la la-plus"></i> Add Geofence
                    </a>
                    @endif
                </div>
            </div>

            <div class="table-container">
                <table class="modern-table example">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Geofence Name</th>
                            <th style="text-align: right; width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($geofences) && count($geofences) > 0)
                        @php $sr = 1; @endphp
                        @foreach($geofences as $row)
                        <tr>
                            <td>{{ $sr++ }}</td>
                            <td style="font-weight: 500; color: var(--text-main);">{{ ucfirst($row->name) }}</td>
                            <td>
                                <div class="action-icons-cell">
                                    {{-- Updated route to prevent undefined route error --}}
                                    <a href="{{ route('viewGeofence', $row->id) }}" class="icon-action icon-view" title="View Geofence">
                                        <i class="la la-eye"></i>
                                    </a>

                                    @if($user->role_id != '4')
                                    <a href="{{ route('clients.geofence_edit', [$client_id, $site_id, $row->id]) }}" class="icon-action icon-edit" title="Edit">
                                        <i class="la la-edit"></i>
                                    </a>
                                    <button type="button" class="icon-action icon-delete" onclick="deleteGeofence('{{$client_id}}','{{$site_id}}','{{$row['id']}}')" title="Delete">
                                        <i class="la la-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 60px 40px; color: var(--text-muted);">
                                <i class="la la-map" style="font-size: 40px; opacity: 0.5; margin-bottom: 10px; display: block;"></i>
                                No geofences found for this site.
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if(isset($geofences) && count($geofences) > 0)
            <div class="table-footer">
                Showing 1 to {{ count($geofences) }} of {{ count($geofences) }} entries
            </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
    /**
     * Helper function to bridge your code with SweetAlert2
     * This fixes the "showSweetAlert is not defined" error.
     */
    function showSweetAlert(title, text, confirmText, showCancel, cancelText) {
        return Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: showCancel,
            confirmButtonColor: '#ef4444', // Danger red
            cancelButtonColor: '#64748b', // Muted gray
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            reverseButtons: true
        }).then((result) => {
            return result.isConfirmed; // Returns true if the user clicked "Delete"
        });
    }

    async function deleteGeofence(client_id, site_id, id) {
        // Now this function will work because showSweetAlert is defined above
        var deleted = await showSweetAlert(
            'Delete Confirmation',
            'Are you sure you want to delete this geofence?',
            'Delete',
            true,
            'Cancel'
        );

        if (deleted === true) {
            // Build the URL using the route helper
            var url = '{{ route("clients.geofence_delete", [":client_id",":site_id",":id"]) }}';
            url = url.replace(':client_id', client_id)
                .replace(':site_id', site_id)
                .replace(':id', id);

            // Redirect to the delete route
            window.location.href = url;
        }
    }
</script>
@endpush
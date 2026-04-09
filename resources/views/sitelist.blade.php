@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    $user = session('user');
    $company = session('company');
    $label = isset($company) && ($company->is_forest ?? 1) == 1 ? 'Beat' : 'Site';
    // dump('hi');
@endphp
@extends('layouts.app')
@section('title', get_label('label_site', 'Sites') . ' List')
@section('content')

    <style>
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
            margin-top: 1rem;
        }

        /* Custom Form Inputs */
        .custom-input {
            background-color: var(--bg-body);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 0.85rem;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .custom-input:focus {
            border-color: var(--sapphire-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        html[data-bs-theme="dark"] .custom-input {
            color-scheme: dark;
        }

        /* Action Buttons */
        .btn-sapphire {
            background-color: var(--sapphire-primary);
            color: #ffffff;
            border: none;
            font-weight: 500;
            padding: 6px 14px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            text-decoration: none;
        }

        .btn-sapphire:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            color: #ffffff;
        }

        .btn-sapphire-outline {
            background-color: transparent;
            color: var(--text-main);
            border: 1px solid var(--border-color);
            font-weight: 500;
            padding: 6px 14px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            text-decoration: none;
        }

        .btn-sapphire-outline:hover {
            background-color: var(--table-hover);
            color: var(--sapphire-primary);
            border-color: var(--sapphire-primary);
        }

        /* Icon Action Buttons (View, Edit, Delete) */
        .btn-icon-soft {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: transparent;
            transition: all 0.2s ease;
            font-size: 1.05rem;
            text-decoration: none !important;
            cursor: pointer;
        }

        .btn-icon-soft.view {
            color: var(--sapphire-primary);
        }

        .btn-icon-soft.view:hover {
            background: rgba(59, 130, 246, 0.15);
            transform: translateY(-2px);
        }

        .btn-icon-soft.edit {
            color: var(--sapphire-success);
        }

        .btn-icon-soft.edit:hover {
            background: rgba(16, 185, 129, 0.15);
            transform: translateY(-2px);
        }

        .btn-icon-soft.delete {
            color: var(--sapphire-danger);
        }

        .btn-icon-soft.delete:hover {
            background: rgba(239, 68, 68, 0.15);
            transform: translateY(-2px);
        }

        /* Tables & Sorting */
        .dash-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .dash-table th {
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.85rem;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem;
            background-color: transparent !important;
            white-space: nowrap;
        }

        .dash-table td {
            color: var(--text-main);
            font-weight: 500;
            font-size: 0.9rem;
            border-bottom: 1px dashed var(--border-color);
            padding: 1rem;
            vertical-align: middle;
            background-color: transparent !important;
        }

        .dash-table tr:hover td {
            background-color: var(--table-hover) !important;
        }

        .dash-table tr:last-child td {
            border-bottom: none;
        }

        .sort-link {
            color: var(--text-muted);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: color 0.2s ease;
        }

        .sort-link:hover {
            color: var(--sapphire-primary);
        }

        .sort-link i {
            font-size: 0.75rem;
            opacity: 0.5;
        }

        .sort-link:hover i {
            opacity: 1;
        }

        /* Soft Badges for Management Links */
        .badge-soft {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            text-decoration: none !important;
            background: var(--bg-body);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            transition: transform 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }

        .badge-soft:hover {
            transform: translateY(-2px);
            border-color: var(--sapphire-primary);
            color: var(--sapphire-primary);
            background: rgba(59, 130, 246, 0.05);
        }

        .site-name-link {
            font-weight: 600;
            color: var(--sapphire-primary);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .site-name-link:hover {
            color: var(--text-main);
            text-decoration: underline;
        }
    </style>

    <div class="container-fluid py-4">

        <div class="dash-card p-0 overflow-hidden">

            {{-- COMPACT HEADER CONTROLS --}}
            <div class="p-3 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3"
    style="border-bottom: 1px solid var(--border-color); background: var(--bg-card);">

    {{-- Title & Back Button --}}
    <div class="d-flex align-items-center gap-3">
        @if ($client_id != 'playBackSites')
            <a href="javascript:history.back()" class="btn-sapphire-outline" style="padding: 6px 10px;"
                title="Go Back">
                <i class="bi bi-arrow-left"></i>
            </a>
        @endif
        <h5 class="fw-bold mb-0" style="color: var(--text-main);">
            @if ($client_id == 'playBackSites')
                <i class="bi bi-play-circle-fill me-2" style="color: var(--sapphire-primary);"></i> Playback
                {{-- Pluralized override (e.g., Beats) --}}
                {{ Str::plural(get_label('label_site', 'Site')) }}
            @elseif (isset($clientName))
                <i class="bi bi-geo-alt-fill me-2" style="color: var(--sapphire-primary);"></i>
                {{ ucfirst($clientName->name) }} — {{ get_label('label_site', 'Site') }} List
            @else
                <i class="bi bi-geo-alt-fill me-2" style="color: var(--sapphire-primary);"></i>
                {{ get_label('label_site', 'Site') }} List
            @endif
        </h5>
    </div>

    {{-- Search & Actions --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center gap-2">
        {{-- AJAX Search Form --}}
        <div class="d-flex gap-2 m-0">
            <div class="position-relative grow">
                <i class="bi bi-search position-absolute"
                    style="left: 12px; top: 10px; color: var(--text-muted);"></i>
                {{-- Dynamic Search Placeholder (e.g., Search beat...) --}}
                <input type="text" id="ajaxSearch" name="search" value="{{ request('search') }}"
                    class="custom-input"
                    placeholder="Search {{ strtolower(get_label('label_site', 'site')) }}..."
                    style="padding-left: 36px; min-width: 200px;">

                {{-- AJAX Clear/Reset Button --}}
                <button type="button" id="clearSearch" class="btn btn-link position-absolute p-0"
                    style="right: 12px; top: 8px; color: var(--text-muted); display: none;"
                    title="Clear Search">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            </div>
        </div>

        <div class="vr d-none d-md-block mx-1" style="color: var(--border-color);"></div>

        {{-- Action Buttons --}}
        @if (!isset($supervisor_id) && $user->role_id != '4' && $client_id !== 'playBackSites')
            <a href="{{ route('sites.site_create', is_numeric($client_id) ? $client_id : 0) }}"
                class="btn-sapphire text-nowrap">
                <i class="bi bi-plus-lg"></i> Add {{ get_label('label_site', 'Site') }}
            </a>
        @endif
        @if ($client_id == 0 || $client_id != 'playBackSites')
            <a href="{{ route('sites.export', $client_id != 'playBackSites' && is_numeric($client_id) ? $client_id : 0) }}"
                class="btn-sapphire-outline text-nowrap"
                style="color: var(--sapphire-success); border-color: var(--sapphire-success);">
                <i class="bi bi-download"></i> Export
            </a>
        @endif
    </div>

</div>

            {{-- Table Container for AJAX Updates --}}
            <div id="sitesTableContainer">
                @include('partials.sites_table')
            </div>

        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                let searchInput = document.getElementById('ajaxSearch');
                let clearBtn = document.getElementById('clearSearch');
                let tableContainer = document.getElementById('sitesTableContainer');
                let currentSort = '{{ request('sort', 'name') }}';
                let currentDir = '{{ request('dir', 'asc') }}';
                let debounceTimeout = null;

                // Sync clear button visibility
                function toggleClearBtn() {
                    if (searchInput.value.length > 0) {
                        clearBtn.style.display = 'block';
                    } else {
                        clearBtn.style.display = 'none';
                    }
                }
                toggleClearBtn();

                // AJAX Fetch Function
                window.fetchSites = function(page = 1) {
                    let search = searchInput.value;
                    let url = `{{ url()->current() }}?page=${page}&search=${search}&sort=${currentSort}&dir=${currentDir}`;

                    tableContainer.style.opacity = '0.5';

                    fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.text())
                        .then(html => {
                            tableContainer.innerHTML = html;
                            tableContainer.style.opacity = '1';

                            bindPagination();
                        })
                        .catch(error => {
                            console.error('Error fetching sites:', error);
                            tableContainer.style.opacity = '1';
                        });
                };

                // Handle Pagination Clicks
                function bindPagination() {
                    let paginationLinks = document.querySelectorAll('.ajax-pagination a');
                    paginationLinks.forEach(link => {
                        link.addEventListener('click', function(e) {
                            e.preventDefault();
                            let pageUrl = new URL(this.href);
                            let page = pageUrl.searchParams.get('page');
                            fetchSites(page);
                        });
                    });
                }
                bindPagination();

                // Handle Search Input (As you type)
                searchInput.addEventListener('input', function() {
                    toggleClearBtn();
                    clearTimeout(debounceTimeout);
                    debounceTimeout = setTimeout(() => {
                        fetchSites(1);
                    }, 400); // 400ms debounce
                });

                // Clear Search
                window.clearSearch = function() {
                    searchInput.value = '';
                    toggleClearBtn();
                    fetchSites(1);
                };
                clearBtn.addEventListener('click', clearSearch);

                // Handle Column Sort
                window.handleSort = function(column) {
                    if (currentSort === column) {
                        currentDir = currentDir === 'asc' ? 'desc' : 'asc';
                    } else {
                        currentSort = column;
                        currentDir = 'asc';
                    }
                    fetchSites(1);
                };

                // Delete Function (Mapped to Safari/Sapphire styles)
                window.deleteSite = function(client_id, id) {
                    var title = 'Delete Confirmation';
                    var msg = 'Are you sure you want to delete this site?';

                    // Check if SweetAlert is loaded
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: title,
                            text: msg,
                            icon: 'warning',
                            showCancelButton: true,
                            background: getComputedStyle(document.documentElement).getPropertyValue(
                                '--bg-card').trim() || '#fff',
                            color: getComputedStyle(document.documentElement).getPropertyValue(
                                '--text-main').trim() || '#000',
                            confirmButtonColor: '#EF4444',
                            cancelButtonColor: '#64748B',
                            confirmButtonText: 'Delete'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                var url = '{{ route('sites.site_delete', [':client_id', ':id']) }}';
                                url = url.replace(':client_id', client_id).replace(':id', id);
                                window.location = url;
                            }
                        });
                    } else {
                        if (confirm(msg)) {
                            var url = '{{ route('sites.site_delete', [':client_id', ':id']) }}';
                            url = url.replace(':client_id', client_id).replace(':id', id);
                            window.location = url;
                        }
                    }
                };

                // Playback redirect
                window.playBackOfGuards = function(siteId) {
                    var url = '{{ route('playBackOfGuards', ':site_id') }}';
                    window.location = url.replace(':site_id', siteId);
                }

            });
        </script>
    @endpush

@endsection

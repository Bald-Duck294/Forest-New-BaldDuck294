@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    // $user = session('user');
    // dump('testing');
@endphp
@extends('layouts.app')

@section('title', 'Manage Ranges')

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

        /* Action Buttons */
        .btn-sapphire {
            background-color: var(--sapphire-primary);
            color: #ffffff;
            border: none;
            font-weight: 600;
            padding: 8px 16px;
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
            font-weight: 600;
            padding: 8px 16px;
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

        /* Custom Search Input */
        .custom-input {
            background-color: var(--bg-body);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.85rem;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            min-width: 220px;
        }

        .custom-input:focus {
            border-color: var(--sapphire-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .custom-input::placeholder {
            color: var(--text-muted);
            opacity: 0.7;
        }

        html[data-bs-theme="dark"] .custom-input {
            color-scheme: dark;
        }

        /* Icon Action Buttons (View, Edit, Delete) */
        .btn-icon-soft {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            border: none;
            background: transparent;
            transition: all 0.2s ease;
            font-size: 1.1rem;
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

        /* Custom Switch Styling */
        .custom-switch .form-check-input {
            width: 2.8em;
            height: 1.4em;
            background-color: var(--border-color);
            border-color: var(--border-color);
            cursor: pointer;
            transition: background-color 0.2s ease, border-color 0.2s ease;
            margin-top: 0;
        }

        .custom-switch .form-check-input:checked {
            background-color: var(--sapphire-success);
            border-color: var(--sapphire-success);
        }

        .custom-switch .form-check-input:focus {
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.25);
        }

        /* Table Typography Overrides */
        .client-name-link {
            font-weight: 600;
            color: var(--sapphire-primary);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .client-name-link:hover {
            color: var(--text-main);
            text-decoration: underline;
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

        .sort-link:hover,
        .sort-link.active {
            color: var(--sapphire-primary);
        }

        .sort-link i {
            font-size: 0.75rem;
            opacity: 0.5;
        }

        .sort-link:hover i,
        .sort-link.active i {
            opacity: 1;
        }
    </style>

    <div class="container-fluid py-4">

        {{-- COMPACT HEADER & TABLE CARD --}}
        <div class="dash-card p-0 overflow-hidden">

            {{-- Top Controls Bar --}}
            <div class="p-3 p-md-4 d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 border-bottom"
                style="border-color: var(--border-color) !important;">

                {{-- Title & Back --}}
                <div class="d-flex align-items-center gap-3">
                    <a href="javascript:history.back()" class="btn-sapphire-outline px-2 py-1" title="Go Back">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div>
                        <h4 class="fw-bold mb-0" style="color: var(--text-main); line-height: 1.2;">Manage Ranges</h4>
                        <p class="mb-0" style="color: var(--text-muted); font-size: 0.8rem;">Overview of all assigned
                            ranges and operational status.</p>
                    </div>
                </div>

                {{-- Search & Actions --}}
                <div class="d-flex flex-column flex-md-row gap-2">
                    {{-- Search Form --}}
                    <div class="d-flex gap-2 m-0">
                        <div class="position-relative flex-grow-1">
                            <i class="bi bi-search position-absolute"
                                style="left: 12px; top: 10px; color: var(--text-muted);"></i>
                            <input type="text" name="search" id="ajaxSearch" value="{{ request('search') }}"
                                class="custom-input" style="padding-left: 36px;"
                                placeholder="Search range, city, or state...">

                            {{-- AJAX Clear/Reset Button --}}
                            <button type="button" id="clearSearch" class="btn btn-link position-absolute p-0"
                                style="right: 12px; top: 8px; color: var(--text-muted); display: none;"
                                title="Clear Search">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </div>
                    </div>

                    <div class="vr d-none d-md-block mx-1" style="color: var(--border-color);"></div>

                    <a href="{{ route('clients.export') }}" class="btn-sapphire-outline text-nowrap"
                        style="color: var(--sapphire-success); border-color: var(--sapphire-success);">
                        <i class="bi bi-download"></i> Export
                    </a>
                    <a href="{{ route('clients.create') }}" class="btn-sapphire text-nowrap">
                        <i class="bi bi-plus-lg"></i> Add Range
                    </a>
                </div>
            </div>

            {{-- Table Container for AJAX Updates --}}
            <div id="clientsTableContainer">
                @include('partials.clients_table')
            </div>

        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                let searchInput = document.getElementById('ajaxSearch');
                let clearBtn = document.getElementById('clearSearch');
                let tableContainer = document.getElementById('clientsTableContainer');
                let currentSort = '{{ request('sort', 'id') }}';
                let currentDir = '{{ request('dir', 'desc') }}';
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
                window.fetchClients = function(page = 1) {
                    let search = searchInput.value;
                    let url =
                        `{{ route('clients') }}?page=${page}&search=${search}&sort=${currentSort}&dir=${currentDir}`;

                    // Show a subtle loading state instead of blanking
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

                            // Re-bind pagination clicks since they are now part of the new HTML
                            bindPagination();
                        })
                        .catch(error => {
                            console.error('Error fetching clients:', error);
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
                            fetchClients(page);
                        });
                    });
                }
                bindPagination();

                // Handle Search Input (As you type)
                searchInput.addEventListener('input', function() {
                    toggleClearBtn();
                    clearTimeout(debounceTimeout);
                    debounceTimeout = setTimeout(() => {
                        fetchClients(1);
                    }, 400); // 400ms debounce
                });

                // Clear Search
                window.clearSearch = function() {
                    searchInput.value = '';
                    toggleClearBtn();
                    fetchClients(1);
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
                    fetchClients(1);
                };

                // Delete Function
                window.deleteClient = function(id) {
                    var title = 'Delete Confirmation';
                    var msg = 'Are you sure you want to delete this range?';

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
                            confirmButtonColor: '#EF4444', // Sapphire Danger
                            cancelButtonColor: '#64748B', // Text Muted
                            confirmButtonText: 'Yes, Delete'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                var url = '{{ route('clients.deleteClient', ':id') }}';
                                window.location = url.replace(':id', id);
                            }
                        });
                    } else {
                        if (confirm(msg)) {
                            var url = '{{ route('clients.deleteClient', ':id') }}';
                            window.location = url.replace(':id', id);
                        }
                    }
                };

                // Toggle Status Function - Upgraded for better UX
                window.toggleActive = function(id, element, currentlyActive) {
                    event.preventDefault();

                    var action = currentlyActive ? 'deactivate' : 'activate';
                    var title = action === 'deactivate' ? 'Deactivation Confirmation' : 'Activation Confirmation';
                    var msg = action === 'deactivate' ? 'Are you sure you want to deactivate this range?' :
                        'Are you sure you want to activate this range?';
                    var btnText = action === 'deactivate' ? 'Deactivate' : 'Activate';
                    var btnColor = action === 'deactivate' ? '#EF4444' : '#10B981';

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
                            confirmButtonColor: btnColor,
                            cancelButtonColor: '#64748B',
                            confirmButtonText: btnText
                        }).then((result) => {
                            if (result.isConfirmed) {
                                element.checked = !currentlyActive;
                                var url = action === 'deactivate' ?
                                    '{{ route('clients.inactive', ':id') }}' :
                                    '{{ route('clients.active', ':id') }}';
                                window.location = url.replace(':id', id);
                            } else {
                                element.checked = currentlyActive;
                            }
                        });
                    } else {
                        if (confirm(msg)) {
                            element.checked = !currentlyActive;
                            var url = action === 'deactivate' ? '{{ route('clients.inactive', ':id') }}' :
                                '{{ route('clients.active', ':id') }}';
                            window.location = url.replace(':id', id);
                        } else {
                            element.checked = currentlyActive;
                        }
                    }
                };

            });
        </script>
    @endpush

@endsection

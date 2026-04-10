@php
    $hideGlobalFilters = true;
    $hideBackground = true;

    // Parse the JSON data safely
    $reportData = json_decode($report->report_data, true) ?? [];

    // 🔥 SMART PHOTO PARSING
    $hiddenKeys = ['client_id', 'site_id', 'patrol_id', 'photo', 'photos', 'image', 'images'];

    $photos = [];

    // 1. Check DB Column
    $dbPhotos = json_decode($report->photo ?? '[]', true);
    if (!is_array($dbPhotos)) {
        $dbPhotos = [$report->photo];
    }
    if (!empty($dbPhotos)) {
        $photos = array_merge($photos, $dbPhotos);
    }

    // 2. Check JSON payload for stray image URLs
    foreach (['photo', 'photos', 'image', 'images'] as $imgKey) {
        if (isset($reportData[$imgKey]) && !empty($reportData[$imgKey])) {
            $val = $reportData[$imgKey];
            $jsonPhotos = is_string($val) ? [$val] : (is_array($val) ? $val : []);
            $photos = array_merge($photos, $jsonPhotos);
        }
    }

    // Clean up to only valid URLs
    $photos = array_values(
        array_filter(array_unique($photos), function ($url) {
            return filter_var($url, FILTER_VALIDATE_URL);
        }),
    );
@endphp

@extends('layouts.app')
@section('title', 'Report Details')

@section('content')
    <style>
        /* =========================================
           LOCAL COMPONENT STYLES
           (Hooked to Global Sapphire Variables)
        ========================================= */
        .dash-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            overflow: hidden;
        }

        .card-header-custom {
            background: var(--bg-body);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.5rem;
            font-weight: 700;
            color: var(--text-main);
            font-size: 0.95rem;
        }

        /* Back Button */
        .btn-back {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: transparent;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-muted);
            text-decoration: none !important;
            transition: all 0.2s ease;
            font-size: 1.2rem;
        }

        .btn-back:hover {
            background: var(--table-hover);
            color: var(--sapphire-primary);
            border-color: var(--sapphire-primary);
        }

        /* Action Buttons */
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

        /* Metadata Bar */
        .metadata-bar {
            display: flex;
            flex-wrap: wrap;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .metadata-item {
            flex: 1 1 30%;
            padding: 1.25rem 1.5rem;
            border-right: 1px solid var(--border-color);
        }

        .metadata-item:last-child {
            border-right: none;
        }

        @media (max-width: 768px) {
            .metadata-item {
                flex: 1 1 100%;
                border-right: none;
                border-bottom: 1px solid var(--border-color);
            }

            .metadata-item:last-child {
                border-bottom: none;
            }
        }

        .metadata-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .metadata-value {
            font-size: 1rem;
            color: var(--text-main);
            font-weight: 600;
        }

        /* Key-Value Data Grid */
        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            padding: 1.5rem;
        }

        .data-item-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 4px;
            font-weight: 600;
        }

        .data-item-value {
            font-size: 0.95rem;
            color: var(--text-main);
            font-weight: 500;
            word-break: break-word;
        }

        /* Form Elements */
        .custom-textarea {
            background-color: var(--bg-body);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px;
            font-size: 0.9rem;
            width: 100%;
            outline: none;
            transition: all 0.2s ease;
            resize: vertical;
            min-height: 100px;
        }

        .custom-textarea:focus {
            border-color: var(--sapphire-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Gallery */
        .gallery-img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .gallery-img:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        /* Map */
        #reportMap {
            height: 300px;
            width: 100%;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        /* =========================================
           PRINT STYLES (For Export to PDF / Print)
        ========================================= */
        @media print {
            body {
                background: white !important;
                color: black !important;
            }

            .sidebar,
            .btn-back,
            button,
            .action-panel,
            .map-panel {
                display: none !important;
            }

            .dash-card,
            .metadata-bar {
                box-shadow: none !important;
                border: 1px solid #ccc !important;
                break-inside: avoid;
            }

            .data-item-value,
            .metadata-value {
                color: black !important;
            }

            .data-item-label,
            .metadata-label {
                color: #555 !important;
            }

            .gallery-img {
                border: 1px solid #ddd !important;
            }

            .container-fluid {
                padding: 0 !important;
                margin: 0 !important;
            }

            /* Force background colors to print for badges */
            .badge {
                border: 1px solid #ccc !important;
                color: black !important;
                background: transparent !important;
            }
        }
    </style>

    <div class="container-fluid py-4 px-md-4">

        {{-- TOP HEADER --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div class="d-flex align-items-center gap-3">
                <a href="javascript:history.back()" class="btn-back" title="Go Back">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h4 class="fw-bold mb-1" style="color: var(--text-main);">
                        Report #{{ $report->report_id }}
                    </h4>
                    <div class="d-flex align-items-center gap-2">
                        @if ($report->status == 'Pending')
                            <span class="badge"
                                style="background: rgba(245, 158, 11, 0.15); color: var(--sapphire-warning, #f59e0b); padding: 4px 8px;">Pending
                                Review</span>
                        @elseif($report->status == 'Approved')
                            <span class="badge"
                                style="background: rgba(16, 185, 129, 0.15); color: var(--sapphire-success, #10b981); padding: 4px 8px;">Approved</span>
                        @else
                            <span class="badge"
                                style="background: rgba(239, 68, 68, 0.15); color: var(--sapphire-danger, #ef4444); padding: 4px 8px;">Rejected</span>
                        @endif
                        <span style="color: var(--text-muted); font-size: 0.8rem;">• {{ $report->category }}
                            ({{ ucfirst($report->report_type) }})</span>
                    </div>
                </div>
            </div>

            <div>
                {{-- 🔥 Replaced PDF export with Print --}}
                <button class="btn-sapphire-outline" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print Report
                </button>
            </div>
        </div>

        {{-- MINIMALIST METADATA BAR --}}
        <div class="metadata-bar shadow-sm">
            <div class="metadata-item">
                <div class="metadata-label">Submitted By</div>
                <div class="metadata-value">
                    <i class="bi bi-person-badge me-1" style="color: var(--sapphire-primary);"></i>
                    {{ $report->reporter_name ?? 'Unknown Field User' }}
                </div>
            </div>
            <div class="metadata-item">
                <div class="metadata-label">Supervisor</div>
                <div class="metadata-value">{{ $report->supervisor_id ?? 'Unassigned' }}</div>
            </div>
            <div class="metadata-item">
                <div class="metadata-label">Timestamp</div>
                <div class="metadata-value">
                    {{ \Carbon\Carbon::parse($report->date_time)->format('d M Y, h:i A') }}
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- LEFT COLUMN: Report Details & Photos --}}
            <div class="col-lg-8">

                {{-- Formatted Data Grid --}}
                <div class="dash-card mb-4">
                    <div class="card-header-custom">
                        <i class="bi bi-list-columns-reverse me-2" style="color: var(--text-muted);"></i> Detailed Report
                        Data
                    </div>
                    <div class="data-grid">
                        @forelse($reportData as $key => $value)
                            @if (!in_array($key, $hiddenKeys) && $value !== '')
                                <div>
                                    <div class="data-item-label">{{ ucwords(str_replace('_', ' ', $key)) }}</div>
                                    <div class="data-item-value">{{ is_array($value) ? implode(', ', $value) : $value }}
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="col-12 text-muted" style="font-style: italic;">No detailed data available.</div>
                        @endforelse
                    </div>
                </div>

                {{-- Photo Gallery --}}
                @if (count($photos) > 0)
                    <div class="dash-card">
                        <div class="card-header-custom">
                            <i class="bi bi-images me-2" style="color: var(--text-muted);"></i> Attached Evidence
                        </div>
                        <div class="p-3">
                            <div class="row g-3">
                                @foreach ($photos as $imgUrl)
                                    <div class="col-6 col-sm-4 col-md-3">
                                        <img src="{{ $imgUrl }}" class="gallery-img shadow-sm"
                                            onclick="openImageModal('{{ $imgUrl }}')">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- RIGHT COLUMN: Map & Actions --}}
            <div class="col-lg-4">

                {{-- Location Map --}}
                <div class="dash-card mb-4 map-panel">
                    <div class="card-header-custom d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-geo-alt-fill me-2" style="color: var(--text-muted);"></i> Location</span>
                        <small style="color: var(--text-muted); font-weight: normal;">
                            {{ number_format((float) $report->latitude, 5) }},
                            {{ number_format((float) $report->longitude, 5) }}
                        </small>
                    </div>
                    <div id="reportMap"></div>
                </div>

                {{-- Action Panel (Smart Toggle) --}}
                <div class="dash-card border-0 action-panel"
                    style="border: 1px solid var(--sapphire-primary) !important; background: rgba(59, 130, 246, 0.02);">
                    <div class="card-header-custom"
                        style="background: transparent; border-bottom: 1px solid rgba(59, 130, 246, 0.1);">
                        <i class="bi bi-shield-check me-2" style="color: var(--sapphire-primary);"></i> Supervisor Actions
                    </div>
                    <div class="p-3">

                        {{-- READ-ONLY VIEW --}}
                        <div id="statusViewMode" class="{{ $report->status == 'Pending' ? 'd-none' : '' }}">
                            <label class="data-item-label mb-1">Final Remarks</label>
                            <div class="p-3 mb-3 rounded"
                                style="background: var(--bg-body); border: 1px solid var(--border-color); color: var(--text-main); font-size: 0.9rem;">
                                {{ $report->final_remarks ?: 'No remarks provided by supervisor.' }}
                            </div>

                            <button type="button" class="btn w-100 fw-bold border" onclick="toggleEditMode()"
                                style="background-color: var(--bg-card); color: var(--sapphire-primary); border-color: var(--border-color);">
                                <i class="bi bi-pencil-square me-1"></i> Edit Status
                            </button>
                        </div>

                        {{-- EDIT MODE --}}
                        <div id="statusEditMode" class="{{ $report->status != 'Pending' ? 'd-none' : '' }}">
                            <form method="POST" action="{{ route('report-configs.updateStatus', $report->id) }}">
                                @csrf
                                <label class="data-item-label mb-2">Final Remarks</label>
                                <textarea name="final_remarks" class="custom-textarea mb-3" placeholder="Enter notes before changing status...">{{ $report->final_remarks }}</textarea>

                                <div class="d-flex flex-column gap-2">
                                    <button name="status" value="Approved" class="btn w-100 fw-bold shadow-sm"
                                        style="background-color: var(--sapphire-success, #10b981); color: white; border: none;">
                                        <i class="bi bi-check-lg me-1"></i> Approve Report
                                    </button>

                                    <div class="d-flex gap-2">
                                        <button name="status" value="Pending" class="btn w-50 fw-bold border"
                                            style="background-color: var(--bg-card); color: var(--sapphire-warning, #f59e0b); border-color: var(--border-color);">
                                            Clarification
                                        </button>

                                        <button name="status" value="Rejected" class="btn w-50 fw-bold"
                                            style="background-color: rgba(239, 68, 68, 0.1); color: var(--sapphire-danger, #ef4444); border: 1px solid rgba(239, 68, 68, 0.2);">
                                            Reject
                                        </button>
                                    </div>

                                    @if ($report->status != 'Pending')
                                        <button type="button"
                                            class="btn btn-link text-muted w-100 mt-2 text-decoration-none"
                                            onclick="toggleEditMode()">Cancel Edit</button>
                                    @endif
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- 🔥 RESTRUCTURED IMAGE MODAL --}}
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0 shadow-none">
                <div class="modal-body text-center p-0" id="modalBodyWrapper">

                    {{-- Wrapper to tightly fit the image so the absolute button sits on the actual image corner --}}
                    <div class="position-relative d-inline-block">
                        {{-- The overlapping "X" close button --}}
                        <button type="button" class="btn-close position-absolute shadow" data-bs-dismiss="modal"
                            aria-label="Close"
                            style="top: -12px; right: -12px; background-color: white; padding: 10px; border-radius: 50%; opacity: 1; z-index: 1055; cursor: pointer;">
                        </button>

                        {{-- The Image --}}
                        <img id="modalFullImage" src="" class="img-fluid rounded"
                            style="max-height: 80vh; max-width: 100%; object-fit: contain; border: 4px solid var(--bg-card); box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfBFN6L_HROTd-mS8QqUDRIqskkvHvFYk&libraries=geometry">
    </script>
    <script>
        let mapInstance = null;

        // Dark mode styles for Map
        const mapDarkStyle = [{
                elementType: "geometry",
                stylers: [{
                    color: "#1e293b"
                }]
            },
            {
                elementType: "labels.text.stroke",
                stylers: [{
                    color: "#1e293b"
                }]
            },
            {
                elementType: "labels.text.fill",
                stylers: [{
                    color: "#94a3b8"
                }]
            },
            {
                featureType: "water",
                elementType: "geometry",
                stylers: [{
                    color: "#0f172a"
                }]
            },
            {
                featureType: "poi",
                elementType: "labels",
                stylers: [{
                    visibility: "off"
                }]
            }
        ];

        $(document).ready(function() {
            if (typeof google === 'object' && typeof google.maps === 'object') {
                initReportMap();
            } else {
                $('#reportMap').html(
                    '<div class="d-flex align-items-center justify-content-center h-100 text-muted">API Error</div>'
                    );
            }

            // Optional: Ensure clicking the invisible background wrapper also closes the modal
            $('#modalBodyWrapper').on('click', function(e) {
                if (e.target === this) {
                    $('#imagePreviewModal').modal('hide');
                }
            });
        });

        function initReportMap() {
            const mapEl = document.getElementById("reportMap");
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';

            const lat = parseFloat("{{ $report->latitude }}") || 20.0;
            const lng = parseFloat("{{ $report->longitude }}") || 78.0;
            const pos = {
                lat: lat,
                lng: lng
            };

            mapInstance = new google.maps.Map(mapEl, {
                zoom: 16,
                center: pos,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                disableDefaultUI: false,
                zoomControl: true,
                streetViewControl: false,
                mapTypeControl: false,
                styles: isDark ? mapDarkStyle : []
            });

            new google.maps.Marker({
                position: pos,
                map: mapInstance,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 8,
                    fillColor: "#ef4444",
                    fillOpacity: 1,
                    strokeWeight: 3,
                    strokeColor: "#ffffff"
                }
            });
        }

        // Modal Image Viewer
        function openImageModal(url) {
            document.getElementById('modalFullImage').src = url;
            var myModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
            myModal.show();
        }

        // Toggle Edit Mode Logic
        function toggleEditMode() {
            $('#statusViewMode').toggleClass('d-none');
            $('#statusEditMode').toggleClass('d-none');
        }

        // Dynamic Theme Listener for Map
        window.addEventListener('themeChanged', () => {
            if (mapInstance) {
                const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                mapInstance.setOptions({
                    styles: isDark ? mapDarkStyle : []
                });
            }
        });
    </script>
@endpush

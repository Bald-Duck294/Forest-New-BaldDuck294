@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    $user = session('user');
@endphp

@extends('layouts.app')

@section('title', 'Patrol Details')

@section('content')
    <style>
        /* LAYOUT STYLING */
        .patrol-header-card {
            background: var(--bg-card);
            padding: 1.5rem;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .info-label {
            font-weight: 700;
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .info-value {
            color: var(--text-main);
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        #patrolMap {
            height: 500px;
            width: 100%;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }

        .table thead th {
            background-color: var(--bg-body);
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            border-top: none;
            border-bottom: 1px solid var(--border-color);
        }

        .table td {
            color: var(--text-main);
            border-bottom: 1px solid var(--border-color);
        }

        /* GALLERY HOVER */
        .gallery-img {
            transition: all 0.2s ease;
            object-fit: cover;
        }

        .gallery-img:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            cursor: pointer;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center w-100 mb-4 gap-3"
            id="dynamic-header-top">


            <a href="javascript:history.back()" class="btn shadow-sm d-flex align-items-center justify-content-center"
                style="background-color: var(--bg-card); color: var(--text-main); border: 1px solid var(--border-color); border-radius: 8px; padding: 6px 14px; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: all 0.2s ease;"
                onmouseover="this.style.backgroundColor='var(--table-hover)'; this.style.color='var(--sapphire-primary)';"
                onmouseout="this.style.backgroundColor='var(--bg-card)'; this.style.color='var(--text-main)';">
                <i class="bi bi-arrow-left me-2"></i> Back
            </a>

        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="patrol-header-card">
                    <h6 class="fw-bold mb-3 border-bottom pb-2"
                        style="color: var(--text-main); border-color: var(--border-color) !important;">Session Overview
                    </h6>
                    <div class="row">
                        <div class="col-6">
                            <div class="info-label">Officer</div>
                            <div class="info-value text-truncate">{{ $patrol->user->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="info-label">Duration</div>
                            <div class="info-value">
                                @if ($patrol->started_at && $patrol->ended_at)
                                    {{ $patrol->started_at->diffForHumans($patrol->ended_at, true) }}
                                @else
                                    <span class="badge bg-warning text-dark">Ongoing</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="info-label">Total Distance</div>
                            <div class="info-value fs-5" style="color: var(--sapphire-primary);" id="patrolDistanceDisplay">
                                Calculating...</div>
                        </div>
                        <div class="col-6">
                            <div class="info-label">Start Time</div>
                            <div class="info-value small" style="color: var(--text-muted);">
                                {{ $patrol->started_at?->format('h:i A') }}</div>
                        </div>
                        <div class="col-6">
                            <div class="info-label">Method</div>
                            <div class="info-value small">{{ ucfirst($patrol->method) }}</div>
                        </div>
                    </div>
                </div>

                @if ($patrol->media->count())
                    <div class="patrol-header-card">
                        <h6 class="fw-bold mb-3" style="color: var(--text-main);">Session Gallery</h6>
                        <div class="row g-2">
                            @foreach ($patrol->media as $index => $media)
                                <div class="col-4">
                                    <img src="{{ $media->url }}" class="img-fluid rounded gallery-img"
                                        style="height: 70px; width: 100%; border: 1px solid var(--border-color);"
                                        data-toggle="modal" data-target="#mediaModal" data-src="{{ $media->url }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: transparent;">
                    <div id="patrolMap"></div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden"
            style="background: var(--bg-card); border: 1px solid var(--border-color) !important;">
            <div class="card-header py-3 border-bottom"
                style="background: var(--bg-card); border-color: var(--border-color) !important;">
                <h6 class="mb-0 fw-bold" style="color: var(--text-main);">Live Activity Log</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0"
                        style="--bs-table-bg: transparent; --bs-table-hover-bg: var(--table-hover);">
                        <thead>
                            <tr>
                                <th class="ps-4">Time</th>
                                <th>Type</th>
                                <th>Notes</th>
                                <th>Location</th>
                                <th>Media</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($patrol->logs as $log)
                                <tr>
                                    <td class="ps-4 small" style="color: var(--text-muted);">
                                        {{ $log->created_at->format('h:i A') }}</td>
                                    <td>
                                        <span class="badge rounded-pill px-3 py-2 border font-weight-normal"
                                            style="background: var(--bg-body); color: var(--text-main); border-color: var(--border-color) !important;">
                                            {{ ucfirst($log->type) }}
                                        </span>
                                    </td>
                                    <td class="text-wrap" style="max-width: 250px; color: var(--text-main);">
                                        {{ $log->notes ?: '-' }}</td>
                                    <td>
                                        @if ($log->lat)
                                            <a href="https://www.google.com/maps?q={{ $log->lat }},{{ $log->lng }}"
                                                target="_blank" class="btn btn-sm btn-link text-decoration-none"
                                                style="color: var(--sapphire-primary);">
                                                <i class="bi bi-geo-alt-fill"></i> View
                                            </a>
                                        @else
                                            <span style="color: var(--text-muted);">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @foreach ($log->media as $m)
                                            <img src="{{ $m->url }}" width="35" height="35"
                                                class="rounded border gallery-img me-1"
                                                style="border-color: var(--border-color) !important;" data-toggle="modal"
                                                data-target="#mediaModal" data-src="{{ $m->url }}">
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="mediaModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content border-0" style="background: var(--bg-card);">
                <div class="modal-body p-0 position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-dismiss="modal"
                        style="z-index: 10; background-color: white; padding: 10px; border-radius: 50%; opacity: 0.8;"></button>
                    <img id="modalImage" src="" class="img-fluid rounded" alt="Preview"
                        style="width: 100%; max-height: 80vh; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- 1. Load the Google Maps API --}}
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfBFN6L_HROTd-mS8QqUDRIqskkvHvFYk&libraries=geometry">
    </script>

    <script>
        let mapInstance = null; // Store map instance globally so theme switcher can reach it

        // Map Dark Mode Styles
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
            $('#patrolMap').css('height', '500px');

            if (typeof google === 'object' && typeof google.maps === 'object') {
                initPatrolMap();
                initGallery();
            } else {
                console.error("Google Maps API failed to load.");
                $('#patrolDistanceDisplay').text("API Error");
            }
        });

        function initPatrolMap() {
            const mapEl = document.getElementById("patrolMap");
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';

            const startLat = parseFloat("{{ $patrol->start_lat }}") || 20.0;
            const startLng = parseFloat("{{ $patrol->start_lng }}") || 78.0;

            mapInstance = new google.maps.Map(mapEl, {
                zoom: 15,
                center: {
                    lat: startLat,
                    lng: startLng
                },
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                disableDefaultUI: false,
                zoomControl: true,
                styles: isDark ? mapDarkStyle : [] // Apply dark mode on load
            });

            const bounds = new google.maps.LatLngBounds();

            // --- DRAW PATROL PATH ---
            @if ($patrol->path_geojson)
                try {
                    let rawPath = {!! is_string($patrol->path_geojson) ? $patrol->path_geojson : json_encode($patrol->path_geojson) !!};
                    let coords = [];

                    if (rawPath.type === "LineString") {
                        coords = rawPath.coordinates;
                    } else if (rawPath.geometry && rawPath.geometry.type === "LineString") {
                        coords = rawPath.geometry.coordinates;
                    } else if (Array.isArray(rawPath)) {
                        coords = rawPath;
                    }

                    if (coords && coords.length > 0) {
                        let pathCoords = coords.map(c => ({
                            lat: parseFloat(c[1]),
                            lng: parseFloat(c[0])
                        }));

                        const polyline = new google.maps.Polyline({
                            path: pathCoords,
                            geodesic: true,
                            strokeColor: "#3b82f6",
                            strokeOpacity: 0.8,
                            strokeWeight: 6,
                            map: mapInstance
                        });

                        pathCoords.forEach(p => bounds.extend(p));

                        let distMeters = google.maps.geometry.spherical.computeLength(polyline.getPath());
                        let distKm = (distMeters / 1000).toFixed(2);
                        document.getElementById("patrolDistanceDisplay").innerText = distKm + " km";

                        new google.maps.Marker({
                            position: pathCoords[0],
                            map: mapInstance,
                            label: "S",
                            title: "Start"
                        });
                        new google.maps.Marker({
                            position: pathCoords[pathCoords.length - 1],
                            map: mapInstance,
                            label: "E",
                            title: "End"
                        });
                    }
                } catch (e) {
                    console.error("GeoJSON Parsing Error:", e);
                    document.getElementById("patrolDistanceDisplay").innerText = "Data Format Error";
                }
            @else
                document.getElementById("patrolDistanceDisplay").innerText = "No Path Data";
            @endif

            // --- DRAW LOG MARKERS ---
            @foreach ($patrol->logs as $log)
                @if ($log->lat && $log->lng)
                    (function() {
                        let pos = {
                            lat: parseFloat("{{ $log->lat }}"),
                            lng: parseFloat("{{ $log->lng }}")
                        };
                        let logMarker = new google.maps.Marker({
                            position: pos,
                            map: mapInstance,
                            title: "{{ ucfirst($log->type) }}",
                            icon: {
                                path: google.maps.SymbolPath.CIRCLE,
                                scale: 6,
                                fillColor: "{{ $log->type == 'animal mortality' ? '#ef4444' : '#64748b' }}",
                                fillOpacity: 1,
                                strokeWeight: 2,
                                strokeColor: "#fff"
                            }
                        });

                        let info = new google.maps.InfoWindow({
                            content: `<div style="font-family: 'Inter', sans-serif; padding: 4px; color: var(--text-main);">
                                            <strong style="display:block; margin-bottom: 4px; border-bottom: 1px solid var(--border-color); padding-bottom: 4px;">{{ ucfirst($log->type) }}</strong>
                                            <small style="color: var(--text-muted);">{{ $log->notes }}</small>
                                          </div>`
                        });

                        logMarker.addListener("click", () => info.open(mapInstance, logMarker));
                        bounds.extend(pos);
                    })();
                @endif
            @endforeach

            if (!bounds.isEmpty()) {
                mapInstance.fitBounds(bounds);
            }
        }

        function initGallery() {
            $('.gallery-img').on('click', function() {
                let src = $(this).attr('data-src') || $(this).attr('src');
                $('#modalImage').attr('src', src);
                $('#mediaModal').modal('show');
            });
        }

        // Listen for Sidebar Theme Toggles
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

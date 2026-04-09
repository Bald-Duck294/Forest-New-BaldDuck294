@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    $user = session('user');
@endphp

@extends('layouts.app')

@section('title', 'Patrol Details')

@section('content')
<style>
    /* SAPPHIRE THEME VARIABLES */
    :root {
        --sapphire-primary: #3b82f6;
        --bg-card: #ffffff;
        --border-color: #e2e8f0;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --success-soft: rgba(16, 185, 129, 0.1);
        --danger-soft: rgba(239, 68, 68, 0.1);
    }

    /* LAYOUT STYLING */
    .patrol-header-card {
        background: white;
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
        background-color: #f8fafc;
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        border-top: none;
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0" style="color: var(--text-main);">Patrol Analysis</h3>
            <p class="text-muted small mb-0">Detailed tracking data for Session #{{ $patrol->id }}</p>
        </div>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm px-3 shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="patrol-header-card">
                <h6 class="fw-bold mb-3 border-bottom pb-2">Session Overview</h6>
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
                            @else <span class="badge bg-warning text-dark">Ongoing</span> @endif
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="info-label">Total Distance</div>
                        <div class="info-value text-primary fs-5" id="patrolDistanceDisplay">Calculating...</div>
                    </div>
                    <div class="col-6">
                        <div class="info-label">Start Time</div>
                        <div class="info-value small text-muted">{{ $patrol->started_at?->format('h:i A') }}</div>
                    </div>
                    <div class="col-6">
                        <div class="info-label">Method</div>
                        <div class="info-value small">{{ ucfirst($patrol->method) }}</div>
                    </div>
                </div>
            </div>

            @if ($patrol->media->count())
            <div class="patrol-header-card">
                <h6 class="fw-bold mb-3">Session Gallery</h6>
                <div class="row g-2">
                    @foreach ($patrol->media as $index => $media)
                    <div class="col-4">
                        <img src="{{ $media->url }}" class="img-fluid rounded gallery-img"
                             style="height: 70px; width: 100%;"
                             data-toggle="modal" data-target="#mediaModal" data-src="{{ $media->url }}">
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div id="patrolMap"></div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold">Live Activity Log</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
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
                            <td class="ps-4 text-muted small">{{ $log->created_at->format('h:i A') }}</td>
                            <td>
                                <span class="badge rounded-pill px-3 py-2 border font-weight-normal text-dark" style="background: #f1f5f9;">
                                    {{ ucfirst($log->type) }}
                                </span>
                            </td>
                            <td class="text-wrap" style="max-width: 250px;">{{ $log->notes ?: '-' }}</td>
                            <td>
                                @if ($log->lat)
                                <a href="https://www.google.com/maps?q={{ $log->lat }},{{ $log->lng }}" target="_blank" class="btn btn-sm btn-link text-decoration-none">
                                    <i class="bi bi-geo-alt-fill"></i> View
                                </a>
                                @else <span class="text-muted">N/A</span> @endif
                            </td>
                            <td>
                                @foreach ($log->media as $m)
                                <img src="{{ $m->url }}" width="35" height="35" class="rounded border gallery-img me-1"
                                     data-toggle="modal" data-target="#mediaModal" data-src="{{ $m->url }}">
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
        <div class="modal-content bg-dark border-0">
            <div class="modal-body p-0 position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-dismiss="modal" style="z-index: 10;"></button>
                <img id="modalImage" src="" class="img-fluid rounded" alt="Preview">
            </div>
            <div class="modal-footer justify-content-between border-0">
                <button type="button" class="btn btn-outline-light btn-sm" id="prevImage"><i class="bi bi-chevron-left"></i> Prev</button>
                <button type="button" class="btn btn-outline-light btn-sm" id="nextImage">Next <i class="bi bi-chevron-right"></i></button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- 1. Load the Google Maps API --}}
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfBFN6L_HROTd-mS8QqUDRIqskkvHvFYk&libraries=geometry"></script>

<script>
    $(document).ready(function() {
        console.log("Scripts pushed successfully! Starting map initialization...");

        // Ensure the map container has a height even if CSS fails
        $('#patrolMap').css('height', '500px');

        if (typeof google === 'object' && typeof google.maps === 'object') {
            initPatrolMap();
            initGallery();
        } else {
            console.error("Google Maps API failed to load. Check your API key or connection.");
            $('#patrolDistanceDisplay').text("API Error");
        }
    });

    function initPatrolMap() {
        const mapEl = document.getElementById("patrolMap");

        // Safety: Parse coordinates as floats
        const startLat = parseFloat("{{ $patrol->start_lat }}") || 20.0;
        const startLng = parseFloat("{{ $patrol->start_lng }}") || 78.0;

        const map = new google.maps.Map(mapEl, {
            zoom: 15,
            center: { lat: startLat, lng: startLng },
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            disableDefaultUI: false,
            zoomControl: true
        });

        const bounds = new google.maps.LatLngBounds();

        // --- DRAW PATROL PATH ---
        @if($patrol->path_geojson)
            try {
                // Determine if path is already an object or a JSON string
                let rawPath = {!! is_string($patrol->path_geojson) ? $patrol->path_geojson : json_encode($patrol->path_geojson) !!};
                let coords = [];

                // Standardize GeoJSON formats
                if (rawPath.type === "LineString") {
                    coords = rawPath.coordinates;
                } else if (rawPath.geometry && rawPath.geometry.type === "LineString") {
                    coords = rawPath.geometry.coordinates;
                } else if (Array.isArray(rawPath)) {
                    coords = rawPath;
                }

                if (coords && coords.length > 0) {
                    let pathCoords = coords.map(c => ({ lat: parseFloat(c[1]), lng: parseFloat(c[0]) }));

                    const polyline = new google.maps.Polyline({
                        path: pathCoords,
                        geodesic: true,
                        strokeColor: "#3b82f6",
                        strokeOpacity: 0.8,
                        strokeWeight: 6,
                        map: map
                    });

                    pathCoords.forEach(p => bounds.extend(p));

                    // Calculate and Display Distance
                    let distMeters = google.maps.geometry.spherical.computeLength(polyline.getPath());
                    let distKm = (distMeters / 1000).toFixed(2);
                    document.getElementById("patrolDistanceDisplay").innerText = distKm + " km";

                    // Start/End Markers
                    new google.maps.Marker({ position: pathCoords[0], map: map, label: "S", title: "Start" });
                    new google.maps.Marker({ position: pathCoords[pathCoords.length - 1], map: map, label: "E", title: "End" });
                }
            } catch (e) {
                console.error("GeoJSON Parsing Error:", e);
                document.getElementById("patrolDistanceDisplay").innerText = "Data Format Error";
            }
        @else
            document.getElementById("patrolDistanceDisplay").innerText = "No Path Data";
        @endif

        // --- DRAW LOG MARKERS (Sightings, etc.) ---
        @foreach($patrol->logs as $log)
            @if($log->lat && $log->lng)
                (function() {
                    let pos = { lat: parseFloat("{{ $log->lat }}"), lng: parseFloat("{{ $log->lng }}") };
                    let logMarker = new google.maps.Marker({
                        position: pos,
                        map: map,
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
                        content: `<div style="color:black"><strong>{{ ucfirst($log->type) }}</strong><br><small>{{ $log->notes }}</small></div>`
                    });

                    logMarker.addListener("click", () => info.open(map, logMarker));
                    bounds.extend(pos);
                })();
            @endif
        @endforeach

        // Automatically zoom the map to show everything
        if (!bounds.isEmpty()) {
            map.fitBounds(bounds);
        }
    }

    function initGallery() {
        $('.gallery-img').on('click', function() {
            let src = $(this).attr('data-src') || $(this).attr('src');
            $('#modalImage').attr('src', src);
            $('#mediaModal').modal('show'); // Force open if toggle fails
        });
    }
</script>
@endpush

@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    $user = session('user');
@endphp
@extends('layouts.app')
<div class="content">
    <div class="container-fluid">
        <div class="card">

            <!-- Header -->
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Patrol Details</h4>
                <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
                    <i class="la la-arrow-left"></i> Back
                </a>
            </div>

            <!-- Body -->
            <div class="card-body">

                <!-- Session Info -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Officer:</strong> {{ $patrol->user->name ?? 'N/A' }}</p>
                        <p><strong>Site:</strong> {{ $patrol->site->name ?? 'N/A' }}</p>
                        <p><strong>Method:</strong> {{ ucfirst($patrol->method) }}</p>
                        <p><strong>Start:</strong> {{ $patrol->started_at?->format('d M Y h:i A') }}</p>
                        <p><strong>End:</strong> {{ $patrol->ended_at?->format('d M Y h:i A') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Duration:</strong>
                            @if ($patrol->started_at && $patrol->ended_at)
                                {{ $patrol->started_at->diffForHumans($patrol->ended_at, true) }}
                            @else
                                In Progress
                            @endif
                        </p>
                        <p><strong>Distance:</strong> <span id="patrolDistance">Calculating...</span></p>
                    </div>
                </div>

                <!-- Map -->
                <div class="mb-4">
                    <h5>Patrol Path</h5>
                    <div id="patrolMap" style="height: 400px;" class="rounded border"></div>
                </div>

                <!-- Logs -->
                <div class="mb-4">
                    <h5>Logs</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Time</th>
                                    <th>Type</th>
                                    <th>Notes</th>
                                    <th>Location</th>
                                    <th>Photos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($patrol->logs as $log)
                                    <tr>
                                        <td>{{ $log->created_at->format('d M Y h:i A') }}</td>
                                        <td>{{ ucfirst($log->type) }}</td>
                                        <td>{{ $log->notes }}</td>
                                        <td>
                                            @if ($log->lat && $log->lng)
                                                <a href="https://maps.google.com/?q={{ $log->lat }},{{ $log->lng }}"
                                                    target="_blank">
                                                    {{ $log->lat }}, {{ $log->lng }}
                                                </a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap">
                                                @foreach ($log->media as $index => $media)
                                                    <div class="p-1">
                                                        <img src="{{ $media->url }}" alt="photo" width="60"
                                                            class="img-thumbnail gallery-img" data-toggle="modal"
                                                            data-target="#mediaModal"
                                                            data-index="{{ $loop->parent->index }}-{{ $index }}">
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Session Photos -->
                @if ($patrol->media->count())
                    <div class="mb-4">
                        <h5>Session Photos</h5>
                        <div class="row">
                            @foreach ($patrol->media as $index => $media)
                                <div class="col-md-3 col-6 mb-3">
                                    <img src="{{ $media->url }}" alt="Session Photo"
                                        class="img-fluid rounded shadow-sm gallery-img"
                                        style="max-height: 180px; object-fit: cover; width: 100%; cursor: pointer;"
                                        data-toggle="modal" data-target="#mediaModal"
                                        data-index="session-{{ $index }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Modal for Media -->
<div class="modal fade" id="mediaModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content bg-dark">
            <div class="modal-body text-center p-0">
                <img id="modalImage" src="" class="img-fluid rounded" alt="Media Preview">
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-light" id="prevImage">← Prev</button>
                <button type="button" class="btn btn-light" id="nextImage">Next →</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>



<script>
    let geofences = @json($geofences);
    console.log(geofences);
    let geofenceArray = [];
    $(document).ready(function() {
        initPatrolMap();
    });


    function initPatrolMap() {
        let map = new google.maps.Map(document.getElementById("patrolMap"), {
            zoom: 13,
            center: {
                lat: {{ $patrol->start_lat ?? 20 }},
                lng: {{ $patrol->start_lng ?? 78 }}
            }
        });

        let bounds = new google.maps.LatLngBounds();

        // --- Draw Path ---
        @if ($patrol->path_geojson)
            let pathCoords = [];
            let pathData = JSON.parse(`{!! json_encode($patrol->path_geojson) !!}`);

            if (pathData.type === "LineString") {
                pathCoords = pathData.coordinates.map(c => ({
                    lat: c[1],
                    lng: c[0]
                }));
            } else if (pathData.type === "Feature" && pathData.geometry.type === "LineString") {
                pathCoords = pathData.geometry.coordinates.map(c => ({
                    lat: c[1],
                    lng: c[0]
                }));
            } else if (Array.isArray(pathData)) {
                pathCoords = pathData.map(c => ({
                    lat: c[1],
                    lng: c[0]
                }));
            }

            if (pathCoords.length > 0) {
                let polyline = new google.maps.Polyline({
                    path: pathCoords,
                    geodesic: true,
                    strokeColor: "#007bff",
                    strokeOpacity: 1.0,
                    strokeWeight: 3,
                    map: map
                });

                pathCoords.forEach(c => bounds.extend(c));

                // --- Compute distance in meters ---
                let distanceMeters = google.maps.geometry.spherical.computeLength(polyline.getPath());
                // let distanceKm = (distanceMeters / 1000).toFixed(2); // in km

                // Show on page
                document.getElementById("patrolDistance").innerText = distanceMeters.toFixed(2) + " m";

                // Start Marker
                new google.maps.Marker({
                    position: pathCoords[0],
                    map: map,
                    title: "Start Point",
                    zIndex: 9999,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 8,
                        fillColor: "#28a745",
                        fillOpacity: 1,
                        strokeWeight: 2,
                        strokeColor: "#ffffff"
                    }
                });

                // End Marker
                new google.maps.Marker({
                    position: pathCoords[pathCoords.length - 1],
                    map: map,
                    title: "End Point",
                    zIndex: 9999,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 8,
                        fillColor: "#dc3545",
                        fillOpacity: 1,
                        strokeWeight: 2,
                        strokeColor: "#ffffff"
                    }
                });

                // Geofence Markers
                geofences.forEach(geofence => {
                    let path = JSON.parse(geofence.poly_lat_lng);
                    const geo = new google.maps.Polygon({
                        paths: path,
                        strokeColor: "#FF0000",
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: "#FF0000",
                        fillOpacity: 0.35,
                        map: map
                    });
                    const center = getPolygonCenter(path);
                    const infowindow = new google.maps.InfoWindow({
                        content: `<div style="font-size:13px"><strong>${geofence.name}</strong></div>`
                    });
                    geo.addListener('click', function() {
                        if (infowindow) {
                            infowindow.close();
                        }
                        infowindow.setPosition(center);
                        infowindow.open(map);
                    });

                    geofenceArray.push(geo);
                });
            }
        @endif

        // --- Log Markers ---
        let logType = "";
        let iconOptions = {};
        let contentHtml = "";
        let marker, infowindow;
        @foreach ($patrol->logs as $log)
            @if ($log->lat && $log->lng)

                // Decide icon color based on type
                logType = "{{ ucfirst($log->type) }}";
                iconOptions = {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 7,
                    fillOpacity: 1,
                    strokeWeight: 2,
                    strokeColor: "#ffffff"
                };

                switch (logType.toLowerCase()) {
                    case "animal sighting":
                        iconOptions.fillColor = "#28a745"; // green
                        break;
                    case "animal mortality":
                        iconOptions.fillColor = "#dc3545"; // red
                        break;
                    case "water source":
                        iconOptions.fillColor = "#007bff"; // blue
                        break;
                    case "human impact":
                        iconOptions.fillColor = "#fd7e14"; // orange
                        break;
                    default:
                        iconOptions.fillColor = "#6c757d"; // gray
                }

                // Info window content
                contentHtml = `<strong>${logType}</strong><br>{{ $log->notes ?? '' }}`;

                @if ($log->media->count())
                    contentHtml += `<div style="margin-top:5px;">`;
                    @foreach ($log->media as $media)
                        contentHtml +=
                            `<img src="{{ $media->url }}" 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     style="max-width:100px;max-height:100px;margin:2px;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            border:1px solid #ccc;border-radius:4px;" />`;
                    @endforeach
                    contentHtml += `</div>`;
                @endif

                // Marker
                marker = new google.maps.Marker({
                    position: {
                        lat: {{ $log->lat }},
                        lng: {{ $log->lng }}
                    },
                    map: map,
                    title: logType,
                    icon: iconOptions,
                    zIndex: 9998
                });

                // Info window
                infowindow = new google.maps.InfoWindow({
                    content: contentHtml
                });

                marker.addListener("click", () => {
                    infowindow.open(map, marker);
                });

                bounds.extend(marker.getPosition());
            @endif
        @endforeach


        if (!bounds.isEmpty()) {
            map.fitBounds(bounds);
        }
    }

    function toggleGeofence() {
        geofenceArray.forEach(geo => {
            geo.setMap(map);
        });
    }

    const galleryImages = document.querySelectorAll('.gallery-img');
    const modalImage = document.getElementById('modalImage');
    let currentIndex = 0;
    let imageSources = [];

    galleryImages.forEach((img, index) => {
        imageSources.push(img.src);
        img.dataset.index = index;

        img.addEventListener('click', () => {
            currentIndex = index;
            modalImage.src = imageSources[currentIndex];
        });
    });

    document.getElementById('prevImage').addEventListener('click', () => {
        if (currentIndex > 0) currentIndex--;
        modalImage.src = imageSources[currentIndex];
    });

    document.getElementById('nextImage').addEventListener('click', () => {
        if (currentIndex < imageSources.length - 1) currentIndex++;
        modalImage.src = imageSources[currentIndex];
    });

    // Calculate the centroid of the polygon
    function getPolygonCenter(path) {
        let lat = 0,
            lng = 0;
        path.forEach(point => {
            lat += point.lat;
            lng += point.lng;
        });
        return {
            lat: lat / path.length,
            lng: lng / path.length
        };
    }
</script>

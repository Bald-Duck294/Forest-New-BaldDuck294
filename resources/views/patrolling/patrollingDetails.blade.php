{{-- resources/views/patrolingDetails.blade.php --}}



@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    $user = session('user');
@endphp
@extends('layouts.app')
{{-- Page-specific styles for the map container --}}
<style>
    #patrolRouteMap {
        height: 450px;
        width: 100%;
        border-radius: .25rem;
        border: 1px solid #ddd;
    }
</style>

<div class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Patrol Details for Session: {{ $patrol->session ?? 'N/A' }}</h4>
                    <a href="{{ url()->previous() }}" class="btn btn-primary">&larr; Back to List</a>
                </div>
            </div>
            <div class="card-body">

                {{-- 1. Map container is placed here --}}
                <h5 class="mb-3">Patrol Route Map</h5>
                <div id="patrolRouteMap" data-geojson="{{ $patrol->path_geojson }}"
                    data-start-lat="{{ $patrol->start_lat }}" data-start-lng="{{ $patrol->start_lng }}"
                    data-start-time="{{ $patrol->started_at ? \Carbon\Carbon::parse($patrol->started_at)->format('d M, Y h:i A') : 'N/A' }}"
                    data-end-lat="{{ $patrol->end_lat }}" data-end-lng="{{ $patrol->end_lng }}"
                    data-end-time="{{ $patrol->ended_at ? \Carbon\Carbon::parse($patrol->ended_at)->format('d M, Y h:i A') : 'N/A' }}">
                </div>

                @if (!$patrol->path_geojson && !$patrol->start_lat)
                    <div class="alert alert-warning mt-3" role="alert">
                        No location data is available to display on the map for this patrol.
                    </div>
                @endif

                <hr>

                {{-- 2. Your original details table remains unchanged --}}
                <h5 class="mb-3">Patrol Data</h5>
                <table class="table table-bordered table-striped">
                    <tbody>
                        <tr>
                            <th style="width: 200px;">Patrol ID</th>
                            <td>{{ $patrol->id }}</td>
                        </tr>
                        <tr>
                            <th>Officer Name</th>
                            <td>{{ $patrol->user->name ?? 'N/A' }}</td>
                        </tr>
                        {{-- <tr>
                            <th>Beat Name</th>
                            <td>{{ $patrol->beat->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Range Name</th>
                            <td>{{ $patrol->beat->range->name ?? 'N/A' }}</td>
                        </tr> --}}
                        <tr>
                            <th>Status</th>
                            <td>
                                <span
                                    class="badge {{ $patrol->status === 'Completed' ? 'badge-success' : 'badge-info' }}">
                                    {{ $patrol->status }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Patrol Type</th>
                            <td>{{ $patrol->type ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Patrol By </th>
                            <td>{{ $patrol->session ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Started At</th>
                            <td>{{ $patrol->started_at ? \Carbon\Carbon::parse($patrol->started_at)->format('d M, Y h:i:s A') : 'N/A' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Ended At</th>
                            <td>{{ $patrol->ended_at ? \Carbon\Carbon::parse($patrol->ended_at)->format('d M, Y h:i:s A') : 'N/A' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Start Coordinates</th>
                            <td>{{ $patrol->start_lat ?? 'N/A' }}, {{ $patrol->start_lng ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>End Coordinates</th>
                            <td>{{ $patrol->end_lat ?? 'N/A' }}, {{ $patrol->end_lng ?? 'N/A' }}</td>
                        </tr>
                        {{-- <tr>
                            <th>Path GeoJSON</th>
                            <td>
                                <textarea class="form-control" rows="4"
                                    readonly>{{ $patrol->path_geojson ?? 'No path data.' }}</textarea>
                            </td>
                        </tr> --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


{{-- @push('scripts') --}}
<script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfBFN6L_HROTd-mS8QqUDRIqskkvHvFYk&libraries=geometry&callback=initMap"
    async defer></script>

<script>
    function initMap() {
        const mapElement = document.getElementById('patrolRouteMap');
        if (!mapElement) return;

        const geoJsonData = mapElement.dataset.geojson;
        const startLat = parseFloat(mapElement.dataset.startLat);
        const startLng = parseFloat(mapElement.dataset.startLng);
        const endLat = parseFloat(mapElement.dataset.endLat);
        const endLng = parseFloat(mapElement.dataset.endLng);

        console.log(startLat, startLng, endLat, endLng);

        if (isNaN(startLat) || isNaN(startLng)) {
            console.error("Invalid start coordinates for map.");
            return;
        }

        const map = new google.maps.Map(mapElement, {
            zoom: 15,
            center: {
                lat: startLat,
                lng: startLng
            },
            mapTypeId: 'roadmap'
        });

        const bounds = new google.maps.LatLngBounds();

        let pathCoordinates = [];

        // ✅ Case 1: Draw from GeoJSON if available
        if (geoJsonData) {
            try {
                const patrolPath = JSON.parse(geoJsonData);
                pathCoordinates = patrolPath.coordinates.map(coord => ({
                    lat: coord[1],
                    lng: coord[0]
                }));
            } catch (e) {
                console.error("Could not parse GeoJSON data:", e);
            }
        }

        // ✅ Case 2: If no GeoJSON but we have end coords → fallback to start & end
        if ((!geoJsonData || pathCoordinates.length === 0) && !isNaN(endLat) && !isNaN(endLng)) {
            pathCoordinates = [{
                    lat: startLat,
                    lng: startLng
                },
                {
                    lat: endLat,
                    lng: endLng
                }
            ];
        }

        // ✅ Draw polyline if we have coordinates
        if (pathCoordinates.length > 0) {
            const polyline = new google.maps.Polyline({
                path: pathCoordinates,
                geodesic: true,
                strokeColor: geoJsonData ? "#0000FF" : "#FF0000", // 🔵 Blue if full path, 🔴 Red if fallback
                strokeOpacity: 0.8,
                strokeWeight: 4
            });
            polyline.setMap(map);

            pathCoordinates.forEach(coord => bounds.extend(coord));
        }

        // Start marker
        if (!isNaN(startLat) && !isNaN(startLng)) {
            const startMarker = new google.maps.Marker({
                position: {
                    lat: startLat,
                    lng: startLng
                },
                map: map,
                title: 'Start Point',
                zIndex: 9999, // 👈 ensures it shows on top
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 8,
                    fillColor: "#28a745", // green
                    fillOpacity: 1,
                    strokeWeight: 2,
                    strokeColor: "#ffffff"
                }
            });

            const startInfoWindow = new google.maps.InfoWindow({
                content: `<strong>Start Point</strong><br>${mapElement.dataset.startTime}`
            });
            startMarker.addListener('click', () => startInfoWindow.open(map, startMarker));
            bounds.extend(startMarker.getPosition());
        }

        // End marker
        if (!isNaN(endLat) && !isNaN(endLng)) {
            const endMarker = new google.maps.Marker({
                position: {
                    lat: endLat,
                    lng: endLng
                },
                map: map,
                title: 'End Point',
                zIndex: 9999, // 👈 ensures it shows on top
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 8,
                    fillColor: "#dc3545", // red
                    fillOpacity: 1,
                    strokeWeight: 2,
                    strokeColor: "#ffffff"
                }
            });

            const endInfoWindow = new google.maps.InfoWindow({
                content: `<strong>End Point</strong><br>${mapElement.dataset.endTime}`
            });
            endMarker.addListener('click', () => endInfoWindow.open(map, endMarker));
            bounds.extend(endMarker.getPosition());
        }

        // ✅ Fit map to route
        map.fitBounds(bounds);
    }
</script>

{{-- @endpush --}}

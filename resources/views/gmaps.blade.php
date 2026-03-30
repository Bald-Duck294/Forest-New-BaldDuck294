@php
$hideGlobalFilters = true;
$hideBackground = true;
$user = session('user');
@endphp
@extends('layouts.app')

@section('content')
<style>
    /* Modern Dashboard Alignment Fixes */
    body,
    html {
        overflow-y: hidden !important;
    }

    .content {
        background-color: transparent;
        max-height: calc(100vh - 70px);
        overflow-y: auto;
        padding: 0;
    }

    .modern-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        margin-top: 15px;
        margin-bottom: 30px;
        overflow: hidden;
        box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    }

    .modern-header {
        display: flex;
        align-items: center;
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
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
        border-radius: 0.5rem;
        color: var(--text-muted);
        text-decoration: none !important;
        transition: all 0.2s ease;
    }

    .btn-back:hover {
        background: var(--bg-input);
        color: var(--text-main);
    }

    .header-title {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .header-icon {
        color: #3b82f6;
        font-size: 24px;
    }

    /* Map Styling */
    #mapCanvas {
        width: 100%;
        height: calc(100vh - 220px);
        min-height: 500px;
    }

    .info-strip {
        background: rgba(59, 130, 246, 0.05);
        padding: 12px 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        gap: 20px;
    }

    .info-item {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-main);
    }

    .info-label {
        color: var(--text-muted);
        margin-right: 5px;
    }
</style>

<div class="content">
    <div class="container-fluid">
        <div class="modern-card">

            <div class="modern-header">
                <a href="javascript:history.back()" class="btn-back" title="Go Back">
                    <i class="la la-arrow-left"></i>
                </a>
                <h4 class="header-title">
                    <i class="la la-map-marked-alt header-icon"></i>
                    {{ $geofence->name }} — Geofence View
                </h4>
            </div>

            <div class="info-strip">
                <div class="info-item"><span class="info-label">Type:</span> {{ $geofence->type }}</div>
                @if($geofence->type == 'Circle')
                <div class="info-item"><span class="info-label">Radius:</span> {{ $geofence->radius }} m</div>
                @endif
                <div class="info-item"><span class="info-label">ID:</span> #{{ $geofence->id }}</div>
            </div>

            <div id="mapCanvas"></div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var data = @json($geofence);

    window.initMap = function() {
        var map;
        var centerData;

        // 1. Parse Center Point
        try {
            centerData = typeof data.center === 'string' ? JSON.parse(data.center) : data.center;
        } catch (e) {
            centerData = {
                lat: 21.150585,
                lng: 79.103984
            };
        }

        var mapOptions = {
            mapTypeId: 'roadmap',
            center: new google.maps.LatLng(centerData.lat, centerData.lng),
            zoom: 15,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true
        };

        map = new google.maps.Map(document.getElementById("mapCanvas"), mapOptions);
        var infoWindow = new google.maps.InfoWindow();

        if (data.type == 'Circle') {
            // --- Circle Logic ---
            var circle = new google.maps.Circle({
                strokeColor: "#3b82f6",
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: "#3b82f6",
                fillOpacity: 0.35,
                map: map,
                center: new google.maps.LatLng(centerData.lat, centerData.lng),
                radius: parseFloat(data.radius)
            });

            var marker = new google.maps.Marker({
                position: new google.maps.LatLng(centerData.lat, centerData.lng),
                map: map,
            });

            map.fitBounds(circle.getBounds());

        } else if (data.type == 'Polygon') {
            // --- Polygon Logic (The Critical Part) ---

            var rawPath = [];
            try {
                // Try to find the coordinates in any of the possible column names
                var rawData = data.poly_coords || data.poly_lat_lng || data.coordinates;
                rawPath = typeof rawData === 'string' ? JSON.parse(rawData) : rawData;
            } catch (e) {
                console.error("JSON Parse Error:", e);
            }

            // DATA NORMALIZATION: Ensure we have an array of {lat, lng}
            // This handles cases where the data might be nested or in an unexpected format
            var cleanPath = [];
            if (Array.isArray(rawPath)) {
                cleanPath = rawPath.map(function(pt) {
                    return {
                        lat: parseFloat(pt.lat || pt.lat()), // handles objects or google objects
                        lng: parseFloat(pt.lng || pt.lng())
                    };
                }).filter(pt => !isNaN(pt.lat)); // Remove any broken points
            }

            if (cleanPath.length > 0) {
                var polygon = new google.maps.Polygon({
                    paths: cleanPath,
                    strokeColor: "#3b82f6",
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: "#3b82f6",
                    fillOpacity: 0.35,
                    map: map
                });

                // Auto-zoom to show the whole polygon
                var bounds = new google.maps.LatLngBounds();
                cleanPath.forEach(function(point) {
                    bounds.extend(point);
                });
                map.fitBounds(bounds);

                polygon.addListener('click', function(event) {
                    infoWindow.setContent("<div style='color:black; padding:5px;'><strong>" + data.name + "</strong></div>");
                    infoWindow.setPosition(event.latLng);
                    infoWindow.open(map);
                });
            } else {
                console.error("No valid coordinates found for polygon ID:", data.id);
                // Fallback: If polygon has no points, just show the center marker
                map.setCenter(new google.maps.LatLng(centerData.lat, centerData.lng));
            }
        }
    };
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfBFN6L_HROTd-mS8QqUDRIqskkvHvFYk&libraries=places&callback=initMap" async defer></script>
@endpush
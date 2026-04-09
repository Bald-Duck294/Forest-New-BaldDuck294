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
        align-items: center;
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

    /* Action styling for Locate Me button */
    .info-actions {
        margin-left: auto;
    }

    .btn-locate {
        background: var(--bg-card);
        color: var(--text-main);
        border: 1px solid var(--border-color);
        padding: 6px 14px;
        border-radius: 0.5rem;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    }

    .btn-locate:hover {
        background: var(--bg-input);
        border-color: #3b82f6;
        color: #3b82f6;
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

                <div class="info-actions">
                    <button type="button" class="btn-locate" onclick="locateMe()" title="Find My Location">
                        <i class="la la-crosshairs"></i> Locate Me
                    </button>
                </div>
            </div>

            <div id="mapCanvas"></div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    var data = @json($geofence);
    var map = null; // Moved map to global scope so LocateMe can access it
    var userMarker = null; // Marker specifically for the user's live location

    window.initMap = function() {
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
            mapTypeControl: true, // <-- CHANGED: Enabled Satellite toggle
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
                position: google.maps.ControlPosition.TOP_RIGHT
            },
            streetViewControl: false,
            fullscreenControl: true,
            gestureHandling: 'cooperative' // <-- CHANGED: Added Ctrl+Scroll requirement
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
            // --- Polygon Logic ---

            var rawPath = [];
            try {
                var rawData = data.poly_coords || data.poly_lat_lng || data.coordinates;
                rawPath = typeof rawData === 'string' ? JSON.parse(rawData) : rawData;
            } catch (e) {
                console.error("JSON Parse Error:", e);
            }

            var cleanPath = [];
            if (Array.isArray(rawPath)) {
                cleanPath = rawPath.map(function(pt) {
                    return {
                        lat: parseFloat(pt.lat || pt.lat()),
                        lng: parseFloat(pt.lng || pt.lng())
                    };
                }).filter(pt => !isNaN(pt.lat));
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
                map.setCenter(new google.maps.LatLng(centerData.lat, centerData.lng));
            }
        }
    };

    // NEW: Locate Me Function
    function locateMe() {
        if (!map) return;

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    var pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };

                    map.setCenter(pos);
                    map.setZoom(16);

                    // Create or move the user's live location marker (styled as a blue dot)
                    if (userMarker) {
                        userMarker.setPosition(pos);
                    } else {
                        userMarker = new google.maps.Marker({
                            position: pos,
                            map: map,
                            title: "You are here",
                            icon: {
                                path: google.maps.SymbolPath.CIRCLE,
                                scale: 8,
                                fillColor: "#4285F4",
                                fillOpacity: 1,
                                strokeColor: "white",
                                strokeWeight: 2,
                            }
                        });
                    }
                },
                function() {
                    if(typeof Swal !== 'undefined') {
                        Swal.fire("Error", "The Geolocation service failed or permission was denied.", "error");
                    } else {
                        alert("The Geolocation service failed or permission was denied.");
                    }
                }
            );
        } else {
            if(typeof Swal !== 'undefined') {
                Swal.fire("Error", "Your browser doesn't support geolocation.", "error");
            } else {
                alert("Your browser doesn't support geolocation.");
            }
        }
    }
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfBFN6L_HROTd-mS8QqUDRIqskkvHvFYk&libraries=places&callback=initMap" async defer></script>
@endpush

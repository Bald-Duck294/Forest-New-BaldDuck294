@php
$hideGlobalFilters = true;
$hideBackground = true;
$user = session('user');
@endphp
@extends('layouts.app')

@section('content')
<style>
    /* =========================================
       Theme Variables
       ========================================= */
    :root,
    [data-theme="light"],
    [data-bs-theme="light"],
    body.light-mode {
        --primary-color: #3b82f6;
        --primary-hover: #2563eb;
        --secondary-color: #64748b;
        --secondary-hover: #475569;
        --bg-page: #f8fafc;
        --bg-card: #ffffff;
        --bg-input: #f1f5f9;
        --border-color: #e2e8f0;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --danger-color: #ef4444;

        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --radius-md: 0.5rem;
        --radius-lg: 0.75rem;
    }

    .dark,
    .dark-mode,
    [data-theme="dark"],
    [data-bs-theme="dark"] {
        --primary-color: #3b82f6;
        --primary-hover: #60a5fa;
        --secondary-color: #475569;
        --secondary-hover: #64748b;
        --bg-page: #0f172a;
        --bg-card: #1e293b;
        --bg-input: #334155;
        --border-color: #334155;
        --text-main: #f8fafc;
        --text-muted: #94a3b8;
        --danger-color: #f87171;

        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.5);
    }

    /* System Fallback */
    @media (prefers-color-scheme: dark) {
        :root:not([data-theme="light"]):not([data-bs-theme="light"]):not(.light-mode) {
            --primary-color: #3b82f6;
            --primary-hover: #60a5fa;
            --secondary-color: #475569;
            --secondary-hover: #64748b;
            --bg-page: #0f172a;
            --bg-card: #1e293b;
            --bg-input: #334155;
            --border-color: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --danger-color: #f87171;
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.5);
        }
    }

    /* --- Fix Double Scrollbar --- */
    body,
    html {
        overflow-y: hidden !important;
    }

    /* Layout & Base */
    .content {
        background-color: transparent;
        color: var(--text-main);
        transition: all 0.3s ease;
        /* Handles the inner scrolling perfectly */
        max-height: calc(100vh - 70px);
        overflow-y: auto;
        padding-bottom: 40px;
    }

    .modern-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-top: 15px;
        margin-bottom: 30px;
    }

    /* Header Styling */
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
        color: var(--primary-color);
        font-size: 24px;
    }

    /* Controls Panel */
    .map-controls-panel {
        padding: 20px 24px;
        display: flex;
        flex-wrap: wrap;
        gap: 24px;
        align-items: center;
        border-bottom: 1px solid var(--border-color);
        background: rgba(0, 0, 0, 0.01);
    }

    /* Modern Segmented Toggle */
    .geo-type-toggle {
        display: inline-flex;
        background: var(--bg-input);
        border-radius: var(--radius-md);
        padding: 4px;
        border: 1px solid var(--border-color);
    }

    .btn-toggle {
        background: transparent;
        border: none;
        padding: 8px 20px;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-muted);
        border-radius: calc(var(--radius-md) - 2px);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-toggle.active {
        background: var(--bg-card);
        color: var(--primary-color);
        box-shadow: var(--shadow-sm);
    }

    /* Modern Range Slider */
    .radius-control {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-grow: 1;
        max-width: 400px;
    }

    .radius-control label {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-main);
        white-space: nowrap;
    }

    .range-slider__range {
        -webkit-appearance: none;
        width: 100%;
        height: 6px;
        border-radius: 5px;
        background: var(--border-color);
        outline: none;
        transition: background 0.2s;
    }

    .range-slider__range::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: var(--primary-color);
        cursor: pointer;
        box-shadow: 0 0 0 3px var(--bg-card), 0 0 0 6px var(--primary-color);
        transition: transform 0.1s;
    }

    .range-slider__range::-webkit-slider-thumb:active {
        transform: scale(1.2);
    }

    .range-slider__value {
        background: var(--bg-input);
        color: var(--primary-color);
        padding: 4px 12px;
        border-radius: var(--radius-md);
        font-size: 14px;
        font-weight: 700;
        min-width: 70px;
        text-align: center;
        border: 1px solid var(--border-color);
    }

    /* Map Container */
    .map-wrapper {
        position: relative;
        width: 100%;
        background: var(--bg-input);
    }

    #map {
        width: 100%;
        /* Dynamic height so it perfectly fits your screen without excessive scrolling */
        height: calc(100vh - 360px);
        min-height: 400px;
    }

    /* Google Maps Search Input Styling (Injected by API) */
    #searchMapInput {
        background-color: var(--bg-card);
        color: var(--text-main);
        border: 1px solid var(--border-color);
        font-family: inherit;
        font-size: 14px;
        font-weight: 500;
        padding: 10px 16px;
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-md);
        margin-top: 15px;
        margin-left: 15px;
        width: 300px;
        outline: none;
    }

    #searchMapInput:focus {
        border-color: var(--primary-color);
    }

    /* Action Footer */
    .action-footer {
        padding: 20px 24px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 12px;
        border-top: 1px solid var(--border-color);
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
        border-radius: var(--radius-md);
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }

    .btn-primary {
        background: var(--primary-color);
        color: #ffffff !important;
    }

    .btn-primary:hover {
        background: var(--primary-hover);
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: var(--bg-input);
        color: var(--text-main) !important;
        border: 1px solid var(--border-color);
    }

    .btn-secondary:hover {
        background: var(--border-color);
    }

    /* SweetAlert Dark Mode Fix */
    .swal2-popup {
        background: var(--bg-card) !important;
        color: var(--text-main) !important;
    }

    .swal2-input {
        color: var(--text-main) !important;
        background: var(--bg-input) !important;
        border-color: var(--border-color) !important;
    }

    .swal2-title {
        color: var(--text-main) !important;
    }

    @media (max-width: 768px) {
        .map-controls-panel {
            flex-direction: column;
            align-items: stretch;
        }

        .radius-control {
            max-width: 100%;
        }

        .action-footer {
            justify-content: stretch;
            flex-direction: column;
        }

        .btn-action {
            width: 100%;
            justify-content: center;
        }

        #searchMapInput {
            width: calc(100% - 30px) !important;
        }
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
                    <i class="la la-map-pin header-icon"></i>
                    Add Geofence
                </h4>
            </div>

            <form id="geofence" onsubmit="event.preventDefault();">
                {{ csrf_field() }}

                <div class="map-controls-panel">
                    <div class="geo-type-toggle">
                        <button type="button" class="btn-toggle active" id="geo-circle" onclick="changeGeoType('Circle')">
                            <i class="la la-circle-o"></i> Circle
                        </button>
                        <button type="button" class="btn-toggle" id="geo-polygon" onclick="changeGeoType('Polygon')">
                            <i class="la la-draw-polygon"></i> Polygon
                        </button>
                    </div>

                    <div class="radius-control" id="slider-wrapper">
                        <label>Radius</label>
                        <input class="range-slider__range" type="range" id="sliderRange" value="0" min="0" max="1000" step="10">
                        <span class="range-slider__value">0 m</span>
                    </div>
                </div>

                <div class="map-wrapper">
                    <input type="text" id="searchMapInput" placeholder="Search for location...">
                    <div id="map"></div>
                </div>

                <div class="action-footer">
                    <button type="button" class="btn-action btn-secondary" onclick="clearMap()">
                        <i class="la la-eraser"></i> Clear Map
                    </button>
                    <button type="button" class="btn-action btn-primary" onclick="myFunction()">
                        <i class="la la-save"></i> Save Geofence
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfBFN6L_HROTd-mS8QqUDRIqskkvHvFYk&libraries=places,drawing&callback=initMap" async defer></script>

<script>
    var lat = 21.150585;
    var lng = 79.103984;
    var map = null;
    var cityCircle = null; // This will hold either our Circle OR our Polygon
    var marker = null;
    var radius = null;
    var geoName = null;
    var client_id = <?php echo json_encode($client_id ?? ''); ?>;
    var site_id = <?php echo json_encode($site_id ?? ''); ?>;
    var drawingManager;
    var geoType = 'Circle';
    var coord;

    // Range Slider Logic
    var rangeSlider = function() {
        var slider = $('#sliderRange'),
            valueDisplay = $('.range-slider__value');

        valueDisplay.html(slider.val() + ' m');
        slider.on('input', function() {
            valueDisplay.html(this.value + ' m');
        });
    };

    $(document).ready(function() {
        rangeSlider();
    });

    window.initMap = function() {
        map = new google.maps.Map(document.getElementById('map'), {
            center: {
                lat: lat,
                lng: lng
            },
            zoom: 13,
            mapTypeControl: false,
            streetViewControl: false
        });

        var input = document.getElementById('searchMapInput');
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        var autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.bindTo('bounds', map);

        var infowindow = new google.maps.InfoWindow();
        marker = new google.maps.Marker({
            map: map,
            anchorPoint: new google.maps.Point(0, -29)
        });

        autocomplete.addListener('place_changed', function() {
            infowindow.close();
            marker.setVisible(false);
            var place = autocomplete.getPlace();

            if (!place.geometry) return;

            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(17);
            }

            lat = place.geometry.location.lat();
            lng = place.geometry.location.lng();

            marker.setPosition(place.geometry.location);
            marker.setVisible(true);
            map.panTo(place.geometry.location);

            var address = '';
            if (place.address_components) {
                address = [
                    (place.address_components[0] && place.address_components[0].short_name || ''),
                    (place.address_components[1] && place.address_components[1].short_name || ''),
                    (place.address_components[2] && place.address_components[2].short_name || '')
                ].join(' ');
            }

            infowindow.setContent('<div><strong style="color: black;">' + place.name + '</strong><br><span style="color: gray;">' + address + '</span></div>');
            infowindow.open(map, marker);
        });

        // Drawing Manager Setup
        drawingManager = new google.maps.drawing.DrawingManager({
            drawingMode: google.maps.drawing.OverlayType.DEFAULT,
            drawingControl: false,
            polygonOptions: {
                editable: true,
                draggable: true,
                fillColor: '#3b82f6',
                strokeColor: '#2563eb',
                strokeWeight: 2,
                fillOpacity: 0.4,
            },
            circleOptions: {
                fillColor: '#3b82f6',
                strokeColor: '#2563eb',
                fillOpacity: 0.4,
                strokeWeight: 2,
                clickable: true,
                editable: true,
                draggable: true,
                zIndex: 1
            }
        });

        drawingManager.setMap(map);

        map.addListener("click", (e) => {
            // Only place a click-marker if we are in Circle mode
            if (geoType === 'Circle') {
                lat = e.latLng.lat();
                lng = e.latLng.lng();
                placeMarkerAndPanTo(e.latLng, map);
            }
        });

        // FIXED: The Drawing Listener
        google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event) {
            // If a previous shape exists, remove it
            if (cityCircle) cityCircle.setMap(null);
            cityCircle = event.overlay;

            if (event.type == 'polygon') {
                var path = event.overlay.getPath();

                if (path.getLength() < 3) {
                    Swal.fire('Error', 'Please draw at least 3 points.', 'error');
                    event.overlay.setMap(null);
                    return;
                }

                // Official way to get coordinates:
                var coordsArray = [];
                for (var i = 0; i < path.getLength(); i++) {
                    var point = path.getAt(i);
                    coordsArray.push({
                        lat: point.lat(),
                        lng: point.lng()
                    });
                }
                coord = JSON.stringify(coordsArray);
            }
        });

        function placeMarkerAndPanTo(latLng, map) {
            if (marker) marker.setMap(null);
            marker = new google.maps.Marker({
                position: latLng,
                map: map,
            });
            map.panTo(latLng);
        }

        document.getElementById("sliderRange").oninput = function() {
            if (typeof google === 'undefined') return;

            radius = +this.value;
            if (cityCircle) cityCircle.setMap(null);

            cityCircle = new google.maps.Circle({
                strokeColor: "#2563eb",
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: "#3b82f6",
                fillOpacity: 0.35,
                map: map,
                center: {
                    lat: lat,
                    lng: lng
                },
                radius: radius,
            });
        };
    };

    function changeGeoType(type) {
        geoType = type;
        $('.btn-toggle').removeClass('active');
        clearMap(); // Clean map when switching modes

        if (type === 'Polygon') {
            $('#geo-polygon').addClass('active');
            $('#slider-wrapper').hide();
            drawingManager.setDrawingMode(google.maps.drawing.OverlayType.POLYGON);
        } else {
            $('#geo-circle').addClass('active');
            $('#slider-wrapper').show();
            drawingManager.setDrawingMode(google.maps.drawing.OverlayType.DEFAULT);
        }
    }

    function clearMap() {
        if (marker) marker.setMap(null);
        if (cityCircle) cityCircle.setMap(null);
        coord = null;
        radius = 0;
        $('#sliderRange').val(0);
        $('.range-slider__value').html('0 m');
    }

    function myFunction() {
        if ((radius && lat && geoType === 'Circle') || (coord && geoType === 'Polygon')) {
            Swal.fire({
                title: 'Name this Geofence',
                input: 'text',
                inputAttributes: {
                    placeholder: 'Enter geofence name'
                },
                showCancelButton: true,
                confirmButtonText: 'Save',
                confirmButtonColor: 'var(--primary-color)',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed && result.value.trim() !== '') {
                    geoName = result.value;

                    var formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('name', geoName);
                    formData.append('center', JSON.stringify({
                        lat: lat,
                        lng: lng
                    }));
                    formData.append('radius', radius || 0);
                    formData.append('poly_coords', coord || '');
                    formData.append('type', geoType);

                    var url = '{{ route("clients.geofencestore", [":client_id", ":site_id"]) }}'
                        .replace(':client_id', client_id)
                        .replace(':site_id', site_id);

                    $.ajax({
                        type: "POST",
                        url: url,
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function() {
                            Swal.fire("Success", "Geofence Created", "success").then(() => {
                                window.location.href = '{{ route("clients.getclientgeofences", [":client_id", ":site_id"]) }}'
                                    .replace(':client_id', client_id)
                                    .replace(':site_id', site_id);
                            });
                        },
                        error: function(xhr) {
                            Swal.fire("Error", "Server returned " + xhr.status, "error");
                        }
                    });
                }
            });
        } else {
            Swal.fire("Error", "Please draw a shape on the map first.", "error");
        }
    }
</script>
@endpush
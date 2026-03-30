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

    /* --- Alignment & Scrollbar Fixes --- */
    body,
    html {
        overflow-y: hidden !important;
    }

    .content {
        background-color: transparent;
        color: var(--text-main);
        transition: all 0.3s ease;
        max-height: calc(100vh - 70px);
        overflow-y: auto;
        padding: 0;
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

    .radius-control {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-grow: 1;
        max-width: 400px;
    }

    .range-slider__range {
        -webkit-appearance: none;
        width: 100%;
        height: 6px;
        border-radius: 5px;
        background: var(--border-color);
        outline: none;
    }

    .range-slider__range::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: var(--primary-color);
        cursor: pointer;
        box-shadow: 0 0 0 3px var(--bg-card), 0 0 0 6px var(--primary-color);
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

    /* Map Area */
    .map-wrapper {
        position: relative;
        width: 100%;
        background: var(--bg-input);
    }

    #map {
        width: 100%;
        height: calc(100vh - 360px);
        min-height: 450px;
    }

    #searchMapInput {
        background-color: var(--bg-card);
        color: var(--text-main);
        border: 1px solid var(--border-color);
        padding: 10px 16px;
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-md);
        margin-top: 15px;
        margin-left: 15px;
        width: 300px;
        outline: none;
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
        border-radius: var(--radius-md);
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none !important;
        border: none;
    }

    .btn-primary {
        background: var(--primary-color);
        color: #fff !important;
    }

    .btn-primary:hover {
        background: var(--primary-hover);
    }

    .btn-secondary {
        background: var(--bg-input);
        color: var(--text-main) !important;
        border: 1px solid var(--border-color);
    }

    .btn-secondary:hover {
        background: var(--border-color);
    }

    @media (max-width: 768px) {
        .map-controls-panel {
            flex-direction: column;
            align-items: stretch;
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
                    <i class="la la-edit header-icon"></i>
                    Update Geofence
                </h4>
            </div>

            <form id="geofence" onsubmit="event.preventDefault();">
                {{ csrf_field() }}

                <div class="map-controls-panel">
                    <div class="geo-type-toggle">
                        <button type="button" class="btn-toggle {{ $geofence->type == 'Circle' ? 'active' : '' }}" id="geo-circle" onclick="changeGeoType('Circle')">
                            <i class="la la-circle-o"></i> Circle
                        </button>
                        <button type="button" class="btn-toggle {{ $geofence->type == 'Polygon' ? 'active' : '' }}" id="geo-polygon" onclick="changeGeoType('Polygon')">
                            <i class="la la-draw-polygon"></i> Polygon
                        </button>
                    </div>

                    <div class="radius-control" id="slider-wrapper" style="{{ $geofence->type == 'Polygon' ? 'display:none;' : '' }}">
                        <label>Radius</label>
                        <input class="range-slider__range" type="range" id="sliderRange" value="{{$geofence->radius}}" min="0" max="1000" step="10">
                        <span class="range-slider__value">{{$geofence->radius}} m</span>
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
                        <i class="la la-refresh"></i> Update Geofence
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
    var lat = @json($lat);
    var lng = @json($lng);
    var map = null;
    var cityCircle = null;
    var marker = null;
    var client_id = @json($client_id);
    var site_id = @json($site_id);
    var id = @json($id);
    var drawingManager;
    // FIXED: Removed spaces from ->
    var radius = @json($geofence->radius);
    var geoType = @json($geofence->type);
    var coord = @json($geofence->poly_lat_lng);
    var initialData = @json($geofence);
    // Initialize Range Slider UI
    $(document).ready(function() {
        $('#sliderRange').on('input', function() {
            $('.range-slider__value').html(this.value + ' m');
        });
    });

    window.initMap = function() {
        map = new google.maps.Map(document.getElementById('map'), {
            center: {
                lat: lat,
                lng: lng
            },
            zoom: 15,
            mapTypeControl: false,
            streetViewControl: false
        });

        var input = document.getElementById('searchMapInput');
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
        var autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.bindTo('bounds', map);

        marker = new google.maps.Marker({
            position: {
                lat: lat,
                lng: lng
            },
            map: map,
            draggable: true
        });

        // Load existing shape
        if (initialData.type == 'Circle') {
            var center = JSON.parse(initialData.center);
            cityCircle = new google.maps.Circle({
                strokeColor: "#FF0000",
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: "#FF0000",
                fillOpacity: 0.35,
                map: map,
                center: {
                    lat: center.lat,
                    lng: center.lng
                },
                radius: +initialData.radius,
                editable: true
            });
        } else if (coord) {
            cityCircle = new google.maps.Polygon({
                paths: JSON.parse(coord),
                strokeColor: "#FF0000",
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: "#FF0000",
                fillOpacity: 0.35,
                map: map,
                editable: true
            });
        }

        autocomplete.addListener('place_changed', function() {
            var place = autocomplete.getPlace();
            if (!place.geometry) return;

            if (place.geometry.viewport) map.fitBounds(place.geometry.viewport);
            else {
                map.setCenter(place.geometry.location);
                map.setZoom(17);
            }

            marker.setPosition(place.geometry.location);
            lat = place.geometry.location.lat();
            lng = place.geometry.location.lng();
        });

        drawingManager = new google.maps.drawing.DrawingManager({
            drawingMode: google.maps.drawing.OverlayType.DEFAULT,
            drawingControl: false,
            polygonOptions: {
                fillColor: '#3b82f6',
                fillOpacity: 0.4,
                strokeWeight: 2,
                editable: true
            }
        });
        drawingManager.setMap(map);

        google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event) {
            if (event.type == 'polygon') {
                if (cityCircle) cityCircle.setMap(null);
                cityCircle = event.overlay;
                var path = cityCircle.getPath();
                coord = JSON.stringify(path.getArray());
            }
        });

        // Slider logic
        document.getElementById("sliderRange").oninput = function() {
            radius = +this.value;
            if (geoType === 'Circle') {
                if (cityCircle) cityCircle.setRadius(radius);
            }
        };
    };

    function changeGeoType(type) {
        geoType = type;
        $('.btn-toggle').removeClass('active');
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
    }

    function myFunction() {
        if ((radius && lat && geoType === 'Circle') || (coord && geoType === 'Polygon')) {
            Swal.fire({
                title: 'Update Geofence Name',
                input: 'text',
                // FIXED: Removed spaces from ->
                inputValue: @json($geofence -> name),
                showCancelButton: true,
                confirmButtonText: 'Update',
                confirmButtonColor: 'var(--primary-color)',
                reverseButtons: true,
                inputValidator: (value) => {
                    if (!value) return 'Name is required!';
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    var formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('name', result.value);
                    formData.append('center', JSON.stringify({
                        lat: marker.getPosition().lat(),
                        lng: marker.getPosition().lng()
                    }));
                    formData.append('radius', radius || 0);
                    formData.append('poly_coords', coord || '');
                    formData.append('type', geoType);

                    var url = '{{ route("clients.geofence_editaction",[":client_id",":site_id",":id"]) }}'
                        .replace(':client_id', client_id).replace(':site_id', site_id).replace(':id', id);

                    $.ajax({
                        type: "POST",
                        url: url,
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            Swal.fire("Success", "Geofence updated successfully!", "success").then(() => {
                                window.location.href = '{{ route("clients.getclientgeofences",[":client_id",":site_id"]) }}'
                                    .replace(':client_id', client_id).replace(':site_id', site_id);
                            });
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            Swal.fire("Error", "Update failed. Check console for details.", "error");
                        }
                    });
                }
            });
        } else {
            Swal.fire("Error", "Please define a geofence on the map first.", "error");
        }
    }
</script>
@endpush

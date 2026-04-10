@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    // $user = session('user');
    // dump('testing');
@endphp
@extends('layouts.app')

@section('title', 'Add Asset')

@section('content')

    <style>
        /* =========================================
                               SAPPHIRE FORM STYLES
                            ========================================= */
        .dash-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        }

        .custom-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 700;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .custom-input {
            background-color: var(--bg-body);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            width: 100%;
            outline: none;
        }

        .custom-input:focus {
            border-color: var(--sapphire-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        html[data-bs-theme="dark"] .custom-input {
            color-scheme: dark;
        }

        /* File Input Styling */
        .custom-file-input::file-selector-button {
            background-color: var(--bg-body);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 6px 12px;
            margin-right: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s ease;
        }

        .custom-file-input::file-selector-button:hover {
            background-color: var(--table-hover);
            color: var(--sapphire-primary);
            border-color: var(--sapphire-primary);
        }

        /* Buttons */
        .btn-sapphire {
            background-color: var(--sapphire-primary);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 10px 24px;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-sapphire:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            color: #ffffff;
        }

        .btn-cancel {
            background-color: transparent;
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 10px 24px;
            font-weight: 600;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-cancel:hover {
            background-color: var(--table-hover);
            color: var(--text-main);
        }

        .btn-back-soft {
            display: inline-flex;
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

        .btn-back-soft:hover {
            background: var(--table-hover);
            color: var(--sapphire-primary);
            border-color: var(--sapphire-primary);
        }

        /* Map */
        #map {
            border: 1px solid var(--border-color);
            border-radius: 8px;
        }
    </style>

    <div class="container-fluid py-4">

        {{-- HEADER --}}
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('assets.index') }}" class="btn-back-soft" title="Go Back">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="fw-bold mb-0" style="color: var(--text-main);">Add New Asset</h4>
                <p class="text-muted small mb-0">Register a new tool, vehicle, or facility.</p>
            </div>
        </div>

        {{-- FORM CARD --}}
        <div class="dash-card p-4">
            <form action="{{ route('assets.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="custom-label">Asset Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="custom-input" placeholder="Enter asset name..."
                            required>
                    </div>

                    <div class="col-md-6">
                        <label class="custom-label">Category</label>
                        <select name="category" class="custom-input">
                            <option value="">Select Category...</option>
                            <option>Offices / Govt Residence</option>
                            <option>Nursery</option>
                            <option>Plantations</option>
                            <option>Eco Tourism Sites</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="custom-label">Condition</label>
                        <select name="condition" class="custom-input">
                            <option>Good</option>
                            <option>Needs Repair</option>
                            <option>Poor</option>
                            <option>Not in Use</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="custom-label">Year of Acquisition</label>
                        <input type="number" name="year" class="custom-input" placeholder="YYYY">
                    </div>

                    <div class="col-md-12">
                        <label class="custom-label">Description & Notes</label>
                        <textarea name="description" rows="3" class="custom-input" placeholder="Provide any additional details..."></textarea>
                    </div>

                    <div class="col-md-12">
                        <label class="custom-label">Upload Photos</label>
                        <input type="file" name="photos[]" class="custom-input custom-file-input" multiple
                            accept="image/*">
                        <div class="text-muted mt-1" style="font-size: 0.75rem;">Supported formats: JPG, PNG. You can select
                            multiple files.</div>
                    </div>

                    <div class="col-12 border-top my-2" style="border-color: var(--border-color) !important;"></div>

                    <div class="col-md-12">
                        <label class="custom-label"><i class="bi bi-geo-alt text-primary me-1"></i> Asset Location</label>
                        <input id="pac-input" class="custom-input mb-3" type="text"
                            placeholder="Search for a place or region...">

                        <div id="map" style="height: 350px; width: 100%;"></div>
                        <div class="text-muted mt-2 text-end" style="font-size: 0.75rem;">Drag the pin to set precise
                            coordinates.</div>

                        <input type="hidden" id="lat" name="location[lat]">
                        <input type="hidden" id="lng" name="location[lng]">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-5">
                    <a href="{{ route('assets.index') }}" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-sapphire">
                        <i class="bi bi-floppy"></i> Save Asset
                    </button>
                </div>
            </form>
        </div>

    </div>

    @push('scripts')
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfBFN6L_HROTd-mS8QqUDRIqskkvHvFYk&libraries=places">
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

            function initMap() {
                const latInput = document.getElementById("lat");
                const lngInput = document.getElementById("lng");
                const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';

                const initialLat = parseFloat(latInput.value) || 21.1108512;
                const initialLng = parseFloat(lngInput.value) || 79.0628162;

                mapInstance = new google.maps.Map(document.getElementById("map"), {
                    center: {
                        lat: initialLat,
                        lng: initialLng
                    },
                    zoom: 13,
                    disableDefaultUI: false,
                    styles: isDark ? mapDarkStyle : []
                });

                const marker = new google.maps.Marker({
                    position: {
                        lat: initialLat,
                        lng: initialLng
                    },
                    map: mapInstance,
                    draggable: true,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 8,
                        fillColor: "#3b82f6",
                        fillOpacity: 1,
                        strokeWeight: 3,
                        strokeColor: "#ffffff"
                    }
                });

                google.maps.event.addListener(marker, "dragend", function(evt) {
                    latInput.value = evt.latLng.lat().toFixed(6);
                    lngInput.value = evt.latLng.lng().toFixed(6);
                });

                const input = document.getElementById("pac-input");
                const autocomplete = new google.maps.places.Autocomplete(input);
                autocomplete.bindTo("bounds", mapInstance);

                autocomplete.addListener("place_changed", function() {
                    const place = autocomplete.getPlace();
                    if (!place.geometry) return;

                    if (place.geometry.viewport) {
                        mapInstance.fitBounds(place.geometry.viewport);
                    } else {
                        mapInstance.setCenter(place.geometry.location);
                        mapInstance.setZoom(16);
                    }

                    marker.setPosition(place.geometry.location);
                    latInput.value = place.geometry.location.lat().toFixed(6);
                    lngInput.value = place.geometry.location.lng().toFixed(6);
                });

                latInput.value = initialLat.toFixed(6);
                lngInput.value = initialLng.toFixed(6);
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

            document.addEventListener("DOMContentLoaded", initMap);
        </script>
    @endpush
@endsection

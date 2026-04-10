@extends('layouts.app')

@section('title', 'View Asset')

@section('content')

    <style>
        /* =========================================
                               SAPPHIRE VIEW STYLES
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
            flex: 1 1 200px;
            padding: 1.25rem 1.5rem;
            border-right: 1px solid var(--border-color);
        }

        .metadata-item:last-child {
            border-right: none;
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

        /* Buttons */
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

        .btn-back-soft {
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

        .btn-back-soft:hover {
            background: var(--table-hover);
            color: var(--sapphire-primary);
            border-color: var(--sapphire-primary);
        }

        /* Gallery */
        .gallery-img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .gallery-img:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        /* Map */
        #asset-map {
            height: 300px;
            width: 100%;
            border-radius: 8px;
        }
    </style>

    <div class="container-fluid py-4 px-md-4">

        {{-- TOP HEADER --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('assets.index') }}" class="btn-back-soft" title="Go Back">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h4 class="fw-bold mb-1" style="color: var(--text-main);">
                        {{ $asset->name }}
                    </h4>
                    <div class="text-muted small">AST-{{ $asset->id }} • Added {{ $asset->created_at->format('d M Y') }}
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('assets.edit', $asset->id) }}" class="btn-sapphire-outline">
                    <i class="bi bi-pencil-square"></i> Edit
                </a>
                <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-sapphire-outline"
                        style="color: var(--sapphire-danger, #ef4444); border-color: rgba(239, 68, 68, 0.5);"
                        onclick="return confirm('Delete this asset?')">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>

        {{-- METADATA BAR --}}
        <div class="metadata-bar shadow-sm">
            <div class="metadata-item">
                <div class="metadata-label">Category</div>
                <div class="metadata-value">{{ $asset->category ?? 'Uncategorized' }}</div>
            </div>
            <div class="metadata-item">
                <div class="metadata-label">Condition</div>
                <div class="metadata-value">
                    <span class="badge"
                        style="background: var(--bg-body); border: 1px solid var(--border-color); padding: 4px 8px; font-weight: normal; color: var(--text-main);">
                        {{ $asset->condition ?? 'Unknown' }}
                    </span>
                </div>
            </div>
            <div class="metadata-item">
                <div class="metadata-label">Acquisition Year</div>
                <div class="metadata-value">{{ $asset->year ?? 'N/A' }}</div>
            </div>
        </div>

        <div class="row g-4">

            {{-- LEFT COLUMN: Details & Photos --}}
            <div class="col-lg-7">

                <div class="dash-card mb-4">
                    <div class="card-header-custom">
                        <i class="bi bi-card-text me-2" style="color: var(--text-muted);"></i> Description
                    </div>
                    <div class="p-3" style="color: var(--text-main); font-size: 0.95rem; line-height: 1.6;">
                        {{ $asset->description ?: 'No description provided for this asset.' }}
                    </div>
                </div>

                @if ($asset->photos && is_array($asset->photos) && count($asset->photos) > 0)
                    <div class="dash-card">
                        <div class="card-header-custom">
                            <i class="bi bi-images me-2" style="color: var(--text-muted);"></i> Asset Photos
                        </div>
                        <div class="p-3">
                            <div class="row g-3">
                                @foreach ($asset->photos as $p)
                                    <div class="col-6 col-sm-4">
                                        <a href="{{ $p }}" target="_blank">
                                            <img src="{{ $p }}" class="gallery-img shadow-sm">
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- RIGHT COLUMN: Map --}}
            <div class="col-lg-5">
                <div class="dash-card">
                    <div class="card-header-custom d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-geo-alt-fill me-2" style="color: var(--text-muted);"></i> Location</span>
                        @if ($asset->location)
                            <small style="color: var(--text-muted); font-weight: normal;">
                                {{ $asset->location['lat'] }}, {{ $asset->location['lng'] }}
                            </small>
                        @endif
                    </div>
                    <div class="p-2">
                        @if ($asset->location && !empty($asset->location['lat']) && !empty($asset->location['lng']))
                            <div id="asset-map"></div>
                        @else
                            <div class="d-flex align-items-center justify-content-center bg-light rounded text-muted"
                                style="height: 300px; border: 1px dashed var(--border-color); background: var(--bg-body) !important;">
                                <div class="text-center">
                                    <i class="bi bi-geo-alt fs-1 mb-2"></i>
                                    <p class="mb-0">No coordinates saved for this asset.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    @if ($asset->location && !empty($asset->location['lat']) && !empty($asset->location['lng']))
        @push('scripts')
            <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfBFN6L_HROTd-mS8QqUDRIqskkvHvFYk"></script>
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

                document.addEventListener("DOMContentLoaded", function() {
                    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                    const lat = parseFloat(`{{ $asset->location['lat'] }}`);
                    const lng = parseFloat(`{{ $asset->location['lng'] }}`);

                    mapInstance = new google.maps.Map(document.getElementById("asset-map"), {
                        center: {
                            lat: lat,
                            lng: lng
                        },
                        zoom: 15,
                        disableDefaultUI: false,
                        styles: isDark ? mapDarkStyle : []
                    });

                    new google.maps.Marker({
                        position: {
                            lat: lat,
                            lng: lng
                        },
                        map: mapInstance,
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 8,
                            fillColor: "#3b82f6",
                            fillOpacity: 1,
                            strokeWeight: 3,
                            strokeColor: "#ffffff"
                        }
                    });
                });

                // Dynamic Theme Listener
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
    @endif

@endsection

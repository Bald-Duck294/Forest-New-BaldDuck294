<div id="overall-container" class="row g-4">

    {{-- ==========================================
         65% MAP AREA WITH GLASS SIDEBAR OVERLAY
    ========================================== --}}
    <div class="col-lg-8">
        <div class="dash-card p-0 position-relative overflow-hidden" style="height: 550px; z-index: 1;">

            {{-- The Google Map Container --}}
            <div id="map" class="h-100 w-100" style="z-index: 0;"></div>

            {{-- Floating Drawer Toggle (Opens Layer Menu) --}}
            <button class="drawer-toggle shadow-sm" id="mapDrawerToggle">
                <i class="bi bi-layers-half"></i>
                <span>Layers</span>
            </button>

            {{-- The Glass Panel Sidebar (Layer Filters) --}}
            <div class="map-filter-sidebar glass-panel shadow-lg" id="mapFilterSidebar">
                <div class="sidebar-header">
                    <h5 class="mb-0 fw-bold" style="color: var(--text-main);">Map Layers</h5>
                    <div class="sub-title"
                        style="color: var(--sapphire-primary); font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">
                        Select to show/hide
                    </div>
                </div>

                <div class="sidebar-content">
                    {{-- Hidden form to prevent JS FormData errors --}}
                    <form id="filterForm" class="d-none">
                        <input type="hidden" name="range_id" value="">
                        <input type="hidden" name="site_id" value="">
                        <input type="hidden" name="year" value="">
                    </form>

                    <div id="layerControlsContainer">
                        {{-- Geofence Layer --}}
                        <div class="layer-item" id="item_geofences" onclick="toggleLayerUI('geofences')">
                            <div class="status-dot" style="background-color: var(--sapphire-primary);"></div>
                            <div class="layer-icon-box" style="color: var(--sapphire-primary);">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div class="layer-label">Beat Boundary</div>
                            <div id="count_geofences" class="count-pill">0</div>
                            <div class="eye-toggle" id="eye_geofences"><i class="bi bi-eye-fill"></i></div>
                            <input type="checkbox" class="layer-toggle d-none" value="geofences" id="check_geofences">
                        </div>

                        {{-- Dynamic Layers --}}
                        @php
                            $layers = [
                                [
                                    'id' => 'drainage',
                                    'label' => 'Drainage',
                                    'color' => '#3B82F6',
                                    'icon' => 'bi-droplet-half',
                                ],
                                [
                                    'id' => 'elephant_movement',
                                    'label' => 'Elephant Movements',
                                    'color' => '#F59E0B',
                                    'icon' => 'bi-paw',
                                ],
                                [
                                    'id' => 'fire_point',
                                    'label' => 'Fire Points',
                                    'color' => '#EF4444',
                                    'icon' => 'bi-fire',
                                ],
                                [
                                    'id' => 'forest_boundary',
                                    'label' => 'Forest Boundary',
                                    'color' => '#10B981',
                                    'icon' => 'bi-leaf-fill',
                                ],
                                [
                                    'id' => 'plantation_site',
                                    'label' => 'Plantation Sites',
                                    'color' => '#06B6D4',
                                    'icon' => 'bi-flower1',
                                ],
                                [
                                    'id' => 'revenue_forest_land',
                                    'label' => 'Revenue Forest Land',
                                    'color' => '#8B5CF6',
                                    'icon' => 'bi-globe',
                                ],
                                [
                                    'id' => 'water_body',
                                    'label' => 'Water Bodies',
                                    'color' => '#3B82F6',
                                    'icon' => 'bi-cloud-rain-fill',
                                ],
                            ];
                        @endphp
                        @foreach ($layers as $layer)
                            <div class="layer-item" id="item_{{ $layer['id'] }}"
                                onclick="toggleLayerUI('{{ $layer['id'] }}')">
                                <div class="status-dot" style="background-color: {{ $layer['color'] }}"></div>
                                <div class="layer-icon-box" style="color: {{ $layer['color'] }}">
                                    <i class="bi {{ $layer['icon'] }}"></i>
                                </div>
                                <div class="layer-label">{{ $layer['label'] }}</div>
                                <div id="count_{{ $layer['id'] }}" class="count-pill">0</div>
                                <div class="eye-toggle" id="eye_{{ $layer['id'] }}"><i class="bi bi-eye-fill"></i>
                                </div>
                                <div id="spinner_{{ $layer['id'] }}"
                                    class="spinner-border spinner-border-sm text-primary ms-2" role="status"
                                    style="display: none; width: 0.8rem; height: 0.8rem;"></div>
                                <input type="checkbox" class="layer-toggle d-none" value="{{ $layer['id'] }}"
                                    id="check_{{ $layer['id'] }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Custom Loader --}}
            <div id="customMapLoader" class="custom-loader d-none">
                <div class="spinner-border mb-2" style="color: var(--sapphire-primary);" role="status"></div>
                <span class="small fw-bold" style="color: var(--text-main);">Loading Data...</span>
            </div>

            {{-- Ctrl+Scroll Warning Overlay --}}
            <div id="map-scroll-msg" class="map-scroll-overlay d-none">
                <div class="d-flex flex-column align-items-center gap-2">
                    <i class="bi bi-mouse fs-2"></i>
                    <span class="fw-bold fs-6">Use Ctrl + Scroll to zoom</span>
                </div>
            </div>

        </div>
    </div>

    {{-- ==========================================
         35% TERRITORY OVERVIEW CHART AREA
    ========================================== --}}
    <div class="col-lg-4">
        <div class="dash-card h-100 p-4 d-flex flex-column">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0" style="color: var(--text-main);">Territory Overview</h6>
                <i class="bi bi-bar-chart-line-fill fs-5" style="color: var(--sapphire-primary);"></i>
            </div>

            {{-- Chart Data Toggles --}}
            <div class="view-toggle w-100 mb-3 d-flex" id="overall-chart-toggles">
                <button onclick="updateOverallChart('criminal', this)"
                    class="view-toggle-btn active flex-grow-1 justify-content-center"
                    style="font-size: 0.75rem;">Criminal Activities</button>
                <button onclick="updateOverallChart('events', this)"
                    class="view-toggle-btn flex-grow-1 justify-content-center" style="font-size: 0.75rem;">Events &
                    Monitoring</button>
            </div>

            {{-- Chart Canvas --}}
            <div class="flex-grow-1 position-relative" style="min-height: 350px;">
                <canvas id="overall-summary-chart"></canvas>
            </div>

        </div>
    </div>

</div>

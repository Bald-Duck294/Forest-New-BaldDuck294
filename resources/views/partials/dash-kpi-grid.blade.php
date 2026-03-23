<style>
    /* Custom 7-Column Grid for Desktop */
    @media (min-width: 1200px) {
        .kpi-7-grid {
            display: grid !important;
            grid-template-columns: repeat(7, 1fr) !important;
            gap: 0.75rem !important;
            /* Space between cards */
        }

        /* Strip default bootstrap column padding since Grid handles gaps */
        .kpi-7-grid>.col-kpi {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        /* Shrink internal card elements to fit */
        .kpi-7-grid .dash-card {
            padding: 0.75rem !important;
        }

        .kpi-7-grid .kpi-label {
            font-size: 0.55rem !important;
            /* Small enough to fit */
            letter-spacing: 0px !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            /* Adds '...' if text is too long */
            max-width: 90px;
            /* Restrict width so it doesn't push the icon */
        }

        .kpi-7-grid h3 {
            font-size: 1.15rem !important;
            /* Smaller numbers */
        }

        /* Shrink the icon circle */
        .kpi-7-grid .kpi-icon-box {
            width: 32px !important;
            height: 32px !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .kpi-7-grid .kpi-icon-box i {
            font-size: 0.9rem !important;
        }
    }
</style>

<div id="main-kpi-grid" class="row g-3 mb-4 kpi-7-grid">
    @php
        $items = [
            [
                'id' => 'officers',
                'label' => 'On Duty Officers',
                'val' => $kpis['officers'] ?? 0,
                'icon' => 'bi-people',
                'color' => 'badge-soft-primary',
                'trend' => 'Registered Staff',
            ],
            [
                'id' => 'patrol',
                'label' => 'Patrol Status',
                'val' => $kpis['patrols'] ?? 0,
                'icon' => 'bi-map',
                'color' => 'badge-soft-info',
                'trend' => 'Active Reports',
            ],
            [
                'id' => 'criminal',
                'label' => 'Criminal Activity',
                'val' => $kpis['criminal'] ?? 0,
                'icon' => 'bi-exclamation-triangle',
                'color' => 'badge-soft-danger',
                'nav' => 'criminal',
            ],
            [
                'id' => 'events',
                'label' => 'Events & Monitoring',
                'val' => $kpis['events'] ?? 0,
                'icon' => 'bi-binoculars',
                'color' => 'badge-soft-success',
                'nav' => 'events',
            ],
            [
                'id' => 'fire',
                'label' => 'Fire Alerts',
                'val' => $kpis['fire'] ?? 0,
                'icon' => 'bi-fire',
                'color' => 'badge-soft-warning',
                'nav' => 'fire',
            ],
            [
                'id' => 'assets',
                'label' => 'Assets & Tools',
                'val' => $kpis['assets'] ?? 0,
                'icon' => 'bi-shield-check',
                'color' => 'badge-soft-primary',
                'nav' => 'assets',
            ],
            [
                'id' => 'forestry',
                'label' => 'Plantations',
                'val' => $kpis['plantations'] ?? 0,
                'icon' => 'bi-tree',
                'color' => 'badge-soft-success',
                'nav' => 'forestry',
            ],
        ];
    @endphp

    @foreach ($items as $item)
        <div class="col-6 col-md-4 col-kpi">
            <div class="dash-card hover-lift h-100 p-3" onclick="navigateTo('{{ $item['nav'] ?? '' }}')"
                style="cursor: pointer;">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div style="min-width: 0;">
                        <p class="text-muted mb-1 text-uppercase kpi-label" title="{{ $item['label'] }}">
                            {{ $item['label'] }}</p>
                        <h3 class="fw-bold mb-0 text-main" id="kpi-{{ $item['id'] }}">
                            {{ number_format($item['val']) }}
                        </h3>
                    </div>
                    <div class="kpi-icon-box {{ $item['color'] }} flex-shrink-0 ms-1" style="border-radius: 8px;">
                        <i class="{{ $item['icon'] }}"></i>
                    </div>
                </div>
                <div class="mt-auto pt-2">
                    <p class="mb-0 text-truncate"
                        style="font-size: 0.65rem; font-weight: 600; color: var(--text-muted);">
                        @isset($item['nav'])
                            <span style="color: var(--sapphire-primary);">Details <i class="bi bi-arrow-right"></i></span>
                        @else
                            <i class="bi bi-check2-circle me-1" style="color: var(--sapphire-success);"></i>
                            {{ explode(' ', trim($item['trend']))[0] }}
                        @endisset
                    </p>
                </div>
            </div>
        </div>
    @endforeach
</div>

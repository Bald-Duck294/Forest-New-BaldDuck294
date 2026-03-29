<style>
    /* Custom Card Styling to mimic the target design */
    .kpi-card-bs {
        background-color: #ffffff;
        border-radius: 0.75rem;
        padding: 1.25rem;
        border: 1px solid #f1f5f9;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        height: 100%;
        display: block;
    }
    .kpi-card-bs.clickable {
        cursor: pointer;
    }
    .kpi-card-bs.clickable:hover {
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border-color: #6ee7b7;
        transform: translateY(-3px);
    }

    /* Top Right Corner Curve */
    .kpi-bg-curve {
        position: absolute;
        right: 0;
        top: 0;
        width: 5rem;
        height: 5rem;
        border-bottom-left-radius: 100%;
        z-index: 0;
        transition: transform 0.3s ease, background-color 0.3s ease;
    }
    .kpi-card-bs:hover .kpi-bg-curve { transform: scale(1.15); }

    /* Ensure text sits above the background curve */
    .kpi-content { position: relative; z-index: 1; }

    /* Icon Container */
    .kpi-icon-box {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        font-size: 1.1rem;
    }

    /* Exact Color Themes from the Image */
    .theme-blue .kpi-bg-curve { background-color: #eff6ff; }
    .theme-blue .kpi-icon-box { background-color: #dbeafe; color: #2563eb; }

    .theme-indigo .kpi-bg-curve { background-color: #eef2ff; }
    .theme-indigo .kpi-icon-box { background-color: #e0e7ff; color: #4f46e5; }

    .theme-rose .kpi-bg-curve { background-color: #fff1f2; }
    .kpi-card-bs:hover .theme-rose .kpi-bg-curve { background-color: #ffe4e6; }
    .theme-rose .kpi-icon-box { background-color: #ffe4e6; color: #e11d48; }

    .theme-amber .kpi-bg-curve { background-color: #fffbeb; }
    .kpi-card-bs:hover .theme-amber .kpi-bg-curve { background-color: #fef3c7; }
    .theme-amber .kpi-icon-box { background-color: #fef3c7; color: #d97706; }

    .theme-orange .kpi-bg-curve { background-color: #fff7ed; }
    .kpi-card-bs:hover .theme-orange .kpi-bg-curve { background-color: #ffedd5; }
    .theme-orange .kpi-icon-box { background-color: #ffedd5; color: #ea580c; }

    .theme-teal .kpi-bg-curve { background-color: #f0fdfa; }
    .kpi-card-bs:hover .theme-teal .kpi-bg-curve { background-color: #ccfbf1; }
    .theme-teal .kpi-icon-box { background-color: #ccfbf1; color: #0d9488; }
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
                'url' => url('/reports/detailed?category=onduty'), // Redirect URL added
            ],
            // Find this block in your $items array:
            [
                'id' => 'patrol',
                'label' => 'Patrol Status',
                'val' => $kpis['patrols'] ?? 0,
                'icon' => 'bi-map',
                'color' => 'badge-soft-info',
                'url' => url('/patrolling'),
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

<div id="main-kpi-grid" class="row g-3 mb-5">
    @foreach ($items as $item)
        <div class="col-6 col-md-4 col-kpi">
            <div class="dash-card hover-lift h-100 p-3"
                @if (isset($item['url'])) onclick="window.location.href='{{ $item['url'] }}'" style="cursor: pointer;"
                @elseif(isset($item['nav']))
                    onclick="navigateTo('{{ $item['nav'] }}')" style="cursor: pointer;" @endif>

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

                        @if (isset($item['url']))
                            <span style="color: var(--sapphire-primary);">View List <i
                                    class="bi bi-arrow-right ms-1"></i></span>
                        @elseif(isset($item['nav']))
                            <span style="color: var(--sapphire-primary);">View Analytics <i
                                    class="bi bi-arrow-right ms-1"></i></span>
                        @else
                            <i class="bi bi-check2-circle me-1" style="color: var(--sapphire-success);"></i>
                            {{ explode(' ', trim($item['trend']))[0] }}
                        @endif

                    </p>
                </div>

            </div>
        </div>
    @endforeach
</div>

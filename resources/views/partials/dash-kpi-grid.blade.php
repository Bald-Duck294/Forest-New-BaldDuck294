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

@php
    $items = [
        [
            'id' => 'officers', 'label' => 'On Duty Officers', 'val' => $kpis['officers'] ?? 412, 'nav' => null,
            'theme' => 'theme-blue', 'icon' => 'bi-people',
            'trend_text' => '98% Attendance', 'trend_color' => '#059669', 'trend_icon' => 'bi-graph-up-arrow',
        ],
        [
            'id' => 'patrol', 'label' => 'Patrol Status', 'val' => $kpis['patrols'] ?? 1204, 'nav' => null,
            'theme' => 'theme-indigo', 'icon' => 'bi-map',
            'trend_text' => 'Total beats covered this period', 'trend_color' => '#64748b', 'trend_icon' => null,
        ],
        [
            'id' => 'criminal', 'label' => 'Criminal Activity', 'val' => $kpis['criminal'] ?? 99, 'nav' => 'criminal',
            'theme' => 'theme-rose', 'icon' => 'bi-exclamation-triangle',
            'trend_text' => 'Click to view details', 'trend_color' => '#f43f5e', 'trend_icon' => 'bi-arrow-right',
        ],
        [
            'id' => 'events', 'label' => 'Events & Monitoring', 'val' => $kpis['events'] ?? 335, 'nav' => 'events',
            'theme' => 'theme-amber', 'icon' => 'bi-binoculars',
            'trend_text' => 'Click to view details', 'trend_color' => '#f59e0b', 'trend_icon' => 'bi-arrow-right',
        ],
        [
            'id' => 'fire', 'label' => 'Fire Alerts', 'val' => $kpis['fire'] ?? 21, 'nav' => 'fire',
            'theme' => 'theme-orange', 'icon' => 'bi-fire',
            'trend_text' => 'Click to view details', 'trend_color' => '#f97316', 'trend_icon' => 'bi-arrow-right',
        ],
        [
            'id' => 'assets', 'label' => 'Assets & Tools', 'val' => $kpis['assets'] ?? 1182, 'nav' => 'assets',
            'theme' => 'theme-teal', 'icon' => 'bi-shield-check',
            'trend_text' => 'Click to view details', 'trend_color' => '#14b8a6', 'trend_icon' => 'bi-arrow-right',
        ]
    ];
@endphp

<div id="main-kpi-grid" class="row g-3 mb-5">
    @foreach ($items as $item)
        <div class="col-12 col-sm-6 col-md-4 col-lg-2">
            <div class="kpi-card-bs {{ $item['theme'] }} @isset($item['nav']) clickable @endisset"
                 @isset($item['nav']) onclick="navigateTo('{{ $item['nav'] }}')" @endisset>

                <div class="kpi-bg-curve"></div>

                <div class="kpi-content">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <p class="mb-1 text-muted" style="font-size: 0.75rem; font-weight: 500;">{{ $item['label'] }}</p>
                            <h3 class="mb-0 fw-bold text-dark" style="font-size: 1.5rem;" id="kpi-{{ $item['id'] }}">
                                {{ number_format($item['val']) }}
                            </h3>
                        </div>
                        <div class="kpi-icon-box">
                            <i class="bi {{ $item['icon'] }}"></i>
                        </div>
                    </div>

                    <p class="mb-0 d-flex align-items-center gap-1" style="font-size: 0.65rem; font-weight: 500; color: {{ $item['trend_color'] }};">
                        @if($item['trend_icon'] == 'bi-graph-up-arrow')
                            <i class="bi bi-graph-up-arrow me-1"></i> {{ $item['trend_text'] }}
                        @elseif($item['trend_icon'] == 'bi-arrow-right')
                            {{ $item['trend_text'] }} <i class="bi bi-arrow-right ms-1"></i>
                        @else
                            {{ $item['trend_text'] }}
                        @endif
                    </p>
                </div>

            </div>
        </div>
    @endforeach
</div>

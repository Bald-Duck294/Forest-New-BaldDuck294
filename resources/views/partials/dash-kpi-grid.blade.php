<style>
    /* =====================================================
       RESPONSIVE KPI GRID — auto-fits equal columns at
       every breakpoint, 1 → 2 → 3 → 4 → 7 columns
    ===================================================== */

    /* Import Inter font */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    .kpi-grid-wrap {
        /* CSS Grid: fills available width, min card = 150px, max = 1fr */
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(min(150px, 100%), 1fr));
        gap: clamp(10px, 1.2vw, 18px);
        width: 100%;
    }

    /* On screens wide enough to comfortably show all 7 */
    @media (min-width: 1100px) {
        .kpi-grid-wrap {
            grid-template-columns: repeat(7, 1fr);
        }
    }

    /* 4 columns on tablet landscape */
    @media (min-width: 768px) and (max-width: 1099px) {
        .kpi-grid-wrap {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    /* 2 columns on tablet portrait / large phone */
    @media (min-width: 480px) and (max-width: 767px) {
        .kpi-grid-wrap {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    /* 1 column on small phones */
    @media (max-width: 479px) {
        .kpi-grid-wrap {
            grid-template-columns: 1fr;
        }
    }

    /* ── Card Base ────────────────────────────────────── */
    .kpi-card-bs {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        background-color: var(--bg-card, #ffffff);
        border-radius: 12px;

        /* Fluid padding: grows slightly on wider screens */
        padding: clamp(0.85rem, 1.5vw, 1.25rem) clamp(0.75rem, 1.2vw, 1rem);

        border: 1px solid var(--border-color, rgba(0, 0, 0, 0.08));
        position: relative;
        overflow: hidden;
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1),
                    box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1);

        /* Equal height within each row */
        height: 100%;
        min-height: clamp(110px, 12vw, 140px);

        display: flex;
        flex-direction: column;
        justify-content: space-between;

        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }

    .kpi-card-bs.clickable {
        cursor: pointer;
    }

    .kpi-card-bs.clickable:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 24px -10px rgba(0, 0, 0, 0.14);
    }

    /* ── Decorative corner curve ──────────────────────── */
    .kpi-bg-curve {
        position: absolute;
        right: 0;
        top: 0;
        width: 5rem;
        height: 5rem;
        border-bottom-left-radius: 100%;
        z-index: 0;
        transition: transform 0.4s ease;
        pointer-events: none;
    }

    .kpi-card-bs:hover .kpi-bg-curve {
        transform: scale(1.18);
    }

    /* ── Content layer (sits above curve) ─────────────── */
    .kpi-content {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
    }

    /* ── Icon box ─────────────────────────────────────── */
    .kpi-icon-box {
        width: clamp(32px, 3vw, 40px);
        height: clamp(32px, 3vw, 40px);
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: clamp(1rem, 1.15vw, 1.2rem);
    }

    /* ── Value number ─────────────────────────────────── */
    .kpi-value {
        font-size: clamp(1.3rem, 2.2vw, 1.7rem);
        font-weight: 700;
        color: var(--text-main, #1a1a2e);
        letter-spacing: -0.5px;
        line-height: 1.1;
        margin: 0;
    }

    /* ── Label ────────────────────────────────────────── */
    .kpi-label {
        font-size: clamp(0.65rem, 0.75vw, 0.78rem);
        font-weight: 500;
        letter-spacing: 0.2px;
        white-space: normal;          /* wrap instead of truncate on tiny cards */
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        margin-bottom: 0.25rem;
        line-height: 1.3;
    }

    /* ── Trend line ───────────────────────────────────── */
    .kpi-trend {
        font-size: clamp(0.62rem, 0.72vw, 0.72rem);
        font-weight: 600;
        margin: 0;
        margin-top: 0.65rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ── Theme colour tokens ──────────────────────────── */
    .theme-blue   .kpi-bg-curve { background-color: rgba(59,  130, 246, 0.06); }
    .theme-blue   .kpi-icon-box { background-color: rgba(59,  130, 246, 0.14); color: #3b82f6; }

    .theme-indigo .kpi-bg-curve { background-color: rgba(99,  102, 241, 0.06); }
    .theme-indigo .kpi-icon-box { background-color: rgba(99,  102, 241, 0.14); color: #6366f1; }

    .theme-rose   .kpi-bg-curve { background-color: rgba(244,  63,  94, 0.06); }
    .theme-rose   .kpi-icon-box { background-color: rgba(244,  63,  94, 0.14); color: #f43f5e; }

    .theme-amber  .kpi-bg-curve { background-color: rgba(245, 158,  11, 0.06); }
    .theme-amber  .kpi-icon-box { background-color: rgba(245, 158,  11, 0.14); color: #f59e0b; }

    .theme-orange .kpi-bg-curve { background-color: rgba(249, 115,  22, 0.06); }
    .theme-orange .kpi-icon-box { background-color: rgba(249, 115,  22, 0.14); color: #f97316; }

    .theme-teal   .kpi-bg-curve { background-color: rgba( 20, 184, 166, 0.06); }
    .theme-teal   .kpi-icon-box { background-color: rgba( 20, 184, 166, 0.14); color: #14b8a6; }

    .theme-emerald .kpi-bg-curve { background-color: rgba( 16, 185, 129, 0.06); }
    .theme-emerald .kpi-icon-box { background-color: rgba( 16, 185, 129, 0.14); color: #10b981; }
</style>

@php
    $items = [
        [
            'id'          => 'officers',
            'label'       => 'On Duty Officers',
            'val'         => $kpis['officers'] ?? 0,
            'url'         => url('/reports/detailed?category=onduty'),
            'theme'       => 'theme-blue',
            'icon'        => 'bi-people',
            'trend_text'  => ($kpis['attendanceRate'] ?? 0) . '% (' . ($kpis['officers'] ?? 0) . '/' . ($kpis['totalOfficers'] ?? 0) . ')',
            'trend_color' => '#3b82f6',
            'trend_icon'  => 'bi-graph-up-arrow',
        ],
        [
            'id'          => 'patrol',
            'label'       => 'Patrol Status',
            'val'         => $kpis['patrols'] ?? 0,
            'url'         => url('/patrolling'),
            'theme'       => 'theme-indigo',
            'icon'        => 'bi-map',
            'trend_text'  => 'View List',
            'trend_color' => '#6366f1',
            'trend_icon'  => 'bi-arrow-right',
        ],
        [
            'id'          => 'criminal',
            'label'       => 'Criminal Activity',
            'val'         => $kpis['criminal'] ?? 0,
            'nav'         => 'criminal',
            'theme'       => 'theme-rose',
            'icon'        => 'bi-exclamation-triangle',
            'trend_text'  => 'View Analytics',
            'trend_color' => '#f43f5e',
            'trend_icon'  => 'bi-arrow-right',
        ],
        [
            'id'          => 'events',
            'label'       => 'Events & Monitor',
            'val'         => $kpis['events'] ?? 0,
            'nav'         => 'events',
            'theme'       => 'theme-amber',
            'icon'        => 'bi-binoculars',
            'trend_text'  => 'View Analytics',
            'trend_color' => '#f59e0b',
            'trend_icon'  => 'bi-arrow-right',
        ],
        [
            'id'          => 'fire',
            'label'       => 'Fire Alerts',
            'val'         => $kpis['fire'] ?? 0,
            'nav'         => 'fire',
            'theme'       => 'theme-orange',
            'icon'        => 'bi-fire',
            'trend_text'  => 'View Analytics',
            'trend_color' => '#f97316',
            'trend_icon'  => 'bi-arrow-right',
        ],
        [
            'id'          => 'assets',
            'label'       => 'Assets & Tools',
            'val'         => $kpis['assets'] ?? 0,
            'nav'         => 'assets',
            'theme'       => 'theme-teal',
            'icon'        => 'bi-shield-check',
            'trend_text'  => 'View Analytics',
            'trend_color' => '#14b8a6',
            'trend_icon'  => 'bi-arrow-right',
        ],
        [
            'id'          => 'forestry',
            'label'       => 'Plantations',
            'val'         => $kpis['plantations'] ?? 0,
            'nav'         => 'forestry',
            'theme'       => 'theme-emerald',
            'icon'        => 'bi-tree',
            'trend_text'  => 'View Analytics',
            'trend_color' => '#10b981',
            'trend_icon'  => 'bi-arrow-right',
        ],
    ];
@endphp

<div id="main-kpi-grid" class="kpi-grid-wrap mb-4">
    @foreach ($items as $item)
        <div class="kpi-card-bs {{ $item['theme'] }}{{ (isset($item['url']) || isset($item['nav'])) ? ' clickable' : '' }}"
            @if (isset($item['url']))
                onclick="window.location.href='{{ $item['url'] }}'"
            @elseif (isset($item['nav']))
                onclick="navigateTo('{{ $item['nav'] }}')"
            @endif>

            {{-- Corner decoration --}}
            <div class="kpi-bg-curve"></div>

            <div class="kpi-content">

                {{-- Top row: label + icon --}}
                <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">

                    <div style="min-width: 0; flex: 1; padding-right: 8px;">
                        <p class="kpi-label text-muted mb-1" title="{{ $item['label'] }}">
                            {{ $item['label'] }}
                        </p>
                        <p class="kpi-value" id="kpi-{{ $item['id'] }}">
                            {{ number_format($item['val']) }}
                        </p>
                    </div>

                    <div class="kpi-icon-box">
                        <i class="bi {{ $item['icon'] }}"></i>
                    </div>
                </div>

                {{-- Trend text --}}
                <p class="kpi-trend" style="color: {{ $item['trend_color'] }};" title="{{ $item['trend_text'] }}">
                    {{ $item['trend_text'] }} <i class="bi {{ $item['trend_icon'] }} ms-1"></i>
                </p>

            </div>
        </div>
    @endforeach
</div>

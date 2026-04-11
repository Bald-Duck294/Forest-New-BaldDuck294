<style>
    /* =========================================
       7-COLUMN EXACT FIT GRID & SAAS KPI CARDS
    ========================================= */
    @media (min-width: 1200px) {
        .kpi-7-row {
            display: grid !important;
            grid-template-columns: repeat(7, 1fr) !important;
            gap: 14px !important;
        }

        .kpi-7-row>.col-kpi {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
    }

    @media (max-width: 1199px) {
        .kpi-7-row {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 12px;
            gap: 14px;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .kpi-7-row::-webkit-scrollbar {
            display: none;
        }

        .kpi-7-row>.col-kpi {
            flex: 0 0 210px;
        }
    }

    .kpi-card-bs {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif !important;
        background-color: var(--bg-card);
        border-radius: 12px;
        padding: 1.25rem 1rem;
        border: 1px solid var(--border-color);
        position: relative;
        overflow: hidden;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        min-height: 125px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    .kpi-card-bs.clickable {
        cursor: pointer;
    }

    .kpi-card-bs.clickable:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px -8px rgba(0, 0, 0, 0.15);
        border-color: var(--border-color);
    }

    .kpi-bg-curve {
        position: absolute;
        right: 0;
        top: 0;
        width: 5.5rem;
        height: 5.5rem;
        border-bottom-left-radius: 100%;
        z-index: 0;
        transition: transform 0.4s ease;
    }

    .kpi-card-bs:hover .kpi-bg-curve {
        transform: scale(1.15);
    }

    .kpi-content {
        position: relative;
        z-index: 1;
    }

    .kpi-icon-box {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 1.15rem;
    }

    /* Exact Color Themes */
    .theme-blue .kpi-bg-curve {
        background-color: rgba(59, 130, 246, 0.05);
    }

    .theme-blue .kpi-icon-box {
        background-color: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
    }

    .theme-indigo .kpi-bg-curve {
        background-color: rgba(99, 102, 241, 0.05);
    }

    .theme-indigo .kpi-icon-box {
        background-color: rgba(99, 102, 241, 0.15);
        color: #6366f1;
    }

    .theme-rose .kpi-bg-curve {
        background-color: rgba(244, 63, 94, 0.05);
    }

    .theme-rose .kpi-icon-box {
        background-color: rgba(244, 63, 94, 0.15);
        color: #f43f5e;
    }

    .theme-amber .kpi-bg-curve {
        background-color: rgba(245, 158, 11, 0.05);
    }

    .theme-amber .kpi-icon-box {
        background-color: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
    }

    .theme-orange .kpi-bg-curve {
        background-color: rgba(249, 115, 22, 0.05);
    }

    .theme-orange .kpi-icon-box {
        background-color: rgba(249, 115, 22, 0.15);
        color: #f97316;
    }

    .theme-teal .kpi-bg-curve {
        background-color: rgba(20, 184, 166, 0.05);
    }

    .theme-teal .kpi-icon-box {
        background-color: rgba(20, 184, 166, 0.15);
        color: #14b8a6;
    }

    .theme-emerald .kpi-bg-curve {
        background-color: rgba(16, 185, 129, 0.05);
    }

    .theme-emerald .kpi-icon-box {
        background-color: rgba(16, 185, 129, 0.15);
        color: #10b981;
    }

    /* 🔥 Disabled KPI State */
    .kpi-card-disabled {
        background-color: var(--bg-body) !important;
        border: 1px dashed var(--border-color) !important;
        box-shadow: none !important;
        cursor: not-allowed !important;
        opacity: 0.85;
        transition: all 0.2s ease;
    }

    .kpi-card-disabled .kpi-icon-box {
        background: rgba(100, 116, 139, 0.1) !important;
        color: var(--text-muted) !important;
        border: 1px solid var(--border-color);
    }

    .kpi-card-disabled h3,
    .kpi-card-disabled h6,
    .kpi-card-disabled .kpi-bg-curve {
        display: none !important;
    }
</style>

@php
    // 🔥 Check if the current filter makes "On Duty" invalid
    $currentFilter = request('date_filter', 'month');
    $isDutyDisabled = in_array($currentFilter, ['overall', 'week', 'custom']);

    $items = [
        [
            'id' => 'officers',
            'label' => 'On Duty Today',
            'val' => $kpis['officers'] ?? 0,
            // 🔥 UNIQUE LOGIC: Uses the custom smart function for the list
            'custom_click' => $isDutyDisabled ? null : "goToDetailedList('onduty')",
            'theme' => 'theme-blue',
            'icon' => 'bi-people',
            'trend_text' => $isDutyDisabled
                ? "Select 'Today' or 'Month'"
                : ($kpis['attendanceRate'] ?? 0) .
                    '% (' .
                    ($kpis['officers'] ?? 0) .
                    '/' .
                    ($kpis['totalOfficers'] ?? 0) .
                    ')',
            'trend_color' => $isDutyDisabled ? 'var(--text-muted)' : '#3b82f6',
            'trend_icon' => $isDutyDisabled ? 'bi-info-circle' : 'bi-graph-up-arrow',
            'disabled' => $isDutyDisabled,
        ],
        [
            'id' => 'patrol',
            'label' => 'Patrol Status',
            'val' => $kpis['patrols'] ?? 0,
            'url' => url('/patrolling'),
            'theme' => 'theme-indigo',
            'icon' => 'bi-map',
            'trend_text' => 'View List',
            'trend_color' => '#6366f1',
            'trend_icon' => 'bi-arrow-right',
        ],
        [
            'id' => 'criminal',
            'label' => 'Forest Crimes',
            'val' => $kpis['criminal'] ?? 0,
            'nav' => 'criminal', // 🔥 Restored: Triggers navigateTo() Analytics scrolling
            'theme' => 'theme-rose',
            'icon' => 'bi-exclamation-triangle',
            'trend_text' => 'View Analytics',
            'trend_color' => '#f43f5e',
            'trend_icon' => 'bi-arrow-right',
        ],
        [
            'id' => 'events',
            'label' => 'Crime / Events',
            'val' => $kpis['events'] ?? 0,
            'nav' => 'events', // 🔥 Restored: Triggers navigateTo() Analytics scrolling
            'theme' => 'theme-amber',
            'icon' => 'bi-binoculars',
            'trend_text' => 'View Analytics',
            'trend_color' => '#f59e0b',
            'trend_icon' => 'bi-arrow-right',
        ],
        [
            'id' => 'fire',
            'label' => 'Fire Alerts',
            'val' => $kpis['fire'] ?? 0,
            'nav' => 'fire', // 🔥 Restored: Triggers navigateTo() Analytics scrolling
            'theme' => 'theme-orange',
            'icon' => 'bi-fire',
            'trend_text' => 'View Analytics',
            'trend_color' => '#f97316',
            'trend_icon' => 'bi-arrow-right',
        ],
        [
            'id' => 'assets',
            'label' => 'Assets & Tools',
            'val' => $kpis['assets'] ?? 0,
            'nav' => 'assets', // 🔥 Restored: Triggers navigateTo() Analytics scrolling
            'theme' => 'theme-teal',
            'icon' => 'bi-shield-check',
            'trend_text' => 'View Analytics',
            'trend_color' => '#14b8a6',
            'trend_icon' => 'bi-arrow-right',
        ],
        [
            'id' => 'forestry',
            'label' => 'Plantations',
            'val' => $kpis['plantations'] ?? 0,
            'nav' => 'plantations', // 🔥 Restored: Triggers navigateTo() Analytics scrolling
            'theme' => 'theme-emerald',
            'icon' => 'bi-tree',
            'trend_text' => 'View Analytics',
            'trend_color' => '#10b981',
            'trend_icon' => 'bi-arrow-right',
        ],
    ];
@endphp

<div id="main-kpi-grid" class="kpi-7-row mb-4">
    @foreach ($items as $item)
        @php
            $isDisabled = isset($item['disabled']) && $item['disabled'];
        @endphp

        <div class="col-kpi">
            {{-- 🔥 FIXED: Perfectly isolates custom_click (List), url (Redirect), and nav (Chart Scrolling) --}}
            <div class="kpi-card-bs {{ $item['theme'] }} {{ $isDisabled ? 'kpi-card-disabled' : '' }} @if (!$isDisabled && (isset($item['url']) || isset($item['nav']) || isset($item['custom_click']))) clickable @endif"
                @if (!$isDisabled && isset($item['custom_click'])) onclick="{!! $item['custom_click'] !!}" 
                @elseif (!$isDisabled && isset($item['url'])) 
                    onclick="window.location.href='{{ $item['url'] }}'" 
                @elseif(!$isDisabled && isset($item['nav'])) 
                    onclick="navigateTo('{{ $item['nav'] }}')" @endif>

                <div class="kpi-bg-curve"></div>

                <div class="kpi-content h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start w-100">

                        <div style="min-width: 0; padding-right: 8px; flex-grow: 1;">
                            <p class="mb-1 text-truncate {{ $isDisabled ? 'text-muted' : 'text-muted' }}"
                                style="font-size: 0.75rem; font-weight: 500; letter-spacing: 0.2px;"
                                title="{{ $item['label'] }}">
                                {{ $item['label'] }}
                            </p>

                            <h3 class="mb-0 fw-bold"
                                style="font-size: 1.6rem; color: {{ $isDisabled ? 'var(--text-muted)' : 'var(--text-main)' }}; letter-spacing: -0.5px;"
                                id="kpi-{{ $item['id'] }}">
                                {{ $isDisabled ? 'N/A' : number_format($item['val']) }}
                            </h3>
                        </div>

                        <div class="kpi-icon-box flex-shrink-0">
                            <i class="bi {{ $item['icon'] }}"></i>
                        </div>
                    </div>

                    <p class="mb-0 mt-3 text-truncate w-100 {{ $isDisabled ? 'fst-italic' : '' }}"
                        style="font-size: 0.70rem; font-weight: 600; color: {{ $item['trend_color'] }};"
                        title="{{ $item['trend_text'] }}">
                        {{ $item['trend_text'] }} <i class="bi {{ $item['trend_icon'] }} ms-1"></i>
                    </p>
                </div>

            </div>
        </div>
    @endforeach
</div>

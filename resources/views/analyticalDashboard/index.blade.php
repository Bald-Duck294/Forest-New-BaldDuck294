<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    .ana-font { font-family: 'Inter', sans-serif; }

    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    /* Main Tabs */
  /* Main Tabs */
    .ana-main-tabs-container {
        display: flex;
        gap: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 1.5rem;

        /* 🔥 THE FIX: Force horizontal scrolling on mobile */
        overflow-x: auto !important;
        flex-wrap: nowrap !important;
        -webkit-overflow-scrolling: touch; /* Smooth swiping on phones */

        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }

    .ana-main-tab {
        padding-bottom: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s;
        border-bottom: 2px solid transparent;
        background: none;
        border-top: none;
        border-left: none;
        border-right: none;
        cursor: pointer;

        /* 🔥 THE FIX: Never wrap text and never shrink the tab */
        white-space: nowrap !important;
        flex-shrink: 0 !important;
    }
    .ana-main-tab.active { color: #059669; border-bottom-color: #059669; font-weight: 700; }
    .ana-main-tab:not(.active) { color: #64748b; font-weight: 600; }
    .ana-main-tab:not(.active):hover { color: #334155; }

    /* Header */
    .ana-header-title { font-size: 1.125rem; font-weight: 700; color: #1e293b; letter-spacing: -0.025em; margin-bottom: 0.25rem; line-height: 1.2; }
    .ana-header-sub { font-size: 11px; color: #64748b; margin-bottom: 0; }

    /* Tiles */
    .ana-tile-container { display: flex; gap: 0.75rem; overflow-x: auto; margin-bottom: 1.5rem; padding-bottom: 0.5rem; }
    .ana-tile { flex-shrink: 0; min-width: 160px; padding: 0.75rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; cursor: pointer; transition: all 0.2s; background: #fff; }
    .ana-tile.active { border-color: #10b981; background-color: rgba(236, 253, 245, 0.4); box-shadow: 0 0 0 1px #10b981; }
    .ana-tile:not(.active):hover { border-color: #cbd5e1; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
    .ana-tile-header { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.625rem; }
    .ana-tile-icon { padding: 0.25rem; border-radius: 0.375rem; display: flex; align-items: center; justify-content: center; }
    .ana-tile.active .ana-tile-icon { color: #059669; }
    .ana-tile:not(.active) .ana-tile-icon { color: #94a3b8; }
    .ana-tile-label { font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
    .ana-tile-value { font-size: 1.5rem; font-weight: 800; color: #1e293b; margin-left: 0.25rem; margin-bottom: 0; line-height: 1; }

    /* Charts Card */
    .ana-chart-card { background: #fff; padding: 1rem; border-radius: 0.75rem; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); border: 1px solid #f1f5f9; display: flex; flex-direction: column; height: 340px; }
    .ana-chart-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; gap: 0.5rem; }
    .ana-chart-title { font-weight: 600; color: #1e293b; font-size: 0.75rem; line-height: 1.25; margin-bottom: 0; }
    .ana-chart-pill { font-size: 9px; font-weight: 700; padding: 0.125rem 0.375rem; border-radius: 0.25rem; background: #ecfdf5; color: #047857; white-space: nowrap; border: 1px solid #d1fae5; }
    .ana-chart-toggles { display: flex; gap: 0.25rem; margin-bottom: 0.5rem; background: #f1f5f9; padding: 0.25rem; border-radius: 0.5rem; width: max-content; overflow-x: auto; }
    .ana-chart-toggle-btn { padding: 0.125rem 0.5rem; font-size: 10px; border-radius: 0.25rem; font-weight: 500; transition: all 0.2s; border: none; cursor: pointer; background: transparent; color: #64748b; }
    .ana-chart-toggle-btn.active { background: #fff; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); color: #047857; }
    .ana-chart-toggle-btn:not(.active):hover { color: #1e293b; }
    .ana-chart-body { position: relative; flex-grow: 1; width: 100%; margin-top: auto; }
</style>
<div id="analytical-container" class="d-none ana-font">

    <div class="ana-main-tabs-container hide-scrollbar" id="main-tabs-nav">
    </div>

   <div class="d-flex flex-column flex-lg-row align-items-start justify-content-between w-100">

        <div id="sub-tabs-container" class="ana-tile-container hide-scrollbar flex-grow-1 w-100" style="min-width: 0;">
        </div>

        <div id="kpi-action-button" class="ms-lg-3 mt-2 mt-lg-0 flex-shrink-0"></div>

    </div>

    <div id="charts-grid" class="row g-3">
    </div>

</div>

<style>
    /* Custom Inputs */
    .custom-input {
        background-color: var(--bg-body);
        color: var(--text-main);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 0.85rem;
        outline: none;
        transition: all 0.2s ease;
    }

    .custom-input:focus {
        border-color: var(--sapphire-primary);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    html[data-bs-theme="dark"] .custom-input {
        color-scheme: dark;
    }

    /* Cards */
    .dash-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        transition: all 0.25s ease;
    }

    .hover-lift {
        cursor: pointer;
    }

    .hover-lift:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
        border-color: var(--sapphire-primary);
    }

    /* View Toggles */
    .view-toggle {
        display: inline-flex;
        background: var(--bg-body);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 4px;
    }

    .view-toggle-btn {
        background: transparent;
        color: var(--text-muted);
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .view-toggle-btn:hover {
        color: var(--text-main);
    }

    .view-toggle-btn.active {
        background: var(--sapphire-primary);
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Buttons */
    .btn-sapphire {
        background-color: var(--sapphire-primary);
        color: #ffffff;
        border: none;
        font-weight: 600;
        padding: 6px 16px;
        border-radius: 8px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-sapphire:hover {
        opacity: 0.9;
        color: #ffffff;
        transform: translateY(-1px);
    }

    /* Soft Badges */
    .badge-soft {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .badge-soft-primary {
        background: rgba(59, 130, 246, 0.15);
        color: var(--sapphire-primary);
    }

    .badge-soft-success {
        background: rgba(16, 185, 129, 0.15);
        color: var(--sapphire-success);
    }

    .badge-soft-warning {
        background: rgba(245, 158, 11, 0.15);
        color: var(--sapphire-warning);
    }

    .badge-soft-danger {
        background: rgba(239, 68, 68, 0.15);
        color: var(--sapphire-danger);
    }

    .badge-soft-info {
        background: rgba(6, 182, 212, 0.15);
        color: #06b6d4;
    }

    /* KPI Icon Box */
    .kpi-icon-box {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 1.2rem;
    }

    /* Analytical Tabs */
    .main-tab-link {
        color: var(--text-muted);
        font-weight: 600;
        border-bottom: 3px solid transparent;
        padding-bottom: 12px;
        text-decoration: none;
        transition: 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .main-tab-link:hover {
        color: var(--text-main);
    }

    .main-tab-link.active {
        color: var(--sapphire-primary);
        border-bottom-color: var(--sapphire-primary);
    }

    /* Analytical Breakdown Tiles */
    .breakdown-tile {
        min-width: 180px;
        padding: 16px;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        transition: all 0.2s;
        cursor: pointer;
    }

    .breakdown-tile:hover {
        border-color: var(--sapphire-primary);
        transform: translateY(-2px);
    }

    .breakdown-tile.active {
        border-color: var(--sapphire-primary);
        background: var(--bg-body);
        box-shadow: 0 0 0 1px var(--sapphire-primary);
    }

    .breakdown-tile h2 {
        font-size: 1.8rem;
        font-weight: 800;
        margin-top: 8px;
        margin-bottom: 0;
        color: var(--text-main);
    }

    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .text-main {
        color: var(--text-main);
    }

    /* Loader */
    .custom-loader-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(var(--bg-body-rgb, 255, 255, 255), 0.8);
        backdrop-filter: blur(6px);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }

    /* =========================================
   MAP OVERLAY & GLASS PANEL STYLES
========================================= */
    .map-filter-sidebar {
        position: absolute;
        top: 15px;
        right: 15px;
        width: 320px;
        max-height: calc(100% - 30px);
        z-index: 1000;
        display: flex;
        flex-direction: column;
        background: var(--bg-card);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        transform: translateX(calc(100% + 40px));
    }

    .map-filter-sidebar.open {
        transform: translateX(0);
    }

    .drawer-toggle {
        position: absolute;
        top: 30%;
        right: 0;
        transform: translateY(-50%);
        z-index: 1001;
        width: 44px;
        height: 52px;
        background: var(--sapphire-primary);
        color: #ffffff;
        border: none;
        border-radius: 12px 0 0 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        cursor: pointer;
        box-shadow: -4px 0 15px rgba(0, 0, 0, 0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .drawer-toggle span {
        font-size: 0.55rem;
        font-weight: 800;
        text-transform: uppercase;
        margin-top: 2px;
    }

    .drawer-toggle.active {
        right: 335px;
        background: var(--sapphire-danger);
        border-radius: 12px;
    }

    .sidebar-header {
        padding: 16px 20px 10px;
        border-bottom: 1px dashed var(--border-color);
    }

    .sidebar-content {
        padding: 15px;
        overflow-y: auto;
        flex: 1;
    }

    .layer-item {
        background: var(--bg-body);
        border-radius: 12px;
        margin-bottom: 8px;
        padding: 8px 12px;
        border: 1px solid var(--border-color);
        transition: all 0.2s ease-out;
        cursor: pointer;
        display: flex;
        align-items: center;
    }

    .layer-item:hover {
        transform: translateY(-2px);
        border-color: var(--sapphire-primary);
    }

    .layer-item.active {
        background: var(--bg-card);
        border-color: var(--sapphire-primary);
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }

    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 12px;
    }

    .layer-icon-box {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        margin-right: 12px;
        background: rgba(0, 0, 0, 0.03);
        border-radius: 8px;
    }

    .layer-label {
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--text-main);
        flex: 1;
    }

    .count-pill {
        background: var(--table-hover);
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--text-muted);
        margin: 0 10px;
        min-width: 32px;
        text-align: center;
    }

    .eye-toggle {
        font-size: 1.1rem;
        color: var(--border-color);
        transition: color 0.2s;
    }

    .layer-item.active .eye-toggle {
        color: var(--sapphire-success);
    }

    /* Premium Map Popups */
    .premium-popup .popup-header {
        padding: 12px 16px;
        border-radius: 12px 12px 0 0;
        color: #ffffff;
    }

    .premium-popup .popup-layer-badge {
        font-size: 0.6rem;
        background: rgba(255, 255, 255, 0.2);
        padding: 4px 8px;
        border-radius: 6px;
        text-transform: uppercase;
        font-weight: 700;
        margin-bottom: 4px;
        display: inline-block;
    }

    .premium-popup .popup-title {
        font-size: 1rem;
        font-weight: 800;
        margin: 0;
    }

    .premium-popup .popup-body {
        padding: 12px 16px;
        max-height: 200px;
        overflow-y: auto;
        background-color: var(--bg-card);
        color: var(--text-main);
        border-radius: 0 0 12px 12px;
    }

    .premium-popup .popup-table {
        width: 100%;
        font-size: 0.8rem;
    }

    .premium-popup .popup-table tr {
        border-bottom: 1px dashed var(--border-color);
    }

    .premium-popup .popup-table tr:last-child {
        border-bottom: none;
    }

    .premium-popup .popup-table td {
        padding: 6px 0;
        color: var(--text-main);
        font-weight: 600;
    }

    .premium-popup .popup-table .popup-label {
        color: var(--text-muted);
        font-weight: 700;
        width: 40%;
        font-size: 0.7rem;
        text-transform: uppercase;
    }

    .leaflet-popup-content-wrapper,
    .gm-style-iw {
        background-color: transparent !important;
        box-shadow: none !important;
        padding: 0 !important;
        border-radius: 12px !important;
    }

    .gm-style-iw-d {
        overflow: hidden !important;
    }

    .gm-style-iw button.gm-ui-hover-effect {
        filter: var(--bs-theme)=='dark' ? 'invert(1)': 'none';
        top: 5px !important;
        right: 5px !important;
    }

    /* Scroll Overlay */
    .map-scroll-overlay {
        position: absolute;
        inset: 0;
        background: rgba(var(--bg-body-rgb, 15, 23, 42), 0.7);
        color: var(--text-main);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2000;
        backdrop-filter: blur(4px);
        transition: opacity 0.3s;
    }

    /* Custom Loader */
    .custom-loader {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--bg-card);
        opacity: 0.9;
        z-index: 2000;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        border-radius: 12px;
    }

    /* Layer Items inside the Sidebar */
    .sidebar-header {
        padding: 12px 16px 8px;
        border-bottom: 1px dashed var(--border-color);
    }

    .sidebar-content {
        padding: 10px 14px;
        overflow-y: auto;
    }

    .layer-item {
        background: var(--bg-body);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        margin-bottom: 6px;
        padding: 6px 10px;
        display: flex;
        align-items: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .layer-item:hover {
        transform: translateY(-1px);
        border-color: var(--sapphire-primary);
    }

    .layer-item.active {
        background: var(--bg-card);
        border-color: var(--sapphire-primary);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .layer-icon-box {
        width: 24px;
        height: 24px;
        /* REDUCED */
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        border-radius: 6px;
        margin-right: 10px;
        background: var(--table-hover);
    }

    .layer-label {
        font-weight: 600;
        font-size: 0.75rem;
        color: var(--text-main);
        flex: 1;
    }

    .layer-count-pill {
        background: var(--table-hover);
        color: var(--text-muted);
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 0.65rem;
        font-weight: 700;
        margin: 0 8px;
    }

    .layer-eye {
        font-size: 1rem;
        color: var(--border-color);
        transition: color 0.2s;
    }

    .layer-item.active .layer-eye {
        color: var(--sapphire-success);
    }

    /* Custom Leaflet DivIcons (Emojis on Map) */
    .custom-map-marker {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        color: white;
        font-size: 12px;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);
        border: 2px solid white;
    }

    /* Scroll Overlay */
    .map-scroll-overlay {
        position: absolute;
        inset: 0;
        background: rgba(var(--bg-body-rgb, 15, 23, 42), 0.7);
        color: var(--text-main);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2000;
        backdrop-filter: blur(4px);
        transition: opacity 0.3s;
    }

    /* Layer Items inside the Sidebar */
    .sidebar-header {
        padding: 20px 20px 10px;
        border-bottom: 1px dashed var(--border-color);
    }

    .sidebar-content {
        padding: 15px 20px;
        overflow-y: auto;
    }

    .layer-item {
        background: var(--bg-body);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        margin-bottom: 8px;
        padding: 10px 14px;
        display: flex;
        align-items: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .layer-item:hover {
        transform: translateY(-2px);
        border-color: var(--sapphire-primary);
    }

    .layer-item.active {
        background: var(--bg-card);
        border-color: var(--sapphire-primary);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .layer-icon-box {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        border-radius: 8px;
        margin-right: 12px;
        background: var(--table-hover);
    }

    .layer-label {
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--text-main);
        flex: 1;
    }

    .layer-count-pill {
        background: var(--table-hover);
        color: var(--text-muted);
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 700;
        margin: 0 10px;
    }

    .layer-eye {
        font-size: 1.1rem;
        color: var(--border-color);
        transition: color 0.2s;
    }

    .layer-item.active .layer-eye {
        color: var(--sapphire-success);
    }

    /* Custom Leaflet DivIcons (Emojis on Map) */
    .custom-map-marker {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        color: white;
        font-size: 14px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        border: 2px solid white;
    }

    /* Scroll Overlay */
    .map-scroll-overlay {
        position: absolute;
        inset: 0;
        background: rgba(var(--bg-body-rgb, 15, 23, 42), 0.7);
        color: var(--text-main);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2000;
        backdrop-filter: blur(4px);
        transition: opacity 0.3s;
    }

    /* Dark Mode Map Hack (Inverts map colors without needing new tiles) */
    [data-bs-theme="dark"] .leaflet-layer,
    [data-bs-theme="dark"] .leaflet-control-zoom-in,
    [data-bs-theme="dark"] .leaflet-control-zoom-out,
    [data-bs-theme="dark"] .leaflet-control-attribution {
        filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
    }
</style>

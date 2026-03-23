@php
    // Detect User and Role
    $authUser = Auth::user() ?? session('user');
    $roleId = $authUser->role_id ?? null;

    // Role Definitions
    $isGlobalAdmin = $roleId == 8; // Provider / Global Admin

    // State Detection
    $isGlobalRoute = request()->is('global*');
    $isSimulating = $isGlobalAdmin && !$isGlobalRoute;
@endphp

<style>
    /* ================================================
       SIDEBAR — SAPPHIRE THEME INTEGRATION
       - Hover to expand / collapse
       - Perfectly synced with Light/Dark mode
       - Dynamic Theme Toggler Included
    ================================================ */

    :root {
        --sb-width-collapsed: 76px;
        --sb-width-expanded: 260px;
        --sb-transition: cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ================================================
       BASE SIDEBAR
    ================================================ */
    .sidebar {
        height: 100vh;
        width: var(--sb-width-collapsed);
        position: fixed;
        top: 0;
        left: 0;
        background-color: var(--bg-card);
        border-right: 1px solid var(--border-color);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        z-index: 1050;
        transition: width 0.35s var(--sb-transition), background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.35s var(--sb-transition);
        will-change: width;
    }

    .sidebar:hover {
        width: var(--sb-width-expanded);
        box-shadow: 4px 0 24px rgba(0, 0, 0, 0.05);
    }

    /* ================================================
       SCROLLBAR
    ================================================ */
    .sidebar-content {
        flex-grow: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 12px 0 16px;
        scrollbar-width: thin;
        scrollbar-color: var(--border-color) transparent;
    }

    .sidebar-content::-webkit-scrollbar {
        width: 4px;
    }

    .sidebar-content::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar-content::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 99px;
    }

    /* ================================================
       LOGO AREA
    ================================================ */
    .sidebar-header {
        display: flex;
        align-items: center;
        padding: 0 18px;
        height: 70px;
        flex-shrink: 0;
        background: var(--bg-card);
        border-bottom: 1px solid var(--border-color);
        gap: 12px;
    }

    .sidebar-logo-icon {
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--sapphire-primary);
        transition: transform 0.3s var(--sb-transition), background-color 0.3s;
    }

    .sidebar:hover .sidebar-logo-icon {
        transform: scale(1.05);
    }

    .sidebar-logo-icon img {
        max-height: 24px;
        max-width: 24px;
        object-fit: contain;
        filter: brightness(0) invert(1);
    }

    .sidebar-logo-text {
        opacity: 0;
        transform: translateX(-8px);
        transition: opacity 0.2s ease, transform 0.3s var(--sb-transition);
        white-space: nowrap;
        overflow: hidden;
    }

    .sidebar:hover .sidebar-logo-text {
        opacity: 1;
        transform: translateX(0);
    }

    .sidebar-logo-text h6 {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        color: var(--text-main);
        letter-spacing: 0.5px;
    }

    .sidebar-logo-text span {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: var(--sapphire-primary);
    }

    /* SECTION TITLES */
    .sidebar-section-title {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-muted);
        margin: 16px 18px 4px 18px;
        opacity: 0;
        transition: opacity 0.2s ease;
        white-space: nowrap;
    }

    .sidebar:hover .sidebar-section-title {
        opacity: 1;
    }

    /* ================================================
       NAV LINKS
    ================================================ */
    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 18px;
        margin: 4px 10px;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        border-radius: 10px;
        white-space: nowrap;
        position: relative;
        transition: all 0.2s ease;
    }

    .sidebar-link i {
        font-size: 1.2rem;
        flex-shrink: 0;
        width: 24px;
        text-align: center;
        transition: color 0.2s ease, transform 0.2s ease;
    }

    .sidebar-link .link-text {
        opacity: 0;
        transform: translateX(-6px);
        transition: opacity 0.2s ease, transform 0.3s var(--sb-transition);
        pointer-events: none;
    }

    .sidebar:hover .sidebar-link .link-text {
        opacity: 1;
        transform: translateX(0);
    }

    /* Hover & Active States */
    .sidebar-link:hover {
        background: var(--table-hover);
        color: var(--sapphire-primary);
    }

    .sidebar-link:hover i {
        transform: scale(1.1);
    }

    .sidebar-link.active {
        background: var(--table-hover);
        color: var(--sapphire-primary);
        font-weight: 600;
    }

    /* Special Active State for Exit Simulation Button */
    .sidebar-link.btn-exit-sim {
        background: rgba(239, 68, 68, 0.1);
        color: var(--sapphire-danger);
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .sidebar-link.btn-exit-sim i {
        color: var(--sapphire-danger);
    }

    .sidebar-link.btn-exit-sim:hover {
        background: var(--sapphire-danger);
        color: #ffffff;
    }

    .sidebar-link.btn-exit-sim:hover i {
        color: #ffffff;
    }

    /* Glowing Indicator Dot */
    .sidebar-link.active:not(.btn-exit-sim)::before {
        content: "";
        position: absolute;
        left: -10px;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 60%;
        border-radius: 0 4px 4px 0;
        background: var(--sapphire-primary);
        transition: background-color 0.3s;
    }

    /* ================================================
       DROPDOWNS
    ================================================ */
    .sidebar-dropdown {
        flex-direction: column;
    }

    .dropdown-arrow {
        margin-left: auto;
        font-size: 0.7rem !important;
        transition: transform 0.3s ease;
        opacity: 0;
    }

    .sidebar:hover .dropdown-arrow {
        opacity: 1;
    }

    .sidebar-link.dropdown-open .dropdown-arrow {
        transform: rotate(180deg);
    }

    .sidebar-submenu {
        padding-left: 48px;
        overflow: hidden;
        background: transparent;
        margin: 4px 10px;
        border-radius: 8px;
    }

    .sidebar-sublink {
        display: block;
        color: var(--text-muted);
        text-decoration: none;
        padding: 8px 12px;
        font-size: 0.85rem;
        border-radius: 6px;
        transition: all 0.2s ease;
        margin-bottom: 2px;
    }

    .sidebar-sublink:hover {
        background: var(--table-hover);
        color: var(--sapphire-primary);
        transform: translateX(3px);
    }

    .sidebar-sublink.active {
        color: var(--sapphire-primary);
        font-weight: 600;
    }

    .sidebar:not(:hover) .sidebar-submenu {
        display: none !important;
    }

    /* ================================================
       THEME TOGGLER & FOOTER
    ================================================ */
    .sidebar-footer {
        padding: 16px 10px;
        border-top: 1px solid var(--border-color);
        background: var(--bg-card);
        flex-shrink: 0;
        overflow: hidden;
    }

    .theme-controls {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 8px 12px 8px;
        border-bottom: 1px dashed var(--border-color);
        margin-bottom: 12px;
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .sidebar:hover .theme-controls {
        opacity: 1;
    }

    .theme-swatch {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid transparent;
        transition: transform 0.2s ease;
    }

    .theme-swatch:hover {
        transform: scale(1.2);
    }

    .mode-toggle {
        background: var(--bg-body);
        border: 1px solid var(--border-color);
        color: var(--text-muted);
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .mode-toggle:hover {
        color: var(--sapphire-primary);
        border-color: var(--sapphire-primary);
    }

    .sidebar-user {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px;
        border-radius: 10px;
        transition: background 0.2s ease;
        text-decoration: none;
    }

    .sidebar-user:hover {
        background: var(--table-hover);
    }

    .sidebar-user-avatar {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
        background: var(--bg-body);
        border: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sidebar-user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .sidebar-user-avatar i {
        color: var(--text-muted);
        font-size: 1.2rem;
    }

    .sidebar-user-info {
        display: flex;
        flex-direction: column;
        opacity: 0;
        white-space: nowrap;
        flex: 1;
        transition: opacity 0.2s ease;
    }

    .sidebar:hover .sidebar-user-info {
        opacity: 1;
    }

    .sidebar-user-info strong {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-main);
    }

    .sidebar-user-info small {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .sidebar-user-logout {
        color: var(--sapphire-danger);
        background: transparent;
        border: none;
        padding: 6px;
        border-radius: 6px;
        cursor: pointer;
        opacity: 0;
        transition: all 0.2s ease;
    }

    .sidebar:hover .sidebar-user-logout {
        opacity: 1;
    }

    .sidebar-user-logout:hover {
        background: rgba(239, 68, 68, 0.1);
    }

    @media (max-width: 991px) {
        .sidebar {
            transform: translateX(-100%);
            width: var(--sb-width-expanded);
        }

        .sidebar:hover {
            width: var(--sb-width-expanded);
        }

        .sidebar.mobile-show {
            transform: translateX(0);
        }

        .sidebar.mobile-show .sidebar-logo-text,
        .sidebar.mobile-show .link-text,
        .sidebar.mobile-show .sidebar-user-info,
        .sidebar.mobile-show .sidebar-user-logout,
        .sidebar.mobile-show .theme-controls {
            opacity: 1;
            transform: translateX(0);
        }
    }
</style>

<div class="sidebar" id="sidebar">

    {{-- ===== HEADER / LOGO ===== --}}
    {{-- <div class="sidebar-header">
        <div class="sidebar-logo-icon">
            <img src="{{ asset('images/logo1.png') }}" alt="Logo">
        </div>
        <div class="sidebar-logo-text">
            <h6>PUGARCH</h6>
            <span>AI Intel</span>
        </div>
    </div> --}}

    <div class="sidebar-header">

        <!-- ICON -->
        <div class="sidebar-logo-icon forest-icon">
            <!-- Forest SVG -->
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M12 2L6 10H10L5 18H19L14 10H18L12 2Z" fill="white" />
            </svg>
        </div>

        <!-- TEXT -->
        <div class="sidebar-logo-text">
            <div class="font-headline font-black text-xl tracking-tight text-primary">
                Pugarch
            </div>
            <div class="font-label text-[10px] font-semibold uppercase tracking-widest text-on-surface-variant">
                Forest Intelligence
            </div>
        </div>

    </div>
    {{-- ===== SCROLLABLE CONTENT ===== --}}
    <div class="sidebar-content">

        @if ($isGlobalAdmin && $isGlobalRoute)
            {{-- ==============================================
                 GLOBAL ADMIN DASHBOARD (Provider View)
            ============================================== --}}
            <div class="sidebar-section-title">Global Management</div>

            <a href="{{ route('global.companies') }}"
                class="sidebar-link {{ request()->routeIs('global.companies*') ? 'active' : '' }}"
                title="All Companies">
                <i class="bi bi-buildings"></i>
                <span class="link-text">Companies</span>
            </a>

            <a href="{{ route('global.superadmins') }}"
                class="sidebar-link {{ request()->routeIs('global.superadmins*') ? 'active' : '' }}"
                title="Super Admins">
                <i class="bi bi-person-badge"></i>
                <span class="link-text">Super Admins</span>
            </a>

            <a href="{{ route('global.admins') }}"
                class="sidebar-link {{ request()->routeIs('global.admins*') ? 'active' : '' }}" title="Admins">
                <i class="bi bi-person-gear"></i>
                <span class="link-text">Admins</span>
            </a>

            <a href="/global/dynamic-labels"
                class="sidebar-link {{ request()->is('/global/dynamic-labels*') ? 'active' : '' }}"
                title="Dynamic Labels">
                <i class="bi bi-tags"></i>
                <span class="link-text">Dynamic Labels</span>
            </a>
        @else
            {{-- ==============================================
                 COMPANY SPECIFIC DASHBOARD (Client View)
            ============================================== --}}

            @if ($isSimulating)
                {{-- Exit Simulation Button for Global Admins --}}
                <a href="{{ route('global.exit_simulation') }}" class="sidebar-link btn-exit-sim mb-3"
                    title="Exit Simulation">
                    <i class="bi bi-box-arrow-left"></i>
                    <span class="link-text fw-bold">Back to Global</span>
                </a>
            @endif

            <div class="sidebar-section-title">Company Menu</div>

            {{-- Dashboard --}}
            <a href="/home" class="sidebar-link {{ request()->is('home') ? 'active' : '' }}" title="Dashboard">
                <i class="bi bi-speedometer2"></i>
                <span class="link-text">Dashboard</span>
            </a>

            {{-- Clients / Ranges --}}
            <a href="{{ route('clients') }}" class="sidebar-link {{ request()->routeIs('clients*') ? 'active' : '' }}"
                title="Clients">
                <i class="bi bi-building"></i>
                <span class="link-text">Clients</span>
            </a>

            {{-- Sites / Beats --}}
            <a href="{{ route('sites.getsites', 0) }}"
                class="sidebar-link {{ request()->routeIs('sites*') ? 'active' : '' }}" title="Sites">
                <i class="bi bi-geo-alt"></i>
                <span class="link-text">Sites</span>
            </a>

            {{-- Dropdown: Users --}}
            <div class="sidebar-dropdown">
                <a href="#"
                    class="sidebar-link dropdown-toggle {{ request()->routeIs('guards*') || request()->routeIs('registrations*') ? 'active' : '' }}"
                    title="Users" onclick="toggleDropdown(event, 'users-menu')">
                    <i class="bi bi-people"></i>
                    <span class="link-text">Users</span>
                    <i class="bi bi-chevron-down dropdown-arrow"></i>
                </a>
                <div class="sidebar-submenu" id="users-menu"
                    style="{{ request()->routeIs('guards*') || request()->routeIs('registrations*') ? 'display: block;' : 'display: none;' }}">
                    <a href="{{ route('guards') }}"
                        class="sidebar-sublink {{ request()->routeIs('guards*') ? 'active' : '' }}">All Users</a>
                    <a href="{{ route('registrations.index') }}"
                        class="sidebar-sublink {{ request()->routeIs('registrations*') ? 'active' : '' }}">Registrations</a>
                </div>
            </div>

            {{-- Dynamic Labels (If allowed within company view, else remove) --}}
            <a href="/dynamic-labels" class="sidebar-link {{ request()->is('dynamic-labels*') ? 'active' : '' }}"
                title="Dynamic Labels">
                <i class="bi bi-tags"></i>
                <span class="link-text">Dynamic Labels</span>
            </a>

            {{-- Dropdown: Attendance --}}
            <div class="sidebar-dropdown">
                <a href="#"
                    class="sidebar-link dropdown-toggle {{ request()->is('attendance/*') ? 'active' : '' }}"
                    title="Attendance Summary" onclick="toggleDropdown(event, 'attendance-menu')">
                    <i class="bi bi-calendar-check"></i>
                    <span class="link-text">Attendance</span>
                    <i class="bi bi-chevron-down dropdown-arrow"></i>
                </a>
                <div class="sidebar-submenu" id="attendance-menu"
                    style="{{ request()->is('attendance/*') ? 'display: block;' : 'display: none;' }}">
                    <a href="/attendance/explorer"
                        class="sidebar-sublink {{ request()->is('attendance/explorer') ? 'active' : '' }}">Overview</a>
                    <a href="/attendance/logs"
                        class="sidebar-sublink {{ request()->is('attendance/logs') ? 'active' : '' }}">Logs</a>
                    <a href="/attendance/requests"
                        class="sidebar-sublink {{ request()->is('attendance/requests') ? 'active' : '' }}">Requests</a>
                </div>
            </div>

            {{-- Events --}}
            <a href="{{ route('report-configs.dashboard') }}"
                class="sidebar-link {{ request()->is('report-configs*') ? 'active' : '' }}">
                <i class="bi bi-calendar-event"></i>
                <span class="link-text">Events</span>
            </a>

            {{-- Know Your Area --}}
            <a href="{{ route('know-your-area.normal') }}"
                class="sidebar-link {{ request()->is('know-your-area.normal') ? 'active' : '' }}"
                title="Know Your Area">
                <i class="bi bi-map"></i>
                <span class="link-text">Know Your Area</span>
            </a>

            {{-- Dropdown: Plantation --}}
            <div class="sidebar-dropdown">
                <a href="#"
                    class="sidebar-link dropdown-toggle {{ request()->is('plantation*') ? 'active' : '' }}"
                    title="Plantation" onclick="toggleDropdown(event, 'plantation-menu')">
                    <i class="bi bi-tree"></i>
                    <span class="link-text">Plantation</span>
                    <i class="bi bi-chevron-down dropdown-arrow"></i>
                </a>
                <div class="sidebar-submenu" id="plantation-menu"
                    style="{{ request()->is('plantation*') ? 'display: block;' : 'display: none;' }}">
                    <a href="/plantation/dashboard"
                        class="sidebar-sublink {{ request()->is('plantation/dashboard') ? 'active' : '' }}">Dashboard</a>
                    <a href="/plantation/analytics"
                        class="sidebar-sublink {{ request()->is('plantation/analytics') ? 'active' : '' }}">Analytics</a>
                </div>
            </div>

            {{-- Dropdown: Patrolling --}}
            <div class="sidebar-dropdown">
                <a href="#"
                    class="sidebar-link dropdown-toggle {{ request()->routeIs('patrolling.*') || (request()->is('patrol*') && !request()->is('patrol/maps')) ? 'active' : '' }}"
                    title="Patrolling" onclick="toggleDropdown(event, 'patrolling-menu')">
                    <i class="bi bi-shield-check"></i>
                    <span class="link-text">Patrolling</span>
                    <i class="bi bi-chevron-down dropdown-arrow"></i>
                </a>
                <div class="sidebar-submenu" id="patrolling-menu"
                    style="{{ request()->routeIs('patrolling.*') || (request()->is('patrol*') && !request()->is('patrol/maps')) ? 'display: block;' : 'display: none;' }}">
                    <a href="{{ route('patrolling.log', ['flag' => 'all']) }}"
                        class="sidebar-sublink {{ request()->routeIs('patrolling.log') ? 'active' : '' }}">Patrol
                        Logs</a>
                    <a href="{{ route('patrolling') }}"
                        class="sidebar-sublink {{ request()->routeIs('patrolling') ? 'active' : '' }}">All
                        Patrolling</a>
                    <a href="{{ route('patrolling.analysis') }}"
                        class="sidebar-sublink {{ request()->routeIs('patrolling.analysis') ? 'active' : '' }}">Map
                        View</a>
                    <a href="{{ route('patrolling.analytics.pro.advanced') }}"
                        class="sidebar-sublink {{ request()->routeIs('patrolling.analytics.pro.advanced') ? 'active' : '' }}">Advanced
                        Analytics</a>
                </div>
            </div>

            <div class="sidebar-section-title">Reports & Analytics</div>

            {{-- Reports --}}
            <a href="/report/view" class="sidebar-link {{ request()->is('report/view') ? 'active' : '' }}"
                title="Reports">
                <i class="bi bi-file-earmark-text"></i>
                <span class="link-text">Reports</span>
            </a>

            {{-- Anukampa --}}
            <a href="{{ route('anukampa.dashboard') }}"
                class="sidebar-link {{ request()->is('anukampa*') ? 'active' : '' }}" title="Anukampa">
                <i class="bi bi-heart-pulse"></i>
                <span class="link-text">Anukampa</span>
            </a>

            {{-- Camera Tracking --}}
            <a href="/reports/camera-tracking"
                class="sidebar-link {{ request()->is('reports/camera-tracking') ? 'active' : '' }}"
                title="Camera Tracking">
                <i class="bi bi-camera-video"></i>
                <span class="link-text">Camera Tracking</span>
            </a>
        @endif

    </div>

    {{-- ===== FOOTER / USER & THEME TOGGLER ===== --}}
    <div class="sidebar-footer">

        {{-- Theme Controls --}}
        <div class="theme-controls">
            <div class="d-flex gap-2">
                <div class="theme-swatch" style="background: #3B82F6;" onclick="setThemeAccent('#3B82F6')"
                    title="Sapphire"></div>
                <div class="theme-swatch" style="background: #10B981;" onclick="setThemeAccent('#10B981')"
                    title="Emerald"></div>
                <div class="theme-swatch" style="background: #8B5CF6;" onclick="setThemeAccent('#8B5CF6')"
                    title="Amethyst"></div>
                <div class="theme-swatch" style="background: #EF4444;" onclick="setThemeAccent('#EF4444')"
                    title="Ruby"></div>
                <div class="theme-swatch" style="background: #F59E0B;" onclick="setThemeAccent('#F59E0B')"
                    title="Amber"></div>
            </div>
            <button class="mode-toggle" id="themeModeToggle" onclick="toggleLightDarkMode()"
                title="Toggle Light/Dark Mode">
                <i class="bi bi-moon-stars-fill"></i>
            </button>
        </div>
    </div>

</div>

<script>
    // --- Dropdown Logic ---
    function toggleDropdown(event, menuId) {
        event.preventDefault();
        const sidebar = document.getElementById('sidebar');
        if (sidebar.offsetWidth <= 76) return; // Prevent opening when collapsed

        const $menu = $('#' + menuId);
        const $link = $(event.currentTarget);

        if ($menu.is(':visible')) {
            $menu.slideUp(250);
            $link.removeClass('dropdown-open');
        } else {
            $menu.slideDown(250);
            $link.addClass('dropdown-open');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.sidebar-submenu').forEach(menu => {
            if (menu.style.display === 'block') {
                menu.previousElementSibling.classList.add('dropdown-open');
            }
        });

        // Initialize Theme & Mode
        const savedAccent = localStorage.getItem('sapphire-accent');
        if (savedAccent) setThemeAccent(savedAccent);

        updateModeIcon();
    });

    // --- Theme Accent Toggler ---
    function setThemeAccent(hexColor) {
        document.documentElement.style.setProperty('--sapphire-primary', hexColor);
        localStorage.setItem('sapphire-accent', hexColor);

        // Dispatch event so charts/maps can instantly react if needed
        window.dispatchEvent(new Event('themeChanged'));
    }

    // --- Light/Dark Mode Toggler ---
    function toggleLightDarkMode() {
        const htmlTag = document.documentElement;
        const currentMode = htmlTag.getAttribute('data-bs-theme');
        const newMode = currentMode === 'dark' ? 'light' : 'dark';

        htmlTag.setAttribute('data-bs-theme', newMode);
        localStorage.setItem('app-mode', newMode); // Assume you have setup to read this on load

        updateModeIcon();
        window.dispatchEvent(new Event('themeChanged'));
    }

    function updateModeIcon() {
        const currentMode = document.documentElement.getAttribute('data-bs-theme');
        const icon = document.querySelector('#themeModeToggle i');
        if (currentMode === 'dark') {
            icon.className = 'bi bi-sun-fill';
        } else {
            icon.className = 'bi bi-moon-stars-fill';
        }
    }
</script>

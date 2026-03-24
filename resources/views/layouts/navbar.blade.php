@php
    use App\Models\User;
    use Illuminate\Support\Facades\Auth;

    $user = Auth::user() ?? session('user');
    $company = session('company');
    $notificationsCount = 3; // Mocked for demonstration

    $isForest = $company?->is_forest ?? false;
    $roles = [
        1 => $isForest ? 'DFO' : 'Superadmin',
        2 => $isForest ? 'Ranger' : 'Supervisor',
        7 => $isForest ? 'ACF' : 'Admin',
        4 => 'Client',
        8 => 'Global Admin',
    ];
@endphp

<style>
    /* =========================================
       SAPPHIRE HEADER — PREMIUM & MINIMAL
    ========================================= */

    /* Header Shell */
    .sapphire-header-wrapper {
        position: sticky;
        top: 0;
        z-index: 1040;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), padding 0.3s ease;
    }

    .sapphire-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 1.5rem;
        height: var(--header-height, 65px);
        background-color: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-bottom: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    [data-bs-theme="dark"] .sapphire-header {
        background-color: rgba(15, 23, 42, 0.85);
    }

    /* === 4 HEADER LAYOUT OPTIONS === */
    .sapphire-header-wrapper.layout-sticky {
        padding: 0;
    }

    .sapphire-header-wrapper.layout-floating {
        top: 16px;
        padding: 0 24px;
    }

    .sapphire-header-wrapper.layout-floating .sapphire-header {
        border-radius: 100px;
        border: 1px solid var(--border-color);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        max-width: 1400px;
        margin: 0 auto;
    }

    .sapphire-header-wrapper.layout-autohide {
        padding: 0;
    }

    .sapphire-header-wrapper.header-hidden {
        transform: translateY(-120%);
    }

    .sapphire-header-wrapper.layout-minimal .sapphire-header {
        background-color: transparent !important;
        backdrop-filter: none;
        border-bottom: 2px solid var(--sapphire-primary);
        padding: 0 1rem;
    }

    /* Density Controls */
    body.density-compact {
        --header-height: 54px;
    }

    body.density-comfortable {
        --header-height: 70px;
    }

    /* =========================================
       LEFT: BREADCRUMBS
    ========================================= */
    .header-breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-muted);
    }

    .header-breadcrumb .brand-accent {
        color: var(--text-main);
        font-weight: 800;
        letter-spacing: -0.5px;
        font-size: 1.05rem;
    }

    .header-breadcrumb .separator {
        color: var(--border-color);
        font-weight: 300;
    }

    .header-breadcrumb .current-page {
        color: var(--sapphire-primary);
    }

    /* =========================================
       RIGHT: UTILITIES & ICONS
    ========================================= */
    .nav-utils {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .nav-icon-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: transparent;
        border: 1px solid transparent;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }

    .nav-icon-btn:hover,
    .nav-icon-btn.active {
        background-color: var(--bg-body);
        border-color: var(--border-color);
        color: var(--sapphire-primary);
    }

    .nav-icon-btn i {
        font-size: 1.15rem;
    }

    /* Notification Dot */
    .notification-dot {
        position: absolute;
        top: 6px;
        right: 8px;
        width: 8px;
        height: 8px;
        background-color: var(--sapphire-danger);
        border-radius: 50%;
        box-shadow: 0 0 0 2px var(--bg-card);
    }

    /* Profile Dropdown Override */
    .profile-avatar {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        object-fit: cover;
        background: var(--sapphire-primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        border: 2px solid transparent;
        transition: border-color 0.2s ease;
    }

    .profile-avatar:hover {
        border-color: var(--sapphire-primary);
    }

    /* Dropdown Menus */
    .sapphire-dropdown-menu {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
        padding: 8px 0;
        min-width: 240px;
        margin-top: 12px !important;
        z-index: 1050;
    }

    .dropdown-section-title {
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-muted);
        padding: 8px 20px 4px 20px;
    }

    .sapphire-dropdown-item {
        padding: 8px 20px;
        color: var(--text-main);
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.2s ease;
        text-decoration: none;
        cursor: pointer;
        background: transparent;
        border: none;
        width: 100%;
    }

    .sapphire-dropdown-item:hover,
    .sapphire-dropdown-item.active {
        background: var(--bg-body);
        color: var(--sapphire-primary);
    }

    .sapphire-dropdown-item i {
        font-size: 1rem;
        color: var(--text-muted);
        transition: color 0.2s ease;
    }

    .sapphire-dropdown-item:hover i,
    .sapphire-dropdown-item.active i {
        color: var(--sapphire-primary);
    }

    /* Theme Check Icon */
    .theme-check-icon {
        display: none;
        color: var(--sapphire-primary);
        font-size: 1rem;
    }

    .sapphire-dropdown-item.active .theme-check-icon {
        display: block;
    }
</style>

<div class="sapphire-header-wrapper" id="headerWrapper">
    <header class="sapphire-header">

        {{-- Left: Mobile Toggle & Dynamic Breadcrumbs --}}
        <div class="d-flex align-items-center gap-3">
            <button class="btn border-0 d-lg-none p-0 nav-icon-btn" id="sidebarToggle" type="button">
                <i class="bi bi-list"></i>
            </button>

            <div class="header-breadcrumb d-none d-md-flex">
                @if ($user->role_id == 8)
                    <span class="brand-accent"><i class="bi bi-globe me-1"></i>
                        {{ $company?->name ?? 'Global Admin' }}</span>
                @else
                    <span
                        class="brand-accent">{{ $user?->company_name ?? ($company?->name ?? 'Patrol Analytics') }}</span>
                @endif
                <span class="separator">/</span>
                <span class="current-page" id="dynamicPageTitle">@yield('title', 'Dashboard')</span>
            </div>
        </div>

        {{-- Right: Utilities --}}
        <div class="nav-utils">

            {{-- 🟢 ADMIN ONLY FEATURES (Role 8) --}}
            @if ($user?->role_id == 8)
                {{-- Search (Command Palette Trigger) --}}
                <button class="nav-icon-btn" id="searchTrigger" title="Search (Ctrl+K)">
                    <i class="bi bi-search"></i>
                </button>

                {{-- Browser Fullscreen Toggle --}}
                <button class="nav-icon-btn d-none d-md-flex" id="fullscreenToggle" title="Toggle Fullscreen">
                    <i class="bi bi-arrows-fullscreen" id="fullscreenIcon"></i>
                </button>

                {{-- Appearance & Settings Dropdown --}}
                <div class="dropdown">
                    <button class="nav-icon-btn" data-bs-toggle="dropdown" aria-expanded="false"
                        title="Display Settings">
                        <i class="bi bi-sliders2"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end sapphire-dropdown-menu">
                        <div class="dropdown-section-title">Focus & Layout</div>
                        <button class="sapphire-dropdown-item" id="zenModeToggle">
                            <div class="d-flex align-items-center gap-2"><i
                                    class="bi bi-layout-sidebar-inset-reverse"></i> Zen Mode (Focus)</div>
                        </button>
                        <div class="my-2 border-bottom" style="border-color: var(--border-color);"></div>

                        <div class="dropdown-section-title">Header Style</div>
                        <button class="sapphire-dropdown-item layout-option" data-layout="sticky"
                            onclick="setHeaderLayout('sticky')">
                            <div class="d-flex align-items-center gap-2"><i class="bi bi-layout-top"></i> Sticky Top
                            </div>
                            <i class="bi bi-check2 theme-check-icon"></i>
                        </button>
                        <button class="sapphire-dropdown-item layout-option" data-layout="floating"
                            onclick="setHeaderLayout('floating')">
                            <div class="d-flex align-items-center gap-2"><i class="bi bi-capsule"></i> Floating Pill
                            </div>
                            <i class="bi bi-check2 theme-check-icon"></i>
                        </button>
                        <button class="sapphire-dropdown-item layout-option" data-layout="autohide"
                            onclick="setHeaderLayout('autohide')">
                            <div class="d-flex align-items-center gap-2"><i class="bi bi-arrows-collapse"></i> Auto-Hide
                                Scroll</div>
                            <i class="bi bi-check2 theme-check-icon"></i>
                        </button>
                        <button class="sapphire-dropdown-item layout-option" data-layout="minimal"
                            onclick="setHeaderLayout('minimal')">
                            <div class="d-flex align-items-center gap-2"><i class="bi bi-border-bottom"></i> Minimal
                                Edge</div>
                            <i class="bi bi-check2 theme-check-icon"></i>
                        </button>

                        <div class="my-2 border-bottom" style="border-color: var(--border-color);"></div>

                        <div class="dropdown-section-title">UI Density</div>
                        <button class="sapphire-dropdown-item density-option" data-density="comfortable"
                            onclick="setUIDensity('comfortable')">
                            <div class="d-flex align-items-center gap-2"><i class="bi bi-view-list"></i> Comfortable
                            </div>
                            <i class="bi bi-check2 theme-check-icon"></i>
                        </button>
                        <button class="sapphire-dropdown-item density-option" data-density="compact"
                            onclick="setUIDensity('compact')">
                            <div class="d-flex align-items-center gap-2"><i class="bi bi-view-stacked"></i> Compact
                            </div>
                            <i class="bi bi-check2 theme-check-icon"></i>
                        </button>
                    </div>
                </div>
            @endif
            {{-- 🔴 END ADMIN ONLY FEATURES --}}


            {{-- Advanced Theme Toggle (Light/Dark/System) --}}
            <div class="dropdown">
                <button class="nav-icon-btn" data-bs-toggle="dropdown" aria-expanded="false" title="Theme Mode">
                    <i class="bi" id="headerThemeIcon"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end sapphire-dropdown-menu" style="min-width: 180px;">
                    <button class="sapphire-dropdown-item theme-option" data-theme="light"
                        onclick="setAppTheme('light')">
                        <div class="d-flex align-items-center gap-2"><i class="bi bi-sun"></i> Light</div>
                        <i class="bi bi-check2 theme-check-icon"></i>
                    </button>
                    <button class="sapphire-dropdown-item theme-option" data-theme="dark" onclick="setAppTheme('dark')">
                        <div class="d-flex align-items-center gap-2"><i class="bi bi-moon-stars"></i> Dark</div>
                        <i class="bi bi-check2 theme-check-icon"></i>
                    </button>
                    <button class="sapphire-dropdown-item theme-option" data-theme="system"
                        onclick="setAppTheme('system')">
                        <div class="d-flex align-items-center gap-2"><i class="bi bi-display"></i> System Default
                        </div>
                        <i class="bi bi-check2 theme-check-icon"></i>
                    </button>
                </div>
            </div>

            {{-- Notifications --}}
            <button class="nav-icon-btn" title="Notifications">
                <i class="bi bi-bell"></i>
                @if ($notificationsCount > 0)
                    <div class="notification-dot"></div>
                @endif
            </button>

            <div class="vr mx-2" style="height: 20px; opacity: 0.15; background-color: var(--text-main);"></div>

            {{-- Profile Dropdown --}}
            <div class="dropdown">
                <div data-bs-toggle="dropdown" aria-expanded="false" title="{{ $user?->name }}"
                    style="cursor: pointer;">
                    @if ($user?->profile_pic)
                        <img src="{{ $user->profile_pic }}" class="profile-avatar shadow-sm" alt="Avatar">
                    @else
                        <div class="profile-avatar shadow-sm">
                            {{ substr($user?->name ?? 'U', 0, 1) }}
                        </div>
                    @endif
                </div>

                <div class="dropdown-menu dropdown-menu-end sapphire-dropdown-menu" style="min-width: 220px;">
                    <div class="px-4 py-2 mb-2"
                        style="background: var(--bg-body); border-bottom: 1px solid var(--border-color); border-radius: 12px 12px 0 0; margin-top: -8px;">
                        <div class="fw-bold" style="color: var(--text-main); font-size: 0.95rem;">{{ $user?->name }}
                        </div>
                        <div
                            style="color: var(--sapphire-primary); font-size: 0.7rem; font-weight: 700; text-transform: uppercase;">
                            {{ $roles[$user?->role_id ?? 0] ?? 'User' }}
                        </div>
                    </div>

                    <a class="sapphire-dropdown-item" href="{{ route('profile', $user?->id ?? 0) }}">
                        <div class="d-flex align-items-center gap-2"><i class="bi bi-person"></i> My Account</div>
                    </a>

                    <div class="my-2 border-bottom" style="border-color: var(--border-color);"></div>

                    <form action="{{ route('logout') }}" method="POST" class="m-0 p-0">
                        @csrf
                        <button type="submit" class="sapphire-dropdown-item" style="color: var(--sapphire-danger);">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-box-arrow-right" style="color: var(--sapphire-danger);"></i> Sign out
                            </div>
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </header>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        // Check if Admin scripts should be initialized
        const isAdmin = {{ $user?->role_id == 8 ? 'true' : 'false' }};

        if (isAdmin) {
            /* =========================================================
               1. COMMAND PALETTE (Ctrl+K Shortcut)
            ========================================================= */
            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    alert("Command Palette Opened! (Implement your search modal logic here)");
                }
            });

            const searchTrigger = document.getElementById('searchTrigger');
            if (searchTrigger) {
                searchTrigger.addEventListener('click', () => {
                    alert("Command Palette Opened! (Implement your search modal logic here)");
                });
            }

            /* =========================================================
               2. FULLSCREEN API LOGIC
            ========================================================= */
            const fullscreenBtn = document.getElementById('fullscreenToggle');
            const fullscreenIcon = document.getElementById('fullscreenIcon');

            if (fullscreenBtn) {
                fullscreenBtn.addEventListener('click', () => {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(err => console.log(err));
                    } else {
                        document.exitFullscreen();
                    }
                });

                document.addEventListener('fullscreenchange', () => {
                    if (document.fullscreenElement) {
                        fullscreenIcon.classList.replace('bi-arrows-fullscreen', 'bi-fullscreen-exit');
                    } else {
                        fullscreenIcon.classList.replace('bi-fullscreen-exit', 'bi-arrows-fullscreen');
                    }
                });
            }

            /* =========================================================
               3. ZEN MODE (Focus Mode)
            ========================================================= */
            const zenToggle = document.getElementById('zenModeToggle');
            const sidebar = document.getElementById('sidebar');

            if (zenToggle) {
                zenToggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    document.body.classList.toggle('zen-mode-active');
                    if (document.body.classList.contains('zen-mode-active')) {
                        if (sidebar) sidebar.style.transform = 'translateX(-100%)';
                        document.querySelector('.content').style.marginLeft = '0';
                    } else {
                        if (sidebar) sidebar.style.transform = '';
                        document.querySelector('.content').style.marginLeft = '';
                    }
                });
            }

            /* =========================================================
               4. SMART HEADER LAYOUTS & AUTO-HIDE
            ========================================================= */
            const headerWrapper = document.getElementById('headerWrapper');
            let lastScrollY = window.scrollY;
            let isAutoHideEnabled = localStorage.getItem('header-layout') === 'autohide';

            window.setHeaderLayout = function(layoutName) {
                headerWrapper.classList.remove('layout-sticky', 'layout-floating', 'layout-autohide',
                    'layout-minimal');
                headerWrapper.classList.remove('header-hidden');
                isAutoHideEnabled = false;

                headerWrapper.classList.add(`layout-${layoutName}`);
                if (layoutName === 'autohide') isAutoHideEnabled = true;

                localStorage.setItem('header-layout', layoutName);
                updateDropdownCheckmarks('.layout-option', layoutName);
            };

            window.addEventListener('scroll', () => {
                if (!isAutoHideEnabled) return;
                if (window.scrollY > lastScrollY && window.scrollY > 80) {
                    headerWrapper.classList.add('header-hidden');
                } else {
                    headerWrapper.classList.remove('header-hidden');
                }
                lastScrollY = window.scrollY;
            }, {
                passive: true
            });

            setHeaderLayout(localStorage.getItem('header-layout') || 'sticky');

            /* =========================================================
               5. UI DENSITY
            ========================================================= */
            window.setUIDensity = function(density) {
                document.body.classList.remove('density-compact', 'density-comfortable');
                document.body.classList.add(`density-${density}`);
                localStorage.setItem('ui-density', density);
                updateDropdownCheckmarks('.density-option', density);
            };

            setUIDensity(localStorage.getItem('ui-density') || 'comfortable');
        }

        /* =========================================================
           6. LIGHT / DARK / SYSTEM MODE LOGIC (Available to All)
        ========================================================= */
        const htmlTag = document.documentElement;
        const headerThemeIcon = document.getElementById('headerThemeIcon');

        function applyTheme(theme) {
            let activeTheme = theme;

            if (theme === 'system') {
                activeTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            htmlTag.setAttribute('data-bs-theme', activeTheme);
            headerThemeIcon.classList.remove('bi-display', 'bi-moon-stars', 'bi-sun');

            if (theme === 'system') {
                headerThemeIcon.classList.add('bi-display');
            } else if (activeTheme === 'dark') {
                headerThemeIcon.classList.add('bi-moon-stars');
            } else {
                headerThemeIcon.classList.add('bi-sun');
            }

            updateDropdownCheckmarks('.theme-option', theme);
            window.dispatchEvent(new Event('themeChanged'));
        }

        window.setAppTheme = function(theme) {
            localStorage.setItem('app-theme-preference', theme);
            applyTheme(theme);
        };

        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            if (localStorage.getItem('app-theme-preference') === 'system') {
                applyTheme('system');
            }
        });

        const savedThemePref = localStorage.getItem('app-theme-preference') || 'light';
        applyTheme(savedThemePref);

        function updateDropdownCheckmarks(selectorClass, activeValue) {
            document.querySelectorAll(selectorClass).forEach(btn => {
                const dataAttr = selectorClass.replace('.', '') === 'theme-option' ? 'data-theme' :
                    selectorClass.replace('.', '') === 'layout-option' ? 'data-layout' : 'data-density';

                if (btn.getAttribute(dataAttr) === activeValue) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        }
    });
</script>

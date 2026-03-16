<div class="sidebar" id="sidebar">

    {{-- ===== HEADER / LOGO ===== --}}
    <div class="sidebar-header">
        <div class="sidebar-logo-icon">
            <img src="{{ asset('images/logo1.png') }}" alt="Logo">
        </div>
        <div class="sidebar-logo-text">
            <h6>PUGARCH</h6>
            <span>AI Intel</span>
        </div>
    </div>

    {{-- ===== SCROLLABLE CONTENT ===== --}}
    <div class="sidebar-content">

        {{-- ─── SECTION: AI PATROLLING ─── --}}
        <div class="sidebar-section-label">
            <span class="label-line"></span>
            <span class="label-text">AI Patrolling</span>
        </div>

        <a href="/home"
            class="sidebar-link {{ request()->is('home') ? 'active' : '' }}"
            title="Dashboard">
            <i class="bi bi-speedometer2"></i>
            <span class="link-text">Dashboard</span>
        </a>

        <a href="/analytics/executive"
            class="sidebar-link {{ request()->is('analytics/executive') ? 'active' : '' }}"
            title="Executive Analytics">
            <i class="bi bi-graph-up-arrow"></i>
            <span class="link-text">Executive Analytics</span>
        </a>

        <a href="/patrol/foot-summary"
            class="sidebar-link {{ request()->is('patrol/foot-summary') ? 'active' : '' }}"
            title="Foot Patrolling">
            <i class="bi bi-person-walking"></i>
            <span class="link-text">Foot Patrolling</span>
        </a>

        <a href="/patrol/night-summary"
            class="sidebar-link {{ request()->is('patrol/night-summary') ? 'active' : '' }}"
            title="Night Patrolling">
            <i class="bi bi-moon-stars"></i>
            <span class="link-text">Night Patrolling</span>
        </a>

        <a href="/patrol/maps"
            class="sidebar-link {{ request()->is('patrol/maps') ? 'active' : '' }}"
            title="KML / Patrol Map">
            <i class="bi bi-map"></i>
            <span class="link-text">KML / Patrol Map</span>
        </a>

        <div class="sidebar-item">

            <a class="sidebar-link d-flex align-items-center justify-content-between"
                data-bs-toggle="collapse"
                href="#attendanceMenu"
                role="button"
                aria-expanded="{{ request()->is('attendance/*') ? 'true' : 'false' }}">

                <div class="d-flex align-items-center">
                    <i class="bi bi-calendar-check fs-5 me-2"></i>
                    <span class="link-text">Attendance Dashboard</span>
                </div>

                <i class="bi bi-chevron-down sidebar-arrow"></i>
            </a>

            <div class="collapse {{ request()->is('attendance/*') ? 'show' : '' }}" id="attendanceMenu">

                <ul class="list-unstyled ms-4 mt-2">

                    <li>
                        <a href="/attendance/explorer"
                            class="sidebar-link {{ request()->is('attendance/explorer') ? 'active' : '' }}">
                            Attendance Overview
                        </a>
                    </li>
                    <li>
                        <a href="/attendance/logs"
                            class="sidebar-link {{ request()->is('attendance/logs') ? 'active' : '' }}">
                            Attendance logs
                        </a>
                    </li>

                    <li>
                        <a href="/attendance/requests"
                            class="sidebar-link {{ request()->is('attendance/requests') ? 'active' : '' }}">
                            Requests
                        </a>
                    </li>

                    <li>
                        <a href="/attendance/map"
                            class="sidebar-link {{ request()->is('attendance/map') ? 'active' : '' }}">
                            Map View
                        </a>
                    </li>

                </ul>

            </div>

        </div>
        <a href="/incidents/summary"
            class="sidebar-link {{ request()->is('incidents/summary') ? 'active' : '' }}"
            title="Incident Summary">
            <i class="bi bi-exclamation-triangle"></i>
            <span class="link-text">Incident Summary</span>
        </a>

        <a href="/reports/monthly"
            class="sidebar-link {{ request()->is('reports/monthly') ? 'active' : '' }}"
            title="Reports">
            <i class="bi bi-file-earmark-text"></i>
            <span class="link-text">Reports</span>
        </a>

        <a href="/reports/camera-tracking"
            class="sidebar-link {{ request()->is('reports/camera-tracking') ? 'active' : '' }}"
            title="Camera & Tracking">
            <i class="bi bi-camera-video"></i>
            <span class="link-text">Camera &amp; Tracking</span>
        </a>

        {{-- ─── SECTION: SYSTEM ─── --}}
        <hr class="sidebar-divider">

        <div class="sidebar-section-label">
            <span class="label-line"></span>
            <span class="label-text">System</span>
        </div>

        <a href="{{ route('clients') }}"
            class="sidebar-link {{ request()->routeIs('clients*') ? 'active' : '' }}"
            title="Clients">
            <i class="bi bi-building"></i>
            <span class="link-text">Clients</span>
        </a>

        <a href="{{ route('sites.getsites', 0) }}"
            class="sidebar-link {{ request()->routeIs('sites*') ? 'active' : '' }}"
            title="Sites">
            <i class="bi bi-geo-alt"></i>
            <span class="link-text">Sites</span>
        </a>

        <a href="/dynamic-labels"
            class="sidebar-link {{ request()->is('dynamic-labels*') ? 'active' : '' }}"
            title="Dynamic Labels">
            <i class="bi bi-tags"></i>
            <span class="link-text">Dynamic Labels</span>
        </a>

        <a href="{{ route('anukampa.dashboard') }}"
            class="sidebar-link {{ request()->is('anukampa*') ? 'active' : '' }}"
            title="Anukampa">
            <i class="bi bi-heart-pulse"></i>
            <span class="link-text">Anukampa</span>
        </a>

        <a href="{{ route('beat_features.dashboard') }}"
            class="sidebar-link {{ request()->is('forest/beat-features*') ? 'active' : '' }}"
            title="Beat Features">
            <i class="bi bi-pin-map"></i>
            <span class="link-text">Beat Features</span>
        </a>
        <a href="{{ route('events.reports.dashboard') }}"
            class="sidebar-link {{ request()->is('reports-dashboard*') ? 'active' : '' }}"
            title="Events">

            <i class="bi bi-calendar-event"></i>
            <span class="link-text">Events</span>

        </a>



        {{-- ─── SECTION: PLANTATION MANAGEMENT ─── --}}
        <hr class="sidebar-divider">

        <div class="sidebar-section-label">
            <span class="label-line"></span>
            <span class="label-text">Plantation</span>
        </div>

        <a href="/plantation/dashboard"
            class="sidebar-link {{ request()->is('plantation/dashboard') ? 'active' : '' }}"
            title="Plantation Dashboard">
            <i class="bi bi-tree"></i>
            <span class="link-text">Plantation Dashboard</span>
        </a>

        <a href="/plantation/analytics"
            class="sidebar-link {{ request()->is('plantation/analytics') ? 'active' : '' }}"
            title="Survival Analytics">
            <i class="bi bi-bar-chart-line"></i>
            <span class="link-text">Survival Analytics</span>
        </a>

    </div>{{-- END sidebar-content --}}

    {{-- ===== FOOTER / USER ===== --}}
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                @if(Auth::check() && Auth::user()->profile_photo)
                <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="User">
                @else
                <i class="bi bi-person-fill"></i>
                @endif
            </div>
            <div class="sidebar-user-info">
                <strong>{{ Auth::check() ? Auth::user()->name : 'User' }}</strong>
                <small>{{ Auth::check() ? Auth::user()->email : '' }}</small>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="d-inline m-0 p-0">
                @csrf
                <button type="submit" class="sidebar-user-logout" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>

</div>{{-- END .sidebar --}}

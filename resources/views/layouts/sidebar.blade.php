@php
$user = Auth::user();
@endphp

<div class="sidebar" id="sidebar">

    {{-- GLOBAL SUPERADMIN SIDEBAR --}}
    @if($user && $user->role_id == 8)

    <div class="sidebar-header">
        <div class="sidebar-logo-icon">
            <img src="{{ asset('images/logo1.png') }}" alt="Logo">
        </div>
        <div class="sidebar-logo-text">
            <h6>PUGARCH</h6>
            <span>Global Admin</span>
        </div>
    </div>

    <div class="sidebar-content">

        <div class="sidebar-section-label">
            <span class="label-line"></span>
            <span class="label-text">Global Control</span>
        </div>

        <a href="/global-dashboard"
            class="sidebar-link {{ request()->is('global-dashboard') ? 'active' : '' }}">
            <i class="bi bi-globe"></i>
            <span class="link-text">Global Dashboard</span>
        </a>

        <a href="{{ route('modules.index') }}"
            class="sidebar-link {{ request()->is('modules*') ? 'active' : '' }}">
            <i class="bi bi-grid"></i>
            <span class="link-text">Module Permission</span>
        </a>

    </div>

    {{-- USER FOOTER --}}
    <div class="sidebar-footer">

        <div class="sidebar-user">

            <div class="sidebar-user-avatar">
                <i class="bi bi-person-fill"></i>
            </div>

            <div class="sidebar-user-info">
                <strong>{{ $user->name }}</strong>
                <small>{{ $user->email }}</small>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-user-logout">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>

        </div>

    </div>

    @else

    @php
    $features = [];

    if(Auth::check()){
    $company = \App\Models\Company::find(Auth::user()->company_id);
    $features = $company->features ?? [];
    }
    @endphp
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

        <div class="sidebar-content">

            {{-- ─── SECTION: AI PATROLLING ─── --}}
            <div class="sidebar-section-label">
                <span class="label-line"></span>
                <span class="label-text">AI Patrolling</span>
            </div>

            <a href="/home"
                class="sidebar-link {{ request()->is('home') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span class="link-text">Dashboard</span>
            </a>

            <a href="/analytics/executive"
                class="sidebar-link {{ request()->is('analytics/executive') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow"></i>
                <span class="link-text">Executive Analytics</span>
            </a>

            @if($features['track'] ?? false)
            <a href="/patrol/maps"
                class="sidebar-link {{ request()->is('patrol/maps') ? 'active' : '' }}">
                <i class="bi bi-map"></i>
                <span class="link-text">KML / Patrol Map</span>
            </a>
            @endif


            @if($features['attendance'] ?? false)
            <div class="sidebar-item">

                <a class="sidebar-link d-flex align-items-center justify-content-between"
                    data-bs-toggle="collapse"
                    href="#attendanceMenu"
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
                                Attendance Logs
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
            @endif


            @if($features['incidence'] ?? false)
            <a href="/incidents/summary"
                class="sidebar-link {{ request()->is('incidents/summary') ? 'active' : '' }}">
                <i class="bi bi-exclamation-triangle"></i>
                <span class="link-text">Incident Summary</span>
            </a>
            @endif


            @if($features['allReport'] ?? false)
            <a href="/reports/monthly"
                class="sidebar-link {{ request()->is('reports/monthly') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i>
                <span class="link-text">Reports</span>
            </a>
            @endif


            @if($features['offline'] ?? false)
            <a href="/reports/camera-tracking"
                class="sidebar-link {{ request()->is('reports/camera-tracking') ? 'active' : '' }}">
                <i class="bi bi-camera-video"></i>
                <span class="link-text">Camera & Tracking</span>
            </a>
            @endif


            {{-- SYSTEM --}}
            <hr class="sidebar-divider">

            <div class="sidebar-section-label">
                <span class="label-line"></span>
                <span class="label-text">System</span>
            </div>

            <a href="{{ route('clients') }}"
                class="sidebar-link {{ request()->routeIs('clients*') ? 'active' : '' }}">
                <i class="bi bi-building"></i>
                <span class="link-text">Clients</span>
            </a>

            <a href="{{ route('sites.getsites', 0) }}"
                class="sidebar-link {{ request()->routeIs('sites*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt"></i>
                <span class="link-text">Sites</span>
            </a>

            <a href="/dynamic-labels"
                class="sidebar-link {{ request()->is('dynamic-labels*') ? 'active' : '' }}">
                <i class="bi bi-tags"></i>
                <span class="link-text">Dynamic Labels</span>
            </a>

            <!-- <a href="{{ route('modules.index') }}"
                class="sidebar-link {{ request()->is('modules*') ? 'active' : '' }}">
                <i class="bi bi-grid"></i>
                <span class="link-text">Module Permission</span>
            </a> -->


            {{-- PLANTATION --}}
            <hr class="sidebar-divider">

            <div class="sidebar-section-label">
                <span class="label-line"></span>
                <span class="label-text">Plantation</span>
            </div>

            <a href="/plantation/dashboard"
                class="sidebar-link {{ request()->is('plantation/dashboard') ? 'active' : '' }}">
                <i class="bi bi-tree"></i>
                <span class="link-text">Plantation Dashboard</span>
            </a>

            <a href="/plantation/analytics"
                class="sidebar-link {{ request()->is('plantation/analytics') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line"></i>
                <span class="link-text">Survival Analytics</span>
            </a>

        </div>



        {{-- USER FOOTER --}}
        <div class="sidebar-footer">

            <div class="sidebar-user">

                <div class="sidebar-user-avatar">
                    @if(Auth::check() && Auth::user()->profile_photo)
                    <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}">
                    @else
                    <i class="bi bi-person-fill"></i>
                    @endif
                </div>

                <div class="sidebar-user-info">
                    <strong>{{ Auth::user()->name }}</strong>
                    <small>{{ Auth::user()->email }}</small>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sidebar-user-logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>

            </div>

        </div>

    </div>


    @endif
</div>
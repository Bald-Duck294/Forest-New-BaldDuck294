@php
$user = Auth::user() ?? session('user');

$isGlobalAdmin = ($user && $user->role_id == 8);
$isSimulating = session()->has('simulated_company_id');

$features = [];

// Determine which feature set to load
if ($isSimulating) {
// Global Admin is simulating this company
$company = \App\Models\Company::find(session('simulated_company_id'));
$features = $company->features ?? [];
} elseif (Auth::check() && !$isGlobalAdmin) {
// Normal Superadmin/User
$company = \App\Models\Company::find($user->company_id);
$features = $company->features ?? [];
}
@endphp

<div class="sidebar" id="sidebar">

    {{-- ─── SIDEBAR HEADER ─── --}}
    <div class="sidebar-header">
        <div class="sidebar-logo-icon">
            <img src="{{ asset('images/logo1.png') }}" alt="Logo">
        </div>
        <div class="sidebar-logo-text">
            <h6>PUGARCH</h6>
            <span>{{ ($isGlobalAdmin && !$isSimulating) ? 'Global Admin' : 'AI Intel' }}</span>
        </div>
    </div>

    <div class="sidebar-content">

        {{-- ─── CASE 1: GLOBAL ADMIN (Main Dashboard View) ─── --}}
        @if($isGlobalAdmin && !$isSimulating)
        <div class="sidebar-section-label">
            <span class="label-line"></span>
            <span class="label-text">Global Control</span>
        </div>

        <a href="/global-dashboard"
            class="sidebar-link {{ request()->is('global-dashboard') ? 'active' : '' }}">
            <i class="bi bi-globe text-primary"></i>
            <span class="link-text">Global Dashboard</span>
        </a>

        <a href="{{ route('global.companies') }}"
            class="sidebar-link {{ request()->is('global/companies*') ? 'active' : '' }}">
            <i class="bi bi-building"></i>
            <span class="link-text">Company Management</span>
        </a>

        <a href="{{ route('modules.index') }}"
            class="sidebar-link {{ request()->is('modules*') ? 'active' : '' }}">
            <i class="bi bi-grid"></i>
            <span class="link-text">Module Permission</span>
        </a>

        <a href="{{ route('global.superadmins') }}"
            class="sidebar-link {{ request()->is('global/superadmins*') ? 'active' : '' }}">
            <i class="bi bi-people"></i>
            <span class="link-text">System Admins</span>
        </a>

        {{-- ─── CASE 2: COMPANY VIEW (Simulation OR Normal User) ─── --}}
        @else

        {{-- SPECIAL EXIT BUTTON: Only for Global Admins while simulating --}}
        @if($isGlobalAdmin && $isSimulating)
        <div class="sidebar-section-label">
            <span class="label-line"></span>
            <span class="label-text text-primary">Simulation Active</span>
        </div>

        <a href="{{ route('global.exit_simulation') }}"
            class="sidebar-link mb-3 border border-primary border-opacity-25 bg-primary bg-opacity-10 rounded-3 mx-2"
            style="width: calc(100% - 20px);">
            <i class="bi bi-arrow-left-circle-fill text-primary"></i>
            <span class="link-text fw-bold text-primary">BACK TO GLOBAL</span>
        </a>
        <hr class="sidebar-divider opacity-50">
        @endif

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

        {{-- BYPASS: Track Map --}}
        @if(($features['track'] ?? false) || $isGlobalAdmin)
        <a href="/patrol/maps"
            class="sidebar-link {{ request()->is('patrol/maps') ? 'active' : '' }}">
            <i class="bi bi-map"></i>
            <span class="link-text">KML / Patrol Map</span>
        </a>
        @endif

        {{-- BYPASS: Attendance --}}
        @if(($features['attendance'] ?? false) || $isGlobalAdmin)
        <div class="sidebar-item">
            <a class="sidebar-link d-flex align-items-center justify-content-between"
                data-bs-toggle="collapse" href="#attendanceMenu">
                <div class="d-flex align-items-center">
                    <i class="bi bi-calendar-check fs-5 me-2"></i>
                    <span class="link-text">Attendance</span>
                </div>
                <i class="bi bi-chevron-down sidebar-arrow"></i>
            </a>
            <div class="collapse {{ request()->is('attendance/*') ? 'show' : '' }}" id="attendanceMenu">
                <ul class="list-unstyled ms-4 mt-2 small">
                    <li><a href="/attendance/explorer" class="sidebar-link {{ request()->is('attendance/explorer') ? 'active' : '' }}">Overview</a></li>
                    <li><a href="/attendance/logs" class="sidebar-link {{ request()->is('attendance/logs') ? 'active' : '' }}">Logs</a></li>
                    <li><a href="/attendance/requests" class="sidebar-link {{ request()->is('attendance/requests') ? 'active' : '' }}">Requests</a></li>
                </ul>
            </div>
        </div>
        @endif

        {{-- ─── SECTION: SYSTEM ─── --}}
        <div class="sidebar-section-label mt-3">
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

        <a href="{{ route('report-configs.dashboard') }}"
            class="sidebar-link {{ request()->is('report-configs*') ? 'active' : '' }}">
            <i class="bi bi-calendar-event"></i>
            <span class="link-text">Events</span>
        </a>

        {{-- ─── SECTION: PLANTATION ─── --}}
        {{-- BYPASS: Plantation --}}
        @if(($features['plantation'] ?? false) || $isGlobalAdmin)
        <div class="sidebar-section-label mt-3">
            <span class="label-line"></span>
            <span class="label-text">Plantation</span>
        </div>

        <a href="/plantation/dashboard"
            class="sidebar-link {{ request()->is('plantation/dashboard') ? 'active' : '' }}">
            <i class="bi bi-tree"></i>
            <span class="link-text">Dashboard</span>
        </a>
        @endif

        @endif
    </div>

    {{-- ─── USER FOOTER ─── --}}
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                {{-- isset() checks if the property exists AND isn't null --}}
                @if(isset($user->profile_photo) && $user->profile_photo)
                <img src="{{ asset('storage/' . $user->profile_photo) }}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                @else
                <i class="bi bi-person-circle fs-4"></i>
                @endif
            </div>

            <div class="sidebar-user-info">
                <strong class="text-truncate d-block" style="max-width: 120px;">
                    {{ auth()->user()->name ?? 'Guest' }}
                </strong>
                <small class="text-truncate d-block" style="max-width: 120px;">
                    {{ $isSimulating ? 'Simulating' : (auth()->user()->email ?? '') }}
                </small>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-user-logout" title="Logout">
                    <i class="bi bi-box-arrow-right text-danger"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@php
use App\Models\User;
use Illuminate\Support\Facades\Auth;

$user = Auth::user() ?? session('user');
$company = session('company');
$date = now()->format('Y-m-d');
$notificationsCount = 0;

$isForest = $company?->is_forest ?? false;
$roles = [
1 => $isForest ? 'DFO' : 'Superadmin',
2 => $isForest ? 'Ranger' : 'Supervisor',
7 => $isForest ? 'ACF' : 'Admin',
4 => 'Client',
];
@endphp

<nav class="navbar navbar-expand bg-body-tertiary shadow-sm px-3 py-2 border-bottom w-100"
    style="backdrop-filter: blur(12px); --bs-bg-opacity:.85; transition:background-color .3s ease;">

    {{-- Mobile Sidebar Toggle --}}
    <button class="btn border-0 me-2 d-lg-none" id="sidebarToggle" type="button" style="z-index:1100;">
        <i class="bi bi-list fs-4"></i>
    </button>

    <div class="d-flex align-items-center">
        <a class="navbar-brand fw-semibold text-primary d-flex align-items-center me-3"
            href="{{ url('/home') }}">

            <img src="{{ asset('images/logo1.png') }}"
                class="img-fluid d-lg-none"
                style="height:32px">

            <span class="d-none d-md-block fw-bold text-body ms-2"
                style="font-size:1.1rem;">
                {{ $company?->name ?? 'Patrol Analytics' }}
            </span>

        </a>
    </div>

    <ul class="navbar-nav ms-auto align-items-center flex-row gap-3">

        {{-- Theme Toggle --}}
        <li class="nav-item">
            <button
                class="btn btn-link nav-link px-2 d-flex align-items-center rounded-circle border bg-body"
                id="themeToggle"
                type="button"
                aria-label="Toggle theme"
                style="width:38px;height:38px;justify-content:center;">

                <i class="bi fs-5" id="themeIcon"></i>

            </button>
        </li>

        {{-- Notifications --}}
        <li class="nav-item position-relative">

            <a href="#"
                class="btn btn-link nav-link px-2 d-flex align-items-center rounded-circle border bg-body"
                style="width:38px;height:38px;justify-content:center;">

                <i class="bi bi-bell fs-5"></i>

                @if($notificationsCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                    style="font-size:.65rem;">
                    {{ $notificationsCount }}
                </span>
                @endif

            </a>

        </li>

        <div class="vr mx-1 opacity-25"></div>

        {{-- Profile --}}
        <li class="nav-item dropdown">

            <a class="nav-link dropdown-toggle d-flex align-items-center p-0 rounded-pill pe-2"
                data-bs-toggle="dropdown"
                style="border:1px solid var(--bs-border-color);background:var(--bs-body-bg);">

                @if(isset($user->profile_pic) && $user->profile_pic)
                <img src="{{ $user->profile_pic }}"
                    class="rounded-circle object-fit-cover shadow-sm ms-n1 my-n1"
                    width="38" height="38">
                @else
                <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center shadow-sm ms-n1 my-n1"
                    style="width:38px;height:38px;font-weight:bold;">
                    {{ substr($user->name ?? 'U', 0, 1) }}
                </div>
                @endif

                <div class="d-none d-sm-block ms-2 lh-sm text-start pe-2 py-1">
                    <span class="d-block fw-semibold text-body"
                        style="font-size:.85rem;">
                        {{ $user?->name ?? 'Guest' }}
                    </span>

                    <small class="text-body-secondary"
                        style="font-size:.70rem;">
                        {{ $roles[$user?->role_id ?? 0] ?? 'User' }}
                    </small>
                </div>

            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 rounded-3"
                style="min-width:16rem;">

                <li class="px-3 py-2 border-bottom mb-2 bg-body-tertiary">
                    <div class="fw-bold text-body">{{ $user?->name }}</div>
                    <div class="small text-body-secondary">{{ $company?->name }}</div>
                </li>

                <li>
                    <a class="dropdown-item py-2 d-flex align-items-center"
                        href="{{ route('profile',$user?->id ?? 0) }}">
                        <i class="bi bi-person me-2 fs-5 text-primary"></i>
                        My Profile
                    </a>
                </li>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="dropdown-item py-2 d-flex align-items-center text-danger">
                            <i class="bi bi-box-arrow-right me-2 fs-5"></i>
                            Logout
                        </button>
                    </form>
                </li>

            </ul>

        </li>

    </ul>

</nav>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        const html = document.documentElement;
        const toggle = document.getElementById('themeToggle');
        const icon = document.getElementById('themeIcon');

        const savedTheme = localStorage.getItem('theme') || 'light';

        html.setAttribute('data-bs-theme', savedTheme);

        const setIcon = (theme) => {

            icon.classList.remove('bi-moon-stars', 'bi-sun');

            if (theme === 'dark') {
                icon.classList.add('bi-sun');
            } else {
                icon.classList.add('bi-moon-stars');
            }

        };

        setIcon(savedTheme);

        toggle.addEventListener('click', () => {

            const current = html.getAttribute('data-bs-theme');

            const newTheme = current === 'dark' ? 'light' : 'dark';

            html.setAttribute('data-bs-theme', newTheme);

            localStorage.setItem('theme', newTheme);

            setIcon(newTheme);

            window.dispatchEvent(new Event('themeChanged'));

        });

    });
</script>

@php
    // 1. Fetch beats directly to avoid variable hijacking
    $safeCompanyId = session('user')->company_id ?? auth()->user()->company_id ?? 46;

    if (auth()->check() && auth()->user()->role_id == 8 && session()->has('simulated_company_id')) {
        $safeCompanyId = session('simulated_company_id');
    }

    $safeBeats = \Illuminate\Support\Facades\DB::table('site_details')
        ->where('company_id', $safeCompanyId)
        ->select('id', 'name', 'client_id')
        ->get();
@endphp

<style>
    .mini-view-toggle {
        display: flex; background: var(--bg-body, #f8f9fa);
        border: 1px solid var(--border-color, #dee2e6);
        border-radius: 8px; padding: 2px;
    }
    .mini-view-toggle button {
        background: transparent; border: none; color: var(--text-muted, #6c757d);
        padding: 6px 14px; font-size: 0.8rem; font-weight: 600;
        border-radius: 6px; cursor: pointer; transition: all 0.2s ease;
        display: flex; align-items: center; gap: 6px;
    }
    .mini-view-toggle button.active {
        background: var(--bg-card, #ffffff);
        color: var(--sapphire-primary, #0d6efd);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    .custom-input {
        background-color: var(--bg-body, #ffffff); color: var(--text-main, #212529);
        border: 1px solid var(--border-color, #dee2e6); border-radius: 8px;
        padding: 6px 12px; font-size: 0.8rem; font-weight: 500; outline: none;
    }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center w-100 mb-4 gap-3">
    {{-- Left: View Toggle --}}
    <div class="mini-view-toggle shadow-sm flex-shrink-0">
        <button id="view-overall" onclick="setViewMode('overall')"
            class="{{ request('view', 'overall') == 'overall' ? 'active' : '' }}">
            <i class="bi bi-map"></i> Overall View (Map)
        </button>
        <button id="view-analytical" onclick="setViewMode('analytical')"
            class="{{ request('view') == 'analytical' ? 'active' : '' }}">
            <i class="bi bi-graph-up"></i> Analytical View
        </button>
    </div>

    {{-- Right: Filters --}}
    <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
        <select id="range_id" class="custom-input" style="min-width: 140px;" onchange="filterBeats()">
            <option value="">All Ranges</option>
            @foreach ($ranges ?? [] as $id => $name)
                <option value="{{ $id }}" {{ request('range_id') == $id ? 'selected' : '' }}>
                    {{ $name }}
                </option>
            @endforeach
        </select>

        <select id="site_id" class="custom-input" style="min-width: 140px;" onchange="forceSyncDashboard()">
            <option value="">All Beats</option>
        </select>

        <select id="date_filter" class="custom-input" style="min-width: 130px;" onchange="toggleCustomDates()">
            <option value="overall" {{ request('date_filter') == 'overall' ? 'selected' : '' }}>Overall Stats</option>
            <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today</option>
            <option value="week" {{ request('date_filter') == 'week' ? 'selected' : '' }}>This Week</option>
            <option value="month" {{ request('date_filter') == 'month' ? 'selected' : '' }}>This Month</option>
            <option value="custom" {{ request('date_filter') == 'custom' ? 'selected' : '' }}>Custom Range</option>
        </select>

        <div id="custom-date-inputs" class="custom-date-container {{ request('date_filter') == 'custom' ? 'd-flex' : 'd-none' }} align-items-center gap-2">
            <input type="date" id="from_date" class="custom-input" value="{{ request('from_date') }}">
            <span class="text-muted small">to</span>
            <input type="date" id="to_date" class="custom-input" value="{{ request('to_date') }}">
        </div>

        <button type="button" onclick="forceSyncDashboard()" class="btn btn-primary d-flex align-items-center gap-2"
            style="background-color: #0d6efd; border: none; font-size: 0.8rem; font-weight: 600; padding: 6px 14px; border-radius: 8px;">
            <i class="bi bi-arrow-repeat"></i> Sync
        </button>

        <button type="button" onclick="resetFilters()" class="btn btn-light border d-flex align-items-center gap-2"
            style="font-size: 0.8rem; font-weight: 600; padding: 6px 14px; border-radius: 8px;">
            <i class="bi bi-x-circle text-danger"></i> Reset
        </button>
    </div>
</div>

<script>
    const allBeats = @json($safeBeats);
    const currentSelectedBeat = "{{ request('site_id') }}";

    function filterBeats() {
        const rSelect = document.getElementById('range_id');
        const bSelect = document.getElementById('site_id');
        if (!rSelect || !bSelect) return;

        const rid = rSelect.value;
        bSelect.innerHTML = '<option value="">All Beats</option>';

        const filtered = rid ? allBeats.filter(b => String(b.client_id) === String(rid)) : allBeats;

        filtered.forEach(beat => {
            const opt = document.createElement('option');
            opt.value = beat.id;
            opt.textContent = beat.name || 'Unnamed Beat';
            if (String(beat.id) === String(currentSelectedBeat)) opt.selected = true;
            bSelect.appendChild(opt);
        });
    }

    function forceSyncDashboard() {
        const url = new URL(window.location.href.split('#')[0]);
        const rid = document.getElementById('range_id')?.value || '';
        const sid = document.getElementById('site_id')?.value || '';
        const df = document.getElementById('date_filter')?.value || 'overall';
        const isAnalytical = document.getElementById('view-analytical')?.classList.contains('active');

        url.searchParams.set('range_id', rid);
        url.searchParams.set('site_id', sid);
        url.searchParams.set('date_filter', df);
        url.searchParams.set('view', isAnalytical ? 'analytical' : 'overall');

        if (df === 'custom') {
            const f = document.getElementById('from_date')?.value;
            const t = document.getElementById('to_date')?.value;
            if (f) url.searchParams.set('from_date', f);
            if (t) url.searchParams.set('to_date', t);
        }
        window.location.href = url.toString();
    }

    function resetFilters() {
        const url = new URL(window.location.href.split('?')[0]);
        const isAnalytical = document.getElementById('view-analytical')?.classList.contains('active');
        if (isAnalytical) url.searchParams.set('view', 'analytical');
        window.location.href = url.toString();
    }

    function toggleCustomDates() {
        const val = document.getElementById('date_filter').value;
        const cont = document.getElementById('custom-date-inputs');
        if (val === 'custom') {
            cont.classList.replace('d-none', 'd-flex');
        } else {
            cont.classList.replace('d-flex', 'd-none');
            forceSyncDashboard();
        }
    }

    document.addEventListener('DOMContentLoaded', filterBeats);
</script>

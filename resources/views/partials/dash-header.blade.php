{{-- @php
dd($dropdownBeats , "dpr beats");
@endphp --}}

<style>
    .mini-view-toggle {
        display: flex;
        background: var(--bg-body, #f8f9fa);
        border: 1px solid var(--border-color, #dee2e6);
        border-radius: 8px;
        padding: 2px;
    }

    .mini-view-toggle button {
        background: transparent;
        border: none;
        color: var(--text-muted, #6c757d);
        padding: 6px 14px;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .mini-view-toggle button.active {
        background: var(--bg-card, #ffffff);
        color: var(--sapphire-primary, #0d6efd);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .custom-input {
        background-color: var(--bg-body, #ffffff);
        color: var(--text-main, #212529);
        border: 1px solid var(--border-color, #dee2e6);
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 0.8rem;
        font-weight: 500;
        outline: none;
    }
</style>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-center w-100 mb-4"
    id="dynamic-header-top"
    style="margin-top: -15px; margin-bottom: 0px">

    {{-- Left Side: View Toggle --}}
    <div class="d-flex align-items-center" id="dynamic-header-bottom">
        <div class="mini-view-toggle shadow-sm" id="view-toggle-buttons">
            <button id="view-overall" onclick="setViewMode('overall')"
                class="{{ request('view', 'overall') == 'overall' ? 'active' : '' }}">
                <i class="bi bi-map"></i> Overall View (Map)
            </button>
            <button id="view-analytical" onclick="setViewMode('analytical')"
                class="{{ request('view') == 'analytical' ? 'active' : '' }}">
                <i class="bi bi-graph-up"></i> Analytical View
            </button>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-md-end" id="global-filters-container">

        {{-- Range Dropdown --}}
        {{-- Range Dropdown --}}
        <select id="range_id" class="custom-input" style="width: auto; min-width: 150px;" onchange="filterBeats()">
            <option value="">All Ranges</option>
            {{-- 🔥 Use $dropdownRanges --}}
            @foreach ($dropdownRanges ?? [] as $id => $name)
                <option value="{{ $id }}"
                    {{ (string) request('range_id') == (string) $id ? 'selected' : '' }}>
                    {{ $name }}
                </option>
            @endforeach
        </select>

        {{-- Beat Dropdown (Removed the broken onchange event) --}}
        <select id="site_id" class="custom-input" style="width: auto; min-width: 150px;">
            <option value="">All Beats</option>
        </select>
        {{-- Date Filters --}}
        @php
            $currentDateFilter = request('date_filter', 'month'); // 🔥 Defaults to month in UI
        @endphp
        <select id="date_filter" class="custom-input" style="width: auto; min-width: 130px;"
            onchange="toggleCustomDates()">
            <option value="overall" {{ $currentDateFilter == 'overall' ? 'selected' : '' }}>Overall Stats</option>
            <option value="today" {{ $currentDateFilter == 'today' ? 'selected' : '' }}>Today</option>
            <option value="week" {{ $currentDateFilter == 'week' ? 'selected' : '' }}>This Week</option>
            <option value="month" {{ $currentDateFilter == 'month' ? 'selected' : '' }}>This Month</option>
            <option value="custom" {{ $currentDateFilter == 'custom' ? 'selected' : '' }}>Custom Range</option>
        </select>

        <div id="custom-date-inputs"
            class="custom-date-container {{ $currentDateFilter == 'custom' ? 'd-flex' : 'd-none' }} align-items-center gap-2">
            <input type="date" id="from_date" class="custom-input" value="{{ request('from_date') }}">
            <span class="text-muted small">to</span>
            <input type="date" id="to_date" class="custom-input" title="To Date" value="{{ request('to_date') }}">
        </div>

        {{-- Buttons --}}
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
    const allBeats = @json($beats ?? []);
    const currentSelectedBeat = "{{ request('site_id') }}";

    function filterBeats() {
        const rangeSelect = document.getElementById('range_id');
        const beatSelect = document.getElementById('site_id');
        if (!rangeSelect || !beatSelect) return;

        const selectedRangeId = rangeSelect.value;
        beatSelect.innerHTML = '<option value="">All Beats</option>';

        const filteredBeats = selectedRangeId ?
            allBeats.filter(beat => beat.client_id == selectedRangeId) :
            allBeats;

        filteredBeats.forEach(beat => {
            const option = document.createElement('option');
            option.value = beat.id;
            option.textContent = beat.name || 'Unnamed Beat';
            if (beat.id == currentSelectedBeat) option.selected = true;
            beatSelect.appendChild(option);
        });
    }

    function toggleCustomDates() {
        const filter = document.getElementById('date_filter').value;
        const customContainer = document.getElementById('custom-date-inputs');

        if (filter === 'custom') {
            customContainer.classList.remove('d-none');
            customContainer.classList.add('d-flex');
        } else {
            customContainer.classList.remove('d-flex');
            customContainer.classList.add('d-none');
            document.getElementById('from_date').value = '';
            document.getElementById('to_date').value = '';
            // Auto-sync when changing standard dates
            forceSyncDashboard();
        }
    }

    function forceSyncDashboard() {
        console.log("--- Sync Button Clicked ---");

        let url = new URL(window.location.href.split('#')[0]);

        let rangeId = document.getElementById('range_id')?.value || '';
        let siteId = document.getElementById('site_id')?.value || '';
        let dateFilter = document.getElementById('date_filter')?.value || 'overall';

        // 🔥 THE FIX: Grab the active view mode and attach it!
        let activeView = window.viewMode || new URLSearchParams(window.location.search).get('view') || 'overall';
        url.searchParams.set('view', activeView);

        if (rangeId) url.searchParams.set('range_id', rangeId);
        else url.searchParams.delete('range_id');

        if (siteId) url.searchParams.set('site_id', siteId);
        else url.searchParams.delete('site_id');

        url.searchParams.set('date_filter', dateFilter);

        if (dateFilter === 'custom') {
            let fromDate = document.getElementById('from_date')?.value;
            let toDate = document.getElementById('to_date')?.value;

            if (fromDate) url.searchParams.set('from_date', fromDate);
            else url.searchParams.delete('from_date');

            if (toDate) url.searchParams.set('to_date', toDate);
            else url.searchParams.delete('to_date');
        } else {
            url.searchParams.delete('from_date');
            url.searchParams.delete('to_date');
        }

        console.log("Final URL:", url.toString());
        window.location.href = url.toString();
    }

  function resetFilters() {
        // 1. Look at the actual button on the screen to see what is currently active
        const analyticalBtn = document.getElementById('view-analytical');
        const isAnalytical = analyticalBtn && analyticalBtn.classList.contains('active');
        const targetView = isAnalytical ? 'analytical' : 'overall';

        // 2. Get the base URL and strip away all the messy date/range filters
        let url = new URL(window.location.href.split('?')[0]);

        // 3. Put ONLY the view parameter back
        url.searchParams.set('view', targetView);

        // 4. Reload the page with clean filters but the correct tab
        window.location.href = url.toString();
    }

    // Run this on page load to initialize everything correctly
  document.addEventListener('DOMContentLoaded', () => {
        // 1. Check the URL the moment the page loads
        const urlParams = new URLSearchParams(window.location.search);
        const currentView = urlParams.get('view') || 'overall';

        // 2. FORCE the page to switch to that view immediately!
        if (typeof setViewMode === 'function') {
            setViewMode(currentView);
        }

        // 3. Load your beat dropdown
        filterBeats();
    });
</script>

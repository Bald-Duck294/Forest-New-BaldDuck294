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

<div class="d-flex flex-column flex-md-row justify-content-between align-items-center w-100 mb-4 gap-3"
    id="dynamic-header-top">

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
    const allBeats = @json($dropdownBeats);
    const currentSelectedBeat = "{{ request('site_id') }}";

    function filterBeats() {
        const rangeSelect = document.getElementById('range_id');
        const beatSelect = document.getElementById('site_id');
        if (!rangeSelect || !beatSelect) return;

        const selectedRangeId = rangeSelect.value;

        // Wipe the dropdown clean
        beatSelect.innerHTML = '<option value="">All Beats</option>';

        // 🔥 FIX: Only show beats that belong to the selected range. 
        // If no range is selected, show ALL beats.
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
        const fromDateInput = document.getElementById('from_date');
        const toDateInput = document.getElementById('to_date');

        if (filter === 'custom') {
            customContainer.classList.remove('d-none');
            customContainer.classList.add('d-flex');

            // 🔥 SMART DEFAULT: If empty, set to exactly 1 month back
            if (!fromDateInput.value || !toDateInput.value) {
                let today = new Date();
                let lastMonth = new Date();
                lastMonth.setMonth(today.getMonth() - 1);

                toDateInput.value = today.toISOString().split('T')[0];
                fromDateInput.value = lastMonth.toISOString().split('T')[0];
            }
        } else {
            customContainer.classList.remove('d-flex');
            customContainer.classList.add('d-none');
            // 🔥 CLEAR: Wipes the custom dates if they switch back to "This Month"
            fromDateInput.value = '';
            toDateInput.value = '';
            forceSyncDashboard();
        }
    }

    function forceSyncDashboard() {
        let url = new URL(window.location.href.split('#')[0]);

        let rangeId = document.getElementById('range_id')?.value || '';
        let siteId = document.getElementById('site_id')?.value || '';
        let dateFilter = document.getElementById('date_filter')?.value || 'month';

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

        window.location.href = url.toString();
    }

    function resetFilters() {
        let url = new URL(window.location.href.split('?')[0]);
        if (new URLSearchParams(window.location.search).has('view')) {
            url.searchParams.set('view', new URLSearchParams(window.location.search).get('view'));
        }
        window.location.href = url.toString();
    }

    document.addEventListener('DOMContentLoaded', () => {
        filterBeats();
    });
</script>
{{--
<script>
    const allBeats = @json($beats ?? []);
    const currentSelectedBeat = "{{ request('site_id') }}";

    function toggleCustomDates() {
        const filter = document.getElementById('date_filter').value;
        const customContainer = document.getElementById('custom-date-inputs');

        if (filter === 'custom') {
            customContainer.classList.remove('d-none');
        } else {
            customContainer.classList.add('d-none');
            // Clear out the dates when hiding them
            document.getElementById('from_date').value = '';
            document.getElementById('to_date').value = '';
        }

        // If they switch back to week/month/today, auto-refresh to apply it immediately
        if (filter !== 'custom') {
            refreshData();
        }
    }

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
        } else {
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

        url.searchParams.set('range_id', rangeId);
        url.searchParams.set('site_id', siteId);
        url.searchParams.set('date_filter', dateFilter);

        if (dateFilter === 'custom') {
            let fromDate = document.getElementById('from_date')?.value;
            let toDate = document.getElementById('to_date')?.value;

            if (fromDate) url.searchParams.set('from_date', fromDate);
            else url.searchParams.delete('from_date');

            if (toDate) url.searchParams.set('to_date', toDate);
            else url.searchParams.delete('to_date');
        }

        console.log("Final URL:", url.toString());

        // 🛑 LEAVE THIS COMMENTED OUT UNTIL YOU SEE THE ALERT 🛑
        window.location.href = url.toString();
    }

    function resetFilters() {
        let url = new URL(window.location.href.split('?')[0]); // Strip all query params

        // Preserve the view mode if they are in analytical mode
        if (new URLSearchParams(window.location.search).has('view')) {
            url.searchParams.set('view', new URLSearchParams(window.location.search).get('view'));
        }

        window.location.href = url.toString();
    }

    // Run this on page load to initialize the beat dropdown correctly
    document.addEventListener('DOMContentLoaded', () => {
        filterBeats();
    });
</script> --}}

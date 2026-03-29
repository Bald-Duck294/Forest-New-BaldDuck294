<style>
    /* Mini View Toggle inside Filters */
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
        transition: border-color 0.2s ease;
    }

    .custom-input:focus {
        border-color: var(--sapphire-primary, #0d6efd);
    }
</style>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-center w-100 mb-2 gap-3" id="dynamic-header-top">

    {{-- Top Row: Title & Filters (Default Overall View Layout) --}}
    {{-- Top Row: Title & Filters (Default Overall View Layout) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start w-100"
        id="dynamic-header-top">

        {{-- The Page Title Block --}}
        <div id="page-title-block" class="{{ request('view') == 'analytical' ? 'd-none' : '' }}">
            <h3 class="fw-bold mb-1" style="color: var(--text-main);">Protection Analytics</h3>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Real-time forest monitoring and incident tracking.</p>
        </div>
    </div>

    {{-- Right Side: Global Filters --}}
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-md-end" id="global-filters-container">

        {{-- The Global Filters Container --}}
        <div class="d-flex flex-wrap gap-2 align-items-center" id="global-filters-container">

            <select id="range_id" class="custom-input" style="width: auto; min-width: 130px;" onchange="filterBeats()">
                <option value="">All Ranges</option>
                @foreach ($ranges ?? [] as $id => $name)
                    <option value="{{ $id }}"
                        {{ (string) request('range_id') == (string) $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>

            <select id="site_id" class="custom-input" style="width: auto; min-width: 130px;" onchange="refreshData()">
                <option value="">All Beats</option>
            </select>

            <select id="date_filter" class="custom-input" style="width: auto; min-width: 130px;"
                onchange="toggleCustomDates()">
                <option value="overall" {{ request('date_filter') == 'overall' ? 'selected' : '' }}>Overall Stats
                </option>
                <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today</option>
                <option value="week" {{ request('date_filter') == 'week' ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ request('date_filter') == 'month' ? 'selected' : '' }}>This Month</option>
                <option value="custom" {{ request('date_filter') == 'custom' ? 'selected' : '' }}>Custom Range</option>
            </select>

            {{-- Custom Date Inputs (Hidden by default unless 'custom' is selected) --}}
            <div id="custom-date-inputs"
                class="custom-date-container {{ request('date_filter') == 'custom' ? '' : 'd-none' }}">
                <input type="date" id="from_date" class="custom-input" title="From Date"
                    value="{{ request('from_date') }}">
                <span class="text-muted small">to</span>
                <input type="date" id="to_date" class="custom-input" title="To Date"
                    value="{{ request('to_date') }}">
            </div>

            <button type="button" onclick="refreshData()" class="btn btn-primary d-flex align-items-center gap-2"
                style="background-color: var(--sapphire-primary); border: none; font-size: 0.8rem; font-weight: 600; padding: 6px 14px; border-radius: 8px;">
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

    function refreshData() {
        // Grab the current URL without any hashes
        let url = new URL(window.location.href.split('#')[0]);

        // Safely grab element values
        let rangeId = document.getElementById('range_id')?.value || '';
        let siteId = document.getElementById('site_id')?.value || '';
        let dateFilter = document.getElementById('date_filter')?.value || 'overall';

        // Set standard filters
        url.searchParams.set('range_id', rangeId);
        url.searchParams.set('site_id', siteId);
        url.searchParams.set('date_filter', dateFilter);

        // Handle Custom Dates strictly
        if (dateFilter === 'custom') {
            let fromDate = document.getElementById('from_date')?.value;
            let toDate = document.getElementById('to_date')?.value;

            // Apply or remove based on user input
            if (fromDate) {
                url.searchParams.set('from_date', fromDate);
            } else {
                url.searchParams.delete('from_date');
            }

            if (toDate) {
                url.searchParams.set('to_date', toDate);
            } else {
                url.searchParams.delete('to_date');
            }
        } else {
            // Strip custom dates from URL if we are using "Today/Week/Month"
            url.searchParams.delete('from_date');
            url.searchParams.delete('to_date');
        }

        // Fire the redirect to sync the data!
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
</script>

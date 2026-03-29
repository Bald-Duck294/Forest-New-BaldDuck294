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

    /* This active class gives it the "button" look */
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

<div class="d-flex flex-column flex-md-row justify-content-between align-items-center w-100 mb-4 gap-3" id="dynamic-header-top">

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

    {{-- Right Side: Global Filters --}}
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-md-end" id="global-filters-container">

        <select id="range_id" class="custom-input" style="width: auto; min-width: 150px;" onchange="filterBeats()">
            <option value="">All Ranges</option>
            @foreach ($ranges ?? [] as $id => $name)
            <option value="{{ $id }}"
                {{ (string) request('range_id') == (string) $id ? 'selected' : '' }}>
                {{ $name }}
            </option>
            @endforeach
        </select>

        <select id="site_id" class="custom-input" style="width: auto; min-width: 150px;" onchange="refreshData()">
            <option value="">All Beats</option>
        </select>

        <select id="date_filter" class="custom-input" style="width: auto; min-width: 150px;"
            onchange="refreshData()">
            <option value="overall" {{ request('date_filter') == 'overall' ? 'selected' : '' }}>Overall Stats</option>
            <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today</option>
            <option value="week" {{ request('date_filter') == 'week' ? 'selected' : '' }}>This Week</option>
            <option value="month" {{ request('date_filter') == 'month' ? 'selected' : '' }}>This Month</option>
        </select>

        <button onclick="refreshData()" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm"
            style="background-color: var(--sapphire-primary, #0d6efd); border: none; font-size: 0.8rem; font-weight: 600; padding: 6px 14px; border-radius: 8px;">
            <i class="bi bi-arrow-repeat"></i> Sync
        </button>

        <button onclick="resetFilters()" class="btn btn-light border d-flex align-items-center gap-2 shadow-sm"
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

    document.addEventListener('DOMContentLoaded', () => filterBeats());
</script>
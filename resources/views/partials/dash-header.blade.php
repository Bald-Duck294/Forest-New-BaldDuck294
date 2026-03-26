<style>
    /* Mini View Toggle inside Filters */
    .mini-view-toggle {
        display: flex;
        background: var(--bg-body);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 2px;
    }

    .mini-view-toggle button {
        background: transparent;
        border: none;
        color: var(--text-muted);
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
        background: var(--bg-card);
        color: var(--sapphire-primary);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .custom-input {
        background-color: var(--bg-body);
        color: var(--text-main);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 0.8rem;
        font-weight: 500;
        outline: none;
        transition: border-color 0.2s ease;
    }

    .custom-input:focus {
        border-color: var(--sapphire-primary);
    }
</style>

<div class="d-flex flex-column mb-4 gap-3">

    {{-- Top Row: Title & Filters (Default Overall View Layout) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start w-100"
        id="dynamic-header-top">

        {{-- The Page Title Block --}}
        <div id="page-title-block" class="{{ request('view') == 'analytical' ? 'd-none' : '' }}">
            <h3 class="fw-bold mb-1" style="color: var(--text-main);">Protection Analytics</h3>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Real-time forest monitoring and incident tracking.</p>
        </div>

        {{-- The Global Filters Container --}}
        <div class="d-flex flex-wrap gap-2 align-items-center" id="global-filters-container">
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
                <option value="overall" {{ request('date_filter') == 'overall' ? 'selected' : '' }}>Overall Stats
                </option>
                <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today</option>
                <option value="week" {{ request('date_filter') == 'week' ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ request('date_filter') == 'month' ? 'selected' : '' }}>This Month</option>
            </select>

            <button onclick="refreshData()" class="btn btn-primary d-flex align-items-center gap-2"
                style="background-color: var(--sapphire-primary); border: none; font-size: 0.8rem; font-weight: 600; padding: 6px 14px; border-radius: 8px;">
                <i class="bi bi-arrow-repeat"></i> Sync
            </button>

            <button onclick="resetFilters()" class="btn btn-light border d-flex align-items-center gap-2"
                style="font-size: 0.8rem; font-weight: 600; padding: 6px 14px; border-radius: 8px;">
                <i class="bi bi-x-circle text-danger"></i> Reset
            </button>
        </div>
    </div>

    {{-- Bottom Row: View Toggle --}}
    <div class="d-flex align-items-center {{ request('view') == 'analytical' ? 'justify-content-between w-100' : '' }}"
        id="dynamic-header-bottom">
        <div class="mini-view-toggle shadow-sm" id="view-toggle-buttons">

            {{-- 🔥 FIX: The class checks request('view') to apply 'active' on page load --}}
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

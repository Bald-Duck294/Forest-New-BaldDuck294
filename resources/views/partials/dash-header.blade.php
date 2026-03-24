<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1" style="color: var(--text-main);">Protection Analytics</h4>
        <p class="mb-0" style="color: var(--text-muted); font-size: 0.85rem;">Real-time forest monitoring and incident
            tracking.</p>
    </div>

    <div class="d-flex flex-wrap gap-2">
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
            {{-- Populated by JS filterBeats() --}}
        </select>

        <select id="date_filter" class="custom-input" style="width: auto; min-width: 150px;" onchange="refreshData()">
            <option value="overall" {{ request('date_filter') == 'overall' ? 'selected' : '' }}>Overall Stats</option>
            <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Today</option>
            <option value="week" {{ request('date_filter') == 'week' ? 'selected' : '' }}>This Week</option>
            <option value="month" {{ request('date_filter') == 'month' ? 'selected' : '' }}>This Month</option>
        </select>

        <button onclick="refreshData()" class="btn-sapphire" title="Sync Data">
            <i class="bi bi-arrow-repeat"></i> Sync
        </button>

        <button onclick="resetFilters()" class="btn-soft-danger border" title="Reset Filters"
            style="padding: 0.5rem 1rem;">
            <i class="bi bi-x-circle"></i> Reset
        </button>
    </div>
</div>

<script>
    // Store all beats in a JavaScript variable passed directly from Laravel
    const allBeats = @json($beats ?? []);

    // Remember the currently selected beat from the URL (if any)
    const currentSelectedBeat = "{{ request('site_id') }}";

    function filterBeats() {
        const rangeSelect = document.getElementById('range_id');
        const beatSelect = document.getElementById('site_id');
        const selectedRangeId = rangeSelect.value;

        // Clear out the current Beats dropdown
        beatSelect.innerHTML = '<option value="">All Beats</option>';

        // Filter the beats. If a range is selected, only show beats belonging to that range (client_id)
        // If "All Ranges" is selected, show all beats.
        const filteredBeats = selectedRangeId ?
            allBeats.filter(beat => beat.client_id == selectedRangeId) :
            allBeats;

        // Populate the Beats dropdown with the filtered results
        filteredBeats.forEach(beat => {
            const option = document.createElement('option');
            option.value = beat.id;
            option.textContent = beat.name || 'Unnamed Beat';

            // If this beat was the one the user previously searched for, keep it selected
            if (beat.id == currentSelectedBeat) {
                option.selected = true;
            }

            beatSelect.appendChild(option);
        });
    }

    // Run this function once when the page loads so the Beats dropdown is correctly populated 
    // based on whatever Range is currently selected in the URL
    document.addEventListener('DOMContentLoaded', () => {
        filterBeats();
    });
</script>

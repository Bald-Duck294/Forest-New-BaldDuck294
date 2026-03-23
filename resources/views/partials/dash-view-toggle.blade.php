<div class="d-flex mb-4">
    <div class="view-toggle shadow-sm">
        <button id="view-overall" onclick="setViewMode('overall')" 
            class="view-toggle-btn {{ request('view', 'overall') == 'overall' ? 'active' : '' }}">
            <i class="bi bi-map"></i> Overall View (Map)
        </button>
        <button id="view-analytical" onclick="setViewMode('analytical')" 
            class="view-toggle-btn {{ request('view') == 'analytical' ? 'active' : '' }}">
            <i class="bi bi-graph-up"></i> Analytical View
        </button>
    </div>
</div>

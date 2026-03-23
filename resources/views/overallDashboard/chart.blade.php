<div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100 p-4" style="border-radius: 15px;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0" style="color: var(--text-main);">Territory Overview</h6>
            <i class="bi bi-bar-chart-line text-success"></i>
        </div>

        <div class="d-flex bg-light rounded p-1 mb-3" style="border: 1px solid var(--border-color);">
            <button class="btn btn-sm w-50 active text-success fw-bold bg-white shadow-sm border-0"
                onclick="updateOverallChart('criminal', this)" style="border-radius: 8px;">
                Criminal Activities
            </button>
            <button class="btn btn-sm w-50 text-muted bg-transparent border-0"
                onclick="updateOverallChart('events', this)" style="border-radius: 8px;">
                Events & Monitoring
            </button>
        </div>

        <div style="height: 330px; position: relative;">
            <canvas id="overall-summary-chart"></canvas>
        </div>
    </div>
</div>

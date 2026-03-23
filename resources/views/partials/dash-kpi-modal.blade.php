<div class="modal fade" id="kpiQuickViewModal" tabindex="-1" aria-labelledby="kpiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; background-color: var(--bg-card);">
            <div class="modal-header border-bottom border-slate-200 p-4">
                <div>
                    <h5 class="modal-title fw-bold mb-1" id="kpiModalLabel" style="color: var(--text-main);">Quick View
                    </h5>
                    <p class="text-muted mb-0" style="font-size: 0.85rem;">Latest 20 records. Click 'View All' for full
                        history and filters.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead style="position: sticky; top: 0; background-color: var(--bg-body); z-index: 1;">
                            <tr>
                                <th class="ps-4">ID / Code</th>
                                <th>Type / Name</th>
                                <th>Location / Info</th>
                                <th>Date</th>
                                <th class="text-end pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody id="kpiModalTableBody">
                            <tr>
                                <td colspan="5" class="text-center py-4">Loading data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top border-slate-200 p-3 bg-light">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <a href="#" id="viewAllDataBtn" class="btn btn-primary"
                    style="background-color: var(--sapphire-primary); border: none;">
                    View All Detailed Data <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

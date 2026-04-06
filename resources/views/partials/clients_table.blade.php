{{-- Table Body & Pagination Partial --}}
<div class="table-responsive">
    <table class="table dash-table align-middle">
        <thead>
            <tr>
                <th class="ps-4" style="width: 70px;">#</th>
                <th>
                    <a href="javascript:void(0)" onclick="handleSort('name')"
                        class="sort-link {{ request('sort') == 'name' ? 'active' : '' }}">

                        {{-- Dynamic Label + " Name" --}}
                        {{ get_label('label_client', 'Client') }} Name

                        <i class="bi {{ request('sort') == 'name' ? (request('dir') == 'asc' ? 'bi-sort-up' : 'bi-sort-down') : 'bi-arrow-down-up' }}"></i>
                    </a>
                </th>
                <th>
                    <a href="javascript:void(0)" onclick="handleSort('state')"
                        class="sort-link {{ request('sort') == 'state' ? 'active' : '' }}">
                        State
                        <i class="bi {{ request('sort') == 'state' ? (request('dir') == 'asc' ? 'bi-sort-up' : 'bi-sort-down') : 'bi-arrow-down-up' }}"></i>
                    </a>
                </th>
                <th>
                    <a href="javascript:void(0)" onclick="handleSort('city')"
                        class="sort-link {{ request('sort') == 'city' ? 'active' : '' }}">
                        City
                        <i class="bi {{ request('sort') == 'city' ? (request('dir') == 'asc' ? 'bi-sort-up' : 'bi-sort-down') : 'bi-arrow-down-up' }}"></i>
                    </a>
                </th>
                <th class="text-center" style="width: 120px;">
                    <a href="javascript:void(0)" onclick="handleSort('status')"
                        class="sort-link justify-content-center {{ request('sort') == 'status' ? 'active' : '' }}">
                        Status
                        <i class="bi {{ request('sort') == 'status' ? (request('dir') == 'asc' ? 'bi-sort-up' : 'bi-sort-down') : 'bi-arrow-down-up' }}"></i>
                    </a>
                </th>
                <th class="text-center pe-4" style="width: 160px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @if ($clients && count($clients) > 0)
            @php $sr = ($clients->currentPage() - 1) * $clients->perPage() + 1; @endphp
            @foreach ($clients as $row)
            <tr>
                <td class="ps-4 fw-semibold" style="color: var(--text-muted);">
                    {{ $sr++ }}
                </td>
                <td>
                    <a href="{{ route('clients.view', $row->id) }}" class="client-name-link">
                        {{ $row->name }}
                    </a>
                </td>
                <td style="color: var(--text-main);">
                    {{ $row->state ?: '-' }}
                </td>
                <td style="color: var(--text-main);">
                    {{ $row->city ?: '-' }}
                </td>
                <td class="text-center">
                    <div class="form-check form-switch custom-switch d-flex justify-content-center m-0">
                        <input class="form-check-input status-toggle shadow-sm" type="checkbox" role="switch"
                            id="statusSwitch{{ $row->id }}" {{ $row->isActive == 1 ? 'checked' : '' }}
                            onclick="toggleActive('{{ $row->id }}', this, {{ $row->isActive == 1 ? 'true' : 'false' }})">
                    </div>
                </td>
                <td class="text-center pe-4">
                    <div class="d-flex justify-content-center gap-1">
                        <a href="{{ route('sites.getsites', $row->id) }}" class="btn-icon-soft view"
                            title="View Sites">
                            <i class="bi bi-eye-fill"></i>
                        </a>
                        <a href="{{ route('clients.editClient', $row->id) }}" class="btn-icon-soft edit"
                            title="Edit Range">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <button type="button" onclick="deleteClient('{{ $row->id }}')"
                            class="btn-icon-soft delete" title="Delete Range">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
            @else
            <tr>
                <td colspan="6" class="text-center py-5">
                    <div class="py-4">
                        <i class="bi bi-buildings"
                            style="font-size: 3rem; color: var(--text-muted); opacity: 0.3;"></i>
                        <h5 class="fw-bold mt-3 mb-1" style="color: var(--text-main);">No ranges found</h5>
                        <p style="color: var(--text-muted); font-size: 0.9rem;">
                            @if (request('search'))
                            No results match your search for "<strong>{{ request('search') }}</strong>".
                            <a href="javascript:void(0)" onclick="clearSearch()"
                                style="color: var(--sapphire-primary); text-decoration: none;">Clear Search</a>.
                            @else
                            There are no items found here. <a href="{{ route('clients.create') }}"
                                style="color: var(--sapphire-primary); font-weight: 600; text-decoration: none;">Add
                                your first one</a>.
                            @endif
                        </p>
                    </div>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if ($clients && count($clients) > 0)
<div class="p-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3"
    style="border-top: 1px solid var(--border-color); background: var(--bg-body);">
    <small style="color: var(--text-muted); font-weight: 500;">
        Showing {{ $clients->firstItem() ?? 0 }} to {{ $clients->lastItem() ?? 0 }} of
        {{ $clients->total() ?? 0 }} entries
    </small>
    <div class="m-0 p-0 ajax-pagination">
        {{ $clients->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>
@endif
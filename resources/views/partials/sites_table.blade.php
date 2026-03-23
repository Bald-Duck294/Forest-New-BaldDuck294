{{-- Sites Table Partial --}}
<div class="table-responsive">
    <table class="table dash-table align-middle">
        <thead>
            <tr>
                <th class="ps-4" style="width: 60px;">#</th>
                <th>
                    <a href="javascript:void(0)" onclick="handleSort('name')"
                        class="sort-link {{ request('sort') == 'name' ? 'active' : '' }}">
                        {{ $label }} Name
                        <i
                            class="bi {{ request('sort') == 'name' ? (request('dir') == 'asc' ? 'bi-sort-up' : 'bi-sort-down') : 'bi-arrow-down-up' }}"></i>
                    </a>
                </th>
                <th>
                    <a href="javascript:void(0)" onclick="handleSort('client')"
                        class="sort-link {{ request('sort') == 'client' ? 'active' : '' }}">
                        Client
                        <i
                            class="bi {{ request('sort') == 'client' ? (request('dir') == 'asc' ? 'bi-sort-up' : 'bi-sort-down') : 'bi-arrow-down-up' }}"></i>
                    </a>
                </th>

                @if ($client_id == 'playBackSites')
                    <th>Action</th>
                @elseif ($client_id == 'daily-update')
                    <th>Daily Update</th>
                @else
                    <th>Management</th>
                @endif

                @if ($client_id != 'playBackSites' && $client_id != 'daily-update')
                    <th class="text-end pe-4" style="width: 140px;">Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @if (isset($Sites) && count($Sites) > 0)
                @php $sr = ($Sites->currentPage() - 1) * $Sites->perPage() + 1; @endphp

                @foreach ($Sites as $row)
                    <tr>
                        <td class="ps-4 fw-semibold" style="color: var(--text-muted);">{{ $sr++ }}</td>

                        <td>
                            @php $viewId = is_numeric($client_id) && $client_id != 0 ? $client_id : $row->client_id; @endphp
                            <a href="{{ route('sites.site_view', [$viewId, $row->id]) }}" class="site-name-link">
                                {{ ucfirst($row->name) }}
                            </a>
                        </td>

                        <td>{{ ucfirst($row->client_name ?? '—') }}</td>

                        <td>
                            @if ($client_id != 'playBackSites' && $client_id != 'daily-update')
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('clients.getshifts', [$row->client_id, $row->id]) }}"
                                        class="badge-soft">
                                        <i class="bi bi-clock" style="color: var(--sapphire-primary);"></i>
                                        Shifts
                                    </a>
                                    <a href="{{ route('clients.getclientgeofences', [$row->client_id, $row->id]) }}"
                                        class="badge-soft">
                                        <i class="bi bi-geo-alt" style="color: var(--sapphire-success);"></i>
                                        Geofence
                                    </a>
                                    <a href="{{ route('clients.getclientguards', [$row->client_id, $row->id]) }}"
                                        class="badge-soft">
                                        <i class="bi bi-person" style="color: var(--sapphire-warning);"></i>
                                        Employee
                                    </a>

                                    @php
                                        $features = session('features');
                                        $hasTour = $features ? array_search('tour', $features) : false;
                                    @endphp

                                    @if ($hasTour !== false)
                                        <a href="{{ route('clients.gettours', $row->id) }}" class="badge-soft">
                                            <i class="bi bi-signpost-split" style="color: var(--sapphire-danger);"></i>
                                            Tour
                                        </a>
                                    @endif
                                </div>
                            @elseif ($client_id == 'playBackSites')
                                <button type="button" class="btn-sapphire btn-sm"
                                    onclick="playBackOfGuards('{{ $row->id }}')">
                                    <i class="bi bi-play-fill"></i> Playback
                                </button>
                            @elseif ($client_id == 'daily-update')
                                <a href="{{ route('DailyUpdate', $row->id) }}" class="btn-sapphire-outline btn-sm">
                                    <i class="bi bi-eye"></i> View Updates
                                </a>
                            @endif
                        </td>

                        @if ($client_id != 'playBackSites' && $client_id != 'daily-update')
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('sites.site_view', [$row->client_id, $row->id]) }}"
                                        class="btn-icon-soft view" title="View">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>

                                    @if ($user->role_id != '4')
                                        <a href="{{ route('sites.site_edit', [$row->client_id, $row->id]) }}"
                                            class="btn-icon-soft edit" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <button class="btn-icon-soft delete"
                                            onclick="deleteSite('{{ $row->client_id }}','{{ $row->id }}')"
                                            title="Delete">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
            @else
                <tr>
                    @php $colSpan = ($client_id != 'playBackSites' && $client_id != 'daily-update') ? 5 : 4; @endphp
                    <td colspan="{{ $colSpan }}" class="text-center py-5">
                        <div class="py-4">
                            <i class="bi bi-geo-alt" style="font-size: 3rem; color: var(--text-muted); opacity: 0.4;"></i>
                            <h5 class="fw-bold mt-3 mb-1" style="color: var(--text-main);">No
                                {{ strtolower($label) }}s found</h5>
                            <p style="color: var(--text-muted); font-size: 0.9rem;">
                                @if (request('search'))
                                    No results match your search for "<strong>{{ request('search') }}</strong>".
                                    <a href="javascript:void(0)" onclick="clearSearch()"
                                        style="color: var(--sapphire-primary); text-decoration: none;">Clear Search</a>.
                                @else
                                    No {{ strtolower($label) }}s have been added for this client yet.
                                @endif
                            </p>

                            @if ($user->role_id != '4' && $client_id !== 'playBackSites')
                                <div class="mt-3">
                                    @php
                                        $createId = is_numeric($client_id) ? $client_id : 0;
                                    @endphp
                                    <a href="{{ route('sites.site_create', $createId) }}" class="btn-sapphire">
                                        <i class="bi bi-plus-lg"></i> Add {{ $label }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if (isset($Sites) && count($Sites) > 0)
    <div class="p-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3"
        style="border-top: 1px solid var(--border-color); background: var(--bg-body);">
        <small style="color: var(--text-muted);">
            Showing {{ $Sites->firstItem() ?? 0 }} to {{ $Sites->lastItem() ?? 0 }} of
            {{ $Sites->total() ?? 0 }} entries
        </small>
        <div class="m-0 p-0 ajax-pagination">
            {{ $Sites->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endif

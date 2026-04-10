@extends('layouts.app')

@section('title', 'Asset Inventory')

@section('content')

    <style>
        /* =========================================
               SAPPHIRE INDEX STYLES
            ========================================= */
        .dash-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        }

        .custom-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 700;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .custom-input {
            background-color: var(--bg-body);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.85rem;
            outline: none;
            transition: all 0.2s ease;
            width: 100%;
        }

        .custom-input:focus {
            border-color: var(--sapphire-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        html[data-bs-theme="dark"] .custom-input {
            color-scheme: dark;
        }

        /* Action Buttons */
        .btn-sapphire {
            background-color: var(--sapphire-primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            text-decoration: none;
        }

        .btn-sapphire:hover {
            background-color: #2563eb;
            color: white;
            transform: translateY(-1px);
        }

        .btn-sapphire-outline {
            background-color: transparent;
            color: var(--text-main);
            border: 1px solid var(--border-color);
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            text-decoration: none;
        }

        .btn-sapphire-outline:hover {
            background-color: var(--table-hover);
            color: var(--sapphire-primary);
            border-color: var(--sapphire-primary);
        }

        /* Icon Buttons */
        .btn-icon-soft {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: none;
            background: transparent;
            transition: all 0.2s ease;
            font-size: 1.1rem;
            text-decoration: none !important;
            cursor: pointer;
        }

        .btn-icon-soft.view {
            color: var(--sapphire-primary);
        }

        .btn-icon-soft.view:hover {
            background: rgba(59, 130, 246, 0.15);
        }

        .btn-icon-soft.edit {
            color: var(--sapphire-success, #10b981);
        }

        .btn-icon-soft.edit:hover {
            background: rgba(16, 185, 129, 0.15);
        }

        .btn-icon-soft.delete {
            color: var(--sapphire-danger, #ef4444);
        }

        .btn-icon-soft.delete:hover {
            background: rgba(239, 68, 68, 0.15);
        }

        /* Table Styles */
        .table-sapphire {
            width: 100%;
            border-collapse: collapse;
        }

        .table-sapphire th {
            color: var(--text-muted);
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            border-bottom: 2px solid var(--border-color);
            padding: 1rem;
            background-color: transparent;
            white-space: nowrap;
        }

        .table-sapphire td {
            color: var(--text-main);
            font-weight: 500;
            font-size: 0.9rem;
            border-bottom: 1px dashed var(--border-color);
            padding: 1rem;
            vertical-align: middle;
        }

        .table-sapphire tr:hover td {
            background-color: var(--table-hover);
        }

        /* Badges */
        .badge-soft-neutral {
            background: var(--bg-body);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 600;
        }
    </style>

    <div class="container-fluid py-4">

        {{-- HEADER --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--text-main);">Asset Inventory</h3>
                <p class="mb-0 text-muted" style="font-size: 0.9rem;">Manage tools, vehicles, and facilities.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('assets.export.pdf', request()->all()) }}" class="btn-sapphire-outline text-nowrap"
                    style="color: var(--sapphire-danger, #ef4444); border-color: rgba(239, 68, 68, 0.5);">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </a>
                <a href="{{ route('assets.export.excel', request()->all()) }}" class="btn-sapphire-outline text-nowrap"
                    style="color: var(--sapphire-success, #10b981); border-color: rgba(16, 185, 129, 0.5);">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </a>
                <a href="{{ route('assets.create') }}" class="btn-sapphire text-nowrap">
                    <i class="bi bi-plus-lg"></i> Add Asset
                </a>
            </div>
        </div>

        {{-- MAIN CARD --}}
        <div class="dash-card p-0 overflow-hidden">

            {{-- FILTER BAR --}}
            <div class="p-3 border-bottom"
                style="border-color: var(--border-color) !important; background: var(--bg-card);">
                <form method="GET" action="{{ route('assets.index') }}" class="row g-2 align-items-end">

                    <div class="col-md-2 col-6">
                        <label class="custom-label">Category</label>
                        <select name="category" class="custom-input">
                            <option value="">All Categories</option>
                            <option value="Offices / Govt Residence"
                                {{ request('category') == 'Offices / Govt Residence' ? 'selected' : '' }}>Offices / Govt Res
                            </option>
                            <option value="Nursery" {{ request('category') == 'Nursery' ? 'selected' : '' }}>Nursery
                            </option>
                            <option value="Plantations" {{ request('category') == 'Plantations' ? 'selected' : '' }}>
                                Plantations</option>
                            <option value="Eco Tourism Sites"
                                {{ request('category') == 'Eco Tourism Sites' ? 'selected' : '' }}>Eco Tourism Sites
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2 col-6">
                        <label class="custom-label">Added From</label>
                        <input type="date" name="date_from" class="custom-input" value="{{ request('date_from') }}">
                    </div>

                    <div class="col-md-2 col-6">
                        <label class="custom-label">Added To</label>
                        <input type="date" name="date_to" class="custom-input" value="{{ request('date_to') }}">
                    </div>

                    <div class="col-md-3 col-6">
                        <label class="custom-label">Search Name</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="custom-input"
                            placeholder="Search asset name...">
                    </div>

                    <div class="col-md-3 col-12 d-flex gap-2 mt-2 mt-md-0">
                        <button type="submit" class="btn-sapphire flex-grow-1 justify-content-center">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('assets.index') }}" class="btn-sapphire-outline justify-content-center"
                            title="Reset">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </form>
            </div>

            {{-- TABLE --}}
            <div class="table-responsive">
                <table class="table-sapphire mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 5%;">#</th>
                            <th style="width: 25%;">Asset Name</th>
                            <th style="width: 20%;">Category</th>
                            <th style="width: 15%;">Condition</th>
                            <th style="width: 10%;">Year</th>
                            <th style="width: 15%;">Date Added</th>
                            <th class="text-end pe-4" style="width: 10%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $asset)
                            <tr>
                                <td class="ps-4 text-muted fw-bold">
                                    {{ ($assets->currentPage() - 1) * $assets->perPage() + $loop->iteration }}</td>
                                <td class="fw-bold" style="color: var(--sapphire-primary);">{{ $asset->name }}</td>
                                <td>{{ $asset->category ?? 'Uncategorized' }}</td>
                                <td><span class="badge-soft-neutral">{{ $asset->condition ?? 'N/A' }}</span></td>
                                <td>{{ $asset->year ?? 'N/A' }}</td>
                                <td>{{ $asset->created_at->format('d M Y') }}</td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('assets.show', $asset->id) }}" class="btn-icon-soft view"
                                            title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('assets.edit', $asset->id) }}" class="btn-icon-soft edit"
                                            title="Edit Asset">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('assets.destroy', $asset->id) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-icon-soft delete"
                                                onclick="return confirm('Are you sure you want to delete this asset?')"
                                                title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3" style="opacity: 0.5;"></i>
                                    <h5 class="fw-bold mb-1">No assets found</h5>
                                    <p class="mb-0 small">Try adjusting your filters or add a new asset.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            @if ($assets->total() > 0)
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top"
                    style="border-color: var(--border-color) !important; background: var(--bg-body);">
                    <div class="text-muted small fw-bold mb-3 mb-md-0">
                        Showing {{ $assets->firstItem() }} to {{ $assets->lastItem() }} of {{ $assets->total() }} entries
                    </div>
                    <div>
                        {{ $assets->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection

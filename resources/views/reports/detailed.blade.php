@php
    // Helper function to build clickable sort headers
    $hideGlobalFilters = true;

    $sort = request('sort');
    $dir = request('dir', 'desc');

    $renderSortHeader = function ($label, $column) use ($sort, $dir) {
        $newDir = $sort === $column && $dir === 'asc' ? 'desc' : 'asc';
        $icon = 'bi-arrow-down-up opacity-25';
        $textClass = 'text-muted';
        if ($sort === $column) {
            $icon = $dir === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down';
            $icon .= ' text-primary opacity-100';
            $textClass = 'text-primary';
        }
        $url = request()->fullUrlWithQuery(['sort' => $column, 'dir' => $newDir]);
        return "<a href=\"{$url}\" class=\"text-decoration-none {$textClass} d-flex align-items-center gap-1\" style=\"white-space: nowrap;\">
                {$label} <i class=\"bi {$icon}\" style=\"font-size:0.75rem;\"></i></a>";
    };

    // Badge Colors
    $getReportBadgeClass = function ($type) {
        $type = strtolower(trim($type));
        $colors = [
            'felling' => 'badge-soft-danger',
            'transport' => 'badge-soft-warning',
            'storage' => 'badge-soft-orange',
            'poaching' => 'badge-soft-danger',
            'encroachment' => 'badge-soft-purple',
            'mining' => 'badge-soft-secondary',
            'sighting' => 'badge-soft-success',
            'water_status' => 'badge-soft-primary',
            'compensation' => 'badge-soft-teal',
            'fire' => 'badge-soft-danger',
        ];
        return $colors[$type] ?? 'badge-soft-neutral';
    };

    // Dynamic 'Other' value extractor
    $displayValue = function ($array, $key) {
        if (!is_array($array)) {
            return 'N/A';
        }
        $val = $array[$key] ?? 'N/A';
        $valStr = is_string($val) ? strtolower(trim($val)) : '';
        if ($valStr === 'other' || $valStr === 'others') {
            return $array[$key . '_other'] ?? 'Other (Not Specified)';
        }
        return is_string($val) || is_numeric($val) ? $val : 'N/A';
    };

    // $hasActiveFilters =
    //     $search || $fromDate || $toDate || $subType || request('dynamic_filter') || request('secondary_filter');

    // 🔥 FIX: Only trigger the banner if parameters actually exist in the URL request
    $hasActiveFilters =
        request('search') ||
        request('from_date') ||
        request('to_date') ||
        request('sub_type') ||
        request('dynamic_filter') ||
        request('secondary_filter') ||
        request('range_id') ||
        request('site_id');
@endphp

@extends('layouts.app')

@section('title', 'Detailed Data View')

@section('content')

    <style>
        .detailed-header-btn {
            background-color: var(--bg-card);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .detailed-header-btn:hover {
            background-color: var(--sapphire-primary);
            color: white;
            border-color: var(--sapphire-primary);
        }

        .sapphire-nav-pills {
            display: flex;
            gap: 8px;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 12px;
            overflow-x: auto;
            flex-wrap: nowrap;
            scrollbar-width: none;
        }

        .sapphire-nav-pills::-webkit-scrollbar {
            display: none;
        }

        .sapphire-nav-link {
            color: var(--text-muted);
            background-color: transparent;
            border: 1px solid transparent;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            white-space: nowrap;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .sapphire-nav-link:hover {
            color: var(--text-main);
            background-color: var(--bg-card);
        }

        .sapphire-nav-link.active {
            color: white;
            background-color: var(--sapphire-primary);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
        }

        .active-filters-banner {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            padding: 12px 16px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 1rem;
            animation: slideDown 0.3s ease-out forwards;
        }

        .active-filters-label {
            color: var(--sapphire-primary);
            font-weight: 700;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-right: 12px;
        }

        .filter-pill {
            background: var(--bg-body);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 4px 10px;
            font-weight: 600;
            font-size: 0.75rem;
            margin-right: 8px;
        }

        .clear-filters-btn {
            margin-left: auto;
            color: var(--sapphire-danger, #ef4444);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: opacity 0.2s;
        }

        .clear-filters-btn:hover {
            opacity: 0.7;
            color: var(--sapphire-danger, #ef4444);
        }

        /* 🔥 Highlighted Result Count */
        .results-count-badge {
            background-color: var(--sapphire-primary);
            color: white;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 6px 14px;
            border-radius: 20px;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
            margin-left: 12px;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .table-sapphire th {
            color: var(--text-muted);
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border-color);
            padding: 1rem;
            background-color: var(--bg-card);
            white-space: nowrap;
        }

        .table-sapphire td {
            color: var(--text-main);
            font-weight: 500;
            font-size: 0.9rem;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem;
            vertical-align: middle;
            background-color: transparent;
        }

        .table-sapphire tr:hover td {
            background-color: var(--table-hover);
        }

        .badge {
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .badge-soft-danger {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }

        .badge-soft-warning {
            background: rgba(245, 158, 11, 0.15);
            color: #f59e0b;
        }

        .badge-soft-orange {
            background: rgba(249, 115, 22, 0.15);
            color: #f97316;
        }

        .badge-soft-purple {
            background: rgba(168, 85, 247, 0.15);
            color: #a855f7;
        }

        .badge-soft-secondary {
            background: rgba(100, 116, 139, 0.15);
            color: #64748b;
        }

        .badge-soft-success {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
        }

        .badge-soft-primary {
            background: rgba(59, 130, 246, 0.15);
            color: #3b82f6;
        }

        .badge-soft-teal {
            background: rgba(20, 184, 166, 0.15);
            color: #14b8a6;
        }

        .badge-soft-neutral {
            background: var(--bg-body);
            color: var(--text-main);
            border: 1px solid var(--border-color);
        }
    </style>

    <div class="container-fluid py-4">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--text-main);">Detailed Data Records</h3>
                <p class="mb-0 text-muted" style="font-size: 0.9rem;">View, filter, and export all system records.</p>
            </div>
            <a href="/report-configs/reports-dashboard"
                class="btn detailed-header-btn shadow-sm d-flex align-items-center gap-2 px-4 py-2">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <nav class="sapphire-nav-pills mb-4">
            <a class="sapphire-nav-link {{ $category == 'onduty' ? 'active' : '' }}" href="?category=onduty"><i
                    class="bi bi-people me-2"></i>On Duty</a>
            <a class="sapphire-nav-link {{ $category == 'criminal' ? 'active' : '' }}" href="?category=criminal"><i
                    class="bi bi-hammer me-2"></i>Report a Forest Crime</a>
            <a class="sapphire-nav-link {{ $category == 'events' ? 'active' : '' }}" href="?category=events"><i
                    class="bi bi-eye me-2"></i>Forest Crime / Event</a>
            <a class="sapphire-nav-link {{ $category == 'fire' ? 'active' : '' }}" href="?category=fire"><i
                    class="bi bi-fire me-2"></i>Fire Incidents</a>
            <a class="sapphire-nav-link {{ $category == 'assets' ? 'active' : '' }}" href="?category=assets"><i
                    class="bi bi-shield-check me-2"></i>Assets & Tools</a>
            <a class="sapphire-nav-link {{ $category == 'plantations' ? 'active' : '' }}" href="?category=plantations"><i
                    class="bi bi-tree me-2"></i>Plantations</a>
        </nav>

        @if ($hasActiveFilters)
            <div class="active-filters-banner">
                <div class="active-filters-label"><i class="bi bi-funnel-fill"></i> Active Filters:</div>

                @if (request('search'))
                    <div class="filter-pill">Search: {{ request('search') }}</div>
                @endif

                @if (request('range_id'))
                    <div class="filter-pill">Range Filtered</div>
                @endif

                @if (request('site_id'))
                    <div class="filter-pill">Beat Filtered</div>
                @endif

                @if ($subType)
                    @php
                        $displaySubType = ucfirst(str_replace('_', ' ', $subType));
                        if ($subType == 'poaching') {
                            $displaySubType = 'Wild Animal Death';
                        }
                        if ($subType == 'sighting') {
                            $displaySubType = 'Animal Sighting';
                        }
                        if ($subType == 'water_status') {
                            $displaySubType = 'Water Source';
                        }
                    @endphp
                    <div class="filter-pill">Type: {{ $displaySubType }}</div>
                @endif

                @if (request('dynamic_filter'))
                    @php
                        $filterName = 'Filter';
                        if (in_array($subType, ['felling', 'storage', 'jfmc'])) {
                            $filterName = 'Species';
                        } elseif (in_array($subType, ['sighting', 'poaching'])) {
                            $filterName = 'Animal';
                        } elseif ($subType == 'mining') {
                            $filterName = 'Mineral';
                        } elseif ($subType == 'water_status') {
                            $filterName = 'Source';
                        } elseif ($subType == 'transport') {
                            $filterName = 'Vehicle';
                        } elseif ($subType == 'compensation') {
                            $filterName = 'Comp Type';
                        }
                    @endphp
                    <div class="filter-pill">{{ $filterName }}: {{ request('dynamic_filter') }}</div>
                @endif

                @if (request('secondary_filter'))
                    @php
                        $secFilterName = 'Secondary';
                        if ($subType == 'sighting') {
                            $secFilterName = 'Evidence';
                        }
                        if ($subType == 'poaching') {
                            $secFilterName = 'Age Class';
                        }
                    @endphp
                    <div class="filter-pill">{{ $secFilterName }}: {{ request('secondary_filter') }}</div>
                @endif

                {{-- 🔥 FIX: Now strictly checks request() instead of the controller's default fallback --}}
                @if (request('from_date'))
                    <div class="filter-pill">From: {{ \Carbon\Carbon::parse(request('from_date'))->format('d M Y') }}</div>
                @endif
                @if (request('to_date'))
                    <div class="filter-pill">To: {{ \Carbon\Carbon::parse(request('to_date'))->format('d M Y') }}</div>
                @endif

                {{-- HIGH VISIBILITY RESULT COUNT --}}
                <div class="results-count-badge">
                    <i class="bi bi-list-check me-1"></i> {{ $records->total() }} Records Found
                </div>

                <a href="?category={{ $category }}" class="clear-filters-btn">
                    <i class="bi bi-x-circle"></i> Clear Filters
                </a>
            </div>
        @endif

        <form method="GET" action="{{ route('reports.detailed') }}" id="filterForm">
            <input type="hidden" name="category" value="{{ $category }}">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="dir" value="{{ $dir }}">

            <div class="dash-card p-0 overflow-hidden" style="border: 1px solid var(--border-color); border-radius: 12px;">
                <div class="p-3 border-bottom" style="border-color: var(--border-color); background: var(--bg-card);">
                    <div class="row g-3 align-items-center">
                        <div class="col-auto">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted small fw-bold">Show</span>
                                <select name="per_page" class="form-select form-select-sm shadow-none"
                                    style="width: 75px; background-color: var(--bg-body); color: var(--text-main); border-color: var(--border-color);"
                                    onchange="autoSubmit()">
                                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>
                        </div>

                        <div class="col d-flex flex-wrap gap-2 justify-content-md-end align-items-center">

                            <select name="range_id" class="form-select form-select-sm shadow-none"
                                style="width: auto; min-width: 140px; background-color: var(--bg-body); color: var(--text-main); border-color: var(--border-color);"
                                onchange="onRangeChange()">
                                <option value="">All Ranges</option>
                                @foreach ($dropdownRanges as $id => $name)
                                    <option value="{{ $id }}"
                                        {{ request('range_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>

                            <select name="site_id" class="form-select form-select-sm shadow-none"
                                style="width: auto; min-width: 140px; background-color: var(--bg-body); color: var(--text-main); border-color: var(--border-color);"
                                onchange="autoSubmit()">
                                <option value="">All Beats</option>
                                @foreach ($dropdownBeats as $beat)
                                    <option value="{{ $beat->id }}"
                                        {{ request('site_id') == $beat->id ? 'selected' : '' }}>{{ $beat->name }}
                                    </option>
                                @endforeach
                            </select>

                            @if (in_array($category, ['criminal', 'events']))
                                <select name="sub_type" class="form-select form-select-sm shadow-none"
                                    style="width: auto; min-width: 140px; background-color: var(--bg-body); color: var(--text-main); border-color: var(--border-color);"
                                    onchange="onSubTypeChange()">
                                    <option value="">All Types</option>
                                    @if ($category == 'criminal')
                                        <option value="felling" {{ $subType == 'felling' ? 'selected' : '' }}>Illegal
                                            Felling</option>
                                        <option value="poaching" {{ $subType == 'poaching' ? 'selected' : '' }}>Wild Animal
                                            Death</option>
                                        <option value="encroachment" {{ $subType == 'encroachment' ? 'selected' : '' }}>
                                            Encroachment</option>
                                        <option value="mining" {{ $subType == 'mining' ? 'selected' : '' }}>Mining</option>
                                        <option value="storage" {{ $subType == 'storage' ? 'selected' : '' }}>Storage
                                        </option>
                                        <option value="transport" {{ $subType == 'transport' ? 'selected' : '' }}>Transport
                                        </option>
                                    @elseif($category == 'events')
                                        <option value="sighting" {{ $subType == 'sighting' ? 'selected' : '' }}>Animal
                                            Sighting</option>
                                        <option value="water_status" {{ $subType == 'water_status' ? 'selected' : '' }}>
                                            Water Source</option>
                                        <option value="compensation" {{ $subType == 'compensation' ? 'selected' : '' }}>
                                            Compensation</option>
                                    @endif
                                </select>
                            @endif

                            {{-- 🔥 DYNAMIC PRIMARY FILTER (Smart Naming based on SubType) --}}
                            @if (in_array($subType, [
                                    'sighting',
                                    'poaching',
                                    'felling',
                                    'storage',
                                    'water_status',
                                    'transport',
                                    'compensation',
                                    'mining',
                                    'jfmc',
                                ]))
                                @php
                                    $filterLabel = 'All Filters';
                                    if (in_array($subType, ['felling', 'storage', 'jfmc'])) {
                                        $filterLabel = 'All Species';
                                    } elseif (in_array($subType, ['sighting', 'poaching'])) {
                                        $filterLabel = 'All Animals';
                                    } elseif ($subType == 'mining') {
                                        $filterLabel = 'All Minerals';
                                    } elseif ($subType == 'water_status') {
                                        $filterLabel = 'All Sources';
                                    } elseif ($subType == 'transport') {
                                        $filterLabel = 'All Vehicles';
                                    } elseif ($subType == 'compensation') {
                                        $filterLabel = 'All Types';
                                    }
                                @endphp

                                <select name="dynamic_filter" class="form-select form-select-sm shadow-none"
                                    style="width: auto; min-width: 140px; background-color: var(--bg-body); color: var(--text-main); border-color: var(--border-color);"
                                    onchange="autoSubmit()">
                                    <option value="">{{ $filterLabel }}</option>

                                    @if (in_array($subType, ['sighting', 'poaching']))
                                        <option value="Leopard"
                                            {{ request('dynamic_filter') == 'Leopard' ? 'selected' : '' }}>Leopard</option>
                                        <option value="Sloth Bear"
                                            {{ request('dynamic_filter') == 'Sloth Bear' ? 'selected' : '' }}>Sloth Bear
                                        </option>
                                        <option value="Wild Boar"
                                            {{ request('dynamic_filter') == 'Wild Boar' ? 'selected' : '' }}>Wild Boar
                                        </option>
                                        <option value="Jackal"
                                            {{ request('dynamic_filter') == 'Jackal' ? 'selected' : '' }}>Jackal</option>
                                        <option value="Hyena"
                                            {{ request('dynamic_filter') == 'Hyena' ? 'selected' : '' }}>Hyena</option>
                                        <option value="Spotted Deer"
                                            {{ request('dynamic_filter') == 'Spotted Deer' ? 'selected' : '' }}>Spotted
                                            Deer</option>
                                        <option value="Sambar"
                                            {{ request('dynamic_filter') == 'Sambar' ? 'selected' : '' }}>Sambar</option>
                                    @elseif(in_array($subType, ['felling', 'storage', 'jfmc']))
                                        <option value="Sal" {{ request('dynamic_filter') == 'Sal' ? 'selected' : '' }}>
                                            Sal</option>
                                        <option value="Saja"
                                            {{ request('dynamic_filter') == 'Saja' ? 'selected' : '' }}>Saja</option>
                                        <option value="Sagaon"
                                            {{ request('dynamic_filter') == 'Sagaon' ? 'selected' : '' }}>Sagaon</option>
                                        <option value="Beeja"
                                            {{ request('dynamic_filter') == 'Beeja' ? 'selected' : '' }}>Beeja</option>
                                        <option value="Haldu"
                                            {{ request('dynamic_filter') == 'Haldu' ? 'selected' : '' }}>Haldu</option>
                                        <option value="Dhawda"
                                            {{ request('dynamic_filter') == 'Dhawda' ? 'selected' : '' }}>Dhawda</option>
                                        <option value="Safed Siris"
                                            {{ request('dynamic_filter') == 'Safed Siris' ? 'selected' : '' }}>Safed Siris
                                        </option>
                                        <option value="Kala Siris"
                                            {{ request('dynamic_filter') == 'Kala Siris' ? 'selected' : '' }}>Kala Siris
                                        </option>
                                        <option value="Jamun"
                                            {{ request('dynamic_filter') == 'Jamun' ? 'selected' : '' }}>Jamun</option>
                                        <option value="Aam" {{ request('dynamic_filter') == 'Aam' ? 'selected' : '' }}>
                                            Aam</option>
                                        <option value="Semal"
                                            {{ request('dynamic_filter') == 'Semal' ? 'selected' : '' }}>Semal</option>
                                        <option value="Mahua"
                                            {{ request('dynamic_filter') == 'Mahua' ? 'selected' : '' }}>Mahua</option>
                                        <option value="Tendu"
                                            {{ request('dynamic_filter') == 'Tendu' ? 'selected' : '' }}>Tendu</option>
                                        <option value="Nilgiri"
                                            {{ request('dynamic_filter') == 'Nilgiri' ? 'selected' : '' }}>Nilgiri</option>
                                    @elseif($subType == 'water_status')
                                        <option value="Natural pond"
                                            {{ request('dynamic_filter') == 'Natural pond' ? 'selected' : '' }}>Natural
                                            pond</option>
                                        <option value="Earthen dam"
                                            {{ request('dynamic_filter') == 'Earthen dam' ? 'selected' : '' }}>Earthen dam
                                        </option>
                                        <option value="Check dam"
                                            {{ request('dynamic_filter') == 'Check dam' ? 'selected' : '' }}>Check dam
                                        </option>
                                        <option value="Stop dam"
                                            {{ request('dynamic_filter') == 'Stop dam' ? 'selected' : '' }}>Stop dam
                                        </option>
                                        <option value="Concrete water hole"
                                            {{ request('dynamic_filter') == 'Concrete water hole' ? 'selected' : '' }}>
                                            Concrete water hole</option>
                                        <option value="River stream"
                                            {{ request('dynamic_filter') == 'River stream' ? 'selected' : '' }}>River
                                            stream</option>
                                        <option value="Open well"
                                            {{ request('dynamic_filter') == 'Open well' ? 'selected' : '' }}>Open well
                                        </option>
                                        <option value="Closed well"
                                            {{ request('dynamic_filter') == 'Closed well' ? 'selected' : '' }}>Closed well
                                        </option>
                                    @elseif($subType == 'mining')
                                        <option value="Sand"
                                            {{ request('dynamic_filter') == 'Sand' ? 'selected' : '' }}>Sand</option>
                                        <option value="Stone"
                                            {{ request('dynamic_filter') == 'Stone' ? 'selected' : '' }}>Stone</option>
                                        <option value="Murrum"
                                            {{ request('dynamic_filter') == 'Murrum' ? 'selected' : '' }}>Murrum</option>
                                    @elseif($subType == 'transport')
                                        <option value="Truck"
                                            {{ request('dynamic_filter') == 'Truck' ? 'selected' : '' }}>Truck</option>
                                        <option value="Tractor"
                                            {{ request('dynamic_filter') == 'Tractor' ? 'selected' : '' }}>Tractor</option>
                                        <option value="Tempo"
                                            {{ request('dynamic_filter') == 'Tempo' ? 'selected' : '' }}>Tempo</option>
                                        <option value="Bullock Cart"
                                            {{ request('dynamic_filter') == 'Bullock Cart' ? 'selected' : '' }}>Bullock
                                            Cart</option>
                                        <option value="Two-Wheeler"
                                            {{ request('dynamic_filter') == 'Two-Wheeler' ? 'selected' : '' }}>Two-Wheeler
                                        </option>
                                    @elseif($subType == 'compensation')
                                        <option value="Human death"
                                            {{ request('dynamic_filter') == 'Human death' ? 'selected' : '' }}>Human death
                                        </option>
                                        <option value="Permanent disability"
                                            {{ request('dynamic_filter') == 'Permanent disability' ? 'selected' : '' }}>
                                            Permanent disability</option>
                                        <option value="Human injury"
                                            {{ request('dynamic_filter') == 'Human injury' ? 'selected' : '' }}>Human
                                            injury</option>
                                        <option value="Cattle death"
                                            {{ request('dynamic_filter') == 'Cattle death' ? 'selected' : '' }}>Cattle
                                            death</option>
                                        <option value="Crop damage"
                                            {{ request('dynamic_filter') == 'Crop damage' ? 'selected' : '' }}>Crop damage
                                        </option>
                                        <option value="House damage"
                                            {{ request('dynamic_filter') == 'House damage' ? 'selected' : '' }}>House
                                            damage</option>
                                    @endif
                                </select>
                            @endif

                            {{-- 🔥 DYNAMIC SECONDARY FILTER --}}
                            @if (in_array($subType, ['sighting', 'poaching']))
                                @php
                                    $secFilterLabel = $subType == 'sighting' ? 'All Evidence Types' : 'All Age Classes';
                                @endphp
                                <select name="secondary_filter" class="form-select form-select-sm shadow-none"
                                    style="width: auto; min-width: 140px; background-color: var(--bg-body); color: var(--text-main); border-color: var(--border-color);"
                                    onchange="autoSubmit()">
                                    <option value="">{{ $secFilterLabel }}</option>

                                    @if ($subType == 'sighting')
                                        <option value="Photo"
                                            {{ request('secondary_filter') == 'Photo' ? 'selected' : '' }}>Photo</option>
                                        <option value="Pugmark"
                                            {{ request('secondary_filter') == 'Pugmark' ? 'selected' : '' }}>Pugmark
                                        </option>
                                        <option value="Scratch"
                                            {{ request('secondary_filter') == 'Scratch' ? 'selected' : '' }}>Scratch
                                        </option>
                                        <option value="Scat"
                                            {{ request('secondary_filter') == 'Scat' ? 'selected' : '' }}>Scat</option>
                                        <option value="Body Part"
                                            {{ request('secondary_filter') == 'Body Part' ? 'selected' : '' }}>Body Part
                                        </option>
                                        <option value="Den"
                                            {{ request('secondary_filter') == 'Den' ? 'selected' : '' }}>Den</option>
                                    @elseif($subType == 'poaching')
                                        <option value="Adult"
                                            {{ request('secondary_filter') == 'Adult' ? 'selected' : '' }}>Adult</option>
                                        <option value="Sub-Adult"
                                            {{ request('secondary_filter') == 'Sub-Adult' ? 'selected' : '' }}>Sub-Adult
                                        </option>
                                        <option value="Juvenile"
                                            {{ request('secondary_filter') == 'Juvenile' ? 'selected' : '' }}>Juvenile
                                        </option>
                                    @endif
                                </select>
                            @endif

                            <div class="d-flex align-items-center gap-1">
                                <input type="date" name="from_date" class="form-control form-control-sm shadow-none"
                                    style="width: 130px; background-color: var(--bg-body); color: var(--text-main); border-color: var(--border-color);"
                                    value="{{ $fromDate }}" title="From Date">
                                <input type="date" name="to_date" class="form-control form-control-sm shadow-none"
                                    style="width: 130px; background-color: var(--bg-body); color: var(--text-main); border-color: var(--border-color);"
                                    value="{{ $toDate }}" title="To Date">
                                <button type="submit" class="btn btn-sm d-flex align-items-center gap-1 shadow-sm"
                                    style="background-color: var(--sapphire-primary); color: white; border-radius: 6px;">
                                    <i class="bi bi-funnel"></i> Filter
                                </button>
                            </div>

                            <div class="input-group input-group-sm" style="width: 200px;">
                                <span class="input-group-text bg-transparent border-end-0"
                                    style="border-color: var(--border-color);"><i
                                        class="bi bi-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 shadow-none"
                                    style="background-color: var(--bg-body); color: var(--text-main); border-color: var(--border-color);"
                                    placeholder="Search ID, Name..." value="{{ $search }}"
                                    onkeyup="debounceSearch()">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sapphire mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4" style="width: 60px;">Sr. No.</th>

                                @if ($viewType == 'reports')
                                    <th>{!! $renderSortHeader('Report ID', 'report_id') !!}</th>
                                    <th>{!! $renderSortHeader('Date / Time', 'created_at') !!}</th>
                                    <th>{!! $renderSortHeader('Beat / Range', 'beat') !!}</th>

                                    @if (in_array($subType, ['felling', 'jfmc']))
                                        <th>Primary Species</th>
                                        <th>Total Trees</th>
                                        <th>Vol (CuM)</th>
                                    @elseif($subType == 'transport')
                                        <th>Produce</th>
                                        <th>Vehicle Type</th>
                                        <th>Vol (CuM)</th>
                                    @elseif($subType == 'encroachment')
                                        <th>Encroach Type</th>
                                        <th>Area (Ha)</th>
                                        <th>Vehicle Seized?</th>
                                    @elseif($subType == 'mining')
                                        <th>Mineral Type</th>
                                        <th>Vol (CuM)</th>
                                        <th>Vehicle Seized?</th>
                                    @elseif($subType == 'storage')
                                        <th>Storage Type</th>
                                        <th>Species</th>
                                        <th>Qty (CMT)</th>
                                    @elseif($subType == 'sighting')
                                        <th>Species</th>
                                        <th>Evidence Type</th>
                                        <th>Count</th>
                                    @elseif($subType == 'poaching')
                                        <th>Species</th>
                                        <th>Age Class</th>
                                        <th>Cause of Death</th>
                                    @elseif($subType == 'water_status')
                                        <th>Source Type</th>
                                        <th>Is Dry?</th>
                                        <th>Quality</th>
                                    @elseif($subType == 'compensation')
                                        <th>Comp Type</th>
                                        <th>Victim / Owner</th>
                                        <th>Claimed (₹)</th>
                                    @elseif($category == 'fire' || $subType == 'fire')
                                        <th>Fire Cause</th>
                                        <th>Area Burnt (Ha)</th>
                                        <th>Resp. Time</th>
                                    @else
                                        <th>Report Type</th>
                                        <th>Primary Subject</th>
                                        <th>Key Detail</th>
                                    @endif

                                    {{-- 🔥 Status Header Removed for Reports --}}
                                @elseif($viewType == 'assets')
                                    <th>{!! $renderSortHeader('Asset ID', 'id') !!}</th>
                                    <th>{!! $renderSortHeader('Category', 'category') !!}</th>
                                    <th>{!! $renderSortHeader('Condition', 'condition') !!}</th>
                                    <th>{!! $renderSortHeader('Date Added', 'created_at') !!}</th>
                                @elseif($viewType == 'plantations')
                                    <th>{!! $renderSortHeader('Code', 'code') !!}</th>
                                    <th>{!! $renderSortHeader('Plantation Name', 'name') !!}</th>
                                    <th>{!! $renderSortHeader('Species', 'plant_species') !!}</th>
                                    <th>{!! $renderSortHeader('Area (Ha)', 'area') !!}</th>
                                    <th class="text-end pe-4">{!! $renderSortHeader('Phase', 'current_phase') !!}</th>
                                @elseif($viewType == 'onduty')
                                    <th>{!! $renderSortHeader('Name of Employee', 'users.name') !!}</th>
                                    <th>{!! $renderSortHeader('Beat Name', 'site_assign.site_name') !!}</th>
                                    <th>{!! $renderSortHeader('Compartment', 'attendance.status') !!}</th>
                                    <th>{!! $renderSortHeader('Date', 'attendance.dateFormat') !!}</th>
                                    <th>{!! $renderSortHeader('Entry', 'attendance.in_time') !!}</th>
                                    <th class="text-center pe-4">Location</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $row)
                                @php
                                    $data = [];
                                    if (!empty($row->report_data)) {
                                        $parsed = is_string($row->report_data)
                                            ? json_decode($row->report_data, true)
                                            : (array) $row->report_data;
                                        $data = is_array($parsed) ? $parsed : [];
                                    }
                                    $rType = strtolower(trim($row->report_type ?? ''));
                                @endphp

                                <tr>
                                    <td class="ps-4 fw-bold text-muted">
                                        {{ ($records->currentPage() - 1) * $records->perPage() + $loop->iteration }}
                                    </td>

                                    @if ($viewType == 'reports')
                                        <td class="fw-bold" style="color: var(--sapphire-primary);">
                                            <a href="{{ url('/reports/show/' . ($row->id ?? 0)) }}"
                                                class="text-decoration-none">
                                                {{ $row->report_id ?? 'RPT-' . ($row->id ?? 0) }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="fw-bold">
                                                {{ \Carbon\Carbon::parse($row->created_at ?? now())->format('d M Y') }}
                                            </div>
                                            <div class="text-muted small">
                                                {{ \Carbon\Carbon::parse($row->created_at ?? now())->format('h:i A') }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold">
                                                {{ $row->resolved_range ?? ($row->range ?? 'Unknown Range') }}</div>
                                            <div class="text-muted small">
                                                {{ $row->resolved_beat ?? ($row->beat ?? 'Unknown Beat') }}</div>
                                        </td>

                                        @if (in_array($subType, ['felling', 'jfmc']))
                                            @php
                                                $speciesGroup =
                                                    isset($data['species_group']) && is_array($data['species_group'])
                                                        ? $data['species_group']
                                                        : [
                                                            [
                                                                'species' => $data['species'] ?? 'N/A',
                                                                'qty' => $data['qty'] ?? 0,
                                                                'volume' => $data['volume'] ?? 0,
                                                            ],
                                                        ];

                                                $primarySpeciesRaw = $speciesGroup[0]['species'] ?? 'N/A';
                                                $primarySpeciesStr = is_string($primarySpeciesRaw)
                                                    ? strtolower(trim($primarySpeciesRaw))
                                                    : '';
                                                $primarySpecies =
                                                    $primarySpeciesStr === 'other' || $primarySpeciesStr === 'others'
                                                        ? $speciesGroup[0]['species_other'] ?? 'Other (Specified)'
                                                        : (is_string($primarySpeciesRaw) ||
                                                        is_numeric($primarySpeciesRaw)
                                                            ? $primarySpeciesRaw
                                                            : 'N/A');

                                                $totalTrees = array_sum(
                                                    array_map('floatval', array_column($speciesGroup, 'qty')),
                                                );
                                                $totalVol = array_sum(
                                                    array_map('floatval', array_column($speciesGroup, 'volume')),
                                                );
                                            @endphp
                                            <td class="fw-bold text-danger">{{ $primarySpecies }}
                                                @if (count($speciesGroup) > 1)
                                                    <span
                                                        class="badge bg-light text-dark border ms-1">+{{ count($speciesGroup) - 1 }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $totalTrees }}</td>
                                            <td>{{ number_format((float) $totalVol, 2) }}</td>
                                        @elseif($subType == 'transport')
                                            <td class="fw-bold text-warning">{{ $data['produce_name'] ?? 'N/A' }}</td>
                                            <td><span
                                                    class="badge badge-soft-neutral">{{ $displayValue($data, 'vehicle_type') }}</span>
                                            </td>
                                            <td>{{ $data['qty_volume'] ?? ($data['qty_final'] ?? 0) }}</td>
                                        @elseif($subType == 'encroachment')
                                            <td class="fw-bold text-purple">
                                                {{ $displayValue($data, 'encroachment_type') }}</td>
                                            <td>{{ $data['area_hectare'] ?? 0 }}</td>
                                            <td>
                                                @if (isset($data['vehicle_seized']) && strcasecmp($data['vehicle_seized'], 'yes') === 0)
                                                    <span class="badge badge-soft-danger"><i
                                                            class="bi bi-truck me-1"></i>Yes</span>
                                                @else
                                                    <span class="badge badge-soft-neutral">No</span>
                                                @endif
                                            </td>
                                        @elseif($subType == 'mining')
                                            <td class="fw-bold text-secondary">{{ $displayValue($data, 'mineral_type') }}
                                            </td>
                                            <td>{{ $data['volume_cum'] ?? 0 }}</td>
                                            <td>
                                                @if (isset($data['vehicle_seized']) && strcasecmp($data['vehicle_seized'], 'yes') === 0)
                                                    <span class="badge badge-soft-danger"><i
                                                            class="bi bi-truck me-1"></i>Yes</span>
                                                @else
                                                    <span class="badge badge-soft-neutral">No</span>
                                                @endif
                                            </td>
                                        @elseif($subType == 'storage')
                                            <td class="fw-bold text-orange">{{ $displayValue($data, 'storage_type') }}
                                            </td>
                                            <td>{{ $displayValue($data, 'species') }}</td>
                                            <td>{{ $data['qty_cmt'] ?? 0 }}</td>
                                        @elseif($subType == 'sighting')
                                            <td class="fw-bold text-success">{{ $displayValue($data, 'species') }}</td>
                                            <td>{{ $displayValue($data, 'evidence_type') }}</td>
                                            <td>{{ $data['num_animals'] ?? 1 }}</td>
                                        @elseif($subType == 'poaching')
                                            <td class="fw-bold text-danger">{{ $displayValue($data, 'species') }}</td>
                                            <td>{{ $data['age_class'] ?? 'Unk' }}</td>
                                            <td>{{ $data['cause_death'] ?? 'Unknown' }}</td>
                                        @elseif($subType == 'water_status')
                                            <td class="fw-bold text-primary">{{ $displayValue($data, 'source_type') }}
                                            </td>
                                            <td>
                                                @if (isset($data['is_dry']) && strtolower(trim($data['is_dry'])) == 'yes')
                                                    <span class="badge badge-soft-danger">Yes (Dry)</span>
                                                @else
                                                    <span class="badge badge-soft-primary">No (Has Water)</span>
                                                @endif
                                            </td>
                                            <td>{{ $displayValue($data, 'water_quality') }}</td>
                                        @elseif($subType == 'compensation')
                                            <td class="fw-bold text-teal">{{ $displayValue($data, 'comp_type') }}</td>
                                            <td>{{ $data['victim_name'] ?? 'N/A' }}</td>
                                            <td class="text-danger fw-bold">
                                                ₹{{ number_format((float) ($data['amount_claimed'] ?? 0)) }}</td>
                                        @elseif($category == 'fire' || $subType == 'fire')
                                            <td class="fw-bold text-danger">{{ $data['fire_cause'] ?? 'Unknown' }}</td>
                                            <td class="text-danger">{{ $data['area_burnt'] ?? 0 }}</td>
                                            <td>{{ $data['response_time'] ?? 0 }} mins</td>
                                        @else
                                            <td>
                                                <span class="badge {{ $getReportBadgeClass($rType) }} px-2 py-1">
                                                    @php
                                                        $displayName =
                                                            $rType == 'poaching'
                                                                ? 'Wild Animal Death'
                                                                : ($rType == 'sighting'
                                                                    ? 'Animal Sighting'
                                                                    : $rType);
                                                    @endphp
                                                    {{ ucwords(str_replace('_', ' ', $displayName)) }}
                                                </span>
                                            </td>
                                            <td class="fw-bold">
                                                @php
                                                    $fallbackSubject =
                                                        $data['species'] ??
                                                        ($data['produce_name'] ??
                                                            ($data['encroachment_type'] ??
                                                                ($data['mineral_type'] ??
                                                                    ($data['source_type'] ?? 'N/A'))));
                                                    $fbStr = is_string($fallbackSubject)
                                                        ? strtolower(trim($fallbackSubject))
                                                        : '';
                                                    if ($fbStr === 'other' || $fbStr === 'others') {
                                                        $fallbackSubject =
                                                            $data['species_other'] ??
                                                            ($data['encroachment_type_other'] ??
                                                                ($data['mineral_type_other'] ??
                                                                    ($data['source_type_other'] ??
                                                                        'Other (Specified)')));
                                                    }
                                                @endphp
                                                {{ is_string($fallbackSubject) || is_numeric($fallbackSubject) ? $fallbackSubject : 'N/A' }}
                                            </td>
                                            <td class="fw-semibold">
                                                @php
                                                    $keyDetail = 'View Details';
                                                    if (in_array($rType, ['felling', 'jfmc'])) {
                                                        $qtyRaw =
                                                            isset($data['species_group']) &&
                                                            is_array($data['species_group'])
                                                                ? array_sum(
                                                                    array_map(
                                                                        'floatval',
                                                                        array_column($data['species_group'], 'qty'),
                                                                    ),
                                                                )
                                                                : floatval($data['qty'] ?? 0);
                                                        $keyDetail = 'Trees: ' . $qtyRaw;
                                                    } elseif ($rType == 'transport') {
                                                        $keyDetail =
                                                            'Vol: ' .
                                                            ($data['qty_volume'] ?? ($data['qty_final'] ?? 0)) .
                                                            ' CuM';
                                                    } elseif ($rType == 'encroachment') {
                                                        $keyDetail = 'Area: ' . ($data['area_hectare'] ?? 0) . ' Ha';
                                                    } elseif ($rType == 'mining') {
                                                        $keyDetail = 'Vol: ' . ($data['volume_cum'] ?? 0) . ' CuM';
                                                    } elseif ($rType == 'storage') {
                                                        $keyDetail = 'Qty: ' . ($data['qty_cmt'] ?? 0) . ' CMT';
                                                    } elseif ($rType == 'sighting') {
                                                        $keyDetail = 'Count: ' . ($data['num_animals'] ?? 1);
                                                    } elseif ($rType == 'poaching') {
                                                        $keyDetail = 'Cause: ' . ($data['cause_death'] ?? 'Unknown');
                                                    } elseif ($rType == 'water_status') {
                                                        $keyDetail = 'Dry: ' . ($data['is_dry'] ?? 'Unknown');
                                                    } elseif ($rType == 'compensation') {
                                                        $keyDetail =
                                                            'Claim: ₹' .
                                                            number_format((float) ($data['amount_claimed'] ?? 0));
                                                    } elseif ($rType == 'fire') {
                                                        $keyDetail = 'Burnt: ' . ($data['area_burnt'] ?? 0) . ' Ha';
                                                    }
                                                @endphp
                                                <span class="fw-semibold"
                                                    style="background: var(--bg-body); color: var(--text-main); padding: 4px 8px; border-radius: 4px; border: 1px solid var(--border-color);">
                                                    {{ $keyDetail }}
                                                </span>
                                            </td>
                                        @endif

                                        {{-- 🔥 Status Data Cell Removed for Reports --}}
                                    @elseif($viewType == 'assets')
                                        <td class="fw-bold" style="color: var(--sapphire-primary);">
                                            AST-{{ $row->id }}</td>
                                        <td>{{ $row->category ?? 'Equipment' }}</td>
                                        <td><span
                                                class="badge badge-soft-neutral px-2 py-1">{{ $row->condition ?? 'N/A' }}</span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($row->created_at ?? now())->format('d M Y') }}</td>
                                    @elseif($viewType == 'plantations')
                                        <td class="fw-bold" style="color: var(--sapphire-primary);">{{ $row->code }}
                                        </td>
                                        <td class="fw-bold">{{ $row->name }}</td>
                                        <td>{{ $row->plant_species ?? 'Mixed' }}</td>
                                        <td>{{ $row->area ?? 0 }} Ha</td>
                                        <td class="text-end pe-4"><span
                                                class="badge badge-soft-primary px-3 py-2 rounded-pill">{{ ucfirst($row->current_phase ?? 'Unknown') }}</span>
                                        </td>
                                    @elseif($viewType == 'onduty')
                                        <td class="fw-bold">{{ $row->name ?? 'Unknown' }}</td>
                                        <td>{{ $row->site_name ?? 'Floating/Unassigned' }}</td>
                                        <td>{{ $row->geofence_status ?? 'Unknown' }}</td>
                                        <td>{{ $row->date ? \Carbon\Carbon::parse($row->date)->format('d M Y') : 'N/A' }}
                                        </td>
                                        <td>{{ $row->in_time ? \Carbon\Carbon::parse($row->in_time)->format('h:i a') : 'N/A' }}
                                        </td>
                                        <td class="text-center pe-4">
                                            @if (!empty($row->location))
                                                <a href="https://maps.google.com/?q={{ $row->location }}"
                                                    target="_blank" class="map-pin-btn text-decoration-none"
                                                    title="View on Map">
                                                    <i class="bi bi-geo-alt"></i>
                                                </a>
                                            @else
                                                <span class="text-muted small">N/A</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-3" style="opacity: 0.5;"></i>
                                        <h5 class="fw-bold mb-1">No records found</h5>
                                        <p class="mb-0 small">Try adjusting your filters or search query.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($records->total() > 0)
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top"
                        style="border-color: var(--border-color); background-color: var(--bg-body);">
                        <div class="text-muted small fw-bold mb-3 mb-md-0">
                            Showing {{ $records->firstItem() }} to {{ $records->lastItem() }} of
                            {{ $records->total() }} entries
                        </div>
                        <div>
                            {{ $records->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                @endif
            </div>
        </form>

    </div>

    <script>
        function autoSubmit() {
            document.getElementById('filterForm').submit();
        }

        function onRangeChange() {
            const beatSelect = document.querySelector('select[name="site_id"]');
            if (beatSelect) beatSelect.value = '';
            autoSubmit();
        }

        function onSubTypeChange() {
            const dynamicFilter = document.querySelector('select[name="dynamic_filter"]');
            if (dynamicFilter) dynamicFilter.value = '';

            // 🔥 Clear the Secondary Filter when Sub-Type changes
            const secondaryFilter = document.querySelector('select[name="secondary_filter"]');
            if (secondaryFilter) secondaryFilter.value = '';

            autoSubmit();
        }

        let searchTimeout = null;

        function debounceSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                autoSubmit();
            }, 600);
        }
    </script>
@endsection

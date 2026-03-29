@php
$hideGlobalFilters = true;
@endphp

@extends('layouts.app')



@section('content')

{{-- Loader --}}
@include('partials.dash-loader')

{{-- Added 'd-flex flex-column' to force the exact visual order --}}
<main class="container-fluid py-4 d-flex flex-column gap-2">

    {{-- 1. KPI Cards (Forces it to be Row 1) --}}
    <div class="w-100 order-1">
        @include('partials.dash-kpi-grid')
    </div>

    {{-- 2. Toggle & Filters Row (Forces it to be Row 2) --}}
    <div class="w-100 order-2 mb-3">
        @include('partials.dash-header')
    </div>

    {{-- 3. Main Content (Forces it to be Row 3) --}}
    <div class="w-100 order-3">
        {{-- Overall --}}
        @include('overallDashboard.index')

        {{-- Analytical --}}
        @include('analyticalDashboard.index')
    </div>

</main>

@endsection

@section('styles')
@include('styles.dashboard')
@endsection

@section('scripts')
@include('scripts.dashboard')
@endsection
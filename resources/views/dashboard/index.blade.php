@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    $user = session('user');
@endphp
@extends('layouts.app')



@push('styles')
    @include('dashboard.styles')
@endpush

@section('content')
    {{-- 1. Loader --}}
    @include('partials.dash-loader')

    <main class="container-fluid py-4" id="dashboard-app">
        {{-- 2. Header & Filters --}}
        @include('partials.dash-header')

        {{-- 3. KPI Cards --}}
        @include('partials.dash-kpi-grid')

        {{-- 4. View Toggle --}}
        {{-- @include('partials.dash-view-toggle') --}}

        {{-- 5. Dashboard Views --}}
        @include('overallDashboard.index')
        @include('analyticalDashboard.index')

        {{-- 6. Modals (Added here at the bottom of main!) --}}
        @include('partials.dash-kpi-modal')
    </main>
@endsection

@push('scripts')
    {{-- REQUIRED FOR GOOGLE MAPS --}}
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfBFN6L_HROTd-mS8QqUDRIqskkvHvFYk&libraries=visualization">
    </script>
    <script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>

    {{-- REQUIRED FOR CHARTS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

    {{-- YOUR CUSTOM SCRIPTS --}}
    @include('dashboard.scripts')
@endpush

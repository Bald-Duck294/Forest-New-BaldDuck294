@php
    $hideGlobalFilters = true;
@endphp

@extends('layouts.app')

@section('title', 'ForestFix Analytics')

@section('content')

    {{-- Loader --}}
    @include('partials.dash-loader')

    <main class="container-fluid py-4">

        {{-- Header --}}
        @include('partials.dash-header')

        {{-- KPI --}}
        @include('partials.dash-kpi-grid')

        {{-- Toggle --}}
        @include('partials.dash-view-toggle')

        {{-- Overall --}}
        @include('overallDashboard.index')

        {{-- Analytical --}}
        @include('analyticalDashboard.index')

    </main>

@endsection

@section('styles')
    @include('styles.dashboard')
@endsection

@section('scripts')
    @include('scripts.dashboard')
@endsection

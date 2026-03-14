@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    $user = session('user');
@endphp
@extends('layouts.app')

@section('title', 'Create Plantation')

@section('content')

    <div class="container py-4">

        <div class="mb-4">
            <h4 class="fw-bold">Create Plantation</h4>
            <small class="text-muted">Start a new plantation workflow</small>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">

                <form action="{{ route('plantation.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">

                        {{-- Plantation Code --}}
                        <div class="col-md-6">
                            <label class="form-label">Plantation Code</label>
                            <input type="text" class="form-control" value="{{ $nextCode }}" disabled>
                        </div>

                        {{-- Plantation Name --}}
                        <div class="col-md-6">
                            <label class="form-label">Plantation Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        {{-- Site --}}
                        <div class="col-md-6">
                            <label class="form-label">Site</label>
                            <select name="site_id" class="form-select">
                                <option value="">Select Site</option>
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}">
                                        {{ $site->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Description --}}
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="3" class="form-control"></textarea>
                        </div>

                    </div>

                    <div class="mt-4 d-flex justify-content-end">

                        <a href="{{ route('plantation.dashboard') }}" class="btn btn-light me-2">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary">
                            Create Plantation
                        </button>

                    </div>

                </form>

            </div>
        </div>

    </div>

@endsection

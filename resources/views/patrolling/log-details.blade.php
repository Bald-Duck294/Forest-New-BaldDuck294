@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    // $user = session('user');
@endphp
@extends('layouts.app')

@section('title', 'Patrol Log Details')

@section('content')

    <style>
        /* =========================================
                           LOCAL COMPONENT STYLES
                           (Hooked to Global Sapphire Variables)
                        ========================================= */

        /* Cards */
        .dash-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }

        /* Action Buttons */
        .btn-sapphire-outline {
            background-color: transparent;
            color: var(--text-main);
            border: 1px solid var(--border-color);
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-sapphire-outline:hover {
            background-color: var(--table-hover);
            color: var(--sapphire-primary);
            border-color: var(--sapphire-primary);
        }

        /* Info Data Blocks */
        .info-block {
            padding: 12px 16px;
            background: var(--bg-body);
            border-radius: 8px;
            border: 1px solid var(--border-color);
            height: 100%;
        }

        .info-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 0.95rem;
            color: var(--text-main);
            font-weight: 500;
            margin: 0;
        }

        /* Soft Badges */
        .badge-soft {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-soft-primary {
            background: rgba(59, 130, 246, 0.15);
            color: var(--sapphire-primary);
        }

        /* Tables */
        .dash-table {
            width: 100%;
            border-collapse: collapse;
        }

        .dash-table th {
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.85rem;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem;
            background-color: transparent !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .dash-table td {
            color: var(--text-main);
            font-weight: 500;
            font-size: 0.9rem;
            border-bottom: 1px dashed var(--border-color);
            padding: 1rem;
            vertical-align: middle;
            background-color: transparent !important;
        }

        .dash-table tr:hover td {
            background-color: var(--table-hover) !important;
        }

        .dash-table tr:last-child td {
            border-bottom: none;
        }

        /* Interactive Hover Lift */
        .hover-lift {
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
            cursor: pointer;
        }

        .hover-lift:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
            border-color: var(--sapphire-primary);
        }

        /* Modal Overrides */
        .modal-content.sapphire-modal {
            background-color: var(--bg-card);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 12px;
        }

        .modal-header.sapphire-modal-header {
            border-bottom: 1px solid var(--border-color);
        }

        .modal-footer.sapphire-modal-footer {
            border-top: 1px solid var(--border-color);
        }
    </style>

    <div class="container-fluid py-4">

        {{-- HEADER --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-1" style="color: var(--text-main);">Patrol Log Details</h3>
                <p class="mb-0" style="color: var(--text-muted); font-size: 0.9rem;">
                    Detailed view of a specific patrol activity record.
                </p>
            </div>
            <div>
                <a href="{{ url()->previous() }}" class="btn-sapphire-outline shadow-sm">
                    <i class="bi bi-arrow-left"></i> Back to Logs
                </a>
            </div>
        </div>

        <div class="row g-4">

            {{-- BASIC INFO --}}
            <div class="col-12">
                <div class="dash-card p-4">
                    <h5 class="fw-bold mb-4" style="color: var(--text-main);">
                        <i class="bi bi-info-circle me-2" style="color: var(--sapphire-primary);"></i> General Information
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-4 col-sm-6">
                            <div class="info-block">
                                <div class="info-label">Officer Name</div>
                                <p class="info-value">{{ $log->patrolSession->user->name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-6">
                            <div class="info-block">
                                <div class="info-label">Site / Beat</div>
                                <p class="info-value">{{ $log->patrolSession->site->name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-6">
                            <div class="info-block">
                                <div class="info-label">Record Type</div>
                                <p class="info-value">
                                    <span class="badge-soft badge-soft-primary mt-1">
                                        {{ ucwords(str_replace(['-', '_'], ' ', $log->type)) }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-6">
                            <div class="info-block">
                                <div class="info-label">Date & Time</div>
                                <p class="info-value">
                                    {{ $log->created_at ? $log->created_at->format('d M, Y h:i A') : 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-6">
                            <div class="info-block">
                                <div class="info-label">Coordinates</div>
                                <p class="info-value" style="font-family: monospace;">
                                    <i class="bi bi-geo-alt-fill opacity-50 me-1"></i>
                                    {{ $log->lat ?? '-' }}, {{ $log->lng ?? '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-6">
                            <div class="info-block">
                                <div class="info-label">Notes</div>
                                <p class="info-value">{{ $log->notes ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PAYLOAD DATA --}}
            @if ($log->payload && is_array($log->payload))
                <div class="col-12">
                    <div class="dash-card p-0 overflow-hidden">
                        <div class="p-4 pb-3" style="border-bottom: 1px solid var(--border-color);">
                            <h5 class="fw-bold mb-0" style="color: var(--text-main);">
                                <i class="bi bi-code-slash me-2" style="color: var(--sapphire-primary);"></i> Payload Data
                            </h5>
                        </div>

                        <div class="table-responsive">
                            <table class="table dash-table mb-0">
                                <tbody>
                                    @foreach ($log->payload as $key => $value)
                                        <tr>
                                            <th
                                                style="width: 250px; text-transform: capitalize; border-right: 1px solid var(--border-color);">
                                                {{ ucwords(str_replace('_', ' ', $key)) }}
                                            </th>
                                            <td class="p-0">
                                                @if (is_array($value))
                                                    <table class="table dash-table mb-0 w-100"
                                                        style="background: transparent;">
                                                        <tbody>
                                                            @foreach ($value as $subKey => $subValue)
                                                                <tr>
                                                                    <th
                                                                        style="width: 200px; text-transform: capitalize; border-right: 1px dashed var(--border-color); background: rgba(0,0,0,0.02) !important;">
                                                                        {{ ucwords(str_replace('_', ' ', $subKey)) }}
                                                                    </th>
                                                                    <td>
                                                                        @if (is_array($subValue))
                                                                            <pre class="mb-0"
                                                                                style="color: var(--text-main); font-size: 0.85rem; background: var(--bg-body); padding: 8px; border-radius: 6px; border: 1px solid var(--border-color);">{{ json_encode($subValue, JSON_PRETTY_PRINT) }}</pre>
                                                                        @else
                                                                            {{ $subValue === true ? 'Yes' : ($subValue === false ? 'No' : $subValue) }}
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                @else
                                                    <div class="p-3">
                                                        {{ $value }}
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- MEDIA GALLERY --}}
            @if ($log->media && $log->media->count())
                <div class="col-12">
                    <div class="dash-card p-4 mb-5">
                        <h5 class="fw-bold mb-4" style="color: var(--text-main);">
                            <i class="bi bi-images me-2" style="color: var(--sapphire-primary);"></i> Attached Media
                        </h5>

                        <div class="row g-3">
                            @foreach ($log->media as $index => $media)
                                <div class="col-xl-2 col-lg-3 col-md-4 col-6">
                                    <div class="rounded overflow-hidden border hover-lift"
                                        style="border-color: var(--border-color); aspect-ratio: 1/1; background: var(--bg-body);">
                                        <img src="{{ $media->url }}" alt="Media Image" class="w-100 h-100 gallery-img"
                                            style="object-fit: cover;" data-bs-toggle="modal" data-bs-target="#mediaModal"
                                            data-index="{{ $index }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- BOOTSTRAP 5 MODAL FOR MEDIA --}}
                <div class="modal fade" id="mediaModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content sapphire-modal">

                            <div class="modal-header sapphire-modal-header border-0 pb-0">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                                    style="filter: var(--bs-theme) == 'dark' ? 'invert(1)' : 'none';"></button>
                            </div>

                            <div class="modal-body text-center p-3">
                                <img id="modalImage" src="" class="img-fluid rounded" alt="Media Preview"
                                    style="max-height: 70vh; object-fit: contain;">
                            </div>

                            <div
                                class="modal-footer sapphire-modal-footer d-flex justify-content-between align-items-center border-0 pt-0">
                                <button type="button" class="btn-sapphire-outline" id="prevImage">
                                    <i class="bi bi-arrow-left"></i> Previous
                                </button>
                                <span id="imageCounter"
                                    style="color: var(--text-muted); font-size: 0.9rem; font-weight: 500;"></span>
                                <button type="button" class="btn-sapphire-outline" id="nextImage">
                                    Next <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    @if ($log->media && $log->media->count())
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                let currentIndex = 0;
                const images = @json($log->media->pluck('url'));
                const totalImages = images.length;

                const modalImage = document.getElementById("modalImage");
                const imageCounter = document.getElementById("imageCounter");

                function updateModalContent() {
                    modalImage.src = images[currentIndex];
                    imageCounter.innerText = `${currentIndex + 1} of ${totalImages}`;
                }

                // Open clicked image in modal
                document.querySelectorAll(".gallery-img").forEach(img => {
                    img.addEventListener("click", function() {
                        currentIndex = parseInt(this.dataset.index);
                        updateModalContent();
                    });
                });

                // Next button
                document.getElementById("nextImage").addEventListener("click", function() {
                    currentIndex = (currentIndex + 1) % totalImages;
                    updateModalContent();
                });

                // Previous button
                document.getElementById("prevImage").addEventListener("click", function() {
                    currentIndex = (currentIndex - 1 + totalImages) % totalImages;
                    updateModalContent();
                });

                // Keyboard navigation support
                document.addEventListener('keydown', function(event) {
                    const modalElement = document.getElementById('mediaModal');
                    if (modalElement.classList.contains('show')) {
                        if (event.key === "ArrowRight") {
                            document.getElementById("nextImage").click();
                        } else if (event.key === "ArrowLeft") {
                            document.getElementById("prevImage").click();
                        }
                    }
                });
            });
        </script>
    @endif

@endsection

@php
    $hideGlobalFilters = true;
    $hideBackground = true;
    // $user = session('user');
@endphp
@extends('layouts.app')

@section('title', 'Dynamic Labels')

@push('scripts')
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush

@section('content')

    <style>
        /* =========================================
                   LOCAL COMPONENT STYLES
                   (Hooked to Global Sapphire Variables)
                ========================================= */

        /* View & Tab Toggles */
        .view-toggle {
            display: inline-flex;
            background: var(--bg-body);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 4px;
        }

        .view-toggle-btn {
            background: transparent;
            color: var(--text-muted);
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .view-toggle-btn:hover {
            color: var(--text-main);
        }

        .view-toggle-btn.active {
            background: var(--sapphire-primary);
            color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Custom Form Inputs */
        .custom-input {
            background-color: var(--bg-body);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.9rem;
            width: 100%;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .custom-input:focus {
            border-color: var(--sapphire-primary);
            background-color: var(--bg-body);
            color: var(--text-main);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        html[data-bs-theme="dark"] .custom-input {
            color-scheme: dark;
        }

        /* Cards */
        .dash-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
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

        /* Action Buttons */
        .btn-sapphire {
            background-color: var(--sapphire-primary);
            color: #ffffff;
            border: none;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
        }

        .btn-sapphire:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            color: #ffffff;
        }

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
            font-size: 0.85rem;
        }

        .btn-sapphire-outline:hover {
            background-color: var(--table-hover);
            color: var(--sapphire-primary);
            border-color: var(--sapphire-primary);
        }

        /* Icon Action Buttons (View, Edit, Delete) */
        .btn-icon-soft {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: transparent;
            transition: all 0.2s ease;
            font-size: 1.05rem;
        }

        .btn-icon-soft.edit {
            color: var(--sapphire-primary);
        }

        .btn-icon-soft.edit:hover {
            background: rgba(59, 130, 246, 0.15);
            transform: translateY(-2px);
        }

        .btn-icon-soft.delete {
            color: var(--sapphire-danger);
        }

        .btn-icon-soft.delete:hover {
            background: rgba(239, 68, 68, 0.15);
            transform: translateY(-2px);
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

        .badge-soft-success {
            background: rgba(16, 185, 129, 0.15);
            color: var(--sapphire-success);
        }

        .badge-soft-muted {
            background: rgba(100, 116, 139, 0.15);
            color: var(--text-muted);
        }

        /* Tables */
        .dash-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .dash-table th {
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.8rem;
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

        /* Modal Overrides */
        .sapphire-modal {
            background-color: var(--bg-card);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 12px;
        }

        .sapphire-modal-header {
            border-bottom: 1px solid var(--border-color);
        }

        .sapphire-modal-footer {
            border-top: 1px solid var(--border-color);
        }
    </style>

    <div class="container-fluid py-4" x-data="labelManager()">

        {{-- FLASH MESSAGE --}}
        @if (session('success'))
            <div class="alert alert-dismissible fade show shadow-sm mb-4"
                style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--sapphire-success); color: var(--sapphire-success); border-radius: 8px;">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button class="btn-close" data-bs-dismiss="alert" style="filter: opacity(0.5);"></button>
            </div>
        @endif

        {{-- COMPACT HEADER CONTROLS --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">

            {{-- Primary Tab Toggles --}}
            <div class="view-toggle shadow-sm">
                <button @click="activeTab = 'master'" :class="activeTab === 'master' ? 'active' : ''"
                    class="view-toggle-btn">
                    <i class="bi bi-journal-text me-1"></i> Master Dictionary
                </button>
                <button @click="activeTab = 'companies'" :class="activeTab === 'companies' ? 'active' : ''"
                    class="view-toggle-btn">
                    <i class="bi bi-buildings me-1"></i> Company Overrides
                </button>
            </div>

            {{-- Contextual Actions (Changes based on tab) --}}
            <div class="d-flex align-items-center gap-2">

                {{-- Master Tab Search & Add --}}
                <div x-show="activeTab === 'master'" class="d-flex gap-2" x-transition>
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute"
                            style="left: 12px; top: 10px; color: var(--text-muted);"></i>
                        <input type="text" x-model="masterSearch" class="custom-input"
                            style="padding-left: 36px; width: 250px;" placeholder="Search keys...">
                    </div>
                    <button @click="openAddMaster()" class="btn-sapphire shadow-sm text-nowrap">
                        <i class="bi bi-plus-lg"></i> Add Master Key
                    </button>
                </div>

                {{-- Companies Tab Search & View Toggle --}}
                <div x-show="activeTab === 'companies'" class="d-flex gap-3" x-transition>
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute"
                            style="left: 12px; top: 10px; color: var(--text-muted);"></i>
                        <input type="text" x-model="companySearch" class="custom-input"
                            style="padding-left: 36px; width: 250px;" placeholder="Find company...">
                    </div>

                    <div class="view-toggle shadow-sm">
                        <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'active' : ''"
                            class="view-toggle-btn px-2" title="Grid View">
                            <i class="bi bi-grid-fill"></i>
                        </button>
                        <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'active' : ''"
                            class="view-toggle-btn px-2" title="List View">
                            <i class="bi bi-list-ul"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        {{-- =========================================
         MASTER DICTIONARY TAB
    ========================================= --}}
        <div x-show="activeTab === 'master'" x-transition x-cloak class="dash-card p-0 overflow-hidden">
            <div class="d-flex justify-content-between align-items-center p-4 pb-3"
                style="border-bottom: 1px solid var(--border-color);">
                <h5 class="fw-bold mb-0" style="color: var(--text-main);">Global Defaults</h5>
                <span class="badge-soft badge-soft-muted"><span x-text="masterFields.length"></span> Keys</span>
            </div>

            <div class="table-responsive">
                <table class="table dash-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4">Field Key</th>
                            <th>Default Value</th>
                            <th>Global Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="field in filteredMasterFields" :key="field.id">
                            <tr>
                                <td class="ps-4 font-monospace fw-bold"
                                    style="color: var(--sapphire-primary); font-size: 0.85rem;" x-text="field.field_key">
                                </td>
                                <td style="color: var(--text-main); font-weight: 500;" x-text="field.default_label"></td>
                                <td><span class="badge-soft badge-soft-success">Deployed</span></td>
                                <td class="text-end pe-4">
                                    <button @click="editMaster(field)" class="btn-icon-soft edit" title="Edit Key"><i
                                            class="bi bi-pencil-square"></i></button>
                                    <button @click="confirmDelete(field)" class="btn-icon-soft delete" title="Delete Key"><i
                                            class="bi bi-trash-fill"></i></button>
                                </td>
                            </tr>
                        </template>
                        <template x-if="filteredMasterFields.length === 0">
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">No master keys found.</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- =========================================
         COMPANIES TAB (OVERRIDES)
    ========================================= --}}
        <div x-show="activeTab === 'companies'" x-transition x-cloak>

            <div x-show="viewMode === 'grid'" class="row g-4" x-transition>
                <template x-for="company in filteredCompanies" :key="company.id">
                    <div class="col-md-6 col-lg-4">
                        <div class="dash-card hover-lift h-100 p-4">

                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center justify-content-center rounded-circle fw-bold shadow-sm"
                                    style="width: 48px; height: 48px; font-size: 1.2rem; background: var(--bg-body); color: var(--sapphire-primary); border: 1px solid var(--border-color);"
                                    x-text="company.name.charAt(0)"></div>

                                <div class="d-flex align-items-center gap-2">
                                    <span :class="company.isActive ? 'badge-soft-success' : 'badge-soft-muted'"
                                        class="badge-soft" x-text="company.isActive ? 'Active' : 'Inactive'"></span>
                                    <button @click="openEditCompany(company)" class="btn-sapphire-outline px-2 py-1"
                                        style="font-size: 0.75rem;"><i class="bi bi-pencil-fill"></i> Edit</button>
                                </div>
                            </div>

                            <h5 class="fw-bold mb-1" style="color: var(--text-main);" x-text="company.name"></h5>
                            <p class="text-muted small mb-3 font-monospace" x-text="'ID: #' + company.id"></p>

                            <div class="mt-auto pt-3" style="border-top: 1px dashed var(--border-color);">
                                <p class="text-uppercase fw-bold mb-2"
                                    style="font-size: 0.7rem; color: var(--text-muted);">Active Overrides</p>

                                <div class="d-flex flex-column gap-2">
                                    <template x-if="company.field_labels.length === 0">
                                        <p
                                            style="color: var(--text-muted); font-size: 0.85rem; font-style: italic; margin: 0;">
                                            Using all default labels</p>
                                    </template>

                                    <template x-for="ov in company.field_labels.slice(0, 3)">
                                        <div class="d-flex justify-content-between align-items-center p-2 rounded"
                                            style="background: var(--bg-body); border: 1px solid var(--border-color);">
                                            <span class="font-monospace text-muted" style="font-size: 0.7rem;"
                                                x-text="ov.field_key"></span>
                                            <span class="fw-bold"
                                                style="font-size: 0.8rem; color: var(--sapphire-primary);"
                                                x-text="ov.custom_label"></span>
                                        </div>
                                    </template>

                                    <template x-if="company.field_labels.length > 3">
                                        <p class="text-center fw-bold mb-0 mt-1"
                                            style="font-size: 0.75rem; color: var(--sapphire-primary);"
                                            x-text="'+ ' + (company.field_labels.length - 3) + ' more'"></p>
                                    </template>
                                </div>
                            </div>

                        </div>
                    </div>
                </template>
            </div>

            <div x-show="viewMode === 'list'" class="dash-card p-0 overflow-hidden" x-transition>
                <div class="table-responsive">
                    <table class="table dash-table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th class="ps-4" style="width: 70px;">Sr. No.</th>
                                <th>Company Name</th>
                                <th>Custom Overrides</th>
                                <th>Status</th>
                                <th class="text-end pe-4" style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(company, index) in filteredCompanies" :key="company.id">
                                <tr>
                                    <td class="ps-4 fw-semibold" style="color: var(--text-muted);" x-text="index + 1">
                                    </td>

                                    <td>
                                        <div class="fw-bold" style="color: var(--text-main);" x-text="company.name">
                                        </div>
                                        <div class="font-monospace" style="color: var(--text-muted); font-size: 0.75rem;"
                                            x-text="'ID: #' + company.id"></div>
                                    </td>

                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <template x-if="company.field_labels.length === 0">
                                                <span
                                                    style="color: var(--text-muted); font-size: 0.85rem; font-style: italic;">No
                                                    customizations</span>
                                            </template>
                                            <template x-for="ov in company.field_labels">
                                                <span class="badge-soft badge-soft-primary">
                                                    <span x-text="ov.field_key"
                                                        style="opacity: 0.7; margin-right: 4px;"></span>
                                                    <i class="bi bi-arrow-right mx-1"></i>
                                                    <span x-text="ov.custom_label"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </td>

                                    <td>
                                        <span :class="company.isActive ? 'badge-soft-success' : 'badge-soft-muted'"
                                            class="badge-soft" x-text="company.isActive ? 'Active' : 'Inactive'"></span>
                                    </td>

                                    <td class="text-end pe-4">
                                        <button @click="openEditCompany(company)" class="btn-sapphire-outline py-1 px-3"
                                            style="font-size: 0.8rem;">
                                            <i class="bi bi-pencil-fill me-1"></i> Edit
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- =========================================
         EDIT COMPANY OVERRIDES TAB
    ========================================= --}}
        <div x-show="activeTab === 'edit_company'" x-transition x-cloak class="dash-card overflow-hidden">

            <div class="p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3"
                style="border-bottom: 1px solid var(--border-color); background: var(--bg-body);">
                <div class="d-flex align-items-center gap-3">
                    <button @click="activeTab = 'companies'" class="btn-sapphire-outline" style="padding: 6px 10px;"
                        title="Go Back">
                        <i class="bi bi-arrow-left"></i>
                    </button>
                    <div>
                        <h4 class="mb-0 fw-bold" style="color: var(--text-main);" x-text="selectedCompany?.name"></h4>
                        <div
                            style="font-size: 0.75rem; letter-spacing: 0.5px; color: var(--sapphire-primary); text-transform: uppercase; font-weight: 600;">
                            Customizing Overrides
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button @click="activeTab = 'companies'" class="btn-sapphire-outline">Discard</button>
                    <form :action="'/dynamic-labels/company/' + selectedCompany?.id" method="POST" id="companySaveForm"
                        class="m-0">
                        @csrf
                        <button type="button" @click="submitCompanyOverrides()" class="btn-sapphire">
                            <i class="bi bi-save"></i> Save & Sync
                        </button>
                    </form>
                </div>
            </div>

            <div class="p-4">

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                    <h5 class="fw-bold mb-0" style="color: var(--text-main);">Active Overrides</h5>

                    <div class="d-flex gap-2 align-items-center">
                        <select class="custom-input" x-model="selectedKeyToAdd"
                            style="width: 300px; padding-top: 6px; padding-bottom: 6px;">
                            <option value="">-- Select Master Key to Override --</option>
                            <template x-for="field in availableMasterKeysToAdd" :key="field.id">
                                <option :value="field.field_key"
                                    x-text="field.default_label + ' (' + field.field_key + ')'"></option>
                            </template>
                        </select>
                        <button class="btn-sapphire" style="padding-top: 6px; padding-bottom: 6px;"
                            @click="addOverrideField()" :disabled="!selectedKeyToAdd">
                            Add Override
                        </button>
                    </div>
                </div>

                <div class="row g-4">
                    <template x-for="fieldKey in activeOverrideKeys" :key="fieldKey">
                        <div class="col-md-6 col-lg-4">
                            <div class="p-4 rounded-3 position-relative"
                                style="background: var(--bg-body); border: 1px solid var(--border-color);">
                                <button type="button" @click="removeOverrideField(fieldKey)"
                                    class="btn-icon-soft delete position-absolute" style="top: 8px; right: 8px;">
                                    <i class="bi bi-x-circle-fill"></i>
                                </button>

                                <div class="d-flex justify-content-between align-items-start mb-3 pe-4">
                                    <span class="font-monospace fw-bold"
                                        style="font-size: 0.8rem; color: var(--text-muted);" x-text="fieldKey"></span>
                                    <span class="badge-soft badge-soft-primary">Default: <span
                                            x-text="getMasterLabel(fieldKey)"></span></span>
                                </div>
                                <div>
                                    <label class="form-label text-uppercase fw-bold"
                                        style="font-size: 0.7rem; color: var(--text-muted);">Company-Specific Label</label>
                                    <input type="text" :id="'override_' + fieldKey"
                                        :value="getExistingOverride(fieldKey)"
                                        :placeholder="'Override for ' + getMasterLabel(fieldKey)"
                                        class="custom-input company-override-input" :data-key="fieldKey">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <template x-if="activeOverrideKeys.length === 0">
                    <div class="text-center p-5">
                        <i class="bi bi-inbox fs-1 d-block mb-3" style="color: var(--text-muted); opacity: 0.5;"></i>
                        <p style="color: var(--text-muted);">No active overrides for this company. Select a master key
                            above to add an override.</p>
                    </div>
                </template>

            </div>
        </div>

        {{-- =========================================
         MODALS
    ========================================= --}}

        <div class="modal fade" id="masterModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content sapphire-modal">
                    <form :action="isEditMode ? '/dynamic-labels/master/update/' + modalForm.id : '/dynamic-labels/master'"
                        method="POST">
                        @csrf
                        <div class="modal-header sapphire-modal-header pb-3">
                            <h5 class="modal-title fw-bold"
                                x-text="isEditMode ? 'Modify Master Key' : 'Create Master Key'"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                                style="filter: var(--bs-theme) == 'dark' ? 'invert(1)' : 'none';"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-4">
                                <label class="form-label text-uppercase fw-bold"
                                    style="font-size: 0.7rem; color: var(--text-muted);">Technical Key Name</label>
                                <input name="field_key" x-model="modalForm.field_key" :readonly="isEditMode"
                                    type="text" class="custom-input font-monospace" required>
                            </div>
                            <div>
                                <label class="form-label text-uppercase fw-bold"
                                    style="font-size: 0.7rem; color: var(--text-muted);">Default Display Label</label>
                                <input name="default_label" x-model="modalForm.default_label" type="text"
                                    class="custom-input" required>
                            </div>
                        </div>
                        <div class="modal-footer sapphire-modal-footer d-flex gap-2">
                            <button type="button" class="btn-sapphire-outline flex-grow-1 justify-content-center"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn-sapphire flex-grow-1 justify-content-center"
                                x-text="isEditMode ? 'Update Key' : 'Deploy Key'"></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content sapphire-modal">
                    <div class="modal-body p-4 text-center">
                        <div class="mb-3">
                            <i class="bi bi-exclamation-triangle-fill"
                                style="font-size: 3rem; color: var(--sapphire-danger);"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Delete Master Key?</h5>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem;">
                            This will permanently remove the key "<strong x-text="deleteForm.field_key"
                                style="color: var(--text-main);"></strong>" and all its company overrides. This action
                            cannot be undone.
                        </p>

                        <form :action="'/dynamic-labels/master/delete/' + deleteForm.id" method="POST"
                            class="d-flex gap-2">
                            @csrf
                            <button type="button" class="btn-sapphire-outline flex-grow-1 justify-content-center"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn flex-grow-1 fw-bold"
                                style="background: var(--sapphire-danger); color: white;">Yes, Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function labelManager() {
            return {
                activeTab: 'master',
                viewMode: 'list',
                companySearch: '',
                masterSearch: '',

                // Laravel Passed Data
                masterFields: @json($masters),
                companies: @json($companies),

                // Modals Logic
                isEditMode: false,
                selectedCompany: null,
                selectedKeyToAdd: '',
                activeOverrideKeys: [],
                modalForm: {
                    id: '',
                    field_key: '',
                    default_label: ''
                },
                deleteForm: {
                    id: '',
                    field_key: ''
                },
                masterModalInstance: null,
                deleteModalInstance: null,

                init() {
                    this.$nextTick(() => {
                        this.masterModalInstance = new bootstrap.Modal(document.getElementById('masterModal'));
                        this.deleteModalInstance = new bootstrap.Modal(document.getElementById('deleteModal'));
                    });
                },

                get filteredMasterFields() {
                    if (this.masterSearch === '') return this.masterFields;
                    return this.masterFields.filter(f => f.field_key.toLowerCase().includes(this.masterSearch
                        .toLowerCase()) || f.default_label.toLowerCase().includes(this.masterSearch
                        .toLowerCase()));
                },

                get filteredCompanies() {
                    if (this.companySearch === '') return this.companies;
                    return this.companies.filter(c => c.name.toLowerCase().includes(this.companySearch.toLowerCase()));
                },

                get availableMasterKeysToAdd() {
                    return this.masterFields.filter(f => !this.activeOverrideKeys.includes(f.field_key));
                },

                getExistingOverride(key) {
                    if (!this.selectedCompany || !this.selectedCompany.field_labels) return '';
                    const match = this.selectedCompany.field_labels.find(o => o.field_key === key);
                    return match ? match.custom_label : '';
                },

                getMasterLabel(key) {
                    const match = this.masterFields.find(f => f.field_key === key);
                    return match ? match.default_label : '';
                },

                openEditCompany(company) {
                    this.selectedCompany = company;
                    this.activeOverrideKeys = company.field_labels ? company.field_labels.map(o => o.field_key) : [];
                    this.selectedKeyToAdd = '';
                    this.activeTab = 'edit_company';
                },

                addOverrideField() {
                    if (this.selectedKeyToAdd && !this.activeOverrideKeys.includes(this.selectedKeyToAdd)) {
                        this.activeOverrideKeys.push(this.selectedKeyToAdd);
                        this.selectedKeyToAdd = '';
                    }
                },

                removeOverrideField(key) {
                    this.activeOverrideKeys = this.activeOverrideKeys.filter(k => k !== key);
                },

                openAddMaster() {
                    this.isEditMode = false;
                    this.modalForm = {
                        id: '',
                        field_key: '',
                        default_label: ''
                    };
                    this.masterModalInstance.show();
                },

                editMaster(field) {
                    this.isEditMode = true;
                    this.modalForm = {
                        ...field
                    };
                    this.masterModalInstance.show();
                },

                confirmDelete(field) {
                    this.deleteForm = {
                        id: field.id,
                        field_key: field.field_key
                    };
                    this.deleteModalInstance.show();
                },

                submitCompanyOverrides() {
                    const form = document.getElementById('companySaveForm');
                    const inputs = document.querySelectorAll('.company-override-input');

                    form.querySelectorAll('.dyn-input').forEach(el => el.remove());

                    inputs.forEach(input => {
                        if (input.value.trim() !== '') {
                            const h = document.createElement('input');
                            h.type = 'hidden';
                            h.name = 'labels[' + input.getAttribute('data-key') + ']';
                            h.value = input.value.trim();
                            h.classList.add('dyn-input');
                            form.appendChild(h);
                        }
                    });

                    form.submit();
                }
            }
        }
    </script>
@endsection

@extends('layouts.app')

@push('scripts')
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        .bg-indigo-600 {
            background-color: #4f46e5 !important;
        }

        .text-indigo-600 {
            color: #4f46e5 !important;
        }

        .hover-bg-indigo-700:hover {
            background-color: #4338ca !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4 font-sans antialiased" x-data="labelManager()">

        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-indigo-600 p-2 rounded-3 text-white">
                    <i class="bi bi-layers-fill fs-4"></i>
                </div>
                <div>
                    <h1 class="h4 mb-0 fw-bold text-dark">Label Control <span class="text-indigo-600">Pro</span></h1>
                    <small class="text-muted text-uppercase fw-bold"
                        style="font-size: 0.65rem; letter-spacing: 1px;">Multi-Tenant Management</small>
                </div>
            </div>
            <button @click="openAddMaster()"
                class="btn text-white bg-indigo-600 hover-bg-indigo-700 rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle"></i> New Master Key
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Controls -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div class="bg-light p-1 rounded-3 d-inline-flex" style="width: fit-content;">
                <button @click="activeTab = 'master'"
                    :class="activeTab === 'master' ? 'bg-white shadow-sm text-indigo-600' : 'text-secondary hover-text-dark'"
                    class="btn border-0 rounded-2 fw-bold text-uppercase"
                    style="font-size: 0.75rem; letter-spacing: 0.5px; transition: all 0.2s;">
                    Master Dictionary
                </button>
                <button @click="activeTab = 'companies'"
                    :class="activeTab === 'companies' ? 'bg-white shadow-sm text-indigo-600' : 'text-secondary hover-text-dark'"
                    class="btn border-0 rounded-2 fw-bold text-uppercase"
                    style="font-size: 0.75rem; letter-spacing: 0.5px; transition: all 0.2s;">
                    Company Overrides
                </button>
            </div>

            <!-- View Toggles for Companies Tab -->
            <div class="d-flex align-items-center gap-2" x-show="activeTab === 'companies'">
                <div class="bg-white border p-1 rounded-3 shadow-sm d-flex">
                    <button @click="viewMode = 'list'"
                        :class="viewMode === 'list' ? 'bg-light text-indigo-600' : 'text-muted'"
                        class="btn btn-sm border-0 d-flex align-items-center gap-2 rounded-2">
                        <i class="bi bi-list"></i> <span class="d-none d-md-inline fw-bold text-uppercase"
                            style="font-size: 0.7rem;">Rows</span>
                    </button>
                    <button @click="viewMode = 'grid'"
                        :class="viewMode === 'grid' ? 'bg-light text-indigo-600' : 'text-muted'"
                        class="btn btn-sm border-0 d-flex align-items-center gap-2 rounded-2">
                        <i class="bi bi-grid-fill"></i> <span class="d-none d-md-inline fw-bold text-uppercase"
                            style="font-size: 0.7rem;">Cards</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- MAIN VIEWS -->

        <!-- Master Dictionary Tab -->
        <div x-show="activeTab === 'master'" x-transition x-cloak class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-light border-bottom p-4 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">Global Defaults <span class="badge bg-secondary ms-2"
                        x-text="masterFields.length + ' keys'"></span></h5>
                <div class="position-relative">
                    <i class="bi bi-search position-absolute text-muted" style="left: 12px; top: 10px;"></i>
                    <input type="text" x-model="masterSearch" placeholder="Search keys..."
                        class="form-control rounded-pill ps-5 bg-white border" style="width: 250px;">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 text-muted fw-bold text-uppercase"
                                style="font-size: 0.65rem; letter-spacing: 1px;">Field Key</th>
                            <th class="px-4 py-3 text-muted fw-bold text-uppercase"
                                style="font-size: 0.65rem; letter-spacing: 1px;">Default Value</th>
                            <th class="px-4 py-3 text-muted fw-bold text-uppercase"
                                style="font-size: 0.65rem; letter-spacing: 1px;">Global Status</th>
                            <th class="px-4 py-3 text-muted fw-bold text-uppercase text-end"
                                style="font-size: 0.65rem; letter-spacing: 1px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white border-top-0">
                        <template x-for="field in filteredMasterFields" :key="field.id">
                            <tr>
                                <td class="px-4 py-3 text-indigo-600 font-monospace" style="font-size: 0.85rem;"
                                    x-text="field.field_key"></td>
                                <td class="px-4 py-3 fw-semibold text-dark" x-text="field.default_label"></td>
                                <td class="px-4 py-3">
                                    <span
                                        class="badge bg-success bg-opacity-10 text-success fw-bold text-uppercase rounded-3"
                                        style="font-size: 0.65rem;">Deployed</span>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <button @click="editMaster(field)"
                                        class="btn btn-sm btn-link text-muted hover-text-indigo-600"><i
                                            class="bi bi-pencil-square"></i></button>
                                    <button @click="confirmDelete(field)" class="btn btn-sm btn-link text-muted"><i
                                            class="bi bi-trash text-danger"></i></button>
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


        <!-- Companies Tab -->
        <div x-show="activeTab === 'companies'" x-transition x-cloak>
            <div class="mb-4">
                <div class="position-relative" style="max-width: 400px;">
                    <i class="bi bi-search position-absolute text-muted" style="left: 15px; top: 12px;"></i>
                    <input x-model="companySearch" type="text" placeholder="Filter companies..."
                        class="form-control form-control-lg rounded-pill ps-5 border-0 shadow-sm">
                </div>
            </div>

            <!-- Grid View -->
            <div x-show="viewMode === 'grid'" class="row g-4" x-transition>
                <template x-for="company in filteredCompanies" :key="company.id">
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 hover-shadow transition">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="bg-indigo-600 text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm"
                                        style="width: 48px; height: 48px; font-size: 1.25rem; font-weight: bold;"
                                        x-text="company.name.charAt(0)"></div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span
                                            :class="company.isActive ? 'bg-success text-success bg-opacity-10' :
                                                'bg-secondary text-secondary bg-opacity-10'"
                                            class="badge rounded-2 text-uppercase" style="font-size: 0.65rem;"
                                            x-text="company.isActive ? 'Active' : 'Inactive'"></span>
                                        <button @click="openEditCompany(company)"
                                            class="btn btn-sm btn-link text-muted"><i
                                                class="bi bi-box-arrow-up-right"></i></button>
                                    </div>
                                </div>
                                <h5 class="fw-bold mb-1 text-dark" x-text="company.name"></h5>
                                <p class="text-muted small mb-3" x-text="'Company ID: #' + company.id"></p>

                                <hr class="text-muted opacity-25">

                                <div>
                                    <p class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.65rem;">Active
                                        Overrides</p>
                                    <div class="d-flex flex-column gap-2">
                                        <template x-if="company.field_labels.length === 0">
                                            <p class="text-muted fst-italic small mb-0">Using all default labels</p>
                                        </template>
                                        <template x-for="ov in company.field_labels.slice(0, 3)">
                                            <div
                                                class="d-flex justify-content-between align-items-center bg-light p-2 rounded-3 border">
                                                <span class="font-monospace text-muted" style="font-size: 0.65rem;"
                                                    x-text="ov.field_key"></span>
                                                <span class="fw-bold text-indigo-600" style="font-size: 0.8rem;"
                                                    x-text="ov.custom_label"></span>
                                            </div>
                                        </template>
                                        <template x-if="company.field_labels.length > 3">
                                            <p class="text-center text-indigo-600 fw-bold mb-0 mt-1"
                                                style="font-size: 0.7rem;"
                                                x-text="'+ ' + (company.field_labels.length - 3) + ' more'"></p>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- List View -->
            <div x-show="viewMode === 'list'" class="card border-0 shadow-sm rounded-4 overflow-hidden" x-transition>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 text-muted fw-bold text-uppercase"
                                    style="font-size: 0.65rem; letter-spacing: 1px;">Company Name</th>
                                <th class="px-4 py-3 text-muted fw-bold text-uppercase"
                                    style="font-size: 0.65rem; letter-spacing: 1px;">Custom Overrides</th>
                                <th class="px-4 py-3 text-muted fw-bold text-uppercase"
                                    style="font-size: 0.65rem; letter-spacing: 1px;">Status</th>
                                <th class="px-4 py-3 text-muted fw-bold text-uppercase text-end"
                                    style="font-size: 0.65rem; letter-spacing: 1px;">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white border-top-0">
                            <template x-for="company in filteredCompanies" :key="company.id">
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="fw-bold text-dark" x-text="company.name"></div>
                                        <div class="text-muted" style="font-size: 0.7rem;" x-text="'ID: ' + company.id">
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="d-flex flex-wrap gap-2">
                                            <template x-if="company.field_labels.length === 0">
                                                <span class="text-muted small">No customizations</span>
                                            </template>
                                            <template x-for="ov in company.field_labels">
                                                <div
                                                    class="d-flex border border-primary border-opacity-25 align-items-center gap-2 bg-primary bg-opacity-10 px-2 py-1 rounded-2">
                                                    <span class="text-muted" style="font-size: 0.65rem;"
                                                        x-text="ov.field_key"></span>
                                                    <i class="bi bi-arrow-right text-primary"
                                                        style="font-size: 0.65rem;"></i>
                                                    <span class="fw-bold text-primary" style="font-size: 0.75rem;"
                                                        x-text="ov.custom_label"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            :class="company.isActive ? 'bg-success text-success bg-opacity-10' :
                                                'bg-secondary text-secondary bg-opacity-10'"
                                            class="badge rounded-2 text-uppercase" style="font-size: 0.65rem;"
                                            x-text="company.isActive ? 'Active' : 'Inactive'"></span>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <button @click="openEditCompany(company)"
                                            class="btn btn-light btn-sm fw-bold px-3 shadow-sm hover-bg-indigo-700 hover-text-white transition">Edit</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <!-- Edit Company Tab -->
        <div x-show="activeTab === 'edit_company'" x-transition x-cloak
            class="card border-0 shadow-lg rounded-4 overflow-hidden">

            <div
                class="card-header bg-dark text-white p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-4">
                    <button @click="activeTab = 'companies'"
                        class="btn btn-outline-light rounded-circle shadow-sm d-flex justify-content-center align-items-center"
                        style="width: 40px; height: 40px;"><i class="bi bi-chevron-left"></i></button>
                    <div>
                        <h3 class="mb-0 fw-bold" x-text="selectedCompany?.name"></h3>
                        <div class="text-info text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">
                            Customizing Company Overrides
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-3">
                    <button @click="activeTab = 'companies'"
                        class="btn btn-link text-light text-decoration-none fw-bold">Discard</button>
                    <form :action="'/dynamic-labels/company/' + selectedCompany?.id" method="POST" id="companySaveForm">
                        @csrf
                        <!-- Hidden inputs will be generated before submit -->
                        <button type="button" @click="submitCompanyOverrides()"
                            class="btn btn-info fw-bold text-white shadow-sm px-4 rounded-3 d-flex align-items-center gap-2">
                            <i class="bi bi-cloud-arrow-up-fill"></i> Save & Sync
                        </button>
                    </form>
                </div>
            </div>

            <div class="card-body p-4 bg-light">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Active Overrides</h5>
                    
                    <div class="d-flex gap-2 align-items-center">
                        <select class="form-select form-select-sm" x-model="selectedKeyToAdd" style="width: 250px;">
                            <option value="">-- Select Master Key to Override --</option>
                            <template x-for="field in availableMasterKeysToAdd" :key="field.id">
                                <option :value="field.field_key" x-text="field.default_label + ' (' + field.field_key + ')'"></option>
                            </template>
                        </select>
                        <button class="btn btn-sm btn-primary fw-bold" @click="addOverrideField()" :disabled="!selectedKeyToAdd">Add Override</button>
                    </div>
                </div>

                <div class="row g-4">
                    <template x-for="fieldKey in activeOverrideKeys" :key="fieldKey">
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 position-relative hover-shadow transition">
                                <button type="button" @click="removeOverrideField(fieldKey)" class="btn btn-sm btn-link text-danger position-absolute" style="top: 10px; right: 10px;">
                                    <i class="bi bi-x-circle-fill fs-5"></i>
                                </button>

                                <div class="d-flex justify-content-between align-items-start mb-3 pe-4">
                                    <span class="font-monospace fw-bold text-muted" style="font-size: 0.7rem;"
                                        x-text="fieldKey"></span>
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill fw-bold"
                                        style="font-size: 0.65rem;">MASTER: <span
                                            x-text="getMasterLabel(fieldKey)"></span></span>
                                </div>
                                <div>
                                    <label class="form-label text-uppercase fw-bold text-secondary"
                                        style="font-size: 0.7rem;">Company-Specific Label</label>
                                    <input type="text" :id="'override_' + fieldKey"
                                        :value="getExistingOverride(fieldKey)"
                                        :placeholder="'Override for ' + getMasterLabel(fieldKey)"
                                        class="form-control form-control-lg bg-light border-0 shadow-none company-override-input"
                                        :data-key="fieldKey" style="font-size: 0.9rem;">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <template x-if="activeOverrideKeys.length === 0">
                    <div class="text-center p-5 text-muted">
                        <i class="bi bi-inbox fs-1"></i>
                        <p class="mt-3">No active overrides for this company. Select a master key above to add an override.</p>
                    </div>
                </template>
            </div>
        </div>


        <!-- Add/Edit Master Modal -->
        <div class="modal fade" id="masterModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <form :action="isEditMode ? '/dynamic-labels/master/update/' + modalForm.id : '/dynamic-labels/master'"
                        method="POST">
                        @csrf
                        <div class="modal-header border-0 p-4 pb-0">
                            <h5 class="modal-title fw-bold"
                                x-text="isEditMode ? 'Modify Master Key' : 'Create Master Key'"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-4">
                                <label class="form-label text-uppercase text-muted fw-bold"
                                    style="font-size: 0.7rem;">Technical Key Name</label>
                                <input name="field_key" x-model="modalForm.field_key" :readonly="isEditMode"
                                    type="text" class="form-control form-control-lg bg-light border-0 font-monospace"
                                    required>
                            </div>
                            <div>
                                <label class="form-label text-uppercase text-muted fw-bold"
                                    style="font-size: 0.7rem;">Default Display Label</label>
                                <input name="default_label" x-model="modalForm.default_label" type="text"
                                    class="form-control form-control-lg border ps-3" required>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0 d-flex gap-2">
                            <button type="button" class="btn btn-light fw-bold flex-grow-1 py-2"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit"
                                class="btn text-white bg-indigo-600 hover-bg-indigo-700 fw-bold flex-grow-1 py-2 shadow-sm"
                                x-text="isEditMode ? 'Update Key' : 'Deploy Key'"></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-body p-4 text-center">
                        <div class="text-danger mb-3">
                            <i class="bi bi-exclamation-triangle-fill" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Delete Master Key?</h5>
                        <p class="text-muted small mb-4">This will permanently remove the key "<strong
                                x-text="deleteForm.field_key"></strong>" and all its company overrides. This action cannot
                            be undone.</p>

                        <form :action="'/dynamic-labels/master/delete/' + deleteForm.id" method="POST"
                            class="d-flex gap-2">
                            @csrf
                            <button type="button" class="btn btn-light fw-bold flex-grow-1"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger fw-bold flex-grow-1">Yes, Delete</button>
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
                    // Initialize Bootstrap modals
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
                    if(this.selectedKeyToAdd && !this.activeOverrideKeys.includes(this.selectedKeyToAdd)) {
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

                    // Clear old hidden inputs to prevent duplicates if clicked twice
                    form.querySelectorAll('.dyn-input').forEach(el => el.remove());

                    inputs.forEach(input => {
                        // Only send if there's a value (otherwise let backend handle emptying/ignoring)
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

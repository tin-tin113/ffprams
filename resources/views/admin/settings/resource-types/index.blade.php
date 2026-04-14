@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Navigation Tabs --}}
    <div class="row mb-4">
        <div class="col-12">
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom settings-tabs-nav">
                <div class="container-fluid px-0">
                    <ul class="navbar-nav w-100">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.settings.program-names.index') }}">
                                <i class="bi bi-list"></i> Programs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.settings.index') }}">
                                <i class="bi bi-building"></i> Agencies
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.settings.purposes.index') }}">
                                <i class="bi bi-tasks"></i> Assistance Purposes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="{{ route('admin.settings.resource-types.index') }}">
                                <i class="bi bi-box"></i> Resource Types
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.settings.form-fields.index') }}">
                                <i class="bi bi-file-form"></i> Form Fields
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            {{-- Tab Navigation --}}
            <ul class="nav nav-tabs border-bottom" id="rtTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="resourceTypesTab" data-bs-toggle="tab" data-bs-target="#resourceTypesContent" type="button" role="tab" aria-controls="resourceTypesContent" aria-selected="true">
                        <i class="bi bi-box"></i> Resource Types
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="purposesTab" data-bs-toggle="tab" data-bs-target="#purposesContent" type="button" role="tab" aria-controls="purposesContent" aria-selected="false">
                        <i class="bi bi-tasks"></i> Assistance Purposes
                    </button>
                </li>
            </ul>
        </div>
    </div>

    {{-- Tab Content --}}
    <div class="tab-content" id="rtTabsContent">
        {{-- Tab 1: Resource Types --}}
        <div class="tab-pane fade show active" id="resourceTypesContent" role="tabpanel" aria-labelledby="resourceTypesTab">
            <div class="row mb-4 mt-4">
                <div class="col-12">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
                        <h5 class="mb-0">
                            <i class="bi bi-box"></i> Resource Types
                        </h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#rtModal">
                            <i class="bi bi-plus"></i> Add Resource Type
                        </button>
                    </div>
                    <p class="text-muted small">Manage different types of resources that can be distributed</p>
                </div>
            </div>

            {{-- Filter Section --}}
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Status</label>
                                    <select id="rtStatusFilter" class="form-select form-select-sm">
                                        <option value="">All Status</option>
                                        <option value="active">Active Only</option>
                                        <option value="inactive">Inactive Only</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-8">
                                    <label class="form-label fw-semibold">Search</label>
                                    <input type="text" id="rtSearch" class="form-control form-control-sm"
                                           placeholder="Search by name or description...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Resource Types Table --}}
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0 table-responsive-cards">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Unit</th>
                                        <th>Agency</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="rtTableBody">
                                    @forelse($resourceTypes as $resourceType)
                                    <tr data-rt-id="{{ $resourceType->id }}">
                                        <td data-label="Name"><strong>{{ $resourceType->name }}</strong></td>
                                        <td data-label="Unit">
                                            <span class="badge bg-light text-dark border">{{ $resourceType->unit }}</span>
                                        </td>
                                        <td data-label="Agency">
                                            <span class="badge bg-secondary">{{ $resourceType->agency->name ?? 'N/A' }}</span>
                                        </td>
                                        <td data-label="Description">
                                            <small class="text-muted">{{ Str::limit($resourceType->description, 50) }}</small>
                                        </td>
                                        <td data-label="Status">
                                            @php $isActive = (bool) ($resourceType->is_active ?? true); @endphp
                                            <span class="badge {{ $isActive ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $isActive ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-center" data-label="Actions">
                                            <button class="btn btn-sm btn-outline-primary edit-rt"
                                                    data-id="{{ $resourceType->id }}"
                                                    data-name="{{ $resourceType->name }}"
                                                    data-unit="{{ $resourceType->unit }}"
                                                    data-agency-id="{{ $resourceType->agency_id }}"
                                                    data-description="{{ $resourceType->description }}"
                                                    data-active="{{ (int) ($resourceType->is_active ?? 1) }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rtModal"
                                                    title="Edit this resource type">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-rt"
                                                    data-id="{{ $resourceType->id }}"
                                                    data-name="{{ $resourceType->name }}"
                                                    title="Delete this resource type">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No resource types found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab 2: Assistance Purposes --}}
        <div class="tab-pane fade" id="purposesContent" role="tabpanel" aria-labelledby="purposesTab">
            <div class="row mb-4 mt-4">
                <div class="col-12">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
                        <h5 class="mb-0">
                            <i class="bi bi-tasks"></i> Assistance Purposes
                        </h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#purposeModal">
                            <i class="bi bi-plus"></i> Add Purpose
                        </button>
                    </div>
                    <p class="text-muted small">Manage assistance funding purposes and categories</p>
                </div>
            </div>

            {{-- Filters Section for Purposes --}}
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Category</label>
                                    <select id="purposeCategoryFilter" class="form-select form-select-sm">
                                        <option value="">All Categories</option>
                                        <option value="agricultural">Agricultural</option>
                                        <option value="fishery">Fishery</option>
                                        <option value="livelihood">Livelihood</option>
                                        <option value="medical">Medical</option>
                                        <option value="emergency">Emergency</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Status</label>
                                    <select id="purposeStatusFilter" class="form-select form-select-sm">
                                        <option value="">All Status</option>
                                        <option value="active">Active Only</option>
                                        <option value="inactive">Inactive Only</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Search</label>
                                    <input type="text" id="purposeSearch" class="form-control form-control-sm"
                                           placeholder="Search by name...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Purposes Table --}}
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0 table-responsive-cards">
                                <thead class="table-light">
                                    <tr>
                                        <th>Category</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="purposesTableBody">
                                    @forelse($purposes as $purpose)
                                    <tr data-purpose-id="{{ $purpose->id }}">
                                        <td data-label="Category">
                                            <span class="badge bg-info">{{ $purpose->category }}</span>
                                        </td>
                                        <td data-label="Name"><strong>{{ $purpose->name }}</strong></td>
                                        <td data-label="Description">
                                            <small class="text-muted">{{ Str::limit($purpose->description, 50) }}</small>
                                        </td>
                                        <td data-label="Status">
                                            <span class="badge {{ $purpose->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $purpose->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-center text-nowrap" data-label="Actions">
                                            <div class="purpose-actions">
                                                <button class="btn btn-sm btn-outline-primary edit-purpose"
                                                        data-id="{{ $purpose->id }}"
                                                        data-name="{{ $purpose->name }}"
                                                        data-category="{{ $purpose->category }}"
                                                        data-description="{{ $purpose->description }}"
                                                        data-active="{{ $purpose->is_active }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#purposeModal"
                                                        title="Edit this purpose">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>

                                                @if($purpose->is_active)
                                                    <button class="btn btn-sm btn-outline-danger deactivate-purpose"
                                                            data-id="{{ $purpose->id }}"
                                                            data-name="{{ $purpose->name }}"
                                                            title="Deactivate this purpose">
                                                        <i class="bi bi-prohibition"></i> Deactivate
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-outline-success activate-purpose"
                                                            data-id="{{ $purpose->id }}"
                                                            data-name="{{ $purpose->name }}"
                                                            data-category="{{ $purpose->category }}"
                                                            title="Activate this purpose">
                                                        <i class="bi bi-check-circle"></i> Activate
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No purposes found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add/Edit Resource Type Modal --}}
<div class="modal fade" id="rtModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rtModalTitle">Add Resource Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @php
                    $defaultUnits = ['kg', 'sacks', 'units', 'heads', 'liters', 'bags', 'packs', 'sets', 'pieces', 'PHP'];
                    $unitOptions = collect($resourceTypes)
                        ->pluck('unit')
                        ->filter()
                        ->map(fn ($unit) => trim((string) $unit))
                        ->filter()
                        ->merge($defaultUnits)
                        ->unique()
                        ->sort()
                        ->values();
                @endphp
                <form id="rtForm">
                    <input type="hidden" id="rtId">

                    <div class="mb-3">
                        <label for="rtAgencyId" class="form-label">Agency <span class="text-danger">*</span></label>
                        <select id="rtAgencyId" class="form-select form-select-sm" required>
                            <option value="" disabled selected>Select agency...</option>
                            @foreach($agencies as $agency)
                            @if($agency->is_active)
                            <option value="{{ $agency->id }}">{{ $agency->name }} — {{ $agency->full_name }}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="rtName" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" id="rtName" class="form-control form-control-sm" required>
                    </div>

                    <div class="mb-3">
                        <label for="rtUnit" class="form-label">Unit <span class="text-danger">*</span></label>
                        <select id="rtUnit" class="form-select form-select-sm" required>
                            <option value="" disabled selected>Select unit...</option>
                            @foreach($unitOptions as $unit)
                            <option value="{{ $unit }}">{{ $unit }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="rtDescription" class="form-label">Description</label>
                        <textarea id="rtDescription" class="form-control form-control-sm" rows="3"></textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" id="rtIsActive" class="form-check-input" checked>
                        <label class="form-check-label" for="rtIsActive">
                            Active
                        </label>
                    </div>

                    <div id="rtErrors" class="alert alert-danger d-none"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="rtSaveBtn">Save</button>
            </div>
        </div>
    </div>
</div>

{{-- Add/Edit Assistance Purpose Modal --}}
<div class="modal fade" id="purposeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="purposeModalTitle">Add Assistance Purpose</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="purposeForm">
                    <input type="hidden" id="purposeId">

                    <div class="mb-3">
                        <label for="purposeCategory" class="form-label">Category <span class="text-danger">*</span></label>
                        <input type="text" id="purposeCategory" class="form-control form-control-sm" required>
                        <small class="text-muted d-block mt-1">e.g., Production, Livelihood, Emergency</small>
                    </div>

                    <div class="mb-3">
                        <label for="purposeName" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" id="purposeName" class="form-control form-control-sm" required>
                    </div>

                    <div class="mb-3">
                        <label for="purposeDescription" class="form-label">Description</label>
                        <textarea id="purposeDescription" class="form-control form-control-sm" rows="3"></textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" id="purposeIsActive" class="form-check-input" checked>
                        <label class="form-check-label" for="purposeIsActive">
                            Active
                        </label>
                    </div>

                    <div id="purposeErrors" class="alert alert-danger d-none"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="purposeSaveBtn">Save</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Page-specific styles - only affect content area, not sidebar */
    .navbar {
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    /* Note: .nav-link styles below only apply to navbar within this page content, not sidebar */
    /* Sidebar navigation is managed in layouts/app.blade.php */
    .main-content .navbar .nav-link {
        border-right: 1px solid #e0e0e0;
        padding: 12px 16px !important;
        color: #6c757d !important;
        transition: all 0.3s ease;
    }

    .main-content .navbar .nav-link:last-child {
        border-right: none;
    }

    .main-content .navbar .nav-link:hover {
        background-color: #f8f9fa;
        color: #0056b3 !important;
    }

    .main-content .navbar .nav-link.active {
        background-color: #0056b3;
        color: white !important;
    }

    .purpose-actions {
        display: inline-flex;
        flex-direction: column;
        gap: 0.35rem;
        align-items: stretch;
        width: 100%;
    }

    .purpose-actions .btn {
        min-width: 0;
        width: 100%;
    }

    @media (min-width: 1200px) {
        .purpose-actions {
            flex-direction: row;
            align-items: center;
            width: auto;
        }

        .purpose-actions .btn {
            width: auto;
            min-width: 108px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function ensureUnitOption(value) {
        if (!value) return;

        const unitSelect = document.getElementById('rtUnit');
        const hasOption = Array.from(unitSelect.options).some(option => option.value === value);

        if (!hasOption) {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            unitSelect.appendChild(option);
        }
    }

    // Combined filter function
    function applyFilters() {
        const statusFilter = document.getElementById('rtStatusFilter').value;
        const searchQuery = document.getElementById('rtSearch').value.toLowerCase();

        document.querySelectorAll('#rtTableBody tr').forEach(row => {
            let show = true;

            // Status filter
            if (statusFilter && show) {
                const statusBadge = row.querySelector('td:nth-child(4) .badge');
                const isActive = statusBadge.textContent.includes('Active');
                show = show && ((statusFilter === 'active' && isActive) || (statusFilter === 'inactive' && !isActive));
            }

            // Search filter
            if (searchQuery && show) {
                const text = row.textContent.toLowerCase();
                show = show && text.includes(searchQuery);
            }

            row.style.display = show ? '' : 'none';
        });
    }

    // Filter change events
    document.getElementById('rtStatusFilter').addEventListener('change', applyFilters);
    document.getElementById('rtSearch').addEventListener('input', applyFilters);

    // Edit resource type
    document.querySelectorAll('.edit-rt').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('rtId').value = this.dataset.id;
            document.getElementById('rtName').value = this.dataset.name;
            ensureUnitOption(this.dataset.unit);
            document.getElementById('rtUnit').value = this.dataset.unit || '';
            document.getElementById('rtAgencyId').value = this.dataset.agencyId;
            document.getElementById('rtDescription').value = this.dataset.description;
            document.getElementById('rtIsActive').checked = this.dataset.active === '1';
            document.getElementById('rtModalTitle').textContent = 'Edit Resource Type';
        });
    });

    // Reset form
    document.getElementById('rtModal').addEventListener('show.bs.modal', function(e) {
        if (!e.relatedTarget || !e.relatedTarget.classList.contains('edit-rt')) {
            document.getElementById('rtForm').reset();
            document.getElementById('rtId').value = '';
            document.getElementById('rtModalTitle').textContent = 'Add Resource Type';
        }
    });

    // Save
    document.getElementById('rtSaveBtn').addEventListener('click', async function() {
        const id = document.getElementById('rtId').value;
        const method = id ? 'PUT' : 'POST';
        const url = id ? `/admin/settings/resource-types/${id}` : '/admin/settings/resource-types';
        const csrftoken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': csrftoken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    agency_id: document.getElementById('rtAgencyId').value,
                    name: document.getElementById('rtName').value,
                    unit: document.getElementById('rtUnit').value,
                    description: document.getElementById('rtDescription').value,
                    is_active: document.getElementById('rtIsActive').checked
                })
            });

            const data = await response.json();
            if (response.ok) {
                location.reload();
            } else {
                const errorsDiv = document.getElementById('rtErrors');
                errorsDiv.textContent = Object.values(data.errors || {}).flat().join('\n') || data.message;
                errorsDiv.classList.remove('d-none');
            }
        } catch (error) {
            document.getElementById('rtErrors').textContent = 'An error occurred';
            document.getElementById('rtErrors').classList.remove('d-none');
        }
    });

    // Delete
    document.querySelectorAll('.delete-rt').forEach(btn => {
        btn.addEventListener('click', function() {
            confirmThenRun(
                'Confirm Deletion',
                `Delete "${this.dataset.name}"? This action cannot be undone.`,
                function () {
                const csrftoken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`/admin/settings/resource-types/${this.dataset.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrftoken,
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Unable to delete resource type.');
                    }
                });
                }.bind(this)
            );
        });
    });

    // ========== ASSISTANCE PURPOSES FUNCTIONS ==========

    // Combined filter function for purposes
    function applyPurposeFilters() {
        const categoryFilter = document.getElementById('purposeCategoryFilter').value.toLowerCase();
        const statusFilter = document.getElementById('purposeStatusFilter').value;
        const searchQuery = document.getElementById('purposeSearch').value.toLowerCase();

        document.querySelectorAll('#purposesTableBody tr').forEach(row => {
            let show = true;

            // Category filter
            if (categoryFilter && show) {
                const categoryBadge = row.querySelector('td:nth-child(1) .badge');
                const rowCategory = categoryBadge.textContent.toLowerCase();
                show = show && rowCategory === categoryFilter;
            }

            // Status filter
            if (statusFilter && show) {
                const statusBadge = row.querySelector('td:nth-child(4) .badge');
                const isActive = statusBadge.textContent.includes('Active');
                show = show && ((statusFilter === 'active' && isActive) || (statusFilter === 'inactive' && !isActive));
            }

            // Search filter
            if (searchQuery && show) {
                const text = row.textContent.toLowerCase();
                show = show && text.includes(searchQuery);
            }

            row.style.display = show ? '' : 'none';
        });
    }

    // Filter change events for purposes
    document.getElementById('purposeCategoryFilter').addEventListener('change', applyPurposeFilters);
    document.getElementById('purposeStatusFilter').addEventListener('change', applyPurposeFilters);
    document.getElementById('purposeSearch').addEventListener('input', applyPurposeFilters);

    // Edit purpose
    document.querySelectorAll('.edit-purpose').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('purposeId').value = this.dataset.id;
            document.getElementById('purposeName').value = this.dataset.name;
            document.getElementById('purposeCategory').value = this.dataset.category;
            document.getElementById('purposeDescription').value = this.dataset.description;
            document.getElementById('purposeIsActive').checked = this.dataset.active === '1';
            document.getElementById('purposeModalTitle').textContent = 'Edit Assistance Purpose';
        });
    });

    // Reset form for new purpose
    document.getElementById('purposeModal').addEventListener('show.bs.modal', function(e) {
        if (!e.relatedTarget || !e.relatedTarget.classList.contains('edit-purpose')) {
            document.getElementById('purposeForm').reset();
            document.getElementById('purposeId').value = '';
            document.getElementById('purposeModalTitle').textContent = 'Add Assistance Purpose';
        }
    });

    // Save purpose
    document.getElementById('purposeSaveBtn').addEventListener('click', async function() {
        const id = document.getElementById('purposeId').value;
        const method = id ? 'PUT' : 'POST';
        const url = id
            ? `/admin/settings/purposes/${id}`
            : '/admin/settings/purposes';

        const csrftoken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': csrftoken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    category: document.getElementById('purposeCategory').value,
                    name: document.getElementById('purposeName').value,
                    description: document.getElementById('purposeDescription').value,
                    is_active: document.getElementById('purposeIsActive').checked
                })
            });

            const data = await response.json();

            if (response.ok) {
                location.reload();
            } else {
                const errorsDiv = document.getElementById('purposeErrors');
                if (data.errors) {
                    errorsDiv.textContent = Object.values(data.errors).flat().join('\n');
                } else {
                    errorsDiv.textContent = data.message || 'An error occurred';
                }
                errorsDiv.classList.remove('d-none');
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('purposeErrors').textContent = 'An error occurred. Please try again.';
            document.getElementById('purposeErrors').classList.remove('d-none');
        }
    });

    // Deactivate purpose
    document.querySelectorAll('.deactivate-purpose').forEach(btn => {
        btn.addEventListener('click', function() {
            confirmThenRun(
                'Confirm Deactivation',
                `Are you sure you want to deactivate "${this.dataset.name}"?`,
                function () {
                const csrftoken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`/admin/settings/purposes/${this.dataset.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrftoken,
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Unable to deactivate purpose.');
                    }
                });
                }.bind(this)
            );
        });
    });

    // Activate purpose
    document.querySelectorAll('.activate-purpose').forEach(btn => {
        btn.addEventListener('click', function() {
            confirmThenRun(
                'Confirm Activation',
                `Are you sure you want to activate "${this.dataset.name}"?`,
                function () {
                const csrftoken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`/admin/settings/purposes/${this.dataset.id}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': csrftoken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: this.dataset.name,
                        category: this.dataset.category,
                        is_active: true,
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Unable to activate purpose.');
                    }
                });
                }.bind(this)
            );
        });
    });
});
</script>

@endsection

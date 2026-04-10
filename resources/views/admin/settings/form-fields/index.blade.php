@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Navigation Tabs --}}
    <div class="row mb-4">
        <div class="col-12">
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <div class="container-fluid px-0">
                    <ul class="navbar-nav w-100">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.settings.index') }}">
                                <i class="fas fa-building"></i> Agencies
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.settings.purposes.index') }}">
                                <i class="fas fa-tasks"></i> Assistance Purposes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.settings.resource-types.index') }}">
                                <i class="fas fa-boxes"></i> Resource Types
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.settings.program-names.index') }}">
                                <i class="fas fa-list"></i> Program Names
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="{{ route('admin.settings.form-fields.index') }}">
                                <i class="fas fa-wpforms"></i> Form Fields
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="mb-3">
                <h3 class="mb-0">
                    <i class="fas fa-wpforms"></i> Form Fields
                </h3>
            </div>
            <p class="text-muted small">Manage form field groups and options</p>
        </div>
    </div>

    {{-- Sticky Action Bar --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0 ff-toolbar">
                <div class="card-body py-2 px-3">
                    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
                        <div>
                            <label for="statusFilter" class="form-label fw-600 mb-1">Status</label>
                            <select id="statusFilter" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="active">Active Only</option>
                                <option value="inactive">Inactive Only</option>
                            </select>
                        </div>

                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#ffModal">
                            <i class="fas fa-plus"></i> Add Field
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $allFieldOptions = $formFields->flatten(1);
        $totalFieldGroups = $formFields->count();
        $activeFieldOptions = $allFieldOptions->where('is_active', true)->count();
        $inactiveFieldOptions = $allFieldOptions->where('is_active', false)->count();
    @endphp

    {{-- Summary Chips --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-2">
                <div class="ff-summary-chip">
                    <span class="label">Field Groups</span>
                    <span class="value">{{ $totalFieldGroups }}</span>
                </div>
                <div class="ff-summary-chip ff-summary-chip-active">
                    <span class="label">Active Options</span>
                    <span class="value">{{ $activeFieldOptions }}</span>
                </div>
                <div class="ff-summary-chip ff-summary-chip-inactive">
                    <span class="label">Inactive Options</span>
                    <span class="value">{{ $inactiveFieldOptions }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Accordion for Field Groups --}}
    <div class="row">
        <div class="col-12">
            <div class="accordion" id="formFieldsAccordion">
                @forelse($formFields as $fieldGroup => $options)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#ff-{{ str_replace('_', '-', $fieldGroup) }}">
                            <span>{{ Str::title(str_replace('_', ' ', $fieldGroup)) }}</span>
                            <span class="badge bg-secondary ms-2">{{ count($options) }}</span>
                        </button>
                    </h2>
                    <div id="ff-{{ str_replace('_', '-', $fieldGroup) }}" class="accordion-collapse collapse"
                         data-bs-parent="#formFieldsAccordion">
                        <div class="accordion-body p-0">
                            {{-- Field Options Table --}}
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30%;">Label</th>
                                            <th style="width: 30%;">Value</th>
                                            <th style="width: 20%;">Order</th>
                                            <th style="width: 10%;">Status</th>
                                            <th style="width: 10%;" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="ff-tbody" data-field="{{ $fieldGroup }}">
                                        @forelse($options as $option)
                                        <tr data-ff-id="{{ $option->id }}">
                                            <td><strong>{{ $option->label }}</strong></td>
                                            <td><code>{{ $option->value }}</code></td>
                                            <td>
                                                <small class="text-muted">{{ $option->sort_order }}</small>
                                            </td>
                                            <td>
                                                <span class="badge {{ $option->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $option->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-primary edit-ff"
                                                        data-id="{{ $option->id }}"
                                                        data-label="{{ $option->label }}"
                                                        data-value="{{ $option->value }}"
                                                        data-field-group="{{ $option->field_group }}"
                                                        data-sort-order="{{ $option->sort_order }}"
                                                        data-active="{{ $option->is_active }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#ffModal"
                                                        title="Edit this field">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger delete-ff"
                                                        data-id="{{ $option->id }}"
                                                        data-label="{{ $option->label }}"
                                                        title="Delete this field">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">
                                                No options for this field
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="alert alert-warning" role="alert">
                    No form fields configured. Please add form fields first.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Add/Edit Form Field Modal --}}
<div class="modal fade" id="ffModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ffModalTitle">Add Form Field</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="ffForm">
                    <input type="hidden" id="ffId">

                    <div class="mb-3">
                        <label for="ffFieldGroup" class="form-label">Field Group <span class="text-danger">*</span></label>
                        <select id="ffFieldGroup" class="form-select form-select-sm" required>
                            <option value="" disabled selected>Select or type new group...</option>
                            @foreach($formFields->keys() as $group)
                            <option value="{{ $group }}">{{ Str::title(str_replace('_', ' ', $group)) }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1 mb-2">Or type a new field group name</small>
                        <input type="text" id="ffCustomFieldName" class="form-control form-control-sm"
                               placeholder="e.g., custom_field_name">
                    </div>

                    <div class="mb-3">
                        <label for="ffLabel" class="form-label">Label <span class="text-danger">*</span></label>
                        <input type="text" id="ffLabel" class="form-control form-control-sm" required>
                        <small class="text-muted">Display name for the field option</small>
                    </div>

                    <div class="mb-3">
                        <label for="ffValue" class="form-label">Value <span class="text-danger">*</span></label>
                        <input type="text" id="ffValue" class="form-control form-control-sm" required>
                        <small class="text-muted">Data value (no spaces)</small>
                    </div>

                    <div class="mb-3">
                        <label for="ffSortOrder" class="form-label">Sort Order</label>
                        <input type="number" id="ffSortOrder" class="form-control form-control-sm" min="0" value="0">
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" id="ffIsActive" class="form-check-input" checked>
                        <label class="form-check-label" for="ffIsActive">
                            Active
                        </label>
                    </div>

                    <div id="ffErrors" class="alert alert-danger d-none"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="ffSaveBtn">Save</button>
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

    .ff-toolbar {
        position: sticky;
        top: calc(var(--header-height) + 0.6rem);
        z-index: 20;
    }

    .ff-toolbar .form-select {
        min-width: 220px;
    }

    .ff-summary-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.4rem 0.75rem;
        border-radius: 999px;
        border: 1px solid #dbe4ee;
        background: #ffffff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
        font-size: 0.8rem;
        color: #334155;
    }

    .ff-summary-chip .value {
        font-weight: 700;
        color: #0f172a;
    }

    .ff-summary-chip-active {
        border-color: #bbf7d0;
        background: #f0fdf4;
    }

    .ff-summary-chip-active .value {
        color: #166534;
    }

    .ff-summary-chip-inactive {
        border-color: #e2e8f0;
        background: #f8fafc;
    }

    .ff-summary-chip-inactive .value {
        color: #475569;
    }

    @media (max-width: 767.98px) {
        .ff-toolbar {
            top: calc(var(--header-height) + 0.4rem);
        }

        .ff-toolbar .form-select {
            min-width: 0;
            width: 100%;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Combined filter function for form fields
    function applyFieldFilters() {
        const statusFilter = document.getElementById('statusFilter').value;

        // Apply status filter to all field groups
        document.querySelectorAll('.ff-tbody').forEach(tbody => {
            tbody.querySelectorAll('tr').forEach(row => {
                let show = true;

                if (statusFilter) {
                    const statusBadge = row.querySelector('td:nth-child(4) .badge');
                    if (statusBadge) {
                        const isActive = statusBadge.textContent.includes('Active');
                        show = show && ((statusFilter === 'active' && isActive) || (statusFilter === 'inactive' && !isActive));
                    }
                }

                row.style.display = show ? '' : 'none';
            });
        });
    }

    document.getElementById('statusFilter').addEventListener('change', applyFieldFilters);

    // Edit form field
    document.querySelectorAll('.edit-ff').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('ffId').value = this.dataset.id;
            document.getElementById('ffLabel').value = this.dataset.label;
            document.getElementById('ffValue').value = this.dataset.value;
            document.getElementById('ffFieldGroup').value = this.dataset.fieldGroup;
            document.getElementById('ffSortOrder').value = this.dataset.sortOrder;
            document.getElementById('ffIsActive').checked = this.dataset.active === '1';
            document.getElementById('ffModalTitle').textContent = 'Edit Form Field';
        });
    });

    // Reset form
    document.getElementById('ffModal').addEventListener('show.bs.modal', function(e) {
        if (!e.relatedTarget || !e.relatedTarget.classList.contains('edit-ff')) {
            document.getElementById('ffForm').reset();
            document.getElementById('ffId').value = '';
            document.getElementById('ffCustomFieldName').value = '';
            document.getElementById('ffModalTitle').textContent = 'Add Form Field';
        }
    });

    // Save
    document.getElementById('ffSaveBtn').addEventListener('click', async function() {
        const id = document.getElementById('ffId').value;
        const method = id ? 'PUT' : 'POST';
        const url = id ? `/admin/settings/form-fields/${id}` : '/admin/settings/form-fields';
        const csrftoken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Get field group (custom or selected)
        let fieldGroup = document.getElementById('ffCustomFieldName').value;
        if (!fieldGroup) {
            fieldGroup = document.getElementById('ffFieldGroup').value;
        }

        if (!fieldGroup) {
            alert('Please select or enter a field group');
            return;
        }

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': csrftoken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    field_group: fieldGroup,
                    label: document.getElementById('ffLabel').value,
                    value: document.getElementById('ffValue').value,
                    sort_order: document.getElementById('ffSortOrder').value,
                    is_active: document.getElementById('ffIsActive').checked
                })
            });

            const data = await response.json();
            if (response.ok) {
                location.reload();
            } else {
                const errorsDiv = document.getElementById('ffErrors');
                errorsDiv.textContent = Object.values(data.errors || {}).flat().join('\n') || data.message;
                errorsDiv.classList.remove('d-none');
            }
        } catch (error) {
            document.getElementById('ffErrors').textContent = 'An error occurred';
            document.getElementById('ffErrors').classList.remove('d-none');
        }
    });

    // Delete
    document.querySelectorAll('.delete-ff').forEach(btn => {
        btn.addEventListener('click', function() {
            confirmThenRun(
                'Confirm Deletion',
                `Delete "${this.dataset.label}"? This action cannot be undone.`,
                function () {
                const csrftoken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`/admin/settings/form-fields/${this.dataset.id}`, {
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
                        alert(data.message || 'Unable to delete form field option.');
                    }
                });
                }.bind(this)
            );
        });
    });
});
</script>

@endsection

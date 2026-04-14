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
                            <a class="nav-link" href="{{ route('admin.settings.resource-types.index') }}">
                                <i class="bi bi-box"></i> Resource Types
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="{{ route('admin.settings.form-fields.index') }}">
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
            <div class="mb-3">
                <h3 class="mb-0">
                    <i class="bi bi-file-form"></i> Form Fields
                </h3>
            </div>
            <p class="text-muted small">Manage form field groups and options</p>
        </div>
    </div>

    {{-- Sticky Action Bar --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0 ff-toolbar">
                <div class="card-body p-3">
                    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
                        <div>
                            <label for="statusFilter" class="form-label fw-semibold mb-1">Status</label>
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
        $fieldGroupMeta = $fieldGroupMeta ?? collect();
        $placementLabels = $placementLabels ?? [
            'personal_information' => 'Agency & Personal Information',
            'farmer_information' => 'DA/RSBSA Information (Farmer)',
            'fisherfolk_information' => 'BFAR/FishR Information (Fisherfolk)',
            'dar_information' => 'DAR/ARB Information',
        ];

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
                @php
                    $meta = $fieldGroupMeta[$fieldGroup] ?? [
                        'placement_section' => 'personal_information',
                        'is_required' => false,
                    ];
                    $placementLabel = $placementLabels[$meta['placement_section']] ?? Str::title(str_replace('_', ' ', $meta['placement_section']));
                @endphp
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#ff-{{ str_replace('_', '-', $fieldGroup) }}">
                            <span class="d-flex flex-wrap align-items-center gap-2">
                                <span>{{ Str::title(str_replace('_', ' ', $fieldGroup)) }}</span>
                                <span class="badge bg-secondary">{{ count($options) }} option{{ count($options) === 1 ? '' : 's' }}</span>
                                <span class="badge bg-light text-dark border">{{ $placementLabel }}</span>
                                @if($meta['is_required'])
                                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">Required</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">Optional</span>
                                @endif
                            </span>
                        </button>
                    </h2>
                    <div id="ff-{{ str_replace('_', '-', $fieldGroup) }}" class="accordion-collapse collapse"
                         data-bs-parent="#formFieldsAccordion">
                        <div class="accordion-body p-0">
                            {{-- Field Options Table --}}
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0 table-responsive-cards">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Label</th>
                                            <th>Value</th>
                                            <th>Order</th>
                                            <th>Status</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="ff-tbody" data-field="{{ $fieldGroup }}">
                                        @forelse($options as $option)
                                        <tr data-ff-id="{{ $option->id }}">
                                            <td data-label="Label"><strong>{{ $option->label }}</strong></td>
                                            <td data-label="Value"><code>{{ $option->value }}</code></td>
                                            <td data-label="Order">
                                                <small class="text-muted">{{ $option->sort_order }}</small>
                                            </td>
                                            <td data-label="Status">
                                                <span class="badge {{ $option->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $option->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="text-center" data-label="Actions">
                                                <button class="btn btn-sm btn-outline-primary edit-ff"
                                                        data-id="{{ $option->id }}"
                                                        data-label="{{ $option->label }}"
                                                        data-value="{{ $option->value }}"
                                                        data-field-group="{{ $option->field_group }}"
                                                        data-placement-section="{{ $option->placement_section }}"
                                                        data-sort-order="{{ $option->sort_order }}"
                                                        data-required="{{ $option->is_required ? '1' : '0' }}"
                                                        data-active="{{ $option->is_active }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#ffModal"
                                                        title="Edit this field">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger delete-ff"
                                                        data-id="{{ $option->id }}"
                                                        data-label="{{ $option->label }}"
                                                        title="Delete this field">
                                                    <i class="bi bi-trash"></i> Delete
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
                        <label for="ffPlacementSection" class="form-label">Display Section <span class="text-danger">*</span></label>
                        <select id="ffPlacementSection" class="form-select form-select-sm" required>
                            @foreach($placementLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <small id="ffPlacementPreview" class="text-muted d-block mt-1">This field group will appear in Agency &amp; Personal Information.</small>
                    </div>

                    <div class="mb-3">
                        <label for="ffValue" class="form-label">Value <span class="text-danger">*</span></label>
                        <input type="text" id="ffValue" class="form-control form-control-sm" required>
                        <small class="text-muted">Use letters, numbers, and underscore only (example: drivers_license).</small>
                    </div>

                    <div class="mb-3">
                        <label for="ffOrderMode" class="form-label">Place Option In List</label>
                        <select id="ffOrderMode" class="form-select form-select-sm">
                            <option value="auto_end">Auto (add to end)</option>
                            <option value="start">Beginning</option>
                            <option value="end">End</option>
                            <option value="before">Before another option</option>
                            <option value="after">After another option</option>
                            <option value="custom">Manual sort number</option>
                        </select>
                        <small class="text-muted">Choose exact placement without memorizing sort numbers.</small>
                    </div>

                    <div class="mb-3 d-none" id="ffPositionTargetWrap">
                        <label for="ffPositionTarget" class="form-label">Target Option</label>
                        <select id="ffPositionTarget" class="form-select form-select-sm">
                            <option value="">Select target option...</option>
                        </select>
                    </div>

                    <div class="mb-3 d-none" id="ffSortOrderWrap">
                        <label for="ffSortOrder" class="form-label">Sort Order (optional)</label>
                        <input type="number" id="ffSortOrder" class="form-control form-control-sm" min="0" placeholder="Auto">
                        <small class="text-muted">Leave blank to auto-place this option at the end.</small>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" id="ffIsRequired" class="form-check-input">
                        <label class="form-check-label" for="ffIsRequired">
                            Required field group
                        </label>
                        <small class="text-muted d-block">When enabled, users must choose one option for this group in the assigned section.</small>
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
    const placementLabels = @json($placementLabels);

    function normalizeKey(text) {
        return String(text || '')
            .trim()
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');
    }

    function updatePlacementPreview() {
        const placementSelect = document.getElementById('ffPlacementSection');
        const preview = document.getElementById('ffPlacementPreview');

        if (!placementSelect || !preview) {
            return;
        }

        const selected = placementSelect.value;
        const label = placementLabels[selected] || selected;
        preview.textContent = `This field group will appear in ${label}.`;
    }

    function getCurrentFieldGroup() {
        let fieldGroup = document.getElementById('ffCustomFieldName').value;
        if (!fieldGroup) {
            fieldGroup = document.getElementById('ffFieldGroup').value;
        }

        return normalizeKey(fieldGroup);
    }

    function getGroupOptions(fieldGroup, excludeId = null) {
        if (!fieldGroup) {
            return [];
        }

        const tbody = document.querySelector(`.ff-tbody[data-field="${fieldGroup}"]`);
        if (!tbody) {
            return [];
        }

        return Array.from(tbody.querySelectorAll('tr[data-ff-id]'))
            .map((row) => {
                const id = Number(row.dataset.ffId || 0);
                const label = row.querySelector('td:first-child')?.textContent?.trim() || '';
                return { id, label };
            })
            .filter((item) => item.id > 0)
            .filter((item) => !excludeId || item.id !== Number(excludeId));
    }

    function refreshPositionTargets() {
        const targetSelect = document.getElementById('ffPositionTarget');
        const currentId = document.getElementById('ffId').value;
        const currentValue = targetSelect.value;
        const groupOptions = getGroupOptions(getCurrentFieldGroup(), currentId);

        targetSelect.innerHTML = '<option value="">Select target option...</option>';

        groupOptions.forEach((option) => {
            const opt = document.createElement('option');
            opt.value = String(option.id);
            opt.textContent = option.label;
            targetSelect.appendChild(opt);
        });

        if (groupOptions.length === 0) {
            const emptyOpt = document.createElement('option');
            emptyOpt.value = '';
            emptyOpt.textContent = 'No other options in this group';
            emptyOpt.disabled = true;
            targetSelect.appendChild(emptyOpt);
        }

        if (groupOptions.some((item) => String(item.id) === String(currentValue))) {
            targetSelect.value = currentValue;
        }
    }

    function syncOrderControls() {
        const orderMode = document.getElementById('ffOrderMode').value;
        const targetWrap = document.getElementById('ffPositionTargetWrap');
        const sortWrap = document.getElementById('ffSortOrderWrap');
        const sortInput = document.getElementById('ffSortOrder');
        const needsTarget = orderMode === 'before' || orderMode === 'after';
        const isManual = orderMode === 'custom';

        targetWrap.classList.toggle('d-none', !needsTarget);
        sortWrap.classList.toggle('d-none', !isManual);
        sortInput.disabled = !isManual;

        if (needsTarget) {
            refreshPositionTargets();
        }
    }

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
            document.getElementById('ffCustomFieldName').value = '';
            document.getElementById('ffSortOrder').value = this.dataset.sortOrder;
            document.getElementById('ffOrderMode').value = 'custom';
            document.getElementById('ffPositionTarget').value = '';
            document.getElementById('ffPlacementSection').value = this.dataset.placementSection || 'personal_information';
            document.getElementById('ffIsRequired').checked = this.dataset.required === '1';
            document.getElementById('ffIsActive').checked = this.dataset.active === '1';
            document.getElementById('ffModalTitle').textContent = 'Edit Form Field';
            updatePlacementPreview();
            syncOrderControls();
        });
    });

    document.getElementById('ffValue').addEventListener('blur', function() {
        this.value = normalizeKey(this.value);
    });

    // Reset form
    document.getElementById('ffModal').addEventListener('show.bs.modal', function(e) {
        const errorsDiv = document.getElementById('ffErrors');
        errorsDiv.classList.add('d-none');
        errorsDiv.textContent = '';

        if (!e.relatedTarget || !e.relatedTarget.classList.contains('edit-ff')) {
            document.getElementById('ffForm').reset();
            document.getElementById('ffId').value = '';
            document.getElementById('ffCustomFieldName').value = '';
            document.getElementById('ffPlacementSection').value = 'personal_information';
            document.getElementById('ffOrderMode').value = 'auto_end';
            document.getElementById('ffPositionTarget').value = '';
            document.getElementById('ffSortOrder').value = '';
            document.getElementById('ffIsRequired').checked = false;
            document.getElementById('ffModalTitle').textContent = 'Add Form Field';
            updatePlacementPreview();
            syncOrderControls();
        }
    });

    document.getElementById('ffPlacementSection').addEventListener('change', updatePlacementPreview);
    document.getElementById('ffOrderMode').addEventListener('change', syncOrderControls);
    document.getElementById('ffFieldGroup').addEventListener('change', syncOrderControls);
    document.getElementById('ffCustomFieldName').addEventListener('input', syncOrderControls);
    updatePlacementPreview();
    syncOrderControls();

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

        fieldGroup = normalizeKey(fieldGroup);
        const normalizedValue = normalizeKey(document.getElementById('ffValue').value);

        if (!normalizedValue) {
            const errorsDiv = document.getElementById('ffErrors');
            errorsDiv.textContent = 'Please enter a valid value (letters or numbers).';
            errorsDiv.classList.remove('d-none');
            return;
        }

        document.getElementById('ffValue').value = normalizedValue;

        if (!fieldGroup) {
            alert('Please select or enter a field group');
            return;
        }

        const orderMode = document.getElementById('ffOrderMode').value;
        const positionTargetRaw = document.getElementById('ffPositionTarget').value;

        if ((orderMode === 'before' || orderMode === 'after') && !positionTargetRaw) {
            const errorsDiv = document.getElementById('ffErrors');
            errorsDiv.textContent = 'Please select a target option for before/after placement.';
            errorsDiv.classList.remove('d-none');
            return;
        }

        const sortOrderRaw = document.getElementById('ffSortOrder').value.trim();

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
                    placement_section: document.getElementById('ffPlacementSection').value,
                    label: document.getElementById('ffLabel').value,
                    value: normalizedValue,
                    order_mode: orderMode,
                    position_target_id: (orderMode === 'before' || orderMode === 'after') ? Number(positionTargetRaw) : null,
                    sort_order: orderMode === 'custom'
                        ? (sortOrderRaw === '' ? null : Number(sortOrderRaw))
                        : null,
                    is_required: document.getElementById('ffIsRequired').checked,
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

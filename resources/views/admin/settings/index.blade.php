@extends('layouts.app')

@section('title', 'System Settings')

@section('breadcrumb')
    <li class="breadcrumb-item active">System Settings</li>
@endsection

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">System Settings</h1>
    </div>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" role="tablist" id="settingsTabs">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="agencies-tab" data-bs-toggle="tab" data-bs-target="#agencies-content" type="button" role="tab" aria-controls="agencies-content" aria-selected="true">
                <i class="bi bi-building"></i> Agencies
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="resource-types-tab" data-bs-toggle="tab" data-bs-target="#resource-types-content" type="button" role="tab" aria-controls="resource-types-content" aria-selected="false">
                <i class="bi bi-box"></i> Resource Types and Assistance Purposes
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="form-fields-tab" data-bs-toggle="tab" data-bs-target="#form-fields-content" type="button" role="tab" aria-controls="form-fields-content" aria-selected="false">
                <i class="bi bi-file-form"></i> Form Fields
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="settingsContent">

        <!-- ========== AGENCIES TAB ========== -->
        <div class="tab-pane fade show active" id="agencies-content" role="tabpanel" aria-labelledby="agencies-tab">
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-building"></i> Agencies Management
                        </h5>
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addAgencyModal">
                            <i class="bi bi-plus-circle me-1"></i> Create Agency
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0" id="agenciesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Full Name</th>
                                    <th>Classifications</th>
                                    <th>Form Fields</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 280px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($agencies as $agency)
                                    <tr data-agency-id="{{ $agency->id }}">
                                        <td><strong>{{ $agency->name }}</strong></td>
                                        <td>{{ $agency->full_name }}</td>
                                        <td>
                                            @forelse ($agency->classifications as $classification)
                                                <span class="badge bg-info">{{ $classification->name }}</span>
                                            @empty
                                                <span class="text-muted small">—</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $agency->active_form_fields_count ?? 0 }} fields
                                            </span>
                                        </td>
                                        <td>
                                            @if ($agency->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button class="btn btn-outline-primary edit-agency-btn"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editAgencyModal"
                                                        data-agency-id="{{ $agency->id }}"
                                                        title="Edit">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <button class="btn btn-outline-secondary manage-fields-btn"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#manageFieldsModal"
                                                        data-agency-id="{{ $agency->id }}"
                                                        title="Manage Agency Fields">
                                                    <i class="bi bi-sliders"></i> Agency Fields
                                                </button>
                                                @if ($agency->is_active)
                                                    <button class="btn btn-outline-danger deactivate-agency-btn"
                                                            data-agency-id="{{ $agency->id }}"
                                                            data-agency-name="{{ $agency->name }}"
                                                            title="Deactivate (data preserved)">
                                                        <i class="bi bi-power"></i> Deactivate
                                                    </button>
                                                @else
                                                    <button class="btn btn-outline-success activate-agency-btn"
                                                            data-agency-id="{{ $agency->id }}"
                                                            data-agency-name="{{ $agency->name }}"
                                                            title="Activate">
                                                        <i class="bi bi-arrow-repeat"></i> Activate
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No agencies found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== RESOURCE TYPES AND ASSISTANCE PURPOSES TAB ========== -->
        <div class="tab-pane fade" id="resource-types-content" role="tabpanel" aria-labelledby="resource-types-tab">
            <!-- Nested Sub-Tabs -->
            <ul class="nav nav-tabs mb-3" role="tablist" id="resourceTypesPurposesTabs">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="resource-types-sub-tab" data-bs-toggle="tab" data-bs-target="#resource-types-sub-content" type="button" role="tab" aria-controls="resource-types-sub-content" aria-selected="true">
                        <i class="bi bi-box"></i> Resource Types
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="purposes-sub-tab" data-bs-toggle="tab" data-bs-target="#purposes-sub-content" type="button" role="tab" aria-controls="purposes-sub-content" aria-selected="false">
                        <i class="bi bi-check-circle"></i> Assistance Purposes
                    </button>
                </li>
            </ul>

            <!-- Sub-Tab Content -->
            <div class="tab-content" id="resourceTypesPurposesContent">
                <!-- Resource Types Sub-Tab -->
                <div class="tab-pane fade show active" id="resource-types-sub-content" role="tabpanel" aria-labelledby="resource-types-sub-tab">
                    <div class="card">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="bi bi-box"></i> Resource Types
                                </h5>
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addResourceTypeModal">
                                    <i class="bi bi-plus-circle me-1"></i> Create Resource Type
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0" id="resourceTypesTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Unit</th>
                                            <th>Agency</th>
                                            <th class="text-center" style="width: 120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($resourceTypes as $resourceType)
                                            <tr data-resource-type-id="{{ $resourceType->id }}">
                                                <td><strong>{{ $resourceType->name }}</strong></td>
                                                <td><small>{{ $resourceType->unit ?: 'N/A' }}</small></td>
                                                <td><small>{{ $resourceType->agency?->name ?? 'N/A' }}</small></td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button class="btn btn-outline-primary edit-resource-type-btn"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editResourceTypeModal"
                                                                data-resource-type-id="{{ $resourceType->id }}"
                                                                title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger delete-resource-type-btn"
                                                                data-resource-type-id="{{ $resourceType->id }}"
                                                                data-resource-type-name="{{ $resourceType->name }}"
                                                                title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">No resource types found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Purposes Sub-Tab -->
                <div class="tab-pane fade" id="purposes-sub-content" role="tabpanel" aria-labelledby="purposes-sub-tab">
                    <div class="card">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="bi bi-check-circle"></i> Assistance Purposes
                                </h5>
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addPurposeModal">
                                    <i class="bi bi-plus-circle me-1"></i> Create Purpose
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0" id="purposesTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Type</th>
                                            <th class="text-center" style="width: 120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($purposes as $purpose)
                                            <tr data-purpose-id="{{ $purpose->id }}">
                                                <td><strong>{{ $purpose->name }}</strong></td>
                                                <td>
                                                    <span class="badge bg-info">{{ $purpose->category }}</span>
                                                </td>
                                                <td>{{ $purpose->type }}</td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button class="btn btn-outline-primary edit-purpose-btn"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editPurposeModal"
                                                                data-purpose-id="{{ $purpose->id }}"
                                                                data-purpose-name="{{ $purpose->name }}"
                                                                data-purpose-category="{{ $purpose->category }}"
                                                                data-purpose-type="{{ $purpose->type }}"
                                                                title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger delete-purpose-btn"
                                                                data-purpose-id="{{ $purpose->id }}"
                                                                data-purpose-name="{{ $purpose->name }}"
                                                                title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">No purposes found</td>
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

        <!-- ========== FORM FIELDS TAB ========== -->
        <div class="tab-pane fade" id="form-fields-content" role="tabpanel" aria-labelledby="form-fields-tab">
            @php
                $optionBasedTypes = \App\Models\FormFieldOption::optionBasedFieldTypes();
                $groupedByPlacement = ($formFields ?? collect())
                    ->groupBy(fn ($options) => $options->first()?->placement_section ?? \App\Models\FormFieldOption::PLACEMENT_PERSONAL_INFORMATION, true);
                $personalFormFields   = $groupedByPlacement->get(\App\Models\FormFieldOption::PLACEMENT_PERSONAL_INFORMATION, collect());
                $farmerGlobalFields   = $groupedByPlacement->get(\App\Models\FormFieldOption::PLACEMENT_FARMER_INFORMATION, collect());
                $fisherfolkGlobalFields = $groupedByPlacement->get(\App\Models\FormFieldOption::PLACEMENT_FISHERFOLK_INFORMATION, collect());
                $farmerCoreFields     = ($classificationCoreFields ?? collect())->get('Farmer', collect());
                $fisherfolkCoreFields = ($classificationCoreFields ?? collect())->get('Fisherfolk', collect());
            @endphp

            <div class="alert alert-light border d-flex gap-2 align-items-start mb-3 py-2 px-3">
                <i class="bi bi-info-circle text-primary mt-1 flex-shrink-0"></i>
                <small class="text-muted">
                    Fields are organized by the form section where they appear on the beneficiary intake form.
                    <strong>Global fields</strong> are fully configurable and shared across all beneficiaries in that section.
                    <strong>Core fields</strong> are system-managed — labels and required status are editable, but they cannot be added or removed.
                    Agency-specific fields (e.g. DAR) are managed under <strong>Agencies &rsaquo; Agency Fields</strong>.
                </small>
            </div>

            <div class="card">
                <div class="card-header bg-light p-0">
                    <ul class="nav nav-tabs border-0 px-2 pt-2" id="formFieldsSectionTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="ff-personal-tab" data-bs-toggle="tab" data-bs-target="#ff-personal" type="button" role="tab">
                                <i class="bi bi-person me-1"></i> Personal &amp; Agency
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="ff-farmer-tab" data-bs-toggle="tab" data-bs-target="#ff-farmer" type="button" role="tab">
                                <i class="bi bi-tree me-1"></i> Farmer
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="ff-fisherfolk-tab" data-bs-toggle="tab" data-bs-target="#ff-fisherfolk" type="button" role="tab">
                                <i class="bi bi-water me-1"></i> Fisherfolk
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content" id="formFieldsSectionTabContent">

                    {{-- ────────────────── PERSONAL & AGENCY ────────────────── --}}
                    <div class="tab-pane fade show active" id="ff-personal" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom bg-white">
                            <small class="text-muted">Shared fields in the <strong>Agency &amp; Personal Information</strong> section, shown for all beneficiaries.</small>
                            <button class="btn btn-sm btn-success section-add-field-btn ms-3 flex-shrink-0"
                                    data-placement="personal_information" data-section-label="Personal">
                                <i class="bi bi-plus-circle me-1"></i> Add Field
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Field</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th>Status</th>
                                        <th class="text-end pe-3">Options</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($personalFormFields as $group => $options)
                                        @php
                                            $groupMeta = $options->first();
                                            $groupType = $groupMeta->field_type ?? \App\Models\FormFieldOption::FIELD_TYPE_DROPDOWN;
                                            $isOptionBasedGroup = in_array($groupType, $optionBasedTypes, true);
                                            $groupRequired = (bool) ($groupMeta->is_required ?? false);
                                            $groupIsActive = (bool) $options->contains(fn ($option) => (bool) $option->is_active);
                                            $groupOptionsCount = $options->count();
                                            $isPersonalCoreGroup = \App\Support\BeneficiaryCoreFields::isPersonalInformationCoreFieldName($group);
                                            $isCoreOptionBased = $isPersonalCoreGroup && $isOptionBasedGroup;
                                            $collapseId = 'globalFieldOptionsGroup' . $loop->index;
                                        @endphp
                                        <tr class="global-field-group-row" data-collapse-target="#{{ $collapseId }}">
                                            <td>
                                                <div class="fw-semibold">{{ Str::title(str_replace('_', ' ', $group)) }}</div>
                                                <small class="text-muted font-monospace">{{ $group }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info text-dark">
                                                    {{ \App\Models\FormFieldOption::fieldTypeLabel($groupType) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($groupRequired)
                                                    <span class="badge bg-danger">Required</span>
                                                @else
                                                    <span class="badge bg-secondary">Optional</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($groupIsActive)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-3">
                                                <div class="d-flex align-items-center justify-content-end gap-2">
                                                    <span class="badge bg-light text-dark border">
                                                        {{ $groupOptionsCount }} {{ Str::plural('option', $groupOptionsCount) }}
                                                    </span>
                                                    @if ($isCoreOptionBased)
                                                        <button class="btn btn-sm btn-outline-success add-core-option-btn"
                                                                data-field-group="{{ $group }}"
                                                                data-field-type="{{ $groupType }}"
                                                                data-placement="personal_information"
                                                                title="Add option to {{ Str::title(str_replace('_', ' ', $group)) }}">
                                                            <i class="bi bi-plus"></i> Add Option
                                                        </button>
                                                    @endif
                                                    <button class="btn btn-sm btn-outline-secondary global-options-toggle"
                                                            type="button"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#{{ $collapseId }}"
                                                            aria-expanded="false"
                                                            title="View options">
                                                        <span class="toggle-label">View</span>
                                                        <i class="bi bi-chevron-down ms-1"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="global-field-options-row">
                                            <td colspan="5" class="p-0">
                                                <div class="collapse" id="{{ $collapseId }}">
                                                    <div class="bg-light px-3 py-3 border-top">
                                                        <div class="small text-muted mb-2">
                                                            @if ($isOptionBasedGroup)
                                                                Options under <strong>{{ Str::title(str_replace('_', ' ', $group)) }}</strong>
                                                            @else
                                                                Configuration for <strong>{{ Str::title(str_replace('_', ' ', $group)) }}</strong>
                                                            @endif
                                                        </div>
                                                        <div class="list-group list-group-flush global-options-list">
                                                            @foreach ($options as $option)
                                                                <div class="list-group-item px-2 py-2 border rounded mb-1">
                                                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                                                        <div>
                                                                            <span class="fw-semibold">{{ $option->label }}</span>
                                                                            <code class="small text-muted ms-2">{{ $option->value }}</code>
                                                                        </div>
                                                                        <div class="btn-group btn-group-sm">
                                                                            <button class="btn btn-outline-primary edit-global-field-btn"
                                                                                    data-field-id="{{ $option->id }}"
                                                                                    data-field-group="{{ $option->field_group }}"
                                                                                    data-field-type="{{ $groupType }}"
                                                                                    data-label="{{ $option->label }}"
                                                                                    data-value="{{ $option->value }}"
                                                                                    data-placement="{{ $option->placement_section }}"
                                                                                    data-sort-order="{{ $option->sort_order }}"
                                                                                    data-required="{{ $option->is_required ? '1' : '0' }}"
                                                                                    data-active="{{ $option->is_active ? '1' : '0' }}"
                                                                                    @if ($isPersonalCoreGroup) data-core-locked="1" @endif
                                                                                    title="{{ $isPersonalCoreGroup ? 'Edit option label / value' : 'Edit' }}">
                                                                                <i class="bi bi-pencil"></i>
                                                                            </button>
                                                                            <button class="btn btn-outline-danger delete-global-field-btn"
                                                                                    data-field-id="{{ $option->id }}"
                                                                                    data-field-label="{{ $option->label }}"
                                                                                    title="Delete">
                                                                                <i class="bi bi-trash"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                No personal fields configured. Click <strong>Add Field</strong> to create one.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ────────────────── FARMER (DA) ────────────────── --}}
                    <div class="tab-pane fade" id="ff-farmer" role="tabpanel">

                        {{-- Global fields --}}
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom bg-white">
                            <div>
                                <span class="fw-semibold small">Additional Farmer Fields</span>
                                <small class="text-muted ms-2">Configurable fields shown only in the <strong>Farmer Information</strong> section.</small>
                            </div>
                            <button class="btn btn-sm btn-success section-add-field-btn ms-3 flex-shrink-0"
                                    data-placement="farmer_information" data-section-label="Farmer">
                                <i class="bi bi-plus-circle me-1"></i> Add Field
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Field</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th>Status</th>
                                        <th class="text-end pe-3">Options</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($farmerGlobalFields as $group => $options)
                                        @php
                                            $groupMeta    = $options->first();
                                            $gType        = $groupMeta->field_type ?? \App\Models\FormFieldOption::FIELD_TYPE_DROPDOWN;
                                            $gIsOptionBased = in_array($gType, $optionBasedTypes, true);
                                            $gRequired    = (bool) ($groupMeta->is_required ?? false);
                                            $gIsActive    = (bool) $options->contains(fn ($o) => (bool) $o->is_active);
                                            $gCount       = $options->count();
                                            $gCollapseId  = 'farmerGlobalGroup' . $loop->index;
                                        @endphp
                                        <tr class="global-field-group-row" data-collapse-target="#{{ $gCollapseId }}">
                                            <td>
                                                <div class="fw-semibold">{{ Str::title(str_replace('_', ' ', $group)) }}</div>
                                                <small class="text-muted font-monospace">{{ $group }}</small>
                                            </td>
                                            <td><span class="badge bg-info text-dark">{{ \App\Models\FormFieldOption::fieldTypeLabel($gType) }}</span></td>
                                            <td>
                                                <span class="badge {{ $gRequired ? 'bg-danger' : 'bg-secondary' }}">{{ $gRequired ? 'Required' : 'Optional' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $gIsActive ? 'bg-success' : 'bg-secondary' }}">{{ $gIsActive ? 'Active' : 'Inactive' }}</span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <div class="d-flex align-items-center justify-content-end gap-2">
                                                    <span class="badge bg-light text-dark border">{{ $gCount }} {{ Str::plural('option', $gCount) }}</span>
                                                    <button class="btn btn-sm btn-outline-secondary global-options-toggle"
                                                            type="button"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#{{ $gCollapseId }}"
                                                            aria-expanded="false"
                                                            title="View options">
                                                        <span class="toggle-label">View</span>
                                                        <i class="bi bi-chevron-down ms-1"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="global-field-options-row">
                                            <td colspan="5" class="p-0">
                                                <div class="collapse" id="{{ $gCollapseId }}">
                                                    <div class="bg-light px-3 py-3 border-top">
                                                        <div class="small text-muted mb-2">
                                                            @if ($gIsOptionBased)
                                                                Options under <strong>{{ Str::title(str_replace('_', ' ', $group)) }}</strong>
                                                            @else
                                                                Configuration for <strong>{{ Str::title(str_replace('_', ' ', $group)) }}</strong>
                                                            @endif
                                                        </div>
                                                        <div class="list-group list-group-flush global-options-list">
                                                            @foreach ($options as $option)
                                                                <div class="list-group-item px-2 py-2 border rounded mb-1">
                                                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                                                        <div>
                                                                            <span class="fw-semibold">{{ $option->label }}</span>
                                                                            <code class="small text-muted ms-2">{{ $option->value }}</code>
                                                                        </div>
                                                                        <div class="btn-group btn-group-sm">
                                                                            <button class="btn btn-outline-primary edit-global-field-btn"
                                                                                    data-field-id="{{ $option->id }}"
                                                                                    data-field-group="{{ $option->field_group }}"
                                                                                    data-field-type="{{ $gType }}"
                                                                                    data-label="{{ $option->label }}"
                                                                                    data-value="{{ $option->value }}"
                                                                                    data-placement="{{ $option->placement_section }}"
                                                                                    data-sort-order="{{ $option->sort_order }}"
                                                                                    data-required="{{ $option->is_required ? '1' : '0' }}"
                                                                                    data-active="{{ $option->is_active ? '1' : '0' }}"
                                                                                    title="Edit">
                                                                                <i class="bi bi-pencil"></i>
                                                                            </button>
                                                                            <button class="btn btn-outline-danger delete-global-field-btn"
                                                                                    data-field-id="{{ $option->id }}"
                                                                                    data-field-label="{{ $option->label }}"
                                                                                    title="Delete">
                                                                                <i class="bi bi-trash"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                No additional fields configured for Farmer. Click <strong>Add Field</strong> to create one.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Core fields --}}
                        <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom" style="background:#f8f9fa;">
                            <span class="fw-semibold small">Core Fields</span>
                            <span class="badge bg-secondary">System</span>
                            <small class="text-muted">Built-in Farmer fields — rename labels or toggle required status only.</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Field</th>
                                        <th style="width: 100px;">Required</th>
                                        <th class="text-center" style="width: 210px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($farmerCoreFields as $coreField)
                                        <tr data-core-field-name="{{ $coreField['field_name'] }}">
                                            <td>
                                                <div class="fw-semibold classification-core-field-label">{{ $coreField['label'] }}</div>
                                                <small class="text-muted font-monospace">{{ $coreField['field_name'] }}</small>
                                            </td>
                                            <td>
                                                <span class="badge {{ $coreField['is_required'] ? 'bg-danger' : 'bg-secondary' }} core-required-badge">
                                                    {{ $coreField['is_required'] ? 'Required' : 'Optional' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button"
                                                            class="btn btn-outline-secondary edit-classification-core-btn"
                                                            data-field-name="{{ $coreField['field_name'] }}"
                                                            data-label="{{ $coreField['label'] }}"
                                                            data-required="{{ $coreField['is_required'] ? '1' : '0' }}"
                                                            data-placement="{{ Str::title(str_replace('_', ' ', $coreField['placement_section'])) }}"
                                                            title="Edit label">
                                                        <i class="bi bi-pencil"></i> Edit Label
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-outline-primary toggle-core-required-btn"
                                                            data-field-name="{{ $coreField['field_name'] }}"
                                                            data-next-required="{{ $coreField['is_required'] ? '0' : '1' }}">
                                                        {{ $coreField['is_required'] ? 'Set Optional' : 'Set Required' }}
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">No core fields defined for Farmer.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ────────────────── FISHERFOLK (BFAR) ────────────────── --}}
                    <div class="tab-pane fade" id="ff-fisherfolk" role="tabpanel">

                        {{-- Global fields --}}
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom bg-white">
                            <div>
                                <span class="fw-semibold small">Additional Fisherfolk Fields</span>
                                <small class="text-muted ms-2">Configurable fields shown only in the <strong>Fisherfolk Information</strong> section.</small>
                            </div>
                            <button class="btn btn-sm btn-success section-add-field-btn ms-3 flex-shrink-0"
                                    data-placement="fisherfolk_information" data-section-label="Fisherfolk">
                                <i class="bi bi-plus-circle me-1"></i> Add Field
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Field</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th>Status</th>
                                        <th class="text-end pe-3">Options</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($fisherfolkGlobalFields as $group => $options)
                                        @php
                                            $groupMeta    = $options->first();
                                            $gType        = $groupMeta->field_type ?? \App\Models\FormFieldOption::FIELD_TYPE_DROPDOWN;
                                            $gIsOptionBased = in_array($gType, $optionBasedTypes, true);
                                            $gRequired    = (bool) ($groupMeta->is_required ?? false);
                                            $gIsActive    = (bool) $options->contains(fn ($o) => (bool) $o->is_active);
                                            $gCount       = $options->count();
                                            $gCollapseId  = 'fisherfolkGlobalGroup' . $loop->index;
                                        @endphp
                                        <tr class="global-field-group-row" data-collapse-target="#{{ $gCollapseId }}">
                                            <td>
                                                <div class="fw-semibold">{{ Str::title(str_replace('_', ' ', $group)) }}</div>
                                                <small class="text-muted font-monospace">{{ $group }}</small>
                                            </td>
                                            <td><span class="badge bg-info text-dark">{{ \App\Models\FormFieldOption::fieldTypeLabel($gType) }}</span></td>
                                            <td>
                                                <span class="badge {{ $gRequired ? 'bg-danger' : 'bg-secondary' }}">{{ $gRequired ? 'Required' : 'Optional' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $gIsActive ? 'bg-success' : 'bg-secondary' }}">{{ $gIsActive ? 'Active' : 'Inactive' }}</span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <div class="d-flex align-items-center justify-content-end gap-2">
                                                    <span class="badge bg-light text-dark border">{{ $gCount }} {{ Str::plural('option', $gCount) }}</span>
                                                    <button class="btn btn-sm btn-outline-secondary global-options-toggle"
                                                            type="button"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#{{ $gCollapseId }}"
                                                            aria-expanded="false"
                                                            title="View options">
                                                        <span class="toggle-label">View</span>
                                                        <i class="bi bi-chevron-down ms-1"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="global-field-options-row">
                                            <td colspan="5" class="p-0">
                                                <div class="collapse" id="{{ $gCollapseId }}">
                                                    <div class="bg-light px-3 py-3 border-top">
                                                        <div class="small text-muted mb-2">
                                                            @if ($gIsOptionBased)
                                                                Options under <strong>{{ Str::title(str_replace('_', ' ', $group)) }}</strong>
                                                            @else
                                                                Configuration for <strong>{{ Str::title(str_replace('_', ' ', $group)) }}</strong>
                                                            @endif
                                                        </div>
                                                        <div class="list-group list-group-flush global-options-list">
                                                            @foreach ($options as $option)
                                                                <div class="list-group-item px-2 py-2 border rounded mb-1">
                                                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                                                        <div>
                                                                            <span class="fw-semibold">{{ $option->label }}</span>
                                                                            <code class="small text-muted ms-2">{{ $option->value }}</code>
                                                                        </div>
                                                                        <div class="btn-group btn-group-sm">
                                                                            <button class="btn btn-outline-primary edit-global-field-btn"
                                                                                    data-field-id="{{ $option->id }}"
                                                                                    data-field-group="{{ $option->field_group }}"
                                                                                    data-field-type="{{ $gType }}"
                                                                                    data-label="{{ $option->label }}"
                                                                                    data-value="{{ $option->value }}"
                                                                                    data-placement="{{ $option->placement_section }}"
                                                                                    data-sort-order="{{ $option->sort_order }}"
                                                                                    data-required="{{ $option->is_required ? '1' : '0' }}"
                                                                                    data-active="{{ $option->is_active ? '1' : '0' }}"
                                                                                    title="Edit">
                                                                                <i class="bi bi-pencil"></i>
                                                                            </button>
                                                                            <button class="btn btn-outline-danger delete-global-field-btn"
                                                                                    data-field-id="{{ $option->id }}"
                                                                                    data-field-label="{{ $option->label }}"
                                                                                    title="Delete">
                                                                                <i class="bi bi-trash"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                No additional fields configured for Fisherfolk. Click <strong>Add Field</strong> to create one.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Core fields --}}
                        <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom" style="background:#f8f9fa;">
                            <span class="fw-semibold small">Core Fields</span>
                            <span class="badge bg-secondary">System</span>
                            <small class="text-muted">Built-in Fisherfolk fields — rename labels or toggle required status only.</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Field</th>
                                        <th style="width: 100px;">Required</th>
                                        <th class="text-center" style="width: 210px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($fisherfolkCoreFields as $coreField)
                                        <tr data-core-field-name="{{ $coreField['field_name'] }}">
                                            <td>
                                                <div class="fw-semibold classification-core-field-label">{{ $coreField['label'] }}</div>
                                                <small class="text-muted font-monospace">{{ $coreField['field_name'] }}</small>
                                            </td>
                                            <td>
                                                <span class="badge {{ $coreField['is_required'] ? 'bg-danger' : 'bg-secondary' }} core-required-badge">
                                                    {{ $coreField['is_required'] ? 'Required' : 'Optional' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button"
                                                            class="btn btn-outline-secondary edit-classification-core-btn"
                                                            data-field-name="{{ $coreField['field_name'] }}"
                                                            data-label="{{ $coreField['label'] }}"
                                                            data-required="{{ $coreField['is_required'] ? '1' : '0' }}"
                                                            data-placement="{{ Str::title(str_replace('_', ' ', $coreField['placement_section'])) }}"
                                                            title="Edit label">
                                                        <i class="bi bi-pencil"></i> Edit Label
                                                    </button>
                                                    <button type="button"
                                                            class="btn btn-outline-primary toggle-core-required-btn"
                                                            data-field-name="{{ $coreField['field_name'] }}"
                                                            data-next-required="{{ $coreField['is_required'] ? '0' : '1' }}">
                                                        {{ $coreField['is_required'] ? 'Set Optional' : 'Set Required' }}
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">No core fields defined for Fisherfolk.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>{{-- /tab-content --}}
            </div>{{-- /card --}}
        </div>

    </div>
</div>

<!-- Edit Classification Core Field Modal -->
<div class="modal fade" id="editClassificationCoreFieldModal" tabindex="-1" aria-labelledby="editClassificationCoreFieldModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editClassificationCoreFieldModalLabel">Edit Classification Core Field</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="classificationCoreFieldForm">
                    <input type="hidden" id="classificationCoreFieldName">

                    <div class="mb-3">
                        <label for="classificationCoreFieldLabel" class="form-label">Field Label <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="classificationCoreFieldLabel" maxlength="255" required>
                    </div>

                    <div class="mb-3">
                        <label for="classificationCoreFieldSection" class="form-label">Section</label>
                        <input type="text" class="form-control" id="classificationCoreFieldSection" readonly>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="classificationCoreFieldRequired">
                        <label class="form-check-label" for="classificationCoreFieldRequired">
                            Required
                        </label>
                    </div>

                    <div id="classificationCoreFieldErrors" class="alert alert-danger d-none mb-0"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveClassificationCoreFieldBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- ============ MODALS ============ -->

<!-- Add Agency Modal -->
<div class="modal fade" id="addAgencyModal" tabindex="-1" aria-labelledby="addAgencyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAgencyModalLabel">Create Agency</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="agencyForm">
                    <input type="hidden" id="agencyId" name="id" value="">

                    <div class="mb-3">
                        <label for="agencyName" class="form-label">Agency Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="agencyName" name="name" required placeholder="e.g., DA">
                        <small class="text-muted">Short name used as identifier</small>
                    </div>

                    <div class="mb-3">
                        <label for="agencyFullName" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="agencyFullName" name="full_name" required placeholder="e.g., Department of Agriculture">
                    </div>

                    <div class="mb-3">
                        <label for="agencyDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="agencyDescription" name="description" rows="3" placeholder="Optional description..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Classifications <span class="text-danger">*</span></label>
                        <div id="classificationsContainer">
                            <!-- Classifications checkboxes will be loaded here -->
                        </div>
                        <small class="text-muted">Select at least one classification</small>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="agencyActive" name="is_active" value="1" checked>
                        <label class="form-check-label" for="agencyActive">
                            Active
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveAgencyBtn">Save Agency</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Agency Modal -->
<div class="modal fade" id="editAgencyModal" tabindex="-1" aria-labelledby="editAgencyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAgencyModalLabel">Edit Agency</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editAgencyForm">
                    <input type="hidden" id="editAgencyId" name="id" value="">

                    <div class="mb-3">
                        <label for="editAgencyName" class="form-label">Agency Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editAgencyName" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="editAgencyFullName" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editAgencyFullName" name="full_name" required>
                    </div>

                    <div class="mb-3">
                        <label for="editAgencyDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editAgencyDescription" name="description" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Classifications <span class="text-danger">*</span></label>
                        <div id="editClassificationsContainer">
                            <!-- Classifications checkboxes will be loaded here -->
                        </div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="editAgencyActive" name="is_active" value="1">
                        <label class="form-check-label" for="editAgencyActive">
                            Active
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEditAgencyBtn">Update Agency</button>
            </div>
        </div>
    </div>
</div>

<!-- Manage Form Fields Modal -->
<div class="modal fade modal-lg" id="manageFieldsModal" tabindex="-1" aria-labelledby="manageFieldsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageFieldsModalLabel">Manage Agency Fields</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 px-3 small" role="alert">
                    These fields apply only to the selected agency.
                </div>
                <div id="fieldsListContainer">
                    <p class="text-muted">Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="addFieldBtn" data-bs-toggle="modal" data-bs-target="#addFieldModal">
                    <i class="bi bi-plus-circle me-1"></i> Add Agency Field
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Form Field Modal (Agency) -->
<div class="modal fade" id="addFieldModal" tabindex="-1" aria-labelledby="addFieldModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addFieldModalLabel">Add Agency Field</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="fieldForm">
                    <input type="hidden" id="fieldAgencyId" name="agency_id" value="">
                    <input type="hidden" id="fieldId" name="id" value="">

                    <div class="mb-3">
                        <label for="fieldName" class="form-label">Field Name (Slug) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="fieldName" name="field_name" required placeholder="e.g., member_id">
                        <small class="text-muted">Lowercase, alphanumeric and underscores only</small>
                    </div>

                    <div class="mb-3">
                        <label for="fieldLabel" class="form-label">Display Label <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="fieldLabel" name="display_label" required placeholder="e.g., Member ID">
                    </div>

                    <div class="mb-3">
                        <label for="fieldType" class="form-label">Field Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="fieldType" name="field_type" required>
                            <option value="">Select a type...</option>
                            <option value="text">Text</option>
                            <option value="number">Number</option>
                            <option value="decimal">Decimal</option>
                            <option value="date">Date</option>
                            <option value="datetime">Date & Time</option>
                            <option value="dropdown">Dropdown</option>
                            <option value="checkbox">Checkboxes</option>
                        </select>
                    </div>

                    <div class="mb-3 d-none" id="fieldOptionsWrapper">
                        <label for="fieldOptionsInput" class="form-label">Options (one per line)</label>
                        <textarea class="form-control" id="fieldOptionsInput" name="field_options" rows="5" placeholder="Capture Fishing|capture_fishing&#10;Aquaculture|aquaculture&#10;Post-Harvest|post_harvest"></textarea>
                        <small class="text-muted">Format: Label|value. If value is omitted, it will be auto-generated from label.</small>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="fieldRequired" name="is_required" value="1">
                        <label class="form-check-label" for="fieldRequired">
                            Required (must have value or unavailability reason)
                        </label>
                    </div>

                    <div class="mb-3">
                        <label for="fieldHelpText" class="form-label">Help Text</label>
                        <textarea class="form-control" id="fieldHelpText" name="help_text" rows="2" placeholder="Optional help text for users..."></textarea>
                    </div>

                    <div class="mb-3 d-none" id="fieldSectionWrapper">
                        <label for="fieldSection" class="form-label">Form Section</label>
                        <select class="form-select" id="fieldSection" name="form_section">
                            <option value="">General Information</option>
                            <option value="farmer_information">Farmer Information</option>
                            <option value="fisherfolk_information">Fisherfolk Information</option>
                            <option value="dar_information">DAR Information</option>
                        </select>
                        <small class="text-muted">Section is auto-assigned based on agency and field context.</small>
                    </div>

                    <div class="mb-3">
                        <label for="fieldSort" class="form-label">Sort Order</label>
                        <input type="number" class="form-control" id="fieldSort" name="sort_order" value="0" min="0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveFieldBtn">Save Field</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Global Form Field Modal -->
<div class="modal fade" id="addGlobalFieldModal" tabindex="-1" aria-labelledby="addGlobalFieldModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addGlobalFieldModalLabel">Add Global Form Field</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="globalFieldForm">
                    <input type="hidden" id="globalFieldId">

                    <div class="mb-3">
                        <label for="globalFieldGroup" class="form-label">Field Group <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="globalFieldGroup" required placeholder="e.g., beneficiary_category">
                        <small class="text-muted" id="globalFieldGroupHelp">Lowercase, alphanumeric, and underscores only.</small>
                    </div>

                    <div class="mb-3">
                        <label for="globalFieldType" class="form-label">Field Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="globalFieldType" required>
                            <option value="dropdown">Dropdown</option>
                            <option value="radio">Radio</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="text">Text</option>
                            <option value="textarea">Textarea</option>
                            <option value="number">Number</option>
                            <option value="decimal">Decimal</option>
                            <option value="date">Date</option>
                            <option value="datetime">Date &amp; Time</option>
                        </select>
                        <small class="text-muted">All labels under one field group use the same field type.</small>
                    </div>

                    <div class="mb-3">
                        <label for="globalFieldLabel" class="form-label" id="globalFieldLabelLabel">Option Label <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="globalFieldLabel" required placeholder="e.g., Small Farmer">
                    </div>

                    <div class="mb-3" id="globalFieldValueWrapper">
                        <label for="globalFieldValue" class="form-label" id="globalFieldValueLabel">Option Value <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="globalFieldValue" placeholder="e.g., small_farmer">
                        <small class="text-muted" id="globalFieldValueHelp">Stored key value for this option.</small>
                    </div>

                    <input type="hidden" id="globalFieldPlacement" value="personal_information">

                    <div class="mb-3">
                        <label for="globalFieldSortOrder" class="form-label">Sort Order</label>
                        <input type="number" class="form-control" id="globalFieldSortOrder" min="0" placeholder="Optional">
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="globalFieldRequired">
                        <label class="form-check-label" for="globalFieldRequired">
                            Required field group
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="globalFieldActive" checked>
                        <label class="form-check-label" for="globalFieldActive">
                            Active
                        </label>
                    </div>

                    <div id="globalFieldErrors" class="alert alert-danger d-none mb-0"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveGlobalFieldBtn">Save Field</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Resource Type Modal -->
<div class="modal fade" id="addResourceTypeModal" tabindex="-1" aria-labelledby="addResourceTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addResourceTypeModalLabel">Create Resource Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="resourceTypeForm">
                    <input type="hidden" id="resourceTypeId" name="id" value="">

                    <div class="mb-3">
                        <label for="rtName" class="form-label">Resource Type Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="rtName" name="name" required placeholder="e.g., Seeds">
                    </div>

                    <div class="mb-3">
                        <label for="rtAgency" class="form-label">Agency <span class="text-danger">*</span></label>
                        <select class="form-select" id="rtAgency" name="agency_id" required>
                            <option value="">Select an agency...</option>
                            @foreach ($activeAgencies as $agency)
                                <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="rtUnit" class="form-label">Unit Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="rtUnit" name="unit" required>
                            <option value="" selected disabled>Select unit type...</option>
                            @foreach(($resourceUnitOptions ?? []) as $unitValue => $unitLabel)
                                <option value="{{ $unitValue }}">{{ $unitLabel }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Use PHP for cash assistance (amount-based).</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveResourceTypeBtn">Save Resource Type</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Resource Type Modal -->
<div class="modal fade" id="editResourceTypeModal" tabindex="-1" aria-labelledby="editResourceTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editResourceTypeModalLabel">Edit Resource Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editResourceTypeForm">
                    <input type="hidden" id="editResourceTypeId" name="id" value="">

                    <div class="mb-3">
                        <label for="editRtName" class="form-label">Resource Type Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editRtName" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="editRtAgency" class="form-label">Agency <span class="text-danger">*</span></label>
                        <select class="form-select" id="editRtAgency" name="agency_id" required>
                            <option value="">Select an agency...</option>
                            @foreach ($activeAgencies as $agency)
                                <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="editRtUnit" class="form-label">Unit Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="editRtUnit" name="unit" required>
                            <option value="" selected disabled>Select unit type...</option>
                            @foreach(($resourceUnitOptions ?? []) as $unitValue => $unitLabel)
                                <option value="{{ $unitValue }}">{{ $unitLabel }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Use PHP for cash assistance (amount-based).</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEditResourceTypeBtn">Update Resource Type</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Purpose Modal -->
<div class="modal fade" id="addPurposeModal" tabindex="-1" aria-labelledby="addPurposeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPurposeModalLabel">Create Assistance Purpose</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="purposeForm">
                    <input type="hidden" id="purposeId" name="id" value="">

                    <div class="mb-3">
                        <label for="purposeName" class="form-label">Purpose Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="purposeName" name="name" required placeholder="e.g., Farm Rehabilitation">
                    </div>

                    <div class="mb-3">
                        <label for="purposeCategory" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="purposeCategory" name="category" required>
                            <option value="">-- Select Category --</option>
                            @foreach(($purposeCategoryOptions ?? []) as $value => $option)
                                <option value="{{ $value }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="purposeType" class="form-label">Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="purposeType" name="type" required disabled>
                            <option value="">-- Select Type --</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="savePurposeBtn">Save Purpose</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Purpose Modal -->
<div class="modal fade" id="editPurposeModal" tabindex="-1" aria-labelledby="editPurposeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPurposeModalLabel">Edit Assistance Purpose</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editPurposeForm">
                    <input type="hidden" id="editPurposeId" name="id" value="">

                    <div class="mb-3">
                        <label for="editPurposeName" class="form-label">Purpose Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editPurposeName" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="editPurposeCategory" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="editPurposeCategory" name="category" required>
                            <option value="">-- Select Category --</option>
                            @foreach(($purposeCategoryOptions ?? []) as $value => $option)
                                <option value="{{ $value }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="editPurposeType" class="form-label">Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="editPurposeType" name="type" required disabled>
                            <option value="">-- Select Type --</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEditPurposeBtn">Update Purpose</button>
            </div>
        </div>
    </div>
</div>

<!-- Settings Notification Modal -->
<div class="modal fade" id="settingsNotificationModal" tabindex="-1" aria-labelledby="settingsNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="settingsNotificationHeader">
                <h5 class="modal-title" id="settingsNotificationModalLabel">Notice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex gap-3 align-items-start">
                    <i class="bi bi-info-circle fs-3 flex-shrink-0" id="settingsNotificationIcon"></i>
                    <p class="mb-0 settings-notification-message" id="settingsNotificationMessage"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="settingsNotificationOkBtn" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<div id="purposeCategoryOptionsData" data-options='@json($purposeCategoryOptions ?? [])' class="d-none"></div>

<style>
    .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        border-bottom: 3px solid transparent;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .nav-tabs .nav-link:hover {
        color: #495057;
        border-bottom-color: #dee2e6;
    }

    .nav-tabs .nav-link.active {
        color: #0d6efd;
        border-bottom-color: #0d6efd;
        background-color: transparent;
    }

    .tab-pane {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* Clean Action Buttons */
    .btn-group-sm .btn {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }

    .btn-outline-primary:hover, .btn-outline-secondary:hover, .btn-outline-danger:hover, .btn-outline-success:hover {
        color: white;
    }

    .table td {
        vertical-align: middle;
    }

    .table thead th {
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        background-color: #f8f9fa;
    }

    .global-field-group-row {
        cursor: pointer;
    }

    .global-field-options-row td {
        border-top: 0;
    }

    .global-options-toggle .bi {
        transition: transform 0.2s ease;
    }

    .global-options-toggle.is-open .bi {
        transform: rotate(180deg);
    }

    .settings-notification-message {
        white-space: pre-line;
    }

    #settingsNotificationModal.settings-notification-top-layer {
        z-index: 1090;
    }

    .modal-backdrop.settings-notification-backdrop {
        z-index: 1085;
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const purposeCategoryOptionsElement = document.getElementById('purposeCategoryOptionsData');
    const purposeCategoryOptions = purposeCategoryOptionsElement
        ? JSON.parse(purposeCategoryOptionsElement.dataset.options || '{}')
        : {};
    const settingsNotificationModalElement = document.getElementById('settingsNotificationModal');
    const settingsNotificationModal = settingsNotificationModalElement
        ? bootstrap.Modal.getOrCreateInstance(settingsNotificationModalElement)
        : null;
    const settingsNotificationHeader = document.getElementById('settingsNotificationHeader');
    const settingsNotificationTitle = document.getElementById('settingsNotificationModalLabel');
    const settingsNotificationIcon = document.getElementById('settingsNotificationIcon');
    const settingsNotificationMessage = document.getElementById('settingsNotificationMessage');
    const settingsNotificationToneClasses = {
        info: {
            header: ['bg-info-subtle', 'text-info-emphasis'],
            icon: ['bi-info-circle', 'text-info'],
        },
        success: {
            header: ['bg-success-subtle', 'text-success-emphasis'],
            icon: ['bi-check-circle', 'text-success'],
        },
        warning: {
            header: ['bg-warning-subtle', 'text-warning-emphasis'],
            icon: ['bi-exclamation-triangle', 'text-warning'],
        },
        danger: {
            header: ['bg-danger-subtle', 'text-danger-emphasis'],
            icon: ['bi-exclamation-octagon', 'text-danger'],
        },
    };
    const settingsNotificationHeaderClasses = [...new Set(
        Object.values(settingsNotificationToneClasses).flatMap((config) => config.header)
    )];
    const settingsNotificationIconClasses = [...new Set(
        Object.values(settingsNotificationToneClasses).flatMap((config) => config.icon)
    )];
    let settingsNotificationResolve = null;

    function getLatestModalBackdrop() {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        return backdrops.length ? backdrops[backdrops.length - 1] : null;
    }

    function showSettingsNotification(message, { title = 'Notice', tone = 'info' } = {}) {
        if (!settingsNotificationModalElement || !settingsNotificationModal || !settingsNotificationTitle || !settingsNotificationMessage) {
            window.alert(String(message || ''));
            return Promise.resolve();
        }

        if (typeof ensureModalInBody === 'function') {
            ensureModalInBody(settingsNotificationModalElement);
        }

        const style = settingsNotificationToneClasses[tone] || settingsNotificationToneClasses.info;

        settingsNotificationTitle.textContent = title;
        settingsNotificationMessage.textContent = String(message || '');

        if (settingsNotificationHeader) {
            settingsNotificationHeader.classList.remove(...settingsNotificationHeaderClasses);
            settingsNotificationHeader.classList.add(...style.header);
        }

        if (settingsNotificationIcon) {
            settingsNotificationIcon.classList.remove(...settingsNotificationIconClasses);
            settingsNotificationIcon.classList.add(...style.icon);
        }

        if (settingsNotificationResolve) {
            const resolvePrevious = settingsNotificationResolve;
            settingsNotificationResolve = null;
            resolvePrevious();
        }

        return new Promise((resolve) => {
            settingsNotificationResolve = resolve;
            settingsNotificationModal.show();
        });
    }

    if (settingsNotificationModalElement) {
        settingsNotificationModalElement.addEventListener('shown.bs.modal', function () {
            settingsNotificationModalElement.classList.add('settings-notification-top-layer');

            const latestBackdrop = typeof getLatestBackdrop === 'function'
                ? getLatestBackdrop()
                : getLatestModalBackdrop();

            if (latestBackdrop) {
                latestBackdrop.classList.add('settings-notification-backdrop');
            }
        });

        settingsNotificationModalElement.addEventListener('hidden.bs.modal', function () {
            settingsNotificationModalElement.classList.remove('settings-notification-top-layer');
            document.querySelectorAll('.modal-backdrop.settings-notification-backdrop').forEach((backdrop) => {
                backdrop.classList.remove('settings-notification-backdrop');
            });

            if (settingsNotificationResolve) {
                const resolve = settingsNotificationResolve;
                settingsNotificationResolve = null;
                resolve();
            }
        });
    }

    const settingsTabByKey = {
        agencies: 'agencies-tab',
        'resource-types': 'resource-types-tab',
        'form-fields': 'form-fields-tab',
    };
    const settingsTabKeyById = {
        'agencies-tab': 'agencies',
        'resource-types-tab': 'resource-types',
        'form-fields-tab': 'form-fields',
    };
    const resourceSubTabByKey = {
        'resource-types': 'resource-types-sub-tab',
        purposes: 'purposes-sub-tab',
    };
    const resourceSubTabKeyById = {
        'resource-types-sub-tab': 'resource-types',
        'purposes-sub-tab': 'purposes',
    };

    function showBootstrapTab(buttonId) {
        const button = document.getElementById(buttonId);
        if (!button) {
            return;
        }

        bootstrap.Tab.getOrCreateInstance(button).show();
    }

    function updateSettingsUrlState({ tab = null, subtab = null } = {}) {
        const url = new URL(window.location.href);

        if (tab === null) {
            url.searchParams.delete('tab');
        } else {
            url.searchParams.set('tab', tab);
        }

        if (subtab === null) {
            url.searchParams.delete('subtab');
        } else {
            url.searchParams.set('subtab', subtab);
        }

        const nextSearch = url.searchParams.toString();
        const nextUrl = `${url.pathname}${nextSearch ? `?${nextSearch}` : ''}${url.hash}`;
        window.history.replaceState({}, '', nextUrl);
    }

    function getActiveSettingsTabKey() {
        const activeMainTab = document.querySelector('#settingsTabs .nav-link.active');
        return activeMainTab ? settingsTabKeyById[activeMainTab.id] || null : null;
    }

    function getActiveResourceSubTabKey() {
        const activeSubTab = document.querySelector('#resourceTypesPurposesTabs .nav-link.active');
        return activeSubTab ? resourceSubTabKeyById[activeSubTab.id] || 'resource-types' : 'resource-types';
    }

    function reloadWithCurrentSettingsTab() {
        const activeTabKey = getActiveSettingsTabKey();

        if (activeTabKey === 'resource-types') {
            updateSettingsUrlState({ tab: activeTabKey, subtab: getActiveResourceSubTabKey() });
        } else if (activeTabKey) {
            updateSettingsUrlState({ tab: activeTabKey, subtab: null });
        }

        location.reload();
    }

    const initialParams = new URLSearchParams(window.location.search);
    const initialTabKey = initialParams.get('tab');
    const hashTabKey = {
        '#agencies-content': 'agencies',
        '#resource-types-content': 'resource-types',
        '#form-fields-content': 'form-fields',
    }[window.location.hash] || null;
    const resolvedTabKey = initialTabKey && settingsTabByKey[initialTabKey] ? initialTabKey : hashTabKey;

    if (resolvedTabKey && settingsTabByKey[resolvedTabKey]) {
        showBootstrapTab(settingsTabByKey[resolvedTabKey]);
    }

    const initialSubTabKey = initialParams.get('subtab');
    if (resolvedTabKey === 'resource-types' && initialSubTabKey && resourceSubTabByKey[initialSubTabKey]) {
        showBootstrapTab(resourceSubTabByKey[initialSubTabKey]);
    }

    document.querySelectorAll('#settingsTabs [data-bs-toggle="tab"]').forEach((tabButton) => {
        tabButton.addEventListener('shown.bs.tab', function (event) {
            const tabKey = settingsTabKeyById[event.target.id];
            if (!tabKey) {
                return;
            }

            if (tabKey !== 'resource-types') {
                updateSettingsUrlState({ tab: tabKey, subtab: null });
                return;
            }

            const activeSubTab = document.querySelector('#resourceTypesPurposesTabs .nav-link.active');
            const subTabKey = activeSubTab ? resourceSubTabKeyById[activeSubTab.id] : 'resource-types';
            updateSettingsUrlState({ tab: tabKey, subtab: subTabKey || 'resource-types' });
        });
    });

    document.querySelectorAll('#resourceTypesPurposesTabs [data-bs-toggle="tab"]').forEach((subTabButton) => {
        subTabButton.addEventListener('shown.bs.tab', function (event) {
            const activeMainTab = document.querySelector('#settingsTabs .nav-link.active');
            if (!activeMainTab || activeMainTab.id !== 'resource-types-tab') {
                return;
            }

            const subTabKey = resourceSubTabKeyById[event.target.id];
            if (subTabKey) {
                updateSettingsUrlState({ tab: 'resource-types', subtab: subTabKey });
            }
        });
    });

    document.querySelectorAll('.toggle-core-required-btn').forEach((button) => {
        button.addEventListener('click', async function() {
            const row = this.closest('tr');
            const fieldName = this.dataset.fieldName;
            const nextRequired = this.dataset.nextRequired === '1';

            if (!fieldName || !row) {
                return;
            }

            this.disabled = true;

            try {
                const response = await fetch(`/admin/settings/classification-core-fields/${encodeURIComponent(fieldName)}/required`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ is_required: nextRequired ? 1 : 0 })
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Failed to update field requirement.');
                }

                const isRequired = typeof result.is_required !== 'undefined'
                    ? !!result.is_required
                    : !!result.data?.is_required;
                const badge = row.querySelector('.core-required-badge');

                if (badge) {
                    badge.textContent = isRequired ? 'Required' : 'Optional';
                    badge.classList.toggle('bg-danger', isRequired);
                    badge.classList.toggle('bg-secondary', !isRequired);
                }

                this.dataset.nextRequired = isRequired ? '0' : '1';
                this.textContent = isRequired ? 'Set Optional' : 'Set Required';
            } catch (error) {
                showSettingsNotification(error.message || 'Error updating classification core field.', {
                    title: 'Update Failed',
                    tone: 'danger',
                });
            } finally {
                this.disabled = false;
            }
        });
    });

    const classificationCoreFieldModalElement = document.getElementById('editClassificationCoreFieldModal');
    const classificationCoreFieldModal = classificationCoreFieldModalElement
        ? bootstrap.Modal.getOrCreateInstance(classificationCoreFieldModalElement)
        : null;
    const classificationCoreFieldForm = document.getElementById('classificationCoreFieldForm');
    const classificationCoreFieldNameInput = document.getElementById('classificationCoreFieldName');
    const classificationCoreFieldLabelInput = document.getElementById('classificationCoreFieldLabel');
    const classificationCoreFieldSectionInput = document.getElementById('classificationCoreFieldSection');
    const classificationCoreFieldRequiredInput = document.getElementById('classificationCoreFieldRequired');
    const classificationCoreFieldErrors = document.getElementById('classificationCoreFieldErrors');
    const saveClassificationCoreFieldBtn = document.getElementById('saveClassificationCoreFieldBtn');

    function clearClassificationCoreFieldErrors() {
        if (!classificationCoreFieldErrors) {
            return;
        }

        classificationCoreFieldErrors.classList.add('d-none');
        classificationCoreFieldErrors.textContent = '';
    }

    document.querySelectorAll('.edit-classification-core-btn').forEach((button) => {
        button.addEventListener('click', function() {
            if (!classificationCoreFieldModal || !classificationCoreFieldNameInput || !classificationCoreFieldLabelInput) {
                return;
            }

            classificationCoreFieldNameInput.value = this.dataset.fieldName || '';
            classificationCoreFieldLabelInput.value = this.dataset.label || '';
            if (classificationCoreFieldSectionInput) {
                classificationCoreFieldSectionInput.value = this.dataset.placement || '';
            }
            if (classificationCoreFieldRequiredInput) {
                classificationCoreFieldRequiredInput.checked = this.dataset.required === '1';
            }
            clearClassificationCoreFieldErrors();
            classificationCoreFieldModal.show();
        });
    });

    if (saveClassificationCoreFieldBtn) {
        saveClassificationCoreFieldBtn.addEventListener('click', async function() {
            if (!classificationCoreFieldForm || !classificationCoreFieldNameInput || !classificationCoreFieldLabelInput) {
                return;
            }

            if (!classificationCoreFieldForm.checkValidity()) {
                classificationCoreFieldForm.reportValidity();
                return;
            }

            clearClassificationCoreFieldErrors();

            const fieldName = classificationCoreFieldNameInput.value;
            const payload = {
                label: classificationCoreFieldLabelInput.value,
                is_required: classificationCoreFieldRequiredInput && classificationCoreFieldRequiredInput.checked ? 1 : 0,
            };

            saveClassificationCoreFieldBtn.disabled = true;

            try {
                const response = await fetch(`/admin/settings/classification-core-fields/${encodeURIComponent(fieldName)}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload),
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    const validationErrors = result.errors
                        ? Object.values(result.errors).flat().join('\n')
                        : null;
                    throw new Error(validationErrors || result.message || 'Failed to update classification field.');
                }

                const row = document.querySelector(`tr[data-core-field-name="${fieldName}"]`);
                if (row) {
                    const labelNode = row.querySelector('.classification-core-field-label');
                    const badge = row.querySelector('.core-required-badge');
                    const toggleBtn = row.querySelector('.toggle-core-required-btn');
                    const editBtn = row.querySelector('.edit-classification-core-btn');
                    const isRequired = !!result.is_required;

                    if (labelNode) {
                        labelNode.textContent = result.label || payload.label;
                    }

                    if (badge) {
                        badge.textContent = isRequired ? 'Required' : 'Optional';
                        badge.classList.toggle('bg-danger', isRequired);
                        badge.classList.toggle('bg-secondary', !isRequired);
                    }

                    if (toggleBtn) {
                        toggleBtn.dataset.nextRequired = isRequired ? '0' : '1';
                        toggleBtn.textContent = isRequired ? 'Set Optional' : 'Set Required';
                    }

                    if (editBtn) {
                        editBtn.dataset.label = result.label || payload.label;
                        editBtn.dataset.required = isRequired ? '1' : '0';
                    }
                }

                classificationCoreFieldModal.hide();
            } catch (error) {
                if (classificationCoreFieldErrors) {
                    classificationCoreFieldErrors.textContent = error.message || 'Error updating classification core field.';
                    classificationCoreFieldErrors.classList.remove('d-none');
                }
            } finally {
                saveClassificationCoreFieldBtn.disabled = false;
            }
        });
    }

    function populatePurposeTypeSelect(typeSelectId, category, selectedType = '') {
        const typeSelect = document.getElementById(typeSelectId);
        const types = purposeCategoryOptions[category]?.types ?? [];

        typeSelect.innerHTML = '<option value="">-- Select Type --</option>';

        types.forEach(type => {
            const option = document.createElement('option');
            option.value = type;
            option.textContent = type;

            if (type === selectedType) {
                option.selected = true;
            }

            typeSelect.appendChild(option);
        });

        typeSelect.disabled = types.length === 0;
    }

    // ========== AGENCIES MANAGEMENT ==========
    const classificationsApiUrl = "{{ route('api.classifications') }}";

    async function loadClassifications(containerId) {
        try {
            const response = await fetch(classificationsApiUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) throw new Error('Failed to load classifications');
            const classifications = await response.json();

            const container = document.getElementById(containerId);
            container.innerHTML = '';
            classifications.forEach(c => {
                const checkboxId = `${containerId}-${c.id}`;
                const div = document.createElement('div');
                div.className = 'form-check';
                div.innerHTML = `
                    <input class="form-check-input classification-checkbox" type="checkbox"
                           id="${checkboxId}" name="classifications" value="${c.id}">
                    <label class="form-check-label" for="${checkboxId}">
                        ${c.name}
                    </label>
                `;
                container.appendChild(div);
            });
        } catch (error) {
            console.error('Error loading classifications:', error);
        }
    }

    loadClassifications('classificationsContainer');
    loadClassifications('editClassificationsContainer');

    // Add Agency
    document.getElementById('addAgencyModal').addEventListener('show.bs.modal', function() {
        document.getElementById('agencyForm').reset();
        document.getElementById('agencyId').value = '';
        document.querySelectorAll('#classificationsContainer .classification-checkbox').forEach(cb => cb.checked = false);
    });

    document.getElementById('saveAgencyBtn').addEventListener('click', async function() {
        const form = document.getElementById('agencyForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const classifications = Array.from(document.querySelectorAll('#classificationsContainer .classification-checkbox:checked'))
            .map(cb => cb.value);

        if (classifications.length === 0) {
            await showSettingsNotification('Please select at least one classification.', {
                title: 'Classification Required',
                tone: 'warning',
            });
            return;
        }

        const formData = {
            name: document.getElementById('agencyName').value,
            full_name: document.getElementById('agencyFullName').value,
            description: document.getElementById('agencyDescription').value,
            is_active: document.getElementById('agencyActive').checked ? 1 : 0,
            classifications: classifications
        };

        try {
            const response = await fetch('/admin/settings/agencies', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(formData)
            });

            if (!response.ok) {
                const error = await response.json();
                await showSettingsNotification(error.message || 'Failed to save agency', {
                    title: 'Save Failed',
                    tone: 'danger',
                });
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('addAgencyModal')).hide();
            reloadWithCurrentSettingsTab();
        } catch (error) {
            console.error('Error saving agency:', error);
            showSettingsNotification('Error saving agency.', {
                title: 'Save Failed',
                tone: 'danger',
            });
        }
    });

    // Edit Agency
    document.querySelectorAll('.edit-agency-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const agencyId = this.dataset.agencyId;
            loadAgencyData(agencyId);
        });
    });

    async function loadAgencyData(agencyId) {
        try {
            const response = await fetch(`/admin/settings/agencies/${agencyId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) throw new Error('Failed to load agency');
            const agency = await response.json();

            document.getElementById('editAgencyId').value = agency.id;
            document.getElementById('editAgencyName').value = agency.name;
            document.getElementById('editAgencyFullName').value = agency.full_name;
            document.getElementById('editAgencyDescription').value = agency.description || '';
            document.getElementById('editAgencyActive').checked = agency.is_active;

            document.querySelectorAll('#editClassificationsContainer .classification-checkbox').forEach(cb => {
                cb.checked = agency.classification_ids.includes(parseInt(cb.value));
            });
        } catch (error) {
            console.error('Error loading agency:', error);
            showSettingsNotification('Error loading agency data.', {
                title: 'Load Failed',
                tone: 'danger',
            });
        }
    }

    document.getElementById('saveEditAgencyBtn').addEventListener('click', async function() {
        const form = document.getElementById('editAgencyForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const agencyId = document.getElementById('editAgencyId').value;
        const classifications = Array.from(document.querySelectorAll('#editClassificationsContainer .classification-checkbox:checked'))
            .map(cb => cb.value);

        if (classifications.length === 0) {
            await showSettingsNotification('Please select at least one classification.', {
                title: 'Classification Required',
                tone: 'warning',
            });
            return;
        }

        const formData = {
            name: document.getElementById('editAgencyName').value,
            full_name: document.getElementById('editAgencyFullName').value,
            description: document.getElementById('editAgencyDescription').value,
            is_active: document.getElementById('editAgencyActive').checked ? 1 : 0,
            classifications: classifications
        };

        try {
            const response = await fetch(`/admin/settings/agencies/${agencyId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(formData)
            });

            if (!response.ok) {
                const error = await response.json();
                await showSettingsNotification(error.message || 'Failed to update agency', {
                    title: 'Update Failed',
                    tone: 'danger',
                });
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('editAgencyModal')).hide();
            reloadWithCurrentSettingsTab();
        } catch (error) {
            console.error('Error updating agency:', error);
            showSettingsNotification('Error updating agency.', {
                title: 'Update Failed',
                tone: 'danger',
            });
        }
    });

    // Deactivate Agency
    document.querySelectorAll('.deactivate-agency-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const agencyId = this.dataset.agencyId;
            const agencyName = this.dataset.agencyName;

            confirmThenRun(
                'Confirm Deactivation',
                `Deactivate "${agencyName}"? The agency will be marked as inactive and hidden from operational forms, but existing records are preserved.`,
                function () {
                    deactivateAgency(agencyId);
                }
            );
        });
    });

    // Activate Agency
    document.querySelectorAll('.activate-agency-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const agencyId = this.dataset.agencyId;
            const agencyName = this.dataset.agencyName;

            confirmThenRun(
                'Confirm Activation',
                `Activate "${agencyName}"? The agency will appear again in operational forms and filters.`,
                function () {
                    activateAgency(agencyId);
                }
            );
        });
    });

    async function deactivateAgency(agencyId) {
        try {
            const response = await fetch(`/admin/settings/agencies/${agencyId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Failed to deactivate agency');
            reloadWithCurrentSettingsTab();
        } catch (error) {
            console.error('Error deactivating agency:', error);
            showSettingsNotification('Error deactivating agency.', {
                title: 'Action Failed',
                tone: 'danger',
            });
        }
    }

    async function activateAgency(agencyId) {
        try {
            const response = await fetch(`/admin/settings/agencies/${agencyId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ is_active: true })
            });

            if (!response.ok) throw new Error('Failed to activate agency');
            reloadWithCurrentSettingsTab();
        } catch (error) {
            console.error('Error activating agency:', error);
            showSettingsNotification('Error activating agency.', {
                title: 'Action Failed',
                tone: 'danger',
            });
        }
    }

    // Manage Form Fields
    const agencyFieldTypeInput = document.getElementById('fieldType');
    const agencyFieldOptionsWrapper = document.getElementById('fieldOptionsWrapper');
    const agencyFieldOptionsInput = document.getElementById('fieldOptionsInput');

    function isAgencyOptionType(fieldType) {
        return ['dropdown', 'checkbox'].includes(String(fieldType || '').toLowerCase());
    }

    function toggleAgencyFieldOptions(fieldType) {
        if (!agencyFieldOptionsWrapper || !agencyFieldOptionsInput) {
            return;
        }

        const show = isAgencyOptionType(fieldType);
        agencyFieldOptionsWrapper.classList.toggle('d-none', !show);
        agencyFieldOptionsInput.disabled = !show;
    }

    function serializeAgencyFieldOptions(rawText) {
        return String(rawText || '')
            .split(/\r?\n/)
            .map(line => line.trim())
            .filter(Boolean)
            .map((line) => {
                const [labelRaw, valueRaw] = line.split('|', 2);
                const label = (labelRaw || '').trim();
                const value = (valueRaw || '').trim();

                if (!label && !value) {
                    return null;
                }

                return {
                    label: label || value,
                    value: value || ''
                };
            })
            .filter(Boolean);
    }

    function deserializeAgencyFieldOptions(options) {
        if (!Array.isArray(options) || options.length === 0) {
            return '';
        }

        return options
            .map((option) => {
                const label = String(option?.label || '').trim();
                const value = String(option?.value || '').trim();

                if (!label && !value) {
                    return '';
                }

                return value ? `${label}|${value}` : label;
            })
            .filter(Boolean)
            .join('\n');
    }

    document.querySelectorAll('.manage-fields-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const agencyId = this.dataset.agencyId;
            document.getElementById('fieldAgencyId').value = agencyId;
            loadFormFields(agencyId);
        });
    });

    async function loadFormFields(agencyId) {
        try {
            const container = document.getElementById('fieldsListContainer');
            console.log('Loading form fields for agency ID:', agencyId);

            const response = await fetch(`/admin/settings/agencies/${agencyId}/form-fields`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            console.log('Response status:', response.status);

            if (!response.ok) throw new Error(`Failed to load form fields: HTTP ${response.status}`);

            const fields = await response.json();
            console.log('Fields received:', fields);

            if (!fields || fields.length === 0) {
                container.innerHTML = '<p class="text-muted p-3">No agency-specific fields configured yet. Click "Add Agency Field" to create one. Classification core fields are managed in Form Fields > Classification Core Fields.</p>';
            } else {
                let html = '<div class="list-group">';
                fields.forEach(field => {
                    const fieldType = field.field_type ? field.field_type.charAt(0).toUpperCase() + field.field_type.slice(1) : 'Text';
                    const requiredBadge = field.is_required ? '<span class="badge bg-danger ms-2">Required</span>' : '';
                    const optionCount = Array.isArray(field.options) ? field.options.length : 0;
                    const optionBadge = isAgencyOptionType(field.field_type)
                        ? `<span class="badge bg-light text-dark border ms-2">${optionCount} option${optionCount === 1 ? '' : 's'}</span>`
                        : '';

                    html += `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${field.display_label}</h6>
                                    <p class="mb-1 small text-muted">
                                        <strong>Field Name:</strong> ${field.field_name} |
                                        <strong>Type:</strong> ${fieldType}
                                        ${requiredBadge}
                                        ${optionBadge}
                                    </p>
                                    ${field.help_text ? `<p class="mb-0 small">${field.help_text}</p>` : ''}
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary edit-field-btn" data-field-id="${field.id}" data-agency-id="${agencyId}" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-danger delete-field-btn" data-field-id="${field.id}" data-agency-id="${agencyId}" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;

                document.querySelectorAll('.edit-field-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const fieldId = this.dataset.fieldId;
                        const agencyId = this.dataset.agencyId;
                        editField(fieldId, agencyId);
                    });
                });

                document.querySelectorAll('.delete-field-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const fieldId = this.dataset.fieldId;
                        const agencyId = this.dataset.agencyId;
                        confirmThenRun(
                            'Remove Field',
                            'Remove this field from active forms? If beneficiary records already use it, the field will be archived and existing profile data will be kept.',
                            function () {
                                deleteField(fieldId, agencyId);
                            }
                        );
                    });
                });
            }
        } catch (error) {
            console.error('Error loading form fields:', error);
            document.getElementById('fieldsListContainer').innerHTML = '<p class="text-danger p-3">Error loading form fields: ' + error.message + '</p>';
        }
    }

    document.getElementById('addFieldBtn').addEventListener('click', function() {
        document.getElementById('fieldForm').reset();
        document.getElementById('fieldId').value = '';
        if (agencyFieldOptionsInput) {
            agencyFieldOptionsInput.value = '';
        }
        toggleAgencyFieldOptions(agencyFieldTypeInput?.value);
        document.querySelector('#addFieldModal .modal-title').textContent = 'Add Agency Field';
    });

    async function editField(fieldId, agencyId) {
        try {
            const response = await fetch(`/admin/settings/agencies/${agencyId}/form-fields/${fieldId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) throw new Error('Failed to load field');
            const field = await response.json();

            document.getElementById('fieldId').value = field.id;
            document.getElementById('fieldName').value = field.field_name;
            document.getElementById('fieldLabel').value = field.display_label;
            document.getElementById('fieldType').value = field.field_type;
            document.getElementById('fieldRequired').checked = field.is_required;
            document.getElementById('fieldHelpText').value = field.help_text || '';
            document.getElementById('fieldSection').value = field.form_section || '';
            document.getElementById('fieldSort').value = field.sort_order || 0;
            if (agencyFieldOptionsInput) {
                agencyFieldOptionsInput.value = deserializeAgencyFieldOptions(field.options || []);
            }
            toggleAgencyFieldOptions(field.field_type);

            document.querySelector('#addFieldModal .modal-title').textContent = 'Edit Agency Field';
            bootstrap.Modal.getOrCreateInstance(document.getElementById('addFieldModal')).show();
        } catch (error) {
            console.error('Error loading field:', error);
            showSettingsNotification('Error loading field.', {
                title: 'Load Failed',
                tone: 'danger',
            });
        }
    }

    async function deleteField(fieldId, agencyId) {
        try {
            const response = await fetch(`/admin/settings/agencies/${agencyId}/form-fields/${fieldId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (!response.ok) throw new Error(result.message || result.error || 'Failed to delete field');
            if (result.archived && result.message) {
                await showSettingsNotification(result.message, {
                    title: 'Field Archived',
                    tone: 'info',
                });
            }
            loadFormFields(agencyId);
        } catch (error) {
            console.error('Error deleting field:', error);
            showSettingsNotification(error.message || 'Error deleting field.', {
                title: 'Delete Failed',
                tone: 'danger',
            });
        }
    }

    document.getElementById('saveFieldBtn').addEventListener('click', async function() {
        const form = document.getElementById('fieldForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const agencyId = document.getElementById('fieldAgencyId').value;
        const fieldId = document.getElementById('fieldId').value;
        const formData = {
            field_name: document.getElementById('fieldName').value,
            display_label: document.getElementById('fieldLabel').value,
            field_type: document.getElementById('fieldType').value,
            is_required: document.getElementById('fieldRequired').checked ? 1 : 0,
            help_text: document.getElementById('fieldHelpText').value,
            form_section: document.getElementById('fieldSection').value,
            sort_order: parseInt(document.getElementById('fieldSort').value),
            options: serializeAgencyFieldOptions(agencyFieldOptionsInput ? agencyFieldOptionsInput.value : '')
        };

        console.log('Sending form data:', formData);

        try {
            let url = `/admin/settings/agencies/${agencyId}/form-fields`;
            let method = 'POST';

            if (fieldId) {
                url += `/${fieldId}`;
                method = 'PUT';
            }

            console.log(`${method} request to ${url}`);

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(formData)
            });

            console.log('Response status:', response.status);

            if (!response.ok) {
                const error = await response.json();
                console.error('Server error:', error);
                const errorMessage = error.message || 'Failed to save field';
                if (error.errors) {
                    const errorDetails = Object.values(error.errors).flat().join('\n');
                    await showSettingsNotification(`${errorMessage}\n\n${errorDetails}`, {
                        title: 'Save Failed',
                        tone: 'danger',
                    });
                } else {
                    await showSettingsNotification(errorMessage, {
                        title: 'Save Failed',
                        tone: 'danger',
                    });
                }
                return;
            }

            const result = await response.json();
            console.log('Field saved successfully:', result);
            bootstrap.Modal.getInstance(document.getElementById('addFieldModal')).hide();
            loadFormFields(agencyId);
        } catch (error) {
            console.error('Error saving field:', error);
            showSettingsNotification(error.message || 'Error saving field.', {
                title: 'Save Failed',
                tone: 'danger',
            });
        }
    });

    // Global form fields (tabbed settings page)
    if (agencyFieldTypeInput) {
        agencyFieldTypeInput.addEventListener('change', function() {
            toggleAgencyFieldOptions(this.value);
        });
        toggleAgencyFieldOptions(agencyFieldTypeInput.value);
    }

    function normalizeKey(value) {
        return String(value || '')
            .trim()
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');
    }

    const globalFieldModal = document.getElementById('addGlobalFieldModal');
    const globalFieldForm = document.getElementById('globalFieldForm');
    const globalFieldErrors = document.getElementById('globalFieldErrors');
    const globalFieldModalTitle = document.getElementById('addGlobalFieldModalLabel');
    const globalFieldSaveBtn = document.getElementById('saveGlobalFieldBtn');
    const globalFieldIdInput = document.getElementById('globalFieldId');
    const globalFieldGroupInput = document.getElementById('globalFieldGroup');
    const globalFieldGroupHelp = document.getElementById('globalFieldGroupHelp');
    const globalFieldTypeInput = document.getElementById('globalFieldType');
    const globalFieldLabelInput = document.getElementById('globalFieldLabel');
    const globalFieldLabelLabel = document.getElementById('globalFieldLabelLabel');
    const globalFieldValueWrapper = document.getElementById('globalFieldValueWrapper');
    const globalFieldValueLabel = document.getElementById('globalFieldValueLabel');
    const globalFieldValueInput = document.getElementById('globalFieldValue');
    const globalFieldValueHelp = document.getElementById('globalFieldValueHelp');
    const globalFieldPlacementInput = document.getElementById('globalFieldPlacement');
    const globalFieldSortOrderInput = document.getElementById('globalFieldSortOrder');
    const globalFieldRequiredInput = document.getElementById('globalFieldRequired');
    const globalFieldActiveInput = document.getElementById('globalFieldActive');
    const optionBasedGlobalTypes = ['dropdown', 'radio', 'checkbox'];

    document.querySelectorAll('.global-options-toggle').forEach((toggleBtn) => {
        const targetSelector = toggleBtn.getAttribute('data-bs-target');
        if (!targetSelector) {
            return;
        }

        const collapseElement = document.querySelector(targetSelector);
        if (!collapseElement) {
            return;
        }

        toggleBtn.addEventListener('click', function(event) {
            event.stopPropagation();
        });

        collapseElement.addEventListener('show.bs.collapse', function() {
            toggleBtn.classList.add('is-open');
            const toggleLabel = toggleBtn.querySelector('.toggle-label');
            if (toggleLabel) {
                toggleLabel.textContent = 'Hide';
            }
            toggleBtn.title = 'Hide options';
        });

        collapseElement.addEventListener('hide.bs.collapse', function() {
            toggleBtn.classList.remove('is-open');
            const toggleLabel = toggleBtn.querySelector('.toggle-label');
            if (toggleLabel) {
                toggleLabel.textContent = 'View';
            }
            toggleBtn.title = 'Show options';
        });
    });

    document.querySelectorAll('.global-field-group-row').forEach((groupRow) => {
        groupRow.addEventListener('click', function(event) {
            if (event.target.closest('.global-options-toggle')) {
                return;
            }

            const toggleBtn = groupRow.querySelector('.global-options-toggle');
            if (toggleBtn) {
                toggleBtn.click();
            }
        });
    });

    function isOptionBasedGlobalType(fieldType) {
        return optionBasedGlobalTypes.includes(fieldType);
    }

    function applyGlobalFieldTypeState(fieldType) {
        const optionBased = isOptionBasedGlobalType(fieldType);

        globalFieldLabelLabel.innerHTML = optionBased
            ? 'Option Label <span class="text-danger">*</span>'
            : 'Field Label <span class="text-danger">*</span>';

        globalFieldValueWrapper.classList.toggle('d-none', !optionBased);
        globalFieldValueInput.disabled = !optionBased;
        globalFieldValueInput.required = false;
        globalFieldValueLabel.innerHTML = optionBased
            ? 'Option Value'
            : 'Stored Value';
        globalFieldValueHelp.textContent = optionBased
            ? 'Auto-generated from label if left blank.'
            : 'Single-value field types automatically use the field group as the stored key.';

        if (!optionBased) {
            globalFieldValueInput.value = normalizeKey(globalFieldGroupInput.value);
        }
    }

    function clearGlobalFieldErrors() {
        globalFieldErrors.classList.add('d-none');
        globalFieldErrors.textContent = '';
    }

    function setGlobalFieldModalMode(mode) {
        const isEdit = mode === 'edit';
        globalFieldModal.dataset.mode = mode;
        globalFieldModalTitle.textContent = isEdit ? 'Edit Global Form Field' : 'Add Global Form Field';
        globalFieldSaveBtn.textContent = isEdit ? 'Update Field' : 'Save Field';
        globalFieldGroupInput.readOnly = false;
        globalFieldTypeInput.disabled = false;
        globalFieldGroupHelp.textContent = isEdit
            ? 'Field group can be updated while editing this option.'
            : 'Lowercase, alphanumeric, and underscores only.';
    }

    function resetGlobalFieldForm() {
        globalFieldForm.reset();
        globalFieldIdInput.value = '';
        globalFieldTypeInput.value = 'dropdown';
        globalFieldTypeInput.disabled = false;
        globalFieldGroupInput.readOnly = false;
        globalFieldActiveInput.checked = true;
        applyGlobalFieldTypeState(globalFieldTypeInput.value);
        clearGlobalFieldErrors();
        setGlobalFieldModalMode('create');
    }

    let _skipGlobalFieldReset = false;

    globalFieldModal.addEventListener('show.bs.modal', function() {
        if (_skipGlobalFieldReset) {
            _skipGlobalFieldReset = false;
            return;
        }
        if ((globalFieldModal.dataset.mode || 'create') !== 'edit') {
            resetGlobalFieldForm();
        }
    });

    globalFieldModal.addEventListener('hidden.bs.modal', function() {
        resetGlobalFieldForm();
    });

    window.openAddGlobalFieldModal = function(placement = 'personal_information', sectionLabel = 'Global') {
        resetGlobalFieldForm();

        globalFieldPlacementInput.value = placement || 'personal_information';
        globalFieldModalTitle.textContent = `Add ${sectionLabel} Field`;
        _skipGlobalFieldReset = true;
        bootstrap.Modal.getOrCreateInstance(globalFieldModal).show();
    };

    document.querySelectorAll('.section-add-field-btn').forEach((button) => {
        button.addEventListener('click', function() {
            const placement = this.dataset.placement || 'personal_information';
            const sectionLabel = this.dataset.sectionLabel || 'Global';
            window.openAddGlobalFieldModal(placement, sectionLabel);
        });
    });

    globalFieldGroupInput.addEventListener('blur', function() {
        this.value = normalizeKey(this.value);

        if (!isOptionBasedGlobalType(globalFieldTypeInput.value)) {
            globalFieldValueInput.value = this.value;
        }
    });

    globalFieldTypeInput.addEventListener('change', function() {
        applyGlobalFieldTypeState(this.value);
    });

    globalFieldValueInput.addEventListener('blur', function() {
        if (isOptionBasedGlobalType(globalFieldTypeInput.value)) {
            this.value = normalizeKey(this.value);
        }
    });

    document.querySelectorAll('.edit-global-field-btn').forEach((btn) => {
        btn.addEventListener('click', function() {
            setGlobalFieldModalMode('edit');

            globalFieldIdInput.value = this.dataset.fieldId || '';
            globalFieldGroupInput.value = this.dataset.fieldGroup || '';
            globalFieldTypeInput.value = this.dataset.fieldType || 'dropdown';
            applyGlobalFieldTypeState(globalFieldTypeInput.value);
            globalFieldLabelInput.value = this.dataset.label || '';
            globalFieldValueInput.value = this.dataset.value || '';
            globalFieldPlacementInput.value = this.dataset.placement || 'personal_information';
            globalFieldSortOrderInput.value = this.dataset.sortOrder || '';
            globalFieldRequiredInput.checked = this.dataset.required === '1';
            globalFieldActiveInput.checked = this.dataset.active !== '0';
            clearGlobalFieldErrors();

            const isCoreLocked = this.dataset.coreLocked === '1';
            globalFieldGroupInput.readOnly = isCoreLocked;
            globalFieldTypeInput.disabled = isCoreLocked;
            if (isCoreLocked) {
                globalFieldModalTitle.textContent = 'Edit Option (Schema-managed Field)';
                globalFieldGroupHelp.textContent = 'Schema-managed — field group and type cannot be changed.';
            }

            bootstrap.Modal.getOrCreateInstance(globalFieldModal).show();
        });
    });

    document.querySelectorAll('.add-core-option-btn').forEach((btn) => {
        btn.addEventListener('click', function() {
            window.openAddGlobalFieldModal(this.dataset.placement, 'Option');
            globalFieldGroupInput.value = this.dataset.fieldGroup || '';
            globalFieldGroupInput.readOnly = true;
            globalFieldTypeInput.value = this.dataset.fieldType || 'dropdown';
            globalFieldTypeInput.disabled = true;
            applyGlobalFieldTypeState(globalFieldTypeInput.value);
            globalFieldGroupHelp.textContent = 'Schema-managed — field group is fixed.';
        });
    });

    async function deleteGlobalField(fieldId) {
        try {
            const response = await fetch(`/admin/settings/form-fields/${fieldId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Failed to delete field');
            }

            if (result.archived && result.message) {
                await showSettingsNotification(result.message, {
                    title: 'Field Archived',
                    tone: 'info',
                });
            }

            reloadWithCurrentSettingsTab();
        } catch (error) {
            showSettingsNotification(error.message || 'Error deleting form field.', {
                title: 'Delete Failed',
                tone: 'danger',
            });
        }
    }

    document.querySelectorAll('.delete-global-field-btn').forEach((btn) => {
        btn.addEventListener('click', function() {
            const fieldId = this.dataset.fieldId;
            const fieldLabel = this.dataset.fieldLabel || 'this option';

            confirmThenRun(
                'Remove Field',
                `Remove "${fieldLabel}" from active forms?\n\nIf beneficiary records already use it, the field will be archived and existing profile data will be kept.`,
                function () {
                    deleteGlobalField(fieldId);
                }
            );
        });
    });

    globalFieldSaveBtn.addEventListener('click', async function() {
        if (!globalFieldForm.checkValidity()) {
            globalFieldForm.reportValidity();
            return;
        }

        clearGlobalFieldErrors();

        const fieldOptionId = globalFieldIdInput.value.trim();
        const isEditMode = fieldOptionId !== '';
        const fieldType = globalFieldTypeInput.value;
        const optionBased = isOptionBasedGlobalType(fieldType);

        const fieldGroup = normalizeKey(globalFieldGroupInput.value);
        const normalizedLabelValue = normalizeKey(globalFieldLabelInput.value);
        const optionValue = optionBased
            ? (normalizeKey(globalFieldValueInput.value) || normalizedLabelValue)
            : fieldGroup;
        const sortOrderRaw = globalFieldSortOrderInput.value.trim();

        globalFieldGroupInput.value = fieldGroup;
        globalFieldValueInput.value = optionValue;

        if (!fieldGroup || (optionBased && !optionValue)) {
            globalFieldErrors.textContent = optionBased
                ? 'Field group and option label/value must contain letters or numbers.'
                : 'Field group must contain letters or numbers.';
            globalFieldErrors.classList.remove('d-none');
            return;
        }

        const payload = {
            field_group: fieldGroup,
            field_type: fieldType,
            placement_section: globalFieldPlacementInput.value,
            label: globalFieldLabelInput.value,
            value: optionValue,
            sort_order: sortOrderRaw === '' ? null : parseInt(sortOrderRaw, 10),
            is_required: globalFieldRequiredInput.checked,
            is_active: globalFieldActiveInput.checked
        };

        try {
            const response = await fetch(
                isEditMode ? `/admin/settings/form-fields/${fieldOptionId}` : '/admin/settings/form-fields',
                {
                method: isEditMode ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
                }
            );

            let result = {};
            const responseText = await response.text();
            if (responseText) {
                result = JSON.parse(responseText);
            }

            if (!response.ok) {
                const message = result.errors
                    ? Object.values(result.errors).flat().join('\n')
                    : (result.message || `Failed to ${isEditMode ? 'update' : 'save'} field`);
                globalFieldErrors.textContent = message;
                globalFieldErrors.classList.remove('d-none');
                return;
            }

            bootstrap.Modal.getInstance(globalFieldModal).hide();
            reloadWithCurrentSettingsTab();
        } catch (error) {
            globalFieldErrors.textContent = `Error ${isEditMode ? 'updating' : 'saving'} form field.`;
            globalFieldErrors.classList.remove('d-none');
        }
    });

    // ========== RESOURCE TYPE MANAGEMENT ==========
    function ensureUnitOption(selectElement, unitValue) {
        if (!selectElement || !unitValue) {
            return;
        }

        const exists = Array.from(selectElement.options).some(option => option.value === unitValue);
        if (exists) {
            return;
        }

        const customOption = document.createElement('option');
        customOption.value = unitValue;
        customOption.textContent = `${unitValue} (Existing)`;
        selectElement.appendChild(customOption);
    }

    document.getElementById('addResourceTypeModal').addEventListener('show.bs.modal', function() {
        document.getElementById('resourceTypeForm').reset();
        document.getElementById('resourceTypeId').value = '';
    });

    document.getElementById('saveResourceTypeBtn').addEventListener('click', async function() {
        const form = document.getElementById('resourceTypeForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = {
            name: document.getElementById('rtName').value,
            unit: document.getElementById('rtUnit').value,
            agency_id: document.getElementById('rtAgency').value
        };

        try {
            const response = await fetch('/admin/settings/resource-types', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(formData)
            });

            if (!response.ok) {
                const error = await response.json();
                await showSettingsNotification(error.message || 'Failed to save resource type', {
                    title: 'Save Failed',
                    tone: 'danger',
                });
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('addResourceTypeModal')).hide();
            reloadWithCurrentSettingsTab();
        } catch (error) {
            console.error('Error saving resource type:', error);
            showSettingsNotification('Error saving resource type.', {
                title: 'Save Failed',
                tone: 'danger',
            });
        }
    });

    document.querySelectorAll('.edit-resource-type-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const resourceTypeId = this.dataset.resourceTypeId;
            loadResourceTypeData(resourceTypeId);
        });
    });

    async function loadResourceTypeData(resourceTypeId) {
        try {
            const response = await fetch(`/admin/settings/resource-types/${resourceTypeId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) throw new Error('Failed to load resource type');
            const rt = await response.json();

            document.getElementById('editResourceTypeId').value = rt.id;
            document.getElementById('editRtName').value = rt.name;
            document.getElementById('editRtAgency').value = rt.agency_id;
            ensureUnitOption(document.getElementById('editRtUnit'), rt.unit || '');
            document.getElementById('editRtUnit').value = rt.unit || '';

            bootstrap.Modal.getOrCreateInstance(document.getElementById('editResourceTypeModal')).show();
        } catch (error) {
            console.error('Error loading resource type:', error);
            showSettingsNotification('Error loading resource type data.', {
                title: 'Load Failed',
                tone: 'danger',
            });
        }
    }

    document.getElementById('saveEditResourceTypeBtn').addEventListener('click', async function() {
        const form = document.getElementById('editResourceTypeForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const resourceTypeId = document.getElementById('editResourceTypeId').value;
        const formData = {
            name: document.getElementById('editRtName').value,
            unit: document.getElementById('editRtUnit').value,
            agency_id: document.getElementById('editRtAgency').value
        };

        try {
            const response = await fetch(`/admin/settings/resource-types/${resourceTypeId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(formData)
            });

            if (!response.ok) {
                const error = await response.json();
                await showSettingsNotification(error.message || 'Failed to update resource type', {
                    title: 'Update Failed',
                    tone: 'danger',
                });
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('editResourceTypeModal')).hide();
            reloadWithCurrentSettingsTab();
        } catch (error) {
            console.error('Error updating resource type:', error);
            showSettingsNotification('Error updating resource type.', {
                title: 'Update Failed',
                tone: 'danger',
            });
        }
    });

    document.querySelectorAll('.delete-resource-type-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const resourceTypeId = this.dataset.resourceTypeId;
            const resourceTypeName = this.dataset.resourceTypeName;

            confirmThenRun(
                'Delete Resource Type',
                `Delete resource type "${resourceTypeName}"?\n\nThis action cannot be undone.`,
                function () {
                    deleteResourceType(resourceTypeId);
                }
            );
        });
    });

    async function deleteResourceType(resourceTypeId) {
        try {
            const response = await fetch(`/admin/settings/resource-types/${resourceTypeId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Failed to delete resource type');
            reloadWithCurrentSettingsTab();
        } catch (error) {
            console.error('Error deleting resource type:', error);
            showSettingsNotification('Error deleting resource type.', {
                title: 'Delete Failed',
                tone: 'danger',
            });
        }
    }

    // ========== PURPOSE MANAGEMENT ==========
    document.getElementById('addPurposeModal').addEventListener('show.bs.modal', function() {
        document.getElementById('purposeForm').reset();
        document.getElementById('purposeId').value = '';
        populatePurposeTypeSelect('purposeType', '');
    });

    document.getElementById('purposeCategory').addEventListener('change', function() {
        populatePurposeTypeSelect('purposeType', this.value);
    });

    document.getElementById('editPurposeCategory').addEventListener('change', function() {
        populatePurposeTypeSelect('editPurposeType', this.value);
    });

    document.getElementById('savePurposeBtn').addEventListener('click', async function() {
        const form = document.getElementById('purposeForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = {
            name: document.getElementById('purposeName').value,
            category: document.getElementById('purposeCategory').value,
            type: document.getElementById('purposeType').value
        };

        try {
            const response = await fetch('/admin/settings/purposes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(formData)
            });

            if (!response.ok) {
                const error = await response.json();
                const details = error.errors
                    ? Object.values(error.errors).flat().join('\n')
                    : '';

                await showSettingsNotification(details || error.message || 'Failed to save purpose', {
                    title: 'Save Failed',
                    tone: 'danger',
                });
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('addPurposeModal')).hide();
            reloadWithCurrentSettingsTab();
        } catch (error) {
            console.error('Error saving purpose:', error);
            showSettingsNotification('Error saving purpose.', {
                title: 'Save Failed',
                tone: 'danger',
            });
        }
    });

    // Edit Purpose
    document.querySelectorAll('.edit-purpose-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('editPurposeId').value = this.dataset.purposeId;
            document.getElementById('editPurposeName').value = this.dataset.purposeName;
            document.getElementById('editPurposeCategory').value = this.dataset.purposeCategory;

            populatePurposeTypeSelect('editPurposeType', this.dataset.purposeCategory, this.dataset.purposeType);

            bootstrap.Modal.getOrCreateInstance(document.getElementById('editPurposeModal')).show();
        });
    });

    document.getElementById('saveEditPurposeBtn').addEventListener('click', async function() {
        const form = document.getElementById('editPurposeForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const purposeId = document.getElementById('editPurposeId').value;
        const formData = {
            name: document.getElementById('editPurposeName').value,
            category: document.getElementById('editPurposeCategory').value,
            type: document.getElementById('editPurposeType').value
        };

        try {
            const response = await fetch(`/admin/settings/purposes/${purposeId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(formData)
            });

            if (!response.ok) {
                const error = await response.json();
                const details = error.errors
                    ? Object.values(error.errors).flat().join('\n')
                    : '';

                await showSettingsNotification(details || error.message || 'Failed to update purpose', {
                    title: 'Update Failed',
                    tone: 'danger',
                });
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('editPurposeModal')).hide();
            reloadWithCurrentSettingsTab();
        } catch (error) {
            console.error('Error updating purpose:', error);
            showSettingsNotification('Error updating purpose.', {
                title: 'Update Failed',
                tone: 'danger',
            });
        }
    });

    // Delete Purpose
    document.querySelectorAll('.delete-purpose-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const purposeId = this.dataset.purposeId;
            const purposeName = this.dataset.purposeName;

            confirmThenRun(
                'Delete Purpose',
                `Delete purpose "${purposeName}"?\n\nThis action cannot be undone.`,
                function () {
                    deletePurpose(purposeId);
                }
            );
        });
    });

    async function deletePurpose(purposeId) {
        try {
            const response = await fetch(`/admin/settings/purposes/${purposeId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Failed to delete purpose');
            reloadWithCurrentSettingsTab();
        } catch (error) {
            console.error('Error deleting purpose:', error);
            showSettingsNotification('Error deleting purpose.', {
                title: 'Delete Failed',
                tone: 'danger',
            });
        }
    }
});
</script>
@endpush

@endsection

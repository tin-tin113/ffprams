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
                <i class="bi bi-box"></i> Resource Types & Purposes
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
                                                {{ $agency->formFields()->where('is_active', true)->count() }} fields
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
                                                        title="Manage Form Fields">
                                                    <i class="bi bi-sliders"></i> Fields
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

        <!-- ========== RESOURCE TYPES & PURPOSES TAB ========== -->
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
                                            <th>Agency</th>
                                            <th>Purposes</th>
                                            <th class="text-center" style="width: 120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($resourceTypes as $resourceType)
                                            <tr data-resource-type-id="{{ $resourceType->id }}">
                                                <td><strong>{{ $resourceType->name }}</strong></td>
                                                <td><small>{{ $resourceType->agency?->name ?? 'N/A' }}</small></td>
                                                <td>
                                                    @if ($resourceType->purposes && $resourceType->purposes->count() > 0)
                                                        @foreach ($resourceType->purposes as $purpose)
                                                            <span class="badge bg-secondary">{{ $purpose->name }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted small">—</span>
                                                    @endif
                                                </td>
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
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-file-form"></i> Global Form Fields
                        </h5>
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addGlobalFieldModal">
                            <i class="bi bi-plus-circle me-1"></i> Add Field
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Field Group</th>
                                    <th>Placement</th>
                                    <th>Required</th>
                                    <th>Options Count</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($fieldGroupMeta as $meta)
                                    <tr>
                                        <td><strong>{{ Str::title(str_replace('_', ' ', $meta['group'])) }}</strong></td>
                                        <td>
                                            <small class="text-muted">
                                                {{ str_replace('_', ' ', $meta['placement']) }}
                                            </small>
                                        </td>
                                        <td>
                                            @if ($meta['required'])
                                                <span class="badge bg-danger">Required</span>
                                            @else
                                                <span class="badge bg-secondary">Optional</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $formFields[$meta['group']]->count() }} options
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-info view-field-group-btn"
                                                    data-field-group="{{ $meta['group'] }}"
                                                    title="View options">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No form fields found</td>
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
                <h5 class="modal-title" id="manageFieldsModalLabel">Manage Form Fields</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="fieldsListContainer">
                    <p class="text-muted">Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="addFieldBtn" data-bs-toggle="modal" data-bs-target="#addFieldModal">
                    <i class="bi bi-plus-circle me-1"></i> Add Form Field
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
                <h5 class="modal-title" id="addFieldModalLabel">Add Form Field</h5>
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
                            <option value="textarea">Text Area</option>
                            <option value="number">Number</option>
                            <option value="decimal">Decimal</option>
                            <option value="date">Date</option>
                            <option value="datetime">Date & Time</option>
                            <option value="dropdown">Dropdown</option>
                            <option value="checkbox">Checkboxes</option>
                        </select>
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

                    <div class="mb-3">
                        <label for="fieldSection" class="form-label">Form Section</label>
                        <select class="form-select" id="fieldSection" name="form_section">
                            <option value="">General Information</option>
                            <option value="farmer_information">Farmer Information</option>
                            <option value="fisherfolk_information">Fisherfolk Information</option>
                            <option value="dar_information">DAR Information</option>
                        </select>
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
                        <label class="form-label">Purposes <span class="text-danger">*</span></label>
                        <div id="purposesContainer" style="max-height: 250px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px;">
                            @forelse ($purposes as $purpose)
                                <div class="form-check">
                                    <input class="form-check-input purpose-checkbox" type="checkbox"
                                           id="purpose{{ $purpose->id }}" name="purposes" value="{{ $purpose->id }}">
                                    <label class="form-check-label" for="purpose{{ $purpose->id }}">
                                        {{ $purpose->name }} <small class="text-muted">({{ $purpose->category }})</small>
                                    </label>
                                </div>
                            @empty
                                <p class="text-muted small">No purposes available</p>
                            @endforelse
                        </div>
                        <small class="text-muted">Select at least one purpose</small>
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
                        <label class="form-label">Purposes <span class="text-danger">*</span></label>
                        <div id="editPurposesContainer" style="max-height: 250px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px;">
                            @forelse ($purposes as $purpose)
                                <div class="form-check">
                                    <input class="form-check-input edit-purpose-checkbox" type="checkbox"
                                           id="editPurpose{{ $purpose->id }}" name="purposes" value="{{ $purpose->id }}">
                                    <label class="form-check-label" for="editPurpose{{ $purpose->id }}">
                                        {{ $purpose->name }} <small class="text-muted">({{ $purpose->category }})</small>
                                    </label>
                                </div>
                            @empty
                                <p class="text-muted small">No purposes available</p>
                            @endforelse
                        </div>
                        <small class="text-muted">Select at least one purpose</small>
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
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const purposeCategoryOptionsElement = document.getElementById('purposeCategoryOptionsData');
    const purposeCategoryOptions = purposeCategoryOptionsElement
        ? JSON.parse(purposeCategoryOptionsElement.dataset.options || '{}')
        : {};

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
    async function loadClassifications(containerId) {
        try {
            const response = await fetch('/api/classifications', {
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
            alert('Please select at least one classification');
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
                alert('Error: ' + (error.message || 'Failed to save agency'));
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('addAgencyModal')).hide();
            location.reload();
        } catch (error) {
            console.error('Error saving agency:', error);
            alert('Error saving agency');
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
            alert('Error loading agency data');
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
            alert('Please select at least one classification');
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
                alert('Error: ' + (error.message || 'Failed to update agency'));
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('editAgencyModal')).hide();
            location.reload();
        } catch (error) {
            console.error('Error updating agency:', error);
            alert('Error updating agency');
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
            location.reload();
        } catch (error) {
            console.error('Error deactivating agency:', error);
            alert('Error deactivating agency');
        }
    }

    async function activateAgency(agencyId) {
        try {
            const response = await fetch(`/admin/settings/agencies/${agencyId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ is_active: true })
            });

            if (!response.ok) throw new Error('Failed to activate agency');
            location.reload();
        } catch (error) {
            console.error('Error activating agency:', error);
            alert('Error activating agency');
        }
    }

    // Manage Form Fields
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
                container.innerHTML = '<p class="text-muted p-3">No form fields configured yet. Click "Add Form Field" to create one.</p>';
            } else {
                let html = '<div class="list-group">';
                fields.forEach(field => {
                    const fieldType = field.field_type ? field.field_type.charAt(0).toUpperCase() + field.field_type.slice(1) : 'Text';
                    const requiredBadge = field.is_required ? '<span class="badge bg-danger ms-2">Required</span>' : '';

                    html += `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${field.display_label}</h6>
                                    <p class="mb-1 small text-muted">
                                        <strong>Field Name:</strong> ${field.field_name} |
                                        <strong>Type:</strong> ${fieldType}
                                        ${requiredBadge}
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
                            'Confirm Deletion',
                            'Delete this form field? This action cannot be undone.',
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
        document.querySelector('#addFieldModal .modal-title').textContent = 'Add Form Field';
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

            document.querySelector('#addFieldModal .modal-title').textContent = 'Edit Form Field';
            bootstrap.Modal.getOrCreateInstance(document.getElementById('addFieldModal')).show();
        } catch (error) {
            console.error('Error loading field:', error);
            alert('Error loading field');
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

            if (!response.ok) throw new Error('Failed to delete field');
            loadFormFields(agencyId);
        } catch (error) {
            console.error('Error deleting field:', error);
            alert('Error deleting field');
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
            sort_order: parseInt(document.getElementById('fieldSort').value)
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
                    alert(errorMessage + '\n\n' + errorDetails);
                } else {
                    alert('Error: ' + errorMessage);
                }
                return;
            }

            const result = await response.json();
            console.log('Field saved successfully:', result);
            bootstrap.Modal.getInstance(document.getElementById('addFieldModal')).hide();
            loadFormFields(agencyId);
        } catch (error) {
            console.error('Error saving field:', error);
            alert('Error saving field: ' + error.message);
        }
    });

    // ========== RESOURCE TYPE MANAGEMENT ==========
    document.getElementById('addResourceTypeModal').addEventListener('show.bs.modal', function() {
        document.getElementById('resourceTypeForm').reset();
        document.getElementById('resourceTypeId').value = '';
        document.querySelectorAll('#purposesContainer .purpose-checkbox').forEach(cb => cb.checked = false);
    });

    document.getElementById('saveResourceTypeBtn').addEventListener('click', async function() {
        const form = document.getElementById('resourceTypeForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const purposes = Array.from(document.querySelectorAll('#purposesContainer .purpose-checkbox:checked'))
            .map(cb => cb.value);

        if (purposes.length === 0) {
            alert('Please select at least one purpose');
            return;
        }

        const formData = {
            name: document.getElementById('rtName').value,
            agency_id: document.getElementById('rtAgency').value,
            purposes: purposes
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
                alert('Error: ' + (error.message || 'Failed to save resource type'));
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('addResourceTypeModal')).hide();
            location.reload();
        } catch (error) {
            console.error('Error saving resource type:', error);
            alert('Error saving resource type');
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

            document.querySelectorAll('#editPurposesContainer .edit-purpose-checkbox').forEach(cb => {
                cb.checked = rt.purpose_ids.includes(parseInt(cb.value));
            });

            bootstrap.Modal.getOrCreateInstance(document.getElementById('editResourceTypeModal')).show();
        } catch (error) {
            console.error('Error loading resource type:', error);
            alert('Error loading resource type data');
        }
    }

    document.getElementById('saveEditResourceTypeBtn').addEventListener('click', async function() {
        const form = document.getElementById('editResourceTypeForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const resourceTypeId = document.getElementById('editResourceTypeId').value;
        const purposes = Array.from(document.querySelectorAll('#editPurposesContainer .edit-purpose-checkbox:checked'))
            .map(cb => cb.value);

        if (purposes.length === 0) {
            alert('Please select at least one purpose');
            return;
        }

        const formData = {
            name: document.getElementById('editRtName').value,
            agency_id: document.getElementById('editRtAgency').value,
            purposes: purposes
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
                alert('Error: ' + (error.message || 'Failed to update resource type'));
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('editResourceTypeModal')).hide();
            location.reload();
        } catch (error) {
            console.error('Error updating resource type:', error);
            alert('Error updating resource type');
        }
    });

    document.querySelectorAll('.delete-resource-type-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const resourceTypeId = this.dataset.resourceTypeId;
            const resourceTypeName = this.dataset.resourceTypeName;

            if (confirm(`Delete resource type "${resourceTypeName}"?\n\nThis action cannot be undone.`)) {
                deleteResourceType(resourceTypeId);
            }
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
            location.reload();
        } catch (error) {
            console.error('Error deleting resource type:', error);
            alert('Error deleting resource type');
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

                alert('Error: ' + (details || error.message || 'Failed to save purpose'));
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('addPurposeModal')).hide();
            location.reload();
        } catch (error) {
            console.error('Error saving purpose:', error);
            alert('Error saving purpose');
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

                alert('Error: ' + (details || error.message || 'Failed to update purpose'));
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('editPurposeModal')).hide();
            location.reload();
        } catch (error) {
            console.error('Error updating purpose:', error);
            alert('Error updating purpose');
        }
    });

    // Delete Purpose
    document.querySelectorAll('.delete-purpose-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const purposeId = this.dataset.purposeId;
            const purposeName = this.dataset.purposeName;

            if (confirm(`Delete purpose "${purposeName}"?\n\nThis action cannot be undone.`)) {
                deletePurpose(purposeId);
            }
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
            location.reload();
        } catch (error) {
            console.error('Error deleting purpose:', error);
            alert('Error deleting purpose');
        }
    }
});
</script>
@endpush

@endsection

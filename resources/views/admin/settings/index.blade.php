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

    @php $activeTab = $activeTab ?? 'agencies'; @endphp

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" role="tablist" id="settingsTabs">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'agencies' ? 'active' : '' }}" id="agencies-tab" data-bs-toggle="tab" data-bs-target="#agencies-content" type="button" role="tab" aria-controls="agencies-content" aria-selected="{{ $activeTab === 'agencies' ? 'true' : 'false' }}">
                <i class="bi bi-building"></i> Agencies
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'resource-types' ? 'active' : '' }}" id="resource-types-tab" data-bs-toggle="tab" data-bs-target="#resource-types-content" type="button" role="tab" aria-controls="resource-types-content" aria-selected="{{ $activeTab === 'resource-types' ? 'true' : 'false' }}">
                <i class="bi bi-box"></i> Resource Types and Assistance Purposes
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'form-fields' ? 'active' : '' }}" id="form-fields-tab" data-bs-toggle="tab" data-bs-target="#form-fields-content" type="button" role="tab" aria-controls="form-fields-content" aria-selected="{{ $activeTab === 'form-fields' ? 'true' : 'false' }}">
                <i class="bi bi-file-earmark-text"></i> Form Fields
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="settingsContent">

        <!-- ========== AGENCIES TAB ========== -->
        <div class="tab-pane fade {{ $activeTab === 'agencies' ? 'show active' : '' }}" id="agencies-content" role="tabpanel" aria-labelledby="agencies-tab">
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-building"></i> Agencies Management
                        </h5>
                        <div class="d-flex align-items-center gap-3">
                        </div>
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
                                                {{ $agency->formFields()->where('is_active', true)->whereNotIn('field_name', \App\Support\BeneficiaryCoreFields::reservedAgencyFormFieldNames())->count() }} fields
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
                <div class="card-footer bg-white py-3 border-top-0">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <div class="text-muted small order-2 order-md-1">
                            @if($agencies->total() > 0)
                                Showing {{ number_format($agencies->firstItem()) }} to {{ number_format($agencies->lastItem()) }} of {{ number_format($agencies->total()) }} agencies
                            @endif
                        </div>
                        @if($agencies->hasPages())
                            <div class="pagination-container order-1 order-md-2">
                                {{ $agencies->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== RESOURCE TYPES AND ASSISTANCE PURPOSES TAB ========== -->
        <div class="tab-pane fade {{ $activeTab === 'resource-types' ? 'show active' : '' }}" id="resource-types-content" role="tabpanel" aria-labelledby="resource-types-tab">
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
                                <div class="d-flex align-items-center gap-3">
                                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addResourceTypeModal">
                                        <i class="bi bi-plus-circle me-1"></i> Create Resource Type
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0" id="resourceTypesTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Unit</th>
                                            <th>Category</th>
                                            <th>Agency</th>
                                            <th class="text-center" style="width: 120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($resourceTypes as $resourceType)
                                            <tr data-resource-type-id="{{ $resourceType->id }}">
                                                <td><strong>{{ $resourceType->name }}</strong></td>
                                                <td><small>{{ $resourceType->unit ?: 'N/A' }}</small></td>
                                                <td>
                                                    @if($resourceType->unit === 'PHP')
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-cash-stack me-1"></i> Financial
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">
                                                            <i class="bi bi-box-seam me-1"></i> Physical
                                                        </span>
                                                    @endif
                                                </td>
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
                        <div class="card-footer bg-white py-3 border-top-0">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                                <div class="text-muted small order-2 order-md-1">
                                    @if($resourceTypes->total() > 0)
                                        Showing {{ number_format($resourceTypes->firstItem()) }} to {{ number_format($resourceTypes->lastItem()) }} of {{ number_format($resourceTypes->total()) }} resource types
                                    @endif
                                </div>
                                @if($resourceTypes->hasPages())
                                    <div class="pagination-container order-1 order-md-2">
                                        {{ $resourceTypes->links() }}
                                    </div>
                                @endif
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
                                <div class="d-flex align-items-center gap-3">
                                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addPurposeModal">
                                        <i class="bi bi-plus-circle me-1"></i> Create Purpose
                                    </button>
                                </div>
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
                        <div class="card-footer bg-white py-3 border-top-0">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                                <div class="text-muted small order-2 order-md-1">
                                    @if($purposes->total() > 0)
                                        Showing {{ number_format($purposes->firstItem()) }} to {{ number_format($purposes->lastItem()) }} of {{ number_format($purposes->total()) }} purposes
                                    @endif
                                </div>
                                @if($purposes->hasPages())
                                    <div class="pagination-container order-1 order-md-2">
                                        {{ $purposes->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>

        <!-- ========== FORM FIELDS TAB ========== -->
        <div class="tab-pane fade {{ $activeTab === 'form-fields' ? 'show active' : '' }}" id="form-fields-content" role="tabpanel" aria-labelledby="form-fields-tab">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-info border-0 shadow-sm mb-0">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                            <div>
                                <h6 class="mb-1 fw-bold">Manage Form Field Sections</h6>
                                <p class="mb-0 small opacity-75">
                                    Fields are organized into three main categories: <strong>Global Fields</strong> (for general information), 
                                    <strong>Classification Fields</strong> (specific to Farmer, Fisherfolk, or DAR), and 
                                    <strong>Agency Fields</strong> (managed in the Agencies tab).
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION: Global Form Fields (Generals) -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-globe2 text-primary me-2"></i> Global Form Fields
                        </h5>
                        <small class="text-muted">Fields and options that apply to all beneficiaries (General Information)</small>
                    </div>
                    <button class="btn btn-primary btn-sm px-3" onclick="openAddGlobalFieldModal('{{ \App\Models\FormFieldOption::PLACEMENT_PERSONAL_INFORMATION }}', 'Global Form Field')">
                        <i class="bi bi-plus-lg me-1"></i> Add Global Field
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 25%;">Field Group</th>
                                    <th style="width: 15%;">Type</th>
                                    <th style="width: 35%;">Options / Stored Value</th>
                                    <th style="width: 10%;">Status</th>
                                    <th style="width: 15%;" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($globalFormFields as $group => $options)
                                    @php $first = $options->first(); @endphp
                                    <tr data-group="{{ $group }}">
                                        <td class="fw-bold text-dark">{{ ucfirst(str_replace('_', ' ', $group)) }}</td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary border">
                                                {{ \App\Models\FormFieldOption::fieldTypeLabel($first->field_type) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if(in_array($first->field_type, \App\Models\FormFieldOption::optionBasedFieldTypes()))
                                                @php $activeOptions = $options->where('is_active', true); @endphp
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($activeOptions->take(3) as $opt)
                                                        <span class="badge bg-light text-dark border">{{ $opt->label }}</span>
                                                    @endforeach
                                                    @if($activeOptions->count() > 3)
                                                        <span class="badge bg-light text-muted border">+{{ $activeOptions->count() - 3 }} more</span>
                                                    @endif
                                                    <button class="btn btn-link btn-sm p-0 ms-1 global-options-toggle" 
                                                            data-bs-toggle="collapse" 
                                                            data-bs-target="#collapse-{{ $group }}" 
                                                            title="View all options">
                                                        <small><i class="bi bi-chevron-down"></i> <span class="toggle-label">View</span></small>
                                                    </button>
                                                </div>
                                            @else
                                                <small class="text-muted font-monospace">{{ $first->value }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($first->is_active)
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-secondary add-global-option-btn" 
                                                        title="Add option to group"
                                                        data-field-group="{{ $group }}"
                                                        data-field-type="{{ $first->field_type }}"
                                                        data-placement="{{ $first->placement_section }}"
                                                        data-required="{{ $first->is_required ? '1' : '0' }}">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-primary edit-global-field-btn"
                                                        title="Edit field settings"
                                                        data-field-id="{{ $first->id }}"
                                                        data-field-group="{{ $group }}"
                                                        data-field-type="{{ $first->field_type }}"
                                                        data-label="{{ $first->label }}"
                                                        data-value="{{ $first->value }}"
                                                        data-placement="{{ $first->placement_section }}"
                                                        data-sort-order="{{ $first->sort_order }}"
                                                        data-required="{{ $first->is_required ? '1' : '0' }}"
                                                        data-active="{{ $first->is_active ? '1' : '0' }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @if(in_array($first->field_type, \App\Models\FormFieldOption::optionBasedFieldTypes()))
                                        <tr class="collapse-row">
                                            <td colspan="5" class="p-0 border-0">
                                                <div class="collapse bg-light-subtle" id="collapse-{{ $group }}">
                                                    <div class="p-3">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <small class="fw-bold text-uppercase text-muted">Field Options List</small>
                                                            <hr class="flex-grow-1 ms-2 mb-0 opacity-25">
                                                        </div>
                                                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-2">
                                                            @foreach($options as $opt)
                                                                <div class="col">
                                                                    <div class="d-flex justify-content-between align-items-center p-2 rounded bg-white border border-light-subtle shadow-xs">
                                                                        <div>
                                                                            <span class="fw-medium {{ $opt->is_active ? '' : 'text-decoration-line-through text-muted' }}">
                                                                                {{ $opt->label }}
                                                                            </span>
                                                                            <div class="small text-muted font-monospace opacity-75">{{ $opt->value }}</div>
                                                                        </div>
                                                                        <div class="btn-group btn-group-xs ms-2">
                                                                            <button type="button" class="btn btn-link text-primary p-0 hvr-grow edit-global-field-btn"
                                                                                    data-field-id="{{ $opt->id }}"
                                                                                    data-field-group="{{ $group }}"
                                                                                    data-field-type="{{ $first->field_type }}"
                                                                                    data-label="{{ $opt->label }}"
                                                                                    data-value="{{ $opt->value }}"
                                                                                    data-placement="{{ $opt->placement_section }}"
                                                                                    data-sort-order="{{ $opt->sort_order }}"
                                                                                    data-required="{{ $first->is_required ? '1' : '0' }}"
                                                                                    data-active="{{ $opt->is_active ? '1' : '0' }}">
                                                                                <i class="bi bi-pencil-square"></i>
                                                                            </button>
                                                                            <button type="button" class="btn btn-link text-danger p-0 ms-2 hvr-grow delete-global-field-btn"
                                                                                    data-field-id="{{ $opt->id }}"
                                                                                    data-field-label="{{ $opt->label }}">
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
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <i class="bi bi-journal-x fs-1 d-block mb-2 opacity-25"></i>
                                            No global form fields defined yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- SECTION: Classification Specific Fields -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-diagram-3 text-info me-2"></i> Classification Specific Fields
                    </h5>
                    <small class="text-muted">Core schema fields and custom global fields grouped by beneficiary classification</small>
                </div>
                <div class="card-body p-0">
                    @php
                        $placements = [
                            'Farmer' => \App\Models\FormFieldOption::PLACEMENT_FARMER_INFORMATION,
                            'Fisherfolk' => \App\Models\FormFieldOption::PLACEMENT_FISHERFOLK_INFORMATION
                        ];
                    @endphp

                    @foreach(['Farmer', 'Fisherfolk'] as $classification)
                        @php 
                            $placementKey = $placements[$classification];
                            $coreFields = $classificationCoreFields->get($classification, collect());
                            $customFields = $classificationCustomFields->get($placementKey, collect())->groupBy('field_group');
                        @endphp
                        
                        <div class="classification-section {{ !$loop->first ? 'border-top' : '' }}">
                            <div class="px-4 py-3 bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">
                                    <span class="badge {{ $classification === 'Farmer' ? 'bg-primary' : ($classification === 'Fisherfolk' ? 'bg-info text-dark' : 'bg-warning text-dark') }} me-2">
                                        {{ $classification }}
                                    </span>
                                    Fields
                                </h6>
                                <button class="btn btn-outline-primary btn-xs" onclick="openAddGlobalFieldModal('{{ $placementKey }}', '{{ $classification }}')">
                                    <i class="bi bi-plus-lg me-1"></i> Add {{ $classification }} Field
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 20%;" class="ps-4">Field Name / Group</th>
                                            <th style="width: 15%;">Source</th>
                                            <th style="width: 15%;">Type</th>
                                            <th style="width: 30%;">Required Status / Options</th>
                                            <th style="width: 20%;" class="text-end pe-4">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Core Fields -->
                                        @foreach($coreFields as $core)
                                            <tr class="table-white">
                                                <td class="ps-4">
                                                    <div class="fw-bold text-dark">{{ $core['label'] }}</div>
                                                    <small class="text-muted font-monospace">{{ $core['field_name'] }}</small>
                                                </td>
                                                <td><span class="badge bg-secondary-subtle text-secondary border">System Core</span></td>
                                                <td><span class="text-muted small">Text / Dropdown</span></td>
                                                <td>
                                                    <span class="badge {{ $core['is_required'] ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary' }} border">
                                                        {{ $core['is_required'] ? 'Required' : 'Optional' }}
                                                    </span>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-secondary btn-xs edit-classification-core-btn"
                                                                data-field-name="{{ $core['field_name'] }}"
                                                                data-label="{{ $core['label'] }}"
                                                                data-required="{{ $core['is_required'] ? '1' : '0' }}"
                                                                data-placement="{{ Str::title(str_replace('_', ' ', $core['placement_section'])) }}">
                                                            <i class="bi bi-gear-fill me-1"></i> Configure
                                                        </button>
                                                        <button type="button" class="btn btn-outline-primary btn-xs toggle-core-required-btn"
                                                                data-field-name="{{ $core['field_name'] }}"
                                                                data-next-required="{{ $core['is_required'] ? '0' : '1' }}">
                                                            {{ $core['is_required'] ? 'Set Optional' : 'Set Required' }}
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach

                                        <!-- Custom Classification Fields -->
                                        @foreach($customFields as $group => $options)
                                            @php $first = $options->first(); @endphp
                                            <tr class="table-white border-top">
                                                <td class="ps-4">
                                                    <div class="fw-bold text-info">{{ ucfirst(str_replace('_', ' ', $group)) }}</div>
                                                    <small class="text-muted font-monospace">{{ $group }}</small>
                                                </td>
                                                <td><span class="badge bg-info-subtle text-info border">User Custom</span></td>
                                                <td>
                                                    <small class="badge bg-light text-muted border">
                                                        {{ \App\Models\FormFieldOption::fieldTypeLabel($first->field_type) }}
                                                    </small>
                                                </td>
                                                <td>
                                                    @if(in_array($first->field_type, \App\Models\FormFieldOption::optionBasedFieldTypes()))
                                                        <small class="text-muted">{{ $options->where('is_active', true)->count() }} options</small>
                                                        <button class="btn btn-link btn-xs p-0 ms-1 global-options-toggle" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $group }}">
                                                            <i class="bi bi-chevron-down"></i>
                                                        </button>
                                                    @else
                                                        <span class="badge {{ $first->is_required ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary' }} border">
                                                            {{ $first->is_required ? 'Required' : 'Optional' }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-end pe-4">
                                                    <div class="btn-group btn-group-xs">
                                                        <button type="button" class="btn btn-outline-secondary add-global-option-btn" 
                                                                data-field-group="{{ $group }}"
                                                                data-field-type="{{ $first->field_type }}"
                                                                data-placement="{{ $first->placement_section }}"
                                                                data-required="{{ $first->is_required ? '1' : '0' }}">
                                                            <i class="bi bi-plus"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-primary edit-global-field-btn"
                                                                data-field-id="{{ $first->id }}"
                                                                data-field-group="{{ $group }}"
                                                                data-field-type="{{ $first->field_type }}"
                                                                data-label="{{ $first->label }}"
                                                                data-value="{{ $first->value }}"
                                                                data-placement="{{ $first->placement_section }}"
                                                                data-sort-order="{{ $first->sort_order }}"
                                                                data-required="{{ $first->is_required ? '1' : '0' }}"
                                                                data-active="{{ $first->is_active ? '1' : '0' }}">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            @if(in_array($first->field_type, \App\Models\FormFieldOption::optionBasedFieldTypes()))
                                                <tr class="collapse-row">
                                                    <td colspan="5" class="p-0 border-0">
                                                        <div class="collapse bg-light-subtle" id="collapse-{{ $group }}">
                                                            <div class="px-5 py-3 border-start border-4 border-info">
                                                                <div class="row row-cols-1 row-cols-md-2 g-2">
                                                                    @foreach($options as $opt)
                                                                        <div class="col">
                                                                            <div class="d-flex justify-content-between align-items-center p-2 rounded bg-white border border-light-subtle">
                                                                                <span class="small fw-medium">{{ $opt->label }}</span>
                                                                                <div class="btn-group btn-group-xs">
                                                                                    <button type="button" class="btn btn-link text-primary p-0 edit-global-field-btn"
                                                                                            data-field-id="{{ $opt->id }}"
                                                                                            data-field-group="{{ $group }}"
                                                                                            data-field-type="{{ $first->field_type }}"
                                                                                            data-label="{{ $opt->label }}"
                                                                                            data-value="{{ $opt->value }}"
                                                                                            data-placement="{{ $opt->placement_section }}"
                                                                                            data-sort-order="{{ $opt->sort_order }}"
                                                                                            data-required="{{ $first->is_required ? '1' : '0' }}"
                                                                                            data-active="{{ $opt->is_active ? '1' : '0' }}">
                                                                                        <i class="bi bi-pencil"></i>
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
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
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
                            <option value="textarea">Text Area</option>
                            <option value="number">Number</option>
                            <option value="decimal">Decimal</option>
                            <option value="date">Date</option>
                            <option value="datetime">Date & Time</option>
                            <option value="dropdown">Dropdown</option>
                            <option value="checkbox">Checkboxes</option>
                            <option value="radio">Radio Buttons</option>
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

                    <div class="mb-3">
                        <label for="fieldSection" class="form-label">Form Section</label>
                        <select class="form-select" id="fieldSection" name="form_section">
                            <option value="">General Information</option>
                            <option value="farmer_information">Farmer Information</option>
                            <option value="fisherfolk_information">Fisherfolk Information</option>
                            <option value="dar_information">DAR/ARB Information</option>
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

                    <div class="mb-3" id="globalFieldLabelWrapper">
                        <label for="globalFieldLabel" class="form-label" id="globalFieldLabelLabel">Option Label <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="globalFieldLabel" required placeholder="e.g., Small Farmer">
                    </div>

                    <div class="mb-3 d-none" id="globalFieldOptionsWrapper">
                        <label for="globalFieldOptionsInput" class="form-label">Options (one per line)</label>
                        <textarea class="form-control" id="globalFieldOptionsInput" rows="5" placeholder="Label|value"></textarea>
                        <small class="text-muted">Use this to add multiple options at once. Format: Label|value</small>
                    </div>

                    <div class="mb-3" id="globalFieldValueWrapper">
                        <label for="globalFieldValue" class="form-label" id="globalFieldValueLabel">Option Value <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="globalFieldValue" required placeholder="e.g., small_farmer">
                        <small class="text-muted" id="globalFieldValueHelp">Stored key value for this option.</small>
                    </div>

                    <div class="mb-3" id="globalFieldPlacementWrapper">
                        <label for="globalFieldPlacement" class="form-label">Display Section (Global Fields Only) <span class="text-danger">*</span></label>
                        <select class="form-select" id="globalFieldPlacement" required>
                            <option value="personal_information">Agency &amp; Personal Information</option>
                            <option value="farmer_information">DA/RSBSA Information (Farmer)</option>
                            <option value="fisherfolk_information">BFAR/FishR Information (Fisherfolk)</option>
                            <option value="dar_information">DAR/ARB Information</option>
                        </select>
                        <div id="globalFieldPlacementDisplay" class="form-control bg-light d-none" readonly></div>
                    </div>

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
                        <label class="form-label">Resource Category <span class="text-danger">*</span></label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="category_toggle" id="rtCategoryPhysical" value="physical" checked>
                            <label class="btn btn-outline-secondary" for="rtCategoryPhysical">
                                <i class="bi bi-box-seam me-1"></i> Physical Resource
                            </label>
                            
                            <input type="radio" class="btn-check" name="category_toggle" id="rtCategoryFinancial" value="financial">
                            <label class="btn btn-outline-success" for="rtCategoryFinancial">
                                <i class="bi bi-cash-stack me-1"></i> Financial Assistance
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="rtUnit" class="form-label">Unit Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="rtUnit" name="unit" required>
                            <option value="" selected disabled>Select unit type...</option>
                            @foreach(($resourceUnitOptions ?? []) as $unitValue => $unitLabel)
                                <option value="{{ $unitValue }}">{{ $unitLabel }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted" id="rtUnitHelp">Use PHP for cash assistance (amount-based).</small>
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
                        <label class="form-label">Resource Category <span class="text-danger">*</span></label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="edit_category_toggle" id="editRtCategoryPhysical" value="physical">
                            <label class="btn btn-outline-secondary" for="editRtCategoryPhysical">
                                <i class="bi bi-box-seam me-1"></i> Physical Resource
                            </label>
                            
                            <input type="radio" class="btn-check" name="edit_category_toggle" id="editRtCategoryFinancial" value="financial">
                            <label class="btn btn-outline-success" for="editRtCategoryFinancial">
                                <i class="bi bi-cash-stack me-1"></i> Financial Assistance
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="editRtUnit" class="form-label">Unit Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="editRtUnit" name="unit" required>
                            <option value="" selected disabled>Select unit type...</option>
                            @foreach(($resourceUnitOptions ?? []) as $unitValue => $unitLabel)
                                <option value="{{ $unitValue }}">{{ $unitLabel }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted" id="editRtUnitHelp">Use PHP for cash assistance (amount-based).</small>
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
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Helper function for button loading state
    function setButtonLoading(btn, isLoading, originalText = '') {
        if (!btn) return;
        if (isLoading) {
            btn.dataset.originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Processing...`;
        } else {
            btn.disabled = false;
            btn.innerHTML = originalText || btn.dataset.originalText || 'Save';
        }
    }

    const purposeCategoryOptionsElement = document.getElementById('purposeCategoryOptionsData');
    const purposeCategoryOptions = purposeCategoryOptionsElement
        ? JSON.parse(purposeCategoryOptionsElement.dataset.options || '{}')
        : {};

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
                alert(error.message || 'Error updating classification core field.');
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

            setButtonLoading(saveClassificationCoreFieldBtn, true);

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
                setButtonLoading(saveClassificationCoreFieldBtn, false);
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
        const btn = this;
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

        setButtonLoading(btn, true);

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
                setButtonLoading(btn, false);
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('addAgencyModal')).hide();
            reloadWithCurrentSettingsTab();
        } catch (error) {
            console.error('Error saving agency:', error);
            alert('Error saving agency');
            setButtonLoading(btn, false);
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
        const btn = this;
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

        setButtonLoading(btn, true);

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
                setButtonLoading(btn, false);
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('editAgencyModal')).hide();
            reloadWithCurrentSettingsTab();
        } catch (error) {
            console.error('Error updating agency:', error);
            alert('Error updating agency');
            setButtonLoading(btn, false);
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
            alert('Error deactivating agency');
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
            alert('Error activating agency');
        }
    }

    // Manage Form Fields
    const agencyFieldTypeInput = document.getElementById('fieldType');
    const agencyFieldOptionsWrapper = document.getElementById('fieldOptionsWrapper');
    const agencyFieldOptionsInput = document.getElementById('fieldOptionsInput');

    function isAgencyOptionType(fieldType) {
        return ['dropdown', 'checkbox', 'radio'].includes(String(fieldType || '').toLowerCase());
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
        const btn = this;
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

        setButtonLoading(btn, true);

        try {
            let url = `/admin/settings/agencies/${agencyId}/form-fields`;
            let method = 'POST';

            if (fieldId) {
                url += `/${fieldId}`;
                method = 'PUT';
            }

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(formData)
            });

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
                setButtonLoading(btn, false);
                return;
            }

            const result = await response.json();
            bootstrap.Modal.getInstance(document.getElementById('addFieldModal')).hide();
            loadFormFields(agencyId);
        } catch (error) {
            console.error('Error saving field:', error);
            alert('Error saving field: ' + error.message);
            setButtonLoading(btn, false);
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
    const globalFieldLabelWrapper = document.getElementById('globalFieldLabelWrapper');
    const globalFieldOptionsWrapper = document.getElementById('globalFieldOptionsWrapper');
    const globalFieldOptionsInput = document.getElementById('globalFieldOptionsInput');
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
        const valueLabel = document.getElementById('globalFieldValueLabel');
        const valueWrapper = document.getElementById('globalFieldValueWrapper');
        const labelLabel = document.getElementById('globalFieldLabelLabel');
        const isEdit = globalFieldModal.dataset.mode === 'edit';
        const isAddOption = globalFieldModal.dataset.mode === 'add_option';

        globalFieldLabelLabel.innerHTML = optionBased
            ? 'Option Label <span class="text-danger">*</span>'
            : 'Field Label <span class="text-danger">*</span>';

        if (optionBased && !isEdit && !isAddOption) {
            globalFieldOptionsWrapper.classList.remove('d-none');
            globalFieldOptionsInput.disabled = false;
            globalFieldLabelWrapper.classList.add('d-none');
            globalFieldValueWrapper.classList.add('d-none');
            globalFieldLabelInput.required = false;
            globalFieldValueInput.required = false;
        } else {
            globalFieldOptionsWrapper.classList.add('d-none');
            globalFieldOptionsInput.disabled = true;
            globalFieldLabelWrapper.classList.remove('d-none');
            globalFieldValueWrapper.classList.remove('d-none');
            globalFieldLabelInput.required = true;
            
            globalFieldValueInput.disabled = !optionBased;
            globalFieldValueInput.required = optionBased;
            globalFieldValueLabel.innerHTML = optionBased
                ? 'Option Value <span class="text-danger">*</span>'
                : 'Stored Value';
            globalFieldValueHelp.textContent = optionBased
                ? 'Stored key value for this option.'
                : 'Single-value field types automatically use the field group as the stored key.';

            if (!optionBased) {
                globalFieldValueInput.value = normalizeKey(globalFieldGroupInput.value);
            }
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
        globalFieldGroupInput.readOnly = isEdit;
        globalFieldGroupHelp.textContent = isEdit
            ? 'Field group is locked while editing an existing option.'
            : 'Lowercase, alphanumeric, and underscores only.';
    }

    function resetGlobalFieldForm() {
        globalFieldForm.reset();
        globalFieldIdInput.value = '';
        globalFieldTypeInput.value = 'dropdown';
        globalFieldTypeInput.disabled = false;
        globalFieldGroupInput.readOnly = false;
        globalFieldActiveInput.checked = true;
        globalFieldOptionsInput.value = '';
        applyGlobalFieldTypeState(globalFieldTypeInput.value);
        clearGlobalFieldErrors();
        setGlobalFieldModalMode('create');
    }

    window.openAddGlobalFieldModal = function(placement = 'personal_information', label = null) {
        resetGlobalFieldForm();
        setGlobalFieldModalMode('create');

        if (placement) {
            globalFieldPlacementInput.value = placement;
            
            // If classification label is provided, show read-only section and hide select
            if (label) {
                const placementDisplay = document.getElementById('globalFieldPlacementDisplay');
                
                globalFieldPlacementInput.classList.add('d-none');
                placementDisplay.textContent = label + ' Section';
                placementDisplay.classList.remove('d-none');
                
                globalFieldModalTitle.textContent = 'Add ' + label + ' Specific Field';
            }
        }
        
        bootstrap.Modal.getOrCreateInstance(globalFieldModal).show();
    };

    globalFieldModal.addEventListener('show.bs.modal', function() {
        // Mode is handled in open-button click or openAddGlobalFieldModal call
    });

    globalFieldLabelInput.addEventListener('blur', function() {
        if (isOptionBasedGlobalType(globalFieldTypeInput.value) && !globalFieldValueInput.value) {
            globalFieldValueInput.value = normalizeKey(this.value);
        }
    });

    globalFieldModal.addEventListener('hidden.bs.modal', function() {
        resetGlobalFieldForm();
        
        // Reset display state
        const placementDisplay = document.getElementById('globalFieldPlacementDisplay');
        globalFieldPlacementInput.classList.remove('d-none');
        placementDisplay.classList.add('d-none');
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

            // When editing, hide the section selection as it's group-level
            const placementDisplay = document.getElementById('globalFieldPlacementDisplay');
            globalFieldPlacementInput.classList.add('d-none');
            const sections = {
                'personal_information': 'Global Form Field',
                'farmer_information': 'Farmer Specific',
                'fisherfolk_information': 'Fisherfolk Specific',
                'dar_information': 'DAR/ARB Information'
            };
            placementDisplay.textContent = sections[globalFieldPlacementInput.value] || globalFieldPlacementInput.value;
            placementDisplay.classList.remove('d-none');

            bootstrap.Modal.getOrCreateInstance(globalFieldModal).show();
        });
    });

    document.querySelectorAll('.add-global-option-btn').forEach((btn) => {
        btn.addEventListener('click', function() {
            setGlobalFieldModalMode('add_option');
            resetGlobalFieldForm();

            globalFieldGroupInput.value = this.dataset.fieldGroup || '';
            globalFieldTypeInput.value = this.dataset.fieldType || 'dropdown';
            globalFieldPlacementInput.value = this.dataset.placement || 'personal_information';
            globalFieldRequiredInput.checked = this.dataset.required === '1';
            
            applyGlobalFieldTypeState(globalFieldTypeInput.value);
            globalFieldGroupInput.readOnly = true;
            globalFieldTypeInput.disabled = true;
            
            const placementDisplay = document.getElementById('globalFieldPlacementDisplay');
            globalFieldPlacementInput.classList.add('d-none');
            placementDisplay.textContent = 'Group: ' + globalFieldGroupInput.value;
            placementDisplay.classList.remove('d-none');
            
            globalFieldModalTitle.textContent = 'Add Alternative Option';

            bootstrap.Modal.getOrCreateInstance(globalFieldModal).show();
        });
    });

    document.querySelectorAll('.delete-global-field-btn').forEach((btn) => {
        btn.addEventListener('click', async function() {
            const fieldId = this.dataset.fieldId;
            const fieldLabel = this.dataset.fieldLabel || 'this option';

            if (!confirm(`Delete "${fieldLabel}"?\n\nThis action cannot be undone.`)) {
                return;
            }

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

                reloadWithCurrentSettingsTab();
            } catch (error) {
                alert(error.message || 'Error deleting form field.');
            }
        });
    });

    globalFieldSaveBtn.addEventListener('click', async function() {
        const btn = this;
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
        const optionValue = optionBased ? normalizeKey(globalFieldValueInput.value) : fieldGroup;
        const sortOrderRaw = globalFieldSortOrderInput.value.trim();

        globalFieldGroupInput.value = fieldGroup;
        globalFieldValueInput.value = optionValue;

        if (!fieldGroup || (optionBased && !optionValue)) {
            globalFieldErrors.textContent = optionBased
                ? 'Field group and option value must contain letters or numbers.'
                : 'Field group must contain letters or numbers.';
            globalFieldErrors.classList.remove('d-none');
            return;
        }

        const payload = {
            field_type: fieldType,
            placement_section: globalFieldPlacementInput.value,
            label: globalFieldLabelInput.value,
            value: optionValue,
            sort_order: sortOrderRaw === '' ? null : parseInt(sortOrderRaw, 10),
            is_required: globalFieldRequiredInput.checked,
            is_active: globalFieldActiveInput.checked
        };

        if (optionBased && globalFieldModal.dataset.mode === 'create' && globalFieldOptionsInput.value.trim() !== '') {
            payload.options = serializeAgencyFieldOptions(globalFieldOptionsInput.value);
            if (!payload.label) payload.label = 'Group Base';
        }

        if (!isEditMode) {
            payload.field_group = fieldGroup;
        }

        setButtonLoading(btn, true);

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
                setButtonLoading(btn, false);
                return;
            }

            bootstrap.Modal.getInstance(globalFieldModal).hide();
            reloadWithCurrentSettingsTab();
        } catch (error) {
            globalFieldErrors.textContent = `Error ${isEditMode ? 'updating' : 'saving'} form field.`;
            globalFieldErrors.classList.remove('d-none');
            setButtonLoading(btn, false);
        }
    });

    // ========== RESOURCE TYPE MANAGEMENT ==========
    function filterUnitsByCategory(selectElement, helpTextElement, category) {
        if (!selectElement) return;
        
        const options = selectElement.options;
        for (let i = 0; i < options.length; i++) {
            const option = options[i];
            if (option.value === "") continue;
            
            if (category === 'financial') {
                // Financial: ONLY PHP
                if (option.value === 'PHP') {
                    option.hidden = false;
                    option.disabled = false;
                } else {
                    option.hidden = true;
                    option.disabled = true;
                }
            } else {
                // Physical: EVERYTHING EXCEPT PHP
                if (option.value === 'PHP') {
                    option.hidden = true;
                    option.disabled = true;
                } else {
                    option.hidden = false;
                    option.disabled = false;
                }
            }
        }
        
        if (category === 'financial') {
            selectElement.value = 'PHP';
            if (helpTextElement) helpTextElement.innerHTML = '<i class="bi bi-info-circle me-1"></i> Financial assistance is restricted to PHP (Pesos) unit.';
        } else {
            if (selectElement.value === 'PHP') {
                selectElement.value = '';
            }
            if (helpTextElement) helpTextElement.innerHTML = '<i class="bi bi-info-circle me-1"></i> Physical resources cannot use PHP (Pesos) as a unit.';
        }
    }

    // Add listeners for category toggles
    document.querySelectorAll('input[name="category_toggle"]').forEach(radio => {
        radio.addEventListener('change', function() {
            filterUnitsByCategory(
                document.getElementById('rtUnit'),
                document.getElementById('rtUnitHelp'),
                this.value
            );
        });
    });

    document.querySelectorAll('input[name="edit_category_toggle"]').forEach(radio => {
        radio.addEventListener('change', function() {
            filterUnitsByCategory(
                document.getElementById('editRtUnit'),
                document.getElementById('editRtUnitHelp'),
                this.value
            );
        });
    });

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
        
        // Default to physical and filter
        document.getElementById('rtCategoryPhysical').checked = true;
        filterUnitsByCategory(
            document.getElementById('rtUnit'),
            document.getElementById('rtUnitHelp'),
            'physical'
        );
    });

    document.getElementById('saveResourceTypeBtn').addEventListener('click', async function() {
        const btn = this;
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

        setButtonLoading(btn, true);

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
                setButtonLoading(btn, false);
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('addResourceTypeModal')).hide();
            reloadWithCurrentSettingsTab();
        } catch (error) {
            console.error('Error saving resource type:', error);
            alert('Error saving resource type');
            setButtonLoading(btn, false);
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

            // Set category based on unit and apply filter
            if (rt.unit === 'PHP') {
                document.getElementById('editRtCategoryFinancial').checked = true;
                filterUnitsByCategory(
                    document.getElementById('editRtUnit'),
                    document.getElementById('editRtUnitHelp'),
                    'financial'
                );
            } else {
                document.getElementById('editRtCategoryPhysical').checked = true;
                filterUnitsByCategory(
                    document.getElementById('editRtUnit'),
                    document.getElementById('editRtUnitHelp'),
                    'physical'
                );
            }

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
                alert('Error: ' + (error.message || 'Failed to update resource type'));
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('editResourceTypeModal')).hide();
            reloadWithCurrentSettingsTab();
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
            reloadWithCurrentSettingsTab();
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
        const btn = this;
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

        setButtonLoading(btn, true);

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
                setButtonLoading(btn, false);
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('addPurposeModal')).hide();
            reloadWithCurrentSettingsTab();
        } catch (error) {
            console.error('Error saving purpose:', error);
            alert('Error saving purpose');
            setButtonLoading(btn, false);
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
        const btn = this;
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

        setButtonLoading(btn, true);

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
                setButtonLoading(btn, false);
                return;
            }

            bootstrap.Modal.getInstance(document.getElementById('editPurposeModal')).hide();
            reloadWithCurrentSettingsTab();
        } catch (error) {
            console.error('Error updating purpose:', error);
            alert('Error updating purpose');
            setButtonLoading(btn, false);
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
            reloadWithCurrentSettingsTab();
        } catch (error) {
            console.error('Error deleting purpose:', error);
            alert('Error deleting purpose');
        }
    }
});
</script>
@endpush

@endsection

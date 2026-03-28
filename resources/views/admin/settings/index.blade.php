@extends('layouts.app')

@section('title', 'System Settings')

@section('breadcrumb')
    <li class="breadcrumb-item active">System Settings</li>
@endsection

@section('content')
    {{-- Page Header --}}
    <div class="mb-4">
        <h1 class="h3 mb-1">System Settings</h1>
        <p class="text-muted mb-0">Manage agencies, assistance purposes, resource types, and form field options</p>
    </div>

    {{-- Bootstrap Tabs --}}
    <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="agencies-tab" data-bs-toggle="tab"
                    data-bs-target="#agencies" type="button" role="tab">
                <i class="bi bi-building me-1"></i> Agencies
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="purposes-tab" data-bs-toggle="tab"
                    data-bs-target="#purposes" type="button" role="tab">
                <i class="bi bi-list-check me-1"></i> Assistance Purposes
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="resource-types-tab" data-bs-toggle="tab"
                    data-bs-target="#resourceTypes" type="button" role="tab">
                <i class="bi bi-tags me-1"></i> Resource Types
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="program-names-tab" data-bs-toggle="tab"
                    data-bs-target="#programNames" type="button" role="tab">
                <i class="bi bi-journal-text me-1"></i> Program Names
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="form-fields-tab" data-bs-toggle="tab"
                    data-bs-target="#formFields" type="button" role="tab">
                <i class="bi bi-ui-checks me-1"></i> Form Fields
            </button>
        </li>
    </ul>

    <div class="tab-content" id="settingsTabContent">

        {{-- ══════════════════════════════════════════ --}}
        {{-- TAB 1: AGENCIES                           --}}
        {{-- ══════════════════════════════════════════ --}}
        <div class="tab-pane fade show active" id="agencies" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><i class="bi bi-building me-1"></i> Supporting Agencies</span>
                    <button class="btn btn-success btn-sm" onclick="openAgencyModal()">
                        <i class="bi bi-plus-lg me-1"></i> Add Agency
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Full Name</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="agenciesTableBody">
                                @foreach($agencies as $agency)
                                    <tr id="agency-row-{{ $agency->id }}">
                                        <td class="fw-semibold">{{ $agency->name }}</td>
                                        <td>{{ $agency->full_name }}</td>
                                        <td>
                                            <span class="badge {{ $agency->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $agency->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <button class="btn btn-sm btn-outline-warning me-1"
                                                    onclick="openAgencyModal({{ $agency->id }}, {{ Js::from($agency) }})">
                                                <i class="bi bi-pencil-square"></i> <span class="btn-action-label">Edit</span>
                                            </button>
                                            @if($agency->is_active)
                                                <button class="btn btn-sm btn-outline-danger"
                                                        onclick="deactivateAgency({{ $agency->id }}, '{{ addslashes($agency->name) }}')">
                                                    <i class="bi bi-x-circle"></i> <span class="btn-action-label">Deactivate</span>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════ --}}
        {{-- TAB 2: ASSISTANCE PURPOSES                --}}
        {{-- ══════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="purposes" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><i class="bi bi-list-check me-1"></i> Assistance Purposes</span>
                    <button class="btn btn-success btn-sm" onclick="openPurposeModal()">
                        <i class="bi bi-plus-lg me-1"></i> Add Purpose
                    </button>
                </div>
                <div class="card-body p-0" id="purposesContainer">
                    @foreach($purposes as $category => $items)
                        <div class="px-3 pt-3 pb-1">
                            <h6 class="text-uppercase text-muted small fw-bold mb-0">
                                <i class="bi bi-tag me-1"></i> {{ ucfirst($category) }}
                            </h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $purpose)
                                        <tr id="purpose-row-{{ $purpose->id }}">
                                            <td class="fw-semibold">{{ $purpose->name }}</td>
                                            <td>
                                                <span class="badge {{ $purpose->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $purpose->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="text-end text-nowrap">
                                                <button class="btn btn-sm btn-outline-warning me-1"
                                                        onclick="openPurposeModal({{ $purpose->id }}, {{ Js::from($purpose) }})">
                                                    <i class="bi bi-pencil-square"></i> <span class="btn-action-label">Edit</span>
                                                </button>
                                                @if($purpose->is_active)
                                                    <button class="btn btn-sm btn-outline-danger"
                                                            onclick="deactivatePurpose({{ $purpose->id }}, '{{ addslashes($purpose->name) }}')">
                                                        <i class="bi bi-x-circle"></i> <span class="btn-action-label">Deactivate</span>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach

                    @if($purposes->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            No assistance purposes found.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════ --}}
        {{-- TAB 3: RESOURCE TYPES                     --}}
        {{-- ══════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="resourceTypes" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><i class="bi bi-tags me-1"></i> Resource Types</span>
                    <button class="btn btn-success btn-sm" onclick="openResourceTypeModal()">
                        <i class="bi bi-plus-lg me-1"></i> Add Resource Type
                    </button>
                </div>
                <div class="card-body p-0" id="resourceTypesContainer">
                    @php
                        $grouped = $resourceTypes->groupBy(fn($rt) => $rt->agency?->name ?? 'Unassigned');
                    @endphp
                    @foreach($grouped as $agencyName => $items)
                        <div class="px-3 pt-3 pb-1">
                            <h6 class="text-uppercase text-muted small fw-bold mb-0">
                                <i class="bi bi-building me-1"></i> {{ $agencyName }}
                            </h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Unit</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $rt)
                                        <tr id="rt-row-{{ $rt->id }}">
                                            <td class="fw-semibold">{{ $rt->name }}</td>
                                            <td><span class="badge bg-info text-dark">{{ $rt->unit }}</span></td>
                                            <td class="text-end text-nowrap">
                                                <button class="btn btn-sm btn-outline-warning me-1"
                                                        onclick="openResourceTypeModal({{ $rt->id }}, {{ Js::from($rt) }})">
                                                    <i class="bi bi-pencil-square"></i> <span class="btn-action-label">Edit</span>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger"
                                                        onclick="deleteResourceType({{ $rt->id }}, '{{ addslashes($rt->name) }}')">
                                                    <i class="bi bi-trash"></i> <span class="btn-action-label">Delete</span>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach

                    @if($resourceTypes->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            No resource types found.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════ --}}
        {{-- TAB 4: PROGRAM NAMES                      --}}
        {{-- ══════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="programNames" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><i class="bi bi-journal-text me-1"></i> Program Names</span>
                    <button class="btn btn-success btn-sm" onclick="openProgramNameModal()">
                        <i class="bi bi-plus-lg me-1"></i> Add Program Name
                    </button>
                </div>
                <div class="card-body p-0" id="programNamesContainer">
                    @php
                        $groupedPrograms = $programNames->groupBy(fn($pn) => $pn->agency?->name ?? 'Unassigned');
                    @endphp
                    @foreach($groupedPrograms as $agencyName => $items)
                        <div class="px-3 pt-3 pb-1">
                            <h6 class="text-uppercase text-muted small fw-bold mb-0">
                                <i class="bi bi-building me-1"></i> {{ $agencyName }}
                            </h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Program Name</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $pn)
                                        <tr id="pn-row-{{ $pn->id }}">
                                            <td class="fw-semibold">{{ $pn->name }}</td>
                                            <td class="text-muted small">{{ $pn->description ?? '—' }}</td>
                                            <td>
                                                <span class="badge {{ $pn->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $pn->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="text-end text-nowrap">
                                                <button class="btn btn-sm btn-outline-warning me-1"
                                                        onclick="openProgramNameModal({{ $pn->id }}, {{ Js::from($pn) }})">
                                                    <i class="bi bi-pencil-square"></i> <span class="btn-action-label">Edit</span>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger"
                                                        onclick="deleteProgramName({{ $pn->id }}, '{{ addslashes($pn->name) }}')">
                                                    <i class="bi bi-trash"></i> <span class="btn-action-label">Delete</span>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach

                    @if($programNames->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            No program names found.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════ --}}
        {{-- TAB 5: FORM FIELDS                        --}}
        {{-- ══════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="formFields" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"><i class="bi bi-ui-checks me-1"></i> Form Field Options</span>
                    <button class="btn btn-success btn-sm" onclick="openFormFieldModal()">
                        <i class="bi bi-plus-lg me-1"></i> Add Option
                    </button>
                </div>
                <div class="card-body" id="formFieldsContainer">
                    @php
                        $fieldLabels = [
                            'civil_status'      => 'Civil Status',
                            'highest_education'  => 'Highest Education',
                            'id_type'            => 'Government ID Type',
                            'farm_type'          => 'Farm Type',
                            'farm_ownership'     => 'Farm Ownership',
                            'fisherfolk_type'    => 'Fisherfolk Type',
                            'arb_classification' => 'ARB Classification',
                            'ownership_scheme'   => 'Ownership Scheme',
                        ];
                    @endphp
                    <div class="accordion" id="formFieldsAccordion">
                        @foreach($fieldLabels as $fieldKey => $fieldLabel)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-{{ $fieldKey }}">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse-{{ $fieldKey }}">
                                        {{ $fieldLabel }}
                                        <span class="badge bg-secondary ms-2" id="count-{{ $fieldKey }}">
                                            {{ isset($formFields[$fieldKey]) ? $formFields[$fieldKey]->count() : 0 }}
                                        </span>
                                    </button>
                                </h2>
                                <div id="collapse-{{ $fieldKey }}" class="accordion-collapse collapse"
                                     data-bs-parent="#formFieldsAccordion">
                                    <div class="accordion-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 40px;"><i class="bi bi-grip-vertical text-muted"></i></th>
                                                        <th>Label</th>
                                                        <th>Value</th>
                                                        <th>Order</th>
                                                        <th>Status</th>
                                                        <th class="text-end">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="ff-tbody-{{ $fieldKey }}" data-field="{{ $fieldKey }}">
                                                    @foreach(($formFields[$fieldKey] ?? []) as $opt)
                                                        <tr id="ff-row-{{ $opt->id }}" data-id="{{ $opt->id }}">
                                                            <td class="drag-handle" style="cursor: grab;"><i class="bi bi-grip-vertical text-muted"></i></td>
                                                            <td class="fw-semibold">{{ $opt->label }}</td>
                                                            <td><code>{{ $opt->value }}</code></td>
                                                            <td>{{ $opt->sort_order }}</td>
                                                            <td>
                                                                <span class="badge {{ $opt->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                                    {{ $opt->is_active ? 'Active' : 'Inactive' }}
                                                                </span>
                                                            </td>
                                                            <td class="text-end text-nowrap">
                                                                <button class="btn btn-sm btn-outline-warning me-1"
                                                                        onclick="openFormFieldModal({{ $opt->id }}, {{ Js::from($opt) }})">
                                                                    <i class="bi bi-pencil-square"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-danger"
                                                                        onclick="deleteFormField({{ $opt->id }}, '{{ addslashes($opt->label) }}')">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- AGENCY MODAL                                   --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div class="modal fade" id="agencyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="agencyModalTitle">Add Agency</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="agencyId">
                    <div class="mb-3">
                        <label for="agencyName" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="agencyName" maxlength="100" placeholder="e.g. DA" required>
                        <div class="invalid-feedback" id="agencyNameError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="agencyFullName" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="agencyFullName" maxlength="255" placeholder="e.g. Department of Agriculture" required>
                        <div class="invalid-feedback" id="agencyFullNameError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="agencyDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="agencyDescription" rows="2" maxlength="500" placeholder="Optional description..."></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="agencyIsActive" checked>
                        <label class="form-check-label" for="agencyIsActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="agencySaveBtn" onclick="saveAgency()">
                        <i class="bi bi-check-lg me-1"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- PURPOSE MODAL                                  --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div class="modal fade" id="purposeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="purposeModalTitle">Add Purpose</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="purposeId">
                    <div class="mb-3">
                        <label for="purposeName" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="purposeName" maxlength="255" placeholder="e.g. Purchase of fishing nets" required>
                        <div class="invalid-feedback" id="purposeNameError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="purposeCategory" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="purposeCategory" required>
                            <option value="" disabled selected>Select category...</option>
                            <option value="agricultural">Agricultural</option>
                            <option value="fishery">Fishery</option>
                            <option value="livelihood">Livelihood</option>
                            <option value="medical">Medical</option>
                            <option value="emergency">Emergency</option>
                            <option value="other">Other</option>
                        </select>
                        <div class="invalid-feedback" id="purposeCategoryError"></div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="purposeIsActive" checked>
                        <label class="form-check-label" for="purposeIsActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="purposeSaveBtn" onclick="savePurpose()">
                        <i class="bi bi-check-lg me-1"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- RESOURCE TYPE MODAL                            --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div class="modal fade" id="resourceTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rtModalTitle">Add Resource Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="rtId">
                    <div class="mb-3">
                        <label for="rtName" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="rtName" maxlength="255" placeholder="e.g. Rice Seeds" required>
                        <div class="invalid-feedback" id="rtNameError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="rtUnit" class="form-label">Unit <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="rtUnit" maxlength="50" placeholder="e.g. kg, bags, PHP" required>
                        <div class="invalid-feedback" id="rtUnitError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="rtAgencyId" class="form-label">Agency <span class="text-danger">*</span></label>
                        <select class="form-select" id="rtAgencyId" required>
                            <option value="" disabled selected>Select agency...</option>
                            @foreach($agencies->where('is_active', true) as $a)
                                <option value="{{ $a->id }}">{{ $a->name }} — {{ $a->full_name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="rtAgencyIdError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="rtDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="rtDescription" rows="2" maxlength="500" placeholder="Optional description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="rtSaveBtn" onclick="saveResourceType()">
                        <i class="bi bi-check-lg me-1"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- PROGRAM NAME MODAL                             --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div class="modal fade" id="programNameModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pnModalTitle">Add Program Name</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="pnId">
                    <div class="mb-3">
                        <label for="pnName" class="form-label">Program Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="pnName" maxlength="255" placeholder="e.g. Rice Seed Program" required>
                        <div class="invalid-feedback" id="pnNameError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="pnAgencyId" class="form-label">Agency <span class="text-danger">*</span></label>
                        <select class="form-select" id="pnAgencyId" required>
                            <option value="" disabled selected>Select agency...</option>
                            @foreach($agencies->where('is_active', true) as $a)
                                <option value="{{ $a->id }}">{{ $a->name }} — {{ $a->full_name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="pnAgencyIdError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="pnDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="pnDescription" rows="2" maxlength="500" placeholder="Optional description..."></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="pnIsActive" checked>
                        <label class="form-check-label" for="pnIsActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="pnSaveBtn" onclick="saveProgramName()">
                        <i class="bi bi-check-lg me-1"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- FORM FIELD OPTION MODAL                       --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div class="modal fade" id="formFieldModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ffModalTitle">Add Option</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="ffId">
                    <div class="mb-3">
                        <label for="ffFieldName" class="form-label">Field <span class="text-danger">*</span></label>
                        <select class="form-select" id="ffFieldName" required>
                            <option value="" disabled selected>Select field...</option>
                            <option value="civil_status">Civil Status</option>
                            <option value="highest_education">Highest Education</option>
                            <option value="id_type">Government ID Type</option>
                            <option value="farm_type">Farm Type</option>
                            <option value="farm_ownership">Farm Ownership</option>
                            <option value="fisherfolk_type">Fisherfolk Type</option>
                            <option value="arb_classification">ARB Classification</option>
                            <option value="ownership_scheme">Ownership Scheme</option>
                        </select>
                        <div class="invalid-feedback" id="ffFieldNameError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="ffLabel" class="form-label">Label <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ffLabel" maxlength="255" placeholder="Display text" required>
                        <div class="invalid-feedback" id="ffLabelError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="ffValue" class="form-label">Value <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ffValue" maxlength="255" placeholder="Stored value" required>
                        <div class="invalid-feedback" id="ffValueError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="ffSortOrder" class="form-label">Sort Order</label>
                        <input type="number" class="form-control" id="ffSortOrder" min="0" placeholder="Auto-assigned if empty">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="ffIsActive" checked>
                        <label class="form-check-label" for="ffIsActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="ffSaveBtn" onclick="saveFormField()">
                        <i class="bi bi-check-lg me-1"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast Container --}}
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
        <div id="settingsToast" class="toast align-items-center border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Restore active tab from hash
    var hash = window.location.hash;
    if (hash) {
        var tab = document.querySelector('#settingsTabs button[data-bs-target="' + hash + '"]');
        if (tab) new bootstrap.Tab(tab).show();
    }
    // Save active tab to hash
    document.querySelectorAll('#settingsTabs button[data-bs-toggle="tab"]').forEach(function (btn) {
        btn.addEventListener('shown.bs.tab', function (e) {
            history.replaceState(null, null, e.target.dataset.bsTarget);
        });
    });
});

var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
var baseUrl   = '{{ url("admin/settings") }}';

// ── Toast ────────────────────────────────────

function showToast(message, type) {
    var toast = document.getElementById('settingsToast');
    var body  = document.getElementById('toastMessage');
    toast.className = 'toast align-items-center border-0 text-bg-' + (type || 'success');
    body.textContent = message;
    bootstrap.Toast.getOrCreateInstance(toast, { delay: 4000 }).show();
}

// ── Helpers ──────────────────────────────────

function esc(str) {
    var div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
}

function clearValidation(prefix, fields) {
    fields.forEach(function (f) {
        var el = document.getElementById(prefix + f);
        if (el) el.classList.remove('is-invalid');
        var err = document.getElementById(prefix + f + 'Error');
        if (err) err.textContent = '';
    });
}

function showValidationErrors(errors, prefix) {
    Object.keys(errors).forEach(function (field) {
        var camel = field.replace(/_([a-z])/g, function (m, p) { return p.toUpperCase(); });
        var key = prefix + camel.charAt(0).toUpperCase() + camel.slice(1);
        var el = document.getElementById(key);
        if (el) el.classList.add('is-invalid');
        var err = document.getElementById(key + 'Error');
        if (err) err.textContent = errors[field][0];
    });
}

function setButtonLoading(btn, loading) {
    if (loading) {
        btn.disabled = true;
        btn.dataset.origHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
    } else {
        btn.disabled = false;
        btn.innerHTML = btn.dataset.origHtml || '<i class="bi bi-check-lg me-1"></i> Save';
    }
}

function ajaxFetch(url, options) {
    return fetch(url, options)
        .then(function (res) {
            return res.json().then(function (d) { return { ok: res.ok, data: d }; });
        });
}

// ══════════════════════════════════════════════
// AGENCIES — Render & CRUD
// ══════════════════════════════════════════════

function refreshAgencies() {
    fetch(baseUrl + '/agencies/list', { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (agencies) { renderAgenciesTable(agencies); });
}

function renderAgenciesTable(agencies) {
    var tbody = document.getElementById('agenciesTableBody');
    if (!agencies.length) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4"><i class="bi bi-inbox fs-3 d-block mb-2"></i>No agencies found.</td></tr>';
        return;
    }
    var html = '';
    agencies.forEach(function (a) {
        var statusClass = a.is_active ? 'bg-success' : 'bg-secondary';
        var statusLabel = a.is_active ? 'Active' : 'Inactive';
        var dataJson = esc(JSON.stringify(a));
        html += '<tr id="agency-row-' + a.id + '">'
            + '<td class="fw-semibold">' + esc(a.name) + '</td>'
            + '<td>' + esc(a.full_name) + '</td>'
            + '<td><span class="badge ' + statusClass + '">' + statusLabel + '</span></td>'
            + '<td class="text-end text-nowrap">'
            + '<button class="btn btn-sm btn-outline-warning me-1" onclick=\'openAgencyModal(' + a.id + ', ' + JSON.stringify(a) + ')\'>'
            + '<i class="bi bi-pencil-square"></i> <span class="btn-action-label">Edit</span></button>';
        if (a.is_active) {
            html += '<button class="btn btn-sm btn-outline-danger" onclick="deactivateAgency(' + a.id + ', \'' + esc(a.name).replace(/'/g, "\\'") + '\')">'
                + '<i class="bi bi-x-circle"></i> <span class="btn-action-label">Deactivate</span></button>';
        }
        html += '</td></tr>';
    });
    tbody.innerHTML = html;

    // Also refresh the resource type modal agency dropdown
    var rtSelect = document.getElementById('rtAgencyId');
    var currentVal = rtSelect.value;
    rtSelect.innerHTML = '<option value="" disabled selected>Select agency...</option>';
    agencies.forEach(function (a) {
        if (a.is_active) {
            var opt = document.createElement('option');
            opt.value = a.id;
            opt.textContent = a.name + ' \u2014 ' + a.full_name;
            rtSelect.appendChild(opt);
        }
    });
    if (currentVal) rtSelect.value = currentVal;

    // Also refresh the program name modal agency dropdown
    var pnSelect = document.getElementById('pnAgencyId');
    var pnCurrentVal = pnSelect.value;
    pnSelect.innerHTML = '<option value="" disabled selected>Select agency...</option>';
    agencies.forEach(function (a) {
        if (a.is_active) {
            var opt = document.createElement('option');
            opt.value = a.id;
            opt.textContent = a.name + ' \u2014 ' + a.full_name;
            pnSelect.appendChild(opt);
        }
    });
    if (pnCurrentVal) pnSelect.value = pnCurrentVal;
}

function openAgencyModal(id, data) {
    document.getElementById('agencyModalTitle').textContent = id ? 'Edit Agency' : 'Add Agency';
    document.getElementById('agencyId').value = id || '';
    document.getElementById('agencyName').value = data ? data.name : '';
    document.getElementById('agencyFullName').value = data ? data.full_name : '';
    document.getElementById('agencyDescription').value = data ? (data.description || '') : '';
    document.getElementById('agencyIsActive').checked = data ? data.is_active : true;
    clearValidation('agency', ['Name', 'FullName']);
    new bootstrap.Modal(document.getElementById('agencyModal')).show();
}

function saveAgency() {
    var id  = document.getElementById('agencyId').value;
    var btn = document.getElementById('agencySaveBtn');
    var url = id ? baseUrl + '/agencies/' + id : baseUrl + '/agencies';

    clearValidation('agency', ['Name', 'FullName']);
    setButtonLoading(btn, true);

    ajaxFetch(url, {
        method: id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({
            name:        document.getElementById('agencyName').value,
            full_name:   document.getElementById('agencyFullName').value,
            description: document.getElementById('agencyDescription').value || null,
            is_active:   document.getElementById('agencyIsActive').checked,
        })
    })
    .then(function (res) {
        setButtonLoading(btn, false);
        if (!res.ok) {
            if (res.data.errors) showValidationErrors(res.data.errors, 'agency');
            else showToast(res.data.message || 'Validation error', 'danger');
            return;
        }
        bootstrap.Modal.getInstance(document.getElementById('agencyModal')).hide();
        showToast('Agency saved successfully.', 'success');
        refreshAgencies();
    })
    .catch(function () {
        setButtonLoading(btn, false);
        showToast('An unexpected error occurred.', 'danger');
    });
}

function deactivateAgency(id, name) {
    if (!confirm('Are you sure you want to deactivate "' + name + '"?')) return;

    fetch(baseUrl + '/agencies/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.success) {
            showToast(data.message || 'Agency deactivated.', data.warning ? 'warning' : 'success');
            refreshAgencies();
        } else {
            showToast(data.message || 'Failed to deactivate agency.', 'danger');
        }
    })
    .catch(function () { showToast('An unexpected error occurred.', 'danger'); });
}

// ══════════════════════════════════════════════
// ASSISTANCE PURPOSES — Render & CRUD
// ══════════════════════════════════════════════

function refreshPurposes() {
    fetch(baseUrl + '/purposes/list', { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (purposes) { renderPurposesTable(purposes); });
}

function renderPurposesTable(purposes) {
    var container = document.getElementById('purposesContainer');

    if (!purposes.length) {
        container.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-inbox fs-3 d-block mb-2"></i>No assistance purposes found.</div>';
        return;
    }

    // Group by category
    var grouped = {};
    purposes.forEach(function (p) {
        if (!grouped[p.category]) grouped[p.category] = [];
        grouped[p.category].push(p);
    });

    var html = '';
    var categories = ['agricultural', 'fishery', 'livelihood', 'medical', 'emergency', 'other'];
    categories.forEach(function (cat) {
        var items = grouped[cat];
        if (!items || !items.length) return;

        html += '<div class="px-3 pt-3 pb-1"><h6 class="text-uppercase text-muted small fw-bold mb-0">'
            + '<i class="bi bi-tag me-1"></i> ' + cat.charAt(0).toUpperCase() + cat.slice(1) + '</h6></div>';
        html += '<div class="table-responsive"><table class="table table-hover align-middle mb-0">'
            + '<thead class="table-light"><tr><th>Name</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>';

        items.forEach(function (p) {
            var statusClass = p.is_active ? 'bg-success' : 'bg-secondary';
            var statusLabel = p.is_active ? 'Active' : 'Inactive';
            html += '<tr id="purpose-row-' + p.id + '">'
                + '<td class="fw-semibold">' + esc(p.name) + '</td>'
                + '<td><span class="badge ' + statusClass + '">' + statusLabel + '</span></td>'
                + '<td class="text-end text-nowrap">'
                + '<button class="btn btn-sm btn-outline-warning me-1" onclick=\'openPurposeModal(' + p.id + ', ' + JSON.stringify(p) + ')\'>'
                + '<i class="bi bi-pencil-square"></i> <span class="btn-action-label">Edit</span></button>';
            if (p.is_active) {
                html += '<button class="btn btn-sm btn-outline-danger" onclick="deactivatePurpose(' + p.id + ', \'' + esc(p.name).replace(/'/g, "\\'") + '\')">'
                    + '<i class="bi bi-x-circle"></i> <span class="btn-action-label">Deactivate</span></button>';
            }
            html += '</td></tr>';
        });
        html += '</tbody></table></div>';
    });

    container.innerHTML = html;
}

function openPurposeModal(id, data) {
    document.getElementById('purposeModalTitle').textContent = id ? 'Edit Purpose' : 'Add Purpose';
    document.getElementById('purposeId').value = id || '';
    document.getElementById('purposeName').value = data ? data.name : '';
    document.getElementById('purposeCategory').value = data ? data.category : '';
    document.getElementById('purposeIsActive').checked = data ? data.is_active : true;
    clearValidation('purpose', ['Name', 'Category']);
    new bootstrap.Modal(document.getElementById('purposeModal')).show();
}

function savePurpose() {
    var id  = document.getElementById('purposeId').value;
    var btn = document.getElementById('purposeSaveBtn');
    var url = id ? baseUrl + '/purposes/' + id : baseUrl + '/purposes';

    clearValidation('purpose', ['Name', 'Category']);
    setButtonLoading(btn, true);

    ajaxFetch(url, {
        method: id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({
            name:      document.getElementById('purposeName').value,
            category:  document.getElementById('purposeCategory').value,
            is_active: document.getElementById('purposeIsActive').checked,
        })
    })
    .then(function (res) {
        setButtonLoading(btn, false);
        if (!res.ok) {
            if (res.data.errors) showValidationErrors(res.data.errors, 'purpose');
            else showToast(res.data.message || 'Validation error', 'danger');
            return;
        }
        bootstrap.Modal.getInstance(document.getElementById('purposeModal')).hide();
        showToast('Purpose saved successfully.', 'success');
        refreshPurposes();
    })
    .catch(function () {
        setButtonLoading(btn, false);
        showToast('An unexpected error occurred.', 'danger');
    });
}

function deactivatePurpose(id, name) {
    if (!confirm('Are you sure you want to deactivate "' + name + '"?')) return;

    fetch(baseUrl + '/purposes/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.success) {
            showToast(data.message || 'Purpose deactivated.', data.warning ? 'warning' : 'success');
            refreshPurposes();
        } else {
            showToast(data.message || 'Failed to deactivate purpose.', 'danger');
        }
    })
    .catch(function () { showToast('An unexpected error occurred.', 'danger'); });
}

// ══════════════════════════════════════════════
// RESOURCE TYPES — Render & CRUD
// ══════════════════════════════════════════════

function refreshResourceTypes() {
    fetch(baseUrl + '/resource-types/list', { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (types) { renderResourceTypesTable(types); });
}

function renderResourceTypesTable(types) {
    var container = document.getElementById('resourceTypesContainer');

    if (!types.length) {
        container.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-inbox fs-3 d-block mb-2"></i>No resource types found.</div>';
        return;
    }

    // Group by agency name
    var grouped = {};
    types.forEach(function (rt) {
        var key = (rt.agency && rt.agency.name) ? rt.agency.name : 'Unassigned';
        if (!grouped[key]) grouped[key] = [];
        grouped[key].push(rt);
    });

    var html = '';
    Object.keys(grouped).sort().forEach(function (agencyName) {
        var items = grouped[agencyName];

        html += '<div class="px-3 pt-3 pb-1"><h6 class="text-uppercase text-muted small fw-bold mb-0">'
            + '<i class="bi bi-building me-1"></i> ' + esc(agencyName) + '</h6></div>';
        html += '<div class="table-responsive"><table class="table table-hover align-middle mb-0">'
            + '<thead class="table-light"><tr><th>Name</th><th>Unit</th><th class="text-end">Actions</th></tr></thead><tbody>';

        items.forEach(function (rt) {
            html += '<tr id="rt-row-' + rt.id + '">'
                + '<td class="fw-semibold">' + esc(rt.name) + '</td>'
                + '<td><span class="badge bg-info text-dark">' + esc(rt.unit) + '</span></td>'
                + '<td class="text-end text-nowrap">'
                + '<button class="btn btn-sm btn-outline-warning me-1" onclick=\'openResourceTypeModal(' + rt.id + ', ' + JSON.stringify(rt) + ')\'>'
                + '<i class="bi bi-pencil-square"></i> <span class="btn-action-label">Edit</span></button>'
                + '<button class="btn btn-sm btn-outline-danger" onclick="deleteResourceType(' + rt.id + ', \'' + esc(rt.name).replace(/'/g, "\\'") + '\')">'
                + '<i class="bi bi-trash"></i> <span class="btn-action-label">Delete</span></button>'
                + '</td></tr>';
        });
        html += '</tbody></table></div>';
    });

    container.innerHTML = html;
}

function openResourceTypeModal(id, data) {
    document.getElementById('rtModalTitle').textContent = id ? 'Edit Resource Type' : 'Add Resource Type';
    document.getElementById('rtId').value = id || '';
    document.getElementById('rtName').value = data ? data.name : '';
    document.getElementById('rtUnit').value = data ? data.unit : '';
    document.getElementById('rtAgencyId').value = data ? (data.agency_id || '') : '';
    document.getElementById('rtDescription').value = data ? (data.description || '') : '';
    clearValidation('rt', ['Name', 'Unit', 'AgencyId']);
    new bootstrap.Modal(document.getElementById('resourceTypeModal')).show();
}

function saveResourceType() {
    var id  = document.getElementById('rtId').value;
    var btn = document.getElementById('rtSaveBtn');
    var url = id ? baseUrl + '/resource-types/' + id : baseUrl + '/resource-types';

    clearValidation('rt', ['Name', 'Unit', 'AgencyId']);
    setButtonLoading(btn, true);

    ajaxFetch(url, {
        method: id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({
            name:        document.getElementById('rtName').value,
            unit:        document.getElementById('rtUnit').value,
            agency_id:   document.getElementById('rtAgencyId').value,
            description: document.getElementById('rtDescription').value || null,
        })
    })
    .then(function (res) {
        setButtonLoading(btn, false);
        if (!res.ok) {
            if (res.data.errors) showValidationErrors(res.data.errors, 'rt');
            else showToast(res.data.message || 'Validation error', 'danger');
            return;
        }
        bootstrap.Modal.getInstance(document.getElementById('resourceTypeModal')).hide();
        showToast('Resource type saved successfully.', 'success');
        refreshResourceTypes();
    })
    .catch(function () {
        setButtonLoading(btn, false);
        showToast('An unexpected error occurred.', 'danger');
    });
}

function deleteResourceType(id, name) {
    if (!confirm('Are you sure you want to delete "' + name + '"? This cannot be undone.')) return;

    ajaxFetch(baseUrl + '/resource-types/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(function (res) {
        if (res.data.success) {
            showToast(res.data.message || 'Resource type deleted.', 'success');
            refreshResourceTypes();
        } else {
            showToast(res.data.message || 'Cannot delete resource type.', 'danger');
        }
    })
    .catch(function () { showToast('An unexpected error occurred.', 'danger'); });
}

// ══════════════════════════════════════════════
// PROGRAM NAMES — Render & CRUD
// ══════════════════════════════════════════════

function refreshProgramNames() {
    fetch(baseUrl + '/program-names/list', { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (programs) { renderProgramNamesTable(programs); });
}

function renderProgramNamesTable(programs) {
    var container = document.getElementById('programNamesContainer');

    if (!programs.length) {
        container.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-inbox fs-3 d-block mb-2"></i>No program names found.</div>';
        return;
    }

    // Group by agency name
    var grouped = {};
    programs.forEach(function (pn) {
        var key = (pn.agency && pn.agency.name) ? pn.agency.name : 'Unassigned';
        if (!grouped[key]) grouped[key] = [];
        grouped[key].push(pn);
    });

    var html = '';
    Object.keys(grouped).sort().forEach(function (agencyName) {
        var items = grouped[agencyName];

        html += '<div class="px-3 pt-3 pb-1"><h6 class="text-uppercase text-muted small fw-bold mb-0">'
            + '<i class="bi bi-building me-1"></i> ' + esc(agencyName) + '</h6></div>';
        html += '<div class="table-responsive"><table class="table table-hover align-middle mb-0">'
            + '<thead class="table-light"><tr><th>Program Name</th><th>Description</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>';

        items.forEach(function (pn) {
            var statusClass = pn.is_active ? 'bg-success' : 'bg-secondary';
            var statusLabel = pn.is_active ? 'Active' : 'Inactive';
            html += '<tr id="pn-row-' + pn.id + '">'
                + '<td class="fw-semibold">' + esc(pn.name) + '</td>'
                + '<td class="text-muted small">' + (pn.description ? esc(pn.description) : '—') + '</td>'
                + '<td><span class="badge ' + statusClass + '">' + statusLabel + '</span></td>'
                + '<td class="text-end text-nowrap">'
                + '<button class="btn btn-sm btn-outline-warning me-1" onclick=\'openProgramNameModal(' + pn.id + ', ' + JSON.stringify(pn) + ')\'>'
                + '<i class="bi bi-pencil-square"></i> <span class="btn-action-label">Edit</span></button>'
                + '<button class="btn btn-sm btn-outline-danger" onclick="deleteProgramName(' + pn.id + ', \'' + esc(pn.name).replace(/'/g, "\\'") + '\')">'
                + '<i class="bi bi-trash"></i> <span class="btn-action-label">Delete</span></button>'
                + '</td></tr>';
        });
        html += '</tbody></table></div>';
    });

    container.innerHTML = html;
}

function openProgramNameModal(id, data) {
    document.getElementById('pnModalTitle').textContent = id ? 'Edit Program Name' : 'Add Program Name';
    document.getElementById('pnId').value = id || '';
    document.getElementById('pnName').value = data ? data.name : '';
    document.getElementById('pnAgencyId').value = data ? (data.agency_id || '') : '';
    document.getElementById('pnDescription').value = data ? (data.description || '') : '';
    document.getElementById('pnIsActive').checked = data ? data.is_active : true;
    clearValidation('pn', ['Name', 'AgencyId']);
    new bootstrap.Modal(document.getElementById('programNameModal')).show();
}

function saveProgramName() {
    var id  = document.getElementById('pnId').value;
    var btn = document.getElementById('pnSaveBtn');
    var url = id ? baseUrl + '/program-names/' + id : baseUrl + '/program-names';

    clearValidation('pn', ['Name', 'AgencyId']);
    setButtonLoading(btn, true);

    ajaxFetch(url, {
        method: id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({
            name:        document.getElementById('pnName').value,
            agency_id:   document.getElementById('pnAgencyId').value,
            description: document.getElementById('pnDescription').value || null,
            is_active:   document.getElementById('pnIsActive').checked,
        })
    })
    .then(function (res) {
        setButtonLoading(btn, false);
        if (!res.ok) {
            if (res.data.errors) showValidationErrors(res.data.errors, 'pn');
            else showToast(res.data.message || 'Validation error', 'danger');
            return;
        }
        bootstrap.Modal.getInstance(document.getElementById('programNameModal')).hide();
        showToast('Program name saved successfully.', 'success');
        refreshProgramNames();
    })
    .catch(function () {
        setButtonLoading(btn, false);
        showToast('An unexpected error occurred.', 'danger');
    });
}

function deleteProgramName(id, name) {
    if (!confirm('Are you sure you want to delete "' + name + '"? This cannot be undone.')) return;

    ajaxFetch(baseUrl + '/program-names/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(function (res) {
        if (res.data.success) {
            showToast(res.data.message || 'Program name deleted.', 'success');
            refreshProgramNames();
        } else {
            showToast(res.data.message || 'Cannot delete program name.', 'danger');
        }
    })
    .catch(function () { showToast('An unexpected error occurred.', 'danger'); });
}

// ══════════════════════════════════════════════
// FORM FIELDS — Render & CRUD
// ══════════════════════════════════════════════

var ffFieldLabels = {
    'civil_status':     'Civil Status',
    'highest_education':'Highest Education',
    'id_type':          'Government ID Type',
    'farm_type':        'Farm Type',
    'farm_ownership':   'Farm Ownership',
    'fisherfolk_type':  'Fisherfolk Type',
    'arb_classification': 'ARB Classification',
    'ownership_scheme': 'Ownership Scheme'
};

var ffOptionsCache = {};

function refreshFormFields() {
    fetch(baseUrl + '/form-fields/list', { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (options) { renderFormFieldsTables(options); });
}

function renderFormFieldsTables(options) {
    ffOptionsCache = {};

    // Group by field_group
    var grouped = {};
    Object.keys(ffFieldLabels).forEach(function (k) { grouped[k] = []; });
    options.forEach(function (opt) {
        if (!grouped[opt.field_group]) grouped[opt.field_group] = [];
        grouped[opt.field_group].push(opt);
    });

    Object.keys(grouped).forEach(function (fieldName) {
        var tbody = document.getElementById('ff-tbody-' + fieldName);
        if (!tbody) return;
        var items = grouped[fieldName];

        // Update count badge
        var badge = document.getElementById('count-' + fieldName);
        if (badge) badge.textContent = items.length;

        if (!items.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">No options configured.</td></tr>';
            return;
        }

        var html = '';
        items.forEach(function (opt) {
            ffOptionsCache[opt.id] = opt;

            var statusClass = opt.is_active ? 'bg-success' : 'bg-secondary';
            var statusLabel = opt.is_active ? 'Active' : 'Inactive';
            html += '<tr id="ff-row-' + opt.id + '" data-id="' + opt.id + '">'
                + '<td class="drag-handle" style="cursor: grab;"><i class="bi bi-grip-vertical text-muted"></i></td>'
                + '<td class="fw-semibold">' + esc(opt.label) + '</td>'
                + '<td><code>' + esc(opt.value) + '</code></td>'
                + '<td>' + opt.sort_order + '</td>'
                + '<td><span class="badge ' + statusClass + '">' + statusLabel + '</span></td>'
                + '<td class="text-end text-nowrap">'
                + '<button class="btn btn-sm btn-outline-warning me-1" onclick="openFormFieldModal(' + opt.id + ')">'
                + '<i class="bi bi-pencil-square"></i></button>'
                + '<button class="btn btn-sm btn-outline-danger" onclick="deleteFormField(' + opt.id + ', \'' + esc(opt.label).replace(/'/g, "\\'") + '\')">'
                + '<i class="bi bi-trash"></i></button>'
                + '</td></tr>';
        });
        tbody.innerHTML = html;

        // Re-init drag-and-drop for this tbody
        initSortable(tbody);
    });
}

function openFormFieldModal(id, data) {
    if (id && !data && ffOptionsCache[id]) {
        data = ffOptionsCache[id];
    }

    document.getElementById('ffModalTitle').textContent = id ? 'Edit Option' : 'Add Option';
    document.getElementById('ffId').value = id || '';
    document.getElementById('ffFieldName').value = data ? data.field_group : '';
    document.getElementById('ffFieldName').disabled = !!id; // Lock field on edit
    document.getElementById('ffLabel').value = data ? data.label : '';
    document.getElementById('ffValue').value = data ? data.value : '';
    document.getElementById('ffSortOrder').value = data ? data.sort_order : '';
    document.getElementById('ffIsActive').checked = data ? data.is_active : true;
    clearValidation('ff', ['FieldName', 'Label', 'Value']);
    new bootstrap.Modal(document.getElementById('formFieldModal')).show();
}

function saveFormField() {
    var id  = document.getElementById('ffId').value;
    var btn = document.getElementById('ffSaveBtn');
    var url = id ? baseUrl + '/form-fields/' + id : baseUrl + '/form-fields';

    clearValidation('ff', ['FieldName', 'Label', 'Value']);
    setButtonLoading(btn, true);

    var payload = {
        label:      document.getElementById('ffLabel').value,
        value:      document.getElementById('ffValue').value,
        is_active:  document.getElementById('ffIsActive').checked,
    };

    var sortVal = document.getElementById('ffSortOrder').value;
    if (sortVal !== '') payload.sort_order = parseInt(sortVal);

    if (!id) {
        payload.field_group = document.getElementById('ffFieldName').value;
    }

    ajaxFetch(url, {
        method: id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(function (res) {
        setButtonLoading(btn, false);
        if (!res.ok) {
            if (res.data.errors) showValidationErrors(res.data.errors, 'ff');
            else showToast(res.data.message || 'Validation error', 'danger');
            return;
        }
        bootstrap.Modal.getInstance(document.getElementById('formFieldModal')).hide();
        showToast('Option saved successfully.', 'success');
        refreshFormFields();
    })
    .catch(function () {
        setButtonLoading(btn, false);
        showToast('An unexpected error occurred.', 'danger');
    });
}

function deleteFormField(id, label) {
    if (!confirm('Are you sure you want to delete "' + label + '"? This cannot be undone.')) return;

    ajaxFetch(baseUrl + '/form-fields/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(function (res) {
        if (res.data.success) {
            showToast(res.data.message || 'Option deleted.', 'success');
            refreshFormFields();
        } else {
            showToast(res.data.message || 'Cannot delete option.', 'danger');
        }
    })
    .catch(function () { showToast('An unexpected error occurred.', 'danger'); });
}

// ── Drag-and-drop reorder ───────────────────

function initSortable(tbody) {
    if (typeof Sortable === 'undefined') return;
    if (tbody._sortable) tbody._sortable.destroy();
    tbody._sortable = Sortable.create(tbody, {
        handle: '.drag-handle',
        animation: 150,
        onEnd: function () {
            var rows = tbody.querySelectorAll('tr[data-id]');
            var items = [];
            rows.forEach(function (row, index) {
                items.push({ id: parseInt(row.dataset.id), order: (index + 1) * 10 });
            });
            fetch(baseUrl + '/form-fields/reorder', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ items: items })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    showToast('Order updated.', 'success');
                    refreshFormFields();
                }
            })
            .catch(function () { showToast('Failed to save order.', 'danger'); });
        }
    });
}

// Init sortable on all form field tbodies
document.addEventListener('DOMContentLoaded', function () {
    Object.keys(ffFieldLabels).forEach(function (fieldName) {
        var tbody = document.getElementById('ff-tbody-' + fieldName);
        if (tbody) initSortable(tbody);
    });
});
</script>
@endpush

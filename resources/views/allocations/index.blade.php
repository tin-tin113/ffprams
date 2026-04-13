@extends('layouts.app')

@section('title', 'Assistance Allocations')

@section('breadcrumb')
    <li class="breadcrumb-item active">Assistance Allocations</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-0">Assistance Allocations</h1>
            <p class="text-muted mb-0">Record direct/personal assistance without creating an event</p>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- ADD DIRECT ASSISTANCE BUTTON                                 --}}
    {{-- ============================================================ --}}
    <div class="mb-4">
        <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addDirectAssistanceModal">
            <i class="bi bi-plus-circle me-2"></i> Add Direct Assistance
        </button>
    </div>

    {{-- ============================================================ --}}
    {{-- MODAL: ADD DIRECT ASSISTANCE                                 --}}
    {{-- ============================================================ --}}
    <div class="modal fade" id="addDirectAssistanceModal" tabindex="-1" aria-labelledby="addDirectAssistanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title" id="addDirectAssistanceModalLabel">
                        <i class="bi bi-plus-circle me-2"></i> Add Direct Assistance
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- Tab Navigation --}}
                <div class="modal-body pt-0">
                    <ul class="nav nav-pills nav-fill mb-3 sticky-top bg-white pt-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="modal_tab_single" data-bs-toggle="tab" data-bs-target="#modal_form_single"
                                    type="button" role="tab" aria-selected="true">
                                <i class="bi bi-person me-1"></i> Single
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="modal_tab_batch" data-bs-toggle="tab" data-bs-target="#modal_form_batch"
                                    type="button" role="tab" aria-selected="false">
                                <i class="bi bi-people me-1"></i> Batch
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        {{-- SINGLE FORM TAB --}}
                        <div class="tab-pane fade show active" id="modal_form_single" role="tabpanel">
                            <form method="POST"
                                action="{{ route('allocations.store') }}"
                                class="row g-3 mb-0"
                                data-submit-spinner
                                data-confirm-title="Confirm Direct Allocation"
                                data-confirm-message="Save this direct assistance allocation? This will create an official transaction record.">
                                @csrf
                                <input type="hidden" name="release_method" value="direct">
                                <div class="col-12">
                    <div class="card border-light bg-light">
                        <div class="card-body">
                            <h6 class="card-title mb-3">
                                <i class="bi bi-search me-2"></i> Find Beneficiary
                            </h6>
                            <div class="row g-2 mb-3">
                                <div class="col-md-3">
                                    <label class="form-label form-label-sm">Barangay</label>
                                    <select id="beneficiary_barangay" class="form-select form-select-sm" data-beneficiary-filter="barangay">
                                        <option value="">All Barangays</option>
                                        @php
                                            $barangays = \App\Models\Barangay::orderBy('name')->get();
                                        @endphp
                                        @foreach($barangays as $barangay)
                                            <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label form-label-sm">Classification</label>
                                    <div class="btn-group btn-group-sm w-100" role="group">
                                        <input type="radio" class="btn-check" id="classification_all" name="beneficiary_classification"
                                               value="" checked data-beneficiary-filter="classification">
                                        <label class="btn btn-outline-secondary" for="classification_all">All</label>
                                        <input type="radio" class="btn-check" id="classification_farmer" name="beneficiary_classification"
                                               value="Farmer" data-beneficiary-filter="classification">
                                        <label class="btn btn-outline-primary" for="classification_farmer">Farmer</label>
                                        <input type="radio" class="btn-check" id="classification_fisherfolk" name="beneficiary_classification"
                                               value="Fisherfolk" data-beneficiary-filter="classification">
                                        <label class="btn btn-outline-info" for="classification_fisherfolk">Fisherfolk</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label form-label-sm">Search by Name or Contact</label>
                                    <input type="text" id="beneficiary_search" class="form-control form-control-sm"
                                           placeholder="Type name or phone number..." data-beneficiary-filter="search">
                                </div>
                            </div>
                            {{-- Search Results --}}
                            <div id="beneficiary_results" class="border-top pt-3" style="display: none;">
                                <div class="small text-muted mb-2">
                                    Found <span id="results_count">0</span> beneficiary(ies)
                                </div>
                                <div id="results_list" class="list-group list-group-sm" style="max-height: 300px; overflow-y: auto;">
                                    {{-- Populated via JS --}}
                                </div>
                            </div>
                            <div id="beneficiary_no_results" class="alert alert-info alert-sm mb-0" style="display: none; font-size: 0.875rem;">
                                <i class="bi bi-info-circle me-1"></i> Type to search for beneficiaries
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Selected Beneficiary Display --}}
                <div class="col-12" id="selected_beneficiary_group" style="display: none;">
                    <div class="alert alert-success py-2 px-3 mb-0">
                        <small class="d-block">
                            <strong>Selected:</strong> <span id="selected_beneficiary_display"></span>
                            <button type="button" id="clear_beneficiary" class="btn btn-sm btn-link p-0 ms-2">Change</button>
                        </small>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Beneficiary <span class="text-danger">*</span></label>
                    <select class="form-select @error('beneficiary_id') is-invalid @enderror" name="beneficiary_id" id="beneficiary_id_field" required>
                        <option value="" selected disabled>Use search above to select</option>
                    </select>
                    @error('beneficiary_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Program <span class="text-danger">*</span></label>
                    <select class="form-select @error('program_name_id') is-invalid @enderror"
                            name="program_name_id"
                            id="program_name_id"
                            required
                            disabled>
                        <option value="" selected disabled>Select Beneficiary First</option>
                    </select>
                    @error('program_name_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted d-block mt-1" id="program_info" style="display: none;">
                        Showing programs eligible for this beneficiary
                    </small>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Resource Type <span class="text-danger">*</span></label>
                    <select class="form-select @error('resource_type_id') is-invalid @enderror"
                            name="resource_type_id"
                            id="resource_type_id"
                            required
                            disabled>
                        <option value="" selected disabled>Select Program First</option>
                    </select>
                    @error('resource_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted d-block mt-1" id="resource_info" style="display: none;">
                        <i class="bi bi-info-circle me-1"></i>Showing resources from program's agency
                    </small>
                </div>

                <div class="col-md-4" id="quantityGroup">
                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" class="form-control @error('quantity') is-invalid @enderror"
                           name="quantity" value="{{ old('quantity') }}" placeholder="e.g. 10">
                    @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4 d-none" id="amountGroup">
                    <label class="form-label">Amount (PHP) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="1" class="form-control @error('amount') is-invalid @enderror"
                           name="amount" value="{{ old('amount') }}" placeholder="e.g. 5000">
                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Assistance Purpose</label>
                    <select class="form-select @error('assistance_purpose_id') is-invalid @enderror" name="assistance_purpose_id">
                        <option value="">Select Purpose (Optional)</option>
                        @foreach($assistancePurposes as $purpose)
                            <option value="{{ $purpose->id }}" {{ old('assistance_purpose_id') == $purpose->id ? 'selected' : '' }}>
                                {{ $purpose->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('assistance_purpose_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control @error('remarks') is-invalid @enderror" name="remarks" rows="2" maxlength="500">{{ old('remarks') }}</textarea>
                    @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-12">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check2-circle me-1"></i> Save Direct Allocation
                    </button>
                </div>
            </form>
                        </div>

                        {{-- BATCH FORM TAB --}}
                        <div class="tab-pane fade" id="modal_form_batch" role="tabpanel">
                            <form id="batch_form" method="POST" action="{{ route('allocations.storeBulk') }}"
                                  data-submit-spinner
                                  data-confirm-title="Confirm Batch Allocation"
                                  data-confirm-message="Save all allocations in batch? This will create official transaction records for each row.">
                                @csrf
                                <input type="hidden" name="release_method" value="direct">

                                <div class="alert alert-info alert-sm mb-3" role="alert">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Batch Mode:</strong> Add multiple allocations at once. Resource types auto-filter based on program's agency.
                                </div>

                                {{-- Batch Beneficiary Finder --}}
                                <div class="card border-light bg-light mb-3">
                                    <div class="card-body py-3">
                                        <h6 class="card-title mb-2">
                                            <i class="bi bi-search me-1"></i> Find Beneficiary (Batch)
                                        </h6>
                                        <div class="row g-2 mb-2">
                                            <div class="col-md-3">
                                                <select id="batch_beneficiary_barangay" class="form-select form-select-sm">
                                                    <option value="">All Barangays</option>
                                                    @foreach($barangays as $barangay)
                                                        <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <select id="batch_beneficiary_classification" class="form-select form-select-sm">
                                                    <option value="">All Classifications</option>
                                                    <option value="Farmer">Farmer</option>
                                                    <option value="Fisherfolk">Fisherfolk</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" id="batch_beneficiary_search" class="form-control form-control-sm" placeholder="Search name or contact...">
                                            </div>
                                            <div class="col-md-2 d-grid">
                                                <button type="button" id="batch_beneficiary_search_btn" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-search me-1"></i> Search
                                                </button>
                                            </div>
                                        </div>
                                        <div id="batch_beneficiary_results" class="list-group list-group-sm" style="max-height: 180px; overflow-y: auto; display:none;"></div>
                                        <small id="batch_beneficiary_hint" class="text-muted">Search and click <strong>Add</strong> to append a beneficiary row quickly.</small>
                                    </div>
                                </div>

                                {{-- Batch Controls --}}
                                <div class="row g-2 mb-3">
                                    <div class="col-auto">
                                        <button type="button" id="batch_add_row" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-lg me-1"></i> Add Row
                                        </button>
                                    </div>
                                    <div class="col-auto">
                                        <button type="button" id="batch_remove_selected" class="btn btn-sm btn-outline-danger" disabled>
                                            <i class="bi bi-trash me-1"></i> Remove Selected
                                        </button>
                                    </div>
                                    <div class="col-auto ms-auto">
                                        <span class="badge bg-secondary" id="batch_row_count">0 rows</span>
                                    </div>
                                </div>

                                {{-- Batch Table --}}
                                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                    <table class="table table-sm table-bordered mb-0" id="batch_table">
                                        <thead class="bg-light sticky-top">
                                            <tr>
                                                <th style="width: 3rem;">
                                                    <input type="checkbox" class="form-check-input" id="batch_select_all" title="Select all rows">
                                                </th>
                                                <th>Beneficiary</th>
                                                <th>Program</th>
                                                <th>Resource Type</th>
                                                <th>Quantity</th>
                                                <th>Purpose</th>
                                                <th style="width: 5rem;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="batch_tbody">
                                            {{-- Rows added via JS --}}
                                        </tbody>
                                    </table>
                                    <div id="batch_empty_state" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                        <p class="mb-0">Click <strong>"Add Row"</strong> to start adding allocations</p>
                                    </div>
                                </div>

                                {{-- Batch Summary --}}
                                <div id="batch_summary" class="card bg-light mt-3" style="display: none;">
                                    <div class="card-body py-2">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <div class="small text-muted">Total Allocations</div>
                                                <div id="summary_count" class="h6 mb-0">0</div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="small text-muted">Validation Status</div>
                                                <div id="summary_status" class="h6 mb-0"><span class="badge bg-warning">Checking...</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Batch Submit --}}
                                <div class="mt-3">
                                    <button type="submit" id="batch_submit" class="btn btn-success" disabled>
                                        <i class="bi bi-check2-circle me-1"></i> Save All Allocations
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('allocations.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-xl-3 col-lg-3 col-md-6">
                        <label class="form-label">Program</label>
                        <select class="form-select" name="program_name_id">
                            <option value="">All Programs</option>
                            @foreach($programNames as $program)
                                <option value="{{ $program->id }}" {{ (string) request('program_name_id') === (string) $program->id ? 'selected' : '' }}>
                                    {{ $program->name }} ({{ $program->agency->name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-2 col-md-6">
                        <label class="form-label">Agency</label>
                        <select class="form-select" name="agency_id">
                            <option value="">All Agencies</option>
                            @foreach($agencies as $agency)
                                <option value="{{ $agency->id }}" {{ (string) request('agency_id') === (string) $agency->id ? 'selected' : '' }}>
                                    {{ $agency->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-2 col-md-6">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Statuses</option>
                            <option value="planned" {{ request('status') === 'planned' ? 'selected' : '' }}>Planned</option>
                            <option value="ready_for_release" {{ request('status') === 'ready_for_release' ? 'selected' : '' }}>Ready for Release</option>
                            <option value="released" {{ request('status') === 'released' ? 'selected' : '' }}>Released</option>
                            <option value="not_received" {{ request('status') === 'not_received' ? 'selected' : '' }}>Not Received</option>
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-2 col-md-6">
                        <label class="form-label">Sort</label>
                        <select class="form-select" name="sort">
                            <option value="date_desc" {{ request('sort', 'date_desc') === 'date_desc' ? 'selected' : '' }}>Date: Newest</option>
                            <option value="date_asc" {{ request('sort') === 'date_asc' ? 'selected' : '' }}>Date: Oldest</option>
                            <option value="program_asc" {{ request('sort') === 'program_asc' ? 'selected' : '' }}>Program: A-Z</option>
                            <option value="program_desc" {{ request('sort') === 'program_desc' ? 'selected' : '' }}>Program: Z-A</option>
                            <option value="status_asc" {{ request('sort') === 'status_asc' ? 'selected' : '' }}>Status: A-Z</option>
                            <option value="status_desc" {{ request('sort') === 'status_desc' ? 'selected' : '' }}>Status: Z-A</option>
                        </select>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-12 d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-funnel me-1"></i> Apply
                        </button>
                        <a href="{{ route('allocations.index') }}" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-list-check me-1"></i> Direct Allocations
        </div>
        <div class="card-body pb-0">
            <p class="text-muted mb-2">{{ $directAllocations->total() }} {{ Str::plural('record', $directAllocations->total()) }} found</p>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-responsive-cards">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Beneficiary</th>
                            <th>Program</th>
                            <th>Resource</th>
                            <th>Value</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th class="text-end text-nowrap" style="min-width: 340px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($directAllocations as $allocation)
                            <tr>
                                <td class="text-muted small" data-label="Date">{{ $allocation->created_at->format('M d, Y') }}</td>
                                <td data-label="Beneficiary">{{ $allocation->beneficiary->full_name ?? 'N/A' }}</td>
                                <td data-label="Program">{{ $allocation->programName->name ?? 'N/A' }}</td>
                                <td data-label="Resource">{{ $allocation->resourceType->name ?? 'N/A' }}</td>
                                <td data-label="Value">{{ $allocation->getDisplayValue() }}</td>
                                <td data-label="Purpose">{{ $allocation->assistancePurpose->name ?? 'N/A' }}</td>
                                @php($releaseStatus = $allocation->release_status)
                                <td data-label="Status">
                                    @switch($releaseStatus)
                                        @case('planned')
                                            <span class="badge bg-warning text-dark">Planned</span>
                                            @break
                                        @case('ready_for_release')
                                            <span class="badge bg-primary">Ready for Release</span>
                                            @break
                                        @case('released')
                                            <span class="badge bg-success">Released</span>
                                            @break
                                        @case('not_received')
                                            <span class="badge bg-danger">Not Received</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $allocation->release_status_label }}</span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="text-end text-nowrap" data-label="Actions">
                                    <div class="d-inline-flex align-items-center gap-1 flex-nowrap justify-content-end">
                                        <a href="{{ route('allocations.show', $allocation) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i> View
                                        </a>

                                        @if(in_array($releaseStatus, ['planned', 'not_received'], true))
                                            <form method="POST"
                                                  action="{{ route('allocations.mark-ready-for-release', $allocation) }}"
                                                  class="allocation-action-form d-inline"
                                                  data-confirm-title="Set Ready for Release"
                                                  data-confirm-message="Set this direct allocation to Ready for Release? If SMS automation is enabled, this will send an automatic SMS to the beneficiary.">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-bell"></i> Ready for Release
                                                </button>
                                            </form>
                                        @elseif($releaseStatus === 'ready_for_release')
                                            <form method="POST"
                                                  action="{{ route('allocations.markDistributed', $allocation) }}"
                                                  class="allocation-action-form d-inline"
                                                  data-confirm-title="Confirm Release"
                                                  data-confirm-message="Mark this direct allocation as released? This will timestamp the release transaction.">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    <i class="bi bi-check2"></i> Mark Released
                                                </button>
                                            </form>

                                            <form method="POST"
                                                  action="{{ route('allocations.markNotReceived', $allocation) }}"
                                                  class="allocation-action-form d-inline"
                                                  data-confirm-title="Confirm Not Received"
                                                  data-confirm-message="Mark this direct allocation as Not Received?">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-x-lg"></i> Not Received
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small ms-1">Finalized</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No direct allocations yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($directAllocations->hasPages())
            <div class="card-footer bg-white">
                {{ $directAllocations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const beneficiarySelect = document.querySelector('select[name="beneficiary_id"]');
    const resourceSelect = document.getElementById('resource_type_id');
    const programSelect = document.getElementById('program_name_id');
    const quantityGroup = document.getElementById('quantityGroup');
    const amountGroup = document.getElementById('amountGroup');
    const programInfo = document.getElementById('program_info');
    const resourceInfo = document.getElementById('resource_info');
    const resourceInfoDefaultHtml = resourceInfo ? resourceInfo.innerHTML : '';

    function setResourceInfoVisibility(isVisible, messageHtml) {
        if (!resourceInfo) return;

        if (isVisible) {
            resourceInfo.innerHTML = messageHtml || resourceInfoDefaultHtml;
            resourceInfo.style.display = 'block';
            return;
        }

        resourceInfo.style.display = 'none';
        resourceInfo.innerHTML = resourceInfoDefaultHtml;
    }

    async function fetchEligiblePrograms(beneficiaryId) {
        const response = await fetch(`/api/allocations/eligible-programs/${beneficiaryId}`);
        if (!response.ok) {
            const text = await response.text();
            console.error('Eligible programs error response:', text);
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        if (!data.success || !Array.isArray(data.programs)) {
            throw new Error(data.error || 'Invalid eligible programs response.');
        }

        return data.programs;
    }

    function resetResourceSelectState(selectElement, placeholderText) {
        selectElement.innerHTML = `<option value="" selected disabled>${placeholderText}</option>`;
        selectElement.disabled = true;
    }

    // Fetch eligible programs when beneficiary is selected
    async function loadEligiblePrograms(beneficiaryId) {
        if (!beneficiaryId) {
            programSelect.innerHTML = '<option value="" selected disabled>Select Beneficiary First</option>';
            programSelect.disabled = true;
            programInfo.style.display = 'none';

            resetResourceSelectState(resourceSelect, 'Select Program First');
            setResourceInfoVisibility(false);

            return;
        }

        try {
            const programs = await fetchEligiblePrograms(beneficiaryId);

            programSelect.innerHTML = '<option value="" selected disabled>Select Program</option>';
            resetResourceSelectState(resourceSelect, 'Select Program First');
            setResourceInfoVisibility(false);

            if (programs.length === 0) {
                programSelect.innerHTML = '<option value="" selected disabled>No eligible programs for this beneficiary</option>';
                programSelect.disabled = true;
                programInfo.style.display = 'none';
                return;
            }

            programs.forEach(prog => {
                const option = document.createElement('option');
                option.value = prog.id;
                option.textContent = prog.formatted;
                programSelect.appendChild(option);
            });

            programSelect.disabled = false;
            programInfo.style.display = 'block';

            const previousValue = programSelect.dataset.previousValue;
            if (previousValue && Array.from(programSelect.options).some(opt => opt.value === previousValue)) {
                programSelect.value = previousValue;
                await loadResourceTypesByProgram(programSelect, resourceSelect);
            }

            delete programSelect.dataset.previousValue;
        } catch (error) {
            console.error('Error loading eligible programs:', error);
            programSelect.innerHTML = '<option value="" selected disabled>Error loading programs</option>';
            programSelect.disabled = true;
            programInfo.style.display = 'none';
            resetResourceSelectState(resourceSelect, 'Error loading resources');
            setResourceInfoVisibility(false);
        }
    }

    // Load programs when beneficiary changes
    beneficiarySelect.addEventListener('change', function () {
        programSelect.dataset.previousValue = programSelect.value;
        loadEligiblePrograms(this.value);
    });

    // Toggle quantity/amount inputs based on resource type
    function toggleValueInputs() {
        const selected = resourceSelect.options[resourceSelect.selectedIndex];
        const unit = selected ? selected.dataset.unit : '';
        const isFinancial = unit === 'PHP';

        quantityGroup.classList.toggle('d-none', isFinancial);
        amountGroup.classList.toggle('d-none', !isFinancial);
    }

    // Toggle value input mode when resource type changes.
    resourceSelect.addEventListener('change', toggleValueInputs);
    toggleValueInputs();

    // ===== BENEFICIARY SEARCH FUNCTIONALITY =====
    const searchInput = document.getElementById('beneficiary_search');
    const barangayFilter = document.getElementById('beneficiary_barangay');
    const classificationFilters = document.querySelectorAll('[data-beneficiary-filter="classification"]');
    const resultsList = document.getElementById('results_list');
    const resultsCount = document.getElementById('results_count');
    const resultsGroup = document.getElementById('beneficiary_results');
    const noResultsMsg = document.getElementById('beneficiary_no_results');
    const beneficiaryIdField = document.getElementById('beneficiary_id_field');
    const selectedBeneficiaryGroup = document.getElementById('selected_beneficiary_group');
    const selectedBeneficiaryDisplay = document.getElementById('selected_beneficiary_display');
    const clearBeneficiaryBtn = document.getElementById('clear_beneficiary');

    let searchTimeout;

    async function performSearch() {
        const query = searchInput.value.trim();
        const barangayId = barangayFilter.value;
        const classification = document.querySelector('[data-beneficiary-filter="classification"]:checked')?.value || '';

        // Show search hint if query is empty
        if (!query && !barangayId && !classification) {
            resultsGroup.style.display = 'none';
            noResultsMsg.style.display = 'block';
            return;
        }

        try {
            const params = new URLSearchParams();
            if (query) params.append('q', query);
            if (barangayId) params.append('barangay_id', barangayId);
            if (classification) params.append('classification', classification);

            const response = await fetch(`/api/beneficiaries/search?${params}`);
            const data = await response.json();

            if (data.success && data.results) {
                resultsList.innerHTML = '';

                if (data.results.length === 0) {
                    noResultsMsg.style.display = 'block';
                    resultsGroup.style.display = 'none';
                } else {
                    noResultsMsg.style.display = 'none';
                    resultsGroup.style.display = 'block';
                    resultsCount.textContent = data.results.length;

                    data.results.forEach(beneficiary => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'list-group-item list-group-item-action list-group-item-sm d-flex justify-content-between align-items-center';
                        item.innerHTML = `
                            <div class="text-start">
                                <div class="fw-semibold small">${beneficiary.name}</div>
                                <div class="text-muted small">
                                    <span class="badge bg-sm ${beneficiary.classification === 'Farmer' ? 'bg-primary' : 'bg-info'}" style="font-size: 0.7rem;">${beneficiary.classification}</span>
                                    ${beneficiary.barangay} ${beneficiary.contact ? '• ' + beneficiary.contact : ''}
                                </div>
                            </div>
                        `;
                        item.addEventListener('click', (e) => {
                            e.preventDefault();
                            selectBeneficiary(beneficiary);
                        });
                        resultsList.appendChild(item);
                    });
                }
            }
        } catch (error) {
            console.error('Search error:', error);
            noResultsMsg.textContent = 'Error loading beneficiaries';
            noResultsMsg.style.display = 'block';
        }
    }

    function selectBeneficiary(beneficiary) {
        // Set the hidden field value
        beneficiaryIdField.value = beneficiary.id;

        // Clear existing options and add the selected one
        beneficiaryIdField.innerHTML = `
            <option value="" disabled>Select Beneficiary</option>
            <option value="${beneficiary.id}" selected>
                ${beneficiary.name} (${beneficiary.classification}) - ${beneficiary.barangay}
            </option>
        `;

        // Dispatch change event to trigger program loading
        beneficiaryIdField.dispatchEvent(new Event('change'));

        // Show selected confirmation
        selectedBeneficiaryDisplay.textContent = `${beneficiary.name} (${beneficiary.classification}) - ${beneficiary.barangay}`;
        selectedBeneficiaryGroup.style.display = 'block';

        // Hide search results
        resultsGroup.style.display = 'none';
        noResultsMsg.style.display = 'none';
        searchInput.value = '';
    }

    // Search triggers
    searchInput.addEventListener('keyup', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, 300);
    });

    barangayFilter.addEventListener('change', performSearch);
    classificationFilters.forEach(filter => {
        filter.addEventListener('change', performSearch);
    });

    // Clear beneficiary selection
    clearBeneficiaryBtn.addEventListener('click', (e) => {
        e.preventDefault();
        beneficiaryIdField.value = '';
        beneficiaryIdField.innerHTML = '<option value="" selected disabled>Use search above to select</option>';
        selectedBeneficiaryGroup.style.display = 'none';
        loadEligiblePrograms('');
        searchInput.focus();
        performSearch();
    });

    // ===== RESOURCE TYPE FILTERING BY PROGRAM =====
    async function loadResourceTypesByProgram(programSelect, resourceTypeSelect) {
        const programId = programSelect.value;
        const isSingleForm = resourceTypeSelect === resourceSelect;

        if (!programId) {
            resetResourceSelectState(resourceTypeSelect, 'Select Program First');
            if (isSingleForm) {
                setResourceInfoVisibility(false);
            }
            return;
        }

        try {
            const response = await fetch(`/api/programs/${programId}/resource-types`);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error || 'Failed to load resource types');
            }

            const resourceTypes = Array.isArray(data.resourceTypes) ? data.resourceTypes : [];
            const emptyMessage = data.message || 'No resources available for this program';

            resourceTypeSelect.innerHTML = '<option value="" selected disabled>Select Resource Type</option>';

            if (resourceTypes.length === 0) {
                resourceTypeSelect.innerHTML = `<option value="" selected disabled>${emptyMessage}</option>`;
                resourceTypeSelect.disabled = true;
                if (isSingleForm) {
                    setResourceInfoVisibility(true, `<i class="bi bi-info-circle me-1"></i>${emptyMessage}`);
                }
                return;
            }

            resourceTypes.forEach(rt => {
                const option = document.createElement('option');
                option.value = rt.id;
                option.dataset.unit = rt.unit;
                option.textContent = rt.formatted;
                resourceTypeSelect.appendChild(option);
            });

            resourceTypeSelect.disabled = false;
            if (isSingleForm) {
                setResourceInfoVisibility(true);
            }

            if (isSingleForm) {
                toggleValueInputs();
            }
        } catch (error) {
            console.error('Error loading resource types:', error);
            resourceTypeSelect.innerHTML = '<option value="" selected disabled>Error loading resources</option>';
            resourceTypeSelect.disabled = true;
            if (isSingleForm) {
                setResourceInfoVisibility(true, '<i class="bi bi-exclamation-triangle me-1"></i>Error loading resources for this program.');
            }
        }
    }

    // Load resource types when program changes (single form)
    programSelect.addEventListener('change', () => {
        loadResourceTypesByProgram(programSelect, resourceSelect);
    });

    // ===== BATCH OPERATIONS =====
    const batchAddRowBtn = document.getElementById('batch_add_row');
    const batchRemoveBtn = document.getElementById('batch_remove_selected');
    const batchSelectAllCheckbox = document.getElementById('batch_select_all');
    const batchTbody = document.getElementById('batch_tbody');
    const batchEmptyState = document.getElementById('batch_empty_state');
    const batchSummary = document.getElementById('batch_summary');
    const batchSummaryStatus = document.getElementById('summary_status');
    const batchSubmitBtn = document.getElementById('batch_submit');
    const batchRowCount = document.getElementById('batch_row_count');
    const summaryCount = document.getElementById('summary_count');
    const batchFinderSearchInput = document.getElementById('batch_beneficiary_search');
    const batchFinderBarangay = document.getElementById('batch_beneficiary_barangay');
    const batchFinderClassification = document.getElementById('batch_beneficiary_classification');
    const batchFinderSearchBtn = document.getElementById('batch_beneficiary_search_btn');
    const batchFinderResults = document.getElementById('batch_beneficiary_results');
    const batchFinderHint = document.getElementById('batch_beneficiary_hint');

    let batchRowIndex = 0;
    const assistancePurposes = @json($assistancePurposes ?? []);

    function updateBatchSummary() {
        const rows = document.querySelectorAll('#batch_tbody tr');
        const count = rows.length;
        summaryCount.textContent = count;
        batchRowCount.textContent = count + (count === 1 ? ' row' : ' rows');

        const isEmpty = count === 0;
        batchEmptyState.style.display = isEmpty ? 'block' : 'none';
        batchSummary.style.display = isEmpty ? 'none' : 'block';
        batchRemoveBtn.disabled = isEmpty;
        batchSubmitBtn.disabled = isEmpty;
    }

    function getBatchSelectedBeneficiaryIds() {
        return Array.from(document.querySelectorAll('#batch_tbody .batch-beneficiary-id'))
            .map((el) => Number(el.value))
            .filter((id) => Number.isFinite(id) && id > 0);
    }

    function renderBatchFinderResults(results) {
        if (!batchFinderResults || !batchFinderHint) {
            return;
        }

        batchFinderResults.innerHTML = '';

        if (!results.length) {
            batchFinderResults.style.display = 'none';
            batchFinderHint.textContent = 'No matching beneficiaries found.';
            return;
        }

        const selectedIds = new Set(getBatchSelectedBeneficiaryIds());

        results.forEach((beneficiary) => {
            const item = document.createElement('div');
            item.className = 'list-group-item d-flex justify-content-between align-items-center';

            const alreadyAdded = selectedIds.has(Number(beneficiary.id));

            item.innerHTML = `
                <div class="small">
                    <div class="fw-semibold">${beneficiary.name}</div>
                    <div class="text-muted">${beneficiary.classification} • ${beneficiary.barangay}</div>
                </div>
            `;

            const addBtn = document.createElement('button');
            addBtn.type = 'button';
            addBtn.className = `btn btn-sm ${alreadyAdded ? 'btn-secondary' : 'btn-outline-primary'}`;
            addBtn.textContent = alreadyAdded ? 'Added' : 'Add';
            addBtn.disabled = alreadyAdded;

            addBtn.addEventListener('click', () => {
                const row = createBatchRow(batchRowIndex++, {
                    beneficiary,
                });

                if (row) {
                    addBtn.textContent = 'Added';
                    addBtn.className = 'btn btn-sm btn-secondary';
                    addBtn.disabled = true;
                }
            });

            item.appendChild(addBtn);
            batchFinderResults.appendChild(item);
        });

        batchFinderResults.style.display = 'block';
        batchFinderHint.textContent = `Found ${results.length} beneficiary(ies).`;
    }

    async function searchBatchBeneficiaries() {
        if (!batchFinderResults || !batchFinderSearchInput) {
            return;
        }

        const query = batchFinderSearchInput.value.trim();
        const barangayId = batchFinderBarangay ? batchFinderBarangay.value : '';
        const classification = batchFinderClassification ? batchFinderClassification.value : '';

        if (!query && !barangayId && !classification) {
            batchFinderResults.style.display = 'none';
            if (batchFinderHint) {
                batchFinderHint.innerHTML = 'Search and click <strong>Add</strong> to append a beneficiary row quickly.';
            }
            return;
        }

        try {
            const params = new URLSearchParams();
            if (query) params.append('q', query);
            if (barangayId) params.append('barangay_id', barangayId);
            if (classification) params.append('classification', classification);

            const response = await fetch(`/api/beneficiaries/search?${params.toString()}`);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            renderBatchFinderResults(Array.isArray(data.results) ? data.results : []);
        } catch (error) {
            console.error('Batch beneficiary search error:', error);
            batchFinderResults.style.display = 'none';
            if (batchFinderHint) {
                batchFinderHint.textContent = 'Error loading beneficiaries. Please try again.';
            }
        }
    }

    function createBatchRow(index, preset = null) {
        const row = document.createElement('tr');
        row.dataset.rowIndex = index;
        const suggestionsId = `batch_beneficiary_suggestions_${index}`;
        row.innerHTML = `
            <td class="text-center">
                <input type="checkbox" class="form-check-input batch-row-checkbox">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm batch-beneficiary-search"
                       placeholder="Type beneficiary name..." data-row-index="${index}" list="${suggestionsId}" autocomplete="off" required>
                <datalist id="${suggestionsId}"></datalist>
                <input type="hidden" name="allocations[${index}][beneficiary_id]" class="batch-beneficiary-id">
            </td>
            <td>
                <select name="allocations[${index}][program_name_id]" class="form-select form-select-sm batch-program" required disabled>
                    <option value="">Select Beneficiary First</option>
                </select>
            </td>
            <td>
                <select name="allocations[${index}][resource_type_id]" class="form-select form-select-sm batch-resource" required disabled>
                    <option value="">Select Program First</option>
                </select>
            </td>
            <td>
                <input type="number" step="0.01" min="0.01" name="allocations[${index}][quantity]"
                       class="form-control form-control-sm batch-quantity" placeholder="Qty" required>
            </td>
            <td>
                <select name="allocations[${index}][assistance_purpose_id]" class="form-select form-select-sm batch-purpose">
                    <option value="">-</option>
                    @foreach($assistancePurposes as $purpose)
                        <option value="{{ $purpose->id }}">{{ $purpose->name }}</option>
                    @endforeach
                </select>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger batch-remove-row" title="Remove row">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        // Beneficiary search in batch row
        const beneficiarySearch = row.querySelector('.batch-beneficiary-search');
        const programSelect = row.querySelector('.batch-program');
        const resourceSelect = row.querySelector('.batch-resource');
        const beneficiaryIdInput = row.querySelector('.batch-beneficiary-id');
        const suggestionList = row.querySelector(`#${suggestionsId}`);
        const beneficiaryMap = new Map();
        let beneficiarySearchTimeout;

        async function loadBatchProgramsForBeneficiary(beneficiaryId) {
            if (!beneficiaryId) {
                programSelect.innerHTML = '<option value="" selected disabled>Select Beneficiary First</option>';
                programSelect.disabled = true;
                resetResourceSelectState(resourceSelect, 'Select Program First');
                return;
            }

            try {
                const programs = await fetchEligiblePrograms(beneficiaryId);
                programSelect.innerHTML = '<option value="" selected disabled>Select Program</option>';
                resetResourceSelectState(resourceSelect, 'Select Program First');

                if (programs.length === 0) {
                    programSelect.innerHTML = '<option value="" selected disabled>No eligible programs</option>';
                    programSelect.disabled = true;
                    return;
                }

                programs.forEach(prog => {
                    const option = document.createElement('option');
                    option.value = prog.id;
                    option.textContent = prog.formatted;
                    programSelect.appendChild(option);
                });

                programSelect.disabled = false;

                if (programs.length === 1) {
                    programSelect.value = String(programs[0].id);
                    await loadResourceTypesByProgram(programSelect, resourceSelect);
                }
            } catch (error) {
                console.error('Error loading batch eligible programs:', error);
                programSelect.innerHTML = '<option value="" selected disabled>Error loading programs</option>';
                programSelect.disabled = true;
                resetResourceSelectState(resourceSelect, 'Error loading resources');
            }
        }

        function applyBatchBeneficiarySelection(displayValue) {
            const selectedBeneficiary = beneficiaryMap.get(displayValue);
            if (!selectedBeneficiary) {
                beneficiaryIdInput.value = '';
                loadBatchProgramsForBeneficiary('');
                return;
            }

            beneficiaryIdInput.value = String(selectedBeneficiary.id);
            beneficiarySearch.value = selectedBeneficiary.display;
            loadBatchProgramsForBeneficiary(selectedBeneficiary.id);
        }

        beneficiarySearch.addEventListener('input', () => {
            const query = beneficiarySearch.value.trim();
            beneficiaryIdInput.value = '';
            beneficiaryMap.clear();
            suggestionList.innerHTML = '';
            clearTimeout(beneficiarySearchTimeout);

            if (query.length < 2) {
                loadBatchProgramsForBeneficiary('');
                return;
            }

            beneficiarySearchTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(`/api/beneficiaries/search?q=${encodeURIComponent(query)}`);
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }

                    const data = await response.json();
                    const results = Array.isArray(data.results) ? data.results : [];

                    suggestionList.innerHTML = '';
                    beneficiaryMap.clear();

                    results.forEach((beneficiary) => {
                        beneficiaryMap.set(beneficiary.display, beneficiary);

                        const option = document.createElement('option');
                        option.value = beneficiary.display;
                        suggestionList.appendChild(option);
                    });

                    if (results.length === 1) {
                        applyBatchBeneficiarySelection(results[0].display);
                    }
                } catch (e) {
                    console.error('Error searching beneficiary:', e);
                }
            }, 250);
        });

        beneficiarySearch.addEventListener('change', () => {
            applyBatchBeneficiarySelection(beneficiarySearch.value.trim());
        });

        beneficiarySearch.addEventListener('blur', () => {
            if (!beneficiaryIdInput.value) {
                applyBatchBeneficiarySelection(beneficiarySearch.value.trim());
            }
        });

        // Load resource types when program changes
        programSelect.addEventListener('change', () => {
            loadResourceTypesByProgram(programSelect, resourceSelect);
        });

        // Remove row
        row.querySelector('.batch-remove-row').addEventListener('click', () => {
            row.remove();
            updateBatchSummary();
        });

        // Select checkbox
        row.querySelector('.batch-row-checkbox').addEventListener('change', () => {
            updateBatchSelectAll();
        });

        if (preset && preset.beneficiary) {
            beneficiaryMap.set(preset.beneficiary.display, preset.beneficiary);
            beneficiarySearch.value = preset.beneficiary.display;
            applyBatchBeneficiarySelection(preset.beneficiary.display);
        }

        batchTbody.appendChild(row);
        updateBatchSummary();

        return row;
    }

    function updateBatchSelectAll() {
        const checkboxes = document.querySelectorAll('.batch-row-checkbox');
        const checked = document.querySelectorAll('.batch-row-checkbox:checked').length;
        batchSelectAllCheckbox.checked = checked === checkboxes.length && checkboxes.length > 0;
        batchSelectAllCheckbox.indeterminate = checked > 0 && checked < checkboxes.length;
    }

    // Batch controls
    batchAddRowBtn.addEventListener('click', () => {
        createBatchRow(batchRowIndex++);
    });

    batchSelectAllCheckbox.addEventListener('change', () => {
        document.querySelectorAll('.batch-row-checkbox').forEach(checkbox => {
            checkbox.checked = batchSelectAllCheckbox.checked;
        });
    });

    batchRemoveBtn.addEventListener('click', () => {
        document.querySelectorAll('.batch-row-checkbox:checked').forEach(checkbox => {
            checkbox.closest('tr').remove();
        });
        batchSelectAllCheckbox.checked = false;
        updateBatchSummary();

        if (batchFinderResults && batchFinderResults.style.display !== 'none') {
            searchBatchBeneficiaries();
        }
    });

    if (batchFinderSearchBtn) {
        batchFinderSearchBtn.addEventListener('click', searchBatchBeneficiaries);
    }

    if (batchFinderSearchInput) {
        batchFinderSearchInput.addEventListener('keyup', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(searchBatchBeneficiaries, 250);
        });
    }

    if (batchFinderBarangay) {
        batchFinderBarangay.addEventListener('change', searchBatchBeneficiaries);
    }

    if (batchFinderClassification) {
        batchFinderClassification.addEventListener('change', searchBatchBeneficiaries);
    }
});
</script>
@endpush

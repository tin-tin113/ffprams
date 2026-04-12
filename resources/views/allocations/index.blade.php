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
    <div class="modal fade modal-lg" id="addDirectAssistanceModal" tabindex="-1" aria-labelledby="addDirectAssistanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
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
                                class="row g-3"
                                data-submit-spinner
                                data-confirm-title="Confirm Direct Allocation"
                                data-confirm-message="Save this direct assistance allocation? This will create an official transaction record.">
                                @csrf
                                <input type="hidden" name="release_method" value="direct">
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

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-list-check me-1"></i> Latest Direct Allocations
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
                                <td data-label="Status">
                                    @if($allocation->distributed_at)
                                        <span class="badge bg-success">Released</span>
                                    @elseif($allocation->release_outcome === 'not_received')
                                        <span class="badge bg-danger">Not Received</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Planned</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap" data-label="Actions">
                                    <div class="d-inline-flex align-items-center gap-1 flex-nowrap justify-content-end">
                                        <a href="{{ route('allocations.show', $allocation) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i> View
                                        </a>

                                        @if(!$allocation->distributed_at && $allocation->release_outcome !== 'not_received')
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
                                            <span class="text-muted small ms-1">Completed</span>
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

    // Fetch eligible programs when beneficiary is selected
    async function loadEligiblePrograms(beneficiaryId) {
        if (!beneficiaryId) {
            programSelect.innerHTML = '<option value="" selected disabled>Select Beneficiary First</option>';
            programSelect.disabled = true;
            programInfo.style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`/api/allocations/eligible-programs/${beneficiaryId}`);

            if (!response.ok) {
                console.error(`HTTP Error: ${response.status} ${response.statusText}`);
                const text = await response.text();
                console.error('Response body:', text);
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log('Programs loaded:', data);

            if (data.success && data.programs) {
                // Clear existing options
                programSelect.innerHTML = '<option value="" selected disabled>Select Program</option>';

                if (data.programs.length === 0) {
                    programSelect.innerHTML = '<option value="" selected disabled>No eligible programs for this beneficiary</option>';
                    programSelect.disabled = true;
                    programInfo.style.display = 'none';
                } else {
                    // Populate with eligible programs
                    data.programs.forEach(prog => {
                        const option = document.createElement('option');
                        option.value = prog.id;
                        option.textContent = prog.formatted;
                        programSelect.appendChild(option);
                    });

                    programSelect.disabled = false;
                    programInfo.style.display = 'block';

                    // If there was a previous selection, try to keep it
                    if (programSelect.dataset.previousValue) {
                        programSelect.value = programSelect.dataset.previousValue;
                        delete programSelect.dataset.previousValue;
                    }
                }
            } else {
                console.error('Invalid response format:', data);
                throw new Error('Invalid response format from server');
            }
        } catch (error) {
            console.error('Error loading eligible programs:', error);
            programSelect.innerHTML = '<option value="" selected disabled>Error loading programs</option>';
            programSelect.disabled = true;
            programInfo.style.display = 'none';
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

    resourceSelect.addEventListener('change', () => {
        loadResourceTypesByProgram(programSelect, resourceSelect);
        toggleValueInputs();
    });

    // Also toggle when directly selecting resource type
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
        searchInput.focus();
        performSearch();
    });

    // ===== RESOURCE TYPE FILTERING BY PROGRAM =====
    async function loadResourceTypesByProgram(programSelect, resourceTypeSelect) {
        const programId = programSelect.value;
        if (!programId) {
            resourceTypeSelect.innerHTML = '<option value="" selected disabled>Select Program First</option>';
            resourceTypeSelect.disabled = true;
            resourceInfo.style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`/api/programs/${programId}/resource-types`);
            const data = await response.json();

            if (data.success && data.resourceTypes) {
                resourceTypeSelect.innerHTML = '<option value="" selected disabled>Select Resource Type</option>';

                if (data.resourceTypes.length === 0) {
                    resourceTypeSelect.innerHTML = '<option value="" selected disabled>No resources available for this program</option>';
                    resourceTypeSelect.disabled = true;
                    resourceInfo.style.display = 'none';
                } else {
                    data.resourceTypes.forEach(rt => {
                        const option = document.createElement('option');
                        option.value = rt.id;
                        option.dataset.unit = rt.unit;
                        option.textContent = rt.formatted;
                        resourceTypeSelect.appendChild(option);
                    });
                    resourceTypeSelect.disabled = false;
                    resourceInfo.style.display = 'block';
                }
            }
        } catch (error) {
            console.error('Error loading resource types:', error);
            resourceTypeSelect.innerHTML = '<option value="" selected disabled>Error loading resources</option>';
            resourceTypeSelect.disabled = true;
            resourceInfo.style.display = 'none';
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

    function createBatchRow(index) {
        const row = document.createElement('tr');
        row.dataset.rowIndex = index;
        row.innerHTML = `
            <td class="text-center">
                <input type="checkbox" class="form-check-input batch-row-checkbox">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm batch-beneficiary-search"
                       placeholder="Type name..." data-row-index="${index}" required>
                <input type="hidden" name="allocations[${index}][beneficiary_id]" class="batch-beneficiary-id">
            </td>
            <td>
                <select name="allocations[${index}][program_name_id]" class="form-select form-select-sm batch-program" required>
                    <option value="">Select Program</option>
                    @php
                        $allPrograms = \App\Models\ProgramName::where('is_active', true)->orderBy('name')->get();
                    @endphp
                    @foreach($allPrograms as $prog)
                        <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="allocations[${index}][resource_type_id]" class="form-select form-select-sm batch-resource" required>
                    <option value="">Select Resource</option>
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
        let beneficiarySearchTimeout;

        beneficiarySearch.addEventListener('keyup', async () => {
            clearTimeout(beneficiarySearchTimeout);
            const query = beneficiarySearch.value.trim();
            if (query.length < 2) return;

            beneficiarySearchTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(`/api/beneficiaries/search?q=${encodeURIComponent(query)}`);
                    const data = await response.json();
                    if (data.results && data.results.length > 0) {
                        const beneficiary = data.results[0];
                        beneficiarySearch.value = beneficiary.display;
                        beneficiaryIdInput.value = beneficiary.id;
                    }
                } catch (e) {
                    console.error('Error searching beneficiary:', e);
                }
            }, 300);
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

        batchTbody.appendChild(row);
        updateBatchSummary();
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
    });
});
</script>
@endpush

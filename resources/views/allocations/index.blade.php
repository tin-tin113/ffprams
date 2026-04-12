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

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <button type="button"
                    class="btn btn-link text-start w-100 p-0 text-decoration-none fw-semibold"
                    data-bs-toggle="collapse"
                    data-bs-target="#addAssistanceForm">
                <i class="bi bi-chevron-down me-2"></i>
                <i class="bi bi-plus-circle me-1"></i> Add Direct Assistance
            </button>
        </div>
        <div id="addAssistanceForm" class="collapse">
            <div class="card-body">
            <form method="POST"
                action="{{ route('allocations.store') }}"
                class="row g-3"
                data-submit-spinner
                data-confirm-title="Confirm Direct Allocation"
                data-confirm-message="Save this direct assistance allocation? This will create an official transaction record.">
                @csrf
                <input type="hidden" name="release_method" value="direct">

                {{-- Beneficiary Search Section --}}
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
                    <select class="form-select @error('resource_type_id') is-invalid @enderror" name="resource_type_id" id="resource_type_id" required>
                        <option value="" selected disabled>Select Resource Type</option>
                        @foreach($resourceTypes as $type)
                            <option value="{{ $type->id }}"
                                    data-unit="{{ $type->unit }}"
                                    {{ old('resource_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }} ({{ $type->unit }}) - {{ $type->agency->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                    @error('resource_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
        beneficiaryIdField.value = beneficiary.id;
        beneficiaryIdField.dispatchEvent(new Event('change'));

        selectedBeneficiaryDisplay.textContent = `${beneficiary.name} (${beneficiary.classification}) - ${beneficiary.barangay}`;
        selectedBeneficiaryGroup.style.display = 'block';

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
        selectedBeneficiaryGroup.style.display = 'none';
        searchInput.focus();
        performSearch();
    });

    // Rotate chevron on collapse toggle
    const collapseElement = document.getElementById('addAssistanceForm');
    const chevronIcon = collapseElement.previousElementSibling.querySelector('.bi-chevron-down');

    collapseElement.addEventListener('show.bs.collapse', function() {
        chevronIcon.style.transform = 'rotate(0deg)';
    });

    collapseElement.addEventListener('hide.bs.collapse', function() {
        chevronIcon.style.transform = 'rotate(-90deg)';
    });
});
</script>
@endpush

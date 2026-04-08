@extends('layouts.app')

@section('title', 'Create Distribution Event')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('distribution-events.index') }}">Distribution Events</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('distribution-events.index') }}" class="btn btn-outline-secondary btn-sm me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="h3 mb-0">Create Distribution Event</h1>
    </div>

    <form action="{{ route('distribution-events.store') }}"
          method="POST"
          data-submit-spinner
          data-confirm-title="Confirm Event Creation"
          data-confirm-message="Create this scheduled distribution event?">
        @csrf

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-calendar-event me-1"></i> Event Details
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Distribution Type --}}
                    <div class="col-md-12">
                        <label class="form-label">Distribution Type <span class="text-danger">*</span></label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="type" id="type_physical" value="physical"
                                   {{ old('type', 'physical') === 'physical' ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary" for="type_physical">
                                <i class="bi bi-box-seam me-1"></i> Physical Resources
                            </label>
                            <input type="radio" class="btn-check" name="type" id="type_financial" value="financial"
                                   {{ old('type') === 'financial' ? 'checked' : '' }}>
                            <label class="btn btn-outline-success" for="type_financial">
                                <i class="bi bi-cash-stack me-1"></i> Financial Assistance
                            </label>
                        </div>
                        @error('type')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Barangay --}}
                    <div class="col-md-6">
                        <label for="barangay_id" class="form-label">Barangay <span class="text-danger">*</span></label>
                        <select class="form-select @error('barangay_id') is-invalid @enderror"
                                id="barangay_id" name="barangay_id" required>
                            <option value="" disabled {{ old('barangay_id') ? '' : 'selected' }}>Select Barangay</option>
                            @foreach($barangays as $barangay)
                                <option value="{{ $barangay->id }}" {{ old('barangay_id') == $barangay->id ? 'selected' : '' }}>
                                    {{ $barangay->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('barangay_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Resource Type --}}
                    <div class="col-md-6">
                        <label for="resource_type_id" class="form-label">Resource Type <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center gap-2">
                            <select class="form-select @error('resource_type_id') is-invalid @enderror"
                                    id="resource_type_id" name="resource_type_id" required>
                                <option value="" disabled {{ old('resource_type_id') ? '' : 'selected' }}>Select Resource Type</option>
                                @foreach($resourceTypes as $type)
                                    <option value="{{ $type->id }}"
                                            data-unit="{{ $type->unit }}"
                                            data-agency-id="{{ $type->agency_id }}"
                                            {{ old('resource_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }} ({{ $type->unit }}) — {{ $type->agency->name ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                            <span id="unitDisplay" class="badge bg-secondary d-none"></span>
                        </div>
                        @error('resource_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Program Name --}}
                    <div class="col-md-6">
                        <label for="program_name_id" class="form-label">Program Name <span class="text-danger">*</span></label>
                        <select class="form-select @error('program_name_id') is-invalid @enderror"
                                id="program_name_id" name="program_name_id" required>
                            <option value="" disabled {{ old('program_name_id') ? '' : 'selected' }}>Select Program Name</option>
                            @foreach($programNames as $program)
                                <option value="{{ $program->id }}"
                                        data-agency-id="{{ $program->agency_id }}"
                                        {{ old('program_name_id') == $program->id ? 'selected' : '' }}>
                                    {{ $program->name }} — {{ $program->agency->name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                        @error('program_name_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Filtered by resource type's agency</small>
                    </div>

                    {{-- Distribution Date --}}
                    <div class="col-md-6">
                        <label for="distribution_date" class="form-label">Distribution Date <span class="text-danger">*</span></label>
                        <input type="date"
                               class="form-control @error('distribution_date') is-invalid @enderror"
                               id="distribution_date" name="distribution_date"
                               value="{{ old('distribution_date') }}" required>
                        @error('distribution_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Total Fund Budget (Financial only) --}}
                    <div class="col-md-6 d-none" id="totalFundGroup">
                        <label for="total_fund_amount" class="form-label">Total Fund Budget (PHP) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="1" max="9999999999.99"
                               class="form-control @error('total_fund_amount') is-invalid @enderror"
                               id="total_fund_amount" name="total_fund_amount"
                               value="{{ old('total_fund_amount') }}"
                               placeholder="e.g. 500000.00">
                        @error('total_fund_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 d-none" id="financialComplianceFields">
                        <div class="border rounded p-3 bg-light">
                            <h6 class="mb-3">Legal and Compliance Details (Financial)</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="legal_basis_type" class="form-label">Legal Basis Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('legal_basis_type') is-invalid @enderror" id="legal_basis_type" name="legal_basis_type">
                                        <option value="" selected disabled>Select legal basis type</option>
                                        <option value="resolution" {{ old('legal_basis_type') === 'resolution' ? 'selected' : '' }}>Resolution</option>
                                        <option value="ordinance" {{ old('legal_basis_type') === 'ordinance' ? 'selected' : '' }}>Ordinance</option>
                                        <option value="memo" {{ old('legal_basis_type') === 'memo' ? 'selected' : '' }}>Memo</option>
                                        <option value="special_order" {{ old('legal_basis_type') === 'special_order' ? 'selected' : '' }}>Special Order</option>
                                        <option value="other" {{ old('legal_basis_type') === 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('legal_basis_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="legal_basis_reference_no" class="form-label">Legal Basis Reference No. <span class="text-danger">*</span></label>
                                    <input type="text" maxlength="150" class="form-control @error('legal_basis_reference_no') is-invalid @enderror" id="legal_basis_reference_no" name="legal_basis_reference_no" value="{{ old('legal_basis_reference_no') }}" placeholder="e.g. RES-2026-014">
                                    @error('legal_basis_reference_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="legal_basis_date" class="form-label">Legal Basis Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('legal_basis_date') is-invalid @enderror" id="legal_basis_date" name="legal_basis_date" value="{{ old('legal_basis_date') }}">
                                    @error('legal_basis_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="fund_source" class="form-label">Fund Source <span class="text-danger">*</span></label>
                                    <select class="form-select @error('fund_source') is-invalid @enderror" id="fund_source" name="fund_source">
                                        <option value="" selected disabled>Select fund source</option>
                                        <option value="lgu_trust_fund" {{ old('fund_source') === 'lgu_trust_fund' ? 'selected' : '' }}>LGU Trust Fund</option>
                                        <option value="nga_transfer" {{ old('fund_source') === 'nga_transfer' ? 'selected' : '' }}>NGA Transfer</option>
                                        <option value="local_program" {{ old('fund_source') === 'local_program' ? 'selected' : '' }}>Local Program</option>
                                        <option value="other" {{ old('fund_source') === 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('fund_source')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6" id="trustAccountGroup">
                                    <label for="trust_account_code" class="form-label">Trust Account Code</label>
                                    <input type="text" maxlength="100" class="form-control @error('trust_account_code') is-invalid @enderror" id="trust_account_code" name="trust_account_code" value="{{ old('trust_account_code') }}" placeholder="Optional">
                                    @error('trust_account_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="fund_release_reference" class="form-label">Fund Release Reference</label>
                                    <input type="text" maxlength="150" class="form-control @error('fund_release_reference') is-invalid @enderror" id="fund_release_reference" name="fund_release_reference" value="{{ old('fund_release_reference') }}">
                                    @error('fund_release_reference')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="liquidation_status" class="form-label">Liquidation Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('liquidation_status') is-invalid @enderror" id="liquidation_status" name="liquidation_status">
                                        <option value="not_required" {{ old('liquidation_status', 'not_required') === 'not_required' ? 'selected' : '' }}>Not Required</option>
                                        <option value="pending" {{ old('liquidation_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="submitted" {{ old('liquidation_status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                                        <option value="verified" {{ old('liquidation_status') === 'verified' ? 'selected' : '' }}>Verified</option>
                                    </select>
                                    @error('liquidation_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4" id="liquidationDueDateGroup">
                                    <label for="liquidation_due_date" class="form-label">Liquidation Due Date</label>
                                    <input type="date" class="form-control @error('liquidation_due_date') is-invalid @enderror" id="liquidation_due_date" name="liquidation_due_date" value="{{ old('liquidation_due_date') }}">
                                    @error('liquidation_due_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4" id="liquidationSubmittedAtGroup">
                                    <label for="liquidation_submitted_at" class="form-label">Liquidation Submitted At</label>
                                    <input type="datetime-local" class="form-control @error('liquidation_submitted_at') is-invalid @enderror" id="liquidation_submitted_at" name="liquidation_submitted_at" value="{{ old('liquidation_submitted_at') }}">
                                    @error('liquidation_submitted_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4" id="liquidationReferenceGroup">
                                    <label for="liquidation_reference_no" class="form-label">Liquidation Reference No.</label>
                                    <input type="text" maxlength="150" class="form-control @error('liquidation_reference_no') is-invalid @enderror" id="liquidation_reference_no" name="liquidation_reference_no" value="{{ old('liquidation_reference_no') }}">
                                    @error('liquidation_reference_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4" id="farmcReferenceGroup">
                                    <label for="farmc_reference_no" class="form-label">FARMC Reference No.</label>
                                    <input type="text" maxlength="150" class="form-control @error('farmc_reference_no') is-invalid @enderror" id="farmc_reference_no" name="farmc_reference_no" value="{{ old('farmc_reference_no') }}">
                                    @error('farmc_reference_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4" id="farmcEndorsedAtGroup">
                                    <label for="farmc_endorsed_at" class="form-label">FARMC Endorsed At</label>
                                    <input type="datetime-local" class="form-control @error('farmc_endorsed_at') is-invalid @enderror" id="farmc_endorsed_at" name="farmc_endorsed_at" value="{{ old('farmc_endorsed_at') }}">
                                    @error('farmc_endorsed_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" value="1" id="requires_farmc_endorsement" name="requires_farmc_endorsement" {{ old('requires_farmc_endorsement') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="requires_farmc_endorsement">
                                            Requires FARMC endorsement for this event
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-12" id="legalRemarksGroup">
                                    <label for="legal_basis_remarks" class="form-label">Legal/Compliance Remarks</label>
                                    <textarea class="form-control @error('legal_basis_remarks') is-invalid @enderror" id="legal_basis_remarks" name="legal_basis_remarks" rows="2" maxlength="1000">{{ old('legal_basis_remarks') }}</textarea>
                                    @error('legal_basis_remarks')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-lg me-1"></i> Create Event
            </button>
            <a href="{{ route('distribution-events.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const resourceSelect = document.getElementById('resource_type_id');
    const programSelect = document.getElementById('program_name_id');
    const unitDisplay = document.getElementById('unitDisplay');
    const totalFundGroup = document.getElementById('totalFundGroup');
    const totalFundInput = document.getElementById('total_fund_amount');
    const financialComplianceFields = document.getElementById('financialComplianceFields');
    const typeRadios = document.querySelectorAll('input[name="type"]');
    const legalBasisType = document.getElementById('legal_basis_type');
    const legalRemarksGroup = document.getElementById('legalRemarksGroup');
    const legalRemarks = document.getElementById('legal_basis_remarks');
    const fundSource = document.getElementById('fund_source');
    const trustAccountGroup = document.getElementById('trustAccountGroup');
    const trustAccount = document.getElementById('trust_account_code');
    const liquidationStatus = document.getElementById('liquidation_status');
    const liquidationDueDateGroup = document.getElementById('liquidationDueDateGroup');
    const liquidationDueDate = document.getElementById('liquidation_due_date');
    const liquidationSubmittedAtGroup = document.getElementById('liquidationSubmittedAtGroup');
    const liquidationSubmittedAt = document.getElementById('liquidation_submitted_at');
    const liquidationReferenceGroup = document.getElementById('liquidationReferenceGroup');
    const liquidationReference = document.getElementById('liquidation_reference_no');
    const requiresFarmc = document.getElementById('requires_farmc_endorsement');
    const farmcReferenceGroup = document.getElementById('farmcReferenceGroup');
    const farmcReference = document.getElementById('farmc_reference_no');
    const farmcEndorsedAtGroup = document.getElementById('farmcEndorsedAtGroup');
    const farmcEndorsedAt = document.getElementById('farmc_endorsed_at');
    const allResourceOptions = Array.from(resourceSelect.options);
    const allProgramOptions = Array.from(programSelect.options);

    function setGroupState(groupEl, inputEl, show, required = false) {
        if (!groupEl || !inputEl) return;
        groupEl.classList.toggle('d-none', !show);
        inputEl.disabled = !show;
        inputEl.required = show && required;
        if (!show && inputEl.type !== 'checkbox') {
            inputEl.value = '';
        }
    }

    function updateComplianceDependencies() {
        const isFinancial = document.querySelector('input[name="type"]:checked').value === 'financial';

        if (!isFinancial) {
            setGroupState(legalRemarksGroup, legalRemarks, false);
            setGroupState(trustAccountGroup, trustAccount, false);
            setGroupState(liquidationDueDateGroup, liquidationDueDate, false);
            setGroupState(liquidationSubmittedAtGroup, liquidationSubmittedAt, false);
            setGroupState(liquidationReferenceGroup, liquidationReference, false);
            setGroupState(farmcReferenceGroup, farmcReference, false);
            setGroupState(farmcEndorsedAtGroup, farmcEndorsedAt, false);
            return;
        }

        const legalType = legalBasisType?.value ?? '';
        setGroupState(legalRemarksGroup, legalRemarks, true, legalType === 'other');

        const source = fundSource?.value ?? '';
        setGroupState(trustAccountGroup, trustAccount, source === 'lgu_trust_fund', source === 'lgu_trust_fund');

        const liq = liquidationStatus?.value ?? 'not_required';
        const dueRequired = ['pending', 'submitted', 'verified'].includes(liq);
        const submittedRequired = ['submitted', 'verified'].includes(liq);
        setGroupState(liquidationDueDateGroup, liquidationDueDate, dueRequired, dueRequired);
        setGroupState(liquidationSubmittedAtGroup, liquidationSubmittedAt, submittedRequired, submittedRequired);
        setGroupState(liquidationReferenceGroup, liquidationReference, submittedRequired, submittedRequired);

        const farmcRequired = !!requiresFarmc?.checked;
        setGroupState(farmcReferenceGroup, farmcReference, farmcRequired, farmcRequired);
        setGroupState(farmcEndorsedAtGroup, farmcEndorsedAt, farmcRequired, false);
    }

    function updateUnit() {
        const selected = resourceSelect.options[resourceSelect.selectedIndex];
        if (selected && selected.dataset.unit) {
            unitDisplay.textContent = selected.dataset.unit;
            unitDisplay.classList.remove('d-none');
        } else {
            unitDisplay.classList.add('d-none');
        }
    }

    function filterProgramsByAgency() {
        const selected = resourceSelect.options[resourceSelect.selectedIndex];
        const agencyId = selected ? selected.dataset.agencyId : '';
        const currentValue = programSelect.value;

        programSelect.innerHTML = '';

        allProgramOptions.forEach(function (opt) {
            if (opt.value === '') {
                programSelect.appendChild(opt.cloneNode(true));
            } else if (!agencyId || opt.dataset.agencyId === agencyId) {
                programSelect.appendChild(opt.cloneNode(true));
            }
        });

        // Restore selection if still valid
        const exists = Array.from(programSelect.options).some(o => o.value === currentValue);
        if (exists) {
            programSelect.value = currentValue;
        } else {
            programSelect.selectedIndex = 0;
        }
    }

    function toggleType() {
        const isFinancial = document.querySelector('input[name="type"]:checked').value === 'financial';

        // Show/hide total fund amount
        if (isFinancial) {
            totalFundGroup.classList.remove('d-none');
            totalFundInput.required = true;
            financialComplianceFields.classList.remove('d-none');
        } else {
            totalFundGroup.classList.add('d-none');
            totalFundInput.required = false;
            financialComplianceFields.classList.add('d-none');
        }

        updateComplianceDependencies();

        // Filter resource type options
        const currentValue = resourceSelect.value;
        resourceSelect.innerHTML = '';

        allResourceOptions.forEach(function (opt) {
            if (opt.value === '') {
                resourceSelect.appendChild(opt.cloneNode(true));
            } else if (isFinancial && opt.dataset.unit === 'PHP') {
                resourceSelect.appendChild(opt.cloneNode(true));
            } else if (!isFinancial && opt.dataset.unit !== 'PHP') {
                resourceSelect.appendChild(opt.cloneNode(true));
            }
        });

        // Restore selection if still valid
        const exists = Array.from(resourceSelect.options).some(o => o.value === currentValue);
        if (exists) {
            resourceSelect.value = currentValue;
        } else {
            resourceSelect.selectedIndex = 0;
        }

        updateUnit();
        filterProgramsByAgency();
    }

    typeRadios.forEach(function (radio) {
        radio.addEventListener('change', toggleType);
    });

    resourceSelect.addEventListener('change', function () {
        updateUnit();
        filterProgramsByAgency();
    });

    legalBasisType?.addEventListener('change', updateComplianceDependencies);
    fundSource?.addEventListener('change', updateComplianceDependencies);
    liquidationStatus?.addEventListener('change', updateComplianceDependencies);
    requiresFarmc?.addEventListener('change', updateComplianceDependencies);

    toggleType();
    updateComplianceDependencies();
});
</script>
@endpush

@extends('layouts.app')

@section('title', 'New Field Assessment')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('field-assessments.index') }}">Field Assessments</a></li>
    <li class="breadcrumb-item active">New Assessment</li>
@endsection

@section('content')
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('field-assessments.index') }}" class="btn btn-outline-secondary btn-sm me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="h3 mb-0">New Field Assessment</h1>
    </div>

    <form action="{{ route('field-assessments.store') }}" method="POST" data-submit-spinner>
        @csrf

        {{-- SECTION 1: Select Beneficiary --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-person-check me-1"></i> Select Beneficiary
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="beneficiary_id" class="form-label">Beneficiary <span class="text-danger">*</span></label>
                    <select class="form-select @error('beneficiary_id') is-invalid @enderror"
                            id="beneficiary_id" name="beneficiary_id" required>
                        <option value="" disabled {{ old('beneficiary_id') ? '' : 'selected' }}>Search or select a beneficiary...</option>
                        @foreach($beneficiaries as $b)
                            <option value="{{ $b->id }}" {{ old('beneficiary_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->full_name }} — {{ $b->barangay->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                    @error('beneficiary_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Beneficiary Summary Card (loaded via JS) --}}
                <div id="beneficiarySummary" class="d-none">
                    <div class="card bg-light border">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="fw-bold fs-5" id="sumName"></div>
                                    <div class="text-muted" id="sumBarangay"></div>
                                    <div class="mt-1" id="sumClassification"></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="text-muted small">Contact Number</div>
                                            <div class="fw-semibold" id="sumContact"></div>
                                        </div>
                                        <div class="col-6" id="sumRsbsaWrap">
                                            <div class="text-muted small">RSBSA Number</div>
                                            <div class="fw-semibold" id="sumRsbsa"></div>
                                        </div>
                                        <div class="col-6" id="sumFishrWrap">
                                            <div class="text-muted small">FishR Number</div>
                                            <div class="fw-semibold" id="sumFishr"></div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-muted small">Approved Assessments</div>
                                            <div class="fw-semibold" id="sumAssessments"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 2: Visit Information --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-calendar-check me-1"></i> Visit Information
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="visit_date" class="form-label">Visit Date <span class="text-danger">*</span></label>
                        <input type="date"
                               class="form-control @error('visit_date') is-invalid @enderror"
                               id="visit_date" name="visit_date"
                               value="{{ old('visit_date', date('Y-m-d')) }}"
                               max="{{ date('Y-m-d') }}" required>
                        @error('visit_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="visit_time" class="form-label">Visit Time</label>
                        <input type="time"
                               class="form-control @error('visit_time') is-invalid @enderror"
                               id="visit_time" name="visit_time"
                               value="{{ old('visit_time') }}">
                        @error('visit_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-12">
                        <label for="findings" class="form-label">Findings <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('findings') is-invalid @enderror"
                                  id="findings" name="findings" rows="5"
                                  minlength="10" maxlength="2000" required
                                  placeholder="Describe your observations and findings during the field visit...">{{ old('findings') }}</textarea>
                        @error('findings')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 3: Eligibility Assessment --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-shield-check me-1"></i> Eligibility Assessment
            </div>
            <div class="card-body">
                {{-- Eligibility Status Radio Group --}}
                <div class="mb-4">
                    <label class="form-label">Eligibility Status <span class="text-danger">*</span></label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="eligibility_status" id="elig_pending" value="pending"
                               {{ old('eligibility_status', 'pending') === 'pending' ? 'checked' : '' }}>
                        <label class="btn btn-outline-secondary" for="elig_pending">
                            <i class="bi bi-hourglass-split me-1"></i> Pending
                        </label>

                        <input type="radio" class="btn-check" name="eligibility_status" id="elig_eligible" value="eligible"
                               {{ old('eligibility_status') === 'eligible' ? 'checked' : '' }}>
                        <label class="btn btn-outline-success" for="elig_eligible">
                            <i class="bi bi-check-circle me-1"></i> Eligible
                        </label>

                        <input type="radio" class="btn-check" name="eligibility_status" id="elig_not_eligible" value="not_eligible"
                               {{ old('eligibility_status') === 'not_eligible' ? 'checked' : '' }}>
                        <label class="btn btn-outline-danger" for="elig_not_eligible">
                            <i class="bi bi-x-circle me-1"></i> Not Eligible
                        </label>
                    </div>
                    @error('eligibility_status')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- SUBSECTION A: Eligible Fields --}}
                <div id="eligibleFields" class="d-none">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="recommended_assistance_purpose_id" class="form-label">
                                Recommended Assistance Purpose <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('recommended_assistance_purpose_id') is-invalid @enderror"
                                    id="recommended_assistance_purpose_id" name="recommended_assistance_purpose_id">
                                <option value="">Select purpose...</option>
                                @foreach($purposes as $category => $items)
                                    <optgroup label="{{ ucfirst($category) }}">
                                        @foreach($items as $purpose)
                                            <option value="{{ $purpose->id }}"
                                                {{ old('recommended_assistance_purpose_id') == $purpose->id ? 'selected' : '' }}>
                                                {{ $purpose->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('recommended_assistance_purpose_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="recommended_amount" class="form-label">
                                Recommended Amount (PHP) <span class="text-danger">*</span>
                            </label>
                            <input type="number" step="0.01" min="1"
                                   class="form-control @error('recommended_amount') is-invalid @enderror"
                                   id="recommended_amount" name="recommended_amount"
                                   value="{{ old('recommended_amount') }}"
                                   placeholder="e.g. 5000.00">
                            @error('recommended_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label for="eligibility_notes_eligible" class="form-label">Eligibility Notes</label>
                            <textarea class="form-control @error('eligibility_notes') is-invalid @enderror"
                                      id="eligibility_notes_eligible" name="eligibility_notes" rows="3"
                                      maxlength="1000"
                                      placeholder="Optional notes about eligibility...">{{ old('eligibility_notes') }}</textarea>
                            @error('eligibility_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- SUBSECTION B: Not Eligible Fields --}}
                <div id="notEligibleFields" class="d-none">
                    <div class="col-md-12">
                        <label for="eligibility_notes_not_eligible" class="form-label">
                            Reason for Ineligibility <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control @error('eligibility_notes') is-invalid @enderror"
                                  id="eligibility_notes_not_eligible" rows="3"
                                  maxlength="1000"
                                  placeholder="Explain why the beneficiary does not qualify for assistance...">{{ old('eligibility_notes') }}</textarea>
                        @error('eligibility_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-lg me-1"></i> Submit Assessment
            </button>
            <a href="{{ route('field-assessments.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Eligibility Status Toggle ---
    var eligRadios = document.querySelectorAll('input[name="eligibility_status"]');
    var eligibleFields = document.getElementById('eligibleFields');
    var notEligibleFields = document.getElementById('notEligibleFields');
    var eligNotesEligible = document.getElementById('eligibility_notes_eligible');
    var eligNotesNotEligible = document.getElementById('eligibility_notes_not_eligible');

    function toggleEligibility() {
        var selected = document.querySelector('input[name="eligibility_status"]:checked').value;

        if (selected === 'eligible') {
            eligibleFields.classList.remove('d-none');
            notEligibleFields.classList.add('d-none');
            // Sync notes: not_eligible → eligible
            if (eligNotesNotEligible.value && !eligNotesEligible.value) {
                eligNotesEligible.value = eligNotesNotEligible.value;
            }
        } else if (selected === 'not_eligible') {
            eligibleFields.classList.add('d-none');
            notEligibleFields.classList.remove('d-none');
            // Sync notes: eligible → not_eligible
            if (eligNotesEligible.value && !eligNotesNotEligible.value) {
                eligNotesNotEligible.value = eligNotesEligible.value;
            }
        } else {
            eligibleFields.classList.add('d-none');
            notEligibleFields.classList.add('d-none');
        }
    }

    eligRadios.forEach(function (radio) {
        radio.addEventListener('change', toggleEligibility);
    });
    toggleEligibility();

    // Sync the two eligibility_notes textareas on form submit
    var form = document.querySelector('form[data-submit-spinner]');
    form.addEventListener('submit', function () {
        var selected = document.querySelector('input[name="eligibility_status"]:checked').value;
        if (selected === 'not_eligible') {
            eligNotesEligible.value = eligNotesNotEligible.value;
        }
    });

    // --- Beneficiary Summary Fetch ---
    var beneficiarySelect = document.getElementById('beneficiary_id');
    var summaryCard = document.getElementById('beneficiarySummary');

    function loadBeneficiarySummary(id) {
        if (!id) {
            summaryCard.classList.add('d-none');
            return;
        }

        fetch('/api/beneficiaries/' + id + '/summary', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            document.getElementById('sumName').textContent = data.full_name;
            document.getElementById('sumBarangay').textContent = data.barangay || '—';
            document.getElementById('sumContact').textContent = data.contact_number || '—';
            document.getElementById('sumAssessments').textContent = data.approved_assessments ?? 0;

            // Classification badge
            var classEl = document.getElementById('sumClassification');
            var badgeClass = data.classification === 'Farmer' ? 'bg-primary' :
                             data.classification === 'Fisherfolk' ? 'bg-info text-dark' :
                             data.classification === 'Both' ? '' : 'bg-secondary';
            var badgeStyle = data.classification === 'Both' ? 'background-color:#6f42c1;' : '';
            classEl.innerHTML = '<span class="badge ' + badgeClass + '" style="' + badgeStyle + '">' + data.classification + '</span>';

            // RSBSA / FishR
            var rsbsaWrap = document.getElementById('sumRsbsaWrap');
            var fishrWrap = document.getElementById('sumFishrWrap');

            if (data.rsbsa_number) {
                rsbsaWrap.classList.remove('d-none');
                document.getElementById('sumRsbsa').textContent = data.rsbsa_number;
            } else {
                rsbsaWrap.classList.add('d-none');
            }

            if (data.fishr_number) {
                fishrWrap.classList.remove('d-none');
                document.getElementById('sumFishr').textContent = data.fishr_number;
            } else {
                fishrWrap.classList.add('d-none');
            }

            summaryCard.classList.remove('d-none');
        })
        .catch(function () {
            summaryCard.classList.add('d-none');
        });
    }

    beneficiarySelect.addEventListener('change', function () {
        loadBeneficiarySummary(this.value);
    });

    // Load on page load if old value exists
    if (beneficiarySelect.value) {
        loadBeneficiarySummary(beneficiarySelect.value);
    }
});
</script>
@endpush

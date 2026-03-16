@extends('layouts.app')

@section('title', 'Edit Field Assessment')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('field-assessments.index') }}">Field Assessments</a></li>
    <li class="breadcrumb-item"><a href="{{ route('field-assessments.show', $fieldAssessment) }}">#{{ $fieldAssessment->id }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('field-assessments.show', $fieldAssessment) }}" class="btn btn-outline-secondary btn-sm me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="h3 mb-0">Edit Field Assessment &mdash; {{ $fieldAssessment->beneficiary->full_name }}</h1>
    </div>

    <form action="{{ route('field-assessments.update', $fieldAssessment) }}" method="POST" data-submit-spinner>
        @csrf
        @method('PUT')

        {{-- SECTION 1: Beneficiary (read-only display) --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-person-check me-1"></i> Beneficiary
            </div>
            <div class="card-body">
                <input type="hidden" name="beneficiary_id" value="{{ $fieldAssessment->beneficiary_id }}">
                <div class="d-flex align-items-center">
                    <div>
                        <div class="fw-bold fs-5">{{ $fieldAssessment->beneficiary->full_name }}</div>
                        <div class="text-muted">{{ $fieldAssessment->beneficiary->barangay->name ?? '—' }}</div>
                        @php
                            $classBadge = match($fieldAssessment->beneficiary->classification) {
                                'Farmer'     => 'bg-primary',
                                'Fisherfolk' => 'bg-info text-dark',
                                'Both'       => '',
                                default      => 'bg-secondary',
                            };
                        @endphp
                        @if($fieldAssessment->beneficiary->classification === 'Both')
                            <span class="badge mt-1" style="background-color: #6f42c1;">{{ $fieldAssessment->beneficiary->classification }}</span>
                        @else
                            <span class="badge {{ $classBadge }} mt-1">{{ $fieldAssessment->beneficiary->classification }}</span>
                        @endif
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
                               value="{{ old('visit_date', $fieldAssessment->visit_date->format('Y-m-d')) }}"
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
                               value="{{ old('visit_time', $fieldAssessment->visit_time ? \Carbon\Carbon::parse($fieldAssessment->visit_time)->format('H:i') : '') }}">
                        @error('visit_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-12">
                        <label for="findings" class="form-label">Findings <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('findings') is-invalid @enderror"
                                  id="findings" name="findings" rows="5"
                                  minlength="10" maxlength="2000" required
                                  placeholder="Describe your observations and findings during the field visit...">{{ old('findings', $fieldAssessment->findings) }}</textarea>
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
                <div class="mb-4">
                    <label class="form-label">Eligibility Status <span class="text-danger">*</span></label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="eligibility_status" id="elig_pending" value="pending"
                               {{ old('eligibility_status', $fieldAssessment->eligibility_status) === 'pending' ? 'checked' : '' }}>
                        <label class="btn btn-outline-secondary" for="elig_pending">
                            <i class="bi bi-hourglass-split me-1"></i> Pending
                        </label>

                        <input type="radio" class="btn-check" name="eligibility_status" id="elig_eligible" value="eligible"
                               {{ old('eligibility_status', $fieldAssessment->eligibility_status) === 'eligible' ? 'checked' : '' }}>
                        <label class="btn btn-outline-success" for="elig_eligible">
                            <i class="bi bi-check-circle me-1"></i> Eligible
                        </label>

                        <input type="radio" class="btn-check" name="eligibility_status" id="elig_not_eligible" value="not_eligible"
                               {{ old('eligibility_status', $fieldAssessment->eligibility_status) === 'not_eligible' ? 'checked' : '' }}>
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
                                                {{ old('recommended_assistance_purpose_id', $fieldAssessment->recommended_assistance_purpose_id) == $purpose->id ? 'selected' : '' }}>
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
                                   value="{{ old('recommended_amount', $fieldAssessment->recommended_amount) }}"
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
                                      placeholder="Optional notes about eligibility...">{{ old('eligibility_notes', $fieldAssessment->eligibility_notes) }}</textarea>
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
                                  placeholder="Explain why the beneficiary does not qualify for assistance...">{{ old('eligibility_notes', $fieldAssessment->eligibility_notes) }}</textarea>
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
                <i class="bi bi-check-lg me-1"></i> Update Assessment
            </button>
            <a href="{{ route('field-assessments.show', $fieldAssessment) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
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
            if (eligNotesNotEligible.value && !eligNotesEligible.value) {
                eligNotesEligible.value = eligNotesNotEligible.value;
            }
        } else if (selected === 'not_eligible') {
            eligibleFields.classList.add('d-none');
            notEligibleFields.classList.remove('d-none');
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
});
</script>
@endpush

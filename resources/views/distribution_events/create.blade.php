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
    const typeRadios = document.querySelectorAll('input[name="type"]');
    const allResourceOptions = Array.from(resourceSelect.options);
    const allProgramOptions = Array.from(programSelect.options);

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
        } else {
            totalFundGroup.classList.add('d-none');
            totalFundInput.required = false;
        }

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

    toggleType();
});
</script>
@endpush

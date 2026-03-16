@extends('layouts.app')

@section('title', 'Edit Distribution Event')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('distribution-events.index') }}">Distribution Events</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('distribution-events.index') }}" class="btn btn-outline-secondary btn-sm me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1 class="h3 mb-0">Edit Distribution Event</h1>
    </div>

    {{-- Warning Banner --}}
    <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <div>This event can only be edited while its status is Pending.</div>
    </div>

    <form action="{{ route('distribution-events.update', $event) }}" method="POST" data-submit-spinner>
        @csrf
        @method('PUT')

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
                                   {{ old('type', $event->type) === 'physical' ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary" for="type_physical">
                                <i class="bi bi-box-seam me-1"></i> Physical Resources
                            </label>
                            <input type="radio" class="btn-check" name="type" id="type_financial" value="financial"
                                   {{ old('type', $event->type) === 'financial' ? 'checked' : '' }}>
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
                            <option value="" disabled>Select Barangay</option>
                            @foreach($barangays as $barangay)
                                <option value="{{ $barangay->id }}" {{ old('barangay_id', $event->barangay_id) == $barangay->id ? 'selected' : '' }}>
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
                                <option value="" disabled>Select Resource Type</option>
                                @foreach($resourceTypes as $type)
                                    <option value="{{ $type->id }}"
                                            data-unit="{{ $type->unit }}"
                                            {{ old('resource_type_id', $event->resource_type_id) == $type->id ? 'selected' : '' }}>
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

                    {{-- Distribution Date --}}
                    <div class="col-md-6">
                        <label for="distribution_date" class="form-label">Distribution Date <span class="text-danger">*</span></label>
                        <input type="date"
                               class="form-control @error('distribution_date') is-invalid @enderror"
                               id="distribution_date" name="distribution_date"
                               value="{{ old('distribution_date', $event->distribution_date->format('Y-m-d')) }}" required>
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
                               value="{{ old('total_fund_amount', $event->total_fund_amount) }}"
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
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i> Update Event
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
    const unitDisplay = document.getElementById('unitDisplay');
    const totalFundGroup = document.getElementById('totalFundGroup');
    const totalFundInput = document.getElementById('total_fund_amount');
    const typeRadios = document.querySelectorAll('input[name="type"]');
    const allOptions = Array.from(resourceSelect.options);

    function updateUnit() {
        const selected = resourceSelect.options[resourceSelect.selectedIndex];
        if (selected && selected.dataset.unit) {
            unitDisplay.textContent = selected.dataset.unit;
            unitDisplay.classList.remove('d-none');
        } else {
            unitDisplay.classList.add('d-none');
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

        allOptions.forEach(function (opt) {
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
    }

    typeRadios.forEach(function (radio) {
        radio.addEventListener('change', toggleType);
    });

    resourceSelect.addEventListener('change', updateUnit);
    toggleType();
});
</script>
@endpush

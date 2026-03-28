@extends('layouts.app')

@section('title', 'Assistance Allocations')

@section('breadcrumb')
    <li class="breadcrumb-item active">Assistance Allocations</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Assistance Allocations</h1>
            <p class="text-muted mb-0">Record direct/personal assistance without creating an event</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-plus-circle me-1"></i> Add Direct Assistance
        </div>
        <div class="card-body">
            <form method="POST"
                action="{{ route('allocations.store') }}"
                class="row g-3"
                data-submit-spinner
                data-confirm-title="Confirm Direct Allocation"
                data-confirm-message="Save this direct assistance allocation? This will create an official transaction record.">
                @csrf
                <input type="hidden" name="release_method" value="direct">

                <div class="col-md-4">
                    <label class="form-label">Beneficiary <span class="text-danger">*</span></label>
                    <select class="form-select @error('beneficiary_id') is-invalid @enderror" name="beneficiary_id" required>
                        <option value="" selected disabled>Select Beneficiary</option>
                        @foreach($beneficiaries as $beneficiary)
                            <option value="{{ $beneficiary->id }}" {{ old('beneficiary_id') == $beneficiary->id ? 'selected' : '' }}>
                                {{ $beneficiary->full_name }} - {{ $beneficiary->barangay->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                    @error('beneficiary_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Program <span class="text-danger">*</span></label>
                    <select class="form-select @error('program_name_id') is-invalid @enderror" name="program_name_id" required>
                        <option value="" selected disabled>Select Program</option>
                        @foreach($programNames as $program)
                            <option value="{{ $program->id }}" {{ old('program_name_id') == $program->id ? 'selected' : '' }}>
                                {{ $program->name }} - {{ $program->agency->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                    @error('program_name_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
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

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-list-check me-1"></i> Latest Direct Allocations
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Beneficiary</th>
                            <th>Program</th>
                            <th>Resource</th>
                            <th>Value</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($directAllocations as $allocation)
                            <tr>
                                <td class="text-muted small">{{ $allocation->created_at->format('M d, Y') }}</td>
                                <td>{{ $allocation->beneficiary->full_name ?? 'N/A' }}</td>
                                <td>{{ $allocation->programName->name ?? 'N/A' }}</td>
                                <td>{{ $allocation->resourceType->name ?? 'N/A' }}</td>
                                <td>{{ $allocation->getDisplayValue() }}</td>
                                <td>{{ $allocation->assistancePurpose->name ?? 'N/A' }}</td>
                                <td>
                                    @if($allocation->distributed_at)
                                        <span class="badge bg-success">Released</span>
                                    @elseif($allocation->release_outcome === 'not_received')
                                        <span class="badge bg-danger">Not Received</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Planned</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if(!$allocation->distributed_at && $allocation->release_outcome !== 'not_received')
                                        <form method="POST"
                                              action="{{ route('allocations.markDistributed', $allocation) }}"
                                              class="d-inline"
                                              data-confirm-title="Confirm Release"
                                              data-confirm-message="Mark this direct allocation as released? This will timestamp the release transaction.">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-check2"></i> Mark Released
                                            </button>
                                        </form>

                                        <form method="POST"
                                              action="{{ route('allocations.markNotReceived', $allocation) }}"
                                              class="d-inline"
                                              data-confirm-title="Confirm Not Received"
                                              data-confirm-message="Mark this direct allocation as Not Received?">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-x-lg"></i> Not Received
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">Completed</span>
                                    @endif
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
    const resourceSelect = document.getElementById('resource_type_id');
    const quantityGroup = document.getElementById('quantityGroup');
    const amountGroup = document.getElementById('amountGroup');

    function toggleValueInputs() {
        const selected = resourceSelect.options[resourceSelect.selectedIndex];
        const unit = selected ? selected.dataset.unit : '';
        const isFinancial = unit === 'PHP';

        quantityGroup.classList.toggle('d-none', isFinancial);
        amountGroup.classList.toggle('d-none', !isFinancial);
    }

    resourceSelect.addEventListener('change', toggleValueInputs);
    toggleValueInputs();
});
</script>
@endpush

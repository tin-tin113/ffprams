@php
    $isEdit = isset($directAssistance);
    $selectedBeneficiary = $isEdit ? $directAssistance->beneficiary : null;
    $selectedProgram = $isEdit ? $directAssistance->programName : null;
    $selectedResourceType = $isEdit ? $directAssistance->resourceType : null;
@endphp

<form method="POST"
      action="{{ $isEdit ? route('direct-assistance.update', $directAssistance) : route('direct-assistance.store') }}"
      class="row g-3"
      data-submit-spinner
      data-confirm-title="{{ $isEdit ? 'Confirm Update' : 'Confirm Create' }}"
      data-confirm-message="{{ $isEdit ? 'Update this direct assistance record?' : 'Create this direct assistance record?' }}">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <!-- Beneficiary Selection (Always Visible) -->
    <div class="col-12">
        <label class="form-label fw-semibold">Beneficiary <span class="text-danger">*</span></label>
        <select class="form-select @error('beneficiary_id') is-invalid @enderror"
                name="beneficiary_id"
                id="beneficiarySelect"
                {{ $isEdit ? 'disabled' : 'required' }}>
            <option value="" selected disabled>Select Beneficiary</option>
            @foreach($beneficiaries as $beneficiary)
                <option value="{{ $beneficiary->id }}"
                        data-agency="{{ $beneficiary->agency->name }}"
                        data-classification="{{ $beneficiary->classification }}"
                        {{ old('beneficiary_id', $isEdit ? $directAssistance->beneficiary_id : '') == $beneficiary->id ? 'selected' : '' }}>
                    {{ $beneficiary->full_name }} - {{ $beneficiary->barangay->name ?? 'N/A' }} ({{ $beneficiary->agency->name ?? 'N/A' }})
                </option>
            @endforeach
        </select>
        @if($isEdit)
            <input type="hidden" name="beneficiary_id" value="{{ $directAssistance->beneficiary_id }}">
            <small class="text-muted d-block mt-2">
                <strong>Agency:</strong> {{ $selectedBeneficiary?->agency->name ?? 'N/A' }} |
                <strong>Classification:</strong> {{ $selectedBeneficiary?->classification ?? 'N/A' }}
            </small>
        @endif
        @error('beneficiary_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <!-- Program Selection Section (Collapsible) -->
    <div class="col-12">
        <div class="card border-0 bg-light">
            <div class="card-header bg-white">
                <button type="button"
                        class="btn btn-link text-start w-100 p-0 text-decoration-none fw-semibold"
                        data-bs-toggle="collapse"
                        data-bs-target="#assistanceDetailsSection">
                    <i class="bi bi-chevron-down me-2"></i>
                    <span id="toggleText">Add Direct Assistance Details</span>
                </button>
            </div>
            <div id="assistanceDetailsSection" class="collapse {{ old('program_name_id') || $isEdit ? 'show' : '' }}">
                <div class="card-body row g-3">

                    <!-- Program Selection -->
                    <div class="col-md-6">
                        <label class="form-label">Program <span class="text-danger">*</span></label>
                        <select class="form-select @error('program_name_id') is-invalid @enderror"
                                name="program_name_id"
                                id="programSelect"
                                required>
                            <option value="" selected disabled>Select Program</option>
                            @if($isEdit && $selectedProgram)
                                <option value="{{ $selectedProgram->id }}" selected>
                                    {{ $selectedProgram->name }} - {{ $selectedProgram->agency->name ?? 'N/A' }}
                                </option>
                            @else
                                @foreach($programs ?? [] as $program)
                                    <option value="{{ $program->id }}" {{ old('program_name_id') == $program->id ? 'selected' : '' }}>
                                        {{ $program->name }} - {{ $program->agency->name ?? 'N/A' }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <small class="text-muted d-block mt-2">Programs filtered by beneficiary's agency and classification</small>
                        @error('program_name_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <!-- Resource Type Selection -->
                    <div class="col-md-6">
                        <label class="form-label">Resource Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('resource_type_id') is-invalid @enderror"
                                name="resource_type_id"
                                id="resourceTypeSelect"
                                required>
                            <option value="" selected disabled>Select Resource Type</option>
                            @foreach($resourceTypes ?? collected([]) as $resource)
                                <option value="{{ $resource->id }}"
                                        data-unit="{{ $resource->unit }}"
                                        {{ old('resource_type_id', $isEdit ? $directAssistance->resource_type_id : '') == $resource->id ? 'selected' : '' }}>
                                    {{ $resource->name }} ({{ $resource->unit }})
                                </option>
                            @endforeach
                        </select>
                        @error('resource_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <!-- Quantity Field -->
                    <div class="col-md-4" id="quantityGroup" style="display: none;">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number"
                               step="0.01"
                               min="0.01"
                               class="form-control @error('quantity') is-invalid @enderror"
                               name="quantity"
                               id="quantityInput"
                               value="{{ old('quantity', $isEdit ? $directAssistance->quantity : '') }}"
                               placeholder="e.g., 10">
                        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <!-- Amount Field -->
                    <div class="col-md-4" id="amountGroup" style="display: none;">
                        <label class="form-label">Amount (PHP) <span class="text-danger">*</span></label>
                        <input type="number"
                               step="0.01"
                               min="1"
                               class="form-control @error('amount') is-invalid @enderror"
                               name="amount"
                               id="amountInput"
                               value="{{ old('amount', $isEdit ? $directAssistance->amount : '') }}"
                               placeholder="e.g., 5000">
                        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <!-- Assistance Purpose -->
                    <div class="col-md-4">
                        <label class="form-label">Purpose (Optional)</label>
                        <select class="form-select @error('assistance_purpose_id') is-invalid @enderror"
                                name="assistance_purpose_id">
                            <option value="">Select Purpose</option>
                            @foreach($assistancePurposes ?? [] as $purpose)
                                <option value="{{ $purpose->id }}" {{ old('assistance_purpose_id', $isEdit ? $directAssistance->assistance_purpose_id : '') == $purpose->id ? 'selected' : '' }}>
                                    {{ $purpose->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('assistance_purpose_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <!-- Remarks -->
                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control @error('remarks') is-invalid @enderror"
                                  name="remarks"
                                  rows="2"
                                  maxlength="500">{{ old('remarks', $isEdit ? $directAssistance->remarks : '') }}</textarea>
                        <small class="text-muted">{{ strlen(old('remarks', $isEdit ? $directAssistance->remarks : '')) }}/500</small>
                        @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <!-- Distribution Event Linking (Optional) -->
                    <div class="col-12">
                        <label class="form-label">Link to Distribution Event (Optional)</label>
                        <select class="form-select @error('distribution_event_id') is-invalid @enderror"
                                name="distribution_event_id">
                            <option value="">Not linked to any event</option>
                            @foreach($distributionEvents ?? [] as $event)
                                <option value="{{ $event->id }}" {{ old('distribution_event_id', $isEdit ? $directAssistance->distribution_event_id : '') == $event->id ? 'selected' : '' }}>
                                    {{ $event->resourceType->name ?? 'N/A' }} - {{ $event->barangay->name ?? 'N/A' }} ({{ $event->distribution_date->format('M d, Y') }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">Optional: Link this assistance to a batch distribution event</small>
                        @error('distribution_event_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Distribution Tracking (Show on Edit) -->
    @if($isEdit && $directAssistance->distributed_at)
        <div class="col-12">
            <div class="alert alert-info border-0">
                <strong>Distribution Information</strong><br>
                <small>
                    Distributed on: <strong>{{ $directAssistance->distributed_at->format('M d, Y H:i') }}</strong><br>
                    By: <strong>{{ $directAssistance->distributedBy->name ?? 'N/A' }}</strong><br>
                    Release Outcome: <strong>{{ ucfirst(str_replace('_', ' ', $directAssistance->release_outcome ?? 'N/A')) }}</strong>
                </small>
            </div>
        </div>
    @endif

    <!-- Submit Button -->
    <div class="col-12">
        <button type="submit" class="btn btn-success">
            <i class="bi bi-check2-circle me-1"></i> {{ $isEdit ? 'Update' : 'Save' }} Direct Assistance
        </button>
        <a href="{{ $isEdit ? route('direct-assistance.show', $directAssistance) : route('direct-assistance.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-x-lg me-1"></i> Cancel
        </a>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const beneficiarySelect = document.getElementById('beneficiarySelect');
        const programSelect = document.getElementById('programSelect');
        const resourceTypeSelect = document.getElementById('resourceTypeSelect');
        const quantityGroup = document.getElementById('quantityGroup');
        const amountGroup = document.getElementById('amountGroup');
        const quantityInput = document.getElementById('quantityInput');
        const amountInput = document.getElementById('amountInput');

        // Toggle quantity/amount based on resource type
        function updateAmountQuantityDisplay() {
            const selected = resourceTypeSelect.options[resourceTypeSelect.selectedIndex];
            if (!selected || !selected.value) {
                quantityGroup.style.display = 'none';
                amountGroup.style.display = 'none';
                return;
            }

            const unit = selected.dataset.unit;
            if (unit === 'PHP') {
                quantityGroup.style.display = 'none';
                amountGroup.style.display = 'block';
                quantityInput.required = false;
                amountInput.required = true;
                quantityInput.value = '';
            } else {
                quantityGroup.style.display = 'block';
                amountGroup.style.display = 'none';
                amountInput.required = false;
                quantityInput.required = true;
                amountInput.value = '';
            }
        }

        resourceTypeSelect.addEventListener('change', updateAmountQuantityDisplay);

        // Load eligible programs when beneficiary changes
        beneficiarySelect.addEventListener('change', async function() {
            if (!this.value) {
                programSelect.innerHTML = '<option value="" selected disabled>Select Program</option>';
                updateAmountQuantityDisplay();
                return;
            }

            try {
                const response = await fetch(`/api/eligible-programs/${this.value}`);
                const programs = await response.json();

                let html = '<option value="" selected disabled>Select Program</option>';
                programs.forEach(program => {
                    html += `<option value="${program.id}">${program.name} - ${program.agency?.name ?? 'N/A'}</option>`;
                });
                programSelect.innerHTML = html;
            } catch (error) {
                console.error('Error loading programs:', error);
            }
        });

        // Initialize display
        updateAmountQuantityDisplay();
    });
</script>

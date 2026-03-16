{{-- $beneficiary is null on create, populated on edit --}}
@php
    $editing = isset($beneficiary);
    $fo = $fieldOptions ?? [];
@endphp

{{-- SECTION 1 — Personal Information --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-person me-1"></i> Personal Information
    </div>
    <div class="card-body">
        <div class="row g-3">
            {{-- Full Name --}}
            <div class="col-md-6">
                <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control @error('full_name') is-invalid @enderror"
                       id="full_name" name="full_name"
                       value="{{ old('full_name', $beneficiary->full_name ?? '') }}" required>
                @error('full_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Civil Status --}}
            <div class="col-md-3">
                <label for="civil_status" class="form-label">Civil Status <span class="text-danger">*</span></label>
                <select class="form-select @error('civil_status') is-invalid @enderror"
                        id="civil_status" name="civil_status" required>
                    <option value="" disabled {{ old('civil_status', $beneficiary->civil_status ?? '') === '' ? 'selected' : '' }}>Select...</option>
                    @foreach(($fo['civil_status'] ?? collect(['Single', 'Married', 'Widowed', 'Separated'])->map(fn($v) => (object)['value' => $v, 'label' => $v])) as $opt)
                        <option value="{{ $opt->value ?? $opt }}" {{ old('civil_status', $beneficiary->civil_status ?? '') == ($opt->value ?? $opt) ? 'selected' : '' }}>{{ $opt->label ?? $opt }}</option>
                    @endforeach
                </select>
                @error('civil_status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Highest Education --}}
            <div class="col-md-3">
                <label for="highest_education" class="form-label">Highest Education <span class="text-danger">*</span></label>
                <select class="form-select @error('highest_education') is-invalid @enderror"
                        id="highest_education" name="highest_education" required>
                    <option value="" disabled {{ old('highest_education', $beneficiary->highest_education ?? '') === '' ? 'selected' : '' }}>Select...</option>
                    @foreach(($fo['highest_education'] ?? collect(['No Formal Education', 'Elementary', 'High School', 'Vocational', 'College', 'Post Graduate'])->map(fn($v) => (object)['value' => $v, 'label' => $v])) as $opt)
                        <option value="{{ $opt->value ?? $opt }}" {{ old('highest_education', $beneficiary->highest_education ?? '') == ($opt->value ?? $opt) ? 'selected' : '' }}>{{ $opt->label ?? $opt }}</option>
                    @endforeach
                </select>
                @error('highest_education')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Number of Dependents --}}
            <div class="col-md-3">
                <label for="number_of_dependents" class="form-label">Number of Dependents <span class="text-danger">*</span></label>
                <input type="number"
                       class="form-control @error('number_of_dependents') is-invalid @enderror"
                       id="number_of_dependents" name="number_of_dependents"
                       value="{{ old('number_of_dependents', $beneficiary->number_of_dependents ?? '') }}" min="0" required>
                @error('number_of_dependents')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Contact Number --}}
            <div class="col-md-3">
                <label for="contact_number" class="form-label">Contact Number <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control @error('contact_number') is-invalid @enderror"
                       id="contact_number" name="contact_number"
                       placeholder="09XXXXXXXXX"
                       value="{{ old('contact_number', $beneficiary->contact_number ?? '') }}" required>
                @error('contact_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Main Source of Income --}}
            <div class="col-md-6">
                <label for="main_income_source" class="form-label">Main Source of Income <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control @error('main_income_source') is-invalid @enderror"
                       id="main_income_source" name="main_income_source"
                       value="{{ old('main_income_source', $beneficiary->main_income_source ?? '') }}" required>
                @error('main_income_source')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

{{-- SECTION 2 — Location & Registration --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-geo-alt me-1"></i> Location &amp; Registration
    </div>
    <div class="card-body">
        <div class="row g-3">
            {{-- Barangay --}}
            <div class="col-md-4">
                <label for="barangay_id" class="form-label">Barangay <span class="text-danger">*</span></label>
                <select class="form-select @error('barangay_id') is-invalid @enderror"
                        id="barangay_id" name="barangay_id" required>
                    <option value="" disabled {{ old('barangay_id', $beneficiary->barangay_id ?? '') === '' ? 'selected' : '' }}>Select barangay...</option>
                    @foreach($barangays as $barangay)
                        <option value="{{ $barangay->id }}" {{ (int) old('barangay_id', $beneficiary->barangay_id ?? '') === $barangay->id ? 'selected' : '' }}>{{ $barangay->name }}</option>
                    @endforeach
                </select>
                @error('barangay_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Classification --}}
            <div class="col-md-4">
                <label for="classification" class="form-label">Classification <span class="text-danger">*</span></label>
                <select class="form-select @error('classification') is-invalid @enderror"
                        id="classification" name="classification" required>
                    <option value="" disabled {{ old('classification', $beneficiary->classification ?? '') === '' ? 'selected' : '' }}>Select...</option>
                    @foreach(['Farmer', 'Fisherfolk', 'Both'] as $type)
                        <option value="{{ $type }}" {{ old('classification', $beneficiary->classification ?? '') === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
                @error('classification')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Government ID Type --}}
            <div class="col-md-4">
                <label for="id_type" class="form-label">Government ID Type <span class="text-danger">*</span></label>
                <select class="form-select @error('id_type') is-invalid @enderror"
                        id="id_type" name="id_type" required>
                    <option value="" disabled {{ old('id_type', $beneficiary->id_type ?? '') === '' ? 'selected' : '' }}>Select...</option>
                    @foreach(($fo['id_type'] ?? collect(['PhilSys ID', "Voter's ID", "Driver's License", 'Passport', 'Senior Citizen ID', 'PWD ID', 'Postal ID', 'TIN ID'])->map(fn($v) => (object)['value' => $v, 'label' => $v])) as $opt)
                        <option value="{{ $opt->value ?? $opt }}" {{ old('id_type', $beneficiary->id_type ?? '') == ($opt->value ?? $opt) ? 'selected' : '' }}>{{ $opt->label ?? $opt }}</option>
                    @endforeach
                </select>
                @error('id_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Government ID Number --}}
            <div class="col-md-4">
                <label for="government_id" class="form-label">Government ID Number <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control @error('government_id') is-invalid @enderror"
                       id="government_id" name="government_id"
                       value="{{ old('government_id', $beneficiary->government_id ?? '') }}"
                       {{ $editing ? 'readonly' : '' }} required>
                @error('government_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Registration Date --}}
            <div class="col-md-4">
                <label for="registered_at" class="form-label">Registration Date <span class="text-danger">*</span></label>
                <input type="date"
                       class="form-control @error('registered_at') is-invalid @enderror"
                       id="registered_at" name="registered_at"
                       value="{{ old('registered_at', isset($beneficiary) ? $beneficiary->registered_at->format('Y-m-d') : '') }}" required>
                @error('registered_at')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Status --}}
            <div class="col-md-2">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select @error('status') is-invalid @enderror"
                        id="status" name="status" required>
                    @foreach(['Active', 'Inactive'] as $s)
                        <option value="{{ $s }}" {{ old('status', $beneficiary->status ?? 'Active') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Household Size --}}
            <div class="col-md-2">
                <label for="household_size" class="form-label">Household Size <span class="text-danger">*</span></label>
                <input type="number"
                       class="form-control @error('household_size') is-invalid @enderror"
                       id="household_size" name="household_size"
                       value="{{ old('household_size', $beneficiary->household_size ?? '') }}" min="1" max="20" required>
                @error('household_size')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

{{-- SECTION 3 — Farmer Information (toggle via JS) --}}
<div class="card border-0 shadow-sm mb-4" id="farmer-section" style="display: none;">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-tree me-1"></i> Farmer Information
    </div>
    <div class="card-body">
        <div class="row g-3">
            {{-- RSBSA Number --}}
            <div class="col-md-4">
                <label for="rsbsa_number" class="form-label">RSBSA Number <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control @error('rsbsa_number') is-invalid @enderror"
                       id="rsbsa_number" name="rsbsa_number"
                       value="{{ old('rsbsa_number', $beneficiary->rsbsa_number ?? '') }}">
                @error('rsbsa_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Farm Ownership --}}
            <div class="col-md-4">
                <label for="farm_ownership" class="form-label">Farm Ownership <span class="text-danger">*</span></label>
                <select class="form-select @error('farm_ownership') is-invalid @enderror"
                        id="farm_ownership" name="farm_ownership">
                    <option value="" disabled {{ old('farm_ownership', $beneficiary->farm_ownership ?? '') === '' ? 'selected' : '' }}>Select...</option>
                    @foreach(($fo['farm_ownership'] ?? collect(['Owner', 'Lessee', 'Share Tenant'])->map(fn($v) => (object)['value' => $v, 'label' => $v])) as $opt)
                        <option value="{{ $opt->value ?? $opt }}" {{ old('farm_ownership', $beneficiary->farm_ownership ?? '') == ($opt->value ?? $opt) ? 'selected' : '' }}>{{ $opt->label ?? $opt }}</option>
                    @endforeach
                </select>
                @error('farm_ownership')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Farm Size --}}
            <div class="col-md-4">
                <label for="farm_size_hectares" class="form-label">Farm Size (Hectares) <span class="text-danger">*</span></label>
                <input type="number"
                       class="form-control @error('farm_size_hectares') is-invalid @enderror"
                       id="farm_size_hectares" name="farm_size_hectares"
                       value="{{ old('farm_size_hectares', $beneficiary->farm_size_hectares ?? '') }}" step="0.01" min="0.01">
                @error('farm_size_hectares')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Primary Commodity --}}
            <div class="col-md-6">
                <label for="primary_commodity" class="form-label">Primary Commodity <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control @error('primary_commodity') is-invalid @enderror"
                       id="primary_commodity" name="primary_commodity"
                       placeholder="e.g. Rice, Corn, Vegetables"
                       value="{{ old('primary_commodity', $beneficiary->primary_commodity ?? '') }}">
                @error('primary_commodity')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Farm Type --}}
            <div class="col-md-6">
                <label for="farm_type" class="form-label">Farm Type <span class="text-danger">*</span></label>
                <select class="form-select @error('farm_type') is-invalid @enderror"
                        id="farm_type" name="farm_type">
                    <option value="" disabled {{ old('farm_type', $beneficiary->farm_type ?? '') === '' ? 'selected' : '' }}>Select...</option>
                    @foreach(($fo['farm_type'] ?? collect(['Irrigated', 'Rainfed Lowland', 'Upland'])->map(fn($v) => (object)['value' => $v, 'label' => $v])) as $opt)
                        <option value="{{ $opt->value ?? $opt }}" {{ old('farm_type', $beneficiary->farm_type ?? '') == ($opt->value ?? $opt) ? 'selected' : '' }}>{{ $opt->label ?? $opt }}</option>
                    @endforeach
                </select>
                @error('farm_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

{{-- SECTION 4 — Fisherfolk Information (toggle via JS) --}}
<div class="card border-0 shadow-sm mb-4" id="fisherfolk-section" style="display: none;">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-water me-1"></i> Fisherfolk Information
    </div>
    <div class="card-body">
        <div class="row g-3">
            {{-- FishR Number --}}
            <div class="col-md-4">
                <label for="fishr_number" class="form-label">FishR Number <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control @error('fishr_number') is-invalid @enderror"
                       id="fishr_number" name="fishr_number"
                       value="{{ old('fishr_number', $beneficiary->fishr_number ?? '') }}">
                @error('fishr_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Fisherfolk Type --}}
            <div class="col-md-4">
                <label for="fisherfolk_type" class="form-label">Fisherfolk Type <span class="text-danger">*</span></label>
                <select class="form-select @error('fisherfolk_type') is-invalid @enderror"
                        id="fisherfolk_type" name="fisherfolk_type">
                    <option value="" disabled {{ old('fisherfolk_type', $beneficiary->fisherfolk_type ?? '') === '' ? 'selected' : '' }}>Select...</option>
                    @foreach(($fo['fisherfolk_type'] ?? collect(['Capture Fishing', 'Fish Farming', 'Fish Vendor', 'Fish Worker'])->map(fn($v) => (object)['value' => $v, 'label' => $v])) as $opt)
                        <option value="{{ $opt->value ?? $opt }}" {{ old('fisherfolk_type', $beneficiary->fisherfolk_type ?? '') == ($opt->value ?? $opt) ? 'selected' : '' }}>{{ $opt->label ?? $opt }}</option>
                    @endforeach
                </select>
                @error('fisherfolk_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Main Fishing Gear --}}
            <div class="col-md-4">
                <label for="main_fishing_gear" class="form-label">Main Fishing Gear</label>
                <input type="text"
                       class="form-control @error('main_fishing_gear') is-invalid @enderror"
                       id="main_fishing_gear" name="main_fishing_gear"
                       value="{{ old('main_fishing_gear', $beneficiary->main_fishing_gear ?? '') }}">
                @error('main_fishing_gear')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Has Fishing Vessel --}}
            <div class="col-md-4">
                <div class="form-check mt-4">
                    <input type="hidden" name="has_fishing_vessel" value="0">
                    <input type="checkbox"
                           class="form-check-input @error('has_fishing_vessel') is-invalid @enderror"
                           id="has_fishing_vessel" name="has_fishing_vessel" value="1"
                           {{ old('has_fishing_vessel', $beneficiary->has_fishing_vessel ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="has_fishing_vessel">Has Fishing Vessel</label>
                    @error('has_fishing_vessel')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 5 — Association & Emergency Contact --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-shield-check me-1"></i> Association &amp; Emergency Contact
    </div>
    <div class="card-body">
        <div class="row g-3">
            {{-- Association Member --}}
            <div class="col-md-6">
                <div class="form-check form-switch mt-2">
                    <input type="hidden" name="association_member" value="0">
                    <input type="checkbox"
                           class="form-check-input @error('association_member') is-invalid @enderror"
                           id="association_member" name="association_member" value="1"
                           {{ old('association_member', $beneficiary->association_member ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="association_member">Association Member</label>
                    @error('association_member')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Association Name (toggle via JS) --}}
            <div class="col-md-6" id="association-name-wrapper" style="display: none;">
                <label for="association_name" class="form-label">Association Name <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control @error('association_name') is-invalid @enderror"
                       id="association_name" name="association_name"
                       value="{{ old('association_name', $beneficiary->association_name ?? '') }}">
                @error('association_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Emergency Contact Name --}}
            <div class="col-md-6">
                <label for="emergency_contact_name" class="form-label">Emergency Contact Name <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control @error('emergency_contact_name') is-invalid @enderror"
                       id="emergency_contact_name" name="emergency_contact_name"
                       value="{{ old('emergency_contact_name', $beneficiary->emergency_contact_name ?? '') }}" required>
                @error('emergency_contact_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Emergency Contact Number --}}
            <div class="col-md-6">
                <label for="emergency_contact_number" class="form-label">Emergency Contact Number <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control @error('emergency_contact_number') is-invalid @enderror"
                       id="emergency_contact_number" name="emergency_contact_number"
                       placeholder="09XXXXXXXXX"
                       value="{{ old('emergency_contact_number', $beneficiary->emergency_contact_number ?? '') }}" required>
                @error('emergency_contact_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

{{-- Submit / Cancel --}}
<div class="d-flex gap-2">
    <button type="submit" class="btn {{ $editing ? 'btn-primary' : 'btn-success' }}">
        <i class="bi bi-check-lg me-1"></i> {{ $editing ? 'Update Beneficiary' : 'Register Beneficiary' }}
    </button>
    <a href="{{ route('beneficiaries.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const classification       = document.getElementById('classification');
        const farmerSection        = document.getElementById('farmer-section');
        const fisherfolkSection    = document.getElementById('fisherfolk-section');
        const associationCheckbox  = document.getElementById('association_member');
        const associationWrapper   = document.getElementById('association-name-wrapper');

        function toggleSections() {
            const val = classification.value;
            farmerSection.style.display     = (val === 'Farmer' || val === 'Both') ? '' : 'none';
            fisherfolkSection.style.display  = (val === 'Fisherfolk' || val === 'Both') ? '' : 'none';
        }

        function toggleAssociation() {
            associationWrapper.style.display = associationCheckbox.checked ? '' : 'none';
        }

        classification.addEventListener('change', toggleSections);
        associationCheckbox.addEventListener('change', toggleAssociation);

        // Run on page load to restore state from old() / $beneficiary
        toggleSections();
        toggleAssociation();
    });
</script>
@endpush

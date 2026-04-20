{{-- $beneficiary is null on create, populated on edit --}}
@php
    $editing = isset($beneficiary);
    $fo = $fieldOptions ?? [];
    $fieldGroupSettings = $fieldGroupSettings ?? [];
    $beneficiaryCustomFields = (array) (($beneficiary->custom_fields ?? []) ?: []);
    $beneficiaryDynamicAgencyValues = (array) ($beneficiaryCustomFields['agency_dynamic'] ?? []);
    $beneficiaryReasonMap = $editing ? (array) (($beneficiary->custom_field_unavailability_reasons ?? []) ?: []) : [];
    $beneficiaryDynamicAgencyReasons = (array) ($beneficiaryReasonMap['agency_dynamic'] ?? []);

    $placementLabels = [
        'personal_information' => 'Agency & Personal Information',
        'farmer_information' => 'DA/RSBSA Information (Farmer)',
        'fisherfolk_information' => 'BFAR/FishR Information (Fisherfolk)',
        'dar_information' => 'DAR/ARB Information',
    ];

    $nativeFieldGroups = [
        'civil_status',
        'highest_education',
        'id_type',
        'farm_ownership',
        'farm_type',
        'fisherfolk_type',
        'arb_classification',
        'ownership_scheme',
    ];

    $getGroupSetting = function (string $fieldGroup, string $setting, $fallback = null) use ($fieldGroupSettings) {
        if (! array_key_exists($fieldGroup, $fieldGroupSettings)) {
            return $fallback;
        }

        return $fieldGroupSettings[$fieldGroup][$setting] ?? $fallback;
    };

    $isGroupRequired = function (string $fieldGroup, bool $fallback = false) use ($getGroupSetting) {
        return (bool) $getGroupSetting($fieldGroup, 'is_required', $fallback);
    };

    $normalizeFieldOptions = function ($items, array $fallback) {
        if (empty($items) || (is_countable($items) && count($items) === 0)) {
            return collect($fallback)->map(fn ($value) => (object) ['value' => $value, 'label' => $value]);
        }

        return collect($items)
            ->map(function ($item) {
                $value = is_object($item) ? ($item->value ?? $item->label ?? '') : ($item['value'] ?? $item['label'] ?? '');
                $label = is_object($item) ? ($item->label ?? $item->value ?? '') : ($item['label'] ?? $item['value'] ?? '');

                return (object) ['value' => $value, 'label' => $label];
            })
            ->filter(fn ($item) => trim((string) $item->value) !== '' || trim((string) $item->label) !== '')
            ->unique(function ($item) {
                $normalizedLabel = strtolower(preg_replace('/\s+/', ' ', trim((string) $item->label)));
                $normalizedValue = strtolower(preg_replace('/\s+/', ' ', trim((string) $item->value)));

                return $normalizedLabel !== '' ? $normalizedLabel : $normalizedValue;
            })
            ->values();
    };

    $customFieldGroups = collect($fo)
        ->filter(fn ($items, $group) => ! in_array($group, $nativeFieldGroups, true))
        ->map(function ($items, $group) use ($normalizeFieldOptions, $getGroupSetting) {
            $placement = $getGroupSetting($group, 'placement_section', 'personal_information');
            $fieldType = strtolower((string) $getGroupSetting($group, 'field_type', \App\Models\FormFieldOption::FIELD_TYPE_DROPDOWN));
            $isOptionBased = in_array($fieldType, \App\Models\FormFieldOption::optionBasedFieldTypes(), true);
            $options = $normalizeFieldOptions($items, []);

            return [
                'field_group' => $group,
                'label' => Str::title(str_replace('_', ' ', $group)),
                'field_type' => $fieldType,
                'is_option_based' => $isOptionBased,
                'placement_section' => $placement,
                'placement_label' => [
                    'personal_information' => 'Agency & Personal Information',
                    'farmer_information' => 'DA/RSBSA Information (Farmer)',
                    'fisherfolk_information' => 'BFAR/FishR Information (Fisherfolk)',
                    'dar_information' => 'DAR/ARB Information',
                ][$placement] ?? Str::title(str_replace('_', ' ', $placement)),
                'is_required' => (bool) $getGroupSetting($group, 'is_required', false),
                'options' => $options,
            ];
        })
        ->filter(fn ($config) => $config['is_option_based'] ? $config['options']->isNotEmpty() : true)
        ->sortBy('label')
        ->groupBy('placement_section');

    $civilStatusRequired = $isGroupRequired('civil_status', true);
    $highestEducationRequired = $isGroupRequired('highest_education', false);
    $farmOwnershipRequired = $isGroupRequired('farm_ownership', true);
    $farmTypeRequired = $isGroupRequired('farm_type', true);
    $fisherfolkTypeRequired = $isGroupRequired('fisherfolk_type', true);
    $arbClassificationRequired = $isGroupRequired('arb_classification', true);
    $ownershipSchemeRequired = $isGroupRequired('ownership_scheme', true);

    $civilStatusOptions = $normalizeFieldOptions($fo['civil_status'] ?? [], ['Single', 'Married', 'Widowed', 'Separated']);
    $highestEducationOptions = $normalizeFieldOptions($fo['highest_education'] ?? [], [
        'No Formal Education',
        'Elementary',
        'High School',
        'Vocational',
        'College',
        'Post Graduate',
    ]);
    $farmOwnershipOptions = $normalizeFieldOptions($fo['farm_ownership'] ?? [], ['Registered Owner', 'Tenant', 'Lessee']);
    $farmTypeOptions = $normalizeFieldOptions($fo['farm_type'] ?? [], ['Irrigated', 'Rainfed Upland', 'Rainfed Lowland']);
    $fisherfolkTypeOptions = $normalizeFieldOptions($fo['fisherfolk_type'] ?? [], ['Capture Fishing', 'Aquaculture', 'Post-Harvest']);
    $arbClassificationOptions = $normalizeFieldOptions($fo['arb_classification'] ?? [], [
        'Agricultural Lessee',
        'Regular Farmworker',
        'Seasonal Farmworker',
        'Other Farmworker',
        'Actual Tiller',
        'Collective/Cooperative',
        'Others',
    ]);
    $ownershipSchemeOptions = $normalizeFieldOptions($fo['ownership_scheme'] ?? [], ['Individual', 'Collective', 'Cooperative']);
    $cloaAvailabilityStatus = old(
        'cloa_ep_availability_status',
        filled($beneficiary->cloa_ep_number ?? null)
            ? 'provided'
            : (filled($beneficiary->cloa_ep_unavailability_reason ?? null) ? 'not_available_yet' : 'provided')
    );

    $firstNameValue = old('first_name', $beneficiary->first_name ?? '');
    $middleNameValue = old('middle_name', $beneficiary->middle_name ?? '');
    $lastNameValue = old('last_name', $beneficiary->last_name ?? '');
    $nameSuffixValue = old('name_suffix', $beneficiary->name_suffix ?? '');

    if ($editing && (blank($firstNameValue) || blank($lastNameValue)) && ! blank($beneficiary->full_name ?? null)) {
        $nameParts = preg_split('/\s+/', trim((string) $beneficiary->full_name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($nameParts) > 1) {
            $lastToken = end($nameParts);
            $normalizedSuffix = strtolower(str_replace('.', '', (string) $lastToken));
            $suffixMap = [
                'jr' => 'Jr.',
                'sr' => 'Sr.',
                'ii' => 'II',
                'iii' => 'III',
                'iv' => 'IV',
                'v' => 'V',
            ];

            if (array_key_exists($normalizedSuffix, $suffixMap)) {
                array_pop($nameParts);
                $nameSuffixValue = $nameSuffixValue ?: $suffixMap[$normalizedSuffix];
            }
        }

        if (count($nameParts) > 0) {
            $firstNameValue = $firstNameValue ?: array_shift($nameParts);
            $lastNameValue = $lastNameValue ?: (count($nameParts) ? array_pop($nameParts) : '');
            $middleNameValue = $middleNameValue ?: implode(' ', $nameParts);
        }
    }
@endphp

{{-- SECTION 1 — Agency & Personal Information --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-building me-1"></i> Agency & Personal Information
    </div>
    <div class="card-body">
        <div class="row g-3">
            {{-- Agency Selection (Multi-Select) --}}
            <div class="col-12">
                <label class="form-label">Source Agencies <span class="text-danger">*</span></label>
                <div id="agency-checkboxes" class="mb-3">
                    {{-- Populated dynamically based on classification --}}
                    {{-- Farmer classification shows: DA, DAR --}}
                    {{-- Fisherfolk classification shows: DA, BFAR --}}
                </div>
                <small class="text-muted">Select all agencies this beneficiary is registered under</small>
                @error('agencies')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            {{-- Classification --}}
            <div class="col-12 col-md-4">
                <label for="classification" class="form-label">Classification <span class="text-danger">*</span></label>
                <select class="form-select @error('classification') is-invalid @enderror"
                        id="classification" name="classification" required>
                    <option value="" disabled {{ old('classification', $beneficiary->classification ?? '') === '' ? 'selected' : '' }}>Select...</option>
                    @foreach(['Farmer', 'Fisherfolk'] as $type)
                        <option value="{{ $type }}" {{ old('classification', $beneficiary->classification ?? '') === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
                @error('classification')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Status --}}
            <div class="col-12 col-md-4">
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

            {{-- Name Fields --}}
            <div class="col-12 col-md-3">
                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                       id="first_name" name="first_name"
                       value="{{ $firstNameValue }}" required>
                @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-3">
                <label for="middle_name" class="form-label">Middle Name</label>
                <input type="text" class="form-control @error('middle_name') is-invalid @enderror"
                       id="middle_name" name="middle_name"
                       value="{{ $middleNameValue }}">
                @error('middle_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-3">
                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                       id="last_name" name="last_name"
                       value="{{ $lastNameValue }}" required>
                @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-3">
                <label for="name_suffix" class="form-label">Name Extension</label>
                <input type="text" class="form-control @error('name_suffix') is-invalid @enderror"
                       id="name_suffix" name="name_suffix"
                       value="{{ $nameSuffixValue }}" placeholder="Jr., Sr., III">
                @error('name_suffix')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Sex --}}
            <div class="col-12 col-md-3">
                <label for="sex" class="form-label">Sex <span class="text-danger">*</span></label>
                <select class="form-select @error('sex') is-invalid @enderror" id="sex" name="sex" required>
                    <option value="" disabled {{ old('sex', $beneficiary->sex ?? '') === '' ? 'selected' : '' }}>Select...</option>
                    @foreach(['Male', 'Female'] as $opt)
                        <option value="{{ $opt }}" {{ old('sex', $beneficiary->sex ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                </select>
                @error('sex')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Date of Birth --}}
            <div class="col-12 col-md-3">
                <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                       id="date_of_birth" name="date_of_birth"
                       value="{{ old('date_of_birth', isset($beneficiary) && $beneficiary->date_of_birth ? $beneficiary->date_of_birth->format('Y-m-d') : '') }}" required>
                @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Civil Status --}}
            <div class="col-12 col-md-3">
                <label for="civil_status" class="form-label">Civil Status {!! $civilStatusRequired ? '<span class="text-danger">*</span>' : '' !!}</label>
                <select class="form-select @error('civil_status') is-invalid @enderror" id="civil_status" name="civil_status" {{ $civilStatusRequired ? 'required' : '' }}>
                    <option value="" disabled {{ old('civil_status', $beneficiary->civil_status ?? '') === '' ? 'selected' : '' }}>Select...</option>
                    @foreach($civilStatusOptions as $opt)
                        <option value="{{ $opt->value }}" {{ old('civil_status', $beneficiary->civil_status ?? '') === $opt->value ? 'selected' : '' }}>{{ $opt->label }}</option>
                    @endforeach
                </select>
                @error('civil_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Highest Education --}}
            <div class="col-12 col-md-3">
                <label for="highest_education" class="form-label">Highest Education {!! $highestEducationRequired ? '<span class="text-danger">*</span>' : '' !!}</label>
                <select class="form-select @error('highest_education') is-invalid @enderror" id="highest_education" name="highest_education" {{ $highestEducationRequired ? 'required' : '' }}>
                    <option value="" {{ old('highest_education', $beneficiary->highest_education ?? '') === '' ? 'selected' : '' }}>Select...</option>
                    @foreach($highestEducationOptions as $opt)
                        <option value="{{ $opt->value }}" {{ old('highest_education', $beneficiary->highest_education ?? '') === $opt->value ? 'selected' : '' }}>{{ $opt->label }}</option>
                    @endforeach
                </select>
                @error('highest_education')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Contact Number --}}
            <div class="col-12 col-md-3">
                <label for="contact_number" class="form-label">Contact Number <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('contact_number') is-invalid @enderror"
                       id="contact_number" name="contact_number" placeholder="09XXXXXXXXX or +639XXXXXXXXX"
                       value="{{ old('contact_number', $beneficiary->contact_number ?? '') }}" required>
                @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">Accepted: 09XXXXXXXXX, 9XXXXXXXXX, 639XXXXXXXXX, +639XXXXXXXXX</small>
            </div>



            {{-- Registration Date --}}
            <div class="col-12 col-md-3">
                <label for="registered_at" class="form-label">Registration Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('registered_at') is-invalid @enderror"
                       id="registered_at" name="registered_at"
                       value="{{ old('registered_at', isset($beneficiary) ? $beneficiary->registered_at->format('Y-m-d') : '') }}" required>
                @error('registered_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>



            @foreach($customFieldGroups->get('personal_information', collect()) as $customField)
                @include('beneficiaries.partials.custom-field-input', ['customField' => $customField, 'beneficiaryCustomFields' => $beneficiaryCustomFields])
            @endforeach

            <input type="hidden" name="photo_path" value="{{ old('photo_path', $beneficiary->photo_path ?? '') }}">
        </div>
    </div>
</div>

{{-- SECTION 2 — Address Information --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-geo-alt me-1"></i> Address Information</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-8">
                <label for="home_address" class="form-label">Home Address <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('home_address') is-invalid @enderror"
                       id="home_address" name="home_address" placeholder="House No., Street, Purok/Sitio"
                       value="{{ old('home_address', $beneficiary->home_address ?? '') }}" required>
                @error('home_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-4">
                <label for="barangay_id" class="form-label">Barangay <span class="text-danger">*</span></label>
                <select class="form-select @error('barangay_id') is-invalid @enderror" id="barangay_id" name="barangay_id" required>
                    <option value="" disabled {{ old('barangay_id', $beneficiary->barangay_id ?? '') === '' ? 'selected' : '' }}>Select barangay...</option>
                    @foreach($barangays as $barangay)
                        <option value="{{ $barangay->id }}" {{ (int) old('barangay_id', $beneficiary->barangay_id ?? '') === $barangay->id ? 'selected' : '' }}>{{ $barangay->name }}</option>
                    @endforeach
                </select>
                @error('barangay_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

{{-- SECTION 3 — Farmer Information (DA/RSBSA) --}}
<div class="card border-0 shadow-sm mb-4" id="farmer-info-section" style="display: none;">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-leaf me-1"></i> DA/RSBSA Information (Farmer)</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label for="rsbsa_number" class="form-label">RSBSA Number</label>
                <input type="text" class="form-control @error('rsbsa_number') is-invalid @enderror"
                       id="rsbsa_number" name="rsbsa_number" maxlength="50"
                       value="{{ old('rsbsa_number', $beneficiary->rsbsa_number ?? '') }}">
                @error('rsbsa_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="farm_ownership" class="form-label">Farm Ownership</label>
                <select class="form-select @error('farm_ownership') is-invalid @enderror" id="farm_ownership" name="farm_ownership">
                    <option value="">Select...</option>
                    @foreach($fieldOptions['farm_ownership'] ?? ['Registered Owner', 'Tenant', 'Lessee', 'Owner', 'Share Tenant'] as $option)
                        @php
                            if (is_object($option)) {
                                $label = (string) ($option->label ?? $option->value ?? '');
                                $value = (string) ($option->value ?? $option->label ?? '');
                            } elseif (is_array($option)) {
                                $label = (string) ($option['label'] ?? $option['value'] ?? '');
                                $value = (string) ($option['value'] ?? $option['label'] ?? '');
                            } else {
                                $label = (string) $option;
                                $value = (string) $option;
                            }
                        @endphp
                        <option value="{{ $value }}" {{ old('farm_ownership', $beneficiary->farm_ownership ?? '') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('farm_ownership')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="farm_size_hectares" class="form-label">Farm Size (Hectares)</label>
                <input type="number" class="form-control @error('farm_size_hectares') is-invalid @enderror"
                       id="farm_size_hectares" name="farm_size_hectares" step="0.01" min="0"
                       value="{{ old('farm_size_hectares', $beneficiary->farm_size_hectares ?? '') }}">
                @error('farm_size_hectares')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="primary_commodity" class="form-label">Primary Commodity</label>
                <input type="text" class="form-control @error('primary_commodity') is-invalid @enderror"
                       id="primary_commodity" name="primary_commodity" maxlength="255"
                       value="{{ old('primary_commodity', $beneficiary->primary_commodity ?? '') }}">
                @error('primary_commodity')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="farm_type" class="form-label">Farm Type</label>
                <select class="form-select @error('farm_type') is-invalid @enderror" id="farm_type" name="farm_type">
                    <option value="">Select...</option>
                    @foreach($fieldOptions['farm_type'] ?? ['Irrigated', 'Rainfed Upland', 'Rainfed Lowland', 'Upland'] as $option)
                        @php
                            if (is_object($option)) {
                                $label = (string) ($option->label ?? $option->value ?? '');
                                $value = (string) ($option->value ?? $option->label ?? '');
                            } elseif (is_array($option)) {
                                $label = (string) ($option['label'] ?? $option['value'] ?? '');
                                $value = (string) ($option['value'] ?? $option['label'] ?? '');
                            } else {
                                $label = (string) $option;
                                $value = (string) $option;
                            }
                        @endphp
                        <option value="{{ $value }}" {{ old('farm_type', $beneficiary->farm_type ?? '') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('farm_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="organization_membership" class="form-label">Organization Membership</label>
                <input type="text" class="form-control @error('organization_membership') is-invalid @enderror"
                       id="organization_membership" name="organization_membership" maxlength="255"
                       value="{{ old('organization_membership', $beneficiary->organization_membership ?? '') }}">
                @error('organization_membership')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @foreach($customFieldGroups->get('farmer_information', collect()) as $customField)
                @include('beneficiaries.partials.custom-field-input', ['customField' => $customField, 'beneficiaryCustomFields' => $beneficiaryCustomFields])
            @endforeach
        </div>
    </div>
</div>

{{-- SECTION 4 — Fisherfolk Information --}}
<div class="card border-0 shadow-sm mb-4" id="fisherfolk-info-section" style="display: none;">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-water me-1"></i> Fisherfolk Information</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label for="fishr_number" class="form-label">FishR Number</label>
                <input type="text" class="form-control @error('fishr_number') is-invalid @enderror"
                       id="fishr_number" name="fishr_number" maxlength="50"
                       value="{{ old('fishr_number', $beneficiary->fishr_number ?? '') }}">
                @error('fishr_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="fisherfolk_type" class="form-label">Fisherfolk Type</label>
                <select class="form-select @error('fisherfolk_type') is-invalid @enderror" id="fisherfolk_type" name="fisherfolk_type">
                    <option value="">Select...</option>
                    @foreach($fieldOptions['fisherfolk_type'] ?? ['Capture Fishing', 'Aquaculture', 'Post-Harvest', 'Fish Farming', 'Fish Vendor', 'Fish Worker'] as $option)
                        @php
                            if (is_object($option)) {
                                $label = (string) ($option->label ?? $option->value ?? '');
                                $value = (string) ($option->value ?? $option->label ?? '');
                            } elseif (is_array($option)) {
                                $label = (string) ($option['label'] ?? $option['value'] ?? '');
                                $value = (string) ($option['value'] ?? $option['label'] ?? '');
                            } else {
                                $label = (string) $option;
                                $value = (string) $option;
                            }
                        @endphp
                        <option value="{{ $value }}" {{ old('fisherfolk_type', $beneficiary->fisherfolk_type ?? '') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('fisherfolk_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="main_fishing_gear" class="form-label">Main Fishing Gear</label>
                <input type="text" class="form-control @error('main_fishing_gear') is-invalid @enderror"
                       id="main_fishing_gear" name="main_fishing_gear" maxlength="255"
                       value="{{ old('main_fishing_gear', $beneficiary->main_fishing_gear ?? '') }}">
                @error('main_fishing_gear')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="length_of_residency_months" class="form-label">Length of Residency (Months) <span class="text-danger">*</span></label>
                <input type="number" class="form-control @error('length_of_residency_months') is-invalid @enderror"
                       id="length_of_residency_months" name="length_of_residency_months" min="0"
                       value="{{ old('length_of_residency_months', $beneficiary->length_of_residency_months ?? '') }}">
                <small class="text-muted">At least 6 months per RA 8550</small>
                @error('length_of_residency_months')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <div class="form-check form-switch">
                    <input type="hidden" name="has_fishing_vessel" value="0">
                    <input type="checkbox" class="form-check-input" id="has_fishing_vessel" name="has_fishing_vessel" value="1"
                           {{ old('has_fishing_vessel', $beneficiary->has_fishing_vessel ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="has_fishing_vessel">Has Fishing Vessel</label>
                </div>
            </div>
            <div class="col-12 col-md-6" id="vessel-type-wrapper" style="display: none;">
                <label for="fishing_vessel_type" class="form-label">Fishing Vessel Type</label>
                <input type="text" class="form-control @error('fishing_vessel_type') is-invalid @enderror"
                       id="fishing_vessel_type" name="fishing_vessel_type" maxlength="255"
                       value="{{ old('fishing_vessel_type', $beneficiary->fishing_vessel_type ?? '') }}">
                @error('fishing_vessel_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6" id="vessel-tonnage-wrapper" style="display: none;">
                <label for="fishing_vessel_tonnage" class="form-label">Fishing Vessel Tonnage</label>
                <input type="number" class="form-control @error('fishing_vessel_tonnage') is-invalid @enderror"
                       id="fishing_vessel_tonnage" name="fishing_vessel_tonnage" step="0.01" min="0"
                       value="{{ old('fishing_vessel_tonnage', $beneficiary->fishing_vessel_tonnage ?? '') }}">
                @error('fishing_vessel_tonnage')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @foreach($customFieldGroups->get('fisherfolk_information', collect()) as $customField)
                @include('beneficiaries.partials.custom-field-input', ['customField' => $customField, 'beneficiaryCustomFields' => $beneficiaryCustomFields])
            @endforeach
        </div>
    </div>
</div>

{{-- SECTION 5 — DAR Information --}}
<div class="card border-0 shadow-sm mb-4" id="dar-info-section" style="display: none;">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-file-earmark-text me-1"></i> DAR/ARB Information</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label for="cloa_ep_availability_status" class="form-label">CLOA/EP Availability Status <span class="text-danger">*</span></label>
                <select class="form-select @error('cloa_ep_availability_status') is-invalid @enderror"
                        id="cloa_ep_availability_status"
                        name="cloa_ep_availability_status">
                    <option value="provided" {{ $cloaAvailabilityStatus === 'provided' ? 'selected' : '' }}>Provided</option>
                    <option value="not_available_yet" {{ $cloaAvailabilityStatus === 'not_available_yet' ? 'selected' : '' }}>Not available yet</option>
                    <option value="not_applicable" {{ $cloaAvailabilityStatus === 'not_applicable' ? 'selected' : '' }}>Not applicable</option>
                    <option value="to_be_verified" {{ $cloaAvailabilityStatus === 'to_be_verified' ? 'selected' : '' }}>To be verified</option>
                </select>
                @error('cloa_ep_availability_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6 {{ $cloaAvailabilityStatus === 'provided' ? '' : 'd-none' }}" id="cloa-ep-number-wrapper">
                <label for="cloa_ep_number" class="form-label">CLOA/EP Number <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control @error('cloa_ep_number') is-invalid @enderror"
                       id="cloa_ep_number"
                       name="cloa_ep_number"
                       maxlength="100"
                       value="{{ old('cloa_ep_number', $beneficiary->cloa_ep_number ?? '') }}"
                       {{ $cloaAvailabilityStatus === 'provided' ? 'required' : '' }}>
                @error('cloa_ep_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 {{ $cloaAvailabilityStatus === 'provided' ? 'd-none' : '' }}" id="cloa-ep-reason-wrapper">
                <label for="cloa_ep_unavailability_reason" class="form-label">Reason for Unavailability <span class="text-danger">*</span></label>
                <textarea class="form-control @error('cloa_ep_unavailability_reason') is-invalid @enderror"
                          id="cloa_ep_unavailability_reason"
                          name="cloa_ep_unavailability_reason"
                          rows="3"
                          maxlength="500"
                          placeholder="Explain why CLOA/EP Number is unavailable..."
                          {{ $cloaAvailabilityStatus !== 'provided' ? 'required' : '' }}>{{ old('cloa_ep_unavailability_reason', $beneficiary->cloa_ep_unavailability_reason ?? '') }}</textarea>
                @error('cloa_ep_unavailability_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="arb_classification" class="form-label">ARB Classification</label>
                <select class="form-select @error('arb_classification') is-invalid @enderror" id="arb_classification" name="arb_classification">
                    <option value="">Select...</option>
                    @foreach($fieldOptions['arb_classification'] ?? ['Agricultural Lessee', 'Regular Farmworker', 'Seasonal Farmworker', 'Other Farmworker', 'Actual Tiller', 'Collective/Cooperative', 'Others'] as $option)
                        @php
                            if (is_object($option)) {
                                $label = (string) ($option->label ?? $option->value ?? '');
                                $value = (string) ($option->value ?? $option->label ?? '');
                            } elseif (is_array($option)) {
                                $label = (string) ($option['label'] ?? $option['value'] ?? '');
                                $value = (string) ($option['value'] ?? $option['label'] ?? '');
                            } else {
                                $label = (string) $option;
                                $value = (string) $option;
                            }
                        @endphp
                        <option value="{{ $value }}" {{ old('arb_classification', $beneficiary->arb_classification ?? '') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('arb_classification')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label for="landholding_description" class="form-label">Landholding Description <span class="text-danger">*</span></label>
                <textarea class="form-control @error('landholding_description') is-invalid @enderror"
                          id="landholding_description" name="landholding_description" rows="3" maxlength="1000"
                          placeholder="Describe the landholding...">{{ old('landholding_description', $beneficiary->landholding_description ?? '') }}</textarea>
                @error('landholding_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="land_area_awarded_hectares" class="form-label">Land Area Awarded (Hectares) <span class="text-danger">*</span></label>
                <input type="number" class="form-control @error('land_area_awarded_hectares') is-invalid @enderror"
                       id="land_area_awarded_hectares" name="land_area_awarded_hectares" step="0.01" min="0"
                       value="{{ old('land_area_awarded_hectares', $beneficiary->land_area_awarded_hectares ?? '') }}">
                @error('land_area_awarded_hectares')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="ownership_scheme" class="form-label">Ownership Scheme</label>
                <select class="form-select @error('ownership_scheme') is-invalid @enderror" id="ownership_scheme" name="ownership_scheme">
                    <option value="">Select...</option>
                    @foreach($fieldOptions['ownership_scheme'] ?? ['Individual', 'Collective', 'Cooperative'] as $option)
                        @php
                            if (is_object($option)) {
                                $label = (string) ($option->label ?? $option->value ?? '');
                                $value = (string) ($option->value ?? $option->label ?? '');
                            } elseif (is_array($option)) {
                                $label = (string) ($option['label'] ?? $option['value'] ?? '');
                                $value = (string) ($option['value'] ?? $option['label'] ?? '');
                            } else {
                                $label = (string) $option;
                                $value = (string) $option;
                            }
                        @endphp
                        <option value="{{ $value }}" {{ old('ownership_scheme', $beneficiary->ownership_scheme ?? '') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('ownership_scheme')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="barc_membership_status" class="form-label">BARC Membership Status</label>
                <input type="text" class="form-control @error('barc_membership_status') is-invalid @enderror"
                       id="barc_membership_status" name="barc_membership_status" maxlength="100"
                       value="{{ old('barc_membership_status', $beneficiary->barc_membership_status ?? '') }}">
                @error('barc_membership_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @foreach($customFieldGroups->get('dar_information', collect()) as $customField)
                @include('beneficiaries.partials.custom-field-input', ['customField' => $customField, 'beneficiaryCustomFields' => $beneficiaryCustomFields])
            @endforeach
        </div>
    </div>
</div>

{{-- SECTION 6 — Dynamic Agency Form Fields --}}
<div id="dynamic-agencies-container">
    {{-- Will be populated by JavaScript based on selected agencies --}}
</div>

<div
    id="existingAgencyDynamicData"
    class="d-none"
    data-values='@json($beneficiaryDynamicAgencyValues)'
    data-reasons='@json($beneficiaryDynamicAgencyReasons)'
></div>

{{-- SECTION 8 — Association Membership --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-people me-1"></i> Association Membership</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="form-check form-switch mt-2">
                    <input type="hidden" name="association_member" value="0">
                    <input type="checkbox" class="form-check-input" id="association_member" name="association_member" value="1"
                           {{ old('association_member', $beneficiary->association_member ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="association_member">Member of Farmers/Fisherfolk Association</label>
                </div>
            </div>
            <div class="col-12 col-md-6" id="association-name-wrapper" style="display: none;">
                <label for="association_name" class="form-label">Association Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('association_name') is-invalid @enderror"
                       id="association_name" name="association_name" value="{{ old('association_name', $beneficiary->association_name ?? '') }}">
                @error('association_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
    const classificationSelect = document.getElementById('classification');
    const agencyCheckboxes = document.getElementById('agency-checkboxes');
    const farmerSection = document.getElementById('farmer-info-section');
    const fisherfolkSection = document.getElementById('fisherfolk-info-section');
    const darSection = document.getElementById('dar-info-section');
    const associationCheckbox = document.getElementById('association_member');
    const associationWrapper = document.getElementById('association-name-wrapper');
    const hasVesselCheckbox = document.getElementById('has_fishing_vessel');
    const vesselTypeWrapper = document.getElementById('vessel-type-wrapper');
    const vesselTonnageWrapper = document.getElementById('vessel-tonnage-wrapper');
    const cloaAvailabilitySelect = document.getElementById('cloa_ep_availability_status');
    const cloaNumberWrapper = document.getElementById('cloa-ep-number-wrapper');
    const cloaReasonWrapper = document.getElementById('cloa-ep-reason-wrapper');
    const cloaNumberField = document.getElementById('cloa_ep_number');
    const cloaReasonField = document.getElementById('cloa_ep_unavailability_reason');

    function updateSections() {
        const classification = classificationSelect.value;
        const selectedAgencies = Array.from(document.querySelectorAll('#agency-checkboxes .agency-checkbox:checked'))
            .map(cb => cb.dataset.agencyName.toUpperCase());

        // Hide all sections first
        farmerSection.style.display = 'none';
        fisherfolkSection.style.display = 'none';
        darSection.style.display = 'none';

        // Show sections based on classification and selected agencies
        if (classification === 'Farmer') {
            if (selectedAgencies.includes('DA')) {
                farmerSection.style.display = 'block';
            }
            if (selectedAgencies.includes('DAR')) {
                darSection.style.display = 'block';
            }
        } else if (classification === 'Fisherfolk') {
            if (selectedAgencies.includes('DA') || selectedAgencies.includes('BFAR')) {
                fisherfolkSection.style.display = 'block';
            }
        }

        console.log('Updated sections - Classification:', classification, 'Agencies:', selectedAgencies);
    }

    function toggleAssociation() {
        if (associationWrapper) {
            associationWrapper.style.display = associationCheckbox.checked ? '' : 'none';
            const associationNameField = document.getElementById('association_name');
            if (associationNameField) {
                associationNameField.required = associationCheckbox.checked;
                if (!associationCheckbox.checked) {
                    associationNameField.setCustomValidity('');
                }
            }
        }
    }

    function toggleVesselFields() {
        if (hasVesselCheckbox && vesselTypeWrapper && vesselTonnageWrapper) {
            const show = hasVesselCheckbox.checked;
            vesselTypeWrapper.style.display = show ? '' : 'none';
            vesselTonnageWrapper.style.display = show ? '' : 'none';
        }
    }

    function toggleCloaAvailability() {
        if (!cloaAvailabilitySelect || !cloaNumberWrapper || !cloaReasonWrapper) {
            return;
        }

        const isProvided = cloaAvailabilitySelect.value === 'provided';
        cloaNumberWrapper.style.display = isProvided ? '' : 'none';
        cloaReasonWrapper.style.display = isProvided ? 'none' : '';

        if (cloaNumberField) {
            cloaNumberField.required = isProvided;
        }

        if (cloaReasonField) {
            cloaReasonField.required = !isProvided;
        }
    }

    // Event listeners
    if (classificationSelect) {
        classificationSelect.addEventListener('change', updateSections);
    }

    if (agencyCheckboxes) {
        agencyCheckboxes.addEventListener('change', updateSections);
    }

    if (associationCheckbox) {
        associationCheckbox.addEventListener('change', toggleAssociation);
    }

    if (hasVesselCheckbox) {
        hasVesselCheckbox.addEventListener('change', toggleVesselFields);
    }

    if (cloaAvailabilitySelect) {
        cloaAvailabilitySelect.addEventListener('change', toggleCloaAvailability);
    }

    // Initial setup
    updateSections();
    toggleAssociation();
    toggleVesselFields();
    toggleCloaAvailability();
});
</script>
@endpush

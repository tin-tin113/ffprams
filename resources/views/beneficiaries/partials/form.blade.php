{{-- $beneficiary is null on create, populated on edit --}}
@php
    $editing = isset($beneficiary);
    $fo = $fieldOptions ?? [];
    $fieldGroupSettings = $fieldGroupSettings ?? [];
    $beneficiaryCustomFields = (array) (($beneficiary->custom_fields ?? []) ?: []);

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

        return collect($items)->map(function ($item) {
            $value = is_object($item) ? ($item->value ?? $item->label ?? '') : ($item['value'] ?? $item['label'] ?? '');
            $label = is_object($item) ? ($item->label ?? $item->value ?? '') : ($item['label'] ?? $item['value'] ?? '');
            return (object) ['value' => $value, 'label' => $label];
        });
    };

    $customFieldGroups = collect($fo)
        ->filter(fn ($items, $group) => ! in_array($group, $nativeFieldGroups, true))
        ->map(function ($items, $group) use ($normalizeFieldOptions, $getGroupSetting) {
            $placement = $getGroupSetting($group, 'placement_section', 'personal_information');

            return [
                'field_group' => $group,
                'label' => Str::title(str_replace('_', ' ', $group)),
                'placement_section' => $placement,
                'placement_label' => [
                    'personal_information' => 'Agency & Personal Information',
                    'farmer_information' => 'DA/RSBSA Information (Farmer)',
                    'fisherfolk_information' => 'BFAR/FishR Information (Fisherfolk)',
                    'dar_information' => 'DAR/ARB Information',
                ][$placement] ?? Str::title(str_replace('_', ' ', $placement)),
                'is_required' => (bool) $getGroupSetting($group, 'is_required', false),
                'options' => $normalizeFieldOptions($items, []),
            ];
        })
        ->filter(fn ($config) => $config['options']->isNotEmpty())
        ->sortBy('label')
        ->groupBy('placement_section');

    $civilStatusRequired = $isGroupRequired('civil_status', true);
    $highestEducationRequired = $isGroupRequired('highest_education', false);
    $idTypeRequired = $isGroupRequired('id_type', false);
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
    $idTypeOptions = $normalizeFieldOptions($fo['id_type'] ?? [], [
        'PhilSys ID',
        "Voter's ID",
        "Driver's License",
        'Passport',
        'Senior Citizen ID',
        'PWD ID',
        'Postal ID',
        'TIN ID',
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

            {{-- Government ID Type --}}
            <div class="col-12 col-md-3">
                <label for="id_type" class="form-label">Government ID Type {!! $idTypeRequired ? '<span class="text-danger">*</span>' : '' !!}</label>
                <select class="form-select @error('id_type') is-invalid @enderror" id="id_type" name="id_type" {{ $idTypeRequired ? 'required' : '' }}>
                    <option value="" {{ old('id_type', $beneficiary->id_type ?? '') === '' ? 'selected' : '' }}>Select...</option>
                    @foreach($idTypeOptions as $opt)
                        <option value="{{ $opt->value }}" {{ old('id_type', $beneficiary->id_type ?? '') === $opt->value ? 'selected' : '' }}>{{ $opt->label }}</option>
                    @endforeach
                </select>
                @error('id_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Contact Number --}}
            <div class="col-12 col-md-3">
                <label for="contact_number" class="form-label">Contact Number <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('contact_number') is-invalid @enderror"
                       id="contact_number" name="contact_number" placeholder="09XXXXXXXXX"
                       value="{{ old('contact_number', $beneficiary->contact_number ?? '') }}" required>
                @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                @php
                    $customGroup = $customField['field_group'];
                    $customFieldName = 'custom_fields.' . $customGroup;
                    $customFieldValue = old($customFieldName, $beneficiaryCustomFields[$customGroup] ?? '');
                @endphp
                <div class="col-12 col-md-3">
                    <label for="custom_{{ $customGroup }}" class="form-label">
                        {{ $customField['label'] }}
                        @if($customField['is_required'])
                            <span class="text-danger">*</span>
                        @endif
                    </label>
                    <select class="form-select @error($customFieldName) is-invalid @enderror"
                            id="custom_{{ $customGroup }}"
                            name="custom_fields[{{ $customGroup }}]"
                            data-custom-required="{{ $customField['is_required'] ? '1' : '0' }}"
                            data-custom-placement="personal_information"
                            {{ $customField['is_required'] ? 'required' : '' }}>
                        <option value="">Select...</option>
                        @foreach($customField['options'] as $opt)
                            <option value="{{ $opt->value }}" {{ (string) $customFieldValue === (string) $opt->value ? 'selected' : '' }}>{{ $opt->label }}</option>
                        @endforeach
                    </select>
                    @error($customFieldName)<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
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

{{-- SECTION 3 — DA/RSBSA Information (Farmer) --}}
<div class="card border-0 shadow-sm mb-4" id="da-farmer-section" style="display: none;">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-tree me-1"></i> DA/RSBSA Information (Farmer)</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label for="rsbsa_number" class="form-label">RSBSA Number</label>
                <input type="text" class="form-control @error('rsbsa_number') is-invalid @enderror"
                       id="rsbsa_number" name="rsbsa_number" value="{{ old('rsbsa_number', $beneficiary->rsbsa_number ?? '') }}">
                @error('rsbsa_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">Can be added after registration</small>
            </div>
            <div class="col-12 col-md-4">
                <label for="farm_ownership" class="form-label">Land Ownership / Tenure {!! $farmOwnershipRequired ? '<span class="text-danger">*</span>' : '' !!}</label>
                <select class="form-select @error('farm_ownership') is-invalid @enderror" id="farm_ownership" name="farm_ownership">
                    <option value="" disabled {{ old('farm_ownership', $beneficiary->farm_ownership ?? '') === '' ? 'selected' : '' }}>Select...</option>
                    @foreach($farmOwnershipOptions as $opt)
                        <option value="{{ $opt->value }}" {{ old('farm_ownership', $beneficiary->farm_ownership ?? '') === $opt->value ? 'selected' : '' }}>{{ $opt->label }}</option>
                    @endforeach
                </select>
                @error('farm_ownership')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-4">
                <label for="farm_size_hectares" class="form-label">Farm Area (Hectares) <span class="text-danger">*</span></label>
                <input type="number" class="form-control @error('farm_size_hectares') is-invalid @enderror"
                       id="farm_size_hectares" name="farm_size_hectares"
                       value="{{ old('farm_size_hectares', $beneficiary->farm_size_hectares ?? '') }}" step="0.01" min="0.01">
                @error('farm_size_hectares')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="primary_commodity" class="form-label">Crop Type / Commodity <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('primary_commodity') is-invalid @enderror"
                       id="primary_commodity" name="primary_commodity" placeholder="e.g. Rice, Corn, Vegetables"
                       value="{{ old('primary_commodity', $beneficiary->primary_commodity ?? '') }}">
                @error('primary_commodity')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="farm_type" class="form-label">Farm Type / Irrigation {!! $farmTypeRequired ? '<span class="text-danger">*</span>' : '' !!}</label>
                <select class="form-select @error('farm_type') is-invalid @enderror" id="farm_type" name="farm_type">
                    <option value="" disabled {{ old('farm_type', $beneficiary->farm_type ?? '') === '' ? 'selected' : '' }}>Select...</option>
                    @foreach($farmTypeOptions as $opt)
                        <option value="{{ $opt->value }}" {{ old('farm_type', $beneficiary->farm_type ?? '') === $opt->value ? 'selected' : '' }}>{{ $opt->label }}</option>
                    @endforeach
                </select>
                @error('farm_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="organization_membership" class="form-label">Organization / Cooperative Membership</label>
                <input type="text" class="form-control @error('organization_membership') is-invalid @enderror"
                       id="organization_membership" name="organization_membership"
                       value="{{ old('organization_membership', $beneficiary->organization_membership ?? '') }}">
                @error('organization_membership')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @foreach($customFieldGroups->get('farmer_information', collect()) as $customField)
                @php
                    $customGroup = $customField['field_group'];
                    $customFieldName = 'custom_fields.' . $customGroup;
                    $customFieldValue = old($customFieldName, $beneficiaryCustomFields[$customGroup] ?? '');
                @endphp
                <div class="col-12 col-md-6">
                    <label for="custom_{{ $customGroup }}" class="form-label">
                        {{ $customField['label'] }}
                        @if($customField['is_required'])
                            <span class="text-danger">*</span>
                        @endif
                    </label>
                    <select class="form-select @error($customFieldName) is-invalid @enderror"
                            id="custom_{{ $customGroup }}"
                            name="custom_fields[{{ $customGroup }}]"
                            data-custom-required="{{ $customField['is_required'] ? '1' : '0' }}"
                            data-custom-placement="farmer_information">
                        <option value="">Select...</option>
                        @foreach($customField['options'] as $opt)
                            <option value="{{ $opt->value }}" {{ (string) $customFieldValue === (string) $opt->value ? 'selected' : '' }}>{{ $opt->label }}</option>
                        @endforeach
                    </select>
                    @error($customFieldName)<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- SECTION 4 — Shared Fisherfolk Information (for ANY Fisherfolk classification) --}}
<div class="card border-0 shadow-sm mb-4" id="shared-fisherfolk-section" style="display: none;">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-water me-1"></i> Fisherfolk Information</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label for="fisherfolk_type" class="form-label">Type of Fishing Activity {!! $fisherfolkTypeRequired ? '<span class="text-danger">*</span>' : '' !!}</label>
                <select class="form-select @error('fisherfolk_type') is-invalid @enderror" id="fisherfolk_type" name="fisherfolk_type">
                    <option value="" disabled {{ old('fisherfolk_type', $beneficiary->fisherfolk_type ?? '') === '' ? 'selected' : '' }}>Select...</option>
                    @foreach($fisherfolkTypeOptions as $opt)
                        <option value="{{ $opt->value }}" {{ old('fisherfolk_type', $beneficiary->fisherfolk_type ?? '') === $opt->value ? 'selected' : '' }}>{{ $opt->label }}</option>
                    @endforeach
                </select>
                @error('fisherfolk_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-4">
                <label for="length_of_residency_months" class="form-label">Residency (Months) <span class="text-danger">*</span></label>
                <input type="number" class="form-control @error('length_of_residency_months') is-invalid @enderror"
                       id="length_of_residency_months" name="length_of_residency_months"
                       value="{{ old('length_of_residency_months', $beneficiary->length_of_residency_months ?? '') }}" min="6">
                @error('length_of_residency_months')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">Min 6 months per RA 8550</small>
            </div>
            <div class="col-12 col-md-4">
                <label for="main_fishing_gear" class="form-label">Fishing Gear Type</label>
                <input type="text" class="form-control @error('main_fishing_gear') is-invalid @enderror"
                       id="main_fishing_gear" name="main_fishing_gear" value="{{ old('main_fishing_gear', $beneficiary->main_fishing_gear ?? '') }}">
                @error('main_fishing_gear')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-2">
                <div class="form-check mt-4">
                    <input type="hidden" name="has_fishing_vessel" value="0">
                    <input type="checkbox" class="form-check-input" id="has_fishing_vessel" name="has_fishing_vessel" value="1"
                           {{ old('has_fishing_vessel', $beneficiary->has_fishing_vessel ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="has_fishing_vessel">Has Vessel</label>
                </div>
            </div>
            <div class="col-12 col-md-3" id="vessel-type-wrapper" style="display: none;">
                <label for="fishing_vessel_type" class="form-label">Vessel Type</label>
                <input type="text" class="form-control" id="fishing_vessel_type" name="fishing_vessel_type"
                       value="{{ old('fishing_vessel_type', $beneficiary->fishing_vessel_type ?? '') }}">
            </div>
            <div class="col-12 col-md-3" id="vessel-tonnage-wrapper" style="display: none;">
                <label for="fishing_vessel_tonnage" class="form-label">Gross Tonnage</label>
                <input type="number" class="form-control" id="fishing_vessel_tonnage" name="fishing_vessel_tonnage"
                       value="{{ old('fishing_vessel_tonnage', $beneficiary->fishing_vessel_tonnage ?? '') }}" step="0.01" min="0">
            </div>

            @foreach($customFieldGroups->get('fisherfolk_information', collect()) as $customField)
                @php
                    $customGroup = $customField['field_group'];
                    $customFieldName = 'custom_fields.' . $customGroup;
                    $customFieldValue = old($customFieldName, $beneficiaryCustomFields[$customGroup] ?? '');
                @endphp
                <div class="col-12 col-md-4">
                    <label for="custom_{{ $customGroup }}" class="form-label">
                        {{ $customField['label'] }}
                        @if($customField['is_required'])
                            <span class="text-danger">*</span>
                        @endif
                    </label>
                    <select class="form-select @error($customFieldName) is-invalid @enderror"
                            id="custom_{{ $customGroup }}"
                            name="custom_fields[{{ $customGroup }}]"
                            data-custom-required="{{ $customField['is_required'] ? '1' : '0' }}"
                            data-custom-placement="fisherfolk_information">
                        <option value="">Select...</option>
                        @foreach($customField['options'] as $opt)
                            <option value="{{ $opt->value }}" {{ (string) $customFieldValue === (string) $opt->value ? 'selected' : '' }}>{{ $opt->label }}</option>
                        @endforeach
                    </select>
                    @error($customFieldName)<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- SECTION 5 — DA/RSBSA Information (Fisherfolk Registration) - SIMPLIFIED --}}
<div class="card border-0 shadow-sm mb-4" id="da-fisherfolk-section" style="display: none;">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-file-text me-1"></i> DA/RSBSA Registration (Fisherfolk)</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label for="rsbsa_number" class="form-label">RSBSA Number</label>
                <input type="text" class="form-control @error('rsbsa_number') is-invalid @enderror"
                       id="rsbsa_number" name="rsbsa_number" placeholder="e.g. DA-2024-001"
                       value="{{ old('rsbsa_number', $beneficiary->rsbsa_number ?? '') }}">
                @error('rsbsa_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">Can be added after registration</small>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 6 — BFAR/FishR Information (Fisherfolk Registration) - SIMPLIFIED --}}
<div class="card border-0 shadow-sm mb-4" id="bfar-section" style="display: none;">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-file-text me-1"></i> BFAR/FishR Registration (Fisherfolk)</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label for="fishr_number" class="form-label">FishR Number</label>
                <input type="text" class="form-control @error('fishr_number') is-invalid @enderror"
                       id="fishr_number" name="fishr_number" placeholder="e.g. FISHR-2024-567"
                       value="{{ old('fishr_number', $beneficiary->fishr_number ?? '') }}">
                @error('fishr_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">Can be added after registration</small>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 7 — DAR/ARB Information --}}
<div class="card border-0 shadow-sm mb-4" id="dar-section" style="display: none;">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-file-earmark-text me-1"></i> DAR/ARB Information
        <span class="badge bg-warning text-dark ms-2">CLOA/EP Required</span>
    </div>
    <div class="card-body">
        <div class="alert alert-warning mb-3">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <strong>Note:</strong> CLOA or EP number is REQUIRED for DAR beneficiaries as it is the legal proof of land award issued by the MARO/PARO.
        </div>
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label for="cloa_ep_number" class="form-label">CLOA / EP Number <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('cloa_ep_number') is-invalid @enderror"
                       id="cloa_ep_number" name="cloa_ep_number" value="{{ old('cloa_ep_number', $beneficiary->cloa_ep_number ?? '') }}">
                @error('cloa_ep_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-4">
                <label for="arb_classification" class="form-label">ARB Classification {!! $arbClassificationRequired ? '<span class="text-danger">*</span>' : '' !!}</label>
                <select class="form-select @error('arb_classification') is-invalid @enderror" id="arb_classification" name="arb_classification">
                    <option value="" disabled {{ old('arb_classification', $beneficiary->arb_classification ?? '') === '' ? 'selected' : '' }}>Select...</option>
                    @foreach($arbClassificationOptions as $opt)
                        <option value="{{ $opt->value }}" {{ old('arb_classification', $beneficiary->arb_classification ?? '') === $opt->value ? 'selected' : '' }}>{{ $opt->label }}</option>
                    @endforeach
                </select>
                @error('arb_classification')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-4">
                <label for="ownership_scheme" class="form-label">Ownership Scheme {!! $ownershipSchemeRequired ? '<span class="text-danger">*</span>' : '' !!}</label>
                <select class="form-select @error('ownership_scheme') is-invalid @enderror" id="ownership_scheme" name="ownership_scheme">
                    <option value="" disabled {{ old('ownership_scheme', $beneficiary->ownership_scheme ?? '') === '' ? 'selected' : '' }}>Select...</option>
                    @foreach($ownershipSchemeOptions as $opt)
                        <option value="{{ $opt->value }}" {{ old('ownership_scheme', $beneficiary->ownership_scheme ?? '') === $opt->value ? 'selected' : '' }}>{{ $opt->label }}</option>
                    @endforeach
                </select>
                @error('ownership_scheme')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-8">
                <label for="landholding_description" class="form-label">Landholding Description <span class="text-danger">*</span></label>
                <textarea class="form-control @error('landholding_description') is-invalid @enderror"
                          id="landholding_description" name="landholding_description" rows="2">{{ old('landholding_description', $beneficiary->landholding_description ?? '') }}</textarea>
                @error('landholding_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-4">
                <label for="land_area_awarded_hectares" class="form-label">Land Area Awarded (Ha) <span class="text-danger">*</span></label>
                <input type="number" class="form-control @error('land_area_awarded_hectares') is-invalid @enderror"
                       id="land_area_awarded_hectares" name="land_area_awarded_hectares"
                       value="{{ old('land_area_awarded_hectares', $beneficiary->land_area_awarded_hectares ?? '') }}" step="0.01" min="0.01">
                @error('land_area_awarded_hectares')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-6">
                <label for="barc_membership_status" class="form-label">BARC Membership Status</label>
                <input type="text" class="form-control @error('barc_membership_status') is-invalid @enderror"
                       id="barc_membership_status" name="barc_membership_status"
                       value="{{ old('barc_membership_status', $beneficiary->barc_membership_status ?? '') }}">
                @error('barc_membership_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @foreach($customFieldGroups->get('dar_information', collect()) as $customField)
                @php
                    $customGroup = $customField['field_group'];
                    $customFieldName = 'custom_fields.' . $customGroup;
                    $customFieldValue = old($customFieldName, $beneficiaryCustomFields[$customGroup] ?? '');
                @endphp
                <div class="col-12 col-md-6">
                    <label for="custom_{{ $customGroup }}" class="form-label">
                        {{ $customField['label'] }}
                        @if($customField['is_required'])
                            <span class="text-danger">*</span>
                        @endif
                    </label>
                    <select class="form-select @error($customFieldName) is-invalid @enderror"
                            id="custom_{{ $customGroup }}"
                            name="custom_fields[{{ $customGroup }}]"
                            data-custom-required="{{ $customField['is_required'] ? '1' : '0' }}"
                            data-custom-placement="dar_information">
                        <option value="">Select...</option>
                        @foreach($customField['options'] as $opt)
                            <option value="{{ $opt->value }}" {{ (string) $customFieldValue === (string) $opt->value ? 'selected' : '' }}>{{ $opt->label }}</option>
                        @endforeach
                    </select>
                    @error($customFieldName)<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endforeach
        </div>
    </div>
</div>

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
    const agencyCheckboxesContainer = document.getElementById('agency-checkboxes');
    const classification = document.getElementById('classification');
    const daFarmerSection = document.getElementById('da-farmer-section');
    const daFisherfolkSection = document.getElementById('da-fisherfolk-section');
    const bfarSection = document.getElementById('bfar-section');
    const darSection = document.getElementById('dar-section');
    const associationCheckbox = document.getElementById('association_member');
    const associationWrapper = document.getElementById('association-name-wrapper');
    const hasVesselCheckbox = document.getElementById('has_fishing_vessel');
    const vesselTypeWrapper = document.getElementById('vessel-type-wrapper');
    const vesselTonnageWrapper = document.getElementById('vessel-tonnage-wrapper');

    // Beneficiary data for edit mode
    @php
        $selectedAgencyIds = [];
        if ($editing && $beneficiary->id) {
            $selectedAgencyIds = $beneficiary->agencies()->pluck('agencies.id')->toArray();
            if (empty($selectedAgencyIds) && $beneficiary->agency_id) {
                $selectedAgencyIds = [$beneficiary->agency_id];
            }
        }

        $agencyLookup = [];
        foreach (($agencies ?? collect()) as $agency) {
            $name = strtoupper((string) $agency->name);
            if ($name === '') {
                continue;
            }

            $agencyLookup[$name] = [
                'id' => (int) $agency->id,
                'name' => $name,
                'full_name' => (string) $agency->full_name,
            ];
        }
    @endphp
    const selectedAgencyIds = {{ json_encode($selectedAgencyIds) }};
    const agencyLookup = @json($agencyLookup);

    const conditionalRequirements = {
        farmOwnership: @json($farmOwnershipRequired),
        farmType: @json($farmTypeRequired),
        fisherfolkType: @json($fisherfolkTypeRequired),
        arbClassification: @json($arbClassificationRequired),
        ownershipScheme: @json($ownershipSchemeRequired)
    };

    let selectedAgencyIdSet = new Set((selectedAgencyIds || []).map(function (id) {
        return Number(id);
    }).filter(function (id) {
        return Number.isFinite(id);
    }));

    function formatAgencyLabel(agencyName, fullName) {
        if (agencyName === 'DA') {
            return 'DA (Department of Agriculture - RSBSA)';
        }

        if (agencyName === 'BFAR') {
            return 'BFAR (Bureau of Fisheries & Aquatic Resources)';
        }

        if (agencyName === 'DAR') {
            return 'DAR (Department of Agrarian Reform)';
        }

        return agencyName + ' (' + (fullName || agencyName) + ')';
    }

    function resolveAgencyConfig(agencyName) {
        const agency = agencyLookup[agencyName];
        if (!agency) {
            return null;
        }

        return {
            id: Number(agency.id),
            name: agency.name,
            label: formatAgencyLabel(agency.name, agency.full_name)
        };
    }

    // Agency mapping based on classification
    const agencyMap = {
        'Farmer': ['DA', 'DAR'].map(resolveAgencyConfig).filter(Boolean),
        'Fisherfolk': ['DA', 'BFAR'].map(resolveAgencyConfig).filter(Boolean)
    };

    function syncSelectedAgencyIdsFromDom() {
        const existingCheckboxes = document.querySelectorAll('input[name="agencies[]"]');
        if (!existingCheckboxes.length) {
            return;
        }

        selectedAgencyIdSet = new Set(Array.from(existingCheckboxes)
            .filter(function (checkbox) {
                return checkbox.checked;
            })
            .map(function (checkbox) {
                return Number(checkbox.value);
            })
            .filter(function (id) {
                return Number.isFinite(id);
            }));
    }

    function updateAgencySelectionValidity() {
        const agencyCheckboxes = document.querySelectorAll('input[name="agencies[]"]');
        if (!agencyCheckboxes.length) {
            return;
        }

        const hasSelection = Array.from(agencyCheckboxes).some(function (checkbox) {
            return checkbox.checked;
        });

        agencyCheckboxes.forEach(function (checkbox) {
            checkbox.setCustomValidity(hasSelection ? '' : 'Select at least one source agency.');
        });
    }

    function setFieldRequired(fieldName, shouldRequire) {
        document.querySelectorAll('[name="' + fieldName + '"]').forEach(function (field) {
            if (field.type === 'hidden') {
                return;
            }

            field.required = shouldRequire;
            if (!shouldRequire) {
                field.setCustomValidity('');
            }
        });
    }

    // Populate agency checkboxes based on classification
    function updateAgencyCheckboxes() {
        syncSelectedAgencyIdsFromDom();

        const classVal = classification.value;
        agencyCheckboxesContainer.innerHTML = '';

        const agencies = agencyMap[classVal] || [];

        if (!agencies.length) {
            toggleSections();
            return;
        }

        agencies.forEach(agency => {
            const checkbox = document.createElement('div');
            checkbox.className = 'form-check mb-2';
            const isChecked = selectedAgencyIdSet.has(agency.id);
            checkbox.innerHTML = `
                <input class="form-check-input agency-checkbox"
                       type="checkbox"
                       id="agency_${agency.id}"
                       name="agencies[]"
                       value="${agency.id}"
                       data-agency-name="${agency.name}"
                       ${isChecked ? 'checked' : ''}>
                <label class="form-check-label" for="agency_${agency.id}">
                    ${agency.label}
                </label>
            `;
            agencyCheckboxesContainer.appendChild(checkbox);

            // Add event listener to checkboxes
            checkbox.querySelector('.agency-checkbox').addEventListener('change', function (event) {
                const agencyId = Number(event.target.value);
                if (event.target.checked) {
                    selectedAgencyIdSet.add(agencyId);
                } else {
                    selectedAgencyIdSet.delete(agencyId);
                }

                toggleSections();
            });
        });

        toggleSections();
    }

    // Show/hide sections based on selected agencies and classification
    function toggleSections() {
        const agencyCheckboxes = document.querySelectorAll('input[name="agencies[]"]:checked');
        const selectedAgencies = Array.from(agencyCheckboxes).map(cb => cb.dataset.agencyName.toUpperCase());
        const classVal = classification.value;
        const hasDa = selectedAgencies.includes('DA');
        const hasBfar = selectedAgencies.includes('BFAR');
        const hasDar = selectedAgencies.includes('DAR');
        const isFarmer = classVal === 'Farmer';
        const isFisherfolk = classVal === 'Fisherfolk';

        // Show DA Farmer section for DA farmers and DAR farmers to keep custom field visibility aligned.
        if (daFarmerSection) {
            daFarmerSection.style.display = (isFarmer && (hasDa || hasDar)) ? '' : 'none';
        }

        // Show shared Fisherfolk section if: Fisherfolk classification AND (DA OR BFAR checked)
        const sharedFisherfolkSection = document.getElementById('shared-fisherfolk-section');
        if (sharedFisherfolkSection) {
            const showShared = isFisherfolk && (hasDa || hasBfar);
            sharedFisherfolkSection.style.display = showShared ? '' : 'none';
        }

        // Show DA Fisherfolk section if: Fisherfolk classification AND DA checked (RSBSA number only)
        if (daFisherfolkSection) {
            daFisherfolkSection.style.display = (isFisherfolk && hasDa) ? '' : 'none';
        }

        // Show BFAR section if: Fisherfolk classification AND BFAR checked (FishR number only)
        if (bfarSection) {
            bfarSection.style.display = (isFisherfolk && hasBfar) ? '' : 'none';
        }

        // Show DAR section if: DAR checked (independent of classification)
        if (darSection) {
            darSection.style.display = hasDar ? '' : 'none';
        }

        updateAgencySelectionValidity();

        // Update required attributes to match server-side validation.
        updateRequiredFields(selectedAgencies, classVal);
    }

    // Update required attributes based on visible sections
    function updateRequiredFields(selectedAgencies, classVal) {
        const hasDa = selectedAgencies.includes('DA');
        const hasBfar = selectedAgencies.includes('BFAR');
        const hasDar = selectedAgencies.includes('DAR');
        const isFarmer = classVal === 'Farmer';
        const isFisherfolk = classVal === 'Fisherfolk';
        const showSharedFisherfolk = isFisherfolk && (hasDa || hasBfar);

        setFieldRequired('farm_ownership', conditionalRequirements.farmOwnership && hasDa && isFarmer);
        setFieldRequired('farm_size_hectares', hasDa && isFarmer);
        setFieldRequired('primary_commodity', hasDa && isFarmer);
        setFieldRequired('farm_type', conditionalRequirements.farmType && hasDa && isFarmer);

        setFieldRequired('fisherfolk_type', conditionalRequirements.fisherfolkType && showSharedFisherfolk);
        setFieldRequired('length_of_residency_months', showSharedFisherfolk);

        setFieldRequired('cloa_ep_number', hasDar);
        setFieldRequired('arb_classification', conditionalRequirements.arbClassification && hasDar);
        setFieldRequired('landholding_description', hasDar);
        setFieldRequired('land_area_awarded_hectares', hasDar);
        setFieldRequired('ownership_scheme', conditionalRequirements.ownershipScheme && hasDar);

        document.querySelectorAll('[data-custom-placement]').forEach((field) => {
            const configuredRequired = field.dataset.customRequired === '1';
            const placement = field.dataset.customPlacement;

            let shouldRequire = false;

            if (configuredRequired) {
                if (placement === 'farmer_information') {
                    shouldRequire = (hasDa && isFarmer) || hasDar;
                } else if (placement === 'fisherfolk_information') {
                    shouldRequire = (hasDa && isFisherfolk) || hasBfar;
                } else if (placement === 'dar_information') {
                    shouldRequire = hasDar;
                } else {
                    shouldRequire = true;
                }
            }

            field.required = shouldRequire;
            if (!shouldRequire) {
                field.setCustomValidity('');
            }
        });
    }

    function toggleAssociation() {
        associationWrapper.style.display = associationCheckbox.checked ? '' : 'none';

        const associationNameField = document.getElementById('association_name');
        if (associationNameField) {
            associationNameField.required = associationCheckbox.checked;
            if (!associationCheckbox.checked) {
                associationNameField.setCustomValidity('');
            }
        }
    }

    function toggleVesselFields() {
        const show = hasVesselCheckbox.checked;
        vesselTypeWrapper.style.display = show ? '' : 'none';
        vesselTonnageWrapper.style.display = show ? '' : 'none';
    }

    classification.addEventListener('change', updateAgencyCheckboxes);
    associationCheckbox.addEventListener('change', toggleAssociation);
    if (hasVesselCheckbox) {
        hasVesselCheckbox.addEventListener('change', toggleVesselFields);
    }

    // Initial setup
    updateAgencyCheckboxes();
    toggleAssociation();
    toggleVesselFields();
});
</script>
@endpush

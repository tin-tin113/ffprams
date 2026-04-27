{{-- $beneficiary is null on create, populated on edit --}}
@php
    $editing = isset($beneficiary);
    $fo = $fieldOptions ?? [];
    $fieldGroupSettings = $fieldGroupSettings ?? [];
    $beneficiaryCustomFields = (array) (($beneficiary->custom_fields ?? []) ?: []);
    $beneficiaryDynamicAgencyValues = (array) ($beneficiaryCustomFields['agency_dynamic'] ?? []);
    $beneficiaryReasonMap = $editing ? (array) (($beneficiary->custom_field_unavailability_reasons ?? []) ?: []) : [];
    $beneficiaryDynamicAgencyReasons = (array) ($beneficiaryReasonMap['agency_dynamic'] ?? []);
    $selectedAgencyRaw = old('agencies');
    if ($selectedAgencyRaw === null && $editing) {
        $selectedAgencyRaw = $beneficiary->agencies->pluck('id')->all();
    }
    $selectedAgencyIds = collect((array) ($selectedAgencyRaw ?? []))
        ->flatMap(function ($value, $key) {
            $results = [];

            if (is_numeric($key)) {
                $results[] = $key;

                if (! is_array($value) && is_numeric($value)) {
                    $results[] = $value;
                }
            } else {
                if (is_numeric($value)) {
                    $results[] = $value;
                }
            }

            return $results;
        })
        ->map(fn ($id) => (int) $id)
        ->filter(fn ($id) => $id > 0)
        ->unique()
        ->values()
        ->all();

    $placementLabels = [
        'personal_information' => 'Agency & Personal Information',
        'farmer_information' => 'Farmer Information',
        'fisherfolk_information' => 'Fisherfolk Information',
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

    $agencySpecificCoreGroups = \App\Support\BeneficiaryCoreFields::agencySpecificCoreFieldNames();

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
        ->filter(fn ($items, $group) => ! in_array((string) $group, $agencySpecificCoreGroups, true))
        ->map(function ($items, $group) use ($normalizeFieldOptions, $getGroupSetting) {
            $placement = $getGroupSetting($group, 'placement_section', 'personal_information');
            $fieldType = strtolower((string) $getGroupSetting($group, 'field_type', \App\Models\FormFieldOption::FIELD_TYPE_DROPDOWN));
            $isOptionBased = in_array($fieldType, \App\Models\FormFieldOption::optionBasedFieldTypes(), true);
            $options = $normalizeFieldOptions($items, []);
            $firstItem = collect($items)->first();
            $configuredLabel = trim((string) (is_object($firstItem) ? ($firstItem->label ?? '') : ($firstItem['label'] ?? '')));

            return [
                'field_group' => $group,
                'label' => ! $isOptionBased && $configuredLabel !== '' ? $configuredLabel : Str::title(str_replace('_', ' ', $group)),
                'field_type' => $fieldType,
                'is_option_based' => $isOptionBased,
                'placement_section' => $placement,
                'placement_label' => [
                    'personal_information' => 'Agency & Personal Information',
                    'farmer_information' => 'Farmer Information',
                    'fisherfolk_information' => 'Fisherfolk Information',
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

    $civilStatusOptions = $normalizeFieldOptions($fo['civil_status'] ?? [], []);
    $highestEducationOptions = $normalizeFieldOptions($fo['highest_education'] ?? [], []);
    $idTypeOptions = $normalizeFieldOptions($fo['id_type'] ?? [], []);
    $farmOwnershipOptions = $normalizeFieldOptions($fo['farm_ownership'] ?? [], []);
    $farmTypeOptions = $normalizeFieldOptions($fo['farm_type'] ?? [], []);
    $fisherfolkTypeOptions = $normalizeFieldOptions($fo['fisherfolk_type'] ?? [], []);
    $rsbsaAvailabilityStatus = old(
        'rsbsa_availability_status',
        filled($beneficiary->rsbsa_unavailability_reason ?? null) ? 'not_available_yet' : 'provided'
    );
    $fishrAvailabilityStatus = old(
        'fishr_availability_status',
        filled($beneficiary->fishr_unavailability_reason ?? null) ? 'not_available_yet' : 'provided'
    );

    $governmentIdAvailabilityStatus = old(
        'government_id_availability_status',
        (filled($beneficiary->id_type ?? null) || filled($beneficiary->id_number ?? null)) ? 'available' : 'not_available'
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

{{-- SECTION 0 — Registration Context --}}
<div class="mb-5 bg-white rounded-3 p-4 border" id="registration-context-section">
    <div class="row g-4">
        {{-- Sector/Classification --}}
        <div class="col-12 col-md-3">
            <label for="classification" class="form-label text-muted fw-semibold small text-uppercase">Sector <span class="text-danger">*</span></label>
            <select class="form-select border-secondary border-opacity-25 @error('classification') is-invalid @enderror"
                    id="classification" name="classification" required>
                <option value="" disabled {{ old('classification', $beneficiary->classification ?? '') === '' ? 'selected' : '' }}>Select classification...</option>
                @foreach(['Farmer', 'Fisherfolk'] as $type)
                    <option value="{{ $type }}" {{ old('classification', $beneficiary->classification ?? '') === $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
            @error('classification')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Agency Selection (Multi-Select) --}}
        <div class="col-12 col-md-9 border-md-start ps-md-4">
            <label class="form-label text-muted fw-semibold small text-uppercase">Required Document Agencies <span class="text-danger">*</span></label>
            <div id="agency-checkboxes" class="mb-1 d-flex flex-wrap gap-4">
                {{-- Populated dynamically --}}
            </div>
            <small class="text-muted">Fields will dynamically adjust based on your selection.</small>
            @error('agencies')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div class="col-12"><hr class="text-black-50 my-1 border-dashed"></div>

        {{-- Status --}}
        <div class="col-12 col-md-4">
            <label for="status" class="form-label text-muted fw-semibold small text-uppercase">Account Status <span class="text-danger">*</span></label>
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

        {{-- Registration Date --}}
        <div class="col-12 col-md-4">
            <label for="registered_at" class="form-label text-muted fw-semibold small text-uppercase">Registration Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control @error('registered_at') is-invalid @enderror"
                   id="registered_at" name="registered_at"
                   value="{{ old('registered_at', isset($beneficiary) && $beneficiary->registered_at ? $beneficiary->registered_at->format('Y-m-d') : '') }}" required>
            @error('registered_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="mb-5 bg-white rounded-3 p-4 border" id="personal-info-section">
    <div class="border-bottom pb-2 mb-4">
        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-person me-2 text-muted"></i>Personal Information</h5>
    </div>
    <div class="row g-4">

            {{-- Name Fields --}}
            <div class="col-12 col-md-4">
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

            <div class="col-12 col-md-2">
                <label for="name_suffix" class="form-label">Suffix / Ext.</label>
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

            <div class="col-12"><hr class="text-black-50 my-2 border-dashed"></div>

            <div class="col-12 col-md-4">
                <label for="government_id_availability_status" class="form-label">Government ID Availability</label>
                <select class="form-select @error('government_id_availability_status') is-invalid @enderror"
                        id="government_id_availability_status"
                        name="government_id_availability_status">
                    <option value="available" {{ $governmentIdAvailabilityStatus === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="not_available" {{ $governmentIdAvailabilityStatus === 'not_available' ? 'selected' : '' }}>Not Available</option>
                </select>
                @error('government_id_availability_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div id="government-id-fields-wrapper" class="col-12 col-md-8 contents {{ $governmentIdAvailabilityStatus === 'available' ? '' : 'd-none' }}">
                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <label for="id_type" class="form-label">Government ID Type</label>
                        <select class="form-select @error('id_type') is-invalid @enderror" id="id_type" name="id_type">
                            <option value="" {{ old('id_type', $beneficiary->id_type ?? '') === '' ? 'selected' : '' }}>Select ID type...</option>
                            @foreach($idTypeOptions as $opt)
                                <option value="{{ $opt->value }}" {{ old('id_type', $beneficiary->id_type ?? '') === $opt->value ? 'selected' : '' }}>{{ $opt->label }}</option>
                            @endforeach
                        </select>
                        @error('id_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="id_number" class="form-label">ID Number</label>
                        <input type="text" class="form-control @error('id_number') is-invalid @enderror"
                               id="id_number" name="id_number" maxlength="100"
                               value="{{ old('id_number', $beneficiary->id_number ?? '') }}"
                               placeholder="Enter ID number">
                        @error('id_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Contact Number --}}
            <div class="col-12 col-md-4">
                <label for="contact_number" class="form-label">Contact Number <span class="text-danger">*</span></label>
                <div class="input-group has-validation">
                    <span class="input-group-text bg-white text-muted border-end-0 pe-2" id="contact_number_addon">
                        +63 <span class="mx-1 text-black-50">|</span>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-1 @error('contact_number') is-invalid @enderror"
                           id="contact_number" name="contact_number" placeholder="9XXXXXXXXX"
                           value="{{ preg_replace('/^(0|63)+/', '', preg_replace('/\D/', '', old('contact_number', $beneficiary->contact_number ?? ''))) }}"
                           inputmode="numeric"
                           maxlength="20"
                           pattern="^(\+639\d{9}|639\d{9}|09\d{9}|9\d{9})$"
                           oninput="this.value = this.value.replace(/\D/g, '').replace(/^(0|63)+/, '').substring(0, 10);"
                           title="Start with 9 (e.g. 9123456789)"
                           aria-describedby="contact_number_addon"
                           required>
                    @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="text-muted">Enter 10-digit mobile number</small>
            </div>



            @foreach($customFieldGroups->get('personal_information', collect()) as $customField)
                @include('beneficiaries.partials.custom-field-input', ['customField' => $customField, 'beneficiaryCustomFields' => $beneficiaryCustomFields])
            @endforeach

            <input type="hidden" name="photo_path" value="{{ old('photo_path', $beneficiary->photo_path ?? '') }}">
        </div>
</div>

{{-- SECTION 2 — Address Information --}}
<div class="mb-5 bg-white rounded-3 p-4 border" id="address-section">
    <div class="border-bottom pb-2 mb-4">
        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-geo-alt me-2 text-muted"></i>Address Information</h5>
    </div>
        <div class="row g-4">
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

{{-- SECTION 3 — Farmer Information --}}
<div class="mb-5 bg-white rounded-3 p-4 border" id="farmer-info-section" style="display: none;">
    <div class="border-bottom pb-2 mb-4">
        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-leaf me-2 text-muted"></i>Farmer Information</h5>
    </div>
    
    <div class="col-12 border rounded p-3 mb-4 bg-light bg-opacity-50">
        <div class="row g-3 align-items-center mb-3">
            <div class="col-md-4">
                <label for="rsbsa_availability_status" class="form-label mb-1 text-muted fw-semibold small text-uppercase">Farmer Data Availability <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm @error('rsbsa_availability_status') is-invalid @enderror"
                        id="rsbsa_availability_status"
                        name="rsbsa_availability_status">
                    <option value="provided" {{ $rsbsaAvailabilityStatus === 'provided' ? 'selected' : '' }}>Provided</option>
                    <option value="not_available_yet" {{ $rsbsaAvailabilityStatus === 'not_available_yet' ? 'selected' : '' }}>Not available yet</option>
                    <option value="not_applicable" {{ $rsbsaAvailabilityStatus === 'not_applicable' ? 'selected' : '' }}>Not applicable</option>
                    <option value="to_be_verified" {{ $rsbsaAvailabilityStatus === 'to_be_verified' ? 'selected' : '' }}>To be verified</option>
                </select>
                @error('rsbsa_availability_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-8">
                <div class="text-muted small">Select the current availability status for the farmer-specific profile (ownership, farm size, etc).</div>
            </div>
        </div>

        <div class="col-12 {{ $rsbsaAvailabilityStatus === 'provided' ? 'd-none' : '' }}" id="rsbsa-reason-wrapper">
            <div class="row">
                <div class="col-12 col-md-10 mt-2">
                    <label for="rsbsa_unavailability_reason" class="form-label text-danger">Reason for Unavailability <span class="text-danger">*</span></label>
                    <textarea class="form-control border-danger-subtle @error('rsbsa_unavailability_reason') is-invalid @enderror"
                              id="rsbsa_unavailability_reason" name="rsbsa_unavailability_reason" rows="2" maxlength="500"
                              placeholder="Explain why farmer fields are unavailable..."
                              {{ $rsbsaAvailabilityStatus !== 'provided' ? 'required' : '' }}>{{ old('rsbsa_unavailability_reason', $beneficiary->rsbsa_unavailability_reason ?? '') }}</textarea>
                    @error('rsbsa_unavailability_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div id="rsbsa-fields-wrapper" class="contents {{ $rsbsaAvailabilityStatus === 'provided' ? '' : 'd-none' }}">
        <div class="row g-4">
            <div class="col-12 col-md-4">
                <label for="farm_ownership" class="form-label">Farm Ownership {!! $farmOwnershipRequired ? '<span class="text-danger">*</span>' : '' !!}</label>
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
            
            <div class="col-12 col-md-4">
                <label for="farm_type" class="form-label">Farm Type {!! $farmTypeRequired ? '<span class="text-danger">*</span>' : '' !!}</label>
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

            <div class="col-12 col-md-4">
                <label for="farm_size_hectares" class="form-label">Farm Size (Hectares) <span class="text-danger">*</span></label>
                <input type="number" class="form-control @error('farm_size_hectares') is-invalid @enderror"
                       id="farm_size_hectares" name="farm_size_hectares" step="0.01" min="0"
                       value="{{ old('farm_size_hectares', $beneficiary->farm_size_hectares ?? '') }}">
                @error('farm_size_hectares')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            
            <div class="col-12 col-md-6">
                <label for="primary_commodity" class="form-label">Primary Commodity <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('primary_commodity') is-invalid @enderror"
                       id="primary_commodity" name="primary_commodity" maxlength="255"
                       value="{{ old('primary_commodity', $beneficiary->primary_commodity ?? '') }}">
                @error('primary_commodity')<div class="invalid-feedback">{{ $message }}</div>@enderror
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

            @foreach($customFieldGroups->get('dar_information', collect()) as $customField)
                @include('beneficiaries.partials.custom-field-input', ['customField' => $customField, 'beneficiaryCustomFields' => $beneficiaryCustomFields])
            @endforeach
        </div>
    </div>
</div>

{{-- SECTION 4 — Fisherfolk Information --}}
<div class="mb-5 bg-white rounded-3 p-4 border" id="fisherfolk-info-section" style="display: none;">
    <div class="border-bottom pb-2 mb-4">
        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-water me-2 text-muted"></i>Fisherfolk Information</h5>
    </div>
    
    <div class="col-12 border rounded p-3 mb-4 bg-light bg-opacity-50">
        <div class="row g-3 align-items-center mb-3">
            <div class="col-md-4">
                <label for="fishr_availability_status" class="form-label mb-1 text-muted fw-semibold small text-uppercase">Fisherfolk Data Availability <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm @error('fishr_availability_status') is-invalid @enderror"
                        id="fishr_availability_status"
                        name="fishr_availability_status">
                    <option value="provided" {{ $fishrAvailabilityStatus === 'provided' ? 'selected' : '' }}>Provided</option>
                    <option value="not_available_yet" {{ $fishrAvailabilityStatus === 'not_available_yet' ? 'selected' : '' }}>Not available yet</option>
                    <option value="not_applicable" {{ $fishrAvailabilityStatus === 'not_applicable' ? 'selected' : '' }}>Not applicable</option>
                    <option value="to_be_verified" {{ $fishrAvailabilityStatus === 'to_be_verified' ? 'selected' : '' }}>To be verified</option>
                </select>
                @error('fishr_availability_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-8">
                <div class="text-muted small">Select the current availability status for the fisherfolk-specific profile.</div>
            </div>
        </div>

        <div class="col-12 {{ $fishrAvailabilityStatus === 'provided' ? 'd-none' : '' }}" id="fishr-reason-wrapper">
            <div class="row">
                <div class="col-12 col-md-10 mt-2">
                    <label for="fishr_unavailability_reason" class="form-label text-danger">Reason for Unavailability <span class="text-danger">*</span></label>
                    <textarea class="form-control border-danger-subtle @error('fishr_unavailability_reason') is-invalid @enderror"
                              id="fishr_unavailability_reason" name="fishr_unavailability_reason" rows="2" maxlength="500"
                              placeholder="Explain why fisherfolk fields are unavailable..."
                              {{ $fishrAvailabilityStatus !== 'provided' ? 'required' : '' }}>{{ old('fishr_unavailability_reason', $beneficiary->fishr_unavailability_reason ?? '') }}</textarea>
                    @error('fishr_unavailability_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div id="fishr-fields-wrapper" class="contents {{ $fishrAvailabilityStatus === 'provided' ? '' : 'd-none' }}">
        <div class="row g-4">
            <div class="col-12 col-md-4">
                <label for="fisherfolk_type" class="form-label">Fisherfolk Type {!! $fisherfolkTypeRequired ? '<span class="text-danger">*</span>' : '' !!}</label>
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
            
            <div class="col-12 col-md-4">
                <label for="main_fishing_gear" class="form-label">Main Fishing Gear</label>
                <input type="text" class="form-control @error('main_fishing_gear') is-invalid @enderror"
                       id="main_fishing_gear" name="main_fishing_gear" maxlength="255"
                       value="{{ old('main_fishing_gear', $beneficiary->main_fishing_gear ?? '') }}">
                @error('main_fishing_gear')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            
            <div class="col-12 col-md-4">
                <label for="length_of_residency_months" class="form-label">Length of Residency (Months) <span class="text-danger">*</span></label>
                <input type="number" class="form-control @error('length_of_residency_months') is-invalid @enderror"
                       id="length_of_residency_months" name="length_of_residency_months" min="0"
                       value="{{ old('length_of_residency_months', $beneficiary->length_of_residency_months ?? '') }}">
                <small class="text-muted">At least 6 months per RA 8550</small>
                @error('length_of_residency_months')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            
            <div class="col-12">
                <div class="form-check form-switch bg-light p-3 rounded d-inline-block ps-5">
                    <input type="hidden" name="has_fishing_vessel" value="0">
                    <input type="checkbox" class="form-check-input" id="has_fishing_vessel" name="has_fishing_vessel" value="1"
                           {{ old('has_fishing_vessel', $beneficiary->has_fishing_vessel ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label ms-2 fw-medium" for="has_fishing_vessel">Has Fishing Vessel</label>
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




{{-- SECTION 6 — Dynamic Agency Form Fields --}}
<div class="mb-5 bg-white rounded-3 p-4 border" id="agency-dynamic-fields-section">
    <div class="border-bottom pb-2 mb-4">
        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-sliders me-2 text-muted"></i>Agency-Specific Dynamic Fields</h5>
    </div>
        <div id="dynamic-agencies-container">
            {{-- Will be populated by JavaScript based on selected agencies --}}
        </div>
</div>

<div
    id="existingAgencyDynamicData"
    class="d-none"
    data-values='@json($beneficiaryDynamicAgencyValues)'
    data-reasons='@json($beneficiaryDynamicAgencyReasons)'
    data-selected-agencies='@json($selectedAgencyIds)'
></div>

{{-- SECTION 8 — Association Membership --}}
<div class="mb-5 bg-white rounded-3 p-4 border" id="association-section">
    <div class="border-bottom pb-2 mb-4">
        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-people me-2 text-muted"></i>Association Membership</h5>
    </div>
        <div class="row g-4">
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



{{-- Submit / Cancel --}}
<div class="d-flex justify-content-center gap-3 mt-5 border-top pt-4" id="submit-section">
    <button type="submit" class="btn {{ $editing ? 'btn-primary' : 'btn-success' }}">
        <i class="bi bi-check-lg me-1"></i> {{ $editing ? 'Update Beneficiary' : 'Register Beneficiary' }}
    </button>
    <a href="{{ route('beneficiaries.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const MISSING_CONTEXT_REASON = 'Specific agency or classification is missing for this section.';

    const classificationSelect = document.getElementById('classification');
    const agencyCheckboxes = document.getElementById('agency-checkboxes');
    const farmerSection = document.getElementById('farmer-info-section');
    const fisherfolkSection = document.getElementById('fisherfolk-info-section');
    const associationCheckbox = document.getElementById('association_member');
    const associationWrapper = document.getElementById('association-name-wrapper');
    const hasVesselCheckbox = document.getElementById('has_fishing_vessel');
    const vesselTypeWrapper = document.getElementById('vessel-type-wrapper');
    const vesselTonnageWrapper = document.getElementById('vessel-tonnage-wrapper');
    const rsbsaAvailabilitySelect = document.getElementById('rsbsa_availability_status');
    const rsbsaFieldsWrapper = document.getElementById('rsbsa-fields-wrapper');
    const rsbsaReasonWrapper = document.getElementById('rsbsa-reason-wrapper');
    const rsbsaReasonField = document.getElementById('rsbsa_unavailability_reason');
    const fishrAvailabilitySelect = document.getElementById('fishr_availability_status');
    const fishrFieldsWrapper = document.getElementById('fishr-fields-wrapper');
    const fishrReasonWrapper = document.getElementById('fishr-reason-wrapper');
    const fishrReasonField = document.getElementById('fishr_unavailability_reason');

    const governmentIdAvailabilitySelect = document.getElementById('government_id_availability_status');
    const governmentIdFieldsWrapper = document.getElementById('government-id-fields-wrapper');
    const idTypeField = document.getElementById('id_type');
    const idNumberField = document.getElementById('id_number');

    function getSelectedAgencyNames() {
        return Array.from(document.querySelectorAll('#agency-checkboxes .agency-checkbox:checked'))
            .map(cb => cb.dataset.agencyName.toUpperCase());
    }

    function updateSections() {
        const classification = classificationSelect.value;
        const selectedAgencies = getSelectedAgencyNames();

        // Hide all sections first
        farmerSection.style.display = 'none';
        fisherfolkSection.style.display = 'none';

        // Show sections based on classification and selected agencies
        if (classification === 'Farmer') {
            farmerSection.style.display = 'block';
        } else if (classification === 'Fisherfolk') {
            fisherfolkSection.style.display = 'block';
        }
        

        toggleRsbsaAvailability();
        toggleFishrAvailability();


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

    function toggleSectionAvailability(statusSelect, fieldsWrapper, reasonWrapper, reasonField, requiredFieldIds = []) {
        if (!statusSelect || !fieldsWrapper || !reasonWrapper) {
            return;
        }

        const isProvided = statusSelect.value === 'provided';
        fieldsWrapper.classList.toggle('d-none', !isProvided);
        reasonWrapper.classList.toggle('d-none', isProvided);

        if (reasonField) {
            reasonField.required = !isProvided;

            if (!isProvided && statusSelect.value === 'not_applicable' && !reasonField.value.trim()) {
                reasonField.value = MISSING_CONTEXT_REASON;
            }
        }

        requiredFieldIds.forEach((fieldId) => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.required = isProvided;
            }
        });
    }

    function toggleRsbsaAvailability() {
        const isApplicable = classificationSelect && classificationSelect.value === 'Farmer';

        toggleSectionAvailability(
            rsbsaAvailabilitySelect,
            rsbsaFieldsWrapper,
            rsbsaReasonWrapper,
            rsbsaReasonField,
            isApplicable ? ['farm_ownership', 'farm_size_hectares', 'primary_commodity', 'farm_type'] : []
        );

        if (rsbsaAvailabilitySelect) {
            rsbsaAvailabilitySelect.disabled = !isApplicable;
        }

        if (!isApplicable) {
            if (rsbsaFieldsWrapper) {
                rsbsaFieldsWrapper.classList.add('d-none');
            }
            if (rsbsaReasonWrapper) {
                rsbsaReasonWrapper.classList.add('d-none');
            }
            if (rsbsaReasonField) {
                rsbsaReasonField.required = false;
            }

            ['farm_ownership', 'farm_size_hectares', 'primary_commodity', 'farm_type'].forEach((fieldId) => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.required = false;
                }
            });
        }
    }

    function toggleFishrAvailability() {
        const isApplicable = classificationSelect && classificationSelect.value === 'Fisherfolk';

        toggleSectionAvailability(
            fishrAvailabilitySelect,
            fishrFieldsWrapper,
            fishrReasonWrapper,
            fishrReasonField,
            isApplicable ? ['fisherfolk_type', 'length_of_residency_months'] : []
        );

        if (fishrAvailabilitySelect) {
            fishrAvailabilitySelect.disabled = !isApplicable;
        }

        if (!isApplicable) {
            if (fishrFieldsWrapper) {
                fishrFieldsWrapper.classList.add('d-none');
            }
            if (fishrReasonWrapper) {
                fishrReasonWrapper.classList.add('d-none');
            }
            if (fishrReasonField) {
                fishrReasonField.required = false;
            }

            ['fisherfolk_type', 'length_of_residency_months'].forEach((fieldId) => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.required = false;
                }
            });
        }
    }



    function toggleGovernmentIdFields() {
        if (!governmentIdAvailabilitySelect || !governmentIdFieldsWrapper) {
            return;
        }

        const isAvailable = governmentIdAvailabilitySelect.value === 'available';
        governmentIdFieldsWrapper.classList.toggle('d-none', !isAvailable);

        if (idTypeField) {
            idTypeField.required = isAvailable;
            if (!isAvailable) {
                idTypeField.setCustomValidity('');
            }
        }

        if (idNumberField) {
            idNumberField.required = isAvailable;
            if (!isAvailable) {
                idNumberField.setCustomValidity('');
            }
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

    if (rsbsaAvailabilitySelect) {
        rsbsaAvailabilitySelect.addEventListener('change', toggleRsbsaAvailability);
    }

    if (fishrAvailabilitySelect) {
        fishrAvailabilitySelect.addEventListener('change', toggleFishrAvailability);
    }



    if (governmentIdAvailabilitySelect) {
        governmentIdAvailabilitySelect.addEventListener('change', toggleGovernmentIdFields);
    }

    // Initial setup
    updateSections();
    toggleAssociation();
    toggleVesselFields();
    toggleRsbsaAvailability();
    toggleFishrAvailability();

    toggleGovernmentIdFields();
});
</script>
@endpush

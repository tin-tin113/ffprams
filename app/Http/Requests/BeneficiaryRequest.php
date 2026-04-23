<?php

namespace App\Http\Requests;

use App\Models\Agency;
use App\Models\AgencyFormField;
use App\Models\Beneficiary;
use App\Models\FormFieldOption;
use App\Support\BeneficiaryCoreFields;
use App\Support\PhilippineMobileNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class BeneficiaryRequest extends FormRequest
{
    private const NATIVE_FIELD_GROUPS = [
        'civil_status',
        'highest_education',
        'id_type',
        'farm_ownership',
        'farm_type',
        'fisherfolk_type',
        'arb_classification',
        'ownership_scheme',
    ];

    protected function prepareForValidation(): void
    {
        $first = trim((string) $this->input('first_name', ''));
        $middle = trim((string) $this->input('middle_name', ''));
        $last = trim((string) $this->input('last_name', ''));
        $suffix = trim((string) $this->input('name_suffix', ''));
        $contactNumber = trim((string) $this->input('contact_number', ''));
        $normalizedContactNumber = PhilippineMobileNumber::normalize($contactNumber);

        $fullName = trim(implode(' ', array_filter([$first, $middle, $last, $suffix])));

        $normalizedNativeFieldInputs = $this->normalizeNativeFieldInputs();
        $agencyCoreFieldInputs = $this->extractAgencyCoreFieldInputsForValidation();
        $defaultAvailabilityReasons = $this->defaultAvailabilityReasonsForMissingContext();

        $this->merge(array_merge([
            'first_name' => $first,
            'middle_name' => $middle,
            'last_name' => $last,
            'name_suffix' => $suffix,
            'full_name' => $fullName,
            'contact_number' => $normalizedContactNumber ?? $contactNumber,
        ], $normalizedNativeFieldInputs, $agencyCoreFieldInputs, $defaultAvailabilityReasons));
    }

    /**
     * Provide a sensible default reason when status is Not applicable because
     * required agency/classification context is missing.
     *
     * @return array<string, string>
     */
    private function defaultAvailabilityReasonsForMissingContext(): array
    {
        $classification = strtolower(trim((string) $this->input('classification', '')));
        $agencyIds = $this->extractAgencyIds((array) $this->input('agencies', []));
        $selectedAgencyNames = Agency::query()
            ->whereIn('id', $agencyIds)
            ->pluck('name')
            ->map(fn ($name) => strtoupper(trim((string) $name)))
            ->all();

        $hasDa = in_array('DA', $selectedAgencyNames, true);
        $hasBfar = in_array('BFAR', $selectedAgencyNames, true);
        $hasDar = in_array('DAR', $selectedAgencyNames, true);

        $defaultReason = 'Specific agency or classification is missing for this section.';
        $updates = [];

        $contextRules = [
            'rsbsa_unavailability_reason' => [
                'status' => 'rsbsa_availability_status',
                'is_applicable' => $classification === 'farmer' && $hasDa,
            ],
            'fishr_unavailability_reason' => [
                'status' => 'fishr_availability_status',
                'is_applicable' => $classification === 'fisherfolk' && ($hasDa || $hasBfar),
            ],
        ];

        foreach ($contextRules as $reasonKey => $rule) {
            $status = (string) $this->input($rule['status'], '');
            $reason = trim((string) $this->input($reasonKey, ''));

            if ($rule['is_applicable'] || $status !== 'not_applicable' || $reason !== '') {
                continue;
            }

            $updates[$reasonKey] = $defaultReason;
        }

        return $updates;
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $nativeFallbackValues = $this->nativeFieldFallbackValues();

        $beneficiaryId = $this->route('beneficiary')?->id;
        $agencyData = (array) $this->input('agencies', []);
        $agencyIds = $this->extractAgencyIds($agencyData);
        $selectedAgencies = Agency::whereIn('id', $agencyIds)->get();
        $fieldGroupSettings = $this->fieldGroupSettings();
        $classification = $this->input('classification');

        $civilStatusValues = $this->allowedFieldValues('civil_status', $nativeFallbackValues['civil_status'], $agencyIds);
        $highestEducationValues = $this->allowedFieldValues('highest_education', $nativeFallbackValues['highest_education'], $agencyIds);
        $farmOwnershipValues = $this->allowedFieldValues('farm_ownership', $nativeFallbackValues['farm_ownership'], $agencyIds);
        $farmTypeValues = $this->allowedFieldValues('farm_type', $nativeFallbackValues['farm_type'], $agencyIds);
        $fisherfolkTypeValues = $this->allowedFieldValues('fisherfolk_type', $nativeFallbackValues['fisherfolk_type'], $agencyIds);
        $arbClassificationValues = $this->allowedFieldValues('arb_classification', $nativeFallbackValues['arb_classification'], $agencyIds);
        $ownershipSchemeValues = $this->allowedFieldValues('ownership_scheme', $nativeFallbackValues['ownership_scheme'], $agencyIds);

        $civilStatusRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'civil_status', true);
        $highestEducationRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'highest_education', false);
        $farmOwnershipRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'farm_ownership', true);
        $farmTypeRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'farm_type', true);
        $fisherfolkTypeRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'fisherfolk_type', true);
        $arbClassificationRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'arb_classification', true);
        $ownershipSchemeRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'ownership_scheme', true);
        $rsbsaNumberRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'rsbsa_number', false);
        $farmSizeRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'farm_size_hectares', true);
        $primaryCommodityRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'primary_commodity', true);
        $organizationMembershipRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'organization_membership', false);
        $fishrNumberRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'fishr_number', false);
        $mainFishingGearRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'main_fishing_gear', false);
        $hasFishingVesselRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'has_fishing_vessel', false);
        $fishingVesselTypeRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'fishing_vessel_type', false);
        $fishingVesselTonnageRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'fishing_vessel_tonnage', false);
        $residencyMonthsRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'length_of_residency_months', true);
        $cloaNumberRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'cloa_ep_number', true);
        $landholdingDescriptionRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'landholding_description', true);
        $landAreaAwardedRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'land_area_awarded_hectares', true);
        $barcMembershipRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'barc_membership_status', false);

        $rules = [
            // Multiple agencies (multi-select) - handles both flat array [1,2,3] and nested array {1: {...}, 2: {...}}
            'agencies'         => [
                'required', 'array', 'min:1',
                function ($attribute, $value, $fail) use ($classification) {
                    if (! $classification) {
                        return; // Will fail on classification required rule first
                    }

                    // Extract agency IDs - handle both flat and nested array formats
                    $agencyIds = is_array($value) ? (
                        isset($value[0]) && !is_array($value[0])
                            ? $value // flat array [1, 2, 3]
                            : array_keys($value) // nested array {1: {...}, 2: {...}}
                    ) : [];

                    // Dynamic agency-classification validation using new system
                    $classificationModel = \App\Models\Classification::where('name', $classification)->first();
                    if ($classificationModel) {
                        $agenciesQuery = $classificationModel->agencies()
                            ->where('is_active', true)
                            ->pluck('agencies.id');

                        $validAgencyIds = $agenciesQuery ? $agenciesQuery->toArray() : [];

                        foreach ($agencyIds as $agencyId) {
                            if (is_array($validAgencyIds) && !in_array($agencyId, $validAgencyIds, true)) {
                                $agency = Agency::find($agencyId);
                                $fail("Agency '{$agency->name}' is not applicable to '{$classification}' classification.");
                            }
                        }
                    }

                    // Legacy hardcoded validation for backward compatibility
                    $agenciesQuery = Agency::whereIn('id', $agencyIds)
                        ->pluck('name')
                        ->map(fn($n) => strtoupper($n));

                    $selectedAgencies = $agenciesQuery ? $agenciesQuery->toArray() : [];

                    if (is_array($selectedAgencies)) {
                        if ($classification === 'Farmer' && in_array('BFAR', $selectedAgencies, true)) {
                            $fail('BFAR (Bureau of Fisheries) cannot be selected for Farmer classification.');
                        }

                        if ($classification === 'Fisherfolk' && in_array('DAR', $selectedAgencies, true)) {
                            $fail('DAR (Department of Agrarian Reform) cannot be selected for Fisherfolk classification.');
                        }
                    }
                }
            ],
            // agencies.* validation is not needed - nested array structure validates agency IDs via the key extraction above
            // Each nested agency object with its form fields is validated in the dynamic fields section below

            // Common fields per reference document
            'first_name'       => ['required', 'string', 'max:100'],
            'middle_name'      => ['nullable', 'string', 'max:100'],
            'last_name'        => ['required', 'string', 'max:100'],
            'name_suffix'      => ['nullable', 'string', 'max:20'],
            'full_name'        => ['required', 'string', 'max:255'],
            'sex'              => ['required', Rule::in(['Male', 'Female'])],
            'date_of_birth'    => ['required', 'date', 'before:today'],
            'home_address'     => ['required', 'string', 'max:500'],
            'barangay_id'      => ['required', 'exists:barangays,id'],
            'contact_number'   => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! PhilippineMobileNumber::isValid(is_string($value) ? $value : null)) {
                        $fail('Contact number must be a valid Philippine mobile number (e.g., 09XXXXXXXXX, 9XXXXXXXXX, 639XXXXXXXXX, +639XXXXXXXXX).');
                    }
                },
            ],
            'photo_path'       => ['nullable', 'string', 'max:255'],
            'civil_status'     => [$civilStatusRequired ? 'required' : 'nullable', Rule::in($civilStatusValues)],
            'highest_education'=> [$highestEducationRequired ? 'required' : 'nullable', Rule::in($highestEducationValues)],
            'government_id_availability_status' => ['nullable', 'in:available,not_available'],
            'id_type'          => ['nullable', 'required_if:government_id_availability_status,available', 'string', 'max:100'],
            'id_number'        => ['nullable', 'required_if:government_id_availability_status,available', 'string', 'max:100'],
            'status'           => ['required', Rule::in(['Active', 'Inactive'])],
            'registered_at'    => ['required', 'date', 'before_or_equal:today'],
            'classification'   => [
                'required',
                Rule::in(['Farmer', 'Fisherfolk']),
                function ($attribute, $value, $fail) {
                    // Lock classification if editing and registration numbers exist
                    $beneficiaryId = $this->route('beneficiary')?->id;
                    if ($beneficiaryId) {
                        $beneficiary = Beneficiary::find($beneficiaryId);
                        if ($beneficiary && (!empty($beneficiary->rsbsa_number) ||
                            !empty($beneficiary->fishr_number) ||
                            !empty($beneficiary->cloa_ep_number))) {
                            // Classification cannot be changed once identifiers are assigned
                            if ($value !== $beneficiary->classification) {
                                $fail('Classification cannot be changed after registration numbers are assigned.');
                            }
                        }
                    }
                },
            ],
            'custom_fields'    => ['nullable', 'array'],
            'rsbsa_availability_status' => ['nullable', 'in:provided,not_available_yet,not_applicable,to_be_verified'],
            'rsbsa_unavailability_reason' => ['nullable', 'string', 'max:500'],
            'fishr_availability_status' => ['nullable', 'in:provided,not_available_yet,not_applicable,to_be_verified'],
            'fishr_unavailability_reason' => ['nullable', 'string', 'max:500'],

            // Association membership (common to all)
            'association_member' => ['required', 'boolean'],
            'association_name'   => ['nullable', 'required_if:association_member,true', 'required_if:association_member,1', 'string', 'max:255'],
        ];

        // Validate fields based on EACH selected agency + classification
        foreach ($selectedAgencies as $agency) {
            $agencyName = strtoupper($agency->name);

            // DA with Farmer classification
            if ($agencyName === 'DA' && $classification === 'Farmer') {
                $rules['rsbsa_availability_status'] = ['required', 'in:provided,not_available_yet,not_applicable,to_be_verified'];
                $rules['rsbsa_unavailability_reason'] = ['nullable', 'string', 'max:500'];
                $rules['rsbsa_number'] = [$rsbsaNumberRequired ? 'required_if:rsbsa_availability_status,provided' : 'nullable', 'nullable', 'string', 'max:50', Rule::unique('beneficiaries', 'rsbsa_number')->ignore($beneficiaryId)];
                $rules['farm_ownership'] = [$farmOwnershipRequired ? 'required_if:rsbsa_availability_status,provided' : 'nullable', 'nullable', Rule::in($farmOwnershipValues)];
                $rules['farm_size_hectares'] = [$farmSizeRequired ? 'required_if:rsbsa_availability_status,provided' : 'nullable', 'nullable', 'numeric', 'min:0.01'];
                $rules['primary_commodity'] = [$primaryCommodityRequired ? 'required_if:rsbsa_availability_status,provided' : 'nullable', 'nullable', 'string', 'max:255'];
                $rules['farm_type'] = [$farmTypeRequired ? 'required_if:rsbsa_availability_status,provided' : 'nullable', 'nullable', Rule::in($farmTypeValues)];
                $rules['organization_membership'] = [$organizationMembershipRequired ? 'required_if:rsbsa_availability_status,provided' : 'nullable', 'nullable', 'string', 'max:255'];
            }

            // DA with Fisherfolk classification
            if ($agencyName === 'DA' && $classification === 'Fisherfolk') {
                $rules['fishr_availability_status'] = ['required', 'in:provided,not_available_yet,not_applicable,to_be_verified'];
                $rules['fishr_unavailability_reason'] = ['nullable', 'string', 'max:500'];
                $rules['rsbsa_number'] = [$rsbsaNumberRequired ? 'required_if:fishr_availability_status,provided' : 'nullable', 'nullable', 'string', 'max:50', Rule::unique('beneficiaries', 'rsbsa_number')->ignore($beneficiaryId)];
                $rules['fisherfolk_type'] = [$fisherfolkTypeRequired ? 'required_if:fishr_availability_status,provided' : 'nullable', 'nullable', Rule::in($fisherfolkTypeValues)];
                $rules['main_fishing_gear'] = [$mainFishingGearRequired ? 'required_if:fishr_availability_status,provided' : 'nullable', 'nullable', 'string', 'max:255'];
                $rules['has_fishing_vessel'] = [$hasFishingVesselRequired ? 'required_if:fishr_availability_status,provided' : 'nullable', 'nullable', 'boolean'];
                $rules['fishing_vessel_type'] = [$fishingVesselTypeRequired ? 'required_if:fishr_availability_status,provided' : 'nullable', 'nullable', 'string', 'max:255'];
                $rules['fishing_vessel_tonnage'] = [$fishingVesselTonnageRequired ? 'required_if:fishr_availability_status,provided' : 'nullable', 'nullable', 'numeric', 'min:0'];
                $rules['length_of_residency_months'] = [$residencyMonthsRequired ? 'required_if:fishr_availability_status,provided' : 'nullable', 'nullable', 'integer', 'min:6'];
            }

            // BFAR with Fisherfolk classification
            if ($agencyName === 'BFAR' && $classification === 'Fisherfolk') {
                $rules['fishr_availability_status'] = ['required', 'in:provided,not_available_yet,not_applicable,to_be_verified'];
                $rules['fishr_unavailability_reason'] = ['nullable', 'string', 'max:500'];
                $rules['fishr_number'] = [$fishrNumberRequired ? 'required_if:fishr_availability_status,provided' : 'nullable', 'nullable', 'string', 'max:50', Rule::unique('beneficiaries', 'fishr_number')->ignore($beneficiaryId)];
                $rules['fisherfolk_type'] = [$fisherfolkTypeRequired ? 'required_if:fishr_availability_status,provided' : 'nullable', 'nullable', Rule::in($fisherfolkTypeValues)];
                $rules['main_fishing_gear'] = [$mainFishingGearRequired ? 'required_if:fishr_availability_status,provided' : 'nullable', 'nullable', 'string', 'max:255'];
                $rules['has_fishing_vessel'] = [$hasFishingVesselRequired ? 'required_if:fishr_availability_status,provided' : 'nullable', 'nullable', 'boolean'];
                $rules['fishing_vessel_type'] = [$fishingVesselTypeRequired ? 'required_if:fishr_availability_status,provided' : 'nullable', 'nullable', 'string', 'max:255'];
                $rules['fishing_vessel_tonnage'] = [$fishingVesselTonnageRequired ? 'required_if:fishr_availability_status,provided' : 'nullable', 'nullable', 'numeric', 'min:0'];
                $rules['length_of_residency_months'] = [$residencyMonthsRequired ? 'required_if:fishr_availability_status,provided' : 'nullable', 'nullable', 'integer', 'min:6'];
            }


        }

        // If no fields have been set to required for farmer/fisherfolk/dar, make them optional
        if (!isset($rules['rsbsa_number'])) {
            $rules['rsbsa_number'] = ['nullable', 'string', 'max:50'];
        }
        if (!isset($rules['farm_ownership'])) {
            $rules['farm_ownership'] = ['nullable', Rule::in($farmOwnershipValues)];
        }
        if (!isset($rules['farm_size_hectares'])) {
            $rules['farm_size_hectares'] = ['nullable', 'numeric', 'min:0.01'];
        }
        if (!isset($rules['primary_commodity'])) {
            $rules['primary_commodity'] = ['nullable', 'string', 'max:255'];
        }
        if (!isset($rules['farm_type'])) {
            $rules['farm_type'] = ['nullable', Rule::in($farmTypeValues)];
        }
        if (!isset($rules['organization_membership'])) {
            $rules['organization_membership'] = ['nullable', 'string', 'max:255'];
        }
        if (!isset($rules['fishr_number'])) {
            $rules['fishr_number'] = ['nullable', 'string', 'max:50'];
        }
        if (!isset($rules['fisherfolk_type'])) {
            $rules['fisherfolk_type'] = ['nullable', Rule::in($fisherfolkTypeValues)];
        }
        if (!isset($rules['main_fishing_gear'])) {
            $rules['main_fishing_gear'] = ['nullable', 'string', 'max:255'];
        }
        if (!isset($rules['has_fishing_vessel'])) {
            $rules['has_fishing_vessel'] = ['nullable', 'boolean'];
        }
        if (!isset($rules['fishing_vessel_type'])) {
            $rules['fishing_vessel_type'] = ['nullable', 'string', 'max:255'];
        }
        if (!isset($rules['fishing_vessel_tonnage'])) {
            $rules['fishing_vessel_tonnage'] = ['nullable', 'numeric', 'min:0'];
        }
        if (!isset($rules['length_of_residency_months'])) {
            $rules['length_of_residency_months'] = ['nullable', 'integer', 'min:0'];
        }


        // ===== DYNAMIC AGENCY FORM FIELDS VALIDATION =====
        // Validate custom fields defined by each selected agency
        $allowedAgencySections = $this->allowedAgencyFormSections((string) $classification);

        $dynamicAvailabilityExemptFieldNames = ['rsbsa_number', 'fishr_number', 'cloa_ep_number'];

        foreach ($selectedAgencies as $agency) {
            /** @var \App\Models\Agency $agency */
            $agencyFormFields = $agency->formFields()
                ->where('is_active', true)
            ->whereIn('form_section', $allowedAgencySections)
                ->get();

            foreach ($agencyFormFields as $field) {
                $fieldName = $field->field_name;
                $agencyId = $agency->id;

                // Fields from the form are in format: agencies[{agencyId}][{fieldName}]
                $inputKey = "agencies.{$agencyId}.{$fieldName}";
                $statusKey = "agencies.{$agencyId}.{$fieldName}_availability_status";
                $reasonKey = "agencies.{$agencyId}.{$fieldName}_unavailability_reason";

                $requiresDynamicAvailability = $field->is_required
                    && ! in_array($fieldName, $dynamicAvailabilityExemptFieldNames, true);

                if ($requiresDynamicAvailability) {
                    // Required field: must have value OR unavailability reason
                    $rules[$inputKey] = ['nullable', $this->getFieldTypeValidation($field)];
                    $rules[$reasonKey] = ['nullable', 'string', 'max:500'];
                    $rules[$statusKey] = ['required', 'in:provided,not_available_yet,not_applicable,to_be_verified'];

                    // Will be validated in withValidator() hook
                } else {
                    // Optional field: just validate the field type if provided
                    $rules[$inputKey] = ['nullable', $this->getFieldTypeValidation($field)];
                }

                // For dropdown/checkbox types, validate against options
                if (in_array($field->field_type, ['dropdown', 'checkbox'])) {
                    $optionValues = $field->options()
                        ->where('is_active', true)
                        ->pluck('value')
                        ->toArray();

                    if (! empty($optionValues)) {
                        if ($field->field_type === 'checkbox') {
                            // For checkbox: each item must be in options
                            unset($rules[$inputKey]); // Remove generic rule
                            $rules[$inputKey] = ['nullable', 'array'];
                            $rules[$inputKey . '.*'] = ['in:' . implode(',', $optionValues)];
                        } else {
                            // For dropdown: single value must be in options
                            $rules[$inputKey] = ['nullable', 'in:' . implode(',', $optionValues)];
                        }
                    }
                }
            }
        }

        // Custom field validation
        $customGroupSettings = collect($fieldGroupSettings)
            ->except(self::NATIVE_FIELD_GROUPS)
            ->all();

        // Build agency-classification context for custom field visibility
        $agencyNamesCollection = $selectedAgencies ? $selectedAgencies->pluck('name') : collect([]);
        $selectedAgencyNames = $agencyNamesCollection
            ? $agencyNamesCollection->map(fn($n) => strtoupper((string) $n))->toArray()
            : [];

        $hasDa = is_array($selectedAgencyNames) && in_array('DA', $selectedAgencyNames, true);
        $hasBfar = is_array($selectedAgencyNames) && in_array('BFAR', $selectedAgencyNames, true);
        $hasDar = is_array($selectedAgencyNames) && in_array('DAR', $selectedAgencyNames, true);
        $isFarmer = $classification === 'Farmer';
        $isFisherfolk = $classification === 'Fisherfolk';

        foreach ($customGroupSettings as $fieldGroup => $groupSetting) {
            $fieldType = strtolower((string) ($groupSetting['field_type'] ?? FormFieldOption::FIELD_TYPE_DROPDOWN));
            $isOptionBased = in_array($fieldType, FormFieldOption::optionBasedFieldTypes(), true);

            $fieldRules = [];

            $placement = $groupSetting['placement_section'] ?? FormFieldOption::PLACEMENT_PERSONAL_INFORMATION;

            // Determine visibility based on multi-agency selection + classification
            $isVisible = match ($placement) {
                FormFieldOption::PLACEMENT_FARMER_INFORMATION => ($hasDa && $isFarmer) || $hasDar,
                FormFieldOption::PLACEMENT_FISHERFOLK_INFORMATION => ($hasDa && $isFisherfolk) || $hasBfar,
                FormFieldOption::PLACEMENT_DAR_INFORMATION => $hasDar,
                default => true, // PLACEMENT_PERSONAL_INFORMATION and others are always visible
            };

            $isRequired = (bool) ($groupSetting['is_required'] ?? false) && $isVisible;

            $fieldRules[] = $isRequired ? 'required' : 'nullable';

            if ($isOptionBased) {
                $allowedValues = $this->allowedFieldValues($fieldGroup, [], $agencyIds);

                if (empty($allowedValues)) {
                    continue;
                }

                if ($fieldType === FormFieldOption::FIELD_TYPE_CHECKBOX) {
                    $checkboxRules = [$isRequired ? 'required' : 'nullable', 'array'];
                    if ($isRequired) {
                        $checkboxRules[] = 'min:1';
                    }

                    $rules['custom_fields.' . $fieldGroup] = $checkboxRules;
                    $rules['custom_fields.' . $fieldGroup . '.*'] = [Rule::in($allowedValues)];

                    continue;
                }

                $fieldRules[] = Rule::in($allowedValues);
            } else {
                $fieldRules[] = $this->getGlobalFieldTypeValidationRule($fieldType);
            }

            $rules['custom_fields.' . $fieldGroup] = $fieldRules;
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'first_name.required'              => 'First name is required.',
            'last_name.required'               => 'Last name is required.',
            'registered_at.before_or_equal'     => 'Registration date cannot be a future date.',
            'date_of_birth.before'              => 'Date of birth must be a past date.',
            'cloa_ep_number.required'           => 'CLOA/EP number is required for DAR beneficiaries.',
            'cloa_ep_number.unique'             => 'A beneficiary with this CLOA/EP number already exists.',
            'length_of_residency_months.min'    => 'Fisherfolk must have at least 6 months residency per RA 8550.',
            'rsbsa_number.unique'               => 'A beneficiary with this RSBSA number already exists.',
            'fishr_number.unique'               => 'A beneficiary with this FishR number already exists.',
        ];
    }

    private function allowedFieldValues(string $fieldGroup, array $fallback, array $agencyIds = []): array
    {
        $dbValues = FormFieldOption::query()
            ->where('field_group', $fieldGroup)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['value', 'label'])
            ->map(function (FormFieldOption $option) use ($fieldGroup): string {
                $value = trim((string) $option->value);
                $label = trim((string) $option->label);

                if (in_array($fieldGroup, self::NATIVE_FIELD_GROUPS, true)) {
                    return $label !== '' ? $label : $value;
                }

                return $value !== '' ? $value : $label;
            })
            ->filter()
            ->values()
            ->all();

        $agencyValues = [];
        $agencyBackedCoreGroups = [
            'farm_ownership',
            'farm_type',
            'fisherfolk_type',
            'arb_classification',
            'ownership_scheme',
        ];

        if (! empty($agencyIds) && in_array($fieldGroup, $agencyBackedCoreGroups, true)) {
            $agencyValues = AgencyFormField::query()
                ->whereIn('agency_id', $agencyIds)
                ->where('field_name', $fieldGroup)
                ->where('is_active', true)
                ->whereIn('field_type', ['dropdown', 'checkbox'])
                ->with('options')
                ->get()
                ->flatMap(fn (AgencyFormField $field) => $field->options)
                ->map(function ($option): string {
                    $label = trim((string) ($option->label ?? ''));
                    $value = trim((string) ($option->value ?? ''));

                    return $label !== '' ? $label : $value;
                })
                ->filter()
                ->values()
                ->all();
        }

        $merged = array_values(array_unique(array_merge($fallback, $dbValues, $agencyValues)));

        if (empty($merged)) {
            return $fallback;
        }

        return $merged;
    }

    private function normalizeNativeFieldInputs(): array
    {
        $fallbackValues = $this->nativeFieldFallbackValues();
        $nativeFieldGroups = array_keys($fallbackValues);

        $maps = [];

        foreach ($fallbackValues as $fieldGroup => $labels) {
            foreach ($labels as $label) {
                $resolvedLabel = trim((string) $label);

                if ($resolvedLabel === '') {
                    continue;
                }

                $maps[$fieldGroup][strtolower($resolvedLabel)] = $resolvedLabel;
                $maps[$fieldGroup][$this->normalizeOptionKey($resolvedLabel)] = $resolvedLabel;
            }
        }

        $nativeOptions = FormFieldOption::query()
            ->whereIn('field_group', $nativeFieldGroups)
            ->where('is_active', true)
            ->get(['field_group', 'value', 'label']);

        foreach ($nativeOptions as $option) {
            $fieldGroup = (string) $option->field_group;
            $label = trim((string) $option->label);

            if ($label === '') {
                continue;
            }

            $maps[$fieldGroup][strtolower($label)] = $label;

            $valueKey = $this->normalizeOptionKey((string) $option->value);
            if ($valueKey !== '') {
                $maps[$fieldGroup][$valueKey] = $label;
            }

            $labelKey = $this->normalizeOptionKey($label);
            if ($labelKey !== '') {
                $maps[$fieldGroup][$labelKey] = $label;
            }
        }

        $agencyBackedCoreGroups = [
            'farm_ownership',
            'farm_type',
            'fisherfolk_type',
            'arb_classification',
            'ownership_scheme',
        ];
        $agencyIds = $this->extractAgencyIds((array) $this->input('agencies', []));

        if (! empty($agencyIds)) {
            $agencyNativeOptions = AgencyFormField::query()
                ->whereIn('agency_id', $agencyIds)
                ->whereIn('field_name', $agencyBackedCoreGroups)
                ->where('is_active', true)
                ->whereIn('field_type', ['dropdown', 'checkbox'])
                ->with('options')
                ->get(['id', 'field_name']);

            foreach ($agencyNativeOptions as $agencyField) {
                $fieldGroup = (string) $agencyField->field_name;

                foreach ($agencyField->options as $option) {
                    $label = trim((string) ($option->label ?? ''));
                    if ($label === '') {
                        $label = trim((string) ($option->value ?? ''));
                    }

                    if ($label === '') {
                        continue;
                    }

                    $maps[$fieldGroup][strtolower($label)] = $label;

                    $valueKey = $this->normalizeOptionKey((string) ($option->value ?? ''));
                    if ($valueKey !== '') {
                        $maps[$fieldGroup][$valueKey] = $label;
                    }

                    $labelKey = $this->normalizeOptionKey($label);
                    if ($labelKey !== '') {
                        $maps[$fieldGroup][$labelKey] = $label;
                    }
                }
            }
        }

        $normalized = [];

        foreach ($nativeFieldGroups as $fieldGroup) {
            $rawValue = $this->input($fieldGroup);

            if (! is_string($rawValue)) {
                continue;
            }

            $rawValue = trim($rawValue);

            if ($rawValue === '') {
                continue;
            }

            $lowerRaw = strtolower($rawValue);
            $normalizedRaw = $this->normalizeOptionKey($rawValue);

            if (isset($maps[$fieldGroup][$lowerRaw])) {
                $normalized[$fieldGroup] = $maps[$fieldGroup][$lowerRaw];

                continue;
            }

            if ($normalizedRaw !== '' && isset($maps[$fieldGroup][$normalizedRaw])) {
                $normalized[$fieldGroup] = $maps[$fieldGroup][$normalizedRaw];
            }
        }

        return $normalized;
    }

    private function normalizeOptionKey(string $input): string
    {
        $normalized = strtolower(trim($input));
        $normalized = preg_replace('/[^a-z0-9]+/', '_', $normalized) ?? '';

        return trim($normalized, '_');
    }

    /**
     * Ensure agency-nested core inputs are validated by top-level core rules.
     *
     * @return array<string, mixed>
     */
    private function extractAgencyCoreFieldInputsForValidation(): array
    {
        $agencyData = (array) $this->input('agencies', []);
        $coreFieldNames = BeneficiaryCoreFields::agencySpecificCoreFieldNames();
        $resolved = [];

        foreach ($agencyData as $agencyValues) {
            if (! is_array($agencyValues)) {
                continue;
            }

            foreach ($coreFieldNames as $fieldName) {
                if (! array_key_exists($fieldName, $agencyValues)) {
                    continue;
                }

                $currentValue = $this->input($fieldName);
                if ($currentValue !== null && $currentValue !== '') {
                    continue;
                }

                $value = $agencyValues[$fieldName];
                if ($value === null || $value === '') {
                    continue;
                }

                $resolved[$fieldName] = $value;
            }
        }

        return $resolved;
    }

    private function nativeFieldFallbackValues(): array
    {
        return [
            'civil_status' => ['Single', 'Married', 'Widowed', 'Separated'],
            'highest_education' => [
                'No Formal Education',
                'Elementary',
                'High School',
                'Vocational',
                'College',
                'Post Graduate',
            ],
            'id_type' => [
                'PhilSys ID',
                "Voter's ID",
                "Driver's License",
                'Passport',
                'Senior Citizen ID',
                'PWD ID',
                'Postal ID',
                'TIN ID',
            ],
            'farm_ownership' => ['Registered Owner', 'Tenant', 'Lessee', 'Owner', 'Share Tenant'],
            'farm_type' => ['Irrigated', 'Rainfed Upland', 'Rainfed Lowland', 'Upland'],
            'fisherfolk_type' => ['Capture Fishing', 'Aquaculture', 'Post-Harvest', 'Fish Farming', 'Fish Vendor', 'Fish Worker'],
            'arb_classification' => [
                'Agricultural Lessee',
                'Regular Farmworker',
                'Seasonal Farmworker',
                'Other Farmworker',
                'Actual Tiller',
                'Collective/Cooperative',
                'Others',
            ],
            'ownership_scheme' => ['Individual', 'Collective', 'Cooperative'],
        ];
    }

    private function fieldGroupSettings(): array
    {
        $selectColumns = ['field_group', 'placement_section', 'is_required'];
        if (Schema::hasColumn('form_field_options', 'field_type')) {
            $selectColumns[] = 'field_type';
        }

        return FormFieldOption::query()
            ->where('is_active', true)
            ->orderBy('field_group')
            ->orderByDesc('id')
            ->get($selectColumns)
            ->groupBy('field_group')
            ->map(function ($rows) {
                $first = $rows->first();

                return [
                    'placement_section' => $first?->placement_section ?? FormFieldOption::PLACEMENT_PERSONAL_INFORMATION,
                    'is_required' => (bool) ($first?->is_required ?? false),
                    'field_type' => $first?->field_type ?? FormFieldOption::FIELD_TYPE_DROPDOWN,
                ];
            })
            ->toArray();
    }

    private function getGlobalFieldTypeValidationRule(string $fieldType): string
    {
        return match ($fieldType) {
            'number' => 'integer',
            'decimal' => 'numeric',
            'date', 'datetime' => 'date',
            default => 'string',
        };
    }

    private function isFieldGroupRequired(array $settings, string $fieldGroup, bool $fallback): bool
    {
        if (! array_key_exists($fieldGroup, $settings)) {
            return $fallback;
        }

        return (bool) ($settings[$fieldGroup]['is_required'] ?? $fallback);
    }

    /**
     * Get validation rule for a dynamic form field based on its type
     */
    private function getFieldTypeValidation(\App\Models\AgencyFormField $field): string
    {
        return match ($field->field_type) {
            'number' => 'integer',
            'decimal' => 'numeric',
            'date' => 'date',
            'datetime' => 'date',
            'dropdown' => 'string',
            'checkbox' => 'array',
            default => 'string',
        };
    }

    /**
     * Validate required dynamic fields with unavailability reason alternative
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $agencyData = (array) $this->input('agencies', []);
            $selectedAgencyIds = $this->extractAgencyIds($agencyData);
            $selectedAgencies = \App\Models\Agency::whereIn('id', $selectedAgencyIds)->get();
            $classification = (string) $this->input('classification', '');
            $allowedAgencySections = $this->allowedAgencyFormSections($classification);

            // DAR validation is now handled by the dynamic agency field validation loop below.

            $hasDaForFarmer = strtolower($classification) === 'farmer'
                && $selectedAgencies->contains(fn ($agency) => strtoupper((string) $agency->name) === 'DA');

            if ($hasDaForFarmer) {
                $status = (string) $this->input('rsbsa_availability_status', '');
                $reason = $this->input('rsbsa_unavailability_reason');
                $validStatuses = ['provided', 'not_available_yet', 'not_applicable', 'to_be_verified'];

                if (! in_array($status, $validStatuses, true)) {
                    $validator->errors()->add(
                        'rsbsa_availability_status',
                        'Please select DA/RSBSA availability status.'
                    );
                } elseif ($status !== 'provided' && empty($reason)) {
                    $validator->errors()->add(
                        'rsbsa_unavailability_reason',
                        'Please provide a reason when DA/RSBSA fields are not marked as Provided.'
                    );
                }
            }

            $hasFishrSection = strtolower($classification) === 'fisherfolk'
                && $selectedAgencies->contains(fn ($agency) => in_array(strtoupper((string) $agency->name), ['DA', 'BFAR'], true));

            if ($hasFishrSection) {
                $status = (string) $this->input('fishr_availability_status', '');
                $reason = $this->input('fishr_unavailability_reason');
                $validStatuses = ['provided', 'not_available_yet', 'not_applicable', 'to_be_verified'];

                if (! in_array($status, $validStatuses, true)) {
                    $validator->errors()->add(
                        'fishr_availability_status',
                        'Please select FishR/BFAR availability status.'
                    );
                } elseif ($status !== 'provided' && empty($reason)) {
                    $validator->errors()->add(
                        'fishr_unavailability_reason',
                        'Please provide a reason when FishR/BFAR fields are not marked as Provided.'
                    );
                }
            }

            $dynamicAvailabilityExemptFieldNames = ['rsbsa_number', 'fishr_number', 'cloa_ep_number'];

            foreach ($selectedAgencies as $agency) {
                /** @var \App\Models\Agency $agency */
                $agencyFormFields = $agency->formFields()
                    ->where('is_active', true)
                    ->where('is_required', true)
                    ->whereIn('form_section', $allowedAgencySections)
                    ->get();

                foreach ($agencyFormFields as $field) {
                    $fieldName = $field->field_name;
                    if (in_array($fieldName, $dynamicAvailabilityExemptFieldNames, true)) {
                        continue;
                    }

                    $agencyId = $agency->id;

                    $fieldValue = $this->input("agencies.{$agencyId}.{$fieldName}");
                    $reasonValue = $this->input("agencies.{$agencyId}.{$fieldName}_unavailability_reason");
                    $status = $this->input("agencies.{$agencyId}.{$fieldName}_availability_status");
                    $validStatuses = ['provided', 'not_available_yet', 'not_applicable', 'to_be_verified'];

                    // Required field must have: value OR reason
                    if (! in_array($status, $validStatuses, true)) {
                        $validator->errors()->add(
                            "agencies.{$agencyId}.{$fieldName}_availability_status",
                            "Please select a status for {$field->display_label}."
                        );
                    } elseif ($status === 'provided' && empty($fieldValue)) {
                        $validator->errors()->add(
                            "agencies.{$agencyId}.{$fieldName}",
                            "{$field->display_label} is required when status is set to Provided."
                        );
                    } elseif ($status !== 'provided' && empty($reasonValue)) {
                        $validator->errors()->add(
                            "agencies.{$agencyId}.{$fieldName}_unavailability_reason",
                            "Please provide a reason when {$field->display_label} is not marked as Provided."
                        );
                    }
                }
            }
        });
    }

    /**
     * @param  array<int|string, mixed>  $agencyData
     * @return array<int>
     */
    private function extractAgencyIds(array $agencyData): array
    {
        $agencyIds = [];

        foreach ($agencyData as $key => $value) {
            if (is_numeric($key)) {
                $keyId = (int) $key;
                if ($keyId > 0) {
                    $agencyIds[] = $keyId;
                }

                if (! is_array($value) && is_numeric($value)) {
                    $valueId = (int) $value;
                    if ($valueId > 0) {
                        $agencyIds[] = $valueId;
                    }
                }

                continue;
            }

            if (is_array($value)) {
                if (isset($value['id']) && is_numeric($value['id'])) {
                    $agencyId = (int) $value['id'];
                    if ($agencyId > 0) {
                        $agencyIds[] = $agencyId;
                    }
                }

                continue;
            }

            if (is_numeric($value)) {
                $agencyId = (int) $value;
                if ($agencyId > 0) {
                    $agencyIds[] = $agencyId;
                }
            }
        }

        return array_values(array_unique($agencyIds));
    }

    /**
     * @return array<int, string>
     */
    private function allowedAgencyFormSections(string $classification): array
    {
        $normalized = strtolower(trim($classification));

        $baseSections = [
            'general_information',
            'additional_information',
            '',
        ];

        if ($normalized === 'farmer') {
            return array_values(array_unique(array_merge($baseSections, [
                'farmer_information',
                'dar_information',
            ])));
        }

        if ($normalized === 'fisherfolk') {
            return array_values(array_unique(array_merge($baseSections, [
                'fisherfolk_information',
            ])));
        }

        return array_values(array_unique(array_merge($baseSections, [
            'farmer_information',
            'fisherfolk_information',
            'dar_information',
        ])));
    }

}

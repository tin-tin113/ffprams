<?php

namespace App\Http\Requests;

use App\Models\Agency;
use App\Models\Beneficiary;
use App\Models\FormFieldOption;
use App\Support\PhilippineMobileNumber;
use Illuminate\Foundation\Http\FormRequest;
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

        $this->merge(array_merge([
            'first_name' => $first,
            'middle_name' => $middle,
            'last_name' => $last,
            'name_suffix' => $suffix,
            'full_name' => $fullName,
            'contact_number' => $normalizedContactNumber ?? $contactNumber,
        ], $normalizedNativeFieldInputs));
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $nativeFallbackValues = $this->nativeFieldFallbackValues();

        $beneficiaryId = $this->route('beneficiary')?->id;
        $agencyIds = (array) $this->input('agencies', []);
        $selectedAgencies = Agency::whereIn('id', $agencyIds)->get();
        $fieldGroupSettings = $this->fieldGroupSettings();
        $classification = $this->input('classification');

        $civilStatusValues = $this->allowedFieldValues('civil_status', $nativeFallbackValues['civil_status']);
        $highestEducationValues = $this->allowedFieldValues('highest_education', $nativeFallbackValues['highest_education']);
        $idTypeValues = $this->allowedFieldValues('id_type', $nativeFallbackValues['id_type']);
        $farmOwnershipValues = $this->allowedFieldValues('farm_ownership', $nativeFallbackValues['farm_ownership']);
        $farmTypeValues = $this->allowedFieldValues('farm_type', $nativeFallbackValues['farm_type']);
        $fisherfolkTypeValues = $this->allowedFieldValues('fisherfolk_type', $nativeFallbackValues['fisherfolk_type']);
        $arbClassificationValues = $this->allowedFieldValues('arb_classification', $nativeFallbackValues['arb_classification']);
        $ownershipSchemeValues = $this->allowedFieldValues('ownership_scheme', $nativeFallbackValues['ownership_scheme']);

        $civilStatusRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'civil_status', true);
        $highestEducationRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'highest_education', false);
        $idTypeRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'id_type', false);
        $farmOwnershipRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'farm_ownership', true);
        $farmTypeRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'farm_type', true);
        $fisherfolkTypeRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'fisherfolk_type', true);
        $arbClassificationRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'arb_classification', true);
        $ownershipSchemeRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'ownership_scheme', true);

        $rules = [
            // Multiple agencies (multi-select)
            'agencies'         => [
                'required', 'array', 'min:1',
                function ($attribute, $value, $fail) {
                    $classification = $this->input('classification');
                    $selectedAgencies = Agency::whereIn('id', $value)
                        ->pluck('name')
                        ->map(fn($n) => strtoupper($n))
                        ->toArray();

                    // Validate agency-classification compatibility
                    if ($classification === 'Farmer' && in_array('BFAR', $selectedAgencies)) {
                        $fail('BFAR (Bureau of Fisheries) cannot be selected for Farmer classification.');
                    }

                    if ($classification === 'Fisherfolk' && in_array('DAR', $selectedAgencies)) {
                        $fail('DAR (Department of Agrarian Reform) cannot be selected for Fisherfolk classification.');
                    }
                }
            ],
            'agencies.*'       => ['required', 'integer', 'exists:agencies,id'],

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
            'id_type'          => [$idTypeRequired ? 'required' : 'nullable', Rule::in($idTypeValues)],
            'government_id'    => ['nullable', 'string', 'max:100'],
            'household_size'   => ['nullable', 'integer', 'min:1'],
            'number_of_dependents' => ['nullable', 'integer', 'min:0'],
            'main_income_source' => ['nullable', 'string', 'max:255'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_number' => ['nullable', 'string', 'max:20'],
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

            // Association membership (common to all)
            'association_member' => ['required', 'boolean'],
            'association_name'   => ['nullable', 'required_if:association_member,true', 'required_if:association_member,1', 'string', 'max:255'],
        ];

        // Validate fields based on EACH selected agency + classification
        foreach ($selectedAgencies as $agency) {
            $agencyName = strtoupper($agency->name);

            // DA with Farmer classification
            if ($agencyName === 'DA' && $classification === 'Farmer') {
                $rules['rsbsa_number'] = ['nullable', 'string', 'max:50', Rule::unique('beneficiaries', 'rsbsa_number')->ignore($beneficiaryId)];
                $rules['farm_ownership'] = [$farmOwnershipRequired ? 'required' : 'nullable', Rule::in($farmOwnershipValues)];
                $rules['farm_size_hectares'] = ['required', 'numeric', 'min:0.01'];
                $rules['primary_commodity'] = ['required', 'string', 'max:255'];
                $rules['farm_type'] = [$farmTypeRequired ? 'required' : 'nullable', Rule::in($farmTypeValues)];
                $rules['organization_membership'] = ['nullable', 'string', 'max:255'];
            }

            // DA with Fisherfolk classification
            if ($agencyName === 'DA' && $classification === 'Fisherfolk') {
                $rules['rsbsa_number'] = ['nullable', 'string', 'max:50', Rule::unique('beneficiaries', 'rsbsa_number')->ignore($beneficiaryId)];
                $rules['fisherfolk_type'] = [$fisherfolkTypeRequired ? 'required' : 'nullable', Rule::in($fisherfolkTypeValues)];
                $rules['main_fishing_gear'] = ['nullable', 'string', 'max:255'];
                $rules['has_fishing_vessel'] = ['nullable', 'boolean'];
                $rules['fishing_vessel_type'] = ['nullable', 'string', 'max:255'];
                $rules['fishing_vessel_tonnage'] = ['nullable', 'numeric', 'min:0'];
                $rules['length_of_residency_months'] = ['required', 'integer', 'min:6'];
            }

            // BFAR with Fisherfolk classification
            if ($agencyName === 'BFAR' && $classification === 'Fisherfolk') {
                $rules['fishr_number'] = ['nullable', 'string', 'max:50', Rule::unique('beneficiaries', 'fishr_number')->ignore($beneficiaryId)];
                $rules['fisherfolk_type'] = [$fisherfolkTypeRequired ? 'required' : 'nullable', Rule::in($fisherfolkTypeValues)];
                $rules['main_fishing_gear'] = ['nullable', 'string', 'max:255'];
                $rules['has_fishing_vessel'] = ['nullable', 'boolean'];
                $rules['fishing_vessel_type'] = ['nullable', 'string', 'max:255'];
                $rules['fishing_vessel_tonnage'] = ['nullable', 'numeric', 'min:0'];
                $rules['length_of_residency_months'] = ['required', 'integer', 'min:6'];
            }

            // DAR with Farmer classification
            if ($agencyName === 'DAR' && $classification === 'Farmer') {
                $rules['cloa_ep_number'] = ['required', 'string', 'max:100', Rule::unique('beneficiaries', 'cloa_ep_number')->ignore($beneficiaryId)];
                $rules['arb_classification'] = [$arbClassificationRequired ? 'required' : 'nullable', Rule::in($arbClassificationValues)];
                $rules['landholding_description'] = ['required', 'string', 'max:1000'];
                $rules['land_area_awarded_hectares'] = ['required', 'numeric', 'min:0.01'];
                $rules['ownership_scheme'] = [$ownershipSchemeRequired ? 'required' : 'nullable', Rule::in($ownershipSchemeValues)];
                $rules['barc_membership_status'] = ['nullable', 'string', 'max:100'];
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
        if (!isset($rules['cloa_ep_number'])) {
            $rules['cloa_ep_number'] = ['nullable', 'string', 'max:100'];
        }
        if (!isset($rules['arb_classification'])) {
            $rules['arb_classification'] = ['nullable', 'string', 'max:100'];
        }
        if (!isset($rules['landholding_description'])) {
            $rules['landholding_description'] = ['nullable', 'string', 'max:1000'];
        }
        if (!isset($rules['land_area_awarded_hectares'])) {
            $rules['land_area_awarded_hectares'] = ['nullable', 'numeric', 'min:0.01'];
        }
        if (!isset($rules['ownership_scheme'])) {
            $rules['ownership_scheme'] = ['nullable', Rule::in($ownershipSchemeValues)];
        }
        if (!isset($rules['barc_membership_status'])) {
            $rules['barc_membership_status'] = ['nullable', 'string', 'max:100'];
        }

        // Custom field validation
        $customGroupSettings = collect($fieldGroupSettings)
            ->except(self::NATIVE_FIELD_GROUPS)
            ->all();

        // Build agency-classification context for custom field visibility
        $selectedAgencyNames = $selectedAgencies->pluck('name')->map(fn($n) => strtoupper($n))->toArray();
        $hasDa = in_array('DA', $selectedAgencyNames, true);
        $hasBfar = in_array('BFAR', $selectedAgencyNames, true);
        $hasDar = in_array('DAR', $selectedAgencyNames, true);
        $isFarmer = $classification === 'Farmer';
        $isFisherfolk = $classification === 'Fisherfolk';

        foreach ($customGroupSettings as $fieldGroup => $groupSetting) {
            $allowedValues = $this->allowedFieldValues($fieldGroup, []);

            if (empty($allowedValues)) {
                continue;
            }

            $placement = $groupSetting['placement_section'] ?? FormFieldOption::PLACEMENT_PERSONAL_INFORMATION;

            // Determine visibility based on multi-agency selection + classification
            $isVisible = match ($placement) {
                FormFieldOption::PLACEMENT_FARMER_INFORMATION => ($hasDa && $isFarmer) || $hasDar,
                FormFieldOption::PLACEMENT_FISHERFOLK_INFORMATION => ($hasDa && $isFisherfolk) || $hasBfar,
                FormFieldOption::PLACEMENT_DAR_INFORMATION => $hasDar,
                default => true, // PLACEMENT_PERSONAL_INFORMATION and others are always visible
            };

            $isRequired = (bool) ($groupSetting['is_required'] ?? false) && $isVisible;

            $rules['custom_fields.' . $fieldGroup] = [
                $isRequired ? 'required' : 'nullable',
                Rule::in($allowedValues),
            ];
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

    private function allowedFieldValues(string $fieldGroup, array $fallback): array
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

        if (empty($dbValues)) {
            return $fallback;
        }

        return array_values(array_unique(array_merge($fallback, $dbValues)));
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
        return FormFieldOption::query()
            ->where('is_active', true)
            ->orderBy('field_group')
            ->orderByDesc('id')
            ->get(['field_group', 'placement_section', 'is_required'])
            ->groupBy('field_group')
            ->map(function ($rows) {
                $first = $rows->first();

                return [
                    'placement_section' => $first?->placement_section ?? FormFieldOption::PLACEMENT_PERSONAL_INFORMATION,
                    'is_required' => (bool) ($first?->is_required ?? false),
                ];
            })
            ->toArray();
    }

    private function isFieldGroupRequired(array $settings, string $fieldGroup, bool $fallback): bool
    {
        if (! array_key_exists($fieldGroup, $settings)) {
            return $fallback;
        }

        return (bool) ($settings[$fieldGroup]['is_required'] ?? $fallback);
    }

}

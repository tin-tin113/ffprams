<?php

namespace App\Http\Requests;

use App\Models\Agency;
use App\Models\FormFieldOption;
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

        $fullName = trim(implode(' ', array_filter([$first, $middle, $last, $suffix])));

        $this->merge([
            'first_name' => $first,
            'middle_name' => $middle,
            'last_name' => $last,
            'name_suffix' => $suffix,
            'full_name' => $fullName,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $beneficiaryId = $this->route('beneficiary')?->id;
        $agencyId = $this->input('agency_id');
        $agency = $agencyId ? Agency::find($agencyId) : null;
        $agencyName = $agency?->name ? strtoupper($agency->name) : null;
        $fieldGroupSettings = $this->fieldGroupSettings();

        $civilStatusValues = $this->allowedFieldValues('civil_status', ['Single', 'Married', 'Widowed', 'Separated']);
        $highestEducationValues = $this->allowedFieldValues('highest_education', [
            'No Formal Education',
            'Elementary',
            'High School',
            'Vocational',
            'College',
            'Post Graduate',
        ]);
        $idTypeValues = $this->allowedFieldValues('id_type', [
            'PhilSys ID',
            "Voter's ID",
            "Driver's License",
            'Passport',
            'Senior Citizen ID',
            'PWD ID',
            'Postal ID',
            'TIN ID',
        ]);
        $farmOwnershipValues = $this->allowedFieldValues('farm_ownership', ['Registered Owner', 'Tenant', 'Lessee']);
        $farmTypeValues = $this->allowedFieldValues('farm_type', ['Irrigated', 'Rainfed Upland', 'Rainfed Lowland']);
        $fisherfolkTypeValues = $this->allowedFieldValues('fisherfolk_type', ['Capture Fishing', 'Aquaculture', 'Post-Harvest']);
        $arbClassificationValues = $this->allowedFieldValues('arb_classification', [
            'Agricultural Lessee',
            'Regular Farmworker',
            'Seasonal Farmworker',
            'Other Farmworker',
            'Actual Tiller',
            'Collective/Cooperative',
            'Others',
        ]);
        $ownershipSchemeValues = $this->allowedFieldValues('ownership_scheme', ['Individual', 'Collective', 'Cooperative']);

        $civilStatusRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'civil_status', true);
        $highestEducationRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'highest_education', false);
        $idTypeRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'id_type', false);
        $farmOwnershipRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'farm_ownership', true);
        $farmTypeRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'farm_type', true);
        $fisherfolkTypeRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'fisherfolk_type', true);
        $arbClassificationRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'arb_classification', true);
        $ownershipSchemeRequired = $this->isFieldGroupRequired($fieldGroupSettings, 'ownership_scheme', true);

        $rules = [
            // Agency source (determines which fields are required)
            'agency_id'        => ['required', 'exists:agencies,id'],

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
            'contact_number'   => ['required', 'string', 'regex:/^09\d{9}$/'],
            'photo_path'       => ['nullable', 'string', 'max:255'],
            'civil_status'     => [$civilStatusRequired ? 'required' : 'nullable', Rule::in($civilStatusValues)],
            'highest_education'=> [$highestEducationRequired ? 'required' : 'nullable', Rule::in($highestEducationValues)],
            'id_type'          => [$idTypeRequired ? 'required' : 'nullable', Rule::in($idTypeValues)],
            'status'           => ['required', Rule::in(['Active', 'Inactive'])],
            'registered_at'    => ['required', 'date', 'before_or_equal:today'],
            'classification'   => ['required', Rule::in(['Farmer', 'Fisherfolk', 'Both'])],
            'custom_fields'    => ['nullable', 'array'],

            // Association membership (common to all)
            'association_member' => ['required', 'boolean'],
            'association_name'   => ['nullable', 'required_if:association_member,true', 'required_if:association_member,1', 'string', 'max:255'],
        ];

        // DA/RSBSA fields (required when agency is DA or classification is Farmer/Both)
        $isDa = $agencyName === 'DA';
        $isFarmer = in_array($this->input('classification'), ['Farmer', 'Both']);

        if ($isDa || $isFarmer) {
            $rules['rsbsa_number'] = ['nullable', 'string', 'max:50', Rule::unique('beneficiaries', 'rsbsa_number')->ignore($beneficiaryId)];
            $rules['farm_ownership'] = [$farmOwnershipRequired ? 'required' : 'nullable', Rule::in($farmOwnershipValues)];
            $rules['farm_size_hectares'] = ['required', 'numeric', 'min:0.01'];
            $rules['primary_commodity'] = ['required', 'string', 'max:255'];
            $rules['farm_type'] = [$farmTypeRequired ? 'required' : 'nullable', Rule::in($farmTypeValues)];
            $rules['organization_membership'] = ['nullable', 'string', 'max:255'];
        } else {
            $rules['rsbsa_number'] = ['nullable', 'string', 'max:50'];
            $rules['farm_ownership'] = ['nullable', Rule::in($farmOwnershipValues)];
            $rules['farm_size_hectares'] = ['nullable', 'numeric', 'min:0.01'];
            $rules['primary_commodity'] = ['nullable', 'string', 'max:255'];
            $rules['farm_type'] = ['nullable', Rule::in($farmTypeValues)];
            $rules['organization_membership'] = ['nullable', 'string', 'max:255'];
        }

        // BFAR/FishR fields (required when agency is BFAR or classification is Fisherfolk/Both)
        $isBfar = $agencyName === 'BFAR';
        $isFisherfolk = in_array($this->input('classification'), ['Fisherfolk', 'Both']);

        if ($isBfar || $isFisherfolk) {
            $rules['fishr_number'] = ['nullable', 'string', 'max:50', Rule::unique('beneficiaries', 'fishr_number')->ignore($beneficiaryId)];
            $rules['fisherfolk_type'] = [$fisherfolkTypeRequired ? 'required' : 'nullable', Rule::in($fisherfolkTypeValues)];
            $rules['main_fishing_gear'] = ['nullable', 'string', 'max:255'];
            $rules['has_fishing_vessel'] = ['nullable', 'boolean'];
            $rules['fishing_vessel_type'] = ['nullable', 'string', 'max:255'];
            $rules['fishing_vessel_tonnage'] = ['nullable', 'numeric', 'min:0'];
            $rules['length_of_residency_months'] = ['required', 'integer', 'min:6'];
        } else {
            $rules['fishr_number'] = ['nullable', 'string', 'max:50'];
            $rules['fisherfolk_type'] = ['nullable', Rule::in($fisherfolkTypeValues)];
            $rules['main_fishing_gear'] = ['nullable', 'string', 'max:255'];
            $rules['has_fishing_vessel'] = ['nullable', 'boolean'];
            $rules['fishing_vessel_type'] = ['nullable', 'string', 'max:255'];
            $rules['fishing_vessel_tonnage'] = ['nullable', 'numeric', 'min:0'];
            $rules['length_of_residency_months'] = ['nullable', 'integer', 'min:0'];
        }

        // DAR/ARB fields (required when agency is DAR)
        $isDar = $agencyName === 'DAR';

        if ($isDar) {
            // CLOA/EP number is REQUIRED for DAR beneficiaries per reference document
            $rules['cloa_ep_number'] = ['required', 'string', 'max:100', Rule::unique('beneficiaries', 'cloa_ep_number')->ignore($beneficiaryId)];
            $rules['arb_classification'] = [$arbClassificationRequired ? 'required' : 'nullable', Rule::in($arbClassificationValues)];
            $rules['landholding_description'] = ['required', 'string', 'max:1000'];
            $rules['land_area_awarded_hectares'] = ['required', 'numeric', 'min:0.01'];
            $rules['ownership_scheme'] = [$ownershipSchemeRequired ? 'required' : 'nullable', Rule::in($ownershipSchemeValues)];
            $rules['barc_membership_status'] = ['nullable', 'string', 'max:100'];
        } else {
            $rules['cloa_ep_number'] = ['nullable', 'string', 'max:100'];
            $rules['arb_classification'] = ['nullable', 'string', 'max:100'];
            $rules['landholding_description'] = ['nullable', 'string', 'max:1000'];
            $rules['land_area_awarded_hectares'] = ['nullable', 'numeric', 'min:0.01'];
            $rules['ownership_scheme'] = ['nullable', Rule::in($ownershipSchemeValues)];
            $rules['barc_membership_status'] = ['nullable', 'string', 'max:100'];
        }

        $customGroupSettings = collect($fieldGroupSettings)
            ->except(self::NATIVE_FIELD_GROUPS)
            ->all();

        foreach ($customGroupSettings as $fieldGroup => $groupSetting) {
            $allowedValues = $this->allowedFieldValues($fieldGroup, []);

            if (empty($allowedValues)) {
                continue;
            }

            $placement = $groupSetting['placement_section'] ?? FormFieldOption::PLACEMENT_PERSONAL_INFORMATION;

            $isVisible = $this->isPlacementVisible(
                $placement,
                $isDa,
                $isFarmer,
                $isBfar,
                $isFisherfolk,
                $isDar,
            );

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
            'contact_number.regex'              => 'Contact number must be in 09XXXXXXXXX format.',
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
            ->pluck('value')
            ->filter()
            ->values()
            ->all();

        if (empty($dbValues)) {
            return $fallback;
        }

        return array_values(array_unique(array_merge($fallback, $dbValues)));
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

    private function isPlacementVisible(
        string $placement,
        bool $isDa,
        bool $isFarmer,
        bool $isBfar,
        bool $isFisherfolk,
        bool $isDar,
    ): bool {
        return match ($placement) {
            FormFieldOption::PLACEMENT_FARMER_INFORMATION => $isDa || $isFarmer,
            FormFieldOption::PLACEMENT_FISHERFOLK_INFORMATION => $isBfar || $isFisherfolk,
            FormFieldOption::PLACEMENT_DAR_INFORMATION => $isDar,
            default => true,
        };
    }
}

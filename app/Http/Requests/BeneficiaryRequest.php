<?php

namespace App\Http\Requests;

use App\Models\Agency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BeneficiaryRequest extends FormRequest
{
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

        $rules = [
            // Agency source (determines which fields are required)
            'agency_id'        => ['required', 'exists:agencies,id'],

            // Common fields per reference document
            'full_name'        => ['required', 'string', 'max:255'],
            'sex'              => ['required', Rule::in(['Male', 'Female'])],
            'date_of_birth'    => ['required', 'date', 'before:today'],
            'home_address'     => ['required', 'string', 'max:500'],
            'barangay_id'      => ['required', 'exists:barangays,id'],
            'contact_number'   => ['required', 'string', 'regex:/^09\d{9}$/'],
            'photo_path'       => ['nullable', 'string', 'max:255'],
            'civil_status'     => ['required', Rule::in(['Single', 'Married', 'Widowed', 'Separated'])],
            'status'           => ['required', Rule::in(['Active', 'Inactive'])],
            'registered_at'    => ['required', 'date', 'before_or_equal:today'],
            'classification'   => ['required', Rule::in(['Farmer', 'Fisherfolk', 'Both'])],

            // Association membership (common to all)
            'association_member' => ['required', 'boolean'],
            'association_name'   => ['nullable', 'required_if:association_member,true', 'required_if:association_member,1', 'string', 'max:255'],
        ];

        // DA/RSBSA fields (required when agency is DA or classification is Farmer/Both)
        $isDa = $agencyName === 'DA';
        $isFarmer = in_array($this->input('classification'), ['Farmer', 'Both']);

        if ($isDa || $isFarmer) {
            $rules['rsbsa_number'] = ['nullable', 'string', 'max:50', Rule::unique('beneficiaries', 'rsbsa_number')->ignore($beneficiaryId)];
            $rules['farm_ownership'] = ['required', Rule::in(['Registered Owner', 'Tenant', 'Lessee'])];
            $rules['farm_size_hectares'] = ['required', 'numeric', 'min:0.01'];
            $rules['primary_commodity'] = ['required', 'string', 'max:255'];
            $rules['farm_type'] = ['required', Rule::in(['Irrigated', 'Rainfed Upland', 'Rainfed Lowland'])];
            $rules['organization_membership'] = ['nullable', 'string', 'max:255'];
        } else {
            $rules['rsbsa_number'] = ['nullable', 'string', 'max:50'];
            $rules['farm_ownership'] = ['nullable', Rule::in(['Registered Owner', 'Tenant', 'Lessee'])];
            $rules['farm_size_hectares'] = ['nullable', 'numeric', 'min:0.01'];
            $rules['primary_commodity'] = ['nullable', 'string', 'max:255'];
            $rules['farm_type'] = ['nullable', Rule::in(['Irrigated', 'Rainfed Upland', 'Rainfed Lowland'])];
            $rules['organization_membership'] = ['nullable', 'string', 'max:255'];
        }

        // BFAR/FishR fields (required when agency is BFAR or classification is Fisherfolk/Both)
        $isBfar = $agencyName === 'BFAR';
        $isFisherfolk = in_array($this->input('classification'), ['Fisherfolk', 'Both']);

        if ($isBfar || $isFisherfolk) {
            $rules['fishr_number'] = ['nullable', 'string', 'max:50', Rule::unique('beneficiaries', 'fishr_number')->ignore($beneficiaryId)];
            $rules['fisherfolk_type'] = ['required', Rule::in(['Capture Fishing', 'Aquaculture', 'Post-Harvest'])];
            $rules['main_fishing_gear'] = ['nullable', 'string', 'max:255'];
            $rules['has_fishing_vessel'] = ['nullable', 'boolean'];
            $rules['fishing_vessel_type'] = ['nullable', 'string', 'max:255'];
            $rules['fishing_vessel_tonnage'] = ['nullable', 'numeric', 'min:0'];
            $rules['length_of_residency_months'] = ['required', 'integer', 'min:6'];
        } else {
            $rules['fishr_number'] = ['nullable', 'string', 'max:50'];
            $rules['fisherfolk_type'] = ['nullable', Rule::in(['Capture Fishing', 'Aquaculture', 'Post-Harvest'])];
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
            $rules['arb_classification'] = ['required', Rule::in([
                'Agricultural Lessee',
                'Regular Farmworker',
                'Seasonal Farmworker',
                'Other Farmworker',
                'Actual Tiller',
                'Collective/Cooperative',
                'Others',
            ])];
            $rules['landholding_description'] = ['required', 'string', 'max:1000'];
            $rules['land_area_awarded_hectares'] = ['required', 'numeric', 'min:0.01'];
            $rules['ownership_scheme'] = ['required', Rule::in(['Individual', 'Collective', 'Cooperative'])];
            $rules['barc_membership_status'] = ['nullable', 'string', 'max:100'];
        } else {
            $rules['cloa_ep_number'] = ['nullable', 'string', 'max:100'];
            $rules['arb_classification'] = ['nullable', 'string', 'max:100'];
            $rules['landholding_description'] = ['nullable', 'string', 'max:1000'];
            $rules['land_area_awarded_hectares'] = ['nullable', 'numeric', 'min:0.01'];
            $rules['ownership_scheme'] = ['nullable', Rule::in(['Individual', 'Collective', 'Cooperative'])];
            $rules['barc_membership_status'] = ['nullable', 'string', 'max:100'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
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
}

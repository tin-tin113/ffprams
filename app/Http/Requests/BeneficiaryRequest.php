<?php

namespace App\Http\Requests;

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

        return [
            // Always required
            'full_name'                => ['required', 'string', 'max:255'],
            'barangay_id'              => ['required', 'exists:barangays,id'],
            'classification'           => ['required', Rule::in(['Farmer', 'Fisherfolk', 'Both'])],
            'contact_number'           => ['required', 'string', 'regex:/^09\d{9}$/'],
            'household_size'           => ['required', 'integer', 'min:1', 'max:20'],
            'id_type'                  => ['required', 'string', 'max:100'],
            'government_id'            => [
                'required', 'string',
                Rule::unique('beneficiaries', 'government_id')->ignore($beneficiaryId),
            ],
            'status'                   => ['required', Rule::in(['Active', 'Inactive'])],
            'registered_at'            => ['required', 'date', 'before_or_equal:today'],
            'civil_status'             => ['required', Rule::in(['Single', 'Married', 'Widowed', 'Separated'])],
            'highest_education'        => ['required', 'string', 'max:100'],
            'number_of_dependents'     => ['required', 'integer', 'min:0'],
            'main_income_source'       => ['required', 'string', 'max:255'],
            'emergency_contact_name'   => ['required', 'string', 'max:255'],
            'emergency_contact_number' => ['required', 'string', 'regex:/^09\d{9}$/'],
            'association_member'       => ['required', 'boolean'],

            // Farmer-specific (required if Farmer or Both)
            'rsbsa_number'      => ['nullable', 'required_if:classification,Farmer', 'required_if:classification,Both', 'string', 'max:50', Rule::unique('beneficiaries', 'rsbsa_number')->ignore($beneficiaryId)],
            'farm_ownership'    => ['nullable', 'required_if:classification,Farmer', 'required_if:classification,Both', Rule::in(['Owner', 'Lessee', 'Share Tenant'])],
            'farm_size_hectares'=> ['nullable', 'required_if:classification,Farmer', 'required_if:classification,Both', 'numeric', 'min:0.01'],
            'primary_commodity' => ['nullable', 'required_if:classification,Farmer', 'required_if:classification,Both', 'string', 'max:255'],
            'farm_type'         => ['nullable', 'required_if:classification,Farmer', 'required_if:classification,Both', Rule::in(['Irrigated', 'Rainfed Lowland', 'Upland'])],

            // Fisherfolk-specific (required if Fisherfolk or Both)
            'fishr_number'     => ['nullable', 'required_if:classification,Fisherfolk', 'required_if:classification,Both', 'string', 'max:50', Rule::unique('beneficiaries', 'fishr_number')->ignore($beneficiaryId)],
            'fisherfolk_type'  => ['nullable', 'required_if:classification,Fisherfolk', 'required_if:classification,Both', Rule::in(['Capture Fishing', 'Fish Farming', 'Fish Vendor', 'Fish Worker'])],
            'main_fishing_gear'=> ['nullable', 'string', 'max:255'],
            'has_fishing_vessel'=> ['nullable', 'boolean'],

            // Association name (required if association_member is true)
            'association_name' => ['nullable', 'required_if:association_member,true', 'required_if:association_member,1', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'contact_number.regex'           => 'Contact number must be in 09XXXXXXXXX format.',
            'emergency_contact_number.regex'  => 'Emergency contact must be in 09XXXXXXXXX format.',
            'government_id.unique'            => 'A beneficiary with this Government ID already exists.',
            'registered_at.before_or_equal'   => 'Registration date cannot be a future date.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DistributionEventRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'requires_farmc_endorsement' => $this->boolean('requires_farmc_endorsement'),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'barangay_id'       => ['required', 'exists:barangays,id'],
            'resource_type_id'  => ['required', 'exists:resource_types,id'],
            'program_name_id'   => ['required', 'exists:program_names,id'],
            'distribution_date' => ['required', 'date'],
            'type'              => ['required', Rule::in(['physical', 'financial'])],
        ];

        if ($this->input('type') === 'financial') {
            $rules['total_fund_amount'] = ['required', 'numeric', 'min:1', 'max:9999999999.99'];
            $rules['legal_basis_type'] = ['required', Rule::in(['resolution', 'ordinance', 'memo', 'special_order', 'other'])];
            $rules['legal_basis_reference_no'] = ['required', 'string', 'max:150'];
            $rules['legal_basis_date'] = ['required', 'date', 'before_or_equal:today'];
            $rules['legal_basis_remarks'] = ['required_if:legal_basis_type,other', 'nullable', 'string', 'max:1000'];
            $rules['fund_source'] = ['required', Rule::in(['lgu_trust_fund', 'nga_transfer', 'local_program', 'other'])];
            $rules['trust_account_code'] = ['required_if:fund_source,lgu_trust_fund', 'nullable', 'string', 'max:100'];
            $rules['fund_release_reference'] = ['nullable', 'string', 'max:150'];
            $rules['liquidation_status'] = ['required', Rule::in(['not_required', 'pending', 'submitted', 'verified'])];
            $rules['liquidation_due_date'] = ['required_if:liquidation_status,pending,submitted,verified', 'nullable', 'date'];
            $rules['liquidation_submitted_at'] = ['required_if:liquidation_status,submitted,verified', 'nullable', 'date', 'before_or_equal:today'];
            $rules['liquidation_reference_no'] = ['required_if:liquidation_status,submitted,verified', 'nullable', 'string', 'max:150'];
            $rules['requires_farmc_endorsement'] = ['nullable', 'boolean'];
            $rules['farmc_endorsed_at'] = ['nullable', 'date'];
            $rules['farmc_reference_no'] = ['required_if:requires_farmc_endorsement,1', 'nullable', 'string', 'max:150'];
        } else {
            $rules['total_fund_amount'] = ['nullable'];
            $rules['legal_basis_type'] = ['nullable'];
            $rules['legal_basis_reference_no'] = ['nullable'];
            $rules['legal_basis_date'] = ['nullable'];
            $rules['legal_basis_remarks'] = ['nullable'];
            $rules['fund_source'] = ['nullable'];
            $rules['trust_account_code'] = ['nullable'];
            $rules['fund_release_reference'] = ['nullable'];
            $rules['liquidation_status'] = ['nullable'];
            $rules['liquidation_due_date'] = ['nullable'];
            $rules['liquidation_submitted_at'] = ['nullable'];
            $rules['liquidation_reference_no'] = ['nullable'];
            $rules['requires_farmc_endorsement'] = ['nullable', 'boolean'];
            $rules['farmc_endorsed_at'] = ['nullable'];
            $rules['farmc_reference_no'] = ['nullable'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'barangay_id.required'       => 'Please select a barangay.',
            'barangay_id.exists'         => 'Please select a valid barangay.',
            'resource_type_id.required'  => 'Please select a resource type.',
            'resource_type_id.exists'    => 'Please select a valid resource type.',
            'program_name_id.required'   => 'Please select a program name.',
            'program_name_id.exists'     => 'Please select a valid program name.',
            'distribution_date.required' => 'The distribution date is required.',
            'distribution_date.date'     => 'Please enter a valid date.',
            'type.required'              => 'Please select a distribution type.',
            'type.in'                    => 'Invalid distribution type.',
            'total_fund_amount.required' => 'Total fund budget is required for financial assistance.',
            'total_fund_amount.min'      => 'Total fund budget must be at least 1.',
            'total_fund_amount.max'      => 'Total fund budget must not exceed 9,999,999,999.99.',
            'legal_basis_type.required' => 'Legal basis type is required for financial assistance events.',
            'legal_basis_reference_no.required' => 'Legal basis reference number is required for financial assistance events.',
            'legal_basis_date.required' => 'Legal basis date is required for financial assistance events.',
            'legal_basis_date.before_or_equal' => 'Legal basis date cannot be in the future.',
            'legal_basis_remarks.required_if' => 'Please provide legal/compliance remarks when Legal Basis Type is Other.',
            'fund_source.required' => 'Fund source is required for financial assistance events.',
            'trust_account_code.required_if' => 'Trust account code is required when fund source is LGU Trust Fund.',
            'liquidation_status.required' => 'Liquidation status is required for financial assistance events.',
            'liquidation_due_date.required_if' => 'Liquidation due date is required when liquidation is Pending, Submitted, or Verified.',
            'liquidation_submitted_at.required_if' => 'Liquidation submitted date/time is required when liquidation is Submitted or Verified.',
            'liquidation_submitted_at.before_or_equal' => 'Liquidation submitted date/time cannot be in the future.',
            'liquidation_reference_no.required_if' => 'Liquidation reference number is required when liquidation is Submitted or Verified.',
            'farmc_reference_no.required_if' => 'FARMC reference number is required when FARMC endorsement is required.',
        ];
    }
}

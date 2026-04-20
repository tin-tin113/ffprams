<?php

namespace App\Http\Requests;

use App\Models\DistributionEvent;
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
            $rules['legal_basis_type'] = ['nullable', Rule::in(['resolution', 'ordinance', 'memo', 'special_order', 'other'])];
            $rules['legal_basis_reference_no'] = ['nullable', 'string', 'max:150'];
            $rules['legal_basis_date'] = ['nullable', 'date', 'before_or_equal:today'];
            $rules['legal_basis_remarks'] = ['nullable', 'string', 'max:1000'];
            $rules['fund_source'] = ['nullable', Rule::in(['lgu_trust_fund', 'nga_transfer', 'local_program', 'other'])];
            $rules['trust_account_code'] = ['nullable', 'string', 'max:100'];
            $rules['fund_release_reference'] = ['nullable', 'string', 'max:150'];
            $rules['liquidation_status'] = ['nullable', Rule::in(['not_required', 'pending', 'submitted', 'verified'])];
            $rules['liquidation_due_date'] = ['nullable', 'date'];
            $rules['liquidation_submitted_at'] = ['nullable', 'date', 'before_or_equal:today'];
            $rules['liquidation_reference_no'] = ['nullable', 'string', 'max:150'];
            $rules['requires_farmc_endorsement'] = ['nullable', 'boolean'];
            $rules['farmc_endorsed_at'] = ['nullable', 'date'];
            $rules['farmc_reference_no'] = ['nullable', 'string', 'max:150'];

            $rules['compliance_states'] = ['nullable', 'array'];
            $rules['compliance_reasons'] = ['nullable', 'array'];
            $rules['compliance_overall_status'] = ['nullable', Rule::in(DistributionEvent::complianceStatuses())];
            $rules['compliance_overall_reason'] = ['nullable', 'string', 'max:500'];

            foreach (DistributionEvent::COMPLIANCE_TRACKED_FIELDS as $field) {
                $rules["compliance_states.{$field}"] = ['nullable', Rule::in(DistributionEvent::complianceStatuses())];
                $rules["compliance_reasons.{$field}"] = ['nullable', 'string', 'max:500'];
            }
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
            $rules['compliance_states'] = ['nullable', 'array'];
            $rules['compliance_reasons'] = ['nullable', 'array'];
            $rules['compliance_overall_status'] = ['nullable', Rule::in(DistributionEvent::complianceStatuses())];
            $rules['compliance_overall_reason'] = ['nullable', 'string', 'max:500'];
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
            'legal_basis_date.before_or_equal' => 'Legal basis date cannot be in the future.',
            'liquidation_submitted_at.before_or_equal' => 'Liquidation submitted date/time cannot be in the future.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DistributionEventRequest extends FormRequest
{
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
        } else {
            $rules['total_fund_amount'] = ['nullable'];
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
        ];
    }
}

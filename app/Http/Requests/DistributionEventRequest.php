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
        return [
            'barangay_id'      => ['required', 'exists:barangays,id'],
            'resource_type_id' => ['required', 'exists:resource_types,id'],
            'distribution_date' => ['required', 'date'],
            'status'           => ['sometimes', Rule::in(['Pending', 'Ongoing', 'Completed'])],
        ];
    }

    public function messages(): array
    {
        return [
            'barangay_id.required'      => 'Please select a barangay.',
            'barangay_id.exists'        => 'Please select a valid barangay.',
            'resource_type_id.required' => 'Please select a resource type.',
            'resource_type_id.exists'   => 'Please select a valid resource type.',
            'distribution_date.required' => 'The distribution date is required.',
            'distribution_date.date'    => 'Please enter a valid date.',
        ];
    }
}

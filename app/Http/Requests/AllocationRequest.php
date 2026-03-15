<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AllocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'distribution_event_id' => ['required', 'exists:distribution_events,id'],
            'beneficiary_id'        => ['required', 'exists:beneficiaries,id'],
            'quantity'              => ['required', 'numeric', 'min:0.01', 'max:9999.99'],
            'remarks'               => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'distribution_event_id.required' => 'The distribution event is required.',
            'distribution_event_id.exists'   => 'The selected distribution event is invalid.',
            'beneficiary_id.required'        => 'Please select a beneficiary.',
            'beneficiary_id.exists'          => 'Please select a valid beneficiary.',
            'quantity.required'              => 'The quantity is required.',
            'quantity.min'                   => 'Quantity must be greater than zero.',
            'quantity.max'                   => 'Quantity must not exceed 9999.99.',
        ];
    }
}

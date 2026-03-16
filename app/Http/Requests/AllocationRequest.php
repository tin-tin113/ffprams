<?php

namespace App\Http\Requests;

use App\Models\DistributionEvent;
use Illuminate\Foundation\Http\FormRequest;

class AllocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $event = DistributionEvent::find($this->input('distribution_event_id'));

        $rules = [
            'distribution_event_id' => ['required', 'exists:distribution_events,id'],
            'beneficiary_id'        => ['required', 'exists:beneficiaries,id'],
            'remarks'               => ['nullable', 'string', 'max:500'],
        ];

        if ($event && $event->isFinancial()) {
            $rules['amount']   = ['required', 'numeric', 'min:1', 'max:9999999999.99'];
            $rules['quantity'] = ['nullable'];
        } else {
            $rules['quantity'] = ['required', 'numeric', 'min:0.01', 'max:9999.99'];
            $rules['amount']   = ['nullable'];
        }

        return $rules;
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
            'amount.required'               => 'The amount is required for financial assistance.',
            'amount.min'                     => 'Amount must be at least 1.',
            'amount.max'                     => 'Amount must not exceed 9,999,999,999.99.',
        ];
    }
}

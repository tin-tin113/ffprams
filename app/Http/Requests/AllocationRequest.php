<?php

namespace App\Http\Requests;

use App\Models\DistributionEvent;
use App\Models\ResourceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AllocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $releaseMethod = $this->input('release_method', 'event');
        $event = DistributionEvent::find($this->input('distribution_event_id'));
        $resourceType = ResourceType::find($this->input('resource_type_id'));

        $rules = [
            'release_method'        => ['required', Rule::in(['event', 'direct'])],
            'distribution_event_id' => ['nullable', 'exists:distribution_events,id'],
            'beneficiary_id'        => ['required', 'exists:beneficiaries,id'],
            'assistance_purpose_id' => ['nullable', 'exists:assistance_purposes,id'],
            'program_name_id'       => ['nullable', 'exists:program_names,id'],
            'resource_type_id'      => ['nullable', 'exists:resource_types,id'],
            'remarks'               => ['nullable', 'string', 'max:500'],
        ];

        if ($releaseMethod === 'event') {
            $rules['distribution_event_id'] = ['required', 'exists:distribution_events,id'];
            $rules['program_name_id'] = ['nullable'];
            $rules['resource_type_id'] = ['nullable'];
        } else {
            $rules['distribution_event_id'] = ['nullable'];
            $rules['program_name_id'] = ['required', 'exists:program_names,id'];
            $rules['resource_type_id'] = ['required', 'exists:resource_types,id'];
        }

        $isFinancial = false;
        if ($releaseMethod === 'event' && $event) {
            $isFinancial = $event->isFinancial();
        }
        if ($releaseMethod === 'direct' && $resourceType && $resourceType->unit === 'PHP') {
            $isFinancial = true;
        }

        if ($isFinancial) {
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
            'release_method.required'      => 'Please select a release method.',
            'release_method.in'            => 'Invalid release method selected.',
            'distribution_event_id.required' => 'The distribution event is required.',
            'distribution_event_id.exists'   => 'The selected distribution event is invalid.',
            'beneficiary_id.required'        => 'Please select a beneficiary.',
            'beneficiary_id.exists'          => 'Please select a valid beneficiary.',
            'program_name_id.required'       => 'Please select a program for direct assistance.',
            'resource_type_id.required'      => 'Please select a resource type for direct assistance.',
            'quantity.required'              => 'The quantity is required.',
            'quantity.min'                   => 'Quantity must be greater than zero.',
            'quantity.max'                   => 'Quantity must not exceed 9999.99.',
            'amount.required'                => 'The amount is required for financial assistance.',
            'amount.min'                     => 'Amount must be at least 1.',
            'amount.max'                     => 'Amount must not exceed 9,999,999,999.99.',
        ];
    }
}

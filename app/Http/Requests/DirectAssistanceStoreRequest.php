<?php

namespace App\Http\Requests;

use App\Models\ResourceType;
use App\Services\ProgramEligibilityService;
use Illuminate\Foundation\Http\FormRequest;

class DirectAssistanceStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'beneficiary_id' => ['required', 'exists:beneficiaries,id'],
            'program_name_id' => ['required', 'exists:program_names,id'],
            'resource_type_id' => ['required', 'exists:resource_types,id'],
            'assistance_purpose_id' => ['nullable', 'exists:assistance_purposes,id'],
            'quantity' => ['nullable', 'numeric', 'min:0.01', 'max:9999.99'],
            'amount' => ['nullable', 'numeric', 'min:1', 'max:9999999999.99'],
            'remarks' => ['nullable', 'string', 'max:500'],
            'distribution_event_id' => ['nullable', 'exists:distribution_events,id'],
        ];

        if ($this->isFinancialResourceType()) {
            $rules['amount'] = ['required', 'numeric', 'min:1', 'max:9999999999.99'];
            $rules['quantity'] = ['nullable'];
        } else {
            $rules['quantity'] = ['required', 'numeric', 'min:0.01', 'max:9999.99'];
            $rules['amount'] = ['nullable'];
        }

        // Add custom validation for beneficiary eligibility with program
        $rules['beneficiary_id'][] = function ($attribute, $value, $fail) {
            $beneficiary = \App\Models\Beneficiary::find($value);
            $program = \App\Models\ProgramName::find($this->input('program_name_id'));

            if ($program && $beneficiary && !ProgramEligibilityService::isEligible($beneficiary, $program)) {
                $reason = ProgramEligibilityService::getIneligibilityReason($beneficiary, $program);
                $fail('Beneficiary eligibility: ' . $reason);
            }
        };

        return $rules;
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Amount is required for financial assistance.',
            'quantity.required' => 'Quantity is required for non-financial resources.',
        ];
    }

    public function normalizedPayload(): array
    {
        $validated = $this->validated();

        if ($this->isFinancialResourceType()) {
            $validated['quantity'] = null;
        } else {
            $validated['amount'] = null;
        }

        return $validated;
    }

    private function isFinancialResourceType(): bool
    {
        $resourceTypeId = $this->input('resource_type_id');
        if (! $resourceTypeId) {
            return false;
        }

        return ResourceType::isFinancialUnit(ResourceType::find($resourceTypeId)?->unit);
    }
}

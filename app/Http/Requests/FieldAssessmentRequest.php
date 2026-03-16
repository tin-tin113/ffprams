<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FieldAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'beneficiary_id'                    => ['required', 'exists:beneficiaries,id'],
            'visit_date'                        => ['required', 'date', 'before_or_equal:today'],
            'visit_time'                        => ['nullable', 'date_format:H:i'],
            'findings'                          => ['required', 'string', 'min:10', 'max:2000'],
            'eligibility_status'                => ['required', 'in:pending,eligible,not_eligible'],
            'eligibility_notes'                 => ['nullable', 'string', 'max:1000'],
            'recommended_assistance_purpose_id' => ['required_if:eligibility_status,eligible', 'nullable', 'exists:assistance_purposes,id'],
            'recommended_amount'                => ['required_if:eligibility_status,eligible', 'nullable', 'numeric', 'min:1', 'max:9999999999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'findings.min'                                      => 'Please provide detailed findings (minimum 10 characters).',
            'recommended_assistance_purpose_id.required_if'     => 'Please select the recommended assistance purpose for eligible beneficiaries.',
            'recommended_amount.required_if'                    => 'Please enter the recommended assistance amount for eligible beneficiaries.',
        ];
    }
}

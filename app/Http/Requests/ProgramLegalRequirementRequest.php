<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProgramLegalRequirementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'document_type' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'A legal requirement document is required.',
            'file.mimes' => 'Only PDF, JPG, and PNG files are accepted.',
            'file.max' => 'Document must not exceed 5MB.',
            'document_type.max' => 'Document type must not exceed 100 characters.',
            'remarks.max' => 'Remarks must not exceed 500 characters.',
        ];
    }
}

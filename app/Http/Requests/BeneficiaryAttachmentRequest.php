<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BeneficiaryAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_type' => ['nullable', 'string', 'max:100'],
            'attachment' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'attachment.required' => 'Please select a file to upload.',
            'attachment.mimes' => 'Allowed file types are PDF, JPG, JPEG, and PNG.',
            'attachment.max' => 'Attachment must not exceed 5 MB.',
        ];
    }
}

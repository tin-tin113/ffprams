<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_type' => ['nullable', 'string', 'max:100'],
            'attachment' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,csv,txt', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'attachment.required' => 'Please select a file to upload.',
            'attachment.mimes' => 'Allowed file types are PDF, JPG, JPEG, PNG, DOC, DOCX, XLS, XLSX, CSV, and TXT.',
            'attachment.max' => 'Attachment must not exceed 10 MB.',
        ];
    }
}

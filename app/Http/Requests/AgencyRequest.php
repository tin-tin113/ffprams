<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $agencyId = $this->route('agency')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                $agencyId
                    ? "unique:agencies,name,{$agencyId}"
                    : 'unique:agencies,name',
            ],
            'full_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'classifications' => 'required|array|min:1',
            'classifications.*' => 'integer|exists:classifications,id',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Agency name is required.',
            'name.unique' => 'An agency with this name already exists.',
            'full_name.required' => 'Full name is required.',
            'classifications.required' => 'Please select at least one classification.',
            'classifications.min' => 'Please select at least one classification.',
        ];
    }
}

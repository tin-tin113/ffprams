<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResourceTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $resourceTypeId = $this->route('resource_type')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('resource_types', 'name')->ignore($resourceTypeId),
            ],
            'unit' => ['required', 'string', 'max:50'],
            'source_agency' => ['required', Rule::in(['DA', 'BFAR', 'DAR', 'LGU'])],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The resource type name is required.',
            'name.unique' => 'A resource type with this name already exists.',
            'unit.required' => 'The unit of measurement is required.',
            'source_agency.required' => 'The source agency is required.',
            'source_agency.in' => 'The source agency must be one of: DA, BFAR, DAR, LGU.',
            'description.max' => 'The description must not exceed 500 characters.',
        ];
    }
}

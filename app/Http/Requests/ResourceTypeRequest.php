<?php

namespace App\Http\Requests;

use App\Models\ResourceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResourceTypeRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'unit' => ResourceType::normalizeUnit($this->input('unit')),
        ]);
    }

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
            'unit' => ['required', 'string', 'max:50', Rule::in(ResourceType::unitValues())],
            'agency_id' => ['required', 'exists:agencies,id'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The resource type name is required.',
            'name.unique' => 'A resource type with this name already exists.',
            'unit.required' => 'The unit of measurement is required.',
            'agency_id.required' => 'The source agency is required.',
            'agency_id.exists' => 'The selected agency is invalid.',
            'description.max' => 'The description must not exceed 500 characters.',
        ];
    }
}

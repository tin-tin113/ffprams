<?php

namespace App\Http\Controllers\Api;

use App\Models\Agency;
use App\Models\Classification;
use Illuminate\Http\Request;

class AgencyFormFieldController
{
    /**
     * Fetch form fields for given agencies
     * GET /api/agencies/form-fields?agencies=1,2,3
     */
    public function getFormFields(Request $request)
    {
        $agencyIds = collect(explode(',', $request->query('agencies', '')))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray();

        if (empty($agencyIds)) {
            return response()->json([]);
        }

        $agencies = Agency::whereIn('id', $agencyIds)
            ->where('is_active', true)
            ->with([
                'formFields' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
                'formFields.options' => fn ($q) => $q->orderBy('sort_order')
            ])
            ->get()
            ->map(fn ($agency) => [
                'id' => $agency->id,
                'name' => $agency->name,
                'full_name' => $agency->full_name,
                'form_fields' => $agency->formFields->map(fn ($field) => [
                    'id' => $field->id,
                    'field_name' => $field->field_name,
                    'display_label' => $field->display_label,
                    'field_type' => $field->field_type,
                    'is_required' => $field->is_required,
                    'help_text' => $field->help_text,
                    'form_section' => $field->form_section,
                    'validation_rules' => $field->validation_rules,
                    'options' => $field->options->map(fn ($opt) => [
                        'value' => $opt->value,
                        'label' => $opt->label,
                    ])->values()->toArray(),
                ])->values()->toArray(),
            ]);

        return response()->json($agencies);
    }
}

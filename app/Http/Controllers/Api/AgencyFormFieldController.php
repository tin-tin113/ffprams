<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use Illuminate\Http\Request;

class AgencyFormFieldController extends Controller
{
    /**
     * Fetch agencies by classification
     * GET /api/agencies/by-classification?classification=Farmer
     */
    public function getByClassification(Request $request)
    {
        $classification = trim((string) $request->query('classification', ''));
        if ($classification === '') {
            return response()->json([]);
        }

        // Handle dual classification
        $searchTerms = [$classification];
        $lowerClass = strtolower($classification);
        if ($lowerClass === 'farmer & fisherfolk' || $lowerClass === 'farmer and fisherfolk' || $lowerClass === 'both') {
            $searchTerms = ['Farmer', 'Fisherfolk', 'Farmer & Fisherfolk'];
        }

        $agencies = Agency::whereHas('classifications', function ($q) use ($searchTerms) {
            $q->whereIn('name', $searchTerms);
        })
        ->where('is_active', true)
        ->select('id', 'name', 'full_name')
        ->orderByRaw("CASE UPPER(name) WHEN 'DA' THEN 1 WHEN 'BFAR' THEN 2 WHEN 'DAR' THEN 3 ELSE 100 END")
        ->orderBy('name')
        ->get()
        ->unique('id')
        ->values();

        return response()->json($agencies);
    }

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
                'formFields' => fn ($q) => $q
                    ->where('is_active', true)
                    ->orderBy('sort_order'),
                'formFields.options' => fn ($q) => $q
                    ->where('is_active', true)
                    ->orderBy('sort_order')
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
            ])
            ->toArray();

        return response()->json($agencies);
    }

    /**
     * Fetch all classifications
     * GET /api/classifications
     */
    public function getClassifications()
    {
        $classifications = \App\Models\Classification::select('id', 'name', 'description')
            ->orderBy('name')
            ->get();

        return response()->json($classifications);
    }
}

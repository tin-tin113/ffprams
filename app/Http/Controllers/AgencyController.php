<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgencyRequest;
use App\Models\Agency;
use App\Models\AgencyFormField;
use App\Models\Classification;
use App\Models\AgencyFormFieldOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgencyController extends Controller
{
    /**
     * Display a listing of all agencies
     */
    public function index(): View
    {
        $agencies = Agency::with('classifications')
            ->orderBy('name')
            ->paginate(25);

        return view('admin.agencies.index', compact('agencies'));
    }

    /**
     * Show the form for creating a new agency
     */
    public function create(): View
    {
        $classifications = Classification::orderBy('name')->get();

        return view('admin.agencies.form', compact('classifications'));
    }

    /**
     * Store a newly created agency in storage
     */
    public function store(AgencyRequest $request): RedirectResponse
    {
        $agency = Agency::create([
            'name' => $request->input('name'),
            'full_name' => $request->input('full_name'),
            'description' => $request->input('description'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Sync classifications
        $classificationIds = $request->input('classifications', []);
        $agency->classifications()->sync($classificationIds);

        return redirect()
            ->route('admin.agencies.show', $agency)
            ->with('success', "Agency '{$agency->name}' created successfully.");
    }

    /**
     * Show the agency with its form fields
     */
    public function show(Agency $agency): View
    {
        $agency->load(['classifications', 'formFields.options']);

        return view('admin.agencies.show', compact('agency'));
    }

    /**
     * Show the form for editing an agency
     */
    public function edit(Agency $agency): View
    {
        $agency->load('classifications');
        $classifications = Classification::orderBy('name')->get();
        $selectedClassificationIds = $agency->classifications->pluck('id')->toArray();

        return view('admin.agencies.form', compact('agency', 'classifications', 'selectedClassificationIds'));
    }

    /**
     * Update the specified agency in storage
     */
    public function update(AgencyRequest $request, Agency $agency): RedirectResponse
    {
        $agency->update([
            'name' => $request->input('name'),
            'full_name' => $request->input('full_name'),
            'description' => $request->input('description'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Sync classifications
        $classificationIds = $request->input('classifications', []);
        $agency->classifications()->sync($classificationIds);

        return redirect()
            ->route('admin.agencies.show', $agency)
            ->with('success', "Agency '{$agency->name}' updated successfully.");
    }

    /**
     * Delete the specified agency
     */
    public function destroy(Agency $agency): RedirectResponse
    {
        $agencyName = $agency->name;
        $agency->delete();

        return redirect()
            ->route('admin.agencies.index')
            ->with('success', "Agency '{$agencyName}' deleted successfully.");
    }

    /**
     * Add a new form field to an agency
     */
    public function addFormField(Request $request, Agency $agency): RedirectResponse
    {
        $validated = $request->validate([
            'field_name' => 'required|string|max:255|unique:agency_form_fields,field_name,' . $agency->id . ',agency_id',
            'display_label' => 'required|string|max:255',
            'field_type' => 'required|in:text,number,decimal,date,datetime,dropdown,checkbox',
            'is_required' => 'boolean',
            'help_text' => 'nullable|string|max:500',
            'form_section' => 'required|string|max:255',
            'sort_order' => 'integer|min:0',
            'validation_rules' => 'nullable|json',
        ]);

        $field = $agency->formFields()->create($validated);

        return redirect()
            ->route('admin.agencies.show', $agency)
            ->with('success', "Form field '{$field->display_label}' added successfully.");
    }

    /**
     * Update a form field
     */
    public function updateFormField(Request $request, Agency $agency, AgencyFormField $field): RedirectResponse
    {
        if ($field->agency_id !== $agency->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'field_name' => 'required|string|max:255|unique:agency_form_fields,field_name,' . $field->id,
            'display_label' => 'required|string|max:255',
            'field_type' => 'required|in:text,number,decimal,date,datetime,dropdown,checkbox',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'help_text' => 'nullable|string|max:500',
            'form_section' => 'required|string|max:255',
            'sort_order' => 'integer|min:0',
            'validation_rules' => 'nullable|json',
        ]);

        $field->update($validated);

        return redirect()
            ->route('admin.agencies.show', $agency)
            ->with('success', "Form field updated successfully.");
    }

    /**
     * Delete a form field
     */
    public function deleteFormField(Agency $agency, AgencyFormField $field): RedirectResponse
    {
        if ($field->agency_id !== $agency->id) {
            abort(403, 'Unauthorized');
        }

        $fieldLabel = $field->display_label;
        $field->delete();

        return redirect()
            ->route('admin.agencies.show', $agency)
            ->with('success', "Form field '{$fieldLabel}' deleted successfully.");
    }

    /**
     * Add option to a form field (for dropdown/checkbox types)
     */
    public function addFieldOption(Request $request, Agency $agency, AgencyFormField $field): RedirectResponse
    {
        if ($field->agency_id !== $agency->id) {
            abort(403, 'Unauthorized');
        }

        if (! in_array($field->field_type, ['dropdown', 'checkbox'], true)) {
            return back()->with('error', 'Options can only be added to dropdown or checkbox fields.');
        }

        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'sort_order' => 'integer|min:0',
        ]);

        $field->options()->create($validated);

        return redirect()
            ->route('admin.agencies.show', $agency)
            ->with('success', 'Field option added successfully.');
    }

    /**
     * Delete an option from a form field
     */
    public function deleteFieldOption(Agency $agency, AgencyFormField $field, AgencyFormFieldOption $option): RedirectResponse
    {
        if ($field->agency_id !== $agency->id || $option->agency_form_field_id !== $field->id) {
            abort(403, 'Unauthorized');
        }

        $option->delete();

        return redirect()
            ->route('admin.agencies.show', $agency)
            ->with('success', 'Field option deleted successfully.');
    }
}

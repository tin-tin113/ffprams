<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\AssistancePurpose;
use App\Models\FormFieldOption;
use App\Models\ResourceType;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SystemSettingsController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
    ) {}

    // ── Index ────────────────────────────────────

    public function index(): View
    {
        $agencies   = Agency::orderBy('name')->get();
        $purposes   = AssistancePurpose::orderBy('category')->orderBy('name')->get()->groupBy('category');
        $resourceTypes = ResourceType::with('agency')->orderBy('name')->get();
        $formFields = FormFieldOption::orderBy('field_group')->orderBy('sort_order')->orderBy('label')->get()->groupBy('field_group');

        return view('admin.settings.index', compact('agencies', 'purposes', 'resourceTypes', 'formFields'));
    }

    public function listAgencies(): JsonResponse
    {
        return response()->json(Agency::orderBy('name')->get());
    }

    public function listPurposes(): JsonResponse
    {
        return response()->json(AssistancePurpose::orderBy('category')->orderBy('name')->get());
    }

    public function listResourceTypes(): JsonResponse
    {
        return response()->json(ResourceType::with('agency')->orderBy('name')->get());
    }

    // ── Agencies ─────────────────────────────────

    public function storeAgency(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:agencies,name'],
            'full_name'   => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active'   => ['boolean'],
        ]);

        $agency = DB::transaction(function () use ($validated) {
            $agency = Agency::create([
                'name'        => $validated['name'],
                'full_name'   => $validated['full_name'],
                'description' => $validated['description'] ?? null,
                'is_active'   => $validated['is_active'] ?? true,
            ]);

            $this->audit->log(
                auth()->id(), 'created', 'agencies', $agency->id,
                [], $agency->toArray(),
            );

            return $agency;
        });

        return response()->json(['success' => true, 'agency' => $agency]);
    }

    public function updateAgency(Request $request, Agency $agency): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100', Rule::unique('agencies', 'name')->ignore($agency->id)],
            'full_name'   => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active'   => ['boolean'],
        ]);

        DB::transaction(function () use ($validated, $agency) {
            $oldValues = $agency->toArray();

            $agency->update([
                'name'        => $validated['name'],
                'full_name'   => $validated['full_name'],
                'description' => $validated['description'] ?? null,
                'is_active'   => $validated['is_active'] ?? true,
            ]);

            $this->audit->log(
                auth()->id(), 'updated', 'agencies', $agency->id,
                $oldValues, $agency->fresh()->toArray(),
            );
        });

        return response()->json(['success' => true, 'agency' => $agency->fresh()]);
    }

    public function destroyAgency(Agency $agency): JsonResponse
    {
        $hasLinked = $agency->resourceTypes()->exists();

        DB::transaction(function () use ($agency) {
            $oldValues = $agency->toArray();

            $agency->update(['is_active' => false]);

            $this->audit->log(
                auth()->id(), 'updated', 'agencies', $agency->id,
                $oldValues, $agency->fresh()->toArray(),
            );
        });

        $message = $hasLinked
            ? 'Agency deactivated. Existing resource types linked to this agency are not affected.'
            : 'Agency deactivated successfully.';

        return response()->json(['success' => true, 'warning' => $hasLinked, 'message' => $message]);
    }

    // ── Assistance Purposes ──────────────────────

    public function storePurpose(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255', 'unique:assistance_purposes,name'],
            'category'  => ['required', Rule::in(['agricultural', 'fishery', 'livelihood', 'medical', 'emergency', 'other'])],
            'is_active' => ['boolean'],
        ]);

        $purpose = DB::transaction(function () use ($validated) {
            $purpose = AssistancePurpose::create([
                'name'      => $validated['name'],
                'category'  => $validated['category'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $this->audit->log(
                auth()->id(), 'created', 'assistance_purposes', $purpose->id,
                [], $purpose->toArray(),
            );

            return $purpose;
        });

        return response()->json(['success' => true, 'purpose' => $purpose]);
    }

    public function updatePurpose(Request $request, AssistancePurpose $purpose): JsonResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255', Rule::unique('assistance_purposes', 'name')->ignore($purpose->id)],
            'category'  => ['required', Rule::in(['agricultural', 'fishery', 'livelihood', 'medical', 'emergency', 'other'])],
            'is_active' => ['boolean'],
        ]);

        DB::transaction(function () use ($validated, $purpose) {
            $oldValues = $purpose->toArray();

            $purpose->update([
                'name'      => $validated['name'],
                'category'  => $validated['category'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $this->audit->log(
                auth()->id(), 'updated', 'assistance_purposes', $purpose->id,
                $oldValues, $purpose->fresh()->toArray(),
            );
        });

        return response()->json(['success' => true, 'purpose' => $purpose->fresh()]);
    }

    public function destroyPurpose(AssistancePurpose $purpose): JsonResponse
    {
        $hasLinked = $purpose->allocations()->exists() || $purpose->fieldAssessments()->exists();

        DB::transaction(function () use ($purpose) {
            $oldValues = $purpose->toArray();

            $purpose->update(['is_active' => false]);

            $this->audit->log(
                auth()->id(), 'updated', 'assistance_purposes', $purpose->id,
                $oldValues, $purpose->fresh()->toArray(),
            );
        });

        $message = $hasLinked
            ? 'Purpose deactivated. Existing allocations and assessments linked to this purpose are not affected.'
            : 'Purpose deactivated successfully.';

        return response()->json(['success' => true, 'warning' => $hasLinked, 'message' => $message]);
    }

    // ── Resource Types ───────────────────────────

    public function storeResourceType(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255', 'unique:resource_types,name'],
            'unit'         => ['required', 'string', 'max:50'],
            'agency_id'    => ['required', 'exists:agencies,id'],
            'description'  => ['nullable', 'string', 'max:500'],
        ]);

        $resourceType = DB::transaction(function () use ($validated) {
            $resourceType = ResourceType::create($validated);

            $this->audit->log(
                auth()->id(), 'created', 'resource_types', $resourceType->id,
                [], $resourceType->toArray(),
            );

            return $resourceType;
        });

        return response()->json(['success' => true, 'resourceType' => $resourceType->load('agency')]);
    }

    public function updateResourceType(Request $request, ResourceType $resourceType): JsonResponse
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255', Rule::unique('resource_types', 'name')->ignore($resourceType->id)],
            'unit'         => ['required', 'string', 'max:50'],
            'agency_id'    => ['required', 'exists:agencies,id'],
            'description'  => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($validated, $resourceType) {
            $oldValues = $resourceType->toArray();

            $resourceType->update($validated);

            $this->audit->log(
                auth()->id(), 'updated', 'resource_types', $resourceType->id,
                $oldValues, $resourceType->fresh()->toArray(),
            );
        });

        return response()->json(['success' => true, 'resourceType' => $resourceType->fresh()->load('agency')]);
    }

    public function destroyResourceType(ResourceType $resourceType): JsonResponse
    {
        if ($resourceType->distributionEvents()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This resource type cannot be deleted because it is linked to existing distribution events.',
            ], 422);
        }

        DB::transaction(function () use ($resourceType) {
            $this->audit->log(
                auth()->id(), 'deleted', 'resource_types', $resourceType->id,
                $resourceType->toArray(), [],
            );

            $resourceType->delete();
        });

        return response()->json(['success' => true, 'message' => 'Resource type deleted successfully.']);
    }

    // ── Form Fields ─────────────────────────────

    public function listFormFields(): JsonResponse
    {
        return response()->json(
            FormFieldOption::orderBy('field_group')
                ->orderBy('sort_order')
                ->orderBy('label')
                ->get()
        );
    }

    public function storeFormField(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'field_group' => ['required', 'string', 'max:100'],
            'label'      => ['required', 'string', 'max:255'],
            'value'      => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active'  => ['boolean'],
        ]);

        // Ensure unique field_group + value pair
        $exists = FormFieldOption::where('field_group', $validated['field_group'])
            ->where('value', $validated['value'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'errors'  => ['value' => ['This value already exists for this field.']],
            ], 422);
        }

        $option = DB::transaction(function () use ($validated) {
            // Auto-assign sort_order if not provided
            if (empty($validated['sort_order'])) {
                $maxOrder = FormFieldOption::where('field_group', $validated['field_group'])->max('sort_order');
                $validated['sort_order'] = ($maxOrder ?? 0) + 10;
            }

            $option = FormFieldOption::create([
                'field_group' => $validated['field_group'],
                'label'      => $validated['label'],
                'value'      => $validated['value'],
                'sort_order' => $validated['sort_order'],
                'is_active'  => $validated['is_active'] ?? true,
            ]);

            $this->audit->log(
                auth()->id(), 'created', 'form_field_options', $option->id,
                [], $option->toArray(),
            );

            return $option;
        });

        return response()->json(['success' => true, 'option' => $option]);
    }

    public function updateFormField(Request $request, FormFieldOption $formFieldOption): JsonResponse
    {
        $validated = $request->validate([
            'label'      => ['required', 'string', 'max:255'],
            'value'      => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active'  => ['boolean'],
        ]);

        // Ensure unique field_group + value pair (exclude self)
        $exists = FormFieldOption::where('field_group', $formFieldOption->field_group)
            ->where('value', $validated['value'])
            ->where('id', '!=', $formFieldOption->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'errors'  => ['value' => ['This value already exists for this field.']],
            ], 422);
        }

        DB::transaction(function () use ($validated, $formFieldOption) {
            $oldValues = $formFieldOption->toArray();

            $formFieldOption->update([
                'label'      => $validated['label'],
                'value'      => $validated['value'],
                'sort_order' => $validated['sort_order'] ?? $formFieldOption->sort_order,
                'is_active'  => $validated['is_active'] ?? true,
            ]);

            $this->audit->log(
                auth()->id(), 'updated', 'form_field_options', $formFieldOption->id,
                $oldValues, $formFieldOption->fresh()->toArray(),
            );
        });

        return response()->json(['success' => true, 'option' => $formFieldOption->fresh()]);
    }

    public function destroyFormField(FormFieldOption $formFieldOption): JsonResponse
    {
        DB::transaction(function () use ($formFieldOption) {
            $this->audit->log(
                auth()->id(), 'deleted', 'form_field_options', $formFieldOption->id,
                $formFieldOption->toArray(), [],
            );

            $formFieldOption->delete();
        });

        return response()->json(['success' => true, 'message' => 'Option deleted successfully.']);
    }

    public function reorderFormFields(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items'         => ['required', 'array'],
            'items.*.id'    => ['required', 'integer', 'exists:form_field_options,id'],
            'items.*.order' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['items'] as $item) {
                FormFieldOption::where('id', $item['id'])->update(['sort_order' => $item['order']]);
            }
        });

        return response()->json(['success' => true]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\AssistancePurpose;
use App\Models\FormFieldOption;
use App\Models\ProgramName;
use App\Models\ResourceType;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SystemSettingsController extends Controller
{
    private const ORDER_MODES = ['auto_end', 'start', 'end', 'before', 'after', 'custom'];

    public function __construct(
        private AuditLogService $audit,
    ) {}

    // ── Index ────────────────────────────────────

    /**
     * Show agencies page (main settings landing page)
     */
    public function index(): View
    {
        $agencies = Agency::orderBy('name')->get();

        return view('admin.settings.agencies.index', compact('agencies'));
    }

    /**
     * Separate Page Views for Multi-Page Interface
     */
    public function indexAgencies(): View
    {
        $agencies = Agency::orderBy('name')->get();

        return view('admin.settings.agencies.index', compact('agencies'));
    }

    public function indexPurposes(): View
    {
        $purposes = AssistancePurpose::orderBy('category')->orderBy('name')->get();

        return view('admin.settings.purposes.index', compact('purposes'));
    }

    public function indexResourceTypes(): View
    {
        $agencies = Agency::where('is_active', true)->orderBy('name')->get();
        $resourceTypes = ResourceType::with('agency')->orderBy('name')->get();

        return view('admin.settings.resource-types.index', compact('agencies', 'resourceTypes'));
    }

    public function indexProgramNames(): View
    {
        $agencies = Agency::where('is_active', true)->orderBy('name')->get();
        $programNames = ProgramName::with('agency')->orderBy('name')->get();

        return view('admin.settings.program-names.index', compact('agencies', 'programNames'));
    }

    public function indexFormFields(): View
    {
        $this->validateFormFieldOptions();
        $formFields = FormFieldOption::orderBy('field_group')->orderBy('sort_order')->orderBy('label')->get()->groupBy('field_group');

        $fieldGroupMeta = $formFields->map(function ($options) {
            $first = $options->first();

            return [
                'placement_section' => $first?->placement_section ?? FormFieldOption::PLACEMENT_PERSONAL_INFORMATION,
                'is_required' => (bool) ($first?->is_required ?? false),
            ];
        });

        $placementLabels = FormFieldOption::placementLabels();

        return view('admin.settings.form-fields.index', compact('formFields', 'fieldGroupMeta', 'placementLabels'));
    }

    // ── API List Methods ────────────────────────────────────

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

    public function listProgramNames(): JsonResponse
    {
        return response()->json(ProgramName::with('agency')->orderBy('name')->get());
    }

    /**
     * Get active agencies for modal dropdowns (Phase D - Dynamic Modal Dropdowns)
     * This endpoint is called when modals open to ensure fresh agency data in dropdowns
     */
    public function getActiveAgencies(): JsonResponse
    {
        $agencies = Agency::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'full_name', 'is_active']);

        return response()->json($agencies);
    }

    // ── Agencies ─────────────────────────────────

    public function storeAgency(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:agencies,name'],
            'full_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        $agency = DB::transaction(function () use ($validated) {
            $agency = Agency::create([
                'name' => $validated['name'],
                'full_name' => $validated['full_name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
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
            'name' => ['required', 'string', 'max:100', Rule::unique('agencies', 'name')->ignore($agency->id)],
            'full_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        DB::transaction(function () use ($validated, $agency) {
            $oldValues = $agency->toArray();

            $agency->update([
                'name' => $validated['name'],
                'full_name' => $validated['full_name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
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
            'name' => ['required', 'string', 'max:255', 'unique:assistance_purposes,name'],
            'category' => ['required', Rule::in(['agricultural', 'fishery', 'livelihood', 'medical', 'emergency', 'other'])],
            'is_active' => ['boolean'],
        ]);

        $purpose = DB::transaction(function () use ($validated) {
            $purpose = AssistancePurpose::create([
                'name' => $validated['name'],
                'category' => $validated['category'],
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
            'name' => ['required', 'string', 'max:255', Rule::unique('assistance_purposes', 'name')->ignore($purpose->id)],
            'category' => ['required', Rule::in(['agricultural', 'fishery', 'livelihood', 'medical', 'emergency', 'other'])],
            'is_active' => ['boolean'],
        ]);

        DB::transaction(function () use ($validated, $purpose) {
            $oldValues = $purpose->toArray();

            $purpose->update([
                'name' => $validated['name'],
                'category' => $validated['category'],
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
        $hasLinked = $purpose->allocations()->exists();

        DB::transaction(function () use ($purpose) {
            $oldValues = $purpose->toArray();

            $purpose->update(['is_active' => false]);

            $this->audit->log(
                auth()->id(), 'updated', 'assistance_purposes', $purpose->id,
                $oldValues, $purpose->fresh()->toArray(),
            );
        });

        $message = $hasLinked
            ? 'Purpose deactivated. Existing allocations linked to this purpose are not affected.'
            : 'Purpose deactivated successfully.';

        return response()->json(['success' => true, 'warning' => $hasLinked, 'message' => $message]);
    }

    // ── Resource Types ───────────────────────────

    public function storeResourceType(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:resource_types,name'],
            'unit' => ['required', 'string', 'max:50'],
            'agency_id' => ['required', 'exists:agencies,id'],
            'description' => ['nullable', 'string', 'max:500'],
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
            'name' => ['required', 'string', 'max:255', Rule::unique('resource_types', 'name')->ignore($resourceType->id)],
            'unit' => ['required', 'string', 'max:50'],
            'agency_id' => ['required', 'exists:agencies,id'],
            'description' => ['nullable', 'string', 'max:500'],
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

    // ── Program Names ───────────────────────────────

    public function storeProgramName(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'agency_id' => ['required', 'exists:agencies,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        // Ensure unique name per agency
        $exists = ProgramName::where('agency_id', $validated['agency_id'])
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'errors' => ['name' => ['This program name already exists for this agency.']],
            ], 422);
        }

        $programName = DB::transaction(function () use ($validated) {
            $programName = ProgramName::create([
                'name' => $validated['name'],
                'agency_id' => $validated['agency_id'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $this->audit->log(
                auth()->id(), 'created', 'program_names', $programName->id,
                [], $programName->toArray(),
            );

            return $programName;
        });

        return response()->json(['success' => true, 'programName' => $programName->load('agency')]);
    }

    public function updateProgramName(Request $request, ProgramName $programName): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'agency_id' => ['required', 'exists:agencies,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        // Ensure unique name per agency (exclude self)
        $exists = ProgramName::where('agency_id', $validated['agency_id'])
            ->where('name', $validated['name'])
            ->where('id', '!=', $programName->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'errors' => ['name' => ['This program name already exists for this agency.']],
            ], 422);
        }

        DB::transaction(function () use ($validated, $programName) {
            $oldValues = $programName->toArray();

            $programName->update([
                'name' => $validated['name'],
                'agency_id' => $validated['agency_id'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $this->audit->log(
                auth()->id(), 'updated', 'program_names', $programName->id,
                $oldValues, $programName->fresh()->toArray(),
            );
        });

        return response()->json(['success' => true, 'programName' => $programName->fresh()->load('agency')]);
    }

    public function destroyProgramName(ProgramName $programName): JsonResponse
    {
        if ($programName->distributionEvents()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This program name cannot be deleted because it is linked to existing distribution events.',
            ], 422);
        }

        DB::transaction(function () use ($programName) {
            $this->audit->log(
                auth()->id(), 'deleted', 'program_names', $programName->id,
                $programName->toArray(), [],
            );

            $programName->delete();
        });

        return response()->json(['success' => true, 'message' => 'Program name deleted successfully.']);
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
            'placement_section' => ['required', Rule::in(FormFieldOption::allowedPlacements())],
            'label' => ['required', 'string', 'max:255'],
            'value' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'order_mode' => ['nullable', Rule::in(self::ORDER_MODES)],
            'position_target_id' => ['nullable', 'integer', 'exists:form_field_options,id'],
            'is_required' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $validated['field_group'] = $this->normalizeKey($validated['field_group']);
        $validated['value'] = $this->normalizeKey($validated['value']);

        if ($validated['field_group'] === '' || $validated['value'] === '') {
            return response()->json([
                'success' => false,
                'errors' => [
                    'value' => ['Value and field group must contain letters or numbers.'],
                ],
            ], 422);
        }

        $resolvedOrderMode = $this->resolveOrderMode(
            $validated['order_mode'] ?? null,
            $validated['sort_order'] ?? null,
            false,
        );

        // Ensure unique field_group + value pair
        $exists = FormFieldOption::where('field_group', $validated['field_group'])
            ->where('value', $validated['value'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'errors' => ['value' => ['This value already exists for this field.']],
            ], 422);
        }

        // Check for strict classification compliance conflicts
        $conflictWarning = $this->checkFieldPlacementConflict(
            $validated['field_group'],
            $validated['placement_section']
        );

        $option = DB::transaction(function () use ($validated, $resolvedOrderMode) {
            $resolvedSortOrder = $this->resolveSortOrder(
                $validated['field_group'],
                null,
                $resolvedOrderMode,
                $validated['position_target_id'] ?? null,
                $validated['sort_order'] ?? null,
            );

            // Keep group-level configuration aligned across all options in the same group.
            FormFieldOption::where('field_group', $validated['field_group'])->update([
                'placement_section' => $validated['placement_section'],
                'is_required' => $validated['is_required'] ?? false,
            ]);

            $option = FormFieldOption::create([
                'field_group' => $validated['field_group'],
                'placement_section' => $validated['placement_section'],
                'label' => $validated['label'],
                'value' => $validated['value'],
                'sort_order' => $resolvedSortOrder,
                'is_required' => $validated['is_required'] ?? false,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $this->normalizeGroupSortOrder($validated['field_group']);
            $option->refresh();

            $this->audit->log(
                auth()->id(), 'created', 'form_field_options', $option->id,
                [], $option->toArray(),
            );

            return $option;
        });

        return response()->json([
            'success' => true,
            'option' => $option,
            'warning' => $conflictWarning,
        ]);
    }

    public function updateFormField(Request $request, FormFieldOption $formFieldOption): JsonResponse
    {
        $validated = $request->validate([
            'placement_section' => ['required', Rule::in(FormFieldOption::allowedPlacements())],
            'label' => ['required', 'string', 'max:255'],
            'value' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'order_mode' => ['nullable', Rule::in(self::ORDER_MODES)],
            'position_target_id' => ['nullable', 'integer', 'exists:form_field_options,id'],
            'is_required' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $validated['value'] = $this->normalizeKey($validated['value']);

        if ($validated['value'] === '') {
            return response()->json([
                'success' => false,
                'errors' => [
                    'value' => ['Value must contain letters or numbers.'],
                ],
            ], 422);
        }

        $orderMode = $this->resolveOrderMode(
            $validated['order_mode'] ?? null,
            $validated['sort_order'] ?? null,
            true,
        );

        // Ensure unique field_group + value pair (exclude self)
        $exists = FormFieldOption::where('field_group', $formFieldOption->field_group)
            ->where('value', $validated['value'])
            ->where('id', '!=', $formFieldOption->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'errors' => ['value' => ['This value already exists for this field.']],
            ], 422);
        }

        DB::transaction(function () use ($validated, $formFieldOption, $orderMode) {
            $oldValues = $formFieldOption->toArray();

            $resolvedSortOrder = $formFieldOption->sort_order;
            if ($orderMode !== 'keep') {
                $resolvedSortOrder = $this->resolveSortOrder(
                    $formFieldOption->field_group,
                    $formFieldOption->id,
                    $orderMode,
                    $validated['position_target_id'] ?? null,
                    $validated['sort_order'] ?? null,
                );
            }

            FormFieldOption::where('field_group', $formFieldOption->field_group)
                ->where('id', '!=', $formFieldOption->id)
                ->update([
                    'placement_section' => $validated['placement_section'],
                    'is_required' => $validated['is_required'] ?? false,
                ]);

            $formFieldOption->update([
                'placement_section' => $validated['placement_section'],
                'label' => $validated['label'],
                'value' => $validated['value'],
                'sort_order' => $resolvedSortOrder,
                'is_required' => $validated['is_required'] ?? false,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $this->normalizeGroupSortOrder($formFieldOption->field_group);
            $formFieldOption->refresh();

            $this->audit->log(
                auth()->id(), 'updated', 'form_field_options', $formFieldOption->id,
                $oldValues, $formFieldOption->fresh()->toArray(),
            );
        });

        return response()->json(['success' => true, 'option' => $formFieldOption->fresh()]);
    }

    private function normalizeKey(string $input): string
    {
        return Str::of($input)
            ->trim()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();
    }

    private function resolveOrderMode(?string $requestedMode, ?int $sortOrder, bool $isUpdate): string
    {
        if ($requestedMode && in_array($requestedMode, self::ORDER_MODES, true)) {
            return $requestedMode;
        }

        if ($sortOrder !== null) {
            return 'custom';
        }

        return $isUpdate ? 'keep' : 'auto_end';
    }

    private function resolveSortOrder(
        string $fieldGroup,
        ?int $currentOptionId,
        string $orderMode,
        ?int $positionTargetId,
        ?int $customSortOrder,
    ): int {
        $optionsQuery = FormFieldOption::query()->where('field_group', $fieldGroup);

        if ($currentOptionId !== null) {
            $optionsQuery->where('id', '!=', $currentOptionId);
        }

        $groupOptions = $optionsQuery
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'sort_order']);

        $minSortOrder = (int) ($groupOptions->min('sort_order') ?? 10);
        $maxSortOrder = (int) ($groupOptions->max('sort_order') ?? 0);

        if (in_array($orderMode, ['before', 'after'], true)) {
            if (! $positionTargetId) {
                throw ValidationException::withMessages([
                    'position_target_id' => ['Select a target option to place this item.'],
                ]);
            }

            $target = $groupOptions->firstWhere('id', $positionTargetId);
            if (! $target) {
                throw ValidationException::withMessages([
                    'position_target_id' => ['The selected target option must belong to the same field group.'],
                ]);
            }

            return $orderMode === 'before'
                ? ((int) $target->sort_order) - 1
                : ((int) $target->sort_order) + 1;
        }

        return match ($orderMode) {
            'start' => $groupOptions->isEmpty() ? 10 : $minSortOrder - 10,
            'end', 'auto_end' => $groupOptions->isEmpty() ? 10 : $maxSortOrder + 10,
            'custom' => $customSortOrder ?? ($groupOptions->isEmpty() ? 10 : $maxSortOrder + 10),
            default => $groupOptions->isEmpty() ? 10 : $maxSortOrder + 10,
        };
    }

    private function normalizeGroupSortOrder(string $fieldGroup): void
    {
        $options = FormFieldOption::query()
            ->where('field_group', $fieldGroup)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id']);

        $nextOrder = 10;
        foreach ($options as $option) {
            FormFieldOption::where('id', $option->id)->update(['sort_order' => $nextOrder]);
            $nextOrder += 10;
        }
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
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:form_field_options,id'],
            'items.*.order' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['items'] as $item) {
                FormFieldOption::where('id', $item['id'])->update(['sort_order' => $item['order']]);
            }
        });

        return response()->json(['success' => true]);
    }

    /**
     * Check for potential field placement conflicts in strict classification mode.
     * In strict mode: Farmer fields (placement=farmer_information) should not overlap
     * with Fisherfolk fields (placement=fisherfolk_information) for the same field group.
     *
     * Returns conflict warning message if overlap detected, null otherwise.
     */
    private function checkFieldPlacementConflict(string $fieldGroup, string $newPlacement): ?string
    {
        // Only check for conflicts between farmer and fisherfolk sections
        if ($newPlacement === 'farmer_information') {
            $conflictingPlacement = 'fisherfolk_information';
        } elseif ($newPlacement === 'fisherfolk_information') {
            $conflictingPlacement = 'farmer_information';
        } else {
            return null; // No conflict check needed for other placements
        }

        // Check if this field group already has options in the conflicting placement
        $conflict = FormFieldOption::where('field_group', $fieldGroup)
            ->where('placement_section', $conflictingPlacement)
            ->where('is_active', true)
            ->exists();

        if ($conflict) {
            $conflictingLabel = $conflictingPlacement === 'farmer_information'
                ? 'DA/RSBSA Information (Farmer)'
                : 'BFAR/FishR Information (Fisherfolk)';

            $newLabel = $newPlacement === 'farmer_information'
                ? 'DA/RSBSA Information (Farmer)'
                : 'BFAR/FishR Information (Fisherfolk)';

            return "⚠️ Warning: The field group '{$fieldGroup}' already has active options in {$conflictingLabel}. "
                . "Adding options to {$newLabel} may cause form field overlap violations in strict classification mode. "
                . "Ensure this is intentional — each beneficiary should have either Farmer OR Fisherfolk fields, never both.";
        }

        return null;
    }

    /**
     * Validate that all required FormFieldOption groups exist.
     * Logs warnings for missing groups.
     */
    private function validateFormFieldOptions(): void
    {
        $requiredFieldGroups = [
            'civil_status',
            'highest_education',
            'id_type',
            'farm_ownership',
            'farm_type',
            'fisherfolk_type',
            'arb_classification',
            'ownership_scheme',
        ];

        foreach ($requiredFieldGroups as $group) {
            if (! FormFieldOption::forGroup($group)->exists()) {
                Log::warning("FormFieldOption group is empty: {$group}. System will use hardcoded defaults.", [
                    'field_group' => $group,
                    'timestamp' => now(),
                ]);
            }
        }
    }
}

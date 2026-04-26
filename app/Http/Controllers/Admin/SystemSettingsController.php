<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\AgencyFormField;
use App\Models\Allocation;
use App\Models\AssistancePurpose;
use App\Models\Beneficiary;
use App\Models\DistributionEvent;
use App\Models\FormFieldOption;
use App\Models\ProgramName;
use App\Models\ProgramLegalRequirement;
use App\Models\ResourceType;
use App\Services\AuditLogService;
use App\Support\BeneficiaryCoreFields;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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
     * Show admin settings dashboard with all settings in tabs
     */
    public function index(): View
    {
        // Agencies data
        $agencies = Agency::with('classifications')
            ->withCount([
                'formFields as active_form_fields_count' => fn ($query) => $query->where('is_active', true),
            ])
            ->orderBy('name')
            ->get();

        // Resource Types data
        $activeAgencies = Agency::where('is_active', true)->orderBy('name')->get();
        $resourceTypes = ResourceType::with('agency')->orderBy('name')->get();
        $resourceUnitOptions = ResourceType::unitOptions();
        $purposes = AssistancePurpose::orderBy('category')->orderBy('name')->get();
        $purposeCategoryOptions = AssistancePurpose::getCategoryOptions();

        // Form Fields data
        $this->validateFormFieldOptions();
        $formFields = FormFieldOption::query()
            ->orderBy('field_group')
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get()
            ->groupBy('field_group');

        $classificationCoreFields = collect($this->classificationCoreFieldDefinitions())
            ->map(function (array $definition): array {
                return [
                    ...$definition,
                    'label' => $this->classificationCoreFieldLabel(
                        $definition['field_name'],
                        (string) $definition['label'],
                    ),
                    'is_required' => $this->classificationCoreFieldRequiredStatus(
                        $definition['field_name'],
                        (bool) $definition['default_required'],
                    ),
                ];
            })
            ->groupBy('classification');

        return view('admin.settings.index', compact('agencies', 'activeAgencies', 'resourceTypes', 'resourceUnitOptions', 'purposes', 'purposeCategoryOptions', 'formFields', 'classificationCoreFields'));
    }

    public function indexProgramNames(Request $request): View
    {
        $agencies = Agency::with('classifications:id,name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $agencyClassificationMap = $agencies
            ->mapWithKeys(function (Agency $agency): array {
                return [
                    (string) $agency->id => $agency->classifications->pluck('name')->values()->all(),
                ];
            })
            ->all();
        $programQuery = ProgramName::with(['agency', 'legalRequirements'])
            ->orderBy('name');

        $agencyId = $request->input('agency', $request->input('agency_id'));
        if ($agencyId !== null && $agencyId !== '') {
            $programQuery->where('agency_id', (int) $agencyId);
        }

        if ($request->filled('classification')) {
            $programQuery->where('classification', $request->input('classification'));
        }

        if ($request->filled('status')) {
            $programQuery->where('is_active', $request->input('status') === 'active');
        }

        if ($request->filled('search')) {
            $programQuery->where('name', 'like', '%' . $request->input('search') . '%');
        }

        $programNames = $programQuery->paginate(25)->withQueryString();

        $summary = [
            'total' => ProgramName::count(),
            'active' => ProgramName::where('is_active', true)->count(),
            'inactive' => ProgramName::where('is_active', false)->count(),
        ];

        return view('admin.settings.program-names.index', compact('agencies', 'programNames', 'summary', 'agencyClassificationMap'));
    }

    public function diagnostics(): View
    {
        $coreAgencyNames = ['DA', 'BFAR', 'DAR'];
        $coreAgencies = Agency::whereIn('name', $coreAgencyNames)->get();

        $repairedFieldCount = 0;
        DB::transaction(function () use ($coreAgencies, &$repairedFieldCount): void {
            foreach ($coreAgencies as $agency) {
                $repairedFieldCount += $this->bootstrapAgencyCoreFields($agency);
            }
        });

        $agencies = Agency::with(['classifications', 'formFields.options'])->orderBy('name')->get();

        $routeChecks = [
            ['label' => 'Settings Index', 'name' => 'admin.settings.index'],
            ['label' => 'Agency Show API', 'name' => 'admin.settings.agencies.show'],
            ['label' => 'Agency Store', 'name' => 'admin.settings.agencies.store'],
            ['label' => 'Agency Update', 'name' => 'admin.settings.agencies.update'],
            ['label' => 'Agency Status Toggle', 'name' => 'admin.settings.agencies.status'],
            ['label' => 'Agency Form Fields List', 'name' => 'admin.settings.agencies.form-fields.index'],
            ['label' => 'Agency Form Field Store', 'name' => 'admin.settings.agencies.form-fields.store'],
            ['label' => 'Global Form Fields List', 'name' => 'admin.settings.form-fields.list'],
            ['label' => 'API Agencies by Classification', 'name' => 'api.agencies.by-classification'],
            ['label' => 'API Agencies Form Fields', 'name' => 'api.agencies.form-fields'],
        ];

        $routeDiagnostics = collect($routeChecks)->map(function (array $item): array {
            return [
                'label' => $item['label'],
                'name' => $item['name'],
                'exists' => Route::has($item['name']),
            ];
        })->values();

        $coreAgencyFieldGroups = BeneficiaryCoreFields::agencySpecificCoreFieldNames();
        $globalCoreGroupCounts = FormFieldOption::query()
            ->whereIn('field_group', $coreAgencyFieldGroups)
            ->selectRaw('field_group, COUNT(*) as total')
            ->groupBy('field_group')
            ->pluck('total', 'field_group');

        $agencyDiagnostics = $agencies->map(function (Agency $agency): array {
            $templates = collect($this->agencyCoreFieldTemplatesFor($agency));
            $expectedCoreFields = $templates
                ->pluck('field_name')
                ->map(fn ($name) => strtolower(trim((string) $name)))
                ->filter()
                ->unique()
                ->values();

            $existingCoreFields = $agency->formFields
                ->pluck('field_name')
                ->map(fn ($name) => strtolower(trim((string) $name)))
                ->filter()
                ->unique()
                ->values();

            $missingCoreFields = $expectedCoreFields->diff($existingCoreFields)->values();

            return [
                'id' => $agency->id,
                'name' => $agency->name,
                'full_name' => $agency->full_name,
                'is_active' => (bool) $agency->is_active,
                'classifications' => $agency->classifications->pluck('name')->values()->all(),
                'total_form_fields' => $agency->formFields->count(),
                'expected_core_count' => $expectedCoreFields->count(),
                'existing_core_count' => $expectedCoreFields->intersect($existingCoreFields)->count(),
                'missing_core_fields' => $missingCoreFields->all(),
            ];
        })->values();

        return view('admin.settings.diagnostics', [
            'routeDiagnostics' => $routeDiagnostics,
            'agencyDiagnostics' => $agencyDiagnostics,
            'globalCoreGroupCounts' => $globalCoreGroupCounts,
            'repairedFieldCount' => $repairedFieldCount,
        ]);
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

    /**
     * Get a single agency with classifications
     * GET /settings/agencies/{agency}
     */
    public function getAgency(Request $request, Agency $agency): JsonResponse
    {
        try {
            return response()->json([
                'id' => $agency->id,
                'name' => $agency->name,
                'full_name' => $agency->full_name,
                'description' => $agency->description,
                'is_active' => $agency->is_active,
                'classification_ids' => $agency->classifications()->pluck('classifications.id')->toArray(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error fetching agency', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to load agency'], 500);
        }
    }

    /**
     * Resolve derived classification for an agency.
     * GET /admin/settings/agencies/{agency}/classification
     */
    public function resolveAgencyClassification(Agency $agency): JsonResponse
    {
        try {
            $classification = $this->deriveProgramClassificationFromAgency($agency->id);

            return response()->json([
                'success' => true,
                'classification' => $classification,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Unable to derive classification.',
            ], 422);
        }
    }

    // ── Agencies ─────────────────────────────────

    public function storeAgency(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:agencies,name'],
            'full_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'classifications' => ['required', 'array', 'min:1'],
            'classifications.*' => ['integer', 'exists:classifications,id'],
        ]);

        $agency = DB::transaction(function () use ($validated) {
            $agency = Agency::create([
                'name' => $validated['name'],
                'full_name' => $validated['full_name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $agency->classifications()->sync($validated['classifications'] ?? []);

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
            'classifications' => ['required', 'array', 'min:1'],
            'classifications.*' => ['integer', 'exists:classifications,id'],
        ]);

        DB::transaction(function () use ($validated, $agency) {
            $oldValues = $agency->toArray();

            $agency->update([
                'name' => $validated['name'],
                'full_name' => $validated['full_name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $agency->classifications()->sync($validated['classifications'] ?? []);

            $this->audit->log(
                auth()->id(), 'updated', 'agencies', $agency->id,
                $oldValues, $agency->fresh()->toArray(),
            );
        });

        return response()->json(['success' => true, 'agency' => $agency->fresh()]);
    }

    public function updateAgencyStatus(Request $request, Agency $agency): JsonResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($validated, $agency) {
            $oldValues = $agency->toArray();

            $agency->update([
                'is_active' => (bool) $validated['is_active'],
            ]);

            $this->audit->log(
                auth()->id(),
                'updated',
                'agencies',
                $agency->id,
                $oldValues,
                $agency->fresh()->toArray(),
            );
        });

        return response()->json([
            'success' => true,
            'agency' => $agency->fresh(),
            'message' => $validated['is_active'] ? 'Agency activated successfully.' : 'Agency deactivated successfully.',
        ]);
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
        $categories = array_keys(AssistancePurpose::getCategoryOptions());

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:assistance_purposes,name'],
            'category' => ['required', Rule::in($categories)],
            'type' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $purpose = DB::transaction(function () use ($validated) {
            $purpose = AssistancePurpose::create([
                'name' => $validated['name'],
                'category' => $validated['category'],
                'type' => $validated['type'],
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
        $categories = array_keys(AssistancePurpose::getCategoryOptions());

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('assistance_purposes', 'name')->ignore($purpose->id)],
            'category' => ['required', Rule::in($categories)],
            'type' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        DB::transaction(function () use ($validated, $purpose) {
            $oldValues = $purpose->toArray();

            $purpose->update([
                'name' => $validated['name'],
                'category' => $validated['category'],
                'type' => $validated['type'],
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
        $request->merge([
            'unit' => ResourceType::normalizeUnit($request->input('unit')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:resource_types,name'],
            'unit' => ['required', 'string', 'max:50', Rule::in(ResourceType::unitValues())],
            'agency_id' => ['required', 'exists:agencies,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);
        $validated['unit'] = ResourceType::normalizeUnit($validated['unit']);

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
        $request->merge([
            'unit' => ResourceType::normalizeUnit($request->input('unit')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('resource_types', 'name')->ignore($resourceType->id)],
            'unit' => ['required', 'string', 'max:50', Rule::in(ResourceType::unitValues())],
            'agency_id' => ['required', 'exists:agencies,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = (bool) ($validated['is_active'] ?? $resourceType->is_active);
        $validated['unit'] = ResourceType::normalizeUnit($validated['unit']);

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
            'classification' => ['nullable', Rule::in(['Farmer', 'Fisherfolk', 'Both'])],
        ]);

        $derivedClassification = $this->deriveProgramClassificationFromAgency((int) $validated['agency_id']);

        if (! empty($validated['classification']) && $validated['classification'] !== $derivedClassification) {
            throw ValidationException::withMessages([
                'classification' => ['Classification is defined by the selected agency and cannot be set manually.'],
            ]);
        }

        $validated['classification'] = $derivedClassification;

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

        $programName = DB::transaction(function () use ($validated, $derivedClassification) {
            $programName = ProgramName::create([
                'name' => $validated['name'],
                'agency_id' => $validated['agency_id'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'classification' => $derivedClassification,
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
            'classification' => ['nullable', Rule::in(['Farmer', 'Fisherfolk', 'Both'])],
        ]);

        $derivedClassification = $this->deriveProgramClassificationFromAgency((int) $validated['agency_id']);

        if (! empty($validated['classification']) && $validated['classification'] !== $derivedClassification) {
            throw ValidationException::withMessages([
                'classification' => ['Classification is defined by the selected agency and cannot be set manually.'],
            ]);
        }

        $validated['classification'] = $derivedClassification;

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

        DB::transaction(function () use ($validated, $programName, $derivedClassification) {
            $oldValues = $programName->toArray();

            $programName->update([
                'name' => $validated['name'],
                'agency_id' => $validated['agency_id'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'classification' => $derivedClassification,
            ]);

            $this->audit->log(
                auth()->id(), 'updated', 'program_names', $programName->id,
                $oldValues, $programName->fresh()->toArray(),
            );
        });

        return response()->json(['success' => true, 'programName' => $programName->fresh()->load('agency')]);
    }

    private function deriveProgramClassificationFromAgency(int $agencyId): string
    {
        $agency = Agency::with('classifications:id,name')->find($agencyId);

        if (! $agency) {
            throw ValidationException::withMessages([
                'agency_id' => ['The selected agency could not be found.'],
            ]);
        }

        $classificationNames = $agency->classifications
            ->pluck('name')
            ->map(fn ($name) => strtolower(trim((string) $name)))
            ->filter()
            ->unique()
            ->values();

        $hasFarmer = $classificationNames->contains('farmer');
        $hasFisherfolk = $classificationNames->contains('fisherfolk');

        if ($hasFarmer && $hasFisherfolk) {
            return 'Both';
        }

        if ($hasFarmer) {
            return 'Farmer';
        }

        if ($hasFisherfolk) {
            return 'Fisherfolk';
        }

        throw ValidationException::withMessages([
            'agency_id' => ['Selected agency has no valid classification mapping. Please configure agency classifications first.'],
        ]);
    }

    public function toggleProgramStatus(Request $request, ProgramName $programName): JsonResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($validated, $programName) {
            $oldValues = $programName->toArray();

            $programName->update([
                'is_active' => $validated['is_active'],
            ]);

            $this->audit->log(
                auth()->id(), 'updated', 'program_names', $programName->id,
                $oldValues, $programName->fresh()->toArray(),
            );
        });

        return response()->json([
            'success' => true,
            'message' => $validated['is_active'] ? 'Program reactivated successfully.' : 'Program deactivated successfully.',
            'programName' => $programName->fresh()->load('agency'),
        ]);
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

    // ── Program Legal Requirements ─────────────────

    public function uploadProgramLegalRequirement(Request $request, ProgramName $programName): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'document_type' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        if (! $request->hasFile('file')) {
            return response()->json([
                'success' => false,
                'message' => 'No file provided.',
            ], 422);
        }

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->extension();
        $mimeType = $file->getMimeType();

        // Generate unique filename with timestamp
        $uuid = Str::uuid();
        $timestamp = now()->format('YmdHis');
        $storedName = "{$uuid}_{$timestamp}.{$extension}";

        // Store path with year/month structure
        $yearMonth = now()->format('Y/m');
        $path = "program-requirements/{$programName->id}/{$yearMonth}/{$storedName}";

        try {
            $fileContent = file_get_contents($file->getRealPath());
            $sha256 = hash('sha256', $fileContent);

            // Store the file
            Storage::disk('program_documents')->put($path, $fileContent);

            $requirement = DB::transaction(function () use ($programName, $file, $path, $storedName, $mimeType, $extension, $sha256, $originalName, $request) {
                $requirement = ProgramLegalRequirement::create([
                    'program_name_id' => $programName->id,
                    'uploaded_by' => auth()->id(),
                    'document_type' => $request->input('document_type'),
                    'original_name' => $originalName,
                    'stored_name' => $storedName,
                    'path' => $path,
                    'disk' => 'program_documents',
                    'mime_type' => $mimeType,
                    'extension' => $extension,
                    'size_bytes' => $file->getSize(),
                    'sha256' => $sha256,
                    'remarks' => $request->input('remarks'),
                ]);

                $this->audit->log(
                    auth()->id(), 'uploaded', 'program_legal_requirements', $requirement->id,
                    [], $requirement->toArray(),
                );

                return $requirement;
            });

            return response()->json([
                'success' => true,
                'requirement' => $requirement->load('uploader'),
                'message' => 'Legal requirement document uploaded successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to upload program legal requirement', [
                'program_id' => $programName->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document. Please try again.',
            ], 500);
        }
    }

    public function listProgramLegalRequirements(ProgramName $programName): JsonResponse
    {
        $requirements = $programName->legalRequirements()
            ->with('uploader:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'requirements' => $requirements,
        ]);
    }

    public function downloadProgramLegalRequirement(ProgramLegalRequirement $requirement): Response
    {
        if (! Storage::disk('program_documents')->exists($requirement->path)) {
            abort(404, 'File not found');
        }

        $this->audit->log(
            auth()->id(), 'downloaded', 'program_legal_requirements', $requirement->id,
            [], $requirement->toArray(),
        );

        return Storage::disk('program_documents')->download(
            $requirement->path,
            $requirement->original_name,
            [
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function deleteProgramLegalRequirement(ProgramLegalRequirement $requirement): JsonResponse
    {
        try {
            DB::transaction(function () use ($requirement) {
                $requirementData = $requirement->toArray();

                // Delete from storage
                if (Storage::disk('program_documents')->exists($requirement->path)) {
                    Storage::disk('program_documents')->delete($requirement->path);
                }

                // Log the deletion
                $this->audit->log(
                    auth()->id(), 'deleted', 'program_legal_requirements', $requirement->id,
                    $requirementData, [],
                );

                // Delete from database
                $requirement->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Legal requirement document deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete program legal requirement', [
                'requirement_id' => $requirement->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document. Please try again.',
            ], 500);
        }
    }

    // ── Program Detail View ─────────────────────────

    public function showProgramDetail(Request $request, ProgramName $programName): View
    {
        $programName->load([
            'agency',
            'legalRequirements.uploader',
        ]);

        $eventsPerPage = 10;
        $allocationsPerPage = 10;
        $directPerPage = 10;
        $beneficiariesPerPage = 15;

        $events = DistributionEvent::query()
            ->where('program_name_id', $programName->id)
            ->with([
                'allocations.beneficiary',
                'allocations.resourceType',
                'allocations.assistancePurpose',
                'resourceType',
                'barangay',
            ])
            ->withCount('allocations')
            ->orderByDesc('distribution_date')
            ->paginate($eventsPerPage, ['*'], 'events_page')
            ->withQueryString();

        // Include both event-based and direct allocations under this specific program.
        $allocations = Allocation::query()
            ->where('program_name_id', $programName->id)
            ->with([
                'beneficiary',
                'resourceType',
                'assistancePurpose',
                'distributionEvent.barangay',
            ])
            ->orderByDesc('created_at')
            ->paginate($allocationsPerPage, ['*'], 'allocations_page')
            ->withQueryString();

        $directAssistanceRecords = Allocation::query()
            ->where('program_name_id', $programName->id)
            ->where('release_method', 'direct')
            ->with([
                'beneficiary',
                'resourceType',
                'assistancePurpose',
                'distributionEvent.barangay',
            ])
            ->orderByDesc('created_at')
            ->paginate($directPerPage, ['*'], 'direct_page')
            ->withQueryString();

        $beneficiaryIds = Allocation::query()
            ->where('program_name_id', $programName->id)
            ->pluck('beneficiary_id')
            ->unique()
            ->values();

        $beneficiaries = Beneficiary::query()
            ->whereIn('id', $beneficiaryIds)
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('full_name', 'like', '%' . $request->input('search') . '%');
            })
            ->orderBy('full_name')
            ->paginate($beneficiariesPerPage, ['*'], 'beneficiaries_page')
            ->withQueryString();

        $beneficiaryAllocationCounts = Allocation::query()
            ->where('program_name_id', $programName->id)
            ->selectRaw('beneficiary_id, COUNT(*) as total')
            ->groupBy('beneficiary_id')
            ->pluck('total', 'beneficiary_id')
            ->map(fn ($count) => (int) $count)
            ->toArray();

        // Analytics datasets used by the detail charts.
        $allRecords = Allocation::query()
            ->where('program_name_id', $programName->id)
            ->get(['beneficiary_id', 'resource_type_id', 'assistance_purpose_id', 'amount', 'created_at'])
            ->map(function (Allocation $allocation) {
                return [
                    'beneficiary_id' => $allocation->beneficiary_id,
                    'resource_type_id' => $allocation->resource_type_id,
                    'assistance_purpose_id' => $allocation->assistance_purpose_id,
                    'amount' => (float) ($allocation->amount ?? 0),
                    'occurred_at' => $allocation->created_at,
                ];
            });

        $resourceTypeNames = ResourceType::query()->pluck('name', 'id');
        $purposeNames = AssistancePurpose::query()->pluck('name', 'id');

        $uniqueBeneficiaryRows = Beneficiary::query()
            ->whereIn('id', $allRecords->pluck('beneficiary_id')->filter()->unique()->values())
            ->with('barangay:id,name')
            ->get(['id', 'barangay_id']);

        $barangayReach = $uniqueBeneficiaryRows
            ->groupBy(function (Beneficiary $beneficiary): string {
                return (string) ($beneficiary->barangay?->name ?? 'Unknown');
            })
            ->map(function ($rows, $name) {
                return [
                    'name' => $name,
                    'total' => $rows->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $resourceMix = $allRecords
            ->groupBy('resource_type_id')
            ->map(function ($rows, $resourceTypeId) use ($resourceTypeNames) {
                $resourceTypeId = (int) $resourceTypeId;

                return [
                    'name' => $resourceTypeNames[$resourceTypeId] ?? 'Unknown',
                    'total' => $rows->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $monthlyTrend = $allRecords
            ->groupBy(function (array $row): string {
                return \Carbon\Carbon::parse($row['occurred_at'])->format('Y-m');
            })
            ->sortKeys()
            ->map(function ($rows, $monthKey) {
                return [
                    'month' => \Carbon\Carbon::createFromFormat('Y-m', $monthKey)->format('M Y'),
                    'total' => $rows->count(),
                    'total_amount' => (float) $rows->sum('amount'),
                ];
            })
            ->values();

        $purposeBreakdown = $allRecords
            ->groupBy('assistance_purpose_id')
            ->map(function ($rows, $purposeId) use ($purposeNames) {
                $purposeId = (int) $purposeId;

                return [
                    'name' => $purposeNames[$purposeId] ?? 'Unspecified',
                    'total' => $rows->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $totalEvents = $events->total();
        $activeDistributionsCount = DistributionEvent::query()
            ->where('program_name_id', $programName->id)
            ->whereIn('status', ['Ongoing', 'Pending', 'ongoing', 'pending'])
            ->count();
        $complianceDocumentsCount = $programName->legalRequirements->count();
        $totalAllocatedAmount = (float) $allRecords->sum('amount');
        $totalBeneficiaries = $beneficiaryIds->count();

        return view('admin.settings.program-names.detail', compact(
            'programName',
            'events',
            'allocations',
            'directAssistanceRecords',
            'beneficiaries',
            'beneficiaryAllocationCounts',
            'totalEvents',
            'activeDistributionsCount',
            'complianceDocumentsCount',
            'barangayReach',
            'resourceMix',
            'monthlyTrend',
            'purposeBreakdown',
            'totalAllocatedAmount',
            'totalBeneficiaries',
        ));
    }

    /**
     * Get legal requirements count for a program (API)
     */
    public function getProgramLegalRequirementsCount(ProgramName $programName): JsonResponse
    {
        return response()->json([
            'count' => $programName->legalRequirements()->count(),
        ]);
    }

    /**
     * Get legal requirements documents for a program (API)
     */
    public function getProgramLegalRequirements(ProgramName $programName): JsonResponse
    {
        $documents = $programName->legalRequirements()
            ->get()
            ->map(fn ($req) => [
                'id' => $req->id,
                'filename' => $req->filename,
                'type' => $req->document_type,
                'url' => route('admin.settings.program-names.legal-requirements.download', [$programName, $req]),
            ]);

        return response()->json([
            'documents' => $documents,
        ]);
    }

    /**
     * Get program details with statistics (API)
     */
    public function getProgramDetails(ProgramName $programName): JsonResponse
    {
        $programName->load('agency');

        $events = DistributionEvent::query()
            ->where('program_name_id', $programName->id)
            ->get();

        // Include all allocations directly linked to this program.
        $allocations = Allocation::query()
            ->where('program_name_id', $programName->id)
            ->get();

        // Get unique beneficiary IDs (allocations now include direct assistance via release_method = 'direct')
        $beneficiaryIds = $allocations->pluck('beneficiary_id')
            ->unique()
            ->values();

        return response()->json([
            'program' => [
                'id' => $programName->id,
                'name' => $programName->name,
                'description' => $programName->description,
                'classification' => $programName->classification,
                'is_active' => (bool) $programName->is_active,
                'agency' => [
                    'id' => $programName->agency->id,
                    'name' => $programName->agency->name,
                ],
            ],
            'allocation_count' => $allocations->count(),
            'beneficiary_count' => $beneficiaryIds->count(),
        ]);
    }

    // ── Form Fields ─────────────────────────────

    public function listFormFields(): JsonResponse
    {
        $hiddenGlobalGroups = BeneficiaryCoreFields::agencySpecificCoreFieldNames();

        return response()->json(
            FormFieldOption::query()
                ->whereNotIn('field_group', $hiddenGlobalGroups)
                ->orderBy('field_group')
                ->orderBy('sort_order')
                ->orderBy('label')
                ->get()
        );
    }

    public function listClassificationCoreFields(): JsonResponse
    {
        $rows = collect($this->classificationCoreFieldDefinitions())
            ->map(function (array $definition): array {
                return [
                    ...$definition,
                    'label' => $this->classificationCoreFieldLabel(
                        $definition['field_name'],
                        (string) $definition['label'],
                    ),
                    'is_required' => $this->classificationCoreFieldRequiredStatus(
                        $definition['field_name'],
                        (bool) $definition['default_required'],
                    ),
                ];
            })
            ->values();

        return response()->json($rows);
    }

    public function updateClassificationCoreField(Request $request, string $fieldName): JsonResponse
    {
        $fieldName = strtolower(trim($fieldName));
        $definitions = collect($this->classificationCoreFieldDefinitions())
            ->keyBy(fn (array $definition) => $definition['field_name']);

        if (! $definitions->has($fieldName)) {
            return response()->json([
                'success' => false,
                'message' => 'Unknown classification core field.',
            ], 404);
        }

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'is_required' => ['required', 'boolean'],
        ]);

        $definition = $definitions->get($fieldName);

        DB::transaction(function () use ($fieldName, $validated, $definition): void {
            FormFieldOption::query()
                ->where('field_group', $fieldName)
                ->update([
                    'is_required' => (bool) $validated['is_required'],
                ]);

            $configRow = $this->findOrCreateClassificationCoreConfigRow($fieldName, $definition);
            $configRow->label = trim((string) $validated['label']);
            $configRow->is_required = (bool) $validated['is_required'];
            $configRow->is_active = true;
            $configRow->save();
        });

        return response()->json([
            'success' => true,
            'field_name' => $fieldName,
            'label' => trim((string) $validated['label']),
            'is_required' => (bool) $validated['is_required'],
        ]);
    }

    public function updateClassificationCoreFieldRequired(Request $request, string $fieldName): JsonResponse
    {
        $fieldName = strtolower(trim($fieldName));
        $definitions = collect($this->classificationCoreFieldDefinitions())
            ->keyBy(fn (array $definition) => $definition['field_name']);

        if (! $definitions->has($fieldName)) {
            return response()->json([
                'success' => false,
                'message' => 'Unknown classification core field.',
            ], 404);
        }

        $validated = $request->validate([
            'is_required' => ['required', 'boolean'],
        ]);

        $definition = $definitions->get($fieldName);

        DB::transaction(function () use ($fieldName, $validated, $definition): void {
            FormFieldOption::query()
                ->where('field_group', $fieldName)
                ->update([
                    'is_required' => (bool) $validated['is_required'],
                ]);

            $configRow = $this->findOrCreateClassificationCoreConfigRow($fieldName, $definition);
            $configRow->is_required = (bool) $validated['is_required'];
            $configRow->is_active = true;
            $configRow->save();
        });

        return response()->json([
            'success' => true,
            'field_name' => $fieldName,
            'is_required' => (bool) $validated['is_required'],
        ]);
    }

    public function storeFormField(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'field_group' => ['required', 'string', 'max:100'],
            'field_type' => ['required', Rule::in(FormFieldOption::supportedFieldTypes())],
            'placement_section' => ['required', Rule::in(FormFieldOption::allowedPlacements())],
            'label' => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'order_mode' => ['nullable', Rule::in(self::ORDER_MODES)],
            'position_target_id' => ['nullable', 'integer', 'exists:form_field_options,id'],
            'is_required' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $validated['field_group'] = $this->normalizeKey($validated['field_group']);
        $validated['field_type'] = strtolower(trim((string) $validated['field_type']));

        if ($this->isPersonalInformationCoreFieldName($validated['field_group'])) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'field_group' => [
                        "Core personal-information field '{$validated['field_group']}' is schema-managed and cannot be edited from Settings.",
                    ],
                ],
            ], 422);
        }

        $isOptionBased = in_array($validated['field_type'], FormFieldOption::optionBasedFieldTypes(), true);
        $normalizedValueSource = (string) ($validated['value'] ?? '');
        if ($isOptionBased && trim($normalizedValueSource) === '') {
            $normalizedValueSource = (string) ($validated['label'] ?? '');
        }

        $validated['value'] = $isOptionBased
            ? $this->normalizeKey($normalizedValueSource)
            : $validated['field_group'];

        if ($validated['field_group'] === '' || ($isOptionBased && $validated['value'] === '')) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'value' => ['Field group and option label/value must contain letters or numbers.'],
                ],
            ], 422);
        }

        if ($overlapError = $this->globalAgencyOverlapError($validated['field_group'])) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'field_group' => [$overlapError],
                ],
            ], 422);
        }

        $existingGroupOptions = FormFieldOption::query()
            ->where('field_group', $validated['field_group'])
            ->orderBy('id')
            ->get(['id', 'field_type']);

        if ($existingGroupOptions->isNotEmpty()) {
            $existingType = (string) ($existingGroupOptions->first()->field_type ?? FormFieldOption::FIELD_TYPE_DROPDOWN);
            if ($existingType !== $validated['field_type']) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'field_type' => [
                            "Field group '{$validated['field_group']}' already exists as type '{$existingType}'. "
                            . 'Use the same type for all entries under one group.',
                        ],
                    ],
                ], 422);
            }

            if (! $isOptionBased) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'field_group' => [
                            "Field group '{$validated['field_group']}' stores a single {$validated['field_type']} value. "
                            . 'Edit the existing field instead of adding another item.',
                        ],
                    ],
                ], 422);
            }
        }

        if ($validated['placement_section'] === FormFieldOption::PLACEMENT_DAR_INFORMATION) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'placement_section' => ['DAR placement is not supported for global fields. Configure DAR fields under Agencies > Agency Fields.'],
                ],
            ], 422);
        }

        $resolvedOrderMode = $this->resolveOrderMode(
            $validated['order_mode'] ?? null,
            $validated['sort_order'] ?? null,
            false,
        );

        // Ensure unique field_group + value pair for option-based groups.
        $exists = $isOptionBased
            ? FormFieldOption::where('field_group', $validated['field_group'])
                ->where('value', $validated['value'])
                ->exists()
            : false;

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
                'field_type' => $validated['field_type'],
                'placement_section' => $validated['placement_section'],
                'is_required' => $validated['is_required'] ?? false,
            ]);

            $option = FormFieldOption::create([
                'field_group' => $validated['field_group'],
                'field_type' => $validated['field_type'],
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
        if ($this->isPersonalInformationCoreFieldName((string) $formFieldOption->field_group)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'field_group' => [
                        "Core personal-information field '{$formFieldOption->field_group}' is schema-managed and cannot be edited from Settings.",
                    ],
                ],
            ], 422);
        }

        $validated = $request->validate([
            'field_group' => ['sometimes', 'string', 'max:100'],
            'field_type' => ['required', Rule::in(FormFieldOption::supportedFieldTypes())],
            'placement_section' => ['required', Rule::in(FormFieldOption::allowedPlacements())],
            'label' => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'order_mode' => ['nullable', Rule::in(self::ORDER_MODES)],
            'position_target_id' => ['nullable', 'integer', 'exists:form_field_options,id'],
            'is_required' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $validated['field_type'] = strtolower(trim((string) $validated['field_type']));
        $targetFieldGroup = array_key_exists('field_group', $validated)
            ? $this->normalizeKey((string) $validated['field_group'])
            : (string) $formFieldOption->field_group;

        if ($targetFieldGroup === '') {
            return response()->json([
                'success' => false,
                'errors' => [
                    'field_group' => ['Field group must contain letters or numbers.'],
                ],
            ], 422);
        }

        if ($this->isPersonalInformationCoreFieldName($targetFieldGroup)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'field_group' => [
                        "Core personal-information field '{$targetFieldGroup}' is schema-managed and cannot be edited from Settings.",
                    ],
                ],
            ], 422);
        }

        if ($overlapError = $this->globalAgencyOverlapError($targetFieldGroup)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'field_group' => [$overlapError],
                ],
            ], 422);
        }

        $isOptionBased = in_array($validated['field_type'], FormFieldOption::optionBasedFieldTypes(), true);
        $normalizedValueSource = (string) ($validated['value'] ?? '');
        if ($isOptionBased && trim($normalizedValueSource) === '') {
            $normalizedValueSource = (string) ($validated['label'] ?? '');
        }

        $validated['value'] = $isOptionBased
            ? $this->normalizeKey($normalizedValueSource)
            : $targetFieldGroup;

        if ($isOptionBased && $validated['value'] === '') {
            return response()->json([
                'success' => false,
                'errors' => [
                    'value' => ['Option label/value must contain letters or numbers.'],
                ],
            ], 422);
        }

        $targetGroupOptions = FormFieldOption::query()
            ->where('field_group', $targetFieldGroup)
            ->where('id', '!=', $formFieldOption->id)
            ->orderBy('id')
            ->get(['id', 'field_type']);

        if ($targetGroupOptions->isNotEmpty()) {
            $existingType = (string) ($targetGroupOptions->first()->field_type ?? FormFieldOption::FIELD_TYPE_DROPDOWN);
            if ($existingType !== $validated['field_type']) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'field_type' => [
                            "Field group '{$targetFieldGroup}' already exists as type '{$existingType}'. "
                            . 'Use the same type for all entries under one group.',
                        ],
                    ],
                ], 422);
            }

            if (! $isOptionBased) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'field_group' => [
                            "Field group '{$targetFieldGroup}' already stores a single {$validated['field_type']} value.",
                        ],
                    ],
                ], 422);
            }
        }

        $groupOptionsCount = FormFieldOption::query()
            ->where('field_group', $targetFieldGroup)
            ->where('id', '!=', $formFieldOption->id)
            ->count();

        if (! $isOptionBased && $groupOptionsCount > 0) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'field_type' => [
                        "Field group '{$targetFieldGroup}' already has an option. "
                        . 'Single-value fields can only keep one item per field group.',
                    ],
                ],
            ], 422);
        }

        if ($validated['placement_section'] === FormFieldOption::PLACEMENT_DAR_INFORMATION) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'placement_section' => ['DAR placement is not supported for global fields. Configure DAR fields under Agencies > Agency Fields.'],
                ],
            ], 422);
        }

        $orderMode = $this->resolveOrderMode(
            $validated['order_mode'] ?? null,
            $validated['sort_order'] ?? null,
            true,
        );

        // Ensure unique field_group + value pair (exclude self)
        $exists = FormFieldOption::where('field_group', $targetFieldGroup)
            ->where('value', $validated['value'])
            ->where('id', '!=', $formFieldOption->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'errors' => ['value' => ['This value already exists for this field.']],
            ], 422);
        }

        DB::transaction(function () use ($validated, $formFieldOption, $orderMode, $targetFieldGroup) {
            $oldValues = $formFieldOption->toArray();
            $previousFieldGroup = (string) $formFieldOption->field_group;

            $resolvedSortOrder = $formFieldOption->sort_order;
            if ($orderMode !== 'keep') {
                $resolvedSortOrder = $this->resolveSortOrder(
                    $targetFieldGroup,
                    $formFieldOption->id,
                    $orderMode,
                    $validated['position_target_id'] ?? null,
                    $validated['sort_order'] ?? null,
                );
            }

            FormFieldOption::where('field_group', $targetFieldGroup)
                ->where('id', '!=', $formFieldOption->id)
                ->update([
                    'field_type' => $validated['field_type'],
                    'placement_section' => $validated['placement_section'],
                    'is_required' => $validated['is_required'] ?? false,
                ]);

            $formFieldOption->update([
                'field_group' => $targetFieldGroup,
                'field_type' => $validated['field_type'],
                'placement_section' => $validated['placement_section'],
                'label' => $validated['label'],
                'value' => $validated['value'],
                'sort_order' => $resolvedSortOrder,
                'is_required' => $validated['is_required'] ?? false,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $this->normalizeGroupSortOrder($targetFieldGroup);
            if ($previousFieldGroup !== $targetFieldGroup) {
                $this->normalizeGroupSortOrder($previousFieldGroup);
            }
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
        if ($this->isPersonalInformationCoreFieldName((string) $formFieldOption->field_group)) {
            return response()->json([
                'success' => false,
                'message' => "Core personal-information field '{$formFieldOption->field_group}' is schema-managed and cannot be deleted from Settings.",
            ], 422);
        }

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

    private function globalAgencyOverlapError(string $fieldGroup): ?string
    {
        $normalizedGroup = $this->normalizeKey($fieldGroup);
        if ($normalizedGroup === '') {
            return null;
        }

        $hasAgencyOverlap = AgencyFormField::query()
            ->where('field_name', $normalizedGroup)
            ->exists();

        if ($hasAgencyOverlap) {
            return "Field group '{$normalizedGroup}' is already used by at least one agency dynamic field name. "
                . 'Global field groups and agency dynamic field names must be distinct.';
        }

        return null;
    }

    private function agencyGlobalOverlapError(string $fieldName): ?string
    {
        $normalizedFieldName = $this->normalizeKey($fieldName);
        if ($normalizedFieldName === '') {
            return null;
        }

        $hasGlobalOverlap = FormFieldOption::query()
            ->where('field_group', $normalizedFieldName)
            ->exists();

        if ($hasGlobalOverlap) {
            return "Field name '{$normalizedFieldName}' is already used by a global field group. "
                . 'Agency dynamic field names must be distinct from global field groups.';
        }

        return null;
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

    /**
     * Get form fields for a specific agency
     * GET /settings/agencies/{agency}/form-fields
     */
    public function getAgencyFormFields(Request $request, Agency $agency): JsonResponse
    {
        try {
            $this->bootstrapAgencyCoreFields($agency);

            $fields = $agency->formFields()
                ->where('is_active', true)
                ->with('options')
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($field) => [
                    'id' => $field->id,
                    'field_name' => $field->field_name,
                    'display_label' => $field->display_label,
                    'field_type' => $field->field_type,
                    'is_required' => $field->is_required,
                    'help_text' => $field->help_text,
                    'form_section' => $field->form_section,
                    'sort_order' => $field->sort_order,
                    'options' => $field->options->map(fn ($opt) => [
                        'id' => $opt->id,
                        'value' => $opt->value,
                        'label' => $opt->label,
                    ])->values(),
                ])
                ->values();

            return response()->json($fields);
        } catch (\Throwable $e) {
            \Log::error('Error fetching agency form fields', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to load form fields'], 500);
        }
    }

    /**
     * Get single form field for an agency
     * GET /settings/agencies/{agency}/form-fields/{field}
     */
    public function getFormField(Request $request, Agency $agency, $fieldId): JsonResponse
    {
        try {
            $field = $agency->formFields()
                ->where('id', $fieldId)
                ->with('options')
                ->firstOrFail();

            return response()->json([
                'id' => $field->id,
                'field_name' => $field->field_name,
                'display_label' => $field->display_label,
                'field_type' => $field->field_type,
                'is_required' => $field->is_required,
                'help_text' => $field->help_text,
                'form_section' => $field->form_section,
                'sort_order' => $field->sort_order,
                'options' => $field->options->map(fn ($opt) => [
                    'id' => $opt->id,
                    'value' => $opt->value,
                    'label' => $opt->label,
                ])->values(),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Form field not found'], 404);
        } catch (\Throwable $e) {
            \Log::error('Error fetching form field', ['error' => $e->getMessage()]);
            return response()->json(['error'=>'Failed to load form field'], 500);
        }
    }

    /**
     * Add form field to agency
     * POST /settings/agencies/{agency}/form-fields
     */
    public function addFormField(Request $request, Agency $agency): JsonResponse
    {
        try {
            $validated = $request->validate([
                'field_name' => ['required', 'string', 'lowercase', 'regex:/^[a-z0-9_]+$/', 'max:255'],
                'display_label' => ['required', 'string', 'max:255'],
                'field_type' => ['required', 'in:text,number,decimal,date,datetime,dropdown,checkbox'],
                'is_required' => ['nullable', 'boolean'],
                'help_text' => ['nullable', 'string'],
                'form_section' => ['nullable', 'string'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
                'options' => ['nullable', 'array'],
                'options.*.label' => ['required_with:options', 'string', 'max:255'],
                'options.*.value' => ['nullable', 'string', 'max:255'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        }

        try {
            if ($this->isPersonalInformationCoreFieldName($validated['field_name'])) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => [
                        'field_name' => [
                            "Core personal-information field '{$validated['field_name']}' is schema-managed and cannot be added as a dynamic agency field.",
                        ],
                    ],
                ], 422);
            }

            $reservedSection = BeneficiaryCoreFields::reservedAgencyFormFieldSection($validated['field_name']);
            if ($reservedSection !== null) {
                $requestedSection = (string) ($validated['form_section'] ?? '');
                if ($requestedSection !== '' && $requestedSection !== $reservedSection) {
                    return response()->json([
                        'message' => 'Validation error',
                        'errors' => [
                            'form_section' => [
                                "Core field '{$validated['field_name']}' must stay under '{$reservedSection}'.",
                            ],
                        ],
                    ], 422);
                }

                $validated['form_section'] = $reservedSection;
            }

            // Check if field_name already exists for this agency
            if ($agency->formFields()->where('field_name', $validated['field_name'])->exists()) {
                return response()->json([
                    'error' => 'Field name already exists for this agency'
                ], 422);
            }

            if ($overlapError = $this->agencyGlobalOverlapError($validated['field_name'])) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => [
                        'field_name' => [$overlapError],
                    ],
                ], 422);
            }

            $field = DB::transaction(function () use ($validated, $agency) {
                $normalizedFieldType = $this->normalizeAgencyFieldType((string) $validated['field_type']);
                $resolvedFormSection = $this->resolveAgencyFormSection(
                    $agency,
                    (string) $validated['field_name'],
                    (string) ($validated['form_section'] ?? ''),
                );

                $field = $agency->formFields()->create([
                    'field_name' => $validated['field_name'],
                    'display_label' => $validated['display_label'],
                    'field_type' => $normalizedFieldType,
                    'is_required' => $validated['is_required'] ?? false,
                    'is_active' => true,
                    'help_text' => $validated['help_text'] ?? null,
                    'form_section' => $resolvedFormSection,
                    'sort_order' => $validated['sort_order'] ?? 0,
                ]);

                $this->syncAgencyFieldOptions($field, $validated['options'] ?? []);

                return $field;
            });

            $field->load('options');

            return response()->json([
                'success' => true,
                'field' => [
                    'id' => $field->id,
                    'field_name' => $field->field_name,
                    'display_label' => $field->display_label,
                    'field_type' => $field->field_type,
                    'is_required' => $field->is_required,
                    'help_text' => $field->help_text,
                    'form_section' => $field->form_section,
                    'sort_order' => $field->sort_order,
                    'options' => $field->options->map(fn ($option) => [
                        'id' => $option->id,
                        'label' => $option->label,
                        'value' => $option->value,
                        'sort_order' => $option->sort_order,
                    ])->values(),
                ],
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error adding form field', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to add form field'], 500);
        }
    }

    /**
     * Update agency form field
     * PUT /settings/agencies/{agency}/form-fields/{field}
     */
    public function updateAgencyFormField(Request $request, Agency $agency, $fieldId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'field_name' => ['sometimes', 'string', 'lowercase', 'regex:/^[a-z0-9_]+$/', 'max:255'],
                'display_label' => ['sometimes', 'string', 'max:255'],
                'field_type' => ['sometimes', 'in:text,number,decimal,date,datetime,dropdown,checkbox'],
                'is_required' => ['nullable', 'boolean'],
                'help_text' => ['nullable', 'string'],
                'form_section' => ['nullable', 'string'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
                'options' => ['nullable', 'array'],
                'options.*.label' => ['required_with:options', 'string', 'max:255'],
                'options.*.value' => ['nullable', 'string', 'max:255'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        }

        try {
            $field = $agency->formFields()->findOrFail($fieldId);
            $effectiveFieldName = (string) ($validated['field_name'] ?? $field->field_name);

            if ($this->isPersonalInformationCoreFieldName($effectiveFieldName)) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => [
                        'field_name' => [
                            "Core personal-information field '{$effectiveFieldName}' is schema-managed and cannot be edited as a dynamic agency field.",
                        ],
                    ],
                ], 422);
            }

            $reservedSection = BeneficiaryCoreFields::reservedAgencyFormFieldSection($effectiveFieldName);
            if ($reservedSection !== null && array_key_exists('form_section', $validated)) {
                $requestedSection = (string) ($validated['form_section'] ?? '');
                if ($requestedSection !== '' && $requestedSection !== $reservedSection) {
                    return response()->json([
                        'message' => 'Validation error',
                        'errors' => [
                            'form_section' => [
                                "Core field '{$effectiveFieldName}' must stay under '{$reservedSection}'.",
                            ],
                        ],
                    ], 422);
                }
            }

            if ($reservedSection !== null) {
                $validated['form_section'] = $reservedSection;
            }

            $validated['form_section'] = $this->resolveAgencyFormSection(
                $agency,
                $effectiveFieldName,
                (string) ($validated['form_section'] ?? $field->form_section ?? ''),
            );

            // Check if new field_name already exists (if changing)
            if (isset($validated['field_name']) && $validated['field_name'] !== $field->field_name) {
                if ($agency->formFields()->where('field_name', $validated['field_name'])->exists()) {
                    return response()->json([
                        'error' => 'Field name already exists for this agency'
                    ], 422);
                }

                if ($overlapError = $this->agencyGlobalOverlapError($validated['field_name'])) {
                    return response()->json([
                        'message' => 'Validation error',
                        'errors' => [
                            'field_name' => [$overlapError],
                        ],
                    ], 422);
                }
            }

            DB::transaction(function () use ($validated, $field) {
                if (array_key_exists('field_type', $validated)) {
                    $validated['field_type'] = $this->normalizeAgencyFieldType((string) $validated['field_type']);
                }

                $field->update($validated);

                if (array_key_exists('options', $validated) || in_array($field->field_type, ['dropdown', 'checkbox'], true)) {
                    $this->syncAgencyFieldOptions($field, $validated['options'] ?? []);
                }
            });

            $field->load('options');

            return response()->json([
                'success' => true,
                'field' => [
                    'id' => $field->id,
                    'field_name' => $field->field_name,
                    'display_label' => $field->display_label,
                    'field_type' => $field->field_type,
                    'is_required' => $field->is_required,
                    'help_text' => $field->help_text,
                    'form_section' => $field->form_section,
                    'sort_order' => $field->sort_order,
                    'options' => $field->options->map(fn ($option) => [
                        'id' => $option->id,
                        'label' => $option->label,
                        'value' => $option->value,
                        'sort_order' => $option->sort_order,
                    ])->values(),
                ],
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Form field not found'], 404);
        } catch (\Throwable $e) {
            \Log::error('Error updating form field', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to update form field'], 500);
        }
    }

    /**
     * Delete form field
     * DELETE /settings/agencies/{agency}/form-fields/{field}
     */
    public function deleteFormField(Request $request, Agency $agency, $fieldId): JsonResponse
    {
        try {
            $field = $agency->formFields()->findOrFail($fieldId);

            DB::transaction(function () use ($field) {
                // Delete associated options first
                $field->options()->delete();
                // Delete the field
                $field->delete();
            });

            return response()->json(['success' => true]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Form field not found'], 404);
        } catch (\Throwable $e) {
            \Log::error('Error deleting form field', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to delete form field'], 500);
        }
    }

    /**
     * Remove legacy reserved core-field duplicates from agency-specific dynamic fields.
     * DELETE /settings/agencies/{agency}/form-fields/cleanup-reserved
     */
    public function cleanupReservedAgencyFormFields(Request $request, Agency $agency): JsonResponse
    {
        try {
            $reservedFieldNames = BeneficiaryCoreFields::reservedAgencyFormFieldNames();

            $fields = $agency->formFields()
                ->whereIn('field_name', $reservedFieldNames)
                ->with('options:id,agency_form_field_id')
                ->get();

            if ($fields->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'deleted_fields' => 0,
                    'deleted_options' => 0,
                    'message' => 'No legacy reserved duplicate fields were found for this agency.',
                ]);
            }

            $deletedFields = 0;
            $deletedOptions = 0;

            DB::transaction(function () use ($fields, &$deletedFields, &$deletedOptions) {
                foreach ($fields as $field) {
                    $deletedOptions += $field->options->count();
                    $field->options()->delete();
                    $field->delete();
                    $deletedFields++;
                }
            });

            return response()->json([
                'success' => true,
                'deleted_fields' => $deletedFields,
                'deleted_options' => $deletedOptions,
                'message' => "Cleanup completed. Removed {$deletedFields} duplicate field(s).",
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error cleaning up reserved agency form fields', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Failed to clean up reserved fields'], 500);
        }
    }

    private function isReservedAgencyFormFieldName(string $fieldName): bool
    {
        return BeneficiaryCoreFields::isReservedAgencyFormFieldName($fieldName);
    }

    private function isPersonalInformationCoreFieldName(string $fieldName): bool
    {
        return BeneficiaryCoreFields::isPersonalInformationCoreFieldName($fieldName);
    }

    private function isAgencySpecificCoreFieldName(string $fieldName): bool
    {
        return BeneficiaryCoreFields::isAgencySpecificCoreFieldName($fieldName);
    }

    private function bootstrapAgencyCoreFields(Agency $agency): int
    {
        $templates = $this->agencyCoreFieldTemplatesFor($agency);
        if (empty($templates)) {
            return 0;
        }

        $existingFieldNames = $agency->formFields()
            ->pluck('field_name')
            ->map(fn ($name) => strtolower(trim((string) $name)))
            ->all();

        $existingLookup = array_fill_keys($existingFieldNames, true);

        $createdCount = 0;

        DB::transaction(function () use ($agency, $templates, $existingLookup, &$createdCount): void {
            foreach ($templates as $template) {
                $fieldName = strtolower(trim((string) ($template['field_name'] ?? '')));
                if ($fieldName === '' || isset($existingLookup[$fieldName])) {
                    continue;
                }

                $field = $agency->formFields()->create([
                    'field_name' => $fieldName,
                    'display_label' => (string) ($template['display_label'] ?? Str::title(str_replace('_', ' ', $fieldName))),
                    'field_type' => $this->normalizeAgencyFieldType((string) ($template['field_type'] ?? 'text')),
                    'is_required' => (bool) ($template['is_required'] ?? false),
                    'is_active' => true,
                    'help_text' => $template['help_text'] ?? null,
                    'form_section' => (string) ($template['form_section'] ?? 'general_information'),
                    'sort_order' => (int) ($template['sort_order'] ?? 0),
                ]);

                $createdCount++;

                $optionGroup = (string) ($template['option_group'] ?? '');
                if ($optionGroup !== '' && in_array($field->field_type, ['dropdown', 'checkbox'], true)) {
                    $globalOptions = FormFieldOption::query()
                        ->where('field_group', $optionGroup)
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('label')
                        ->get(['label', 'value']);

                    $sortOrder = 10;
                    foreach ($globalOptions as $option) {
                        $label = trim((string) $option->label);
                        $value = trim((string) $option->value);
                        if ($label === '' && $value === '') {
                            continue;
                        }

                        $field->options()->create([
                            'label' => $label !== '' ? $label : $value,
                            'value' => $value !== '' ? $value : $label,
                            'sort_order' => $sortOrder,
                        ]);

                        $sortOrder += 10;
                    }
                }
            }
        });

        return $createdCount;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function agencyCoreFieldTemplatesFor(Agency $agency): array
    {
        $agencyName = strtoupper(trim((string) $agency->name));

        if ($agencyName === 'DA') {
            return [
                ['field_name' => 'rsbsa_number', 'display_label' => 'RSBSA Number', 'field_type' => 'text', 'form_section' => 'farmer_information', 'sort_order' => 10],
            ];
        }

        if ($agencyName === 'BFAR') {
            return [
                ['field_name' => 'fishr_number', 'display_label' => 'FishR Number', 'field_type' => 'text', 'form_section' => 'fisherfolk_information', 'sort_order' => 10],
            ];
        }

        if ($agencyName === 'DAR') {
            return [
                ['field_name' => 'cloa_ep_number', 'display_label' => 'CLOA/EP Number', 'field_type' => 'text', 'form_section' => 'dar_information', 'sort_order' => 10],
                ['field_name' => 'arb_classification', 'display_label' => 'ARB Classification', 'field_type' => 'dropdown', 'form_section' => 'dar_information', 'sort_order' => 20, 'option_group' => 'arb_classification'],
                ['field_name' => 'landholding_description', 'display_label' => 'Landholding Description', 'field_type' => 'text', 'form_section' => 'dar_information', 'sort_order' => 30],
                ['field_name' => 'land_area_awarded_hectares', 'display_label' => 'Land Area Awarded (Hectares)', 'field_type' => 'decimal', 'form_section' => 'dar_information', 'sort_order' => 40],
                ['field_name' => 'ownership_scheme', 'display_label' => 'Ownership Scheme', 'field_type' => 'dropdown', 'form_section' => 'dar_information', 'sort_order' => 50, 'option_group' => 'ownership_scheme'],
                ['field_name' => 'barc_membership_status', 'display_label' => 'BARC Membership Status', 'field_type' => 'text', 'form_section' => 'dar_information', 'sort_order' => 60],
            ];
        }

        return [];
    }

    /**
     * @return array<int, string>
     */
    private function classificationCoreFieldNames(): array
    {
        return collect($this->classificationCoreFieldDefinitions())
            ->pluck('field_name')
            ->map(fn ($fieldName) => strtolower(trim((string) $fieldName)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function cleanupClassificationAgencyFieldOverlap(Agency $agency): void
    {
        $classificationFieldNames = $this->classificationCoreFieldNames();
        if (empty($classificationFieldNames)) {
            return;
        }

        DB::transaction(function () use ($agency, $classificationFieldNames): void {
            $overlapFields = $agency->formFields()
                ->whereIn('field_name', $classificationFieldNames)
                ->with('options:id,agency_form_field_id')
                ->get();

            foreach ($overlapFields as $field) {
                $field->options()->delete();
                $field->delete();
            }
        });
    }

    private function normalizeAgencyFieldType(string $fieldType): string
    {
        $normalized = strtolower(trim($fieldType));

        $allowed = ['text', 'number', 'decimal', 'date', 'datetime', 'dropdown', 'checkbox'];

        if (! in_array($normalized, $allowed, true)) {
            return 'text';
        }

        return $normalized;
    }

    private function resolveAgencyFormSection(Agency $agency, string $fieldName, string $requestedSection = ''): string
    {
        $normalizedFieldName = strtolower(trim($fieldName));
        $reservedSection = BeneficiaryCoreFields::reservedAgencyFormFieldSection($normalizedFieldName);
        if ($reservedSection !== null) {
            return $reservedSection;
        }

        if (strtoupper(trim((string) $agency->name)) === 'DAR') {
            return 'dar_information';
        }

        $classificationNames = $agency->classifications()
            ->pluck('name')
            ->map(fn ($name) => strtolower(trim((string) $name)))
            ->values()
            ->all();

        $hasFarmer = in_array('farmer', $classificationNames, true);
        $hasFisherfolk = in_array('fisherfolk', $classificationNames, true);

        if ($hasFarmer && ! $hasFisherfolk) {
            return 'farmer_information';
        }

        if ($hasFisherfolk && ! $hasFarmer) {
            return 'fisherfolk_information';
        }

        $normalizedRequested = strtolower(trim($requestedSection));
        if (in_array($normalizedRequested, ['general_information', 'additional_information', 'farmer_information', 'fisherfolk_information', 'dar_information'], true)) {
            return $normalizedRequested;
        }

        return 'general_information';
    }

    /**
     * @param array<int, array{label?: string, value?: string}> $rawOptions
     */
    private function syncAgencyFieldOptions(AgencyFormField $field, array $rawOptions): void
    {
        if (! in_array($field->field_type, ['dropdown', 'checkbox'], true)) {
            $field->options()->delete();

            return;
        }

        $normalized = collect($rawOptions)
            ->map(function ($item) {
                $label = trim((string) ($item['label'] ?? ''));
                $value = trim((string) ($item['value'] ?? ''));

                if ($label === '' && $value === '') {
                    return null;
                }

                if ($label === '') {
                    $label = $value;
                }

                if ($value === '') {
                    $value = Str::of($label)
                        ->lower()
                        ->replaceMatches('/[^a-z0-9]+/', '_')
                        ->trim('_')
                        ->toString();
                }

                return [
                    'label' => $label,
                    'value' => $value,
                ];
            })
            ->filter()
            ->unique(fn ($item) => ($item['value'] ?? '') . '|' . ($item['label'] ?? ''))
            ->values();

        $field->options()->delete();

        $sortOrder = 10;
        foreach ($normalized as $option) {
            $field->options()->create([
                'label' => $option['label'],
                'value' => $option['value'],
                'sort_order' => $sortOrder,
            ]);

            $sortOrder += 10;
        }
    }

    /**
     * @return array<int, array{field_name: string, label: string, classification: string, placement_section: string, default_required: bool}>
     */
    private function classificationCoreFieldDefinitions(): array
    {
        return [
            // Farmer (DA)
            ['field_name' => 'farm_ownership', 'label' => 'Farm Ownership', 'classification' => 'Farmer', 'placement_section' => 'farmer_information', 'default_required' => true],
            ['field_name' => 'farm_size_hectares', 'label' => 'Farm Size (Hectares)', 'classification' => 'Farmer', 'placement_section' => 'farmer_information', 'default_required' => true],
            ['field_name' => 'primary_commodity', 'label' => 'Primary Commodity', 'classification' => 'Farmer', 'placement_section' => 'farmer_information', 'default_required' => true],
            ['field_name' => 'farm_type', 'label' => 'Farm Type', 'classification' => 'Farmer', 'placement_section' => 'farmer_information', 'default_required' => true],
            ['field_name' => 'organization_membership', 'label' => 'Organization Membership', 'classification' => 'Farmer', 'placement_section' => 'farmer_information', 'default_required' => false],

            // Fisherfolk
            ['field_name' => 'fisherfolk_type', 'label' => 'Fisherfolk Type', 'classification' => 'Fisherfolk', 'placement_section' => 'fisherfolk_information', 'default_required' => true],
            ['field_name' => 'main_fishing_gear', 'label' => 'Main Fishing Gear', 'classification' => 'Fisherfolk', 'placement_section' => 'fisherfolk_information', 'default_required' => false],
            ['field_name' => 'has_fishing_vessel', 'label' => 'Has Fishing Vessel', 'classification' => 'Fisherfolk', 'placement_section' => 'fisherfolk_information', 'default_required' => false],
            ['field_name' => 'fishing_vessel_type', 'label' => 'Fishing Vessel Type', 'classification' => 'Fisherfolk', 'placement_section' => 'fisherfolk_information', 'default_required' => false],
            ['field_name' => 'fishing_vessel_tonnage', 'label' => 'Fishing Vessel Tonnage', 'classification' => 'Fisherfolk', 'placement_section' => 'fisherfolk_information', 'default_required' => false],
            ['field_name' => 'length_of_residency_months', 'label' => 'Length of Residency (Months)', 'classification' => 'Fisherfolk', 'placement_section' => 'fisherfolk_information', 'default_required' => true],
        ];
    }

    private function classificationCoreFieldRequiredStatus(string $fieldName, bool $default): bool
    {
        $row = FormFieldOption::query()
            ->where('field_group', $fieldName)
            ->orderByDesc('id')
            ->first(['is_required']);

        if (! $row) {
            return $default;
        }

        return (bool) $row->is_required;
    }

    private function classificationCoreFieldLabel(string $fieldName, string $default): string
    {
        $configRow = FormFieldOption::query()
            ->where('field_group', $fieldName)
            ->where('value', $fieldName)
            ->orderByDesc('id')
            ->first(['label']);

        if (! $configRow || trim((string) $configRow->label) === '') {
            return $default;
        }

        return trim((string) $configRow->label);
    }

    /**
     * @param array{field_name: string, label: string, classification: string, placement_section: string, default_required: bool} $definition
     */
    private function findOrCreateClassificationCoreConfigRow(string $fieldName, array $definition): FormFieldOption
    {
        $configRow = FormFieldOption::query()
            ->where('field_group', $fieldName)
            ->where('value', $fieldName)
            ->orderByDesc('id')
            ->first();

        if ($configRow) {
            return $configRow;
        }

        return FormFieldOption::query()->create([
            'field_group' => $fieldName,
            'field_type' => FormFieldOption::FIELD_TYPE_TEXT,
            'placement_section' => (string) ($definition['placement_section'] ?? FormFieldOption::PLACEMENT_PERSONAL_INFORMATION),
            'label' => (string) ($definition['label'] ?? Str::title(str_replace('_', ' ', $fieldName))),
            'value' => $fieldName,
            'sort_order' => 0,
            'is_required' => (bool) ($definition['default_required'] ?? false),
            'is_active' => true,
        ]);
    }
}

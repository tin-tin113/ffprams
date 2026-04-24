<?php

namespace App\Http\Controllers;

use App\Http\Requests\BeneficiaryRequest;
use App\Models\Agency;
use App\Models\AgencyFormField;
use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Models\FormFieldOption;
use App\Services\AuditLogService;
use App\Services\DuplicateDetectionService;
use App\Services\SemaphoreService;
use App\Support\BeneficiaryCoreFields;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class BeneficiaryController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
        private SemaphoreService $sms,
        private DuplicateDetectionService $duplicateService,
    ) {}

    /**
     * Paginated list with filters and search.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $documentFilter = (string) $request->input('documents', '');

        if (! in_array($documentFilter, ['with', 'without'], true)) {
            $documentFilter = '';
        }

        $allowedPerPage = [25, 50, 100];

        $perPage = (int) $request->input('per_page', 25);
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 25;
        }

        $beneficiaries = Beneficiary::query()
            ->with([
                'barangay:id,name',
                'agency:id,name',
            ])
            ->withCount('attachments')
            ->when($request->filled('barangay_id'), fn ($q) => $q->where('barangay_id', (int) $request->barangay_id))
            ->when($request->filled('agency_id'), fn ($q) => $q->where('agency_id', (int) $request->agency_id))
            ->when($request->filled('classification'), fn ($q) => $q->where('classification', $request->classification))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($documentFilter === 'with', fn ($q) => $q->has('attachments'))
            ->when($documentFilter === 'without', fn ($q) => $q->doesntHave('attachments'))
            ->when($search !== '', function ($q) use ($search) {
                $like = "%{$search}%";

                $q->where(function ($sub) use ($like) {
                    $sub->where('full_name', 'like', $like)
                        ->orWhere('first_name', 'like', $like)
                        ->orWhere('middle_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('contact_number', 'like', $like)
                        ->orWhere('rsbsa_number', 'like', $like)
                        ->orWhere('fishr_number', 'like', $like)
                        ->orWhereHas('agencies', function ($aq) use ($like) {
                            $aq->where('beneficiary_agencies.identifier', 'like', $like);
                        });
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        $barangays = Barangay::orderBy('name')->get();
        $agencies = Agency::active()->orderBy('name')->get();

        $summary = [
            'total_all' => Beneficiary::count(),
            'total_active' => Beneficiary::where('status', 'Active')->count(),
            'with_documents' => Beneficiary::has('attachments')->count(),
            'without_documents' => Beneficiary::doesntHave('attachments')->count(),
        ];

        $activeFilterCount = collect([
            $search,
            $request->input('barangay_id'),
            $request->input('agency_id'),
            $request->input('classification'),
            $request->input('status'),
            $documentFilter,
        ])->filter(fn ($value) => $value !== null && $value !== '')->count();

        return view('beneficiaries.index', compact(
            'beneficiaries',
            'barangays',
            'agencies',
            'summary',
            'activeFilterCount',
            'perPage',
            'documentFilter',
        ));
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        $barangays = Barangay::orderBy('name')->get();
        $agencies = Agency::active()->orderBy('name')->get();
        $fieldOptions = $this->getFormFieldOptions();
        $fieldGroupSettings = $this->getFieldGroupSettings();

        return view('beneficiaries.create', compact('barangays', 'agencies', 'fieldOptions', 'fieldGroupSettings'));
    }

    /**
     * Store a new beneficiary.
     */
    public function store(BeneficiaryRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        // Check for potential duplicates before creating - BLOCK if found
        $duplicates = $this->duplicateService->findPotentialDuplicates($validated);

        if ($duplicates->isNotEmpty()) {
            // Get the highest-scoring match
            $bestMatch = $duplicates->sortByDesc('score')->first();
            $existing = $bestMatch['beneficiary'];

            $message = 'Registration blocked: A potential duplicate record already exists. ';
            $message .= "Existing beneficiary: {$existing->full_name}";
            if ($existing->barangay) {
                $message .= " (Barangay {$existing->barangay->name})";
            }
            $message .= ". Match type: {$bestMatch['match_type']}, Score: {$bestMatch['score']}%.";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'duplicate' => true,
                    'message' => $message.' Please verify and update this existing record if needed.',
                    'existing_beneficiary_id' => $existing->id,
                    'redirect_url' => route('beneficiaries.show', $existing),
                ], 409);
            }

            return redirect()->route('beneficiaries.show', $existing)
                ->with('warning', $message.' Please verify and update this existing record if needed.');
        }

        try {
            // No duplicates - proceed with registration
            $beneficiary = DB::transaction(function () use ($validated, $request) {
                [$agencyIds, $agencyFieldsData] = $this->extractAgencyData($request->input('agencies', $validated['agencies'] ?? []));
                $validated['agency_id'] = $agencyIds[0] ?? null;
                $beneficiaryFillable = (new Beneficiary())->getFillable();

                unset($validated['agencies']);
                unset($validated['rsbsa_availability_status'], $validated['fishr_availability_status'], $validated['cloa_ep_availability_status']);

                // Store unavailability reasons from dynamic fields
                $customFieldReasons = (array) ($validated['custom_field_unavailability_reasons'] ?? []);
                $agencyDynamicReasons = [];
                foreach ($agencyFieldsData as $agencyId => $fieldData) {
                    if (! is_array($fieldData)) {
                        continue;
                    }

                    foreach ($fieldData as $key => $value) {
                        if (strpos($key, '_unavailability_reason') !== false) {
                            $fieldName = str_replace('_unavailability_reason', '', $key);

                            $reasonColumn = BeneficiaryCoreFields::unavailabilityReasonColumnFor($fieldName);
                            if ($reasonColumn !== null) {
                                $validated[$reasonColumn] = $value;
                            } elseif ($value !== null && $value !== '') {
                                $agencyDynamicReasons[(string) $agencyId][$fieldName] = $value;
                            }

                            unset($agencyFieldsData[$agencyId][$key]);
                        }
                    }
                }

                if (! empty($agencyDynamicReasons)) {
                    $customFieldReasons['agency_dynamic'] = $agencyDynamicReasons;
                }

                if (! empty($customFieldReasons)) {
                    $validated['custom_field_unavailability_reasons'] = $customFieldReasons;
                }

                // Store dynamic field values directly in beneficiary record
                $customFields = (array) ($validated['custom_fields'] ?? []);
                $agencyDynamicValues = [];
                foreach ($agencyFieldsData as $agencyId => $fieldData) {
                    if (! is_array($fieldData)) {
                        continue;
                    }

                    foreach ($fieldData as $key => $value) {
                        if ($this->isAgencyMetaFieldKey((string) $key)) {
                            continue;
                        }

                        if (in_array($key, $beneficiaryFillable, true)) {
                            $validated[$key] = $value;
                        } else {
                            $agencyDynamicValues[(string) $agencyId][$key] = $value;
                        }
                    }
                }

                if (! empty($agencyDynamicValues)) {
                    $customFields['agency_dynamic'] = $agencyDynamicValues;
                }

                if (! empty($customFields)) {
                    $validated['custom_fields'] = $customFields;
                }

                $beneficiary = Beneficiary::create(array_merge($validated, ['status' => 'Active']));

                // Sync all selected agencies to pivot table with their identifiers
                $agencies = Agency::whereIn('id', $agencyIds)->get();
                foreach ($agencies as $agency) {
                    $agencyName = strtoupper($agency->name);
                    $identifier = null;

                    // Extract the correct identifier for this agency from beneficiary data
                    if ($agencyName === 'DA') {
                        $identifier = $beneficiary->rsbsa_number ?? null;
                    } elseif ($agencyName === 'BFAR') {
                        $identifier = $beneficiary->fishr_number ?? null;
                    } elseif ($agencyName === 'DAR') {
                        $identifier = $this->extractDarIdentifierFromAgencyPayload(
                            (int) $agency->id,
                            $agencyFieldsData,
                            $beneficiary,
                        );
                    }

                    // Attach agency with identifier and registration date
                    $beneficiary->agencies()->attach($agency->id, [
                        'identifier' => $identifier,
                        'registered_at' => now()->toDateString(),
                    ]);
                }

                $this->audit->log(
                    (int) Auth::id(),
                    'created',
                    'beneficiaries',
                    $beneficiary->id,
                    [],
                    $beneficiary->toArray(),
                );

                return $beneficiary;
            });
        } catch (QueryException $e) {
            $duplicateError = $this->resolveBeneficiaryUniqueConstraintError($e);

            if ($duplicateError !== null) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'The given data was invalid.',
                        'errors' => [
                            $duplicateError['field'] => [$duplicateError['message']],
                        ],
                    ], 422);
                }

                return back()->withInput()->withErrors([
                    $duplicateError['field'] => $duplicateError['message'],
                ]);
            }

            throw $e;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Beneficiary registered successfully.',
                'beneficiary_id' => $beneficiary->id,
                'redirect_url' => route('beneficiaries.show', $beneficiary),
            ]);
        }

        return redirect()->route('beneficiaries.index')
            ->with('success', 'Beneficiary registered successfully.');
    }

    /**
     * Show full beneficiary profile.
     */
    public function show(Beneficiary $beneficiary): View
    {
        $beneficiary->load([
            'barangay',
            'agency',
            'agencies',
            'attachments' => fn ($q) => $q->latest('id')->with('uploader:id,name'),
            'allocations.distributionEvent.resourceType.agency',
            'allocations.programName.agency',
            'allocations.resourceType.agency',
            'allocations.assistancePurpose',
            'directAssistance.programName.agency',
            'directAssistance.resourceType.agency',
            'directAssistance.assistancePurpose',
            'directAssistance.distributionEvent.barangay',
            'directAssistance.createdBy',
            'directAssistance.distributedBy',
            'smsLogs' => fn ($q) => $q->latest('sent_at')->limit(5),
        ]);

        $agencyFieldLabels = AgencyFormField::query()
            ->whereIn('agency_id', $beneficiary->agencies->pluck('id')->all())
            ->get(['agency_id', 'field_name', 'display_label'])
            ->groupBy('agency_id')
            ->map(function ($fields): array {
                return $fields
                    ->mapWithKeys(fn (AgencyFormField $field): array => [
                        (string) $field->field_name => (string) $field->display_label,
                    ])
                    ->toArray();
            })
            ->toArray();

        return view('beneficiaries.show', compact('beneficiary', 'agencyFieldLabels'));
    }

    /**
     * Show the edit form.
     */
    public function edit(Beneficiary $beneficiary): View
    {
        $barangays = Barangay::orderBy('name')->get();
        $agencies = Agency::active()->orderBy('name')->get();
        $fieldOptions = $this->getFormFieldOptions();
        $fieldGroupSettings = $this->getFieldGroupSettings();

        return view('beneficiaries.edit', compact('beneficiary', 'barangays', 'agencies', 'fieldOptions', 'fieldGroupSettings'));
    }

    /**
     * Update an existing beneficiary.
     */
    public function update(BeneficiaryRequest $request, Beneficiary $beneficiary): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        // Prevent duplicate creation during profile updates as well.
        $duplicates = $this->duplicateService->findPotentialDuplicates($validated, $beneficiary->id);

        if ($duplicates->isNotEmpty()) {
            $bestMatch = $duplicates->sortByDesc('score')->first();
            $existing = $bestMatch['beneficiary'];

            $message = 'Update blocked: This change would match an existing beneficiary record. ';
            $message .= "Existing beneficiary: {$existing->full_name}";
            if ($existing->barangay) {
                $message .= " (Barangay {$existing->barangay->name})";
            }
            $message .= ". Match type: {$bestMatch['match_type']}, Score: {$bestMatch['score']}%.";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'duplicate' => true,
                    'message' => $message.' Please review the existing record before updating.',
                    'existing_beneficiary_id' => $existing->id,
                    'redirect_url' => route('beneficiaries.show', $existing),
                ], 409);
            }

            return redirect()->route('beneficiaries.show', $existing)
                ->with('warning', $message.' Please review the existing record before updating.');
        }

        try {
            DB::transaction(function () use ($beneficiary, $validated, $request) {
                $oldValues = $beneficiary->toArray();

            [$agencyIds, $agencyFieldsData] = $this->extractAgencyData($request->input('agencies', $validated['agencies'] ?? []));
            $validated['agency_id'] = $agencyIds[0] ?? null;
            $beneficiaryFillable = (new Beneficiary())->getFillable();

            unset($validated['agencies']);
            unset($validated['rsbsa_availability_status'], $validated['fishr_availability_status'], $validated['cloa_ep_availability_status']);

            // Store unavailability reasons from dynamic fields
            $customFieldReasons = (array) ($validated['custom_field_unavailability_reasons'] ?? []);
            $agencyDynamicReasons = [];
            foreach ($agencyFieldsData as $agencyId => $fieldData) {
                if (! is_array($fieldData)) {
                    continue;
                }

                foreach ($fieldData as $key => $value) {
                    if (strpos($key, '_unavailability_reason') !== false) {
                        $fieldName = str_replace('_unavailability_reason', '', $key);

                        $reasonColumn = BeneficiaryCoreFields::unavailabilityReasonColumnFor($fieldName);
                        if ($reasonColumn !== null) {
                            $validated[$reasonColumn] = $value;
                        } elseif ($value !== null && $value !== '') {
                            $agencyDynamicReasons[(string) $agencyId][$fieldName] = $value;
                        }

                        unset($agencyFieldsData[$agencyId][$key]);
                    }
                }
            }

            if (! empty($agencyDynamicReasons)) {
                $customFieldReasons['agency_dynamic'] = $agencyDynamicReasons;
            } else {
                unset($customFieldReasons['agency_dynamic']);
            }

            $validated['custom_field_unavailability_reasons'] = $customFieldReasons;

            // Store dynamic field values directly in beneficiary record
            $customFields = (array) ($validated['custom_fields'] ?? []);
            $agencyDynamicValues = [];
            foreach ($agencyFieldsData as $agencyId => $fieldData) {
                if (! is_array($fieldData)) {
                    continue;
                }

                foreach ($fieldData as $key => $value) {
                    if ($this->isAgencyMetaFieldKey((string) $key)) {
                        continue;
                    }

                    if (in_array($key, $beneficiaryFillable, true)) {
                        $validated[$key] = $value;
                    } else {
                        $agencyDynamicValues[(string) $agencyId][$key] = $value;
                    }
                }
            }

            if (! empty($agencyDynamicValues)) {
                $customFields['agency_dynamic'] = $agencyDynamicValues;
            } else {
                unset($customFields['agency_dynamic']);
            }

            $validated['custom_fields'] = $customFields;

            $beneficiary->update($validated);

            // Sync all selected agencies to pivot table with their identifiers
            $agencies = Agency::whereIn('id', $agencyIds)->get();
            $agencyPivotData = [];

            foreach ($agencies as $agency) {
                $agencyName = strtoupper($agency->name);
                $identifier = null;

                // Extract the correct identifier for this agency
                if ($agencyName === 'DA') {
                    $identifier = $beneficiary->rsbsa_number ?? null;
                } elseif ($agencyName === 'BFAR') {
                    $identifier = $beneficiary->fishr_number ?? null;
                } elseif ($agencyName === 'DAR') {
                    $identifier = $this->extractDarIdentifierFromAgencyPayload(
                        (int) $agency->id,
                        $agencyFieldsData,
                        $beneficiary,
                    );
                }

                // Get existing registration date if agency was already registered, otherwise use today
                $existingPivot = $beneficiary->agencies()
                    ->where('agency_id', $agency->id)
                    ->first();
                $registeredAt = $existingPivot?->pivot->registered_at ?? now()->toDateString();

                $agencyPivotData[$agency->id] = [
                    'identifier' => $identifier,
                    'registered_at' => $registeredAt,
                ];
            }

            // Sync pivot table (adds new agencies, removes old ones)
            $beneficiary->agencies()->sync($agencyPivotData);

                $this->audit->log(
                    (int) Auth::id(),
                    'updated',
                    'beneficiaries',
                    $beneficiary->id,
                    $oldValues,
                    $beneficiary->fresh()->toArray(),
                );
            });
        } catch (QueryException $e) {
            $duplicateError = $this->resolveBeneficiaryUniqueConstraintError($e);

            if ($duplicateError !== null) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'The given data was invalid.',
                        'errors' => [
                            $duplicateError['field'] => [$duplicateError['message']],
                        ],
                    ], 422);
                }

                return back()->withInput()->withErrors([
                    $duplicateError['field'] => $duplicateError['message'],
                ]);
            }

            throw $e;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Beneficiary updated successfully.',
                'beneficiary_id' => $beneficiary->id,
                'redirect_url' => route('beneficiaries.show', $beneficiary),
            ]);
        }

        return redirect()->route('beneficiaries.index')
            ->with('success', 'Beneficiary updated successfully.');
    }

    /**
     * Normalize agency payload to support both flat IDs and nested dynamic-field structure.
     *
     * @return array{0: array<int>, 1: array<int, array<string, mixed>>}
     */
    private function extractAgencyData(mixed $agenciesInput): array
    {
        if (! is_array($agenciesInput)) {
            return [[], []];
        }

        $agencyIds = [];
        $agencyFieldsData = [];

        foreach ($agenciesInput as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $agencyId = (int) $key;
                    if ($agencyId > 0) {
                        $agencyIds[] = $agencyId;
                        $agencyFieldsData[$agencyId] = $value;
                    }
                }

                continue;
            }

            if (is_numeric($key)) {
                $agencyId = (int) $key;
                if ($agencyId > 0) {
                    $agencyIds[] = $agencyId;
                }

                if (is_numeric($value)) {
                    $valueAgencyId = (int) $value;
                    if ($valueAgencyId > 0 && $valueAgencyId !== $agencyId) {
                        $agencyIds[] = $valueAgencyId;
                    }
                }

                continue;
            }

            if (is_numeric($value)) {
                $agencyId = (int) $value;
                if ($agencyId > 0) {
                    $agencyIds[] = $agencyId;
                }
            }
        }

        $agencyIds = array_values(array_unique($agencyIds));

        return [$agencyIds, $agencyFieldsData];
    }

    /**
     * Resolve DAR identifier from dynamic agency payload first, then fallback to existing pivot value.
     *
     * @param  array<int, array<string, mixed>>  $agencyFieldsData
     */
    private function extractDarIdentifierFromAgencyPayload(int $agencyId, array $agencyFieldsData, Beneficiary $beneficiary): ?string
    {
        $fieldData = $agencyFieldsData[$agencyId] ?? null;

        if (is_array($fieldData)) {
            $identifier = $fieldData['cloa_ep_number'] ?? null;
            if (is_string($identifier) && trim($identifier) !== '') {
                return trim($identifier);
            }
        }

        $existingDarPivot = $beneficiary->agencies()->where('agency_id', $agencyId)->first();
        $existingIdentifier = $existingDarPivot?->pivot?->identifier;

        if (is_string($existingIdentifier) && trim($existingIdentifier) !== '') {
            return trim($existingIdentifier);
        }

        return null;
    }

    private function isAgencyMetaFieldKey(string $key): bool
    {
        return $key === 'has_value'
            || $key === 'availability_status'
            || str_ends_with($key, '_has_value')
            || str_ends_with($key, '_availability_status')
            || str_ends_with($key, '_unavailability_reason');
    }

    /**
     * @return array{field: string, message: string}|null
     */
    private function resolveBeneficiaryUniqueConstraintError(QueryException $e): ?array
    {
        $message = $e->getMessage();
        if (! str_contains($message, '1062')) {
            return null;
        }

        $indexToField = [
            'beneficiaries_rsbsa_number_unique' => [
                'field' => 'rsbsa_number',
                'message' => 'A beneficiary with this RSBSA number already exists.',
            ],
            'beneficiaries_fishr_number_unique' => [
                'field' => 'fishr_number',
                'message' => 'A beneficiary with this FishR number already exists.',
            ],
        ];

        foreach ($indexToField as $indexName => $error) {
            if (str_contains($message, $indexName)) {
                return $error;
            }
        }

        return null;
    }

    /**
     * Soft delete a beneficiary.
     */
    public function destroy(Beneficiary $beneficiary): RedirectResponse
    {
        if (Auth::user()?->role !== 'admin') {
            abort(403, 'Only admins can delete beneficiaries.');
        }

        $beneficiary->delete();

        $this->audit->log(
            (int) Auth::id(),
            'deleted',
            'beneficiaries',
            $beneficiary->id,
            $beneficiary->toArray(),
        );

        return redirect()->route('beneficiaries.index')
            ->with('success', 'Beneficiary deleted successfully.');
    }

    /**
     * Send a custom SMS to a beneficiary.
     */
    public function sendSms(Request $request, Beneficiary $beneficiary): RedirectResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:300'],
        ]);

        if (empty($beneficiary->contact_number)) {
            return redirect()->back()
                ->with('error', 'This beneficiary has no contact number on file.');
        }

        $sent = $this->sms->sendSms(
            $beneficiary->contact_number,
            $request->message,
            $beneficiary->id,
        );

        return redirect()->route('beneficiaries.show', $beneficiary)
            ->with($sent ? 'success' : 'error', $sent ? 'SMS sent successfully.' : 'Failed to send SMS. Please try again.');
    }

    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'selected_ids' => ['required', 'array', 'min:1'],
            'selected_ids.*' => ['integer', 'distinct', 'exists:beneficiaries,id'],
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        $selectedIds = collect($validated['selected_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $beneficiaries = Beneficiary::whereIn('id', $selectedIds)->get();

        if ($beneficiaries->isEmpty()) {
            return redirect()->back()->with('warning', 'No valid beneficiaries selected.');
        }

        $updatedCount = 0;

        DB::transaction(function () use ($beneficiaries, $validated, &$updatedCount): void {
            foreach ($beneficiaries as $beneficiary) {
                if ($beneficiary->status === $validated['status']) {
                    continue;
                }

                $oldValues = $beneficiary->toArray();

                $beneficiary->update([
                    'status' => $validated['status'],
                ]);

                $this->audit->log(
                    (int) Auth::id(),
                    'updated',
                    'beneficiaries',
                    $beneficiary->id,
                    $oldValues,
                    $beneficiary->fresh()->toArray(),
                );

                $updatedCount++;
            }
        });

        if ($updatedCount === 0) {
            return redirect()->back()->with('warning', 'Selected beneficiaries already have that status.');
        }

        return redirect()->back()->with('success', "Updated {$updatedCount} beneficiary status record(s) to {$validated['status']}.");
    }

    public function summary(Beneficiary $beneficiary): JsonResponse
    {
        $beneficiary->load('barangay');

        return response()->json([
            'id' => $beneficiary->id,
            'first_name' => $beneficiary->first_name,
            'middle_name' => $beneficiary->middle_name,
            'last_name' => $beneficiary->last_name,
            'name_suffix' => $beneficiary->name_suffix,
            'full_name' => $beneficiary->full_name,
            'barangay_name' => $beneficiary->barangay->name ?? null,
            'classification' => $beneficiary->classification,
            'contact_number' => $beneficiary->contact_number,
            'rsbsa_number' => $beneficiary->isFarmer() ? $beneficiary->rsbsa_number : null,
            'fishr_number' => $beneficiary->isFisherfolk() ? $beneficiary->fishr_number : null,
        ]);
    }

    private function getFormFieldOptions(): array
    {
        $coreFields = [
            'farm_type',
            'farm_ownership',
            'fisherfolk_type',
            'civil_status',
            'highest_education',
            'id_type',
        ];

        $allGroups = FormFieldOption::query()
            ->active()
            ->orderBy('field_group')
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get()
            ->groupBy('field_group');

        $options = $allGroups
            ->map(fn ($groupOptions) => $groupOptions->values())
            ->toArray();

        $agencyCoreOptions = AgencyFormField::query()
            ->whereIn('field_name', $coreFields)
            ->where('is_active', true)
            ->whereIn('field_type', ['dropdown', 'checkbox'])
            ->with(['options' => fn ($q) => $q->orderBy('sort_order')])
            ->get(['id', 'field_name'])
            ->groupBy('field_name');

        foreach ($coreFields as $field) {
            if (! isset($options[$field]) || empty($options[$field])) {
                $options[$field] = collect();
            }

            $existing = collect($options[$field])
                ->map(function ($item) {
                    $label = trim((string) (is_object($item) ? ($item->label ?? '') : ($item['label'] ?? '')));

                    return strtolower($label);
                })
                ->filter()
                ->all();

            $extra = collect($agencyCoreOptions->get($field, collect()))
                ->flatMap(function (AgencyFormField $fieldDef) {
                    return $fieldDef->options->map(fn ($opt) => (object) [
                        'value' => (string) $opt->value,
                        'label' => (string) $opt->label,
                    ]);
                })
                ->filter(function ($option) use ($existing) {
                    $label = strtolower(trim((string) ($option->label ?? '')));

                    return $label !== '' && ! in_array($label, $existing, true);
                })
                ->values();

            if ($extra->isNotEmpty()) {
                $options[$field] = collect($options[$field])->merge($extra)->values();
            }
        }

        foreach ($coreFields as $field) {
            if (! array_key_exists($field, $options) || empty($options[$field])) {
                Log::warning("FormFieldOption group is missing: {$field}. Please ensure this field group is configured in System Settings > Form Fields.");

                $options[$field] = collect();
            }
        }

        return $options;
    }

    private function getFieldGroupSettings(): array
    {
        $selectColumns = ['field_group', 'placement_section', 'is_required'];
        if (Schema::hasColumn('form_field_options', 'field_type')) {
            $selectColumns[] = 'field_type';
        }

        return FormFieldOption::query()
            ->active()
            ->orderBy('field_group')
            ->orderByDesc('id')
            ->get($selectColumns)
            ->groupBy('field_group')
            ->map(function ($groupRows) {
                $first = $groupRows->first();

                return [
                    'placement_section' => $first?->placement_section ?? FormFieldOption::PLACEMENT_PERSONAL_INFORMATION,
                    'is_required' => (bool) ($first?->is_required ?? false),
                    'field_type' => $first?->field_type ?? FormFieldOption::FIELD_TYPE_DROPDOWN,
                ];
            })
            ->toArray();
    }
}

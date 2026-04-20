<?php

namespace App\Http\Controllers;

use App\Http\Requests\AllocationRequest;
use App\Models\Agency;
use App\Models\Allocation;
use App\Models\AssistancePurpose;
use App\Models\Beneficiary;
use App\Models\DistributionEvent;
use App\Models\ProgramName;
use App\Models\ResourceType;
use App\Services\AuditLogService;
use App\Services\ProgramEligibilityService;
use App\Services\ReleaseOutcomeService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AllocationController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
        private ReleaseOutcomeService $releaseOutcome,
    ) {}

    public function index(Request $request): View
    {
        $beneficiaries = Beneficiary::with('barangay')
            ->where('status', 'Active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'barangay_id']);

        // Programs now loaded dynamically via AJAX when beneficiary is selected
        // This prevents showing ineligible programs to users
        $resourceTypes = ResourceType::with('agency')
            ->active()
            ->whereHas('agency', fn ($query) => $query->active())
            ->orderBy('name')
            ->get();
        $programNames = ProgramName::with('agency')
            ->active()
            ->whereHas('agency', fn ($query) => $query->active())
            ->orderBy('name')
            ->get();
        $agencies = Agency::active()->orderBy('name')->get(['id', 'name']);
        $assistancePurposes = AssistancePurpose::active()->orderBy('name')->get();

        $status = (string) $request->input('status', '');
        $allowedStatusFilters = ['planned', 'ready_for_release', 'released', 'not_received'];
        $sort = (string) $request->input('sort', 'date_desc');
        $allowedSorts = ['date_desc', 'date_asc', 'program_asc', 'program_desc', 'status_asc', 'status_desc'];

        if (! in_array($status, $allowedStatusFilters, true)) {
            $status = '';
        }

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'date_desc';
        }

        $summary = [
            'total' => Allocation::where('release_method', 'direct')->count(),
            'planned' => Allocation::where('release_method', 'direct')->whereReleaseStatus('planned')->count(),
            'ready' => Allocation::where('release_method', 'direct')->whereReleaseStatus('ready_for_release')->count(),
            'released' => Allocation::where('release_method', 'direct')->whereReleaseStatus('released')->count(),
            'not_received' => Allocation::where('release_method', 'direct')->whereReleaseStatus('not_received')->count(),
        ];

        $directAllocations = Allocation::with([
            'beneficiary',
            'programName',
            'resourceType',
            'assistancePurpose',
        ])
            ->where('release_method', 'direct')
            ->when($request->filled('program_name_id'), fn ($query) => $query->where('program_name_id', $request->program_name_id))
            ->when($request->filled('agency_id'), function ($query) use ($request) {
                $agencyId = (int) $request->agency_id;

                $query->where(function ($agencyQuery) use ($agencyId) {
                    $agencyQuery
                        ->whereHas('resourceType', fn ($resourceQuery) => $resourceQuery->where('agency_id', $agencyId))
                        ->orWhereHas('programName', fn ($programQuery) => $programQuery->where('agency_id', $agencyId));
                });
            })
            ->when($status !== '', fn ($query) => $query->whereReleaseStatus($status))
            ->when($sort === 'date_desc', fn ($query) => $query->orderByDesc('created_at'))
            ->when($sort === 'date_asc', fn ($query) => $query->orderBy('created_at'))
            ->when($sort === 'program_asc', fn ($query) => $query->orderBy(
                ProgramName::select('name')->whereColumn('program_names.id', 'allocations.program_name_id')
            ))
            ->when($sort === 'program_desc', fn ($query) => $query->orderByDesc(
                ProgramName::select('name')->whereColumn('program_names.id', 'allocations.program_name_id')
            ))
            ->when($sort === 'status_asc', fn ($query) => $query->orderByRaw(
                "CASE WHEN release_outcome = 'not_received' THEN 4 WHEN distributed_at IS NOT NULL OR release_outcome = 'received' THEN 3 WHEN is_ready_for_release = 1 THEN 2 ELSE 1 END ASC"
            ))
            ->when($sort === 'status_desc', fn ($query) => $query->orderByRaw(
                "CASE WHEN release_outcome = 'not_received' THEN 4 WHEN distributed_at IS NOT NULL OR release_outcome = 'received' THEN 3 WHEN is_ready_for_release = 1 THEN 2 ELSE 1 END DESC"
            ))
            ->paginate(30)
            ->withQueryString();

        return view('allocations.index', compact(
            'beneficiaries',
            'resourceTypes',
            'programNames',
            'agencies',
            'assistancePurposes',
            'directAllocations',
            'summary'
        ));
    }

    public function show(Allocation $allocation): View
    {
        $allocation->load([
            'beneficiary.barangay',
            'distributionEvent.barangay',
            'programName.agency',
            'resourceType.agency',
            'assistancePurpose',
            'attachments' => fn ($q) => $q->latest('id')->with('uploader:id,name'),
        ]);

        return view('allocations.show', compact('allocation'));
    }

    /**
     * Get eligible programs for a beneficiary (API endpoint)
     * Used for dynamic filtering in allocation forms
     */
    public function getEligiblePrograms(Beneficiary $beneficiary)
    {
        try {
            $programs = ProgramEligibilityService::getEligiblePrograms($beneficiary);

            return response()->json([
                'success' => true,
                'programs' => $programs->map(fn ($prog) => [
                    'id' => $prog->id,
                    'name' => $prog->name,
                    'agency_name' => $prog->agency?->name ?? 'N/A',
                    'formatted' => "{$prog->name} - " . ($prog->agency?->name ?? 'Unknown Agency'),
                ])->values(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching eligible programs', [
                'beneficiary_id' => $beneficiary->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error loading programs: ' . $e->getMessage(),
                'programs' => [],
            ], 500);
        }
    }

    public function searchBeneficiaries(Request $request)
    {
        $query = $request->input('q', '');
        $barangayId = $request->input('barangay_id');
        $classification = $request->input('classification');

        $beneficiaries = Beneficiary::with('barangay')
            ->where('status', 'Active');

        // Text search on full_name or contact_number
        if ($query) {
            $beneficiaries->where(function ($q) use ($query) {
                $q->where('full_name', 'like', "%{$query}%")
                  ->orWhere('contact_number', 'like', "%{$query}%");
            });
        }

        // Filter by barangay
        if ($barangayId) {
            $beneficiaries->where('barangay_id', $barangayId);
        }

        // Filter by classification
        if ($classification && in_array($classification, ['Farmer', 'Fisherfolk'])) {
            $beneficiaries->where('classification', $classification);
        }

        $results = $beneficiaries
            ->orderBy('full_name')
            ->limit(20)
            ->get(['id', 'full_name', 'classification', 'barangay_id', 'contact_number'])
            ->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->full_name,
                'classification' => $b->classification,
                'barangay' => $b->barangay?->name ?? 'N/A',
                'contact' => $b->contact_number ?? '',
                'display' => "{$b->full_name} ({$b->classification}) - {$b->barangay?->name}",
            ]);

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    public function getResourceTypesByAgency(ProgramName $program)
    {
        try {
            if (! $program->agency_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Selected program has no agency mapping.',
                    'resourceTypes' => [],
                ], 422);
            }

            $program->loadMissing('agency:id,name');
            $agencyName = strtoupper(trim((string) ($program->agency?->name ?? '')));

            $resourceTypes = ResourceType::query()
                ->active()
                ->whereHas('agency', fn ($query) => $query->active())
                ->where('agency_id', $program->agency_id)
                ->orderBy('name')
                ->get(['id', 'name', 'unit', 'agency_id'])
                ->unique('id')
                ->values()
                ->map(fn ($rt) => [
                    'id' => $rt->id,
                    'name' => $rt->name,
                    'unit' => $rt->unit,
                    'formatted' => "{$rt->name} ({$rt->unit})",
                ]);

            if ($resourceTypes->isEmpty()) {
                $label = $agencyName !== '' ? $agencyName : 'selected agency';

                return response()->json([
                    'success' => true,
                    'message' => "No resource types are configured for {$label} programs yet.",
                    'resourceTypes' => [],
                ]);
            }

            return response()->json([
                'success' => true,
                'resourceTypes' => $resourceTypes,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching resource types', [
                'program_id' => $program->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error loading resource types',
                'resourceTypes' => [],
            ], 500);
        }
    }

    public function store(AllocationRequest $request): RedirectResponse
    {
        $beneficiary = Beneficiary::findOrFail($request->beneficiary_id);
        $releaseMethod = $request->input('release_method', 'event');

        if ($releaseMethod === 'event') {
            $event = DistributionEvent::with(['barangay', 'resourceType'])->findOrFail($request->distribution_event_id);
            $overrideReason = $this->validatedPostApprovalAddReason($request, $event);

            if ($overrideReason === false) {
                return redirect()->back()
                    ->withErrors([
                        'approval_override_reason' => 'Beneficiary list is already approved. Provide a valid reason (at least 10 characters) before adding new beneficiaries.',
                    ])
                    ->withInput();
            }

            if ($event->status === 'Completed') {
                return redirect()->back()
                    ->with('error', 'Allocations cannot be created for a completed event.');
            }

            // Check program eligibility before allocating
            $program = $event->programName;
            if (! ProgramEligibilityService::isEligible($beneficiary, $program)) {
                $reason = ProgramEligibilityService::getIneligibilityReason($beneficiary, $program);
                return redirect()->back()
                    ->with('error', 'Beneficiary is not eligible for this program: ' . $reason);
            }

            if ($beneficiary->barangay_id !== $event->barangay_id) {
                return redirect()->back()
                    ->with('error', 'This beneficiary does not belong to the same barangay as the distribution event.');
            }

            $exists = Allocation::where('distribution_event_id', $event->id)
                ->where('beneficiary_id', $beneficiary->id)
                ->exists();

            if ($exists) {
                return redirect()->back()
                    ->with('error', 'This beneficiary has already been allocated for this event.');
            }

            // Permanently remove any soft-deleted allocation so the new one can be created cleanly
            Allocation::onlyTrashed()
                ->where('distribution_event_id', $event->id)
                ->where('beneficiary_id', $beneficiary->id)
                ->forceDelete();

            try {
                DB::transaction(function () use ($request, $event, $beneficiary, $overrideReason) {
                    if ($event->isFinancial()) {
                        $this->assertFinancialBudgetAvailable($event, (float) $request->amount);
                    }

                    $allocation = Allocation::create([
                        'release_method' => 'event',
                        'distribution_event_id' => $event->id,
                        'beneficiary_id' => $beneficiary->id,
                        'program_name_id' => $event->program_name_id,
                        'resource_type_id' => $event->resource_type_id,
                        'quantity' => $event->isFinancial() ? null : $request->quantity,
                        'amount' => $event->isFinancial() ? $request->amount : null,
                        'assistance_purpose_id' => $request->assistance_purpose_id,
                        'remarks' => $request->remarks,
                    ]);

                    $this->audit->log(
                        (int) Auth::id(),
                        'created',
                        'allocations',
                        $allocation->id,
                        [],
                        array_merge($allocation->toArray(), [
                            'beneficiary_list_override_reason' => $overrideReason,
                        ]),
                    );

                    return $allocation;
                });
            } catch (\RuntimeException $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }

            return redirect()->route('distribution-events.show', $event)
                ->with('success', 'Beneficiary allocated successfully.');
        }

        $resourceType = ResourceType::with('agency')->findOrFail($request->resource_type_id);

        DB::transaction(function () use ($request, $beneficiary, $resourceType) {
            // Check program eligibility before allocating
            $program = ProgramName::findOrFail($request->program_name_id);
            if (! ProgramEligibilityService::isEligible($beneficiary, $program)) {
                $reason = ProgramEligibilityService::getIneligibilityReason($beneficiary, $program);
                throw new \RuntimeException('Beneficiary is not eligible for this program: ' . $reason);
            }

            $isFinancial = ResourceType::isFinancialUnit($resourceType->unit);

            $allocation = Allocation::create([
                'release_method' => 'direct',
                'distribution_event_id' => null,
                'beneficiary_id' => $beneficiary->id,
                'program_name_id' => $request->program_name_id,
                'resource_type_id' => $resourceType->id,
                'quantity' => $isFinancial ? null : $request->quantity,
                'amount' => $isFinancial ? $request->amount : null,
                'assistance_purpose_id' => $request->assistance_purpose_id,
                'remarks' => $request->remarks,
            ]);

            $this->audit->log(
                (int) Auth::id(),
                'created',
                'allocations',
                $allocation->id,
                [],
                $allocation->toArray(),
            );

            return $allocation;
        });

        return redirect()->route('allocations.index')
            ->with('success', 'Direct assistance allocation saved successfully.');
    }

    public function storeBulk(Request $request): RedirectResponse
    {
        $isDirectBatch = ! $request->filled('distribution_event_id');

        if ($isDirectBatch) {
            return $this->storeDirectBatch($request);
        }

        // Existing event-based batch logic
        $event = DistributionEvent::with(['barangay', 'resourceType'])->findOrFail($request->distribution_event_id);
        $overrideReason = $this->validatedPostApprovalAddReason($request, $event);

        if ($overrideReason === false) {
            return redirect()->back()
                ->withErrors([
                    'approval_override_reason' => 'Beneficiary list is already approved. Provide a valid reason (at least 10 characters) before adding new beneficiaries.',
                ])
                ->withInput();
        }

        if ($event->status === 'Completed') {
            return redirect()->back()
                ->with('error', 'Allocations cannot be created for a completed event.');
        }

        $bulkRules = [
            'distribution_event_id' => ['required', 'exists:distribution_events,id'],
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.beneficiary_id' => ['required', 'distinct', 'exists:beneficiaries,id'],
            'allocations.*.assistance_purpose_id' => ['nullable', 'exists:assistance_purposes,id'],
            'allocations.*.remarks' => ['nullable', 'string', 'max:500'],
        ];

        if ($event->isFinancial()) {
            $bulkRules['allocations.*.amount'] = ['required', 'numeric', 'min:1', 'max:9999999999.99'];
        } else {
            $bulkRules['allocations.*.quantity'] = ['required', 'numeric', 'min:0.01', 'max:9999.99'];
        }

        $request->validate($bulkRules);

        // Only check active (non-deleted) allocations
        $existingIds = Allocation::where('distribution_event_id', $event->id)
            ->pluck('beneficiary_id')
            ->toArray();

        // Clean up any soft-deleted allocations for this event so re-allocation works
        Allocation::onlyTrashed()
            ->where('distribution_event_id', $event->id)
            ->whereIn('beneficiary_id', collect($request->input('allocations'))->pluck('beneficiary_id'))
            ->forceDelete();

        $allocated = 0;
        $skipped = 0;

        try {
            DB::transaction(function () use ($request, $event, $existingIds, $overrideReason, &$allocated, &$skipped) {
                $seenInRequest = [];

                foreach ($request->input('allocations') as $row) {
                    $beneficiary = Beneficiary::find($row['beneficiary_id']);

                    if (! $beneficiary || $beneficiary->barangay_id !== $event->barangay_id) {
                        $skipped++;

                        continue;
                    }

                    // Check program eligibility
                    if (! ProgramEligibilityService::isEligible($beneficiary, $event->programName)) {
                        $skipped++;

                        continue;
                    }

                    if (in_array($beneficiary->id, $existingIds)) {
                        $skipped++;

                        continue;
                    }

                    if (in_array($beneficiary->id, $seenInRequest, true)) {
                        $skipped++;

                        continue;
                    }

                    if ($event->isFinancial()) {
                        $this->assertFinancialBudgetAvailable($event, (float) $row['amount']);
                    }

                    $allocation = Allocation::create([
                        'release_method' => 'event',
                        'distribution_event_id' => $event->id,
                        'beneficiary_id' => $beneficiary->id,
                        'program_name_id' => $event->program_name_id,
                        'resource_type_id' => $event->resource_type_id,
                        'quantity' => $event->isFinancial() ? null : $row['quantity'],
                        'amount' => $event->isFinancial() ? $row['amount'] : null,
                        'assistance_purpose_id' => $row['assistance_purpose_id'] ?? null,
                        'remarks' => $row['remarks'] ?? null,
                    ]);

                    $seenInRequest[] = $beneficiary->id;

                    $this->audit->log(
                        (int) Auth::id(),
                        'created',
                        'allocations',
                        $allocation->id,
                        [],
                        array_merge($allocation->toArray(), [
                            'beneficiary_list_override_reason' => $overrideReason,
                        ]),
                    );

                    $allocated++;
                }
            });
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('distribution-events.show', $event)
            ->with('success', "{$allocated} allocated, {$skipped} skipped.");
    }

    private function storeDirectBatch(Request $request): RedirectResponse
    {
        $bulkRules = [
            'release_method' => ['required', 'in:direct'],
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.beneficiary_id' => ['required', 'distinct', 'exists:beneficiaries,id'],
            'allocations.*.program_name_id' => ['required', 'exists:program_names,id'],
            'allocations.*.resource_type_id' => ['required', 'exists:resource_types,id'],
            'allocations.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:9999.99'],
            'allocations.*.assistance_purpose_id' => ['nullable', 'exists:assistance_purposes,id'],
        ];

        $request->validate($bulkRules);

        $allocated = 0;
        $errors = [];

        try {
            DB::transaction(function () use ($request, &$allocated, &$errors) {
                foreach ($request->input('allocations') as $idx => $row) {
                    try {
                        $beneficiary = Beneficiary::findOrFail($row['beneficiary_id']);
                        $program = ProgramName::findOrFail($row['program_name_id']);
                        $resourceType = ResourceType::findOrFail($row['resource_type_id']);

                        // Validate program eligibility
                        if (! ProgramEligibilityService::isEligible($beneficiary, $program)) {
                            $reason = ProgramEligibilityService::getIneligibilityReason($beneficiary, $program);
                            $errors[] = "Row " . ($idx + 1) . ": {$beneficiary->full_name} - {$reason}";
                            continue;
                        }

                        // Check if resource belongs to program's agency
                        if ($resourceType->agency_id !== $program->agency_id) {
                            $errors[] = "Row " . ($idx + 1) . ": {$beneficiary->full_name} - Resource type not from program's agency";
                            continue;
                        }

                        // Check for duplicates in batch
                        $exists = Allocation::where('beneficiary_id', $beneficiary->id)
                            ->where('program_name_id', $program->id)
                            ->where('release_method', 'direct')
                            ->whereNull('distribution_event_id')
                            ->whereNull('distributed_at')
                            ->exists();

                        if ($exists) {
                            $errors[] = "Row " . ($idx + 1) . ": {$beneficiary->full_name} - Already allocated to this program";
                            continue;
                        }

                        $allocation = Allocation::create([
                            'release_method' => 'direct',
                            'distribution_event_id' => null,
                            'beneficiary_id' => $beneficiary->id,
                            'program_name_id' => $program->id,
                            'resource_type_id' => $resourceType->id,
                            'quantity' => $row['quantity'],
                            'amount' => null,
                            'assistance_purpose_id' => $row['assistance_purpose_id'] ?? null,
                        ]);

                        $this->audit->log(
                            (int) Auth::id(),
                            'created',
                            'allocations',
                            $allocation->id,
                            [],
                            $allocation->toArray(),
                        );

                        $allocated++;
                    } catch (\Exception $e) {
                        $errors[] = "Row " . ($idx + 1) . ": " . $e->getMessage();
                    }
                }
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Batch operation failed: ' . $e->getMessage())
                ->withInput();
        }

        $message = "{$allocated} allocations created successfully.";
        if (! empty($errors)) {
            $message .= " " . count($errors) . " row(s) failed: " . implode('; ', array_slice($errors, 0, 3));
        }

        return redirect()->route('allocations.index')
            ->with($allocated > 0 ? 'success' : 'error', $message);
    }

    public function downloadImportCsvTemplate(Request $request): StreamedResponse
    {
        $request->validate([
            'distribution_event_id' => ['required', 'exists:distribution_events,id'],
        ]);

        $event = DistributionEvent::findOrFail($request->distribution_event_id);
        $isFinancial = $event->isFinancial();

        $filename = 'allocation-import-template-event-'.$event->id.'.csv';
        $headers = ['beneficiary_id', $isFinancial ? 'amount' : 'quantity', 'assistance_purpose_id', 'remarks'];

        return response()->streamDownload(function () use ($headers, $isFinancial): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                return;
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $headers);

            $sample = [
                1001,
                $isFinancial ? '1500.00' : '10.00',
                '',
                'Sample remarks',
            ];

            fputcsv($output, $sample);
            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function downloadImportCsvErrorsReport(string $report): BinaryFileResponse
    {
        if (! preg_match('/^allocation-import-errors-event-\d+-\d{8}-\d{6}-[a-f0-9-]+\.csv$/i', $report)) {
            abort(404);
        }

        $disk = Storage::disk('allocation_import_reports');

        if (! $disk->exists($report)) {
            abort(404, 'Import error report file not found.');
        }

        return response()->download(
            $disk->path($report),
            $report,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }

    public function importCsv(Request $request): RedirectResponse
    {
        $request->validate([
            'distribution_event_id' => ['required', 'exists:distribution_events,id'],
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $event = DistributionEvent::with(['barangay', 'resourceType'])->findOrFail($request->distribution_event_id);
        $overrideReason = $this->validatedPostApprovalAddReason($request, $event);

        if ($overrideReason === false) {
            return redirect()->back()
                ->withErrors([
                    'approval_override_reason' => 'Beneficiary list is already approved. Provide a valid reason (at least 10 characters) before adding new beneficiaries.',
                ])
                ->withInput();
        }

        if ($event->status === 'Completed') {
            return redirect()->back()
                ->with('error', 'Allocations cannot be imported for a completed event.');
        }

        try {
            $rows = $this->parseBulkAllocationCsv($request->file('csv_file'), $event->isFinancial());
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        if (empty($rows)) {
            return redirect()->back()->with('error', 'CSV file has no data rows to import.');
        }

        $incomingIds = collect($rows)
            ->pluck('beneficiary_id')
            ->filter(fn ($id) => (int) $id > 0)
            ->unique()
            ->values();

        if ($incomingIds->isNotEmpty()) {
            Allocation::onlyTrashed()
                ->where('distribution_event_id', $event->id)
                ->whereIn('beneficiary_id', $incomingIds)
                ->forceDelete();
        }

        $existingIds = Allocation::where('distribution_event_id', $event->id)
            ->pluck('beneficiary_id')
            ->toArray();

        $purposeIds = collect($rows)
            ->pluck('assistance_purpose_id')
            ->filter(fn ($id) => ! is_null($id))
            ->unique()
            ->values();

        $validPurposeIds = $purposeIds->isEmpty()
            ? []
            : AssistancePurpose::whereIn('id', $purposeIds)->pluck('id')->toArray();

        $allocated = 0;
        $skipped = 0;
        $rowErrors = [];

        DB::transaction(function () use ($rows, $event, $existingIds, $validPurposeIds, $overrideReason, &$allocated, &$skipped, &$rowErrors): void {
            $seenInFile = [];

            foreach ($rows as $row) {
                $line = (int) $row['_line'];
                $beneficiaryId = (int) $row['beneficiary_id'];

                if ($beneficiaryId <= 0) {
                    $skipped++;
                    $rowErrors[] = $this->buildCsvImportErrorRow($row, 'beneficiary_id is invalid.');

                    continue;
                }

                if (in_array($beneficiaryId, $seenInFile, true)) {
                    $skipped++;
                    $rowErrors[] = $this->buildCsvImportErrorRow($row, 'duplicate beneficiary_id in the same CSV.');

                    continue;
                }

                if (in_array($beneficiaryId, $existingIds, true)) {
                    $skipped++;
                    $rowErrors[] = $this->buildCsvImportErrorRow($row, 'beneficiary is already allocated in this event.');

                    continue;
                }

                $beneficiary = Beneficiary::find($beneficiaryId);

                if (! $beneficiary) {
                    $skipped++;
                    $rowErrors[] = $this->buildCsvImportErrorRow($row, "beneficiary_id {$beneficiaryId} does not exist.");

                    continue;
                }

                if ($beneficiary->barangay_id !== $event->barangay_id) {
                    $skipped++;
                    $rowErrors[] = $this->buildCsvImportErrorRow($row, 'beneficiary barangay does not match event barangay.');

                    continue;
                }

                $assistancePurposeId = $row['assistance_purpose_id'];
                if (! is_null($assistancePurposeId) && ! in_array((int) $assistancePurposeId, $validPurposeIds, true)) {
                    $skipped++;
                    $rowErrors[] = $this->buildCsvImportErrorRow($row, 'assistance_purpose_id is invalid.');

                    continue;
                }

                if ($event->isFinancial()) {
                    $amount = $row['amount'];

                    if (! is_numeric($amount) || (float) $amount <= 0) {
                        $skipped++;
                        $rowErrors[] = $this->buildCsvImportErrorRow($row, 'amount must be a positive number.');

                        continue;
                    }

                    try {
                        $this->assertFinancialBudgetAvailable($event, (float) $amount);
                    } catch (\RuntimeException $e) {
                        $skipped++;
                        $rowErrors[] = $this->buildCsvImportErrorRow($row, $e->getMessage());

                        continue;
                    }
                } else {
                    $quantity = $row['quantity'];

                    if (! is_numeric($quantity) || (float) $quantity <= 0) {
                        $skipped++;
                        $rowErrors[] = $this->buildCsvImportErrorRow($row, 'quantity must be a positive number.');

                        continue;
                    }
                }

                $allocation = Allocation::create([
                    'release_method' => 'event',
                    'distribution_event_id' => $event->id,
                    'beneficiary_id' => $beneficiary->id,
                    'program_name_id' => $event->program_name_id,
                    'resource_type_id' => $event->resource_type_id,
                    'quantity' => $event->isFinancial() ? null : $row['quantity'],
                    'amount' => $event->isFinancial() ? $row['amount'] : null,
                    'assistance_purpose_id' => $assistancePurposeId,
                    'remarks' => $row['remarks'],
                ]);

                $this->audit->log(
                    (int) Auth::id(),
                    'created',
                    'allocations',
                    $allocation->id,
                    [],
                    array_merge($allocation->toArray(), [
                        'beneficiary_list_override_reason' => $overrideReason,
                    ]),
                );

                $seenInFile[] = $beneficiaryId;
                $allocated++;
            }
        });

        $errorReportFile = ! empty($rowErrors)
            ? $this->storeCsvImportErrorReport($event->id, $rowErrors)
            : null;

        $sampleIssues = collect($rowErrors)
            ->pluck('error')
            ->take(3)
            ->implode(' | ');

        if ($allocated === 0) {
            $warning = "No allocations imported. {$skipped} row(s) skipped.";
            if ($sampleIssues !== '') {
                $warning .= " Sample issues: {$sampleIssues}";
            }

            $response = redirect()->route('distribution-events.show', $event)
                ->with('warning', $warning);

            if ($errorReportFile !== null) {
                $response
                    ->with('import_error_report_file', $errorReportFile)
                    ->with('import_error_report_count', count($rowErrors));
            }

            return $response;
        }

        $message = "{$allocated} allocated, {$skipped} skipped via CSV import.";
        if ($sampleIssues !== '') {
            $message .= " Sample issues: {$sampleIssues}";
        }

        $response = redirect()->route('distribution-events.show', $event)
            ->with('success', $message);

        if ($errorReportFile !== null) {
            $response
                ->with('import_error_report_file', $errorReportFile)
                ->with('import_error_report_count', count($rowErrors));
        }

        return $response;
    }

    /**
     * Require a reason when adding beneficiaries after beneficiary-list approval.
     *
     * @return string|false|null Valid reason string, false for invalid input, null when not required.
     */
    private function validatedPostApprovalAddReason(Request $request, DistributionEvent $event): string|false|null
    {
        if (! $event->isBeneficiaryListApproved()) {
            return null;
        }

        $reason = trim((string) $request->input('approval_override_reason', ''));

        if ($reason === '' || Str::length($reason) < 10) {
            return false;
        }

        if (Str::length($reason) > 500) {
            return false;
        }

        return $reason;
    }

    public function update(Request $request, Allocation $allocation): RedirectResponse
    {
        $event = $allocation->distributionEvent;

        if (! $event) {
            return redirect()->route('allocations.index')
                ->with('error', 'Direct allocations can only be edited from the assistance allocation page.');
        }

        if ($event->status === 'Completed') {
            return redirect()->back()
                ->with('error', 'Allocations cannot be updated for a completed event.');
        }

        $rules = ['remarks' => ['nullable', 'string', 'max:500']];
        $rules['assistance_purpose_id'] = ['nullable', 'exists:assistance_purposes,id'];

        if ($event->isFinancial()) {
            $rules['amount'] = ['required', 'numeric', 'min:1', 'max:9999999999.99'];
            $rules['quantity'] = ['nullable'];
        } else {
            $rules['quantity'] = ['required', 'numeric', 'min:0.01', 'max:9999.99'];
            $rules['amount'] = ['nullable'];
        }

        $request->validate($rules);

        try {
            DB::transaction(function () use ($request, $allocation, $event) {
                $oldValues = $allocation->toArray();

                if ($event->isFinancial()) {
                    $newAmount = (float) $request->amount;
                    $this->assertFinancialAllocationAmountFits($event, $newAmount, $allocation->id);
                }

                $allocation->update([
                    'quantity' => $event->isFinancial() ? null : $request->quantity,
                    'amount' => $event->isFinancial() ? $request->amount : null,
                    'assistance_purpose_id' => $request->assistance_purpose_id,
                    'remarks' => $request->remarks,
                ]);

                $this->audit->log(
                    (int) Auth::id(),
                    'updated',
                    'allocations',
                    $allocation->id,
                    $oldValues,
                    $allocation->fresh()->toArray(),
                );
            });
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('distribution-events.show', $allocation->distribution_event_id)
            ->with('success', 'Allocation updated successfully.');
    }

    public function destroy(Allocation $allocation): RedirectResponse
    {
        $event = $allocation->distributionEvent;

        if ($event && $event->status === 'Completed') {
            return redirect()->back()
                ->with('error', 'Allocations cannot be removed from a completed event.');
        }

        DB::transaction(function () use ($allocation) {
            $allocation->delete();

            $this->audit->log(
                (int) Auth::id(),
                'deleted',
                'allocations',
                $allocation->id,
                $allocation->toArray(),
            );
        });

        if ($event) {
            return redirect()->route('distribution-events.show', $event)
                ->with('success', 'Allocation removed successfully.');
        }

        return redirect()->route('allocations.index')
            ->with('success', 'Allocation removed successfully.');
    }

    public function markDistributed(Allocation $allocation): RedirectResponse
    {
        $event = $allocation->distributionEvent;

        if ($event && $event->status === 'Pending') {
            return redirect()->back()
                ->with('error', 'Cannot mark as distributed while event is still Pending.');
        }

        if ($allocation->isDirect()) {
            if ($allocation->release_status === 'released') {
                return redirect()->back()
                    ->with('error', 'This direct allocation is already marked as released.');
            }

            if ($allocation->release_status === 'not_received') {
                return redirect()->back()
                    ->with('error', 'Set this direct allocation to Ready for Release before marking it as released.');
            }

            if (! (bool) $allocation->is_ready_for_release) {
                return redirect()->back()
                    ->with('error', 'Set this direct allocation to Ready for Release before marking it as released.');
            }
        } elseif ($allocation->distributed_at || $allocation->release_outcome === 'not_received') {
            return redirect()->back()
                ->with('error', 'This allocation already has a final release outcome.');
        }

        $this->releaseOutcome->apply(
            $allocation,
            [
                'is_ready_for_release' => false,
                'distributed_at' => Carbon::now(),
                'release_outcome' => 'received',
            ],
            $this->audit,
            'updated',
            'allocations',
        );

        return redirect()->back()
            ->with('success', 'Allocation marked as distributed.');
    }

    public function markReadyForRelease(Allocation $allocation): RedirectResponse
    {
        if (! $allocation->isDirect()) {
            return redirect()->back()
                ->with('error', 'Ready for Release transition is available only for direct allocations.');
        }

        $event = $allocation->distributionEvent;
        if ($event && $event->status === 'Pending') {
            return redirect()->back()
                ->with('error', 'Cannot mark as Ready for Release while event is still Pending.');
        }

        if ($allocation->release_status === 'released') {
            return redirect()->back()
                ->with('error', 'Released allocations cannot be moved back to Ready for Release.');
        }

        if ((bool) $allocation->is_ready_for_release) {
            return redirect()->back()
                ->with('warning', 'This allocation is already marked as Ready for Release.');
        }

        $this->releaseOutcome->apply(
            $allocation,
            [
                'is_ready_for_release' => true,
                'distributed_at' => null,
                'release_outcome' => null,
            ],
            $this->audit,
            'updated',
            'allocations',
        );

        return redirect()->back()
            ->with('success', 'Allocation marked as Ready for Release.');
    }

    public function markNotReceived(Allocation $allocation): RedirectResponse
    {
        $event = $allocation->distributionEvent;

        if ($event && $event->status === 'Pending') {
            return redirect()->back()
                ->with('error', 'Cannot mark as not received while event is still Pending.');
        }

        if ($allocation->isDirect()) {
            if ($allocation->release_status === 'released') {
                return redirect()->back()
                    ->with('error', 'Released direct allocations cannot be marked as not received.');
            }

            if ($allocation->release_status === 'not_received') {
                return redirect()->back()
                    ->with('error', 'This direct allocation is already marked as Not Received.');
            }

            if (! (bool) $allocation->is_ready_for_release) {
                return redirect()->back()
                    ->with('error', 'Set this direct allocation to Ready for Release before marking it as Not Received.');
            }
        } elseif ($allocation->distributed_at || $allocation->release_outcome === 'not_received') {
            return redirect()->back()
                ->with('error', 'This allocation already has a final release outcome.');
        }

        $this->releaseOutcome->apply(
            $allocation,
            [
                'is_ready_for_release' => false,
                'distributed_at' => null,
                'release_outcome' => 'not_received',
            ],
            $this->audit,
            'updated',
            'allocations',
        );

        return redirect()->back()
            ->with('success', 'Allocation marked as not received.');
    }

    public function bulkUpdateReleaseOutcome(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'distribution_event_id' => ['required', 'exists:distribution_events,id'],
            'allocation_ids' => ['required', 'array', 'min:1'],
            'allocation_ids.*' => ['integer', 'distinct', 'exists:allocations,id'],
            'action' => ['required', 'in:distributed,not_received'],
        ]);

        $event = DistributionEvent::findOrFail($validated['distribution_event_id']);

        if ($event->status === 'Pending') {
            return redirect()->back()
                ->with('error', 'Bulk release updates are only allowed after the event starts.');
        }

        $allocationIds = collect($validated['allocation_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $allocations = Allocation::whereIn('id', $allocationIds)
            ->where('distribution_event_id', $event->id)
            ->where('release_method', 'event')
            ->get();

        if ($allocations->isEmpty()) {
            return redirect()->back()
                ->with('error', 'No valid allocations were selected for this event.');
        }

        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($allocations, $validated, &$updated, &$skipped) {
            foreach ($allocations as $allocation) {
                if ($allocation->distributed_at || $allocation->release_outcome === 'not_received') {
                    $skipped++;

                    continue;
                }

                $oldValues = $allocation->toArray();

                if ($validated['action'] === 'distributed') {
                    $allocation->update([
                        'is_ready_for_release' => false,
                        'distributed_at' => Carbon::now(),
                        'release_outcome' => 'received',
                    ]);
                } else {
                    $allocation->update([
                        'is_ready_for_release' => false,
                        'distributed_at' => null,
                        'release_outcome' => 'not_received',
                    ]);
                }

                $this->audit->log(
                    (int) Auth::id(),
                    'updated',
                    'allocations',
                    $allocation->id,
                    $oldValues,
                    $allocation->fresh()->toArray(),
                );

                $updated++;
            }
        });

        if ($updated === 0) {
            return redirect()->back()
                ->with('warning', 'No allocations were updated. Selected entries may already have final outcomes.');
        }

        $label = $validated['action'] === 'distributed'
            ? 'marked as distributed'
            : 'marked as not received';

        return redirect()->back()
            ->with('success', "{$updated} allocation(s) {$label}. {$skipped} skipped.");
    }

    private function assertFinancialBudgetAvailable(DistributionEvent $event, float $additionalAmount, ?int $excludeAllocationId = null): void
    {
        if (! $event->isFinancial() || $additionalAmount <= 0) {
            return;
        }

        $lockedEvent = DistributionEvent::whereKey($event->id)->lockForUpdate()->firstOrFail();

        $budget = (float) ($lockedEvent->total_fund_amount ?? 0);
        if ($budget <= 0) {
            throw new \RuntimeException('This financial event has no valid total fund amount configured.');
        }

        $currentAllocated = Allocation::where('distribution_event_id', $event->id)
            ->when($excludeAllocationId, fn ($q) => $q->where('id', '!=', $excludeAllocationId))
            ->sum('amount');

        $remaining = $budget - (float) $currentAllocated;

        if ($additionalAmount - $remaining > 0.00001) {
            throw new \RuntimeException('Allocation exceeds remaining event budget. Remaining budget: PHP '.number_format(max($remaining, 0), 2).'.');
        }
    }

    private function assertFinancialAllocationAmountFits(DistributionEvent $event, float $proposedAmount, ?int $excludeAllocationId = null): void
    {
        if (! $event->isFinancial() || $proposedAmount <= 0) {
            return;
        }

        $lockedEvent = DistributionEvent::whereKey($event->id)->lockForUpdate()->firstOrFail();
        $budget = (float) ($lockedEvent->total_fund_amount ?? 0);

        if ($budget <= 0) {
            throw new \RuntimeException('This financial event has no valid total fund amount configured.');
        }

        $otherAllocated = Allocation::where('distribution_event_id', $event->id)
            ->when($excludeAllocationId, fn ($q) => $q->where('id', '!=', $excludeAllocationId))
            ->sum('amount');

        if (($otherAllocated + $proposedAmount) - $budget > 0.00001) {
            $remaining = max($budget - (float) $otherAllocated, 0);
            throw new \RuntimeException('Allocation exceeds remaining event budget. Remaining budget: PHP '.number_format($remaining, 2).'.');
        }
    }

    private function parseBulkAllocationCsv(UploadedFile $csvFile, bool $isFinancial): array
    {
        $handle = fopen($csvFile->getRealPath(), 'rb');

        if ($handle === false) {
            throw new \RuntimeException('Unable to read the uploaded CSV file.');
        }

        try {
            $header = fgetcsv($handle);

            if ($header === false || count($header) === 0) {
                throw new \RuntimeException('CSV file is empty.');
            }

            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]);
            $normalizedHeaders = array_map(fn ($cell) => $this->normalizeCsvHeader((string) $cell), $header);

            $requiredColumns = ['beneficiary_id', $isFinancial ? 'amount' : 'quantity'];
            foreach ($requiredColumns as $requiredColumn) {
                if (! in_array($requiredColumn, $normalizedHeaders, true)) {
                    throw new \RuntimeException("CSV is missing required column: {$requiredColumn}.");
                }
            }

            $rows = [];
            $line = 1;

            while (($data = fgetcsv($handle)) !== false) {
                $line++;

                if ($this->isCsvRowEmpty($data)) {
                    continue;
                }

                $rowData = array_pad($data, count($normalizedHeaders), null);
                $mapped = [];

                foreach ($normalizedHeaders as $index => $column) {
                    if ($column === '') {
                        continue;
                    }

                    $mapped[$column] = isset($rowData[$index]) ? trim((string) $rowData[$index]) : null;
                }

                $rows[] = [
                    '_line' => $line,
                    'beneficiary_id' => isset($mapped['beneficiary_id']) ? (int) $mapped['beneficiary_id'] : 0,
                    'amount' => isset($mapped['amount']) && $mapped['amount'] !== '' ? (float) $mapped['amount'] : null,
                    'quantity' => isset($mapped['quantity']) && $mapped['quantity'] !== '' ? (float) $mapped['quantity'] : null,
                    'assistance_purpose_id' => isset($mapped['assistance_purpose_id']) && $mapped['assistance_purpose_id'] !== ''
                        ? (int) $mapped['assistance_purpose_id']
                        : null,
                    'remarks' => isset($mapped['remarks']) && $mapped['remarks'] !== ''
                        ? mb_substr((string) $mapped['remarks'], 0, 500)
                        : null,
                ];
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }

    private function normalizeCsvHeader(string $header): string
    {
        $normalized = strtolower(trim($header));
        $normalized = str_replace([' ', '-'], '_', $normalized);

        return preg_replace('/[^a-z0-9_]/', '', $normalized) ?? '';
    }

    private function isCsvRowEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private function buildCsvImportErrorRow(array $row, string $error): array
    {
        return [
            'line' => (int) ($row['_line'] ?? 0),
            'beneficiary_id' => $row['beneficiary_id'] ?? null,
            'amount' => $row['amount'] ?? null,
            'quantity' => $row['quantity'] ?? null,
            'assistance_purpose_id' => $row['assistance_purpose_id'] ?? null,
            'remarks' => $row['remarks'] ?? null,
            'error' => $error,
        ];
    }

    private function storeCsvImportErrorReport(int $eventId, array $rowErrors): ?string
    {
        $filename = sprintf(
            'allocation-import-errors-event-%d-%s-%s.csv',
            $eventId,
            now()->format('Ymd-His'),
            (string) Str::uuid(),
        );

        $stream = fopen('php://temp', 'w+');

        if ($stream === false) {
            return null;
        }

        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, [
            'line',
            'beneficiary_id',
            'amount',
            'quantity',
            'assistance_purpose_id',
            'remarks',
            'error',
        ]);

        foreach ($rowErrors as $rowError) {
            fputcsv($stream, [
                $rowError['line'] ?? '',
                $rowError['beneficiary_id'] ?? '',
                $rowError['amount'] ?? '',
                $rowError['quantity'] ?? '',
                $rowError['assistance_purpose_id'] ?? '',
                $rowError['remarks'] ?? '',
                $rowError['error'] ?? '',
            ]);
        }

        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        if (! is_string($contents) || $contents === '') {
            return null;
        }

        if (! Storage::disk('allocation_import_reports')->put($filename, $contents)) {
            return null;
        }

        return $filename;
    }
}

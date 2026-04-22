<?php

namespace App\Http\Controllers;

use App\Http\Requests\DistributionEventRequest;
use App\Models\Agency;
use App\Models\AssistancePurpose;
use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Models\DistributionEvent;
use App\Models\ProgramName;
use App\Models\ResourceType;
use App\Services\AuditLogService;
use App\Services\ProgramEligibilityService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DistributionEventController extends Controller
{
    private ?bool $hasComplianceFieldStatesColumn = null;

    public function __construct(
        private AuditLogService $audit,
    ) {}

    public function index(Request $request): View
    {
        $sort = (string) $request->input('sort', 'created_desc');
        $allowedSorts = ['created_desc', 'created_asc', 'date_desc', 'date_asc', 'program_asc', 'program_desc', 'status_asc', 'status_desc'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'date_desc';
        }

        $events = DistributionEvent::with(['barangay', 'resourceType.agency', 'programName', 'createdBy'])
            ->withCount('allocations')
            ->when($request->filled('program_name_id'), fn ($q) => $q->where('program_name_id', $request->program_name_id))
            ->when($request->filled('agency_id'), function ($q) use ($request) {
                $agencyId = (int) $request->agency_id;

                $q->where(function ($agencyQuery) use ($agencyId) {
                    $agencyQuery
                        ->whereHas('resourceType', fn ($resourceQuery) => $resourceQuery->where('agency_id', $agencyId))
                        ->orWhereHas('programName', fn ($programQuery) => $programQuery->where('agency_id', $agencyId));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($sort === 'created_desc', fn ($q) => $q
                ->orderByDesc('created_at')
                ->orderByDesc('id'))
            ->when($sort === 'created_asc', fn ($q) => $q
                ->orderBy('created_at')
                ->orderByDesc('id'))
            ->when($sort === 'date_desc', fn ($q) => $q
                ->orderByDesc('distribution_date')
                ->orderByDesc('created_at')
                ->orderByDesc('id'))
            ->when($sort === 'date_asc', fn ($q) => $q
                ->orderBy('distribution_date')
                ->orderByDesc('created_at')
                ->orderByDesc('id'))
            ->when($sort === 'program_asc', fn ($q) => $q
                ->orderBy(ProgramName::select('name')->whereColumn('program_names.id', 'distribution_events.program_name_id'))
                ->orderByDesc('distribution_date')
                ->orderByDesc('created_at')
                ->orderByDesc('id'))
            ->when($sort === 'program_desc', fn ($q) => $q
                ->orderByDesc(ProgramName::select('name')->whereColumn('program_names.id', 'distribution_events.program_name_id'))
                ->orderByDesc('distribution_date')
                ->orderByDesc('created_at')
                ->orderByDesc('id'))
            ->when($sort === 'status_asc', fn ($q) => $q
                ->orderBy('status')
                ->orderByDesc('distribution_date')
                ->orderByDesc('created_at')
                ->orderByDesc('id'))
            ->when($sort === 'status_desc', fn ($q) => $q
                ->orderByDesc('status')
                ->orderByDesc('distribution_date')
                ->orderByDesc('created_at')
                ->orderByDesc('id'))
            ->paginate(15)
            ->withQueryString();

        $agencies = Agency::active()->orderBy('name')->get(['id', 'name']);
        $programNames = ProgramName::with('agency')->active()->orderBy('name')->get();

        return view('distribution_events.index', compact(
            'events',
            'agencies',
            'programNames',
        ));
    }

    public function create(): View
    {
        $barangays = Barangay::orderBy('name')->get();
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

        return view('distribution_events.create', compact('barangays', 'resourceTypes', 'programNames'));
    }

    public function store(DistributionEventRequest $request): RedirectResponse|JsonResponse
    {
        $event = DB::transaction(function () use ($request) {
            $validated = $request->validated();

            if ($this->hasComplianceFieldStatesColumn()) {
                $expandedInputs = $this->expandComplianceStateInputs(
                    (array) $request->input('compliance_states', []),
                    (array) $request->input('compliance_reasons', []),
                    (string) $request->input('compliance_overall_status', ''),
                    isset($request['compliance_overall_reason']) ? trim((string) $request->input('compliance_overall_reason')) : null,
                );

                $validated['compliance_field_states'] = $this->normalizeComplianceFieldStates(
                    $validated,
                    [],
                    $expandedInputs['states'],
                    $expandedInputs['reasons'],
                );
            }

            $event = DistributionEvent::create([
                ...$validated,
                'created_by' => auth()->id(),
            ]);

            $this->audit->log(
                auth()->id(),
                'created',
                'distribution_events',
                $event->id,
                [],
                $event->toArray(),
            );

            return $event;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Distribution event created successfully.',
                'event_id' => $event->id,
                'redirect_url' => route('distribution-events.show', $event),
            ]);
        }

        return redirect()->route('distribution-events.show', $event)
            ->with('success', 'Distribution event created successfully.');
    }

    public function show(DistributionEvent $event): View
    {
        $event->load([
            'barangay',
            'resourceType.agency',
            'programName',
            'createdBy',
            'beneficiaryListApprovedBy',
            'allocations.beneficiary',
            'attachments' => fn ($q) => $q->latest('id')->with('uploader:id,name'),
        ]);

        $allocatedBeneficiaryIds = $event->allocations->pluck('beneficiary_id')->toArray();
        $assistancePurposes = AssistancePurpose::active()
            ->orderBy('name')
            ->get(['id', 'name']);

        // Get eligible beneficiaries for this event's program (with program eligibility filtering)
        $availableBeneficiaries = Beneficiary::where('barangay_id', $event->barangay_id)
            ->where('status', 'Active')
            ->whereNotIn('id', $allocatedBeneficiaryIds)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'classification', 'agency_id'])
            ->filter(function ($beneficiary) use ($event) {
                // Only include beneficiaries eligible for this event's program
                return ProgramEligibilityService::isEligible($beneficiary, $event->programName);
            })
            ->values();

        $completionComplianceIssues = $event->isFinancial()
            ? $this->getCompletionComplianceIssues($event)
            : [];

        $completionComplianceReady = empty($completionComplianceIssues);
        $unmarkedBeneficiariesCount = $event->unmarkedAllocationsCount();
        $allBeneficiariesMarked = $unmarkedBeneficiariesCount === 0;

        return view('distribution_events.show', compact(
            'event',
            'allocatedBeneficiaryIds',
            'assistancePurposes',
            'availableBeneficiaries',
            'completionComplianceIssues',
            'completionComplianceReady',
            'unmarkedBeneficiariesCount',
            'allBeneficiariesMarked',
        ));
    }

    public function distributionList(DistributionEvent $event): View
    {
        $this->loadDistributionListRelations($event);

        return view('distribution_events.distribution_list', compact('event'));
    }

    public function distributionListPdf(DistributionEvent $event)
    {
        $this->loadDistributionListRelations($event);

        $filename = 'distribution-list-event-'.$event->id.'-'.now()->format('Ymd-His').'.pdf';

        $pdf = Pdf::loadView('distribution_events.distribution_list_pdf', compact('event'))
            ->setPaper('a4', 'landscape');

        if (request()->boolean('inline')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }

    public function distributionListCsv(DistributionEvent $event): StreamedResponse
    {
        $this->loadDistributionListRelations($event);

        $filename = 'distribution-list-event-'.$event->id.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($event) {
            $output = fopen('php://output', 'w');

            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, [
                'No',
                'Beneficiary Name',
                'Classification',
                'Contact Number',
                'Barangay',
                $event->isFinancial() ? 'Amount (PHP)' : 'Quantity',
                'Remarks',
            ]);

            foreach ($event->allocations as $index => $allocation) {
                $allocationValue = $event->isFinancial()
                    ? number_format((float) $allocation->amount, 2, '.', '')
                    : number_format((float) $allocation->quantity, 2, '.', '').' '.$event->resourceType->unit;

                fputcsv($output, [
                    $index + 1,
                    $allocation->beneficiary->full_name,
                    $allocation->beneficiary->classification,
                    $allocation->beneficiary->contact_number ?? '',
                    $event->barangay->name,
                    $allocationValue,
                    $allocation->remarks ?? '',
                ]);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function loadDistributionListRelations(DistributionEvent $event): void
    {
        $event->load([
            'barangay',
            'resourceType.agency',
            'programName',
            'allocations.beneficiary',
        ]);

        $sortedAllocations = $event->allocations
            ->sortBy(function ($allocation) {
                return $allocation->beneficiary->full_name;
            })
            ->values();

        $event->setRelation('allocations', $sortedAllocations);
    }

    public function edit(DistributionEvent $event): View|RedirectResponse
    {
        if ($event->status !== 'Pending') {
            return redirect()->back()
                ->with('error', 'Only Pending events can be edited.');
        }

        $barangays = Barangay::orderBy('name')->get();
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

        return view('distribution_events.edit', compact('event', 'barangays', 'resourceTypes', 'programNames'));
    }

    public function update(DistributionEventRequest $request, DistributionEvent $event): RedirectResponse|JsonResponse
    {
        if ($event->status !== 'Pending') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only Pending events can be edited.',
                ], 422);
            }

            return redirect()->route('distribution-events.index')
                ->with('error', 'Only Pending events can be edited.');
        }

        DB::transaction(function () use ($request, $event) {
            $oldValues = $event->toArray();

            $validated = $request->validated();

            if ($this->hasComplianceFieldStatesColumn()) {
                $expandedInputs = $this->expandComplianceStateInputs(
                    (array) $request->input('compliance_states', []),
                    (array) $request->input('compliance_reasons', []),
                    (string) $request->input('compliance_overall_status', ''),
                    isset($request['compliance_overall_reason']) ? trim((string) $request->input('compliance_overall_reason')) : null,
                );

                $validated['compliance_field_states'] = $this->normalizeComplianceFieldStates(
                    $validated,
                    is_array($event->compliance_field_states) ? $event->compliance_field_states : [],
                    $expandedInputs['states'],
                    $expandedInputs['reasons'],
                );
            }

            $event->update($validated);

            $this->audit->log(
                auth()->id(),
                'updated',
                'distribution_events',
                $event->id,
                $oldValues,
                $event->fresh()->toArray(),
            );
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Distribution event updated successfully.',
                'event_id' => $event->id,
                'redirect_url' => route('distribution-events.show', $event),
            ]);
        }

        return redirect()->route('distribution-events.index')
            ->with('success', 'Distribution event updated successfully.');
    }

    public function destroy(DistributionEvent $event): RedirectResponse
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Only admins can delete distribution events.');
        }

        if ($event->status !== 'Pending') {
            return redirect()->route('distribution-events.index')
                ->with('error', 'Only Pending events can be deleted.');
        }

        DB::transaction(function () use ($event) {
            $event->delete();

            $this->audit->log(
                auth()->id(),
                'deleted',
                'distribution_events',
                $event->id,
                $event->toArray(),
            );
        });

        return redirect()->route('distribution-events.index')
            ->with('success', 'Distribution event deleted successfully.');
    }

    public function updateStatus(Request $request, DistributionEvent $event): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:Pending,Ongoing,Completed'],
        ]);

        $newStatus = $request->input('status');

        if ($event->status === 'Completed') {
            return redirect()->back()
                ->with('error', 'This event is already completed and cannot be changed.');
        }

        // No backward transitions
        $order = ['Pending' => 0, 'Ongoing' => 1, 'Completed' => 2];
        if ($order[$newStatus] <= $order[$event->status]) {
            return redirect()->back()
                ->with('error', 'Invalid status transition.');
        }

        // Only admin can mark as Completed
        if ($newStatus === 'Completed' && auth()->user()->role !== 'admin') {
            return redirect()->back()
                ->with('error', 'Only admin can mark an event as Completed.');
        }

        // Event can only start after admin list approval.
        if ($newStatus === 'Ongoing' && ! $event->isBeneficiaryListApproved()) {
            return redirect()->back()
                ->with('error', 'Approve the beneficiary list before starting this event.');
        }

        if ($newStatus === 'Completed' && ! $event->hasAllBeneficiariesMarked()) {
            $remainingUnmarked = $event->unmarkedAllocationsCount();

            return redirect()->back()
                ->with('error', "Event cannot be marked as Completed until all beneficiaries are marked as Released or Not Received. Remaining unmarked beneficiaries: {$remainingUnmarked}.");
        }

        if ($newStatus === 'Completed' && $event->isFinancial()) {
            $completionIssues = $this->getCompletionComplianceIssues($event);

            if (! empty($completionIssues)) {
                return redirect()->back()
                    ->with('error', 'Financial event completion is blocked until critical compliance items are resolved: '.implode('; ', $completionIssues));
            }
        }

        DB::transaction(function () use ($event, $newStatus) {
            $oldValues = $event->toArray();

            $event->update(['status' => $newStatus]);

            $this->audit->log(
                auth()->id(),
                'updated',
                'distribution_events',
                $event->id,
                $oldValues,
                $event->fresh()->toArray(),
            );
        });

        $response = redirect()->back()
            ->with('success', "Distribution event status updated to {$newStatus}.");

        if ($newStatus === 'Ongoing' && $event->isFinancial()) {
            $warnings = $this->getOngoingComplianceWarnings($event->fresh());

            if (! empty($warnings)) {
                $response->with('warning', 'Compliance details can be completed later. Pending items: '.implode('; ', $warnings));
            }
        }

        return $response;
    }


    public function updateCompliance(Request $request, DistributionEvent $event): RedirectResponse
    {
        if ($event->status === 'Completed') {
            return redirect()->back()
                ->with('error', 'Compliance details cannot be updated after event completion.');
        }

        $request->merge([
            'requires_farmc_endorsement' => $request->boolean('requires_farmc_endorsement'),
        ]);

        $statusRules = [];
        $reasonRules = [];
        foreach (DistributionEvent::COMPLIANCE_TRACKED_FIELDS as $field) {
            $statusRules["compliance_states.{$field}"] = ['nullable', 'in:'.implode(',', DistributionEvent::complianceStatuses())];
            $reasonRules["compliance_reasons.{$field}"] = ['nullable', 'string', 'max:500'];
        }

        $validated = $request->validate(
            $event->isFinancial()
                ? [
                    'legal_basis_type' => ['nullable', 'in:resolution,ordinance,memo,special_order,other'],
                    'legal_basis_reference_no' => ['nullable', 'string', 'max:150'],
                    'legal_basis_date' => ['nullable', 'date', 'before_or_equal:today'],
                    'legal_basis_remarks' => ['nullable', 'string', 'max:1000'],
                    'fund_source' => ['nullable', 'in:lgu_trust_fund,nga_transfer,local_program,other'],
                    'trust_account_code' => ['nullable', 'string', 'max:100'],
                    'fund_release_reference' => ['nullable', 'string', 'max:150'],
                    'liquidation_status' => ['nullable', 'in:not_required,pending,submitted,verified'],
                    'liquidation_due_date' => ['nullable', 'date'],
                    'liquidation_submitted_at' => ['nullable', 'date', 'before_or_equal:today'],
                    'liquidation_reference_no' => ['nullable', 'string', 'max:150'],
                    'requires_farmc_endorsement' => ['nullable', 'boolean'],
                    'farmc_endorsed_at' => ['nullable', 'date'],
                    'farmc_reference_no' => ['nullable', 'string', 'max:150'],
                    'compliance_states' => ['nullable', 'array'],
                    'compliance_reasons' => ['nullable', 'array'],
                    'compliance_overall_status' => ['nullable', 'in:'.implode(',', DistributionEvent::complianceStatuses())],
                    'compliance_overall_reason' => ['nullable', 'string', 'max:500'],
                    ...$statusRules,
                    ...$reasonRules,
                ]
                : [
                    'legal_basis_type' => ['nullable'],
                    'legal_basis_reference_no' => ['nullable'],
                    'legal_basis_date' => ['nullable'],
                    'legal_basis_remarks' => ['nullable'],
                    'fund_source' => ['nullable'],
                    'trust_account_code' => ['nullable'],
                    'fund_release_reference' => ['nullable'],
                    'liquidation_status' => ['nullable'],
                    'liquidation_due_date' => ['nullable'],
                    'liquidation_submitted_at' => ['nullable'],
                    'liquidation_reference_no' => ['nullable'],
                    'requires_farmc_endorsement' => ['nullable', 'boolean'],
                    'farmc_endorsed_at' => ['nullable'],
                    'farmc_reference_no' => ['nullable'],
                    'compliance_states' => ['nullable', 'array'],
                    'compliance_reasons' => ['nullable', 'array'],
                    'compliance_overall_status' => ['nullable', 'in:'.implode(',', DistributionEvent::complianceStatuses())],
                    'compliance_overall_reason' => ['nullable', 'string', 'max:500'],
                    ...$statusRules,
                    ...$reasonRules,
                ]
        );

        $validated['requires_farmc_endorsement'] = $request->boolean('requires_farmc_endorsement');

        $overallStatus = (string) $request->input('compliance_overall_status', '');
        $overallReason = isset($request['compliance_overall_reason']) ? trim((string) $request->input('compliance_overall_reason')) : null;

        if (
            in_array($overallStatus, DistributionEvent::complianceStatuses(), true)
            && $overallStatus !== DistributionEvent::COMPLIANCE_STATUS_PROVIDED
            && $overallReason === ''
        ) {
            throw ValidationException::withMessages([
                'compliance_overall_reason' => 'Provide a reason when general compliance status is not marked as Provided.',
            ]);
        }

        $expandedInputs = $this->expandComplianceStateInputs(
            (array) $request->input('compliance_states', []),
            (array) $request->input('compliance_reasons', []),
            $overallStatus,
            $overallReason,
        );

        $inputStates = $expandedInputs['states'];
        $inputReasons = $expandedInputs['reasons'];

        unset(
            $validated['compliance_states'],
            $validated['compliance_reasons'],
            $validated['compliance_overall_status'],
            $validated['compliance_overall_reason'],
        );

        if ($this->hasComplianceFieldStatesColumn()) {
            $normalizedStates = $this->normalizeComplianceFieldStates(
                $validated,
                is_array($event->compliance_field_states) ? $event->compliance_field_states : [],
                $inputStates,
                $inputReasons,
            );

            $reasonErrors = $this->validateComplianceReasonRequirements($normalizedStates, array_keys($inputStates));
            if (! empty($reasonErrors)) {
                throw ValidationException::withMessages($reasonErrors);
            }

            $validated['compliance_field_states'] = $normalizedStates;
        }

        DB::transaction(function () use ($event, $validated) {
            $oldValues = $event->toArray();

            $event->update($validated);

            $this->audit->log(
                auth()->id(),
                'updated',
                'distribution_events',
                $event->id,
                $oldValues,
                $event->fresh()->toArray(),
            );
        });

        return redirect()->back()->with('success', 'Compliance details updated successfully.');
    }

    /**
     * @param  array<string, mixed>  $validatedFields
     * @param  array<string, mixed>  $existingStates
     * @param  array<string, mixed>  $inputStates
     * @param  array<string, mixed>  $inputReasons
     * @return array<string, array{status: string, reason: ?string}>
     */
    private function normalizeComplianceFieldStates(
        array $validatedFields,
        array $existingStates,
        array $inputStates,
        array $inputReasons,
    ): array {
        $normalized = [];

        foreach (DistributionEvent::COMPLIANCE_TRACKED_FIELDS as $field) {
            $existingStatus = (string) data_get($existingStates, "{$field}.status", '');
            $existingReason = trim((string) data_get($existingStates, "{$field}.reason", ''));

            $value = $validatedFields[$field] ?? null;
            $hasValue = $this->hasComplianceFieldValue($value);

            $status = (string) ($inputStates[$field] ?? '');
            if (! in_array($status, DistributionEvent::complianceStatuses(), true)) {
                if (in_array($existingStatus, DistributionEvent::complianceStatuses(), true)) {
                    $status = $existingStatus;
                } else {
                    $status = $hasValue
                        ? DistributionEvent::COMPLIANCE_STATUS_PROVIDED
                        : DistributionEvent::COMPLIANCE_STATUS_NOT_AVAILABLE_YET;
                }
            }

            $reason = isset($inputReasons[$field])
                ? trim((string) $inputReasons[$field])
                : $existingReason;

            if ($status === DistributionEvent::COMPLIANCE_STATUS_PROVIDED || $reason === '') {
                $reason = $status === DistributionEvent::COMPLIANCE_STATUS_PROVIDED ? null : $reason;
            }

            $normalized[$field] = [
                'status' => $status,
                'reason' => $reason,
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $inputStates
     * @param  array<string, mixed>  $inputReasons
     * @return array{states: array<string, string>, reasons: array<string, string>}
     */
    private function expandComplianceStateInputs(
        array $inputStates,
        array $inputReasons,
        string $overallStatus,
        ?string $overallReason,
    ): array {
        $overallStatus = trim($overallStatus);
        $overallReason = $overallReason !== null ? trim($overallReason) : null;

        if (! in_array($overallStatus, DistributionEvent::complianceStatuses(), true)) {
            return [
                'states' => $inputStates,
                'reasons' => $inputReasons,
            ];
        }

        foreach (DistributionEvent::COMPLIANCE_TRACKED_FIELDS as $field) {
            $inputStates[$field] = $overallStatus;
            if ($overallStatus === DistributionEvent::COMPLIANCE_STATUS_PROVIDED) {
                $inputReasons[$field] = '';
            } elseif ($overallReason !== null) {
                $inputReasons[$field] = $overallReason;
            }
        }

        return [
            'states' => $inputStates,
            'reasons' => $inputReasons,
        ];
    }

    /**
     * @param  array<string, array{status: string, reason: ?string}>  $states
     * @param  array<int, string>  $explicitStatusFields
     * @return array<string, string>
     */
    private function validateComplianceReasonRequirements(array $states, array $explicitStatusFields): array
    {
        $errors = [];
        $explicitLookup = array_flip($explicitStatusFields);

        foreach ($states as $field => $state) {
            if (! isset($explicitLookup[$field])) {
                continue;
            }

            $status = $state['status'] ?? DistributionEvent::COMPLIANCE_STATUS_NOT_AVAILABLE_YET;
            $reason = trim((string) ($state['reason'] ?? ''));

            if ($status !== DistributionEvent::COMPLIANCE_STATUS_PROVIDED && $reason === '') {
                $errors["compliance_reasons.{$field}"] = 'Provide a reason when this field is not marked as Provided.';
            }
        }

        return $errors;
    }

    /**
     * @return array<int, string>
     */
    private function getCompletionComplianceIssues(DistributionEvent $event): array
    {
        $states = $event->complianceStates();

        $criticalFields = [
            'legal_basis_type',
            'legal_basis_reference_no',
            'legal_basis_date',
            'fund_source',
            'liquidation_status',
        ];

        if (($event->fund_source ?? '') === 'lgu_trust_fund') {
            $criticalFields[] = 'trust_account_code';
        }

        if (in_array($event->liquidation_status, ['pending', 'submitted', 'verified'], true)) {
            $criticalFields[] = 'liquidation_due_date';
        }

        if (in_array($event->liquidation_status, ['submitted', 'verified'], true)) {
            $criticalFields[] = 'liquidation_submitted_at';
            $criticalFields[] = 'liquidation_reference_no';
        }

        if ((bool) $event->requires_farmc_endorsement) {
            $criticalFields[] = 'farmc_reference_no';
        }

        $issues = [];
        foreach (array_values(array_unique($criticalFields)) as $field) {
            $state = $states[$field] ?? [
                'status' => DistributionEvent::COMPLIANCE_STATUS_NOT_AVAILABLE_YET,
                'reason' => null,
            ];

            $status = (string) ($state['status'] ?? DistributionEvent::COMPLIANCE_STATUS_NOT_AVAILABLE_YET);
            $reason = trim((string) ($state['reason'] ?? ''));
            $hasValue = $this->hasComplianceFieldValue($event->getAttribute($field));

            if ($status === DistributionEvent::COMPLIANCE_STATUS_PROVIDED && ! $hasValue) {
                $issues[] = $this->complianceFieldLabel($field).' is marked as Provided but has no value.';
                continue;
            }

            if ($status === DistributionEvent::COMPLIANCE_STATUS_NOT_APPLICABLE) {
                if ($reason === '') {
                    $issues[] = $this->complianceFieldLabel($field).' must include a reason when marked Not applicable.';
                }
                continue;
            }

            if (! $hasValue) {
                $issues[] = $this->complianceFieldLabel($field).' is unresolved for completion.';
                continue;
            }

            if ($status !== DistributionEvent::COMPLIANCE_STATUS_PROVIDED && $reason === '') {
                $issues[] = $this->complianceFieldLabel($field).' must include a reason when not marked Provided.';
            }
        }

        if (! in_array($event->liquidation_status, ['verified'], true)) {
            $issues[] = 'Liquidation status must be Verified before completion.';
        }

        return array_values(array_unique($issues));
    }

    /**
     * @return array<int, string>
     */
    private function getOngoingComplianceWarnings(DistributionEvent $event): array
    {
        $warnings = [];
        $states = $event->complianceStates();

        foreach (['legal_basis_type', 'legal_basis_reference_no', 'legal_basis_date', 'fund_source', 'liquidation_status'] as $field) {
            $hasValue = $this->hasComplianceFieldValue($event->getAttribute($field));
            $status = (string) data_get($states, "{$field}.status", DistributionEvent::COMPLIANCE_STATUS_NOT_AVAILABLE_YET);

            if (! $hasValue || $status !== DistributionEvent::COMPLIANCE_STATUS_PROVIDED) {
                $warnings[] = $this->complianceFieldLabel($field);
            }
        }

        return array_values(array_unique($warnings));
    }

    private function complianceFieldLabel(string $field): string
    {
        return match ($field) {
            'legal_basis_type' => 'Legal basis type',
            'legal_basis_reference_no' => 'Legal basis reference number',
            'legal_basis_date' => 'Legal basis date',
            'fund_source' => 'Fund source',
            'trust_account_code' => 'Trust account code',
            'liquidation_status' => 'Liquidation status',
            'liquidation_due_date' => 'Liquidation due date',
            'liquidation_submitted_at' => 'Liquidation submitted at',
            'liquidation_reference_no' => 'Liquidation reference number',
            'farmc_reference_no' => 'FARMC reference number',
            default => str_replace('_', ' ', $field),
        };
    }

    private function hasComplianceFieldValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        return $value !== null;
    }

    private function hasComplianceFieldStatesColumn(): bool
    {
        if ($this->hasComplianceFieldStatesColumn !== null) {
            return $this->hasComplianceFieldStatesColumn;
        }

        $this->hasComplianceFieldStatesColumn = Schema::hasColumn('distribution_events', 'compliance_field_states');

        return $this->hasComplianceFieldStatesColumn;
    }

    public function approveBeneficiaryList(DistributionEvent $event): RedirectResponse
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Only admin can approve the beneficiary list.');
        }

        if ($event->status !== 'Pending') {
            return redirect()->back()
                ->with('error', 'Beneficiary list approval is only allowed while event is Pending.');
        }

        if ($event->isBeneficiaryListApproved()) {
            return redirect()->back()->with('info', 'Beneficiary list has already been approved.');
        }

        DB::transaction(function () use ($event) {
            $oldValues = $event->toArray();

            $event->update([
                'beneficiary_list_approved_at' => now(),
                'beneficiary_list_approved_by' => auth()->id(),
            ]);

            $this->audit->log(
                auth()->id(),
                'updated',
                'distribution_events',
                $event->id,
                $oldValues,
                $event->fresh()->toArray(),
            );
        });

        return redirect()->back()->with('success', 'Beneficiary list approved. You can now start the event.');
    }
}

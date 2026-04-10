<?php

namespace App\Http\Controllers;

use App\Http\Requests\DistributionEventRequest;
use App\Models\AssistancePurpose;
use App\Models\Barangay;
use App\Models\DistributionEvent;
use App\Models\ProgramName;
use App\Models\ResourceType;
use App\Services\AuditLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DistributionEventController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
    ) {}

    public function index(Request $request): View
    {
        $events = DistributionEvent::with(['barangay', 'resourceType.agency', 'programName', 'createdBy'])
            ->withCount('allocations')
            ->when($request->filled('barangay_id'), fn ($q) => $q->where('barangay_id', $request->barangay_id))
            ->when($request->filled('resource_type_id'), fn ($q) => $q->where('resource_type_id', $request->resource_type_id))
            ->when($request->filled('program_name_id'), fn ($q) => $q->where('program_name_id', $request->program_name_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('distribution_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('distribution_date', '<=', $request->to))
            ->orderByDesc('distribution_date')
            ->paginate(15)
            ->withQueryString();

        $total = DistributionEvent::count();
        $pending = DistributionEvent::where('status', 'Pending')->count();
        $ongoing = DistributionEvent::where('status', 'Ongoing')->count();
        $completed = DistributionEvent::where('status', 'Completed')->count();

        $totalFinancialEvents = DistributionEvent::where('type', 'financial')->count();
        $totalCashDisbursed = DB::table('allocations')
            ->join('distribution_events', function ($join) {
                $join->on('allocations.distribution_event_id', '=', 'distribution_events.id')
                    ->where('distribution_events.type', '=', 'financial')
                    ->where('distribution_events.status', '=', 'Completed');
            })
            ->whereNull('allocations.deleted_at')
            ->sum('allocations.amount');

        $barangays = Barangay::orderBy('name')->get();
        $resourceTypes = ResourceType::with('agency')->orderBy('name')->get();
        $programNames = ProgramName::with('agency')->active()->orderBy('name')->get();

        return view('distribution_events.index', compact(
            'events',
            'total',
            'pending',
            'ongoing',
            'completed',
            'totalFinancialEvents',
            'totalCashDisbursed',
            'barangays',
            'resourceTypes',
            'programNames',
        ));
    }

    public function create(): View
    {
        $barangays = Barangay::orderBy('name')->get();
        $resourceTypes = ResourceType::with('agency')->orderBy('name')->get();
        $programNames = ProgramName::with('agency')->active()->orderBy('name')->get();

        return view('distribution_events.create', compact('barangays', 'resourceTypes', 'programNames'));
    }

    public function store(DistributionEventRequest $request): RedirectResponse|JsonResponse
    {
        $event = DB::transaction(function () use ($request) {
            $event = DistributionEvent::create([
                ...$request->validated(),
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
        ]);

        $allocatedBeneficiaryIds = $event->allocations->pluck('beneficiary_id')->toArray();
        $assistancePurposes = AssistancePurpose::active()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('distribution_events.show', compact('event', 'allocatedBeneficiaryIds', 'assistancePurposes'));
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
        $resourceTypes = ResourceType::with('agency')->orderBy('name')->get();
        $programNames = ProgramName::with('agency')->active()->orderBy('name')->get();

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

            $event->update($request->validated());

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

        // Financial events require legal basis and fund source before start.
        if ($newStatus === 'Ongoing' && $event->isFinancial()) {
            if (! $event->hasLegalBasis()) {
                return redirect()->back()
                    ->with('error', 'Financial events require legal basis type, reference number, and date before starting.');
            }

            if (! $event->hasFundSource()) {
                return redirect()->back()
                    ->with('error', 'Financial events require a fund source before starting.');
            }

            if (! $event->isFarmcCompliant()) {
                return redirect()->back()
                    ->with('error', 'FARMC endorsement is required before starting this event.');
            }
        }

        // Financial events must be fully liquidated before completion.
        if ($newStatus === 'Completed' && $event->isFinancial() && ! $event->isLiquidationVerified()) {
            return redirect()->back()
                ->with('error', 'Financial events can only be completed after liquidation status is set to Verified.');
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

        return redirect()->back()
            ->with('success', "Distribution event status updated to {$newStatus}.");
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

        $validated = $request->validate(
            $event->isFinancial()
                ? [
                    'legal_basis_type' => ['required', 'in:resolution,ordinance,memo,special_order,other'],
                    'legal_basis_reference_no' => ['required', 'string', 'max:150'],
                    'legal_basis_date' => ['required', 'date', 'before_or_equal:today'],
                    'legal_basis_remarks' => ['required_if:legal_basis_type,other', 'nullable', 'string', 'max:1000'],
                    'fund_source' => ['required', 'in:lgu_trust_fund,nga_transfer,local_program,other'],
                    'trust_account_code' => ['required_if:fund_source,lgu_trust_fund', 'nullable', 'string', 'max:100'],
                    'fund_release_reference' => ['nullable', 'string', 'max:150'],
                    'liquidation_status' => ['required', 'in:not_required,pending,submitted,verified'],
                    'liquidation_due_date' => ['required_if:liquidation_status,pending,submitted,verified', 'nullable', 'date'],
                    'liquidation_submitted_at' => ['required_if:liquidation_status,submitted,verified', 'nullable', 'date', 'before_or_equal:today'],
                    'liquidation_reference_no' => ['required_if:liquidation_status,submitted,verified', 'nullable', 'string', 'max:150'],
                    'requires_farmc_endorsement' => ['nullable', 'boolean'],
                    'farmc_endorsed_at' => ['nullable', 'date'],
                    'farmc_reference_no' => ['required_if:requires_farmc_endorsement,1', 'nullable', 'string', 'max:150'],
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
                ]
        );

        $validated['requires_farmc_endorsement'] = $request->boolean('requires_farmc_endorsement');

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

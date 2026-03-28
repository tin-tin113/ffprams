<?php

namespace App\Http\Controllers;

use App\Http\Requests\AllocationRequest;
use App\Models\Allocation;
use App\Models\AssistancePurpose;
use App\Models\Beneficiary;
use App\Models\DistributionEvent;
use App\Models\ProgramName;
use App\Models\ResourceType;
use App\Services\AuditLogService;
use App\Services\SemaphoreService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AllocationController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
        private SemaphoreService $sms,
    ) {}

    public function index(): View
    {
        $beneficiaries = Beneficiary::with('barangay')
            ->where('status', 'Active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'barangay_id']);

        $programNames = ProgramName::with('agency')->active()->orderBy('name')->get();
        $resourceTypes = ResourceType::with('agency')->orderBy('name')->get();
        $assistancePurposes = AssistancePurpose::active()->orderBy('name')->get();

        $directAllocations = Allocation::with([
            'beneficiary',
            'programName',
            'resourceType',
            'assistancePurpose',
        ])
            ->where('release_method', 'direct')
            ->latest()
            ->take(30)
            ->get();

        return view('allocations.index', compact(
            'beneficiaries',
            'programNames',
            'resourceTypes',
            'assistancePurposes',
            'directAllocations',
        ));
    }

    public function store(AllocationRequest $request): RedirectResponse
    {
        $beneficiary = Beneficiary::findOrFail($request->beneficiary_id);
        $releaseMethod = $request->input('release_method', 'event');

        if ($releaseMethod === 'event') {
            $event = DistributionEvent::with(['barangay', 'resourceType'])->findOrFail($request->distribution_event_id);

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

            $allocation = DB::transaction(function () use ($request, $event, $beneficiary) {
                $allocation = Allocation::create([
                    'release_method'        => 'event',
                    'distribution_event_id' => $event->id,
                    'beneficiary_id'        => $beneficiary->id,
                    'program_name_id'       => $event->program_name_id,
                    'resource_type_id'      => $event->resource_type_id,
                    'quantity'              => $event->isFinancial() ? null : $request->quantity,
                    'amount'                => $event->isFinancial() ? $request->amount : null,
                    'assistance_purpose_id' => $request->assistance_purpose_id,
                    'remarks'               => $request->remarks,
                ]);

                $this->audit->log(
                    auth()->id(),
                    'created',
                    'allocations',
                    $allocation->id,
                    [],
                    $allocation->toArray(),
                );

                return $allocation;
            });

            // SMS notification (outside transaction -- non-critical)
            if ($beneficiary->contact_number) {
                if ($event->isFinancial()) {
                    $smsMessage = "Hello {$beneficiary->full_name}, you have been allocated PHP {$allocation->amount} for {$event->resourceType->name} scheduled on {$event->distribution_date->format('M d, Y')} at Barangay {$event->barangay->name}. Please coordinate with your barangay official for claiming details.";
                } else {
                    $smsMessage = "Hello {$beneficiary->full_name}, you have been allocated {$allocation->quantity} {$event->resourceType->unit} of {$event->resourceType->name} scheduled on {$event->distribution_date->format('M d, Y')} at Barangay {$event->barangay->name}. Please coordinate with your barangay official for details.";
                }

                $this->sms->sendSms(
                    $beneficiary->contact_number,
                    $smsMessage,
                    $beneficiary->id,
                );
            }

            return redirect()->route('distribution-events.show', $event)
                ->with('success', 'Beneficiary allocated successfully.');
        }

        $resourceType = ResourceType::with('agency')->findOrFail($request->resource_type_id);

        $allocation = DB::transaction(function () use ($request, $beneficiary, $resourceType) {
            $isFinancial = $resourceType->unit === 'PHP';

            $allocation = Allocation::create([
                'release_method'        => 'direct',
                'distribution_event_id' => null,
                'beneficiary_id'        => $beneficiary->id,
                'program_name_id'       => $request->program_name_id,
                'resource_type_id'      => $resourceType->id,
                'quantity'              => $isFinancial ? null : $request->quantity,
                'amount'                => $isFinancial ? $request->amount : null,
                'assistance_purpose_id' => $request->assistance_purpose_id,
                'remarks'               => $request->remarks,
            ]);

            $this->audit->log(
                auth()->id(),
                'created',
                'allocations',
                $allocation->id,
                [],
                $allocation->toArray(),
            );

            return $allocation;
        });

        if ($beneficiary->contact_number) {
            $value = $resourceType->unit === 'PHP'
                ? ('PHP ' . number_format((float) $allocation->amount, 2))
                : (number_format((float) $allocation->quantity, 2) . ' ' . $resourceType->unit);

            $message = "Hello {$beneficiary->full_name}, you have been allocated {$value} of {$resourceType->name} as direct assistance. Please coordinate with the office for release details.";

            $this->sms->sendSms(
                $beneficiary->contact_number,
                $message,
                $beneficiary->id,
            );
        }

        return redirect()->route('allocations.index')
            ->with('success', 'Direct assistance allocation saved successfully.');
    }

    public function storeBulk(Request $request): RedirectResponse
    {
        $event = DistributionEvent::with(['barangay', 'resourceType'])->findOrFail($request->distribution_event_id);

        $bulkRules = [
            'distribution_event_id'        => ['required', 'exists:distribution_events,id'],
            'allocations'                  => ['required', 'array', 'min:1'],
            'allocations.*.beneficiary_id' => ['required', 'distinct', 'exists:beneficiaries,id'],
            'allocations.*.assistance_purpose_id' => ['nullable', 'exists:assistance_purposes,id'],
            'allocations.*.remarks'        => ['nullable', 'string', 'max:500'],
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
        $skipped   = 0;
        $smsQueue  = [];

        DB::transaction(function () use ($request, $event, $existingIds, &$allocated, &$skipped, &$smsQueue) {
            $seenInRequest = [];

            foreach ($request->input('allocations') as $row) {
                $beneficiary = Beneficiary::find($row['beneficiary_id']);

                if (! $beneficiary || $beneficiary->barangay_id !== $event->barangay_id) {
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

                $allocation = Allocation::create([
                    'release_method'        => 'event',
                    'distribution_event_id' => $event->id,
                    'beneficiary_id'        => $beneficiary->id,
                    'program_name_id'       => $event->program_name_id,
                    'resource_type_id'      => $event->resource_type_id,
                    'quantity'              => $event->isFinancial() ? null : $row['quantity'],
                    'amount'                => $event->isFinancial() ? $row['amount'] : null,
                    'assistance_purpose_id' => $row['assistance_purpose_id'] ?? null,
                    'remarks'               => $row['remarks'] ?? null,
                ]);

                $seenInRequest[] = $beneficiary->id;

                $this->audit->log(
                    auth()->id(),
                    'created',
                    'allocations',
                    $allocation->id,
                    [],
                    $allocation->toArray(),
                );

                $smsQueue[] = [
                    'number'         => $beneficiary->contact_number,
                    'full_name'      => $beneficiary->full_name,
                    'quantity'       => $allocation->quantity,
                    'amount'         => $allocation->amount,
                    'beneficiary_id' => $beneficiary->id,
                ];

                $allocated++;
            }
        });

        // SMS notifications (outside transaction -- non-critical)
        foreach ($smsQueue as $sms) {
            if (! $sms['number']) {
                continue;
            }

            if ($event->isFinancial()) {
                $message = "Hello {$sms['full_name']}, you have been allocated PHP {$sms['amount']} for {$event->resourceType->name} scheduled on {$event->distribution_date->format('M d, Y')} at Barangay {$event->barangay->name}. Please coordinate with your barangay official for claiming details.";
            } else {
                $message = "Hello {$sms['full_name']}, you have been allocated {$sms['quantity']} {$event->resourceType->unit} of {$event->resourceType->name} scheduled on {$event->distribution_date->format('M d, Y')} at Barangay {$event->barangay->name}. Please coordinate with your barangay official for details.";
            }

            $this->sms->sendSms(
                $sms['number'],
                $message,
                $sms['beneficiary_id'],
            );
        }

        return redirect()->route('distribution-events.show', $event)
            ->with('success', "{$allocated} allocated, {$skipped} skipped.");
    }

    public function update(Request $request, Allocation $allocation): RedirectResponse
    {
        $event = $allocation->distributionEvent;

        if (! $event) {
            return redirect()->route('allocations.index')
                ->with('error', 'Direct allocations can only be edited from the assistance allocation page.');
        }

        $rules = ['remarks' => ['nullable', 'string', 'max:500']];
        $rules['assistance_purpose_id'] = ['nullable', 'exists:assistance_purposes,id'];

        if ($event->isFinancial()) {
            $rules['amount']   = ['required', 'numeric', 'min:1', 'max:9999999999.99'];
            $rules['quantity'] = ['nullable'];
        } else {
            $rules['quantity'] = ['required', 'numeric', 'min:0.01', 'max:9999.99'];
            $rules['amount']   = ['nullable'];
        }

        $request->validate($rules);

        DB::transaction(function () use ($request, $allocation, $event) {
            $oldValues = $allocation->toArray();

            $allocation->update([
                'quantity' => $event->isFinancial() ? null : $request->quantity,
                'amount'   => $event->isFinancial() ? $request->amount : null,
                'assistance_purpose_id' => $request->assistance_purpose_id,
                'remarks'  => $request->remarks,
            ]);

            $this->audit->log(
                auth()->id(),
                'updated',
                'allocations',
                $allocation->id,
                $oldValues,
                $allocation->fresh()->toArray(),
            );
        });

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
                auth()->id(),
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

        if ($allocation->distributed_at || $allocation->release_outcome === 'not_received') {
            return redirect()->back()
            ->with('error', 'This allocation already has a final release outcome.');
        }

        DB::transaction(function () use ($allocation) {
            $oldValues = $allocation->toArray();

            $allocation->update([
                'distributed_at' => Carbon::now(),
                'release_outcome' => 'received',
            ]);

            $this->audit->log(
                auth()->id(),
                'updated',
                'allocations',
                $allocation->id,
                $oldValues,
                $allocation->fresh()->toArray(),
            );
        });

        return redirect()->back()
            ->with('success', 'Allocation marked as distributed.');
    }

    public function markNotReceived(Allocation $allocation): RedirectResponse
    {
        $event = $allocation->distributionEvent;

        if ($event && $event->status === 'Pending') {
            return redirect()->back()
                ->with('error', 'Cannot mark as not received while event is still Pending.');
        }

        if ($allocation->distributed_at || $allocation->release_outcome === 'not_received') {
            return redirect()->back()
                ->with('error', 'This allocation already has a final release outcome.');
        }

        DB::transaction(function () use ($allocation) {
            $oldValues = $allocation->toArray();

            $allocation->update([
                'distributed_at' => null,
                'release_outcome' => 'not_received',
            ]);

            $this->audit->log(
                auth()->id(),
                'updated',
                'allocations',
                $allocation->id,
                $oldValues,
                $allocation->fresh()->toArray(),
            );
        });

        return redirect()->back()
            ->with('success', 'Allocation marked as not received.');
    }
}

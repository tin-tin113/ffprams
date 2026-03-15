<?php

namespace App\Http\Controllers;

use App\Http\Requests\AllocationRequest;
use App\Models\Allocation;
use App\Models\Beneficiary;
use App\Models\DistributionEvent;
use App\Services\AuditLogService;
use App\Services\SemaphoreService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AllocationController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
        private SemaphoreService $sms,
    ) {}

    public function store(AllocationRequest $request): RedirectResponse
    {
        $event = DistributionEvent::with(['barangay', 'resourceType'])->findOrFail($request->distribution_event_id);

        $beneficiary = Beneficiary::findOrFail($request->beneficiary_id);

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

        $allocation = DB::transaction(function () use ($request, $event, $beneficiary) {
            $allocation = Allocation::create([
                'distribution_event_id' => $event->id,
                'beneficiary_id'        => $beneficiary->id,
                'quantity'              => $request->quantity,
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
        $this->sms->sendSms(
            $beneficiary->contact_number,
            "Hello {$beneficiary->full_name}, you have been allocated {$allocation->quantity} {$event->resourceType->unit} of {$event->resourceType->name} scheduled on {$event->distribution_date->format('M d, Y')} at Barangay {$event->barangay->name}. Please coordinate with your barangay official for details.",
            $beneficiary->id,
        );

        return redirect()->route('distribution-events.show', $event)
            ->with('success', 'Beneficiary allocated successfully.');
    }

    public function storeBulk(Request $request): RedirectResponse
    {
        $request->validate([
            'distribution_event_id'        => ['required', 'exists:distribution_events,id'],
            'allocations'                  => ['required', 'array', 'min:1'],
            'allocations.*.beneficiary_id' => ['required', 'exists:beneficiaries,id'],
            'allocations.*.quantity'       => ['required', 'numeric', 'min:0.01', 'max:9999.99'],
            'allocations.*.remarks'        => ['nullable', 'string', 'max:500'],
        ]);

        $event = DistributionEvent::with(['barangay', 'resourceType'])->findOrFail($request->distribution_event_id);

        $existingIds = Allocation::where('distribution_event_id', $event->id)
            ->pluck('beneficiary_id')
            ->toArray();

        $allocated = 0;
        $skipped   = 0;
        $smsQueue  = [];

        DB::transaction(function () use ($request, $event, $existingIds, &$allocated, &$skipped, &$smsQueue) {
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

                $allocation = Allocation::create([
                    'distribution_event_id' => $event->id,
                    'beneficiary_id'        => $beneficiary->id,
                    'quantity'              => $row['quantity'],
                    'remarks'               => $row['remarks'] ?? null,
                ]);

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
                    'beneficiary_id' => $beneficiary->id,
                ];

                $allocated++;
            }
        });

        // SMS notifications (outside transaction -- non-critical)
        foreach ($smsQueue as $sms) {
            $this->sms->sendSms(
                $sms['number'],
                "Hello {$sms['full_name']}, you have been allocated {$sms['quantity']} {$event->resourceType->unit} of {$event->resourceType->name} scheduled on {$event->distribution_date->format('M d, Y')} at Barangay {$event->barangay->name}. Please coordinate with your barangay official for details.",
                $sms['beneficiary_id'],
            );
        }

        return redirect()->route('distribution-events.show', $event)
            ->with('success', "{$allocated} allocated, {$skipped} skipped.");
    }

    public function update(Request $request, Allocation $allocation): RedirectResponse
    {
        $request->validate([
            'quantity' => ['required', 'numeric', 'min:0.01', 'max:9999.99'],
            'remarks'  => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $allocation) {
            $oldValues = $allocation->toArray();

            $allocation->update([
                'quantity' => $request->quantity,
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

        if ($event->status === 'Completed') {
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

        return redirect()->route('distribution-events.show', $event)
            ->with('success', 'Allocation removed successfully.');
    }

    public function markDistributed(Allocation $allocation): RedirectResponse
    {
        $event = $allocation->distributionEvent;

        if ($event->status === 'Pending') {
            return redirect()->back()
                ->with('error', 'Cannot mark as distributed while event is still Pending.');
        }

        if ($allocation->distributed_at) {
            return redirect()->back()
                ->with('error', 'This allocation has already been marked as distributed.');
        }

        DB::transaction(function () use ($allocation) {
            $oldValues = $allocation->toArray();

            $allocation->update(['distributed_at' => Carbon::now()]);

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
}

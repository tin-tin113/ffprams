<?php

namespace App\Http\Controllers;

use App\Http\Requests\DistributionEventRequest;
use App\Models\Barangay;
use App\Models\DistributionEvent;
use App\Models\ResourceType;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DistributionEventController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
    ) {}

    public function index(Request $request): View
    {
        $events = DistributionEvent::with(['barangay', 'resourceType', 'createdBy'])
            ->withCount('allocations')
            ->when($request->filled('barangay_id'), fn ($q) => $q->where('barangay_id', $request->barangay_id))
            ->when($request->filled('resource_type_id'), fn ($q) => $q->where('resource_type_id', $request->resource_type_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('distribution_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('distribution_date', '<=', $request->to))
            ->orderByDesc('distribution_date')
            ->paginate(15)
            ->withQueryString();

        $total     = DistributionEvent::count();
        $pending   = DistributionEvent::where('status', 'Pending')->count();
        $ongoing   = DistributionEvent::where('status', 'Ongoing')->count();
        $completed = DistributionEvent::where('status', 'Completed')->count();

        $barangays     = Barangay::orderBy('name')->get();
        $resourceTypes = ResourceType::orderBy('name')->get();

        return view('distribution_events.index', compact(
            'events',
            'total',
            'pending',
            'ongoing',
            'completed',
            'barangays',
            'resourceTypes',
        ));
    }

    public function create(): View
    {
        $barangays     = Barangay::orderBy('name')->get();
        $resourceTypes = ResourceType::orderBy('name')->get();

        return view('distribution_events.create', compact('barangays', 'resourceTypes'));
    }

    public function store(DistributionEventRequest $request): RedirectResponse
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

        return redirect()->route('distribution-events.show', $event)
            ->with('success', 'Distribution event created successfully.');
    }

    public function show(DistributionEvent $event): View
    {
        $event->load([
            'barangay',
            'resourceType',
            'createdBy',
            'allocations.beneficiary',
        ]);

        $allocatedBeneficiaryIds = $event->allocations->pluck('beneficiary_id')->toArray();

        return view('distribution_events.show', compact('event', 'allocatedBeneficiaryIds'));
    }

    public function edit(DistributionEvent $event): View|RedirectResponse
    {
        if ($event->status !== 'Pending') {
            return redirect()->back()
                ->with('error', 'Only Pending events can be edited.');
        }

        $barangays     = Barangay::orderBy('name')->get();
        $resourceTypes = ResourceType::orderBy('name')->get();

        return view('distribution_events.edit', compact('event', 'barangays', 'resourceTypes'));
    }

    public function update(DistributionEventRequest $request, DistributionEvent $event): RedirectResponse
    {
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

        return redirect()->route('distribution-events.index')
            ->with('success', 'Distribution event updated successfully.');
    }

    public function destroy(DistributionEvent $event): RedirectResponse
    {
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
}

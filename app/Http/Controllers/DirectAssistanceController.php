<?php

namespace App\Http\Controllers;

use App\Http\Requests\DirectAssistanceStoreRequest;
use App\Http\Requests\DirectAssistanceUpdateRequest;
use App\Models\Agency;
use App\Models\AssistancePurpose;
use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Models\DirectAssistance;
use App\Models\DistributionEvent;
use App\Models\ProgramName;
use App\Services\AuditLogService;
use App\Services\ProgramEligibilityService;
use App\Services\ReleaseOutcomeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DirectAssistanceController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
        private ReleaseOutcomeService $releaseOutcome,
    ) {}

    public function index(Request $request): View
    {
        $sort = (string) $request->input('sort', 'created_desc');
        $allowedSorts = ['created_desc', 'created_asc', 'program_asc', 'program_desc', 'status_asc', 'status_desc'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_desc';
        }

        $query = DirectAssistance::with([
            'beneficiary.barangay',
            'beneficiary.agency',
            'programName.agency',
            'resourceType',
            'assistancePurpose',
            'distributionEvent',
            'createdBy',
            'distributedBy',
        ]);

        // Filters
        if ($request->filled('barangay_id')) {
            $query->whereHas('beneficiary', fn ($q) => $q->where('barangay_id', $request->barangay_id));
        }

        if ($request->filled('agency_id')) {
            $query->whereHas('beneficiary', fn ($q) => $q->where('agency_id', $request->agency_id));
        }

        if ($request->filled('program_id')) {
            $query->where('program_name_id', $request->program_id);
        }

        if ($request->filled('status')) {
            $status = (string) $request->input('status');
            $allowedStatuses = ['planned', 'ready_for_release', 'released', 'not_received'];

            if (in_array($status, $allowedStatuses, true)) {
                $query->whereIn('status', DirectAssistance::statusesForFilter($status));
            }
        }

        $query
            ->when($sort === 'created_desc', fn ($q) => $q->latest())
            ->when($sort === 'created_asc', fn ($q) => $q->oldest())
            ->when($sort === 'program_asc', fn ($q) => $q->orderBy(
                ProgramName::select('name')->whereColumn('program_names.id', 'direct_assistance.program_name_id')
            ))
            ->when($sort === 'program_desc', fn ($q) => $q->orderByDesc(
                ProgramName::select('name')->whereColumn('program_names.id', 'direct_assistance.program_name_id')
            ))
            ->when($sort === 'status_asc', fn ($q) => $q->orderBy('status'))
            ->when($sort === 'status_desc', fn ($q) => $q->orderByDesc('status'));

        $directAssistance = $query->paginate(15)->withQueryString();

        // Load filter options
        $agencies = Agency::orderBy('name')->get();
        $programs = ProgramName::with('agency')->active()->orderBy('name')->get();

        // Summary stats
        $stats = [
            'planned' => DirectAssistance::whereStatusNormalized('planned')->count(),
            'ready_for_release' => DirectAssistance::whereStatusNormalized('ready_for_release')->count(),
            'released_today' => DirectAssistance::whereStatusNormalized('released')
                ->whereDate('distributed_at', today())
                ->count(),
            'this_month' => DirectAssistance::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return view('direct_assistance.index', compact(
            'directAssistance',
            'agencies',
            'programs',
            'stats',
        ));
    }

    public function create(): View
    {
        $beneficiaries = Beneficiary::where('status', 'Active')
            ->with('barangay', 'agency')
            ->orderBy('full_name')
            ->get();

        $distributionEvents = DistributionEvent::where('status', '!=', 'Cancelled')
            ->with('barangay', 'resourceType', 'programName')
            ->orderBy('distribution_date', 'desc')
            ->get();

        $assistancePurposes = AssistancePurpose::active()->orderBy('name')->get();

        return view('direct_assistance.create', compact(
            'beneficiaries',
            'distributionEvents',
            'assistancePurposes',
        ));
    }

    public function store(DirectAssistanceStoreRequest $request): RedirectResponse
    {
        $validated = $request->normalizedPayload();

        $beneficiary = Beneficiary::findOrFail($request->beneficiary_id);
        $program = ProgramName::findOrFail($request->program_name_id);

        // Verify eligibility
        if (! ProgramEligibilityService::isEligible($beneficiary, $program)) {
            return redirect()->back()
                ->with('error', 'Beneficiary is not eligible for this program. '.
                    ProgramEligibilityService::getIneligibilityReason($beneficiary, $program));
        }

        $directAssistance = DB::transaction(function () use ($validated) {
            $directAssistance = DirectAssistance::create([
                ...$validated,
                'created_by' => auth()->id(),
                'status' => 'planned',
            ]);

            $this->audit->log(
                auth()->id(),
                'created',
                'direct_assistance',
                $directAssistance->id,
                [],
                $directAssistance->toArray(),
            );

            return $directAssistance;
        });

        return redirect()->route('direct-assistance.index')
            ->with('success', 'Direct assistance recorded successfully.');
    }

    public function show(DirectAssistance $directAssistance): View
    {
        $directAssistance->load([
            'beneficiary.barangay',
            'beneficiary.agency',
            'programName.agency',
            'resourceType',
            'assistancePurpose',
            'distributionEvent.barangay',
            'createdBy',
            'distributedBy',
            'attachments' => fn ($q) => $q->latest('id')->with('uploader:id,name'),
        ]);

        return view('direct_assistance.show', compact('directAssistance'));
    }

    public function edit(DirectAssistance $directAssistance): View
    {
        $directAssistance->load([
            'beneficiary.barangay',
            'beneficiary.agency',
            'resourceType',
        ]);

        $beneficiaries = Beneficiary::where('status', 'Active')
            ->with('barangay', 'agency')
            ->orderBy('full_name')
            ->get();

        $programs = ProgramEligibilityService::getEligiblePrograms($directAssistance->beneficiary);

        $distributionEvents = DistributionEvent::where('status', '!=', 'Cancelled')
            ->with('barangay', 'resourceType', 'programName')
            ->orderBy('distribution_date', 'desc')
            ->get();

        $assistancePurposes = AssistancePurpose::active()->orderBy('name')->get();

        return view('direct_assistance.edit', compact(
            'directAssistance',
            'beneficiaries',
            'programs',
            'distributionEvents',
            'assistancePurposes',
        ));
    }

    public function update(DirectAssistanceUpdateRequest $request, DirectAssistance $directAssistance): RedirectResponse
    {
        $validated = $request->normalizedPayload();

        DB::transaction(function () use ($directAssistance, $validated) {
            $oldValues = $directAssistance->toArray();

            $directAssistance->update($validated);

            $this->audit->log(
                auth()->id(),
                'updated',
                'direct_assistance',
                $directAssistance->id,
                $oldValues,
                $directAssistance->fresh()->toArray(),
            );
        });

        return redirect()->route('direct-assistance.show', $directAssistance)
            ->with('success', 'Direct assistance updated successfully.');
    }

    public function destroy(DirectAssistance $directAssistance): RedirectResponse
    {
        DB::transaction(function () use ($directAssistance) {
            $oldValues = $directAssistance->toArray();

            $directAssistance->delete();

            $this->audit->log(
                auth()->id(),
                'deleted',
                'direct_assistance',
                $directAssistance->id,
                $oldValues,
            );
        });

        return redirect()->route('direct-assistance.index')
            ->with('success', 'Direct assistance record deleted successfully.');
    }

    public function markReadyForRelease(DirectAssistance $directAssistance): RedirectResponse
    {
        $currentStatus = $directAssistance->normalized_status;

        if ($currentStatus === 'ready_for_release') {
            return redirect()->back()
                ->with('warning', 'This record is already marked as Ready for Release.');
        }

        if ($currentStatus === 'released') {
            return redirect()->back()
                ->with('warning', 'Released records cannot be moved back to Ready for Release.');
        }

        $this->releaseOutcome->apply(
            $directAssistance,
            [
                'status' => 'ready_for_release',
                'release_outcome' => null,
                'distributed_at' => null,
                'distributed_by' => null,
            ],
            $this->audit,
            'marked_ready_for_release',
            'direct_assistance',
        );

        return redirect()->back()
            ->with('success', 'Direct assistance marked as Ready for Release.');
    }

    // Legacy route alias kept for backward compatibility.
    public function markDistributed(DirectAssistance $directAssistance): RedirectResponse
    {
        return $this->markReleased($directAssistance);
    }

    public function markReleased(DirectAssistance $directAssistance): RedirectResponse
    {
        $currentStatus = $directAssistance->normalized_status;

        if ($currentStatus === 'released') {
            return redirect()->back()
                ->with('warning', 'This record is already marked as released.');
        }

        if ($currentStatus !== 'ready_for_release') {
            return redirect()->back()
                ->with('warning', 'Set this record to Ready for Release before marking it as released.');
        }

        $this->releaseOutcome->apply(
            $directAssistance,
            [
                'distributed_at' => now(),
                'distributed_by' => auth()->id(),
                'status' => 'released',
                'release_outcome' => 'accepted',
            ],
            $this->audit,
            'marked_released',
            'direct_assistance',
        );

        return redirect()->back()
            ->with('success', 'Direct assistance marked as released.');
    }

    public function markNotReceived(DirectAssistance $directAssistance): RedirectResponse
    {
        $currentStatus = $directAssistance->normalized_status;

        if ($currentStatus === 'not_received') {
            return redirect()->back()
                ->with('warning', 'This record is already marked as Not Received.');
        }

        if ($currentStatus === 'released') {
            return redirect()->back()
                ->with('warning', 'Released records cannot be marked as Not Received.');
        }

        if ($currentStatus !== 'ready_for_release') {
            return redirect()->back()
                ->with('warning', 'Set this record to Ready for Release before marking it as Not Received.');
        }

        $this->releaseOutcome->apply(
            $directAssistance,
            [
                'status' => 'not_received',
                'release_outcome' => null,
                'distributed_at' => null,
                'distributed_by' => null,
            ],
            $this->audit,
            'marked_not_received',
            'direct_assistance',
        );

        return redirect()->back()
            ->with('success', 'Direct assistance marked as not received.');
    }

    /**
     * Get eligible programs for a beneficiary (API endpoint for dynamic form loading)
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
            Log::error('Error fetching eligible programs for direct assistance', [
                'beneficiary_id' => $beneficiary->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Unable to load programs. Please try again.',
            ], 500);
        }
    }

    /**
     * Get barangay-level analytics dashboard
     */
    public function barangayAnalytics(): View
    {
        $barangays = Barangay::with('beneficiaries')->orderBy('name')->get();

        $analytics = [];
        foreach ($barangays as $barangay) {
            $barangayRecords = DirectAssistance::whereHas('beneficiary', fn ($q) => $q->where('barangay_id', $barangay->id));

            $analytics[] = [
                'barangay' => $barangay,
                'total' => (clone $barangayRecords)->count(),
                'planned' => (clone $barangayRecords)->whereStatusNormalized('planned')->count(),
                'ready_for_release' => (clone $barangayRecords)->whereStatusNormalized('ready_for_release')->count(),
                'released' => (clone $barangayRecords)->whereStatusNormalized('released')->count(),
                'not_received' => (clone $barangayRecords)->whereStatusNormalized('not_received')->count(),
                'released_today' => (clone $barangayRecords)
                    ->whereStatusNormalized('released')
                    ->whereDate('distributed_at', today())
                    ->count(),
            ];
        }

        return view('direct_assistance.barangay_summary', compact('analytics'));
    }
}

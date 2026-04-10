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
use App\Services\SemaphoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DirectAssistanceController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
        private ReleaseOutcomeService $releaseOutcome,
        private SemaphoreService $sms,
    ) {}

    public function index(Request $request): View
    {
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
            $query->where('status', $request->status);
        }

        if ($request->filled('beneficiary_search')) {
            $search = $request->beneficiary_search;
            $query->whereHas('beneficiary', fn ($q) => $q->where('full_name', 'like', "%{$search}%")
                ->orWhere('contact_number', 'like', "%{$search}%")
            );
        }

        $directAssistance = $query->latest()->paginate(15);

        // Load filter options
        $barangays = Barangay::orderBy('name')->get();
        $agencies = Agency::orderBy('name')->get();
        $programs = ProgramName::with('agency')->active()->orderBy('name')->get();

        // Summary stats
        $stats = [
            'pending' => DirectAssistance::where('status', 'recorded')->count(),
            'distributed_today' => DirectAssistance::where('status', 'distributed')
                ->whereDate('distributed_at', today())
                ->count(),
            'this_month' => DirectAssistance::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return view('direct_assistance.index', compact(
            'directAssistance',
            'barangays',
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
                'status' => 'recorded',
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

        // SMS notification
        if ($beneficiary->contact_number) {
            $message = "Hello {$beneficiary->full_name}, direct assistance has been recorded for {$program->name}. Status: Pending distribution. Thank you!";
            $this->sms->sendSms(
                $beneficiary->contact_number,
                $message,
                $beneficiary->id,
            );
        }

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

    public function markDistributed(DirectAssistance $directAssistance): RedirectResponse
    {
        if ($directAssistance->status === 'distributed') {
            return redirect()->back()
                ->with('warning', 'This record is already marked as distributed.');
        }

        $this->releaseOutcome->apply(
            $directAssistance,
            [
                'distributed_at' => now(),
                'distributed_by' => auth()->id(),
                'status' => 'distributed',
            ],
            $this->audit,
            'marked_distributed',
            'direct_assistance',
        );

        // SMS notification with outcome
        $beneficiary = $directAssistance->beneficiary;
        if ($beneficiary->contact_number) {
            $message = "Hello {$beneficiary->full_name}, your direct assistance has been distributed. Release outcome: {$directAssistance->release_outcome}. Thank you!";
            $this->sms->sendSms(
                $beneficiary->contact_number,
                $message,
                $beneficiary->id,
            );
        }

        return redirect()->back()
            ->with('success', 'Direct assistance marked as distributed.');
    }

    public function markNotReceived(DirectAssistance $directAssistance): RedirectResponse
    {
        $this->releaseOutcome->apply(
            $directAssistance,
            [
                'release_outcome' => 'not_received',
                'distributed_at' => null,
                'distributed_by' => null,
                'status' => 'recorded',
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
        $programs = ProgramEligibilityService::getEligiblePrograms($beneficiary);

        return response()->json($programs->toArray());
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
                'pending' => (clone $barangayRecords)->where('status', 'recorded')->count(),
                'distributed' => (clone $barangayRecords)->where('status', 'distributed')->count(),
                'completed' => (clone $barangayRecords)->where('status', 'completed')->count(),
                'distributed_today' => (clone $barangayRecords)
                    ->where('status', 'distributed')
                    ->whereDate('distributed_at', today())
                    ->count(),
            ];
        }

        return view('direct_assistance.barangay_summary', compact('analytics'));
    }
}

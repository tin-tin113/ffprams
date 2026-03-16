<?php

namespace App\Http\Controllers;

use App\Http\Requests\FieldAssessmentRequest;
use App\Models\AssistancePurpose;
use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Models\FieldAssessment;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\SemaphoreService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FieldAssessmentController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
        private SemaphoreService $sms,
    ) {}

    public function index(Request $request): View
    {
        $query = FieldAssessment::with(['beneficiary.barangay', 'assessedBy', 'recommendedPurpose']);

        // Filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('beneficiary', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('government_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('barangay_id')) {
            $query->whereHas('beneficiary', function ($q) use ($request) {
                $q->where('barangay_id', $request->input('barangay_id'));
            });
        }

        if ($request->filled('eligibility_status')) {
            $query->where('eligibility_status', $request->input('eligibility_status'));
        }

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->input('approval_status'));
        }

        if ($request->filled('assessed_by')) {
            $query->where('assessed_by', $request->input('assessed_by'));
        }

        if ($request->filled('date_from')) {
            $query->where('visit_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('visit_date', '<=', $request->input('date_to'));
        }

        $assessments = $query->orderBy('visit_date', 'desc')->paginate(15)->withQueryString();

        // Summary counts
        $summary = [
            'total'               => FieldAssessment::count(),
            'pending_eligibility' => FieldAssessment::where('eligibility_status', 'pending')->count(),
            'eligible'            => FieldAssessment::where('eligibility_status', 'eligible')->count(),
            'not_eligible'        => FieldAssessment::where('eligibility_status', 'not_eligible')->count(),
            'pending_approval'    => FieldAssessment::where('approval_status', 'pending')->count(),
            'approved'            => FieldAssessment::where('approval_status', 'approved')->count(),
            'rejected'            => FieldAssessment::where('approval_status', 'rejected')->count(),
        ];

        $barangays = Barangay::orderBy('name')->get();
        $assessors = User::whereIn('role', ['admin', 'staff'])->orderBy('name')->get();

        return view('field_assessments.index', compact('assessments', 'summary', 'barangays', 'assessors'));
    }

    public function create(): View
    {
        $beneficiaries = Beneficiary::where('status', 'Active')
            ->with('barangay')
            ->orderBy('full_name')
            ->get();

        $purposes = AssistancePurpose::active()
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return view('field_assessments.create', compact('beneficiaries', 'purposes'));
    }

    public function store(FieldAssessmentRequest $request): RedirectResponse
    {
        $assessment = DB::transaction(function () use ($request) {
            $assessment = FieldAssessment::create([
                ...$request->validated(),
                'assessed_by' => auth()->id(),
            ]);

            $this->audit->log(
                auth()->id(),
                'created',
                'field_assessments',
                $assessment->id,
                [],
                $assessment->toArray(),
            );

            return $assessment;
        });

        return redirect()->route('field-assessments.show', $assessment)
            ->with('success', 'Field assessment created successfully.');
    }

    public function show(FieldAssessment $fieldAssessment): View
    {
        $fieldAssessment->load([
            'beneficiary.barangay',
            'assessedBy',
            'approvedBy',
            'recommendedPurpose',
            'allocations.distributionEvent',
        ]);

        return view('field-assessments.show', compact('fieldAssessment'));
    }

    public function edit(FieldAssessment $fieldAssessment): View|RedirectResponse
    {
        if ($fieldAssessment->approval_status !== 'pending') {
            return redirect()->back()
                ->with('error', 'This assessment has already been reviewed and cannot be edited.');
        }

        $beneficiaries = Beneficiary::where('status', 'Active')
            ->with('barangay')
            ->orderBy('full_name')
            ->get();

        $purposes = AssistancePurpose::active()
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return view('field_assessments.edit', compact('fieldAssessment', 'beneficiaries', 'purposes'));
    }

    public function update(FieldAssessmentRequest $request, FieldAssessment $fieldAssessment): RedirectResponse
    {
        if ($fieldAssessment->approval_status !== 'pending') {
            return redirect()->back()
                ->with('error', 'This assessment has already been reviewed and cannot be edited.');
        }

        DB::transaction(function () use ($request, $fieldAssessment) {
            $oldValues = $fieldAssessment->toArray();

            $fieldAssessment->update($request->validated());

            $this->audit->log(
                auth()->id(),
                'updated',
                'field_assessments',
                $fieldAssessment->id,
                $oldValues,
                $fieldAssessment->fresh()->toArray(),
            );
        });

        return redirect()->route('field-assessments.show', $fieldAssessment)
            ->with('success', 'Field assessment updated successfully.');
    }

    public function approve(Request $request, FieldAssessment $fieldAssessment): RedirectResponse
    {
        if ($fieldAssessment->approval_status !== 'pending') {
            return redirect()->back()
                ->with('error', 'This assessment has already been reviewed.');
        }

        $request->validate([
            'approval_notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $fieldAssessment) {
            $oldValues = $fieldAssessment->toArray();

            $fieldAssessment->update([
                'approval_status' => 'approved',
                'approved_by'     => auth()->id(),
                'approved_at'     => Carbon::now(),
                'approval_notes'  => $request->input('approval_notes'),
            ]);

            $this->audit->log(
                auth()->id(),
                'updated',
                'field_assessments',
                $fieldAssessment->id,
                $oldValues,
                $fieldAssessment->fresh()->toArray(),
            );
        });

        // SMS notification (outside transaction -- non-critical)
        $beneficiary = $fieldAssessment->beneficiary;

        if ($beneficiary->contact_number) {
            $purposeName = $fieldAssessment->recommendedPurpose?->name ?? 'financial';

            $smsMessage = "Hello {$beneficiary->full_name}, your financial assistance request has been approved by the Municipal Agriculture Office of Enrique B. Magalona. Please wait for further instructions regarding your {$purposeName} assistance. For inquiries contact the MAO office.";

            $this->sms->sendSms(
                $beneficiary->contact_number,
                $smsMessage,
                $beneficiary->id,
            );
        }

        return redirect()->route('field-assessments.show', $fieldAssessment)
            ->with('success', 'Field assessment has been approved.');
    }

    public function reject(Request $request, FieldAssessment $fieldAssessment): RedirectResponse
    {
        if ($fieldAssessment->approval_status !== 'pending') {
            return redirect()->back()
                ->with('error', 'This assessment has already been reviewed.');
        }

        $request->validate([
            'approval_notes' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $fieldAssessment) {
            $oldValues = $fieldAssessment->toArray();

            $fieldAssessment->update([
                'approval_status' => 'rejected',
                'approved_by'     => auth()->id(),
                'approved_at'     => Carbon::now(),
                'approval_notes'  => $request->input('approval_notes'),
            ]);

            $this->audit->log(
                auth()->id(),
                'updated',
                'field_assessments',
                $fieldAssessment->id,
                $oldValues,
                $fieldAssessment->fresh()->toArray(),
            );
        });

        return redirect()->route('field-assessments.show', $fieldAssessment)
            ->with('info', 'Field assessment has been rejected.');
    }
}

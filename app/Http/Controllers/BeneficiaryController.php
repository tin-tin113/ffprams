<?php

namespace App\Http\Controllers;

use App\Http\Requests\BeneficiaryRequest;
use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Services\AuditLogService;
use App\Services\SemaphoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BeneficiaryController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
        private SemaphoreService $sms,
    ) {}

    /**
     * Paginated list with filters and search.
     */
    public function index(Request $request): View
    {
        $beneficiaries = Beneficiary::with('barangay')
            ->when($request->filled('barangay_id'), fn ($q) => $q->where('barangay_id', $request->barangay_id))
            ->when($request->filled('classification'), fn ($q) => $q->where('classification', $request->classification))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('full_name', 'like', "%{$request->search}%")
                      ->orWhere('government_id', 'like', "%{$request->search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $barangays = Barangay::orderBy('name')->get();

        return view('beneficiaries.index', compact('beneficiaries', 'barangays'));
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        $barangays = Barangay::orderBy('name')->get();

        return view('beneficiaries.create', compact('barangays'));
    }

    /**
     * Store a new beneficiary.
     */
    public function store(BeneficiaryRequest $request): RedirectResponse
    {
        // Check duplicate government_id including soft-deleted records
        $duplicate = Beneficiary::withTrashed()
            ->where('government_id', $request->government_id)
            ->exists();

        if ($duplicate) {
            return back()->withInput()->withErrors([
                'government_id' => 'A beneficiary with this Government ID already exists.',
            ]);
        }

        $beneficiary = DB::transaction(function () use ($request) {
            $beneficiary = Beneficiary::create($request->validated());

            $this->audit->log(
                auth()->id(),
                'created',
                'beneficiaries',
                $beneficiary->id,
                [],
                $beneficiary->toArray(),
            );

            return $beneficiary;
        });

        // SMS notification (outside transaction — non-critical)
        $this->sms->sendSms(
            $beneficiary->contact_number,
            "Hello {$beneficiary->full_name}, you have been successfully registered as a {$beneficiary->classification} beneficiary of Enrique B. Magalona. For inquiries, contact the Municipal Agriculture Office.",
            $beneficiary->id,
        );

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
            'allocations.distributionEvent.resourceType',
            'smsLogs' => fn ($q) => $q->latest('sent_at')->limit(5),
        ]);

        return view('beneficiaries.show', compact('beneficiary'));
    }

    /**
     * Show the edit form.
     */
    public function edit(Beneficiary $beneficiary): View
    {
        $barangays = Barangay::orderBy('name')->get();

        return view('beneficiaries.edit', compact('beneficiary', 'barangays'));
    }

    /**
     * Update an existing beneficiary.
     */
    public function update(BeneficiaryRequest $request, Beneficiary $beneficiary): RedirectResponse
    {
        DB::transaction(function () use ($request, $beneficiary) {
            $oldValues = $beneficiary->toArray();

            $beneficiary->update($request->validated());

            $this->audit->log(
                auth()->id(),
                'updated',
                'beneficiaries',
                $beneficiary->id,
                $oldValues,
                $beneficiary->fresh()->toArray(),
            );
        });

        return redirect()->route('beneficiaries.index')
            ->with('success', 'Beneficiary updated successfully.');
    }

    /**
     * Soft delete a beneficiary.
     */
    public function destroy(Beneficiary $beneficiary): RedirectResponse
    {
        $beneficiary->delete();

        $this->audit->log(
            auth()->id(),
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
}

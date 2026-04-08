<?php

namespace App\Http\Controllers;

use App\Http\Requests\BeneficiaryRequest;
use App\Models\Agency;
use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Models\FormFieldOption;
use App\Services\AuditLogService;
use App\Services\DuplicateDetectionService;
use App\Services\SemaphoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BeneficiaryController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
        private SemaphoreService $sms,
        private DuplicateDetectionService $duplicateService,
    ) {}

    /**
     * Paginated list with filters and search.
     */
    public function index(Request $request): View
    {
        $beneficiaries = Beneficiary::with(['barangay', 'agency'])
            ->when($request->filled('barangay_id'), fn ($q) => $q->where('barangay_id', $request->barangay_id))
            ->when($request->filled('agency_id'), fn ($q) => $q->where('agency_id', $request->agency_id))
            ->when($request->filled('classification'), fn ($q) => $q->where('classification', $request->classification))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('full_name', 'like', "%{$request->search}%")
                      ->orWhere('rsbsa_number', 'like', "%{$request->search}%")
                      ->orWhere('fishr_number', 'like', "%{$request->search}%")
                      ->orWhere('cloa_ep_number', 'like', "%{$request->search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $barangays = Barangay::orderBy('name')->get();
        $agencies = Agency::active()->orderBy('name')->get();

        return view('beneficiaries.index', compact('beneficiaries', 'barangays', 'agencies'));
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        $barangays = Barangay::orderBy('name')->get();
        $agencies = Agency::active()->orderBy('name')->get();
        $fieldOptions = $this->getFormFieldOptions();

        return view('beneficiaries.create', compact('barangays', 'agencies', 'fieldOptions'));
    }

    /**
     * Store a new beneficiary.
     */
    public function store(BeneficiaryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Check for potential duplicates before creating - BLOCK if found
        $duplicates = $this->duplicateService->findPotentialDuplicates($validated);

        if ($duplicates->isNotEmpty()) {
            // Get the highest-scoring match
            $bestMatch = $duplicates->sortByDesc('score')->first();
            $existing = $bestMatch['beneficiary'];

            $message = "Registration blocked: A potential duplicate record already exists. ";
            $message .= "Existing beneficiary: {$existing->full_name}";
            if ($existing->barangay) {
                $message .= " (Barangay {$existing->barangay->name})";
            }
            $message .= ". Match type: {$bestMatch['match_type']}, Score: {$bestMatch['score']}%.";

            return redirect()->route('beneficiaries.show', $existing)
                ->with('warning', $message . ' Please verify and update this existing record if needed.');
        }

        // No duplicates - proceed with registration
        $beneficiary = DB::transaction(function () use ($validated) {
            $beneficiary = Beneficiary::create(array_merge($validated, ['status' => 'Active']));

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

        // Send SMS notification
        $agencyName = $beneficiary->agency?->name ?? 'government';
        $this->sms->sendSms(
            $beneficiary->contact_number,
            "Hello {$beneficiary->full_name}, you have been successfully registered as a {$beneficiary->classification} beneficiary under {$agencyName} in Enrique B. Magalona. For inquiries, contact the Municipal Agriculture Office.",
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
            'agency',
            'allocations.distributionEvent.resourceType.agency',
            'allocations.programName.agency',
            'allocations.resourceType.agency',
            'allocations.assistancePurpose',
            'directAssistance.programName.agency',
            'directAssistance.resourceType.agency',
            'directAssistance.assistancePurpose',
            'directAssistance.distributionEvent.barangay',
            'directAssistance.createdBy',
            'directAssistance.distributedBy',
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
        $agencies = Agency::active()->orderBy('name')->get();
        $fieldOptions = $this->getFormFieldOptions();

        return view('beneficiaries.edit', compact('beneficiary', 'barangays', 'agencies', 'fieldOptions'));
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
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Only admins can delete beneficiaries.');
        }

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

    public function summary(Beneficiary $beneficiary): JsonResponse
    {
        $beneficiary->load('barangay');

        return response()->json([
            'id'                        => $beneficiary->id,
            'first_name'                => $beneficiary->first_name,
            'middle_name'               => $beneficiary->middle_name,
            'last_name'                 => $beneficiary->last_name,
            'name_suffix'               => $beneficiary->name_suffix,
            'full_name'                 => $beneficiary->full_name,
            'barangay_name'             => $beneficiary->barangay->name ?? null,
            'classification'            => $beneficiary->classification,
            'contact_number'            => $beneficiary->contact_number,
            'rsbsa_number'              => $beneficiary->isFarmer() ? $beneficiary->rsbsa_number : null,
            'fishr_number'              => $beneficiary->isFisherfolk() ? $beneficiary->fishr_number : null,
        ]);
    }

    private function getFormFieldOptions(): array
    {
        $fields = [
            'farm_type',
            'farm_ownership',
            'fisherfolk_type',
            'civil_status',
            'highest_education',
            'id_type',
            'arb_classification',
            'ownership_scheme',
        ];
        $options = [];

        foreach ($fields as $field) {
            $dbOptions = FormFieldOption::optionsFor($field);

            // If no DB options, use reference document defaults
            if ($dbOptions->isEmpty()) {
                $options[$field] = $this->getDefaultOptions($field);
            } else {
                $options[$field] = $dbOptions;
            }
        }

        return $options;
    }

    private function getDefaultOptions(string $field): \Illuminate\Support\Collection
    {
        $defaults = [
            'farm_ownership' => ['Registered Owner', 'Tenant', 'Lessee'],
            'farm_type' => ['Irrigated', 'Rainfed Upland', 'Rainfed Lowland'],
            'fisherfolk_type' => ['Capture Fishing', 'Aquaculture', 'Post-Harvest'],
            'civil_status' => ['Single', 'Married', 'Widowed', 'Separated'],
            'highest_education' => [
                'No Formal Education',
                'Elementary',
                'High School',
                'Vocational',
                'College',
                'Post Graduate',
            ],
            'id_type' => [
                'PhilSys ID',
                "Voter's ID",
                "Driver's License",
                'Passport',
                'Senior Citizen ID',
                'PWD ID',
                'Postal ID',
                'TIN ID',
            ],
            'arb_classification' => [
                'Agricultural Lessee',
                'Regular Farmworker',
                'Seasonal Farmworker',
                'Other Farmworker',
                'Actual Tiller',
                'Collective/Cooperative',
                'Others',
            ],
            'ownership_scheme' => ['Individual', 'Collective', 'Cooperative'],
        ];

        $values = $defaults[$field] ?? [];

        return collect($values)->map(fn ($v) => (object) ['value' => $v, 'label' => $v]);
    }
}

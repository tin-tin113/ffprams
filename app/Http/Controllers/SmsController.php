<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Models\DistributionEvent;
use App\Models\ProgramName;
use App\Models\SmsLog;
use App\Services\AuditLogService;
use App\Services\SemaphoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SmsController extends Controller
{
    public function __construct(
        private SemaphoreService $sms,
        private AuditLogService $audit,
    ) {}

    public function index(Request $request): View
    {
        $barangays = Barangay::orderBy('name')->get();
        $programs = ProgramName::active()->orderBy('name')->get(['id', 'name']);
        $events = DistributionEvent::query()
            ->with(['programName:id,name', 'barangay:id,name'])
            ->whereIn('status', ['Pending', 'Ongoing'])
            ->orderByDesc('distribution_date')
            ->get(['id', 'program_name_id', 'barangay_id', 'distribution_date', 'status']);

        $smsLogs = SmsLog::with('beneficiary.barangay')
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->whereHas('beneficiary', fn ($b) => $b->where('full_name', 'like', "%{$request->search}%"));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('date_from'), fn ($q) => $q->where('sent_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->where('sent_at', '<=', $request->date_to.' 23:59:59'))
            ->orderByDesc('sent_at')
            ->paginate(15)
            ->withQueryString();

        $sendOnEventOngoing = Cache::get(
            'sms.send_on_event_ongoing',
            config('services.sms.send_on_event_ongoing')
        );

        $sendOnDirectAssistanceStatusChange = Cache::get(
            'sms.send_on_direct_assistance_status_change',
            config('services.sms.send_on_direct_assistance_status_change')
        );

        return view('sms.index', compact(
            'barangays',
            'programs',
            'events',
            'smsLogs',
            'sendOnEventOngoing',
            'sendOnDirectAssistanceStatusChange',
        ));
    }

    public function updateAutomationSettings(Request $request): RedirectResponse
    {
        $sendOnEventOngoing = $request->boolean('send_on_event_ongoing');
        $sendOnDirectAssistanceStatusChange = $request->boolean('send_on_direct_assistance_status_change');

        Cache::forever('sms.send_on_event_ongoing', $sendOnEventOngoing);
        Cache::forever('sms.send_on_direct_assistance_status_change', $sendOnDirectAssistanceStatusChange);

        $this->audit->log(
            auth()->id(),
            'updated',
            'sms_settings',
            0,
            [],
            [
                'send_on_event_ongoing' => $sendOnEventOngoing,
                'send_on_direct_assistance_status_change' => $sendOnDirectAssistanceStatusChange,
            ],
        );

        return redirect()->route('sms.index')
            ->with('success', 'SMS automation settings updated successfully.');
    }

    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'recipient_type' => ['required', Rule::in(['by_program', 'by_event', 'by_barangay', 'by_classification', 'selected'])],
            'program_name_id' => [
                'required_if:recipient_type,by_program',
                'nullable',
                Rule::exists('program_names', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'distribution_event_id' => [
                'required_if:recipient_type,by_event',
                'nullable',
                Rule::exists('distribution_events', 'id')->where(
                    fn ($query) => $query->whereIn('status', ['Pending', 'Ongoing'])
                ),
            ],
            'barangay_id' => ['required_if:recipient_type,by_barangay', 'nullable', 'exists:barangays,id'],
            'classification' => ['required_if:recipient_type,by_classification', 'nullable', Rule::in(['Farmer', 'Fisherfolk', 'Both'])],
            'beneficiary_ids' => ['required_if:recipient_type,selected', 'nullable', 'array', 'min:1'],
            'beneficiary_ids.*' => ['integer', 'exists:beneficiaries,id'],
        ]);

        $recipients = $this->resolveRecipients($request);

        return response()->json([
            'count' => $recipients->count(),
            'recipients' => $recipients->map(fn ($b) => [
                'id' => $b->id,
                'full_name' => $b->full_name,
                'barangay' => $b->barangay->name ?? null,
                'contact_number' => $b->contact_number,
                'classification' => $b->classification,
            ])->values(),
        ]);
    }

    public function beneficiaries(): JsonResponse
    {
        $beneficiaries = Beneficiary::with('barangay')
            ->where('status', 'Active')
            ->orderBy('full_name')
            ->get();

        return response()->json([
            'count' => $beneficiaries->count(),
            'recipients' => $beneficiaries->map(fn ($b) => [
                'id' => $b->id,
                'full_name' => $b->full_name,
                'barangay' => $b->barangay->name ?? null,
                'contact_number' => $b->contact_number,
                'classification' => $b->classification,
            ])->values(),
        ]);
    }

    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'min:5', 'max:160'],
            'recipient_type' => ['required', Rule::in(['by_program', 'by_event', 'by_barangay', 'by_classification', 'selected'])],
            'program_name_id' => [
                'required_if:recipient_type,by_program',
                'nullable',
                Rule::exists('program_names', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'distribution_event_id' => [
                'required_if:recipient_type,by_event',
                'nullable',
                Rule::exists('distribution_events', 'id')->where(
                    fn ($query) => $query->whereIn('status', ['Pending', 'Ongoing'])
                ),
            ],
            'barangay_id' => ['required_if:recipient_type,by_barangay', 'nullable', 'exists:barangays,id'],
            'classification' => ['required_if:recipient_type,by_classification', 'nullable', Rule::in(['Farmer', 'Fisherfolk', 'Both'])],
            'beneficiary_ids' => ['required_if:recipient_type,selected', 'nullable', 'array', 'min:1'],
            'beneficiary_ids.*' => ['integer', 'exists:beneficiaries,id'],
        ]);

        $recipients = $this->resolveRecipients($request);

        $sent = 0;
        $failed = 0;

        foreach ($recipients as $beneficiary) {
            if (empty($beneficiary->contact_number)) {
                $failed++;

                continue;
            }

            $result = $this->sms->sendSms(
                $beneficiary->contact_number,
                $request->message,
                $beneficiary->id,
            );

            $result ? $sent++ : $failed++;
        }

        $this->audit->log(
            auth()->id(),
            'created',
            'sms_broadcast',
            0,
            [],
            [
                'message' => $request->message,
                'recipient_count' => $recipients->count(),
                'recipient_type' => $request->recipient_type,
                'program_name_id' => $request->program_name_id,
                'distribution_event_id' => $request->distribution_event_id,
                'barangay_id' => $request->barangay_id,
                'classification' => $request->classification,
            ],
        );

        return response()->json([
            'sent' => $sent,
            'failed' => $failed,
            'total' => $recipients->count(),
        ]);
    }

    private function resolveRecipients(Request $request)
    {
        $query = Beneficiary::with('barangay')->where('status', 'Active');

        switch ($request->recipient_type) {
            case 'by_program':
                $query->where(function ($q) use ($request) {
                    $q->whereHas('allocations', fn ($a) => $a->where('program_name_id', $request->program_name_id))
                        ->orWhereHas('directAssistance', fn ($d) => $d->where('program_name_id', $request->program_name_id));
                });
                break;
            case 'by_event':
                $query->where(function ($q) use ($request) {
                    $q->whereHas('allocations', fn ($a) => $a->where('distribution_event_id', $request->distribution_event_id))
                        ->orWhereHas('directAssistance', fn ($d) => $d->where('distribution_event_id', $request->distribution_event_id));
                });
                break;
            case 'by_barangay':
                $query->where('barangay_id', $request->barangay_id);
                break;
            case 'by_classification':
                $query->where('classification', $request->classification);
                break;
            case 'selected':
                $ids = $request->beneficiary_ids ?? [];
                $query->whereIn('id', $ids);
                break;
            default:
                $query->whereRaw('1 = 0');
                break;
        }

        return $query->orderBy('full_name')->get();
    }

    /**
     * Handle SMS delivery callback from E5 SMS Gateway.
     * This is a webhook endpoint that receives delivery status updates.
     * Should be protected by API key or signature verification in production.
     */
    public function handleDeliveryCallback(Request $request): JsonResponse
    {
        // In production, verify the request signature/API key from gateway
        // For now, we accept the callback payload directly

        $payload = $request->all();

        $success = $this->sms->handleDeliveryCallback($payload);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Delivery status updated' : 'Failed to process callback',
        ]);
    }
}

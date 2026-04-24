<?php

namespace App\Http\Controllers;

use App\Models\AssistancePurpose;
use App\Models\Barangay;
use App\Models\Beneficiary;
use App\Models\DistributionEvent;
use App\Models\ProgramName;
use App\Models\ResourceType;
use App\Models\SmsLog;
use App\Models\SmsTemplate;
use App\Services\AuditLogService;
use App\Services\SemaphoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $programs = ProgramName::active()
            ->whereHas('agency', fn ($query) => $query->active())
            ->orderBy('name')
            ->get(['id', 'name']);
        $events = DistributionEvent::query()
            ->with(['programName:id,name', 'barangay:id,name'])
            ->whereIn('status', ['Pending', 'Ongoing'])
            ->orderByDesc('distribution_date')
            ->get(['id', 'program_name_id', 'barangay_id', 'distribution_date', 'status']);

        $resourceTypes = ResourceType::active()
            ->whereHas('agency', fn ($query) => $query->active())
            ->orderBy('name')
            ->get(['id', 'name']);

        // Load templates from database
        $templates = SmsTemplate::where('is_active', true)
            ->orderBy('name')
            ->get();

        $summary = [
            'total' => SmsLog::count(),
            'pending' => SmsLog::where('status', 'pending')->count(),
            'sent' => SmsLog::where('status', 'sent')->count(),
            'failed' => SmsLog::where('status', 'failed')->count(),
        ];

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

        return view('sms.index', compact(
            'barangays',
            'programs',
            'events',
            'resourceTypes',
            'templates',
            'smsLogs',
            'summary'
        ));
    }

    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'recipient_type' => ['required', Rule::in(['by_program', 'by_event', 'by_barangay', 'by_resource_type', 'by_direct_allocation', 'selected'])],
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
            'resource_type_id' => ['required_if:recipient_type,by_resource_type', 'nullable', 'exists:resource_types,id'],
            'direct_allocation_status' => ['required_if:recipient_type,by_direct_allocation', 'nullable', Rule::in(['all', 'planned', 'ready_for_release', 'released', 'not_received'])],
            'beneficiary_ids' => ['nullable', 'array'],
            'beneficiary_ids.*' => ['integer', 'exists:beneficiaries,id'],
        ]);

        $query = $this->resolveRecipients($request);

        // If specific beneficiary IDs are provided (refined selection), further filter
        if ($request->filled('beneficiary_ids')) {
            $refinedIds = $request->beneficiary_ids;
            $query->whereIn('id', $refinedIds);
        }

        $recipients = $query->get();

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
            'message' => ['required', 'string', 'min:5'],
            'recipient_type' => ['required', Rule::in(['by_program', 'by_event', 'by_barangay', 'by_resource_type', 'by_direct_allocation', 'selected'])],
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
            'resource_type_id' => ['required_if:recipient_type,by_resource_type', 'nullable', 'exists:resource_types,id'],
            'direct_allocation_status' => ['required_if:recipient_type,by_direct_allocation', 'nullable', Rule::in(['all', 'planned', 'ready_for_release', 'released', 'not_received'])],
            'beneficiary_ids' => ['nullable', 'array'],
            'beneficiary_ids.*' => ['integer', 'exists:beneficiaries,id'],
        ]);

        if (empty(config('services.sms.api_key'))) {
            return response()->json([
                'error' => 'SMS Service is not configured. Please contact administrator.',
            ], 503);
        }

        $query = $this->resolveRecipients($request);

        // If specific beneficiary IDs are provided (refined selection), further filter
        if ($request->filled('beneficiary_ids')) {
            $refinedIds = $request->beneficiary_ids;
            $query->whereIn('id', $refinedIds);
        }

        $recipients = $query->get();

        if ($recipients->isEmpty()) {
            return response()->json([
                'sent' => 0,
                'failed' => 0,
                'total' => 0,
                'message' => 'No recipients found.',
            ]);
        }

        // Hard limit on message length to prevent excessive segment usage
        $message = $request->message;
        $length = mb_strlen($message);
        if ($length > 480) {
            return response()->json([
                'error' => "Message is too long ({$length} characters). Maximum allowed is 480 characters (3 segments) to avoid excessive credit usage.",
            ], 422);
        }

        // Increase execution time for larger broadcasts
        // 5 seconds per recipient + 30 seconds base
        $timeout = ($recipients->count() * 5) + 30;
        set_time_limit(min($timeout, 3600)); // Max 1 hour

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
                'resource_type_id' => $request->resource_type_id,
                'direct_allocation_status' => $request->direct_allocation_status,
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
            case 'by_resource_type':
                $query->where(function ($q) use ($request) {
                    $q->whereHas('allocations', fn ($a) => $a->where('resource_type_id', $request->resource_type_id))
                        ->orWhereHas('directAssistance', fn ($d) => $d->where('resource_type_id', $request->resource_type_id));
                });
                break;
            case 'by_direct_allocation':
                $status = $request->direct_allocation_status;
                $query->whereHas('allocations', function ($a) use ($status) {
                    $a->where('release_method', 'direct');
                    if ($status && $status !== 'all') {
                        $a->whereReleaseStatus($status);
                    }
                });
                break;
            case 'selected':
                $ids = $request->beneficiary_ids ?? [];
                $query->whereIn('id', $ids);
                break;
            default:
                $query->whereRaw('1 = 0');
                break;
        }

        return $query->orderBy('full_name');
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
    public function storeTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sms_templates,name',
            'content' => 'required|string',
        ]);

        $template = SmsTemplate::create($validated);

        return response()->json([
            'success' => true,
            'template' => $template,
        ]);
    }

    public function updateTemplate(Request $request, SmsTemplate $template): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sms_templates,name,'.$template->id,
            'content' => 'required|string',
        ]);

        $template->update($validated);

        return response()->json([
            'success' => true,
            'template' => $template,
        ]);
    }

    public function destroyTemplate(SmsTemplate $template): JsonResponse
    {
        $template->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}

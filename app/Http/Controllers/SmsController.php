<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use App\Models\Beneficiary;
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

        $totalActive = Beneficiary::where('status', 'Active')->count();
        $sendOnBeneficiaryCreate = Cache::get(
            'sms.send_on_beneficiary_create',
            config('services.sms.send_on_beneficiary_create')
        );

        return view('sms.index', compact('barangays', 'smsLogs', 'totalActive', 'sendOnBeneficiaryCreate'));
    }

    public function updateBeneficiaryRegistrationSmsSetting(Request $request): RedirectResponse
    {
        $enabled = $request->boolean('send_on_beneficiary_create');

        Cache::forever('sms.send_on_beneficiary_create', $enabled);

        $this->audit->log(
            auth()->id(),
            'updated',
            'sms_settings',
            0,
            [],
            ['send_on_beneficiary_create' => $enabled],
        );

        return redirect()->route('sms.index')
            ->with('success', $enabled
                ? 'Auto-SMS on beneficiary registration is now enabled.'
                : 'Auto-SMS on beneficiary registration is now disabled.');
    }

    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'recipient_type' => ['required', Rule::in(['all', 'by_barangay', 'by_classification', 'selected'])],
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

    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'min:5', 'max:160'],
            'recipient_type' => ['required', Rule::in(['all', 'by_barangay', 'by_classification', 'selected'])],
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
            case 'all':
            default:
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

<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SemaphoreService
{
    private string $apiKey;
    private string $senderName;
    private string $endpoint;

    public function __construct()
    {
        $this->endpoint   = config('services.sms.api_url', 'https://smsapiph.onrender.com/api/v1/send/sms');
        $this->apiKey     = config('services.sms.api_key', '');
        $this->senderName = config('services.sms.sender_name', 'FFPRAMS');
    }

    public function sendSms(string $number, string $message, ?int $beneficiaryId = null): bool
    {
        if (empty($this->apiKey)) {
            Log::warning('SemaphoreService: API key is not configured.');
            return false;
        }

        if (empty(trim($number))) {
            Log::warning('SemaphoreService: Cannot send SMS — contact number is empty.', [
                'beneficiary_id' => $beneficiaryId,
            ]);
            return false;
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
            ])->post($this->endpoint, [
                'recipient' => $number,
                'message'   => $message,
            ]);

            $success = $response->successful() && ($response->json('success') === true);

            $this->logSms(
                beneficiaryId: $beneficiaryId,
                message:       $message,
                status:        $success ? 'sent' : 'failed',
                response:      $response->body(),
            );

            return $success;
        } catch (\Throwable $e) {
            Log::error('SemaphoreService: SMS sending failed', [
                'number' => $number,
                'error'  => $e->getMessage(),
            ]);

            $this->logSms(
                beneficiaryId: $beneficiaryId,
                message:       $message,
                status:        'failed',
                response:      $e->getMessage(),
            );

            return false;
        }
    }

    private function logSms(?int $beneficiaryId, string $message, string $status, ?string $response): void
    {
        if (! $beneficiaryId) {
            return;
        }

        try {
            DB::table('sms_logs')->insert([
                'beneficiary_id' => $beneficiaryId,
                'message'        => $message,
                'status'         => $status,
                'response'       => $response,
                'sent_at'        => now(),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('SemaphoreService: Failed to log SMS', ['error' => $e->getMessage()]);
        }
    }
}

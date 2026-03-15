<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SemaphoreService
{
    private string $apiKey;
    private string $senderName;
    private string $endpoint = 'https://api.semaphore.co/api/v4/messages';

    public function __construct()
    {
        $this->apiKey     = config('services.semaphore.api_key', '');
        $this->senderName = config('services.semaphore.sender_name', 'FFPRAMS');
    }

    public function sendSms(string $number, string $message, ?int $beneficiaryId = null): bool
    {
        try {
            $response = Http::asForm()->post($this->endpoint, [
                'apikey'     => $this->apiKey,
                'number'     => $number,
                'message'    => $message,
                'sendername' => $this->senderName,
            ]);

            $success = $response->successful();

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

<?php

namespace App\Services\SmileId;

use App\Models\KycVerification;
use Illuminate\Support\Facades\Http;

class StatusSyncService
{
    public function pushStatus(KycVerification $verification, string $status): bool
    {
        $endpoint = config('smileid.status_update_endpoint');
        if (! is_string($endpoint) || trim($endpoint) === '') {
            return false;
        }

        $timestamp = now()->toIso8601String();
        $partnerId = (string) config('smileid.partner_id', '');
        $apiKey = (string) config('smileid.api_key', '');
        $signature = '';

        if ($partnerId !== '' && $apiKey !== '') {
            $message = $timestamp.$partnerId.'sid_request';
            $signature = base64_encode(hash_hmac('sha256', $message, $apiKey, true));
        }

        $response = Http::timeout(15)->post($endpoint, [
            'job_id' => $verification->job_id,
            'status' => $status,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'partner_params' => [
                'user_id' => $verification->user_id,
                'job_id' => $verification->job_id,
            ],
        ]);

        return $response->successful();
    }
}

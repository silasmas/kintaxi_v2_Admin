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

        $sig = SmileIdSignature::generate();

        $response = Http::timeout(15)->post($endpoint, [
            'job_id' => $verification->job_id,
            'status' => $status,
            'timestamp' => $sig['timestamp'],
            'signature' => $sig['signature'],
            'partner_params' => [
                'user_id' => $verification->user_id,
                'job_id' => $verification->job_id,
            ],
        ]);

        return $response->successful();
    }
}

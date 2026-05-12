<?php

namespace App\Services\SmileId;

use App\Models\KycVerification;

class JobStatusPayloadMerger
{
    /**
     * Fusionne une réponse job_status réussie (code 2302) dans les champs JSON du modèle.
     */
    public function mergeInto(KycVerification $record, array $remote): void
    {
        if (($remote['code'] ?? '') !== '2302') {
            return;
        }

        $payload = json_decode((string) ($record->callback_payload_json ?? ''), true);
        if (! is_array($payload)) {
            $payload = [];
        }

        if (! empty($remote['image_links']) && is_array($remote['image_links'])) {
            $existing = isset($payload['image_links']) && is_array($payload['image_links'])
                ? $payload['image_links']
                : [];
            $payload['image_links'] = array_merge($existing, $remote['image_links']);
        }

        if (! empty($remote['job_complete']) || array_key_exists('job_success', $remote)) {
            $payload['job_status_poll'] = [
                'job_complete' => $remote['job_complete'] ?? null,
                'job_success' => $remote['job_success'] ?? null,
                'polled_at' => now()->toIso8601String(),
            ];
        }

        $encodedPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($encodedPayload !== false) {
            $record->callback_payload_json = $encodedPayload;
        }

        if (! empty($remote['result']) && is_array($remote['result'])) {
            $encodedResult = json_encode($remote['result'], JSON_UNESCAPED_UNICODE);
            if ($encodedResult !== false) {
                $record->smile_result_json = $encodedResult;
            }
        }

        $record->save();
    }
}

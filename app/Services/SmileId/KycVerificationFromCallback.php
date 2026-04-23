<?php

namespace App\Services\SmileId;

use App\Models\KycVerification;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class KycVerificationFromCallback
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function sync(array $payload): KycVerification
    {
        $core = $this->extractCore($payload);
        $partner = $this->extractPartnerParams($payload, $core);

        $jobId = (string) ($partner['job_id'] ?? $partner['jobId'] ?? '');
        if ($jobId === '') {
            throw new \InvalidArgumentException('job_id Smile ID manquant (PartnerParams).');
        }

        $userId = $this->resolveUserId($partner);

        $documentType = $this->stringOrNull(Arr::get($core, 'IDType') ?? Arr::get($core, 'id_type'));
        $country = $this->stringOrNull(Arr::get($core, 'Country') ?? Arr::get($core, 'country'));
        $status = $this->resolveStatus($payload, $core);
        $productType = $this->resolveProductType($partner);

        $smileJson = json_encode($core, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $callbackJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return DB::transaction(function () use ($jobId, $userId, $productType, $documentType, $country, $status, $smileJson, $callbackJson) {
            /** @var KycVerification $row */
            $row = KycVerification::query()->firstOrNew(['job_id' => $jobId]);

            if (! $row->exists) {
                $row->submitted_at = now();
            }

            if ($userId !== null) {
                $row->user_id = $userId;
            }

            $row->product_type = $productType;
            $row->document_type = $documentType;
            $row->country_code = $country;
            $row->status = $status;
            $row->smile_result_json = $smileJson;
            $row->callback_payload_json = $callbackJson;

            if (in_array($status, ['approved', 'rejected', 'completed', 'under_review'], true)) {
                $row->verified_at = now();
            }

            $row->save();

            if ($userId !== null && $status === 'approved') {
                User::query()->whereKey($userId)->update([
                    'kyc_verified' => 1,
                    'kyc_verified_at' => now(),
                ]);
            }

            if ($userId !== null && $status === 'rejected') {
                User::query()->whereKey($userId)->update([
                    'kyc_verified' => 0,
                    'kyc_verified_at' => null,
                ]);
            }

            return $row->fresh() ?? $row;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function extractCore(array $payload): array
    {
        if (isset($payload['result']) && is_array($payload['result'])) {
            return $payload['result'];
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $core
     * @return array<string, mixed>
     */
    private function extractPartnerParams(array $payload, array $core): array
    {
        $candidates = [
            Arr::get($payload, 'PartnerParams'),
            Arr::get($payload, 'partner_params'),
            Arr::get($core, 'PartnerParams'),
            Arr::get($core, 'partner_params'),
        ];

        foreach ($candidates as $p) {
            if (is_array($p) && $p !== []) {
                return $p;
            }
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $partner
     */
    private function resolveUserId(array $partner): ?int
    {
        $raw = $partner['user_id'] ?? $partner['userId'] ?? null;
        if ($raw === null || $raw === '') {
            return null;
        }

        if (! is_numeric($raw)) {
            return null;
        }

        $id = (int) $raw;

        return User::query()->whereKey($id)->exists() ? $id : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $core
     */
    private function resolveStatus(array $payload, array $core): string
    {
        if (array_key_exists('job_complete', $payload) && $payload['job_complete'] === false) {
            return 'pending';
        }

        if (array_key_exists('job_success', $payload) && $payload['job_success'] === false) {
            return 'rejected';
        }

        $actions = Arr::get($core, 'Actions', Arr::get($core, 'actions', []));
        if (! is_array($actions)) {
            $actions = [];
        }

        $verify = $actions['Verify_Document'] ?? $actions['verify_document'] ?? null;
        $resultText = strtolower((string) (Arr::get($core, 'ResultText') ?? Arr::get($core, 'result_text') ?? ''));

        if ($verify === 'Failed' || str_contains($resultText, 'fail')) {
            return 'rejected';
        }

        if (in_array($verify, ['Passed', 'Approved'], true)) {
            return 'approved';
        }

        if ($verify === 'Partial' || str_contains($resultText, 'review')) {
            return 'under_review';
        }

        if ($verify !== null || Arr::get($core, 'ResultCode') !== null) {
            return 'completed';
        }

        return 'pending';
    }

    /**
     * @param  array<string, mixed>  $partner
     */
    private function resolveProductType(array $partner): string
    {
        $jobType = $partner['job_type'] ?? $partner['jobType'] ?? null;
        if ($jobType === null || $jobType === '') {
            return 'document_verification';
        }

        return 'job_type_'.(string) $jobType;
    }

    private function stringOrNull(mixed $v): ?string
    {
        if ($v === null) {
            return null;
        }

        $s = trim((string) $v);

        return $s === '' ? null : $s;
    }
}

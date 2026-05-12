<?php

namespace App\Services\SmileId;

use App\Models\KycVerification;
use Illuminate\Support\Facades\Http;

class JobStatusClient
{
    /**
     * @return array{ok: bool, http_status: int|null, body: array|null, error: string|null, user_id_used: string|null}
     */
    public function fetch(KycVerification $verification, bool $imageLinks = true, bool $history = false): array
    {
        $partnerId = (string) config('smileid.partner_id', '');

        if ($partnerId === '') {
            return [
                'ok' => false,
                'http_status' => null,
                'body' => null,
                'error' => 'Smile ID partner_id manquant',
                'user_id_used' => null,
            ];
        }

        $jobId = trim((string) $verification->job_id);
        if ($jobId === '') {
            return [
                'ok' => false,
                'http_status' => null,
                'body' => null,
                'error' => 'job_id vide',
                'user_id_used' => null,
            ];
        }

        $userIds = KycVerificationSmileUserIds::candidates($verification);
        if ($userIds === []) {
            return [
                'ok' => false,
                'http_status' => null,
                'body' => null,
                'error' => 'Aucun user_id candidat (associez un utilisateur ou renseignez PartnerParams dans le callback).',
                'user_id_used' => null,
            ];
        }

        $attemptErrors = [];
        $attemptCodes = [];
        $lastBody = null;
        $lastHttp = null;

        foreach ($userIds as $userId) {
            $sig = SmileIdSignature::generate();
            if ($sig['signature'] === '') {
                return [
                    'ok' => false,
                    'http_status' => null,
                    'body' => null,
                    'error' => 'Impossible de signer la requête (clé API manquante ?)',
                    'user_id_used' => null,
                ];
            }

            $response = Http::timeout(25)->acceptJson()->post(SmileIdSignature::jobStatusUrl(), [
                'timestamp' => $sig['timestamp'],
                'signature' => $sig['signature'],
                'user_id' => $userId,
                'job_id' => $jobId,
                'partner_id' => $partnerId,
                'image_links' => $imageLinks,
                'history' => $history,
            ]);

            $lastHttp = $response->status();
            $json = $response->json();

            if (! is_array($json)) {
                $attemptErrors[] = 'user_id « '.$userId.' » : corps de réponse non-JSON';

                continue;
            }

            $lastBody = $json;
            $code = isset($json['code']) ? (string) $json['code'] : '';
            if ($code !== '') {
                $attemptCodes[] = $code;
            }

            if ($response->successful() && $code === '2302') {
                return [
                    'ok' => true,
                    'http_status' => $response->status(),
                    'body' => $json,
                    'error' => null,
                    'user_id_used' => $userId,
                ];
            }

            $attemptErrors[] = 'user_id « '.$userId.' » : code '.$code.' (HTTP '.$response->status().')';
        }

        $tail = self::explainSmileAuthorizationFailure($attemptCodes);
        $joined = trim(implode(' ', $attemptErrors).' '.$tail);
        if ($joined === '') {
            $joined = 'job_status a échoué pour tous les identifiants testés.';
        }

        return [
            'ok' => false,
            'http_status' => $lastHttp,
            'body' => $lastBody,
            'error' => $joined,
            'user_id_used' => null,
        ];
    }

    /**
     * Smile ID documente les codes 2204/2205 comme erreurs de signature ou d’environnement.
     *
     * @param  list<string>  $codes
     */
    private static function explainSmileAuthorizationFailure(array $codes): string
    {
        foreach ($codes as $c) {
            if ($c === '2204' || $c === '2205') {
                return '(Smile ID, codes 2204/2205 : requête non autorisée — en général signature ou environnement invalide.) '
                    .'Vérifiez : (1) la clé « API Key for Signature » du même environnement que SMILEID_SID_SERVER '
                    .'(0 = sandbox / testapi.smileidentity.com, 1 = production / api.smileidentity.com) ; '
                    .'(2) le partner_id exact du portail ; (3) le timestamp ISO UTC avec millisecondes et suffixe « Z », '
                    .'strictement celui envoyé dans le corps de la requête. '
                    .'Référence : docs Smile « Troubleshooting error 2204 & 2205 » et « Generating the signature » (REST).';
            }
        }

        return '';
    }
}

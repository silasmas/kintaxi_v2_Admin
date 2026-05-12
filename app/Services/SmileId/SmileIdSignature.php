<?php

namespace App\Services\SmileId;

final class SmileIdSignature
{
    /**
     * Horodatage UTC au format Smile ID pour les signatures REST :
     * yyyy-MM-dd'T'HH:mm:ss.fffZ (voir doc Generate Signature - PHP).
     *
     * @return array{timestamp: string, signature: string}
     */
    public static function generate(?string $timestamp = null): array
    {
        if ($timestamp === null) {
            $timestamp = self::freshTimestampUtc();
        }
        $partnerId = (string) config('smileid.partner_id', '');
        $apiKey = (string) config('smileid.api_key', '');
        $signature = '';

        if ($partnerId !== '' && $apiKey !== '') {
            $message = $timestamp.$partnerId.'sid_request';
            $signature = base64_encode(hash_hmac('sha256', $message, $apiKey, true));
        }

        return ['timestamp' => $timestamp, 'signature' => $signature];
    }

    /**
     * Exemple : 2026-05-06T15:42:51.943Z Les ISO sans millisecondes ou avec offset +00:00
     * peuvent être rejetées (codes 2204/2205).
     */
    public static function freshTimestampUtc(): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
            ->format('Y-m-d\TH:i:s.v\Z');
    }

    public static function jobStatusUrl(): string
    {
        $override = config('smileid.job_status_url');
        if (is_string($override) && trim($override) !== '') {
            return trim($override);
        }

        $sid = config('smileid.sid_server');

        return ($sid === '1' || $sid === 1)
            ? 'https://api.smileidentity.com/v1/job_status'
            : 'https://testapi.smileidentity.com/v1/job_status';
    }
}

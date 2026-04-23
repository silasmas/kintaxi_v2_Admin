<?php

namespace App\Services\SmileId;

class SignatureVerifier
{
    public function valid(string $timestamp, string $signature): bool
    {
        $partnerId = (string) config('smileid.partner_id', '');
        $apiKey = (string) config('smileid.api_key', '');

        if ($partnerId === '' || $apiKey === '') {
            return false;
        }

        $message = $timestamp.$partnerId.'sid_request';
        $expected = base64_encode(hash_hmac('sha256', $message, $apiKey, true));

        return hash_equals($expected, $signature);
    }
}

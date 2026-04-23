<?php

namespace App\Http\Controllers;

use App\Services\SmileId\KycVerificationFromCallback;
use App\Services\SmileId\SignatureVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class SmileIdCallbackController extends Controller
{
    public function __invoke(
        Request $request,
        SignatureVerifier $signatureVerifier,
        KycVerificationFromCallback $processor,
    ): JsonResponse {
        if (! config('smileid.callback_enabled', true)) {
            return response()->json(['ok' => false, 'message' => 'callback disabled'], 503);
        }

        $payload = $request->json()->all();
        if ($payload === []) {
            return response()->json(['ok' => false, 'message' => 'empty body'], 422);
        }

        $timestamp = $payload['timestamp'] ?? null;
        $signature = $payload['signature'] ?? null;

        if (config('smileid.verify_signature', true)) {
            if (! is_string($timestamp) || ! is_string($signature)
                || ! $signatureVerifier->valid($timestamp, $signature)) {
                Log::warning('smileid.callback.signature_invalid');

                return response()->json(['ok' => false, 'message' => 'invalid signature'], 401);
            }
        }

        try {
            $processor->sync($payload);
        } catch (Throwable $e) {
            Log::error('smileid.callback.process_failed', [
                'message' => $e->getMessage(),
            ]);

            return response()->json(['ok' => false, 'message' => 'process failed'], 422);
        }

        return response()->json(['ok' => true]);
    }
}

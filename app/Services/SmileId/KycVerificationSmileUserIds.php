<?php

namespace App\Services\SmileId;

use App\Models\KycVerification;

/**
 * Identifiants Smile «&nbsp;user_id&nbsp;» utilisables avec job_status.
 *
 * Smile exige strictement la même valeur qu’au dépôt du job (PartnerParams.user_id).
 * Les champs mobiles (ex. «&nbsp;target&nbsp;») ne correspondent pas forcément&nbsp;; on ne doit pas les confondre.
 */
final class KycVerificationSmileUserIds
{
    /**
     * @return list<string>
     */
    public static function candidates(KycVerification $verification): array
    {
        $ids = [];

        if ($verification->user_id) {
            $ids[] = (string) (int) $verification->user_id;
        }

        $callback = json_decode((string) ($verification->callback_payload_json ?? ''), true);
        if (is_array($callback) && $callback !== []) {
            self::walk($callback, $ids);
        }

        $seen = [];
        $out = [];

        foreach ($ids as $id) {
            $id = trim((string) $id);
            if ($id === '' || isset($seen[$id])) {
                continue;
            }
            $seen[$id] = true;
            $out[] = $id;
        }

        return $out;
    }

    /**
     * @param  array<int|string, mixed>  $node
     * @param  list<string>  $ids
     */
    private static function walk(array $node, array &$ids): void
    {
        foreach ($node as $key => $value) {
            $keyLower = is_string($key) ? strtolower($key) : '';

            if (in_array($keyLower, ['user_id', 'userid'], true) && (is_string($value) || is_numeric($value))) {
                $candidate = trim((string) $value);
                if ($candidate !== '' && ! self::looksLikeFilesystemPath($candidate)) {
                    $ids[] = $candidate;
                }
            }

            if (is_array($value)) {
                self::walk($value, $ids);
            }
        }
    }

    private static function looksLikeFilesystemPath(string $s): bool
    {
        if (str_starts_with($s, '/data/') || str_starts_with($s, '/storage/')) {
            return true;
        }

        return (bool) preg_match('/^[a-zA-Z]:\\\\/', $s);
    }
}

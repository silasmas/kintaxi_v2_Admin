<?php

namespace App\Services\SmileId;

use App\Models\KycVerification;

final class KycMediaExtractor
{
    /**
     * @return array<int, array{kind: 'url'|'device_path', label: string, url?: string, path?: string}>
     */
    public static function extract(?KycVerification $record): array
    {
        if ($record === null) {
            return [];
        }

        $items = [];

        foreach (['callback_payload_json', 'smile_result_json'] as $field) {
            $decoded = json_decode((string) ($record->{$field} ?? ''), true);
            if (is_array($decoded)) {
                $items = array_merge($items, self::collectFromTree($decoded));
            }
        }

        $seen = [];
        $out = [];

        foreach ($items as $item) {
            $k = $item['kind'] === 'url' ? 'u:'.($item['url'] ?? '') : 'p:'.($item['path'] ?? '');
            if (isset($seen[$k])) {
                continue;
            }
            $seen[$k] = true;
            $out[] = $item;
        }

        return $out;
    }

    /**
     * @param  array<string|int, mixed>  $node
     * @return array<int, array{kind: 'url'|'device_path', label: string, url?: string, path?: string}>
     */
    private static function collectFromTree(array $node, string $path = ''): array
    {
        $out = [];

        foreach ($node as $key => $value) {
            $segment = is_string($key) ? $key : (string) $key;
            $nextPath = $path === '' ? $segment : $path.'.'.$segment;

            if (is_string($value)) {
                $trimmed = trim($value);
                if ($trimmed === '') {
                    continue;
                }

                $url = self::normalizeHttpUrl($trimmed);
                if ($url !== null && self::looksLikeRemoteMedia($url)) {
                    $out[] = [
                        'kind' => 'url',
                        'label' => self::labelForKey($segment, $nextPath),
                        'url' => $url,
                    ];

                    continue;
                }

                if (self::looksLikeLocalDeviceMediaPath($trimmed)) {
                    $out[] = [
                        'kind' => 'device_path',
                        'label' => self::labelForKey($segment, $nextPath),
                        'path' => $trimmed,
                    ];
                }
            } elseif (is_array($value)) {
                $out = array_merge($out, self::collectFromTree($value, $nextPath));
            }
        }

        return $out;
    }

    private static function normalizeHttpUrl(string $value): ?string
    {
        if (str_starts_with($value, 'data:')) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_URL) ? $value : null;
    }

    private static function looksLikeRemoteMedia(string $url): bool
    {
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));

        if ($path !== '' && preg_match('/\.(jpe?g|png|gif|webp|bmp|pdf)(\?|$)/i', $path) === 1) {
            return true;
        }

        return str_contains($url, 'amazonaws.com') || str_contains($url, 'cloudfront.net');
    }

    private static function looksLikeLocalDeviceMediaPath(string $value): bool
    {
        if (str_starts_with($value, 'data:')) {
            return false;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return false;
        }

        $base = basename(str_replace('\\', '/', $value));

        if ($base === '' || $base === '/' || $base === '.') {
            return false;
        }

        if (preg_match('/\.(jpe?g|png|gif|webp|bmp|pdf)$/i', $base) !== 1) {
            return false;
        }

        return str_starts_with($value, '/')
            || str_starts_with($value, 'file:')
            || (bool) preg_match('/^[a-zA-Z]:\\\\/', $value);
    }

    private static function labelForKey(string $key, string $path): string
    {
        $map = [
            'selfie_image' => 'Selfie',
            'selfieFile' => 'Selfie (fichier mobile)',
            'liveness_image' => 'Liveness',
            'id_card_image' => 'Carte d’identité (image)',
            'Document' => 'Document (ID)',
            'document' => 'Document',
            'id_image' => 'Pièce',
            'national_id_image' => 'Pièce nationale',
            'documentFrontFile' => 'Recto du document',
            'documentBackFile' => 'Verso du document',
            'document_front_file' => 'Recto du document',
            'document_back_file' => 'Verso du document',
        ];

        if (isset($map[$key])) {
            return $map[$key];
        }

        return str_replace(['.', '_'], [' → ', ' '], $path);
    }
}

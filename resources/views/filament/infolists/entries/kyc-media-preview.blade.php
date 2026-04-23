@php
    $record = $getRecord();
    $payload = json_decode((string) ($record?->callback_payload_json ?? ''), true);
    $result = json_decode((string) ($record?->smile_result_json ?? ''), true);

    $toUrl = function (mixed $value): ?string {
        if (! is_string($value)) {
            return null;
        }
        $value = trim($value);
        if ($value === '' || str_starts_with($value, 'data:')) {
            return null;
        }
        return filter_var($value, FILTER_VALIDATE_URL) ? $value : null;
    };

    $imageUrls = [];
    $sources = [
        data_get($payload, 'image_links.selfie_image'),
        data_get($payload, 'image_links.liveness_image'),
        data_get($payload, 'result.Document'),
        data_get($result, 'Document'),
    ];

    foreach ($sources as $source) {
        $url = $toUrl($source);
        if ($url !== null) {
            $imageUrls[] = $url;
        }
    }

    $imageUrls = array_values(array_unique($imageUrls));
@endphp

@if (count($imageUrls) === 0)
    <div class="text-sm text-gray-500 dark:text-gray-400">
        Aucun lien d'image exploitable trouvé dans les réponses Smile ID.
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach ($imageUrls as $index => $url)
            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="block">
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <img src="{{ $url }}" alt="Media KYC {{ $index + 1 }}" class="w-full h-56 object-cover" />
                    <div class="px-3 py-2 text-xs text-gray-600 dark:text-gray-300">
                        Média {{ $index + 1 }} (ouvrir)
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@endif

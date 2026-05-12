@php
    use App\Services\SmileId\KycMediaExtractor;

    /** @var \App\Models\KycVerification|null $kycRecord */
    $kycRecord = $getRecord();
    $items = KycMediaExtractor::extract($kycRecord);

    $isPdf = static function (string $url): bool {
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));

        return str_ends_with($path, '.pdf');
    };

    $signedUrls = array_values(array_filter($items, fn (array $i): bool => $i['kind'] === 'url'));
    $devicePaths = array_values(array_filter($items, fn (array $i): bool => $i['kind'] === 'device_path'));
@endphp

@if (count($signedUrls) === 0 && count($devicePaths) === 0)
    <div class="text-sm text-gray-500 dark:text-gray-400">
        Aucun média consultable pour l’instant. Si le job est terminé côté Smile ID, utilisez
        <strong>Rafraîchir depuis Smile ID</strong> pour récupérer les liens signés.
    </div>
@else
    @if (count($signedUrls) > 0)
        <p class="mb-3 text-xs text-gray-600 dark:text-gray-300">Médias distants (liens signés ou HTTP)</p>
        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
            @foreach ($signedUrls as $item)
                @php
                    $label = $item['label'];
                    $url = $item['url'] ?? '';
                    $pdf = $isPdf($url);
                @endphp
                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                    @if ($pdf)
                        <div class="flex flex-col gap-2 bg-gray-50 px-4 py-6 dark:bg-gray-800/60">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $label }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">PDF — ouvrir dans un nouvel onglet</span>
                            <a
                                href="{{ $url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex text-sm font-semibold text-primary-600 underline hover:text-primary-500 dark:text-primary-400"
                            >
                                Télécharger / afficher le PDF
                            </a>
                        </div>
                    @else
                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="block">
                            <div class="relative overflow-hidden bg-gray-100 dark:bg-gray-800">
                                <img src="{{ $url }}" alt="{{ $label }}" class="h-56 w-full object-cover" loading="lazy" />
                            </div>
                            <div class="px-3 py-2 text-xs text-gray-600 dark:text-gray-300">
                                {{ $label }} — ouvrir
                            </div>
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if (count($devicePaths) > 0)
        <p class="mb-2 text-xs font-medium text-gray-700 dark:text-gray-200">Fichiers enregistrés sur l’appareil (non affichables ici)</p>
        <ul class="space-y-2 text-xs text-gray-600 dark:text-gray-300">
            @foreach ($devicePaths as $item)
                @php
                    $p = $item['path'] ?? '';
                    $base = $p !== '' ? basename(str_replace('\\', '/', $p)) : '—';
                @endphp
                <li class="rounded-md border border-dashed border-gray-300 bg-gray-50 px-3 py-2 dark:border-white/10 dark:bg-gray-800/50">
                    <span class="font-medium text-gray-800 dark:text-gray-100">{{ $item['label'] }}</span>
                    <span class="block truncate text-gray-500 dark:text-gray-400" title="{{ $p }}">{{ $base }}</span>
                </li>
            @endforeach
        </ul>
    @endif
@endif

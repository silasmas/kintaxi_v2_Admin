@php
    $record = $getRecord();

    $extractPoint = function (mixed $raw): ?array {
        if (empty($raw)) {
            return null;
        }

        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
        if (! is_array($decoded)) {
            return null;
        }

        $loc = $decoded['location'] ?? $decoded;
        $lat = $loc['lat'] ?? $loc['latitude'] ?? null;
        $lng = $loc['lng'] ?? $loc['lon'] ?? $loc['longitude'] ?? null;

        if ($lat === null || $lng === null) {
            return null;
        }

        return [(float) $lat, (float) $lng];
    };

    $start = $extractPoint($record->start_location ?? $record->pickup_location);
    $end = $extractPoint($record->end_location);
    $mapId = 'ride-route-map-' . $record->id;
@endphp

<div class="space-y-3">
    <div class="kintaxi-map" wire:ignore>
        <div id="{{ $mapId }}" class="w-full" style="height: 380px;"></div>
    </div>

    @if (! $start || ! $end)
        <p class="text-sm text-warning-600 dark:text-warning-400">
            Coordonnees incompletes : le trace complet du trajet ne peut pas etre dessine.
        </p>
    @endif
</div>

@include('filament.maps.assets')

@push('scripts')
    <script>
        (function () {
            const mapId = @js($mapId);
            const start = @js($start);
            const end = @js($end);

            const init = () => {
                if (!window.L || !window.KinTaxiMapKit) {
                    setTimeout(init, 150);
                    return;
                }

                const el = document.getElementById(mapId);
                if (!el || el.dataset.inited === '1') return;

                const kit = window.KinTaxiMapKit;
                const center = start ?? end ?? kit.defaultCenter;
                const map = kit.createMap(mapId, center, 13);
                if (!map) return;

                const bounds = [];

                if (start) {
                    kit.addPin(map, start, {
                        label: 'D',
                        color: kit.colors.start,
                        popup: 'Depart',
                    });
                    bounds.push(start);
                }

                if (end) {
                    kit.addPin(map, end, {
                        label: 'A',
                        color: kit.colors.end,
                        popup: 'Arrivee',
                    });
                    bounds.push(end);
                }

                if (start && end) {
                    kit.drawRoute(map, start, end, {
                        popup: 'Trajet estime',
                    });
                }

                kit.fit(map, bounds, { padding: [30, 30], maxZoom: 15 });
            };

            init();
        })();
    </script>
@endpush

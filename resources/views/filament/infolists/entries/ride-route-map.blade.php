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
    <div id="{{ $mapId }}" class="w-full rounded-lg border border-gray-200 dark:border-gray-700" style="height: 360px;"></div>

    @if (! $start || ! $end)
        <p class="text-sm text-warning-600 dark:text-warning-400">
            Coordonnées incomplètes : le tracé complet du trajet ne peut pas être dessiné.
        </p>
    @endif
</div>

@once
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="anonymous" />
    @endpush
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="anonymous"></script>
    @endpush
@endonce

@push('scripts')
    <script>
        (function () {
            const mapId = @js($mapId);
            const start = @js($start);
            const end = @js($end);

            const init = () => {
                if (!window.L) {
                    setTimeout(init, 150);
                    return;
                }

                const el = document.getElementById(mapId);
                if (!el || el.dataset.inited === '1') return;
                el.dataset.inited = '1';

                const L = window.L;
                const center = start ?? end ?? [-4.4419, 15.2663];
                const map = L.map(mapId).setView(center, 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);

                const bounds = [];

                if (start) {
                    L.marker(start).addTo(map).bindPopup('Départ');
                    bounds.push(start);
                }

                if (end) {
                    L.marker(end).addTo(map).bindPopup('Arrivée');
                    bounds.push(end);
                }

                if (start && end) {
                    L.polyline([start, end], {
                        color: '#2563eb',
                        weight: 4,
                        opacity: 0.8
                    }).addTo(map).bindPopup('Trajet estimé');
                }

                if (bounds.length > 1) {
                    map.fitBounds(bounds, { padding: [30, 30], maxZoom: 15 });
                }
            };

            init();
        })();
    </script>
@endpush

@php
    $record = $getRecord();
    $lat = (float) ($record->latitude ?? -4.325);
    $lng = (float) ($record->longitude ?? 15.322);
    $radiusKm = (float) ($record->radius_km ?? 1);
    $mapId = 'zone-view-map-' . $record->id;
@endphp

<div class="space-y-2">
    <div class="kintaxi-map" wire:ignore>
        <div id="{{ $mapId }}" class="w-full" style="height: 340px;"></div>
    </div>
    <p class="text-xs text-gray-500 dark:text-gray-400">
        Rayon visualisé : {{ number_format($radiusKm, 2, ',', ' ') }} km.
    </p>
</div>

@include('filament.maps.assets')

@push('scripts')
    <script>
        (function () {
            const mapId = @js($mapId);
            const lat = @js($lat);
            const lng = @js($lng);
            const radiusMeters = @js(max($radiusKm, 0.1) * 1000);

            const init = () => {
                if (!window.L || !window.KinTaxiMapKit) return setTimeout(init, 120);
                const el = document.getElementById(mapId);
                if (!el || el.dataset.inited === '1') return;

                const kit = window.KinTaxiMapKit;
                const map = kit.createMap(mapId, [lat, lng], 14);
                if (!map) return;
                kit.addPin(map, [lat, lng], {
                    label: 'Z',
                    color: kit.colors.zone,
                    popup: 'Centre de la zone',
                }).openPopup();
                const circle = L.circle([lat, lng], {
                    radius: radiusMeters,
                    color: kit.colors.zone,
                    fillColor: '#60a5fa',
                    fillOpacity: 0.2,
                    weight: 2,
                }).addTo(map);
                map.fitBounds(circle.getBounds(), { padding: [20, 20], maxZoom: 15 });
            };
            init();
        })();
    </script>
@endpush

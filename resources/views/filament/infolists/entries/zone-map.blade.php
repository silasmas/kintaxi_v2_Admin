@php
    $record = $getRecord();
    $lat = (float) ($record->latitude ?? -4.325);
    $lng = (float) ($record->longitude ?? 15.322);
    $radiusKm = (float) ($record->radius_km ?? 1);
    $mapId = 'zone-view-map-' . $record->id;
@endphp

<div class="space-y-2">
    <div id="{{ $mapId }}" class="w-full rounded-lg border border-gray-200 dark:border-gray-700" style="height: 320px;"></div>
    <p class="text-xs text-gray-500 dark:text-gray-400">
        Rayon visualisé : {{ number_format($radiusKm, 2, ',', ' ') }} km.
    </p>
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
            const lat = @js($lat);
            const lng = @js($lng);
            const radiusMeters = @js(max($radiusKm, 0.1) * 1000);

            const init = () => {
                if (!window.L) return setTimeout(init, 120);
                const el = document.getElementById(mapId);
                if (!el || el.dataset.inited === '1') return;
                el.dataset.inited = '1';

                const map = L.map(mapId).setView([lat, lng], 14);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
                const marker = L.marker([lat, lng]).addTo(map);
                marker.bindPopup('Centre de la zone').openPopup();
                const circle = L.circle([lat, lng], { radius: radiusMeters, color: '#2563eb', fillColor: '#60a5fa', fillOpacity: 0.2 }).addTo(map);
                map.fitBounds(circle.getBounds(), { padding: [20, 20], maxZoom: 15 });
            };
            init();
        })();
    </script>
@endpush

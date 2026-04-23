@php
    $statePath = $getStatePath();
    $mapId = 'zone-form-map-' . md5($statePath);
@endphp

<div class="space-y-2" wire:ignore>
    <div id="{{ $mapId }}" class="w-full rounded-lg border border-gray-200 dark:border-gray-700" style="height: 320px;"></div>
    <p class="text-xs text-gray-500 dark:text-gray-400">
        Prévisualisation de la limite de zone (centrée sur Kinshasa par défaut).
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
            const defaults = { lat: -4.325, lng: 15.322, radiusKm: 1.0 };

            const getInputValue = (name) => {
                const input = document.querySelector(`[name$="${name}"]`);
                if (!input) return null;
                const value = parseFloat(input.value);
                return Number.isFinite(value) ? value : null;
            };

            const init = () => {
                if (!window.L) return setTimeout(init, 120);
                const el = document.getElementById(mapId);
                if (!el || el.dataset.inited === '1') return;
                el.dataset.inited = '1';

                const lat = getInputValue('latitude') ?? defaults.lat;
                const lng = getInputValue('longitude') ?? defaults.lng;
                const radiusKm = Math.max(getInputValue('radius_km') ?? defaults.radiusKm, 0.1);

                const map = L.map(mapId).setView([lat, lng], 14);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
                const marker = L.marker([lat, lng]).addTo(map);
                marker.bindPopup('Centre de la zone');
                const circle = L.circle([lat, lng], {
                    radius: radiusKm * 1000,
                    color: '#2563eb',
                    fillColor: '#60a5fa',
                    fillOpacity: 0.2
                }).addTo(map);
                map.fitBounds(circle.getBounds(), { padding: [20, 20], maxZoom: 15 });
            };

            setTimeout(init, 250);
        })();
    </script>
@endpush

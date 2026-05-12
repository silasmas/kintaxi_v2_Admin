@php
    $statePath = $getStatePath();
    $mapId = 'zone-form-map-' . md5($statePath);
@endphp

<div class="space-y-2" wire:ignore>
    <div class="kintaxi-map">
        <div id="{{ $mapId }}" class="w-full" style="height: 360px;"></div>
    </div>
    <p class="text-xs text-gray-500 dark:text-gray-400">
        Cliquez sur la carte, deplacez le marqueur ou recherchez une adresse pour renseigner le centre de la zone.
    </p>
</div>

@include('filament.maps.assets')

@push('scripts')
    <script>
        (function () {
            const mapId = @js($mapId);
            const defaults = { lat: -4.325, lng: 15.322, radiusKm: 1.0 };

            const findInput = (name) => document.querySelector(`[name$="${name}"]`);

            const getInputValue = (name) => {
                const input = findInput(name);
                if (!input) return null;
                const value = parseFloat(input.value);
                return Number.isFinite(value) ? value : null;
            };

            const setInputValue = (name, value) => {
                const input = findInput(name);
                if (!input) return;
                input.value = Number(value).toFixed(8);
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            };

            const init = () => {
                if (!window.L || !window.KinTaxiMapKit) return setTimeout(init, 120);
                const el = document.getElementById(mapId);
                if (!el || el.dataset.inited === '1') return;

                const kit = window.KinTaxiMapKit;
                const lat = getInputValue('latitude') ?? defaults.lat;
                const lng = getInputValue('longitude') ?? defaults.lng;
                const radiusKm = Math.max(getInputValue('radius_km') ?? defaults.radiusKm, 0.1);
                const center = [lat, lng];

                const map = kit.createMap(mapId, center, 14);
                if (!map) return;

                const marker = kit.addPin(map, center, {
                    label: 'Z',
                    color: kit.colors.zone,
                    draggable: true,
                    popup: 'Centre de la zone',
                });
                const circle = L.circle(center, {
                    radius: radiusKm * 1000,
                    color: kit.colors.zone,
                    fillColor: '#60a5fa',
                    fillOpacity: 0.2,
                    weight: 2,
                }).addTo(map);

                const syncPoint = (point, pan = true) => {
                    setInputValue('latitude', point[0]);
                    setInputValue('longitude', point[1]);
                    marker.setLatLng(point);
                    circle.setLatLng(point);
                    if (pan) map.panTo(point);
                };

                const syncRadius = () => {
                    const value = Math.max(getInputValue('radius_km') ?? defaults.radiusKm, 0.1);
                    circle.setRadius(value * 1000);
                    map.fitBounds(circle.getBounds(), { padding: [20, 20], maxZoom: 15 });
                };

                marker.on('dragend', () => {
                    const point = marker.getLatLng();
                    syncPoint([point.lat, point.lng], false);
                });

                map.on('click', (event) => {
                    syncPoint([event.latlng.lat, event.latlng.lng]);
                });

                ['latitude', 'longitude'].forEach((name) => {
                    const input = findInput(name);
                    input?.addEventListener('change', () => {
                        const nextLat = getInputValue('latitude') ?? defaults.lat;
                        const nextLng = getInputValue('longitude') ?? defaults.lng;
                        syncPoint([nextLat, nextLng]);
                    });
                });

                findInput('radius_km')?.addEventListener('change', syncRadius);

                kit.addSearch(map, (result) => {
                    const point = [result[0], result[1]];
                    syncPoint(point);
                    marker.bindPopup(result[2] || 'Centre de la zone').openPopup();
                });

                kit.addLocate(map, (point) => syncPoint(point));
                syncRadius();
            };

            setTimeout(init, 250);
        })();
    </script>
@endpush

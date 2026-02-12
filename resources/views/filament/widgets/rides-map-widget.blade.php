<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Aperçu des courses sur la carte
        </x-slot>

        @php
            $mapConfig = [
                'defaultLat' => $defaultLat,
                'defaultLng' => $defaultLng,
                'markers' => $markers,
                'ridesToGeocode' => $ridesToGeocode ?? [],
            ];
            $mapId = 'rides-map-' . $this->getId();
            $configId = 'rides-map-config-' . $this->getId();
        @endphp

        <div class="space-y-4">
            {{-- Conteneur carte : hauteur forcée en inline pour garantir l'affichage --}}
            <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700" wire:ignore>
                <div id="{{ $mapId }}"
                     class="w-full bg-gray-100 dark:bg-gray-800"
                     style="width:100%;height:400px;min-height:400px;"></div>
            </div>
            <script type="application/json" id="{{ $configId }}">@json($mapConfig)</script>

            @if(count($rides) > 0)
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-900 dark:text-gray-100">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">Départ</th>
                                <th class="px-4 py-3">Arrivée</th>
                                <th class="px-4 py-3">Statut</th>
                                <th class="px-4 py-3">Coût</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($rides, 0, 10) as $ride)
                                <tr class="bg-white dark:bg-gray-900 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-2">{{ $ride['id'] }}</td>
                                    <td class="px-4 py-2 max-w-[200px] truncate" title="{{ $ride['start_location'] ?? '—' }}">
                                        {{ \Illuminate\Support\Str::limit($ride['start_location'] ?? '—', 30) }}
                                    </td>
                                    <td class="px-4 py-2 max-w-[200px] truncate" title="{{ $ride['end_location'] ?? '—' }}">
                                        {{ \Illuminate\Support\Str::limit($ride['end_location'] ?? '—', 30) }}
                                    </td>
                                    <td class="px-4 py-2">
                                        @php
                                            $statusLabels = [
                                                'requested' => 'Demandée',
                                                'accepted' => 'Acceptée',
                                                'in_progress' => 'En cours',
                                                'completed' => 'Terminée',
                                                'canceled' => 'Annulée',
                                            ];
                                        @endphp
                                        <span class="px-2 py-0.5 rounded text-xs {{ $ride['ride_status'] === 'completed' ? 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400' : ($ride['ride_status'] === 'canceled' ? 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300') }}">
                                            {{ $statusLabels[$ride['ride_status']] ?? $ride['ride_status'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">{{ $ride['cost'] !== null ? number_format($ride['cost'], 0, ',', ' ') : '—' }}</td>
                                    <td class="px-4 py-2">
                                        <a href="{{ url('/admin/rides/' . $ride['id']) }}" class="text-primary-600 hover:underline dark:text-primary-400">
                                            Voir
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400">Aucune course pour le moment.</p>
            @endif
        </div>

        @assets
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="anonymous" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="anonymous"></script>
        @endassets

        @script
        <script>
            (function() {
                var attempts = 0;
                var maxAttempts = 80;

                function tryInit() {
                    var mapEl = document.querySelector('[id^="rides-map-"]');
                    if (!mapEl) {
                        if (attempts++ < maxAttempts) setTimeout(tryInit, 150);
                        return;
                    }
                    var mapId = mapEl.id;
                    if (window.__ridesMapInited && window.__ridesMapInited[mapId]) return;
                    window.__ridesMapInited = window.__ridesMapInited || {};
                    window.__ridesMapInited[mapId] = true;

                    var configEl = document.getElementById('rides-map-config-' + mapId.replace('rides-map-', ''));
                    var config = { markers: [], ridesToGeocode: [], defaultLat: -4.44, defaultLng: 15.27 };
                    if (configEl && configEl.textContent) {
                        try { config = JSON.parse(configEl.textContent); } catch (e) {}
                    }

                    function run() {
                        if (!window.L) {
                            setTimeout(run, 150);
                            return;
                        }
                        var el = document.getElementById(mapId);
                        if (!el) return;
                        try {
                            var L = window.L;
                            var map = L.map(mapId).setView([config.defaultLat || -4.44, config.defaultLng || 15.27], 13);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
                            var allBounds = [];
                            var greenIcon = L.divIcon({ className: 'rides-marker-depart', html: '<span style="background:#22c55e;color:#fff;border-radius:50%;width:22px;height:22px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:bold;">D</span>', iconSize: [22, 22], iconAnchor: [11, 11] });
                            var redIcon = L.divIcon({ className: 'rides-marker-arrivee', html: '<span style="background:#ef4444;color:#fff;border-radius:50%;width:22px;height:22px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:bold;">A</span>', iconSize: [22, 22], iconAnchor: [11, 11] });

                            function addRidePoints(ride, startLat, startLng, endLat, endLng) {
                                var label = 'Course #' + ride.id;
                                if (startLat != null && startLng != null) {
                                    L.marker([startLat, startLng], { icon: greenIcon }).addTo(map).bindPopup('<strong>Départ</strong> – ' + label);
                                    allBounds.push([startLat, startLng]);
                                }
                                if (endLat != null && endLng != null) {
                                    L.marker([endLat, endLng], { icon: redIcon }).addTo(map).bindPopup('<strong>Arrivée</strong> – ' + label);
                                    allBounds.push([endLat, endLng]);
                                }
                                if (startLat != null && startLng != null && endLat != null && endLng != null) {
                                    L.polyline([[startLat, startLng], [endLat, endLng]], { color: '#3b82f6', weight: 3, opacity: 0.7 }).addTo(map);
                                }
                            }

                            (config.markers || []).forEach(function(m) {
                                var sLat = m.start_lat != null ? m.start_lat : null, sLng = m.start_lng != null ? m.start_lng : null;
                                var eLat = m.end_lat != null ? m.end_lat : null, eLng = m.end_lng != null ? m.end_lng : null;
                                if (sLat == null && eLat == null) return;
                                addRidePoints(m, sLat, sLng, eLat, eLng);
                            });

                            function fitMap() {
                                if (allBounds.length > 1) map.fitBounds(allBounds, { padding: [40, 40], maxZoom: 15 });
                                else if (allBounds.length === 1) map.setView(allBounds[0], 15);
                            }
                            if (allBounds.length > 0) fitMap();

                            var geocodeIndex = 0;
                            (config.ridesToGeocode || []).forEach(function(ride) {
                                function doGeocode(addr, isEnd, delay) {
                                    if (!addr || !addr.trim()) return;
                                    setTimeout(function() {
                                        fetch('https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(addr) + '&format=json&limit=1', {
                                            headers: { 'Accept': 'application/json', 'User-Agent': 'KinTaxi-Admin/1.0' }
                                        }).then(function(r) { return r.json(); }).then(function(data) {
                                            if (data && data[0] && data[0].lat != null && data[0].lon != null) {
                                                var lat = parseFloat(data[0].lat), lng = parseFloat(data[0].lon);
                                                var label = 'Course #' + ride.id;
                                                if (isEnd) {
                                                    L.marker([lat, lng], { icon: redIcon }).addTo(map).bindPopup('<strong>Arrivée</strong> – ' + label);
                                                    allBounds.push([lat, lng]);
                                                    if (ride._startLat != null && ride._startLng != null) {
                                                        L.polyline([[ride._startLat, ride._startLng], [lat, lng]], { color: '#3b82f6', weight: 3, opacity: 0.7 }).addTo(map);
                                                    }
                                                } else {
                                                    ride._startLat = lat;
                                                    ride._startLng = lng;
                                                    L.marker([lat, lng], { icon: greenIcon }).addTo(map).bindPopup('<strong>Départ</strong> – ' + label);
                                                    allBounds.push([lat, lng]);
                                                }
                                                fitMap();
                                            }
                                        }).catch(function() {});
                                    }, delay);
                                }
                                geocodeIndex += 1;
                                var baseDelay = geocodeIndex * 2200;
                                doGeocode(ride.start_address, false, baseDelay);
                                doGeocode(ride.end_address, true, baseDelay + 1200);
                            });
                        } catch (err) {
                            console.error('RidesMap init error:', err);
                        }
                    }
                    setTimeout(run, 300);
                }
                tryInit();
            })();
        </script>
        @endscript
    </x-filament::section>
</x-filament-widgets::widget>

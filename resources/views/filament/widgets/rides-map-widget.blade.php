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
            <div class="kintaxi-map" wire:ignore>
                <div id="{{ $mapId }}"
                     class="w-full"
                     style="width:100%;height:430px;min-height:430px;"></div>
            </div>
            <script type="application/json" id="{{ $configId }}">@json($mapConfig)</script>

            @if(count($ridesForTable ?? []) > 0)
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-900 dark:text-gray-100 whitespace-nowrap min-w-max">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-300">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3 min-w-[180px]">Départ</th>
                                <th class="px-4 py-3 min-w-[180px]">Arrivée</th>
                                <th class="px-4 py-3 min-w-[130px]">Statut</th>
                                <th class="px-4 py-3 min-w-[100px]">Coût</th>
                                <th class="px-4 py-3 min-w-[90px]">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $statusOptions = [
                                    'requested' => 'Demandée',
                                    'accepted' => 'Acceptée',
                                    'in_progress' => 'En cours',
                                    'completed' => 'Terminée',
                                    'canceled' => 'Annulée',
                                ];
                            @endphp
                            @foreach($ridesForTable as $ride)
                                <tr wire:key="ride-row-{{ $ride['id'] }}" class="bg-white dark:bg-gray-900 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-2 font-medium">{{ $ride['numero'] }}</td>
                                    <td class="px-4 py-2 min-w-[180px] cursor-help"
                                        x-data="{}"
                                        x-tooltip="{ content: @js($ride['start_display']), theme: $store?.theme ?? 'light' }"
                                        title="{{ $ride['start_display'] }}"
                                    >{{ $ride['start_display'] }}</td>
                                    <td class="px-4 py-2 min-w-[180px] cursor-help"
                                        x-data="{}"
                                        x-tooltip="{ content: @js($ride['end_display']), theme: $store?.theme ?? 'light' }"
                                        title="{{ $ride['end_display'] }}"
                                    >{{ $ride['end_display'] }}</td>
                                    <td class="px-4 py-2 min-w-[130px]">
                                        @php
                                            $statusColor = match($ride['ride_status']) {
                                                'requested' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
                                                'accepted' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                                'in_progress' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                                'completed' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                                'canceled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                                default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium {{ $statusColor }}">
                                            {{ $statusOptions[$ride['ride_status']] ?? $ride['ride_status'] }}
                                        </span>
                                    </td>
                                    @php
                                        $costTooltip = $ride['cost'] !== null ? 'Coût : ' . number_format($ride['cost'], 0, ',', ' ') . ' CDF' : '—';
                                    @endphp
                                    <td class="px-4 py-2 min-w-[100px] cursor-help"
                                        x-data="{}"
                                        x-tooltip="{ content: @js($costTooltip), theme: $store?.theme ?? 'light' }"
                                        title="{{ $costTooltip }}"
                                    >{{ $ride['cost'] !== null ? number_format($ride['cost'], 0, ',', ' ') . ' CDF' : '—' }}</td>
                                    <td class="px-4 py-2 min-w-[90px]">
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

        @include('filament.maps.assets')

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
                        if (!window.L || !window.KinTaxiMapKit) {
                            setTimeout(run, 150);
                            return;
                        }
                        var el = document.getElementById(mapId);
                        if (!el) return;
                        try {
                            var L = window.L;
                            var kit = window.KinTaxiMapKit;
                            var map = kit.createMap(mapId, [config.defaultLat || -4.325, config.defaultLng || 15.322], 12);
                            if (!map) return;
                            var allBounds = [];
                            kit.addSearch(map, function(result) {
                                var point = [result[0], result[1]];
                                kit.addPin(map, point, {
                                    label: 'S',
                                    color: kit.colors.neutral,
                                    popup: result[2] || 'Recherche',
                                }).openPopup();
                                map.setView(point, 15);
                            });
                            var greenIcon = L.divIcon({ className: 'rides-marker-depart', html: '<span class="kintaxi-map-pin" style="background:' + kit.colors.start + '">D</span>', iconSize: [28, 28], iconAnchor: [14, 14] });
                            var redIcon = L.divIcon({ className: 'rides-marker-arrivee', html: '<span class="kintaxi-map-pin" style="background:' + kit.colors.end + '">A</span>', iconSize: [28, 28], iconAnchor: [14, 14] });
                            var carSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#eab308" width="28" height="28"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.22.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/></svg>';
                            var carIcon = L.divIcon({ className: 'rides-marker-car', html: carSvg, iconSize: [28, 28], iconAnchor: [14, 14] });

                            function drawRoute(startLat, startLng, endLat, endLng) {
                                kit.drawRoute(map, [startLat, startLng], [endLat, endLng]);
                            }

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
                                    drawRoute(startLat, startLng, endLat, endLng);
                                    var midLat = (startLat + endLat) / 2, midLng = (startLng + endLng) / 2;
                                    L.marker([midLat, midLng], { icon: carIcon }).addTo(map).bindPopup('<strong>Voiture</strong> – ' + label);
                                    allBounds.push([midLat, midLng]);
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
                                        kit.geocode(addr).then(function(result) {
                                            if (result) {
                                                var lat = result[0], lng = result[1];
                                                var label = 'Course #' + ride.id;
                                                if (isEnd) {
                                                    L.marker([lat, lng], { icon: redIcon }).addTo(map).bindPopup('<strong>Arrivée</strong> – ' + label);
                                                    allBounds.push([lat, lng]);
                                                    if (ride._startLat != null && ride._startLng != null) {
                                                        drawRoute(ride._startLat, ride._startLng, lat, lng);
                                                        var midLat = (ride._startLat + lat) / 2, midLng = (ride._startLng + lng) / 2;
                                                        L.marker([midLat, midLng], { icon: carIcon }).addTo(map).bindPopup('<strong>Voiture</strong> – ' + label);
                                                        allBounds.push([midLat, midLng]);
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

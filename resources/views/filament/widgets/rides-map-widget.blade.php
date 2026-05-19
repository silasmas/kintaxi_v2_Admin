<x-filament-widgets::widget>
    @php
        $mapId = $mapId ?? 'rides-map-widget';
        $wireKey = str_replace('.', '-', (string) $this->getId());
    @endphp

    @include('filament.maps.assets')

    <x-filament::section>
        <x-slot name="heading">Aperçu des courses sur la carte</x-slot>
        <x-slot name="description">Cliquez une course pour voir le trajet. Les filtres mettent à jour la carte instantanément.</x-slot>

        <div class="space-y-4">
            {{-- Carte isolée de Livewire ; le loader reste hors wire:ignore pour wire:loading --}}
            <div class="relative overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                <div wire:ignore>
                    <div
                        id="{{ $mapId }}"
                        class="kintaxi-map w-full bg-gray-100 dark:bg-gray-800"
                        style="height:430px;min-height:430px;z-index:1;"
                        data-map-id="{{ $mapId }}"
                        data-default-lat="{{ $mapConfig['defaultLat'] }}"
                        data-default-lng="{{ $mapConfig['defaultLng'] }}"
                    ></div>
                </div>
                <div
                    id="{{ $mapId }}-loader"
                    class="pointer-events-none absolute inset-0 z-20 hidden items-center justify-center bg-white/75 dark:bg-gray-900/75"
                    wire:loading.class.remove="hidden"
                    wire:loading.class.add="flex"
                    wire:target="search, setStatusFilter, selectRide, gotoPage, previousPage, nextPage"
                >
                    <x-filament::loading-indicator class="h-10 w-10 text-primary-600" />
                </div>
            </div>

            <div
                wire:loading.flex
                wire:target="search, setStatusFilter, selectRide, gotoPage, previousPage, nextPage"
                class="hidden items-center gap-2 text-sm text-gray-500 dark:text-gray-400"
            >
                <x-filament::loading-indicator class="h-5 w-5 text-primary-600" />
                <span>Chargement…</span>
            </div>

            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap gap-2">
                    @foreach ($statusOptions as $value => $label)
                        <button
                            type="button"
                            wire:click="setStatusFilter(@js($value === '' ? null : $value))"
                            wire:loading.attr="disabled"
                            wire:target="setStatusFilter"
                            @class([
                                'rounded-lg px-3 py-1.5 text-xs font-medium transition disabled:opacity-60',
                                'bg-primary-600 text-white' => ($statusFilter ?? null) === ($value === '' ? null : $value),
                                'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700' => ($statusFilter ?? null) !== ($value === '' ? null : $value),
                            ])
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
                <div class="w-full lg:max-w-md">
                    <input
                        type="search"
                        wire:model.live.debounce.250ms="search"
                        placeholder="Rechercher chauffeur, client, véhicule, n° course…"
                        class="fi-input block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                    />
                </div>
            </div>

            @if ($ridesPaginator->total() > 0)
                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="overflow-x-auto">
                    <table class="w-full min-w-max text-left text-sm text-gray-900 dark:text-gray-100">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            <tr>
                                <th class="px-3 py-2">#</th>
                                <th class="px-3 py-2">Client</th>
                                <th class="px-3 py-2">Chauffeur</th>
                                <th class="px-3 py-2">Véhicule</th>
                                <th class="px-3 py-2">Départ</th>
                                <th class="px-3 py-2">Arrivée</th>
                                <th class="px-3 py-2">Statut</th>
                                <th class="px-3 py-2">Coût</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ridesPaginator as $ride)
                                @php
                                    $statusColor = \App\Filament\Support\StatusColorHelper::rideStatusCssClasses($ride['ride_status']);
                                @endphp
                                <tr
                                    wire:key="ride-row-{{ $ride['id'] }}"
                                    wire:click="selectRide({{ $ride['id'] }})"
                                    @class([
                                        'cursor-pointer border-t border-gray-200 transition hover:bg-primary-50 dark:border-gray-700 dark:hover:bg-primary-950/30',
                                        'bg-primary-50 ring-1 ring-inset ring-primary-500 dark:bg-primary-950/40' => $focusedRideId === $ride['id'],
                                        'bg-white dark:bg-gray-900' => $focusedRideId !== $ride['id'],
                                    ])
                                >
                                    <td class="px-3 py-2 font-medium">{{ $ride['numero'] }}</td>
                                    <td class="px-3 py-2">
                                        @include('filament.components.participant-inline', ['participant' => $ride['passenger'] ?? null])
                                    </td>
                                    <td class="px-3 py-2">
                                        @include('filament.components.participant-inline', ['participant' => $ride['driver'] ?? null])
                                    </td>
                                    <td class="px-3 py-2">{{ $ride['vehicle_plate'] ?: '—' }}</td>
                                    <td class="max-w-[10rem] truncate px-3 py-2" title="{{ $ride['start_display'] }}">{{ $ride['start_display'] }}</td>
                                    <td class="max-w-[10rem] truncate px-3 py-2" title="{{ $ride['end_display'] }}">{{ $ride['end_display'] }}</td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-medium {{ $statusColor }}">
                                            {{ $statusLabels[$ride['ride_status']] ?? $ride['ride_status'] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-xs">
                                        <span
                                            @if (! empty($ride['cost_tooltip']))
                                                title="{{ $ride['cost_tooltip'] }}"
                                                class="cursor-help border-b border-dotted border-gray-400 dark:border-gray-500"
                                            @endif
                                        >{{ $ride['cost_display'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                    <div class="flex flex-col gap-3 border-t border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            {{ $ridesPaginator->firstItem() }}–{{ $ridesPaginator->lastItem() }}
                            sur {{ $ridesPaginator->total() }} course(s)
                        </p>
                        <div>
                            {{ $ridesPaginator->links() }}
                        </div>
                    </div>
                </div>
            @else
                <p class="rounded-lg border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                    Aucune course ne correspond aux filtres.
                </p>
            @endif
        </div>
    </x-filament::section>

    @script
    <script>
        (function () {
            const mapId = @js($mapId);
            const allMarkers = @js($mapConfig['markers'] ?? []);
            const initialMarkers = @js($initialMarkers ?? []);
            const ridesToGeocode = @js($mapConfig['ridesToGeocode'] ?? []);

            function showMapLoader(show) {
                const loader = document.getElementById(mapId + '-loader');
                if (loader) {
                    loader.classList.toggle('hidden', !show);
                    loader.classList.toggle('flex', show);
                }
            }

            function initMapIfNeeded() {
                if (window.KinTaxiRidesMapApi && window.KinTaxiRidesMapApi[mapId]) {
                    return window.KinTaxiRidesMapApi[mapId];
                }

                const el = document.getElementById(mapId);
                if (!el || !window.L) {
                    return null;
                }

                const lat = parseFloat(el.dataset.defaultLat) || -4.4419;
                const lng = parseFloat(el.dataset.defaultLng) || 15.2663;

                if (el._leaflet_map) {
                    try { el._leaflet_map.remove(); } catch (e) {}
                    delete el._leaflet_id;
                }

                const map = L.map(el, { scrollWheelZoom: true }).setView([lat, lng], 12);
                const isDark = document.documentElement.classList.contains('dark');
                const tileUrl = isDark
                    ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
                    : 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
                L.tileLayer(tileUrl, { attribution: '&copy; OpenStreetMap', maxZoom: 19 }).addTo(map);

                const routeLayer = L.layerGroup().addTo(map);
                const pointsLayer = L.layerGroup().addTo(map);
                const markersById = {};
                allMarkers.forEach((m) => { markersById[m.id] = m; });

                const kit = window.KinTaxiMapKit || {};
                const colors = kit.colors || { start: '#16a34a', end: '#dc2626', neutral: '#171717', zone: '#2563eb' };

                const greenIcon = L.divIcon({
                    className: '',
                    html: '<span class="kintaxi-map-pin" style="background:' + colors.start + '">D</span>',
                    iconSize: [28, 28], iconAnchor: [14, 14],
                });
                const redIcon = L.divIcon({
                    className: '',
                    html: '<span class="kintaxi-map-pin" style="background:' + colors.end + '">A</span>',
                    iconSize: [28, 28], iconAnchor: [14, 14],
                });
                const pointIcon = L.divIcon({
                    className: '',
                    html: '<span class="kintaxi-map-pin" style="background:' + colors.zone + '">•</span>',
                    iconSize: [22, 22], iconAnchor: [11, 11],
                });

                function drawRide(ride) {
                    routeLayer.clearLayers();
                    const sLat = ride.start_lat, sLng = ride.start_lng, eLat = ride.end_lat, eLng = ride.end_lng;
                    if (sLat == null && eLat == null) return;
                    const label = 'Course #' + ride.id;
                    let popup = label;
                    if (ride.passenger_name) popup += '<br>Client: ' + ride.passenger_name;
                    if (ride.driver_name) popup += '<br>Chauffeur: ' + ride.driver_name;
                    const bounds = [];
                    if (sLat != null && sLng != null) {
                        L.marker([sLat, sLng], { icon: greenIcon }).addTo(routeLayer).bindPopup('<strong>Départ</strong><br>' + popup);
                        bounds.push([sLat, sLng]);
                    }
                    if (eLat != null && eLng != null) {
                        L.marker([eLat, eLng], { icon: redIcon }).addTo(routeLayer).bindPopup('<strong>Arrivée</strong><br>' + popup);
                        bounds.push([eLat, eLng]);
                    }
                    if (sLat != null && sLng != null && eLat != null && eLng != null && kit.drawRoute) {
                        kit.drawRoute(map, [sLat, sLng], [eLat, eLng], { layer: routeLayer, popup: label });
                        bounds.push([(sLat + eLat) / 2, (sLng + eLng) / 2]);
                    }
                    if (bounds.length > 1) map.fitBounds(bounds, { padding: [48, 48], maxZoom: 15 });
                    else if (bounds.length === 1) map.setView(bounds[0], 15);
                    setTimeout(() => map.invalidateSize(), 50);
                }

                function showFiltered(markers, focusId) {
                    pointsLayer.clearLayers();
                    routeLayer.clearLayers();
                    const bounds = [];
                    (markers || []).forEach((ride) => {
                        if (ride.start_lat != null && ride.start_lng != null) {
                            L.marker([ride.start_lat, ride.start_lng], { icon: pointIcon })
                                .addTo(pointsLayer)
                                .bindPopup('#' + ride.id + ' – ' + (ride.passenger_name || ''));
                            bounds.push([ride.start_lat, ride.start_lng]);
                        }
                    });
                    if (focusId && markersById[focusId]) {
                        drawRide(markersById[focusId]);
                    } else if (bounds.length > 1) {
                        map.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 });
                    } else if (bounds.length === 1) {
                        map.setView(bounds[0], 14);
                    }
                    setTimeout(() => map.invalidateSize(), 50);
                }

                el._leaflet_map = map;
                window.KinTaxiRidesMapApi = window.KinTaxiRidesMapApi || {};
                window.KinTaxiRidesMapApi[mapId] = { map, drawRide, showFiltered, markersById };

                setTimeout(() => map.invalidateSize(), 100);
                setTimeout(() => map.invalidateSize(), 500);

                if (kit.addSearch) {
                    kit.addSearch(map, (result) => {
                        L.marker([result[0], result[1]], { icon: greenIcon }).addTo(map).bindPopup(result[2] || '').openPopup();
                        map.setView([result[0], result[1]], 15);
                    }, 'Adresse…');
                }

                ridesToGeocode.forEach((ride, i) => {
                    const delay = (i + 1) * 2000;
                    if (ride.start_address && kit.geocode) {
                        setTimeout(() => kit.geocode(ride.start_address).then((r) => {
                            if (r && markersById[ride.id]) {
                                markersById[ride.id].start_lat = r[0];
                                markersById[ride.id].start_lng = r[1];
                            }
                        }), delay);
                    }
                    if (ride.end_address && kit.geocode) {
                        setTimeout(() => kit.geocode(ride.end_address).then((r) => {
                            if (r && markersById[ride.id]) {
                                markersById[ride.id].end_lat = r[0];
                                markersById[ride.id].end_lng = r[1];
                            }
                        }), delay + 1000);
                    }
                });

                showFiltered(initialMarkers.length ? initialMarkers : allMarkers.slice(0, 25), null);

                return window.KinTaxiRidesMapApi[mapId];
            }

            function bootstrap(attempt) {
                if (window.L && document.getElementById(mapId)) {
                    const api = initMapIfNeeded();
                    if (api) return;
                }
                if (attempt < 80) setTimeout(() => bootstrap(attempt + 1), 150);
            }

            document.addEventListener('DOMContentLoaded', () => bootstrap(0));
            document.addEventListener('livewire:navigated', () => bootstrap(0));
            window.addEventListener('load', () => bootstrap(0));

            function normalizeSyncPayload(payload) {
                if (!payload) {
                    return null;
                }
                if (payload.mapId) {
                    return payload;
                }
                if (Array.isArray(payload) && payload[0]?.mapId) {
                    return payload[0];
                }
                if (payload.detail?.mapId) {
                    return payload.detail;
                }

                return null;
            }

            function handleMapSync(payload) {
                const data = normalizeSyncPayload(payload);
                if (!data || data.mapId !== mapId) {
                    return;
                }
                const api = initMapIfNeeded();
                if (api) {
                    api.showFiltered(data.markers || [], data.focusId ?? null);
                }
            }

            $wire.on('rides-map-sync', handleMapSync);

            document.addEventListener('livewire:init', () => {
                Livewire.on('rides-map-sync', (payload) => {
                    handleMapSync(payload);
                });
            });
        })();
    </script>
    @endscript
</x-filament-widgets::widget>


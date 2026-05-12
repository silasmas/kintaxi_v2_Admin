<x-filament-panels::page>
    <div class="space-y-4">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
            <div>
                <label for="live-ride-status" class="text-sm font-medium text-gray-700 dark:text-gray-200">Statut</label>
                <select id="live-ride-status" class="fi-input mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                    <option value="active">Actives</option>
                    <option value="requested">Demandees</option>
                    <option value="accepted">Acceptees</option>
                    <option value="in_progress">En cours</option>
                    <option value="completed">Terminees</option>
                    <option value="canceled">Annulees</option>
                </select>
            </div>
            <div>
                <label for="live-ride-search" class="text-sm font-medium text-gray-700 dark:text-gray-200">Recherche</label>
                <input id="live-ride-search" type="text" placeholder="ID course ou statut"
                    class="fi-input mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                    <input id="live-ride-auto-fit" type="checkbox" checked class="rounded border-gray-300 dark:border-gray-600" />
                    Auto-centrage carte
                </label>
            </div>
            <div class="flex items-end">
                <button id="live-ride-refresh" type="button"
                    class="fi-btn fi-btn-size-md inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500">
                    Rafraichir maintenant
                </button>
            </div>
        </div>

        <div class="kintaxi-map" wire:ignore>
            <div id="live-ride-map" class="w-full" style="height: 540px;"></div>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            La carte se met a jour toutes les 10 secondes. Les pins D, A et C indiquent depart, arrivee et chauffeur.
        </p>
    </div>

    @include('filament.maps.assets')

    @once
        @push('styles')
            <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
            <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
        @endpush
        @push('scripts')
            <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
        @endpush
    @endonce

    @push('scripts')
        <script>
            (function () {
                let map = null;
                let layerGroup = null;
                let markerCluster = null;
                const rideTrails = {};
                const maxTrailPoints = 20;
                let autoFitEnabled = true;

                const initMap = (lat, lng) => {
                    if (!window.L || !window.KinTaxiMapKit || map) return;

                    const kit = window.KinTaxiMapKit;
                    map = kit.createMap('live-ride-map', [lat, lng], 12);
                    if (!map) return;

                    layerGroup = L.layerGroup().addTo(map);
                    markerCluster = L.markerClusterGroup({
                        maxClusterRadius: 55,
                        disableClusteringAtZoom: 15,
                        spiderfyOnMaxZoom: true,
                    });
                    map.addLayer(markerCluster);
                };

                const addClusterPin = (point, options) => {
                    options.addToMap = false;
                    const marker = window.KinTaxiMapKit.addPin(map, point, options);
                    if (marker) markerCluster.addLayer(marker);
                };

                const renderRides = (payload) => {
                    initMap(payload.default_lat ?? -4.325, payload.default_lng ?? 15.322);
                    if (!map || !layerGroup || !markerCluster) return;

                    const kit = window.KinTaxiMapKit;
                    layerGroup.clearLayers();
                    markerCluster.clearLayers();
                    const bounds = [];

                    (payload.rides ?? []).forEach((ride) => {
                        const start = (ride.start_lat != null && ride.start_lng != null) ? [ride.start_lat, ride.start_lng] : null;
                        const end = (ride.end_lat != null && ride.end_lng != null) ? [ride.end_lat, ride.end_lng] : null;
                        const driver = (ride.driver_lat != null && ride.driver_lng != null) ? [ride.driver_lat, ride.driver_lng] : null;

                        if (start) {
                            addClusterPin(start, {
                                label: 'D',
                                color: kit.colors.start,
                                popup: 'Depart - Course #' + ride.id,
                            });
                            bounds.push(start);
                        }

                        if (end) {
                            addClusterPin(end, {
                                label: 'A',
                                color: kit.colors.end,
                                popup: 'Arrivee - Course #' + ride.id,
                            });
                            bounds.push(end);
                        }

                        if (start && end) {
                            kit.drawRoute(map, start, end, {
                                layer: layerGroup,
                                popup: 'Trajet estime - Course #' + ride.id,
                            });
                        }

                        if (driver) {
                            addClusterPin(driver, {
                                label: 'C',
                                color: kit.colors.driver,
                                popup: 'Position chauffeur - Course #' + ride.id + ' (' + ride.ride_status + ')',
                            });
                            bounds.push(driver);

                            const key = String(ride.id);
                            const trail = rideTrails[key] ?? [];
                            const last = trail[trail.length - 1];
                            const hasChanged = !last || last[0] !== driver[0] || last[1] !== driver[1];
                            if (hasChanged) {
                                trail.push(driver);
                                if (trail.length > maxTrailPoints) trail.shift();
                                rideTrails[key] = trail;
                            }

                            if (trail.length > 1) {
                                L.polyline(trail, {
                                    color: '#a855f7',
                                    weight: 3,
                                    opacity: 0.55,
                                    dashArray: '6 6',
                                })
                                    .bindPopup('Historique chauffeur - Course #' + ride.id)
                                    .addTo(layerGroup);
                            }
                        }
                    });

                    if (autoFitEnabled) {
                        kit.fit(map, bounds, { padding: [30, 30], maxZoom: 15, singleZoom: 14 });
                    }
                };

                const refresh = () => {
                    const statusEl = document.getElementById('live-ride-status');
                    const qEl = document.getElementById('live-ride-search');
                    const params = new URLSearchParams();
                    params.set('status', statusEl?.value || 'active');
                    if ((qEl?.value || '').trim() !== '') {
                        params.set('q', qEl.value.trim());
                    }

                    fetch(@js(route('admin.live-ride-tracking.feed')) + '?' + params.toString())
                        .then((response) => response.json())
                        .then((payload) => renderRides(payload))
                        .catch(() => {});
                };

                const boot = () => {
                    if (!window.L || !window.KinTaxiMapKit || !window.L.markerClusterGroup) {
                        return setTimeout(boot, 150);
                    }

                    document.getElementById('live-ride-refresh')?.addEventListener('click', refresh);
                    document.getElementById('live-ride-status')?.addEventListener('change', refresh);
                    document.getElementById('live-ride-search')?.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter') {
                            event.preventDefault();
                            refresh();
                        }
                    });
                    document.getElementById('live-ride-auto-fit')?.addEventListener('change', (event) => {
                        autoFitEnabled = !!event.target.checked;
                    });
                    refresh();
                    setInterval(refresh, 10000);
                };

                boot();
            })();
        </script>
    @endpush
</x-filament-panels::page>

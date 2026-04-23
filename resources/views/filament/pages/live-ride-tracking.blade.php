<x-filament-panels::page>
    <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label for="live-ride-status" class="text-sm font-medium text-gray-700 dark:text-gray-200">Statut</label>
                <select id="live-ride-status" class="mt-1 fi-input block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                    <option value="active">Actives (acceptée + en cours)</option>
                    <option value="requested">Demandées</option>
                    <option value="accepted">Acceptées</option>
                    <option value="in_progress">En cours</option>
                    <option value="completed">Terminées</option>
                    <option value="canceled">Annulées</option>
                </select>
            </div>
            <div>
                <label for="live-ride-search" class="text-sm font-medium text-gray-700 dark:text-gray-200">Recherche</label>
                <input id="live-ride-search" type="text" placeholder="ID course ou statut"
                    class="mt-1 fi-input block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800" />
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
                    Rafraîchir maintenant
                </button>
            </div>
        </div>

        <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700" wire:ignore>
            <div id="live-ride-map" class="w-full bg-gray-100 dark:bg-gray-800" style="height: 520px;"></div>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Cette carte se met à jour automatiquement toutes les 10 secondes. Les points chauffeur gardent un historique (trace) pendant la session.
        </p>
    </div>

    @once
        @push('styles')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="anonymous" />
            <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
            <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
        @endpush
        @push('scripts')
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="anonymous"></script>
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
                    if (!window.L || map) return;
                    map = L.map('live-ride-map').setView([lat, lng], 12);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(map);
                    layerGroup = L.layerGroup().addTo(map);
                    markerCluster = L.markerClusterGroup({
                        maxClusterRadius: 55,
                        disableClusteringAtZoom: 15,
                        spiderfyOnMaxZoom: true,
                    });
                    map.addLayer(markerCluster);
                };

                const renderRides = (payload) => {
                    initMap(payload.default_lat ?? -4.325, payload.default_lng ?? 15.322);
                    if (!map || !layerGroup || !markerCluster) return;

                    layerGroup.clearLayers();
                    markerCluster.clearLayers();
                    const bounds = [];

                    (payload.rides ?? []).forEach((ride) => {
                        const start = (ride.start_lat != null && ride.start_lng != null) ? [ride.start_lat, ride.start_lng] : null;
                        const end = (ride.end_lat != null && ride.end_lng != null) ? [ride.end_lat, ride.end_lng] : null;
                        const driver = (ride.driver_lat != null && ride.driver_lng != null) ? [ride.driver_lat, ride.driver_lng] : null;

                        if (start) {
                            markerCluster.addLayer(
                                L.circleMarker(start, { radius: 6, color: '#16a34a' })
                                    .bindPopup('Départ - Course #' + ride.id)
                            );
                            bounds.push(start);
                        }

                        if (end) {
                            markerCluster.addLayer(
                                L.circleMarker(end, { radius: 6, color: '#dc2626' })
                                    .bindPopup('Arrivée - Course #' + ride.id)
                            );
                            bounds.push(end);
                        }

                        if (start && end) {
                            L.polyline([start, end], { color: '#2563eb', weight: 4, opacity: 0.85 })
                                .bindPopup('Trajet estimé - Course #' + ride.id)
                                .addTo(layerGroup);
                        }

                        if (driver) {
                            markerCluster.addLayer(
                                L.marker(driver)
                                    .bindPopup('Position chauffeur - Course #' + ride.id + ' (' + ride.ride_status + ')')
                            );
                            bounds.push(driver);

                            const key = String(ride.id);
                            const trail = rideTrails[key] ?? [];
                            const last = trail[trail.length - 1];
                            const hasChanged = !last || last[0] !== driver[0] || last[1] !== driver[1];
                            if (hasChanged) {
                                trail.push(driver);
                                if (trail.length > maxTrailPoints) {
                                    trail.shift();
                                }
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

                    if (autoFitEnabled && bounds.length > 1) {
                        map.fitBounds(bounds, { padding: [30, 30], maxZoom: 15 });
                    } else if (autoFitEnabled && bounds.length === 1) {
                        map.setView(bounds[0], 14);
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
                        .then((r) => r.json())
                        .then((payload) => renderRides(payload))
                        .catch(() => {});
                };

                const boot = () => {
                    if (!window.L) return setTimeout(boot, 150);
                    document.getElementById('live-ride-refresh')?.addEventListener('click', refresh);
                    document.getElementById('live-ride-status')?.addEventListener('change', refresh);
                    document.getElementById('live-ride-search')?.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            refresh();
                        }
                    });
                    document.getElementById('live-ride-auto-fit')?.addEventListener('change', (e) => {
                        autoFitEnabled = !!e.target.checked;
                    });
                    refresh();
                    setInterval(refresh, 10000);
                };

                boot();
            })();
        </script>
    @endpush
</x-filament-panels::page>

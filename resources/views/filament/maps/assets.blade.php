@once
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="anonymous" />
        <style>
            .fi-topbar {
                z-index: 1000 !important;
            }

            .kintaxi-map {
                position: relative;
                overflow: hidden;
                border: 1px solid rgb(229 231 235);
                border-radius: 0.75rem;
                background: rgb(243 244 246);
            }

            .dark .kintaxi-map {
                border-color: rgb(55 65 81);
                background: rgb(31 41 55);
            }

            .kintaxi-map .leaflet-container {
                font-family: inherit;
            }

            .kintaxi-map-pin {
                display: flex;
                width: 28px;
                height: 28px;
                align-items: center;
                justify-content: center;
                border: 2px solid #fff;
                border-radius: 9999px;
                box-shadow: 0 8px 20px rgba(15, 23, 42, 0.2);
                color: #fff;
                font-size: 11px;
                font-weight: 700;
                line-height: 1;
            }

            .kintaxi-map-search {
                display: flex;
                width: min(320px, calc(100vw - 4rem));
                overflow: hidden;
                border: 1px solid rgba(15, 23, 42, 0.16);
                border-radius: 0.5rem;
                background: #fff;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.14);
            }

            .dark .kintaxi-map-search {
                border-color: rgba(255, 255, 255, 0.12);
                background: rgb(31 41 55);
            }

            .kintaxi-map-search input {
                min-width: 0;
                flex: 1;
                border: 0;
                background: transparent;
                padding: 0.55rem 0.7rem;
                color: rgb(17 24 39);
                font-size: 0.875rem;
                outline: none;
            }

            .dark .kintaxi-map-search input {
                color: rgb(243 244 246);
            }

            .kintaxi-map-search button,
            .kintaxi-map-locate {
                border: 0;
                background: rgb(17 24 39);
                color: #fff;
                cursor: pointer;
                font-size: 0.8125rem;
                font-weight: 600;
            }

            .kintaxi-map-search button {
                padding: 0 0.75rem;
            }

            .kintaxi-map-locate {
                width: 2.25rem;
                height: 2.25rem;
                border-radius: 0.5rem;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.14);
            }

            .leaflet-popup-content {
                margin: 0.65rem 0.8rem;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="anonymous"></script>
        <script>
            window.KinTaxiMapKit = window.KinTaxiMapKit || (function () {
                const defaultCenter = [-4.4419, 15.2663];
                const colors = {
                    start: '#16a34a',
                    end: '#dc2626',
                    driver: '#eab308',
                    zone: '#2563eb',
                    neutral: '#171717',
                };

                const isDark = () => document.documentElement.classList.contains('dark');

                const tileUrl = () => isDark()
                    ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
                    : 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';

                const addTiles = (map) => L.tileLayer(tileUrl(), {
                    attribution: '&copy; OpenStreetMap',
                    maxZoom: 19,
                }).addTo(map);

                const pinIcon = (label, color = colors.neutral) => L.divIcon({
                    className: '',
                    html: '<span class="kintaxi-map-pin" style="background:' + color + '">' + label + '</span>',
                    iconSize: [28, 28],
                    iconAnchor: [14, 14],
                    popupAnchor: [0, -14],
                });

                const createMap = (id, center = defaultCenter, zoom = 13) => {
                    const el = document.getElementById(id);
                    if (!el || el.dataset.inited === '1') return null;
                    el.dataset.inited = '1';

                    const map = L.map(id, {
                        zoomControl: true,
                        scrollWheelZoom: true,
                    }).setView(center, zoom);

                    addTiles(map);
                    setTimeout(() => map.invalidateSize(), 150);

                    return map;
                };

                const addPin = (map, point, options = {}) => {
                    if (!point || point[0] == null || point[1] == null) return null;

                    const marker = L.marker(point, {
                        icon: pinIcon(options.label || '*', options.color || colors.neutral),
                        draggable: !!options.draggable,
                    })
                        .bindPopup(options.popup || '');

                    if (options.addToMap !== false) {
                        marker.addTo(map);
                    }

                    return marker;
                };

                const fit = (map, points, options = {}) => {
                    const valid = (points || []).filter((point) => point && point[0] != null && point[1] != null);
                    if (valid.length > 1) {
                        map.fitBounds(valid, { padding: options.padding || [36, 36], maxZoom: options.maxZoom || 15 });
                    } else if (valid.length === 1) {
                        map.setView(valid[0], options.singleZoom || 15);
                    }
                };

                const drawRoute = (map, start, end, options = {}) => {
                    if (!start || !end) return;
                    const target = options.layer || map;

                    const fallback = () => L.polyline([start, end], {
                        color: options.color || colors.zone,
                        weight: options.weight || 4,
                        opacity: 0.8,
                        dashArray: options.dashArray || null,
                    }).addTo(target).bindPopup(options.popup || 'Trajet estime');

                    const url = 'https://router.project-osrm.org/route/v1/driving/'
                        + start[1] + ',' + start[0] + ';' + end[1] + ',' + end[0];

                    fetch(url + '?overview=full&geometries=geojson')
                        .then((response) => response.json())
                        .then((data) => {
                            const route = data && data.routes && data.routes[0];
                            if (!route || !route.geometry || !route.geometry.coordinates) {
                                fallback();
                                return;
                            }

                            L.polyline(route.geometry.coordinates.map((coord) => [coord[1], coord[0]]), {
                                color: options.color || colors.zone,
                                weight: options.weight || 4,
                                opacity: 0.85,
                            }).addTo(target).bindPopup(options.popup || 'Trajet estime');
                        })
                        .catch(fallback);
                };

                const geocode = (query) => fetch('https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(query) + '&format=json&limit=1', {
                    headers: { Accept: 'application/json' },
                })
                    .then((response) => response.json())
                    .then((data) => data && data[0] ? [parseFloat(data[0].lat), parseFloat(data[0].lon), data[0].display_name] : null);

                const addSearch = (map, callback, placeholder = 'Rechercher une adresse') => {
                    const control = L.control({ position: 'topleft' });

                    control.onAdd = function () {
                        const wrapper = L.DomUtil.create('div', 'kintaxi-map-search');
                        wrapper.innerHTML = '<input type="search" placeholder="' + placeholder + '" /><button type="button">OK</button>';
                        L.DomEvent.disableClickPropagation(wrapper);
                        L.DomEvent.disableScrollPropagation(wrapper);

                        const input = wrapper.querySelector('input');
                        const button = wrapper.querySelector('button');
                        const submit = () => {
                            const value = (input.value || '').trim();
                            if (!value) return;

                            button.disabled = true;
                            geocode(value)
                                .then((result) => {
                                    if (result) callback(result);
                                })
                                .finally(() => {
                                    button.disabled = false;
                                });
                        };

                        button.addEventListener('click', submit);
                        input.addEventListener('keydown', (event) => {
                            if (event.key === 'Enter') {
                                event.preventDefault();
                                submit();
                            }
                        });

                        return wrapper;
                    };

                    control.addTo(map);
                };

                const addLocate = (map, callback) => {
                    const control = L.control({ position: 'topleft' });

                    control.onAdd = function () {
                        const button = L.DomUtil.create('button', 'kintaxi-map-locate');
                        button.type = 'button';
                        button.title = 'Utiliser ma position';
                        button.textContent = 'GPS';
                        L.DomEvent.disableClickPropagation(button);
                        button.addEventListener('click', () => {
                            if (!navigator.geolocation) return;
                            navigator.geolocation.getCurrentPosition((position) => {
                                callback([position.coords.latitude, position.coords.longitude]);
                            });
                        });

                        return button;
                    };

                    control.addTo(map);
                };

                return {
                    colors,
                    defaultCenter,
                    createMap,
                    addPin,
                    fit,
                    drawRoute,
                    addSearch,
                    addLocate,
                    geocode,
                };
            })();
        </script>
    @endpush
@endonce

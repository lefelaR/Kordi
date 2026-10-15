(() => {
    const mapElement = document.querySelector('[data-venues-map]');
    if (!mapElement || typeof L === 'undefined') {
        return;
    }

    const capeTown = [-33.9249, 18.4241];
    const defaultZoom = 12;

    L.Icon.Default.imagePath = '/assets/leaflet/images/';

    const map = L.map(mapElement).setView(capeTown, defaultZoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(map);

    const markersLayer = L.layerGroup().addTo(map);
    const geocodeCache = new Map();
    let renderId = 0;

    const parseCoordinate = (value) => {
        if (value === '' || value == null) {
            return null;
        }
        const number = Number(value);
        return Number.isFinite(number) ? number : null;
    };

    const readPoints = () => {
        const element = document.querySelector('[data-venues-map-points]');
        if (!element) {
            return [];
        }

        try {
            const parsed = JSON.parse(element.textContent || '[]');
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    };

    const popupHtml = (point, address) => {
        const name = typeof point.name === 'string' ? point.name : '';
        const location = address || (typeof point.location === 'string' ? point.location : '');
        const title = name !== '' ? name : 'Venue';
        const body = location !== '' ? `<br>${escapeHtml(location)}` : '';
        return `<strong>${escapeHtml(title)}</strong>${body}`;
    };

    const escapeHtml = (value) => value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

    const addMarker = (lat, lng, point, address) => {
        const marker = L.marker([lat, lng]);
        marker.bindPopup(popupHtml(point, address));
        markersLayer.addLayer(marker);
        return [lat, lng];
    };

    const geocodeAddress = async (address) => {
        const key = address.trim().toLowerCase();
        if (geocodeCache.has(key)) {
            return geocodeCache.get(key);
        }

        const url = 'https://nominatim.openstreetmap.org/search?' + new URLSearchParams({
            format: 'json',
            q: address,
            limit: '1',
            countrycodes: 'za',
        }).toString();

        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) {
                geocodeCache.set(key, null);
                return null;
            }
            const results = await response.json();
            if (!Array.isArray(results) || results.length === 0) {
                geocodeCache.set(key, null);
                return null;
            }
            const lat = parseCoordinate(results[0].lat);
            const lng = parseCoordinate(results[0].lon);
            const coords = lat !== null && lng !== null ? { lat, lng } : null;
            geocodeCache.set(key, coords);
            return coords;
        } catch (error) {
            geocodeCache.set(key, null);
            return null;
        }
    };

    const sleep = (ms) => new Promise((resolve) => {
        window.setTimeout(resolve, ms);
    });

    const renderMarkers = async () => {
        const currentRender = ++renderId;
        markersLayer.clearLayers();
        map.setView(capeTown, defaultZoom);

        const points = readPoints();
        const bounds = [];
        const pendingGeocode = [];

        points.forEach((point) => {
            if (!point || typeof point !== 'object') {
                return;
            }
            const lat = parseCoordinate(point.latitude);
            const lng = parseCoordinate(point.longitude);
            if (lat !== null && lng !== null) {
                bounds.push(addMarker(lat, lng, point));
                return;
            }
            const location = typeof point.location === 'string' ? point.location.trim() : '';
            if (location !== '') {
                pendingGeocode.push(point);
            }
        });

        if (bounds.length > 0 && currentRender === renderId) {
            map.fitBounds(bounds, {
                padding: [36, 36],
                maxZoom: 14,
            });
        }

        for (let index = 0; index < pendingGeocode.length; index += 1) {
            if (currentRender !== renderId) {
                return;
            }
            const point = pendingGeocode[index];
            const coords = await geocodeAddress(point.location);
            if (currentRender !== renderId) {
                return;
            }
            if (coords) {
                bounds.push(addMarker(coords.lat, coords.lng, point, point.location));
                if (bounds.length > 0) {
                    map.fitBounds(bounds, {
                        padding: [36, 36],
                        maxZoom: 14,
                    });
                }
            }
            if (index < pendingGeocode.length - 1) {
                await sleep(1100);
            }
        }
    };

    window.setTimeout(() => {
        map.invalidateSize();
        renderMarkers();
    }, 50);

    document.addEventListener('venues:results-updated', () => {
        window.setTimeout(() => {
            map.invalidateSize();
            renderMarkers();
        }, 50);
    });
})();

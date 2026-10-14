(() => {
    const root = document.querySelector('[data-venue-map]');
    if (!root || typeof L === 'undefined') {
        return;
    }

    const mapElement = root.querySelector('[data-venue-map-canvas]');
    const locationInput = document.querySelector('input[name="location"]');
    const latitudeInput = document.querySelector('input[name="latitude"]');
    const longitudeInput = document.querySelector('input[name="longitude"]');
    if (!mapElement || !locationInput || !latitudeInput || !longitudeInput) {
        return;
    }

    const defaultCenter = [-28.4793, 24.6722];
    const defaultZoom = 5;
    const markerZoom = 14;

    L.Icon.Default.mergeOptions({
        iconUrl: '/assets/leaflet/images/marker-icon.png',
        iconRetinaUrl: '/assets/leaflet/images/marker-icon-2x.png',
        shadowUrl: '/assets/leaflet/images/marker-shadow.png',
    });

    const map = L.map(mapElement).setView(defaultCenter, defaultZoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(map);

    let marker = null;
    let geocodeId = 0;
    let debounceId = 0;

    const parseCoordinate = (value) => {
        if (value === '' || value == null) {
            return null;
        }
        const number = Number(value);
        return Number.isFinite(number) ? number : null;
    };

    const setMarker = (lat, lng, { fly = true } = {}) => {
        const position = [lat, lng];
        if (marker) {
            marker.setLatLng(position);
        } else {
            marker = L.marker(position, { draggable: true }).addTo(map);
            marker.on('dragend', () => {
                const next = marker.getLatLng();
                writeCoordinates(next.lat, next.lng);
            });
        }
        writeCoordinates(lat, lng);
        if (fly) {
            map.flyTo(position, Math.max(map.getZoom(), markerZoom));
        } else {
            map.setView(position, Math.max(map.getZoom(), markerZoom));
        }
    };

    const clearMarker = () => {
        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }
        latitudeInput.value = '';
        longitudeInput.value = '';
        map.setView(defaultCenter, defaultZoom);
    };

    const writeCoordinates = (lat, lng) => {
        latitudeInput.value = lat.toFixed(7);
        longitudeInput.value = lng.toFixed(7);
    };

    const geocodeLocation = async (query) => {
        const trimmed = query.trim();
        if (trimmed === '') {
            clearMarker();
            return;
        }

        const requestId = ++geocodeId;
        const url = 'https://nominatim.openstreetmap.org/search?' + new URLSearchParams({
            format: 'json',
            q: trimmed,
            limit: '1',
        }).toString();

        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
            });
            if (!response.ok || requestId !== geocodeId) {
                return;
            }
            const results = await response.json();
            if (requestId !== geocodeId || !Array.isArray(results) || results.length === 0) {
                return;
            }
            const lat = Number(results[0].lat);
            const lng = Number(results[0].lon);
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                return;
            }
            setMarker(lat, lng);
        } catch (error) {
            // Keep any existing pin if geocoding fails.
        }
    };

    const initialLat = parseCoordinate(latitudeInput.value);
    const initialLng = parseCoordinate(longitudeInput.value);
    if (initialLat !== null && initialLng !== null) {
        setMarker(initialLat, initialLng, { fly: false });
    } else if (locationInput.value.trim() !== '') {
        geocodeLocation(locationInput.value);
    }

    locationInput.addEventListener('input', () => {
        window.clearTimeout(debounceId);
        debounceId = window.setTimeout(() => {
            geocodeLocation(locationInput.value);
        }, 500);
    });

    map.on('click', (event) => {
        setMarker(event.latlng.lat, event.latlng.lng);
    });

    window.setTimeout(() => {
        map.invalidateSize();
    }, 0);
})();

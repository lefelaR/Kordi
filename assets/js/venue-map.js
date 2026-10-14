(() => {
    const form = document.querySelector('[data-venue-form]');
    const modal = document.querySelector('[data-venue-map-modal]');
    const openButton = document.querySelector('[data-venue-map-open]');
    if (!form || !modal || !openButton || typeof L === 'undefined') {
        return;
    }

    const mapElement = modal.querySelector('[data-venue-map-modal-canvas]');
    const addressElement = modal.querySelector('[data-venue-map-modal-address]');
    const applyButton = modal.querySelector('[data-venue-map-apply]');
    const closeButtons = modal.querySelectorAll('[data-venue-map-close]');
    const locationInput = form.querySelector('input[name="location"]');
    const latitudeInput = form.querySelector('input[name="latitude"]');
    const longitudeInput = form.querySelector('input[name="longitude"]');

    if (!mapElement || !addressElement || !applyButton || !locationInput || !latitudeInput || !longitudeInput) {
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

    let map = null;
    let marker = null;
    let reverseId = 0;
    let pending = null;

    const parseCoordinate = (value) => {
        if (value === '' || value == null) {
            return null;
        }
        const number = Number(value);
        return Number.isFinite(number) ? number : null;
    };

    const ensureMap = () => {
        if (map) {
            return map;
        }

        map = L.map(mapElement).setView(defaultCenter, defaultZoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        }).addTo(map);

        map.on('click', (event) => {
            setPending(event.latlng.lat, event.latlng.lng);
        });

        return map;
    };

    const setMarker = (lat, lng) => {
        const position = [lat, lng];
        if (marker) {
            marker.setLatLng(position);
        } else {
            marker = L.marker(position).addTo(map);
        }
        map.setView(position, Math.max(map.getZoom(), markerZoom));
    };

    const formatAddress = (result) => {
        if (!result || typeof result !== 'object') {
            return '';
        }
        if (typeof result.display_name === 'string' && result.display_name.trim() !== '') {
            return result.display_name.trim();
        }
        return '';
    };

    const setPending = async (lat, lng) => {
        setMarker(lat, lng);
        pending = { lat, lng, address: '' };
        applyButton.disabled = true;
        addressElement.textContent = 'Looking up address…';

        const requestId = ++reverseId;
        const url = 'https://nominatim.openstreetmap.org/reverse?' + new URLSearchParams({
            format: 'json',
            lat: String(lat),
            lon: String(lng),
            zoom: '18',
            addressdetails: '1',
        }).toString();

        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
            });
            if (!response.ok || requestId !== reverseId) {
                return;
            }
            const result = await response.json();
            if (requestId !== reverseId) {
                return;
            }
            const address = formatAddress(result);
            pending = {
                lat,
                lng,
                address: address !== '' ? address : lat.toFixed(5) + ', ' + lng.toFixed(5),
            };
            addressElement.textContent = pending.address;
            applyButton.disabled = false;
        } catch (error) {
            if (requestId !== reverseId) {
                return;
            }
            pending = {
                lat,
                lng,
                address: lat.toFixed(5) + ', ' + lng.toFixed(5),
            };
            addressElement.textContent = pending.address;
            applyButton.disabled = false;
        }
    };

    const openModal = () => {
        ensureMap();
        pending = null;
        applyButton.disabled = true;

        const lat = parseCoordinate(latitudeInput.value);
        const lng = parseCoordinate(longitudeInput.value);
        if (lat !== null && lng !== null) {
            setMarker(lat, lng);
            pending = {
                lat,
                lng,
                address: locationInput.value.trim(),
            };
            addressElement.textContent = pending.address !== ''
                ? pending.address
                : 'Current pin selected. Click elsewhere to change.';
            applyButton.disabled = false;
        } else {
            if (marker) {
                map.removeLayer(marker);
                marker = null;
            }
            map.setView(defaultCenter, defaultZoom);
            addressElement.textContent = 'Click a point on the map.';
        }

        if (typeof modal.showModal === 'function') {
            modal.showModal();
        } else {
            modal.setAttribute('open', '');
        }

        window.setTimeout(() => {
            map.invalidateSize();
            if (lat !== null && lng !== null) {
                map.setView([lat, lng], Math.max(map.getZoom(), markerZoom));
            }
        }, 50);
    };

    const closeModal = () => {
        if (typeof modal.close === 'function') {
            modal.close();
        } else {
            modal.removeAttribute('open');
        }
    };

    const applySelection = () => {
        if (!pending) {
            return;
        }
        locationInput.value = pending.address.slice(0, 255);
        latitudeInput.value = pending.lat.toFixed(7);
        longitudeInput.value = pending.lng.toFixed(7);
        locationInput.dispatchEvent(new Event('input', { bubbles: true }));
        closeModal();
    };

    openButton.addEventListener('click', openModal);
    applyButton.addEventListener('click', applySelection);
    closeButtons.forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    modal.addEventListener('cancel', (event) => {
        event.preventDefault();
        closeModal();
    });
})();

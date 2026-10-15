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
    const previewElement = document.querySelector('[data-venue-preview-map]');
    const locationInput = form.querySelector('[data-venue-location-input]')
        || form.querySelector('input[name="location"]');
    const suggestionsList = form.querySelector('[data-venue-location-suggestions]');
    const latitudeInput = form.querySelector('input[name="latitude"]');
    const longitudeInput = form.querySelector('input[name="longitude"]');

    if (!mapElement || !addressElement || !applyButton || !locationInput || !latitudeInput || !longitudeInput) {
        return;
    }

    const capeTown = [-33.9249, 18.4241];
    const defaultZoom = 12;
    const markerZoom = 14;

    L.Icon.Default.imagePath = '/assets/leaflet/images/';

    let map = null;
    let marker = null;
    let previewMap = null;
    let previewMarker = null;
    let reverseId = 0;
    let suggestId = 0;
    let pending = null;
    let suggestions = [];
    let activeSuggestion = -1;
    let debounceId = 0;
    let suppressSuggest = false;

    const parseCoordinate = (value) => {
        if (value === '' || value == null) {
            return null;
        }
        const number = Number(value);
        return Number.isFinite(number) ? number : null;
    };

    const addTileLayer = (target) => {
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        }).addTo(target);
    };

    const ensureMap = () => {
        if (map) {
            return map;
        }

        map = L.map(mapElement).setView(capeTown, defaultZoom);
        addTileLayer(map);

        map.on('click', (event) => {
            setPending(event.latlng.lat, event.latlng.lng);
        });

        return map;
    };

    const ensurePreviewMap = () => {
        if (!previewElement) {
            return null;
        }
        if (previewMap) {
            return previewMap;
        }

        previewMap = L.map(previewElement).setView(capeTown, defaultZoom);
        addTileLayer(previewMap);

        return previewMap;
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

    const syncPreviewMap = () => {
        const preview = ensurePreviewMap();
        if (!preview) {
            return;
        }

        const lat = parseCoordinate(latitudeInput.value);
        const lng = parseCoordinate(longitudeInput.value);
        if (lat === null || lng === null) {
            if (previewMarker) {
                preview.removeLayer(previewMarker);
                previewMarker = null;
            }
            preview.setView(capeTown, defaultZoom);
            window.setTimeout(() => preview.invalidateSize(), 50);
            return;
        }

        const position = [lat, lng];
        if (previewMarker) {
            previewMarker.setLatLng(position);
        } else {
            previewMarker = L.marker(position).addTo(preview);
        }

        const address = locationInput.value.trim();
        if (address !== '') {
            previewMarker.bindPopup(address);
        }

        preview.setView(position, markerZoom);
        window.setTimeout(() => preview.invalidateSize(), 50);
    };

    const applyLocation = (address, lat, lng) => {
        suppressSuggest = true;
        locationInput.value = address.slice(0, 255);
        latitudeInput.value = lat.toFixed(7);
        longitudeInput.value = lng.toFixed(7);
        hideSuggestions();
        syncPreviewMap();
        window.setTimeout(() => {
            suppressSuggest = false;
        }, 0);
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

    const hideSuggestions = () => {
        if (!suggestionsList) {
            return;
        }
        suggestions = [];
        activeSuggestion = -1;
        suggestionsList.innerHTML = '';
        suggestionsList.hidden = true;
        locationInput.setAttribute('aria-expanded', 'false');
    };

    const setActiveSuggestion = (index) => {
        if (!suggestionsList) {
            return;
        }
        const buttons = suggestionsList.querySelectorAll('[data-venue-suggestion-index]');
        if (buttons.length === 0) {
            activeSuggestion = -1;
            return;
        }
        activeSuggestion = ((index % buttons.length) + buttons.length) % buttons.length;
        buttons.forEach((button, buttonIndex) => {
            button.setAttribute('aria-selected', buttonIndex === activeSuggestion ? 'true' : 'false');
        });
        buttons[activeSuggestion].scrollIntoView({ block: 'nearest' });
    };

    const renderSuggestions = (items, query) => {
        if (!suggestionsList) {
            return;
        }

        suggestions = items;
        activeSuggestion = -1;
        suggestionsList.innerHTML = '';

        if (items.length === 0) {
            const empty = document.createElement('li');
            empty.className = 'dashboard-location-suggestion-empty';
            empty.textContent = query === '' ? '' : 'No matching addresses.';
            if (query === '') {
                hideSuggestions();
                return;
            }
            suggestionsList.appendChild(empty);
            suggestionsList.hidden = false;
            locationInput.setAttribute('aria-expanded', 'true');
            return;
        }

        items.forEach((item, index) => {
            const option = document.createElement('li');
            option.setAttribute('role', 'option');
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'dashboard-location-suggestion';
            button.setAttribute('data-venue-suggestion-index', String(index));
            button.setAttribute('aria-selected', 'false');
            button.textContent = item.address;
            button.addEventListener('mousedown', (event) => {
                event.preventDefault();
            });
            button.addEventListener('click', () => {
                applyLocation(item.address, item.lat, item.lng);
            });
            option.appendChild(button);
            suggestionsList.appendChild(option);
        });

        suggestionsList.hidden = false;
        locationInput.setAttribute('aria-expanded', 'true');
    };

    const fetchSuggestions = async (query) => {
        const requestId = ++suggestId;
        const url = 'https://nominatim.openstreetmap.org/search?' + new URLSearchParams({
            format: 'json',
            q: query,
            addressdetails: '1',
            limit: '5',
            countrycodes: 'za',
            viewbox: '18.3,-34.1,18.7,-33.7',
            bounded: '0',
        }).toString();

        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
            });
            if (!response.ok || requestId !== suggestId) {
                return;
            }
            const results = await response.json();
            if (requestId !== suggestId) {
                return;
            }
            if (!Array.isArray(results)) {
                renderSuggestions([], query);
                return;
            }

            const items = [];
            results.forEach((result) => {
                const address = formatAddress(result);
                const lat = parseCoordinate(result.lat);
                const lng = parseCoordinate(result.lon);
                if (address === '' || lat === null || lng === null) {
                    return;
                }
                items.push({ address, lat, lng });
            });
            renderSuggestions(items, query);
        } catch (error) {
            if (requestId === suggestId) {
                hideSuggestions();
            }
        }
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
        hideSuggestions();
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
            map.setView(capeTown, defaultZoom);
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
        applyLocation(pending.address, pending.lat, pending.lng);
        closeModal();
    };

    locationInput.addEventListener('input', () => {
        if (suppressSuggest) {
            return;
        }

        latitudeInput.value = '';
        longitudeInput.value = '';
        syncPreviewMap();

        window.clearTimeout(debounceId);
        const query = locationInput.value.trim();
        if (query.length < 3) {
            hideSuggestions();
            return;
        }

        debounceId = window.setTimeout(() => {
            fetchSuggestions(query);
        }, 300);
    });

    locationInput.addEventListener('keydown', (event) => {
        if (!suggestionsList || suggestionsList.hidden) {
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setActiveSuggestion(activeSuggestion + 1);
            return;
        }
        if (event.key === 'ArrowUp') {
            event.preventDefault();
            setActiveSuggestion(activeSuggestion - 1);
            return;
        }
        if (event.key === 'Enter' && activeSuggestion >= 0 && suggestions[activeSuggestion]) {
            event.preventDefault();
            const selected = suggestions[activeSuggestion];
            applyLocation(selected.address, selected.lat, selected.lng);
            return;
        }
        if (event.key === 'Escape') {
            hideSuggestions();
        }
    });

    locationInput.addEventListener('blur', () => {
        window.setTimeout(() => {
            hideSuggestions();
        }, 150);
    });

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

    syncPreviewMap();
})();

(() => {
    const form = document.querySelector('[data-event-form]');
    if (!form) {
        return;
    }

    const locationInput = form.querySelector('[data-event-location-input]');
    const suggestionsList = form.querySelector('[data-event-location-suggestions]');
    if (!locationInput || !suggestionsList) {
        return;
    }

    let suggestId = 0;
    let suggestions = [];
    let activeSuggestion = -1;
    let debounceId = 0;
    let suppressSuggest = false;

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
        suggestions = [];
        activeSuggestion = -1;
        suggestionsList.innerHTML = '';
        suggestionsList.hidden = true;
        locationInput.setAttribute('aria-expanded', 'false');
    };

    const applyAddress = (address) => {
        suppressSuggest = true;
        locationInput.value = address.slice(0, 255);
        hideSuggestions();
        window.setTimeout(() => {
            suppressSuggest = false;
        }, 0);
    };

    const setActiveSuggestion = (index) => {
        const buttons = suggestionsList.querySelectorAll('[data-event-suggestion-index]');
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
        suggestions = items;
        activeSuggestion = -1;
        suggestionsList.innerHTML = '';

        if (items.length === 0) {
            if (query === '') {
                hideSuggestions();
                return;
            }
            const empty = document.createElement('li');
            empty.className = 'dashboard-location-suggestion-empty';
            empty.textContent = 'No matching addresses.';
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
            button.setAttribute('data-event-suggestion-index', String(index));
            button.setAttribute('aria-selected', 'false');
            button.textContent = item.address;
            button.addEventListener('mousedown', (event) => {
                event.preventDefault();
            });
            button.addEventListener('click', () => {
                applyAddress(item.address);
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
            const seen = new Set();
            results.forEach((result) => {
                const address = formatAddress(result);
                if (address === '' || seen.has(address)) {
                    return;
                }
                seen.add(address);
                items.push({ address });
            });
            renderSuggestions(items, query);
        } catch (error) {
            if (requestId === suggestId) {
                hideSuggestions();
            }
        }
    };

    locationInput.addEventListener('input', () => {
        if (suppressSuggest) {
            return;
        }

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
        if (suggestionsList.hidden) {
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
            applyAddress(suggestions[activeSuggestion].address);
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
})();

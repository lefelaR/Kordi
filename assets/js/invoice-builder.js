(() => {
    const root = document.querySelector('[data-invoice-builder]');
    if (!root) {
        return;
    }

    const itemsInput = root.querySelector('[data-invoice-items-input]');
    const companySelect = root.querySelector('[data-invoice-company]');
    const numberInput = root.querySelector('[data-invoice-number]');
    const titleInput = root.querySelector('[data-invoice-title]');
    const eventInput = root.querySelector('[data-invoice-event]');
    const venueInput = root.querySelector('[data-invoice-venue]');
    const dateInput = root.querySelector('[data-invoice-date]');
    const dueInput = root.querySelector('[data-invoice-due]');
    const notesInput = root.querySelector('[data-invoice-notes]');
    const list = root.querySelector('[data-invoice-item-list]');
    const emptyState = root.querySelector('[data-invoice-items-empty]');
    const countBadge = root.querySelector('[data-invoice-item-count]');
    const descriptionInput = root.querySelector('[data-invoice-item-description]');
    const amountInput = root.querySelector('[data-invoice-item-amount]');
    const addButton = root.querySelector('[data-invoice-item-add]');

    const previewCompany = root.querySelector('[data-preview-company]');
    const previewAtt = root.querySelector('[data-preview-att]');
    const previewNumber = root.querySelector('[data-preview-number]');
    const previewDate = root.querySelector('[data-preview-date]');
    const previewDue = root.querySelector('[data-preview-due]');
    const previewEventWrap = root.querySelector('[data-preview-event-wrap]');
    const previewEvent = root.querySelector('[data-preview-event]');
    const previewVenueWrap = root.querySelector('[data-preview-venue-wrap]');
    const previewVenue = root.querySelector('[data-preview-venue]');
    const previewNotes = root.querySelector('[data-preview-notes]');
    const previewNotesText = root.querySelector('[data-preview-notes-text]');
    const previewItems = root.querySelector('[data-preview-items]');
    const previewTotal = root.querySelector('[data-preview-total]');
    const previewDeposit = root.querySelector('[data-preview-deposit]');
    const previewTermEvent = root.querySelector('[data-preview-term-event]');
    const previewTermNumber = root.querySelector('[data-preview-term-number]');

    let items = [];
    try {
        const parsed = JSON.parse(itemsInput?.value || '[]');
        if (Array.isArray(parsed)) {
            items = parsed
                .filter((item) => item && typeof item.description === 'string')
                .map((item) => ({
                    description: item.description,
                    amount: Number(item.amount ?? item.unit_price ?? 0),
                }));
        }
    } catch (_error) {
        items = [];
    }

    const money = (value) =>
        `R ${Number(value || 0).toLocaleString('en-ZA', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`;

    const formatDate = (value) => {
        if (!value) {
            return 'Not set';
        }
        const date = new Date(`${value}T00:00:00`);
        if (Number.isNaN(date.getTime())) {
            return value;
        }
        return date.toLocaleDateString('en-GB', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });
    };

    const selectedCompany = () => {
        const option = companySelect?.selectedOptions?.[0];
        return {
            name: option?.getAttribute('data-company-name') || option?.textContent?.trim() || 'Company',
            contact: option?.getAttribute('data-company-contact') || '',
            email: option?.getAttribute('data-company-email') || '',
            phone: option?.getAttribute('data-company-phone') || '',
        };
    };

    const syncHidden = () => {
        if (itemsInput) {
            itemsInput.value = JSON.stringify(items);
        }
    };

    const renderItems = () => {
        if (!list || !emptyState || !countBadge) {
            return;
        }

        list.innerHTML = '';
        countBadge.textContent = String(items.length);
        emptyState.hidden = items.length > 0;

        items.forEach((item, index) => {
            const li = document.createElement('li');
            li.className = 'dashboard-invoice-builder-item';
            li.innerHTML = `
                <div>
                    <strong></strong>
                    <span></span>
                </div>
                <button type="button" class="dashboard-button dashboard-button-secondary dashboard-icon-button" aria-label="Remove item">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                </button>
            `;
            li.querySelector('strong').textContent = item.description;
            li.querySelector('span').textContent = money(item.amount);
            li.querySelector('button').addEventListener('click', () => {
                items.splice(index, 1);
                syncHidden();
                renderItems();
                renderPreview();
            });
            list.appendChild(li);
        });
    };

    const renderPreview = () => {
        const company = selectedCompany();
        const invoiceNumber = numberInput?.value.trim() || 'INV-000';
        const eventLabel = eventInput?.value.trim() || '';
        const venue = venueInput?.value.trim() || '';
        const notes = notesInput?.value.trim() || '';

        if (previewCompany) {
            previewCompany.textContent = company.name;
        }
        if (previewAtt) {
            const parts = [];
            if (company.contact) {
                parts.push(`Att: ${company.contact}`);
            }
            if (company.phone) {
                parts.push(company.phone);
            }
            if (company.email) {
                parts.push(company.email);
            }
            const attLine = parts.join(' | ');
            previewAtt.hidden = attLine === '';
            previewAtt.textContent = attLine;
        }
        if (previewNumber) {
            previewNumber.textContent = invoiceNumber;
        }
        if (previewDate) {
            previewDate.textContent = formatDate(dateInput?.value || '');
        }
        if (previewDue) {
            previewDue.textContent = formatDate(dueInput?.value || '');
        }
        if (previewEventWrap && previewEvent) {
            previewEventWrap.hidden = eventLabel === '';
            previewEvent.textContent = eventLabel;
        }
        if (previewVenueWrap && previewVenue) {
            previewVenueWrap.hidden = venue === '';
            previewVenue.textContent = venue;
        }
        if (previewNotes && previewNotesText) {
            previewNotes.hidden = notes === '';
            previewNotesText.textContent = notes;
        }
        if (previewTermNumber) {
            previewTermNumber.textContent = invoiceNumber;
        }
        if (previewTermEvent) {
            if (eventLabel && venue) {
                previewTermEvent.textContent = `Event Date: ${eventLabel} at ${venue}.`;
            } else if (eventLabel) {
                previewTermEvent.textContent = `Event: ${eventLabel}.`;
            } else if (venue) {
                previewTermEvent.textContent = `Venue: ${venue}.`;
            } else {
                previewTermEvent.textContent = 'Event details will appear here once set.';
            }
        }

        if (!previewItems || !previewTotal) {
            return;
        }

        previewItems.innerHTML = '';
        let total = 0;

        if (items.length === 0) {
            const row = document.createElement('tr');
            row.setAttribute('data-preview-empty-row', '');
            row.innerHTML = '<td colspan="2">Add items on the left to build this invoice.</td>';
            previewItems.appendChild(row);
        } else {
            items.forEach((item) => {
                total += Number(item.amount) || 0;
                const row = document.createElement('tr');
                row.innerHTML = '<td></td><td></td>';
                const cells = row.querySelectorAll('td');
                cells[0].textContent = item.description;
                cells[1].textContent = money(item.amount);
                previewItems.appendChild(row);
            });
        }

        previewTotal.textContent = money(total);
        if (previewDeposit) {
            previewDeposit.textContent = money(total * 0.5);
        }
    };

    const addItem = () => {
        const description = descriptionInput?.value.trim() || '';
        const amount = Number(amountInput?.value || 0);

        if (description === '' || description.length > 255) {
            descriptionInput?.focus();
            return;
        }
        if (!Number.isFinite(amount) || amount < 0) {
            amountInput?.focus();
            return;
        }

        items.push({
            description,
            amount: Math.round(amount * 100) / 100,
        });

        if (descriptionInput) {
            descriptionInput.value = '';
        }
        if (amountInput) {
            amountInput.value = '0';
        }

        syncHidden();
        renderItems();
        renderPreview();
        descriptionInput?.focus();
    };

    addButton?.addEventListener('click', addItem);
    descriptionInput?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            addItem();
        }
    });
    amountInput?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            addItem();
        }
    });

    [
        companySelect,
        numberInput,
        titleInput,
        eventInput,
        venueInput,
        dateInput,
        dueInput,
        notesInput,
    ].forEach((field) => {
        field?.addEventListener('input', renderPreview);
        field?.addEventListener('change', renderPreview);
    });

    syncHidden();
    renderItems();
    renderPreview();
})();

const dialog = document.querySelector('#event-modal');

if (dialog instanceof HTMLDialogElement) {
	const title = dialog.querySelector('[data-event-modal-title]');
	const fields = {
		date: dialog.querySelector('[data-event-field="date"]'),
		client: dialog.querySelector('[data-event-field="client"]'),
		location: dialog.querySelector('[data-event-field="location"]'),
		status: dialog.querySelector('[data-event-field="status"]'),
		size: dialog.querySelector('[data-event-field="size"]'),
	};

	document.querySelectorAll('[data-event-open]').forEach((button) => {
		button.addEventListener('click', () => {
			if (!(button instanceof HTMLButtonElement) || !(title instanceof HTMLElement)) {
				return;
			}

			title.textContent = button.dataset.eventTitle ?? '';
			Object.entries(fields).forEach(([name, field]) => {
				if (!(field instanceof HTMLElement)) {
					return;
				}
				field.textContent = button.dataset[`event${name.charAt(0).toUpperCase()}${name.slice(1)}`] ?? '';
				if (name === 'status') {
					field.className = `is-${button.dataset.eventTone ?? 'planned'}`;
				}
			});
			dialog.showModal();
		});
	});

	dialog.querySelector('[data-event-close]')?.addEventListener('click', () => dialog.close());
	dialog.addEventListener('click', (event) => {
		if (event.target === dialog) {
			dialog.close();
		}
	});
}

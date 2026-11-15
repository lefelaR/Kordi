(() => {
    const dropZone = document.querySelector('[data-image-drop]');
    const fileInput = dropZone?.querySelector('[data-image-input]');
    const preview = dropZone?.querySelector('[data-image-preview]');
    const error = document.querySelector('[data-image-error]');
    if (!(dropZone instanceof HTMLElement) || !(fileInput instanceof HTMLInputElement)) {
        return;
    }

    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    const maxBytes = 5 * 1024 * 1024;
    let dragDepth = 0;
    let previewUrl = '';

    const showError = (message) => {
        if (!(error instanceof HTMLElement)) {
            return;
        }
        error.hidden = message === '';
        error.textContent = message;
    };

    const showPreview = (file) => {
        if (!(preview instanceof HTMLImageElement)) {
            return;
        }
        if (previewUrl !== '') {
            URL.revokeObjectURL(previewUrl);
        }
        previewUrl = URL.createObjectURL(file);
        preview.src = previewUrl;
        preview.hidden = false;
    };

    const useFile = (file, assignToInput) => {
        if (!allowedTypes.includes(file.type)) {
            showError('Choose a JPEG, PNG, WebP, or GIF image.');
            return;
        }
        if (file.size > maxBytes) {
            showError('Choose an image that is 5 MB or smaller.');
            return;
        }

        if (assignToInput) {
            const transfer = new DataTransfer();
            transfer.items.add(file);
            fileInput.files = transfer.files;
        }

        showError('');
        showPreview(file);
    };

    dropZone.addEventListener('dragenter', (event) => {
        event.preventDefault();
        dragDepth += 1;
        dropZone.classList.add('is-dragover');
    });

    dropZone.addEventListener('dragover', (event) => {
        event.preventDefault();
        if (event.dataTransfer) {
            event.dataTransfer.dropEffect = 'copy';
        }
    });

    dropZone.addEventListener('dragleave', () => {
        dragDepth -= 1;
        if (dragDepth <= 0) {
            dragDepth = 0;
            dropZone.classList.remove('is-dragover');
        }
    });

    dropZone.addEventListener('drop', (event) => {
        event.preventDefault();
        dragDepth = 0;
        dropZone.classList.remove('is-dragover');
        const file = event.dataTransfer?.files?.[0];
        if (file) {
            useFile(file, true);
        }
    });

    fileInput.addEventListener('change', () => {
        const file = fileInput.files?.[0];
        if (file) {
            useFile(file, false);
        }
    });
})();

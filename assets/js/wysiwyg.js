(() => {
    const maxLength = 5000;
    const allowed = new Set(['P', 'BR', 'STRONG', 'B', 'EM', 'I', 'U', 'H2', 'UL', 'OL', 'LI', 'BLOCKQUOTE', 'A']);
    const drop = new Set(['SCRIPT', 'STYLE', 'IFRAME', 'OBJECT', 'EMBED', 'LINK', 'META', 'SVG', 'MATH', 'FORM', 'TEXTAREA', 'INPUT', 'BUTTON']);

    document.querySelectorAll('textarea[data-wysiwyg]').forEach(mount);

    function mount(textarea) {
        const editor = document.createElement('div');
        editor.className = 'wysiwyg';

        const toolbar = document.createElement('div');
        toolbar.className = 'wysiwyg-toolbar';
        toolbar.setAttribute('role', 'toolbar');
        toolbar.setAttribute('aria-label', 'Formatting');

        const controls = [
            { command: 'bold', label: 'Bold', icon: '<path d="M14 12a4 4 0 0 0 0-8H6v8"/><path d="M15 20a4 4 0 0 0 0-8H6v8Z"/>' },
            { command: 'italic', label: 'Italic', icon: '<line x1="19" x2="10" y1="4" y2="4"/><line x1="14" x2="5" y1="20" y2="20"/><line x1="15" x2="9" y1="4" y2="20"/>' },
            { command: 'underline', label: 'Underline', icon: '<path d="M6 4v6a6 6 0 0 0 12 0V4"/><line x1="4" x2="20" y1="20" y2="20"/>' },
            { separator: true },
            { command: 'formatBlock', value: 'h2', label: 'Heading', icon: '<path d="M6 12h12"/><path d="M6 20V4"/><path d="M18 20V4"/>' },
            { separator: true },
            { command: 'insertUnorderedList', label: 'Bulleted list', icon: '<line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/>' },
            { command: 'insertOrderedList', label: 'Numbered list', icon: '<line x1="10" x2="21" y1="6" y2="6"/><line x1="10" x2="21" y1="12" y2="12"/><line x1="10" x2="21" y1="18" y2="18"/><path d="M4 6h1v4"/><path d="M4 10h2"/><path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/>' },
            { separator: true },
            { command: 'createLink', label: 'Link', icon: '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>' },
            { command: 'removeFormat', label: 'Clear formatting', icon: '<path d="M4 7V4h16v3"/><path d="M5 20h6"/><path d="M13 4 8 20"/><path d="m15 15 5 5"/><path d="m20 15-5 5"/>' },
        ];

        const buttons = [];
        controls.forEach((control) => {
            if (control.separator) {
                const separator = document.createElement('span');
                separator.className = 'wysiwyg-separator';
                separator.setAttribute('aria-hidden', 'true');
                toolbar.append(separator);
                return;
            }

            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.command = control.command;
            if (control.value) {
                button.dataset.value = control.value;
            }
            button.setAttribute('aria-label', control.label);
            button.setAttribute('aria-pressed', 'false');
            button.innerHTML = icon(control.icon);
            toolbar.append(button);
            buttons.push(button);
        });

        const linkRow = document.createElement('div');
        linkRow.className = 'wysiwyg-link';
        linkRow.hidden = true;
        const linkInput = document.createElement('input');
        linkInput.type = 'text';
        linkInput.inputMode = 'url';
        linkInput.placeholder = 'https://';
        linkInput.setAttribute('aria-label', 'Link address');
        linkInput.autocomplete = 'off';
        linkInput.disabled = true;
        const applyLink = document.createElement('button');
        applyLink.type = 'button';
        applyLink.className = 'dashboard-button dashboard-button-secondary';
        applyLink.textContent = 'Apply';
        const removeLink = document.createElement('button');
        removeLink.type = 'button';
        removeLink.className = 'dashboard-button dashboard-button-secondary';
        removeLink.textContent = 'Remove';
        linkRow.append(linkInput, applyLink, removeLink);

        const surface = document.createElement('div');
        surface.className = 'wysiwyg-surface';
        surface.contentEditable = 'true';
        surface.spellcheck = true;
        surface.setAttribute('role', 'textbox');
        surface.setAttribute('aria-multiline', 'true');
        surface.dataset.placeholder = textarea.getAttribute('placeholder') || '';
        const labelledBy = textarea.getAttribute('aria-labelledby');
        if (labelledBy) {
            surface.setAttribute('aria-labelledby', labelledBy);
        }

        const meta = document.createElement('p');
        meta.className = 'wysiwyg-meta';

        textarea.hidden = true;
        textarea.setAttribute('aria-hidden', 'true');
        textarea.tabIndex = -1;
        textarea.parentNode.insertBefore(editor, textarea);
        editor.append(toolbar, linkRow, surface, meta, textarea);

        surface.innerHTML = initialHtml(textarea.value);
        sanitize(surface);
        let savedRange = null;

        const update = () => {
            const length = plainLength(surface);
            const empty = length === 0;
            surface.dataset.empty = empty ? 'true' : 'false';
            surface.setAttribute('aria-invalid', length > maxLength ? 'true' : 'false');
            textarea.value = empty ? '' : surface.innerHTML;
            meta.dataset.over = length > maxLength ? 'true' : 'false';
            meta.textContent = length > maxLength
                ? `${length} / ${maxLength}. Shorten the details before saving.`
                : `${length} / ${maxLength}`;
        };

        const commit = () => {
            sanitize(surface);
            update();
        };

        const rememberRange = () => {
            const selection = window.getSelection();
            if (!selection || selection.rangeCount === 0 || !surface.contains(selection.anchorNode)) {
                return;
            }
            savedRange = selection.getRangeAt(0).cloneRange();
        };

        const restoreRange = () => {
            surface.focus();
            if (!savedRange) {
                return;
            }
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(savedRange);
        };

        const run = (command, value) => {
            restoreRange();
            if (command === 'formatBlock') {
                const current = String(document.queryCommandValue('formatBlock') || '').toLowerCase().replace(/[<>]/g, '');
                const next = current === value ? 'p' : value;
                document.execCommand('formatBlock', false, next);
                if (String(document.queryCommandValue('formatBlock') || '').toLowerCase().replace(/[<>]/g, '') !== next) {
                    document.execCommand('formatBlock', false, `<${next}>`);
                }
            } else if (command === 'removeFormat') {
                document.execCommand('removeFormat', false, null);
                document.execCommand('unlink', false, null);
            } else {
                document.execCommand(command, false, value ?? null);
            }
            commit();
            refresh();
        };

        toolbar.addEventListener('mousedown', (event) => {
            if (event.target.closest('button')) {
                event.preventDefault();
            }
        });
        toolbar.addEventListener('click', (event) => {
            const button = event.target.closest('button');
            if (!button) {
                return;
            }
            if (button.dataset.command === 'createLink') {
                rememberRange();
                const existing = currentLink();
                linkInput.disabled = false;
                linkInput.value = existing ? existing.getAttribute('href') || '' : '';
                linkRow.hidden = false;
                linkInput.focus();
                return;
            }
            closeLink();
            run(button.dataset.command, button.dataset.value);
        });

        applyLink.addEventListener('click', () => {
            const href = normalizeHref(linkInput.value);
            if (!isSafeHref(href)) {
                linkInput.focus();
                return;
            }
            restoreRange();
            document.execCommand('createLink', false, href);
            closeLink();
            commit();
            refresh();
        });
        removeLink.addEventListener('click', () => {
            restoreRange();
            document.execCommand('unlink', false, null);
            closeLink();
            commit();
            refresh();
        });
        linkInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                applyLink.click();
            } else if (event.key === 'Escape') {
                closeLink();
                restoreRange();
            }
        });

        function closeLink() {
            linkRow.hidden = true;
            linkInput.value = '';
            linkInput.disabled = true;
        }

        surface.addEventListener('focus', () => {
            document.execCommand('defaultParagraphSeparator', false, 'p');
        });
        surface.addEventListener('input', update);
        surface.addEventListener('keyup', () => {
            rememberRange();
            refresh();
        });
        surface.addEventListener('mouseup', () => {
            rememberRange();
            refresh();
        });
        surface.addEventListener('paste', (event) => {
            event.preventDefault();
            const clipboard = event.clipboardData;
            const html = clipboard ? clipboard.getData('text/html') : '';
            const text = clipboard ? clipboard.getData('text/plain') : '';
            if (html.trim() !== '') {
                const holder = document.createElement('div');
                holder.innerHTML = html;
                sanitize(holder);
                document.execCommand('insertHTML', false, holder.innerHTML);
            } else {
                document.execCommand('insertText', false, text);
            }
            commit();
        });

        document.addEventListener('selectionchange', () => {
            if (!surface.contains(window.getSelection()?.anchorNode || null)) {
                return;
            }
            rememberRange();
            refresh();
        });

        textarea.form?.addEventListener('submit', (event) => {
            commit();
            if (plainLength(surface) > maxLength) {
                event.preventDefault();
                surface.focus();
            }
        });

        commit();

        function refresh() {
            buttons.forEach((button) => {
                const command = button.dataset.command;
                let pressed = false;
                if (command === 'underline') {
                    pressed = selectionInside('u');
                } else if (command === 'bold' || command === 'italic' || command === 'insertUnorderedList' || command === 'insertOrderedList') {
                    pressed = document.queryCommandState(command);
                } else if (command === 'formatBlock') {
                    const value = String(document.queryCommandValue('formatBlock') || '').toLowerCase().replace(/[<>]/g, '');
                    pressed = value === button.dataset.value;
                } else if (command === 'createLink') {
                    pressed = currentLink() !== null;
                }
                button.setAttribute('aria-pressed', pressed ? 'true' : 'false');
            });
        }

        function selectionElement() {
            const selection = window.getSelection();
            const node = selection?.anchorNode;
            const element = node && node.nodeType === Node.TEXT_NODE ? node.parentElement : node;
            if (!element || !surface.contains(element)) {
                return null;
            }
            return element;
        }

        function selectionInside(tag) {
            return selectionElement()?.closest(tag) ?? null;
        }

        function currentLink() {
            return selectionInside('a');
        }
    }

    function initialHtml(value) {
        const normalized = value.replace(/\r\n/g, '\n').trim();
        if (normalized === '') {
            return '';
        }
        if (/<\/?[a-z][^>]*>/i.test(normalized)) {
            return normalized;
        }
        return normalized
            .split(/\n{2,}/)
            .map((block) => `<p>${escapeHtml(block).replace(/\n/g, '<br>')}</p>`)
            .join('');
    }

    function sanitize(root) {
        [...root.childNodes].forEach((child) => sanitizeNode(child));
    }

    function sanitizeNode(node) {
        if (node.nodeType === Node.TEXT_NODE) {
            return;
        }
        if (node.nodeType !== Node.ELEMENT_NODE) {
            node.remove();
            return;
        }

        const tag = node.tagName;
        if (drop.has(tag)) {
            node.remove();
            return;
        }
        if (tag === 'DIV' || (/^H[1-6]$/.test(tag) && tag !== 'H2')) {
            const replacement = document.createElement(tag === 'DIV' ? 'p' : 'h2');
            while (node.firstChild) {
                replacement.append(node.firstChild);
            }
            node.replaceWith(replacement);
            sanitize(replacement);
            return;
        }
        if (!allowed.has(tag)) {
            sanitize(node);
            node.replaceWith(...node.childNodes);
            return;
        }

        [...node.attributes].forEach((attribute) => {
            if (!(tag === 'A' && attribute.name.toLowerCase() === 'href')) {
                node.removeAttribute(attribute.name);
            }
        });

        if (tag === 'A') {
            const href = normalizeHref(node.getAttribute('href') || '');
            if (!isSafeHref(href)) {
                sanitize(node);
                node.replaceWith(...node.childNodes);
                return;
            }
            node.setAttribute('href', href);
            node.setAttribute('rel', 'noopener noreferrer');
            if (/^https?:/i.test(href)) {
                node.setAttribute('target', '_blank');
            }
        }

        sanitize(node);
    }

    function plainLength(root) {
        return (root.innerText || '').replace(/\u00a0/g, ' ').trim().length;
    }

    function normalizeHref(value) {
        const trimmed = value.trim().replace(/[\u0000-\u001F\u007F]/g, '');
        if (trimmed === '') {
            return '';
        }
        if (/^(https?:\/\/|mailto:)/i.test(trimmed)) {
            return trimmed;
        }
        if (/^[\w.-]+\.[a-z]{2,}([/?#].*)?$/i.test(trimmed)) {
            return `https://${trimmed}`;
        }
        return trimmed;
    }

    function isSafeHref(href) {
        return /^(https?:\/\/|mailto:)/i.test(href) && !/^(javascript|data|vbscript):/i.test(href);
    }

    function escapeHtml(text) {
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function icon(paths) {
        return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${paths}</svg>`;
    }
})();

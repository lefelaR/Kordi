<section class="dashboard-page">
    <div class="dashboard-page-bar">
        <header class="dashboard-page-header">
            <h1>Quotations</h1>
            <p>Quotations for events and vendors.</p>
        </header>
        <a class="dashboard-button" href="/quotations/add">new +</a>
    </div>
    <?php if ($quotationCompanies === []): ?>
        <div class="dashboard-panel">
            <p class="dashboard-empty">No companies yet. Add a company to browse quotations.</p>
        </div>
    <?php else: ?>
        <div class="dashboard-invoice-layout" data-quotation-browser>
            <section class="dashboard-panel dashboard-invoice-card">
                <h2>Companies</h2>
                <div class="dashboard-invoice-card-body">
                    <div class="dashboard-invoice-accordion">
                        <?php foreach ($quotationCompanies as $companyIndex => $company): ?>
                            <details class="dashboard-invoice-company"<?= $companyIndex === 0 ? ' open' : '' ?>>
                                <summary class="dashboard-invoice-company-summary">
                                    <span><?= htmlspecialchars($company['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="dashboard-invoice-company-count"><?= count($company['quotations']) ?></span>
                                </summary>
                                <?php if ($company['quotations'] === []): ?>
                                    <p class="dashboard-invoice-empty">No quotations yet.</p>
                                <?php else: ?>
                                    <ul class="dashboard-invoice-list">
                                        <?php foreach ($company['quotations'] as $quotation): ?>
                                            <?php
                                            $isSelected = $selectedQuotation !== null
                                                && ($selectedQuotation['id'] ?? null) === ($quotation['id'] ?? null);
                                            ?>
                                            <li>
                                                <button
                                                    type="button"
                                                    class="dashboard-invoice-item<?= $isSelected ? ' is-active' : '' ?>"
                                                    data-quotation-id="<?= (int) $quotation['id'] ?>"
                                                    data-quotation-src="<?= htmlspecialchars($quotation['file'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-quotation-title="<?= htmlspecialchars($quotation['title'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-quotation-alert-variant="<?= htmlspecialchars($quotation['alertVariant'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-quotation-alert-title="<?= htmlspecialchars($quotation['alertTitle'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-quotation-alert-description="<?= htmlspecialchars($quotation['alertDescription'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-quotation-can-edit="<?= $quotation['canEdit'] ? '1' : '0' ?>"
                                                    data-quotation-email="<?= htmlspecialchars($quotation['email'], ENT_QUOTES, 'UTF-8') ?>"
                                                    aria-pressed="<?= $isSelected ? 'true' : 'false' ?>"
                                                >
                                                    <?= htmlspecialchars($quotation['title'], ENT_QUOTES, 'UTF-8') ?>
                                                </button>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </details>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <section class="dashboard-panel dashboard-invoice-preview" aria-label="Quotation preview">
                <?php if ($selectedQuotation === null): ?>
                    <p class="dashboard-empty dashboard-invoice-preview-empty">Select a quotation to preview.</p>
                <?php else: ?>
                    <div class="dashboard-alert<?= $selectedQuotation['alertVariant'] !== '' ? ' ' . htmlspecialchars($selectedQuotation['alertVariant'], ENT_QUOTES, 'UTF-8') : '' ?>" data-quotation-alert role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3" />
                            <path d="M12 9v4" />
                            <path d="M12 17h.01" />
                        </svg>
                        <div class="dashboard-alert-body">
                            <p class="dashboard-alert-title"><?= htmlspecialchars($selectedQuotation['alertTitle'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p class="dashboard-alert-description"><?= htmlspecialchars($selectedQuotation['alertDescription'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="dashboard-alert-actions">
                            <a class="dashboard-button dashboard-button-secondary dashboard-button-with-icon" href="/quotations/<?= (int) $selectedQuotation['id'] ?>/edit" data-quotation-edit<?= $selectedQuotation['canEdit'] ? '' : ' hidden' ?>>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 20h9" />
                                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                </svg>
                                Edit
                            </a>
                            <form method="post" action="/quotations/<?= (int) $selectedQuotation['id'] ?>/send" data-quotation-send data-quotation-email="<?= htmlspecialchars($selectedQuotation['email'], ENT_QUOTES, 'UTF-8') ?>">
                                <button class="dashboard-button dashboard-button-with-icon" type="button" data-quotation-send-open>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="m22 2-7 20-4-9-9-4Z" />
                                        <path d="M22 2 11 13" />
                                    </svg>
                                    Send
                                </button>
                            </form>
                        </div>
                    </div>
                    <iframe
                        data-quotation-frame
                        src="<?= htmlspecialchars($selectedQuotation['file'], ENT_QUOTES, 'UTF-8') ?>"
                        title="<?= htmlspecialchars($selectedQuotation['title'], ENT_QUOTES, 'UTF-8') ?>"
                    ></iframe>
                <?php endif; ?>
            </section>
        </div>
        <dialog class="dashboard-send-modal" data-quotation-send-modal aria-labelledby="quotation-send-title">
            <div class="dashboard-send-modal-card">
                <header>
                    <h2 id="quotation-send-title">Send quotation</h2>
                    <p>This quotation will be sent to the address below.</p>
                </header>
                <label>
                    Email
                    <input type="email" data-quotation-send-email readonly>
                </label>
                <div class="dashboard-send-modal-actions">
                    <button class="dashboard-button dashboard-button-secondary" type="button" data-quotation-send-cancel>Cancel</button>
                    <button class="dashboard-button" type="button" data-quotation-send-confirm>Confirm</button>
                </div>
            </div>
        </dialog>
        <script>
            (() => {
                const browser = document.querySelector('[data-quotation-browser]');
                if (!browser) {
                    return;
                }

                const preview = browser.querySelector('.dashboard-invoice-preview');
                const items = browser.querySelectorAll('[data-quotation-src]');
                const modal = document.querySelector('[data-quotation-send-modal]');
                const emailField = modal ? modal.querySelector('[data-quotation-send-email]') : null;
                const confirmButton = modal ? modal.querySelector('[data-quotation-send-confirm]') : null;

                const closeSendModal = () => {
                    if (!modal) {
                        return;
                    }
                    if (typeof modal.close === 'function' && modal.open) {
                        modal.close();
                        return;
                    }
                    modal.removeAttribute('open');
                };

                const openSendModal = (email) => {
                    if (!modal) {
                        return;
                    }
                    if (emailField) {
                        emailField.value = email;
                    }
                    if (confirmButton) {
                        confirmButton.disabled = email === '';
                    }
                    if (typeof modal.showModal === 'function') {
                        modal.showModal();
                    } else {
                        modal.setAttribute('open', '');
                    }
                };

                browser.addEventListener('click', (event) => {
                    const trigger = event.target.closest('[data-quotation-send-open]');
                    if (!trigger) {
                        return;
                    }
                    event.preventDefault();
                    const form = trigger.closest('[data-quotation-send]');
                    openSendModal(form ? (form.getAttribute('data-quotation-email') || '') : '');
                });

                if (modal) {
                    modal.querySelectorAll('[data-quotation-send-cancel]').forEach((button) => {
                        button.addEventListener('click', closeSendModal);
                    });
                    modal.addEventListener('click', (event) => {
                        if (event.target === modal) {
                            closeSendModal();
                        }
                    });
                }

                if (confirmButton) {
                    confirmButton.addEventListener('click', () => {
                        const form = preview ? preview.querySelector('[data-quotation-send]') : null;
                        const email = emailField ? emailField.value.trim() : '';
                        closeSendModal();
                        if (form && !form.hidden && email !== '') {
                            form.requestSubmit();
                        }
                    });
                }

                items.forEach((item) => {
                    item.addEventListener('click', () => {
                        const src = item.getAttribute('data-quotation-src');
                        const title = item.getAttribute('data-quotation-title') || 'Quotation preview';

                        if (!src || !preview) {
                            return;
                        }

                        const quotationId = item.getAttribute('data-quotation-id') || '';
                        const alertVariant = item.getAttribute('data-quotation-alert-variant') || '';
                        const alertTitle = item.getAttribute('data-quotation-alert-title') || '';
                        const alertDescription = item.getAttribute('data-quotation-alert-description') || '';
                        const canEdit = item.getAttribute('data-quotation-can-edit') === '1';
                        const email = item.getAttribute('data-quotation-email') || '';

                        let frame = preview.querySelector('[data-quotation-frame]');
                        if (!frame) {
                            preview.innerHTML = '';
                            const alert = document.createElement('div');
                            alert.className = 'dashboard-alert';
                            alert.setAttribute('data-quotation-alert', '');
                            alert.setAttribute('role', 'alert');
                            alert.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3" /><path d="M12 9v4" /><path d="M12 17h.01" /></svg><div class="dashboard-alert-body"><p class="dashboard-alert-title"></p><p class="dashboard-alert-description"></p></div><div class="dashboard-alert-actions"><a class="dashboard-button dashboard-button-secondary dashboard-button-with-icon" data-quotation-edit href="/quotations/0/edit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9" /><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" /></svg>Edit</a><form method="post" data-quotation-send><button class="dashboard-button dashboard-button-with-icon" type="button" data-quotation-send-open><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4Z" /><path d="M22 2 11 13" /></svg>Send</button></form></div>';
                            preview.appendChild(alert);
                            frame = document.createElement('iframe');
                            frame.setAttribute('data-quotation-frame', '');
                            preview.appendChild(frame);
                        }

                        const alert = preview.querySelector('[data-quotation-alert]');
                        if (alert) {
                            alert.className = 'dashboard-alert' + (alertVariant !== '' ? ' ' + alertVariant : '');
                            const titleNode = alert.querySelector('.dashboard-alert-title');
                            const descriptionNode = alert.querySelector('.dashboard-alert-description');
                            const form = alert.querySelector('[data-quotation-send]');
                            if (titleNode) {
                                titleNode.textContent = alertTitle;
                            }
                            if (descriptionNode) {
                                descriptionNode.textContent = alertDescription;
                            }
                            if (form) {
                                form.hidden = false;
                                form.setAttribute('data-quotation-email', email);
                                if (quotationId !== '') {
                                    form.setAttribute('action', '/quotations/' + quotationId + '/send');
                                }
                            }
                            const edit = alert.querySelector('[data-quotation-edit]');
                            if (edit) {
                                edit.hidden = !canEdit;
                                if (quotationId !== '') {
                                    edit.setAttribute('href', '/quotations/' + quotationId + '/edit');
                                }
                            }
                        }

                        frame.setAttribute('src', src);
                        frame.setAttribute('title', title);

                        items.forEach((other) => {
                            const active = other === item;
                            other.classList.toggle('is-active', active);
                            other.setAttribute('aria-pressed', active ? 'true' : 'false');
                        });
                    });
                });
            })();
        </script>
    <?php endif; ?>
</section>

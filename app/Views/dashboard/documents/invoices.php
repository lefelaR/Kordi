<section class="dashboard-page">
    <div class="dashboard-page-bar">
        <header class="dashboard-page-header">
            <h1>Invoices</h1>
            <p>Invoices for events and vendors.</p>
        </header>
        <a class="dashboard-button" href="/invoices/add">new +</a>
    </div>
    <?php if ($invoiceCompanies === []): ?>
        <div class="dashboard-panel">
            <p class="dashboard-empty">No companies yet. Add a company to browse invoices.</p>
        </div>
    <?php else: ?>
        <div class="dashboard-invoice-layout" data-invoice-browser>
            <section class="dashboard-panel dashboard-invoice-card">
                <h2>Companies</h2>
                <div class="dashboard-invoice-card-body">
                    <div class="dashboard-invoice-accordion">
                        <?php foreach ($invoiceCompanies as $companyIndex => $company): ?>
                            <details class="dashboard-invoice-company"<?= $companyIndex === 0 ? ' open' : '' ?>>
                                <summary class="dashboard-invoice-company-summary">
                                    <span><?= htmlspecialchars($company['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="dashboard-invoice-company-count"><?= count($company['invoices']) ?></span>
                                </summary>
                                <?php if ($company['invoices'] === []): ?>
                                    <p class="dashboard-invoice-empty">No invoices yet.</p>
                                <?php else: ?>
                                    <ul class="dashboard-invoice-list">
                                        <?php foreach ($company['invoices'] as $invoiceIndex => $invoice): ?>
                                            <?php
                                            $isSelected = $selectedInvoice !== null
                                                && ($selectedInvoice['id'] ?? null) === ($invoice['id'] ?? null);
                                            ?>
                                            <li>
                                                <button
                                                    type="button"
                                                    class="dashboard-invoice-item<?= $isSelected ? ' is-active' : '' ?>"
                                                    data-invoice-src="<?= htmlspecialchars($invoice['file'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-invoice-title="<?= htmlspecialchars($invoice['title'], ENT_QUOTES, 'UTF-8') ?>"
                                                    aria-pressed="<?= $isSelected ? 'true' : 'false' ?>"
                                                >
                                                    <?= htmlspecialchars($invoice['title'], ENT_QUOTES, 'UTF-8') ?>
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
            <section class="dashboard-panel dashboard-invoice-preview" aria-label="Invoice preview">
                <?php if ($selectedInvoice === null): ?>
                    <p class="dashboard-empty dashboard-invoice-preview-empty">Select an invoice to preview.</p>
                <?php else: ?>
                    <iframe
                        data-invoice-frame
                        src="<?= htmlspecialchars($selectedInvoice['file'], ENT_QUOTES, 'UTF-8') ?>"
                        title="<?= htmlspecialchars($selectedInvoice['title'], ENT_QUOTES, 'UTF-8') ?>"
                    ></iframe>
                <?php endif; ?>
            </section>
        </div>
        <script>
            (() => {
                const browser = document.querySelector('[data-invoice-browser]');
                if (!browser) {
                    return;
                }

                const preview = browser.querySelector('.dashboard-invoice-preview');
                const items = browser.querySelectorAll('[data-invoice-src]');

                items.forEach((item) => {
                    item.addEventListener('click', () => {
                        const src = item.getAttribute('data-invoice-src');
                        const title = item.getAttribute('data-invoice-title') || 'Invoice preview';

                        if (!src || !preview) {
                            return;
                        }

                        let frame = preview.querySelector('[data-invoice-frame]');
                        if (!frame) {
                            preview.innerHTML = '';
                            frame = document.createElement('iframe');
                            frame.setAttribute('data-invoice-frame', '');
                            preview.appendChild(frame);
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

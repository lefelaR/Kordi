<section class="dashboard-page">
    <div class="dashboard-page-bar">
        <header class="dashboard-page-header">
            <h1>Receipts</h1>
            <p>Receipts for events and clients.</p>
        </header>
    </div>
    <?php if ($receiptCompanies === []): ?>
        <div class="dashboard-panel">
            <p class="dashboard-empty">No companies yet. Add a company to browse receipts.</p>
        </div>
    <?php else: ?>
        <div class="dashboard-invoice-layout" data-receipt-browser>
            <section class="dashboard-panel dashboard-invoice-card">
                <h2>Companies</h2>
                <div class="dashboard-invoice-card-body">
                    <div class="dashboard-invoice-accordion">
                        <?php foreach ($receiptCompanies as $companyIndex => $company): ?>
                            <details class="dashboard-invoice-company"<?= $companyIndex === 0 ? ' open' : '' ?>>
                                <summary class="dashboard-invoice-company-summary">
                                    <span><?= htmlspecialchars($company['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="dashboard-invoice-company-count"><?= count($company['receipts']) ?></span>
                                </summary>
                                <?php if ($company['receipts'] === []): ?>
                                    <p class="dashboard-invoice-empty">No receipts yet.</p>
                                <?php else: ?>
                                    <ul class="dashboard-invoice-list">
                                        <?php foreach ($company['receipts'] as $receipt): ?>
                                            <?php
                                            $isSelected = $selectedReceipt !== null
                                                && ($selectedReceipt['id'] ?? null) === ($receipt['id'] ?? null);
                                            ?>
                                            <li>
                                                <button
                                                    type="button"
                                                    class="dashboard-invoice-item<?= $isSelected ? ' is-active' : '' ?>"
                                                    data-receipt-src="<?= htmlspecialchars($receipt['file'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-receipt-title="<?= htmlspecialchars($receipt['title'], ENT_QUOTES, 'UTF-8') ?>"
                                                    aria-pressed="<?= $isSelected ? 'true' : 'false' ?>"
                                                >
                                                    <?= htmlspecialchars($receipt['title'], ENT_QUOTES, 'UTF-8') ?>
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
            <section class="dashboard-panel dashboard-invoice-preview" aria-label="Receipt preview">
                <?php if ($selectedReceipt === null): ?>
                    <p class="dashboard-empty dashboard-invoice-preview-empty">Select a receipt to preview.</p>
                <?php else: ?>
                    <iframe
                        data-receipt-frame
                        src="<?= htmlspecialchars($selectedReceipt['file'], ENT_QUOTES, 'UTF-8') ?>"
                        title="<?= htmlspecialchars($selectedReceipt['title'], ENT_QUOTES, 'UTF-8') ?>"
                    ></iframe>
                <?php endif; ?>
            </section>
        </div>
        <script>
            (() => {
                const browser = document.querySelector('[data-receipt-browser]');
                if (!browser) {
                    return;
                }

                const preview = browser.querySelector('.dashboard-invoice-preview');
                const items = browser.querySelectorAll('[data-receipt-src]');

                items.forEach((item) => {
                    item.addEventListener('click', () => {
                        const src = item.getAttribute('data-receipt-src');
                        const title = item.getAttribute('data-receipt-title') || 'Receipt preview';

                        if (!src || !preview) {
                            return;
                        }

                        let frame = preview.querySelector('[data-receipt-frame]');
                        if (!frame) {
                            preview.innerHTML = '';
                            frame = document.createElement('iframe');
                            frame.setAttribute('data-receipt-frame', '');
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

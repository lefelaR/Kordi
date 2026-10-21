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
                                                    data-quotation-src="<?= htmlspecialchars($quotation['file'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-quotation-title="<?= htmlspecialchars($quotation['title'], ENT_QUOTES, 'UTF-8') ?>"
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
                    <iframe
                        data-quotation-frame
                        src="<?= htmlspecialchars($selectedQuotation['file'], ENT_QUOTES, 'UTF-8') ?>"
                        title="<?= htmlspecialchars($selectedQuotation['title'], ENT_QUOTES, 'UTF-8') ?>"
                    ></iframe>
                <?php endif; ?>
            </section>
        </div>
        <script>
            (() => {
                const browser = document.querySelector('[data-quotation-browser]');
                if (!browser) {
                    return;
                }

                const preview = browser.querySelector('.dashboard-invoice-preview');
                const items = browser.querySelectorAll('[data-quotation-src]');

                items.forEach((item) => {
                    item.addEventListener('click', () => {
                        const src = item.getAttribute('data-quotation-src');
                        const title = item.getAttribute('data-quotation-title') || 'Quotation preview';

                        if (!src || !preview) {
                            return;
                        }

                        let frame = preview.querySelector('[data-quotation-frame]');
                        if (!frame) {
                            preview.innerHTML = '';
                            frame = document.createElement('iframe');
                            frame.setAttribute('data-quotation-frame', '');
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

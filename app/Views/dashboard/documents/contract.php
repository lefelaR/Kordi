<section class="dashboard-page">
    <div class="dashboard-page-bar">
        <header class="dashboard-page-header">
            <h1>Contract</h1>
            <p>Contracts for events and clients.</p>
        </header>
        <a class="dashboard-button" href="/contracts/add">new +</a>
    </div>
    <?php if ($contractCompanies === []): ?>
        <div class="dashboard-panel">
            <p class="dashboard-empty">No companies yet. Add a company to browse contracts.</p>
        </div>
    <?php else: ?>
        <div class="dashboard-invoice-layout" data-contract-browser>
            <section class="dashboard-panel dashboard-invoice-card">
                <h2>Companies</h2>
                <div class="dashboard-invoice-card-body">
                    <div class="dashboard-invoice-accordion">
                        <?php foreach ($contractCompanies as $companyIndex => $company): ?>
                            <details class="dashboard-invoice-company"<?= $companyIndex === 0 ? ' open' : '' ?>>
                                <summary class="dashboard-invoice-company-summary">
                                    <span><?= htmlspecialchars($company['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="dashboard-invoice-company-count"><?= count($company['contracts']) ?></span>
                                </summary>
                                <?php if ($company['contracts'] === []): ?>
                                    <p class="dashboard-invoice-empty">No contracts yet.</p>
                                <?php else: ?>
                                    <ul class="dashboard-invoice-list">
                                        <?php foreach ($company['contracts'] as $contract): ?>
                                            <?php
                                            $isSelected = $selectedContract !== null
                                                && ($selectedContract['id'] ?? null) === ($contract['id'] ?? null);
                                            ?>
                                            <li>
                                                <button
                                                    type="button"
                                                    class="dashboard-invoice-item<?= $isSelected ? ' is-active' : '' ?>"
                                                    data-contract-src="<?= htmlspecialchars($contract['file'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-contract-title="<?= htmlspecialchars($contract['title'], ENT_QUOTES, 'UTF-8') ?>"
                                                    aria-pressed="<?= $isSelected ? 'true' : 'false' ?>"
                                                >
                                                    <?= htmlspecialchars($contract['title'], ENT_QUOTES, 'UTF-8') ?>
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
            <section class="dashboard-panel dashboard-invoice-preview" aria-label="Contract preview">
                <?php if ($selectedContract === null): ?>
                    <p class="dashboard-empty dashboard-invoice-preview-empty">Select a contract to preview.</p>
                <?php else: ?>
                    <iframe
                        data-contract-frame
                        src="<?= htmlspecialchars($selectedContract['file'], ENT_QUOTES, 'UTF-8') ?>"
                        title="<?= htmlspecialchars($selectedContract['title'], ENT_QUOTES, 'UTF-8') ?>"
                    ></iframe>
                <?php endif; ?>
            </section>
        </div>
        <script>
            (() => {
                const browser = document.querySelector('[data-contract-browser]');
                if (!browser) {
                    return;
                }

                const preview = browser.querySelector('.dashboard-invoice-preview');
                const items = browser.querySelectorAll('[data-contract-src]');

                items.forEach((item) => {
                    item.addEventListener('click', () => {
                        const src = item.getAttribute('data-contract-src');
                        const title = item.getAttribute('data-contract-title') || 'Contract preview';

                        if (!src || !preview) {
                            return;
                        }

                        let frame = preview.querySelector('[data-contract-frame]');
                        if (!frame) {
                            preview.innerHTML = '';
                            frame = document.createElement('iframe');
                            frame.setAttribute('data-contract-frame', '');
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

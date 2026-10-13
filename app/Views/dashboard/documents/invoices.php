<?php
$invoiceCompanies = [
    [
        'name' => 'Sportingbet South Africa',
        'invoices' => [
            [
                'id' => 'sportingbet-inv-007',
                'title' => 'Year End Function – INV-007',
                'file' => '/assets/files/Sporting_Bets_Year_End_Invoice_INV-007-Updated.pdf',
            ],
        ],
    ],
    [
        'name' => 'Sinazo Tambo',
        'invoices' => [
            [
                'id' => 'sinazo-inv-101',
                'title' => 'Invoice 101',
                'file' => '/assets/files/KordiCraft_Invoice_Final.pdf',
            ],
        ],
    ],
];

$selectedInvoice = $invoiceCompanies[0]['invoices'][0] ?? null;
?>
<section class="dashboard-page">
    <header class="dashboard-page-header">
        <h1>Invoices</h1>
        <p>Invoices for events and vendors.</p>
    </header>
    <?php if ($selectedInvoice === null): ?>
        <div class="dashboard-panel">
            <p class="dashboard-empty">No invoices yet.</p>
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
                                <ul class="dashboard-invoice-list">
                                    <?php foreach ($company['invoices'] as $invoiceIndex => $invoice): ?>
                                        <?php $isSelected = $companyIndex === 0 && $invoiceIndex === 0; ?>
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
                            </details>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <section class="dashboard-panel dashboard-invoice-preview" aria-label="Invoice preview">
                <iframe
                    data-invoice-frame
                    src="<?= htmlspecialchars($selectedInvoice['file'], ENT_QUOTES, 'UTF-8') ?>"
                    title="<?= htmlspecialchars($selectedInvoice['title'], ENT_QUOTES, 'UTF-8') ?>"
                ></iframe>
            </section>
        </div>
        <script>
            (() => {
                const browser = document.querySelector('[data-invoice-browser]');
                if (!browser) {
                    return;
                }

                const frame = browser.querySelector('[data-invoice-frame]');
                const items = browser.querySelectorAll('[data-invoice-src]');

                items.forEach((item) => {
                    item.addEventListener('click', () => {
                        const src = item.getAttribute('data-invoice-src');
                        const title = item.getAttribute('data-invoice-title') || 'Invoice preview';

                        if (!src || !frame) {
                            return;
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

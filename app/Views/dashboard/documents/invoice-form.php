<?php
$formatPreviewDate = static function (string $value): string {
    if ($value === '') {
        return 'Not set';
    }
    $date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);

    return $date instanceof \DateTimeImmutable ? $date->format('j F Y') : $value;
};
$billToContact = trim(implode(' | ', array_filter([
    $selectedCompany['contact'] !== '' ? 'Att: ' . $selectedCompany['contact'] : '',
    $selectedCompany['phone'],
    $selectedCompany['email'],
])));
?>
<section class="dashboard-page">
    <div class="dashboard-page-bar">
        <header class="dashboard-page-header">
            <h1><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h1>
            <p><?= htmlspecialchars($summary, ENT_QUOTES, 'UTF-8') ?></p>
        </header>
        <div class="dashboard-form-actions">
            <a class="dashboard-button dashboard-button-secondary" href="/dashboard/documents/invoices">Cancel</a>
            <button class="dashboard-button" type="submit" form="invoice-builder-form">Save</button>
        </div>
    </div>
    <?php if ($companyOptions === []): ?>
        <div class="dashboard-panel">
            <p class="dashboard-empty">Add a company before creating an invoice.</p>
            <p><a class="dashboard-button" href="/companies/add">Add company</a></p>
        </div>
    <?php else: ?>
        <?php if ($error !== ''): ?>
            <p class="dashboard-form-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <form
            id="invoice-builder-form"
            class="dashboard-invoice-layout dashboard-invoice-builder"
            method="post"
            action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>"
            data-invoice-builder
        >
            <input type="hidden" name="items" value="<?= htmlspecialchars($itemsJson ?? '[]', ENT_QUOTES, 'UTF-8') ?>" data-invoice-items-input>
            <section class="dashboard-panel dashboard-invoice-card">
                <h2>Details &amp; items</h2>
                <div class="dashboard-invoice-card-body dashboard-invoice-builder-fields">
                    <label>
                        Company
                        <select name="company_id" required data-invoice-company>
                            <?php foreach ($companyOptions as $company): ?>
                                <option
                                    value="<?= (int) $company['id'] ?>"
                                    data-company-name="<?= htmlspecialchars((string) $company['name'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-company-contact="<?= htmlspecialchars((string) ($company['contact'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                    data-company-email="<?= htmlspecialchars((string) ($company['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                    data-company-phone="<?= htmlspecialchars((string) ($company['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                    <?= (int) $company['id'] === (int) $companyId ? ' selected' : '' ?>
                                >
                                    <?= htmlspecialchars((string) $company['name'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>
                        Invoice number
                        <input type="text" name="invoice_number" maxlength="40" required value="<?= htmlspecialchars($invoiceNumber, ENT_QUOTES, 'UTF-8') ?>" data-invoice-number>
                    </label>
                    <label>
                        Title
                        <input type="text" name="title" maxlength="180" required value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>" data-invoice-title placeholder="Year End Function">
                    </label>
                    <label>
                        Event
                        <input type="text" name="event_label" maxlength="255" value="<?= htmlspecialchars($eventLabel, ENT_QUOTES, 'UTF-8') ?>" data-invoice-event placeholder="Year End Function – 3 December 2026">
                    </label>
                    <label>
                        Venue
                        <input type="text" name="venue" maxlength="255" value="<?= htmlspecialchars($venue, ENT_QUOTES, 'UTF-8') ?>" data-invoice-venue placeholder="Premier Hotel Sea Point">
                    </label>
                    <label>
                        Invoice date
                        <input type="date" name="invoice_date" value="<?= htmlspecialchars($invoiceDate, ENT_QUOTES, 'UTF-8') ?>" data-invoice-date>
                    </label>
                    <label>
                        Due date
                        <input type="date" name="due_date" value="<?= htmlspecialchars($dueDate, ENT_QUOTES, 'UTF-8') ?>" data-invoice-due>
                    </label>
                    <label>
                        Important note
                        <textarea name="notes" rows="3" maxlength="5000" data-invoice-notes placeholder="Venue Hire includes the 3-course buffet meal..."><?= htmlspecialchars($notes, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </label>

                    <div class="dashboard-invoice-items-block">
                        <div class="dashboard-invoice-items-heading">
                            <h3>Breakdown</h3>
                            <span class="dashboard-invoice-company-count" data-invoice-item-count>0</span>
                        </div>
                        <ul class="dashboard-invoice-builder-list" data-invoice-item-list></ul>
                        <p class="dashboard-invoice-empty" data-invoice-items-empty>No items yet. Add a line below.</p>
                        <div class="dashboard-invoice-item-form">
                            <label>
                                Description
                                <input type="text" maxlength="255" data-invoice-item-description placeholder="Venue Hire & Food (Buffet)">
                            </label>
                            <label>
                                Amount (ZAR)
                                <input type="number" min="0" step="0.01" value="0" data-invoice-item-amount>
                            </label>
                            <button class="dashboard-button dashboard-button-secondary" type="button" data-invoice-item-add>Add item</button>
                        </div>
                    </div>
                </div>
            </section>
            <section class="dashboard-panel dashboard-invoice-preview dashboard-invoice-proforma" aria-label="Invoice preview">
                <article class="dashboard-proforma" data-invoice-preview>
                    <header class="dashboard-proforma-letterhead">
                        <img class="dashboard-proforma-logo" src="/images/logo.jpeg" alt="Kordicraft Creations">
                        <div class="dashboard-proforma-letterhead-copy">
                            <p class="dashboard-proforma-brand">Kordicraft Creations</p>
                            <p class="dashboard-proforma-tagline">Where Vision Meets Excellence. Effortlessly</p>
                            <p class="dashboard-proforma-contact">15 Africander Street, Montana, 7490 Cape Town</p>
                            <p class="dashboard-proforma-contact">Tel: 082 615 0153 | WhatsApp: 081 311 2922</p>
                            <p class="dashboard-proforma-contact">Email: info@kordicraftcreations.co.za</p>
                        </div>
                    </header>

                    <h2 class="dashboard-proforma-title">Invoice</h2>
                    <p class="dashboard-proforma-meta-line">
                        <span>Invoice No: <strong data-preview-number><?= htmlspecialchars($invoiceNumber, ENT_QUOTES, 'UTF-8') ?></strong></span>
                        <span>Date: <strong data-preview-date><?= htmlspecialchars($formatPreviewDate($invoiceDate), ENT_QUOTES, 'UTF-8') ?></strong></span>
                        <span>Due Date: <strong data-preview-due><?= htmlspecialchars($formatPreviewDate($dueDate), ENT_QUOTES, 'UTF-8') ?></strong></span>
                    </p>

                    <section class="dashboard-proforma-section">
                        <h3>Bill to</h3>
                        <p class="dashboard-proforma-strong" data-preview-company><?= htmlspecialchars($selectedCompany['name'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p data-preview-att <?= $billToContact === '' ? ' hidden' : '' ?>><?= htmlspecialchars($billToContact, ENT_QUOTES, 'UTF-8') ?></p>
                        <p data-preview-event-wrap <?= $eventLabel === '' ? ' hidden' : '' ?>>Event: <span data-preview-event><?= htmlspecialchars($eventLabel, ENT_QUOTES, 'UTF-8') ?></span></p>
                        <p data-preview-venue-wrap <?= $venue === '' ? ' hidden' : '' ?>>Venue: <span data-preview-venue><?= htmlspecialchars($venue, ENT_QUOTES, 'UTF-8') ?></span></p>
                    </section>

                    <section class="dashboard-proforma-section">
                        <h3>Breakdown</h3>
                        <table class="dashboard-proforma-table">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Amount (ZAR)</th>
                                </tr>
                            </thead>
                            <tbody data-preview-items>
                                <tr data-preview-empty-row>
                                    <td colspan="2">Add items on the left to build this invoice.</td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="dashboard-proforma-important" data-preview-notes>
                            <strong>Important:</strong> <span data-preview-notes-text><?= htmlspecialchars($notes !== '' ? $notes : 'Add important terms for this invoice.', ENT_QUOTES, 'UTF-8') ?></span>
                        </p>
                        <div class="dashboard-proforma-total">
                            <span>Total due</span>
                            <strong data-preview-total>R 0.00</strong>
                        </div>
                    </section>

                    <section class="dashboard-proforma-section">
                        <h3>Banking details</h3>
                        <ul class="dashboard-proforma-bank">
                            <li><span>Bank</span> Standard Bank</li>
                            <li><span>Account Name</span> Kordicraft Creations</li>
                            <li><span>Account No</span> 101326136</li>
                            <li><span>Account Type</span> Current</li>
                            <li><span>Branch Code</span> 051001</li>
                        </ul>
                    </section>

                    <section class="dashboard-proforma-section">
                        <h3>Terms &amp; conditions</h3>
                        <ol class="dashboard-proforma-terms" data-preview-terms>
                            <li>50% deposit (<span data-preview-deposit>R 0.00</span>) secures venue and suppliers. Non-refundable if cancelled 30 days or less before the event.</li>
                            <li>Full balance must be paid 7 days before the event.</li>
                            <li>Bar Tab overrun is subject to actual usage and payable on the day of the event.</li>
                            <li data-preview-term-event>Event details will appear here once set.</li>
                            <li>No VAT charged.</li>
                            <li>Please use Invoice No. <span data-preview-term-number><?= htmlspecialchars($invoiceNumber, ENT_QUOTES, 'UTF-8') ?></span> as payment reference.</li>
                            <li>For any queries, contact Kordicraft Creations on the numbers listed above.</li>
                        </ol>
                    </section>

                    <p class="dashboard-proforma-thanks">Thank you for choosing Kordicraft Creations. We look forward to making your event exceptional.</p>
                    <p class="dashboard-proforma-footer">This is a computer-generated invoice. No signature is required.</p>
                </article>
            </section>
        </form>
    <?php endif; ?>
</section>

<?php
$dateLabel = 'Not set';
if (!empty($event['event_date'])) {
    $date = DateTimeImmutable::createFromFormat('Y-m-d', (string) $event['event_date']);
    $dateLabel = $date instanceof DateTimeImmutable ? $date->format('j M Y') : (string) $event['event_date'];
}
$companyLabel = trim((string) ($event['company'] ?? ''));
$locationLabel = trim((string) ($event['location'] ?? ''));
$revenueLabel = $event['revenue'] === null || $event['revenue'] === ''
    ? '—'
    : 'R ' . number_format((float) $event['revenue'], 2);
$statusClass = in_array((string) $event['status'], ['planned', 'confirmed', 'completed'], true)
    ? 'dashboard-status-' . $event['status']
    : '';
$financeClass = match ($event['finance'] ?? '') {
    'quotes' => 'dashboard-finance-quotes',
    'invoiced' => 'dashboard-finance-invoiced',
    'payment made' => 'dashboard-finance-payment-made',
    default => '',
};
$editHref = '/events/' . (int) $event['id'] . '/edit?page=' . (int) $eventPage;
$backHref = (int) $eventPage > 1 ? '/dashboard/events?page=' . (int) $eventPage : '/dashboard/events';
?>
<section class="dashboard-page">
    <div class="dashboard-page-bar">
        <header class="dashboard-page-header">
            <h1><?= htmlspecialchars((string) $event['title'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p>Event details</p>
        </header>
        <div class="dashboard-form-actions">
            <a class="dashboard-button dashboard-button-secondary" href="<?= htmlspecialchars($backHref, ENT_QUOTES, 'UTF-8') ?>">Back</a>
            <a class="dashboard-button" href="<?= htmlspecialchars($editHref, ENT_QUOTES, 'UTF-8') ?>">Edit</a>
        </div>
    </div>

    <div class="dashboard-event-detail">
        <section class="dashboard-panel dashboard-form-card">
            <h2>Event</h2>
            <dl class="dashboard-detail-list">
                <div>
                    <dt>Title</dt>
                    <dd><?= htmlspecialchars((string) $event['title'], ENT_QUOTES, 'UTF-8') ?></dd>
                </div>
                <div>
                    <dt>Date</dt>
                    <dd><?= htmlspecialchars($dateLabel, ENT_QUOTES, 'UTF-8') ?></dd>
                </div>
                <div>
                    <dt>Location</dt>
                    <dd><?= htmlspecialchars($locationLabel !== '' ? $locationLabel : '—', ENT_QUOTES, 'UTF-8') ?></dd>
                </div>
                <div>
                    <dt>Status</dt>
                    <dd>
                        <span class="dashboard-status-badge <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars((string) $event['status'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </dd>
                </div>
                <div>
                    <dt>Finance</dt>
                    <dd>
                        <span class="dashboard-status-badge <?= htmlspecialchars($financeClass, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars((string) ($event['finance'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </dd>
                </div>
                <div>
                    <dt>Size</dt>
                    <dd><?= (int) $event['attendants'] ?></dd>
                </div>
                <div>
                    <dt>Revenue</dt>
                    <dd><?= htmlspecialchars($revenueLabel, ENT_QUOTES, 'UTF-8') ?></dd>
                </div>
            </dl>
        </section>

        <section class="dashboard-panel dashboard-form-card">
            <h2>Company</h2>
            <dl class="dashboard-detail-list">
                <div>
                    <dt>Name</dt>
                    <dd><?= htmlspecialchars($companyLabel !== '' ? $companyLabel : '—', ENT_QUOTES, 'UTF-8') ?></dd>
                </div>
                <div>
                    <dt>Contact</dt>
                    <dd><?= htmlspecialchars($companyDetails['contact'] !== '' ? $companyDetails['contact'] : '—', ENT_QUOTES, 'UTF-8') ?></dd>
                </div>
                <div>
                    <dt>Email</dt>
                    <dd><?= htmlspecialchars($companyDetails['email'] !== '' ? $companyDetails['email'] : '—', ENT_QUOTES, 'UTF-8') ?></dd>
                </div>
                <div>
                    <dt>Phone</dt>
                    <dd><?= htmlspecialchars($companyDetails['phone'] !== '' ? $companyDetails['phone'] : '—', ENT_QUOTES, 'UTF-8') ?></dd>
                </div>
            </dl>
        </section>
    </div>
</section>

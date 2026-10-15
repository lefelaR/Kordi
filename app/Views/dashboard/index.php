<?php
$sections = [
    [
        'href' => '/dashboard/events',
        'title' => 'Events',
        'summary' => $eventCount === 0 ? 'No events yet.' : $eventCount . ($eventCount === 1 ? ' event.' : ' events.'),
    ],
    [
        'href' => '/dashboard/vendors',
        'title' => 'Vendors',
        'summary' => $vendorCount === 0 ? 'No vendors yet.' : $vendorCount . ($vendorCount === 1 ? ' vendor.' : ' vendors.'),
    ],
    [
        'href' => '/dashboard/enquiries',
        'title' => 'Enquiries',
        'summary' => $enquiries === [] ? 'No enquiries yet.' : count($enquiries) . (count($enquiries) === 1 ? ' enquiry.' : ' enquiries.'),
    ],
    [
        'href' => '/dashboard/venues',
        'title' => 'Venues',
        'summary' => $venueCount === 0 ? 'No venues yet.' : $venueCount . ($venueCount === 1 ? ' venue.' : ' venues.'),
    ],
    [
        'href' => '/dashboard/documents',
        'title' => 'Documents',
        'summary' => 'No documents yet.',
    ],
    [
        'href' => '/dashboard/contacts',
        'title' => 'Contacts',
        'summary' => 'No contacts yet.',
    ],
];

$chartYear = (int) ($eventRevenueChart['year'] ?? date('Y'));
$chartEvents = $eventRevenueChart['events'] ?? [];
$hasRevenuePoints = $chartEvents !== [];
$revenueLabel = 'R ' . number_format((float) $revenueTotal, 2, '.', ',');
$monthlyTotals = array_fill(1, 12, 0.0);
foreach ($chartEvents as $event) {
    $month = (int) ($event['month'] ?? 0);
    if ($month >= 1 && $month <= 12) {
        $monthlyTotals[$month] += (float) ($event['revenue'] ?? 0);
    }
}
$monthlyTotalsList = array_values($monthlyTotals);
?>
<?php
$metrics = [
    [
        'label' => 'Revenue',
        'value' => $revenueLabel,
        'meta' => $hasRevenuePoints
            ? 'From events with revenue in ' . $chartYear . '.'
            : 'No revenue recorded yet.',
    ],
    [
        'label' => 'Expenses',
        'value' => 'R 0.00',
        'meta' => 'No expenses recorded yet.',
    ],
    [
        'label' => 'Profits',
        'value' => 'R 0.00',
        'meta' => 'No profits recorded yet.',
    ],
];
?>
<section class="dashboard-page">
    <header class="dashboard-page-header">
        <h1>Dashboard</h1>
        <p>Events, vendors, enquiries, venues, documents, and contacts.</p>
    </header>
    <div class="dashboard-metric-grid">
        <?php foreach ($metrics as $metric): ?>
            <article class="dashboard-metric-card">
                <h2><?= htmlspecialchars($metric['label'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="dashboard-metric-value"><?= htmlspecialchars($metric['value'], ENT_QUOTES, 'UTF-8') ?></p>
                <p class="dashboard-metric-meta"><?= htmlspecialchars($metric['meta'], ENT_QUOTES, 'UTF-8') ?></p>
            </article>
        <?php endforeach; ?>
    </div>
    <div class="dashboard-revenue-layout">
        <div class="dashboard-panel dashboard-chart-panel dashboard-revenue-main">
            <header class="dashboard-chart-header">
                <div>
                    <h2>Event revenue</h2>
                    <p>Monthly revenue for <?= (int) $chartYear ?>, with client names on each event.</p>
                </div>
            </header>
            <?php if (!$hasRevenuePoints): ?>
                <p class="dashboard-empty dashboard-chart-empty">No event revenue for this year yet. Add revenue on an event to see it here.</p>
            <?php else: ?>
                <div class="dashboard-chart-canvas-wrap">
                    <canvas
                        data-overview-revenue-chart
                        aria-label="Event revenue by month for <?= (int) $chartYear ?>"
                        data-chart-events="<?= htmlspecialchars(json_encode($chartEvents, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>"
                    ></canvas>
                </div>
            <?php endif; ?>
        </div>
        <div class="dashboard-panel dashboard-chart-panel dashboard-revenue-side">
            <header class="dashboard-chart-header">
                <div>
                    <h2>Total revenue</h2>
                    <p><?= (int) $chartYear ?> year to date</p>
                </div>
            </header>
            <p class="dashboard-revenue-total"><?= htmlspecialchars($revenueLabel, ENT_QUOTES, 'UTF-8') ?></p>
            <?php if (!$hasRevenuePoints): ?>
                <p class="dashboard-empty dashboard-chart-empty">No revenue recorded yet.</p>
            <?php else: ?>
                <div class="dashboard-chart-canvas-wrap dashboard-chart-canvas-wrap-side">
                    <canvas
                        data-overview-total-revenue-chart
                        aria-label="Total revenue by month for <?= (int) $chartYear ?>"
                        data-monthly-totals="<?= htmlspecialchars(json_encode($monthlyTotalsList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>"
                    ></canvas>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="dashboard-section-grid">
        <?php foreach ($sections as $section): ?>
            <a class="dashboard-section-card" href="<?= htmlspecialchars($section['href'], ENT_QUOTES, 'UTF-8') ?>">
                <h2><?= htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p><?= htmlspecialchars($section['summary'], ENT_QUOTES, 'UTF-8') ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</section>

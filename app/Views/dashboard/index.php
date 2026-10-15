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

$calendarYear = (int) ($overviewCalendar['year'] ?? date('Y'));
$calendarMonth = (int) ($overviewCalendar['month'] ?? date('n'));
$calendarMonthValue = sprintf('%04d-%02d', $calendarYear, $calendarMonth);
$calendarTitle = DateTimeImmutable::createFromFormat('!Y-n', $calendarYear . '-' . $calendarMonth);
$calendarTitleLabel = $calendarTitle instanceof DateTimeImmutable
    ? $calendarTitle->format('F Y')
    : $calendarMonthValue;

$prevMonth = $calendarMonth === 1
    ? sprintf('%04d-12', $calendarYear - 1)
    : sprintf('%04d-%02d', $calendarYear, $calendarMonth - 1);
$nextMonth = $calendarMonth === 12
    ? sprintf('%04d-01', $calendarYear + 1)
    : sprintf('%04d-%02d', $calendarYear, $calendarMonth + 1);
$todayMonthValue = date('Y-m');

$eventsByDay = [];
foreach (($overviewCalendar['events'] ?? []) as $calendarEvent) {
    $day = (int) ($calendarEvent['day'] ?? 0);
    if ($day < 1) {
        continue;
    }
    $eventsByDay[$day][] = $calendarEvent;
}

$firstOfMonth = DateTimeImmutable::createFromFormat('!Y-n-j', $calendarYear . '-' . $calendarMonth . '-1');
$daysInMonth = $firstOfMonth instanceof DateTimeImmutable ? (int) $firstOfMonth->format('t') : 30;
$startWeekday = $firstOfMonth instanceof DateTimeImmutable ? ((int) $firstOfMonth->format('N')) : 1;
$leadingDays = $startWeekday - 1;
$previousMonthDate = $firstOfMonth instanceof DateTimeImmutable
    ? $firstOfMonth->modify('-1 month')
    : null;
$daysInPreviousMonth = $previousMonthDate instanceof DateTimeImmutable ? (int) $previousMonthDate->format('t') : 30;
$totalCells = (int) ceil(($leadingDays + $daysInMonth) / 7) * 7;
$todayDay = ((int) date('Y') === $calendarYear && (int) date('n') === $calendarMonth)
    ? (int) date('j')
    : null;

$weekdayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
$statusTone = static function (string $status): string {
    return match ($status) {
        'confirmed' => 'confirmed',
        'completed' => 'completed',
        default => 'planned',
    };
};

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
    <div class="dashboard-page-bar">
        <header class="dashboard-page-header">
            <h1>Dashboard</h1>
            <p>Events, vendors, enquiries, venues, documents, and contacts.</p>
        </header>
        <form class="dashboard-date-picker" method="get" action="/dashboard" data-overview-month-form>
            <label class="dashboard-visually-hidden" for="overview-month">Month</label>
            <input id="overview-month" type="month" name="month" value="<?= htmlspecialchars($overviewMonthValue ?? $calendarMonthValue, ENT_QUOTES, 'UTF-8') ?>">
        </form>
    </div>
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

    <div class="dashboard-panel dashboard-calendar-panel">
        <header class="dashboard-calendar-toolbar">
            <div class="dashboard-calendar-nav">
                <a class="dashboard-button dashboard-button-secondary dashboard-icon-button" href="/dashboard?month=<?= htmlspecialchars($prevMonth, ENT_QUOTES, 'UTF-8') ?>" aria-label="Previous month">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </a>
                <h2><?= htmlspecialchars($calendarTitleLabel, ENT_QUOTES, 'UTF-8') ?></h2>
                <a class="dashboard-button dashboard-button-secondary dashboard-icon-button" href="/dashboard?month=<?= htmlspecialchars($nextMonth, ENT_QUOTES, 'UTF-8') ?>" aria-label="Next month">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </a>
            </div>
            <div class="dashboard-calendar-views">
                <span class="is-active" aria-current="page">Month</span>
                <a href="/dashboard?month=<?= htmlspecialchars($todayMonthValue, ENT_QUOTES, 'UTF-8') ?>">Today</a>
            </div>
        </header>
        <div class="dashboard-calendar-weekdays" aria-hidden="true">
            <?php foreach ($weekdayLabels as $weekday): ?>
                <div><?= htmlspecialchars($weekday, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endforeach; ?>
        </div>
        <div class="dashboard-calendar-grid" role="grid" aria-label="Events calendar for <?= htmlspecialchars($calendarTitleLabel, ENT_QUOTES, 'UTF-8') ?>">
            <?php for ($cell = 0; $cell < $totalCells; $cell++): ?>
                <?php
                $dayNumber = $cell - $leadingDays + 1;
                $isCurrentMonth = $dayNumber >= 1 && $dayNumber <= $daysInMonth;
                if ($isCurrentMonth) {
                    $displayDay = $dayNumber;
                    $dayEvents = $eventsByDay[$dayNumber] ?? [];
                    $isToday = $todayDay === $dayNumber;
                } elseif ($dayNumber < 1) {
                    $displayDay = $daysInPreviousMonth + $dayNumber;
                    $dayEvents = [];
                    $isToday = false;
                } else {
                    $displayDay = $dayNumber - $daysInMonth;
                    $dayEvents = [];
                    $isToday = false;
                }
                ?>
                <div class="dashboard-calendar-day<?= $isCurrentMonth ? '' : ' is-outside' ?><?= $isToday ? ' is-today' : '' ?>" role="gridcell">
                    <span class="dashboard-calendar-day-number<?= $isToday ? ' is-today' : '' ?>"><?= (int) $displayDay ?></span>
                    <?php if ($dayEvents !== []): ?>
                        <div class="dashboard-calendar-events">
                            <?php foreach ($dayEvents as $dayEvent): ?>
                                <?php
                                $tone = $statusTone((string) ($dayEvent['status'] ?? 'planned'));
                                $label = trim((string) ($dayEvent['title'] ?? ''));
                                if (($dayEvent['client'] ?? '') !== '') {
                                    $label = $dayEvent['client'] . ' · ' . $label;
                                }
                                $href = '/events/' . (int) ($dayEvent['id'] ?? 0) . '/edit';
                                ?>
                                <a class="dashboard-calendar-event is-<?= htmlspecialchars($tone, ENT_QUOTES, 'UTF-8') ?>" href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>">
                                    <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
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
    <script>
        (() => {
            const form = document.querySelector('[data-overview-month-form]');
            const input = document.getElementById('overview-month');
            if (!form || !input) {
                return;
            }
            input.addEventListener('change', () => {
                form.submit();
            });
        })();
    </script>
</section>

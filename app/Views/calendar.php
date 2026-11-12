<?php
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar - Kordicraft Creations</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Montserrat:wght@300;400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/index.css">
</head>

<body>
    <?php require __DIR__ . '/includes/site-header.php'; ?>

    <section class="calendar-section" aria-labelledby="calendar-heading">
        <h1 id="calendar-heading" class="section-title">Calendar</h1>
        <p class="section-sub">Events scheduled this month.</p>
        <div class="site-calendar">
            <div class="site-calendar-toolbar">
                <a class="site-calendar-shift" href="/calendar?month=<?= htmlspecialchars($previousMonth, ENT_QUOTES, 'UTF-8') ?>">Previous</a>
                <h2 id="calendar-month"><?= htmlspecialchars($calendarLabel, ENT_QUOTES, 'UTF-8') ?></h2>
                <a class="site-calendar-shift" href="/calendar?month=<?= htmlspecialchars($nextMonth, ENT_QUOTES, 'UTF-8') ?>">Next</a>
            </div>
            <div class="dashboard-calendar-weekdays" aria-hidden="true">
                <div>Mon</div>
                <div>Tue</div>
                <div>Wed</div>
                <div>Thu</div>
                <div>Fri</div>
                <div>Sat</div>
                <div>Sun</div>
            </div>
            <div class="dashboard-calendar-grid" role="grid" aria-labelledby="calendar-month">
                <?php foreach ($weeks as $week): ?>
                    <?php foreach ($week as $day): ?>
                        <div class="dashboard-calendar-day<?= $day['outside'] ? ' is-outside' : '' ?><?= $day['today'] ? ' is-today' : '' ?>" role="gridcell"<?= $day['today'] ? ' aria-current="date"' : '' ?>>
                            <span class="dashboard-calendar-day-number<?= $day['today'] ? ' is-today' : '' ?>"><?= (int) $day['day'] ?></span>
                            <?php if ($day['events'] !== []): ?>
                                <div class="dashboard-calendar-events">
                                    <?php foreach ($day['events'] as $event): ?>
                                        <button
                                            type="button"
                                            class="dashboard-calendar-event is-<?= htmlspecialchars($event['tone'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-event-open
                                            data-event-title="<?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-event-date="<?= htmlspecialchars($event['date'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-event-client="<?= htmlspecialchars($event['client'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-event-location="<?= htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-event-status="<?= htmlspecialchars($event['status'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-event-size="<?= htmlspecialchars($event['size'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-event-tone="<?= htmlspecialchars($event['tone'], ENT_QUOTES, 'UTF-8') ?>"
                                        >
                                            <span><?= htmlspecialchars($event['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <dialog class="event-modal" id="event-modal" aria-labelledby="event-modal-title">
        <div class="event-modal-card">
            <header class="event-modal-header">
                <h2 id="event-modal-title" data-event-modal-title></h2>
                <button type="button" class="event-modal-close" data-event-close aria-label="Close">Close</button>
            </header>
            <dl class="event-modal-details">
                <div>
                    <dt>Date</dt>
                    <dd data-event-field="date"></dd>
                </div>
                <div>
                    <dt>Client</dt>
                    <dd data-event-field="client"></dd>
                </div>
                <div>
                    <dt>Location</dt>
                    <dd data-event-field="location"></dd>
                </div>
                <div>
                    <dt>Status</dt>
                    <dd data-event-field="status"></dd>
                </div>
                <div>
                    <dt>Size</dt>
                    <dd data-event-field="size"></dd>
                </div>
            </dl>
            <div class="event-modal-actions">
                <a class="btn-black" href="/#contact">Buy ticket</a>
            </div>
        </div>
    </dialog>

    <footer>
        <h2>Let's Plan Your Event</h2>
        <p>©️ 2024 Kordicraft Creations • All Rights Reserved • Designed with care</p>
    </footer>

    <script src="/assets/js/index.js" defer></script>
    <script src="/assets/js/calendar-event.js" defer></script>
</body>

</html>

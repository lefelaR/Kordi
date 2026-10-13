<section class="dashboard-page">
    <div class="dashboard-page-bar">
        <header class="dashboard-page-header">
            <h1>Events</h1>
            <p>Planned events.</p>
        </header>
        <a class="dashboard-button" href="/events/add">Add</a>
    </div>
    <?php if ($events !== []): ?>
        <div class="dashboard-status" aria-label="Event statuses">
            <?php
            $statusCounts = [];
            foreach ($events as $event) {
                $status = $event['status'] !== '' ? $event['status'] : 'Unspecified';
                $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
            }
            foreach ($statusCounts as $status => $count): ?>
                <span><?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>: <?= (int) $count ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="dashboard-panel">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($events === []): ?>
                    <tr>
                        <td class="dashboard-empty" colspan="3">No events yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($events as $event): ?>
                        <?php
                        $dateLabel = 'Not set';
                        if (!empty($event['event_date'])) {
                            $date = DateTimeImmutable::createFromFormat('Y-m-d', $event['event_date']);
                            $dateLabel = $date instanceof DateTimeImmutable ? $date->format('j M Y') : $event['event_date'];
                        }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($dateLabel, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($event['status'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

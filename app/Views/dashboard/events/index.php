<section class="dashboard-page">
    <div class="dashboard-page-bar">
        <header class="dashboard-page-header">
            <h1>Events</h1>
            <p>Planned events.</p>
        </header>
        <a class="dashboard-button" href="/events/add">Add</a>
    </div>
    <?php if ($eventStatusCounts !== []): ?>
        <div class="dashboard-status" aria-label="Event statuses">
            <?php foreach ($eventStatusCounts as $status => $count): ?>
                <?php $statusClass = in_array((string) $status, ['planned', 'confirmed', 'completed'], true) ? 'dashboard-status-' . $status : ''; ?>
                <span class="<?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>: <?= (int) $count ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="dashboard-panel">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Company</th>
                    <th>Location</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Finance</th>
                    <th>Size</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($events === []): ?>
                    <tr>
                        <td class="dashboard-empty" colspan="8">No events yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($events as $event): ?>
                        <?php
                        $dateLabel = 'Not set';
                        if (!empty($event['event_date'])) {
                            $date = DateTimeImmutable::createFromFormat('Y-m-d', $event['event_date']);
                            $dateLabel = $date instanceof DateTimeImmutable ? $date->format('j M Y') : $event['event_date'];
                        }
                        $companyLabel = trim((string) ($event['company'] ?? ''));
                        $locationLabel = trim((string) ($event['location'] ?? ''));
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($companyLabel !== '' ? $companyLabel : '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($locationLabel !== '' ? $locationLabel : '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($dateLabel, ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php $statusClass = in_array($event['status'], ['planned', 'confirmed', 'completed'], true) ? 'dashboard-status-' . $event['status'] : ''; ?>
                                <span class="dashboard-status-badge <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($event['status'], ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <?php
                                $financeClass = match ($event['finance'] ?? '') {
                                    'quotes' => 'dashboard-finance-quotes',
                                    'invoiced' => 'dashboard-finance-invoiced',
                                    'payment made' => 'dashboard-finance-payment-made',
                                    default => '',
                                };
                                ?>
                                <span class="dashboard-status-badge <?= htmlspecialchars($financeClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) ($event['finance'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td><?= (int) $event['attendants'] ?></td>
                            <td>
                                <div class="dashboard-actions">
                                    <!-- <form method="post" action="/events/<?= (int) $event['id'] ?>/status">
                                        <input type="hidden" name="page" value="<?= (int) $eventPage ?>">
                                        <label>
                                            <span class="dashboard-visually-hidden">Status for <?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></span>
                                            <select name="status" onchange="this.form.requestSubmit()">
                                                <?php foreach (['planned', 'confirmed', 'completed'] as $option): ?>
                                                    <option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"<?= $event['status'] === $option ? ' selected' : '' ?>><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>
                                    </form> -->
                                    <a class="dashboard-button dashboard-button-secondary dashboard-icon-button" href="/events/<?= (int) $event['id'] ?>/edit?page=<?= (int) $eventPage ?>" aria-label="Edit <?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 20h9" />
                                            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                        </svg>
                                    </a>
                                    <form method="post" action="/events/<?= (int) $event['id'] ?>/delete" onsubmit="return confirm('Delete this event?')">
                                        <input type="hidden" name="page" value="<?= (int) $eventPage ?>">
                                        <button class="dashboard-button dashboard-button-destructive dashboard-icon-button" type="submit" aria-label="Delete <?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M3 6h18" />
                                                <path d="M8 6V4h8v2" />
                                                <path d="M19 6l-1 14H6L5 6" />
                                                <path d="M10 11v6" />
                                                <path d="M14 11v6" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php if ($eventCount > 0): ?>
            <nav class="dashboard-pagination" aria-label="Events pages">
                <p>Showing <?= (int) $eventRangeStart ?>–<?= (int) $eventRangeEnd ?> of <?= (int) $eventCount ?></p>
                <div class="dashboard-pagination-links">
                    <?php if ($eventPage > 1): ?>
                        <a href="/dashboard/events?page=<?= (int) ($eventPage - 1) ?>">Previous</a>
                    <?php else: ?>
                        <span aria-disabled="true">Previous</span>
                    <?php endif; ?>
                    <?php foreach ($eventPageLinks as $page): ?>
                        <?php if ($page === null): ?>
                            <span aria-hidden="true">…</span>
                        <?php elseif ($page === $eventPage): ?>
                            <a href="/dashboard/events?page=<?= (int) $page ?>" aria-current="page"><?= (int) $page ?></a>
                        <?php else: ?>
                            <a href="/dashboard/events?page=<?= (int) $page ?>"><?= (int) $page ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($eventPage < $eventPageCount): ?>
                        <a href="/dashboard/events?page=<?= (int) ($eventPage + 1) ?>">Next</a>
                    <?php else: ?>
                        <span aria-disabled="true">Next</span>
                    <?php endif; ?>
                </div>
            </nav>
        <?php endif; ?>
    </div>
</section>

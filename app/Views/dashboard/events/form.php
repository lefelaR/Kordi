<section class="dashboard-page">
    <header class="dashboard-page-header">
        <h1><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($summary, ENT_QUOTES, 'UTF-8') ?></p>
    </header>
    <?php if ($error !== ''): ?>
        <p class="dashboard-form-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form class="dashboard-form" method="post" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="page" value="<?= (int) ($eventPage ?? 1) ?>">
        <label>
            Title
            <input type="text" name="title" maxlength="180" required value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>
            Date
            <input type="date" name="event_date" value="<?= htmlspecialchars($eventDate, ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>
            Attendants
            <input type="number" name="attendants" min="0" max="1000000" required value="<?= htmlspecialchars((string) $attendants, ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>
            Status
            <select name="status">
                <?php foreach (['planned', 'confirmed', 'completed'] as $option): ?>
                    <option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"<?= $status === $option ? ' selected' : '' ?>><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="dashboard-form-actions">
            <button class="dashboard-button" type="submit">Save</button>
            <a class="dashboard-button dashboard-button-secondary" href="<?= ($eventPage ?? 1) > 1 ? '/dashboard/events?page=' . (int) $eventPage : '/dashboard/events' ?>">Cancel</a>
        </div>
    </form>
</section>

<section class="dashboard-page">
    <header class="dashboard-page-header">
        <h1>Add event</h1>
        <p>Save an event to the events list.</p>
    </header>
    <?php if ($error !== ''): ?>
        <p class="dashboard-form-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form class="dashboard-form" method="post" action="/events/add">
        <label>
            Title
            <input type="text" name="title" maxlength="180" required value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>
            Date
            <input type="date" name="event_date" value="<?= htmlspecialchars($eventDate, ENT_QUOTES, 'UTF-8') ?>">
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
            <a class="dashboard-button dashboard-button-secondary" href="/dashboard/events">Cancel</a>
        </div>
    </form>
</section>

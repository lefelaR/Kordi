<section class="dashboard-page">
    <header class="dashboard-page-header">
        <h1><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($summary, ENT_QUOTES, 'UTF-8') ?></p>
    </header>
    <?php if ($error !== ''): ?>
        <p class="dashboard-form-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form class="dashboard-form dashboard-form-with-details" method="post" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="page" value="<?= (int) ($eventPage ?? 1) ?>">
        <div class="dashboard-form-fields dashboard-form-card">
            <label>
                Title
                <input type="text" name="title" maxlength="180" required value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label>
                Company
                <input type="text" name="company" maxlength="180" value="<?= htmlspecialchars($company ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label>
                Location
                <input type="text" name="location" maxlength="255" value="<?= htmlspecialchars($location ?? '', ENT_QUOTES, 'UTF-8') ?>">
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
                Revenue
                <input type="text" name="revenue" inputmode="decimal" maxlength="15" value="<?= htmlspecialchars((string) ($revenue ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label>
                Status
                <select name="status">
                    <?php foreach (['planned', 'confirmed', 'completed'] as $option): ?>
                        <option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"<?= $status === $option ? ' selected' : '' ?>><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Finance
                <select name="finance">
                    <?php foreach (['quotes', 'invoiced', 'payment made'] as $option): ?>
                        <option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"<?= $finance === $option ? ' selected' : '' ?>><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="dashboard-form-actions">
                <button class="dashboard-button<?= !empty($actionIcons) ? ' dashboard-button-with-icon' : '' ?>" type="submit">
                    <?php if (!empty($actionIcons)): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 6 9 17l-5-5" />
                        </svg>
                    <?php endif; ?>
                    Save
                </button>
                <a class="dashboard-button dashboard-button-secondary<?= !empty($actionIcons) ? ' dashboard-button-with-icon' : '' ?>" href="<?= ($eventPage ?? 1) > 1 ? '/dashboard/events?page=' . (int) $eventPage : '/dashboard/events' ?>">
                    <?php if (!empty($actionIcons)): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    <?php endif; ?>
                    Cancel
                </a>
            </div>
        </div>
        <div class="dashboard-form-company dashboard-form-card">
            <h2>Company details</h2>
            <p>Saved to the companies list with this event’s company name.</p>
            <label>
                Contact
                <input type="text" name="company_contact" maxlength="180" value="<?= htmlspecialchars((string) ($companyContact ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label>
                Email
                <input type="email" name="company_email" maxlength="255" value="<?= htmlspecialchars((string) ($companyEmail ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </label>
            <label>
                Phone
                <input type="text" name="company_phone" maxlength="40" value="<?= htmlspecialchars((string) ($companyPhone ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </label>
        </div>
    </form>
</section>

<section class="dashboard-page">
    <header class="dashboard-page-header">
        <h1><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($summary, ENT_QUOTES, 'UTF-8') ?></p>
    </header>
    <?php if ($error !== ''): ?>
        <p class="dashboard-form-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php
    $venueCancelQuery = http_build_query(array_filter([
        'q' => ($venueSearch ?? '') !== '' ? $venueSearch : null,
        'page' => ($venuePage ?? 1) > 1 ? (string) $venuePage : null,
    ]));
    $venueCancelHref = '/dashboard/venues' . ($venueCancelQuery !== '' ? '?' . $venueCancelQuery : '');
    ?>
    <form class="dashboard-form" method="post" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="page" value="<?= (int) ($venuePage ?? 1) ?>">
        <?php if (($venueSearch ?? '') !== ''): ?>
            <input type="hidden" name="q" value="<?= htmlspecialchars($venueSearch, ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>
        <label>
            Name
            <input type="text" name="name" maxlength="180" required value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>
            Location
            <input type="text" name="location" maxlength="255" value="<?= htmlspecialchars($location, ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>
            Price
            <input type="text" name="price" inputmode="decimal" maxlength="15" value="<?= htmlspecialchars($price, ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>
            Capacity
            <input type="number" name="capacity" min="1" step="1" value="<?= htmlspecialchars($capacity, ENT_QUOTES, 'UTF-8') ?>">
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
            <a class="dashboard-button dashboard-button-secondary<?= !empty($actionIcons) ? ' dashboard-button-with-icon' : '' ?>" href="<?= htmlspecialchars($venueCancelHref, ENT_QUOTES, 'UTF-8') ?>">
                <?php if (!empty($actionIcons)): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                <?php endif; ?>
                Cancel
            </a>
        </div>
    </form>
</section>

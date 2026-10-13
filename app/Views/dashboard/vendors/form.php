<section class="dashboard-page">
    <header class="dashboard-page-header">
        <h1><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($summary, ENT_QUOTES, 'UTF-8') ?></p>
    </header>
    <?php if ($error !== ''): ?>
        <p class="dashboard-form-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form class="dashboard-form" method="post" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="page" value="<?= (int) ($vendorPage ?? 1) ?>">
        <label>
            Name
            <input type="text" name="name" maxlength="180" required value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>
            Contact
            <input type="text" name="contact" maxlength="180" value="<?= htmlspecialchars($contact, ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>
            Email
            <input type="email" name="email" maxlength="255" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>
            Phone
            <input type="text" name="phone" maxlength="40" value="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>">
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
            <a class="dashboard-button dashboard-button-secondary<?= !empty($actionIcons) ? ' dashboard-button-with-icon' : '' ?>" href="<?= ($vendorPage ?? 1) > 1 ? '/dashboard/vendors?page=' . (int) $vendorPage : '/dashboard/vendors' ?>">
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

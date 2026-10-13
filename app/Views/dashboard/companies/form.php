<section class="dashboard-page">
    <header class="dashboard-page-header">
        <h1><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars($summary, ENT_QUOTES, 'UTF-8') ?></p>
    </header>
    <?php if ($error !== ''): ?>
        <p class="dashboard-form-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form class="dashboard-form" method="post" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="page" value="<?= (int) ($companyPage ?? 1) ?>">
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
            <button class="dashboard-button" type="submit">Save</button>
            <a class="dashboard-button dashboard-button-secondary" href="<?= ($companyPage ?? 1) > 1 ? '/dashboard/companies?page=' . (int) $companyPage : '/dashboard/companies' ?>">Cancel</a>
        </div>
    </form>
</section>

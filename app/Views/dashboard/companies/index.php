<section class="dashboard-page">
    <div class="dashboard-page-bar">
        <header class="dashboard-page-header">
            <h1>Companies</h1>
            <p>Client companies for events and invoices.</p>
        </header>
        <a class="dashboard-button" href="/companies/add">Add</a>
    </div>
    <div class="dashboard-panel">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($companies === []): ?>
                    <tr>
                        <td class="dashboard-empty" colspan="5">No companies yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($companies as $company): ?>
                        <tr>
                            <td><?= htmlspecialchars($company['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($company['contact'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($company['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($company['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <div class="dashboard-actions">
                                    <a class="dashboard-button dashboard-button-secondary dashboard-icon-button" href="/companies/<?= (int) $company['id'] ?>/edit?page=<?= (int) $companyPage ?>" aria-label="Edit <?= htmlspecialchars($company['name'], ENT_QUOTES, 'UTF-8') ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 20h9" />
                                            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                        </svg>
                                    </a>
                                    <form method="post" action="/companies/<?= (int) $company['id'] ?>/delete" onsubmit="return confirm('Delete this company?')">
                                        <input type="hidden" name="page" value="<?= (int) $companyPage ?>">
                                        <button class="dashboard-button dashboard-button-destructive dashboard-icon-button" type="submit" aria-label="Delete <?= htmlspecialchars($company['name'], ENT_QUOTES, 'UTF-8') ?>">
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
        <?php if ($companyCount > 0): ?>
            <nav class="dashboard-pagination" aria-label="Companies pages">
                <p>Showing <?= (int) $companyRangeStart ?>–<?= (int) $companyRangeEnd ?> of <?= (int) $companyCount ?></p>
                <div class="dashboard-pagination-links">
                    <?php if ($companyPage > 1): ?>
                        <a href="/dashboard/companies?page=<?= (int) ($companyPage - 1) ?>">Previous</a>
                    <?php else: ?>
                        <span aria-disabled="true">Previous</span>
                    <?php endif; ?>
                    <?php foreach ($companyPageLinks as $page): ?>
                        <?php if ($page === null): ?>
                            <span aria-hidden="true">…</span>
                        <?php elseif ($page === $companyPage): ?>
                            <a href="/dashboard/companies?page=<?= (int) $page ?>" aria-current="page"><?= (int) $page ?></a>
                        <?php else: ?>
                            <a href="/dashboard/companies?page=<?= (int) $page ?>"><?= (int) $page ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($companyPage < $companyPageCount): ?>
                        <a href="/dashboard/companies?page=<?= (int) ($companyPage + 1) ?>">Next</a>
                    <?php else: ?>
                        <span aria-disabled="true">Next</span>
                    <?php endif; ?>
                </div>
            </nav>
        <?php endif; ?>
    </div>
</section>

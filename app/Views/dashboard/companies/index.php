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
                </tr>
            </thead>
            <tbody>
                <?php if ($companies === []): ?>
                    <tr>
                        <td class="dashboard-empty" colspan="4">No companies yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($companies as $company): ?>
                        <tr>
                            <td><?= htmlspecialchars($company['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($company['contact'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($company['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($company['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
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

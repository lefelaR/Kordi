<section class="dashboard-page">
    <div class="dashboard-page-bar">
        <header class="dashboard-page-header">
            <h1>Vendors</h1>
            <p>People and companies hired for events.</p>
        </header>
        <a class="dashboard-button" href="/vendors/add">Add</a>
    </div>
    <div class="dashboard-panel">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Price</th>
                    <th>Capacity</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($vendors === []): ?>
                    <tr>
                        <td class="dashboard-empty" colspan="5">No vendors yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($vendors as $vendor): ?>
                        <tr>
                            <td><?= htmlspecialchars($vendor['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($vendor['location'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($vendor['price'] !== null && $vendor['price'] !== '' ? 'R ' . number_format((float) $vendor['price'], 2) : '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($vendor['capacity'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <div class="dashboard-actions">
                                    <a class="dashboard-button dashboard-button-secondary dashboard-icon-button" href="/vendors/<?= (int) $vendor['id'] ?>/edit?page=<?= (int) $vendorPage ?>" aria-label="Edit <?= htmlspecialchars($vendor['name'], ENT_QUOTES, 'UTF-8') ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 20h9" />
                                            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                        </svg>
                                    </a>
                                    <form method="post" action="/vendors/<?= (int) $vendor['id'] ?>/delete" onsubmit="return confirm('Delete this vendor?')">
                                        <input type="hidden" name="page" value="<?= (int) $vendorPage ?>">
                                        <button class="dashboard-button dashboard-button-destructive dashboard-icon-button" type="submit" aria-label="Delete <?= htmlspecialchars($vendor['name'], ENT_QUOTES, 'UTF-8') ?>">
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
        <?php if ($vendorCount > 0): ?>
            <nav class="dashboard-pagination" aria-label="Vendors pages">
                <p>Showing <?= (int) $vendorRangeStart ?>–<?= (int) $vendorRangeEnd ?> of <?= (int) $vendorCount ?></p>
                <div class="dashboard-pagination-links">
                    <?php if ($vendorPage > 1): ?>
                        <a href="/dashboard/vendors?page=<?= (int) ($vendorPage - 1) ?>">Previous</a>
                    <?php else: ?>
                        <span aria-disabled="true">Previous</span>
                    <?php endif; ?>
                    <?php foreach ($vendorPageLinks as $page): ?>
                        <?php if ($page === null): ?>
                            <span aria-hidden="true">…</span>
                        <?php elseif ($page === $vendorPage): ?>
                            <a href="/dashboard/vendors?page=<?= (int) $page ?>" aria-current="page"><?= (int) $page ?></a>
                        <?php else: ?>
                            <a href="/dashboard/vendors?page=<?= (int) $page ?>"><?= (int) $page ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($vendorPage < $vendorPageCount): ?>
                        <a href="/dashboard/vendors?page=<?= (int) ($vendorPage + 1) ?>">Next</a>
                    <?php else: ?>
                        <span aria-disabled="true">Next</span>
                    <?php endif; ?>
                </div>
            </nav>
        <?php endif; ?>
    </div>
</section>

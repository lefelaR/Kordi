<?php
$dashboardLinks = [
    'overview' => [
        'label' => 'Dashboard',
        'href' => '/dashboard',
        'icon' => '<rect width="7" height="7" x="3" y="3" rx="1" /><rect width="7" height="7" x="14" y="3" rx="1" /><rect width="7" height="7" x="14" y="14" rx="1" /><rect width="7" height="7" x="3" y="14" rx="1" />',
    ],
    'events' => [
        'label' => 'Events',
        'href' => '/dashboard/events',
        'icon' => '<path d="M8 2v4" /><path d="M16 2v4" /><rect width="18" height="18" x="3" y="4" rx="2" /><path d="M3 10h18" />',
    ],
    'vendors' => [
        'label' => 'Vendors',
        'href' => '/dashboard/vendors',
        'icon' => '<path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" /><rect width="20" height="14" x="2" y="6" rx="2" />',
    ],
    'enquiries' => [
        'label' => 'Enquiries',
        'href' => '/dashboard/enquiries',
        'icon' => '<rect width="20" height="16" x="2" y="4" rx="2" /><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />',
    ],
    'venues' => [
        'label' => 'Venues',
        'href' => '/dashboard/venues',
        'icon' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" /><circle cx="12" cy="10" r="3" />',
    ],
    'companies' => [
        'label' => 'Companies',
        'href' => '/dashboard/companies',
        'icon' => '<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z" /><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2" /><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2" /><path d="M10 6h4" /><path d="M10 10h4" /><path d="M10 14h4" /><path d="M10 18h4" />',
    ],
    'documents' => [
        'label' => 'Documents',
        'href' => '/dashboard/documents',
        'icon' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" /><path d="M14 2v4a2 2 0 0 0 2 2h4" /><path d="M10 9H8" /><path d="M16 13H8" /><path d="M16 17H8" />',
        'children' => [
            // 'proposals' => ['label' => 'Proposals', 'href' => '/dashboard/documents/proposals'],
            'invoices' => ['label' => 'Invoices', 'href' => '/dashboard/documents/invoices'],
            'agreements' => ['label' => 'Agreements', 'href' => '/dashboard/documents/agreements'],
            // 'proofs' => ['label' => 'Proofs', 'href' => '/dashboard/documents/proofs'],
            // 'acknowledgement' => ['label' => 'Acknowledgement', 'href' => '/dashboard/documents/acknowledgement'],
        ],
    ],
    'contacts' => [
        'label' => 'Contacts',
        'href' => '/dashboard/contacts',
        'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />',
    ],
    'settings' => [
        'label' => 'Settings',
        'href' => '/dashboard/settings',
        'icon' => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" /><circle cx="12" cy="12" r="3" />',
    ],
];
$currentSection = $currentSection ?? '';
$currentDocument = $currentDocument ?? '';
?>
<aside class="dashboard-sidebar">
    <a class="dashboard-brand" href="/dashboard">Kordicraft</a>
    <nav class="dashboard-side-nav" aria-label="Dashboard">
        <?php foreach ($dashboardLinks as $section => $link): ?>
            <?php if (!empty($link['children'])): ?>
                <?php $groupOpen = $currentSection === $section; ?>
                <details class="dashboard-nav-group"<?= $groupOpen ? ' open' : '' ?>>
                    <summary class="dashboard-nav-trigger<?= $groupOpen ? ' dashboard-nav-parent-active' : '' ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $link['icon'] ?></svg>
                        <span class="dashboard-nav-trigger-label"><?= htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') ?></span>
                        <svg class="dashboard-nav-chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m9 18 6-6-6-6" />
                        </svg>
                    </summary>
                    <div class="dashboard-nav-children">
                        <?php foreach ($link['children'] as $childKey => $child): ?>
                            <a href="<?= htmlspecialchars($child['href'], ENT_QUOTES, 'UTF-8') ?>"<?= $currentSection === $section && $currentDocument === $childKey ? ' aria-current="page"' : '' ?>><?= htmlspecialchars($child['label'], ENT_QUOTES, 'UTF-8') ?></a>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php else: ?>
                <a href="<?= htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8') ?>"<?= $currentSection === $section ? ' aria-current="page"' : '' ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $link['icon'] ?></svg>
                    <?= htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
        <a class="dashboard-nav-logout" href="/logout">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                <path d="m16 17 5-5-5-5" />
                <path d="M21 12H9" />
            </svg>
            Log out
        </a>
    </nav>
</aside>

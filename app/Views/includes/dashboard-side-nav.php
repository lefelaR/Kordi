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
    'documents' => [
        'label' => 'Documents',
        'href' => '/dashboard/documents',
        'icon' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" /><path d="M14 2v4a2 2 0 0 0 2 2h4" /><path d="M10 9H8" /><path d="M16 13H8" /><path d="M16 17H8" />',
    ],
    'contacts' => [
        'label' => 'Contacts',
        'href' => '/dashboard/contacts',
        'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />',
    ],
];
$currentSection = $currentSection ?? '';
?>
<aside class="dashboard-sidebar">
    <a class="dashboard-brand" href="/dashboard">Kordicraft</a>
    <nav class="dashboard-side-nav" aria-label="Dashboard">
        <?php foreach ($dashboardLinks as $section => $link): ?>
            <a href="<?= htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8') ?>"<?= $currentSection === $section ? ' aria-current="page"' : '' ?>>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $link['icon'] ?></svg>
                <?= htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') ?>
            </a>
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

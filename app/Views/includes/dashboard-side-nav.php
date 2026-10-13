<?php
$dashboardLinks = [
    'overview' => ['label' => 'Dashboard', 'href' => '/dashboard'],
    'events' => ['label' => 'Events', 'href' => '/dashboard/events'],
    'vendors' => ['label' => 'Vendors', 'href' => '/dashboard/vendors'],
    'enquiries' => ['label' => 'Enquiries', 'href' => '/dashboard/enquiries'],
    'venues' => ['label' => 'Venues', 'href' => '/dashboard/venues'],
    'documents' => ['label' => 'Documents', 'href' => '/dashboard/documents'],
    'contacts' => ['label' => 'Contacts', 'href' => '/dashboard/contacts'],
];
$currentSection = $currentSection ?? '';
?>
<aside class="dashboard-sidebar">
    <a class="dashboard-brand" href="/dashboard">Kordicraft</a>
    <nav class="dashboard-side-nav" aria-label="Dashboard">
        <?php foreach ($dashboardLinks as $section => $link): ?>
            <a href="<?= htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8') ?>"<?= $currentSection === $section ? ' aria-current="page"' : '' ?>><?= htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') ?></a>
        <?php endforeach; ?>
        <a class="dashboard-nav-logout" href="/logout">Log out</a>
    </nav>
</aside>

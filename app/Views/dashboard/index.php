<?php
$sections = [
    [
        'href' => '/dashboard/events',
        'title' => 'Events',
        'summary' => $eventCount === 0 ? 'No events yet.' : $eventCount . ($eventCount === 1 ? ' event.' : ' events.'),
    ],
    [
        'href' => '/dashboard/vendors',
        'title' => 'Vendors',
        'summary' => $vendorCount === 0 ? 'No vendors yet.' : $vendorCount . ($vendorCount === 1 ? ' vendor.' : ' vendors.'),
    ],
    [
        'href' => '/dashboard/enquiries',
        'title' => 'Enquiries',
        'summary' => $enquiries === [] ? 'No enquiries yet.' : count($enquiries) . (count($enquiries) === 1 ? ' enquiry.' : ' enquiries.'),
    ],
    [
        'href' => '/dashboard/venues',
        'title' => 'Venues',
        'summary' => 'No venues yet.',
    ],
    [
        'href' => '/dashboard/documents',
        'title' => 'Documents',
        'summary' => 'No documents yet.',
    ],
    [
        'href' => '/dashboard/contacts',
        'title' => 'Contacts',
        'summary' => 'No contacts yet.',
    ],
];
?>
<section class="dashboard-page">
    <header class="dashboard-page-header">
        <h1>Dashboard</h1>
        <p>Events, vendors, enquiries, venues, documents, and contacts.</p>
    </header>
    <div class="dashboard-section-grid">
        <?php foreach ($sections as $section): ?>
            <a class="dashboard-section-card" href="<?= htmlspecialchars($section['href'], ENT_QUOTES, 'UTF-8') ?>">
                <h2><?= htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p><?= htmlspecialchars($section['summary'], ENT_QUOTES, 'UTF-8') ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</section>

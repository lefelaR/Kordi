<section class="dashboard-page">
    <header class="dashboard-page-header">
        <h1>Enquiries</h1>
        <p>Messages sent from the website.</p>
    </header>
    <div class="dashboard-panel">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Event type</th>
                    <th>Date</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($enquiries === []): ?>
                    <tr>
                        <td class="dashboard-empty" colspan="5">No enquiries yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($enquiries as $enquiry): ?>
                        <?php
                        $dateLabel = 'Not set';
                        if (!empty($enquiry['event_date'])) {
                            $date = DateTimeImmutable::createFromFormat('Y-m-d', $enquiry['event_date']);
                            $dateLabel = $date instanceof DateTimeImmutable ? $date->format('j M Y') : $enquiry['event_date'];
                        }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($enquiry['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($enquiry['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($enquiry['event_type'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($dateLabel, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($enquiry['message'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

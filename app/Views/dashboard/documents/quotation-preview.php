<?php
/** @var array<string, mixed> $quotation */
$items = is_array($quotation['items'] ?? null) ? $quotation['items'] : [];
$total = 0.0;
foreach ($items as $item) {
    $total += (float) ($item['amount'] ?? ((float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0)));
}
if ($items === [] && $quotation['amount'] !== null && $quotation['amount'] !== '') {
    $total = (float) $quotation['amount'];
}
$deposit = round($total * 0.5, 2);
$money = static fn (float $value): string => 'R ' . number_format($value, 2, '.', ',');
$formatDate = static function (?string $value): string {
    if ($value === null || $value === '') {
        return 'Not set';
    }
    $date = \DateTimeImmutable::createFromFormat('Y-m-d', substr($value, 0, 10));

    return $date instanceof \DateTimeImmutable ? $date->format('j F Y') : $value;
};
$quotationNumber = (string) ($quotation['quotation_number'] ?? ('QUO-' . str_pad((string) $quotation['id'], 3, '0', STR_PAD_LEFT)));
$eventLabel = trim((string) ($quotation['event_label'] ?? ''));
$venue = trim((string) ($quotation['venue'] ?? ''));
$attParts = [];
if (trim((string) ($quotation['company_contact'] ?? '')) !== '') {
    $attParts[] = 'Att: ' . trim((string) $quotation['company_contact']);
}
if (trim((string) ($quotation['company_phone'] ?? '')) !== '') {
    $attParts[] = trim((string) $quotation['company_phone']);
}
if (trim((string) ($quotation['company_email'] ?? '')) !== '') {
    $attParts[] = trim((string) $quotation['company_email']);
}
$attLine = implode(' | ', $attParts);
$termEvent = 'Event details will appear here once set.';
if ($eventLabel !== '' && $venue !== '') {
    $termEvent = 'Event Date: ' . $eventLabel . ' at ' . $venue . '.';
} elseif ($eventLabel !== '') {
    $termEvent = 'Event: ' . $eventLabel . '.';
} elseif ($venue !== '') {
    $termEvent = 'Venue: ' . $venue . '.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($quotationNumber . ' — ' . (string) $quotation['title'], ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body {
            margin: 0;
            padding: 36px 40px;
            font-family: Inter, ui-sans-serif, system-ui, sans-serif;
            color: #18181b;
            background: #fff;
            line-height: 1.45;
        }
        p, h1, h2, h3, ul, ol {
            margin: 0;
        }
        .letterhead {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e4e4e7;
        }
        .logo {
            display: block;
            width: 88px;
            height: auto;
            flex-shrink: 0;
            object-fit: contain;
        }
        .letterhead-copy {
            text-align: right;
        }
        .brand {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .tagline {
            margin-top: 4px;
            color: #52525b;
            font-size: 13px;
            font-style: italic;
        }
        .contact {
            margin-top: 2px;
            color: #52525b;
            font-size: 12px;
        }
        .title {
            margin: 24px 0 8px;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-align: center;
            text-transform: uppercase;
        }
        .meta {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px 24px;
            margin-bottom: 24px;
            font-size: 13px;
            text-align: center;
        }
        .meta span {
            margin-left: 0;
        }
        .section {
            margin-bottom: 24px;
        }
        .section h3 {
            margin-bottom: 8px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .strong {
            font-size: 15px;
            font-weight: 600;
        }
        .section p {
            margin-top: 4px;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        th, td {
            padding: 10px 0;
            border-bottom: 1px solid #e4e4e7;
            text-align: left;
            vertical-align: top;
        }
        th:last-child,
        td:last-child {
            text-align: right;
            white-space: nowrap;
            padding-left: 16px;
        }
        th {
            color: #71717a;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .important {
            margin: 12px 0 0;
            color: #3f3f46;
            font-size: 11px;
            line-height: 1.5;
        }
        .total {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-top: 14px;
            padding: 12px 14px;
            border-top: 1px solid #e4e4e7;
            background: #f4f4f5;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .bank {
            list-style: none;
            padding: 0;
            font-size: 13px;
        }
        .bank li {
            display: grid;
            grid-template-columns: 8rem 1fr;
            gap: 8px;
            margin-top: 4px;
        }
        .bank span {
            color: #71717a;
        }
        .terms {
            padding-left: 1.2rem;
            font-size: 12px;
            color: #3f3f46;
        }
        .terms li + li {
            margin-top: 6px;
        }
        .thanks {
            margin-top: 28px;
            font-size: 13px;
            text-align: center;
        }
        .footer {
            margin-top: 10px;
            color: #71717a;
            font-size: 11px;
            text-align: center;
        }
    </style>
</head>
<body>
    <header class="letterhead">
        <img class="logo" src="/images/logo.jpeg" alt="Kordicraft Creations">
        <div class="letterhead-copy">
            <p class="brand">Kordicraft Creations</p>
            <p class="tagline">Where Vision Meets Excellence. Effortlessly</p>
            <p class="contact">15 Africander Street, Montana, 7490 Cape Town</p>
            <p class="contact">Tel: 082 615 0153 | WhatsApp: 081 311 2922</p>
            <p class="contact">Email: info@kordicraftcreations.co.za</p>
        </div>
    </header>

    <h1 class="title">Quotation</h1>
    <p class="meta">
        <span>Quotation No: <strong><?= htmlspecialchars($quotationNumber, ENT_QUOTES, 'UTF-8') ?></strong></span>
        <span>Date: <strong><?= htmlspecialchars($formatDate(isset($quotation['quotation_date']) ? (string) $quotation['quotation_date'] : null), ENT_QUOTES, 'UTF-8') ?></strong></span>
        <span>Due Date: <strong><?= htmlspecialchars($formatDate(isset($quotation['due_date']) ? (string) $quotation['due_date'] : null), ENT_QUOTES, 'UTF-8') ?></strong></span>
    </p>

    <section class="section">
        <h3>Bill to</h3>
        <p class="strong"><?= htmlspecialchars((string) $quotation['company_name'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php if ($attLine !== ''): ?>
            <p><?= htmlspecialchars($attLine, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if ($eventLabel !== ''): ?>
            <p>Event: <?= htmlspecialchars($eventLabel, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if ($venue !== ''): ?>
            <p>Venue: <?= htmlspecialchars($venue, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </section>

    <section class="section">
        <h3>Breakdown</h3>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount (ZAR)</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($items === []): ?>
                    <tr>
                        <td colspan="2">No line items.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <?php
                        $lineAmount = (float) ($item['amount'] ?? ((float) $item['quantity'] * (float) $item['unit_price']));
                        ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $item['description'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($money($lineAmount), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <p class="important"><strong>Important:</strong> <?= htmlspecialchars(
            trim((string) ($quotation['notes'] ?? '')) !== ''
                ? (string) $quotation['notes']
                : 'Add important terms for this quotation.',
            ENT_QUOTES,
            'UTF-8'
        ) ?></p>
        <div class="total">
            <span>Total due</span>
            <strong><?= htmlspecialchars($money($total), ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
    </section>

    <section class="section">
        <h3>Banking details</h3>
        <ul class="bank">
            <li><span>Bank</span> Standard Bank</li>
            <li><span>Account Name</span> Kordicraft Creations</li>
            <li><span>Account No</span> 101326136</li>
            <li><span>Account Type</span> Current</li>
            <li><span>Branch Code</span> 051001</li>
        </ul>
    </section>

    <section class="section">
        <h3>Terms &amp; conditions</h3>
        <ol class="terms">
            <li>50% deposit (<?= htmlspecialchars($money($deposit), ENT_QUOTES, 'UTF-8') ?>) secures venue and suppliers. Non-refundable if cancelled 30 days or less before the event.</li>
            <li>Full balance must be paid 7 days before the event.</li>
            <li>Bar Tab overrun is subject to actual usage and payable on the day of the event.</li>
            <li><?= htmlspecialchars($termEvent, ENT_QUOTES, 'UTF-8') ?></li>
            <li>No VAT charged.</li>
            <li>Please use Quotation No. <?= htmlspecialchars($quotationNumber, ENT_QUOTES, 'UTF-8') ?> as payment reference.</li>
            <li>For any queries, contact Kordicraft Creations on the numbers listed above.</li>
        </ol>
    </section>

    <p class="thanks">Thank you for choosing Kordicraft Creations. We look forward to making your event exceptional.</p>
    <p class="footer">This is a computer-generated quotation. No signature is required.</p>
</body>
</html>

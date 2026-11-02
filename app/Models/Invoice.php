<?php

namespace App\Models;

use PDO;

class Invoice
{
    public const DEPOSIT_PERCENTAGE = 50.0;

    public function __construct(private PDO $database)
    {
    }

    public function find(int $id): ?array
    {
        $statement = $this->database->prepare(
            'SELECT invoices.id, invoices.company_id, invoices.quotation_id, invoices.invoice_number, invoices.title,'
            . ' invoices.event_label, invoices.venue, invoices.amount, invoices.percentage, invoices.invoice_date,'
            . ' invoices.due_date, invoices.notes, invoices.created_at,'
            . ' companies.name AS company_name, companies.contact AS company_contact,'
            . ' companies.email AS company_email, companies.phone AS company_phone,'
            . ' quotations.quotation_number'
            . ' FROM invoices'
            . ' INNER JOIN companies ON companies.id = invoices.company_id'
            . ' LEFT JOIN quotations ON quotations.id = invoices.quotation_id'
            . ' WHERE invoices.id = :id'
        );
        $statement->execute(['id' => $id]);
        $invoice = $statement->fetch();

        if ($invoice === false) {
            return null;
        }

        $invoice['items'] = $this->itemsFor($id);

        return $invoice;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forCompany(int $companyId): array
    {
        $statement = $this->database->prepare(
            'SELECT id, company_id, invoice_number, title, event_label, venue, amount,'
            . ' invoice_date, due_date, notes, created_at'
            . ' FROM invoices WHERE company_id = :company_id'
            . ' ORDER BY invoice_date DESC, id DESC'
        );
        $statement->execute(['company_id' => $companyId]);

        return $statement->fetchAll();
    }

    public function depositIdForQuotation(int $quotationId): ?int
    {
        $statement = $this->database->prepare(
            'SELECT id FROM invoices'
            . ' WHERE quotation_id = :quotation_id AND percentage = :percentage'
            . ' ORDER BY id ASC LIMIT 1'
        );
        $statement->execute([
            'quotation_id' => $quotationId,
            'percentage' => self::DEPOSIT_PERCENTAGE,
        ]);
        $id = $statement->fetchColumn();

        return $id === false ? null : (int) $id;
    }

    /**
     * @param  array<string, mixed>  $quotation
     */
    public function createDeposit(array $quotation): int
    {
        $total = $this->quotationTotal($quotation);
        $deposit = round($total * (self::DEPOSIT_PERCENTAGE / 100), 2);
        $quotationId = (int) $quotation['id'];
        $quotationNumber = (string) ($quotation['quotation_number'] ?? ('QUO-' . str_pad((string) $quotationId, 3, '0', STR_PAD_LEFT)));
        $percentageLabel = rtrim(rtrim(number_format(self::DEPOSIT_PERCENTAGE, 2, '.', ''), '0'), '.');
        $dueDate = substr((string) ($quotation['due_date'] ?? ''), 0, 10);
        if ($dueDate === '') {
            $dueDate = date('Y-m-d', strtotime('+7 days'));
        }

        $title = 'Deposit — ' . (string) $quotation['title'];
        if (strlen($title) > 180) {
            $title = substr($title, 0, 180);
        }

        $eventLabel = trim((string) ($quotation['event_label'] ?? ''));
        $venue = trim((string) ($quotation['venue'] ?? ''));
        $notes = trim((string) ($quotation['notes'] ?? ''));

        return $this->create(
            (int) $quotation['company_id'],
            $this->nextInvoiceNumber(),
            $title,
            $eventLabel === '' ? null : $eventLabel,
            $venue === '' ? null : $venue,
            $deposit,
            date('Y-m-d'),
            $dueDate,
            $notes === '' ? null : $notes,
            [[
                'description' => $percentageLabel . '% deposit for ' . $quotationNumber,
                'quantity' => 1.0,
                'unit_price' => $deposit,
            ]],
            $quotationId,
            self::DEPOSIT_PERCENTAGE,
        );
    }

    public function nextInvoiceNumber(): string
    {
        $next = ((int) $this->database->query('SELECT COUNT(*) FROM invoices')->fetchColumn()) + 1;

        return 'INV-' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    /**
     * @return list<array{id: int, description: string, quantity: float, unit_price: float, amount: float, sort_order: int}>
     */
    public function itemsFor(int $invoiceId): array
    {
        $statement = $this->database->prepare(
            'SELECT id, description, quantity, unit_price, sort_order'
            . ' FROM invoice_items WHERE invoice_id = :invoice_id'
            . ' ORDER BY sort_order ASC, id ASC'
        );
        $statement->execute(['invoice_id' => $invoiceId]);

        $items = [];
        foreach ($statement->fetchAll() as $row) {
            $quantity = (float) $row['quantity'];
            $unitPrice = (float) $row['unit_price'];
            $items[] = [
                'id' => (int) $row['id'],
                'description' => (string) $row['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'amount' => round($quantity * $unitPrice, 2),
                'sort_order' => (int) $row['sort_order'],
            ];
        }

        return $items;
    }

    /**
     * @param  list<array{description: string, quantity: float, unit_price: float}>  $items
     */
    public function create(
        int $companyId,
        string $invoiceNumber,
        string $title,
        ?string $eventLabel,
        ?string $venue,
        ?float $amount,
        ?string $invoiceDate,
        ?string $dueDate,
        ?string $notes,
        array $items = [],
        ?int $quotationId = null,
        ?float $percentage = null,
    ): int {
        $this->database->beginTransaction();

        try {
            $statement = $this->database->prepare(
                'INSERT INTO invoices ('
                . ' company_id, quotation_id, invoice_number, title, event_label, venue, amount, percentage, invoice_date, due_date, notes'
                . ') VALUES ('
                . ' :company_id, :quotation_id, :invoice_number, :title, :event_label, :venue, :amount, :percentage, :invoice_date, :due_date, :notes'
                . ')'
            );
            $statement->execute([
                'company_id' => $companyId,
                'quotation_id' => $quotationId,
                'invoice_number' => $invoiceNumber,
                'title' => $title,
                'event_label' => $eventLabel,
                'venue' => $venue,
                'amount' => $amount,
                'percentage' => $percentage,
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'notes' => $notes,
            ]);

            $invoiceId = (int) $this->database->lastInsertId();
            $this->insertItems($invoiceId, $items);
            $this->database->commit();

            return $invoiceId;
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }
    }

    /**
     * @param  list<array{description: string, quantity: float, unit_price: float}>  $items
     */
    private function insertItems(int $invoiceId, array $items): void
    {
        if ($items === []) {
            return;
        }

        $statement = $this->database->prepare(
            'INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, sort_order)'
            . ' VALUES (:invoice_id, :description, :quantity, :unit_price, :sort_order)'
        );

        foreach ($items as $index => $item) {
            $statement->execute([
                'invoice_id' => $invoiceId,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $quotation
     */
    private function quotationTotal(array $quotation): float
    {
        $items = is_array($quotation['items'] ?? null) ? $quotation['items'] : [];
        if ($items !== []) {
            $total = 0.0;
            foreach ($items as $item) {
                $total += (float) ($item['amount'] ?? ((float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0)));
            }

            return round($total, 2);
        }

        if (isset($quotation['amount']) && $quotation['amount'] !== null && $quotation['amount'] !== '') {
            return round((float) $quotation['amount'], 2);
        }

        return 0.0;
    }
}

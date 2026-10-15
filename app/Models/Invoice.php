<?php

namespace App\Models;

use PDO;

class Invoice
{
    public function __construct(private PDO $database)
    {
    }

    public function find(int $id): ?array
    {
        $statement = $this->database->prepare(
            'SELECT invoices.id, invoices.company_id, invoices.invoice_number, invoices.title,'
            . ' invoices.event_label, invoices.venue, invoices.amount, invoices.invoice_date,'
            . ' invoices.due_date, invoices.notes, invoices.created_at,'
            . ' companies.name AS company_name, companies.contact AS company_contact,'
            . ' companies.email AS company_email, companies.phone AS company_phone'
            . ' FROM invoices'
            . ' INNER JOIN companies ON companies.id = invoices.company_id'
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
    ): int {
        $this->database->beginTransaction();

        try {
            $statement = $this->database->prepare(
                'INSERT INTO invoices ('
                . ' company_id, invoice_number, title, event_label, venue, amount, invoice_date, due_date, notes'
                . ') VALUES ('
                . ' :company_id, :invoice_number, :title, :event_label, :venue, :amount, :invoice_date, :due_date, :notes'
                . ')'
            );
            $statement->execute([
                'company_id' => $companyId,
                'invoice_number' => $invoiceNumber,
                'title' => $title,
                'event_label' => $eventLabel,
                'venue' => $venue,
                'amount' => $amount,
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
}

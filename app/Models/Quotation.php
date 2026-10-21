<?php

namespace App\Models;

use PDO;

class Quotation
{
    public function __construct(private PDO $database)
    {
    }

    public function find(int $id): ?array
    {
        $statement = $this->database->prepare(
            'SELECT quotations.id, quotations.company_id, quotations.quotation_number, quotations.title,'
            . ' quotations.event_label, quotations.venue, quotations.amount, quotations.quotation_date,'
            . ' quotations.due_date, quotations.notes, quotations.send_status, quotations.created_at,'
            . ' companies.name AS company_name, companies.contact AS company_contact,'
            . ' companies.email AS company_email, companies.phone AS company_phone'
            . ' FROM quotations'
            . ' INNER JOIN companies ON companies.id = quotations.company_id'
            . ' WHERE quotations.id = :id'
        );
        $statement->execute(['id' => $id]);
        $quotation = $statement->fetch();

        if ($quotation === false) {
            return null;
        }

        $quotation['items'] = $this->itemsFor($id);

        return $quotation;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forCompany(int $companyId): array
    {
        $statement = $this->database->prepare(
            'SELECT id, company_id, quotation_number, title, event_label, venue, amount,'
            . ' quotation_date, due_date, notes, send_status, created_at'
            . ' FROM quotations WHERE company_id = :company_id'
            . ' ORDER BY quotation_date DESC, id DESC'
        );
        $statement->execute(['company_id' => $companyId]);

        return $statement->fetchAll();
    }

    public function nextQuotationNumber(): string
    {
        $next = ((int) $this->database->query('SELECT COUNT(*) FROM quotations')->fetchColumn()) + 1;

        return 'QUO-' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    /**
     * @return list<array{id: int, description: string, quantity: float, unit_price: float, amount: float, sort_order: int}>
     */
    public function itemsFor(int $quotationId): array
    {
        $statement = $this->database->prepare(
            'SELECT id, description, quantity, unit_price, sort_order'
            . ' FROM quotation_items WHERE quotation_id = :quotation_id'
            . ' ORDER BY sort_order ASC, id ASC'
        );
        $statement->execute(['quotation_id' => $quotationId]);

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
        string $quotationNumber,
        string $title,
        ?string $eventLabel,
        ?string $venue,
        ?float $amount,
        ?string $quotationDate,
        ?string $dueDate,
        ?string $notes,
        array $items = [],
    ): int {
        $this->database->beginTransaction();

        try {
            $statement = $this->database->prepare(
                'INSERT INTO quotations ('
                . ' company_id, quotation_number, title, event_label, venue, amount, quotation_date, due_date, notes'
                . ') VALUES ('
                . ' :company_id, :quotation_number, :title, :event_label, :venue, :amount, :quotation_date, :due_date, :notes'
                . ')'
            );
            $statement->execute([
                'company_id' => $companyId,
                'quotation_number' => $quotationNumber,
                'title' => $title,
                'event_label' => $eventLabel,
                'venue' => $venue,
                'amount' => $amount,
                'quotation_date' => $quotationDate,
                'due_date' => $dueDate,
                'notes' => $notes,
            ]);

            $quotationId = (int) $this->database->lastInsertId();
            $this->insertItems($quotationId, $items);
            $this->database->commit();

            return $quotationId;
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }
    }

    /**
     * @param  list<array{description: string, quantity: float, unit_price: float}>  $items
     */
    public function update(
        int $id,
        int $companyId,
        string $quotationNumber,
        string $title,
        ?string $eventLabel,
        ?string $venue,
        ?float $amount,
        ?string $quotationDate,
        ?string $dueDate,
        ?string $notes,
        array $items = [],
    ): void {
        $this->database->beginTransaction();

        try {
            $statement = $this->database->prepare(
                'UPDATE quotations SET'
                . ' company_id = :company_id, quotation_number = :quotation_number, title = :title,'
                . ' event_label = :event_label, venue = :venue, amount = :amount,'
                . ' quotation_date = :quotation_date, due_date = :due_date, notes = :notes'
                . ' WHERE id = :id'
            );
            $statement->execute([
                'id' => $id,
                'company_id' => $companyId,
                'quotation_number' => $quotationNumber,
                'title' => $title,
                'event_label' => $eventLabel,
                'venue' => $venue,
                'amount' => $amount,
                'quotation_date' => $quotationDate,
                'due_date' => $dueDate,
                'notes' => $notes,
            ]);

            $delete = $this->database->prepare('DELETE FROM quotation_items WHERE quotation_id = :quotation_id');
            $delete->execute(['quotation_id' => $id]);
            $this->insertItems($id, $items);
            $this->database->commit();
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }
    }

    public function recordSendStatus(int $id, string $status): void
    {
        if ($status !== 'sent' && $status !== 'failed') {
            return;
        }

        $statement = $this->database->prepare(
            'UPDATE quotations SET send_status = :send_status WHERE id = :id'
        );
        $statement->execute([
            'send_status' => $status,
            'id' => $id,
        ]);
    }

    /**
     * @param  list<array{description: string, quantity: float, unit_price: float}>  $items
     */
    private function insertItems(int $quotationId, array $items): void
    {
        if ($items === []) {
            return;
        }

        $statement = $this->database->prepare(
            'INSERT INTO quotation_items (quotation_id, description, quantity, unit_price, sort_order)'
            . ' VALUES (:quotation_id, :description, :quantity, :unit_price, :sort_order)'
        );

        foreach ($items as $index => $item) {
            $statement->execute([
                'quotation_id' => $quotationId,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'sort_order' => $index,
            ]);
        }
    }
}

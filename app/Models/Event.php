<?php

namespace App\Models;

use PDO;

class Event
{
    public function __construct(private PDO $database)
    {
    }

    public function count(): int
    {
        return (int) $this->database->query('SELECT COUNT(*) FROM events')->fetchColumn();
    }

    public function statusCounts(): array
    {
        $statement = $this->database->query(
            'SELECT status, COUNT(*) AS total FROM events GROUP BY status ORDER BY status'
        );
        $counts = [];

        foreach ($statement->fetchAll() as $row) {
            $status = $row['status'] !== '' ? (string) $row['status'] : 'Unspecified';
            $counts[$status] = (int) $row['total'];
        }

        return $counts;
    }

    public function page(int $page, int $perPage): array
    {
        $perPage = max(1, $perPage);
        $offset = (max(1, $page) - 1) * $perPage;
        $statement = $this->database->query(
            'SELECT id, title, company, location, event_date, status, finance, attendants, revenue, created_at'
            . ' FROM events ORDER BY created_at DESC, id DESC LIMIT '
            . $perPage
            . ' OFFSET '
            . $offset
        );

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->database->prepare(
            'SELECT id, title, company, location, event_date, status, finance, attendants, revenue FROM events WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
        $event = $statement->fetch();

        return $event === false ? null : $event;
    }

    public function create(
        string $title,
        ?string $company,
        ?string $location,
        ?string $eventDate,
        string $status,
        string $finance,
        int $attendants,
        ?string $revenue,
    ): void {
        $statement = $this->database->prepare(
            'INSERT INTO events (title, company, location, event_date, status, finance, attendants, revenue)'
            . ' VALUES (:title, :company, :location, :event_date, :status, :finance, :attendants, :revenue)'
        );
        $statement->execute([
            'title' => $title,
            'company' => $company,
            'location' => $location,
            'event_date' => $eventDate,
            'status' => $status,
            'finance' => $finance,
            'attendants' => $attendants,
            'revenue' => $revenue,
        ]);
    }

    public function update(
        int $id,
        string $title,
        ?string $company,
        ?string $location,
        ?string $eventDate,
        string $status,
        string $finance,
        int $attendants,
        ?string $revenue,
    ): void {
        $statement = $this->database->prepare(
            'UPDATE events SET title = :title, company = :company, location = :location, event_date = :event_date,'
            . ' status = :status, finance = :finance, attendants = :attendants, revenue = :revenue WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'title' => $title,
            'company' => $company,
            'location' => $location,
            'event_date' => $eventDate,
            'status' => $status,
            'finance' => $finance,
            'attendants' => $attendants,
            'revenue' => $revenue,
        ]);
    }

    public function updateStatus(int $id, string $status): void
    {
        $statement = $this->database->prepare('UPDATE events SET status = :status WHERE id = :id');
        $statement->execute([
            'id' => $id,
            'status' => $status,
        ]);
    }

    public function delete(int $id): void
    {
        $statement = $this->database->prepare('DELETE FROM events WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
}

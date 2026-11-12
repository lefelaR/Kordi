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

    /**
     * @return list<array{id: int, title: string, client: string, location: string, day: int, date: string, status: string, attendants: int}>
     */
    public function scheduledInMonth(int $year, int $month): array
    {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = (new \DateTimeImmutable($start))->modify('first day of next month')->format('Y-m-d');
        $statement = $this->database->prepare(
            'SELECT id, title, company, location, event_date, status, attendants FROM events'
            . ' WHERE event_date IS NOT NULL AND event_date >= :start AND event_date < :end'
            . ' ORDER BY event_date, id'
        );
        $statement->execute([
            'start' => $start,
            'end' => $end,
        ]);

        $events = [];
        foreach ($statement->fetchAll() as $row) {
            $date = new \DateTimeImmutable((string) $row['event_date']);
            $events[] = [
                'id' => (int) $row['id'],
                'title' => (string) $row['title'],
                'client' => trim((string) ($row['company'] ?? '')),
                'location' => trim((string) ($row['location'] ?? '')),
                'day' => (int) $date->format('j'),
                'date' => $date->format('Y-m-d'),
                'status' => (string) ($row['status'] !== '' ? $row['status'] : 'planned'),
                'attendants' => (int) ($row['attendants'] ?? 0),
            ];
        }

        return $events;
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

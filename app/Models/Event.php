<?php

namespace App\Models;

use PDO;

class Event
{
    public function __construct(private PDO $database)
    {
    }

    public function all(): array
    {
        $statement = $this->database->query(
            'SELECT id, title, event_date, status, created_at FROM events ORDER BY created_at DESC, id DESC'
        );

        return $statement->fetchAll();
    }

    public function create(string $title, ?string $eventDate, string $status): void
    {
        $statement = $this->database->prepare(
            'INSERT INTO events (title, event_date, status) VALUES (:title, :event_date, :status)'
        );
        $statement->execute([
            'title' => $title,
            'event_date' => $eventDate,
            'status' => $status,
        ]);
    }
}

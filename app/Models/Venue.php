<?php

namespace App\Models;

use PDO;

class Venue
{
    public function __construct(private PDO $database)
    {
    }

    public function count(?string $search = null): int
    {
        if ($search === null || $search === '') {
            return (int) $this->database->query('SELECT COUNT(*) FROM venues')->fetchColumn();
        }

        $pattern = $this->searchPattern($search);
        $statement = $this->database->prepare(
            'SELECT COUNT(*) FROM venues WHERE name LIKE :search_name OR location LIKE :search_location'
        );
        $statement->execute([
            'search_name' => $pattern,
            'search_location' => $pattern,
        ]);

        return (int) $statement->fetchColumn();
    }

    public function page(int $page, int $perPage, ?string $search = null): array
    {
        $perPage = max(1, $perPage);
        $offset = (max(1, $page) - 1) * $perPage;
        $limitSql = ' LIMIT ' . $perPage . ' OFFSET ' . $offset;

        if ($search === null || $search === '') {
            $statement = $this->database->query(
                'SELECT id, name, location, price, capacity, created_at FROM venues ORDER BY created_at DESC, id DESC'
                . $limitSql
            );

            return $statement->fetchAll();
        }

        $pattern = $this->searchPattern($search);
        $statement = $this->database->prepare(
            'SELECT id, name, location, price, capacity, created_at FROM venues'
            . ' WHERE name LIKE :search_name OR location LIKE :search_location'
            . ' ORDER BY created_at DESC, id DESC'
            . $limitSql
        );
        $statement->execute([
            'search_name' => $pattern,
            'search_location' => $pattern,
        ]);

        return $statement->fetchAll();
    }

    private function searchPattern(string $search): string
    {
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);

        return '%' . $escaped . '%';
    }

    public function find(int $id): ?array
    {
        $statement = $this->database->prepare(
            'SELECT id, name, location, price, capacity FROM venues WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
        $venue = $statement->fetch();

        return $venue === false ? null : $venue;
    }

    public function create(string $name, ?string $location, ?string $price, ?int $capacity): void
    {
        $statement = $this->database->prepare(
            'INSERT INTO venues (name, location, price, capacity) VALUES (:name, :location, :price, :capacity)'
        );
        $statement->execute([
            'name' => $name,
            'location' => $location,
            'price' => $price,
            'capacity' => $capacity,
        ]);
    }

    public function update(int $id, string $name, ?string $location, ?string $price, ?int $capacity): void
    {
        $statement = $this->database->prepare(
            'UPDATE venues SET name = :name, location = :location, price = :price, capacity = :capacity WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'name' => $name,
            'location' => $location,
            'price' => $price,
            'capacity' => $capacity,
        ]);
    }

    public function delete(int $id): void
    {
        $statement = $this->database->prepare('DELETE FROM venues WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
}

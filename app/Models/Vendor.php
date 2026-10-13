<?php

namespace App\Models;

use PDO;

class Vendor
{
    public function __construct(private PDO $database)
    {
    }

    public function count(): int
    {
        return (int) $this->database->query('SELECT COUNT(*) FROM vendors')->fetchColumn();
    }

    public function page(int $page, int $perPage): array
    {
        $perPage = max(1, $perPage);
        $offset = (max(1, $page) - 1) * $perPage;
        $statement = $this->database->query(
            'SELECT id, name, contact, email, phone, created_at FROM vendors ORDER BY created_at DESC, id DESC LIMIT '
            . $perPage
            . ' OFFSET '
            . $offset
        );

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->database->prepare(
            'SELECT id, name, contact, email, phone FROM vendors WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
        $vendor = $statement->fetch();

        return $vendor === false ? null : $vendor;
    }

    public function create(string $name, ?string $contact, ?string $email, ?string $phone): void
    {
        $statement = $this->database->prepare(
            'INSERT INTO vendors (name, contact, email, phone) VALUES (:name, :contact, :email, :phone)'
        );
        $statement->execute([
            'name' => $name,
            'contact' => $contact,
            'email' => $email,
            'phone' => $phone,
        ]);
    }

    public function update(int $id, string $name, ?string $contact, ?string $email, ?string $phone): void
    {
        $statement = $this->database->prepare(
            'UPDATE vendors SET name = :name, contact = :contact, email = :email, phone = :phone WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'name' => $name,
            'contact' => $contact,
            'email' => $email,
            'phone' => $phone,
        ]);
    }

    public function delete(int $id): void
    {
        $statement = $this->database->prepare('DELETE FROM vendors WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
}

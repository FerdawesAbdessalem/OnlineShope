<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class UserRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, email, role, password_hash FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE LOWER(email) = LOWER(?)');
        $stmt->execute([trim($email)]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name, string $email, string $password): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([trim($name), strtolower(trim($email)), password_hash($password, PASSWORD_DEFAULT), 'customer']);
        return (int) $this->pdo->lastInsertId();
    }

    public function countCustomers(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    }
}

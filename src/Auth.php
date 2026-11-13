<?php

declare(strict_types=1);

namespace App;

use App\Repository\UserRepository;

/**
 * Session-based authentication with hashed passwords
 * (the old login compared against a hard-coded password).
 */
final class Auth
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    public function attempt(string $email, string $password): bool
    {
        $user = $this->users->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        session_regenerate_id(true); // prevents session fixation
        $_SESSION['user_id'] = (int) $user['id'];
        return true;
    }

    public function login(int $userId): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
    }

    public function logout(): void
    {
        unset($_SESSION['user_id']);
        session_regenerate_id(true);
    }

    public function user(): ?array
    {
        static $cache = [];
        $id = $_SESSION['user_id'] ?? null;
        if ($id === null) {
            return null;
        }
        return $cache[$id] ??= $this->users->find((int) $id);
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function isAdmin(): bool
    {
        return ($this->user()['role'] ?? null) === 'admin';
    }
}

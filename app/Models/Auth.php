<?php

namespace App\Models;

class Auth
{
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function attempt(string $email, string $password): bool
    {
        $configuredEmail = $_ENV['APP_LOGIN_EMAIL'] ?? getenv('APP_LOGIN_EMAIL') ?: 'admin@kordicraftcreations.co.za';
        $configuredPassword = $_ENV['APP_LOGIN_PASSWORD'] ?? getenv('APP_LOGIN_PASSWORD') ?: 'kordicraft-demo';

        if (!hash_equals($configuredEmail, $email) || !hash_equals($configuredPassword, $password)) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['authenticated'] = true;
        $_SESSION['user_email'] = $email;

        return true;
    }

    public function check(): bool
    {
        return !empty($_SESSION['authenticated']);
    }

    public function email(): string
    {
        return $_SESSION['user_email'] ?? '';
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}
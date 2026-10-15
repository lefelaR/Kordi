<?php

namespace App\Models;

use RuntimeException;

class Auth
{
    public function __construct(private ?ApiClient $api = null)
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

        if ($this->api === null) {
            throw new RuntimeException('API client is required for login.');
        }

        $response = $this->api->post('/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $token = $response['token'] ?? null;
        if (!is_string($token) || $token === '') {
            throw new RuntimeException('API login did not return a token.');
        }

        session_regenerate_id(true);
        $_SESSION['authenticated'] = true;
        $_SESSION['user_email'] = $email;
        $_SESSION['api_token'] = $token;

        return true;
    }

    public function check(): bool
    {
        return !empty($_SESSION['authenticated']) && $this->apiToken() !== '';
    }

    public function email(): string
    {
        return $_SESSION['user_email'] ?? '';
    }

    public function apiToken(): string
    {
        return is_string($_SESSION['api_token'] ?? null) ? $_SESSION['api_token'] : '';
    }

    public function logout(): void
    {
        $token = $this->apiToken();
        if ($token !== '' && $this->api !== null) {
            try {
                $this->api->withToken($token)->post('/logout');
            } catch (RuntimeException) {
                // Session is cleared regardless of API revoke success.
            }
        }

        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}

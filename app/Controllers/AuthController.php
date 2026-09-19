<?php

namespace App\Controllers;

use App\Models\Auth;

class AuthController
{
    public function __construct(private Auth $auth)
    {
    }

    public function login(): void
    {
        if ($this->auth->check()) {
            header('Location: /dashboard');
            exit;
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($this->auth->attempt($email, $password)) {
                header('Location: /dashboard');
                exit;
            }

            $error = 'The email or password is incorrect.';
        }

        require __DIR__ . '/../Views/login.php';
    }

    public function logout(): void
    {
        $this->auth->logout();
        header('Location: /login');
        exit;
    }
}
<?php

namespace App\Controllers;

use App\Models\Auth;

class DashboardController
{
    public function __construct(private Auth $auth)
    {
    }

    public function index(): void
    {
        if (!$this->auth->check()) {
            header('Location: /login');
            exit;
        }

        $userEmail = $this->auth->email();
        require __DIR__ . '/../Views/dashboard.php';
    }
}
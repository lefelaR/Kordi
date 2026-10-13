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
        $pageTitle = 'Dashboard | Kordicraft Creations';

        ob_start();
        require __DIR__ . '/../Views/dashboard.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/dashboard.php';
    }
}
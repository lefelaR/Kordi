<?php

namespace App\Controllers;

use App\Models\Auth;

class ContractController
{
    public function __construct(private Auth $auth)
    {
    }

    public function add(): void
    {
        if (!$this->auth->check()) {
            header('Location: /login');
            exit;
        }

        $pageTitle = 'New contract | Kordicraft Creations';
        $currentSection = 'documents';
        $currentDocument = 'contract';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'href' => '/dashboard'],
            ['label' => 'Documents', 'href' => '/dashboard/documents'],
            ['label' => 'Contract', 'href' => '/dashboard/documents/contract'],
            ['label' => 'New contract'],
        ];

        ob_start();
        require __DIR__ . '/../Views/dashboard/documents/contract-form.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/dashboard.php';
    }
}

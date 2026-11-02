<?php

namespace App\Controllers;

use App\Models\Auth;

class ReceiptController
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

        $pageTitle = 'New receipt | Kordicraft Creations';
        $currentSection = 'documents';
        $currentDocument = 'receipts';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'href' => '/dashboard'],
            ['label' => 'Documents', 'href' => '/dashboard/documents'],
            ['label' => 'Receipts', 'href' => '/dashboard/documents/receipts'],
            ['label' => 'New receipt'],
        ];

        ob_start();
        require __DIR__ . '/../Views/dashboard/documents/receipt-form.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/dashboard.php';
    }
}

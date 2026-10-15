<?php

namespace App\Controllers;

use App\Models\Auth;
use App\Models\Company;
use App\Models\Invoice;

class InvoiceController
{
    public function __construct(
        private Auth $auth,
        private Invoice $invoices,
        private Company $companies,
    ) {
    }

    public function add(): void
    {
        if (!$this->auth->check()) {
            header('Location: /login');
            exit;
        }

        $companyOptions = $this->companies->all();
        $error = '';
        $companyId = 0;
        $invoiceNumber = $this->invoices->nextInvoiceNumber();
        $title = '';
        $eventLabel = '';
        $venue = '';
        $invoiceDate = date('Y-m-d');
        $dueDate = date('Y-m-d', strtotime('+7 days'));
        $notes = 'Venue Hire includes the 3-course buffet meal. Bar Tab overrun is payable on the day based on actual usage. Casino Theme Hire includes themed décor and props.';
        $items = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->readInvoiceInput($companyOptions);
            $error = $input['error'];
            $companyId = $input['companyId'];
            $invoiceNumber = $input['invoiceNumber'];
            $title = $input['title'];
            $eventLabel = $input['eventLabel'];
            $venue = $input['venue'];
            $invoiceDate = $input['invoiceDate'];
            $dueDate = $input['dueDate'];
            $notes = $input['notes'];
            $items = $input['items'];

            if ($error === '') {
                $this->invoices->create(
                    $input['companyId'],
                    $input['invoiceNumber'],
                    $input['title'],
                    $input['storedEventLabel'],
                    $input['storedVenue'],
                    $input['storedAmount'],
                    $input['storedInvoiceDate'],
                    $input['storedDueDate'],
                    $input['storedNotes'],
                    $input['items'],
                );
                header('Location: /dashboard/documents/invoices');
                exit;
            }
        } elseif ($companyOptions !== []) {
            $companyId = (int) $companyOptions[0]['id'];
        }

        $selectedCompany = [
            'name' => 'Company',
            'contact' => '',
            'email' => '',
            'phone' => '',
        ];
        foreach ($companyOptions as $company) {
            if ((int) $company['id'] === $companyId) {
                $selectedCompany = [
                    'name' => (string) $company['name'],
                    'contact' => (string) ($company['contact'] ?? ''),
                    'email' => (string) ($company['email'] ?? ''),
                    'phone' => (string) ($company['phone'] ?? ''),
                ];
                break;
            }
        }

        $pageTitle = 'New invoice | Kordicraft Creations';
        $currentSection = 'documents';
        $currentDocument = 'invoices';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'href' => '/dashboard'],
            ['label' => 'Documents', 'href' => '/dashboard/documents'],
            ['label' => 'Invoices', 'href' => '/dashboard/documents/invoices'],
            ['label' => 'New invoice'],
        ];
        $formAction = '/invoices/add';
        $heading = 'New invoice';
        $summary = 'Add details and line items on the left. The invoice updates on the right.';
        $itemsJson = json_encode($items, JSON_THROW_ON_ERROR);
        $extraScripts = ['/assets/js/invoice-builder.js'];

        ob_start();
        require __DIR__ . '/../Views/dashboard/documents/invoice-form.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/dashboard.php';
    }

    public function preview(int $id): void
    {
        if (!$this->auth->check()) {
            header('Location: /login');
            exit;
        }

        $invoice = $this->invoices->find($id);
        if ($invoice === null) {
            http_response_code(404);
            echo 'Invoice not found.';
            return;
        }

        require __DIR__ . '/../Views/dashboard/documents/invoice-preview.php';
    }

    /**
     * @param  list<array<string, mixed>>  $companyOptions
     * @return array<string, mixed>
     */
    private function readInvoiceInput(array $companyOptions): array
    {
        $companyId = filter_input(INPUT_POST, 'company_id', FILTER_VALIDATE_INT);
        $invoiceNumber = trim($_POST['invoice_number'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $eventLabel = trim($_POST['event_label'] ?? '');
        $venue = trim($_POST['venue'] ?? '');
        $invoiceDate = trim($_POST['invoice_date'] ?? '');
        $dueDate = trim($_POST['due_date'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $items = $this->parseItems($_POST['items'] ?? '[]');
        $error = '';
        $storedInvoiceDate = null;
        $storedDueDate = null;

        $validCompanyIds = [];
        foreach ($companyOptions as $company) {
            $validCompanyIds[(int) $company['id']] = true;
        }

        if (!is_int($companyId) || $companyId < 1 || !isset($validCompanyIds[$companyId])) {
            $error = 'Choose a company.';
            $companyId = is_int($companyId) ? $companyId : 0;
        } elseif ($invoiceNumber === '' || strlen($invoiceNumber) > 40) {
            $error = 'Enter an invoice number of 40 characters or fewer.';
        } elseif ($title === '' || strlen($title) > 180) {
            $error = 'Enter an invoice title of 180 characters or fewer.';
        } elseif (strlen($eventLabel) > 255) {
            $error = 'Enter an event of 255 characters or fewer.';
        } elseif (strlen($venue) > 255) {
            $error = 'Enter a venue of 255 characters or fewer.';
        } elseif ($items === []) {
            $error = 'Add at least one line item.';
        }

        if ($error === '' && $invoiceDate !== '') {
            $date = \DateTimeImmutable::createFromFormat('Y-m-d', $invoiceDate);
            if ($date === false || $date->format('Y-m-d') !== $invoiceDate) {
                $error = 'Enter a valid invoice date.';
            } else {
                $storedInvoiceDate = $invoiceDate;
            }
        }

        if ($error === '' && $dueDate !== '') {
            $date = \DateTimeImmutable::createFromFormat('Y-m-d', $dueDate);
            if ($date === false || $date->format('Y-m-d') !== $dueDate) {
                $error = 'Enter a valid due date.';
            } else {
                $storedDueDate = $dueDate;
            }
        }

        if ($error === '' && strlen($notes) > 5000) {
            $error = 'Important notes must be 5000 characters or fewer.';
        }

        $storedAmount = null;
        if ($items !== []) {
            $storedAmount = 0.0;
            foreach ($items as $item) {
                $storedAmount += $item['quantity'] * $item['unit_price'];
            }
            $storedAmount = round($storedAmount, 2);
        }

        return [
            'error' => $error,
            'companyId' => is_int($companyId) ? $companyId : 0,
            'invoiceNumber' => $invoiceNumber,
            'title' => $title,
            'eventLabel' => $eventLabel,
            'venue' => $venue,
            'invoiceDate' => $invoiceDate,
            'dueDate' => $dueDate,
            'notes' => $notes,
            'items' => $items,
            'storedAmount' => $storedAmount,
            'storedEventLabel' => $eventLabel === '' ? null : $eventLabel,
            'storedVenue' => $venue === '' ? null : $venue,
            'storedInvoiceDate' => $storedInvoiceDate,
            'storedDueDate' => $storedDueDate,
            'storedNotes' => $notes === '' ? null : $notes,
        ];
    }

    /**
     * @return list<array{description: string, quantity: float, unit_price: float}>
     */
    private function parseItems(mixed $raw): array
    {
        if (!is_string($raw) || $raw === '') {
            return [];
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        if (!is_array($decoded)) {
            return [];
        }

        $items = [];
        foreach ($decoded as $row) {
            if (!is_array($row)) {
                continue;
            }

            $description = trim((string) ($row['description'] ?? ''));
            $amount = $row['amount'] ?? $row['unit_price'] ?? null;

            if ($description === '' || strlen($description) > 255) {
                continue;
            }
            if (!is_numeric($amount) || (float) $amount < 0) {
                continue;
            }

            $items[] = [
                'description' => $description,
                'quantity' => 1.0,
                'unit_price' => round((float) $amount, 2),
            ];
        }

        return $items;
    }
}

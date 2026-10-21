<?php

namespace App\Controllers;

use App\Models\Auth;
use App\Models\Company;
use App\Models\Quotation;

class QuotationController
{
    public function __construct(
        private Auth $auth,
        private Quotation $quotations,
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
        $quotationNumber = $this->quotations->nextQuotationNumber();
        $title = '';
        $eventLabel = '';
        $venue = '';
        $quotationDate = date('Y-m-d');
        $dueDate = date('Y-m-d', strtotime('+7 days'));
        $notes = 'Venue Hire includes the 3-course buffet meal. Bar Tab overrun is payable on the day based on actual usage. Casino Theme Hire includes themed décor and props.';
        $items = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->readQuotationInput($companyOptions);
            $error = $input['error'];
            $companyId = $input['companyId'];
            $quotationNumber = $input['quotationNumber'];
            $title = $input['title'];
            $eventLabel = $input['eventLabel'];
            $venue = $input['venue'];
            $quotationDate = $input['quotationDate'];
            $dueDate = $input['dueDate'];
            $notes = $input['notes'];
            $items = $input['items'];

            if ($error === '') {
                $this->quotations->create(
                    $input['companyId'],
                    $input['quotationNumber'],
                    $input['title'],
                    $input['storedEventLabel'],
                    $input['storedVenue'],
                    $input['storedAmount'],
                    $input['storedQuotationDate'],
                    $input['storedDueDate'],
                    $input['storedNotes'],
                    $input['items'],
                );
                header('Location: /dashboard/documents/quotations');
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

        $pageTitle = 'New quotation | Kordicraft Creations';
        $currentSection = 'documents';
        $currentDocument = 'quotations';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'href' => '/dashboard'],
            ['label' => 'Documents', 'href' => '/dashboard/documents'],
            ['label' => 'Quotations', 'href' => '/dashboard/documents/quotations'],
            ['label' => 'New quotation'],
        ];
        $formAction = '/quotations/add';
        $heading = 'New quotation';
        $summary = 'Add details and line items on the left. The quotation updates on the right.';
        $itemsJson = json_encode($items, JSON_THROW_ON_ERROR);
        $extraScripts = ['/assets/js/quotation-builder.js'];

        ob_start();
        require __DIR__ . '/../Views/dashboard/documents/quotation-form.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/dashboard.php';
    }

    public function preview(int $id): void
    {
        if (!$this->auth->check()) {
            header('Location: /login');
            exit;
        }

        $quotation = $this->quotations->find($id);
        if ($quotation === null) {
            http_response_code(404);
            echo 'Quotation not found.';
            return;
        }

        require __DIR__ . '/../Views/dashboard/documents/quotation-preview.php';
    }

    /**
     * @param  list<array<string, mixed>>  $companyOptions
     * @return array<string, mixed>
     */
    private function readQuotationInput(array $companyOptions): array
    {
        $companyId = filter_input(INPUT_POST, 'company_id', FILTER_VALIDATE_INT);
        $quotationNumber = trim($_POST['quotation_number'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $eventLabel = trim($_POST['event_label'] ?? '');
        $venue = trim($_POST['venue'] ?? '');
        $quotationDate = trim($_POST['quotation_date'] ?? '');
        $dueDate = trim($_POST['due_date'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $items = $this->parseItems($_POST['items'] ?? '[]');
        $error = '';
        $storedQuotationDate = null;
        $storedDueDate = null;

        $validCompanyIds = [];
        foreach ($companyOptions as $company) {
            $validCompanyIds[(int) $company['id']] = true;
        }

        if (!is_int($companyId) || $companyId < 1 || !isset($validCompanyIds[$companyId])) {
            $error = 'Choose a company.';
            $companyId = is_int($companyId) ? $companyId : 0;
        } elseif ($quotationNumber === '' || strlen($quotationNumber) > 40) {
            $error = 'Enter a quotation number of 40 characters or fewer.';
        } elseif ($title === '' || strlen($title) > 180) {
            $error = 'Enter a quotation title of 180 characters or fewer.';
        } elseif (strlen($eventLabel) > 255) {
            $error = 'Enter an event of 255 characters or fewer.';
        } elseif (strlen($venue) > 255) {
            $error = 'Enter a venue of 255 characters or fewer.';
        } elseif ($items === []) {
            $error = 'Add at least one line item.';
        }

        if ($error === '' && $quotationDate !== '') {
            $date = \DateTimeImmutable::createFromFormat('Y-m-d', $quotationDate);
            if ($date === false || $date->format('Y-m-d') !== $quotationDate) {
                $error = 'Enter a valid quotation date.';
            } else {
                $storedQuotationDate = $quotationDate;
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
            'quotationNumber' => $quotationNumber,
            'title' => $title,
            'eventLabel' => $eventLabel,
            'venue' => $venue,
            'quotationDate' => $quotationDate,
            'dueDate' => $dueDate,
            'notes' => $notes,
            'items' => $items,
            'storedAmount' => $storedAmount,
            'storedEventLabel' => $eventLabel === '' ? null : $eventLabel,
            'storedVenue' => $venue === '' ? null : $venue,
            'storedQuotationDate' => $storedQuotationDate,
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

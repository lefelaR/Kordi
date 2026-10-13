<?php

namespace App\Controllers;

use App\Models\Auth;
use App\Models\Company;

class CompanyController
{
    public function __construct(private Auth $auth, private Company $companies)
    {
    }

    public function add(): void
    {
        if (!$this->auth->check()) {
            header('Location: /login');
            exit;
        }

        $error = '';
        $name = '';
        $contact = '';
        $email = '';
        $phone = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->readCompanyInput();
            $error = $input['error'];
            $name = $input['name'];
            $contact = $input['contact'];
            $email = $input['email'];
            $phone = $input['phone'];

            if ($error === '') {
                $this->companies->create($name, $input['storedContact'], $input['storedEmail'], $input['storedPhone']);
                header('Location: ' . $this->companiesLocation());
                exit;
            }
        }

        $pageTitle = 'Add company | Kordicraft Creations';
        $currentSection = 'companies';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'href' => '/dashboard'],
            ['label' => 'Companies', 'href' => '/dashboard/companies'],
            ['label' => 'Add company'],
        ];
        $companyPage = $this->requestedPage();
        $formAction = '/companies/add';
        $heading = 'Add company';
        $summary = 'Save a company to the companies list.';

        ob_start();
        require __DIR__ . '/../Views/dashboard/companies/form.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/dashboard.php';
    }

    private function requestedPage(): int
    {
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
        if (!is_int($page) || $page < 1) {
            $page = filter_input(INPUT_POST, 'page', FILTER_VALIDATE_INT);
        }

        return is_int($page) && $page > 0 ? $page : 1;
    }

    private function companiesLocation(): string
    {
        $page = $this->requestedPage();
        if ($page < 2) {
            return '/dashboard/companies';
        }

        return '/dashboard/companies?page=' . $page;
    }

    private function readCompanyInput(): array
    {
        $name = trim($_POST['name'] ?? '');
        $contact = trim($_POST['contact'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $error = '';

        if ($name === '' || strlen($name) > 180) {
            $error = 'Enter a company name of 180 characters or fewer.';
        } elseif (strlen($contact) > 180) {
            $error = 'Enter a contact name of 180 characters or fewer.';
        } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a valid email address.';
        } elseif (strlen($phone) > 40) {
            $error = 'Enter a phone number of 40 characters or fewer.';
        }

        return [
            'error' => $error,
            'name' => $name,
            'contact' => $contact,
            'email' => $email,
            'phone' => $phone,
            'storedContact' => $contact === '' ? null : $contact,
            'storedEmail' => $email === '' ? null : $email,
            'storedPhone' => $phone === '' ? null : $phone,
        ];
    }
}

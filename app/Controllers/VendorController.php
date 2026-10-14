<?php

namespace App\Controllers;

use App\Models\Auth;
use App\Models\Vendor;

class VendorController
{
    public function __construct(private Auth $auth, private Vendor $vendors)
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
            $input = $this->readVendorInput();
            $error = $input['error'];
            $name = $input['name'];
            $contact = $input['contact'];
            $email = $input['email'];
            $phone = $input['phone'];

            if ($error === '') {
                $this->vendors->create($name, $input['storedContact'], $input['storedEmail'], $input['storedPhone']);
                header('Location: ' . $this->vendorsLocation());
                exit;
            }
        }

        $pageTitle = 'Add vendor | Kordicraft Creations';
        $currentSection = 'vendors';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'href' => '/dashboard'],
            ['label' => 'Vendors', 'href' => '/dashboard/vendors'],
            ['label' => 'Add vendor'],
        ];
        $vendorPage = $this->requestedPage();
        $formAction = '/vendors/add';
        $heading = 'Add vendor';
        $summary = 'Save a vendor to the vendors list.';

        ob_start();
        require __DIR__ . '/../Views/dashboard/vendors/form.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/dashboard.php';
    }

    public function edit(int $id): void
    {
        if (!$this->auth->check()) {
            header('Location: /login');
            exit;
        }

        $vendor = $this->vendors->find($id);
        if ($vendor === null) {
            $this->notFound();
            return;
        }

        $error = '';
        $name = (string) $vendor['name'];
        $contact = (string) ($vendor['contact'] ?? '');
        $email = (string) ($vendor['email'] ?? '');
        $phone = (string) ($vendor['phone'] ?? '');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->readVendorInput();
            $error = $input['error'];
            $name = $input['name'];
            $contact = $input['contact'];
            $email = $input['email'];
            $phone = $input['phone'];

            if ($error === '') {
                $this->vendors->update($id, $name, $input['storedContact'], $input['storedEmail'], $input['storedPhone']);
                header('Location: ' . $this->vendorsLocation());
                exit;
            }
        }

        $pageTitle = 'Edit vendor | Kordicraft Creations';
        $currentSection = 'vendors';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'href' => '/dashboard'],
            ['label' => 'Vendors', 'href' => '/dashboard/vendors'],
            ['label' => 'Edit vendor'],
        ];
        $vendorPage = $this->requestedPage();
        $formAction = '/vendors/' . $id . '/edit';
        $heading = 'Edit vendor';
        $summary = 'Update this vendor.';
        $actionIcons = true;

        ob_start();
        require __DIR__ . '/../Views/dashboard/vendors/form.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/dashboard.php';
    }

    public function delete(int $id): void
    {
        if (!$this->auth->check()) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->vendors->delete($id);
        }

        header('Location: ' . $this->vendorsLocation());
        exit;
    }

    private function notFound(): void
    {
        http_response_code(404);
        $pageTitle = 'Not found | Kordicraft Creations';
        $currentSection = 'vendors';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'href' => '/dashboard'],
            ['label' => 'Not found'],
        ];

        ob_start();
        require __DIR__ . '/../Views/dashboard/not-found.php';
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

    private function vendorsLocation(): string
    {
        $page = $this->requestedPage();
        if ($page < 2) {
            return '/dashboard/vendors';
        }

        return '/dashboard/vendors?page=' . $page;
    }

    private function readVendorInput(): array
    {
        $name = trim($_POST['name'] ?? '');
        $contact = trim($_POST['contact'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $error = '';

        if ($name === '' || strlen($name) > 180) {
            $error = 'Enter a business name of 180 characters or fewer.';
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

<?php

namespace App\Controllers;

use App\Models\Auth;
use App\Models\Company;
use App\Models\Event;
use DateTimeImmutable;

class EventController
{
    private const STATUSES = ['planned', 'confirmed', 'completed'];

    private const FINANCES = ['quotes', 'invoiced', 'payment made'];

    public function __construct(
        private Auth $auth,
        private Event $events,
        private Company $companies,
    ) {
    }

    public function add(): void
    {
        if (!$this->requireAuth()) {
            return;
        }

        $error = '';
        $title = '';
        $company = '';
        $companyContact = '';
        $companyEmail = '';
        $companyPhone = '';
        $location = '';
        $eventDate = '';
        $status = 'planned';
        $finance = 'quotes';
        $attendants = '0';
        $revenue = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->readEventInput();
            $error = $input['error'];
            $title = $input['title'];
            $company = $input['company'];
            $companyContact = $input['companyContact'];
            $companyEmail = $input['companyEmail'];
            $companyPhone = $input['companyPhone'];
            $location = $input['location'];
            $eventDate = $input['eventDate'];
            $status = $input['status'];
            $finance = $input['finance'];
            $attendants = $input['attendants'];
            $revenue = $input['revenue'];

            if ($error === '') {
                $this->events->create(
                    $title,
                    $input['storedCompany'],
                    $input['storedLocation'],
                    $input['storedDate'],
                    $status,
                    $finance,
                    $input['attendantCount'],
                    $input['storedRevenue']
                );
                $this->saveCompanyDetails(
                    $input['storedCompany'],
                    $input['storedCompanyContact'],
                    $input['storedCompanyEmail'],
                    $input['storedCompanyPhone']
                );
                header('Location: ' . $this->eventsLocation());
                exit;
            }
        }

        $pageTitle = 'Add event | Kordicraft Creations';
        $currentSection = 'events';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'href' => '/dashboard'],
            ['label' => 'Events', 'href' => '/dashboard/events'],
            ['label' => 'Add event'],
        ];
        $eventPage = $this->requestedPage();
        $formAction = '/events/add';
        $heading = 'Add event';
        $summary = 'Save an event to the events list.';

        ob_start();
        require __DIR__ . '/../Views/dashboard/events/form.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/dashboard.php';
    }

    public function edit(int $id): void
    {
        if (!$this->requireAuth()) {
            return;
        }

        $event = $this->events->find($id);
        if ($event === null) {
            $this->notFound();
            return;
        }

        $error = '';
        $title = (string) $event['title'];
        $company = (string) ($event['company'] ?? '');
        $location = (string) ($event['location'] ?? '');
        $eventDate = (string) ($event['event_date'] ?? '');
        $status = (string) $event['status'];
        $finance = (string) $event['finance'];
        $attendants = (string) $event['attendants'];
        $revenue = $event['revenue'] === null ? '' : (string) $event['revenue'];
        $companyDetails = $this->companyDetailsFor($company);
        $companyContact = $companyDetails['contact'];
        $companyEmail = $companyDetails['email'];
        $companyPhone = $companyDetails['phone'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->readEventInput();
            $error = $input['error'];
            $title = $input['title'];
            $company = $input['company'];
            $companyContact = $input['companyContact'];
            $companyEmail = $input['companyEmail'];
            $companyPhone = $input['companyPhone'];
            $location = $input['location'];
            $eventDate = $input['eventDate'];
            $status = $input['status'];
            $finance = $input['finance'];
            $attendants = $input['attendants'];
            $revenue = $input['revenue'];

            if ($error === '') {
                $this->events->update(
                    $id,
                    $title,
                    $input['storedCompany'],
                    $input['storedLocation'],
                    $input['storedDate'],
                    $status,
                    $finance,
                    $input['attendantCount'],
                    $input['storedRevenue']
                );
                $this->saveCompanyDetails(
                    $input['storedCompany'],
                    $input['storedCompanyContact'],
                    $input['storedCompanyEmail'],
                    $input['storedCompanyPhone']
                );
                header('Location: ' . $this->eventsLocation());
                exit;
            }
        }

        $pageTitle = 'Edit event | Kordicraft Creations';
        $currentSection = 'events';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'href' => '/dashboard'],
            ['label' => 'Events', 'href' => '/dashboard/events'],
            ['label' => 'Edit event'],
        ];
        $eventPage = $this->requestedPage();
        $formAction = '/events/' . $id . '/edit';
        $heading = 'Edit event';
        $summary = 'Update this event.';
        $actionIcons = true;

        ob_start();
        require __DIR__ . '/../Views/dashboard/events/form.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/dashboard.php';
    }

    public function updateStatus(int $id): void
    {
        if (!$this->requireAuth()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['status'] ?? '', self::STATUSES, true)) {
            $this->events->updateStatus($id, $_POST['status']);
        }

        header('Location: ' . $this->eventsLocation());
        exit;
    }

    public function delete(int $id): void
    {
        if (!$this->requireAuth()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->events->delete($id);
        }

        header('Location: ' . $this->eventsLocation());
        exit;
    }

    private function requireAuth(): bool
    {
        if ($this->auth->check()) {
            return true;
        }

        header('Location: /login');
        exit;
    }

    private function notFound(): void
    {
        http_response_code(404);
        $pageTitle = 'Not found | Kordicraft Creations';
        $currentSection = 'events';
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

    private function eventsLocation(): string
    {
        $page = $this->requestedPage();
        if ($page < 2) {
            return '/dashboard/events';
        }

        return '/dashboard/events?page=' . $page;
    }

    /**
     * @return array{contact: string, email: string, phone: string}
     */
    private function companyDetailsFor(string $companyName): array
    {
        if ($companyName === '') {
            return [
                'contact' => '',
                'email' => '',
                'phone' => '',
            ];
        }

        $existing = $this->companies->findByName($companyName);
        if ($existing === null) {
            return [
                'contact' => '',
                'email' => '',
                'phone' => '',
            ];
        }

        return [
            'contact' => (string) ($existing['contact'] ?? ''),
            'email' => (string) ($existing['email'] ?? ''),
            'phone' => (string) ($existing['phone'] ?? ''),
        ];
    }

    private function saveCompanyDetails(
        ?string $companyName,
        ?string $contact,
        ?string $email,
        ?string $phone,
    ): void {
        if ($companyName === null || $companyName === '') {
            return;
        }

        $this->companies->saveDetails($companyName, $contact, $email, $phone);
    }

    private function readEventInput(): array
    {
        $title = trim($_POST['title'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $companyContact = trim($_POST['company_contact'] ?? '');
        $companyEmail = trim($_POST['company_email'] ?? '');
        $companyPhone = trim($_POST['company_phone'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $eventDate = trim($_POST['event_date'] ?? '');
        $status = $_POST['status'] ?? '';
        $finance = $_POST['finance'] ?? '';
        $attendants = trim($_POST['attendants'] ?? '');
        $revenue = trim($_POST['revenue'] ?? '');
        $storedCompany = null;
        $storedLocation = null;
        $storedDate = null;
        $storedRevenue = null;
        $storedCompanyContact = null;
        $storedCompanyEmail = null;
        $storedCompanyPhone = null;
        $attendantCount = 0;
        $error = '';

        if ($title === '' || strlen($title) > 180) {
            $error = 'Enter an event title of 180 characters or fewer.';
        } elseif (strlen($company) > 180) {
            $error = 'Enter a company name of 180 characters or fewer.';
        } elseif (strlen($companyContact) > 180) {
            $error = 'Enter a contact name of 180 characters or fewer.';
        } elseif ($companyEmail !== '' && !filter_var($companyEmail, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a valid company email address.';
        } elseif (strlen($companyEmail) > 255) {
            $error = 'Enter a company email of 255 characters or fewer.';
        } elseif (strlen($companyPhone) > 40) {
            $error = 'Enter a company phone of 40 characters or fewer.';
        } elseif (
            $company === ''
            && ($companyContact !== '' || $companyEmail !== '' || $companyPhone !== '')
        ) {
            $error = 'Enter a company name to save company details.';
        } elseif (strlen($location) > 255) {
            $error = 'Enter a location of 255 characters or fewer.';
        } elseif ($eventDate !== '') {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $eventDate);
            if (!$date instanceof DateTimeImmutable || $date->format('Y-m-d') !== $eventDate) {
                $error = 'Enter a valid date.';
            } else {
                $storedDate = $eventDate;
            }
        }

        if ($error === '' && !in_array($status, self::STATUSES, true)) {
            $error = 'Choose a status.';
        }

        if ($error === '' && !in_array($finance, self::FINANCES, true)) {
            $error = 'Choose a finance option.';
        }

        if ($error === '' && ($attendants === '' || !preg_match('/^\d+$/', $attendants) || (int) $attendants > 1000000)) {
            $error = 'Enter the number of attendants as a whole number.';
        } elseif ($error === '') {
            $attendantCount = (int) $attendants;
            $storedCompany = $company === '' ? null : $company;
            $storedLocation = $location === '' ? null : $location;
            $storedCompanyContact = $companyContact === '' ? null : $companyContact;
            $storedCompanyEmail = $companyEmail === '' ? null : $companyEmail;
            $storedCompanyPhone = $companyPhone === '' ? null : $companyPhone;
        }

        if ($error === '' && $revenue !== '') {
            if (!preg_match('/^\d+(\.\d{1,2})?$/', $revenue)) {
                $error = 'Enter a valid revenue with up to two decimal places.';
            } else {
                $storedRevenue = $revenue;
            }
        }

        return [
            'error' => $error,
            'title' => $title,
            'company' => $company,
            'storedCompany' => $storedCompany,
            'companyContact' => $companyContact,
            'storedCompanyContact' => $storedCompanyContact,
            'companyEmail' => $companyEmail,
            'storedCompanyEmail' => $storedCompanyEmail,
            'companyPhone' => $companyPhone,
            'storedCompanyPhone' => $storedCompanyPhone,
            'location' => $location,
            'storedLocation' => $storedLocation,
            'eventDate' => $eventDate,
            'storedDate' => $storedDate,
            'status' => $status,
            'finance' => $finance,
            'attendants' => $attendants,
            'attendantCount' => $attendantCount,
            'revenue' => $revenue,
            'storedRevenue' => $storedRevenue,
        ];
    }
}

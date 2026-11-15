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
        $extraScripts = ['/assets/js/event-location.js'];

        ob_start();
        require __DIR__ . '/../Views/dashboard/events/form.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/dashboard.php';
    }

    public function show(int $id): void
    {
        if (!$this->requireAuth()) {
            return;
        }

        $event = $this->events->find($id);
        if ($event === null) {
            $this->notFound();
            return;
        }

        $company = (string) ($event['company'] ?? '');
        $companyDetails = $this->companyDetailsFor($company);
        $eventPage = $this->requestedPage();
        $pageTitle = (string) $event['title'] . ' | Kordicraft Creations';
        $currentSection = 'events';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'href' => '/dashboard'],
            ['label' => 'Events', 'href' => '/dashboard/events'],
            ['label' => (string) $event['title']],
        ];

        ob_start();
        require __DIR__ . '/../Views/dashboard/events/show.php';
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
        $eventImage = (string) ($event['image'] ?? '');
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
                $imageError = $this->imageUploadError();
                if ($imageError !== '') {
                    $error = $imageError;
                }
            }

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
                $storedImage = $this->storeEventImage($id, $eventImage);
                if ($storedImage['error'] !== '') {
                    $error = $storedImage['error'];
                } else {
                    if ($storedImage['path'] !== null) {
                        $eventImage = $storedImage['path'];
                    }
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
        }

        $eventId = $id;
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
        $extraScripts = ['/assets/js/event-location.js', '/assets/js/event-image.js'];

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

    private function imageUploadError(): string
    {
        $file = $_FILES['image'] ?? null;
        if (!is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return '';
        }

        $uploadError = (int) ($file['error'] ?? UPLOAD_ERR_OK);
        if ($uploadError !== UPLOAD_ERR_OK) {
            return 'The image could not be uploaded. Try a smaller file.';
        }

        if ((int) ($file['size'] ?? 0) > 5 * 1024 * 1024) {
            return 'Choose an image that is 5 MB or smaller.';
        }

        $temporary = (string) ($file['tmp_name'] ?? '');
        if ($temporary === '' || !is_uploaded_file($temporary)) {
            return 'The image could not be uploaded.';
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($temporary);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
            return 'Choose a JPEG, PNG, WebP, or GIF image.';
        }

        return '';
    }

    /**
     * @return array{error: string, path: ?string}
     */
    private function storeEventImage(int $eventId, string $currentPath): array
    {
        $file = $_FILES['image'] ?? null;
        if (!is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['error' => '', 'path' => null];
        }

        $temporary = (string) ($file['tmp_name'] ?? '');
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($temporary);
        $extension = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ][$mime] ?? null;
        if ($extension === null) {
            return ['error' => 'Choose a JPEG, PNG, WebP, or GIF image.', 'path' => null];
        }

        $directory = dirname(__DIR__, 2) . '/images/events';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            return ['error' => 'The image could not be saved.', 'path' => null];
        }

        $filename = 'event-' . $eventId . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
        if (!move_uploaded_file($temporary, $directory . '/' . $filename)) {
            return ['error' => 'The image could not be saved.', 'path' => null];
        }

        $path = '/images/events/' . $filename;
        $this->events->saveImage($eventId, $path);
        $this->removeStoredEventImage($currentPath, $path);

        return ['error' => '', 'path' => $path];
    }

    private function removeStoredEventImage(string $currentPath, string $replacement): void
    {
        if ($currentPath === '' || $currentPath === $replacement || !str_starts_with($currentPath, '/images/events/')) {
            return;
        }

        $fullPath = dirname(__DIR__, 2) . '/images/events/' . basename($currentPath);
        if (is_file($fullPath)) {
            unlink($fullPath);
        }
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

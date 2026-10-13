<?php

namespace App\Controllers;

use App\Models\Auth;
use App\Models\Event;
use DateTimeImmutable;

class EventController
{
    private const STATUSES = ['planned', 'confirmed', 'completed'];

    private const FINANCES = ['quotes', 'invoiced', 'payment made'];

    public function __construct(private Auth $auth, private Event $events)
    {
    }

    public function add(): void
    {
        if (!$this->requireAuth()) {
            return;
        }

        $error = '';
        $title = '';
        $eventDate = '';
        $status = 'planned';
        $finance = 'quotes';
        $attendants = '0';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->readEventInput();
            $error = $input['error'];
            $title = $input['title'];
            $eventDate = $input['eventDate'];
            $status = $input['status'];
            $finance = $input['finance'];
            $attendants = $input['attendants'];

            if ($error === '') {
                $this->events->create($title, $input['storedDate'], $status, $finance, $input['attendantCount']);
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
        $eventDate = (string) ($event['event_date'] ?? '');
        $status = (string) $event['status'];
        $finance = (string) $event['finance'];
        $attendants = (string) $event['attendants'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $this->readEventInput();
            $error = $input['error'];
            $title = $input['title'];
            $eventDate = $input['eventDate'];
            $status = $input['status'];
            $finance = $input['finance'];
            $attendants = $input['attendants'];

            if ($error === '') {
                $this->events->update($id, $title, $input['storedDate'], $status, $finance, $input['attendantCount']);
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

    private function readEventInput(): array
    {
        $title = trim($_POST['title'] ?? '');
        $eventDate = trim($_POST['event_date'] ?? '');
        $status = $_POST['status'] ?? '';
        $finance = $_POST['finance'] ?? '';
        $attendants = trim($_POST['attendants'] ?? '');
        $storedDate = null;
        $attendantCount = 0;
        $error = '';

        if ($title === '' || strlen($title) > 180) {
            $error = 'Enter an event title of 180 characters or fewer.';
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
        }

        return [
            'error' => $error,
            'title' => $title,
            'eventDate' => $eventDate,
            'storedDate' => $storedDate,
            'status' => $status,
            'finance' => $finance,
            'attendants' => $attendants,
            'attendantCount' => $attendantCount,
        ];
    }
}

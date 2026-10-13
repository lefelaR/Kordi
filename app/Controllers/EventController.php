<?php

namespace App\Controllers;

use App\Models\Auth;
use App\Models\Event;
use DateTimeImmutable;

class EventController
{
    public function __construct(private Auth $auth, private Event $events)
    {
    }

    public function add(): void
    {
        if (!$this->auth->check()) {
            header('Location: /login');
            exit;
        }

        $error = '';
        $title = '';
        $eventDate = '';
        $status = 'planned';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $eventDate = trim($_POST['event_date'] ?? '');
            $status = $_POST['status'] ?? '';
            $storedDate = null;
            $allowedStatuses = ['planned', 'confirmed', 'completed'];

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

            if ($error === '' && !in_array($status, $allowedStatuses, true)) {
                $error = 'Choose a status.';
            }

            if ($error === '') {
                $this->events->create($title, $storedDate, $status);
                header('Location: /dashboard/events');
                exit;
            }
        }

        $pageTitle = 'Add event | Kordicraft Creations';
        $currentSection = 'events';

        ob_start();
        require __DIR__ . '/../Views/dashboard/events/add.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/dashboard.php';
    }
}

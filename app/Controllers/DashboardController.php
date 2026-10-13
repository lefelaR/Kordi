<?php

namespace App\Controllers;

use App\Config\Database;
use App\Models\Auth;
use App\Models\Event;

class DashboardController
{
    public function __construct(private Auth $auth, private Event $events)
    {
    }

    public function show(string $section): void
    {
        if (!$this->auth->check()) {
            header('Location: /login');
            exit;
        }

        $pages = [
            'overview' => [
                'title' => 'Dashboard | Kordicraft Creations',
                'view' => __DIR__ . '/../Views/dashboard/index.php',
            ],
            'events' => [
                'title' => 'Events | Kordicraft Creations',
                'view' => __DIR__ . '/../Views/dashboard/events/index.php',
            ],
            'vendors' => [
                'title' => 'Vendors | Kordicraft Creations',
                'view' => __DIR__ . '/../Views/dashboard/vendors/index.php',
            ],
            'enquiries' => [
                'title' => 'Enquiries | Kordicraft Creations',
                'view' => __DIR__ . '/../Views/dashboard/enquiries/index.php',
            ],
            'venues' => [
                'title' => 'Venues | Kordicraft Creations',
                'view' => __DIR__ . '/../Views/dashboard/venues/index.php',
            ],
            'documents' => [
                'title' => 'Documents | Kordicraft Creations',
                'view' => __DIR__ . '/../Views/dashboard/documents/index.php',
            ],
            'contacts' => [
                'title' => 'Contacts | Kordicraft Creations',
                'view' => __DIR__ . '/../Views/dashboard/contacts/index.php',
            ],
        ];

        if (!isset($pages[$section])) {
            http_response_code(404);
            $pageTitle = 'Not found | Kordicraft Creations';
            $currentSection = '';
            $breadcrumbs = [
                ['label' => 'Dashboard', 'href' => '/dashboard'],
                ['label' => 'Not found'],
            ];
            ob_start();
            require __DIR__ . '/../Views/dashboard/not-found.php';
            $content = ob_get_clean();
            require __DIR__ . '/../Views/layouts/dashboard.php';
            return;
        }

        $eventCount = in_array($section, ['overview', 'events'], true) ? $this->events->count() : 0;
        $events = [];
        $eventStatusCounts = [];
        $eventPage = 1;
        $eventPageCount = 1;
        $eventPageLinks = [1];
        $eventRangeStart = 0;
        $eventRangeEnd = 0;

        if ($section === 'events') {
            $perPage = 10;
            $eventPageCount = max(1, (int) ceil($eventCount / $perPage));
            $requestedPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
            $eventPage = is_int($requestedPage) && $requestedPage > 0 ? min($requestedPage, $eventPageCount) : 1;
            if (isset($_GET['page']) && (!is_int($requestedPage) || $requestedPage !== $eventPage)) {
                $location = $eventPage === 1 ? '/dashboard/events' : '/dashboard/events?page=' . $eventPage;
                header('Location: ' . $location);
                exit;
            }
            $events = $eventCount === 0 ? [] : $this->events->page($eventPage, $perPage);
            $eventStatusCounts = $this->events->statusCounts();
            $eventPageLinks = $this->eventPageLinks($eventPage, $eventPageCount);
            $eventRangeStart = (($eventPage - 1) * $perPage) + 1;
            $eventRangeEnd = min($eventCount, $eventPage * $perPage);
        }

        $enquiries = in_array($section, ['overview', 'enquiries'], true) ? $this->enquiries() : [];
        $pageTitle = $pages[$section]['title'];
        $currentSection = $section;
        $sectionLabel = trim(strtok($pageTitle, '|'));
        $breadcrumbs = $section === 'overview'
            ? [['label' => 'Dashboard']]
            : [
                ['label' => 'Dashboard', 'href' => '/dashboard'],
                ['label' => $sectionLabel],
            ];

        ob_start();
        require $pages[$section]['view'];
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/dashboard.php';
    }

    private function eventPageLinks(int $current, int $last): array
    {
        if ($last <= 7) {
            return range(1, $last);
        }

        $pages = [1];
        $start = max(2, $current - 1);
        $end = min($last - 1, $current + 1);

        if ($start > 2) {
            $pages[] = null;
        }

        for ($page = $start; $page <= $end; $page++) {
            $pages[] = $page;
        }

        if ($end < $last - 1) {
            $pages[] = null;
        }

        $pages[] = $last;

        return $pages;
    }

    private function enquiries(): array
    {
        $statement = Database::connection()->query(
            'SELECT id, name, email, event_type, event_date, message, created_at FROM enquiries ORDER BY created_at DESC, id DESC'
        );

        return $statement->fetchAll();
    }
}

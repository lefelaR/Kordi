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
            ob_start();
            require __DIR__ . '/../Views/dashboard/not-found.php';
            $content = ob_get_clean();
            require __DIR__ . '/../Views/layouts/dashboard.php';
            return;
        }

        $events = in_array($section, ['overview', 'events'], true) ? $this->events->all() : [];
        $enquiries = in_array($section, ['overview', 'enquiries'], true) ? $this->enquiries() : [];
        $pageTitle = $pages[$section]['title'];
        $currentSection = $section;

        ob_start();
        require $pages[$section]['view'];
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/dashboard.php';
    }

    private function enquiries(): array
    {
        $statement = Database::connection()->query(
            'SELECT id, name, email, event_type, event_date, message, created_at FROM enquiries ORDER BY created_at DESC, id DESC'
        );

        return $statement->fetchAll();
    }
}

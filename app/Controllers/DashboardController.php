<?php

namespace App\Controllers;

use App\Config\Database;
use App\Models\Auth;
use App\Models\Company;
use App\Models\Event;
use App\Models\Vendor;

class DashboardController
{
    public function __construct(private Auth $auth, private Event $events, private Vendor $vendors, private Company $companies)
    {
    }

    public function show(string $section, ?string $subsection = null): void
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
            'companies' => [
                'title' => 'Companies | Kordicraft Creations',
                'view' => __DIR__ . '/../Views/dashboard/companies/index.php',
            ],
            'documents' => [
                'title' => 'Documents | Kordicraft Creations',
                'view' => __DIR__ . '/../Views/dashboard/documents/index.php',
                'children' => [
                    'proposals' => [
                        'title' => 'Proposals | Kordicraft Creations',
                        'label' => 'Proposals',
                        'view' => __DIR__ . '/../Views/dashboard/documents/proposals.php',
                    ],
                    'invoices' => [
                        'title' => 'Invoices | Kordicraft Creations',
                        'label' => 'Invoices',
                        'view' => __DIR__ . '/../Views/dashboard/documents/invoices.php',
                    ],
                    'proofs' => [
                        'title' => 'Proofs | Kordicraft Creations',
                        'label' => 'Proofs',
                        'view' => __DIR__ . '/../Views/dashboard/documents/proofs.php',
                    ],
                    'acknowledgement' => [
                        'title' => 'Acknowledgement | Kordicraft Creations',
                        'label' => 'Acknowledgement',
                        'view' => __DIR__ . '/../Views/dashboard/documents/acknowledgement.php',
                    ],
                ],
            ],
            'contacts' => [
                'title' => 'Contacts | Kordicraft Creations',
                'view' => __DIR__ . '/../Views/dashboard/contacts/index.php',
            ],
            'settings' => [
                'title' => 'Settings | Kordicraft Creations',
                'view' => __DIR__ . '/../Views/dashboard/settings/index.php',
            ],
        ];

        if (!isset($pages[$section]) || ($subsection !== null && !isset($pages[$section]['children'][$subsection]))) {
            http_response_code(404);
            $pageTitle = 'Not found | Kordicraft Creations';
            $currentSection = '';
            $currentDocument = '';
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
            $eventPageLinks = $this->pageLinks($eventPage, $eventPageCount);
            $eventRangeStart = (($eventPage - 1) * $perPage) + 1;
            $eventRangeEnd = min($eventCount, $eventPage * $perPage);
        }

        $vendorCount = in_array($section, ['overview', 'vendors'], true) ? $this->vendors->count() : 0;
        $vendors = [];
        $vendorPage = 1;
        $vendorPageCount = 1;
        $vendorPageLinks = [1];
        $vendorRangeStart = 0;
        $vendorRangeEnd = 0;

        if ($section === 'vendors') {
            $perPage = 10;
            $vendorPageCount = max(1, (int) ceil($vendorCount / $perPage));
            $requestedPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
            $vendorPage = is_int($requestedPage) && $requestedPage > 0 ? min($requestedPage, $vendorPageCount) : 1;
            if (isset($_GET['page']) && (!is_int($requestedPage) || $requestedPage !== $vendorPage)) {
                $location = $vendorPage === 1 ? '/dashboard/vendors' : '/dashboard/vendors?page=' . $vendorPage;
                header('Location: ' . $location);
                exit;
            }
            $vendors = $vendorCount === 0 ? [] : $this->vendors->page($vendorPage, $perPage);
            $vendorPageLinks = $this->pageLinks($vendorPage, $vendorPageCount);
            $vendorRangeStart = (($vendorPage - 1) * $perPage) + 1;
            $vendorRangeEnd = min($vendorCount, $vendorPage * $perPage);
        }

        $companyCount = $section === 'companies' ? $this->companies->count() : 0;
        $companies = [];
        $companyPage = 1;
        $companyPageCount = 1;
        $companyPageLinks = [1];
        $companyRangeStart = 0;
        $companyRangeEnd = 0;

        if ($section === 'companies') {
            $perPage = 10;
            $companyPageCount = max(1, (int) ceil($companyCount / $perPage));
            $requestedPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
            $companyPage = is_int($requestedPage) && $requestedPage > 0 ? min($requestedPage, $companyPageCount) : 1;
            if (isset($_GET['page']) && (!is_int($requestedPage) || $requestedPage !== $companyPage)) {
                $location = $companyPage === 1 ? '/dashboard/companies' : '/dashboard/companies?page=' . $companyPage;
                header('Location: ' . $location);
                exit;
            }
            $companies = $companyCount === 0 ? [] : $this->companies->page($companyPage, $perPage);
            $companyPageLinks = $this->pageLinks($companyPage, $companyPageCount);
            $companyRangeStart = (($companyPage - 1) * $perPage) + 1;
            $companyRangeEnd = min($companyCount, $companyPage * $perPage);
        }

        $enquiries = in_array($section, ['overview', 'enquiries'], true) ? $this->enquiries() : [];
        $currentSection = $section;
        $currentDocument = $subsection ?? '';

        if ($subsection !== null) {
            $page = $pages[$section]['children'][$subsection];
            $pageTitle = $page['title'];
            $breadcrumbs = [
                ['label' => 'Dashboard', 'href' => '/dashboard'],
                ['label' => 'Documents', 'href' => '/dashboard/documents'],
                ['label' => $page['label']],
            ];
            $view = $page['view'];
        } else {
            $pageTitle = $pages[$section]['title'];
            $sectionLabel = trim(strtok($pageTitle, '|'));
            $breadcrumbs = $section === 'overview'
                ? [['label' => 'Dashboard']]
                : [
                    ['label' => 'Dashboard', 'href' => '/dashboard'],
                    ['label' => $sectionLabel],
                ];
            $view = $pages[$section]['view'];
        }

        ob_start();
        require $view;
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/dashboard.php';
    }

    private function pageLinks(int $current, int $last): array
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

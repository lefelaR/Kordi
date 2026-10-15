<?php

namespace App\Controllers;

use App\Models\Auth;
use App\Models\DashboardApi;

class DashboardController
{
    public function __construct(
        private Auth $auth,
        private DashboardApi $dashboardApi,
    ) {
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

        $eventCount = 0;
        $events = [];
        $eventStatusCounts = [];
        $eventPage = 1;
        $eventPageCount = 1;
        $eventPageLinks = [1];
        $eventRangeStart = 0;
        $eventRangeEnd = 0;

        $vendorCount = 0;
        $vendors = [];
        $vendorPage = 1;
        $vendorPageCount = 1;
        $vendorPageLinks = [1];
        $vendorRangeStart = 0;
        $vendorRangeEnd = 0;

        $companyCount = 0;
        $companies = [];
        $companyPage = 1;
        $companyPageCount = 1;
        $companyPageLinks = [1];
        $companyRangeStart = 0;
        $companyRangeEnd = 0;

        $venueSearch = '';
        $venueCount = 0;
        $venues = [];
        $venuePage = 1;
        $venuePageCount = 1;
        $venuePageLinks = [1];
        $venueRangeStart = 0;
        $venueRangeEnd = 0;

        $enquiries = [];
        $invoiceCompanies = [];
        $selectedInvoice = null;
        $revenueTotal = 0.0;
        $eventRevenueChart = [
            'year' => (int) date('Y'),
            'events' => [],
        ];
        $overviewCalendar = [
            'year' => (int) date('Y'),
            'month' => (int) date('n'),
            'events' => [],
        ];
        $overviewMonthValue = date('Y-m');

        if ($section === 'overview') {
            $requestedMonth = trim((string) ($_GET['month'] ?? ''));
            if (preg_match('/^(\d{4})-(\d{2})$/', $requestedMonth, $monthParts) === 1) {
                $overviewYear = (int) $monthParts[1];
                $overviewMonth = (int) $monthParts[2];
                if ($overviewYear < 2000 || $overviewYear > 2100 || $overviewMonth < 1 || $overviewMonth > 12) {
                    $overviewYear = (int) date('Y');
                    $overviewMonth = (int) date('n');
                }
            } else {
                $overviewYear = (int) date('Y');
                $overviewMonth = (int) date('n');
            }
            $overviewMonthValue = sprintf('%04d-%02d', $overviewYear, $overviewMonth);

            $overview = $this->dashboardApi->overview($overviewYear, $overviewMonth);
            $eventCount = $overview['event_count'];
            $vendorCount = $overview['vendor_count'];
            $venueCount = $overview['venue_count'];
            $enquiries = $overview['enquiries'];
            $revenueTotal = $overview['revenue_total'];
            $eventRevenueChart = $overview['event_revenue_chart'];
            $overviewCalendar = $overview['calendar'];
            $extraScripts = [
                '/assets/js/chart.umd.min.js',
                '/assets/js/overview-revenue-chart.js',
            ];
        }

        if ($section === 'events') {
            $requestedPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
            $requestedPage = is_int($requestedPage) && $requestedPage > 0 ? $requestedPage : 1;
            $payload = $this->dashboardApi->events($requestedPage);
            $eventCount = $payload['meta']['total'];
            $eventPage = $payload['meta']['page'];
            $eventPageCount = $payload['meta']['last_page'];
            $events = $payload['data'];
            $eventStatusCounts = $payload['status_counts'];
            if (isset($_GET['page']) && (!is_int($requestedPage) || $requestedPage !== $eventPage)) {
                $location = $eventPage === 1 ? '/dashboard/events' : '/dashboard/events?page=' . $eventPage;
                header('Location: ' . $location);
                exit;
            }
            $eventPageLinks = $this->pageLinks($eventPage, $eventPageCount);
            $eventRangeStart = $eventCount === 0 ? 0 : (($eventPage - 1) * $payload['meta']['per_page']) + 1;
            $eventRangeEnd = min($eventCount, $eventPage * $payload['meta']['per_page']);
        }

        if ($section === 'vendors') {
            $requestedPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
            $requestedPage = is_int($requestedPage) && $requestedPage > 0 ? $requestedPage : 1;
            $payload = $this->dashboardApi->vendors($requestedPage);
            $vendorCount = $payload['meta']['total'];
            $vendorPage = $payload['meta']['page'];
            $vendorPageCount = $payload['meta']['last_page'];
            $vendors = $payload['data'];
            if (isset($_GET['page']) && (!is_int($requestedPage) || $requestedPage !== $vendorPage)) {
                $location = $vendorPage === 1 ? '/dashboard/vendors' : '/dashboard/vendors?page=' . $vendorPage;
                header('Location: ' . $location);
                exit;
            }
            $vendorPageLinks = $this->pageLinks($vendorPage, $vendorPageCount);
            $vendorRangeStart = $vendorCount === 0 ? 0 : (($vendorPage - 1) * $payload['meta']['per_page']) + 1;
            $vendorRangeEnd = min($vendorCount, $vendorPage * $payload['meta']['per_page']);
        }

        if ($section === 'companies') {
            $requestedPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
            $requestedPage = is_int($requestedPage) && $requestedPage > 0 ? $requestedPage : 1;
            $payload = $this->dashboardApi->companies($requestedPage);
            $companyCount = $payload['meta']['total'];
            $companyPage = $payload['meta']['page'];
            $companyPageCount = $payload['meta']['last_page'];
            $companies = $payload['data'];
            if (isset($_GET['page']) && (!is_int($requestedPage) || $requestedPage !== $companyPage)) {
                $location = $companyPage === 1 ? '/dashboard/companies' : '/dashboard/companies?page=' . $companyPage;
                header('Location: ' . $location);
                exit;
            }
            $companyPageLinks = $this->pageLinks($companyPage, $companyPageCount);
            $companyRangeStart = $companyCount === 0 ? 0 : (($companyPage - 1) * $payload['meta']['per_page']) + 1;
            $companyRangeEnd = min($companyCount, $companyPage * $payload['meta']['per_page']);
        }

        if ($section === 'venues') {
            $venueSearch = trim((string) ($_GET['q'] ?? ''));
            if (strlen($venueSearch) > 120) {
                $venueSearch = substr($venueSearch, 0, 120);
            }
            $requestedPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
            $requestedPage = is_int($requestedPage) && $requestedPage > 0 ? $requestedPage : 1;
            $payload = $this->dashboardApi->venues($requestedPage, $venueSearch);
            $venueSearch = $payload['search'];
            $venueCount = $payload['meta']['total'];
            $venuePage = $payload['meta']['page'];
            $venuePageCount = $payload['meta']['last_page'];
            $venues = $payload['data'];
            if (isset($_GET['page']) && (!is_int($requestedPage) || $requestedPage !== $venuePage)) {
                header('Location: ' . $this->venuesListLocation($venuePage, $venueSearch));
                exit;
            }
            $venuePageLinks = $this->pageLinks($venuePage, $venuePageCount);
            $venueRangeStart = $venueCount === 0 ? 0 : (($venuePage - 1) * $payload['meta']['per_page']) + 1;
            $venueRangeEnd = min($venueCount, $venuePage * $payload['meta']['per_page']);
            $extraStylesheets = ['/assets/leaflet/leaflet.css'];
            $extraScripts = [
                '/assets/leaflet/leaflet.js',
                '/assets/js/venues-list-map.js',
            ];
        }

        if ($section === 'enquiries') {
            $enquiries = $this->dashboardApi->enquiries();
        }

        if ($section === 'documents' && $subsection === 'invoices') {
            foreach ($this->dashboardApi->companiesAll() as $company) {
                $invoiceCompanies[] = [
                    'id' => (int) $company['id'],
                    'name' => (string) $company['name'],
                    'invoices' => [],
                ];
            }
        }

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

    private function venuesListLocation(int $page, string $search): string
    {
        $query = [];
        if ($search !== '') {
            $query['q'] = $search;
        }
        if ($page > 1) {
            $query['page'] = (string) $page;
        }

        if ($query === []) {
            return '/dashboard/venues';
        }

        return '/dashboard/venues?' . http_build_query($query);
    }
}

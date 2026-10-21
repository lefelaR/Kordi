<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/app/error_handler.php';
kordi_register_error_handlers();

require __DIR__ . '/app/Config/Database.php';
require __DIR__ . '/app/Models/MailTransport.php';
require __DIR__ . '/app/Models/EnquiryMailer.php';
require __DIR__ . '/app/Models/ApiClient.php';
require __DIR__ . '/app/Models/DashboardApi.php';
require __DIR__ . '/app/Models/Auth.php';
require __DIR__ . '/app/Models/Event.php';
require __DIR__ . '/app/Models/Vendor.php';
require __DIR__ . '/app/Models/Company.php';
require __DIR__ . '/app/Models/Invoice.php';
require __DIR__ . '/app/Models/Quotation.php';
require __DIR__ . '/app/Models/QuotationMailer.php';
require __DIR__ . '/app/Models/Venue.php';
require __DIR__ . '/app/Controllers/HomeController.php';
require __DIR__ . '/app/Controllers/AuthController.php';
require __DIR__ . '/app/Controllers/DashboardController.php';
require __DIR__ . '/app/Controllers/EventController.php';
require __DIR__ . '/app/Controllers/VendorController.php';
require __DIR__ . '/app/Controllers/CompanyController.php';
require __DIR__ . '/app/Controllers/InvoiceController.php';
require __DIR__ . '/app/Controllers/QuotationController.php';
require __DIR__ . '/app/Controllers/ContractController.php';
require __DIR__ . '/app/Controllers/VenueController.php';

$apiBaseUrl = $_ENV['API_BASE_URL'] ?? getenv('API_BASE_URL') ?: 'http://api:8000/api';
$apiClient = new App\Models\ApiClient($apiBaseUrl);
$auth = new App\Models\Auth($apiClient);
$dashboardApi = new App\Models\DashboardApi($apiClient->withToken($auth->apiToken()));
$mailTransport = new App\Models\MailTransport();

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($requestPath === '/login') {
    (new App\Controllers\AuthController($auth))->login();
} elseif ($requestPath === '/logout') {
    (new App\Controllers\AuthController($auth))->logout();
} elseif ($requestPath === '/vendors/add') {
    $vendors = new App\Models\Vendor(App\Config\Database::connection());
    (new App\Controllers\VendorController($auth, $vendors))->add();
} elseif (preg_match('#^/vendors/(\d+)/(edit|delete)$#', $requestPath, $matches) === 1) {
    $vendors = new App\Models\Vendor(App\Config\Database::connection());
    $vendorController = new App\Controllers\VendorController($auth, $vendors);
    $vendorId = (int) $matches[1];
    if ($matches[2] === 'edit') {
        $vendorController->edit($vendorId);
    } else {
        $vendorController->delete($vendorId);
    }
} elseif ($requestPath === '/companies/add') {
    $companies = new App\Models\Company(App\Config\Database::connection());
    (new App\Controllers\CompanyController($auth, $companies))->add();
} elseif ($requestPath === '/invoices/add') {
    $database = App\Config\Database::connection();
    $invoices = new App\Models\Invoice($database);
    $companies = new App\Models\Company($database);
    (new App\Controllers\InvoiceController($auth, $invoices, $companies))->add();
} elseif ($requestPath === '/quotations/add') {
    $database = App\Config\Database::connection();
    $quotations = new App\Models\Quotation($database);
    $companies = new App\Models\Company($database);
    $quotationMailer = new App\Models\QuotationMailer($mailTransport);
    (new App\Controllers\QuotationController($auth, $quotations, $companies, $quotationMailer))->add();
} elseif (preg_match('#^/quotations/(\d+)/send$#', $requestPath, $matches) === 1) {
    $database = App\Config\Database::connection();
    $quotations = new App\Models\Quotation($database);
    $companies = new App\Models\Company($database);
    $quotationMailer = new App\Models\QuotationMailer($mailTransport);
    (new App\Controllers\QuotationController($auth, $quotations, $companies, $quotationMailer))->send((int) $matches[1]);
} elseif (preg_match('#^/quotations/(\d+)/edit$#', $requestPath, $matches) === 1) {
    $database = App\Config\Database::connection();
    $quotations = new App\Models\Quotation($database);
    $companies = new App\Models\Company($database);
    $quotationMailer = new App\Models\QuotationMailer($mailTransport);
    (new App\Controllers\QuotationController($auth, $quotations, $companies, $quotationMailer))->edit((int) $matches[1]);
} elseif ($requestPath === '/contracts/add') {
    (new App\Controllers\ContractController($auth))->add();
} elseif (preg_match('#^/invoices/(\d+)/preview$#', $requestPath, $matches) === 1) {
    $database = App\Config\Database::connection();
    $invoices = new App\Models\Invoice($database);
    $companies = new App\Models\Company($database);
    (new App\Controllers\InvoiceController($auth, $invoices, $companies))->preview((int) $matches[1]);
} elseif (preg_match('#^/quotations/(\d+)/preview$#', $requestPath, $matches) === 1) {
    $database = App\Config\Database::connection();
    $quotations = new App\Models\Quotation($database);
    $companies = new App\Models\Company($database);
    $quotationMailer = new App\Models\QuotationMailer($mailTransport);
    (new App\Controllers\QuotationController($auth, $quotations, $companies, $quotationMailer))->preview((int) $matches[1]);
} elseif (preg_match('#^/companies/(\d+)/(edit|delete)$#', $requestPath, $matches) === 1) {
    $companies = new App\Models\Company(App\Config\Database::connection());
    $companyController = new App\Controllers\CompanyController($auth, $companies);
    $companyId = (int) $matches[1];
    if ($matches[2] === 'edit') {
        $companyController->edit($companyId);
    } else {
        $companyController->delete($companyId);
    }
} elseif ($requestPath === '/venues/add') {
    $venues = new App\Models\Venue(App\Config\Database::connection());
    (new App\Controllers\VenueController($auth, $venues))->add();
} elseif (preg_match('#^/venues/(\d+)/(edit|delete)$#', $requestPath, $matches) === 1) {
    $venues = new App\Models\Venue(App\Config\Database::connection());
    $venueController = new App\Controllers\VenueController($auth, $venues);
    $venueId = (int) $matches[1];
    if ($matches[2] === 'edit') {
        $venueController->edit($venueId);
    } else {
        $venueController->delete($venueId);
    }
} elseif ($requestPath === '/events/add') {
    $database = App\Config\Database::connection();
    $events = new App\Models\Event($database);
    $companies = new App\Models\Company($database);
    (new App\Controllers\EventController($auth, $events, $companies))->add();
} elseif (preg_match('#^/events/(\d+)$#', $requestPath, $matches) === 1) {
    $database = App\Config\Database::connection();
    $events = new App\Models\Event($database);
    $companies = new App\Models\Company($database);
    (new App\Controllers\EventController($auth, $events, $companies))->show((int) $matches[1]);
} elseif (preg_match('#^/events/(\d+)/(edit|status|delete)$#', $requestPath, $matches) === 1) {
    $database = App\Config\Database::connection();
    $events = new App\Models\Event($database);
    $companies = new App\Models\Company($database);
    $eventController = new App\Controllers\EventController($auth, $events, $companies);
    $eventId = (int) $matches[1];
    if ($matches[2] === 'edit') {
        $eventController->edit($eventId);
    } elseif ($matches[2] === 'status') {
        $eventController->updateStatus($eventId);
    } else {
        $eventController->delete($eventId);
    }
} elseif (preg_match('#^/dashboard/documents/(proposals|invoices|receipts|contract|quotations|proofs|acknowledgement)$#', $requestPath, $matches) === 1) {
    $database = App\Config\Database::connection();
    $invoices = new App\Models\Invoice($database);
    $quotations = new App\Models\Quotation($database);
    (new App\Controllers\DashboardController($auth, $dashboardApi, $invoices, $quotations))->show('documents', $matches[1]);
} elseif (preg_match('#^/dashboard(?:/([a-z]+))?$#', $requestPath, $matches) === 1) {
    $database = App\Config\Database::connection();
    $invoices = new App\Models\Invoice($database);
    $quotations = new App\Models\Quotation($database);
    (new App\Controllers\DashboardController($auth, $dashboardApi, $invoices, $quotations))->show($matches[1] ?? 'overview');
} else {
    (new App\Controllers\HomeController(new App\Models\EnquiryMailer($mailTransport)))->handle();
}

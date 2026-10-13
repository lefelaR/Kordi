<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/app/Config/Database.php';
require __DIR__ . '/app/Models/EnquiryMailer.php';
require __DIR__ . '/app/Models/Auth.php';
require __DIR__ . '/app/Models/Event.php';
require __DIR__ . '/app/Models/Vendor.php';
require __DIR__ . '/app/Models/Company.php';
require __DIR__ . '/app/Controllers/HomeController.php';
require __DIR__ . '/app/Controllers/AuthController.php';
require __DIR__ . '/app/Controllers/DashboardController.php';
require __DIR__ . '/app/Controllers/EventController.php';
require __DIR__ . '/app/Controllers/VendorController.php';
require __DIR__ . '/app/Controllers/CompanyController.php';

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$auth = new App\Models\Auth();

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
} elseif (preg_match('#^/companies/(\d+)/(edit|delete)$#', $requestPath, $matches) === 1) {
    $companies = new App\Models\Company(App\Config\Database::connection());
    $companyController = new App\Controllers\CompanyController($auth, $companies);
    $companyId = (int) $matches[1];
    if ($matches[2] === 'edit') {
        $companyController->edit($companyId);
    } else {
        $companyController->delete($companyId);
    }
} elseif ($requestPath === '/events/add') {
    $events = new App\Models\Event(App\Config\Database::connection());
    (new App\Controllers\EventController($auth, $events))->add();
} elseif (preg_match('#^/events/(\d+)/(edit|status|delete)$#', $requestPath, $matches) === 1) {
    $events = new App\Models\Event(App\Config\Database::connection());
    $eventController = new App\Controllers\EventController($auth, $events);
    $eventId = (int) $matches[1];
    if ($matches[2] === 'edit') {
        $eventController->edit($eventId);
    } elseif ($matches[2] === 'status') {
        $eventController->updateStatus($eventId);
    } else {
        $eventController->delete($eventId);
    }
} elseif (preg_match('#^/dashboard/documents/(proposals|invoices|proofs|acknowledgement)$#', $requestPath, $matches) === 1) {
    $database = App\Config\Database::connection();
    $events = new App\Models\Event($database);
    $vendors = new App\Models\Vendor($database);
    $companies = new App\Models\Company($database);
    (new App\Controllers\DashboardController($auth, $events, $vendors, $companies))->show('documents', $matches[1]);
} elseif (preg_match('#^/dashboard(?:/([a-z]+))?$#', $requestPath, $matches) === 1) {
    $database = App\Config\Database::connection();
    $events = new App\Models\Event($database);
    $vendors = new App\Models\Vendor($database);
    $companies = new App\Models\Company($database);
    (new App\Controllers\DashboardController($auth, $events, $vendors, $companies))->show($matches[1] ?? 'overview');
} else {
    (new App\Controllers\HomeController(new App\Models\EnquiryMailer()))->handle();
}
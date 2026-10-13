<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/app/Config/Database.php';
require __DIR__ . '/app/Models/EnquiryMailer.php';
require __DIR__ . '/app/Models/Auth.php';
require __DIR__ . '/app/Models/Event.php';
require __DIR__ . '/app/Controllers/HomeController.php';
require __DIR__ . '/app/Controllers/AuthController.php';
require __DIR__ . '/app/Controllers/DashboardController.php';
require __DIR__ . '/app/Controllers/EventController.php';

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$auth = new App\Models\Auth();

if ($requestPath === '/login') {
    (new App\Controllers\AuthController($auth))->login();
} elseif ($requestPath === '/logout') {
    (new App\Controllers\AuthController($auth))->logout();
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
} elseif (preg_match('#^/dashboard(?:/([a-z]+))?$#', $requestPath, $matches) === 1) {
    $events = new App\Models\Event(App\Config\Database::connection());
    (new App\Controllers\DashboardController($auth, $events))->show($matches[1] ?? 'overview');
} else {
    (new App\Controllers\HomeController(new App\Models\EnquiryMailer()))->handle();
}
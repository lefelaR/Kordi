<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/app/Models/EnquiryMailer.php';
require __DIR__ . '/app/Models/Auth.php';
require __DIR__ . '/app/Controllers/HomeController.php';
require __DIR__ . '/app/Controllers/AuthController.php';
require __DIR__ . '/app/Controllers/DashboardController.php';

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$auth = new App\Models\Auth();

if ($requestPath === '/login') {
    (new App\Controllers\AuthController($auth))->login();
} elseif ($requestPath === '/logout') {
    (new App\Controllers\AuthController($auth))->logout();
} elseif ($requestPath === '/dashboard') {
    (new App\Controllers\DashboardController($auth))->index();
} else {
    (new App\Controllers\HomeController(new App\Models\EnquiryMailer()))->handle();
}
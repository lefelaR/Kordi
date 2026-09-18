<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/app/Models/EnquiryMailer.php';
require __DIR__ . '/app/Controllers/HomeController.php';

$controller = new App\Controllers\HomeController(
    new App\Models\EnquiryMailer()
);

$controller->handle();
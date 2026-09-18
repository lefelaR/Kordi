<?php

namespace App\Controllers;

use App\Models\EnquiryMailer;

class HomeController
{
    public function __construct(private EnquiryMailer $enquiryMailer)
    {
    }

    public function handle(): void
    {
        $formMessage = '';
        $formMessageClass = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $eventType = trim($_POST['event_type'] ?? '');
            $eventDate = trim($_POST['event_date'] ?? '');
            $message = trim($_POST['message'] ?? '');

            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
                $formMessage = 'Please complete your name, email address, and message.';
                $formMessageClass = 'error';
            } elseif ($this->enquiryMailer->send($name, $email, $eventType, $eventDate, $message)) {
                $formMessage = 'Thank you. Your enquiry has been sent.';
                $formMessageClass = 'success';
            } else {
                $formMessage = 'Your enquiry could not be sent right now. Please try again or contact us directly.';
                $formMessageClass = 'error';
            }
        }

        require __DIR__ . '/../Views/home.php';
    }
}
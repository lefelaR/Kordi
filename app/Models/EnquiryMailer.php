<?php

namespace App\Models;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class EnquiryMailer
{
    public function send(
        string $name,
        string $email,
        string $eventType,
        string $eventDate,
        string $message
    ): bool {
        try {
            $mailer = new PHPMailer(true);
            $mailer->isMail();
            $mailer->setFrom('website@kordicraftcreations.co.za', 'Kordicraft Creations Website');
            $mailer->addAddress('info@kordicraftcreations.co.za');
            $mailer->addReplyTo($email, $name);
            $mailer->Subject = 'New event enquiry from ' . $name;
            $mailer->isHTML(false);
            $mailer->Body = "Name: {$name}\n"
                . "Email: {$email}\n"
                . "Event type: {$eventType}\n"
                . "Event date: {$eventDate}\n\n"
                . "Message:\n{$message}";

            return $mailer->send();
        } catch (Exception $exception) {
            error_log('Unable to send enquiry email: ' . $exception->getMessage());
            return false;
        }
    }
}
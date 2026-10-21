<?php

namespace App\Models;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class QuotationMailer
{
    public function send(string $email, string $companyName, string $quotationNumber, string $title): bool
    {
        try {
            $mailer = new PHPMailer(true);
            $mailer->isMail();
            $mailer->setFrom('info@kordicraftcreations.co.za', 'Kordicraft Creations');
            $mailer->addAddress($email, $companyName);
            $mailer->Subject = 'Quotation ' . $quotationNumber;
            $mailer->isHTML(false);
            $mailer->Body = "Quotation {$quotationNumber}: {$title}\n\n"
                . "Please find your quotation from Kordicraft Creations.";

            return $mailer->send();
        } catch (Exception $exception) {
            error_log('Unable to send quotation email: ' . $exception->getMessage());

            return false;
        }
    }
}

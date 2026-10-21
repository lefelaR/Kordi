<?php

namespace App\Models;

use PHPMailer\PHPMailer\Exception;

class QuotationMailer
{
    public function __construct(private MailTransport $transport)
    {
    }

    public function send(string $email, string $companyName, string $quotationNumber, string $title): bool
    {
        try {
            $mailer = $this->transport->open();
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

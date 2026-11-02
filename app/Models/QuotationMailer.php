<?php

namespace App\Models;

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use Throwable;

class QuotationMailer
{
    public function __construct(private MailTransport $transport)
    {
    }

    public function send(
        string $email,
        string $companyName,
        string $quotationNumber,
        string $title,
        string $documentHtml,
    ): bool {
        try {
            $mailer = $this->transport->open();
            $mailer->setFrom('info@kordicraftcreations.co.za', 'Kordicraft Creations');
            $mailer->addAddress($email, $companyName);
            $mailer->Subject = 'Quotation ' . $quotationNumber;
            $mailer->isHTML(false);
            $mailer->Body = "Quotation {$quotationNumber}: {$title}\n\n"
                . "Please find your quotation attached.";
            $mailer->addStringAttachment(
                $this->pdf($documentHtml),
                $this->filename($quotationNumber),
                PHPMailer::ENCODING_BASE64,
                'application/pdf'
            );

            return $mailer->send();
        } catch (Throwable $exception) {
            error_log('Unable to send quotation email: ' . $exception->getMessage());

            return false;
        }
    }

    private function pdf(string $html): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $document = new Dompdf($options);
        $document->loadHtml($html);
        $document->setPaper('A4');
        $document->render();

        return $document->output();
    }

    private function filename(string $quotationNumber): string
    {
        $safe = preg_replace('/[^A-Za-z0-9._-]+/', '-', $quotationNumber) ?? 'quotation';
        $safe = trim($safe, '-.');
        if ($safe === '') {
            $safe = 'quotation';
        }

        return $safe . '.pdf';
    }
}

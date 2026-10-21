<?php

namespace App\Models;

use PHPMailer\PHPMailer\PHPMailer;

class MailTransport
{
    public function open(): PHPMailer
    {
        $mailer = new PHPMailer(true);
        $host = $this->setting('MAIL_HOST');

        if ($host === '') {
            $mailer->isMail();

            return $mailer;
        }

        $mailer->isSMTP();
        $mailer->Host = $host;
        $port = $this->setting('MAIL_PORT');
        $mailer->Port = $port === '' ? 587 : (int) $port;

        $username = $this->setting('MAIL_USERNAME');
        $mailer->SMTPAuth = $username !== '';
        if ($username !== '') {
            $mailer->Username = $username;
            $mailer->Password = $this->setting('MAIL_PASSWORD');
        }

        $encryption = strtolower($this->setting('MAIL_ENCRYPTION'));
        if ($encryption === 'tls') {
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($encryption === 'ssl') {
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mailer->SMTPAutoTLS = false;
            $mailer->SMTPSecure = '';
        }

        return $mailer;
    }

    private function setting(string $name): string
    {
        $value = $_ENV[$name] ?? getenv($name);

        return is_string($value) ? trim($value) : '';
    }
}

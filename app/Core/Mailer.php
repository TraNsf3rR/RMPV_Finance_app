<?php

declare(strict_types=1);

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

class Mailer
{
    public function sendPasswordReset(string $recipient, string $token): void
    {
        $appUrl = rtrim((string) config('app_url', ''), '/');
        $appUrlParts = parse_url($appUrl);

        if (
            $appUrlParts === false
            || !in_array($appUrlParts['scheme'] ?? '', ['http', 'https'], true)
            || empty($appUrlParts['host'])
            || isset($appUrlParts['user'])
            || isset($appUrlParts['pass'])
            || isset($appUrlParts['query'])
            || isset($appUrlParts['fragment'])
            || !in_array($appUrlParts['path'] ?? '', ['', '/'], true)
        ) {
            throw new RuntimeException('APP_URL must be configured as an absolute HTTP or HTTPS origin.');
        }

        $username = (string) config('mail.username', '');
        $password = (string) config('mail.password', '');
        $fromAddress = (string) config('mail.from_address', '');

        if ($username === '' || $password === '' || $fromAddress === '') {
            throw new RuntimeException(
                'Configure MAIL_USERNAME, MAIL_PASSWORD, and MAIL_FROM_ADDRESS to enable password-reset email.'
            );
        }

        $resetUrl = $appUrl . \url('/reset-password?token=' . rawurlencode($token));
        $safeResetUrl = htmlspecialchars($resetUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = (string) config('mail.host', 'smtp.gmail.com');
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int) config('mail.port', 587);
        $mail->Timeout = 10;
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->setFrom($fromAddress, (string) config('mail.from_name', 'Finance Tracker'));
        $mail->addAddress($recipient);
        $mail->isHTML(true);
        $mail->Subject = 'Reset your Finance Tracker password';
        $mail->Body = '<p>We received a request to reset your Finance Tracker password.</p>'
            . '<p><a href="' . $safeResetUrl . '">Choose a new password</a></p>'
            . '<p>This link expires in one hour. If you did not request a reset, you can ignore this email.</p>';
        $mail->AltBody = "We received a request to reset your Finance Tracker password.\n\n"
            . "Choose a new password using this link:\n{$resetUrl}\n\n"
            . 'This link expires in one hour. If you did not request a reset, you can ignore this email.';
        $mail->send();
    }
}

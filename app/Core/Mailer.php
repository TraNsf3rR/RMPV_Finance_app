<?php

declare(strict_types=1);

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

class Mailer
{
    public function sendPasswordReset(string $recipient, string $token): void
    {
        $resetUrl = $this->applicationUrl('/reset-password?token=' . rawurlencode($token));
        $safeResetUrl = htmlspecialchars($resetUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $this->send(
            $recipient,
            'Reset your Finance Tracker password',
            '<p>We received a request to reset your Finance Tracker password.</p>'
            . '<p><a href="' . $safeResetUrl . '">Choose a new password</a></p>'
            . '<p>This link expires in one hour. If you did not request a reset, you can ignore this email.</p>',
            "We received a request to reset your Finance Tracker password.\n\n"
            . "Choose a new password using this link:\n{$resetUrl}\n\n"
            . 'This link expires in one hour. If you did not request a reset, you can ignore this email.'
        );
    }

    public function sendEmailVerification(string $recipient, string $token): void
    {
        $verificationUrl = $this->applicationUrl('/verify-email?token=' . rawurlencode($token));
        $safeVerificationUrl = htmlspecialchars($verificationUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $this->send(
            $recipient,
            'Activate your Finance Tracker account',
            '<p>Complete your Finance Tracker registration by activating your account.</p>'
            . '<p><a href="' . $safeVerificationUrl . '">Review account activation</a></p>'
            . '<p>This link expires in 24 hours. If you did not create this account, you can ignore this email.</p>',
            "Complete your Finance Tracker registration by activating your account.\n\n"
            . "Review account activation using this link:\n{$verificationUrl}\n\n"
            . 'This link expires in 24 hours. If you did not create this account, you can ignore this email.'
        );
    }

    private function applicationUrl(string $path): string
    {
        $appUrl = rtrim((string) config('app_url', ''), '/');
        $appUrlParts = parse_url($appUrl);
        $scheme = is_array($appUrlParts) ? strtolower((string) ($appUrlParts['scheme'] ?? '')) : '';
        $host = is_array($appUrlParts) ? strtolower(trim((string) ($appUrlParts['host'] ?? ''), '[]')) : '';
        $isLocalHost = in_array($host, ['localhost', '127.0.0.1', '::1'], true);

        if (
            $appUrlParts === false
            || !in_array($scheme, ['http', 'https'], true)
            || ($scheme !== 'https' && !$isLocalHost)
            || empty($appUrlParts['host'])
            || isset($appUrlParts['user'])
            || isset($appUrlParts['pass'])
            || isset($appUrlParts['query'])
            || isset($appUrlParts['fragment'])
            || !in_array($appUrlParts['path'] ?? '', ['', '/'], true)
        ) {
            throw new RuntimeException(
                'APP_URL must be an HTTPS origin, except for localhost development.'
            );
        }

        return $appUrl . \url($path);
    }

    private function send(string $recipient, string $subject, string $htmlBody, string $textBody): void
    {
        $username = (string) config('mail.username', '');
        $password = (string) config('mail.password', '');
        $fromAddress = (string) config('mail.from_address', '');

        if ($username === '' || $password === '' || $fromAddress === '') {
            throw new RuntimeException(
                'Configure MAIL_USERNAME, MAIL_PASSWORD, and MAIL_FROM_ADDRESS to enable email delivery.'
            );
        }

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
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $textBody;
        $mail->send();
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Mail;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

final class SymfonyMailer implements MailerInterface
{
    public function __construct(
        private readonly string $dsn,
        private readonly string $from,
    ) {
    }

    public function send(string $to, string $subject, string $body): void
    {
        $transport = Transport::fromDsn($this->dsn);
        $mailer = new Mailer($transport);

        $email = (new Email())
            ->from($this->from)
            ->to($to)
            ->subject($subject)
            ->text($body);

        $mailer->send($email);
    }
}

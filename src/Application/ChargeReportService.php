<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Charge;
use App\Domain\Repository\ChargeRepositoryInterface;
use App\Domain\Repository\MerchantRepositoryInterface;
use App\Infrastructure\Mail\MailerInterface;
use DateTimeImmutable;
use RuntimeException;

final class ChargeReportService
{
    public function __construct(
        private readonly MerchantRepositoryInterface $merchants,
        private readonly ChargeRepositoryInterface $charges,
        private readonly MailerInterface $mailer,
    ) {
    }

    public function send(
        int $merchantId,
        DateTimeImmutable $from,
        DateTimeImmutable $to
    ): void {
        if ($to < $from) {
            throw new RuntimeException('"to" must be after "from"');
        }

        $merchant = $this->merchants->findById($merchantId);

        if ($merchant === null) {
            throw new RuntimeException('Merchant not found');
        }

        $charges = $this->charges->findForMerchantBetween($merchantId, $from, $to);

        $successful = array_filter(
            $charges,
            static fn (Charge $charge): bool => $charge->status === 'successful'
        );

        $failed = array_filter(
            $charges,
            static fn (Charge $charge): bool => $charge->status === 'failed'
        );

        $total = array_sum(
            array_map(
                static fn (Charge $charge): int => $charge->amount,
                $successful
            )
        );

        $lines = [
            "Merchant: {$merchant->name}",
            '',
            'Charge Report',
            $from->format('Y-m-d H:i:s') . ' - ' . $to->format('Y-m-d H:i:s'),
            '',
            sprintf('%-5s %-19s %-12s %-12s %-12s', 'ID', 'Date', 'Amount', 'Provider', 'Status'),
            str_repeat('-', 68),
        ];

        foreach ($charges as $charge) {
            $lines[] = sprintf(
                '%-5s %-19s %-12s %-12s %-12s',
                (string) $charge->id,
                $charge->createdAt->format('Y-m-d H:i:s'),
                number_format($charge->amount / 100, 2) . ' ' . $charge->currency,
                $charge->provider,
                $charge->status
            );
        }

        $lines[] = '';
        $lines[] = 'Successful charges: ' . count($successful);
        $lines[] = 'Failed charges: ' . count($failed);
        $lines[] = 'Total successful amount: ' . number_format($total / 100, 2) . ' EUR';

        $this->mailer->send(
            $merchant->email,
            'Payment charge report',
            implode(PHP_EOL, $lines)
        );
    }
}

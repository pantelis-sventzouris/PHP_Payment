<?php

declare(strict_types=1);

namespace App\Domain;

use DateTimeImmutable;

final class Charge
{
    public function __construct(
        public ?int $id,
        public readonly int $merchantId,
        public readonly int $amount,
        public readonly string $currency,
        public readonly string $provider,
        public readonly ?string $providerReference,
        public readonly string $status,
        public readonly ?string $failureReason,
        public readonly ?string $cardLastFour,
        public readonly DateTimeImmutable $createdAt,
    ) {
    }
}

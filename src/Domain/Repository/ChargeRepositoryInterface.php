<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Charge;
use DateTimeImmutable;

interface ChargeRepositoryInterface
{
    public function save(Charge $charge): void;

    /**
     * @return Charge[]
     */
    public function findForMerchantBetween(
        int $merchantId,
        DateTimeImmutable $from,
        DateTimeImmutable $to
    ): array;
}

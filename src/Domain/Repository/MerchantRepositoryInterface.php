<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Merchant;

interface MerchantRepositoryInterface
{
    public function findByApiKey(string $apiKey): ?Merchant;

    public function findById(int $merchantId): ?Merchant;
}

<?php

declare(strict_types=1);

namespace App\Http;

use App\Domain\Merchant;
use App\Domain\Repository\MerchantRepositoryInterface;

final class Authenticator
{
    public function __construct(
        private readonly MerchantRepositoryInterface $merchants
    ) {
    }

    public function authenticate(?string $apiKey): Merchant
    {
        if ($apiKey === null || trim($apiKey) === '') {
            throw new AuthenticationException('Missing API key');
        }

        $merchant = $this->merchants->findByApiKey(trim($apiKey));

        if ($merchant === null) {
            throw new AuthenticationException('Invalid API key');
        }

        return $merchant;
    }
}

<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Charge;
use App\Domain\Merchant;
use App\Domain\Repository\ChargeRepositoryInterface;
use App\Infrastructure\Payment\PaymentProviderFactory;
use DateTimeImmutable;
use InvalidArgumentException;

final class ChargeService
{
    public function __construct(
        private readonly ChargeRepositoryInterface $charges,
        private readonly PaymentProviderFactory $providerFactory,
    ) {
    }

    public function charge(Merchant $merchant, int $amount, array $paymentDetails): Charge
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount must be greater than zero');
        }

        $provider = $this->providerFactory->create($merchant->provider);
        $result = $provider->charge($amount, $paymentDetails);

        $lastFour = null;

        if ($merchant->provider === 'fakeStripe' && isset($paymentDetails['card_number'])) {
            $normalized = preg_replace('/\D+/', '', (string) $paymentDetails['card_number']);
            $lastFour = substr($normalized, -4) ?: null;
        }

        $charge = new Charge(
            id: null,
            merchantId: $merchant->id,
            amount: $amount,
            currency: 'EUR',
            provider: $merchant->provider,
            providerReference: $result->reference,
            status: $result->successful ? 'successful' : 'failed',
            failureReason: $result->failureReason,
            cardLastFour: $lastFour,
            createdAt: new DateTimeImmutable(),
        );

        $this->charges->save($charge);

        return $charge;
    }
}

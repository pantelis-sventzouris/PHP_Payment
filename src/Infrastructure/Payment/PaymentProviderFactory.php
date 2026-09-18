<?php

declare(strict_types=1);

namespace App\Infrastructure\Payment;

use App\Domain\Payment\PaymentProviderInterface;
use InvalidArgumentException;

final class PaymentProviderFactory
{
    public function create(string $provider): PaymentProviderInterface
    {
        return match ($provider) {
            'fakeStripe' => new FakeStripeProvider(),
            'fakePaypal' => new FakePaypalProvider(),
            default => throw new InvalidArgumentException("Unsupported provider: {$provider}"),
        };
    }
}

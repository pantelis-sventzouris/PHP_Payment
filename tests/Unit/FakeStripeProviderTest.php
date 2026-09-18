<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Infrastructure\Payment\FakeStripeProvider;
use PHPUnit\Framework\TestCase;

final class FakeStripeProviderTest extends TestCase
{
    public function testSuccessfulCharge(): void
    {
        $provider = new FakeStripeProvider();

        $result = $provider->charge(1000, [
            'card_number' => '4242424242424242',
            'cvv' => '123',
            'expiry_month' => 12,
            'expiry_year' => (int) date('Y') + 2,
        ]);

        self::assertTrue($result->successful);
        self::assertNotNull($result->reference);
        self::assertNull($result->failureReason);
    }

    public function testCardEndingIn0000IsDeclined(): void
    {
        $provider = new FakeStripeProvider();

        $result = $provider->charge(1000, [
            'card_number' => '4242424242420000',
            'cvv' => '123',
            'expiry_month' => 12,
            'expiry_year' => (int) date('Y') + 2,
        ]);

        self::assertFalse($result->successful);
        self::assertSame('Card declined', $result->failureReason);
    }
}

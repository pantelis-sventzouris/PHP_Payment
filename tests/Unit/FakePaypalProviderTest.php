<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Infrastructure\Payment\FakePaypalProvider;
use PHPUnit\Framework\TestCase;

final class FakePaypalProviderTest extends TestCase
{
    public function testSuccessfulCharge(): void
    {
        $provider = new FakePaypalProvider();

        $result = $provider->charge(1000, [
            'email' => 'customer@example.com',
            'password' => 'secret',
        ]);

        self::assertTrue($result->successful);
        self::assertNotNull($result->reference);
    }

    public function testFailEmailIsDeclined(): void
    {
        $provider = new FakePaypalProvider();

        $result = $provider->charge(1000, [
            'email' => 'fail@example.com',
            'password' => 'secret',
        ]);

        self::assertFalse($result->successful);
        self::assertSame('PayPal payment rejected', $result->failureReason);
    }
}

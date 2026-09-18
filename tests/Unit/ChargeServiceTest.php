<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Application\ChargeService;
use App\Domain\Charge;
use App\Domain\Merchant;
use App\Domain\Repository\ChargeRepositoryInterface;
use App\Infrastructure\Payment\PaymentProviderFactory;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ChargeServiceTest extends TestCase
{
    public function testSuccessfulChargeIsPersisted(): void
    {
        $repository = new class implements ChargeRepositoryInterface {
            public array $saved = [];

            public function save(Charge $charge): void
            {
                $charge->id = 1;
                $this->saved[] = $charge;
            }

            public function findForMerchantBetween(
                int $merchantId,
                DateTimeImmutable $from,
                DateTimeImmutable $to
            ): array {
                return [];
            }
        };

        $service = new ChargeService(
            $repository,
            new PaymentProviderFactory()
        );

        $merchant = new Merchant(
            1,
            'Test Merchant',
            'merchant@example.test',
            'fakeStripe'
        );

        $charge = $service->charge($merchant, 1999, [
            'card_number' => '4242424242424242',
            'cvv' => '123',
            'expiry_month' => 12,
            'expiry_year' => (int) date('Y') + 2,
        ]);

        self::assertSame('successful', $charge->status);
        self::assertSame(1999, $charge->amount);
        self::assertSame('4242', $charge->cardLastFour);
        self::assertCount(1, $repository->saved);
    }
}

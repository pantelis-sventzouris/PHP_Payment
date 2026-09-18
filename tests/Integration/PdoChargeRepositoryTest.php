<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Domain\Charge;
use App\Infrastructure\Database\PdoChargeRepository;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;

final class PdoChargeRepositoryTest extends TestCase
{
    public function testSaveAndFindByMerchantAndDateRange(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $schema = file_get_contents(dirname(__DIR__, 2) . '/database/schema.sql');
        self::assertNotFalse($schema);
        $pdo->exec($schema);

        $pdo->exec("
            INSERT INTO merchants (name, email, api_key_hash, provider)
            VALUES ('Test', 'test@example.test', 'abc', 'fakeStripe')
        ");

        $repository = new PdoChargeRepository($pdo);

        $now = new DateTimeImmutable();

        $charge = new Charge(
            id: null,
            merchantId: 1,
            amount: 2500,
            currency: 'EUR',
            provider: 'fakeStripe',
            providerReference: 'stripe_test',
            status: 'successful',
            failureReason: null,
            cardLastFour: '4242',
            createdAt: $now
        );

        $repository->save($charge);

        $found = $repository->findForMerchantBetween(
            1,
            $now->modify('-1 minute'),
            $now->modify('+1 minute')
        );

        self::assertCount(1, $found);
        self::assertSame(2500, $found[0]->amount);
        self::assertSame('stripe_test', $found[0]->providerReference);
    }
}

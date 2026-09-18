<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Domain\Charge;
use App\Domain\Repository\ChargeRepositoryInterface;
use DateTimeImmutable;
use PDO;

final class PdoChargeRepository implements ChargeRepositoryInterface
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function save(Charge $charge): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO charges (
                merchant_id,
                amount,
                currency,
                provider,
                provider_reference,
                status,
                failure_reason,
                card_last_four,
                created_at
            ) VALUES (
                :merchant_id,
                :amount,
                :currency,
                :provider,
                :provider_reference,
                :status,
                :failure_reason,
                :card_last_four,
                :created_at
            )'
        );

        $statement->execute([
            'merchant_id' => $charge->merchantId,
            'amount' => $charge->amount,
            'currency' => $charge->currency,
            'provider' => $charge->provider,
            'provider_reference' => $charge->providerReference,
            'status' => $charge->status,
            'failure_reason' => $charge->failureReason,
            'card_last_four' => $charge->cardLastFour,
            'created_at' => $charge->createdAt->format('Y-m-d H:i:s'),
        ]);

        $charge->id = (int) $this->pdo->lastInsertId();
    }

    public function findForMerchantBetween(
        int $merchantId,
        DateTimeImmutable $from,
        DateTimeImmutable $to
    ): array {
        $statement = $this->pdo->prepare(
            'SELECT *
             FROM charges
             WHERE merchant_id = :merchant_id
               AND created_at >= :date_from
               AND created_at <= :date_to
             ORDER BY created_at ASC'
        );

        $statement->execute([
            'merchant_id' => $merchantId,
            'date_from' => $from->format('Y-m-d H:i:s'),
            'date_to' => $to->format('Y-m-d H:i:s'),
        ]);

        $charges = [];

        foreach ($statement->fetchAll() as $row) {
            $charges[] = new Charge(
                id: (int) $row['id'],
                merchantId: (int) $row['merchant_id'],
                amount: (int) $row['amount'],
                currency: (string) $row['currency'],
                provider: (string) $row['provider'],
                providerReference: $row['provider_reference'] !== null ? (string) $row['provider_reference'] : null,
                status: (string) $row['status'],
                failureReason: $row['failure_reason'] !== null ? (string) $row['failure_reason'] : null,
                cardLastFour: $row['card_last_four'] !== null ? (string) $row['card_last_four'] : null,
                createdAt: new DateTimeImmutable((string) $row['created_at']),
            );
        }

        return $charges;
    }
}

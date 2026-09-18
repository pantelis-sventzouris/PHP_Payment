<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Domain\Merchant;
use App\Domain\Repository\MerchantRepositoryInterface;
use PDO;

final class PdoMerchantRepository implements MerchantRepositoryInterface
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findByApiKey(string $apiKey): ?Merchant
    {
        $hash = hash('sha256', $apiKey);

        $statement = $this->pdo->prepare(
            'SELECT id, name, email, provider
             FROM merchants
             WHERE api_key_hash = :api_key_hash
             LIMIT 1'
        );

        $statement->execute(['api_key_hash' => $hash]);

        $row = $statement->fetch();

        return $row ? $this->map($row) : null;
    }

    public function findById(int $merchantId): ?Merchant
    {
        $statement = $this->pdo->prepare(
            'SELECT id, name, email, provider
             FROM merchants
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute(['id' => $merchantId]);

        $row = $statement->fetch();

        return $row ? $this->map($row) : null;
    }

    private function map(array $row): Merchant
    {
        return new Merchant(
            (int) $row['id'],
            (string) $row['name'],
            (string) $row['email'],
            (string) $row['provider'],
        );
    }
}

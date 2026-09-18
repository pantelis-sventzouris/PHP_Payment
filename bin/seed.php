<?php

declare(strict_types=1);

use App\Infrastructure\Database\Connection;

require dirname(__DIR__) . '/vendor/autoload.php';

$config = require dirname(__DIR__) . '/config/config.php';

$pdo = Connection::create($config['database_path']);

$merchants = [
    [
        'name' => 'Stripe Demo Merchant',
        'email' => 'stripe-merchant@example.test',
        'api_key' => 'stripe-merchant-secret',
        'provider' => 'fakeStripe',
    ],
    [
        'name' => 'PayPal Demo Merchant',
        'email' => 'paypal-merchant@example.test',
        'api_key' => 'paypal-merchant-secret',
        'provider' => 'fakePaypal',
    ],
];

$statement = $pdo->prepare(
    'INSERT OR IGNORE INTO merchants (
        name,
        email,
        api_key_hash,
        provider
    ) VALUES (
        :name,
        :email,
        :api_key_hash,
        :provider
    )'
);

foreach ($merchants as $merchant) {
    $statement->execute([
        'name' => $merchant['name'],
        'email' => $merchant['email'],
        'api_key_hash' => hash('sha256', $merchant['api_key']),
        'provider' => $merchant['provider'],
    ]);
}

fwrite(STDOUT, "Seed completed.\n");
fwrite(STDOUT, "Stripe API key: stripe-merchant-secret\n");
fwrite(STDOUT, "PayPal API key: paypal-merchant-secret\n");
